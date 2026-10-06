<div class="form-section">
    <form action="{{ route('assessment-setting.monitor.store') }}" method="POST" class="row">
        @csrf
        <div class="col-md-4 col-sm-4 col-xs-12 form-group">
            <select name="subject_category_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Category --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5 col-sm-5 col-xs-12 form-group">
            <select name="employee_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Monitor (Employee) --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Add Monitor</button>
        </div>
    </form>
</div>
<table class="table table-striped table-bordered datatable" style="width: 100%">
    <thead>
        <tr>
            <th>Category</th>
            <th>Monitor</th>
            <th style="width: 15%;">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($monitors as $m)
            <tr>
                <td><span class="label label-info">{{ $m->subjectCategory->name ?? '' }}</span></td>
                <td><strong>{{ $m->employee->user->name ?? 'Unknown User' }}</strong></td>
                <td>
                    <form action="{{ route('assessment-setting.monitor.destroy', $m->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this monitor?')"><i class="fa fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
