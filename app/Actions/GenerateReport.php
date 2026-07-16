<?php

namespace App\Actions;

use App\Models\Assessment;
use App\Models\EngineVersion;
use App\Models\PriceTable;
use App\Models\Report;
use App\Scoring\ScoringEngine;
use InvalidArgumentException;

final class GenerateReport
{
    /**
     * The snapshot write path: captures the exact answers/apps, engine
     * version, and price table a report was generated from, so it stays
     * reproducible even if the assessment's live answers or the scoring
     * rules change later.
     */
    public function execute(Assessment $assessment): Report
    {
        if ($assessment->status !== 'completed') {
            throw new InvalidArgumentException('Cannot generate a report for an assessment that is not completed.');
        }

        $answers = $assessment->answers()->pluck('value', 'question_key')->all();

        $apps = $assessment->apps()->get()->map(fn ($app) => [
            'name' => $app->name,
            'category' => $app->category,
            'is_cots' => $app->is_cots,
            'vendor_supported' => $app->vendor_supported,
            'licensing_tied_to_hardware' => $app->licensing_tied_to_hardware,
        ])->all();

        $priceTable = PriceTable::where('version', ScoringEngine::PRICE_TABLE_VERSION)->firstOrFail();
        $engineVersion = EngineVersion::where('version', ScoringEngine::VERSION)->firstOrFail();

        return Report::create([
            'assessment_id' => $assessment->id,
            'price_table_id' => $priceTable->id,
            'engine_version_id' => $engineVersion->id,
            'answers_snapshot' => ['answers' => $answers, 'apps' => $apps],
            'generated_at' => now(),
        ]);
    }
}
