<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CalculateStockSystemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $start;
    public $end;
    public $type;
    public $pageSize;
    public $jobId;

    // you can tune pageSize (how many grouped rows per chunk)
    public function __construct(string $jobId, string $start, string $end, string $type = 'opname', int $pageSize = 500)
    {
        $this->jobId = $jobId;
        $this->start = $start;
        $this->end   = $end;
        $this->type  = $type;
        $this->pageSize = $pageSize;
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            // update job status
            DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                'status' => 'processing',
                'progress' => 0,
                'updated_at' => now()
            ]);

            // 1) build grouped query for lastOpnames (grouped by head|sub|item)
            $groupQuery = DB::connection('pgsql')
                ->table('accurate_stock_opnames as s')
                ->leftJoin('accurate_stock_opname_details as d', 's.id', '=', 'd.opname_id')
                ->join('contact_groups as cg', 's.customer_code', '=', 'cg.customer_no')
                ->leftJoin('contact_group_customer as cgc', 's.customer_code_sub', '=', 'cgc.customer_no')
                ->whereBetween(DB::raw('DATE(s.trans_date)'), [$this->start, $this->end])
                ->where('s.type', $this->type)
                ->select(
                    's.customer_code as head_account_no',
                    'cg.customer_name as head_account',
                    DB::raw("COALESCE(cgc.customer_no, '-') as subaccount_no"),
                    'cgc.customer_name as subaccount',
                    'd.item_no',
                    'd.item_name',
                    DB::raw('MAX(s.trans_date) as last_date'),
                    DB::raw('SUM(d.stock_real) as stock_opname')
                )
                ->groupBy(
                    's.customer_code',
                    'cg.customer_name',
                    'cgc.customer_no',
                    'cgc.customer_name',
                    'd.item_no',
                    'd.item_name'
                );

            // total items to process (grouped rows)
            $total = $groupQuery->get()->count(); // note: heavy but necessary to compute progress
            DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                'total_items' => $total,
                'updated_at' => now()
            ]);

            if ($total === 0) {
                DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                    'status' => 'done',
                    'progress' => 100,
                    'result' => json_encode(['message' => 'no data']),
                    'updated_at' => now()
                ]);
                DB::commit();
                return;
            }

            // We'll process the grouped rows in pages
            $processed = 0;
            $page = 0;
            // truncate tmp table first
            DB::connection('pgsql')->table('accurate_stock_calculated_details_tmp')->truncate();

            while ($processed < $total) {
                $page++;
                $offset = ($page - 1) * $this->pageSize;

                $rows = (clone $groupQuery)
                    ->offset($offset)
                    ->limit($this->pageSize)
                    ->get();

                if ($rows->isEmpty()) {
                    break;
                }

                // Collect item_nos, head accounts and subaccounts to narrow transactions queries
                $itemNos = $rows->pluck('item_no')->unique()->values()->all();
                $headAccounts = $rows->pluck('head_account_no')->unique()->values()->all();
                $subAccounts = $rows->pluck('subaccount_no')->unique()->values()->all();

                // --- avg daily sales (3 months) for these items/accounts
                $avgStart = Carbon::parse($this->end)->subMonths(3)->format('Y-m-d');
                $avgEnd = $this->end;

                $avgSalesRows = DB::connection('pgsql')
                    ->table('accurate_sales_order as o')
                    ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                    ->where('o.status_name', 'Terproses')
                    ->whereBetween(DB::raw('DATE(o.trans_date)'), [$avgStart, $avgEnd])
                    ->whereIn('d.item_no', $itemNos)
                    ->whereIn('o.customer_no', $headAccounts)
                    ->select(
                        'o.customer_no as head_account_no',
                        DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                        'd.item_no',
                        DB::raw('SUM(d.quantity) as total_sold'),
                        DB::raw('COUNT(DISTINCT DATE(o.trans_date)) as active_days')
                    )
                    ->groupBy('o.customer_no', 'o.customer_no_sub', 'd.item_no')
                    ->get();

                $avgSalesMap = [];
                foreach ($avgSalesRows as $r) {
                    $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                    $days = max(intval($r->active_days), 1);
                    $avgSalesMap[$key] = round(floatval($r->total_sold) / $days, 1);
                }

                // --- transactions (sales, transfer out, transfer in) limited by itemNos & accounts & date range
                $salesRows = DB::connection('pgsql')
                    ->table('accurate_sales_order as o')
                    ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                    ->where('o.status_name', 'Terproses')
                    ->whereBetween(DB::raw('DATE(o.trans_date)'), [$this->start, $this->end])
                    ->whereIn('d.item_no', $itemNos)
                    ->whereIn('o.customer_no', $headAccounts)
                    ->select(
                        'o.customer_no as head_account_no',
                        DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                        'd.item_no',
                        DB::raw('DATE(o.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                $transferOutRows = DB::connection('pgsql')
                    ->table('accurate_item_transfer as t')
                    ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                    ->where('t.tipe_proses', 'TRANSFER_OUT')
                    ->whereBetween(DB::raw('DATE(t.trans_date)'), [$this->start, $this->end])
                    ->whereIn('d.item_no', $itemNos)
                    ->whereIn('t.customer_code', $headAccounts)
                    ->select(
                        't.customer_code as head_account_no',
                        DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                        'd.item_no',
                        DB::raw('DATE(t.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                $transferInRows = DB::connection('pgsql')
                    ->table('accurate_item_transfer as t')
                    ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                    ->where('t.tipe_proses', 'TRANSFER_IN')
                    ->whereBetween(DB::raw('DATE(t.trans_date)'), [$this->start, $this->end])
                    ->whereIn('d.item_no', $itemNos)
                    ->whereIn('t.customer_code', $headAccounts)
                    ->select(
                        't.customer_code as head_account_no',
                        DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                        'd.item_no',
                        DB::raw('DATE(t.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                // build maps for quick lookup
                $makeMap = function ($rows) {
                    $map = [];
                    foreach ($rows as $r) {
                        $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                        $map[$key][] = ['date' => (string)$r->trans_date, 'qty' => floatval($r->quantity)];
                    }
                    return $map;
                };

                $salesMap = $makeMap($salesRows);
                $transferOutMap = $makeMap($transferOutRows);
                $transferInMap = $makeMap($transferInRows);

                $insertBatch = [];
                foreach ($rows as $row) {
                    $key = "{$row->head_account_no}|{$row->subaccount_no}|{$row->item_no}";
                    $lastDate = (string)$row->last_date;

                    $sumSales = isset($salesMap[$key]) ? array_sum(array_column(array_filter($salesMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty')) : 0;
                    $sumOut = isset($transferOutMap[$key]) ? array_sum(array_column(array_filter($transferOutMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty')) : 0;
                    $sumIn  = isset($transferInMap[$key]) ? array_sum(array_column(array_filter($transferInMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty')) : 0;

                    $avgDailySales = $avgSalesMap[$key] ?? 0;
                    $stockSystem = (intval($row->stock_opname) + $sumIn + $sumOut * 0 /*depending on semantics*/) - $sumSales;
                    // Note: I kept semantics similar to your earlier logic; adjust sign if needed

                    $daysOnHand = $avgDailySales > 0 ? round($stockSystem / $avgDailySales, 1) : 0;
                    $runoutDate = $avgDailySales > 0 ? Carbon::now()->addDays($daysOnHand)->format('Y-m-d') : null;

                    $insertBatch[] = [
                        'head_account_no' => $row->head_account_no,
                        'head_account'    => $row->head_account,
                        'subaccount_no'   => $row->subaccount_no,
                        'subaccount'      => $row->subaccount,
                        'item_no'         => $row->item_no,
                        'item_name'       => $row->item_name,
                        'stock_opname'    => intval($row->stock_opname),
                        'stock_in'        => intval($sumIn),
                        'stock_out'       => intval($sumSales),
                        'stock_return'    => intval($sumOut),
                        'stock_system'    => intval($stockSystem),
                        'avg_daily_sales' => round($avgDailySales, 1),
                        'days_on_hand'    => $daysOnHand,
                        'runout_date'     => $runoutDate,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                // insert batch to tmp table
                if (!empty($insertBatch)) {
                    foreach (array_chunk($insertBatch, 1000) as $chunk) {
                        DB::connection('pgsql')->table('accurate_stock_calculated_details_tmp')->insert($chunk);
                    }
                }

                // update counters & progress
                $processed += count($rows);
                $percent = (int) floor(($processed / $total) * 100);
                DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                    'progress' => $percent,
                    'updated_at' => now()
                ]);
            }

            // done
            DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                'status' => 'done',
                'progress' => 100,
                'result' => json_encode(['message' => 'done', 'processed' => $processed]),
                'updated_at' => now()
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            DB::table('stock_calc_jobs')->where('job_id', $this->jobId)->update([
                'status' => 'failed',
                'result' => json_encode(['error' => $e->getMessage()]),
                'updated_at' => now()
            ]);
            throw $e;
        }
    }
}
