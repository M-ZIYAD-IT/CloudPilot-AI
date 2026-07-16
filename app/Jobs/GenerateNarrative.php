<?php

namespace App\Jobs;

use App\Models\Assessment;
use App\Models\Report;
use App\Reporting\NarrativeGenerator;
use App\Scoring\ScoringEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Narrative generation is best-effort: a missing API key or a transient
 * Claude API failure is recorded on the report (and reported to monitoring)
 * rather than blocking RenderReport/EmailReport - the deterministic numbers
 * are the substance of the report, the prose is supplementary. Re-running
 * this job later (e.g. once a real API key is configured) fills in the
 * narrative retroactively.
 */
class GenerateNarrative implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Assessment $assessment) {}

    public function handle(NarrativeGenerator $narrativeGenerator): void
    {
        $report = Report::where('assessment_id', $this->assessment->id)->latest()->firstOrFail();

        try {
            $engine = ScoringEngine::forVersion($report->engineVersion->version);
            $findings = $engine->score(
                $report->answers_snapshot['answers'],
                $report->answers_snapshot['apps']
            );

            $narrative = $narrativeGenerator->generate($findings);

            $report->update(['narrative' => $narrative, 'narrative_error' => null]);
        } catch (Throwable $exception) {
            report($exception);
            $report->update(['narrative_error' => $exception->getMessage()]);
        }
    }
}
