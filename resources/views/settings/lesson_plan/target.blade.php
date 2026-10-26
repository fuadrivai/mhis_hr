@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-calendar-check-o"></i> Lesson Plan Targets</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="form-section">
                    <form action="{{ route('lesson-plan-target.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Target Title / Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" placeholder="e.g. August 16 Target for Sept/Oct" required>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Submission Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-12 form-group">
                                <label>Applicable Subject Categories <small class="text-muted">(Optional: Leave unselected to apply to all categories, or select specific categories)</small></label>
                                <div style="background: #fff; border: 1px solid #ccc; padding: 10px 15px; border-radius: 4px; max-height: 160px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach($categories as $cat)
                                            <div class="col-md-3 col-sm-4 col-xs-6">
                                                <label style="font-weight: normal; cursor: pointer;">
                                                    <input type="checkbox" name="subject_category_ids[]" value="{{ $cat->id }}"> {{ $cat->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-12">
                                <label>Target Months <span class="text-danger">*</span></label>
                                <div id="months-container">
                                    <div class="row month-row" style="margin-bottom: 10px;">
                                        <div class="col-md-3 col-sm-5 col-xs-5">
                                            <select name="months[]" class="form-control" required>
                                                <option value="January">January</option>
                                                <option value="February">February</option>
                                                <option value="March">March</option>
                                                <option value="April">April</option>
                                                <option value="May">May</option>
                                                <option value="June">June</option>
                                                <option value="July">July</option>
                                                <option value="August">August</option>
                                                <option value="September">September</option>
                                                <option value="October">October</option>
                                                <option value="November">November</option>
                                                <option value="December">December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-4 col-xs-4">
                                            <input type="number" name="years[]" class="form-control" value="{{ date('Y') }}" required>
                                        </div>
                                        <div class="col-md-4 col-sm-3 col-xs-3" style="padding-top: 5px;">
                                            <label><input type="checkbox" name="has_5_weeks[0]" value="1"> 5 Weeks?</label>
                                        </div>
                                        <div class="col-md-2 col-sm-12 col-xs-12" style="margin-top: 5px;">
                                            <button type="button" class="btn btn-success btn-add-month"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="border-top: 1px solid #ccc;">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Target</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table class="table table-striped table-bordered datatable" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="width: 25%">Description</th>
                            <th style="width: 15%">Deadline Date</th>
                            <th style="width: 25%">Target Months</th>
                            <th style="width: 20%">Applicable Categories</th>
                            <th style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                        <tr>
                            <td><strong>{{ $target->description }}</strong></td>
                            <td><span class="badge bg-red"><i class="fa fa-clock-o"></i> {{ $target->deadline_date }}</span></td>
                            <td>
                                @foreach($target->months as $m)
                                    <span class="label label-info" style="margin-right: 5px;"><i class="fa fa-calendar"></i> {{ $m->month }} {{ $m->year }} {{ $m->has_5_weeks ? '(5 Weeks)' : '' }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($target->subjectCategories->isEmpty())
                                    <span class="label label-success"><i class="fa fa-globe"></i> All Categories</span>
                                @else
                                    @foreach($target->subjectCategories as $sc)
                                        <span class="label label-primary" style="margin-right: 3px; display: inline-block; margin-bottom: 2px;"><i class="fa fa-tag"></i> {{ $sc->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('lesson-plan-target.destroy', $target->id) }}" method="POST">
                                    <a href="{{ route('lesson-plan-target.edit', $target->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this target?');"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".datatable").DataTable();
            
            var monthIndex = 1;
            $(document).on('click', '.btn-add-month', function() {
                var newRow = $('.month-row').first().clone();
                newRow.find('.btn-add-month').removeClass('btn-add-month btn-success').addClass('btn-remove-month btn-danger').html('<i class="fa fa-minus"></i>');
                
                // Update checkbox name index
                var checkbox = newRow.find('input[type="checkbox"]');
                checkbox.attr('name', 'has_5_weeks[' + monthIndex + ']');
                checkbox.prop('checked', false);

                $('#months-container').append(newRow);
                monthIndex++;
            });

            $(document).on('click', '.btn-remove-month', function() {
                $(this).closest('.month-row').remove();
            });
        });
    </script>
@endsection
