<?php

namespace App\Scoring;

final class ComplianceEvaluator
{
    public function __construct(private readonly array $matrix) {}

    public function evaluate(array $answers): array
    {
        $selected = $answers['compliance_regulations'] ?? [];
        $selected = is_array($selected) ? $selected : [$selected];

        $register = [];

        foreach ($this->matrix['regulations'] as $regulation => $details) {
            $register[] = [
                'regulation' => $regulation,
                'applicable' => in_array($regulation, $selected, true),
                'requirement' => $details['requirement'],
                'satisfying_providers' => $details['satisfying_providers'],
                'notes' => $details['notes'],
            ];
        }

        return [
            'data_residency_required' => ($answers['compliance_data_residency'] ?? null) === 'Yes, required',
            'register' => $register,
        ];
    }
}
