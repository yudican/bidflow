<div class="page-inner">
    <x-loading />
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>Data Agent</span>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i>
                                Tambah Data</button>
                            <button class="btn btn-primary btn-sm ml-2" wire:click="export"><i class="fas fa-excel"></i>
                                Export</button>
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @if ($form_active)
                    <div>

                    </div>
                    @else
                    <div class="accordion" id="accordionExample">
                        @foreach ($items as $item)
                        <div class="card shadow">
                            <div class="card-header" id="headingOne-{{$item->pid}}">
                                <h2 class="mb-0 w-100 d-flex justify-content-between align-content-center">
                                    <button class="btn btn-link btn-block text-left text-black text-decoration-none" type="button" wire:click="loadKabupaten('{{$item->pid}}')">
                                        <div class="d-flex flex-row justify-content-start align-items-center">
                                            <span class="ml-4 " style="color:black;">{{$item->nama}}</span>
                                        </div>
                                    </button>
                                    <img wire:loading wire:target="loadKabupaten('{{$item->pid}}')" src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e5/Phi_fenomeni.gif/50px-Phi_fenomeni.gif" alt="">
                                </h2>
                            </div>

                            <div id="collapseOne-{{$item->pid}}" class="collapse @if($open == $item->pid) show @endif" aria-labelledby="headingOne-{{$item->pid}}" data-parent="#accordionExample">
                                <div class="card-body  bg-white ">
                                    <div class="accordion" id="accordionRate-{{$item->pid}}">
                                        @foreach ($kabupatens as $kabupaten)
                                        <div class="card mt-2 shadow-sm">
                                            <div class="card-header w-100" id="headingOneRate-{{$kabupaten->pid}}">
                                                <h2 class="mb-0 w-100 d-flex justify-content-between align-content-center">
                                                    <button class="btn btn-link btn-block text-left text-black text-decoration-none" type="button" wire:click="loadAgent('{{$kabupaten->pid}}')">
                                                        <span style="color:black;">{{$kabupaten->nama}}</span>
                                                    </button>
                                                    <img wire:loading wire:target="loadAgent('{{$kabupaten->pid}}'),toggleStatusAgent" src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e5/Phi_fenomeni.gif/50px-Phi_fenomeni.gif" alt="">
                                                </h2>
                                            </div>

                                            <div id="collapseOneRate-{{$kabupaten->pid}}" class="collapse @if($detailOpen == $kabupaten->pid) show @endif" aria-labelledby="headingOneRate-{{$kabupaten->pid}}" data-parent="#accordionRate-{{$item->pid}}">
                                                <div class="card-body">
                                                    <table width="100%" class="table table-lightss">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th colspan="3">Detail</th>
                                                                <th>Libur</th>
                                                                <th>Status</th>
                                                                <th>Special</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                    <ul class="dd-list w-100" wire:sortable="updateOrder">
                                                        @foreach ($agents as $key => $agent)
                                                        <li class="dd-item" wire:sortable.item="{{$agent->agent->id}}" style="min-height: 0;border-radius:5px;" wire:key="agent_{{$agent->agent->id}}">
                                                            <div class="dd-handle">
                                                                <div>
                                                                    <table class="table table-lightss">
                                                                        <tr>
                                                                            <td width="5%" wire:sortable.handle>
                                                                                <span class="handle ui-sortable-handle " style="cursor:move;">
                                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                                </span>
                                                                            </td>
                                                                            <td class="tg-0lax">Nama</td>
                                                                            <td class="tg-0lax" colspan="2">: {{$agent->agent->user->name}}</td>
                                                                            <td class="tg-0lax">
                                                                                @livewire('components.toggle-status', [
                                                                                'id' => $agent->agent->id,
                                                                                'active' => $agent->agent->libur,
                                                                                'field' => 'libur',
                                                                                'emitter' => 'toggleStatusAgent',
                                                                                'parent_id' => $item->pid,
                                                                                'child_id' => $kabupaten->pid,
                                                                                ],key('libur_'.$agent->agent->id))
                                                                            </td>
                                                                            <td class="tg-0lax">
                                                                                @livewire('components.toggle-status', [
                                                                                'id' => $agent->agent->id,
                                                                                'active' => $agent->agent->active,
                                                                                'field' => 'active',
                                                                                'emitter' => 'toggleStatusAgent',
                                                                                'parent_id' => $item->pid,
                                                                                'child_id' => $kabupaten->pid,
                                                                                ],key('active_'.$agent->agent->id))
                                                                            </td>
                                                                            <td class="tg-0lax">
                                                                                @livewire('components.toggle-status', [
                                                                                'id' => $agent->agent->id,
                                                                                'active' => $agent->agent->special,
                                                                                'field' => 'special',
                                                                                'emitter' => 'toggleStatusAgent',
                                                                                'parent_id' => $item->pid,
                                                                                'child_id' => $kabupaten->pid,
                                                                                ],key('special_'.$agent->agent->id))
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax">Telepon</td>
                                                                            <td class="tg-0lax" colspan="2">{{$agent->agent->user->telepon}}</td>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax"></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax">Email</td>
                                                                            <td class="tg-0lax" colspan="2">{{$agent->agent->user->email}}</td>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax"></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="tg-0lax"></td>
                                                                            <td class="tg-0lax">Market Place</td>
                                                                            <td class="tg-0lax" colspan="5">
                                                                                <div class="d-flex flex-row justify-content-between align-items-center">
                                                                                    <a href="{{$agent->agent->instagram_url}}">
                                                                                        <img src="https://img.icons8.com/fluency/48/000000/instagram-new.png" style="width: 50px;" />
                                                                                    </a>
                                                                                    <a href="{{$agent->agent->shopee_url}}">
                                                                                        <img src="https://img.icons8.com/color/48/000000/shopee.png" style="width: 50px;" />
                                                                                    </a>
                                                                                    <a href="{{$agent->agent->tokopedia_url}}">
                                                                                        <img src="https://seeklogo.com/images/T/tokopedia-logo-5340B636F6-seeklogo.com.png" style="width: 50px;" />
                                                                                    </a>
                                                                                    <a href="{{$agent->agent->lazada_url}}">
                                                                                        <img src="https://img.icons8.com/plasticine/100/000000/lazada.png" style="width: 50px;" />
                                                                                    </a>
                                                                                    <a href="{{$agent->agent->bukalapak_url}}">
                                                                                        <img src="https://res.cloudinary.com/crunchbase-production/image/upload/c_lpad,h_170,w_170,f_auto,b_white,q_auto:eco,dpr_1/wgwdrf8fsk9fnc2wngdf" style="width: 50px;" />
                                                                                    </a>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
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
</div>