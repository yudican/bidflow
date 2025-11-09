<div class="page-inner" wire:init="init">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($detail)?'Detail Contact':(($form_active)?'Form Pengisian Data Contact':'List Data Contact') }}</span>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        @if ((auth()->user()->role->role_type == 'superadmin' || auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type != 'leadwh') && !$detail && !$form_active)
        <div class="col-md-12">
            <div class="card">
                <div class="card-body row">
                    <div class="col-md-6">
                        <x-select name="filter_role_id" label="Pilih Role">
                            <option value="all">Semua Role</option>
                            @foreach ($roles as $role)
                            <option value="{{$role->id}}">{{$role->role_name}}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="col-md-6">
                        <x-select name="filter_status" label="Pilih Role">
                            <option value="all">Semua Status</option>
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
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
        @endif
        <div class="col-md-12">
            @if ($detail)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Contact Detail
                        <div class="pull-right" style="font-size:14px;">

                            <!-- <button class="btn btn-danger btn-xs" wire:click="set_blacklist('{{$userdata->id}}')" style="font-size:13px;">Blacklist Contact</button> -->

                            Status :
                            <button class="btn btn-success btn-xs" style="font-size:13px;">
                                {{ ($userdata->status)?'Active':'Blacklist'}}</button>
                        </div>
                    </h4>
                </div>

                {{-- card detail --}}
                <div class="card-body">
                    <ul class="nav nav-pills nav-justified nav-secondary" id="pills-tab" role="tablist">
                        <li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 1) active show @endif truncate" id="pills-info-tab" href="#pills-info" wire:click="changeTab(1)"><b>Contact Info</b></a>
                        </li>
                        <li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 2) active show @endif truncate" id="pills-transactive-tab" href="#pills-transactive" wire:click="changeTab(2)"><b>Transaction Active</b></a>
                        </li>
                        <li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 3) active show @endif truncate" id="pills-transhistory-tab" href="#pills-transhistory" wire:click="changeTab(3)"><b>History Transaction</b></a>
                        </li>
                        <!--<li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 4) active show @endif truncate" id="pills-whislist-tab" href="#pills-whislist" wire:click="changeTab(4)"><b>Whislist</b></a>
                        </li>-->
                        <li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 5) active show @endif truncate" id="pills-case-tab" href="#pills-case" wire:click="changeTab(5)"><b>History Case</b></a>
                        </li>
                        <li class="nav-item submenu">
                            <a class="nav-link @if($activeTab == 6) active show @endif truncate" id="pills-setting-tab" href="#pills-setting" wire:click="changeTab(6)"><b>Setting Profile</b></a>
                        </li>
                    </ul>
                    {{-- dany --}}
                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                        @if ($activeTab == 1)
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                            <div class='flex justify-between border-b pb-2 pt-1 mb-4'>
                                <h1 class='text-2xl font-semibold'>
                                    <span class="svg-icon svg-icon-1 svg-icon-primary" style="color: #009EF7;line-height: 1;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" style="display:inline; height: 1.75rem !important;width: 1.75rem !important;vertical-align: sub;">
                                            <path
                                                d="M10.0813 3.7242C10.8849 2.16438 13.1151 2.16438 13.9187 3.7242V3.7242C14.4016 4.66147 15.4909 5.1127 16.4951 4.79139V4.79139C18.1663 4.25668 19.7433 5.83365 19.2086 7.50485V7.50485C18.8873 8.50905 19.3385 9.59842 20.2758 10.0813V10.0813C21.8356 10.8849 21.8356 13.1151 20.2758 13.9187V13.9187C19.3385 14.4016 18.8873 15.491 19.2086 16.4951V16.4951C19.7433 18.1663 18.1663 19.7433 16.4951 19.2086V19.2086C15.491 18.8873 14.4016 19.3385 13.9187 20.2758V20.2758C13.1151 21.8356 10.8849 21.8356 10.0813 20.2758V20.2758C9.59842 19.3385 8.50905 18.8873 7.50485 19.2086V19.2086C5.83365 19.7433 4.25668 18.1663 4.79139 16.4951V16.4951C5.1127 15.491 4.66147 14.4016 3.7242 13.9187V13.9187C2.16438 13.1151 2.16438 10.8849 3.7242 10.0813V10.0813C4.66147 9.59842 5.1127 8.50905 4.79139 7.50485V7.50485C4.25668 5.83365 5.83365 4.25668 7.50485 4.79139V4.79139C8.50905 5.1127 9.59842 4.66147 10.0813 3.7242V3.7242Z"
                                                fill="#00A3FF"></path>
                                            <path class="permanent"
                                                d="M14.8563 9.1903C15.0606 8.94984 15.3771 8.9385 15.6175 9.14289C15.858 9.34728 15.8229 9.66433 15.6185 9.9048L11.863 14.6558C11.6554 14.9001 11.2876 14.9258 11.048 14.7128L8.47656 12.4271C8.24068 12.2174 8.21944 11.8563 8.42911 11.6204C8.63877 11.3845 8.99996 11.3633 9.23583 11.5729L11.3706 13.4705L14.8563 9.1903Z"
                                                fill="white"></path>
                                        </svg>
                                    </span>
                                    {{ $userdata->name }} Profile's
                                </h1>
                                <button class="btn btn-danger btn-xs" wire:click="set_blacklist('{{$userdata->id}}')" style="font-size:13px;">Blacklist Contact</button>
                            </div>
                            <div class="">
                                <div class="row">
                                    <div class="col-md-3 col-lg-3">
                                        <!--<img src="../../assets/img/profile.jpg" alt="image" style="width: 160px;height: 160px;max-width: none;flex-shrink: 0;display: inline-block;border-radius: 0.475rem;vertical-align: middle;">-->
                                        <div class="avatar avatar-xxl">
                                            <img src="{{$userdata->profile_photo_url}}" class="avatar-img rounded-circle" style="position:absolute;top:50%;left:50%;object-fit:contain;">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6">
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Full Name</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->name }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Birth of Date</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->bod ? $userdata->bod : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Age</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->age >= 0 ? $userdata->age : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Email</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->email }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Gender</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->gender ? $userdata->gender : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Phone</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->telepon }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Brand</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->brands->pluck('name')->implode(', ') }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Owner</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ (empty($userdata->created_by)?'Created By System':getName($userdata->created_by)) }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">NPWP</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ $userdata->npwp ? $userdata->npwp : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class='flex justify-between border-b pb-2 pt-1 mb-4'>
                                    <h1 class='text-2xl font-semibold'>
                                        Company Details
                                    </h1>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-lg-4" style="line-height: 36px;">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>Company Name </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->name }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Business Entity </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>Apotik</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Company Email </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->email }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Company Phone </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->phone }}</b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>PIC Sales</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->pic_name }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>PIC Phone</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->pic_phone }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Owner Name</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->owner_name }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Owner Phone</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ @$company->owner_phone }}</b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                            <div>
                                <hr>
                                <br><br><br>
                                <button class="btn btn-primary btn-sm float-right mr-3" style="z-index: 9999;position:relative;" wire:click="showModal('{{$user_id}}')"><i class="fas fa-plus"></i> Tambah Data</button>
                                <div>
                                    @livewire('table.contact.address-contact-table', ['params' => ['user_id' => $user_id,'segment' => $route_name]], key($user_id))
                                </div>
                            </div>
                        </div>
                        @elseif ($activeTab == 2)
                        <div class="tab-pane fade active show" id="pills-transactive" role="tabpanel" aria-labelledby="pills-transactive-tab">
                            @livewire('table.contact.transaction-active-table', ['params' => ['user_id' => $user_id,'segment' => $route_name]], key('ta-'.$user_id))
                        </div>
                        @elseif ($activeTab == 3)
                        <div class="tab-pane fade active show" id="pills-transhistory" role="tabpanel" aria-labelledby="pills-transhistory-tab">
                            @livewire('table.contact.transaction-history-table', ['params' => ['user_id' => $user_id,'segment' => $route_name]], key('th-'.$user_id))
                        </div>
                        @elseif ($activeTab == 4)
                        <div class="tab-pane fade active show" id="pills-whislist" role="tabpanel" aria-labelledby="pills-whislist-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="table-history-case" class="display table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Name</th>
                                                            <th>Price</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($whislist as $whis)
                                                        <tr>
                                                            <td>{{ $whis->name }}</td>
                                                            <td>{{ $whis->customer_price }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif ($activeTab == 5)
                        <div class="tab-pane fade active show" id="pills-case" role="tabpanel" aria-labelledby="pills-case-tab">
                            @livewire('table.contact.case-history-table', ['params' => ['user_id' => $user_id,'segment' => $route_name]], key('th-'.$user_id))
                        </div>
                        @elseif ($activeTab == 6)
                        <div class="tab-pane fade active show" id="pills-setting" role="tabpanel" aria-labelledby="pills-setting-tab">
                            <div class="card">
                                <div class="card-body row">
                                    <div class="col-md-12">
                                        <input type="hidden" name="users_id" wire:model="users_id" id="users_id" />
                                        <x-text-field type="text" name="name" label="Name" />
                                        <x-text-field type="text" name="email" label="Email" />
                                        <x-text-field type="number" name="telepon" label="Telepon" />
                                        <div class="form-group">
                                            <label>Password</label><br>
                                            <input type="password" name="password" wire:model="password" @if(!$password) placeholder="Password Harus Diisi" @endif class="form-control" />
                                            <small id="helpId" class="text-danger"></small>
                                        </div>
                                        <x-select name="role_id" label="Pilih Role" ignore>
                                            <option value="">Pilih Role</option>
                                            @foreach ($roles as $role)
                                            <option value="{{$role->id}}">{{$role->role_name}}</option>
                                            @endforeach
                                        </x-select>
                                        <x-input-photo foto="{{$photo}}" path="{{optional($photo_path)->temporaryUrl()}}" name="photo_path" label="Photo Profile" />
                                        <div class="form-group float-right">
                                            <button type="button" wire:click="update" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="basic-datatables" class="display table table-striped table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Fullnames</th>
                                                                <th>BOD</th>
                                                                <th>Age</th>
                                                                <th>Email</th>
                                                                <th>Gender</th>
                                                                <th>Telp</th>
                                                                <th>Photo Profile</th>
                                                                <th>Brand</th>
                                                                <th>Owner</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>{{ $userdata->name }}</td>
                                                                <td>{{ $userdata->bod }}</td>
                                                                <td>{{ $userdata->age }}</td>
                                                                <td>{{ $userdata->email }}</td>
                                                                <td>{{ $userdata->gender }}</td>
                                                                <td>{{ $userdata->telepon }}</td>
                                                                <td>{{ (empty($userdata->profile_photo_path)?'-':'<img src="$userdata->profile_photo_path">') }}</td>
                                                                <td>{{ $userdata->brand_name }}</td>
                                                                <td>{{ (empty($userdata->created_by)?'Created By System':$userdata->created_by) }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>

                </div>
                @elseif ($form_active)
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @if (auth()->user()->role->role_type != 'sales')
                            <div class="col-md-6">
                                <x-select name="role_id" label="Pilih Role">
                                    <option value="">Choose Role</option>
                                    @foreach ($roles as $role)
                                    <option value="{{$role->id}}">{{$role->role_name}}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih Role</label>
                                    <select name="role_id" class="form-control" disabled>
                                        <option value="agent">Agent</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-text-field type="text" name="name" label="Name" isreq="*" />
                            </div>
                            <div class="col-md-6">
                                <x-text-field type="email" name="email" label="Email" isreq="*" />
                            </div>
                        </div>
                        <div class="row">
                            <!-- <div class="col-md-6">
                            <x-text-field type="password" name="password" label="Password" />
                        </div> -->
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-text-field type="text" name="telepon" label="Mobile Phone" isreq="*" />
                            </div>
                            <div class="col-md-6">
                                <x-text-field type="date" name="bod" label="Bod" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-select name="gender" label="Pilih Gender" isreq="*" ignore>
                                    <option value="">Choose Gender</option>
                                    <option value="Laki-Laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </x-select>
                            </div>
                            <div class="col-md-6">
                                <x-text-field type="text" name="npwp" label="NPWP" />
                            </div>
                        </div>
                        <hr>
                        <br>
                        <br>
                        <h3 style="font-size:18px py-2">Data Company</h3>
                        <br>
                        <hr>
                        <div class="row">
                            <input type="hidden" name="company_id" wire:model="company_id" />
                            <div class="col-md-6">
                                <x-text-field type="text" name="company_name" label="Company Name" isreq="*" />

                            </div>
                            <div class="col-md-6">
                                <x-text-field type="email" name="company_email" label="Company Email" isreq="*" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-select name="bs_entity" label="Business Entity" ignore>
                                    <option value="">Select Business Entity</option>
                                    @foreach ($business_entity as $business)
                                    <option value="{{$business->id}}">{{$business->title}}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-md-6">
                                <x-textarea type="textarea" name="address" label="Company Address" isreq="*" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-text-field type="text" name="phone" label="Company Phone" isreq="*" />
                            </div>
                            <div class="col-md-6">
                                <x-select name="brand_id" label="Brand" isreq="*" multiple ignore>
                                    <option value="">Select Brand</option>
                                    @foreach ($brands as $brand)
                                    <option value="{{$brand->id}}">{{$brand->name}}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-text-field type="text" name="owner_name" label="Owner Name" />
                            </div>
                            <div class="col-md-6">
                                <x-text-field type="text" name="owner_phone" label="Owner Phone" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-text-field type="text" name="pic_name" label="Pic Name" />
                            </div>
                            <div class="col-md-6">
                                <x-text-field type="text" name="pic_phone" label="Pic Phone" />
                            </div>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                        </div>
                    </div>
                </div>
                @else
                <div>
                    @if ($loading)
                    <div class="card flex justify-content-center align-items-center">
                        <img src="{{asset('assets/img/loader.gif')}}" alt="loader">
                    </div>
                    @else
                    <div>
                        <livewire:table.contact-table params="{{$route_name}}" />
                    </div>
                    @endif
                </div>

                @endif

            </div>

            <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
                <div class="modal-dialog" permission="document">
                    <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                            <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} address</h5>
                            <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="user_id" wire:model="user_id" id="user_id" />
                            <x-text-field type="text" name="type" label="Type" />
                            <x-textarea type="text" name="alamat" label="Alamat" />

                            <x-select name="provinsi_id" label="Provinsi" handleChange="getKabupaten">
                                <option value="">Select Provinsi</option>
                                @foreach ($provinces as $provinsi)
                                <option value="{{@$provinsi->pid}}">{{@$provinsi->nama}}</option>
                                @endforeach
                            </x-select>

                            <x-select name="kabupaten_id" label="Kota/Kabupaten" handleChange="getKecamatan">
                                <option value="">Select Kota/Kabupaten</option>
                                @foreach ($kabupatens as $kab)
                                <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
                                @endforeach
                            </x-select>

                            <x-select name="kecamatan_id" label="Kecamatan" handleChange="getKelurahan">
                                <option value="">Select Kecamatan</option>
                                @foreach ($kecamatans as $kecamatan)
                                <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
                                @endforeach
                            </x-select>

                            <x-select name="kelurahan_id" label="Kelurahan" handleChange="getKodepos">
                                <option value="">Select Kelurahan</option>
                                @foreach ($kelurahans as $kelurahan)
                                <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
                                @endforeach
                            </x-select>

                            <x-text-field type="text" name="kodepos" label="Kodepos" />
                            <x-text-field type="text" name="telepon" label="Telepon" />
                        </div>
                        <div class="modal-footer">

                            <button type="button" wire:click="store_address" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>

                        </div>
                    </div>
                </div>
            </div>

            <div id="address-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
                <div class="modal-dialog" permission="document">
                    <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                            <h5 class="modal-title text-capitalize" id="my-modal-title">Detail address</h5>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="user_id" wire:model="user_id" id="user_id" />
                            <div class="row">
                                <div class="col-md-3">Type</div>
                                <div class="col-md-9">: {{$type}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Address</div>
                                <div class="col-md-9">: {{$alamat}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Kelurahan</div>
                                <div class="col-md-9">: {{@$kelurahan_id}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Kecamatan</div>
                                <div class="col-md-9">: {{@$kecamatan_id}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Kabupaten/Kota</div>
                                <div class="col-md-9">: {{@$kabupaten_id}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Provinsi</div>
                                <div class="col-md-9">: {{@$provinsi_id}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Kode Pos</div>
                                <div class="col-md-9">: {{@$kode_pos}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">Is Default</div>
                                <div class="col-md-9">: {{($is_default)?'Yes':'No'}}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
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

            {{-- Modal confirm --}}
            <div id="confirm2-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
                <div class="modal-dialog" permission="document">
                    <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                            <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                        </div>
                        <div class="modal-body">
                            <p>Apakah anda yakin hapus data ini.?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" wire:click='deleteAddress' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                            <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style type="text/css">
            .nav-pills.nav-secondary .nav-link.active {
                background: #3669C9;
                border: 1px solid #3669C9;
            }

            .btn-success {
                background: #248456 !important;
                border-color: #248456 !important;
            }

            .nav-pills>li>.nav-link {
                color: #3669C9;
                font-size: 16px;
            }
        </style>

        @push('scripts')
        <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
        <script src="{{asset('assets/js/plugin/datatables/datatables.min.js')}}"></script>


        <script>
            $(document).ready(function(value) {
                window.livewire.on('loadForm', (data) => {
                    $('#basic-datatables-adress').DataTable();
                    $('#choices-multiple-remove-button').select2({
                        theme: "bootstrap",
                    });
                    $('#choices-multiple-remove-button').on('change', function (e) {
                        let data = $(this).val();
                        @this.set('brand_id', data);
                    });

                    // role_id
                    $('#role_id').select2({
                        theme: "bootstrap",
                    });
                    $('#role_id').on('change', function (e) {
                        let data = $(this).val();
                        @this.set('role_id', data);
                    });

                    // gender
                    $('#gender').select2({
                        theme: "bootstrap",
                    });
                    $('#gender').on('change', function (e) {
                        let data = $(this).val();
                        @this.set('gender', data);
                    });

                    // bs_entity
                    $('#bs_entity').select2({
                        theme: "bootstrap",
                    });

                    $('#bs_entity').on('change', function (e) {
                        let data = $(this).val();
                        @this.set('bs_entity', data);
                    });

                    // brand_id
                    $('#brand_id').select2({
                        theme: "bootstrap",
                    });
                    
                    $('#brand_id').on('change', function (e) {
                        let data = $(this).val();
                        @this.set('brand_id', data);
                    });
                });
                window.livewire.on('changeTab', (tab) => {
                    if (tab == 1) {
                        $('#basic-datatables-adress').DataTable();
                    }else if (tab == 2) {
                        $('#table-transaction-active').DataTable();
                    }else if (tab == 3) {
                        $('#table-history-transaction').DataTable();
                    }else if (tab == 4) {
                        $('#table-history-case').DataTable();
                    }
                });

                window.livewire.on('showModal', (data) => {
                    $('#form-modal').modal('show')
                });
                window.livewire.on('showModalAddress', (data) => {
                    $('#address-modal').modal('show')
                });
                
                window.livewire.on('closeModal', (data) => {
                    $('#confirm-modal').modal('hide')
                    $('#confirm2-modal').modal('hide')
                    $('#form-modal').modal('hide')
                    $('#address-modal').modal('hide')
                });
            })
        </script>
        @endpush
    </div>