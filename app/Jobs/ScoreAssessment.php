<?php

namespace App\Jobs;

use App\Actions\GenerateReport;
use App\Models\Assessment;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScoreAssessment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Assessment $assessment) {}

    /**
     * Idempotent: a retry (or a re-run of the chain) reuses the existing
     * report rather than creating a duplicate snapshot.
     */
    public function handle(GenerateReport $generateReport): void
    {
        $existing = Report::where('assessment_id', $this->assessment->id)->latest()->first();

        if ($existing === null) {
            $generateReport->execute($this->assessment);
        }
    }
}
