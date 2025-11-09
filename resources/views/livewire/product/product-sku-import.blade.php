<div class="page-inner">
  <x-loading />
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title text-capitalize">
            <span>Import Data</span>
            <div class="pull-right d-flex justify-between align-items-center">
              @if ($showConvert)
              {{--
              <x-button onClick="convert" label="Convert Data" icon="fas fa-plus" /> --}}
              <x-button onClick="convert" label="Convert Data" variant="success" />
              {{-- <button class="btn btn-success btn-sm" wire:click="convert">Convert Data</button> --}}
              @endif
              @if ($progress)
              <button class="btn btn-primary btn-sm" disabled><i class="fas fa-plus"></i> Convert</button>
              @else
              @if ($showConvert)
              {{--
              <x-button onClick="convert" label="Convert Data" icon="fas fa-plus" /> --}}
              <button class="btn btn-danger btn-sm ml-2" wire:click="onDiscard">Discard</button>
              @else
              <x-button onClick="$emit('showModalImport','show')" label="Import" icon="fas fa-cloud-download-alt" />
              @endif

              @endif
            </div>
          </h4>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div>
        @livewire('components.loading-import')
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Total Success: <span class="text-success">{{$total_success}}</span> | Total Error: <span class="text-danger">{{$total_error}}</span></div>
        </div>
        <div class="card-body">
          <livewire:table.temp.product-temp-import-table />
        </div>
      </div>
    </div>


    {{-- import excel --}}
    <div id="import-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
      <div class="modal-dialog" permission="document">
        <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
          <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
            <h5 class="modal-title" id="my-modal-title">Import Data</h5>
          </div>
          <div class="modal-body">
            {{-- loading --}}
            <div wire:loading wire:target="file_path,saveImport,_reset" class="loading">
            </div>
            <div class="alert alert-warning">
              <p>Pastikan data yang di import sesuai dengan template yang sudah di sediakan. <span wire:click="downloadSample" class="cursor-pointer text-primary">Klik Disini Untuk Download sample format import</span></p>
            </div>
            {{-- <img src="{{asset('assets/img/loader.gif')}}" alt="loader"> --}}
            <x-input-file file="{{$file}}" path="{{optional($file_path)->getClientOriginalName()}}" name="file_path" label="Input File" />
          </div>
          <div class="modal-footer">
            <button type="submit" wire:click='saveImport' class="btn btn-success btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
            <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
          </div>
        </div>
      </div>
    </div>

    <div id="convert-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
      <div class="modal-dialog" permission="document">
        <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
          <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
            <h5 class="modal-title" id="my-modal-title">Import Data</h5>
          </div>
          <div class="modal-body">
            @if ($convertData)
            @livewire('components.convert-progress',['data' => $convertData])
            @endif

          </div>
          <div class="modal-footer">
            <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</a>
          </div>
        </div>
      </div>
    </div>


    {{-- update --}}
    <div id="update-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
      <div class="modal-dialog" permission="document">
        <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
          <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
            <h5 class="modal-title" id="my-modal-title">Import Data</h5>
          </div>
          <div class="modal-body">
            <x-text-field type="text" name="sku" label="SKU" placeholder="Masukkan SKU" isreq="*" />
            <x-text-field type="text" name="harga_awal" label="Harga Awal" placeholder="Masukkan Harga Awal" isreq="*" />
            <x-text-field type="text" name="harga_promo" label="Harga Promo" placeholder="Masukkan Harga Promo" isreq="*" />
            <x-text-field type="text" name="qty" label="QTY" placeholder="Masukkan QTY" isreq="*" />
          </div>
          <div class="modal-footer">
            <button type="submit" wire:click='saveUpdate' class="btn btn-success btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
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
            window.livewire.on('showModalImport', (data) => {
                $('#import-modal').modal(data)
            });

            window.livewire.on('showModalProgressConvert', (data) => {
                $('#convert-modal').modal(data)
            });

            window.livewire.on('showModalImportUpdate', (data) => {
                $('#update-modal').modal(data)
            });
        })
  </script>
  @endpush
</div>