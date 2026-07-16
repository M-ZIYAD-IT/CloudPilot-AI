<?php

namespace App\Scoring;

final class PlatformRecommender
{
    public function __construct(private readonly array $rules) {}

    public function score(array $answers): array
    {
        $scores = $this->rules['base'];

        foreach ($this->rules['adjustments'] as $questionKey => $choiceAdjustments) {
            foreach (AnswerWeights::adjustmentsFor($answers, $questionKey, $choiceAdjustments) as $platform => $delta) {
                $scores[$platform] = ($scores[$platform] ?? 0) + $delta;
            }
        }

        foreach ($scores as $platform => $score) {
            $scores[$platform] = max(0, min(100, (int) round($score)));
        }

        arsort($scores);

        return [
            'scores' => $scores,
            'recommended' => array_key_first($scores),
        ];
    }
}
