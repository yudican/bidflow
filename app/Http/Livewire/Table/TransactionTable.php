<?php

namespace App\Http\Livewire\Table;

use App\Exports\TransactionExportTable;
use App\Models\Transaction;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionAgent;
use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\TransactionDeliveryStatus;
use Carbon\Carbon;
use GuzzleHttp\Client;

class TransactionTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'applyFilter'];
    public $hideable = 'select';
    public $table_name = 'transactions.';
    public $hide = [];
    public $filters = [];

    public $exportable = true;

    public function builder()
    {
        $user = auth()->user();
        $transaction = Transaction::query();
        $this->table_name = 'transactions.';
        $segment_agent = $this->params['segment'] == 'agent-proccess';
        if ($segment_agent) {
            $transaction = TransactionAgent::query();
            $this->table_name = 'transaction_agents.';
        } else if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            $transaction = TransactionAgent::query();
            $this->table_name = 'transaction_agents.';
        }

        if ($this->params['status'] == 'waiting-confirm') {
            if (count($this->filters) > 0) {
                return $transaction->whereIn($this->table_name . 'status', [2])->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->whereIn($this->table_name . 'status', [2]);
        } else if ($this->params['status'] == 'confirm-payment') {
            if (count($this->filters) > 0) {
                return $transaction->whereIn($this->table_name . 'status', [3])->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->whereIn($this->table_name . 'status', [3]);
        } else if ($this->params['status'] == 'process') {
            if (count($this->filters) > 0) {
                return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1);
        } else if ($this->params['status'] == 'delivered') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->where($this->table_name . 'status_delivery', 4)->where($this->table_name . 'status', 7)->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters);
                }
                return $transaction->where($this->table_name . 'status_delivery', 4)->where($this->table_name . 'status', 7)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->where($this->table_name . 'status_delivery', 4)->where($this->table_name . 'status', 7)->where($this->table_name . 'user_id', auth()->user()->id);
            }
            return $transaction->where($this->table_name . 'status_delivery', 4)->where($this->table_name . 'status', 7);
        } else if ($this->params['status'] == 'waiting-payment') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->whereIn($this->table_name . 'status', [1])->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters);
                }
                return $transaction->whereIn($this->table_name . 'status', [1])->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->whereIn($this->table_name . 'status', [1])->where($this->table_name . 'user_id', auth()->user()->id);
            }
            return $transaction->whereIn($this->table_name . 'status', [1]);
        } else if ($this->params['status'] == 'approve-finance') {
            if (count($this->filters) > 0) {
                return $transaction->where($this->table_name . 'status', 3)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->where($this->table_name . 'status', 3);
        } else if ($this->params['status'] == 'new-order') {
            if (count($this->filters) > 0) {
                return $transaction->where($this->table_name . 'status', 3)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->where($this->table_name . 'status', 3);
        } else if ($this->params['status'] == 'admin-process') {
            if (count($this->filters) > 0) {
                return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', '<', 4)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', '<', 4);
        } else if ($this->params['status'] == 'on-process') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1)->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters);
                }
                return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1)->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1)->where($this->table_name . 'user_id', auth()->user()->id);
            }
            return $transaction->where($this->table_name . 'status', 7)->where($this->table_name . 'status_delivery', 1);
        } else if ($this->params['status'] == 'delivery') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->where($this->table_name . 'status_delivery', 3)->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters)->orderBy($this->table_name . 'created_at', 'desc');;
                }
                return $transaction->where($this->table_name . 'status_delivery', 3)->orderBy($this->table_name . 'created_at', 'desc')->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->where($this->table_name . 'status_delivery', 3)->where($this->table_name . 'user_id', auth()->user()->id)->orderBy($this->table_name . 'created_at', 'desc');;
            }
            return $transaction->where($this->table_name . 'status_delivery', 3)->orderBy($this->table_name . 'created_at', 'desc');;
        } else if ($this->params['status'] == 'history') {
            if (count($this->filters) > 0) {
                return $transaction->where($this->table_name . 'status_delivery', '>', 3)->orderBy($this->table_name . 'created_at', 'desc')->whereBetween($this->table_name . 'created_at', $this->filters);
            }
            return $transaction->where($this->table_name . 'status_delivery', '>', 3)->orderBy($this->table_name . 'created_at', 'desc');
        } else if ($this->params['status'] == 'siap-dikirim') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->whereIn($this->table_name . 'status', [3, 7])->where($this->table_name . 'status_delivery', 21)->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters)->orderBy($this->table_name . 'created_at', 'desc');
                }
                return $transaction->whereIn($this->table_name . 'status', [3, 7])->where($this->table_name . 'status_delivery', 21)->orderBy($this->table_name . 'created_at', 'desc');
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->whereIn($this->table_name . 'status', [3, 7])->where($this->table_name . 'status_delivery', 21)->where($this->table_name . 'user_id', auth()->user()->id)->orderBy($this->table_name . 'created_at', 'desc');
            }
            return $transaction->whereIn($this->table_name . 'status', [3, 7])->where($this->table_name . 'status_delivery', 21)->orderBy($this->table_name . 'created_at', 'desc');
        } else if ($this->params['status'] == 'lists') {
            if (count($this->filters) > 0) {
                if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                    return $transaction->where($this->table_name . 'user_id', auth()->user()->id)->whereBetween($this->table_name . 'created_at', $this->filters)->orderBy($this->table_name . 'created_at', 'desc');
                }
                return $transaction->whereBetween($this->table_name . 'created_at', $this->filters)->orderBy($this->table_name . 'created_at', 'desc');
            }
            if (in_array($user->role->role_type, ['agent', 'subagent'])) {
                return $transaction->where($this->table_name . 'user_id', auth()->user()->id)->orderBy($this->table_name . 'created_at', 'desc');
            }
            return $transaction->orderBy($this->table_name . 'created_at', 'desc');
        }
        if (count($this->filters) > 0) {
            return $transaction->orderBy($this->table_name . 'created_at', 'desc')->whereBetween($this->table_name . 'created_at', $this->filters);
        }
        return $transaction->orderBy($this->table_name . 'created_at', 'desc');;
    }

    public function columns()
    {
        return [
            Column::checkbox(),
            Column::name('id')->label('No.')->width(5),
            Column::name('user.name')->label('User')->searchable(),
            Column::name('id_transaksi')->label('Trans ID')->searchable(),
            Column::callback(['id_transaksi'], function ($id_transaksi) {
                $transaction = $this->_getTransaction()->where('id_transaksi', $id_transaksi)->first();
                if ($transaction && $transaction->label) {
                    if ($transaction->label->status > 0) {
                        return 'Sudah Cetak Label';
                    }
                    return 'Belum Cetak Label';
                }
                return 'Belum Cetak Label';
            })->label(__('Cetak Label'))->hide(),
            Column::callback(['created_at'], function ($transdate) {
                return date('D, d M Y, h:i:s', strtotime($transdate));
            })->label('Trans Date'),
            Column::name('paymentMethod.nama_bank')->label('Payment Method')->searchable()->hide(),

            Column::name('resi')->label('Resi')->searchable()->hide(),
            Column::callback(['nominal'], function ($nominal) {
                return "Rp " . number_format($nominal, 0, ',', '.');
            })->label('Nominal'),
            Column::callback('status', function ($status) {
                switch ($status) {
                    case 1:
                        return 'Waiting Payment';
                        break;
                    case 2:
                        return 'On Progress';
                        break;
                    case 3:
                        return 'Success';
                        break;
                    case 4:
                        return 'Cancel By System';
                        break;
                    case 5:
                        return 'Cancel By User';
                        break;
                    case 6:
                        return 'Cancel By Admin';
                        break;
                    case 7:
                        return 'Admin Process';
                        break;
                    default:
                        return 'Waiting Payment';
                        break;
                }
            })->label('Status')->hide(),
            Column::callback('status_delivery', function ($status_delivery) {
                switch ($status_delivery) {
                    case 1:
                        return 'Waiting Process';
                        break;
                    case 2:
                        return 'Proses Packing';
                        break;
                    case 21:
                        return 'Siap Dikirim';
                        break;
                    case 3:
                        return 'Sedang Dikirim';
                        break;
                    case 4:
                        return 'Pesanan Diterima';
                        break;
                    case 5:
                        return 'Pesanan Belum Diterima';
                        break;
                    case 6:
                        return 'Pesanan Gagal';
                        break;
                    case 7:
                        return 'Cancel By System';
                        break;
                    default:
                        return 'Waiting Process';
                        break;
                }
            })->label('Status Delivery')->hide(),
            Column::callback(['id'], function ($id) {
                $role = auth()->user()->role;
                if ($role->role_type != 'cs') {
                    $trans = $this->_getTransaction()->where('id', $id)->first();
                    if ($trans) {
                        return view('livewire.components.transaction-action-button', ['trans' => $trans]);
                    }
                }
                return '';
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataTransactionById', $id);
    }

    public function assignWarehouse($id)
    {
        $this->emit('assignWarehouse', $id);
    }

    public function packingProcess($id)
    {
        $this->emit('packingProcess', $id);
    }

    public function inputResi($id)
    {
        $this->emit('inputResi', $id);
    }

    public function productReceived($id)
    {
        $this->emit('productReceived', $id);
    }

    public function showTimeline($transaction_id)
    {
        $client = new Client();
        $token = getSetting('POPAKET_TOKEN');
        try {
            $response = $client->request('GET', getSetting('POPAKET_BASE_URL') . "/shipment/v1/orders/{$transaction_id}/track", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                return  $this->emit('showTimeline', $responseJSON['tracking_history']);
            }
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get track order (getAwbNumber) transaction',
            ]);
            return  $this->emit('showTimeline', null);
        }
    }

    public function showPaymentDetail($id)
    {
        $this->emit('showPaymentDetail', $id);
    }

    public function showPhoto($id)
    {
        $this->emit('showPhoto', $id);
    }

    public function getId($id)
    {
        $this->emit('getTransactionId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    public function export()
    {
        return Excel::download(new TransactionExportTable($this->params, $this->filters), 'data-transaction.xlsx');
    }

    public function applyFilter($data)
    {
        $this->filters = $data;
    }

    public function assignToWarehouse()
    {
        foreach ($this->selected as $value) {
            $this->emit('assignWarehouse', $value);
        }
    }

    public function readyToOrder()
    {
        if (count($this->selected) > 0) {
            $transactions = Transaction::whereIn('id', $this->selected)->get();
            $data = [];
            foreach ($transactions as $key => $transaction) {
                $data[] = [
                    'client_order_no' => $transaction->id_transaksi,
                    'pickup_time' => strtotime(Carbon::now()->addDays(1)),
                ];
                $transaction->update(['status_delivery' => 21]);
                TransactionDeliveryStatus::create([
                    'id_transaksi' => $transaction->id_transaksi,
                    'delivery_status' => 21,
                ]);
            }

            $client = new Client();
            try {
                $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/shipment/v1/orders/generate-bulk-awb', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . getSetting('POPAKET_TOKEN')
                    ],
                    'body' => json_encode($data)
                ]);

                $responseJSON = json_decode($response->getBody(), true);
                if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                    return $this->emit('showAlert', ['msg' => 'Status transaksi berhasil diubah, Produk Siap Dikirim']);
                }
            } catch (\Throwable $th) {
                LogError::updateOrCreate(['id' => 1], [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                    'action' => 'Set pickup time',
                ]);
                return $this->emit('showAlertError', ['msg' => 'Terjadi kesalahan silahkan coba lagi']);
            }
        }
    }

    public function bulkPrint()
    {
        if (count($this->selected) > 0) {
            $selected = $this->selected;
            $transactions = $this->_getTransaction()->whereIn('id', $selected)->get();
            foreach ($transactions as $key => $transaction) {
                if ($transaction->label) {
                    LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction->id, 'keterangan' => 'Cetak Label']);
                    $transaction->label->update(['status' => 1]);
                    $this->emit('downloadFile', [
                        'filename' => 'LABEL-' . $transaction->id_transaksi . '.pdf',
                        'url' => $transaction->label->label_url,
                    ]);
                }
            }
            $this->selected = [];
            $this->emit('refreshLivewireDatatable');
        }
    }

    public function printInvoice()
    {
        if (count($this->selected) > 0) {
            $selected = $this->selected;
            $urls = [];
            $segment = request()->segment(2) == 'agent' ? '.agent' : '';
            foreach ($selected as $value) {
                $urls[] = route('invoice.print' . $segment, $value);
            }

            print_invoice($urls);
            $this->selected = [];
            $this->emit('refreshLivewireDatatable');
        }
    }

    public function bulkPackingProcess()
    {
        foreach ($this->selected as $value) {
            $this->emit('packingProcess', $value);
        }
    }

    public function logTransaction($id)
    {
        $this->emit('logTransaction', $id);
    }

    public function _getTransaction()
    {
        $role = auth()->user()->role;
        $segment_agent = $this->params['segment'] == 'agent-proccess';
        if ($segment_agent) {
            return TransactionAgent::query();
        } else if (in_array($role->role_type, ['agent', 'subagent'])) {
            return TransactionAgent::query();
        }
        return Transaction::query();
    }
}
