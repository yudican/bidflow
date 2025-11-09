<?php

namespace App\Http\Livewire;

use App\Models\OrderManual;
use App\Models\OrderProduct;
use App\Models\LeadNegotiation;
use App\Models\User;
use App\Models\Role;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\PaymentTerm;
use App\Models\MasterTax;
use App\Models\MasterDiscount;
use App\Models\Warehouse;
use App\Models\AddressUser;
use App\Models\LeadBilling;
use App\Models\LeadReminder;
use App\Models\ProductStock;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class OrderManualController extends Component
{

    public $tbl_order_manuals_id;
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
    public $warehouse_id;
    public $type_customer;

    public $products = [];
    public $tax = [];
    public $discounts = [];
    public $product_id = [''];
    public $tax_id = [''];
    public $discount_id = [''];
    public $qty = [1];
    public $price = [''];
    public $harga_satuan = [''];
    public $harga_total = [''];

    // dinamic form
    public $inputs = [0];
    public $i;
    public $lead = 1;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;
    public $view = false;

    public $active_tab = 1;

    public $inputs2 = [];
    public $r_contact = [];
    public $r_before_7_day = [''];
    public $r_before_3_day = [''];
    public $r_before_1_day = [''];
    public $r_after_7_day = [''];

    protected $listeners = ['getDataOrderManualById', 'getOrderManualId', 'getDetailById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $role_type = auth()->user()->role->role_type;
        $this->start_date = date('Y-m-d');

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

        if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales'])) {
            $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
                return $query->whereIn('roles.role_type', ['agent', 'member']);
            })->get();
        } else {
            $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
                $user_id = auth()->user()->id;
                return $query->whereIn('roles.role_type', ['agent', 'member'])->where('created_by', $user_id)->orWhere('users.id', @$this->contact);
            })->get();
        }


        $productneeds = [];
        if ($this->uid_lead) {
            $productneeds = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        }

        $courier_list = User::leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'roles.role_type')->whereHas('roles', function ($query) {
            return $query->whereIn('roles.role_type', ['courier', 'warehouse']);
        })->get();

        return view('livewire.tbl-order-manuals', [
            'items' => OrderManual::all(),
            'contact_list' => $contact_list,
            'sales_list' => $sales,
            'courier_list' => $courier_list,
            // 'activity' => $activity,
            'brands' => Brand::all(),
            'productneeds' => $productneeds,
            // 'products' => Product::all(),
            'payment_terms' => PaymentTerm::all(),
            'mastertax' => MasterTax::all(),
            'masterdiscount' => MasterDiscount::all(),
            'warehouses' => Warehouse::all(),
            'paymentterms' => PaymentTerm::all(),
        ]);
    }

    public function store()
    {
        // $this->_validate();

        try {
            DB::beginTransaction();
            $user = auth()->user();
            $role_type = $user->role->role_type;
            $role = 'SA';
            $brand = Brand::find($this->brand_id);
            $brand =  $brand ? strtoupper($brand->name) : 'FLIMTY';
            if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
                $role = 'AD';
            }
            if (auth()->user()->role->role_type == 'sales') {
                $sales = auth()->user()->id;
            } else {
                $sales = $this->sales;
            }

            $data = [
                'brand_id'  => $this->brand_id,
                'title'  => $this->generateTitle($brand, $role),
                'uid_lead' => hash('crc32', Carbon::now()->format('U')),
                'contact'  => $this->contact,
                'sales'  => $sales,
                'payment_term'  => $this->payment_term,
                'customer_need'  => $this->customer_need,
                'status'  => 1,
                'user_created' => auth()->user()->id,
                'warehouse_id' => $this->warehouse_id,
                'type_customer' => $this->type_customer,
            ];
            $row = OrderManual::create($data);

            foreach ($this->inputs as $key => $value) {
                ProductNeed::updateOrCreate([
                    'uid_lead' => $data['uid_lead'],
                    'product_id' => $this->product_id[$key]
                ], [
                    'uid_lead' => $data['uid_lead'],
                    'product_id' => $this->product_id[$key],
                    'qty' => $this->qty[$key],
                    'price' => str_replace(",", "", $this->harga_total[$key]),
                    'status' => 1,
                    'user_created' => auth()->user()->id,
                    'tax_id' => $this->tax_id[$key],
                    'discount_id' => $this->discount_id[$key],
                ]);
            }

            $productneed = ProductNeed::where('uid_lead', $this->uid_lead)->get();
            $inputs = [];
            foreach ($productneed as $key => $cs) {
                $inputs[] = $cs->id;
            }
            $this->inputs = $inputs;

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            print_r($th->getMessage());
            die();
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
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
        $row = OrderManual::find($this->tbl_order_manuals_id);

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function assign_warehouse()
    {
        DB::beginTransaction();
        try {
            $data = ['status'  => 2];

            $row = OrderManual::where('uid_lead', $this->uid_lead)->first();
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
            $row = OrderManual::where('uid_lead', $this->uid_lead)->first();
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
            // throw $th;
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function set_closed()
    {
        $data = ['status'  => 3];

        $row = OrderManual::where('uid_lead', $this->uid_lead);
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
            $row = OrderManual::where('uid_lead', $this->uid_lead)->first();
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
            $row = OrderManual::where('uid_lead', $billing->uid_lead)->first();
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
        $lead = OrderManual::where('uid_lead', $this->uid_lead)->first();
        $item = ProductNeed::where('uid_lead', $this->uid_lead)->get();
        //pengembalian stock
        foreach ($item as $it) {
            $prod = ProductStock::where('warehouse_id', $lead->warehouse_id)->where('product_id', $it->product_id)->first();
            $prod->update(['stock' => $prod->stock + $it->qty]);
        }

        $data = ['status'  => 3]; //closed

        $row = OrderManual::where('uid_lead', $this->uid_lead);
        $row->update($data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function delete()
    {
        OrderManual::find($this->tbl_order_manuals_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function cancel()
    {
        $row = OrderManual::find($this->tbl_order_leads_id);
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
        ];

        return $this->validate($rule);
    }

    public function getDataOrderManualById($tbl_order_manuals_id)
    {
        $this->_reset();
        $row = OrderManual::find($tbl_order_manuals_id);
        $productneed = ProductNeed::where('uid_lead', $row->uid_lead)->get();
        $this->tbl_order_manuals_id = $row->id;
        $this->uid_lead = $row->uid_lead;
        $this->title = $row->title;
        $this->contact = $row->contact;
        $this->sales = $row->sales;
        $this->type_customer = $row->type_customer;
        $this->warehouse_id = $row->warehouse_id;
        $this->customer_need = $row->customer_need;
        $this->user_created = $row->user_created;
        $this->user_updated = $row->user_updated;
        $this->payment_term = $row->payment_term;
        $this->brand_id = $row->brand_id;
        $this->status = $row->status;
        if ($this->form) {
            // $this->products = Product::all();
            $this->tax = MasterTax::all();
            $this->discounts = MasterDiscount::all();
            $this->form_active = true;
            $this->productneed = $productneed;
            if (count($productneed) > 0) {
                $inputs = [];
                $product_id = [];
                $harga_satuan = [];
                $harga_total = [];
                $qty = [];
                $price = [];
                $tax_id = [];
                $discount_id = [];
                foreach ($productneed as $key => $cs) {
                    $price_product = $cs->product->price['final_price'];
                    $inputs[] = $cs->id;
                    $harga_satuan[] = number_format($price_product);
                    $harga_total[] = number_format($price_product * $cs->qty);
                    $product_id[] = $cs->product_id;
                    $qty[] = $cs->qty;
                    $price[] = $cs->price;
                    $tax_id[] = $cs->tax_id;
                    $discount_id[] = $cs->discount_id;
                }

                $this->inputs = $inputs;
                $this->harga_satuan = $harga_satuan;
                $this->harga_total = $harga_total;
                $this->product_id = $product_id;
                $this->qty = $qty;
                $this->price = $price;
                $this->tax_id = $tax_id;
                $this->discount_id = $discount_id;
            } else {
                $this->inputs = [0];
            }
            // $this->inputs2 = [0];
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getOrderManualId($tbl_order_manuals_id)
    {
        $row = OrderManual::find($tbl_order_manuals_id);
        $this->tbl_order_manuals_id = $row->id;
    }

    public function getDetailById($uid_lead)
    {
        // test
        $this->_reset();
        $this->uid_lead = $uid_lead;
        $lead = OrderManual::leftjoin('companies', 'companies.user_id', '=', 'order_manuals.contact')->where('uid_lead', $uid_lead)->select('order_manuals.*', 'companies.name as company_name')->first();
        $productneed = ProductNeed::where('uid_lead', $uid_lead)->get();
        $negotiation = LeadNegotiation::where('uid_lead', $uid_lead)->orderBy('created_at', 'desc')->get();
        $products = Product::all();
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
                    $price_product = @$cs->product->price['final_price'];
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

    public function getPrice($product_id, $index = 0, $type = null)
    {
        $product = Product::find($product_id);
        if ($product) {
            $this->harga_satuan[$index] = number_format($product->price['final_price']);
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

    public function toggleForm($form)
    {
        $this->_reset();
        // $this->products = Product::all();
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_order_manuals_id = null;
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
        $this->products = Product::all();
        $this->tax = MasterTax::all();
        $this->discounts = MasterDiscount::all();
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
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

    public function _moveTab($tab = 1)
    {
        $this->active_tab = $tab;
    }
}
