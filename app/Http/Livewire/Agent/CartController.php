<?php

namespace App\Http\Livewire\Agent;

use App\Models\AddressUser;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Level;
use App\Models\Product;
use App\Models\Price;
use App\Models\ProductImage;
use App\Models\Cart;
use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\PaymentMethod;
use App\Models\Warehouse;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class CartController extends Component
{
    use WithFileUploads;
    public $tbl_products_id;
    public $category_id;
    public $brand_id;
    public $name;
    public $slug;
    public $description;
    public $image;
    public $agent_price;
    public $customer_price;
    public $discount_price;
    public $discount_percent;
    public $stock;
    public $weight;
    public $is_varian = 0;
    public $status = 1;
    public $image_path;
    public $code;
    public $commentlist = [];
    public $nama;

    // multiple image
    public $images;
    public $image_lists;
    public $images_path;

    public $shippingMethod = 1;
    public $updateAlamatMode = false;
    public $selectedAddress;
    public $tempSelectedAddress;
    public $shippingInfo;
    public $selectedVoucher;
    public $selectedPayment;
    public $selectedWarehouse;

    // dinamic form images
    public $inputs = [0, 1, 2, 3, 4, 5];
    public $i;

    public $level_id = [];
    public $basic_price = [];
    public $final_price = [];

    // kurir
    public $selectedKurir;
    public $selectedKurirData;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $comment = false;
    public $voucher_active = false;
    public $voucher;
    public $shippingLists = [];
    public $shippingServices = ["cargo", "regular", "same_day", "express", "instant"];

    public $form_index = 1;

    protected $listeners = [
        'getDataProductById', 'getProductId', 'add_cart', 'add_qty',
        'min_qty', 'delete'
    ];

    public function mount()
    {
        if (!$this->selectedAddress) {
            $alamat = AddressUser::whereUserId(auth()->user()->id)->first();
            if ($alamat) {
                $this->selectedAddress = $alamat;
                $this->tempSelectedAddress = $alamat;
                $this->getShippingInfo($alamat);
            }
            $this->route_name = request()->route()->getName();
        }
    }

    public function render()
    {
        $user = auth()->user();
        $payements = PaymentMethod::whereNull('parent_id')->whereStatus(1)->with('children')->get();
        return view('livewire.agent.cart', [
            'carts' => Cart::where('user_id', $user->id)->get(),
            'categories' => Category::where('status', 1)->get(),
            'brands' => Brand::all(),
            'agents' => User::leftjoin('role_user', 'users.id', '=', 'role_user.user_id')->leftjoin('roles', 'roles.id', '=', 'role_user.role_id')->where('roles.role_type', 'agent')->get(),
            'cart_count' => $user->carts->count(),
            'cart_selected_count' => $user->carts()->where('selected', 1)->count(),
            'payment_methods' => $payements,
            'warehouses' => Warehouse::where('status', 1)->get()
        ]);
    }

    public function success()
    {
        return view('livewire.agent.success', [
            'items' => Product::all(),
            'categories' => Category::where('status', 1)->get(),
            'brands' => Brand::all(),
        ]);
    }

    public function add_cart($product_id)
    {
        $user = auth()->user();
        $product = Product::find($product_id);
        $cart = $user->carts()->where('product_id', $product->id)->first();

        if ($cart) {
            // if ($request->type == 'add') {
            if ($cart->qty + 1 <= $product->stock) {
                $cart->increment('qty');
            }
            // }
            // if ($request->type == 'delete') {
            //     if ($cart->qty > 1) {
            //         $cart->decrement('qty');
            //     }
            // }
        } else {
            $cart = $user->carts()->create([
                'product_id' => $product->id,
                'qty' => 1,
            ]);
        }
        $this->emit('updateCart');
        return $this->emit('showAlert', ['msg' => 'Produk Berhasil Disimpan']);
    }

    public function add_qty($cart_id)
    {
        $cart = Cart::find($cart_id);

        if ($cart) {
            $cart->increment('qty');
            $this->emit('updateCart');
        }
    }

    public function min_qty($cart_id)
    {
        $cart = Cart::find($cart_id);

        if ($cart) {
            if ($cart->qty > 1) {
                $cart->decrement('qty');
            }
            $this->emit('updateCart');
        }
    }

    // delete cart
    public function delete($cart_id)
    {
        $cart = Cart::find($cart_id);
        if ($cart) {
            $cart->delete();
            $this->emit('updateCart');
            return $this->emit('showAlert', ['msg' => 'Produk berhasil dihapus dari keranjang']);
        }
        return $this->emit('showAlertError', ['msg' => 'Maaf, keranjang tidak ditemukan']);
    }

    public function update()
    {
        $this->_validate();

        try {
            DB::beginTransaction();
            $data = [
                'category_id'  => $this->category_id,
                'brand_id'  => $this->brand_id,
                'name'  => $this->name,
                'slug'  => $this->slug,
                'code'  => $this->code,
                'description'  => $this->description,
                'image'  => $this->image,
                'agent_price'  => $this->agent_price,
                'customer_price'  => $this->customer_price,
                'discount_price'  => $this->discount_price,
                'discount_percent'  => $this->discount_percent,
                'stock'  => $this->stock,
                'weight'  => $this->weight,
                'is_varian'  => $this->is_varian,
                'status'  => $this->status
            ];
            $row = Product::find($this->tbl_products_id);

            if ($this->image_path) {
                $image = $this->image_path->store('upload', 'public');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $this->image)) {
                    Storage::delete('public/' . $this->image);
                }
            }

            $row->update($data);

            // Update Prices
            if ($row->prices->count() > 0) {
                foreach ($row->prices as $key => $price) {
                    $price->update([
                        'basic_price' => $this->basic_price[$price->level_id],
                        'final_price' => $this->final_price[$price->level_id],
                    ]);
                }
            } else {
                // jika price belum diinput
                $prices = [];
                foreach ($this->basic_price as $key => $value) {
                    $prices[] = [
                        'level_id' => $key,
                        'basic_price' => $value,
                        'final_price' => $this->final_price[$key],
                        'product_id' => $row->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                Price::insert($prices);
            }

            $images  = [];
            foreach ($this->images as $image) {
                $file = $image->store('upload', 'public');

                $images[] = [
                    'product_id' => $row->id,
                    'name' => $file,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            ProductImage::insert($images);

            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_products_id, 'keterangan' => 'Update Product']);

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function getProductId($tbl_products_id)
    {
        $row = Product::find($tbl_products_id);
        $this->tbl_products_id = $row->id;
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm');

        if (!$this->update_mode) {
            $levels = Level::all();
            foreach ($levels as $key => $level) {
                $this->basic_price[$level->id] = 0;
                $this->final_price[$level->id] = 0;
            }
        }
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function pilihPengiriman()
    {
        $this->emit('showModalPengiriman');
        $this->emit('closeModalAlamat');
    }

    public function pilihPembayaran()
    {
        $this->emit('showModalPilihPembayaran');
    }

    public function addAlamat()
    {
        $this->nama = "Test nama";
        $this->emit('showModalAlamat');
    }

    public function addVoucher()
    {
        $this->emit('showModalVoucher');
    }

    public function useVoucher($voucher)
    {
        $this->validate([
            'voucher' => 'required',
        ]);
        $client = new Client();
        try {
            $response = $client->request('POST', getSetting('APP_API_URL') . '/transaction/apply-voucher', [
                'form_params' => [
                    'user_id' => auth()->user()->id,
                    'voucher_code' => $voucher,
                    'nominal' => getSubtotal(auth()->user()->carts),
                ]
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            $this->selectedVoucher = $responseJSON['data'];
            $this->voucher_active = true;
            $this->emit('showAlert', ['msg' => 'Voucher Berhasil Digunakan']);
            $this->emit('closeModal');
        } catch (ClientException $th) {
            $response = $th->getResponse();
            $responseBody = json_decode($response->getBody(), true);
            $this->addError('voucher', $responseBody['message']);
        }
    }

    public function addPayment()
    {
        $this->emit('showModalPayment');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->emit('modalGudang', 'hide');
        $this->tbl_products_id = null;
        $this->category_id = null;
        $this->brand_id = null;
        $this->name = null;
        $this->code = null;
        $this->slug = null;
        $this->description = null;
        $this->image_path = null;
        $this->stock = null;
        $this->weight = null;
        $this->is_varian = 0;
        $this->shippingMethod = 1;
        $this->updateAlamatMode = false;
        $this->level_id = [];
        $this->basic_price = [];
        $this->final_price = [];
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->comment = false;
        $this->form_index = 1;
        $this->images = [];
        $this->image_lists = [];
        $this->images_path = [''];
        $this->inputs = [0];
        $this->i = 0;
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs, $i);
    }

    public function unSelect($i)
    {
        unset($this->images[$i]);
    }
    public function deleteImage($image_id)
    {
        $image = ProductImage::find($image_id);
        $image->delete();
        $this->image_lists = ProductImage::where('product_id', $this->tbl_products_id)->get();
    }

    public function selectAddress($type = 1)
    {
        $this->shippingMethod = $type;
    }

    public function updateAlamat($alamat_id)
    {
        $this->updateAlamatMode = true;
        $this->emit('showModalAlamat', $alamat_id);
    }

    public function selectPayment($payment_id)
    {
        $this->selectedPayment = PaymentMethod::find($payment_id);
    }

    // selected address
    public function selectedAddress($alamat_id, $temporary = false)
    {
        $alamat = AddressUser::find($alamat_id);
        if ($temporary) {
            return $this->tempSelectedAddress = $alamat;
        }
        $this->selectedAddress = $alamat;
        $this->getShippingInfo($alamat);
        $this->emit('closeModal');
    }

    public function getShippingInfo($address)
    {
        $client = new Client();
        if ($this->selectedWarehouse) {
            try {
                $carts = Cart::where('user_id', auth()->user()->id)->get();
                $response = $client->request('POST', getSetting('APP_API_URL') . '/shipping/info', [
                    'form_params' => [
                        'kodepos' => $address->kodepos,
                        'kodepos_origin' => $this->selectedWarehouse->kodepos . '',
                        'weight' => getTotalWeight($carts),
                    ]
                ]);
                $responseJSON = json_decode($response->getBody(), true);

                $this->shippingLists = $responseJSON['data'];
            } catch (\Throwable $th) {
                $this->selectedWarehouse = null;
                $this->emit('showAlertError', ['msg' => 'Terjadi Kesalahan, Silahkan Coba Lagi']);
            }
        }
    }

    // sort array object ascending
    public function sortByKey($array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        return $ret;
    }

    // get selected shipping
    public function getShipping($shipping_data = [])
    {
        $selected = null;
        if (is_array($shipping_data)) {
            foreach ($shipping_data as $item) {
                if ($item['shipping_type_name'] == 'JNE REG') {
                    $selected = $item;
                    break;
                }
            }
        }

        $this->shippingInfo = $selected;
    }

    // transaction proccess
    public function checkoutProduct()
    {

        $client = new Client();
        $voucher = is_array($this->selectedVoucher) ? $this->selectedVoucher : null;
        $shippingInfo = is_array($this->shippingInfo) ? $this->shippingInfo : null;
        $carts = auth()->user()->carts()->whereSelected(1)->get();

        try {

            $diskon = isset($voucher['amount_discount']) ? $voucher['amount_discount'] : 0;
            $ongkir = isset($shippingInfo['shipping_price']) ? $shippingInfo['shipping_price'] : 0;

            $response = $client->request('POST', getSetting('APP_API_URL') . '/transaction/guest', [
                'form_params' => [
                    'payment_method_id' => $this->selectedPayment->id,
                    'brand_id' => auth()->user()->brand->id,
                    'amount_to_pay' => getSubtotal($carts) + $ongkir - $diskon - getShippingDiscount($shippingInfo),
                    'address_user_id' => $this->selectedAddress->id,
                    'shipper_address_id' => $this->selectedWarehouse->id,
                    'products' => $this->_getProduct($carts),
                    'voucher_id' => isset($voucher['id']) ? $voucher['id'] : null,
                    'diskon' => $diskon,
                    'shipping' => $this->shippingInfo,
                    'user_id' => auth()->user()->id,
                    'ongkir' => getShippingDiscount($shippingInfo),
                    'weight' => getTotalWeight($carts),
                ]
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            $this->emit('showAlert', ['msg' => $responseJSON['message']]);
            return redirect(route('transaction.detail', ['transaction_id' => $responseJSON['data']['id']]));
        } catch (ClientException $th) {
            $response = $th->getResponse();
            $responseBody = json_decode($response->getBody(), true);
            $this->emit('showAlertError', ['msg' => 'Transaksi Gagal Dibuat']);
            LogError::updateOrCreate(['id' => 1], [
                'message' => $responseBody['message'],
                'trace' => json_encode($responseBody),
                'action' => 'agent checkout proccess (checkoutProduct)',
            ]);
        }
    }

    public function _getProduct($carts)
    {
        $products = [];

        foreach ($carts as $cart) {
            $products[] = [
                'product_id' => $cart->product_id,
                'qty' => $cart->qty,
                'price' => $cart->product->price['final_price'],
            ];
        }

        return $products;
    }

    public function selectAll($type = 'select')
    {
        if ($type == 'select') {
            return auth()->user()->carts()->update(['selected' => 1]);
        }

        return auth()->user()->carts()->update(['selected' => 0]);
    }

    public function selectCart(Cart $cart)
    {
        $cart->selected = !$cart->selected;
        $cart->save();
    }

    public function toggleModalShipping($type = 'show')
    {
        $this->emit('showModalShipping', $type);
    }

    public function selectKurir($key, $service)
    {
        $this->selectedKurir = $key;
        $this->shippingInfo = $this->shippingLists[$service][$key];
        $this->toggleModalShipping('hide');
    }

    public function applyGudang($warehouse_id)
    {
        $warehouse = Warehouse::find($warehouse_id);
        $this->selectedWarehouse = $warehouse;
        $this->getShippingInfo($this->selectedAddress);
        $this->emit('modalGudang', 'hide');
    }

    public function pilihGudangPengiriman()
    {
        $this->emit('modalGudang', 'show');
    }
}
