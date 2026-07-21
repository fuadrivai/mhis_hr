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
                <h2><i class="fa fa-calendar-check-o"></i> Edit Lesson Plan Target</h2>
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
                    <form action="{{ route('lesson-plan-target.update', $target->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Target Title / Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" value="{{ $target->description }}" required>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Submission Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline_date" class="form-control" value="{{ $target->deadline_date }}" required>
                            </div>
                        </div>
                        
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-12">
                                <label>Target Months <span class="text-danger">*</span></label>
                                <div id="months-container">
                                    @foreach($target->months as $index => $month)
                                    <div class="row month-row" style="margin-bottom: 10px;">
                                        <div class="col-md-3 col-sm-5 col-xs-5">
                                            <select name="months[]" class="form-control" required>
                                                <option value="January" {{ $month->month == 'January' ? 'selected' : '' }}>January</option>
                                                <option value="February" {{ $month->month == 'February' ? 'selected' : '' }}>February</option>
                                                <option value="March" {{ $month->month == 'March' ? 'selected' : '' }}>March</option>
                                                <option value="April" {{ $month->month == 'April' ? 'selected' : '' }}>April</option>
                                                <option value="May" {{ $month->month == 'May' ? 'selected' : '' }}>May</option>
                                                <option value="June" {{ $month->month == 'June' ? 'selected' : '' }}>June</option>
                                                <option value="July" {{ $month->month == 'July' ? 'selected' : '' }}>July</option>
                                                <option value="August" {{ $month->month == 'August' ? 'selected' : '' }}>August</option>
                                                <option value="September" {{ $month->month == 'September' ? 'selected' : '' }}>September</option>
                                                <option value="October" {{ $month->month == 'October' ? 'selected' : '' }}>October</option>
                                                <option value="November" {{ $month->month == 'November' ? 'selected' : '' }}>November</option>
                                                <option value="December" {{ $month->month == 'December' ? 'selected' : '' }}>December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-4 col-xs-4">
                                            <input type="number" name="years[]" class="form-control" value="{{ $month->year }}" required>
                                        </div>
                                        <div class="col-md-4 col-sm-3 col-xs-3" style="padding-top: 5px;">
                                            <label><input type="checkbox" name="has_5_weeks[{{ $index }}]" value="1" {{ $month->has_5_weeks ? 'checked' : '' }}> 5 Weeks?</label>
                                        </div>
                                        <div class="col-md-2 col-sm-12 col-xs-12" style="margin-top: 5px;">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success btn-add-month"><i class="fa fa-plus"></i></button>
                                            @else
                                                <button type="button" class="btn btn-danger btn-remove-month"><i class="fa fa-minus"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <hr style="border-top: 1px solid #ccc;">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Target</button>
                                <a href="{{ route('lesson-plan-target.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script>
        $(document).ready(function() {
            var monthIndex = {{ count($target->months) }};
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
