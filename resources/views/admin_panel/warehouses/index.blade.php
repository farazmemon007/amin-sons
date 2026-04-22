@extends('admin_panel.layout.app')
@section('content')

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">

            <div class="page-header row">
                <div class="page-title col-lg-6">
                    <h4>Warehouse List</h4>
                    <h6>Manage Warehouses</h6>
                </div>
                <div class="page-btn d-flex justify-content-end col-lg-6">
                    @can('warehouse.create')
                    <button class="btn btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#warehouseModal" onclick="clearWarehouse()">Add Warehouse</button>
                    @endcan
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success"><strong>Success!</strong> {{ session('success') }}</div>
                    @endif

                    <table class="table datanew">
                        <thead><tr><th>#</th><th>Created By</th><th>Name</th><th>Location</th><th>Remarks</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($warehouses as $key => $w)
                            <tr>
                                <td>{{ $key+1 }}</td><td>{{ $w->user->name }}</td><td>{{ $w->warehouse_name }}</td><td>{{ $w->location }}</td><td>{{ $w->remarks }}</td>
                                <td>
                                    @can('warehouse.edit')
                                    <button class="btn btn-sm btn-primary edit-warehouse-btn"
                                        data-id="{{ $w->id }}"
                                        data-name="{{ $w->warehouse_name }}"
                                        data-location="{{ $w->location }}"
                                        data-remarks="{{ $w->remarks }}"
                                        data-branches="{{ $w->branches->pluck('id')->implode(',') }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#warehouseModal">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('warehouse.delete')
                                    <a href="{{ url('warehouse/delete/'.$w->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

                    <div class="modal fade" id="warehouseModal">
    <div class="modal-dialog">
        <form action="{{ url('warehouse/store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="warehouse_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add/Edit Warehouse</h5></div>
                <div class="modal-body">
                    @if(Auth::check() && Auth::user()->hasRole('super admin'))
                        <div class="mb-2">
                            <label>Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name ?? 'Branch '.$b->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id }}">
                    @endif
                    <div class="mb-2"><input class="form-control" name="warehouse_name" id="warehouse_name" placeholder="Name" required></div>
                    <div class="mb-2 d-none"><input class="form-control" name="creater_id" id="" value="{{ Auth()->user()->id }}" placeholder="Name" required></div>
                    <div class="mb-2"><input class="form-control" name="location" id="location" placeholder="Location"></div>
                    <div class="mb-2"><textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

@endsection

    {{-- @push('scripts') --}}
    @section('js')
        <script>
    function clearWarehouse(){ $('#warehouse_id').val(''); $('#warehouse_name').val(''); $('#location').val(''); $('#remarks').val(''); }

        // function editWarehouse(id,name,location,remarks){
        //     alert(id);
        //     $('#warehouse_id').val(id);
        //     $('#warehouse_name').val(name);
        //     $('#location').val(location);
        //     $('#remarks').val(remarks);
        //  }

        // Handle Edit button click
        $(document).on('click', '.edit-warehouse-btn', function () {
            // alert("Edit button clicked ✅"+ $(this).data('id'));

            $('#warehouse_id').val($(this).data('id'));
            $('#warehouse_name').val($(this).data('name'));
            $('#location').val($(this).data('location'));
            $('#remarks').val($(this).data('remarks'));
            // set branch selection (if present)
            var branches = $(this).data('branches') || '';
            if ($('#branch_id').length) {
                if (branches) {
                    // pick first associated branch
                    var first = String(branches).split(',')[0];
                    $('#branch_id').val(first);
                } else {
                    // if no branches attached, clear for super admin
                    $('#branch_id').val('');
                }
            } else {
                // hidden input for non-super user — set value to user's branch (no-op)
                // nothing to do
            }
        });


    $('.datanew').DataTable();
    </script>
    {{-- @endpush --}}

@endsection
