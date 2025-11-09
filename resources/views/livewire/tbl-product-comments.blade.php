<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3"></i>List Data Product Comment</span>
                        </a>
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




            @push('scripts')
            <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>

            <script>
                $(document).ready(function(value) {
                $('input[type="file"]').on("change", function() {
                    let filenames = [];
                    let files = document.getElementById("customFile").files;
                    if (files.length > 1) {
                    filenames.push("Total Files (" + files.length + ")");
                    } else {
                    for (let i in files) {
                        if (files.hasOwnProperty(i)) {
                        filenames.push(files[i].name);
                        }
                    }
                    }
                    $(this)
                    .next(".custom-file-label")
                    .html(filenames.join(","));
                });

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

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
            </script>

            @endpush
        </div>