<div class="page-inner">
  <x-loading />
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title text-capitalize">
            <span>Convert List</span>
          </h4>
        </div>
      </div>
    </div>

    <div class="col-md-12" wire:poll="reloadTable">
      <div class="card">
        <div class="card-body">
          <livewire:table.temp.product-convert-table />
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
        })
  </script>
  @endpush
</div>