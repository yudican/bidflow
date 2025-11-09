<?php

namespace App\Http\Controllers\Spa\Order;

use App\Http\Controllers\Controller;
use App\Exports\SalesReturnExport;
use App\Exports\SalesReturnDetailExport;
use App\Models\AddressUser;
use App\Models\LeadBilling;
use App\Models\OrderDeposit;
use App\Models\OrderLead;
use App\Models\PaymentTerm;
use App\Models\ProductVariant;
use App\Models\ReturResi;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SalesReturnController extends Controller
{
    public function index($uid_return = null)
    {
        return view('spa.spa-index');
    }

    public function formReturn($uid_return = null)
    {
        return view('spa.spa-index');
    }


    public function getListSalesReturn(Request $request)
    {
        $search = $request->search;
        $account_id = $request->account_id;
        $items = [
            'contactUser',
            'salesUser',
            'addressUser',
            'createUser',
            'courierUser',
            'brand',
            'leadActivities',
            'user',
            'warehouse',
            'paymentTerm',
            'returnItems'
        ];
        $return =  SalesReturn::with($items);
        if ($search) {
            $return->where('sr_number', 'like', "%$search%");
            $return->orWhere('order_number', 'like', "%$search%");
        }

        // cek switch account
        if ($account_id) {
            if ($account_id != 'null') {
                $return->where('company_id', $account_id);
            }
        }

        $returns =  $return->orderBy('sales_return_masters.created_at', 'desc')->where('status', '>=', 0)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $returns
        ]);
    }

    public function getListSalesReturnDetail($uid_return)
    {
        $items = [
            'contactUser',
            'contactUser.company',
            'salesUser',
            'addressUser',
            'createUser',
            'courierUser',
            'brand',
            'leadActivities',
            'returResi',
            'user',
            'billings',
            'warehouse',
            'paymentTerm',
            'returnItems',
            'returnItems.product',
        ];
        $return =  SalesReturn::with($items)->where('uid_retur', $uid_return)->first();
        return response()->json([
            'status' => 'success',
            'data' => $return
        ]);
    }

    public function saveSalesReturn(Request $request)
    {
        $address = AddressUser::find($request->address_id);
        $warehouse = Warehouse::find($request->warehouse_id);
        $uid_retur = $request->uid_retur ?? hash('crc32', Carbon::now()->format('U'));
        $status = 0;
        $retur = SalesReturn::where('uid_retur', $uid_retur)->first();
        if ($retur) {
            $status = $retur->status;
        }


        $kode_unik = $request->kode_unik ?? $this->getUniqueCodeLead();
        $data = [
            'uid_retur'  => $uid_retur,
            'sr_number'  => $this->generateSRNo(),
            'order_number'  => $request->order_number,
            'brand_id'  => $request->brand_id,
            'contact'  => $request->contact,
            'sales'  => $request->sales,
            'payment_terms'  => $request->payment_terms,
            'due_date'  => $request->due_date,
            'warehouse_id'  => $request->warehouse_id,
            'shipping_address'  => $address ? $address->alamat_detail : null,
            'warehouse_address'  => $warehouse ? $warehouse->alamat : null,
            'notes'  => $request->notes,
            'total'  => $request->total,
            'status'  => $status, // draft
            'kode_unik' => $kode_unik,
            'temp_kode_unik' => $kode_unik,
            'company_id' => $request->account_id
        ];

        $return = SalesReturn::updateOrCreate(['uid_retur'  => $uid_retur], $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => $return
        ]);
    }

    public function selectProductItems(Request $request)
    {
        $data = [];

        if ($request->item_id) {
            $data['id'] = $request->item_id;
        }
        $price = $request->price;
        $product = ProductVariant::where('id', $request->product_id)->first();
        if ($product) {
            $price = $product->price['final_price'];
        }
        SalesReturnItem::updateOrCreate($data, [
            'uid_retur' => $request->uid_retur,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'price' => $price,
            'discount_id' => $request->discount_id,
            'tax_id' => $request->tax_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil ditambahkan',
            'product' => $product
        ]);
    }

    public function addProductItem(Request $request)
    {
        SalesReturnItem::create($request->all());
        $retur = SalesReturn::where('uid_retur', $request->uid_retur)->first();

        if ($request->newData) {
            SalesReturn::updateOrCreate(['uid_retur' => $request->uid_retur], [
                'uid_retur' => $request->uid_retur,
                'type' => $retur ? $retur->type : 'b2b',
                'status' => $retur ? $retur->status : -1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil ditambahkan'
        ]);
    }

    public function deleteProductItem(Request $request)
    {
        SalesReturnItem::find($request->item_id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil dihapus'
        ]);
    }

    public function addQty(Request $request)
    {
        $item = SalesReturnItem::find($request->item_id);
        if ($item->qty) {
            $item->increment('qty');
        }
        return response()->json([
            'status' => 'success',
            'data' => $item->product
        ]);
    }

    public function removeQty(Request $request)
    {
        $item = SalesReturnItem::find($request->item_id);
        if ($item->qty > 1) {
            $item->decrement('qty');
        }
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function loadDataByOrderNumber(Request $request)
    {
        $order = OrderLead::where('order_number', $request->order_number)->first();
        if ($order) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'contact' => ['label' => $order->contactUser->name, 'value' => $order->contactUser->id],
                    'sales' => ['label' => $order->salesUser->name, 'value' => $order->salesUser->id],
                    'brand_id' => $order->brand_id,
                    'warehouse_id' => $order->warehouse_id,
                    'payment_terms' => $order->payment_term,
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function addBilling(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'uid_lead' => $request->uid_retur,
                'account_name' => $request->account_name,
                'account_bank' => $request->account_bank,
                'total_transfer' => $request->total_transfer,
                'transfer_date' => $request->transfer_date,
                'status' => 0,
            ];
            if ($request->upload_billing_photo) {
                $file = $this->uploadImage($request, 'upload_billing_photo');
                $data['upload_billing_photo'] = $file;
            }

            if ($request->upload_transfer_photo) {
                $file = $this->uploadImage($request, 'upload_transfer_photo');
                $data['upload_transfer_photo'] = $file;
            }

            LeadBilling::create($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Billing Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }
    public function billingVerify(Request $request)
    {
        try {
            DB::beginTransaction();

            $billing = LeadBilling::find($request->id);
            if ($billing) {
                $billing->update(['status' => $request->status, 'notes' => $request->notes, 'approved_by' => auth()->user()->id, 'approved_at' => date('Y-m-d H:i:s'), 'payment_number' => $this->generatePaymentNumber($billing->uid_lead)]);

                if ($request->status == 1) {
                    if ($billing->total_transfer < $request->amount) {
                        if ($request->deposite > 0) {
                            $amount = $request->deposite + $billing->total_transfer;
                            $final_amount = $amount - $request->amount;
                            $amount_total  = $final_amount - $request->deposite;
                            LeadBilling::create([
                                'uid_lead' => $billing->uid_lead,
                                'account_name' => '-',
                                'account_bank' => '-',
                                'total_transfer' => $amount_total > 0 ? $amount_total : $request->deposite,
                                'transfer_date' => date('Y-m-d'),
                                'status' => 1,
                                'upload_billing_photo' => null,
                                'upload_transfer_photo' => null,
                                'notes' => 'Deposite',
                                'approved_by' => $billing->approved_by,
                                'approved_at' => date('Y-m-d H:i:s'),
                            ]);

                            OrderDeposit::create([
                                'uid_lead' => $billing->uid_lead,
                                'amount' => $amount_total > 0 ? -$amount_total : -$request->deposite,
                                'order_type' => 'sales-retur',
                                'contact' => $billing->salesRetur ? $billing->salesRetur->contact : '-',
                            ]);
                        } else {
                            if ($request->billing_approved > 0) {
                                $amount_total = $request->billing_approved + $billing->total_transfer - $request->amount;
                                OrderDeposit::create([
                                    'uid_lead' => $billing->uid_lead,
                                    'amount' => $amount_total,
                                    'order_type' => 'sales-retur',
                                    'contact' => $billing->orderLead->contact,
                                ]);
                            }
                        }
                    }

                    if ($billing->total_transfer > $request->amount) {
                        OrderDeposit::create([
                            'uid_lead' => $billing->uid_lead,
                            'amount' => $billing->total_transfer - $request->amount,
                            'order_type' => 'sales-retur',
                            'contact' => $billing->salesRetur ? $billing->salesRetur->contact : '-',
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Billing Berhasil Diupdate',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Diupdate',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function saveResi(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = [
                'uid_retur' => $request->uid_retur,
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'resi' => $request->resi,
                'expedition_name' => $request->expedition_name,
                'created_by' => auth()->user()->id,
            ];
            ReturResi::updateOrCreate(['uid_retur' => $request->uid_retur], $data);
            $retur_sales = SalesReturn::where('uid_retur', $request->uid_retur)->first();
            $retur_sales->update(['status' => 2]);
            // if ($retur_sales) {
            //     createNotification(
            //         'SOR200',
            //         [
            //             'user_id' => $retur_sales->sales
            //         ],
            //         [
            //             'name' => $retur_sales->salesUser?->name ?? '-',
            //             'submit_by' => auth()->user()->name,
            //             'sender_name' => $request->sender_name,
            //             'sender_phone' => $request->sender_phone,
            //             'resi' => $request->resi,
            //             'expedition_name' => $request->expedition_name,
            //         ],
            //         ['brand_id' => $retur_sales->brand_id]
            //     );
            // }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Resi Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Resi Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function assignToWarehouse(Request $request)
    {
        try {
            DB::beginTransaction();

            $retur_sales = SalesReturn::where('uid_retur', $request->uid_retur)->first();
            $retur_sales->update(['status' => 1]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Assign Warehouse Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Assign Warehouse Gagal Disimpan',
            ], 400);
        }
    }

    public function cancel(Request $request)
    {
        try {
            DB::beginTransaction();

            $retur_sales = SalesReturn::where('uid_retur', $request->uid_retur)->first();
            $retur_sales->update(['status' => 5]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Cancel Berhasil',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Cancel Gagal',
            ], 400);
        }
    }

    public function paymentProccess(Request $request)
    {
        try {
            DB::beginTransaction();

            $retur_sales = SalesReturn::where('uid_retur', $request->uid_retur)->first();
            $retur_sales->update(['status' => 3]);

            // $billings = LeadBilling::where('uid_lead', $retur_sales->uid_lead)->get();
            // foreach ($billings as $key => $value) {
            //     $value->update(['payment_number' => $this->generatePaymentNumber($key + 1)]);
            // }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Payment Proccess Berhasil',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Proccess Gagal',
            ], 400);
        }
    }

    public function completed(Request $request)
    {
        try {
            DB::beginTransaction();

            $retur_sales = SalesReturn::where('uid_retur', $request->uid_retur)->first();
            $retur_sales->update(['status' => 4]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Sales Return Telah Selesai',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Return Gagal',
            ], 400);
        }
    }

    private function generateSRNo()
    {
        $year = date('Y');
        $order_number = "SR/$year/";
        $row = SalesReturn::where('sr_number', 'like', "%$order_number%")->orderBy('created_at', 'desc')->limit(1)->first();
        $count_code = 8 + strlen($year);

        $nomor = $order_number . '000000001';
        if ($row) {
            $awal = substr($row->sr_number, $count_code);
            $next = sprintf("%09d", ((int)$awal + 1));
            $nomor = $order_number . $next;
        }
        return $nomor;
    }

    public function getDueDate(Request $request)
    {
        $order = OrderLead::where('order_number', $request->order_number)->first();
        if ($order) {
            $days = $order->paymentTerm->days_of;
            $due_date = Carbon::now()->addDays($days);
            if ($order->due_date) {
                $due_date = Carbon::parse($order->due_date)->addDays($days);
                return response()->json([
                    'status' => 'success',
                    'due_date' => $due_date->format('Y-m-d'),
                ]);
            }
        }

        $payment_term = PaymentTerm::find($request->payment_terms);
        $days = 1;
        if ($payment_term) {
            $days = $payment_term->days_of;
        }
        $due_date = Carbon::now()->addDays($days);
        return response()->json([
            'status' => 'success',
            'due_date' => $due_date->format('Y-m-d'),
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }
        $file = Storage::disk('s3')->put('upload/user', $request[$path], 'public');
        return $file;
    }

    public function deleteUniqueCode(Request $request)
    {
        try {
            DB::beginTransaction();
            $row = SalesReturn::where('uid_lead', $request->uid_retur)->first();
            if ($row) {
                $row->update(['kode_unik' => $request->kode_unik]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Kode Unik Berhasil Di',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Unik Gagal Di',
            ]);
        }
    }

    // update ongkos kirim
    public function updateOngkosKirim(Request $request, $uid_retur)
    {
        try {
            DB::beginTransaction();
            $row = SalesReturn::where('uid_retur', $uid_retur)->first();
            if ($row) {
                $row->update(['ongkir' => $request->ongkir]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Ongkos Kirim Berhasil Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ongkos Kirim Gagal Diupdate',
            ]);
        }
    }

    // get unique code 3 digit max 500 with auto increment
    private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
    {
        return 0;
        $data = SalesReturn::whereDate('created_at', date('Y-m-d'))->select($field)->orderBy('id', 'desc')->limit(1)->first();
        if ($data) {
            if ($data->$field == 500) {
                $nomor = $prefix . '001';
            } else {
                $awal = substr($data->$field, 3);
                $next = sprintf("%03d", ((int)$awal + 1));
                $nomor = $prefix . $next;
            }
        } else {
            $nomor = $prefix . '001';
        }
        return $nomor;
    }

    private function generatePaymentNumber($uid_lead)
    {
        $lastPo = LeadBilling::where('uid_lead', $uid_lead)->orderBy('id', 'desc')->first();
        if ($lastPo) {
            $number = substr($lastPo->received_number, 4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }
        return 'PAY/' . date('Y') . '/' . $number;
    }

    public function export()
    {
        $items = [
            'contactUser',
            'salesUser',
            'addressUser',
            'createUser',
            'courierUser',
            'brand',
            'leadActivities',
            'user',
            'warehouse',
            'paymentTerm',
            'returnItems'
        ];
        $return =  SalesReturn::with($items);

        $file_name = 'convert/FIS-Sales_Return-' . date('d-m-Y') . '.xlsx';

        Excel::store(new SalesReturnExport($return), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function exportDetail($uid)
    {
        $salesReturn = SalesReturn::query();

        $file_name = 'convert/FIS-Sales_Return-' . date('d-m-Y') . '.xlsx';

        Excel::store(new SalesReturnDetailExport($uid), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
