<x-app-layout>
    <x-slot name="header">
        <p class="text-caption text-ink-muted mb-xxs">{{ Auth::user()->name }}, signed in &middot; {{ $organization->name }}</p>
        <h2 class="text-display-md text-ink">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- These aren't mutually exclusive: an org can have a completed report from a
                 prior assessment AND a new one in progress at the same time, so each state
                 renders independently instead of one hiding the other. --}}

            @if ($activeAssessment)
                <div class="spotlight-violet">
                    <p class="text-caption text-ink/70 uppercase tracking-wide mb-xs">Assessment in progress &middot; {{ $organization->name }}</p>
                    <h3 class="text-subhead text-ink mb-xs">Pick up {{ $activeAssessmentClientName }}&rsquo;s assessment where you left off</h3>
                    <p class="text-body text-ink/80 mb-lg max-w-lg">
                        Your answers are saved automatically. Finish the remaining sections to generate your readiness report.
                    </p>
                    <a href="{{ route('survey.start') }}" class="btn-primary">
                        {{ __('Resume assessment') }}
                    </a>
                </div>
            @endif

            @if ($latestCompleted)
                @if ($isGeneratingReport)
                    <div class="card-1 p-lg">
                        <div class="flex items-center gap-3 mb-xs">
                            <span class="h-2 w-2 rounded-full bg-accent-blue animate-pulse"></span>
                            <p class="text-caption text-ink-muted uppercase tracking-wide">Report generating &middot; {{ $organization->name }}</p>
                        </div>
                        <h3 class="text-subhead text-ink mb-xs">{{ $latestCompletedClientName }}&rsquo;s assessment was submitted</h3>
                        <p class="text-body text-ink-muted max-w-lg">
                            We're scoring your answers, writing the narrative, and rendering your report. This usually takes a minute or two — refresh this page shortly, or check your inbox.
                        </p>
                    </div>
                @else
                    @unless ($activeAssessment)
                        <div class="flex items-center justify-between gap-lg px-lg py-md rounded-xl border border-hairline-soft">
                            <p class="text-body-sm text-ink-muted">Want to reassess, or check a different environment?</p>
                            <a href="{{ route('survey.start') }}" class="btn-secondary shrink-0">
                                {{ __('Start a new assessment') }}
                            </a>
                        </div>
                    @endunless

                    <div class="card-1 p-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-lg">
                        <div>
                            <p class="text-caption text-ink-muted uppercase tracking-wide mb-xs">Latest report ready &middot; {{ $organization->name }}</p>
                            <h3 class="text-subhead text-ink mb-xs">{{ $latestCompletedClientName }}&rsquo;s cloud migration report is ready</h3>
                            <p class="text-body text-ink-muted max-w-lg">
                                Completed {{ $latestCompleted->completed_at?->toFormattedDateString() }}. The link below is valid for 30 days and doesn't require login to view or share.
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ $latestReportUrl }}#download" class="btn-secondary" target="_blank" rel="noopener">
                                {{ __('Download PDF') }}
                            </a>
                            <a href="{{ $latestReportUrl }}" class="btn-primary" target="_blank" rel="noopener">
                                {{ __('View report') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endif

            @if (! $activeAssessment && ! $latestCompleted)
                <div class="spotlight-violet">
                    <p class="text-caption text-ink/70 uppercase tracking-wide mb-xs">Get started &middot; {{ $organization->name }}</p>
                    <h3 class="text-subhead text-ink mb-xs">Run {{ $organization->name }}&rsquo;s first cloud readiness assessment</h3>
                    <p class="text-body text-ink/80 mb-lg max-w-lg">
                        Answer a structured questionnaire about your infrastructure, compliance, and budget to get a readiness score, platform recommendation, and cost projection.
                    </p>
                    <a href="{{ route('survey.start') }}" class="btn-primary">
                        {{ __('Start assessment') }}
                    </a>
                </div>
            @endif

            @if ($pastReports->isNotEmpty())
                <div class="card-1 p-lg">
                    <p class="text-caption text-ink-muted uppercase tracking-wide mb-md">Past reports</p>
                    <div class="divide-y divide-hairline-soft">
                        @foreach ($pastReports as $pastReport)
                            <div class="flex items-center justify-between gap-lg py-md first:pt-0 last:pb-0">
                                <div>
                                    <p class="text-body-sm text-ink">{{ $pastReport['clientName'] }}</p>
                                    <p class="text-micro text-ink-muted">Completed {{ $pastReport['completedAt']?->toFormattedDateString() }}</p>
                                </div>
                                <a href="{{ $pastReport['url'] }}" class="text-caption text-accent-blue hover:underline underline-offset-4 shrink-0" target="_blank" rel="noopener">
                                    {{ __('View report') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
