<?php

namespace App\Http\Livewire\Master;

use App\Jobs\UpdateToggleLogistic;
use App\Models\LogError;
use App\Models\Logistic;
use App\Models\LogisticRate;
use App\Models\ShippingVoucher;
use GuzzleHttp\Client;
use Livewire\Component;


class LogisticController extends Component
{

    public $logistic_id;
    public $row;
    public $shipping_price_discount;
    public $shipping_price_discount_start;
    public $shipping_price_discount_end;
    public $route_name = null;


    protected $listeners = ['getDataLogisticById', 'toggleStatus', 'getDataLogisticDiskonById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.tbl-logistics', [
            'items' => Logistic::all()
        ]);
    }


    public function getDataLogisticById($logistic_id)
    {
        $row = Logistic::find($logistic_id);
        $this->logistic_id = $logistic_id;
        $this->row = $row;
        $this->emit('showModal');
    }
    public function getDataLogisticDiskonById($logistic_id)
    {
        $row = Logistic::find($logistic_id);
        $this->logistic_id = $logistic_id;
        $this->row = $row;
        $diskon = [];
        $tgl_mulai = [];
        $tgl_selesai = [];

        foreach ($row->logisticRates as $rate) {
            if ($rate->shippingVoucher) {
                $diskon[$rate->id] = $rate->shippingVoucher->shipping_price_discount;
                $tgl_mulai[$rate->id] = date('Y-m-d', strtotime($rate->shippingVoucher->shipping_price_discount_start));
                $tgl_selesai[$rate->id] = date('Y-m-d', strtotime($rate->shippingVoucher->shipping_price_discount_end));
            }
        }

        $this->shipping_price_discount = $diskon;
        $this->shipping_price_discount_start = $tgl_mulai;
        $this->shipping_price_discount_end = $tgl_selesai;
        $this->emit('showModalDiskon', 'show');
    }

    public function saveDiscount()
    {
        foreach ($this->shipping_price_discount as $key => $value) {
            $start = $this->shipping_price_discount_start;
            $end = $this->shipping_price_discount_end;

            if (!isset($start[$key])) {
                return $this->addError('shipping_price_discount_start.' . $key, 'Tidak Boleh Kosong');
            } else if (!isset($end[$key])) {
                return $this->addError('shipping_price_discount_end.' . $key, 'Tidak Boleh Kosong');
            } else if (strtotime($start[$key]) > strtotime($end[$key])) {
                return $this->addError('shipping_price_discount_end.' . $key, 'Tanggal Tidak Sesuai');
            }
            $data = [
                'logistic_rate_id' => $key,
                'shipping_price_discount' => $value,
                'shipping_price_discount_start' => $start[$key],
                'shipping_price_discount_end' => $end[$key],
            ];
            ShippingVoucher::updateOrCreate(['logistic_rate_id' => $key], $data);
        }
        $this->clearValidation();
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function showModal()
    {
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('showModalDiskon', 'hide');
        $this->emit('refreshTable');
        $this->shipping_price_discount = [];
        $this->shipping_price_discount_start = [];
        $this->shipping_price_discount_end = [];
    }

    public function updateKurir()
    {

        $client = new Client();
        try {
            $response = $client->request('GET', getSetting('LOGISTIC_URL') . '/everpro/client-dashboard/shipment/v1/logistics', [
                'headers' => [
                    'Authorization' => 'Bearer ' . getSetting('LOGISTIC_AUTH_TOKEN')
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                foreach ($responseJSON['data'] as $key => $item) {
                    $logistic = Logistic::updateOrCreate(['logistic_name' => $item['logistic_name']], [
                        'logistic_name' => $item['logistic_name'],
                        'logistic_url_logo' => $item['logistic_url_logo'],
                        'logistic_status' => $item['is_active'],
                        'logistic_original_id' => $item['id'],
                    ]);
                    foreach ($item['rates'] as $key => $rate) {
                        $logistic->logisticRates()->updateOrCreate(['logistic_rate_code' => $rate['rate_code']], [
                            'logistic_rate_code' => $rate['rate_code'],
                            'logistic_rate_name' => $rate['rate_name'],
                            'logistic_rate_status' => $rate['is_active'],
                            'logistic_cod_status' => $rate['is_support_cod'],
                            'logistic_rate_original_id' => $rate['id'],
                        ]);
                    }
                }
            }
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Update kuir popaket (updateKurir) logisticController',
            ]);
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function toggleStatus($data)
    {
        $field = $data['field'];
        $rate = LogisticRate::find($data['id']);
        $status = !$rate->$field;
        $rate->update([
            $field => $status
        ]);
        if ($field == 'logistic_rate_status') {
            UpdateToggleLogistic::dispatch($rate->logistic_rate_original_id, $status, 'rates')->onQueue('queue-log');
        }

        $this->row = Logistic::find($rate->logistic_id);
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }
}
