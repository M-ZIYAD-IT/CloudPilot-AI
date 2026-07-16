<?php

namespace Tests\Unit\Reporting;

use App\Reporting\NarrativeGenerator;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class NarrativeGeneratorTest extends TestCase
{
    public function test_it_throws_when_the_api_key_is_not_configured(): void
    {
        config(['services.anthropic.key' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ANTHROPIC_API_KEY is not configured');

        (new NarrativeGenerator)->generate(['readiness' => ['overall' => 42]]);
    }

    public function test_it_rejects_a_narrative_containing_a_number_not_in_the_source_payload(): void
    {
        $sourceJson = json_encode(['readiness' => ['overall' => 42], 'platform' => ['recommended' => 'aws']]);

        $narrative = [
            'executive_summary' => 'Your readiness score is 42, and we project a 15% cost reduction.',
            'risk_mitigations' => 'No major risks.',
            'app_justifications' => [],
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Narrative contains a number not present in the input findings: "15"');

        $this->invokeValidator($sourceJson, $narrative);
    }

    public function test_it_accepts_a_narrative_whose_numbers_all_appear_in_the_source_payload(): void
    {
        $sourceJson = json_encode(['readiness' => ['overall' => 42], 'tco' => ['current_annual_estimate' => 150000]]);

        $narrative = [
            'executive_summary' => 'Your readiness score is 42, with an estimated current annual spend of 150000.',
            'risk_mitigations' => 'No major risks.',
            'app_justifications' => [
                ['name' => 'App A', 'justification' => 'Scored 42 on readiness.'],
            ],
        ];

        $this->invokeValidator($sourceJson, $narrative);
        $this->addToAssertionCount(1);
    }

    private function invokeValidator(string $sourceJson, array $narrative): void
    {
        $method = new ReflectionMethod(NarrativeGenerator::class, 'assertNoInventedNumbers');
        $method->setAccessible(true);
        $method->invoke(new NarrativeGenerator, $narrative, $sourceJson);
    }
}
