<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            -webkit-print-color-adjust: exact;
        }
        td {
            padding: 8px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .progress-text {
            font-size: 11px;
            margin-top: 4px;
            color: #555;
        }
        .bar-container {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            height: 16px;
            display: flex;
            -webkit-print-color-adjust: exact;
        }
        .bar-success {
            background-color: #28a745;
            color: white;
            text-align: center;
            font-size: 10px;
            line-height: 16px;
            -webkit-print-color-adjust: exact;
        }
        .bar-warning {
            background-color: #ffc107;
            color: black;
            text-align: center;
            font-size: 10px;
            line-height: 16px;
            -webkit-print-color-adjust: exact;
        }
        .bar-primary {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-size: 10px;
            line-height: 16px;
            -webkit-print-color-adjust: exact;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-size: 14px;">Print Now</button>
    </div>

    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Deadline: {{ \Carbon\Carbon::parse($target->deadline_date)->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Category</th>
                <th style="width: 35%;">Subject Name</th>
                <th style="width: 50%;">Overall Progress</th>
            </tr>
        </thead>
        <tbody>
            @if(empty($groupedData))
                <tr>
                    <td colspan="3" class="text-center">No data available for the selected categories.</td>
                </tr>
            @else
                @foreach($groupedData as $subjectId => $data)
                    <tr>
                        <td>{{ $data['category_name'] }}</td>
                        <td><strong>{{ $data['subject_name'] }}</strong></td>
                        <td>
                            <div class="bar-container">
                                @if($data['progress_approved'] > 0)
                                    <div class="bar-success" style="width: {{ $data['progress_approved'] }}%;">{{ $data['total_approved'] }}</div>
                                @endif
                                @if($data['progress_revision'] > 0)
                                    <div class="bar-warning" style="width: {{ $data['progress_revision'] }}%;">{{ $data['total_revision'] }}</div>
                                @endif
                                @if($data['progress_submitted'] > 0)
                                    <div class="bar-primary" style="width: {{ $data['progress_submitted'] }}%;">{{ $data['total_submitted'] }}</div>
                                @endif
                            </div>
                            <div class="progress-text text-center">
                                Approved: <strong>{{ $data['total_approved'] }}</strong> | Revision: <strong>{{ $data['total_revision'] }}</strong> | Submitted: <strong>{{ $data['total_submitted'] }}</strong> | Expected: <strong>{{ $data['total_expected'] }}</strong>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
