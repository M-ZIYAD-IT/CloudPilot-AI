<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090909">
    <title>{{ config('app.name', 'CloudPilot AI') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-canvas text-ink">
    <div class="min-h-screen flex flex-col relative overflow-hidden">
        {{-- Fixed dot-grid texture behind every section, so the sides read as
             designed backdrop rather than dead space. --}}
        <div class="bg-dot-grid"></div>

        {{-- Ambient atmosphere: soft blurred color behind the hero, never in the foreground. --}}
        <div class="bg-glow -top-40 right-[-10%] h-[560px] w-[560px] bg-gradient-violet/25"></div>
        <div class="bg-glow top-[420px] left-[-15%] h-[480px] w-[480px] bg-accent-blue/10"></div>
        <div class="bg-glow top-[900px] right-[-12%] h-[520px] w-[520px] bg-gradient-magenta/10"></div>
        <div class="bg-glow bottom-[-140px] left-[-10%] h-[520px] w-[520px] bg-gradient-orange/10"></div>

        <header class="relative max-w-6xl w-full mx-auto px-6 sm:px-8 py-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-application-logo class="h-6 w-auto fill-current text-ink" />
                <span class="text-body-sm text-ink">{{ config('app.name') }}</span>
            </div>

            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Register</a>
                @endauth
            </nav>
        </header>

        <main class="relative flex-1">
            {{-- Hero --}}
            <section class="max-w-6xl w-full mx-auto px-6 sm:px-8 pt-10 pb-20 sm:pt-16 sm:pb-28">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                    <div>
                        <p class="anim-fade-up text-caption text-accent-blue mb-md uppercase tracking-wide" style="--anim-delay:0s">Trusted by IT &amp; cloud teams</p>
                        <h1 class="anim-fade-up text-display-md sm:text-display-lg text-ink" style="--anim-delay:0.08s">
                            Plan your cloud move with CloudPilot AI.
                        </h1>
                        <p class="anim-fade-up mt-lg text-body-lg text-ink-muted max-w-xl" style="--anim-delay:0.16s">
                            A short assessment of your infrastructure, compliance, and budget, turned into a readiness score, a platform pick, and a cost projection.
                        </p>
                        <div class="anim-fade-up mt-xl flex flex-wrap items-center gap-3" style="--anim-delay:0.24s">
                            @auth
                                <a href="{{ route('survey.start') }}" class="btn-primary">Start assessment</a>
                            @else
                                <a href="{{ route('register') }}" class="btn-primary">Start your assessment</a>
                                <a href="{{ route('login') }}" class="btn-secondary">Log in</a>
                            @endauth
                        </div>
                        <p class="anim-fade-up mt-lg text-micro text-ink-muted" style="--anim-delay:0.3s">No credit card. Report ready in minutes, not weeks.</p>
                    </div>

                    {{-- Report preview deck: report-metric cards stepping through fixed 3D
                         positions, one in focus and the rest fanned above/below it, blurred.
                         Every step wraps around, so it loops in one direction. Click a card,
                         drag, use the arrows, or click a dot. --}}
                    <div class="anim-fade-up" style="--anim-delay:0.2s">
                        <div id="card-deck" class="relative h-[320px] sm:h-[340px]" style="perspective:1400px">
                            <div class="deck-card">
                                <div class="deck-card__inner spotlight-card spotlight-card--violet" data-tilt data-tilt-max="6">
                                    <div class="tilt-card__shine"></div>
                                    <p class="relative text-caption text-ink/70 uppercase tracking-wide">Sample output</p>
                                    <div class="relative flex items-center gap-4">
                                        <svg viewBox="0 0 120 120" class="h-16 w-16 shrink-0 -rotate-90">
                                            <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="10" />
                                            <circle cx="60" cy="60" r="54" fill="none" stroke="#ffffff" stroke-width="10"
                                                stroke-linecap="round" stroke-dasharray="339.3" stroke-dashoffset="74.6" class="anim-dial-ring" />
                                        </svg>
                                        <div>
                                            <div class="text-display-md text-ink leading-none"><span data-count-to="78">78</span></div>
                                            <div class="text-caption text-ink/70 mt-xs">Readiness score / 100</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="deck-card">
                                <div class="deck-card__inner spotlight-card spotlight-card--violet" data-tilt data-tilt-max="6">
                                    <div class="tilt-card__shine"></div>
                                    <p class="relative text-caption text-ink/70 uppercase tracking-wide">Platform</p>
                                    <div class="relative">
                                        <div class="text-display-md text-ink">AWS</div>
                                        <p class="text-micro text-ink/70 mt-xxs">Recommended for you</p>
                                    </div>
                                </div>
                            </div>

                            <div class="deck-card">
                                <div class="deck-card__inner spotlight-card spotlight-card--orange" data-tilt data-tilt-max="6">
                                    <div class="tilt-card__shine"></div>
                                    <p class="relative text-caption text-ink/70 uppercase tracking-wide">Cloud TCO</p>
                                    <div class="relative">
                                        <div class="text-display-md text-ink">$126k/yr</div>
                                        <p class="text-micro text-ink/70 mt-xxs">32% below on-prem</p>
                                    </div>
                                </div>
                            </div>

                            <div class="deck-card">
                                <div class="deck-card__inner spotlight-card spotlight-card--coral" data-tilt data-tilt-max="6">
                                    <div class="tilt-card__shine"></div>
                                    <p class="relative text-caption text-ink/70 uppercase tracking-wide">6R map</p>
                                    <div class="relative">
                                        <div class="text-display-md text-ink">5 apps</div>
                                        <p class="text-micro text-ink/70 mt-xxs">Disposition strategy set</p>
                                    </div>
                                </div>
                            </div>

                            <div class="deck-card">
                                <div class="deck-card__inner spotlight-card spotlight-card--magenta" data-tilt data-tilt-max="6">
                                    <div class="tilt-card__shine"></div>
                                    <p class="relative text-caption text-ink/70 uppercase tracking-wide">Compliance</p>
                                    <div class="relative">
                                        <div class="text-display-md text-ink">2 flags</div>
                                        <p class="text-micro text-ink/70 mt-xxs">PDPL &amp; NCA ECC to review</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-lg flex items-center justify-center gap-4">
                            <button type="button" class="deck-nav-btn" data-deck-prev aria-label="Previous card">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <div class="flex items-center gap-2" data-deck-dots></div>
                            <button type="button" class="deck-nav-btn" data-deck-next aria-label="Next card">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- What you get --}}
            <section class="max-w-6xl w-full mx-auto px-6 sm:px-8 pb-20 sm:pb-28">
                <h2 class="text-headline text-ink mb-xl">What's in your report</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l7 4.5L9 15" />
                            <circle cx="12" cy="12" r="9" stroke-linecap="round" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">Readiness score</h3>
                        <p class="text-body-sm text-ink-muted">A single 0&ndash;100 score across infrastructure, data, security, finance, and applications.</p>
                    </div>
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0.06s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">Platform recommendation</h3>
                        <p class="text-body-sm text-ink-muted">A ranked comparison across AWS, Azure, GCP, STC Cloud, and Oracle Cloud for your specific constraints.</p>
                    </div>
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0.12s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l3-9 4 18 3-9h4" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">6R migration map</h3>
                        <p class="text-body-sm text-ink-muted">A disposition strategy &mdash; rehost, replatform, refactor, and more &mdash; for every application you declare.</p>
                    </div>
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0.18s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M7 8h7a2.5 2.5 0 010 5H9a2.5 2.5 0 000 5h8" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">Cost projection</h3>
                        <p class="text-body-sm text-ink-muted">Current on-prem spend versus optimistic, expected, and pessimistic cloud TCO scenarios.</p>
                    </div>
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0.24s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">Compliance &amp; risk register</h3>
                        <p class="text-body-sm text-ink-muted">Which regulations apply &mdash; PDPL, NCA ECC, and more &mdash; and which providers satisfy them.</p>
                    </div>
                    <div class="anim-reveal anim-card card-1 p-lg" style="--anim-delay:0.3s">
                        <svg class="h-6 w-6 text-accent-blue mb-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z" />
                        </svg>
                        <h3 class="text-body-sm text-ink mb-xs">Phased roadmap</h3>
                        <p class="text-body-sm text-ink-muted">A four-phase plan from landing zone setup through optimization, so migration has a sequence, not just a score.</p>
                    </div>
                </div>
            </section>

            {{-- Closing CTA --}}
            <section class="max-w-6xl w-full mx-auto px-6 sm:px-8 pb-20 sm:pb-28">
                <div class="anim-reveal card-2 p-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-lg">
                    <div>
                        <h2 class="text-subhead text-ink mb-xs">Ready to see your number?</h2>
                        <p class="text-body text-ink-muted">Set up your organization and start the assessment &mdash; it takes about ten minutes.</p>
                    </div>
                    @auth
                        <a href="{{ route('survey.start') }}" class="btn-primary shrink-0">Start assessment</a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary shrink-0">Start your assessment</a>
                    @endauth
                </div>
            </section>
        </main>

        <footer class="max-w-6xl w-full mx-auto px-6 sm:px-8 py-8 border-t border-hairline-soft">
            <p class="text-micro text-ink-muted">
                Reports are directional planning estimates, not binding quotes from any cloud provider.
            </p>
        </footer>
    </div>

    @vite(['resources/js/welcome.js'])
</body>
</html>
