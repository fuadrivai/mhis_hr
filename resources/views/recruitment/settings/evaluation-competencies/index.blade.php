@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="status">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>Evaluation Competencies <small>Used by interviewers when completing recommendation forms</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <button type="button" class="btn btn-success btn-sm text-white" data-toggle="modal" data-target="#modal-add-competency">
                            <i class="fa fa-plus"></i> Add Competency
                        </button>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content table-responsive">
                <table class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">Order</th>
                            <th style="width: 220px;">Competency Name</th>
                            <th>Description</th>
                            <th style="width: 100px;" class="text-center">Status</th>
                            <th style="width: 140px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencies as $comp)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $comp->sort_order }}</td>
                                <td><strong>{{ $comp->name }}</strong></td>
                                <td>{{ $comp->description ?? '-' }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('recruitment.settings.evaluation-competencies.toggle', $comp->id) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs {{ $comp->is_active ? 'btn-success' : 'btn-default' }}" title="Click to toggle status">
                                            {{ $comp->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-edit-competency-{{ $comp->id }}">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('recruitment.settings.evaluation-competencies.destroy', $comp->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this competency?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="modal-edit-competency-{{ $comp->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('recruitment.settings.evaluation-competencies.update', $comp->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                                                <h4 class="modal-title">Edit Competency</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Competency Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $comp->name) }}" required maxlength="100">
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea name="description" class="form-control" rows="3" maxlength="1000">{{ old('description', $comp->description) }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Sort Order</label>
                                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $comp->sort_order) }}" min="0">
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="is_active" value="1" {{ $comp->is_active ? 'checked' : '' }}> Active (visible on evaluation forms)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No evaluation competencies configured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Competency Modal -->
    <div class="modal fade" id="modal-add-competency" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('recruitment.settings.evaluation-competencies.store') }}">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Add Evaluation Competency</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Competency Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Pedagogical Leadership" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief explanation of what behaviors or criteria this competency evaluates" maxlength="1000"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ count($competencies) + 1 }}" min="0">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Active (visible on evaluation forms)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Competency</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

