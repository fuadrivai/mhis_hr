@if (!empty($data['timeoff_type']))
    <tr>
        <td style="padding: 16px 15px; text-align: left; font-weight: bold; color: #800000">Type</td>
        <td style="padding: 16px 15px; text-align: left">
            <p style="margin: 0 0 8px; font-weight: bold; color: #333">: {{ $data['timeoff_type'] }}</p>
        </td>
    </tr>
@endif
@if (!empty($data['timeoff_start_time']) || !empty($data['timeoff_end_time']))
    <tr>
        <td style="padding: 16px 15px; text-align: left; font-weight: bold; color: #800000">Time</td>
        <td style="padding: 16px 15px; text-align: left">
            <p style="margin: 0 0 8px; font-weight: bold; color: #333">
                : {{ $data['timeoff_start_time'] ?? '' }}@if (!empty($data['timeoff_end_time']))
                    - {{ $data['timeoff_end_time'] }}
                @endif
            </p>
        </td>
    </tr>
@endif
@if (isset($data['timeoff_scheduled_days']))
    <tr>
        <td style="padding: 16px 15px; text-align: left; font-weight: bold; color: #800000">Day(s)</td>
        <td style="padding: 16px 15px; text-align: left">
            <p style="margin: 0 0 8px; font-weight: bold; color: #333">: {{ $data['timeoff_scheduled_days'] }}</p>
        </td>
    </tr>
@endif
@if (isset($data['timeoff_duration']))
    <tr>
        <td style="padding: 16px 15px; text-align: left; font-weight: bold; color: #800000">Duration</td>
        <td style="padding: 16px 15px; text-align: left">
            <p style="margin: 0 0 8px; font-weight: bold; color: #333">: {{ $data['timeoff_duration'] }}</p>
        </td>
    </tr>
@endif
