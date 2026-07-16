<?php

namespace App\Jobs;

use App\Mail\ReportReadyMail;
use App\Models\Assessment;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Assessment $assessment) {}

    public function handle(): void
    {
        $report = Report::where('assessment_id', $this->assessment->id)->latest()->firstOrFail();

        $signedUrl = URL::temporarySignedRoute(
            'reports.show',
            now()->addDays(30),
            ['report' => $report->id]
        );

        Mail::to($this->assessment->creator->email)->send(new ReportReadyMail($report, $signedUrl));
    }
}
