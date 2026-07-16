<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cloud Migration Readiness Assessment') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div id="surveyContainer"></div>
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
