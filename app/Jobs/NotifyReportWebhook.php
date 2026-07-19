<?php

namespace App\Jobs;

use App\Models\Assessment;
use App\Models\Report;
use App\Scoring\ScoringEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class NotifyReportWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Assessment $assessment) {}

    public function handle(): void
    {
        $url = config('services.report_webhook.url');

        if (! $url) {
            return;
        }

        $report = Report::where('assessment_id', $this->assessment->id)->latest()->firstOrFail();

        $engine = ScoringEngine::forVersion($report->engineVersion->version);
        $result = $engine->score(
            $report->answers_snapshot['answers'],
            $report->answers_snapshot['apps']
        );

        $clientName = $report->answers_snapshot['answers']['client_name']
            ?? $this->assessment->organization->name;

        $reportUrl = URL::temporarySignedRoute(
            'reports.show',
            now()->addDays(30),
            ['report' => $report->id]
        );

        $pdfBinary = Pdf::loadHTML($report->html_content)->output();

        Http::timeout(30)->asJson()->post($url, [
            'assessment_id' => $this->assessment->id,
            'report_id' => $report->id,
            'organization' => $this->assessment->organization->name,
            'client_name' => $clientName,
            'email' => $this->assessment->creator->email,
            'generated_at' => $report->generated_at?->toIso8601String(),
            'report_url' => $reportUrl,
            'readiness' => $result['readiness'],
            'platform' => $result['platform'],
            'compliance' => $result['compliance'],
            'six_r' => $result['six_r'],
            'tco' => $result['tco'],
            'narrative' => $report->narrative,
            'report_pdf_base64' => base64_encode($pdfBinary),
            'report_pdf_filename' => "cloudpilot-report-{$report->id}.pdf",
        ])->throw();
    }
}
