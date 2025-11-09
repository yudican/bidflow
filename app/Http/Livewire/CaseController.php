<?php

namespace App\Http\Livewire;

use App\Models\Cases;
use App\Models\Transaction;
use App\Models\TypeCase;
use App\Models\CategoryCase;
use App\Models\StatusCase;
use App\Models\PriorityCase;
use App\Models\SourceCase;
use App\Models\CaseAttachment;
use App\Models\CaseAssign;
use App\Models\CaseTransaction;
use App\Models\CaseLog;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;


class CaseController extends Component
{
    use WithFileUploads;
    public $tbl_case_masters_id;
    public $contact;
    public $type_id;
    public $category_id;
    public $priority_id;
    public $source_id;
    public $status_id;
    public $description;
    public $created_by;
    public $updated_by;
    public $status_approval;
    public $approval_notes;

    //attachment
    public $file_attachment;
    public $file_attachment_path;
    public $attact_id;
    public $uid_case;
    public $name;
    public $upload_by;
    public $upload_at;
    public $fileatt;

    //assign
    public $assign_id;
    public $notes;

    // transaction
    public $case_trans_id;
    public $transaction_id;

    //filter
    public $filter_type = 'all';
    public $filter_priority = 'all';
    public $filter_status = 'all';

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;
    public $loading = true;

    protected $listeners = ['getDataCaseById', 'getCaseId', 'getDetailById', 'chatWA'];

    public function init()
    {
        $this->loading = false;
    }

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $contact_list = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
            return $query->whereIn('roles.role_type', ['agent', 'member']);
        })->get();

        $contact_list2 = User::leftjoin('companies', 'companies.user_id', '=', 'users.id')->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'companies.name as com_name', 'roles.role_type')->whereHas('roles', function ($query) {
            return $query->whereIn('roles.role_type', ['admincs', 'leadsales', 'adminsales', 'leadwh']);
        })->get();
        // echo"<pre>";print_r(Transaction::all());die();
        return view('livewire.tbl-case-masters', [
            'items' => Cases::all(),
            'contact_list' => $contact_list,
            'contact_list2' => $contact_list2,
            'type_list' => TypeCase::all(),
            'category_list' => CategoryCase::all(),
            'status_list' => StatusCase::all(),
            'priority_list' => PriorityCase::all(),
            'source_list' => SourceCase::all(),
            'transaction_list' => Transaction::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'title'  => $this->generateTitle(),
            'uid_case' => hash('crc32', Carbon::now()->format('U')),
            'contact'  => $this->contact,
            'type_id'  => $this->type_id,
            'category_id'  => $this->category_id,
            'priority_id'  => $this->priority_id,
            'source_id'  => $this->source_id,
            'status_id'  => 1,
            'description'  => $this->description,
            'created_by'  => auth()->user()->id
        ];

        $case = Cases::create($data);
        $user = User::find($this->contact);
        createNotification(
            'TCTCASE',
            [
                'user_id' => $this->contact
            ],
            [
                'name' => $user?->name ?? '',
                'nomor_tiket' => $case->title,
                'brand' => 'FIMTY'
            ],
            [
                'brand_id' => 1
            ]
        );

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_attachment()
    {
        $this->validate([
            'name' => 'required'
        ]);

        $data = [
            'uid_case'  => $this->uid_case,
            'name'  => $this->name,
            'upload_by'  => auth()->user()->id,
            'upload_at'  => date('Y-m-d')
        ];

        if ($this->file_attachment_path) {
            // $attachment = $this->file_attachment_path->store('upload', 'public');
            $attachment = Storage::disk('s3')->put('upload/case', $this->file_attachment_path, 'public');

            $data['file_attachment'] = $attachment;
        }

        CaseAttachment::create($data);

        $log_data = [
            'uid_case'  => $this->uid_case,
            'contact'  => auth()->user()->id,
            'log_action'  => 'Add Attachment'
        ];

        CaseLog::create($log_data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_assign()
    {
        $this->validate([
            'contact' => 'required'
        ]);

        $data = [
            'uid_case'  => $this->uid_case,
            'contact'  => $this->contact,
            'notes'  => $this->notes
        ];

        CaseAssign::create($data);

        $log_data = [
            'uid_case'  => $this->uid_case,
            'contact'  => auth()->user()->id,
            'log_action'  => 'Add Assign To'
        ];

        CaseLog::create($log_data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function store_trans()
    {
        $this->validate([
            'transaction_id' => 'required'
        ]);

        $data = [
            'uid_case'  => $this->uid_case,
            'transaction_id'  => $this->transaction_id
        ];

        CaseTransaction::create($data);

        $log_data = [
            'uid_case'  => $this->uid_case,
            'contact'  => auth()->user()->id,
            'log_action'  => 'Add Transaction ID'
        ];

        CaseLog::create($log_data);

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'contact'  => $this->contact,
            'type_id'  => $this->type_id,
            'category_id'  => $this->category_id,
            'priority_id'  => $this->priority_id,
            'source_id'  => $this->source_id,
            'status_id'  => $this->status_id,
            'description'  => $this->description,
            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by
        ];
        $row = Cases::find($this->tbl_case_masters_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Cases::find($this->tbl_case_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'contact'  => 'required',
            'type_id'  => 'required',
            'category_id'  => 'required',
            'priority_id'  => 'required',
            'source_id'  => 'required',
            // 'status_id'  => 'required',
            // 'description'  => 'required',
        ];



        return $this->validate($rule);
    }

    public function getDataCaseById($tbl_case_masters_id)
    {
        $this->_reset();
        $row = Cases::find($tbl_case_masters_id);
        $this->tbl_case_masters_id = $row->id;
        $this->contact = $row->contact;
        $this->type_id = $row->type_id;
        $this->category_id = $row->category_id;
        $this->priority_id = $row->priority_id;
        $this->source_id = $row->source_id;
        $this->status_id = $row->status_id;
        $this->description = $row->description;
        $this->created_by = $row->created_by;
        $this->updated_by = $row->updated_by;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCaseId($tbl_case_masters_id)
    {
        $row = Cases::find($tbl_case_masters_id);
        $this->tbl_case_masters_id = $row->id;
    }

    public function chatWA($wa = null)
    {
        if (!empty($wa)) {
            return redirect('https://wa.me/' . $wa);
        } else {
            return $this->emit('showAlertWarning', ['msg' => 'Nomor Whatsapp pada contact ini belum diisi!']);
        }
    }

    public function getDetailById($uid_case)
    {
        $this->_reset();
        $this->uid_case = $uid_case;
        $masters = Cases::where('uid_case', $uid_case)->first();
        $attachment = CaseAttachment::where('uid_case', $uid_case)->get();
        $assign = CaseAssign::where('uid_case', $uid_case)->get();
        $logs = CaseLog::where('uid_case', $uid_case)->get();
        $transactions = CaseTransaction::leftjoin('transactions', 'case_transactions.transaction_id', 'transactions.id')->leftjoin('users', 'users.id', 'transactions.user_id')->select('transactions.*', 'users.*', 'case_transactions.id')->where('uid_case', $uid_case)->get();

        if ($this->form) {
            $this->form_active = false;
            $this->detail = true;
            $this->attachmentlist = $attachment;
            $this->assignlist = $assign;
            $this->loglist = $logs;
            $this->transactionlist = $transactions;
            $this->case = $masters;
            $this->emit('loadForm');
        }
    }

    public function getDetailAttachment($attachment_id)
    {
        $attachment = CaseAttachment::find($attachment_id);
        $this->uid_case = $attachment->uid_case;
        $this->name = $attachment->name;
        $this->file_attachment = $attachment->file_attachment;
        $this->upload_by = User::find($attachment->upload_by);
        $this->upload_at = $attachment->upload_at;

        $this->emit('showModalAttachmentDetail');
    }

    public function getDetailTrans($transaction_id)
    {
        $trans = CaseTransaction::find($transaction_id);
        $transaksi = Transaction::find($trans->transaction_id);
        $this->id_transaksi = $transaksi->id_transaksi;
        $this->user = $transaksi->user->name;
        $this->email = $transaksi->user->email;
        $this->total = $transaksi->amount_to_pay;

        $this->emit('showModalTransDetail');
    }

    public function getDetailAssign($assign_id)
    {
        $assign = CaseAssign::find($assign_id);
        $contact = User::find($assign->contact);
        $this->assign = $assign;
        $this->contact = $contact;

        $this->emit('showModalAssignDetail');
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

    public function set_open()
    {
        $data = ['status_id'  => 2];

        $row = Cases::where('uid_case', $this->uid_case);
        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function approve($status)
    {
        try {
            DB::beginTransaction();
            $row = Cases::where('uid_case', $this->uid_case)->first();
            $data = ['status_approval'  => $status, 'approval_notes' => $this->approval_notes];
            $row->update($data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_case_masters_id = null;
        $this->contact = null;
        $this->type_id = null;
        $this->category_id = null;
        $this->priority_id = null;
        $this->source_id = null;
        $this->status_id = null;
        $this->description = null;
        $this->created_by = null;
        $this->updated_by = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function showModalAttachment()
    {
        $this->_reset();
        $this->emit('showModalAttachment');
    }

    public function showModalTransaction()
    {
        $this->_reset();
        $this->emit('showModalTransaction');
    }

    public function showModalAssign()
    {
        $this->_reset();
        $this->emit('showModalAssign');
    }

    private function generateTitle()
    {
        $year = date('Y');
        $title = 'CASE/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_case_masters` where title like '%$title%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->title, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'CASE/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'CASE/' . $year . '/' . '000000001';
        }
        return $nomor;
    }

    public function getDetailApprove($uid_case)
    {
        $this->uid_case = $uid_case;
        $this->emit('showModalApproval');
    }

    public function selectedType($type)
    {
        $this->emit('applyFilter', ['type' => $type]);
    }

    public function selectedPriority($priority)
    {
        $this->emit('applyFilter', ['priority' => $priority]);
    }

    public function selectedStatus($status)
    {
        $this->emit('applyFilter', ['status' => $status]);
    }

    public function confirm_filter()
    {
        $this->emit('applyFilter', ['type' => $this->filter_type, 'priority' => $this->filter_priority, 'status' => $this->filter_status]);
    }
}
