<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090909">
    <title>Cloud Migration Readiness Report — {{ $clientName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --canvas: #090909;
            --surface-1: #141414;
            --surface-2: #1c1c1c;
            --ink: #ffffff;
            --ink-muted: #999999;
            --hairline: #262626;
            --hairline-soft: #1a1a1a;
            --accent: #0099ff;
            --success: #22c55e;
            --danger: #f87171;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--canvas);
            color: var(--ink);
            max-width: 880px;
            margin: 0 auto;
            padding: 40px 24px 80px;
            line-height: 1.5;
            font-size: 15px;
            letter-spacing: -0.15px;
        }
        h1, h2, h3 { color: var(--ink); font-weight: 500; margin: 0; }
        h1 { font-size: 32px; letter-spacing: -1px; line-height: 1.13; }
        h2 { font-size: 22px; font-weight: 700; letter-spacing: -0.5px; margin-top: 56px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--hairline-soft); }
        .meta { color: var(--ink-muted); font-size: 14px; margin-top: 6px; }
        .disclaimer { background: rgba(0, 153, 255, 0.08); border: 1px solid rgba(0, 153, 255, 0.25); color: var(--ink); padding: 14px 18px; border-radius: 10px; font-size: 14px; margin: 24px 0; }
        .disclaimer strong { color: var(--accent); }
        .headline { display: flex; gap: 16px; flex-wrap: wrap; margin: 24px 0; }
        .stat { background: var(--surface-1); border: 1px solid var(--hairline-soft); border-radius: 15px; padding: 20px 24px; min-width: 200px; flex: 1; }
        .stat .value { font-size: 32px; font-weight: 500; letter-spacing: -1px; color: var(--ink); }
        .stat .label { font-size: 13px; font-weight: 500; letter-spacing: -0.13px; color: var(--ink-muted); margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid var(--hairline-soft); font-size: 14px; }
        th { color: var(--ink-muted); font-weight: 500; letter-spacing: -0.14px; }
        td { color: var(--ink); }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 500; background: rgba(0, 153, 255, 0.15); color: var(--accent); }
        .charts { background: var(--surface-1); border: 1px solid var(--hairline-soft); border-radius: 15px; padding: 16px; display: flex; gap: 24px; flex-wrap: wrap; }
        .charts img { max-width: 100%; border-radius: 10px; display: block; }
        .applicable { color: var(--danger); font-weight: 500; }
        .not-applicable { color: var(--ink-muted); }
        .narrative { color: var(--ink-muted); font-size: 15px; }
        .next-steps { color: var(--ink-muted); padding-left: 20px; }
        .next-steps li { margin-bottom: 8px; }
        footer { margin-top: 64px; padding-top: 24px; border-top: 1px solid var(--hairline-soft); font-size: 12px; color: var(--ink-muted); }
        em { color: var(--ink-muted); font-style: normal; }

        .report-toolbar {
            position: fixed;
            top: 24px;
            right: 24px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            z-index: 10;
        }

        .download-btn,
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: -0.14px;
            padding: 10px 18px;
            border-radius: 100px;
            white-space: nowrap;
            transition: background-color 0.15s ease-out;
        }
        .download-btn svg, .back-btn svg { display: block; }

        .download-btn {
            background: var(--ink);
            color: #000000;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .download-btn:hover { background: #e5e5e5; }

        .back-btn {
            background: var(--surface-1);
            color: var(--ink);
            text-decoration: none;
            border: 1px solid var(--hairline);
        }
        .back-btn:hover { background: var(--surface-2); }

        @media print {
            body { background: #ffffff; color: #111827; }
            h1, h2, h3, td { color: #111827; }
            .stat, .charts { background: #f9fafb; border-color: #e5e7eb; }
            th { color: #6b7280; }
            td, .meta, .narrative, .next-steps, footer { color: #374151; }
            .disclaimer { background: #fffbeb; border-color: #fde68a; color: #92400e; }
            .disclaimer strong { color: #92400e; }
            table, th, td { border-color: #e5e7eb; }
            .badge { background: #eef2ff; color: #3730a3; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    @php
        $platformNames = [
            'aws' => 'AWS',
            'azure' => 'Azure',
            'gcp' => 'GCP',
            'stc_cloud' => 'STC Cloud',
            'oracle_jeddah' => 'Oracle Cloud (Jeddah)',
        ];
        $platformName = fn (string $key) => $platformNames[$key] ?? $key;
    @endphp

    <div class="report-toolbar no-print">
        <a href="{{ url('/dashboard') }}" class="back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Dashboard
        </a>

        <button type="button" class="download-btn" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
            </svg>
            Download PDF
        </button>
    </div>

    <p class="meta" style="margin-top:0;">Prepared for <strong style="color:var(--ink);">{{ $clientName }}</strong></p>
    <h1>Cloud Migration Readiness Report</h1>
    <p class="meta">Generated {{ $report->generated_at?->toFormattedDateString() }} &middot; Scoring engine {{ $result['engine_version'] }}</p>

    <div class="disclaimer">
        <strong>Estimate, not a quote.</strong> Figures in this report are generated from a curated static price table (prices as of {{ \Illuminate\Support\Carbon::parse($result['price_table']['_meta']['as_of_date'])->toFormattedDateString() }}) and self-reported survey answers. They are directional planning inputs, not a binding quote from any cloud provider.
    </div>

    <h2>Executive Summary</h2>
    @if($narrative && !empty($narrative['executive_summary']))
        <p class="narrative">{{ $narrative['executive_summary'] }}</p>
    @else
        <p><em>Narrative summary is not yet available for this report.</em></p>
    @endif

    <div class="headline">
        <div class="stat">
            <div class="value">{{ $result['readiness']['overall'] }}/100</div>
            <div class="label">Overall readiness score</div>
        </div>
        <div class="stat">
            <div class="value" style="font-size:22px;">{{ $platformName($result['platform']['recommended']) }}</div>
            <div class="label">Recommended platform</div>
        </div>
    </div>

    <h2>Cloud Readiness Assessment</h2>
    <div class="charts">
        <img src="{{ $radarChartUrl }}" alt="Readiness radar chart by dimension" width="420" height="260">
    </div>
    <table>
        <thead><tr><th>Dimension</th><th>Score</th></tr></thead>
        <tbody>
        @foreach($result['readiness']['dimensions'] as $dimension => $score)
            <tr><td>{{ ucfirst($dimension) }}</td><td>{{ $score }}/100</td></tr>
        @endforeach
        </tbody>
    </table>

    <h2>Platform Comparison</h2>
    <table>
        <thead><tr><th>Platform</th><th>Score</th></tr></thead>
        <tbody>
        @foreach($result['platform']['scores'] as $platform => $score)
            <tr>
                <td>{{ $platformName($platform) }} @if($platform === $result['platform']['recommended'])<span class="badge">Recommended</span>@endif</td>
                <td>{{ $score }}/100</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Workload Disposition Map (6R)</h2>
    <table>
        <thead><tr><th>Application</th><th>Category</th><th>Strategy</th><th>Justification</th></tr></thead>
        <tbody>
        @forelse($result['six_r'] as $app)
            @php
                $narrativeJustification = collect($narrative['app_justifications'] ?? [])->firstWhere('name', $app['name'])['justification'] ?? null;
            @endphp
            <tr>
                <td>{{ $app['name'] }}</td>
                <td>{{ $app['category'] }}</td>
                <td><span class="badge">{{ $app['strategy'] }}</span></td>
                <td>{{ $narrativeJustification ?? $app['justification'] }}</td>
            </tr>
        @empty
            <tr><td colspan="4"><em>No applications were declared in this assessment.</em></td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Cost Analysis (TCO)</h2>
    @if($tcoChartUrl)
        <div class="charts">
            <img src="{{ $tcoChartUrl }}" alt="TCO comparison chart" width="420" height="260">
        </div>
        <table>
            <thead><tr><th>Scenario</th><th>Estimated annual cost</th></tr></thead>
            <tbody>
                <tr><td>Current (on-prem)</td><td>${{ number_format($result['tco']['current_annual_estimate']) }}</td></tr>
                <tr><td>Cloud &mdash; optimistic</td><td>${{ number_format($result['tco']['cloud_annual_projection']['optimistic']) }}</td></tr>
                <tr><td>Cloud &mdash; expected</td><td>${{ number_format($result['tco']['cloud_annual_projection']['expected']) }}</td></tr>
                <tr><td>Cloud &mdash; pessimistic</td><td>${{ number_format($result['tco']['cloud_annual_projection']['pessimistic']) }}</td></tr>
                <tr><td>One-time migration estimate</td><td>${{ number_format($result['tco']['migration_one_time_estimate']) }}</td></tr>
            </tbody>
        </table>
        <p class="meta">Utilization discount factor applied: {{ $result['tco']['utilization_discount_factor'] * 100 }}% (on-prem servers typically run at 15&ndash;25% utilization).</p>
    @else
        <p><em>{{ $result['tco']['note'] ?? 'A TCO projection could not be computed from the answers provided.' }}</em></p>
    @endif

    <h2>Compliance &amp; Risk Register</h2>
    <table>
        <thead><tr><th>Regulation</th><th>Applicable</th><th>Satisfying providers</th></tr></thead>
        <tbody>
        @foreach($result['compliance']['register'] as $entry)
            <tr>
                <td>{{ $entry['regulation'] }}</td>
                <td class="{{ $entry['applicable'] ? 'applicable' : 'not-applicable' }}">{{ $entry['applicable'] ? 'Yes' : 'No' }}</td>
                <td>{{ implode(', ', array_map($platformName, $entry['satisfying_providers'])) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($narrative && !empty($narrative['risk_mitigations']))
        <p class="narrative">{{ $narrative['risk_mitigations'] }}</p>
    @endif

    <h2>Phased Roadmap</h2>
    <table>
        <thead><tr><th>Phase</th><th>Focus</th></tr></thead>
        <tbody>
            <tr><td>Phase 0</td><td>Landing zone &amp; identity foundation</td></tr>
            <tr><td>Phase 1</td><td>Quick wins (email, file shares, disaster recovery)</td></tr>
            <tr><td>Phase 2</td><td>Core application migration</td></tr>
            <tr><td>Phase 3</td><td>Optimization &amp; modernization</td></tr>
        </tbody>
    </table>

    <h2>Next Steps</h2>
    <ul class="next-steps">
        <li>Review this report with your leadership team and IT stakeholders.</li>
        <li>Validate compliance findings with legal/compliance counsel before acting on them.</li>
        <li>Engage a migration partner to scope Phase 0 (landing zone &amp; identity).</li>
    </ul>

    <footer>
        This report is an automated estimate. It is not a substitute for professional legal, compliance, or financial advice.
    </footer>

    <script>
        // Lets other pages link straight to a download (e.g. a "Download PDF"
        // button elsewhere) without needing a second signed URL — the hash
        // isn't sent to the server, so it can't invalidate the signature.
        if (window.location.hash === '#download') {
            window.print();
        }
    </script>
</body>
</html>
