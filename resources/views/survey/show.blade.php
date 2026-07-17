<x-app-layout>
    <x-slot name="header">
        <p class="text-caption text-ink-muted mb-xxs">Assessment</p>
        <h2 class="text-display-md text-ink">
            {{ __('Cloud Migration Readiness Assessment') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card-1 p-4 sm:p-6" id="sjs-theme-scope">
                <div id="surveyContainer">
                    {{-- Shown until SurveyJS actually renders into this container — removed by
                         survey.js right before survey.render(). If rendering ever fails, this
                         stays visible instead of leaving a blank card with no explanation. --}}
                    <div id="surveyLoading" class="flex flex-col items-center justify-center gap-md py-20">
                        <svg class="h-6 w-6 animate-spin text-ink-muted" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"></circle>
                            <path class="opacity-90" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                        <p class="text-body-sm text-ink-muted">Loading your assessment&hellip;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.SURVEY_CONFIG = {
            json: {!! Illuminate\Support\Js::from($surveyJson) !!},
            data: {!! Illuminate\Support\Js::from($surveyData) !!},
            saveUrl: {!! Illuminate\Support\Js::from(route('survey.answers', $assessment)) !!},
            completeUrl: {!! Illuminate\Support\Js::from(route('survey.complete', $assessment)) !!}
        };
    </script>

    @vite(['resources/js/survey.js'])
</x-app-layout>
