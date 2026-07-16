<?php

namespace Tests\Unit\Scoring;

use App\Scoring\ScoringEngine;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Golden-file tests: lock in today's exact, deterministic engine output for a
 * handful of synthetic company profiles. These numbers aren't an external
 * ground truth (v1's weights are curated judgment calls, not verified fact) -
 * the point is that any future change to the rules JSON or scorer logic shows
 * up as a visible, reviewed diff here rather than silently drifting.
 */
class ScoringEngineGoldenTest extends TestCase
{
    public static function profiles(): array
    {
        return [
            'ksa_clinic' => [
                'answers' => [
                    'business_driver' => ['Security incidents'],
                    'business_timeline' => 'Urgent (< 3 months)',
                    'business_company_size' => '51-200',
                    'business_industry' => 'Healthcare',
                    'business_countries' => ['Saudi Arabia'],
                    'infra_location' => 'Own server room',
                    'infra_server_count' => '11-50',
                    'infra_virtualization' => 'None',
                    'infra_os' => ['Windows Server 2012 or older'],
                    'infra_storage_footprint' => '10-50 TB',
                    'infra_hardware_age' => 'No refresh planned',
                    'compliance_data_categories' => ['PII', 'Health records'],
                    'compliance_regulations' => ['PDPL (Saudi)', 'NCA ECC (Saudi)'],
                    'compliance_data_residency' => 'Yes, required',
                    'compliance_last_audit' => 'More than 2 years ago / never',
                    'compliance_retention' => 'No',
                    'budget_annual_spend' => '$50k - $250k',
                    'budget_migration_budget' => 'none',
                    'budget_capex_opex' => 'CapEx preferred',
                    'budget_commitment_appetite' => 'none',
                ],
                'apps' => [
                    ['name' => 'Electronic Health Records', 'category' => 'Custom application', 'is_cots' => false, 'vendor_supported' => null, 'licensing_tied_to_hardware' => true],
                    ['name' => 'Lab Information System', 'category' => 'Other', 'is_cots' => true, 'vendor_supported' => true, 'licensing_tied_to_hardware' => true],
                ],
                'expected' => [
                    'readiness' => ['overall' => 42, 'dimensions' => ['infrastructure' => 41, 'data' => 41, 'security' => 33, 'finance' => 49, 'apps' => 48]],
                    'platform_recommended' => 'stc_cloud',
                    'platform_scores' => ['stc_cloud' => 95, 'oracle_jeddah' => 85, 'azure' => 80, 'aws' => 65, 'gcp' => 45],
                    'applicable_regulations' => ['PDPL (Saudi)', 'NCA ECC (Saudi)'],
                    'data_residency_required' => true,
                    'six_r_strategies' => ['Electronic Health Records' => 'Refactor', 'Lab Information System' => 'Repurchase'],
                    'tco' => ['current_annual_estimate' => 150000, 'optimistic' => 75000, 'expected' => 97500, 'pessimistic' => 127500, 'migration_one_time_estimate' => 30000],
                ],
            ],
            'retail_ecommerce' => [
                'answers' => [
                    'business_driver' => ['Scalability', 'Expansion to new markets'],
                    'business_timeline' => '3-12 months',
                    'business_company_size' => '201-1000',
                    'business_industry' => 'Retail / e-commerce',
                    'business_countries' => ['Saudi Arabia', 'Other GCC'],
                    'infra_location' => 'Hybrid (some cloud already)',
                    'infra_server_count' => '0-10',
                    'infra_virtualization' => 'None',
                    'infra_os' => ['Linux'],
                    'infra_storage_footprint' => '< 10 TB',
                    'infra_hardware_age' => 'Refresh due in 1-3 years',
                    'compliance_data_categories' => ['PII', 'Payment / PCI'],
                    'compliance_regulations' => ['PCI-DSS'],
                    'compliance_data_residency' => 'No',
                    'compliance_last_audit' => 'Within the last year',
                    'compliance_retention' => 'Yes, clearly defined',
                    'budget_annual_spend' => '> $1M',
                    'budget_migration_budget' => '$100k - $500k',
                    'budget_capex_opex' => 'OpEx preferred',
                    'budget_commitment_appetite' => 'Open to 1-3 year commitment',
                ],
                'apps' => [
                    ['name' => 'E-commerce Platform', 'category' => 'E-commerce', 'is_cots' => true, 'vendor_supported' => true, 'licensing_tied_to_hardware' => false],
                    ['name' => 'CRM', 'category' => 'CRM', 'is_cots' => true, 'vendor_supported' => true, 'licensing_tied_to_hardware' => false],
                ],
                'expected' => [
                    'readiness' => ['overall' => 70, 'dimensions' => ['infrastructure' => 64, 'data' => 61, 'security' => 78, 'finance' => 71, 'apps' => 75]],
                    'platform_recommended' => 'aws',
                    'platform_scores' => ['aws' => 100, 'azure' => 95, 'gcp' => 80, 'stc_cloud' => 75, 'oracle_jeddah' => 70],
                    'applicable_regulations' => ['PCI-DSS'],
                    'data_residency_required' => false,
                    'six_r_strategies' => ['E-commerce Platform' => 'Replatform', 'CRM' => 'Repurchase'],
                    'tco' => ['current_annual_estimate' => 1500000, 'optimistic' => 750000, 'expected' => 975000, 'pessimistic' => 1275000, 'migration_one_time_estimate' => 300000],
                ],
            ],
            'hardware_eol_sme' => [
                'answers' => [
                    'business_driver' => ['End-of-life hardware'],
                    'business_timeline' => 'Urgent (< 3 months)',
                    'business_company_size' => '1-50',
                    'business_industry' => 'Other',
                    'business_countries' => ['Saudi Arabia'],
                    'infra_location' => 'Own server room',
                    'infra_server_count' => '0-10',
                    'infra_virtualization' => 'Hyper-V',
                    'infra_os' => ['Windows Server 2012 or older'],
                    'infra_storage_footprint' => '< 10 TB',
                    'infra_hardware_age' => 'Refresh due within 6 months',
                    'compliance_data_categories' => ['none'],
                    'compliance_regulations' => ['none'],
                    'compliance_data_residency' => 'No',
                    'compliance_last_audit' => 'none',
                    'compliance_retention' => 'none',
                    'budget_annual_spend' => '< $50k',
                    'budget_migration_budget' => '< $25k',
                    'budget_capex_opex' => 'OpEx preferred',
                    'budget_commitment_appetite' => 'Prefer pay-as-you-go',
                ],
                'apps' => [
                    ['name' => 'Line-of-Business App', 'category' => 'Custom application', 'is_cots' => false, 'vendor_supported' => null, 'licensing_tied_to_hardware' => false],
                ],
                'expected' => [
                    'readiness' => ['overall' => 55, 'dimensions' => ['infrastructure' => 57, 'data' => 61, 'security' => 38, 'finance' => 70, 'apps' => 50]],
                    'platform_recommended' => 'stc_cloud',
                    'platform_scores' => ['stc_cloud' => 80, 'aws' => 75, 'azure' => 75, 'gcp' => 70, 'oracle_jeddah' => 65],
                    'applicable_regulations' => [],
                    'data_residency_required' => false,
                    'six_r_strategies' => ['Line-of-Business App' => 'Rehost'],
                    'tco' => ['current_annual_estimate' => 40000, 'optimistic' => 20000, 'expected' => 26000, 'pessimistic' => 34000, 'migration_one_time_estimate' => 15000],
                ],
            ],
            'mostly_unknown' => [
                'answers' => [
                    'business_driver' => ['none'],
                    'business_timeline' => 'none',
                    'infra_location' => 'none',
                    'compliance_data_residency' => 'none',
                ],
                'apps' => [],
                'expected' => [
                    'readiness' => ['overall' => 47, 'dimensions' => ['infrastructure' => 48, 'data' => 49, 'security' => 50, 'finance' => 50, 'apps' => 40]],
                    'platform_recommended' => 'aws',
                    'platform_scores' => ['aws' => 60, 'azure' => 60, 'gcp' => 55, 'stc_cloud' => 50, 'oracle_jeddah' => 45],
                    'applicable_regulations' => [],
                    'data_residency_required' => false,
                    'six_r_strategies' => [],
                    'tco' => null,
                ],
            ],
        ];
    }

    #[DataProvider('profiles')]
    public function test_synthetic_profile_produces_the_expected_scoring_result(array $answers, array $apps, array $expected): void
    {
        $result = ScoringEngine::forVersion()->score($answers, $apps);

        $this->assertSame($expected['readiness'], $result['readiness']);
        $this->assertSame($expected['platform_recommended'], $result['platform']['recommended']);
        $this->assertSame($expected['platform_scores'], $result['platform']['scores']);
        $this->assertSame($expected['data_residency_required'], $result['compliance']['data_residency_required']);

        $applicable = collect($result['compliance']['register'])
            ->where('applicable', true)
            ->pluck('regulation')
            ->all();
        $this->assertSame($expected['applicable_regulations'], $applicable);

        $strategies = collect($result['six_r'])->pluck('strategy', 'name')->all();
        $this->assertSame($expected['six_r_strategies'], $strategies);

        if ($expected['tco'] === null) {
            $this->assertNull($result['tco']['current_annual_estimate']);
        } else {
            $this->assertSame($expected['tco']['current_annual_estimate'], $result['tco']['current_annual_estimate']);
            $this->assertSame($expected['tco']['optimistic'], $result['tco']['cloud_annual_projection']['optimistic']);
            $this->assertSame($expected['tco']['expected'], $result['tco']['cloud_annual_projection']['expected']);
            $this->assertSame($expected['tco']['pessimistic'], $result['tco']['cloud_annual_projection']['pessimistic']);
            $this->assertSame($expected['tco']['migration_one_time_estimate'], $result['tco']['migration_one_time_estimate']);
        }
    }

    public function test_the_same_inputs_always_produce_the_same_outputs(): void
    {
        $profile = self::profiles()['ksa_clinic'];

        $first = ScoringEngine::forVersion()->score($profile['answers'], $profile['apps']);
        $second = ScoringEngine::forVersion()->score($profile['answers'], $profile['apps']);

        $this->assertSame($first, $second);
    }
}
