@extends('layouts.main-layout')

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>{{ optional($employee->personal)->fullname ?? 'Employee ' . $employee->id }} - {{ $group->name }}</h2>
            <div class="pull-right">
                <a href="{{ route('setting.hourly-time-off.show', $group->id) }}" class="btn btn-secondary btn-sm">Back to
                    balances</a>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <p><strong>Period:</strong> {{ $balance->period_start->format('F Y') }}</p>
            <div class="row">
                <div class="col-md-4"><strong>Allocated:</strong> {{ number_format((float) $balance->allocated_hours, 2) }}
                    hours</div>
                <div class="col-md-4"><strong>Used:</strong> {{ number_format((float) $balance->used_hours, 2) }} hours</div>
                <div class="col-md-4"><strong>Remaining:</strong> {{ number_format((float) $balance->remaining_hours, 2) }}
                    hours</div>
            </div>
            <h4 class="mt-4">Transaction History</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time Off</th>
                            <th>Hours</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                <td>{{ optional($transaction->timeoff)->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format((float) $transaction->hours, 2) }}</td>
                                <td>{{ ucfirst($transaction->transaction_type) }}</td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No transactions for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
