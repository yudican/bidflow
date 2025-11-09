<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Exports\StockComparisonExport;
use App\Jobs\CalculateStockSystemJob;

class AccurateController extends Controller
{
    public function index()
    {
        return view('spa.spa-index');
    }

    public function customerJson()
    {
        $data = DB::connection('pgsql')
            ->table('accurate_customers')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function warehouseJson()
    {
        $data = DB::connection('pgsql')
            ->table('accurate_warehouses')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function storeJson()
    {
        // Get store data from PostgreSQL
        $data = DB::connection('pgsql')
            ->table('contact_group_customer as d')
            ->join('contact_groups as i', 'd.contact_group_id', '=', 'i.id')
            ->select(
                'd.id',
                'd.customer_name',
                'i.name as contact_group_name',
                'd.id_user'
            )
            ->get();

        // Get all user IDs that are assigned to stores
        $userIds = $data->pluck('id_user')->filter()->unique()->values();

        // Fetch user data from MySQL (default connection)
        $users = collect();
        if ($userIds->isNotEmpty()) {
            $users = DB::table('users')
                ->whereIn('id', $userIds)
                ->select('id', 'name')
                ->get()
                ->keyBy('id');
        }

        // Merge user data with store data
        $data = $data->map(function ($store) use ($users) {
            $store->assigned_to = $store->id_user && $users->has($store->id_user)
                ? $users->get($store->id_user)->name
                : null;
            return $store;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function storeMDJson($user)
    {
        $data = DB::connection('pgsql')
            ->table('contact_group_customer as d')
            ->select(
                'd.id',
                'd.customer_name'
            )
            ->where('d.id_user', $user)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function merchandiserJson()
    {
        $merchandisers = \App\Models\User::with(['roles' => function ($q) {
                $q->where('role_type', 'merchandiser');
            }])
            ->whereHas('roles', function ($q) {
                $q->where('role_type', 'merchandiser');
            })
            ->get(['id', 'name']);

        $data = $merchandisers->map(function ($m) {
            $toko = DB::connection('pgsql')
                ->table('contact_group_customer')
                ->where('id_user', $m->id)
                ->pluck('id');

            $jumlahStockCount = DB::connection('pgsql')
                ->table('stock_count as d')
                ->where('created_by', $m->id)
                ->count();

            return [
                'id' => $m->id,
                'nama_user_merchandiser' => $m->name,
                'nama_role_merchandiser' => optional($m->roles->first())->role_name ?? '-',
                'jumlah_toko' => $toko->count(),
                'toko_ids' => $toko,
                'jumlah_stock_count' => $jumlahStockCount,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }



    public function updateMerchandiserStores(Request $request, $idUser)
    {
        $request->validate([
            'toko'   => 'array',
            'toko.*' => 'integer',
        ]);

        // Ensure toko is always an array
        $stores = $request->input('toko', []);

        // If stores array is not empty, check for duplicate store assignments
        if (!empty($stores)) {
            $conflictingStores = DB::connection('pgsql')
                ->table('contact_group_customer')
                ->whereIn('id', $stores)
                ->where('id_user', '!=', $idUser)
                ->whereNotNull('id_user')
                ->get(['id', 'customer_name', 'id_user']);

            if ($conflictingStores->isNotEmpty()) {
                $conflictMessages = $conflictingStores->map(function ($store) {
                    $user = \App\Models\User::find($store->id_user);
                    return "Toko '{$store->customer_name}' sudah dimiliki oleh " . ($user ? $user->name : 'user lain');
                });

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terdapat toko yang sudah dimiliki merchandiser lain:',
                    'conflicts' => $conflictMessages->toArray()
                ], 422);
            }
        }

        // Clear existing assignments for this user
        DB::connection('pgsql')->table('contact_group_customer')
            ->where('id_user', $idUser)
            ->update([
                'id_user'    => null,
                'updated_at' => now(),
            ]);

        // Assign new stores if any
        foreach ($stores as $contactGroupId) {
            DB::connection('pgsql')->table('contact_group_customer')
                ->where('id', $contactGroupId)
                ->update([
                    'id_user'    => $idUser,
                    'updated_at' => now(),
                ]);
        }

        // Return appropriate message based on whether stores were assigned or unassigned
        $message = empty($stores)
            ? 'Merchandiser berhasil di-unassign dari semua toko.'
            : 'Data toko berhasil diperbarui.';

        return response()->json([
            'status'  => 'success',
            'message' => $message
        ]);
    }

    public function getMerchandiserStores($idUser)
    {
        // Get merchandiser info
        $merchandiser = \App\Models\User::find($idUser);
        if (!$merchandiser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Merchandiser not found'
            ], 404);
        }

        // Get stores assigned to this merchandiser with stock count data
        $stores = DB::connection('pgsql')
            ->table('contact_group_customer as cgc')
            ->leftJoin('stock_count_lists as scl', function ($join) use ($idUser) {
                $join->on('cgc.customer_no', '=', 'scl.customer_child_id')
                    ->where('scl.created_by', $idUser);
            })
            ->where('cgc.id_user', $idUser)
            ->select([
                'cgc.id',
                'cgc.customer_name as nama_store',
                'cgc.customer_no',
                DB::raw('COUNT(scl.id) as stock_count'),
                DB::raw('MAX(scl.created_at) as last_update')
            ])
            ->groupBy('cgc.id', 'cgc.customer_name', 'cgc.customer_no')
            ->orderBy('stock_count','desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'merchandiser' => [
                    'id' => $merchandiser->id,
                    'name' => $merchandiser->name
                ],
                'stores' => $stores
            ]
        ]);
    }

    public function productJson()
    {
        $data = DB::connection('pgsql')
            ->table('accurate_items as a')
            ->leftJoin('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
            ->select('a.accurate_id', 'a.item_no', 'a.name', 'a.unit1', 'a.item_type_name', 's.quantity as stock_quantity', 's.quantity_in_all_unit', 'a.is_active')
            ->orderBy('a.name', 'asc')
            ->orderBy('a.is_active', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function salesOrderJson(Request $request)
    {
        $query = DB::connection('pgsql')
            ->table('accurate_sales_order')
            ->select([
                'id',
                'number',
                'trans_date',
                'customer_name',
                'customer_no',
                'description',
                'status_name',
                'total_amount',
            ])
            ->where('status_name', 'Terproses')
            ->orderByDesc('trans_date')
            ->orderByDesc('id');
        // ->get();
        // ->paginate(100);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'ilike', "%$search%")
                    ->orWhere('customer_name', 'ilike', "%$search%")
                    ->orWhere('customer_no', 'ilike', "%$search%");
            });
        }

        if ($request->has('status')) {
            $query->where('status_name', $request->get('status'));
        }

        if ($request->has('customer_no')) {
            $query->where('customer_no', $request->get('customer_no'));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween(DB::raw("DATE(trans_date)"), [
                $request->date('date_from'),
                $request->date('date_to'),
            ]);
        }

        $perPage = $request->get('per_page', 10);
        $data = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function salesOrderDetailJson($id)
    {
        $details = DB::connection('pgsql')
            ->table('accurate_sales_order_details')
            ->where('sales_order_id', $id)
            ->select([
                'item_no',
                'item_name',
                'quantity',
                'unit_name',
                'unit_price',
                'total_price',
            ])
            ->orderBy('item_no')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $details,
        ]);
    }

    public function salesInvoiceJson()
    {
        $data = DB::connection('pgsql')
            ->table('accurate_sales_invoice_details as d')
            ->join('accurate_sales_invoice as i', 'd.invoice_id', '=', 'i.id')
            ->select(
                'i.number as invoice_number',
                'i.trans_date as trans_date',
                'i.customer_no',
                'd.item_no',
                'd.quantity',
                'd.unit_price',
                DB::raw('(d.quantity * d.unit_price) as total')
            )
            ->orderByDesc('i.trans_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }


    public function itemTransferJson()
    {
        // Get current authenticated user ID
        $userId = auth()->id();

        $data = DB::connection('pgsql')
            ->table('accurate_item_transfer as ait')
            ->leftJoin('contact_group_customer as cgc', 'ait.customer_code_sub', '=', 'cgc.customer_no')
            ->select([
                'ait.id',
                'ait.number',
                'ait.trans_date',
                'ait.warehouse_name as from_location',
                'cgc.customer_name as to_sub_customer_id',
                'ait.char_field3 as to_customer_id',
                'ait.approval_status',
            ])
            ->where('ait.tipe_proses', 'TRANSFER_OUT')
            ->where('cgc.id_user', $userId) // Filter by stores assigned to current user
            ->orderByDesc('ait.trans_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function itemTransferDetailJson($id)
    {
        // Get current authenticated user ID
        $userId = auth()->id();

        // First, verify that the transfer belongs to stores assigned to current user
        $transferExists = DB::connection('pgsql')
            ->table('accurate_item_transfer as ait')
            ->leftJoin('contact_group_customer as cgc', 'ait.customer_code_sub', '=', 'cgc.customer_no')
            ->where('ait.id', $id)
            ->where('cgc.id_user', $userId)
            ->exists();

        if (!$transferExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transfer not found or access denied',
            ], 403);
        }

        $details = DB::connection('pgsql')
            ->table('accurate_item_transfer_details')
            ->where('transfer_id', $id)
            ->select([
                'item_no',
                'item_name',
                'unit_name',
                'quantity',
                'status_name',
                'created_by',
            ])
            ->orderBy('item_no')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $details,
        ]);
    }

    public function salesReturnJson()
    {
        // Get current authenticated user ID
        $userId = auth()->id();

        $data = DB::connection('pgsql')
            ->table('accurate_item_transfer as ait')
            ->leftJoin('contact_group_customer as cgc', 'ait.customer_code_sub', '=', 'cgc.customer_no')
            ->select([
                'ait.id',
                'ait.number',
                'ait.trans_date',
                'ait.customer_code',
                'ait.char_field3 as to_customer_id',
                'ait.warehouse_name as from_location',
                'ait.tipe_proses',
            ])
            ->where('ait.tipe_proses', 'TRANSFER_IN')
            ->where('cgc.id_user', $userId) // Filter by stores assigned to current user
            ->orderByDesc('ait.trans_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function stockSystemCalculatedJson(Request $request)
    {
        $start   = $request->get('startDate');
        $end     = $request->get('endDate');
        $type    = $request->get('type', 'opname');
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 500);
        $offset  = ($page - 1) * $perPage;

        if (empty($start) || empty($end)) {
            return response()->json(['status' => 'error', 'message' => 'Tanggal tidak valid'], 400);
        }

        try {
            // 1. Ambil data opname dengan pagination
            $lastOpnames = DB::connection('pgsql')
                ->table('accurate_stock_opnames as s')
                ->leftJoin('accurate_stock_opname_details as d', 's.id', '=', 'd.opname_id')
                ->join('contact_groups as cg', 's.customer_code', '=', 'cg.customer_no')
                ->leftJoin('contact_group_customer as cgc', 's.customer_code_sub', '=', 'cgc.customer_no')
                ->whereBetween('s.trans_date', [$start, $end])
                ->where('s.type', $type)
                ->select(
                    's.customer_code as head_account_no',
                    'cg.customer_name as head_account',
                    DB::raw("COALESCE(cgc.customer_no, '-') as subaccount_no"),
                    'cgc.customer_name as subaccount',
                    'd.item_no',
                    'd.item_name',
                    DB::raw('MAX(s.trans_date) as last_date'),
                    DB::raw('SUM(d.qty_all) as stock_opname')
                )
                ->groupBy(
                    's.customer_code',
                    'cg.customer_name',
                    'cgc.customer_no',
                    'cgc.customer_name',
                    'd.item_no',
                    'd.item_name'
                )
                // ->offset($offset)
                // ->limit($perPage)
                ->get();

            if ($lastOpnames->isEmpty()) {
                return response()->json(['status' => 'success', 'data' => []]);
            }

            // Buat list key untuk filter data transaksi
            $keys = $lastOpnames->map(fn($r) => "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}")->toArray();

            // 2. Ambil avg sales 3 bulan terakhir hanya untuk item yang dipilih
            $start3Months = Carbon::parse($end)->firstOfMonth()->subMonths(2)->format('Y-m-d');

            $avgSalesRows = DB::connection('pgsql')
                ->table('accurate_sales_order as o')
                ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                ->where('o.status_name', 'Terproses')
                ->whereBetween(DB::raw('DATE(o.trans_date)'), [$start3Months, $end])
                ->select(
                    'o.customer_no as head_account_no',
                    DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                    'd.item_no',
                    DB::raw('SUM(d.quantity) as qty_order'),
                    DB::raw(" (DATE '$end' - DATE '$start3Months') + 1 as total_days "),
                    DB::raw("
            ROUND(
                SUM(d.quantity) / NULLIF(
                    (DATE '$end' - DATE '$start3Months') + 1,
                    0
                ),
            1) as avg_daily_sales
        ")
                )
                ->groupBy('o.customer_no', 'o.customer_no_sub', 'd.item_no')
                ->get();

            $avgSalesMap = [];
            foreach ($avgSalesRows as $r) {
                $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                $avgSalesMap[$key] = [
                    'qty_order' => (float) $r->qty_order,
                    'avg_daily_sales' => (float) $r->avg_daily_sales,
                    'total_days'      => (int) $r->total_days,
                ];
            }

            // Helper untuk buat map transaksi
            $makeMap = function ($rows) {
                $map = [];
                foreach ($rows as $r) {
                    $dateField = $r->trans_date ?? $r->last_date ?? null;
                    $qtyField = $r->quantity ?? null;
                    $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                    if (!$dateField) continue;
                    if (!$qtyField) continue;
                    $map[$key][] = [
                        'date' => (string) $dateField,
                        'qty'  => (float) $qtyField
                    ];
                }
                return $map;
            };

            // 3. Ambil semua transaksi di periode filter
            $salesRows = DB::connection('pgsql')
                ->table('accurate_sales_order as o')
                ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                ->where('o.status_name', 'Terproses')
                ->whereBetween(DB::raw('DATE(o.trans_date)'), [$start, $end])
                ->select(
                    'o.customer_no as head_account_no',
                    DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('DATE(o.trans_date) as trans_date'),
                    'd.quantity'
                )
                ->get();

            $transferInRows = DB::connection('pgsql')
                ->table('accurate_item_transfer as t')
                ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                ->where('t.tipe_proses', 'TRANSFER_OUT')
                ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
                ->select(
                    't.customer_code as head_account_no',
                    DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('DATE(t.trans_date) as trans_date'),
                    'd.quantity'
                )
                ->get();

            $transferReturnRows = DB::connection('pgsql')
                ->table('accurate_stock_opnames as s')
                ->leftJoin('accurate_stock_opname_details as d', 's.id', '=', 'd.opname_id')
                ->leftJoin('contact_group_customer as cgc', 's.customer_code_sub', '=', 'cgc.customer_no')
                ->whereBetween('s.trans_date', [$start, $end])
                ->where('s.type', 'return')
                ->select(
                    's.customer_code as head_account_no',
                    DB::raw("COALESCE(cgc.customer_no, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('MAX(s.trans_date) as last_date'),
                    DB::raw('SUM(d.qty_all) as stock_opname')
                )
                ->groupBy(
                    's.customer_code',
                    'cgc.customer_no',
                    'd.item_no',
                    'd.item_name'
                )
                ->get();

            // $transferReturnRows = DB::connection('pgsql')
            //     ->table('accurate_item_transfer as t')
            //     ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
            //     ->where('t.tipe_proses', 'TRANSFER_IN')
            //     ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
            //     ->select(
            //         't.customer_code as head_account_no',
            //         DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
            //         'd.item_no',
            //         'd.item_name',
            //         DB::raw('DATE(t.trans_date) as trans_date'),
            //         'd.quantity'
            //     )
            //     ->get();

            $salesMap = $makeMap($salesRows);
            $transferInMap = $makeMap($transferInRows);
            $transferReturnMap = $makeMap($transferReturnRows);

            // 4. Hitung
            $combined = collect();
            foreach ($lastOpnames as $row) {
                $key = "{$row->head_account_no}|{$row->subaccount_no}|{$row->item_no}";
                $lastDate = (string) $row->last_date;

                $sumSales = isset($salesMap[$key])
                    ? array_sum(array_column(array_filter($salesMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty'))
                    : 0;

                $sumIn = isset($transferInMap[$key])
                    ? array_sum(array_column(array_filter($transferInMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty'))
                    : 0;

                $sumReturn = isset($transferReturnMap[$key])
                    ? array_sum(array_column(array_filter($transferReturnMap[$key], fn($r) => $r['date'] >= $lastDate), 'qty'))
                    : 0;

                // $avgDailySales = $avgSalesMap[$key] ?? 0;
                $avgDailySales = $avgSalesMap[$key]['avg_daily_sales'] ?? 0;
                $qtyOrder = $avgSalesMap[$key]['qty_order'] ?? 0;
                $avgMonthSales = $qtyOrder > 0 ? round($qtyOrder / 3, 1) : 0;
                $stockSystem = ($row->stock_opname + $sumIn) - ($sumReturn + $sumSales);
                $daysOnHand = $avgDailySales > 0 ? round($stockSystem / $avgDailySales, 1) : 0;
                // $runoutDate = $avgDailySales > 0 ? Carbon::now()->addDays($daysOnHand)->format('Y-m-d') : null;
                $runoutDate = $avgDailySales > 0 ? Carbon::parse($end)->addDays($daysOnHand)->format('Y-m-d') : null;

                $combined[$key] = (object)[
                    'head_account_no' => $row->head_account_no,
                    'head_account'    => $row->head_account,
                    'subaccount_no'   => $row->subaccount_no,
                    'subaccount'      => $row->subaccount,
                    'item_no'         => $row->item_no,
                    'item_name'       => $row->item_name,
                    'opname_date'     => $lastDate,
                    'stock_opname'    => (int) $row->stock_opname,
                    'stock_in'        => (int) $sumIn,
                    'stock_out'       => (int) $sumSales,
                    'stock_return'    => (int) $sumReturn,
                    'stock_system'    => $stockSystem,
                    'avg_daily_sales' => $avgDailySales,
                    'avg_month_sales' => $avgMonthSales,
                    'days_on_hand'    => $daysOnHand,
                    'runout_date'     => $runoutDate,
                    'qty_order'       => $qtyOrder,
                    'total_days'      => $avgSalesMap[$key]['total_days'] ?? 0,
                ];
            }

            // 5. Insert ke tmp table dengan chunk (cepat & aman)
            DB::connection('pgsql')->table('accurate_stock_calculated_details_tmp')->truncate();
            $insertData = [];
            foreach ($combined as $r) {
                $insertData[] = [
                    'head_account_no' => $r->head_account_no,
                    'head_account'    => $r->head_account,
                    'subaccount_no'   => $r->subaccount_no,
                    'subaccount'      => $r->subaccount,
                    'item_no'         => $r->item_no,
                    'item_name'       => $r->item_name,
                    'stock_opname'    => $r->stock_opname,
                    'stock_in'        => $r->stock_in,
                    'stock_out'       => $r->stock_out,
                    'stock_return'    => $r->stock_return,
                    'stock_system'    => $r->stock_system,
                    'avg_daily_sales' => $r->avg_daily_sales,
                    'avg_month_sales' => $r->avg_month_sales,
                    'days_on_hand'    => $r->days_on_hand,
                    'runout_date'     => $r->runout_date,
                    'opname_date'     => $r->opname_date,
                    'total_days'      => $r->total_days ?? 0,
                    'qty_order'       => $r->qty_order ?? 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            foreach (array_chunk($insertData, 1000) as $chunk) {
                DB::connection('pgsql')->table('accurate_stock_calculated_details_tmp')->insert($chunk);
            }

            // 6. Group hasil untuk response JSON
            $grouped = [];
            foreach ($combined as $row) {
                $custKey = $row->head_account_no;
                $subKey  = $row->subaccount_no;

                if (!isset($grouped[$custKey])) {
                    $grouped[$custKey] = [
                        'customer_id'   => $row->head_account_no,
                        'customer_name' => $row->head_account,
                        'subs'          => [],
                    ];
                }

                if (!isset($grouped[$custKey]['subs'][$subKey])) {
                    $grouped[$custKey]['subs'][$subKey] = [
                        'customer_no_sub'   => $row->subaccount_no,
                        'customer_name_sub' => $row->subaccount,
                        'stock_opname'      => 0,
                        'total_stock_in'    => 0,
                        'total_stock_out'   => 0,
                        'total_stock_return' => 0,
                        'adjusted_stock'    => 0,
                        'details'           => [],
                    ];
                }

                $grouped[$custKey]['subs'][$subKey]['details'][] = [
                    'item_no'          => $row->item_no,
                    'product_name'     => $row->item_name,
                    'stock_opname'     => $row->stock_opname,
                    'stock_in'         => $row->stock_in,
                    'stock_out'        => $row->stock_out,
                    'stock_return'     => $row->stock_return,
                    'selisih_opname'   => $row->stock_system,
                    'avg_daily_sales'  => $row->avg_daily_sales,
                    'avg_month_sales'  => $row->avg_month_sales,
                    'days_on_hand'     => $row->days_on_hand,
                    'runout_date'      => $row->runout_date,
                    'opname_date'      => $row->opname_date,
                    // 'last_date'        => $row->last_date,
                    'qty_order'        => $row->qty_order,
                    'total_days'       => $row->total_days,
                ];

                $grouped[$custKey]['subs'][$subKey]['stock_opname'] += $row->stock_opname;
                $grouped[$custKey]['subs'][$subKey]['total_stock_in'] += $row->stock_in;
                $grouped[$custKey]['subs'][$subKey]['total_stock_out'] += $row->stock_out;
                $grouped[$custKey]['subs'][$subKey]['total_stock_return'] += $row->stock_return;
                $grouped[$custKey]['subs'][$subKey]['adjusted_stock'] += $row->stock_system;
            }

            $data = array_map(function ($cust) {
                $cust['subs'] = array_values($cust['subs']);
                return $cust;
            }, array_values($grouped));

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function stockComparisonExport()
    {
        $start = request('startDate');
        $end   = request('endDate');
        $headAccount   = request('headAccount');
        $subAccounts   = request('subAccounts') ?? [];

        if (empty($start) || empty($end)) {
            return response()->json(['status' => 'error', 'message' => 'Tanggal wajib diisi'], 422);
        }

        if (empty($headAccount)) {
            return response()->json(['status' => 'error', 'message' => 'Head Account wajib diisi'], 422);
        }

        if (empty($subAccounts) || !is_array($subAccounts)) {
            return response()->json(['status' => 'error', 'message' => 'Sub Accounts wajib dipilih'], 422);
        }

        if (empty($start) || empty($end)) {
            return response()->json(['status' => 'error', 'message' => 'Tanggal wajib diisi'], 422);
        }

        try {
            $lastOpnames = DB::connection('pgsql')
                ->table('accurate_stock_opnames as s')
                ->leftJoin('accurate_stock_opname_details as d', 's.id', '=', 'd.opname_id')
                ->join('contact_groups as cg', 's.customer_code', '=', 'cg.customer_no')
                ->leftJoin('contact_group_customer as cgc', 's.customer_code_sub', '=', 'cgc.customer_no')
                ->whereBetween('s.trans_date', [$start, $end])
                ->where('s.customer_code', $headAccount)
                ->whereIn('s.customer_code_sub', $subAccounts)
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
                )
                ->get();

            // Jika tidak ada opname, kembalikan kosong
            if ($lastOpnames->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada data opname untuk periode yang dipilih'], 404);
            }

            // --- 2) Ambil semua sales order (detail rows) di periode start..end
            //     (kita ambil per-barisan, nanti di-PHP kita sum hanya yang tanggal >= last_date)
            $salesRows = DB::connection('pgsql')
                ->table('accurate_sales_order as o')
                ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                ->where('o.status_name', 'Terproses')
                ->whereBetween(DB::raw('DATE(o.trans_date)'), [$start, $end])
                ->select(
                    'o.customer_no as head_account_no',
                    DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('DATE(o.trans_date) as trans_date'),
                    'd.quantity'
                )
                ->get();

            // --- 3) Ambil semua transfer IN (TRANSFER_OUT) rows di periode
            $transferInRows = DB::connection('pgsql')
                ->table('accurate_item_transfer as t')
                ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                ->where('t.tipe_proses', 'TRANSFER_OUT')
                ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
                ->select(
                    't.customer_code as head_account_no',
                    DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('DATE(t.trans_date) as trans_date'),
                    'd.quantity'
                )
                ->get();

            // --- 4) Ambil semua transfer RETURN (TRANSFER_IN) rows di periode
            $transferReturnRows = DB::connection('pgsql')
                ->table('accurate_item_transfer as t')
                ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                ->where('t.tipe_proses', 'TRANSFER_IN')
                ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
                ->select(
                    't.customer_code as head_account_no',
                    DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                    'd.item_no',
                    'd.item_name',
                    DB::raw('DATE(t.trans_date) as trans_date'),
                    'd.quantity'
                )
                ->get();

            $stockCount = DB::connection('pgsql')
                ->table('stock_count as t')
                ->join('accurate_items as d', 't.product_code', '=', 'd.item_no')
                ->whereBetween('t.date', [$start, $end])
                ->where('t.customer_id', $headAccount)
                ->whereIn('t.customer_child_id', $subAccounts)
                ->select(
                    't.customer_id as head_account_no',
                    DB::raw("COALESCE(t.customer_child_id, '-') as subaccount_no"),
                    't.product_code as item_no',
                    'd.name as item_name',
                    DB::raw('DATE(t.date) as trans_date'),
                    't.actual_stock as quantity'
                )
                ->get();

            // --- 5) Buat index (map) untuk cepat lookup: key = head|sub|item -> array of rows
            $makeMap = function ($rows) {
                $map = [];
                foreach ($rows as $r) {
                    $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                    // convert date to string yyyy-mm-dd for easy compare (they already are DATE)
                    $date = (string) $r->trans_date;
                    $map[$key][] = [
                        'date' => $date,
                        'qty'  => floatval($r->quantity ?? 0),
                    ];
                }
                return $map;
            };

            $salesMap = $makeMap($salesRows);
            $transferInMap = $makeMap($transferInRows);
            $transferReturnMap = $makeMap($transferReturnRows);
            $stockCountMap = $makeMap($stockCount);

            // --- 6) Untuk setiap lastOpname, sum hanya transaksi >= last_date
            $combined = collect();
            foreach ($lastOpnames as $row) {
                $key = "{$row->head_account_no}|{$row->subaccount_no}|{$row->item_no}";
                $lastDate = (string) $row->last_date; // format 'YYYY-MM-DD'

                // sum sales where trans_date >= lastDate
                $sumSales = 0;
                if (!empty($salesMap[$key])) {
                    foreach ($salesMap[$key] as $r) {
                        if ($r['date'] >= $lastDate) $sumSales += $r['qty'];
                    }
                }

                // sum transferIn where trans_date >= lastDate
                $sumIn = 0;
                if (!empty($transferInMap[$key])) {
                    foreach ($transferInMap[$key] as $r) {
                        if ($r['date'] >= $lastDate) $sumIn += $r['qty'];
                    }
                }

                // sum transferReturn where trans_date >= lastDate
                $sumReturn = 0;
                if (!empty($transferReturnMap[$key])) {
                    foreach ($transferReturnMap[$key] as $r) {
                        if ($r['date'] >= $lastDate) $sumReturn += $r['qty'];
                    }
                }

                $sumCount = 0;
                if (!empty($stockCountMap[$key])) {
                    foreach ($stockCountMap[$key] as $r) {
                        if ($r['date'] >= $lastDate) $sumCount += $r['qty'];
                    }
                }

                $combined[$key] = (object)[
                    'head_account_no' => $row->head_account_no,
                    'head_account'    => $row->head_account,
                    'subaccount_no'   => $row->subaccount_no,
                    'subaccount'      => $row->subaccount,
                    'item_no'         => $row->item_no,
                    'item_name'       => $row->item_name,
                    'last_date'       => $lastDate,
                    'stock_opname'    => intval($row->stock_opname ?? 0),
                    'stock_in'        => intval($sumIn),
                    'stock_out'       => intval($sumSales),
                    'stock_return'    => intval($sumReturn),
                    'stock_count'     => intval($sumCount),
                ];

                // hitung stock_system
                $combined[$key]->stock_system = (($combined[$key]->stock_opname + $combined[$key]->stock_in + $combined[$key]->stock_return) - $combined[$key]->stock_out) - $combined[$key]->stock_count;
            }

            // --- 7) Simpan ke tmp table (truncate + batch insert)
            DB::connection('pgsql')->table('accurate_stock_comparison_details_tmp')->truncate();

            $insertData = [];
            foreach ($combined as $r) {
                $insertData[] = [
                    'head_account_no' => $r->head_account_no,
                    'head_account'    => $r->head_account,
                    'subaccount_no'   => $r->subaccount_no,
                    'subaccount'      => $r->subaccount,
                    'item_no'         => $r->item_no,
                    'item_name'       => $r->item_name,
                    'stock_opname'    => $r->stock_opname,
                    'stock_in'        => $r->stock_in,
                    'stock_out'       => $r->stock_out,
                    'stock_return'    => $r->stock_return,
                    'stock_count'     => $r->stock_count,
                    'stock_system'    => $r->stock_system,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 1000) as $chunk) {
                    DB::connection('pgsql')->table('accurate_stock_comparison_details_tmp')->insert($chunk);
                }
            }

            // --- Setelah itu, ambil hasilnya dari tmp table
            $results = DB::connection('pgsql')
                ->table('accurate_stock_comparison_details_tmp')
                ->where('head_account_no', $headAccount)
                ->whereIn('subaccount_no', $subAccounts)
                ->orderBy('head_account_no')
                ->orderBy('subaccount_no')
                ->orderBy('item_no')
                ->get();

            if ($results->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada data untuk diexport']);
            }

            // --- Export ke Excel
            return Excel::download(
                new StockComparisonExport($results),
                "stock_comparison_{$start}_{$end}.xlsx"
            );
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function processStockComparison($start, $end)
    {
        $start = request('startDate');
        $end   = request('endDate');
        $headAccount   = request('headAccount');
        $subAccounts   = request('subAccounts');

        if (!empty($start)) {
            try {
                // --- 1) Ambil stok opname (last_date per head/sub/item) dalam periode
                $lastOpnames = DB::connection('pgsql')
                    ->table('accurate_stock_opnames as s')
                    ->leftJoin('accurate_stock_opname_details as d', 's.id', '=', 'd.opname_id')
                    ->join('contact_groups as cg', 's.customer_code', '=', 'cg.customer_no')
                    ->leftJoin('contact_group_customer as cgc', 's.customer_code_sub', '=', 'cgc.customer_no')
                    ->whereBetween('s.trans_date', [$start, $end])
                    ->where('s.customer_code', $headAccount)
                    ->whereIn('subaccount_no', $subAccounts)
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
                    )
                    ->get();

                dd($lastOpnames);
                die;

                // Jika tidak ada opname, kembalikan kosong
                if ($lastOpnames->isEmpty()) {
                    return response()->json(['status' => 'success', 'data' => []]);
                }

                // --- 2) Ambil semua sales order (detail rows) di periode start..end
                //     (kita ambil per-barisan, nanti di-PHP kita sum hanya yang tanggal >= last_date)
                $salesRows = DB::connection('pgsql')
                    ->table('accurate_sales_order as o')
                    ->join('accurate_sales_order_details as d', 'o.id', '=', 'd.sales_order_id')
                    ->where('o.status_name', 'Terproses')
                    ->whereBetween(DB::raw('DATE(o.trans_date)'), [$start, $end])
                    ->select(
                        'o.customer_no as head_account_no',
                        DB::raw("COALESCE(o.customer_no_sub, '-') as subaccount_no"),
                        'd.item_no',
                        'd.item_name',
                        DB::raw('DATE(o.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                // --- 3) Ambil semua transfer IN (TRANSFER_OUT) rows di periode
                $transferInRows = DB::connection('pgsql')
                    ->table('accurate_item_transfer as t')
                    ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                    ->where('t.tipe_proses', 'TRANSFER_OUT')
                    ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
                    ->select(
                        't.customer_code as head_account_no',
                        DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                        'd.item_no',
                        'd.item_name',
                        DB::raw('DATE(t.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                // --- 4) Ambil semua transfer RETURN (TRANSFER_IN) rows di periode
                $transferReturnRows = DB::connection('pgsql')
                    ->table('accurate_item_transfer as t')
                    ->join('accurate_item_transfer_details as d', 't.id', '=', 'd.transfer_id')
                    ->where('t.tipe_proses', 'TRANSFER_IN')
                    ->whereBetween(DB::raw('DATE(t.trans_date)'), [$start, $end])
                    ->select(
                        't.customer_code as head_account_no',
                        DB::raw("COALESCE(t.customer_code_sub, '-') as subaccount_no"),
                        'd.item_no',
                        'd.item_name',
                        DB::raw('DATE(t.trans_date) as trans_date'),
                        'd.quantity'
                    )
                    ->get();

                // --- 5) Buat index (map) untuk cepat lookup: key = head|sub|item -> array of rows
                $makeMap = function ($rows) {
                    $map = [];
                    foreach ($rows as $r) {
                        $key = "{$r->head_account_no}|{$r->subaccount_no}|{$r->item_no}";
                        // convert date to string yyyy-mm-dd for easy compare (they already are DATE)
                        $date = (string) $r->trans_date;
                        $map[$key][] = [
                            'date' => $date,
                            'qty'  => floatval($r->quantity ?? 0),
                        ];
                    }
                    return $map;
                };

                $salesMap = $makeMap($salesRows);
                $transferInMap = $makeMap($transferInRows);
                $transferReturnMap = $makeMap($transferReturnRows);

                // --- 6) Untuk setiap lastOpname, sum hanya transaksi >= last_date
                $combined = collect();
                foreach ($lastOpnames as $row) {
                    $key = "{$row->head_account_no}|{$row->subaccount_no}|{$row->item_no}";
                    $lastDate = (string) $row->last_date; // format 'YYYY-MM-DD'

                    // sum sales where trans_date >= lastDate
                    $sumSales = 0;
                    if (!empty($salesMap[$key])) {
                        foreach ($salesMap[$key] as $r) {
                            if ($r['date'] >= $lastDate) $sumSales += $r['qty'];
                        }
                    }

                    // sum transferIn where trans_date >= lastDate
                    $sumIn = 0;
                    if (!empty($transferInMap[$key])) {
                        foreach ($transferInMap[$key] as $r) {
                            if ($r['date'] >= $lastDate) $sumIn += $r['qty'];
                        }
                    }

                    // sum transferReturn where trans_date >= lastDate
                    $sumReturn = 0;
                    if (!empty($transferReturnMap[$key])) {
                        foreach ($transferReturnMap[$key] as $r) {
                            if ($r['date'] >= $lastDate) $sumReturn += $r['qty'];
                        }
                    }

                    $combined[$key] = (object)[
                        'head_account_no' => $row->head_account_no,
                        'head_account'    => $row->head_account,
                        'subaccount_no'   => $row->subaccount_no,
                        'subaccount'      => $row->subaccount,
                        'item_no'         => $row->item_no,
                        'item_name'       => $row->item_name,
                        'last_date'       => $lastDate,
                        'stock_opname'    => intval($row->stock_opname ?? 0),
                        'stock_in'        => intval($sumIn),
                        'stock_out'       => intval($sumSales),
                        'stock_return'    => intval($sumReturn),
                    ];

                    // hitung stock_system
                    $combined[$key]->stock_system = ($combined[$key]->stock_opname + $combined[$key]->stock_in + $combined[$key]->stock_return) - $combined[$key]->stock_out;
                }

                // --- 7) Simpan ke tmp table (truncate + batch insert)
                DB::connection('pgsql')->table('accurate_stock_comparison_details_tmp')->truncate();

                $insertData = [];
                foreach ($combined as $r) {
                    $insertData[] = [
                        'head_account_no' => $r->head_account_no,
                        'head_account'    => $r->head_account,
                        'subaccount_no'   => $r->subaccount_no,
                        'subaccount'      => $r->subaccount,
                        'item_no'         => $r->item_no,
                        'item_name'       => $r->item_name,
                        'stock_opname'    => $r->stock_opname,
                        'stock_in'        => $r->stock_in,
                        'stock_out'       => $r->stock_out,
                        'stock_return'    => $r->stock_return,
                        'stock_system'    => $r->stock_system,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                if (!empty($insertData)) {
                    foreach (array_chunk($insertData, 1000) as $chunk) {
                        DB::connection('pgsql')->table('accurate_stock_calculated_details_tmp')->insert($chunk);
                    }
                }

                // --- 8) Group untuk response JSON: head -> subs -> details
                $grouped = [];

                foreach ($combined as $row) {
                    $custKey = $row->head_account_no;
                    $subKey  = $row->subaccount_no;

                    if (!isset($grouped[$custKey])) {
                        $grouped[$custKey] = [
                            'customer_id'   => $row->head_account_no,
                            'customer_name' => $row->head_account,
                            'subs'          => [],
                        ];
                    }

                    if (!isset($grouped[$custKey]['subs'][$subKey])) {
                        $grouped[$custKey]['subs'][$subKey] = [
                            'customer_no_sub'   => $row->subaccount_no,
                            'customer_name_sub' => $row->subaccount,
                            'stock_opname'      => 0,
                            'total_stock_in'    => 0,
                            'total_stock_out'   => 0,
                            'total_stock_return' => 0,
                            'adjusted_stock'    => 0,
                            'details'           => [],
                        ];
                    }

                    $grouped[$custKey]['subs'][$subKey]['details'][] = [
                        'item_no'        => $row->item_no,
                        'product_name'   => $row->item_name,
                        'stock_opname'   => $row->stock_opname,
                        'stock_in'       => $row->stock_in,
                        'stock_out'      => $row->stock_out,
                        'stock_return'   => $row->stock_return,
                        'selisih_opname' => $row->stock_system,
                        'last_date'      => $row->last_date,
                    ];

                    $grouped[$custKey]['subs'][$subKey]['stock_opname']   += $row->stock_opname;
                    $grouped[$custKey]['subs'][$subKey]['total_stock_in'] += $row->stock_in;
                    $grouped[$custKey]['subs'][$subKey]['total_stock_out'] += $row->stock_out;
                    $grouped[$custKey]['subs'][$subKey]['total_stock_return'] += $row->stock_return;
                    $grouped[$custKey]['subs'][$subKey]['adjusted_stock'] += $row->stock_system;
                }

                $data = array_map(function ($cust) {
                    $cust['subs'] = array_values($cust['subs']);
                    return $cust;
                }, array_values($grouped));

                return response()->json([
                    'status' => 'success',
                    'data'   => $data,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
    }


    public function contactGroupJson(Request $request)
    {
        $perPage = $request->input('perpage', 10);
        $search = $request->input('search');

        $query = DB::connection('pgsql')
            ->table('contact_groups');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function contactGroupCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'customer_no' => 'required|string',
        ]);



        $groupId = DB::connection('pgsql')->table('contact_groups')->insertGetId([
            'name' => $request->name,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
            'customer_no' => $request->customer_no,
            'customer_name' => $request->name,
            'customer_email' => $request->email,
            'customer_telp' => $request->work_phone,
            'npwp' => $request->npwp,
        ]);

        // DB::connection('pgsql')->table('contact_group_customer')->insert([
        //     'contact_group_id' => $groupId,
        //     'customer_no' => $request->customer_no,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact Group berhasil ditambahkan.'
        ]);
    }

    public function importGroupCustomers(Request $request, $groupId)
    {
        try {
            $rows = $request->input('rows', []); // array dari data Excel
            $results = [];
            $duplicates = [];

            if (empty($rows)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data yang dikirim.',
                ], 400);
            }

            $headAccount = DB::connection('pgsql')->table('contact_groups')
                ->where('id', $groupId)
                ->first();

            if (!$headAccount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contact group tidak ditemukan.',
                ], 404);
            }

            $baseNo = $headAccount->customer_no;

            // Ambil semua nama customer dari seluruh tabel beserta group_id-nya
            $allExistingCustomers = DB::connection('pgsql')
                ->table('contact_group_customer')
                ->select('customer_name', 'contact_group_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => strtolower(trim($item->customer_name)),
                        'group_id' => $item->contact_group_id,
                    ];
                });

            // Ambil semua suffix yang sudah ada sebelumnya
            $existingNumbers = DB::connection('pgsql')->table('contact_group_customer')
                ->where('contact_group_id', $groupId)
                ->where('customer_no', 'like', "$baseNo.%")
                ->pluck('customer_no')
                ->toArray();

            $lastSuffix = 'A';
            if (!empty($existingNumbers)) {
                $suffixes = array_map(function ($no) use ($baseNo) {
                    return strtoupper(str_replace("{$baseNo}.", '', $no));
                }, $existingNumbers);

                usort($suffixes, function ($a, $b) {
                    return strlen($b) <=> strlen($a) ?: strcmp($b, $a);
                });

                $lastSuffix = $suffixes[0];
            }

            foreach ($rows as $row) {
                try {
                    $name = trim($row['name'] ?? $row['customer_name'] ?? '');
                    if ($name === '') continue;

                    $lowerName = strtolower($name);

                    $duplicate = $allExistingCustomers->first(
                        fn($cust) =>
                        $cust['name'] === $lowerName && $cust['group_id'] != $groupId
                    );

                    if ($duplicate) {
                        $duplicates[] = [
                            'name' => $name,
                            'existing_group_id' => $duplicate['group_id'],
                        ];
                        continue;
                    }

                    $existsInSameGroup = $allExistingCustomers->first(
                        fn($cust) =>
                        $cust['name'] === $lowerName && $cust['group_id'] == $groupId
                    );
                    if ($existsInSameGroup) continue;

                    // Hitung suffix baru berdasarkan yang terakhir
                    $suffix = $this->nextSuffix($lastSuffix);
                    $lastSuffix = $suffix;
                    $newNo = "{$baseNo}.{$suffix}";

                    DB::connection('pgsql')->table('contact_group_customer')->insert([
                        'contact_group_id' => $groupId,
                        'customer_no' => $newNo,
                        'customer_name' => $name,
                        // 'customer_email' => $row['email'] ?? null,
                        // 'customer_telp' => $row['telp'] ?? null,
                        'cut_off' => $row['cut_off'] ?? null,
                        'prov' => $row['prov'] ?? $row['provinsi'] ?? null,
                        'kab_kota' => $row['kab_kota'] ?? $row['kabupaten'] ?? null,
                        // 'kec' => $row['kec'] ?? $row['kecamatan'] ?? null,
                        'alamat' => $row['alamat'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $results[] = [
                        'customer_no' => $newNo,
                        'name' => $name,
                    ];

                    $allExistingCustomers->push([
                        'name' => $lowerName,
                        'group_id' => $groupId,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Gagal import customer: {$e->getMessage()}", ['row' => $row]);
                    continue;
                }
            }

            $after = DB::connection('pgsql')->table('contact_group_customer')
                ->where('contact_group_id', $groupId)
                ->get();

            DB::connection('pgsql')->update("
            UPDATE accurate_sales_order aso
            SET customer_no_sub = cgc.customer_no
            FROM contact_group_customer cgc
            WHERE aso.char_field1 = cgc.customer_name
              AND aso.customer_no_sub IS NULL
        ");

            DB::connection('pgsql')->update("
            UPDATE accurate_item_transfer aso
            SET customer_code_sub = cgc.customer_no
            FROM contact_group_customer cgc
            WHERE aso.char_field1 = cgc.customer_name OR aso.char_field6 = cgc.customer_name
              AND aso.customer_code_sub IS NULL
        ");

            DB::connection('pgsql')->table('accurate_sales_order_summary')->truncate();
            DB::connection('pgsql')->insert("
            INSERT INTO accurate_sales_order_summary (customer_no, customer_name, total_qty, created_at, updated_at)
            SELECT
                o.customer_no,
                o.customer_name,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_sales_order_details d
            JOIN
                accurate_sales_order o ON o.id = d.sales_order_id
            GROUP BY
                o.customer_no, o.customer_name
        ");

            DB::connection('pgsql')->table('accurate_item_transfer_summary')->truncate();
            DB::connection('pgsql')->insert("
            INSERT INTO accurate_item_transfer_summary (
                customer_no,
                customer_name,
                tipe_proses,
                total_qty,
                created_at,
                updated_at
            )
            SELECT
                o.customer_code,
                o.char_field3 AS customer_name,
                d.tipe_proses,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_item_transfer_details d
            JOIN
                accurate_item_transfer o ON o.id = d.transfer_id
            WHERE
                d.tipe_proses = 'TRANSFER_OUT'
                AND LEFT(o.number, 2) != 'IT'
            GROUP BY
                o.customer_code,
                o.char_field3,
                d.tipe_proses
        ");

            return response()->json([
                'status' => 'success',
                'data' => $after,
                'imported' => $results,
                'duplicates' => $duplicates,
                'message' => count($results) > 0
                    ? 'Beberapa data berhasil diimpor.'
                    : 'Tidak ada data yang diimpor.',
            ]);
        } catch (\Throwable $e) {
            Log::error("Gagal importGroupCustomers: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function nextSuffix(string $current): string
    {
        $current = strtoupper($current);
        $length = strlen($current);
        $carry = true;
        $result = '';

        for ($i = $length - 1; $i >= 0; $i--) {
            $char = $current[$i];

            if ($carry) {
                if ($char === 'Z') {
                    $result = 'A' . $result;
                    $carry = true;
                } else {
                    $result = chr(ord($char) + 1) . $result;
                    $carry = false;
                }
            } else {
                $result = $char . $result;
            }
        }

        if ($carry) {
            $result = 'A' . $result;
        }

        return $result;
    }




    public function getStatistics(Request $request)
    {
        try {
            $createdBy = $request->input('created_by');

            // Query produk
            $productCount = DB::connection('pgsql')
                ->table('accurate_items')
                // ->when($createdBy, fn($q) => $q->where('created_by', $createdBy))
                ->where('is_active', 1)
                ->count();

            // Query sales order
            $salesOrderCount = DB::connection('pgsql')
                ->table('sales_orders')
                ->when($createdBy, fn($q) => $q->where('created_by', $createdBy))
                ->count();

            // Query stock count sekali saja
            $stockCountsData = DB::connection('pgsql')
                ->table('stock_count')
                ->when($createdBy, fn($q) => $q->where('created_by', $createdBy))
                ->selectRaw("
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 ELSE 0 END) AS today_count
                ")
                ->first();

            $stockCounts = $stockCountsData->total_count ?? 0;
            $stockCountToday = $stockCountsData->today_count ?? 0;

            // Query customer
            $customerCount = DB::connection('pgsql')
                ->table('contact_group_customer')
                // ->when($createdBy, fn($q) => $q->where('created_by', $createdBy))
                ->count();

            // Ambil last visit sekali saja (cuma ambil 1 row)
            $lastVisit = DB::connection('pgsql')
                ->table('stock_count')
                ->when($createdBy, fn($q) => $q->where('created_by', $createdBy))
                ->orderByDesc('created_at')
                ->value('created_at');

            $lastVisitDate = $lastVisit
                ? Carbon::parse($lastVisit)->translatedFormat('l, d F Y')
                : 'Tidak ada data';

            $statistics = [
                ['title' => 'Data Master Produk', 'count' => $productCount, 'icon' => 'package', 'color' => 'green', 'page' => 'data'],
                ['title' => 'Data Customer', 'count' => $customerCount, 'icon' => 'users', 'color' => 'purple', 'page' => 'data'],
                ['title' => 'Data Stock Count', 'count' => $stockCounts, 'icon' => 'shopping-cart', 'color' => 'orange', 'page' => 'data'],
                ['title' => 'Data Request Sales Order', 'count' => $salesOrderCount, 'icon' => 'shopping-cart', 'color' => 'red', 'page' => 'data'],
                ['title' => 'Toko di kunjungi hari ini', 'count' => $stockCountToday, 'icon' => 'cube', 'color' => 'orange', 'page' => 'home'],
                ['title' => 'Tanggal Kunjungan terakhir', 'count' => $lastVisitDate, 'icon' => 'chart', 'color' => 'blue', 'isDate' => true, 'page' => 'home']
            ];

            return response()->json(['status' => 'success', 'data' => $statistics]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to get statistics: ' . $e->getMessage()], 500);
        }
    }


    public function getActivities(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $activityType = $request->input('activity_type');

            $query = DB::connection('pgsql')
                ->table('activities')
                ->orderBy('created_at', 'desc');

            // Filter by search term
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                        ->orWhere('description', 'ILIKE', "%{$search}%")
                        ->orWhere('user_name', 'ILIKE', "%{$search}%");
                });
            }

            // Filter by activity type
            if ($activityType) {
                $query->where('activity_type', $activityType);
            }

            $activities = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get activities: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createActivity(Request $request)
    {
        try {
            $request->validate([
                'activity_type' => 'required|string',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'user_name' => 'nullable|string',
                'user_id' => 'nullable|string',
                'reference_id' => 'nullable|string',
                'reference_type' => 'nullable|string',
                'metadata' => 'nullable|array'
            ]);

            $activityId = DB::connection('pgsql')->table('activities')->insertGetId([
                'activity_type' => $request->activity_type,
                'title' => $request->title,
                'description' => $request->description,
                'user_name' => $request->user_name ?? '[User Input]',
                'user_id' => $request->user_id,
                'reference_id' => $request->reference_id,
                'reference_type' => $request->reference_type,
                'metadata' => $request->metadata ? json_encode($request->metadata) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Activity created successfully',
                'data' => ['id' => $activityId]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create activity: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActivitySummary()
    {
        try {
            // Count activities by type
            $requestCount = DB::connection('pgsql')
                ->table('activities')
                ->where('activity_type', 'sales_order')
                ->count();

            $stockCountCount = DB::connection('pgsql')
                ->table('activities')
                ->where('activity_type', 'stock_count')
                ->count();

            $otherCount = DB::connection('pgsql')
                ->table('activities')
                ->where('activity_type', 'other')
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'aktivitas_request' => $requestCount,
                    'aktivitas_stock_count' => $stockCountCount,
                    'aktivitas_lainnya' => $otherCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get activity summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stockComparisonJson(Request $request)
    {
        $startDate = $request->startDate ?? now()->startOfMonth()->toDateString();
        $endDate = $request->endDate ?? now()->toDateString();

        $useTmp = $request->is('api/accurate/stock-comparison/tmp');
        $table = $useTmp ? 'accurate_stock_comparison_tmp' : 'accurate_stock_comparison';

        $data = DB::connection('pgsql')->table($table)
            // ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('customer_no'); // Sesuaikan field customer

        $result = [];

        foreach ($data as $customerNo => $groupedCustomer) {
            $customerName = $groupedCustomer->first()->customer_name;

            $subs = $groupedCustomer->groupBy('sub_customer_no')->map(function ($subGroup, $subCode) {
                $subName = $subGroup->first()->sub_customer_name;

                $details = $subGroup->map(function ($item) {
                    $stockOut = (float) $item->stock_out;
                    $stockIn = (float) $item->stock_in;
                    $stockAwal = (float) $item->stock_awal;
                    $stockReturn = (float) $item->stock_return;
                    $adjusted = (float) $item->adjusted;
                    $actual = (float) $item->stock_actual;
                    $difference = (float) $item->difference;

                    return [
                        'item_no' => $item->item_no,
                        'product_name' => $item->item_name,
                        'stock_out' => $stockOut,
                        'stock_in' => $stockIn,
                        'stock_return' => $stockReturn,
                        'stock_awal'  => $stockAwal,
                        'adjusted' => $adjusted,
                        'stock_actual' => $actual,
                        'difference' => $difference,
                        'status' => $item->status,
                        'gap_status' => $item->gap_status,
                    ];
                })->values();

                return [
                    'sub_customer_id' => $subCode,
                    'sub_customer_name' => $subName,
                    'adjusted' => $details->sum('adjusted'),
                    'stock_actual' => $details->sum('stock_actual'),
                    'difference' => $details->sum('difference'),
                    'status' => $this->getStatus($details->sum('difference')),
                    'gap_status' => $this->getGapStatus($details->sum('difference')),
                    'details' => $details,
                ];
            })->values();

            $adjustedParent = $subs->sum('adjusted');
            $actualParent = $subs->sum('stock_actual');
            $diffParent = $actualParent - $adjustedParent;

            $result[] = [
                'customer_id' => $customerNo,
                'customer_name' => $customerName,
                'adjusted' => $adjustedParent,
                'stock_actual' => $actualParent,
                'difference' => $diffParent,
                'status' => $this->getStatus($diffParent),
                'gap_status' => $this->getGapStatus($diffParent),
                'subs' => $subs,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }


    private function getStatus($difference)
    {
        return $difference == 0 ? 'MATCH' : 'UNMATCHED';
    }

    private function getGapStatus($difference)
    {
        $absDiff = abs($difference);
        if ($absDiff > 1000) {
            return 'MAJOR';
        } elseif ($absDiff > 0) {
            return 'MINOR';
        } else {
            return null;
        }
    }

    public function switchStatus(Request $request, $accurate_id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $product = DB::connection('pgsql')
            ->table('accurate_items')
            ->where('accurate_id', $accurate_id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        // dd($request->is_active.' -- '.$accurate_id);

        DB::connection('pgsql')
            ->table('accurate_items')
            ->where('accurate_id', $accurate_id)
            ->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'Status produk berhasil diperbarui']);
    }

    public function deleteCustomer($id, $customer_no)
    {
        DB::connection('pgsql')
            ->table('contact_group_customer')
            ->where('contact_group_id', $id)
            ->where('customer_no', $customer_no)
            ->delete();

        DB::connection('pgsql')
            ->table('accurate_sales_order')
            ->where('customer_no_sub', $customer_no)
            ->update(['customer_no_sub' => null]);

        DB::connection('pgsql')
            ->table('accurate_item_transfer')
            ->where('customer_code_sub', $customer_no)
            ->update(['customer_code_sub' => null]);

        DB::connection('pgsql')->table('accurate_sales_order_summary')->truncate();
        DB::connection('pgsql')->insert("
            INSERT INTO accurate_sales_order_summary (customer_no, customer_name, total_qty, created_at, updated_at)
            SELECT
                o.customer_no,
                o.customer_name,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_sales_order_details d
            JOIN
                accurate_sales_order o ON o.id = d.sales_order_id
            GROUP BY
                o.customer_no, o.customer_name
        ");

        DB::connection('pgsql')->table('accurate_item_transfer_summary')->truncate();
        DB::connection('pgsql')->insert("
            INSERT INTO accurate_item_transfer_summary (
                customer_no,
                customer_name,
                tipe_proses,
                total_qty,
                created_at,
                updated_at
            )
            SELECT
                o.customer_code,
                o.char_field3 AS customer_name,
                d.tipe_proses,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_item_transfer_details d
            JOIN
                accurate_item_transfer o ON o.id = d.transfer_id
            WHERE
                d.tipe_proses = 'TRANSFER_OUT'
                AND LEFT(o.number, 2) != 'IT'
            GROUP BY
                o.customer_code,
                o.char_field3,
                d.tipe_proses
        ");

        $after = DB::connection('pgsql')
            ->table('contact_group_customer')
            ->where('contact_group_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $after,
        ]);
    }

    public function filterComparison(Request $request)
    {
        $start = $request->startDate;
        $end = $request->endDate;

        DB::connection('pgsql')->table('accurate_stock_comparison_tmp')->truncate();

        DB::connection('pgsql')->insert("
            INSERT INTO accurate_stock_comparison_tmp (
            customer_no,
            customer_name,
            sub_customer_no,
            sub_customer_name,
            item_no,
            item_name,
            stock_out,
            stock_in,
            stock_return,
            adjusted,
            stock_actual,
            difference,
            status,
            gap_status,
            updated_at
        )
        SELECT
            sto.customer_no,
            sto.customer_name,
            sto.customer_no_sub,
            cgc.customer_name AS sub_customer_name,
            stod.item_no,
            stod.item_name,
            COALESCE(SUM(stod.quantity), 0) AS stock_out,
            COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) AS stock_in,
            COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) AS stock_return,
            (
                COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) -
                COALESCE(SUM(stod.quantity), 0)
            ) AS adjusted,
            COALESCE(sc.actual_stock, 0) AS stock_actual,
            (
                COALESCE(sc.actual_stock, 0) -
                (
                    COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) -
                    COALESCE(SUM(stod.quantity), 0)
                )
            ) AS difference,
            CASE
                WHEN ABS(
                    COALESCE(sc.actual_stock, 0) -
                    (
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) +
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) -
                        COALESCE(SUM(stod.quantity), 0)
                    )
                ) = 0 THEN 'MATCH'
                ELSE 'DIFFERENT'
            END AS status,
            CASE
                WHEN ABS(
                    COALESCE(sc.actual_stock, 0) -
                    (
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) +
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) -
                        COALESCE(SUM(stod.quantity), 0)
                    )
                ) > 100 THEN 'MAJOR'
                WHEN ABS(
                    COALESCE(sc.actual_stock, 0) -
                    (
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_OUT' THEN itd.quantity ELSE 0 END), 0) +
                        COALESCE(SUM(CASE WHEN it.tipe_proses = 'TRANSFER_IN' THEN itd.quantity ELSE 0 END), 0) -
                        COALESCE(SUM(stod.quantity), 0)
                    )
                ) = 0 THEN 'MATCH'
                ELSE 'DIFFERENT'
            END AS gap_status,
            NOW() AS updated_at
        FROM accurate_sales_order sto
        JOIN accurate_sales_order_details stod ON stod.sales_order_id = sto.id
        LEFT JOIN accurate_item_transfer it ON it.customer_code = sto.customer_no
            AND it.customer_code_sub = sto.customer_no_sub
        LEFT JOIN accurate_item_transfer_details itd ON itd.transfer_id = it.accurate_id
            AND itd.item_no = stod.item_no
        LEFT JOIN stock_count sc ON sc.customer_id = sto.customer_no
            AND sc.customer_child_id = sto.customer_no_sub
            AND sc.product_code = stod.item_no
        LEFT JOIN contact_group_customer cgc ON cgc.customer_no = sto.customer_no_sub
        WHERE sto.trans_date BETWEEN ? AND ?
        GROUP BY
            sto.customer_no,
            sto.customer_name,
            sto.customer_no_sub,
            cgc.customer_name,
            stod.item_no,
            stod.item_name,
            sc.actual_stock;
        ", [$start, $end]);

        return response()->json(['status' => true]);
    }

    public function getComparisonData()
    {
        $data = DB::connection('pgsql')->table('accurate_stock_comparison_tmp')->get();
        return response()->json(['data' => $data]);
    }

    public function stockOpnameJson(Request $request, $type)
    {
        // $type = $request->get('type', 'opname');
        $query = DB::connection('pgsql')
            ->table('accurate_stock_opnames as o')
            ->join('accurate_customers as ac', 'o.customer_code', '=', 'ac.customer_no')
            ->leftJoin('contact_group_customer as cg', 'o.customer_code_sub', '=', 'cg.customer_no')
            ->leftJoin('accurate_stock_opname_details as d', 'o.id', '=', 'd.opname_id')
            ->select([
                'o.id',
                'o.code_id',
                'o.customer_code',
                'ac.name as customer_name',
                'o.customer_code_sub',
                'cg.customer_name as customer_name_sub',
                'o.trans_date',
                'o.md',
                DB::raw('COUNT(d.id) as total_item'),
                DB::raw('SUM(COALESCE(d.stock_real, 0)) as total_stock_real')
            ])
            ->where('o.type', $type)
            ->groupBy('o.id', 'o.code_id', 'o.customer_code', 'ac.name', 'o.customer_code_sub', 'cg.customer_name', 'o.trans_date', 'o.md')
            ->orderByDesc('o.trans_date');

        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(ac.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(cg.customer_name) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween(DB::raw('DATE(o.trans_date)'), [
                $request->date('startDate'),
                $request->date('endDate')
            ]);
        }

        $perPage = $request->get('per_page', 10);
        $data = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function stockOpnameDetailJson($id)
    {
        $details = DB::connection('pgsql')
            ->table('accurate_stock_opname_details as d')
            ->join('accurate_items as i', 'd.item_no', '=', 'i.item_no')
            ->where('d.opname_id', $id)
            ->select([
                'd.item_no',
                'i.name as item_name',
                'd.stock_real',
                'd.note'
            ])
            ->orderBy('d.item_no')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $details
            ]
        ]);
    }

    public function updateCustomerInGroup($id, $customer_no, Request $request)
    {
        DB::connection('pgsql')->table('contact_group_customer')
            ->where('customer_no', $customer_no)
            ->where('contact_group_id', $id) // filter berdasarkan customer_no juga
            ->update([
                'customer_name' => $request->customer_name,
                'updated_at' => now(),
            ]);
        return response()->json([
            'status' => true,
            'message' => "Customer {$request->customer_name} di group {$id} berhasil diupdate",
        ]);
    }

    public function importStockOpname(Request $request)
    {
        $rows = $request->input('rows', []);
        $type = $request->input('type', 'opname');

        if (empty($rows)) {
            return response()->json(['status' => 'error', 'message' => 'Data kosong']);
        }

        DB::connection('pgsql')->beginTransaction();

        try {
            $grouped = collect($rows)->groupBy(function ($row) {
                return $row['head_account'] . '|' . $row['sub_account'] . '|' . $row['date'];
            });

            $inserted = [];

            foreach ($grouped as $key => $group) {
                $first = $group->first();

                $customerCode = DB::connection('pgsql')
                    ->table('contact_groups')
                    ->where('name', $first['head_account'])
                    ->value('customer_no');

                $customerCodeSub = DB::connection('pgsql')
                    ->table('contact_group_customer')
                    ->where('customer_name', $first['sub_account'])
                    ->value('customer_no');

                $opnameId = DB::connection('pgsql')
                    ->table('accurate_stock_opnames')
                    ->insertGetId([
                        'code_id'           => $first['retur_number'] ?? null,
                        'md'                => $first['md'] ?? null,
                        'trans_date'        => $first['date'],
                        'customer_code'     => $customerCode ?? null,
                        'customer_code_sub' => $customerCodeSub ?? null,
                        'description'       => 'Import Stock Opname',
                        'type'              => $type,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                foreach ($group as $row) {
                    DB::connection('pgsql')->table('accurate_stock_opname_details')
                        ->insert([
                            'opname_id'     => $opnameId,
                            'item_no'       => $row['sku'],
                            'item_name'     => $row['product_name'],
                            'stock_real'    => $row['qty_opname'],
                            'qty_gimmick'   => $row['qty_gimmick'],
                            'qty_expired'   => $row['qty_expired'],
                            'qty_all'       => $row['qty_expired']+$row['qty_gimmick']+$row['qty_opname'],
                            'note'          => $row['notes'] ?? '',
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                }

                $inserted[] = $opnameId;
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Import stock opname berhasil',
                'inserted' => $inserted
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function groupOpname()
    {
        $data = DB::connection('pgsql')
            ->table('accurate_stock_opnames as o')
            ->join('accurate_customers as ac', 'o.customer_code', '=', 'ac.customer_no')
            ->select(
                'o.customer_code',
                'ac.name as customer_name'
            )
            ->groupBy('o.customer_code', 'ac.name')
            ->orderBy('ac.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function customerDohJson($id)
    {
        $data = DB::connection('pgsql')
            ->table('accurate_items as a')
            ->leftJoin('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
            ->select('a.accurate_id', 'a.item_no', 'a.name', 'a.unit1', 'a.item_type_name', 's.quantity as stock_quantity', 's.quantity_in_all_unit', 'a.is_active')
            ->where('a.is_active', '1')
            ->orderBy('a.name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function downloadImportTemplate(Request $request)
    {
        $headCode = $request->get('headAccount');
        $subCode  = $request->get('subAccount');

        if (!$headCode || !$subCode) {
            return response()->json(['status' => 'error', 'message' => 'Head dan Sub Account wajib dipilih'], 422);
        }

        $head = DB::connection('pgsql')
            ->table('contact_groups')
            ->where('customer_no', $headCode)
            ->value('name');

        $sub = DB::connection('pgsql')
            ->table('contact_group_customer')
            ->where('customer_no', $subCode)
            ->value('customer_name');

        $products = DB::connection('pgsql')
            ->table('accurate_items')
            ->where('is_active', 1)
            ->select('item_no', 'name')
            ->orderBy('item_no')
            ->get();

        $template = [
            ['Retur Number', 'Head Account', 'Sub Account', 'Date', 'SKU', 'Product Name', 'QTY', 'MD', 'Notes']
        ];

        foreach ($products as $p) {
            $template[] = ['', $head, $sub, '2025-10-01', $p->item_no, $p->name, '', '', 'Sales Retur'];
        }

        $filename = "template_import_{$head}_{$sub}.xlsx";

        return Excel::download(new class($template) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function array(): array
            {
                return $this->data;
            }
        }, $filename);
    }

    public function startStockCalculation(Request $request)
    {
        $start = $request->input('startDate');
        $end   = $request->input('endDate');
        $type  = $request->input('type', 'opname');
        $pageSize = $request->input('per_page', 500);

        if (!$start || !$end) {
            return response()->json(['status' => 'error', 'message' => 'startDate and endDate required'], 422);
        }

        $jobId = Str::uuid()->toString();

        DB::table('stock_calc_jobs')->insert([
            'job_id' => $jobId,
            'status' => 'pending',
            'progress' => 0,
            'total_items' => null,
            'meta' => json_encode(['start' => $start, 'end' => $end, 'type' => $type]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // dispatch job
        dispatch(new CalculateStockSystemJob($jobId, $start, $end, $type, (int)$pageSize));

        return response()->json([
            'status' => 'success',
            'job_id' => $jobId,
            'message' => 'Calculation started'
        ]);
    }

    public function stockCalculationStatus($jobId)
    {
        $row = DB::table('stock_calc_jobs')->where('job_id', $jobId)->first();
        if (!$row) return response()->json(['status' => 'error', 'message' => 'Job not found'], 404);

        return response()->json([
            'status' => 'success',
            'data' => [
                'job_id' => $row->job_id,
                'status' => $row->status,
                'progress' => (int)$row->progress,
                'total_items' => $row->total_items,
                'result' => $row->result ? json_decode($row->result, true) : null
            ]
        ]);
    }

    /**
     * Get DOH settings for a contact group
     */
    public function contactGroupDohSettings($contactGroupId)
    {
        $settings = DB::connection('pgsql')
            ->table('contact_group_doh_settings')
            ->where('contact_group_id', $contactGroupId)
            ->where('is_active', true)
            ->orderBy('doh_days', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Save DOH settings for a contact group
     */
    public function saveContactGroupDohSettings(Request $request, $contactGroupId)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.doh_days' => 'required|integer|in:90,30,7',
            'settings.*.notification_type' => 'required|string|in:email,whatsapp',
            'settings.*.email_template' => 'nullable|string',
            'settings.*.whatsapp_template' => 'nullable|string',
            'settings.*.is_active' => 'boolean'
        ]);

        try {
            DB::connection('pgsql')->beginTransaction();

            // Delete existing settings for this contact group
            DB::connection('pgsql')
                ->table('contact_group_doh_settings')
                ->where('contact_group_id', $contactGroupId)
                ->delete();

            // Insert new settings
            foreach ($request->settings as $setting) {
                DB::connection('pgsql')
                    ->table('contact_group_doh_settings')
                    ->insert([
                        'contact_group_id' => $contactGroupId,
                        'doh_days' => $setting['doh_days'],
                        'notification_type' => $setting['notification_type'],
                        'email_template' => $setting['email_template'] ?? null,
                        'whatsapp_template' => $setting['whatsapp_template'] ?? null,
                        'is_active' => $setting['is_active'] ?? true,
                        'created_by' => auth()->user()->id ?? null,
                        'updated_by' => auth()->user()->id ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'status' => 'success',
                'message' => 'DOH settings berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan DOH settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific DOH setting
     */
    public function deleteContactGroupDohSetting($contactGroupId, $settingId)
    {
        try {
            $deleted = DB::connection('pgsql')
                ->table('contact_group_doh_settings')
                ->where('id', $settingId)
                ->where('contact_group_id', $contactGroupId)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'DOH setting berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'DOH setting tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus DOH setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get store information by customer_no
     */
    public function getStoreInfo($customerNo)
    {
        try {
            $store = DB::connection('pgsql')
                ->table('contact_group_customer as cgc')
                ->leftJoin('contact_groups as cg', 'cgc.contact_group_id', '=', 'cg.id')
                ->select([
                    'cgc.id',
                    'cgc.customer_name',
                    'cgc.customer_no',
                    'cgc.email',
                    'cgc.work_phone',
                    'cgc.address',
                    'cg.name as contact_group_name',
                    'cgc.id_user'
                ])
                ->where('cgc.customer_no', $customerNo)
                ->first();

            if (!$store) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Store not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get store info: ' . $e->getMessage()
            ], 500);
        }
    }
}
