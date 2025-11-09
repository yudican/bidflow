<?php

namespace App\Http\Livewire\Transaction;

use Livewire\Component;

class TransactionReportController extends Component
{
    public $selectedDate = null;
    public $status = null;
    public $status_delivery = null;
    public $transaction_type = 'customer';
    public $rangeDate = null;

    public function mount()
    {
        $start = date('d/m/Y', strtotime('today - 30 days'));
        $end = date('d/m/Y');
        $this->selectedDate = [$start, $end];
    }

    public function render()
    {
        $this->emit('getSelected');
        return view('livewire.transaction.transaction-report-cntroller');
    }

    public function applyFilterDate($value)
    {
        $date = explode(' - ', $value);
        $start = date('Y-m-d', strtotime($date[0]));
        $end = date('Y-m-d', strtotime($date[1]));
        $this->selectedDate = [$start, $end];
    }

    public function submitFilter()
    {
        $table = $this->transaction_type == 'agent' ? 'transaction_agents.' : 'transactions.';
        $query = null;
        if ($this->status) {
            $query[$table . 'status'] = $this->status;
        }
        if ($this->status_delivery) {
            $query[$table . 'status_delivery'] = $this->status_delivery;
        }


        $data = [
            'query' => $query,
            'created_at' => $this->selectedDate,
            'transaction_type' => $this->transaction_type,
        ];

        $this->emit('applyFilter', $data);
    }
}
