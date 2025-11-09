<div class="page-inner">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title text-capitalize">
            <a href="{{route('role')}}">
              <span><i class="fas fa-arrow-left mr-3 text-capitalize"></i>Assign Permissions</span>
            </a>
          </h4>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="basic-datatables" class="display table table-striped table-hover">
              <thead>
                <tr>
                  <th>Permission Name</th>
                  <th>#</th>
                </tr>
              </thead>
              <tbody>
                @if (count($items))
                @foreach ($items as $item)
                <tr>
                  <td>{{ $item->permission_name }}</td>
                  <td>
                    @if ($item->roles->count() > 0)
                    <div wire.ignore>
                      <input type="checkbox" class="form-control" wire:model="permission_id" value="{{ $item->id }}"
                        name="permission_id[]" id="{{ $item->id }}"
                        {{ $item->id == $item->roles->first()->pivot->permission_id ? 'checked' : '' }}>
                    </div>
                    @else
                    <div wire.ignore>
                      <input type="checkbox" class="form-control" wire:model="permission_id" value="{{ $item->id }}"
                        name="permission_id[]" id="{{ $item->id }}">
                    </div>
                    @endif

                  </td>

                </tr>
                @endforeach
                @else
                <tr>
                  <td colspan="" class="text-center">
                    <h4 class="card-title">Tidak Ada Data</h4>
                  </td>
                </tr>
                @endif

              </tbody>
            </table>
          </div>
          <button wire:click="store" type="button" class="btn btn-primary btn-sm pull-right" id="btn-add">
            <i class="fas fa-check mr-2"></i>
            <span>Simpan</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>