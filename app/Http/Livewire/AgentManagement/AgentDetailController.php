<?php

namespace App\Http\Livewire\AgentManagement;

use App\Models\AgentAddress;
use App\Models\AgentDetail;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Support\Facades\Cache;
#use Illuminate\Support\Facades\Redis;
use Livewire\Component;


class AgentDetailController extends Component
{

    public $tbl_agent_details_id;
    public $alamat;
    public $instagram_url;
    public $shopee_url;
    public $tokopedia_url;
    public $bukalapak_url;
    public $lazada_url;
    public $other_url;
    public $agent_uid;
    public $libur;
    public $active;
    public $whatsapp_text;

    public $kabupatens = [];
    public $agents = [];


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    public $open = null;
    public $detailOpen = null;

    protected $listeners = ['getDataAgentDetailById', 'getAgentDetailId', 'toggleStatusAgent'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.agentmanagement.tbl-agent-details', [
            'items' => Provinsi::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'alamat'  => $this->alamat,
            'instagram_url'  => $this->instagram_url,
            'shopee_url'  => $this->shopee_url,
            'tokopedia_url'  => $this->tokopedia_url,
            'bukalapak_url'  => $this->bukalapak_url,
            'lazada_url'  => $this->lazada_url,
            'other_url'  => $this->other_url,
            'agent_uid'  => $this->agent_uid,
            'libur'  => $this->libur,
            'active'  => $this->active,
            'whatsapp_text'  => $this->whatsapp_text
        ];

        AgentDetail::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'alamat'  => $this->alamat,
            'instagram_url'  => $this->instagram_url,
            'shopee_url'  => $this->shopee_url,
            'tokopedia_url'  => $this->tokopedia_url,
            'bukalapak_url'  => $this->bukalapak_url,
            'lazada_url'  => $this->lazada_url,
            'other_url'  => $this->other_url,
            'agent_uid'  => $this->agent_uid,
            'libur'  => $this->libur,
            'active'  => $this->active,
            'whatsapp_text'  => $this->whatsapp_text
        ];
        $row = AgentDetail::find($this->tbl_agent_details_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        AgentDetail::find($this->tbl_agent_details_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'alamat'  => 'required',
            'instagram_url'  => 'required',
            'shopee_url'  => 'required',
            'tokopedia_url'  => 'required',
            'bukalapak_url'  => 'required',
            'lazada_url'  => 'required',
            'other_url'  => 'required',
            'agent_uid'  => 'required',
            'libur'  => 'required',
            'active'  => 'required',
            'whatsapp_text'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataAgentDetailById($tbl_agent_details_id)
    {
        $this->_reset();
        $row = AgentDetail::find($tbl_agent_details_id);
        $this->tbl_agent_details_id = $row->id;
        $this->alamat = $row->alamat;
        $this->instagram_url = $row->instagram_url;
        $this->shopee_url = $row->shopee_url;
        $this->tokopedia_url = $row->tokopedia_url;
        $this->bukalapak_url = $row->bukalapak_url;
        $this->lazada_url = $row->lazada_url;
        $this->other_url = $row->other_url;
        $this->agent_uid = $row->agent_uid;
        $this->libur = $row->libur;
        $this->active = $row->active;
        $this->whatsapp_text = $row->whatsapp_text;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getAgentDetailId($tbl_agent_details_id)
    {
        $row = AgentDetail::find($tbl_agent_details_id);
        $this->tbl_agent_details_id = $row->id;
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
        $this->tbl_agent_details_id = null;
        $this->alamat = null;
        $this->instagram_url = null;
        $this->shopee_url = null;
        $this->tokopedia_url = null;
        $this->bukalapak_url = null;
        $this->lazada_url = null;
        $this->other_url = null;
        $this->agent_uid = null;
        $this->libur = null;
        $this->active = null;
        $this->whatsapp_text = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function toggleOpen($id)
    {
        // $data = $this->open;
        // if (isset($data[$id])) {
        //     unset($data[$id]);
        // } else {
        //     $data[$id] = true;
        // }
        $this->open = $id;
    }

    public function toggleStatusAgent($data)
    {
        $field = $data['field'];
        $id = $data['id'];
        $this->open = $data['parent_id'];
        $this->detailOpen = $data['child_id'];
        $detail = AgentDetail::find($id);
        if ($detail) {
            $detail->update([
                $field => $detail[$field] == 0 ? 1 : 0
            ]);
        }
    }

    public function toggleOpenDetail($id)
    {
        $this->emit('loadNestedTable', $id);
        $this->detailOpen = $id;
    }

    public function updateOrder($orders)
    {
        foreach ($orders as $key => $order) {
            AgentDetail::find($order['value'])->update([
                'order' => $order['order'],
            ]);
        }
        $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function loadKabupaten($provinsiPid)
    {
        $this->open = $provinsiPid;
        $this->kabupatens = [];
        $provinsi = Provinsi::where('pid', $provinsiPid)->first();
        if ($provinsi) {
            $provinsis = Cache::rememberForever('kabupatents_' . $provinsiPid, function () use ($provinsi) {
                return $provinsi->kabupaten;
            });
            $this->kabupatens = $provinsis;
        }
    }

    public function loadAgent($kabupaten_pid)
    {
        $this->agents = [];
        $agents = Cache::rememberForever('kabupaten_id' . $kabupaten_pid, function () use ($kabupaten_pid) {
            return AgentAddress::where('kabupaten_id', $kabupaten_pid)->get();
        });
        $this->detailOpen = $kabupaten_pid;
        $this->agents = $agents;
    }
}
