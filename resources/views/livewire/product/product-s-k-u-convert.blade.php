<div class="page-inner">
  <x-loading />
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title text-capitalize">
            <span>Convert Data</span>
            <div class="pull-right d-flex justify-between align-items-center">
              <x-button onClick="exportConvert" label="Download Merge Convert" />
              <x-button onClick="export" label="Download Detail Convert" />
            </div>
          </h4>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          @livewire('table.temp.product-temp-convert-table', [
          'params' => [
          'id' => $product_convert->id,
          'type' => 'all',
          'status' => 0,
          ]
          ])
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
          <x-text-field type="text" name="sku" label="SKU" placeholder="Masukkan SKU" isreq="*" />
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
        window.livewire.on('showModalUpdateConvert', (data) => {
            $('#convert-modal').modal(data)
        });
    })
</script>
@endpush