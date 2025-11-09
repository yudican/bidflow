<?php

namespace App\Exports;

use App\Models\InventoryProductStock;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;


class ProductTransferExport implements FromView, ShouldAutoSize
{
    protected $request;
    protected $title = null;

    public function __construct($request, $title = 'Product Transfer List')
    {
        $this->request = $request;
        $this->title = $title;
    }

    public function view(): View
    {
        $request = $this->request;
        $search = $request->search;
        $warehouse_id = $request->warehouse_id;
        $status = $request->status;
        $account_id = $request->account_id;
        $transfer_category = $request->transfer_category;

        $inventory = InventoryProductStock::query()->where('inventory_type', $request->inventory_type);

        if ($search) {
            $inventory->where(function ($query) use ($search, $request) {
                $query->where('status', 'like', "%$search%")
                    ->orWhere('reference_number', 'like', "%$search%")
                    ->orWhere('vendor', 'like', "%$search%");

                // Hanya jika inventory_type adalah 'konsinyasi'
                if ($request->inventory_type == 'konsinyasi') {
                    $query->orWhereHas('orderTransfer', function ($subQuery) use ($search) {
                        $subQuery->where('order_number', 'like', "%$search%");
                        $subQuery->orWhereHas('masterBin', function ($subQuery) use ($search) {
                            $subQuery->where('master_bins.name', 'like', "%$search%");
                        });
                    });
                }
            });
        }

        if ($warehouse_id) {
            $inventory->where('warehouse_id', $warehouse_id);
        }

        if ($status) {
            $inventory->where('inventory_status', $status);
        }

        if ($transfer_category) {
            $inventory->where('transfer_category', $transfer_category);
        }

        if ($account_id) {
            $inventory->where('company_id', $account_id);
        }

        $inventories = $inventory->orderBy('created_at', 'desc')->get();

        $datas = [];
        foreach ($inventories as $key => $value) {
            // merge value same value
            $datas[$key]['no']       = $key + 1;
            $datas[$key]['warehouse'] = $value->warehouse_name;
            $datas[$key]['inventory_type'] = $value->inventory_type;
            if ($value->inventory_type == 'transfer') {
                $datas[$key]['trf_id']     = $value?->so_ethix;
                $datas[$key]['destination_warehouse']   = $value?->warehouse_destination_name;
            }

            if ($value->inventory_type == 'konsinyasi') {
                $datas[$key]['destination_bin']  = $value->bin_destination_name;
            }
            $datas[$key]['allocated_by']  = $value?->allocated_by_name;
            $datas[$key]['created_on']   = $value?->created_on;
            $datas[$key]['notes'] = $value->note;
            $datas[$key]['status'] = $value->inventory_status;
            $datas[$key]['product'] = $value->detailItems()->get()->map(function ($item) {
                return [
                    'sku' => @$item->sku,
                    'product_name' => @$item->product_name,
                    'qty' => $item->qty_alocation,
                ];
            });
        }
        return view('export.inventory', [
            'datas' => $datas,
        ]);
    }


    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
