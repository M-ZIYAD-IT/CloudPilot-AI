<?php

namespace App\Scoring;

final class ReadinessScorer
{
    private const ANSWER_DIMENSIONS = ['infrastructure', 'data', 'security', 'finance'];

    public function __construct(private readonly array $weights) {}

    public function score(array $answers, array $apps): array
    {
        $dimensions = [];

        foreach (self::ANSWER_DIMENSIONS as $dimension) {
            $dimensions[$dimension] = (int) round($this->scoreDimension($answers, $this->weights[$dimension] ?? []));
        }

        $dimensions['apps'] = (int) round($this->scoreApps($apps));

        $overall = (int) round(array_sum($dimensions) / count($dimensions));

        return ['overall' => $overall, 'dimensions' => $dimensions];
    }

    private function scoreDimension(array $answers, array $questionWeights): float
    {
        if ($questionWeights === []) {
            return 50.0;
        }

        $scores = [];
        foreach ($questionWeights as $questionKey => $choiceWeights) {
            $scores[] = AnswerWeights::scoreFor($answers, $questionKey, $choiceWeights);
        }

        return array_sum($scores) / count($scores);
    }

    /**
     * Derived from the declared apps themselves (not a survey answer): a COTS,
     * vendor-supported, license-flexible portfolio scores higher readiness
     * than one full of unsupported or hardware-locked custom software.
     */
    private function scoreApps(array $apps): float
    {
        if ($apps === []) {
            return 40.0;
        }

        $scores = array_map(function (array $app) {
            $score = 50.0;

            if (($app['is_cots'] ?? false) === true) {
                $score += 10;
            }

            if (($app['vendor_supported'] ?? null) === true) {
                $score += 15;
            } elseif (($app['vendor_supported'] ?? null) === false) {
                $score -= 20;
            }

            if (($app['licensing_tied_to_hardware'] ?? false) === true) {
                $score -= 15;
            }

            return max(0.0, min(100.0, $score));
        }, $apps);

        return array_sum($scores) / count($scores);
    }
}
