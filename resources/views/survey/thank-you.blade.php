<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assessment Submitted') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>{{ __('Thank you — your assessment has been submitted.') }}</p>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Your cloud migration report is being generated and will be available soon.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
