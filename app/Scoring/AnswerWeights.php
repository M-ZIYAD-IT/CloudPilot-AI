<?php

namespace App\Scoring;

/**
 * Shared answer-lookup logic used by both the readiness and platform scorers:
 * a survey answer is either a scalar (radiogroup/dropdown) or an array
 * (checkbox), and SurveyJS always serializes an "I don't know" selection as
 * the literal value "none" regardless of the question's noneText label.
 */
final class AnswerWeights
{
    public static function scoreFor(array $answers, string $questionKey, array $choiceWeights, float $default = 50.0): float
    {
        if (! array_key_exists($questionKey, $answers)) {
            return $default;
        }

        $value = $answers[$questionKey];
        $choices = is_array($value) ? $value : [$value];

        if ($choices === []) {
            return $default;
        }

        $scores = array_map(fn ($choice) => $choiceWeights[$choice] ?? $default, $choices);

        return array_sum($scores) / count($scores);
    }

    public static function adjustmentsFor(array $answers, string $questionKey, array $choiceAdjustments): array
    {
        if (! array_key_exists($questionKey, $answers)) {
            return [];
        }

        $value = $answers[$questionKey];
        $choices = is_array($value) ? $value : [$value];

        $combined = [];
        foreach ($choices as $choice) {
            foreach ($choiceAdjustments[$choice] ?? [] as $platform => $delta) {
                $combined[$platform] = ($combined[$platform] ?? 0) + $delta;
            }
        }

        return $combined;
    }
}
