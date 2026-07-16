<?php

namespace App\Jobs;

use App\Models\Assessment;
use App\Models\Report;
use App\Reporting\QuickChartUrl;
use App\Scoring\ScoringEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

class RenderReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Assessment $assessment) {}

    public function handle(): void
    {
        $report = Report::where('assessment_id', $this->assessment->id)->latest()->firstOrFail();

        $engine = ScoringEngine::forVersion($report->engineVersion->version);
        $result = $engine->score(
            $report->answers_snapshot['answers'],
            $report->answers_snapshot['apps']
        );

        $tco = $result['tco'];

        $html = View::make('reports.show', [
            'report' => $report,
            'assessment' => $this->assessment,
            'result' => $result,
            'narrative' => $report->narrative,
            'radarChartUrl' => QuickChartUrl::radar(
                array_map('ucfirst', array_keys($result['readiness']['dimensions'])),
                array_values($result['readiness']['dimensions'])
            ),
            'tcoChartUrl' => $tco['current_annual_estimate'] === null ? null : QuickChartUrl::bar(
                ['Current', 'Optimistic', 'Expected', 'Pessimistic'],
                [
                    $tco['current_annual_estimate'],
                    $tco['cloud_annual_projection']['optimistic'],
                    $tco['cloud_annual_projection']['expected'],
                    $tco['cloud_annual_projection']['pessimistic'],
                ],
                'Annual cost (USD)'
            ),
        ])->render();

        $report->update(['html_content' => $html]);
    }
}
