<?php

namespace App\Http\Livewire;

use App\Models\ReturMaster;
use App\Models\Product;
use App\Models\ReturItem;
use App\Models\ReturResi;
use App\Models\TypeCase;
use App\Models\Transaction;
use App\Models\SourceCase;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class ReturMasterController extends Component
{
    use WithFileUploads;
    public $tbl_retur_masters_id;
    public $name;
    public $email;
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
    public $uid_retur;
    public $expedition_name;
    public $resi;
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

    protected $listeners = ['getDataReturMasterById', 'getReturMasterId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-retur-masters', [
            'items' => ReturMaster::all(),
            'products' => Product::all(),
            'type_list' => TypeCase::all(),
            'transaction_list' => Transaction::all(),
            'source_list' => SourceCase::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'uid_retur' => hash('crc32', Carbon::now()->format('U')),
            'email'  => $this->email,
            'handphone'  => formatPhone($this->handphone),
            'phone'  => formatPhone($this->phone),
            'address'  => $this->address,
            'type_case'  => $this->type_case,
            'alasan'  => $this->alasan,
            'transaction_from'  => $this->transaction_from,
            'transaction_id'  => $this->transaction_id,
        ];

        if ($this->transfer_photo_path) {
            $transfer_photo = Storage::disk('s3')->put('upload/refund', $this->transfer_photo_path, 'public');
            $data['transfer_photo'] = $transfer_photo;
        }

        ReturMaster::create($data);

        foreach ($this->inputs as $key => $value) {
            // $product_photo[$key] = $this->product_photo_path[$key]->store('upload', 'public');
            $product_photo[$key] = null;
            if ($this->product_photo_path[$key]) {
                $product_photo[$key] = Storage::disk('s3')->put('upload/retur', $this->product_photo_path[$key], 'public');
            }
            ReturItem::updateOrCreate([
                'uid_retur' => $data['uid_retur'],
                'product_id' => $this->product_id[$key]
            ], [
                'uid_retur' => $data['uid_retur'],
                'product_id' => $this->product_id[$key],
                'product_photo' => $product_photo[$key],
            ]);
        }

        $returitem = ReturItem::where('uid_retur', $this->uid_retur)->get();
        $inputs = [];
        foreach ($returitem as $key => $cs) {
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
            'sender_phone' => 'required|numeric',
        ]);

        $data = [
            'uid_retur'  => $this->uid_retur,
            'expedition_name'  => $this->expedition_name,
            'resi'  => $this->resi,
            'sender_name'  => $this->sender_name,
            'sender_phone'  => formatPhone($this->sender_phone),
        ];

        ReturResi::create($data);

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
        ];

        if ($this->transfer_photo_path) {
            $transfer_photo = Storage::disk('s3')->put('upload/refund', $this->transfer_photo_path, 'public');
            $data['transfer_photo'] = $transfer_photo;
        }

        $row = ReturMaster::find($this->tbl_retur_masters_id);


        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ReturMaster::find($this->tbl_retur_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required|email',
            'handphone'  => 'required|numeric|min:11',
            // 'phone'  => 'required',
            'address'  => 'required',
            'type_case'  => 'required',
            'alasan'  => 'required',
            'transaction_from'  => 'required',
            // 'transaction_id'  => 'required'
        ];

        // if(!$this->update_mode){$rule['transfer_photo_path'] = 'required';}

        return $this->validate($rule);
    }

    public function getDataReturMasterById($tbl_retur_masters_id)
    {
        $this->_reset();
        $row = ReturMaster::find($tbl_retur_masters_id);
        $products = Product::all();
        $returitem = ReturItem::where('uid_retur', $row->uid_retur)->get();
        $returresi = ReturResi::where('uid_retur', $row->uid_retur)->get();
        $this->tbl_retur_masters_id = $row->id;
        $this->uid_retur = $row->uid_retur;
        $this->name = $row->name;
        $this->email = $row->email;
        $this->handphone = $row->handphone;
        $this->phone = $row->phone;
        $this->address = $row->address;
        $this->type_case = $row->type_case;
        $this->alasan = $row->alasan;
        $this->transaction_from = $row->transaction_from;
        $this->transaction_id = $row->transaction_id;
        $this->transfer_photo = $row->transfer_photo;
        $this->status = $row->status;
        if ($this->form) {
            $this->form_active = true;

            $this->products = $products;
            $this->returitem = $returitem;
            $this->returresi = $returresi;
            if (count($returitem) > 0) {
                $inputs = [];
                $product_id = [];
                $product_photo = [];
                foreach ($returitem as $key => $cs) {
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

    public function getReturMasterId($tbl_retur_masters_id)
    {
        $row = ReturMaster::find($tbl_retur_masters_id);
        $this->tbl_retur_masters_id = $row->id;
    }

    public function approval($uid_retur, $status)
    {


        $retur = ReturMaster::where('uid_retur', $uid_retur)->first();
        $user = User::where('email', $retur->email)->first();
        if ($status == 1) {
            if ($user) {
                createNotification(
                    'RET200',
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'user' => $user->name,
                        'actionTitle' => 'Input Resi',
                        'actionUrl' => 'https://case.flimty.co/return/resi/' . $uid_retur,
                    ],
                    ['brand_id' => $user->brand_id],
                    'forgot-password'
                );
            }
        } else if ($status == 2) {
            if ($user) {
                createNotification(
                    'RET400',
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'user' => $user->name,
                    ],
                    ['brand_id' =>  $user->brand_id],
                );
            }
        }

        $data = ['status'  => $status];
        $retur->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
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
        $this->tbl_retur_masters_id = null;
        $this->name = null;
        $this->email = null;
        $this->handphone = null;
        $this->phone = null;
        $this->address = null;
        $this->type_case = null;
        $this->alasan = null;
        $this->transaction_from = null;
        $this->transaction_id = null;
        $this->transfer_photo_path = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->products = Product::all();
        $this->type_case = 'Retur';
    }

    public function showModalResi()
    {
        $this->_reset();
        $this->emit('showModalResi');
    }
}
