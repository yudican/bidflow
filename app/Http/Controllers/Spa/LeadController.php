<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Exports\LeadSkuExport;
use App\Jobs\UpdatePriceQueue;
use App\Models\Brand;
use App\Models\CompanyAccount;
use App\Models\InventoryItem;
use App\Models\LeadActivity;
use App\Models\LeadHistory;
use App\Models\LeadMaster;
use App\Models\LeadNegotiation;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\ProductNeed;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    public function index($uid_lead = null)
    {
        return view('spa.spa-index');
    }

    public function listLead(Request $request)
    {
        $search = $request->search;
        $contact = $request->contact;
        $sales = $request->sales;
        $created_at = $request->created_at;
        $status = $request->status;
        $brand = $request->brand;
        $user = auth()->user();
        $role = $user->role->role_type;
        $account_id = $request->account_id;
        $lead =  LeadMaster::query();

        if ($search) {
            $lead->where('order_number', 'like', "%$search%");
            $lead->orWhereHas('contactUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $lead->orWhereHas('salesUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $lead->orWhereHas('createUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
        }

        if ($contact) {
            $lead->whereIn('contact', $contact);
        }

        if ($sales) {
            $lead->whereIn('sales', $sales);
        }

        if ($status) {
            $lead->whereIn('status', $status);
        }

        if ($brand) {
            $lead->whereIn('brand_id', $brand);
        }

        if ($created_at) {
            $lead->whereBetween('created_at', $created_at);
        }

        // cek switch account
        if ($account_id) {
            $lead->where('company_id', $account_id);
        }

        if ($role == 'sales') {
            $lead->where('user_created', $user->id)->orWhere('sales', $user->id);
        }

        $leads = $lead->orderBy('created_at', 'desc')->select('id', 'uid_lead', 'title', 'created_at', 'contact', 'sales', 'brand_id', 'user_created', 'status')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $leads
        ]);
    }

    public function detailLead($uid_lead)
    {
        $lead = LeadMaster::with([
            'contactUser',
            'contactUser.company',
            'salesUser',
            'createUser',
            'leadActivities',
            'leadNegotiations',
            'productNeeds',
            'productNeeds.product',
            'productNeeds.tax',
            'paymentTerm',
        ])->where('uid_lead', $uid_lead)->first();

        return response()->json([
            'status' => 'success',
            'data' => $lead
        ]);
    }

    public function createLead(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $role_type = $user->role->role_type;
            $role = 'SA';
            $brand = 'MULTIPLE';
            $sales = $request->sales;
            $account_id = $request->account_id;

            $company = CompanyAccount::where('status', 1)->first();

            if (count($request->brand_id) == 1) {
                $brand = Brand::find($request->brand_id[0]);
                $brand =  $brand ? strtoupper(@$brand->name) : 'FLIMTY';
            }

            if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
                $role = 'AD';
            }

            if (auth()->user()->role->role_type == 'sales') {
                $sales = auth()->user()->id;
            }
            $companyId = $company ? $company->id : null;
            if ($account_id) {
                $companyId = $account_id;
            }

            $data = [
                'brand_id'  => $request->brand_id[0],
                'title'  => $this->generateTitle($brand, $role),
                'uid_lead' => generateUid(),
                'contact'  => $request->contact,
                'sales'  => $sales,
                'lead_type'  => $request->lead_type,
                'warehouse_id'  => $request->warehouse_id,
                'payment_term'  => $request->payment_term,
                'customer_need'  => $request->customer_need,
                'company_id'  => $companyId,
                'status'  => $request->status,
                'address_id'  => $request->address_id,
                'master_bin_id'  => $request->master_bin_id,
                'user_created' => auth()->user()->id,
                'expired_at' =>  $request->expired_at,
            ];

            $row = LeadMaster::create($data);
            $row->brands()->attach($request->brand_id);

            if ($request->status == 0) {
                if (!empty($request->sales)) {
                    LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $data['uid_lead'], 'description' => 'Create new lead']);
                }

                if ($row->salesUser) {
                    createNotification(
                        'ANL200',
                        [
                            'user_id' => $request->sales
                        ],
                        [
                            'sales' => @$row->salesUser->name,
                            'assign_by' => auth()->user()->name,
                            'lead_title' => $row->title,
                            'date_assign' => $row->created_at,
                            'due_date' => $row->created_at->addDays(1),
                            'contact' => $row->contact_name_only,
                            'company' => $row->company_name,
                            'status_lead' => getStatusLead($row->status),
                        ],
                        ['brand_id' => $row->brand_id]
                    );
                    createNotification(
                        'NLAA200',
                        [],
                        [
                            'sales' => @$row->salesUser->name,
                            'assign_by' => auth()->user()->name,
                            'lead_title' => $row->title,
                            'date_assign' => $row->created_at,
                            'due_date' => $row->created_at->addDays(1),
                            'contact' => $row->contact_name_only,
                            'company' => $row->company_name,
                            'status_lead' => getStatusLead($row->status),
                        ],
                        ['brand_id' => $row->brand_id]
                    );
                }
            }

            if ($request->status == 7) {
                if (!empty($request->sales)) {
                    LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $data['uid_lead'], 'description' => 'Create new lead']);
                }
                createNotification(
                    'NLAA200',
                    [],
                    [
                        'sales' => @$row->salesUser->name,
                        'assign_by' => auth()->user()->name,
                        'lead_title' => $row->title,
                        'date_assign' => $row->created_at,
                        'due_date' => $row->created_at->addDays(1),
                        'contact' => $row->contact_name_only,
                        'company' => $row->company_name,
                        'status_lead' => getStatusLead($row->status),
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Lead Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lead Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $uid_lead)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $role_type = $user->role->role_type;
            $role = 'SA';
            $brand = 'MULTIPLE';
            if (count($request->brand_id) == 1) {
                $brands = Brand::find($request->brand_id[0]);
                $brand =  $brands ? strtoupper($brands->name) : 'FLIMTY';
            }

            if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
                $role = 'AD';
            }
            $data = [
                'brand_id'  => $request->brand_id[0],
                'contact'  => $request->contact,
                'sales'  => $request->sales,
                'lead_type'  => $request->lead_type,
                'customer_need'  => $request->customer_need,
                'address_id'  => $request->address_id,
                'master_bin_id'  => $request->master_bin_id,
                'status'  => 0
            ];
            $row = LeadMaster::where('uid_lead', $uid_lead)->first();
            // $data['title'] = $request->title;
            // if ($row->title != $request->title) {
            //     $data['title'] = $this->generateTitle($brand, $role);
            // }
            $row->update($data);
            $row->brands()->sync($request->brand_id);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Lead Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lead Gagal Diupdate'
            ], 400);
        }
    }

    public function storeActivity(Request $request)
    {
        $start_date = date('Y-m-d H:i:s', strtotime($request->start_date));
        $end_date = date('Y-m-d H:i:s', strtotime($request->end_date));
        if ($start_date != $end_date) {
            $data = [
                'uid_lead'  => $request->uid_lead,
                'title'  => $request->title,
                'description'  => $request->description ?? '-',
                'start_date'  => $request->start_date,
                'end_date'  => $request->end_date,
                'start_time' => date('H:i:s', strtotime($request->start_date)),
                'end_time' => date('H:i:s', strtotime($request->start_date)),
                'result'  => $request->result,
                'latitude'  => $request->latitude,
                'longitude'  => $request->longitude,
                'geo_tagging'  => $request->address_name,
                'status'  => 1,
                'user_created'  => auth()->user()->id,
                'user_updated'  => auth()->user()->id
            ];

            if ($request->attachment) {
                $file = $this->uploadImage($request, 'attachment');
                $data['attachment'] = $file;
            }

            $activity = LeadActivity::create($data);

            $row = LeadMaster::where('uid_lead', $request->uid_lead);
            $last_date = date('Y-m-d', strtotime($request->start_date . ' + 14 days'));
            $data2['status_update'] = $last_date;
            $row->update($data2);

            if ($activity->leadMaster) {
                createNotification(
                    'NAC200',
                    [
                        'user_id' => $request->sales
                    ],
                    [
                        'sales' => $activity->leadMaster->salesUser->name,
                        'lead_title' => $activity->leadMaster->title,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'created_at' => $activity->created_at,
                        'contact' => $activity->leadMaster->contactUser->name,
                        'company' => $activity->leadMaster->brand->name,
                        'result' => $activity->result,
                    ],
                    ['brand_id' => $activity->leadMaster->brand_id]
                );
                createNotification(
                    'NALA200',
                    [
                        'user_id' => $request->sales
                    ],
                    [
                        'sales' => $activity->leadMaster->salesUser->name,
                        'assign_by' => auth()->user()->name,
                        'lead_title' => $activity->leadMaster->title,
                        'date_assign' => $activity->leadMaster->created_at,
                        'contact' => $activity->leadMaster->contactUser->name,
                        'company' => $activity->leadMaster->brand->name,
                        'status_lead' => getStatusLead($activity->leadMaster->status),
                    ],
                    ['brand_id' => $activity->leadMaster->brand_id]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Activity Berhasil Disimpan'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Start Date & End Date Tidak Boleh Sama'
            ], 400);
        }
    }

    public function updateActivity(Request $request, $activity_id)
    {
        $data = [
            'title'  => $request->title,
            'description'  => $request->description ?? '-',
            'start_date'  => date('Y-m-d H:i:s', strtotime($request->start_date)),
            'end_date'  => date('Y-m-d H:i:s', strtotime($request->end_date)),
            'result'  => $request->result,
            'user_updated'  => auth()->user()->id
        ];

        if ($request->attachment) {
            $file = $this->uploadImage($request, 'attachment');
            $data['attachment'] = $file;
        }

        $activity = LeadActivity::find($activity_id);

        $activity->update($data);

        $row = LeadMaster::where('uid_lead', $request->uid_lead);
        $last_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 14 days'));
        $data2['status_update'] = $last_date;
        $row->update($data2);

        if ($activity->leadMaster) {
            createNotification(
                'NAU200',
                [
                    'user_id' => $request->sales
                ],
                [
                    'sales' => $activity->leadMaster->salesUser->name,
                    'lead_title' => $activity->leadMaster->title,
                    'created_at' => $activity->created_at,
                    'contact' => $activity->leadMaster->contactUser->name,
                    'company' => $activity->leadMaster->brand->name,
                    'status_update' => getStatusLead($activity->status),
                    'activity' => $activity->title,
                ],
                ['brand_id' => $activity->leadMaster->brand_id]
            );
        }
    }

    public function deleteActivity($activity_id)
    {
        $activity = LeadActivity::find($activity_id);
        $activity->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Activity Berhasil Dihapus'
        ]);
    }

    // public function selectProductItems(Request $request)
    // {
    //     $data = ['uid_lead' => $request->uid_lead];

    //     if ($request->item_id) {
    //         $data['id'] = $request->item_id;
    //     }

    //     $price = $request->price_nego ?? $request->final_price;

    //     ProductNeed::updateOrCreate($data, [
    //         'uid_lead' => $request->uid_lead,
    //         'product_id' => $request->product_id,
    //         'qty' => $request->qty,
    //         'price' => $request->discount_id ? 0 : $price,
    //         'discount_id' => $request->discount_id,
    //         'tax_id' => $request->tax_id,
    //         'price_type' => 'product'
    //     ]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Product berhasil ditambahkan',
    //     ]);
    // }

    public function selectProductItems(Request $request)
    {
        $data = ['uid_lead' => $request->uid_lead];

        if ($request->item_id) {
            $data['id'] = $request->item_id;
        }

        // $price = $request->price_nego ?? $request->final_price;
        // if ($price < 1) {
        //     if (!$request->discount_id) {
        //         $product = ProductVariant::where('id', $request->product_id)->first();
        //         if ($product) {
        //             $price = $product->price['final_price'];
        //         }
        //     }
        // }

        ProductNeed::updateOrCreate($data, [
            'uid_lead' => $request->uid_lead,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'price' => $request->price_nego,
            'discount' => $request->discount,
            'tax_id' => $request->tax_id,
            'user_updated' => Auth::user()->id,
            'status' => 9,
            'price_type' => 'product'
        ]);

        if ($request->tax_id) {
            ProductNeed::where('uid_lead', $request->uid_lead)->update(['tax_id' => $request->tax_id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil ditambahkan',
            'product' => null
        ]);
    }

    public function addProductItem(Request $request)
    {
        $checkProductNeed = ProductNeed::whereNotNull('tax_id')->where('uid_lead', $request->uid_lead)->first(['tax_id']);
        $product_need = $request->all();
        $product_need['user_created'] = Auth::user()->id;
        $product_need['user_updated'] = Auth::user()->id;
        if ($checkProductNeed) {
            $product_need['tax_id'] = $checkProductNeed->tax_id;
        }
        // $product_need['price_type'] = 'manual';
        ProductNeed::create($product_need);

        return response()->json([
            'status' => 'success',
            'message' => 'Item Product berhasil ditambahkan!'
        ]);
    }

    public function deleteProductItem(Request $request)
    {
        ProductNeed::find($request->item_id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil dihapus'
        ]);
    }

    public function addQty(Request $request)
    {
        $item = ProductNeed::find($request->item_id);
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
        $item = ProductNeed::find($request->item_id);
        if ($item->qty > 1) {
            $item->decrement('qty');
        }
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function reject(Request $request)
    {
        try {
            DB::beginTransaction();
            $row = LeadMaster::where('uid_lead', $request->uid_lead)->first();
            $data = ['status'  => 6, 'status_negotiation'  => 3, 'approval_notes' => $request->notes]; //reject
            LeadNegotiation::create(['uid_lead' => $request->uid_lead, 'notes' => $request->notes, 'status' => 2]);
            LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $request->uid_lead, 'description' => 'Lead rejected']);

            createNotification(
                'LQR200',
                [
                    'user_id' => $row->sales
                ],
                [
                    'sales' => $row->sales_name,
                    'lead_title' => $row->title,
                    'created_at' => $row->created_at,
                    'contact' => $row->contact_name,
                    'company' => $row->brand_name,
                    'qualified_by' => auth()->user()->name,
                    'date_qualified' => date('Y-m-d H:i:s'),
                    'status_updated' => 'Not Qualified',
                ],
                ['brand_id' => $row->brand_id]
            );

            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Lead berhasil di reject'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lead gagal di reject',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function approve(Request $request)
    {
        try {
            DB::beginTransaction();
            $row = LeadMaster::where('uid_lead', $request->uid_lead)->first();
            $data = ['status'  => 1, 'status_negotiation'  => 1, 'approval_notes' => '']; //approve
            LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $request->uid_lead, 'description' => 'Lead approved']);
            // disini insert ke order lead
            $check = OrderLead::where('uid_lead', $request->uid_lead)->first();
            if (empty($check)) {
                //disini insert ke order lead
                $order_number = $this->generateOrderNo();
                $dueDate = Carbon::now()->addDays(7);
                if ($row->paymentTerm) {
                    $dueDate = Carbon::now()->addDays($row->paymentTerm->days_of);
                }
                $kode_unik = $this->getUniqueCodeLead();
                $data_order = [
                    'brand_id'  => $row->brand_id,
                    'title'  => $row->title,
                    'uid_lead' => $row->uid_lead,
                    'contact'  => $row->contact,
                    'sales'  => $row->sales,
                    'customer_need'  => $row->customer_need,
                    'status'  => 1,
                    'user_created' => $row->user_created,
                    'warehouse_id' => $row->warehouse_id,
                    'payment_term' => $row->payment_term,
                    'order_number' => $order_number,
                    'invoice_number' => $this->generateInvoiceNo(),
                    'due_date' => $dueDate,
                    'kode_unik' => $kode_unik,
                    'temp_kode_unik' => $kode_unik,
                    'company_id' => $row->company_id,
                    'address_id'  => $row->address_id,
                    'master_bin_id'  => $row->master_bin_id,
                ];
                OrderLead::updateorCreate(['uid_lead' => $request->uid_lead], $data_order);
                createNotification(
                    'AGO200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser?->name ?? '-',
                        'order_number' => $order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser?->name ?? '-',
                        'assign_by' => auth()->user()->name,
                        'status' => 'Qualified',
                        'courier_name' => '-',
                        'receiver_name' => '-',
                        'shipping_address' => '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );

                LeadNegotiation::create(['uid_lead' => $request->uid_lead, 'notes' => 'Negotiation Berhasil Di approve oleh ' . auth()->user()->name, 'status' => 1]);
            }
            createNotification(
                'LQA200',
                [
                    'user_id' => $row->sales
                ],
                [
                    'sales' => $row->salesUser?->name ?? '-',
                    'lead_title' => $row->title,
                    'created_at' => $row->created_at,
                    'contact' => $row->contactUser?->name ?? '-',
                    'company' => $row->brand?->name ?? '-',
                    'qualified_by' => auth()->user()->name,
                    'date_qualified' => date('Y-m-d H:i:s'),
                    'status_updated' => 'Qualified',
                ],
                ['brand_id' => $row->brand_id]
            );

            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Lead berhasil diapprove'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lead gagal diapprove',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function saveNegotiation(Request $request)
    {
        $lead = LeadMaster::where('uid_lead', $request->uid_lead)->first();
        if (!$lead) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead tidak ditemukan'
            ], 400);
        }

        $data = [
            'is_negotiation'  => null,
            'nego_value'  => $lead->total_negotiation,
            'status_negotiation'  => 1, //qualified
            'status'  => 1, //approvel / qualified
        ];



        if ($lead->total_negotiation > 0) {
            if ($lead->total_negotiation < $lead->margin_total) {
                $data = [
                    'is_negotiation'  => 1,
                    'nego_value'  => $lead->total_negotiation,
                    'status_negotiation'  => 2, //pending
                    'status'  => 2, //waiting approvel
                ];

                LeadNegotiation::create(['uid_lead' => $request->uid_lead, 'notes' => 'Mengirim Negotiation Baru', 'status' => 0]);
            } else {
                $order_number = $this->generateOrderNo();
                $dueDate = Carbon::now()->addDays(7);
                if ($lead->paymentTerm) {
                    $dueDate = Carbon::now()->addDays($lead->paymentTerm->days_of);
                }

                $data_order = [
                    'brand_id'  => $lead->brand_id,
                    'title'  => $lead->title,
                    'uid_lead' => $lead->uid_lead,
                    'contact'  => $lead->contact,
                    'sales'  => $lead->sales,
                    'customer_need'  => $lead->customer_need,
                    'status'  => 1,
                    'user_created' => $lead->user_created,
                    'warehouse_id' => $lead->warehouse_id,
                    'payment_term' => $lead->payment_term,
                    'order_number' => $order_number,
                    'invoice_number' => $this->generateInvoiceNo(),
                    'address_id' => $lead->address_id,
                    'notes' => $lead->notes,
                    'expired_at' => $lead->expired_at,
                    'due_date' => $dueDate,
                    'company_id' => $lead->company_id,
                    'type' => 'lead'
                ];
                $order = OrderManual::create($data_order);

                UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            }
        }

        $lead->update($data);
        createNotification(
            'LOR200',
            [],
            [],
            ['brand_id' => $lead->brand_id]
        );
        return response()->json([
            'status' => 'success',
            'message' => 'Lead berhasil disimpan'
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

    private function generateTitle($brand = 'FLIMTY', $role)
    {
        $date = date('m/Y');
        $brand = strtoupper(str_replace(' ', '-', $brand));
        $title = 'LEAD/' . $brand . '/' . $role . '-' . $date;
        $rw = LeadMaster::where('title', 'like', '%' . $title . '%')->whereNotNull('title')->orderBy('created_at', 'desc')->first();
        $count_code = 8 + strlen($brand) + strlen($role) + strlen($date);
        if ($rw) {
            $awal = substr($rw->title, $count_code);
            $next = sprintf("%03d", ((int)$awal + 1));
            $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . $next;
        } else {
            $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . '001';
        }
        return $nomor;
    }

    private function generateOrderNo()
    {
        $year = date('Y');
        $order_number = 'SO/' . $year . '/';
        $nomor = 'SO/' . $year . '/' . '1000000001';
        $rw = OrderLead::where('order_number', 'like', '%' . $order_number . '%')->whereNotNull('order_number')->orderBy('id', 'desc')->first();
        if ($rw) {
            $awal = substr($rw->order_number, -9);
            $next = '1' . sprintf("%09d", ((int)$awal + 1));
            $nomor = 'SO/' . $year . '/' . $next;
        }
        return $nomor;
    }

    private function generateInvoiceNo()
    {
        $year = date('Y');
        $invoice_number = 'SI/' . $year . '/';
        $nomor = 'SI/' . $year . '/' . '1000000001';
        $rw = OrderLead::where('invoice_number', 'like', '%' . $invoice_number . '%')->whereNotNull('invoice_number')->orderBy('id', 'desc')->first();
        if ($rw) {
            $awal = substr($rw->invoice_number, -9);
            $next = '1' . sprintf("%09d", ((int)$awal + 1));
            $nomor = 'SI/' . $year . '/' . $next;
        }
        return $nomor;
    }

    // get unique code 3 digit max 500 with auto increment
    private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
    {
        return 0;
        $data = OrderLead::whereDate('created_at', date('Y-m-d'))->select($field)->orderBy('id', 'desc')->limit(1)->first();
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

    public function export(Request $request)
    {
        $file_name = 'convert/data-lead-master-' . date('Y-m-d') . '.xlsx';

        Excel::store(new LeadSkuExport($request->items), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
