<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090909">
    <title>{{ __('Link expired') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-canvas text-ink min-h-screen flex items-center justify-center px-6">
    <div class="max-w-md text-center">
        <p class="text-caption text-ink-muted uppercase tracking-wide mb-md">403</p>
        <h1 class="text-display-md text-ink mb-md">{{ __('This link is no longer valid') }}</h1>
        <p class="text-body text-ink-muted mb-xl">
            {{ __('Report links expire after 30 days, or this URL may have been copied incorrectly. If you need this report again, sign in and generate a new link from your dashboard.') }}
        </p>
        <a href="{{ url('/') }}" class="btn-primary">{{ __('Go to homepage') }}</a>
    </div>
</body>
</html>
