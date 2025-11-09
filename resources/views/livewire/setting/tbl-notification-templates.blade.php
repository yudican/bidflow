<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>List Notification Templates</span>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($form_active)
            <div class="card">
                <div class="card-body">
                    <x-text-field type="text" name="notification_code" label="Notification Code" />
                    <x-text-field type="text" name="notification_title" label="Notification Title" />
                    <x-text-field type="text" name="notification_subtitle" label="Notification Subtitle" />
                    <div wire:ignore class="form-group @error('notification_body')has-error has-feedback @enderror">
                        <label for="notification_body" class="text-capitalize">Notification Body</label>
                        <textarea wire:model="notification_body" id="notification_body" class="form-control"></textarea>

                        @error('notification_body')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <x-textarea type="text" name="notification_note" label="Notification Note" />
                    <x-select name="notification_type" label="Notification Type" ignore>
                        <option value="">Select Notification Type</option>
                        <option value="email">Email</option>
                        <option value="alert">Alert</option>
                        <option value="amail-alert">Email & Alert</option>
                    </x-select>
                    <x-select name="role_id" id="choices-multiple-remove-button" label="Role" multiple ignore>
                        @foreach ($roles as $role)
                        <option value="{{$role->id}}">{{$role->role_name}}</option>
                        @endforeach
                    </x-select>

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.notification-template-table params="{{$route_name}}" />
            @endif

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
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                $('#notification_body').summernote({
                    placeholder: 'notification_body',
                    fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
                    tabsize: 2,
                    height: 300,
                    callbacks: {
                                onChange: function(contents, $editable) {
                                    @this.set('notification_body', contents);
                                }
                            }
                        });


                $('#choices-multiple-remove-button').select2({
                    theme: "bootstrap",
                });
                $('#choices-multiple-remove-button').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('role_id', data);
                });

                // notification_type
                $('#notification_type').select2({
                    theme: "bootstrap",
                });
                $('#notification_type').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('notification_type', data);
                });
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>