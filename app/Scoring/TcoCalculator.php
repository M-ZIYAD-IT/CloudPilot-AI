<?php

namespace App\Scoring;

/**
 * Simplified v1 TCO model. The MVP survey only collects bucketed annual
 * spend/migration-budget ranges, not per-workload vCPU/RAM inventory, so a
 * genuine bottom-up instance-sizing TCO isn't possible yet (that needs
 * richer discovery data - see the idea doc's "Instance right-sizing
 * heuristics" section). Instead, the utilization discount factor (on-prem
 * servers average 15-25% utilization) directly drives how aggressive each
 * band's savings assumption is, so the three bands move together if that
 * constant changes.
 */
final class TcoCalculator
{
    private const ANNUAL_SPEND_MIDPOINTS = [
        '< $50k' => 40000,
        '$50k - $250k' => 150000,
        '$250k - $1M' => 625000,
        '> $1M' => 1500000,
    ];

    private const MIGRATION_BUDGET_MIDPOINTS = [
        '< $25k' => 15000,
        '$25k - $100k' => 62500,
        '$100k - $500k' => 300000,
        '> $500k' => 750000,
    ];

    private const UTILIZATION_DISCOUNT_FACTOR = 0.20;

    public function calculate(array $answers): array
    {
        $currentAnnual = self::ANNUAL_SPEND_MIDPOINTS[$answers['budget_annual_spend'] ?? null] ?? null;

        if ($currentAnnual === null) {
            return [
                'current_annual_estimate' => null,
                'cloud_annual_projection' => null,
                'migration_one_time_estimate' => null,
                'utilization_discount_factor' => self::UTILIZATION_DISCOUNT_FACTOR,
                'note' => "Annual IT spend was not answered (or \"I don't know\"), so no TCO projection can be computed.",
            ];
        }

        $migrationBudget = self::MIGRATION_BUDGET_MIDPOINTS[$answers['budget_migration_budget'] ?? null]
            ?? (int) round($currentAnnual * 0.2);

        $factor = self::UTILIZATION_DISCOUNT_FACTOR;

        return [
            'current_annual_estimate' => $currentAnnual,
            'cloud_annual_projection' => [
                'optimistic' => (int) round($currentAnnual * (1 - ($factor + 0.30))),
                'expected' => (int) round($currentAnnual * (1 - ($factor + 0.15))),
                'pessimistic' => (int) round($currentAnnual * (1 - ($factor - 0.05))),
            ],
            'migration_one_time_estimate' => $migrationBudget,
            'utilization_discount_factor' => $factor,
        ];
    }
}
