<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090909">
    <title>Cloud Readiness Report — CloudPilot AI</title>
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
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--canvas);
            color: var(--ink);
            max-width: 720px;
            margin: 0 auto;
            padding: 64px 24px 80px;
            line-height: 1.5;
            font-size: 15px;
            letter-spacing: -0.15px;
        }
        h1 { font-size: 32px; font-weight: 500; letter-spacing: -1px; line-height: 1.13; margin: 0 0 12px; }
        p.lead { color: var(--ink-muted); font-size: 16px; margin: 0 0 32px; }
        .headline { display: flex; gap: 16px; flex-wrap: wrap; margin: 24px 0 40px; }
        .stat { background: var(--surface-1); border: 1px solid var(--hairline-soft); border-radius: 15px; padding: 20px 24px; min-width: 200px; flex: 1; }
        .stat .value { font-size: 32px; font-weight: 500; letter-spacing: -1px; color: var(--ink); }
        .stat .label { font-size: 13px; font-weight: 500; letter-spacing: -0.13px; color: var(--ink-muted); margin-top: 4px; }
        .locked-card {
            background: var(--surface-1);
            border: 1px solid var(--hairline-soft);
            border-radius: 20px;
            padding: 32px;
            margin-top: 8px;
        }
        .locked-card h2 { font-size: 20px; font-weight: 700; margin: 0 0 16px; }
        .locked-card ul { margin: 0 0 28px; padding-left: 20px; color: var(--ink-muted); }
        .locked-card li { margin-bottom: 8px; }
        .unlock-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: -0.14px;
            padding: 12px 24px;
            min-height: 44px;
            border-radius: 100px;
            white-space: nowrap;
            background: var(--ink);
            color: #000000;
            text-decoration: none;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .unlock-btn:hover { background: #e5e5e5; }
        .price-note { color: var(--ink-muted); font-size: 13px; margin-top: 14px; }
        footer { margin-top: 64px; padding-top: 24px; border-top: 1px solid var(--hairline-soft); font-size: 12px; color: var(--ink-muted); }
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
        $platformName = $platformNames[$platformRecommended] ?? $platformRecommended;
    @endphp

    <h1>Your cloud readiness summary</h1>
    <p class="lead">Here's the headline. The full report — platform comparison, 6R migration map, cost projection, and compliance register — unlocks after a one-time payment.</p>

    <div class="headline">
        <div class="stat">
            <div class="value">{{ $readinessOverall }} / 100</div>
            <div class="label">Readiness score</div>
        </div>
        <div class="stat">
            <div class="value">{{ $platformName }}</div>
            <div class="label">Recommended platform</div>
        </div>
    </div>

    <div class="locked-card">
        <h2>Unlock the full report</h2>
        <ul>
            <li>Full readiness breakdown across infrastructure, data, security, finance, and applications</li>
            <li>Ranked platform comparison across AWS, Azure, GCP, STC Cloud, and Oracle Cloud</li>
            <li>6R migration map for every declared application</li>
            <li>Cost projection — current spend vs. optimistic, expected, and pessimistic cloud TCO</li>
            <li>Compliance &amp; risk register (PDPL, NCA ECC, and more)</li>
            <li>Downloadable PDF, emailed to you automatically</li>
        </ul>
        <a href="{{ route('reports.payment.checkout', $report) }}" class="unlock-btn">Unlock full report</a>
        <p class="price-note">Secure payment via Stream. One-time charge, no subscription.</p>
    </div>

    <footer>
        Reports are directional planning estimates, not binding quotes from any cloud provider.
    </footer>
</body>
</html>
