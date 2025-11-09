<?php

namespace App\Http\Controllers\Spa;

use App\Exports\BinExport;
use App\Http\Controllers\Controller;
use App\Models\MasterBin;
use App\Models\MasterBinStock;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\OrderTransfer;
use App\Models\ProductVariant;
use App\Models\InventoryDetailItem;
use App\Imports\BinImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\Paginator;

class BinController extends Controller
{
    public function index($bin_id = null)
    {
        return view('spa.spa-index');
    }

    public function listBin(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $package_id = $request->package_id;
        $variant_id = $request->variant_id;
        $sku = $request->sku;
        $product_id = $request->product_id;
        $sales_channel = $request->sales_channel;
        $master_bin_id = $request->master_bin_id;

        $product =  ProductVariant::query();
        if ($search) {
            $product->where('name', 'like', "%$search%");
        }

        if ($status) {
            $product->whereIn('status', $status);
        }

        if ($package_id) {
            $product->whereIn('package_id', $package_id);
        }

        if ($variant_id) {
            $product->whereIn('variant_id', $variant_id);
        }

        if ($sku) {
            $product->whereIn('sku', $sku);
        }

        if ($product_id) {
            $product->where('product_id', $product_id);
        }

        if ($sales_channel) {
            $product->where('sales_channel', 'like', "%$sales_channel%");
        }

        if (is_array($master_bin_id)) {
            if (in_array('all', $master_bin_id)) {
                $master_bin_id = MasterBin::pluck('id')->toArray();
            }
        }

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => tap($products)->map(function ($product) use ($master_bin_id) {
                $stock_master_bin = MasterBinStock::where('product_variant_id', $product->id);
                if ($master_bin_id) {
                    $stock_master_bin->whereIn('master_bin_id', $master_bin_id);
                }
                $product['stocks'] = $stock_master_bin->sum('stock') > 0 ? $stock_master_bin->sum('stock') : 0;
                $product['realstocks'] = $stock_master_bin->sum('stock');
                return $product;
            }),
            'message' => 'List Product'
        ]);
    }

    // public function listBinDetail(Request $request, $product_variant_id)
    // {
    //     $leads = $this->order($request, 'lead');
    //     $manuals = $this->order($request, 'manual');
    //     $freebiess = $this->order($request, 'freebies');

    //     $orders = [];
    //     $total = 0;
    //     foreach ($leads as $key => $lead) {
    //         $total += 1;
    //         $orders[] = [
    //             'uid_lead' => $lead->uid_lead,
    //             'title' => $lead->title,
    //             'customer_need' => $lead->customer_need,
    //             'order_number' => $lead->order_number,
    //             'invoice_number' => $lead->invoice_number,
    //             'notes' => $lead->notes,
    //             'courier' => $lead->courier,
    //             'status_penagihan' => $lead->status_penagihan,
    //             'status_pengiriman' => $lead->status_pengiriman,
    //             'status_invoice' => $lead->status_invoice,
    //             'due_date' => $lead->due_date,
    //             'status_submit' => $lead->status_submit,
    //             'print_status' => $lead->print_status,
    //             'resi_status' => $lead->resi_status,
    //             'amount' => $lead->amount,
    //             'subtotal' => $lead->subtotal,
    //             'tax_amount' => $lead->tax_amount,
    //             'discount_amount' => $lead->discount_amount,
    //             'contact_name' => $lead->contact_name,
    //             'sales_name' => $lead->sales_name,
    //             'payment_term_name' => $lead->payment_term_name,
    //             'created_by_name' => $lead->created_by_name,
    //             'selected_address' => $lead->selected_address,
    //             'amount_billing_approved' => $lead->amount_billing_approved,
    //             'amount_deposite' => $lead->amount_deposite,
    //             'contact_uid' => $lead->contact_uid,
    //             'warehouse_name' => $lead->warehouse_name,
    //             'so_type' => 'Order Lead',
    //             'qty_delivery' => $lead->productNeeds()->sum('qty_delivery'),
    //             'qty_dibayar' => $lead->productNeeds()->sum('qty_dibayar')
    //         ];
    //     }
    //     foreach ($manuals as $key => $manual) {
    //         $total += 1;
    //         $orders[] = [
    //             'uid_lead' =>  $manual->uid_lead,
    //             'title' =>  $manual->title,
    //             'customer_need' =>  $manual->customer_need,
    //             'order_number' =>  $manual->order_number,
    //             'invoice_number' =>  $manual->invoice_number,
    //             'notes' =>  $manual->notes,
    //             'courier' =>  $manual->courier,
    //             'status_penagihan' =>  $manual->status_penagihan,
    //             'status_pengiriman' =>  $manual->status_pengiriman,
    //             'status_invoice' =>  $manual->status_invoice,
    //             'due_date' =>  $manual->due_date,
    //             'status_submit' =>  $manual->status_submit,
    //             'print_status' =>  $manual->print_status,
    //             'resi_status' =>  $manual->resi_status,
    //             'amount' =>  $manual->amount,
    //             'subtotal' =>  $manual->subtotal,
    //             'tax_amount' =>  $manual->tax_amount,
    //             'discount_amount' =>  $manual->discount_amount,
    //             'contact_name' =>  $manual->contact_name,
    //             'sales_name' =>  $manual->sales_name,
    //             'payment_term_name' =>  $manual->payment_term_name,
    //             'created_by_name' =>  $manual->created_by_name,
    //             'selected_address' =>  $manual->selected_address,
    //             'amount_billing_approved' =>  $manual->amount_billing_approved,
    //             'amount_deposite' =>  $manual->amount_deposite,
    //             'contact_uid' =>  $manual->contact_uid,
    //             'warehouse_name' =>  $manual->warehouse_name,
    //             'so_type' => 'Manual',
    //             'qty_delivery' => $manual->productNeeds()->sum('qty_delivery'),
    //             'qty_dibayar' => $manual->productNeeds()->sum('qty_dibayar'),
    //         ];
    //     }
    //     foreach ($freebiess as $key => $freebies) {
    //         $total += 1;
    //         $orders[] = [
    //             'uid_lead' =>  $manual->uid_lead,
    //             'title' =>  $manual->title,
    //             'customer_need' =>  $manual->customer_need,
    //             'order_number' =>  $manual->order_number,
    //             'invoice_number' =>  $manual->invoice_number,
    //             'notes' =>  $manual->notes,
    //             'courier' =>  $manual->courier,
    //             'status_penagihan' =>  $manual->status_penagihan,
    //             'status_pengiriman' =>  $manual->status_pengiriman,
    //             'status_invoice' =>  $manual->status_invoice,
    //             'due_date' =>  $manual->due_date,
    //             'status_submit' =>  $manual->status_submit,
    //             'print_status' =>  $manual->print_status,
    //             'resi_status' =>  $manual->resi_status,
    //             'amount' =>  $manual->amount,
    //             'subtotal' =>  $manual->subtotal,
    //             'tax_amount' =>  $manual->tax_amount,
    //             'discount_amount' =>  $manual->discount_amount,
    //             'contact_name' =>  $manual->contact_name,
    //             'sales_name' =>  $manual->sales_name,
    //             'payment_term_name' =>  $manual->payment_term_name,
    //             'created_by_name' =>  $manual->created_by_name,
    //             'selected_address' =>  $manual->selected_address,
    //             'amount_billing_approved' =>  $manual->amount_billing_approved,
    //             'amount_deposite' =>  $manual->amount_deposite,
    //             'contact_uid' =>  $manual->contact_uid,
    //             'warehouse_name' =>  $manual->warehouse_name,
    //             'so_type' => 'Freebies',
    //             'qty_delivery' => $manual->productNeeds()->sum('qty_delivery'),
    //             'qty_dibayar' => $manual->productNeeds()->sum('qty_dibayar'),
    //         ];
    //     }

    //     $product = ProductVariant::find($product_variant_id);

    //     $perPage = $request->perPage || 10;
    //     $currentPage = $request->page - 1;
    //     $pagedData = array_slice($orders, $currentPage * $perPage, $perPage);
    //     $data =  new Paginator($pagedData, count($orders), $perPage);
    //     $data['variant_name'] = $product->name;
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $data,
    //         'total' => $total,
    //         'bins' => MasterBin::all(),
    //         'message' => 'List BIn'
    //     ]);
    // }

    public function listBinDetail(Request $request, $product_variant_id)
    {
        $orders = [];
        $product = ProductVariant::find($product_variant_id);
        $orders = InventoryDetailItem::with('orderTransfer')->where('product_id', $product->product_id)->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'product_variant' => $product->name,
            'message' => 'List BIn',
        ]);
    }

    public function binDetail(Request $request, $bin_id)
    {
        return response()->json([
            'data' => OrderManual::where('uid_lead', $bin_id)->where('type', 'konsinyasi')->first()
        ]);
    }

    public function order($request, $type)
    {
        $search = $request->search;
        $contact = $request->contact;
        $sales = $request->sales;
        $created_at = $request->created_at;
        $status = $request->status;
        $user = auth()->user();
        $role = $user->role->role_type;
        $account_id = $request->account_id;
        $payment_term = $request->payment_term;
        $print_status = $request->print_status;
        $resi_status = $request->resi_status;
        $product_variant_id = $request->product_variant_id;
        $orderLead =  OrderManual::query()->whereHas('productNeeds', function ($query) use ($product_variant_id) {
            return $query->whereHas('masterBinStocks', function ($query) use ($product_variant_id) {
                return $query->where('product_variant_id', $product_variant_id);
            });
        });
        if ($type == 'lead') {
            $orderLead =  OrderLead::query()->whereHas('productNeeds', function ($query) use ($product_variant_id) {
                return $query->whereHas('masterBinStocks', function ($query) use ($product_variant_id) {
                    return $query->where('product_variant_id', $product_variant_id);
                });
            });
        } else if ($type == 'freebies') {
            $orderLead =  OrderManual::query()->whereHas('productNeeds', function ($query) use ($product_variant_id) {
                return $query->whereHas('masterBinStocks', function ($query) use ($product_variant_id) {
                    return $query->where('product_variant_id', $product_variant_id);
                });
            })->where('type', 'freebies');
        }
        if ($search) {
            $orderLead->where('order_number', 'like', "%$search%");
            $orderLead->orWhereHas('contactUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $orderLead->orWhereHas('salesUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $orderLead->orWhereHas('createUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
        }
        if ($contact) {
            $orderLead->whereIn('contact', $contact);
        }

        if ($sales) {
            $orderLead->whereIn('sales', $sales);
        }

        if ($status) {
            $orderLead->whereIn('status', $status);
        }

        if ($created_at) {
            $orderLead->whereBetween('created_at', $created_at);
        }

        if ($payment_term) {
            $orderLead->whereIn('payment_term', $payment_term);
        }

        // cek switch account
        if ($account_id) {
            $orderLead->where('company_id', $account_id);
        }

        if ($print_status) {
            $orderLead->where('print_status', $print_status);
        }

        if ($resi_status) {
            $orderLead->where('resi_status', $resi_status);
        }

        if ($role == 'sales') {
            $orderLead->where('user_created', $user->id)->orWhere('sales', $user->id);
        }


        $orderLeads = $orderLead->where('payment_term', 3)->orderBy('created_at', 'desc')->get();

        return $orderLeads;
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('attachment');

            // $exec = Excel::import(new ContactImport(), $file);

            $import = new BinImport();
            Excel::import($import, $file);

            $rowsWithLocationNotMatch = @$import->getRowsWithLocationNotMatch();

            if (!empty($rowsWithLocationNotMatch)) {
                return response()->json([
                    'status' => 'failed',
                    'data' => $rowsWithLocationNotMatch,
                    'message' => 'Input lokasi tidak sesuai',
                ], 200);
            }

            return response()->json(['message' => 'Data berhasil diimpor'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $file_name = 'BIN-EXPORT-' . date('d-m-Y') . '.xlsx';

        Excel::store(new BinExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List BIN'
        ]);
    }
}
