<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Scoring\ScoringEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function show(Report $report): View|Response
    {
        abort_if($report->html_content === null, 404, 'This report has not finished generating yet.');

        if (! $report->isUnlocked()) {
            $engine = ScoringEngine::forVersion($report->engineVersion->version);
            $result = $engine->score(
                $report->answers_snapshot['answers'],
                $report->answers_snapshot['apps']
            );

            return view('reports.locked', [
                'report' => $report,
                'readinessOverall' => $result['readiness']['overall'],
                'platformRecommended' => $result['platform']['recommended'],
            ]);
        }

        return response($report->html_content)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
