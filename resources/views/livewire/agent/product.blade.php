<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>List Data Product</span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="flex justify-between items-center mb-1">
                <div class="flex-grow h-10 flex items-center">
                    <div class="w-96 flex rounded-lg shadow-sm mb-6" style="width: 100%">
                        <div class="relative flex-grow focus-within:z-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" stroke="currentColor" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.debounce.500ms="search" class="w-full pl-10 py-3 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 focus:outline-none bg-transparent" placeholder="Cari Disini" type="text" />
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <button wire:click="$set('search', null)" class="text-gray-300 hover:text-red-600 focus:outline-none">
                                    <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach ($products as $prod)
        <div class="col-md-3 col-sm-6 col-6">
            <div class="card h-80">
                <img class="card-img-top p-2 " src="{{getImage($prod->image)}}" style="
                        height: 140px;
                        object-fit: contain;
                        display: block;
                        margin-left: auto;
                        margin-right: auto;
                    " />
                <div class="card-body">
                    <p class="line-clamp-2 h-12" style="font-size: 13px">
                        {{-- {{ substr($prod->name,0,15)}}.. --}}
                        {{$prod->name}}
                    </p>
                    <span style="color: red">Rp
                        {{ number_format($prod->price['final_price'],0,2) }}</span><br />
                    <span style="text-decoration: line-through; font-size: 12px">Rp
                        {{ number_format($prod->price['basic_price'],0,2) }}</span><br />
                    <button class="btn btn-primary btn-sm" style="width: 100%" wire:click="add_cart('{{$prod->id}}')">
                        + Keranjang
                    </button>
                </div>
            </div>
        </div>
        @endforeach @push('scripts')
        <script src="{{
                asset('assets/js/plugin/summernote/summernote-bs4.min.js')
            }}"></script>
        <script src="{{
                asset('assets/js/plugin/datatables/datatables.min.js')
            }}"></script>
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
                    $('#basic-datatables').DataTable({});
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
</div>