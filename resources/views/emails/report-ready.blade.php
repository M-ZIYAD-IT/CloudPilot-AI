<x-mail::message>
# Your report is ready

Your cloud migration readiness assessment has been generated.

<x-mail::button :url="$signedUrl">
View Your Report
</x-mail::button>

This link is valid for 30 days and does not require a login to view.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
