<div class="page-inner" wire:init="init">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>Case Management</span>
                        </a>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'cs', 'leadcs']))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        @if (!$form_active && !$detail)
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <x-select name="filter_type" label="Pilih Type">
                                <option value="all">Semua Type</option>
                                @foreach ($type_list as $type)
                                <option value="{{$type->id}}">{{$type->type_name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select name="filter_priority" label="Pilih Priority">
                                <option value="all">Semua Priority</option>
                                @foreach ($priority_list as $prio)
                                <option value="{{$prio->id}}">{{$prio->priority_name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select name="filter_status" label="Pilih Status">
                                <option value="all">Semua Status</option>
                                @foreach ($status_list as $status)
                                <option value="{{$status->id}}">{{$status->status_name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary btn-sm" wire:click="confirm_filter">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12">
            @if ($detail)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title" style="font-weight: bold;">{{ @$case->title }}
                        <div class="pull-right" style="font-size:14px;">Status : {{ @getStatusCase($case->status_id) }}</div>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="pull-right">
                                @if (!empty($case->status_approval))
                                <span class="badge {{ ($case->status_approval == 'approved')?'badge-primary':'badge-danger' }}">{{ ($case->status_approval == 'approved') ? 'Approved' : 'Rejected' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Status </td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @getStatusCase($case->status_id) }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Case Type </td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->typeCase->type_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Created On</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ date('l, d F Y', strtotime($case->created_at)) }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Priority </td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->priorityCase->priority_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Source </td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->sourceCase->source_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">Description</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ strip_tags(@$case->description) }}</b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Contact</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->contactUser->name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Category</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->categoryCase->category_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Created By</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>{{ @$case->createdUser->name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>Case Estimation</td>
                                        <td>&nbsp;</td>
                                        <td> : <b>-</b></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Attachment -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Attachment
                        <div class="pull-right">
                            @if ((auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh'))
                            <button class="btn btn-primary btn-sm" wire:click="showModalAttachment"><i class="fas fa-plus"></i> Tambah Attachment</button>
                            @endif
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <table class="display table table-striped table-hover" id="lead-activity">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Attachment</th>
                                <th>Upload By</th>
                                <th>Upload Date</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($attachmentlist) > 0)
                            @foreach ($attachmentlist as $key => $att)
                            <tr id="key-{{$att->id}}" key="{{$att->id}}">
                                <td>{{ $key + 1}}</td>
                                <td>{{ $att->name }}</td>
                                <td>{{@$att->user->name}}</td>
                                <td>{{ $att->upload_at }}</td>
                                <td>@if (!empty($att->file_attachment)) <a target="_blank" href="{{getImage( $att->file_attachment)}}" style="color:blue">Show File</a> @endif</td>
                                <td>
                                    <div class="list-group-item-figure" id="addr">
                                        <div class="dropdown">
                                            <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <div class="dropdown-arrow"></div>
                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <button wire:click="getDetailAttachment('{{ $att->id }}')" class="dropdown-item">Lihat Detail</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="p-0" colspan="6">
                                    <div style="height: 200px;">
                                        <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                            <div class="flex flex-col justify-center items-center mt-8">
                                                <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                <span>Tidak Ada Data</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Transaction -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Transactions
                        <div class="pull-right">
                            @if ((auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh'))
                            <button class="btn btn-primary btn-sm" wire:click="showModalTransaction"><i class="fas fa-plus"></i> Tambah Transaction</button>
                            @endif
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <table class="display table table-striped table-hover" id="lead-activity">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Transaksi</th>
                                <th>Contact</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($transactionlist) > 0)
                            @foreach ($transactionlist as $key => $tra)
                            <tr id="key-{{$tra->id}}" key="{{$tra->id}}">
                                <td>{{ $key + 1}}</td>
                                <td><a href="/transactions/lists">{{ $tra->id_transaksi}}</a></td>
                                <td>{{ @$tra->name }}</td>
                                <td>
                                    <div class="list-group-item-figure" id="addr">
                                        <div class="dropdown">
                                            <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <div class="dropdown-arrow"></div>
                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <button wire:click="getDetailTrans('{{ $tra->id }}')" class="dropdown-item">Lihat Detail</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="p-0" colspan="6">
                                    <div style="height: 200px;">
                                        <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                            <div class="flex flex-col justify-center items-center mt-8">
                                                <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                <span>Tidak Ada Data</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Assign -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Assign To
                        <div class="pull-right">
                            @if ((auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh'))
                            <button class="btn btn-primary btn-sm" wire:click="showModalAssign"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <table class="display table table-striped table-hover" id="lead-activity">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($assignlist) > 0)
                            @foreach ($assignlist as $key => $ass)
                            <tr id="key-{{$ass->id}}" key="{{$ass->id}}">
                                <td>{{ $key + 1}}</td>
                                <td><a href="/contact">{{ @$ass->user->name }}</a></td>
                                <td>{{ @$ass->user->role->role_name }}</td>
                                <td>{{ $ass->notes }}</td>
                                <td>
                                    <div class="list-group-item-figure" id="addr">
                                        <div class="dropdown">
                                            <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <div class="dropdown-arrow"></div>
                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <button wire:click="getDetailAssign('{{ $ass->id }}')" class="dropdown-item">Lihat Detail</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="p-0" colspan="7">
                                    <div style="height: 200px;">
                                        <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                            <div class="flex flex-col justify-center items-center mt-8">
                                                <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                <span>Tidak Ada Data</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Log -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Log History
                    </h4>
                </div>
                <div class="card-body">
                    <table class="display table table-striped table-hover" id="lead-activity">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Contact</th>
                                <th>Log Action</th>
                                <th>Modified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($loglist) > 0)
                            @foreach ($loglist as $key => $log)
                            <tr id="key-{{$log->id}}" key="{{$log->id}}">
                                <td>{{ $key + 1}}</td>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->log_action}}</td>
                                <td>{{ $att->created_at }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="p-0" colspan="4">
                                    <div style="height: 200px;">
                                        <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                            <div class="flex flex-col justify-center items-center mt-8">
                                                <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                <span>Tidak Ada Data</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Action -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if ($case->status_id == 1)
                        <div class="col-md-12">
                            <button class="btn btn-success pull-right mr-2" wire:click="set_open()">Simpan</button>
                        </div>
                        @elseif ($case->status_id == 2 && empty($case->status_approval))
                        <div class="col-md-7">
                            <i>Anda membutuhkan proses approval terlebih dahulu untuk melakukan proses berikut.</i>
                        </div>
                        <div class="col-md-5">
                            <button data-toggle='modal' data-target='#approve-modal' wire:click="getDetailApprove('{{ $case->uid_case }}')" class="btn btn-danger pull-right mr-2">Reject</button>
                            <button class="btn btn-success pull-right mr-2" wire:click="approve('approved')">Approve</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @elseif ($form_active)
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <div class="pull-right"><a href="/contact" class="btn btn-primary btn-sm">Create Contact</a></div>
                    </div>
                    <x-select name="contact" label="Contact">
                        <option value="">Select Contact</option>
                        @foreach ($contact_list as $con)
                        <option value="{{$con->id}}">{{$con->name}} - @if ($con->company) {{ $con->company->name }} @else <i>Perusahaan belum diisi</i> @endif - {{ $con->role->role_type }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="type_id" label="Type">
                        <option value="">Select Type</option>
                        @foreach ($type_list as $tp)
                        <option value="{{$tp->id}}">{{$tp->type_name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="category_id" label="Category">
                        <option value="">Select Category</option>
                        @foreach ($category_list as $cat)
                        <option value="{{$cat->id}}">{{$cat->category_name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="priority_id" label="Priority">
                        <option value="">Select Priority</option>
                        @foreach ($priority_list as $pri)
                        <option value="{{$pri->id}}">{{$pri->priority_name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="source_id" label="Source">
                        <option value="">Select Source</option>
                        @foreach ($source_list as $sou)
                        <option value="{{$sou->id}}">{{$sou->source_name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="status_id" label="Status">
                        @foreach ($status_list as $sta)
                        <option value="{{$sta->id}}">{{$sta->status_name}}</option>
                        @endforeach
                    </x-select>
                    <div wire:ignore class="form-group @error('description')has-error has-feedback @enderror">
                        <label for="description" class="text-capitalize">Description</label>
                        <textarea wire:model="description" id="description" class="form-control"></textarea>
                        @error('description')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            @if ($loading)
            <div class="card flex justify-content-center align-items-center">
                <img src="{{asset('assets/img/loader.gif')}}" alt="loader">
            </div>
            @else
            <div>
                @livewire('table.case-table', ['params' => $route_name ?? 'cases'], key('cases'))
            </div>
            @endif
            @endif
        </div>

        {{-- Modal Attachment --}}
        <div id="attachment-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} Attachment</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="attact_id" id="attact_id" />
                        <input type="hidden" wire:model="uid_case" id="uid_case" />
                        <x-text-field type="text" name="name" label="Nama Attachment" isreq="*" />
                        <x-input-file file="{{$file_attachment}}" path="{{optional($file_attachment_path)->getClientOriginalName()}}" name="file_attachment_path" label="Attachment (Max Upload 1mb)" />
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update_attachment' : 'store_attachment' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Assign --}}
        <div id="assign-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} Assign To</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="assign_id" id="assign_id" />
                        <input type="hidden" wire:model="uid_case" id="uid_case" />
                        <x-select name="contact" label="Contact" id="select2" isreq="*">
                            <option value="">Select Contact</option>
                            @foreach ($contact_list2 as $con)
                            <option value="{{$con->id}}">{{$con->name}} - {{ $con->role->role_type }}</option>
                            @endforeach
                        </x-select>
                        <x-textarea name="notes" label="Notes" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update_assign' : 'store_assign' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Trans --}}
        <div id="trans-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} Transaction</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="case_trans_id" id="case_trans_id" />
                        <input type="hidden" wire:model="uid_case" id="uid_case" />

                        <x-select name="transaction_id" label="Transaction ID" id="select2" isreq="*">
                            <option value="">Select Transaction ID</option>
                            @foreach ($transaction_list as $tra)
                            <option value="{{$tra->id}}">{{$tra->id_transaksi}}{{(!empty($tra->user->name)?' - '.$tra->user->name:'')}}{{(!empty($tra->user->role->role_name)?' - '.$tra->user->role->role_name:'')}}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update_trans' : 'store_trans' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Detail Attachment --}}
        <div id="attachment-detail-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <!-- <div class="modal-dialogl" permission="document" style="position: relative;margin: 0 auto;top: 25%;"> -->
            <div class="modal-dialog" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Detail Attachment
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>

                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">Name</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Attachment</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">@if (!empty($file_attachment)) <a target="_blank" href="{{getImage($file_attachment)}}" style="color:blue">Show File</a> @endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Upload By</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$upload_by->name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Upload At</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$upload_at}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Detail Trans --}}
        <div id="trans-detail-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <!-- <div class="modal-dialogl" permission="document" style="position: relative;margin: 0 auto;top: 25%;"> -->
            <div class="modal-dialog" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Detail Transaction
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>

                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">ID Transaksi</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$id_transaksi}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">User</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$user}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Email</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$email}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Total</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">Rp {{@$total}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Detail Assign --}}
        <div id="assign-detail-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <!-- <div class="modal-dialogl" permission="document" style="position: relative;margin: 0 auto;top: 25%;"> -->
            <div class="modal-dialog" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Detail Assign To
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>

                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">ID Assign</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$assign->id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Name</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$contact->name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Email</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$contact->email}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Notes</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{@$assign->notes}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal confirm approve --}}
        <div id="approve-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Approval</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="uid_case" id="uid_case" />
                        <x-textarea name="approval_notes" label="Notes" />
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click='approve(1)' class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Approve</button>
                        <button type="button" wire:click='approve(3)' class="btn btn-danger btn-sm"><i class="fa fa-times pr-2"></i>Reject</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin hapus data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>


    <script>
        $(document).ready(function(value) {
            $('#testa').on('click', function(){
                console.log('dd')
            })

            window.livewire.on('loadForm', (data) => {
                $('#description').summernote({
                placeholder: 'description',
                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
                tabsize: 2,
                height: 300,
                callbacks: {
                            onChange: function(contents, $editable) {
                                @this.set('description', contents);
                            }
                        }
                });
            });

            window.livewire.on('showModalAttachment', (data) => {
                $('#uid_case').val({{$uid_case}});
                $('#attact_id').val({{$attact_id}});
                $('#attachment-modal').modal('show')
            });

            window.livewire.on('showModalAttachmentDetail', (data) => {
                $('#attachment-detail-modal').modal('show')
            });

            window.livewire.on('showModalTransDetail', (data) => {
                $('#trans-detail-modal').modal('show')
            });

            window.livewire.on('showModalAssignDetail', (data) => {
                $('#assign-detail-modal').modal('show')
            });

            window.livewire.on('showModalAssign', (data) => {
                $('#uid_case').val({{$uid_case}});
                $('#assign_id').val({{$assign_id}});
                $('#assign-modal').modal('show')
            });

            window.livewire.on('showModalTransaction', (data) => {
                $('#uid_case').val({{$uid_case}});
                $('#case_trans_id').val({{$case_trans_id}});
                $('#trans-modal').modal('show')
            });

            window.livewire.on('showModalApproval', (data) => {
                $('#uid_case').val({{$uid_case}});
                $('#approve-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#attachment-modal').modal('hide')
                $('#assign-modal').modal('hide')
                $('#trans-modal').modal('hide')
                $('#attachment-detail-modal').modal('hide')
                $('#trans-detail-modal').modal('hide')
                $('#assign-detail-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>