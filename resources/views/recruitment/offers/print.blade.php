<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Offer — {{ $candidate->full_name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 40px;
            background-color: #fff;
        }
        .header {
            border-bottom: 2px solid #2A3F54;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2A3F54;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            color: #777;
            font-size: 14px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2A3F54;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }
        th {
            width: 200px;
            color: #555;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        td {
            border-bottom: 1px solid #eee;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #26B99A;
        }
        .no-print {
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #26B99A;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print / Save as PDF</button>
        <button class="btn-print" style="background-color: #777;" onclick="window.close()">Close Window</button>
    </div>

    <div class="header">
        <h1>MUTIARA HARAPAN ISLAMIC SCHOOL</h1>
        <p>Human Resources & Talent Acquisition Department</p>
    </div>

    <h2 style="margin-top: 0;">Official Job Offer Summary</h2>
    <p><strong>Offer Version:</strong> {{ $offer->version }} | <strong>Status:</strong> <span class="badge">{{ strtoupper($offer->status) }}</span></p>

    <div class="section-title">Candidate Details</div>
    <table>
        <tr>
            <th>Candidate Name</th>
            <td>{{ $candidate->full_name }}</td>
        </tr>
        <tr>
            <th>Candidate Code</th>
            <td>{{ $candidate->candidate_code ?: '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $candidate->email }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $candidate->phone }}</td>
        </tr>
    </table>

    <div class="section-title">Position & Employment Terms</div>
    <table>
        <tr>
            <th>Position Title</th>
            <td><strong>{{ $offer->position_title }}</strong></td>
        </tr>
        @if ($offer->grade_level)
            <tr>
                <th>Grade / Level</th>
                <td>{{ $offer->grade_level }}</td>
            </tr>
        @endif
        <tr>
            <th>Branch</th>
            <td>{{ $application->branch?->name ?: '-' }}</td>
        </tr>
        <tr>
            <th>Proposed Start Date</th>
            <td>{{ $offer->proposed_start_date ? $offer->proposed_start_date->format('d F Y') : 'To be mutually agreed' }}</td>
        </tr>
        <tr>
            <th>Offer Validity</th>
            <td>{{ $offer->valid_until ? $offer->valid_until->format('d F Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Compensation & Benefits Package</div>
    <table>
        <tr>
            <th>Basic Salary</th>
            <td>
                @if ($offer->canViewCompensation($user))
                    <strong>{{ $offer->getFormattedBasicSalaryFor($user) }}</strong> per month
                @else
                    <span style="color: #888;">[Restricted / Confidential]</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Allowances</th>
            <td>
                @if ($offer->canViewCompensation($user))
                    @php $allowances = $offer->getFormattedAllowancesFor($user); @endphp
                    @if (is_array($allowances) && count($allowances) > 0)
                        <ul>
                            @foreach ($allowances as $item)
                                <li>{{ $item['name'] ?? 'Allowance' }}: Rp {{ number_format((float) ($item['amount'] ?? 0), 0, ',', '.') }}</li>
                            @endforeach
                        </ul>
                    @else
                        <em>Standard package</em>
                    @endif
                @else
                    <span style="color: #888;">[Restricted / Confidential]</span>
                @endif
            </td>
        </tr>
        @if ($offer->other_benefits)
            <tr>
                <th>Other Benefits</th>
                <td>{!! nl2br(e($offer->other_benefits)) !!}</td>
            </tr>
        @endif
        @if ($offer->notes)
            <tr>
                <th>Notes / Special Terms</th>
                <td>{!! nl2br(e($offer->notes)) !!}</td>
            </tr>
        @endif
        @if ($offer->offer_letter_url)
            <tr>
                <th>Official Document / Drive Link</th>
                <td><a href="{{ $offer->offer_letter_url }}" target="_blank">{{ $offer->offer_letter_url }}</a></td>
            </tr>
        @endif
    </table>

    <div class="section-title">Approval Audit Trail</div>
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Level</th>
                <th>Approver</th>
                <th style="width: 100px;">Decision</th>
                <th style="width: 150px;">Date & Time</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($offer->approvalSteps as $step)
                <tr>
                    <td>Level {{ $step->level }}</td>
                    <td>{{ $step->approver?->personal?->first_name }} {{ $step->approver?->personal?->last_name }}</td>
                    <td><strong>{{ strtoupper($step->status) }}</strong></td>
                    <td>{{ $step->acted_at ? $step->acted_at->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $step->comment ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="color: #888;">No approvers assigned to this offer.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; font-size: 12px; color: #888;">
        Generated on {{ now()->format('d F Y H:i') }} by {{ $user->name }}. MHIS HR Recruitment Management System.
    </div>
</body>
</html>

