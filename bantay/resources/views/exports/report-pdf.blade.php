<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #1f2937; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 2px; color: #0f2545; }
        .subtitle { color: #6b7280; margin-bottom: 4px; }
        .meta { color: #9ca3af; font-size: 10px; margin-bottom: 20px; }
        h2 { font-size: 14px; margin-top: 24px; margin-bottom: 6px; color: #163a6b; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .total-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; padding: 10px 14px; margin-bottom: 16px; display: inline-block; }
        .footer-note { margin-top: 30px; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <div class="subtitle">{{ $report['subtitle'] }}</div>
    <div class="meta">Generated {{ $report['generated_at']->format('M d, Y H:i') }} by {{ $generatedBy }}</div>

    <div class="total-box"><strong>Total Cases:</strong> {{ $report['total_cases'] }}</div>

    @foreach ($report['sections'] as $section)
        <h2>{{ $section['heading'] }}</h2>
        @if (count($section['rows']))
            <table>
                <thead>
                    <tr>
                        @foreach ($section['columns'] as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($section['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color:#9ca3af;">No data available for this section.</p>
        @endif
    @endforeach

    <div class="footer-note">
        BANTAY — Barangay-based Analysis of Notifiable Diseases for Tracking, Alerting, and Yielding Decision Support.
        This report supports professional review and is not a substitute for clinical diagnosis.
    </div>
</body>
</html>
