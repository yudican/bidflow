<?php

namespace App\Http\Livewire\Agent;

use App\Models\ConfirmPayment;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class TransactionSuccess extends Component
{
    use WithFileUploads;
    public $transaction;
    public $nama_rekening;
    public $bank_tujuan;
    public $bank_dari;
    public $jumlah_bayar;
    public $foto_struk;
    public $foto_struk_path;
    public $hasConfirm = false;
    public $transaction_id;
    public function mount($transaction_id = null)
    {
        if (!$transaction_id) return abort(404);
        $transaction = TransactionAgent::find($transaction_id);
        if ($transaction) {
            $this->transaction_id = $transaction->id;
            $this->hasConfirm = $transaction->confirmPayment ? true : false;
            if ($transaction->user_id != auth()->user()->id) {
                return abort(403);
            }

            $this->transaction = $transaction;
        }
    }
    public function render()
    {
        return view('livewire.agent.transaction-success');
    }

    public function saveConfirmPayment()
    {
        $this->validate([
            'nama_rekening' => 'required',
            'bank_tujuan' => 'required',
            'bank_dari' => 'required',
            'jumlah_bayar' => 'required',
            'foto_struk_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        try {
            DB::beginTransaction();
            $transaction = TransactionAgent::find($this->transaction_id);
            if ($transaction) {
                $foto_struk = $this->foto_struk_path->store('upload', 'public');
                $data = [
                    'transaction_id' => $transaction->id,
                    'bank_tujuan'  => $this->bank_tujuan,
                    'bank_dari'  => $this->bank_dari,
                    'nama_rekening'  => $this->nama_rekening,
                    'foto_struk'  => $foto_struk,
                    'jumlah_bayar'  => $this->jumlah_bayar,
                    'tanggal_bayar'  => Carbon::now(),
                    'status'  => 0,
                ];

                ConfirmPayment::create($data);
                $transaction->update(['status' => 2]);
                $notification_data = [
                    'user' => $transaction->user->name,
                    'invoice' => $transaction->id_transaksi,
                    'payment_method' => $transaction->paymentMethod->bank_name,
                    'rincian_bayar' => getRincianPembayaran($transaction),
                    'rincian_transaksi' => getRincianTransaksi($transaction),
                    'bank_account_number' => $transaction->paymentMethod->bank_account_number,
                    'bank_account_name' => $transaction->paymentMethod->bank_account_name,
                ];

                createNotification('UUPP200', [], $notification_data, ['transaction_id' => $transaction->id]);
                createNotification('UCPA200', [], $notification_data, ['transaction_id' => $transaction->id]);
                createNotification('UCPAM200', ['user_id' => $transaction->user_id, 'other_id' => $transaction->id], [], ['transaction_id' => $transaction->id]);
                $this->hasConfirm = true;
            }
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Bukti Pembayaran Berhasil Disimpan']);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Bukti Pembayaran Gagal Disimpan']);
        }
    }

    public function confirmPayment()
    {
        $this->jumlah_bayar = $this->transaction->nominal;
        $this->emit('showModalConfirm');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->nama_rekening = null;
        $this->bank_tujuan = null;
        $this->bank_dari = null;
        $this->jumlah_bayar = null;
        $this->foto_struk = null;
        $this->foto_struk_path = null;
    }
}
