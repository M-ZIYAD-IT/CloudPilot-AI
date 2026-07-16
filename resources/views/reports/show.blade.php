<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cloud Migration Readiness Report</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; color: #1f2937; max-width: 860px; margin: 0 auto; padding: 24px; line-height: 1.5; }
        h1, h2 { color: #111827; }
        h1 { font-size: 1.75rem; margin-bottom: 0.25rem; }
        h2 { font-size: 1.25rem; margin-top: 2.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem; }
        .disclaimer { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin: 16px 0; }
        .headline { display: flex; gap: 24px; flex-wrap: wrap; margin: 16px 0; }
        .stat { background: #f9fafb; border-radius: 8px; padding: 16px 20px; min-width: 160px; }
        .stat .value { font-size: 2rem; font-weight: 700; color: #111827; }
        .stat .label { font-size: 0.85rem; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 0.92rem; }
        th { color: #6b7280; font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; background: #eef2ff; color: #3730a3; }
        .charts { display: flex; gap: 24px; flex-wrap: wrap; }
        .charts img { max-width: 100%; }
        .applicable { color: #b91c1c; font-weight: 600; }
        .not-applicable { color: #9ca3af; }
        footer { margin-top: 3rem; font-size: 0.8rem; color: #9ca3af; }
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

    <h1>Cloud Migration Readiness Report</h1>
    <p style="color:#6b7280;">Generated {{ $report->generated_at?->toFormattedDateString() }} &middot; Scoring engine {{ $result['engine_version'] }}</p>

    <div class="disclaimer">
        <strong>Estimate, not a quote.</strong> Figures in this report are generated from a curated static price table (prices as of {{ \Illuminate\Support\Carbon::parse($result['price_table']['_meta']['as_of_date'])->toFormattedDateString() }}) and self-reported survey answers. They are directional planning inputs, not a binding quote from any cloud provider.
    </div>

    <h2>Executive Summary</h2>
    @if($narrative && !empty($narrative['executive_summary']))
        <p>{{ $narrative['executive_summary'] }}</p>
    @else
        <p><em>Narrative summary is not yet available for this report.</em></p>
    @endif

    <div class="headline">
        <div class="stat">
            <div class="value">{{ $result['readiness']['overall'] }}/100</div>
            <div class="label">Overall readiness score</div>
        </div>
        <div class="stat">
            <div class="value" style="font-size:1.4rem;">{{ $platformName($result['platform']['recommended']) }}</div>
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
        <p style="font-size:0.85rem;color:#6b7280;">Utilization discount factor applied: {{ $result['tco']['utilization_discount_factor'] * 100 }}% (on-prem servers typically run at 15&ndash;25% utilization).</p>
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
        <p>{{ $narrative['risk_mitigations'] }}</p>
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
    <ul>
        <li>Review this report with your leadership team and IT stakeholders.</li>
        <li>Validate compliance findings with legal/compliance counsel before acting on them.</li>
        <li>Engage a migration partner to scope Phase 0 (landing zone &amp; identity).</li>
    </ul>

    <footer>
        This report is an automated estimate. It is not a substitute for professional legal, compliance, or financial advice.
    </footer>

</body>
</html>
