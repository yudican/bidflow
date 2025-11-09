<?php

namespace App\Http\Livewire;

use App\Models\LeadMaster;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\PaymentTerm;
use App\Models\Role;
use App\Models\LeadActivity;
use App\Models\ProductNeed;
use App\Models\LeadNegotiation;
use App\Models\LeadHistory;
use App\Models\OrderLead;
use App\Models\Product;
use App\Models\Brand;
use App\Models\MarginBottom;
use App\Models\ProductVariant;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class LeadMasterController extends Component
{
    use WithFileUploads;
    public $tbl_lead_masters_id;
    public $uid_lead;
    public $title;
    public $description;
    public $contact;
    public $sales;
    public $lead_type;
    public $customer_need;
    public $status;
    public $user_created;
    public $attachment;
    public $attachment_path;
    public $activity_id;
    public $result;
    public $start_date;
    public $start_time;
    public $end_date;
    public $end_time;
    public $brand_id;
    public $payment_term;
    public $warehouse_id;
    public $reminder;
    public $type_customer;

    public $products = [];
    public $product_id = [''];
    public $qty = [1];
    public $price = [''];
    public $harga_satuan = [''];
    public $margin_bottom = [''];
    public $harga_total = [''];
    public $nego_value;
    public $approval_notes;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;
    public $view = false;
    public $selectedContact = [];
    public $search_contact = null;

    public $filter_contact = 'all';
    public $filter_sales = 'all';
    public $filter_status = 'all';

    // dinamic form
    public $inputs = [0];
    public $i;

    public $active_tab = 1;
    public $lead = 1;

    public $loading = true;


    protected $listeners = ['getDataLeadMasterById', 'getLeadMasterId', 'getDetailById', 'getDetailApprove', 'getDataLeadActivityById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d', strtotime($this->start_date . ' + 14 days'));
    }

    public function init()
    {
        $this->loading = false;
        $this->emit('initData');
    }

    public function render()
    {

        $activity = [];
        if ($this->uid_lead) {
            $activity = LeadActivity::where('uid_lead', $this->uid_lead)->get();
        }
        $productneeds = [];
        if ($this->uid_lead) {
            $productneeds = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        }
        return view('livewire.tbl-lead-masters', [
            'activity' => $activity,
            'productneeds' => $productneeds,
        ]);
    }

    public function store()
    {
        if (auth()->user()->role->role_type == 'sales') {
            $this->validate([
                'brand_id' => 'required'
            ]);
        } else {
            $this->validate([
                'brand_id' => 'required',
                'sales' => 'required'
            ]);
        }
        try {

            DB::beginTransaction();
            $user = auth()->user();
            $role_type = $user->role->role_type;
            $role = 'SA';
            $brand = 'MULTIPLE';
            $sales = $this->sales;

            if (count($this->brand_id) == 1) {
                $brand = Brand::find($this->brand_id[0]);
                $brand =  $brand ? strtoupper(@$brand->name) : 'FLIMTY';
            }

            if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
                $role = 'AD';
            }

            if (auth()->user()->role->role_type == 'sales') {
                $sales = auth()->user()->id;
            }

            $data = [
                'brand_id'  => $this->brand_id[0],
                'title'  => $this->generateTitle($brand, $role),
                'uid_lead' => hash('crc32', Carbon::now()->format('U')),
                'contact'  => $this->contact,
                'sales'  => $sales,
                'lead_type'  => $this->lead_type,
                'warehouse_id'  => $this->warehouse_id,
                'payment_term'  => $this->payment_term,
                'customer_need'  => $this->customer_need,
                'status'  => 0,
                'user_created' => auth()->user()->id,
            ];

            $row = LeadMaster::create($data);
            $row->brands()->attach($this->brand_id);

            if (!empty($this->sales)) {
                LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $data['uid_lead'], 'description' => 'Create new lead']);
            }
            // $role = Role::where('role_type', 'sales')->first();
            // createNotification('LEAD200', ['user_id' => $this->sales, 'role_id' => $role->id]);
            if ($row->salesUser) {
                createNotification(
                    'ANL200',
                    [
                        'user_id' => $this->sales
                    ],
                    [
                        'sales' => @$row->salesUser->name,
                        'assign_by' => auth()->user()->name,
                        'lead_title' => $row->title,
                        'date_assign' => $row->created_at,
                        'due_date' => $row->created_at->addDays(1),
                        'contact' => @$row->contactUser->name,
                        'company' => @$row->brand->name,
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
                        'contact' => @$row->contactUser->name,
                        'company' => @$row->brand->name,
                        'status_lead' => getStatusLead($row->status),
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            print_r($th->getMessage());
            die();
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function store_activity()
    {
        $this->validate([
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required',
        ]);

        if (date('Y-m-d H:i', strtotime($this->start_date . ' ' . $this->start_time)) != date('Y-m-d H:i', strtotime($this->end_date . ' ' . $this->end_time))) {

            $data = [
                'uid_lead'  => $this->uid_lead,
                'title'  => $this->title,
                'description'  => $this->description,
                'start_date'  => date('Y-m-d H:i:s', strtotime($this->start_date)),
                'end_date'  => date('Y-m-d H:i:s', strtotime($this->end_date)),
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'result'  => $this->result,
                'status'  => 1,
                'user_created'  => auth()->user()->id,
                'user_updated'  => auth()->user()->id
            ];

            if ($this->attachment_path) {
                $attachment = Storage::disk('s3')->put('upload/activity_attachment', $this->attachment_path, 'public');
                // $attachment = $this->attachment_path->store('upload', 'public');
                $data['attachment'] = $attachment;
            }

            $activity = LeadActivity::create($data);

            $row = LeadMaster::where('uid_lead', $this->uid_lead);
            $last_date = date('Y-m-d', strtotime($this->start_date . ' + 14 days'));
            $data2['status_update'] = $last_date;
            $row->update($data2);

            if ($activity->leadMaster) {
                createNotification(
                    'NAC200',
                    [
                        'user_id' => $this->sales
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
                    [],
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

            $this->emit('closeModal');
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } else {
            $this->emit('showAlertError', ['msg' => 'Start Date & End Date Tidak Boleh Sama!']);
        }
    }

    public function update_activity()
    {
        $this->_validate();
        $data = [
            'title'  => $this->title,
            'description'  => $this->description,
            'start_date'  => date('Y-m-d H:i:s', strtotime($this->start_date)),
            'end_date'  => date('Y-m-d H:i:s', strtotime($this->end_date)),
            'result'  => $this->result,
            'user_updated'  => auth()->user()->id
        ];

        if ($this->attachment_path) {
            $attachment = $this->attachment_path->store('upload', 'public');
            $data['attachment'] = $attachment;
            if (Storage::exists('public/' . $this->attachment)) {
                Storage::delete('public/' . $this->attachment);
            }
        }

        $activity = LeadActivity::find($this->activity_id);

        $activity->update($data);

        $row = LeadMaster::where('uid_lead', $this->uid_lead);
        $last_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 14 days'));
        $data2['status_update'] = $last_date;
        $row->update($data2);

        if ($activity->leadMaster) {
            createNotification(
                'NAU200',
                [
                    'user_id' => $this->sales
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
        $this->_reset();
        $this->active_tab = 1;
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function getPrice($product_id, $index = 0, $type = null)
    {
        $product = ProductVariant::find($product_id);
        if ($product) {
            $this->harga_satuan[$index] = number_format($product->price['final_price']);
            $check_margin = MarginBottom::where('product_variant_id', $product->id)->first();
            $this->margin_bottom[$index] = $check_margin ? $check_margin->margin : 0;
            if ($type) {
                if ($type == 'min') {
                    if ($this->qty[$index] > 1) {
                        $this->qty[$index] -= 1;
                        $this->harga_total[$index] = number_format($product->price['final_price'] * $this->qty[$index]);
                    }
                } else if ($type == 'plus') {
                    $this->qty[$index] += 1;
                    $this->harga_total[$index] = number_format($product->price['final_price'] * $this->qty[$index]);
                }
            } else {
                $this->harga_total[$index] = number_format($product->price['final_price'] * $this->qty[$index]);
            }
        }
    }

    public function store_product()
    {
        $margin = array();

        foreach ($this->inputs as $key => $value) {
            ProductNeed::updateOrCreate([
                'uid_lead' => $this->uid_lead,
                'product_id' => $this->product_id[$key]
            ], [
                'uid_lead' => $this->uid_lead,
                'product_id' => $this->product_id[$key],
                'qty' => $this->qty[$key],
                'price' => ($this->price[$key] == 0 || $this->price[$key] == '') ? str_replace(",", "", $this->harga_total[$key]) : $this->price[$key],
                'status' => 1,
                'user_created' => auth()->user()->id,
            ]);

            $check_margin = MarginBottom::where('product_variant_id', $this->product_id[$key])->first();
            $margin_val = (empty($check_margin)) ? 0 : $check_margin->margin;
            $total_margin = $this->qty[$key] * $margin_val;
            $total_nego = $this->qty[$key] * (float) $this->price[$key];

            if ($total_nego >= $total_margin) {
                if (empty($this->price[$key]) || $this->price[$key] == '0') {
                    array_push($margin, 1);
                }
                array_push($margin, 0);
            } else {
                array_push($margin, 1);
            }
        }

        $productneed = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        $inputs = [];
        foreach ($productneed as $key => $cs) {
            $inputs[] = $cs->id;
        }
        $this->inputs = $inputs;

        if (!empty($this->nego_value) || $this->nego_value > 0) {
            $data = [
                'is_negotiation'  => 1,
                'nego_value'  => $this->nego_value,
                'status_negotiation'  => 2, //pending
            ];
            $row = LeadMaster::where('uid_lead', $this->uid_lead);
            $row->update($data);
            createNotification(
                'NLA200',
                [
                    'user_id' => $this->sales
                ],
                [
                    'sales' => $row->salesUser->name,
                    'lead_title' => $row->title,
                    'created_at' => $row->created_at,
                    'contact' => $row->contactUser->name,
                    'company' => $row->brand->name,
                    'approved_by' => auth()->user()->name,
                    'date_approved' => date('Y-m-d H:i:s'),
                    'status_approval' => 'Lead approved',
                    'note' => $this->approval_notes,
                ],
                ['brand_id' => $row->brand_id]
            );
            createNotification(
                'ALNA200',
                [],
                [
                    'sales' => $row->salesUser->name,
                    'assign_by' => auth()->user()->name,
                    'lead_title' => $row->title,
                    'date_assign' => $row->created_at,
                    'contact' => $row->contactUser->name,
                    'company' => $row->brand->name,
                    'status_lead' => getStatusLead($row->status),
                ],
                ['brand_id' => $row->brand_id]
            );
        }

        if (in_array(0, $margin) || empty($this->price[$key])) {
            $data = [
                'status'  => 1, //qualified
            ];
            $row = LeadMaster::where('uid_lead', $this->uid_lead);
            $row->update($data);

            $master = LeadMaster::where('uid_lead', $this->uid_lead)->first();
            //disini insert ke order lead
            $data_order = [
                'brand_id'  => @$master->brand_id,
                'title'  => $master->title,
                'uid_lead' => $master->uid_lead,
                'contact'  => $master->contact,
                'sales'  => $master->sales,
                'customer_need'  => $master->customer_need,
                'status'  => 1,
                'user_created' => $master->user_created,
                'warehouse_id' => $master->warehouse_id,
                'payment_term' => $master->payment_term,
                'order_number' => $this->generateOrderNo(),
                'invoice_number' => $this->generateInvoiceNo()
            ];
            OrderLead::create($data_order);
        }

        $this->_reset();
        $this->active_tab = 2;
        $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        return redirect(route('lead-master'));
    }

    public function assign_admin()
    {
        $data = ['status'  => 2]; //waiting approval
        $row = LeadMaster::where('uid_lead', $this->uid_lead);
        $row->update($data);

        LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $this->uid_lead, 'description' => 'Assign to admin']);
        //ini error notifnya
        // createNotification('ASAR200', ['user_id' => @$row->sales], ['sales_name' => @$row->salesUser->name, 'lead_title' => $row->title, 'request_date' => $row->created_at]);

        $this->_reset();
        $this->active_tab = 2;
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function approve($status)
    {
        try {
            DB::beginTransaction();
            $row = LeadMaster::where('uid_lead', $this->uid_lead)->first();
            if ($status == 1) {
                $data = ['status'  => 1, 'status_negotiation'  => $status, 'approval_notes' => $this->approval_notes]; //approve
                LeadNegotiation::create(['uid_lead' => $this->uid_lead, 'notes' => $this->approval_notes, 'status' => 1]);
                LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $this->uid_lead, 'description' => 'Lead approved']);
                // disini insert ke order lead
                $check = OrderLead::where('uid_lead', $this->uid_lead)->first();
                if (empty($check)) {
                    //disini insert ke order lead
                    $order_number = $this->generateOrderNo();
                    $dueDate = Carbon::now()->addDays(7);
                    if ($row->paymentTerm) {
                        $dueDate = Carbon::now()->addDays($row->paymentTerm->days_of);
                    }
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
                    ];
                    OrderLead::create($data_order);
                    createNotification(
                        'AGO200',
                        [
                            'user_id' => $this->sales
                        ],
                        [
                            'user' => $row->salesUser->name,
                            'order_number' => $order_number,
                            'title_order' => $row->title,
                            'created_on' => $row->created_at,
                            'contact' => $row->contactUser->name,
                            'assign_by' => auth()->user()->name,
                            'status' => 'Qualified',
                            'courier_name' => '-',
                            'receiver_name' => '-',
                            'shipping_address' => '-',
                            'detail_product' => detailProductOrder($row->productNeeds),
                        ],
                        ['brand_id' => $row->brand_id]
                    );
                }
                createNotification(
                    'LQA200',
                    [
                        'user_id' => $this->sales
                    ],
                    [
                        'sales' => $row->salesUser->name,
                        'lead_title' => $row->title,
                        'created_at' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'company' => $row->brand->name,
                        'qualified_by' => auth()->user()->name,
                        'date_qualified' => date('Y-m-d H:i:s'),
                        'status_updated' => 'Qualified',
                    ],
                    ['brand_id' => $row->brand_id]
                );
            } else {
                $data = ['status'  => 6, 'status_negotiation'  => $status, 'approval_notes' => $this->approval_notes]; //reject
                LeadNegotiation::create(['uid_lead' => $this->uid_lead, 'notes' => $this->approval_notes, 'status' => 2]);
                LeadHistory::create(['user_id' => auth()->user()->id, 'uid_lead' => $this->uid_lead, 'description' => 'Lead rejected']);

                createNotification(
                    'LQR200',
                    [
                        'user_id' => $this->sales
                    ],
                    [
                        'sales' => $row->salesUser->name,
                        'lead_title' => $row->title,
                        'created_at' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'company' => $row->brand->name,
                        'qualified_by' => auth()->user()->name,
                        'date_qualified' => date('Y-m-d H:i:s'),
                        'status_updated' => 'Not Qualified',
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }

            $row->update($data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function update()
    {
        $this->validate([
            'brand_id' => 'required',
            'sales' => 'required',
            'contact' => 'required',
            // 'customer_need' => 'required',
        ]);
        $user = auth()->user();
        $role_type = $user->role->role_type;
        $role = 'SA';
        $brand = 'MULTIPLE';
        if (count($this->brand_id) == 1) {
            $brands = Brand::find($this->brand_id[0]);
            $brand =  $brands ? strtoupper($brands->name) : 'FLIMTY';
        }

        if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
            $role = 'AD';
        }
        $data = [
            'brand_id'  => $this->brand_id[0],
            'contact'  => $this->contact,
            'sales'  => $this->sales,
            'lead_type'  => $this->lead_type,
            'customer_need'  => $this->customer_need,
            'status'  => 0
        ];
        $row = LeadMaster::find($this->tbl_lead_masters_id);
        $data['title'] = $this->title;
        if ($row->title != $this->title) {
            $data['title'] = $this->generateTitle($brand, $role);
        }
        $row->update($data);
        $row->brands()->sync($this->brand_id);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        LeadMaster::find($this->tbl_lead_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'title'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataLeadMasterById($tbl_lead_masters_id)
    {
        $this->_reset();
        $row = LeadMaster::find($tbl_lead_masters_id);
        // echo"<pre>";print_r($row);die();


        $brand_id = $row->brands()->pluck('brands.id')->toArray();
        $this->tbl_lead_masters_id = $row->id;
        $this->title = $row->title;
        $this->brand_id = $brand_id;
        $this->contact = $row->contact;
        $this->sales = $row->sales;
        $this->warehouse_id = $row->warehouse_id;
        $this->payment_term = $row->payment_term;
        $this->customer_need = $row->customer_need;
        $this->selectedContact = [
            'user_id' => $row->contactUser->id,
            'name' => $row->contactUser->name,
        ];
        $this->selectedSales = [
            'user_id' => $row->salesUser->id,
            'name' => $row->salesUser->name,
        ];
        $this->lead_type = $row->lead_type;
        $this->customer_need = $row->customer_need;
        $this->type_customer = $row->type_customer;
        $this->status = $row->status;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm', auth()->user()->id);
            $this->brands = Brand::where('status', 1)->get();
            $this->paymentterms = PaymentTerm::all();
            $this->warehouses = Warehouse::all();
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getDataLeadActivityById($tbl_lead_activity_id, $view = false)
    {
        $this->_reset();
        $row = LeadActivity::find($tbl_lead_activity_id);
        $this->activity_id = $row->id;
        $this->title = $row->title;
        $this->description = $row->description;
        $this->start_date = date('Y-m-d', strtotime($row->start_date));
        $this->end_date = date('Y-m-d', strtotime($row->end_date));
        $this->result = $row->result;
        $this->status = $row->status;
        $this->attachment = $row->attachment;
        $this->update_mode = true;
        if ($view) {
            $this->view = true;
            $this->emit('showModalDetail');
        } else {
            $this->emit('showModal');
        }
    }

    public function getDetailById($uid_lead)
    {
        $this->_reset();
        $this->uid_lead = $uid_lead;
        $lead = LeadMaster::where('uid_lead', $uid_lead)->first();
        $productneed = ProductNeed::where('uid_lead', $uid_lead)->get();
        $negotiation = LeadNegotiation::where('uid_lead', $uid_lead)->orderBy('created_at', 'desc')->get();
        $products = ProductVariant::all();

        if ($this->form) {
            $this->form_active = false;
            $this->detail = true;
            $this->productneed = $productneed;
            $this->products = $products;
            $this->nego_value = $lead->nego_value;
            $this->lead = $lead;
            $this->negotiation = $negotiation;

            if (count($productneed) > 0) {
                $inputs = [];
                $product_id = [];
                $harga_satuan = [];
                $harga_total = [];
                $qty = [];
                $price = [];
                $margin_bottom = [];
                foreach ($productneed as $key => $cs) {
                    $price_product = $cs->product->price['final_price'];
                    $inputs[] = $cs->id;
                    $harga_satuan[] = number_format($price_product);
                    $check_margin = MarginBottom::where('product_variant_id', $cs->product_id)->first();
                    $margin_bottom[] = $check_margin ? $check_margin->margin : 0;

                    // $margin_bottom[] = '24';
                    $harga_total[] = number_format($price_product * $cs->qty);
                    $product_id[] = $cs->product_id;
                    $qty[] = $cs->qty;
                    $price[] = $cs->price;
                }

                $this->inputs = $inputs;
                $this->harga_satuan = $harga_satuan;
                $this->margin_bottom = $margin_bottom;
                $this->harga_total = $harga_total;
                $this->product_id = $product_id;
                $this->qty = $qty;
                $this->price = $price;
            } else {
                $this->inputs = [0];
            }
            $this->emit('loadForm', auth()->user()->id);
        }
    }

    public function getDetailApprove($uid_lead)
    {
        $this->uid_lead = $uid_lead;
        $this->emit('showModalApproval');
    }

    public function getLeadMasterId($tbl_lead_masters_id)
    {
        $row = LeadMaster::find($tbl_lead_masters_id);
        $this->tbl_lead_masters_id = $row->id;
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm', auth()->user()->id);
        if ($form) {
            $this->brands = Brand::where('status', 1)->get();
            $this->paymentterms = PaymentTerm::all();
            $this->warehouses = Warehouse::all();
        }
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function showModalProduct()
    {
        $this->_reset();
        $this->emit('showModalProduct');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->emit('initData');
        $this->tbl_lead_masters_id = null;
        $this->title = null;
        $this->brand_id = null;
        $this->contact = null;
        $this->contact_selected = null;
        $this->sales = null;
        $this->lead_type = null;
        $this->customer_need = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->active_tab = 1;

        // leac activity
        $this->search_contact = null;
        $this->warehouse_id = null;
        $this->payment_term = null;
        $this->title = null;
        $this->description = null;
        // $this->start_date = null;
        // $this->end_date = null;
        $this->result = null;
        $this->attachment = null;
        $this->attachment_path = null;
        $this->status = 1;
        $this->user_created = null;
        $this->user_updated = null;
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d', strtotime($this->start_date . ' + 14 days'));
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs, $i);
        array_push($this->harga_satuan, 0);
        array_push($this->harga_total, 0);
        array_push($this->product_id, null);
        array_push($this->qty, 1);
        array_push($this->price, 0);
    }

    public function remove($i)
    {
        $productNeed = ProductNeed::find($this->inputs[$i]);
        if ($productNeed) {
            $productNeed->delete();
        }
        unset($this->inputs[$i]);
        unset($this->harga_satuan[$i]);
        unset($this->harga_total[$i]);
        unset($this->product_id[$i]);
        unset($this->qty[$i]);
        unset($this->price[$i]);
    }

    public function _moveTab($tab = 1)
    {
        $this->active_tab = $tab;
    }

    private function generateTitle($brand = 'FLIMTY', $role)
    {
        $date = date('m/Y');
        $title = 'LEAD/' . $brand . '/' . $role . '-' . $date;
        $data = DB::select("SELECT * FROM `tbl_lead_masters` where title like '%$title%' order by id desc limit 0,1");
        $count_code = 8 + strlen($brand) + strlen($role) + strlen($date);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->title, $count_code);
                $next = sprintf("%03d", ((int)$awal + 1));
                $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . $next;
            }
        } else {
            $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . '001';
        }
        return $nomor;
    }

    private function generateOrderNo()
    {
        $year = date('Y');
        $order_number = 'SO/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_order_leads` where order_number like '%$order_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->order_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SO/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'SO/' . $year . '/' . '000000001';
        }
        return $nomor;
    }

    private function generateInvoiceNo()
    {
        $year = date('Y');
        $invoice_number = 'SI/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_order_leads` where invoice_number like '%$invoice_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->invoice_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SI/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'SI/' . $year . '/' . '000000001';
        }
        return $nomor;
    }


    public function selectedContact($contact)
    {
        $this->emit('applyFilter', ['contact' => $contact]);
    }

    public function selectedSales($sales)
    {
        $this->emit('applyFilter', ['sales' => $sales]);
    }

    public function selectedStatus($status)
    {
        $this->emit('applyFilter', ['status' => $status]);
    }
}
