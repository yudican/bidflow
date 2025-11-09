<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Logistic;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Jobs\UpdateToggleLogistic;
use App\Models\LogError;
use App\Models\LogisticRate;
use GuzzleHttp\Client;

class LogisticTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $table_name = 'tbl_logistics';

    public function builder()
    {
        return Logistic::query();
    }

    public function columns()
    {
        return [
            Column::name('id')->label('No.'),
            Column::name('logistic_name')->label('Logistic Name')->searchable(),
            Column::callback(['logistic_url_logo'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => $image,
                ]);
            })->label(__('Image')),
            // BooleanColumn::name('logistic_status')->label('Logistic Status'),

            Column::callback(['tbl_logistics.logistic_status', 'tbl_logistics.id'], function ($status, $id) {
                if ($status == 0) {
                    return '<div class="toggle btn btn-round btn-black off" wire:click="toggleStatus(' . $id . ')" data-toggle="toggle" style="width: 92.8906px; height: 43.7812px;"><input type="checkbox" checked="" data-toggle="toggle" data-onstyle="info" data-style="btn-round"><div class="toggle-group"><label class="btn btn-info toggle-on">On</label><label class="btn btn-black active toggle-off">Off</label><span class="toggle-handle btn btn-black"></span></div></div>';
                }
                return '<div class="toggle btn btn-round btn-success"  wire:click="toggleStatus(' . $id . ')" data-toggle="toggle" style="width: 92.8906px; height: 43.7812px;"><input type="checkbox" checked="" data-toggle="toggle" data-onstyle="success" data-style="btn-round">
                <div class="toggle-group"><label class="btn btn-success toggle-on">On</label><label class="btn btn-black active toggle-off">Off</label><span class="toggle-handle btn btn-black"></span></div>
            </div>';
            })->label(__('Logistic Status')),
            Column::callback(['id'], function ($id) {
                return "<div>
                <button class='btn btn-success btn-sm mr-2' wire:click=getDataById('" . $id . "') id='btn-detail-" . $id . "'><i class='fas fa-eye'></i> Lihat Detail</button>
                <button class='btn btn-primary btn-sm mr-2' wire:click=getDataById('" . $id . "',true) id='btn-detail-diskon-" . $id . "'><i class='fas fa-gear'></i> Pengaturan Diskon</button>
                </div>";
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id, $diskon = false)
    {
        if ($diskon) {
            return $this->emit('getDataLogisticDiskonById', $id);
        }
        $this->emit('getDataLogisticById', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    public function toggleStatus($logistic_id)
    {

        $logistic = Logistic::find($logistic_id);
        if ($logistic) {
            $status = $logistic->logistic_status == 1 ? 0 : 1;
            UpdateToggleLogistic::dispatch($logistic->logistic_original_id, $status)->onQueue('queue-log');
            $logistic->update([
                'logistic_status' => $status,
            ]);

            foreach ($logistic->logisticRates as $rate) {
                $rate->update([
                    'logistic_rate_status' => $status,
                ]);
                UpdateToggleLogistic::dispatch($rate->logistic_rate_original_id, $status, 'rates')->onQueue('queue-log');
            }
        }
        $this->refreshTable();
    }
    // 
}
