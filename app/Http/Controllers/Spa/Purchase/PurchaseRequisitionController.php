<?php

namespace App\Http\Controllers\Spa\Purchase;

use App\Http\Controllers\Controller;
use App\Exports\PurchaseRequititionExport;
use App\Jobs\CreateLogQueue;
use App\Models\InventoryItem;
use App\Models\InventoryProductStock;
use App\Models\LeadMaster;
use App\Models\ProductNeed;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\PurchaseBilling;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderStockOpname;
use App\Models\PurchaseRequitition;
use App\Models\PurchaseRequititionApproval;
use App\Models\PurchaseRequititionItem;
use App\Models\PurchaseLogApproval;
use App\Models\Role;
use App\Models\Variant;
use App\Models\Brand;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;
use GuzzleHttp\Client;

class PurchaseRequisitionController extends Controller
{
    public function index($purchase_requitition_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPurchaseRequitition(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $tanggal_transaksi = $request->tanggal_transaksi;

        $order =  PurchaseRequitition::query();
        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('pr_number', 'like', "%$search%");
                $query->orWhere('vendor_code', 'like', "%$search%");
                $query->orWhere('vendor_name', 'like', "%$search%");
                $query->orWhere('project_name', 'like', "%$search%");
                $query->orWhere('request_by_name', 'like', "%$search%");
                $query->orWhere('request_by_email', 'like', "%$search%");
                $query->orWhere('request_by_division', 'like', "%$search%");
            });
        }

        if ($status) {
            $order->where('request_status', $status == 10 ? 0 : $status);
        }

        if ($tanggal_transaksi) {
            // Assuming $tanggal_transaksi is an array with two elements: start date and end date
            $startDate = $tanggal_transaksi[0];
            $endDate = $tanggal_transaksi[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $order->whereBetween('created_at', [$startDate, $endDate]);
        }


        $orders = $order->orderByRaw($this->getStatus())->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'List Purchase Requitition'
        ]);
    }

    public function getStatus()
    {
        $orderMapping = [
            '0' => 1,
            '1' => 2,
            '2' => 3,
            '3' => 4,
            '4' => 5,
            '5' => 0,
        ];

        // Convert the mapping to a CASE SQL statement
        $orderByCase = 'CASE request_status';
        foreach ($orderMapping as $status => $order) {
            $orderByCase .= " WHEN '{$status}' THEN {$order}";
        }
        $orderByCase .= ' ELSE 0 END';
        return $orderByCase;
    }


    public function detailPurchaseRequitition($purchase_requitition_id)
    {
        $order = PurchaseRequitition::with(['items', 'approvalLeads', 'approvalLog'])->where('uid_requitition', $purchase_requitition_id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Detail Purchase Requitition'
        ]);
    }

    public function savePurchaseRequitition(Request $request) {}

    public function updatePurchaseRequitition(Request $request, $purchase_requitition_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseRequitition::where('uid_requitition', $purchase_requitition_id)->first();

            $data = [
                'uid_requitition' => hash('crc32', Carbon::now()->format('U')),
                // 'pr_number' => $this->generatePrNumber(),
                'vendor_code' => $request->vendor_code ?? null,
                'vendor_name' => $request->vendor_name ?? null,
                'brand_id' => $request->brand_id ??  null,
                'payment_term_id' => $request->payment_term_id ??  null,
                'company_account_id' => $request->company_account_id ?? 1,
                'received_by' => $request->received_by ??  null,
                'received_address' => $request->received_address ??  '-',
                'project_name' => $request->project_name ??  '-',
                'request_by_name' => $request->request_by_name ??  '-',
                'request_by_email' => $request->request_by_email ??  '-',
                'request_by_division' => $request->request_by_division ??  '-',
                'request_date' => date('Y-m-d', strtotime($request->request_date)),
                'request_note' => $request->request_note ?? null,
                'request_status' => $request->status ?? 0,
            ];

            if ($request->attachment) {
                $file = $this->uploadImage($request, 'attachment');
                $data['attachment'] = $file;
            }
            $row->update($data);

            if ($request->items) {
                $items = json_decode($request->items, true);
                if (is_array($items)) {
                    foreach ($items as $key => $item) {
                        PurchaseRequititionItem::updateOrCreate(['id' => $item['id']], [
                            'purchase_requitition_id' => $row->id,
                            'item_name' => isset($item['item_name']) ? $item['item_name'] : null,
                            'item_qty' => isset($item['item_qty']) ? $item['item_qty'] : null,
                            'item_unit' => isset($item['item_unit']) ? $item['item_unit'] : null,
                            'item_price' => isset($item['item_price']) ? $item['item_price'] : null,
                            'item_tax' => isset($item['item_tax']) ? $item['item_tax'] : null,
                            'item_url' => isset($item['item_url']) ? $item['item_url'] : null,
                            'item_note' => isset($item['item_note']) ? $item['item_note'] : null,
                        ]);
                    }
                }
            }


            if ($request->approvals) {
                $approvals = json_decode($request->approvals, true);
                if (is_array($approvals)) {
                    foreach ($approvals as $key => $item) {
                        PurchaseRequititionApproval::updateOrCreate(['purchase_requitition_id' => $row->id], [
                            'purchase_requitition_id' => $row->id,
                            'user_id' => isset($item['user_id']) ? $item['user_id'] : null,
                            'role_id' => isset($item['role_id']) ? $item['role_id'] : null,
                            'status' => 0,
                            'label' => isset($item['label']) ? $item['label'] : null,
                        ]);
                    }
                }
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Update Purchase Requisition - ' . $purchase_requitition_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'message' => 'Requisition created successfully',

            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Requisition failed to create',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function rejectPurchaseRequitition(Request $request, $purchase_requitition_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseRequitition::where('uid_requitition', $purchase_requitition_id)->first();
            $row->update([
                'request_status' => $request->status,
            ]);

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Reject Purchase Requisition - ' . $purchase_requitition_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'message' => 'Requisition reject successfully',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Requisition failed to reject',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function approvePurchaseRequitition(Request $request, $purchase_requitition_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseRequitition::where('uid_requitition', $purchase_requitition_id)->first();
            $row->update([
                'request_status' => $request->status,
            ]);

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Approve Purchase Requisition - ' . $purchase_requitition_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'message' => 'Requisition approve successfully',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Requisition failed to approve',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function purchaseOrderComplete(Request $request, $purchase_requitition_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'request_status'  => 2
            ];
            $row = PurchaseRequitition::where('uid_requitition', $purchase_requitition_id)->first();
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Complete Purchase Requisition - ' . $purchase_requitition_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Gagal Diupdate'
            ], 400);
        }
    }

    public function cancelPurchaseRequitition($purchase_requitition_id)
    {
        $order = PurchaseOrder::find($purchase_requitition_id);
        $order->update(['status' => 8]);
        $dataLog = [
            'log_type' => '[fis-dev]purchase_requisition',
            'log_description' => 'Cancel Purchase Requisition - ' . $purchase_requitition_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data Purchase Requitition berhasil dihapus'
        ]);
    }

    // generate receive number auto increment with format PO-0001
    public function generateReceiveNumber($purchase_requitition_id = null)
    {
        $lastPo = PurchaseOrderItem::where('purchase_requitition_id', $purchase_requitition_id)->whereNotNull('received_number')->orderBy('id', 'desc')->first();
        if ($lastPo) {
            $number = substr($lastPo->received_number, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }
        return 'RCV/' . date('Y') . '/' . $number;
    }

    public function approvalVerification(Request $request, $approval_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseRequititionApproval::find($approval_id);
            $user = User::find($row->user_id);
            if ($request->status == 1) {
                $row->update(['status' => 1]);
                $data_log = [
                    'purchase_requitition_id' => $row->purchase_requitition_id,
                    'approval_id' => $approval_id,
                    'action' => 'Approved contact : ' . $row->user_name,
                    'execute_by' => auth()->check() ? auth()->user()->id : null,
                ];
                $log_approval = PurchaseLogApproval::create($data_log);

                $purchase_requisiition = PurchaseRequitition::where('uid_requitition', $request->purchase_requisition_id);
                $requisition = $purchase_requisiition->first();
                $requisition->update(['company_account_id' => $request->account_id]);

                if ($row->label == 'Verified by') {
                    createNotification(
                        'PR203',
                        [],
                        [
                            'pr_number' => $requisition->pr_number,
                            'pic_name' => @$user->name,
                        ],
                        ['brand_id' => $requisition->brand_id ?? 1]
                    );
                }

                $requisition_items = PurchaseRequititionApproval::where('purchase_requitition_id', $requisition->id)->get();
                foreach ($requisition_items as $key => $item) {
                    if ($row->label == 'Verified by') {
                        if (in_array($item->role_id, ['ea612622-9bcc-49e9-8b0a-c56974941143'])) {
                            $item->update(['status' => 0]);
                        }
                    }

                    if ($row->label == 'Approved by') {
                        $checkApproval = PurchaseRequititionApproval::where('purchase_requitition_id', $requisition->id)->where('role_id', 'ea612622-9bcc-49e9-8b0a-c56974941143')->where('status', 1)->count();

                        if (!in_array($row->role_id, ['5e81f800-c326-474c-9209-029918a8282b'])) {
                            if ($checkApproval > 1) {
                                if (in_array($item->role_id, ['5e81f800-c326-474c-9209-029918a8282b']) && $item->label == 'Approved by') {
                                    $item->update(['status' => 0]);
                                }
                            }
                        }

                        if (in_array($row->role_id, ['5e81f800-c326-474c-9209-029918a8282b'])) {
                            $requisition->update(['request_status' => 1]);
                            if ($item->label == 'Excecuted by') {
                                $item->update(['status' => 1]);
                            }
                        }
                    }
                }
                // print_r($data_log);die();
                if ($purchase_requisiition->count() > 1) {
                    sendEmailSingle(
                        'PRAPR200',
                        [
                            'email' => $purchase_requisiition->request_by_email
                        ],
                        [
                            'name' => $purchase_requisiition->request_by_name,
                        ],
                        [
                            'brand_id' => $purchase_requisiition->brand_id
                        ]
                    );
                }
            } else if ($request->status == 2) {
                $row->update(['status' => 2]);
                
                $data_log = [
                    'purchase_requitition_id' => $row->purchase_requitition_id,
                    'approval_id' => $approval_id,
                    'action' => 'Rejected contact : ' . $row->user_name,
                    'execute_by' => auth()->check() ? auth()->user()->id : null,
                ];

                $log_approval = PurchaseLogApproval::create($data_log);
                $purchase_requisiition = PurchaseRequitition::where('uid_requitition', $request->purchase_requisition_id)->first();
                $purchase_requisiition->update(['request_status' => 3]);

                if ($row->label == 'Verified by') {
                    createNotification(
                        'PR205',
                        [],
                        [
                            'pr_number' => $purchase_requisiition->pr_number,
                            'pic_name' => @$user->name,
                        ],
                        ['brand_id' => $purchase_requisiition->brand_id ?? 1]
                    );
                }

                sendEmailSingle(
                    'PRRJCT200',
                    [
                        'email' => $purchase_requisiition->request_by_email
                    ],
                    [
                        'name' => $purchase_requisiition->request_by_name,
                    ],
                    [
                        'brand_id' => $purchase_requisiition->brand_id
                    ]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Approval Purchase Requisition - ' . $approval_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function updatePrice(Request $request, $item_id)
    {
        $purchaseItem = PurchaseRequititionItem::find($item_id);
        if ($purchaseItem) {
            $purchaseItem->update(['item_price' => $request->item_price]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Disimpan'
        ], 400);
    }

    public function uploadAttachment(Request $request, $purchase_requitition_id)
    {
        $purchaseItem = PurchaseRequitition::find($purchase_requitition_id);
        if ($purchaseItem) {
            $attachments = $request->attachments;
            if ($attachments && is_array($attachments)) {
                $files = [];
                foreach ($attachments as $key => $item) {
                    $file = Storage::disk('s3')->put('upload/purchase/attachment', $item, 'public');
                    $files[] = $file;
                }

                $purchaseItem->update(['attachment' => $purchaseItem->attachment ? $purchaseItem->attachment . ',' . implode(',', $files) : implode(',', $files)]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Berhasil Disimpan'
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Disimpan'
        ], 400);
    }

    public function deleteAttachment(Request $request, $purchase_requitition_id)
    {
        $purchaseItem = PurchaseRequitition::find($purchase_requitition_id);
        if ($purchaseItem) {
            $attachments = $request->attachments;
            if (is_array($attachments)) {
                $files = [];
                foreach ($attachments as $key => $item) {
                    $files[] = $item;
                }
                if (count($files) > 0) {
                    $purchaseItem->update(['attachment' => implode(',', $files)]);
                } else {
                    $purchaseItem->update(['attachment' => null]);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Berhasil Dihapus'
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Dihapus'
        ], 400);
    }

    public function exportPdf($purchase_requitition_id = null)
    {

        $purchase = PurchaseRequitition::with(['approvalLeads'])->find($purchase_requitition_id);

        $brand = Brand::find($purchase->brand_id);
        $logo = getImage($brand->logo);
        $data = ['data' =>  $purchase, 'logo' => $logo, 'brand' => $brand];
        // $htmlContent = view('print.pr', $data)->render();
        // // Convert HTML to PDF
        // $pdf = Pdf::loadHTML($htmlContent);
        // $pdf->setPaper('A4', 'landscape');
        // Return the generated PDF as a stream
        // return $pdf->stream('purchase_requisition.pdf');
        return view('print.pr', $data);
    }

    public function exportPdfNoStamp($purchase_requitition_id = null)
    {
        $purchase = PurchaseRequitition::with(['approvalLeads'])->find($purchase_requitition_id);

        $brand = Brand::find($purchase->brand_id);
        $logo = getImage($brand->logo);
        return view('print.pr-nostamp', ['data' =>  $purchase, 'logo' => $logo, 'brand' => $brand]);
    }

    public function export(Request $request)
    {
        $status = $request->input('status');
        $created_at = $request->input('created_at');
        $query = PurchaseRequitition::query();

        if (!empty($status)) {
            $statusFourKey = array_search('4', $status);

            if ($statusFourKey !== false) {
                // Remove '4' from the status array
                unset($status[$statusFourKey]);

                // Apply the condition for status 4
                $query->where(function ($q) use ($status) {
                    $q->whereNotIn('request_status', [0, 1, 2, 3]);

                    if (!empty($status)) {
                        // Apply the condition for other statuses
                        $q->orWhereIn('request_status', $status);
                    }
                });
            } else {
                $query->whereIn('request_status', $status);
            }
        }

        if (!empty($created_at) && count($created_at) === 2) {
            // Convert the dates from dd-mm-yyyy to yyyy-mm-dd
            $startDate = DateTime::createFromFormat('d-m-Y', $created_at[0])->format('Y-m-d') . ' 00:00:00';
            $endDate = DateTime::createFromFormat('d-m-Y', $created_at[1])->format('Y-m-d') . ' 23:59:00';

            // Apply the date range filter
            if (!empty($status)) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                });
            } else {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $file_name = 'convert/FIS-Purchase_requisition-' . date('d-m-Y') . '.xlsx';

        // Store the export file in S3
        Excel::store(new PurchaseRequititionExport($query), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);

        // Return response with the URL of the stored file
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    // create requisition
    public function createRequitition(Request $request)
    {
        try {
            DB::beginTransaction();
            // Generate unique PR number
            do {
                $generatePrNumber = $this->generatePrNumber();
                $exists = PurchaseRequitition::where('pr_number', $generatePrNumber)->exists();
            } while ($exists);

            $data = [
                'uid_requitition' => hash('crc32', Carbon::now()->format('U')),
                'pr_number' => $generatePrNumber,
                'vendor_code' => $request->vendor_code ?? null,
                'vendor_name' => $request->vendor_name ?? null,
                'brand_id' => $request->brand_id ?? 1,
                'payment_term_id' => $request->payment_term_id,
                'company_account_id' => $request->company_account_id,
                'received_by' => $request->received_by,
                'received_address' => $request->received_address,
                'project_name' => $request->project_name,
                'request_by_name' => $request->request_by_name,
                'range_harga' => $request->range_harga,
                'request_by_email' => $request->request_by_email,
                'request_by_division' => $request->request_by_division,
                'request_date' => date('Y-m-d', strtotime($request->request_date)),
                'request_note' => $request->request_note ?? null,
                'request_status' => $request->status ?? 0,
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ];


            if ($request->attachment) {
                $file = $this->uploadImage($request, 'attachment');
                $data['attachment'] = $file;
            }

            $requisition = PurchaseRequitition::create($data);

            // dd($requisition); die;

            if ($request->items) {
                $items = is_array($request->items) ? $request->items : json_decode($request->items, true);
                $itemData = is_array($items) ? $items : $request->items;
                if (is_array($itemData)) {
                    foreach ($itemData as $key => $item) {
                        $requisition->items()->create([
                            'item_name' => isset($item['item_name']) ? $item['item_name'] : null,
                            'item_qty' => isset($item['item_qty']) ? $item['item_qty'] : null,
                            'item_unit' => isset($item['item_unit']) ? $item['item_unit'] : null,
                            'item_price' => 0,
                            'item_tax' => isset($item['item_tax']) ? $item['item_tax'] : null,
                            'item_url' => isset($item['item_url']) ? $item['item_url'] : null,
                            'item_note' => isset($item['item_note']) ? $item['item_note'] : null,
                        ]);
                    }
                }
            }

            if ($request->approvals) {
                $approvals = is_array($request->approvals) ? $request->approvals : json_decode($request->approvals, true);
                $approvalItems = is_array($approvals) ? $approvals : $request->approvals;
                $payload = json_encode($approvalItems);
                setSetting('purchase_request_submit', $payload);
                if (is_array($approvalItems)) {
                    foreach ($approvalItems as $key => $item) {
                        if (!isset($item['user_id']) || !isset($item['role_id']) || empty($item['role_id'])) {
                            // Skip jika field penting hilang atau null
                            continue;
                        }
                        $label = isset($item['label']) ? $item['label'] : null;
                        $requisition->approvalLeads()->create([
                            'user_id' => $item['user_id'],
                            'role_id' => $item['role_id'],  // role_id seharusnya tidak null disini
                            'status' => $label == 'Verified by' ? 0 : 3,
                            'label' => $label,
                        ]);


                        if (isset($item['user_id'])) {

                            createNotification(
                                'PRA200',
                                [
                                    'user_id' => $item['user_id']
                                ],
                                [
                                    'pr_number' => $requisition->pr_number,
                                ],
                                ['brand_id' => $requisition->brand_id ?? 1]
                            );
                        }
                    }
                }
            }

            // attachment
            $brand = Brand::find($requisition->brand_id);
            $logo = getImage($brand->logo);
            $data = ['data' =>  $requisition, 'logo' => $logo, 'brand' => $brand];
            $htmlContent = view('print.pr', $data)->render();

            // // Convert HTML to PDF
            $pdf = Pdf::loadHTML($htmlContent);
            $pdf->setPaper('A4', 'landscape');

            // Save the PDF to a temporary location
            $pdfPath = storage_path('app/public/purchase_requisition.pdf');
            $pdf->save($pdfPath);

            if ($request->received_by) {
                createNotification(
                    'PR202',
                    [
                        'user_id' => $request->received_by
                    ],
                    [
                        'contact_name' => $requisition->received_by_name,
                        'nomor_pr' => $requisition->pr_number,
                        'request_by_name' =>  $request->request_by_name,
                        'request_division' =>  $request->request_by_division,
                        'brand_name' =>  $requisition->brand_name,
                        'attachment' => route('spa.purchase-purchase-requisition-export-nostamp.index', ['purchase_requisition_id' => $requisition->id])
                    ],
                    [
                        'brand_id' => 1
                    ]
                );
            }


            $users = ['5e170776-39c4-4006-9cdf-9efc3181fe0e', '963b12db-5dbf-4cd5-91f7-366b2123ccb9'];
            foreach ($users as $key => $user) {
                createNotification(
                    'PRA200',
                    [
                        'user_id' => $user
                    ],
                    [
                        'pr_number' => $requisition->pr_number,
                    ],
                    ['brand_id' => $requisition->brand_id ?? 1]
                );
            }

            if ($request->request_by_email) {
                sendEmailSingle(
                    'PR200',
                    [
                        'email' => $request->request_by_email,
                    ],
                    [
                        'name' => $request->request_by_name,
                        'attachment' => route('spa.purchase-purchase-requisition-export-nostamp.index', ['purchase_requisition_id' => $requisition->id])
                    ],
                    [
                        'brand_id' => 1
                    ]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_requisition',
                'log_description' => 'Create Purchase Requisition - ' . $requisition->pr_number,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'message' => 'Requisition created successfully',
                'data' => $requisition
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Requisition failed to create',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function getPerlengkapanProduct(Request $request) {}

    // generate po number auto increment with format PO-0001
    // public function generatePrNumber()
    // {
    //     $lastPo = PurchaseRequitition::orderBy('id', 'desc')->first();
    //     $number = '0001';
    //     if ($lastPo) {
    //         $number = substr($lastPo->pr_number, -4);
    //         $number = (int) $number + 1;
    //         $number = sprintf("%04d", ((int)$number));
    //     }
    //     return 'PR-' . $number;
    // }

    // generate PR number with format PR-ddmmyyXXXX
    public function generatePrNumber()
    {
        $datePrefix = date('dmY'); // Get the current date in ddmmyy format
        $prefix = 'PR-' . $datePrefix;

        // Find the last PR number with the current date prefix
        $lastPo = PurchaseRequitition::where('pr_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $number = '0001'; // Default number if no previous PR is found

        if ($lastPo) {
            $lastNumber = substr($lastPo->pr_number, -4); // Get the last 4 digits
            $number = sprintf("%04d", ((int) $lastNumber + 1)); // Increment and format the number
        }

        return $prefix . $number;
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
        $file = Storage::disk('s3')->put('upload/purchase-requisition', $request[$path], 'public');
        return $file;
    }

    public function getPrComplete()
    {
        $purchase_requisition = PurchaseRequitition::where('request_status', 2)->whereNull('is_po_created')->get();
        return response()->json([
            'status' => 'success',
            'data' => $purchase_requisition,
            'message' => 'List Purchase Requitition Complete'
        ]);
    }
}
