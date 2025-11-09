<div class="page-inner">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <a href="{{route('dashboard')}}">
                        <span><i class="fas fa-arrow-left mr-3"></i>Crud Generator</span>
                    </a>
                </h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group ">
                                <label for="table">Table</label>
                                <select wire:model="table" class="form-control">
                                    <option value="">Select Table</option>
                                    @foreach ($tables as $item)
                                    <option value="{{$item['name']}}">{{$item['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="table">File Name</label>
                                        <input type="text" wire:model="filename" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="table">Folder Name Space</label>
                                        <input type="text" wire:model="folder_namespace" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="table">Form Type</label>
                                        <select wire:model="form_type" class="form-control">
                                            <option value="">Select Form Type</option>
                                            <option value="modal">Modal</option>
                                            <option value="form">form</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @if ($table)
                            <div class="form-group">
                                <table width="100%">
                                    <tr>

                                        <td>Field</td>
                                        <td>Label</td>
                                        <td>Type</td>
                                        <td>Action</td>
                                    </tr>
                                    @foreach ($columns as $key => $column)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" value="{{$column}}">
                                        </td>
                                        <td>
                                            <input type="text" wire:model="field.label.{{$column}}" class="form-control"
                                                value="{{str_replace('_', '',$column)}}">
                                        </td>
                                        <td>
                                            <select wire:model="field.type.{{$column}}" class="form-control">
                                                <option value="text">text</option>
                                                <option value="textarea">textarea</option>
                                                <option value="number">number</option>
                                                <option value="richtext">richtext</option>
                                                <option value="select">select</option>
                                                <option value="hidden">hidden</option>
                                                <option value="date">date</option>
                                                <option value="image">image</option>
                                                <option value="file">File</option>
                                                <option value="multiple">multiple</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-sm"
                                                wire:click="delete({{$key}},'{{$column}}')"><i
                                                    class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            @endif



                            <div class=" form-group">
                                <button class="btn btn-primary btn-sm pull-right" wire:click="generate">save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>