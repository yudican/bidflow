<?php

namespace App\Http\Livewire;

use App\Models\RefundMaster;
use App\Models\Product;
use App\Models\RefundItem;
use App\Models\RefundResi;
use App\Models\TypeCase;
use App\Models\Transaction;
use App\Models\SourceCase;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class RefundMasterController extends Component
{
    use WithFileUploads;
    public $tbl_refund_masters_id;
    public $name;
    public $email;
    public $ext;
    public $handphone;
    public $phone;
    public $address;
    public $type_case;
    public $alasan;
    public $transaction_from;
    public $transaction_id;
    public $transfer_photo;
    public $transfer_photo_path;

    //item
    public $product_id = [''];
    public $product_photo = [''];
    public $product_photo_path = [''];

    //resi
    public $resi_id;
    public $uid_refund;
    public $expedition_name;
    public $resi;
    public $status = 0;
    public $sender_name;
    public $sender_phone;

    // dinamic form
    public $inputs = [0];
    public $i;
    public $products = [];

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataRefundMasterById', 'getRefundMasterId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
        $this->type_case = 'Refund';
    }

    public function render()
    {
        return view('livewire.tbl-refund-masters', [
            'items' => RefundMaster::all(),
            'products' => Product::all(),
            'type_list' => TypeCase::all(),
            'transaction_list' => Transaction::all(),
            'source_list' => SourceCase::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();
        if (!empty($this->transfer_photo_path)) {
            $transfer_photo = Storage::disk('s3')->put('upload/refund', $this->transfer_photo_path, 'public');
        } else {
            $transfer_photo = '';
        }

        $data = [
            'name'  => $this->name,
            'uid_refund' => hash('crc32', Carbon::now()->format('U')),
            'email'  => $this->email,
            'handphone'  => formatPhone($this->handphone),
            'phone'  => formatPhone($this->phone),
            'address'  => $this->address,
            'type_case'  => $this->type_case,
            'alasan'  => $this->alasan,
            'transaction_from'  => $this->transaction_from,
            'transaction_id'  => $this->transaction_id,
            'transfer_photo'  => $transfer_photo
        ];

        RefundMaster::create($data);

        foreach ($this->inputs as $key => $value) {
            $product_photo[$key] = Storage::disk('s3')->put('upload/refund', $this->product_photo_path[$key], 'public');
            RefundItem::updateOrCreate([
                'uid_refund' => $data['uid_refund'],
                'product_id' => $this->product_id[$key]
            ], [
                'uid_refund' => $data['uid_refund'],
                'product_id' => $this->product_id[$key],
                'product_photo' => (!empty($this->product_photo_path[$key]) ? $product_photo[$key] : ''),
            ]);
        }

        $refunditem = RefundItem::where('uid_refund', $this->uid_refund)->get();
        $inputs = [];
        foreach ($refunditem as $key => $cs) {
            $inputs[] = $cs->id;
        }
        $this->inputs = $inputs;

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_resi()
    {
        $this->validate([
            'expedition_name' => 'required',
            'resi' => 'required',
            'sender_name' => 'required',
            'sender_phone' => 'required',
        ]);

        $data = [
            'uid_refund'  => $this->uid_refund,
            'expedition_name'  => $this->expedition_name,
            'resi'  => $this->resi,
            'sender_name'  => $this->sender_name,
            'sender_phone'  => formatPhone($this->sender_phone),
        ];

        RefundResi::create($data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'email'  => $this->email,
            'handphone'  => formatPhone($this->handphone),
            'phone'  => formatPhone($this->phone),
            'address'  => $this->address,
            'type_case'  => $this->type_case,
            'alasan'  => $this->alasan,
            'transaction_from'  => $this->transaction_from,
            'transaction_id'  => $this->transaction_id,
            'transfer_photo'  => $this->transfer_photo
        ];
        $row = RefundMaster::find($this->tbl_refund_masters_id);


        if ($this->transfer_photo_path) {
            $transfer_photo = $this->transfer_photo_path->store('upload', 'public');
            $data = ['transfer_photo' => $transfer_photo];
            if (Storage::exists('public/' . $this->transfer_photo)) {
                Storage::delete('public/' . $this->transfer_photo);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        RefundMaster::find($this->tbl_refund_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required',
            'handphone'  => 'required|numeric',
            'phone'  => 'numeric',
            'address'  => 'required',
            'type_case'  => 'required',
            'alasan'  => 'required',
            'transaction_from'  => 'required',
            // 'transaction_id'  => 'required'
        ];

        // if(!$this->update_mode){$rule['transfer_photo_path'] = 'required';}

        return $this->validate($rule);
    }

    public function getDataRefundMasterById($tbl_refund_masters_id)
    {
        $this->_reset();
        $row = RefundMaster::find($tbl_refund_masters_id);
        $products = Product::all();
        $refunditem = RefundItem::where('uid_refund', $row->uid_refund)->get();
        $refundresi = RefundResi::where('uid_refund', $row->uid_refund)->get();
        $this->uid_refund = $row->uid_refund;
        $this->tbl_refund_masters_id = $row->id;
        $this->name = $row->name;
        $this->email = $row->email;
        $this->handphone = $row->handphone;
        $this->phone = $row->phone;
        $this->address = $row->address;
        $this->type_case = $row->type_case;
        $this->alasan = $row->alasan;
        $this->status = $row->status;
        $this->transaction_from = $row->transaction_from;
        $this->transaction_id = $row->transaction_id;
        $this->transfer_photo = $row->transfer_photo;

        if ($this->form) {
            $this->type_case = 'Refund';
            $this->form_active = true;
            $this->products = $products;
            $this->refunditem = $refunditem;
            $this->refundresi = $refundresi;
            if (count($refunditem) > 0) {
                $inputs = [];
                $product_id = [];
                $product_photo = [];
                foreach ($refunditem as $key => $cs) {
                    $inputs[] = $cs->id;
                    $product_id[] = $cs->product_id;
                    $product_photo[] = $cs->product_photo;
                }

                $this->inputs = $inputs;
                $this->product_photo = $product_photo;
                $this->product_id = $product_id;
            } else {
                $this->inputs = [0];
            }
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getRefundMasterId($tbl_refund_masters_id)
    {
        $row = RefundMaster::find($tbl_refund_masters_id);
        $this->tbl_refund_masters_id = $row->id;
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

    public function approval($uid_refund, $status)
    {
        try {
            DB::beginTransaction();
            $refund = RefundMaster::where('uid_refund', $uid_refund)->first();
            $user = User::where('email', $refund->email)->first();
            if ($status == 1) {
                if ($user) {
                    createNotification(
                        'REF200',
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'user' =>  $user->name,
                            'actionTitle' => 'Input Resi',
                            'actionUrl' => 'https://case.flimty.co/refund/resi/' . $uid_refund,
                        ],
                        ['brand_id' => $user->brand_id],
                        'forgot-password'
                    );
                }
            } else if ($status == 2) {
                if ($user) {
                    createNotification(
                        'REF400',
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'user' => $user->name,
                        ],
                        ['brand_id' => $user->brand_id],
                    );
                }
            }

            $refund->update(['status'  => $status]);

            $this->_reset();
            DB::commit();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_refund_masters_id = null;
        $this->name = null;
        $this->email = null;
        $this->ext = '+62';
        $this->handphone = null;
        $this->phone = null;
        $this->address = null;
        $this->type_case = null;
        $this->alasan = null;
        $this->status = 0;
        $this->transaction_from = null;
        $this->transaction_id = null;
        $this->transfer_photo_path = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->products = Product::all();
        $this->type_case = 'Refund';
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs, $i);
        array_push($this->product_photo_path, null);
    }

    public function remove($i)
    {
        $refundItem = RefundItem::find($this->inputs[$i]);
        if ($refundItem) {
            $refundItem->delete();
        }
        unset($this->inputs[$i]);
        unset($this->product_photo_path[$i]);
    }

    public function showModalResi()
    {
        $this->_reset();
        $this->emit('showModalResi');
    }
}
