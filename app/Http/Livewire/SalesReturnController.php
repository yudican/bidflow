<?php

namespace App\Http\Livewire;

use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\User;
use App\Models\Role;
use App\Models\Brand;
use App\Models\Warehouse;
use App\Models\PaymentTerm;
use App\Models\OrderLead;
use App\Models\OrderNumber;
use App\Models\Product;
use App\Models\MasterTax;
use App\Models\MasterDiscount;
use App\Models\AddressUser;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Component
{

    public $tbl_sales_return_masters_id;
    public $uid_retur;
    public $sr_number;
    public $order_number;
    public $brand_id;
    public $contact;
    public $sales;
    public $payment_terms;
    public $due_date;
    public $warehouse_id;
    public $shipping_id;
    public $shipping_address;
    public $warehouse_address;
    public $notes;
    public $total;
    public $status;

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

    protected $listeners = ['getDataSalesReturnById', 'getSalesReturnId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $role_type = auth()->user()->role->role_type;
        $this->start_date = date('Y-m-d');

        // if (in_array($role_type, ['adminsales', 'superadmin', 'leadsales'])) {
        //     $sales = User::whereHas('roles', function ($query) {
        //         return $query->where('roles.role_type', 'sales');
        //     })->get();
        // } else {
        //     $sales = User::whereHas('roles', function ($query) {
        //         $user_id = auth()->user()->id;
        //         return $query->where('roles.role_type', 'sales')->where('user_id', $user_id);
        //     })->get();
        // }

        // if (in_array($role_type, ['adminsales', 'superadmin', 'leadsales'])) {
        //     $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
        //         return $query->whereIn('roles.role_type', ['agent', 'member']);
        //     })->get();
        // } else {
        //     $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
        //         $user_id = auth()->user()->id;
        //         return $query->whereIn('roles.role_type', ['agent', 'member'])->where('created_by', $user_id)->orWhere('users.id', @$this->contact);
        //     })->get();
        // }


        return view('livewire.tbl-sales-return-masters', [
            'items' => SalesReturn::all(),
            'brands' => Brand::all(),
            'warehouses' => Warehouse::all(),
            'paymentterms' => PaymentTerm::all(),
            // 'contact_list' => $contact_list,
            // 'sales_list' => $sales,
            'order_list' => OrderLead::all(),
            'user_id' => auth()->user()->id
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'uid_retur'  => hash('crc32', Carbon::now()->format('U')),
            'sr_number'  => $this->generateSRNo(),
            'order_number'  => $this->order_number,
            'brand_id'  => $this->brand_id,
            'contact'  => $this->contact,
            'sales'  => $this->sales,
            'payment_terms'  => $this->payment_terms,
            'due_date'  => $this->due_date,
            'warehouse_id'  => $this->warehouse_id,
            'shipping_address'  => $this->shipping_address,
            'warehouse_address'  => $this->warehouse_address,
            'notes'  => $this->notes,
            'total'  => $this->total,
            'status'  => 0
        ];

        SalesReturn::create($data);

        foreach ($this->inputs as $key => $value) {
            SalesReturnItem::updateOrCreate([
                'uid_retur' => $data['uid_retur'],
                'product_id' => $this->product_id[$key]
            ], [
                'uid_retur' => $data['uid_retur'],
                'product_id' => $this->product_id[$key],
                'qty' => $this->qty[$key],
                'price' => str_replace(",", "", $this->harga_total[$key]),
                'status' => 1,
                'tax_id' => $this->tax_id[$key],
                'discount_id' => $this->discount_id[$key],
            ]);
        }

        $productneed = SalesReturnItem::where('uid_retur', $this->uid_retur)->get();
        $inputs = [];
        foreach ($productneed as $key => $cs) {
            $inputs[] = $cs->id;
        }
        $this->inputs = $inputs;

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'uid_retur'  => $this->uid_retur,
            'sr_number'  => $this->sr_number,
            'order_number'  => $this->order_number,
            'brand_id'  => $this->brand_id,
            'contact'  => $this->contact,
            'sales'  => $this->sales,
            'payment_terms'  => $this->payment_terms,
            'warehouse_id'  => $this->warehouse_id,
            'shipping_address'  => $this->shipping_address,
            'warehouse_address'  => $this->warehouse_address,
            'notes'  => $this->notes,
            'total'  => $this->total,
            'status'  => 0
        ];
        $row = SalesReturn::find($this->tbl_sales_return_masters_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        SalesReturn::find($this->tbl_sales_return_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'brand_id'  => 'required',
            'contact'  => 'required',
            'sales'  => 'required',
            'payment_terms'  => 'required',
            'warehouse_id'  => 'required',
            'shipping_address'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataSalesReturnById($tbl_sales_return_masters_id)
    {
        $this->_reset();
        $row = SalesReturn::find($tbl_sales_return_masters_id);
        $productneed = SalesReturnItem::where('uid_retur', $row->uid_retur)->get();
        $this->tbl_sales_return_masters_id = $row->id;
        $this->uid_retur = $row->uid_retur;
        $this->sr_number = $row->sr_number;
        $this->order_number = $row->order_number;
        $this->brand_id = $row->brand_id;
        $this->contact = $row->contact;
        $this->sales = $row->sales;
        $this->payment_terms = $row->payment_terms;
        $this->warehouse_id = $row->warehouse_id;
        $this->shipping_address = $row->shipping_address;
        $this->warehouse_address = $row->warehouse_address;
        $this->notes = $row->notes;
        $this->total = $row->total;
        $this->status = $row->status;
        $this->getShipping($this->contact);
        if ($this->form) {
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
                    // $price_product = @$cs->product->price['final_price'];
                    $price_product = $cs->price / $cs->qty;
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
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getSalesReturnId($tbl_sales_return_masters_id)
    {
        $row = SalesReturn::find($tbl_sales_return_masters_id);
        $this->tbl_sales_return_masters_id = $row->id;
        $this->getShipping($this->contact);
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

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_sales_return_masters_id = null;
        $this->uid_retur = null;
        $this->sr_number = null;
        $this->order_number = null;
        $this->brand_id = null;
        $this->contact = null;
        $this->sales = null;
        $this->payment_terms = null;
        $this->warehouse_id = null;
        $this->shipping_address = null;
        $this->warehouse_address = null;
        $this->notes = null;
        $this->total = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->products = Product::all();
        $this->tax = MasterTax::all();
        $this->discounts = MasterDiscount::all();
        $this->getShipping($this->contact);
    }

    private function generateSRNo()
    {
        $year = date('Y');
        $order_number = 'SR/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_sales_return_masters` where sr_number like '%$order_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->order_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SR/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'SR/' . $year . '/' . '000000001';
        }
        return $nomor;
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
        $productNeed = SalesReturnItem::find($this->inputs[$i]);
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
        $this->getShipping($this->contact);
    }

    public function getDueDate($top_id)
    {
        $top = PaymentTerm::find($top_id);
        $tgl1 = date('Y-m-d');
        $tgl2 = date('Y-m-d', strtotime('+' . $top->days_of . ' days', strtotime($tgl1)));
        $this->due_date = $tgl2;
        $this->getShipping($this->contact);
    }

    public function getWarehouseAddress($warehouse_id)
    {
        $warehouse = Warehouse::find($warehouse_id);
        $this->warehouse_address = $warehouse->address;
        $this->getShipping($this->contact);
    }

    public function getShipping($contact)
    {
        $this->address = DB::table('address_users')->where('user_id', $contact)->get();
        $data = [];
        foreach ($this->address as $key => $add) {
            $data[$key]['id'] = $add->id;
            $data[$key]['text'] = $add->type . ' - ' . $add->alamat;
        }

        $this->emit('loadShipping', $data);
    }

    public function getShippingAddress($shipping_id)
    {
        $address = AddressUser::find($shipping_id);
        $this->shipping_address = $address->alamat;

        $data = [];
        $data[0]['id'] = $address->id;
        $data[0]['text'] = $address->type . ' - ' . $address->alamat;

        $this->emit('loadShipping', $data);
    }
}
