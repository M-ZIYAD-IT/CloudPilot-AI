<x-app-layout>
    <x-slot name="header">
        <h2 class="text-display-md text-ink">
            {{ __('Assessment Submitted') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card-1 p-lg flex items-start gap-lg">
                <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success/15 text-success">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-subhead text-ink mb-xs">{{ __('Thank you — your assessment has been submitted.') }}</h3>
                    <p class="text-body text-ink-muted max-w-lg">
                        {{ __('We\'re scoring your answers, writing the narrative, and rendering your report now. This usually takes a minute or two.') }}
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn-secondary mt-lg">
                        {{ __('Back to dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
