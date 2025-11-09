<?php

namespace App\Http\Livewire;

use App\Exports\OrderLeadExportTable;
use App\Exports\OrderLeadDetailExport;
use App\Models\User;
use App\Models\Role;
use App\Models\OrderLead;
use App\Models\ProductNeed;
use App\Models\LeadActivity;
use App\Models\LeadNegotiation;
use App\Models\LeadBilling;
use App\Models\LeadReminder;
use App\Models\Product;
use App\Models\Brand;
use App\Models\AddressUser;
use App\Models\MarginBottom;
use App\Models\Warehouse;
use App\Models\WarehouseUser;
use App\Models\MasterTax;
use App\Models\MasterDiscount;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;


class OrderLeadController extends Component
{
    use WithFileUploads;

    public $tbl_order_leads_id;
    public $uid_lead;
    public $title;
    public $contact;
    public $sales;
    public $customer_need;
    public $user_created;
    public $user_updated;
    public $payment_term;
    public $brand_id;
    public $status;
    public $description_payment;
    public $activity_id;
    public $lead_activity = [];
    public $lead_negotiation = [];
    public $addresslists = [];
    public $provinces = [];
    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];
    public $billinglists = [];
    public $warehouse_id;
    public $address_id;
    public $shipping_type;
    public $notes;
    public $courier;
    public $discount;
    public $tax;

    public $filter_contact = 'all';
    public $filter_sales = 'all';
    public $filter_status = 'all';
    public $filter_date = 'all';

    // alamat
    public $type;
    public $alamat;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;
    public $is_default;
    public $origin_code;
    public $telepon;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;
    public $view = false;

    public $active_tab = 1;

    public $inputs = [];
    public $account_name;
    public $account_bank;
    public $total_transfer;
    public $transfer_date;
    public $upload_billing_photo;
    public $upload_billing_photo_path;
    public $upload_transfer_photo;
    public $upload_transfer_photo_path;

    public $inputs2 = [];
    public $r_contact = [];
    public $r_before_7_day = [''];
    public $r_before_3_day = [''];
    public $r_before_1_day = [''];
    public $r_after_7_day = [''];

    protected $listeners = ['getDataOrderLeadById', 'getOrderLeadId', 'getDetailById', 'getDataLeadActivityById', 'getDataLeadNegotiationById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
        $this->provinces = DB::table('addr_provinsi')->get();
    }

    public function render()
    {
        $role_type = auth()->user()->role->role_type;
        $this->start_date = date('Y-m-d');
        $this->provinces = DB::table('addr_provinsi')->get();

        if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales'])) {
            $sales = User::whereHas('roles', function ($query) {
                return $query->where('roles.role_type', 'sales');
            })->get();
        } else {
            $sales = User::whereHas('roles', function ($query) {
                $user_id = auth()->user()->id;
                return $query->where('roles.role_type', 'sales')->where('user_id', $user_id);
            })->get();
        }

        if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'collector'])) {
            $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
                return $query->whereIn('roles.role_type', ['agent', 'member']);
            })->get();
        } else {
            $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
                $user_id = auth()->user()->id;
                return $query->whereIn('roles.role_type', ['agent', 'member'])->where('created_by', $user_id)->orWhere('users.id', @$this->contact);
            })->get();
        }

        $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
            return $query->whereIn('roles.role_type', ['agent', 'member', 'sales', 'adminsales', 'leadwh']);
        })->get();

        $activity = [];
        if ($this->uid_lead) {
            $activity = LeadActivity::where('uid_lead', $this->uid_lead)->get();
        }
        $productneeds = [];
        if ($this->uid_lead) {
            $productneeds = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        }

        $courier_list = User::leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'roles.role_type')->whereHas('roles', function ($query) {
            return $query->whereIn('roles.role_type', ['courier', 'warehouse', 'admindelivery']);
        })->get();

        return view('livewire.tbl-order-leads', [
            'items' => OrderLead::all(),
            'contact_list' => $contact_list,
            'sales_list' => $sales,
            'courier_list' => $courier_list,
            'brands' => Brand::all(),
            'activity' => $activity,
            'productneeds' => $productneeds,
            'warehouses' => Warehouse::all(),
            'provinces' => DB::table('addr_provinsi')->get(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'uid_lead'  => $this->uid_lead,
            'title'  => $this->title,
            'contact'  => $this->contact,
            'sales'  => $this->sales,
            'customer_need'  => $this->customer_need,
            'user_created'  => $this->user_created,
            'user_updated'  => $this->user_updated,
            'payment_term'  => $this->payment_term,
            'brand_id'  => $this->brand_id,
            'status'  => $this->status
        ];

        OrderLead::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'uid_lead'  => $this->uid_lead,
            'title'  => $this->title,
            // 'contact'  => $this->contact,
            // 'sales'  => $this->sales,
            // 'customer_need'  => $this->customer_need,
            'user_created'  => $this->user_created,
            // 'user_updated'  => $this->user_updated,
            'payment_term'  => $this->payment_term,
            // 'brand_id'  => $this->brand_id,
            'warehouse_id'  => $this->warehouse_id,
            'status'  => $this->status
        ];
        $row = OrderLead::find($this->tbl_order_leads_id);

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function assign_warehouse($uid_lead)
    {
        DB::beginTransaction();
        try {
            $data = ['status'  => 2];

            $row = OrderLead::where('uid_lead', $uid_lead)->first();
            if ($row) {
                $row->update($data);

                createNotification(
                    'AGOP200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser->name,
                        'order_number' => $row->order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'assign_by' => auth()->user()->name,
                        'status' => 'Diproses Gudang',
                        'courier_name' => '-',
                        'receiver_name' => '-',
                        'shipping_address' => '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );
                DB::commit();
                $this->_reset();
                return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
            }
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate 1']);
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate 2']);
        }
    }

    public function set_pengiriman($status, $days_of = null)
    {
        DB::beginTransaction();
        try {
            $row = OrderLead::where('uid_lead', $this->uid_lead)->first();
            if ($row) {
                $due_date = date('Y-m-d', strtotime($this->start_date . ' + ' . $days_of . ' days'));
                $data = ['status_pengiriman'  => $status];
                if ($status == 1) {
                    $item = ProductNeed::where('uid_lead', $this->uid_lead)->get();
                    //pengurangan stock
                    foreach ($item as $it) {
                        $prod = ProductStock::where('warehouse_id', $row->warehouse_id)->where('product_id', $it->product_id)->first();
                        $prod->update(['stock' => $prod->stock - $it->qty]);
                    }

                    $data = ['status_pengiriman'  => $status, 'due_date' => $due_date];
                }
                $row->update($data);

                DB::commit();
                $this->_reset();
                return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
            }
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function set_closed()
    {
        $data = ['status'  => 3];

        $row = OrderLead::where('uid_lead', $this->uid_lead);
        $row->update($data);

        $row2 = LeadBilling::where('uid_lead', $this->uid_lead)->where('status', null);
        $row2->update(['status'  => 3]); //cancel

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function setDefault($id)
    {
        $address = AddressUser::find($id);
        $getExist = AddressUser::where('user_id', $address->user_id);
        $getExist->update(['is_default' => 0]);

        AddressUser::find($id)->update(['is_default' => 1]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function store_shipping()
    {
        DB::beginTransaction();
        try {
            $row = OrderLead::where('uid_lead', $this->uid_lead)->first();
            if ($row) {
                $main = AddressUser::where('user_id', $row->contact)->where('is_default', 1)->first();
                if (empty($main)) {
                    $main = AddressUser::where('user_id', $row->contact)->first();
                }

                $data = [
                    'address_id'  => $main->id,
                    'shipping_type'  => 1,
                    'courier' => $this->courier
                ];

                $row->update($data);
                $courier = User::find($this->courier);
                createNotification(
                    'AGOD200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser->name,
                        'order_number' => $row->order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'assign_by' => auth()->user()->name,
                        'status' => 'Dikirim',
                        'courier_name' => $courier ? $courier->name : '-',
                        'receiver_name' => $main ? $main->nama : '-',
                        'shipping_address' => $main ? $main->alamat_detail : '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );
                DB::commit();
                $this->_reset();
                return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
            }
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function verify_billing($id, $status)
    {
        DB::beginTransaction();
        try {
            $data = ['status'  => $status];

            $billing = LeadBilling::find($id);
            $billing->update($data);
            $row = OrderLead::where('uid_lead', $billing->uid_lead)->first();
            if ($row) {
                $notification_code = $status == 1 ? 'AGOACC200' : 'AGODC200';
                createNotification(
                    $notification_code,
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser->name,
                        'order_number' => $row->order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'assign_by' => auth()->user()->name,
                        'status' => 'Dikirim',
                        'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                        'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                        'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function store_address()
    {
        $data = [
            'type'  => $this->type,
            // 'nama'  => $this->nama,
            'alamat'  => $this->alamat,
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
            'telepon'  => $this->telepon,
            // 'catatan'  => $this->catatan,
            'user_id'  => $this->user_id
        ];

        AddressUser::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_penagihan()
    {
        $data = [
            'uid_lead'  => $this->uid_lead,
            'account_name'  => $this->account_name,
            'account_bank'  => $this->account_bank,
            'total_transfer'  => $this->total_transfer,
            'transfer_date'  => $this->transfer_date
        ];

        if ($this->upload_billing_photo) {
            $upload_billing_photo = Storage::disk('s3')->put('upload/billing', $this->upload_billing_photo, 'public');
            // $upload_billing_photo = $this->upload_billing_photo->store('upload', 'public');
            $data['upload_billing_photo'] = $upload_billing_photo;
        }

        if ($this->upload_transfer_photo) {
            $upload_transfer_photo = Storage::disk('s3')->put('upload/transfer', $this->upload_transfer_photo, 'public');
            // $upload_transfer_photo = $this->upload_transfer_photo->store('upload', 'public');
            $data['upload_transfer_photo'] = $upload_transfer_photo;
        }

        $billing = LeadBilling::create($data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_penarikan()
    {
        $lead = OrderLead::where('uid_lead', $this->uid_lead)->first();
        $item = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        //pengembalian stock
        foreach ($item as $it) {
            $prod = ProductStock::where('warehouse_id', $lead->warehouse_id)->where('product_id', $it->product_id)->first();
            $prod->update(['stock' => $prod->stock + $it->qty]);
        }

        $data = ['status'  => 3]; //closed

        $row = OrderLead::where('uid_lead', $this->uid_lead);
        $row->update($data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function delete()
    {
        OrderLead::find($this->tbl_order_leads_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function cancel()
    {
        $row = OrderLead::find($this->tbl_order_leads_id);
        $row->update(['status' => 4]);
        createNotification(
            'AGOC200',
            [
                'user_id' => $row->sales
            ],
            [
                'user' => $row->salesUser->name,
                'order_number' => $row->order_number,
                'title_order' => $row->title,
                'created_on' => $row->created_at,
                'contact' => $row->contactUser->name,
                'assign_by' => auth()->user()->name,
                'status' => 'Order Dibatalkan',
                'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                'detail_product' => detailProductOrder($row->productNeeds),
            ],
            ['brand_id' => $row->brand_id]
        );
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dibatalkan']);
    }

    public function _validate()
    {
        $rule = [
            'uid_lead'  => 'required',
            'title'  => 'required',
            // 'contact'  => 'required',
            // 'sales'  => 'required',
            // 'user_created'  => 'required',
            // 'user_updated'  => 'required',
            // 'payment_term'  => 'required',
            // 'brand_id'  => 'required',
            // 'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataOrderLeadById($tbl_order_leads_id)
    {
        $this->_reset();
        $row = OrderLead::find($tbl_order_leads_id);
        $this->tbl_order_leads_id = $row->id;
        $this->uid_lead = $row->uid_lead;
        $this->title = $row->title;
        $this->contact = $row->contact;
        $this->sales = $row->sales;
        $this->customer_need = $row->customer_need;
        $this->user_created = $row->user_created;
        $this->user_updated = $row->user_updated;
        $this->payment_term = $row->payment_term;
        $this->brand_id = $row->brand_id;
        $this->status = $row->status;
        $this->provinces = DB::table('addr_provinsi')->get();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getDataLeadActivityById($tbl_lead_activity_id, $view = false)
    {
        $this->_reset();
        $row = LeadActivity::where('uid_lead', $tbl_lead_activity_id)->get();
        $this->lead_activity = $row;
        $this->emit('showModalDetail');
    }

    public function getDataLeadNegotiationById($tbl_lead_activity_id, $view = false)
    {
        $this->_reset();
        $row = LeadNegotiation::where('uid_lead', $tbl_lead_activity_id)->get();
        $this->lead_negotiation = $row;
        $this->emit('showModalNego');
    }

    public function getDetailById($uid_lead)
    {
        $this->_reset();
        $this->uid_lead = $uid_lead;
        $lead = OrderLead::leftjoin('companies', 'companies.user_id', '=', 'order_leads.contact')->where('uid_lead', $uid_lead)->select('order_leads.*', 'companies.name as company_name')->first();
        $productneed = ProductNeed::where('uid_lead', $uid_lead)->get();
        $negotiation = LeadNegotiation::where('uid_lead', $uid_lead)->orderBy('created_at', 'desc')->get();
        $products = ProductVariant::all();
        $discount = MasterDiscount::all();
        $tax = MasterTax::all();
        $addresslist = AddressUser::where('user_id', $lead->contact)->get();
        $billinglist = LeadBilling::where('uid_lead', $uid_lead)->get();
        $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
        if (empty($main)) {
            $main = AddressUser::where('user_id', $lead->contact)->first();
        }
        if ($this->form) {
            $this->form_active = false;
            $this->detail = true;
            $this->productneed = $productneed;
            $this->products = $products;
            $this->discounts = $discount;
            $this->taxes = $tax;
            $this->nego_value = $lead->nego_value;
            $this->lead = $lead;
            $this->negotiation = $negotiation;
            $this->addresslists = $addresslist;
            $this->billinglists = $billinglist;
            $this->mainaddress = $main;
            $this->courier = $lead->courier;
            $this->r_contact[0] = $lead->sales;
            if (count($productneed) > 0) {
                $inputs = [];
                $product_id = [];
                $harga_satuan = [];
                $harga_total = [];
                $qty = [];
                $price = [];
                foreach ($productneed as $key => $cs) {
                    $price_product = $cs->product->price['final_price'];
                    $inputs[] = $cs->id;
                    $harga_satuan[] = number_format($price_product);
                    $harga_total[] = number_format($price_product * $cs->qty);
                    $product_id[] = $cs->product_id;
                    $qty[] = $cs->qty;
                    $price[] = $cs->price;
                }

                $this->inputs = $inputs;
                $this->harga_satuan = $harga_satuan;
                $this->harga_total = $harga_total;
                $this->product_id = $product_id;
                $this->qty = $qty;
                $this->price = $price;
            } else {
                $this->inputs = [0];
            }
            $this->inputs2 = [0];
            $this->emit('loadForm');
        }
    }

    public function getOrderLeadId($tbl_order_leads_id)
    {
        $row = OrderLead::find($tbl_order_leads_id);
        $this->tbl_order_leads_id = $row->id;
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function showModalAddress($user_id)
    {
        $this->_reset();
        $this->provinces = DB::table('addr_provinsi')->get();
        $this->user_id = $user_id;
        $this->emit('showModal');
    }

    public function showModalPenagihan($uid_lead)
    {
        $this->_reset();
        $this->lead = OrderLead::where('uid_lead', $uid_lead)->first();
        $productneed = ProductNeed::where('uid_lead', $uid_lead)->get();
        if (count($productneed) > 0) {
            $inputs = [];
            $product_id = [];
            $harga_satuan = [];
            $harga_total = [];
            $qty = [];
            $price = [];
            foreach ($productneed as $key => $cs) {
                $price_product = $cs->product->price['final_price'];
                $inputs[] = $cs->id;
                $harga_satuan[] = number_format($price_product);
                $harga_total[] = number_format($price_product * $cs->qty);
                $product_id[] = $cs->product_id;
                $qty[] = $cs->qty;
                $price[] = $cs->price;
            }

            $this->inputs = $inputs;
            $this->harga_satuan = $harga_satuan;
            $this->harga_total = $harga_total;
            $this->product_id = $product_id;
            $this->qty = $qty;
            $this->price = $price;
        } else {
            $this->inputs = [0];
        }

        $this->uid_lead = $uid_lead;
        $this->emit('showModalPenagihan');
    }

    public function showModalPenarikan($uid_lead)
    {
        $this->_reset();
        $this->lead = OrderLead::where('uid_lead', $uid_lead)->first();
        $productneed = ProductNeed::where('uid_lead', $uid_lead)->get();
        if (count($productneed) > 0) {
            $inputs = [];
            $product_id = [];
            $harga_satuan = [];
            $harga_total = [];
            $qty = [];
            $price = [];
            foreach ($productneed as $key => $cs) {
                $price_product = $cs->product->price['final_price'];
                $inputs[] = $cs->id;
                $harga_satuan[] = number_format($price_product);
                $harga_total[] = number_format($price_product * $cs->qty);
                $product_id[] = $cs->product_id;
                $qty[] = $cs->qty;
                $price[] = $cs->price;
            }

            $this->inputs = $inputs;
            $this->harga_satuan = $harga_satuan;
            $this->harga_total = $harga_total;
            $this->product_id = $product_id;
            $this->qty = $qty;
            $this->price = $price;
        } else {
            $this->inputs = [0];
        }

        $this->uid_lead = $uid_lead;
        $this->emit('showModalPenarikan');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_order_leads_id = null;
        $this->uid_lead = null;
        $this->title = null;
        $this->contact = null;
        $this->sales = null;
        $this->customer_need = null;
        $this->user_created = null;
        $this->user_updated = null;
        $this->payment_term = null;
        $this->brand_id = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function export()
    {
        return Excel::download(new OrderLeadExportTable(), 'data-order-leads.xlsx');
    }

    public function export_detail($uid_lead)
    {
        return Excel::download(new OrderLeadDetailExport($uid_lead), 'detail-order-lead.xlsx');
    }

    // get kabupatent   
    public function getKabupaten($provinsi_id, $update = false)
    {
        $this->provinces = DB::table('addr_provinsi')->get();
        $this->kabupatens = DB::table('addr_kabupaten')->where('prov_id', $provinsi_id)->get();
        if (!$update) {
            $this->provinsi_id = $provinsi_id;
            $this->kecamatan_id = null;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kecamatans = [];
            $this->kelurahans = [];
        }
    }

    // get kecamatan
    public function getKecamatan($kabupaten_id, $update = false)
    {
        $this->kecamatans = DB::table('addr_kecamatan')->where('kab_id', $kabupaten_id)->get();
        if (!$update) {
            $this->kabupaten_id = $kabupaten_id;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kelurahans = [];
        }
    }

    // get kelurahan
    public function getKelurahan($kecamatan_id, $update = false)
    {
        $this->kelurahans = DB::table('addr_kelurahan')->where('kec_id', $kecamatan_id)->get();
        if (!$update) {
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kecamatan_id = $kecamatan_id;
        }
    }

    // get kodepos
    public function getKodepos($kelurahan_id)
    {
        $this->kelurahan_id = $kelurahan_id;
        $kelurahan = DB::table('addr_kelurahan')->where('pid', $kelurahan_id)->first();
        if ($kelurahan) {
            $this->kodepos = $kelurahan->zip;
            $this->origin_code = $kelurahan->kodejne;
        }
    }

    public function _moveTab($tab = 1)
    {
        $this->active_tab = $tab;
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

    public function applyFilterDate($value)
    {
        $this->reportrange = $value;
        $date = explode(' - ', $value);
        $start = date('Y-m-d', strtotime($date[0]));
        $end = date('Y-m-d', strtotime($date[1]));
        $this->emit('applyFilter', ['start' => $start, 'end' => $end]);
    }

    public function selectedDate($date)
    {
        $this->emit('applyFilter', ['created_at' => $created_at]);
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs2, $i);
        array_push($this->r_contact, null);
        array_push($this->r_before_7_day, 0);
        array_push($this->r_before_3_day, 0);
        array_push($this->r_before_1_day, 0);
        array_push($this->r_after_7_day, 0);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function getDetailAddress($address_id)
    {
        $address = AddressUser::find($address_id);
        $this->type = $address->type;
        $this->alamat = $address->alamat;
        $this->provinsi_id = DB::table('addr_provinsi')->where('pid', $address->provinsi_id)->first()->nama;
        $this->kabupaten_id = DB::table('addr_kabupaten')->where('pid', $address->kabupaten_id)->first()->nama;
        $this->kecamatan_id = DB::table('addr_kecamatan')->where('pid', $address->kecamatan_id)->first()->nama;
        $this->kelurahan_id = DB::table('addr_kelurahan')->where('pid', $address->kelurahan_id)->first()->nama;
        $this->kode_pos = $address->kodepos;
        $this->is_default = $address->is_default;

        $this->emit('showModalAddress');
    }
}
