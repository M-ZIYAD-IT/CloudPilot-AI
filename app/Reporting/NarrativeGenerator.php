<?php

namespace App\Reporting;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use RuntimeException;

/**
 * Turns the engine's structured findings into narrative prose via the Claude
 * API. Prompt contract: the model may only write about numbers already
 * present in the findings payload - it must never introduce, calculate, or
 * round a new figure. Enforced twice: instructed in the system prompt, then
 * mechanically verified in assertNoInventedNumbers() before the narrative is
 * accepted, since an instruction alone isn't a guarantee.
 */
final class NarrativeGenerator
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are writing narrative sections for a cloud migration readiness report, based on structured findings produced by a deterministic scoring engine.

        Critical constraint: you must not introduce any number, percentage, or dollar figure that is not already present in the structured findings JSON you are given. Do not calculate, estimate, extrapolate, or round to a new number. Refer to figures in prose without restating exact values if that helps you avoid inventing one.

        Write three things, and return them as JSON matching the required schema:
        1. executive_summary: 2-3 paragraphs summarizing the readiness assessment and top platform recommendation, for a business (non-technical) audience.
        2. app_justifications: for each declared application in the input, rewrite its migration strategy justification in natural, consultant-style prose (2-3 sentences). Do not change the strategy itself or invent new facts about the application - ground every sentence in the given strategy and justification.
        3. risk_mitigations: 1-2 paragraphs identifying the top risks visible in the compliance register and infrastructure signals, with practical mitigation suggestions.
        PROMPT;

    private const SCHEMA = [
        'type' => 'object',
        'properties' => [
            'executive_summary' => ['type' => 'string'],
            'risk_mitigations' => ['type' => 'string'],
            'app_justifications' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'justification' => ['type' => 'string'],
                    ],
                    'required' => ['name', 'justification'],
                    'additionalProperties' => false,
                ],
            ],
        ],
        'required' => ['executive_summary', 'risk_mitigations', 'app_justifications'],
        'additionalProperties' => false,
    ];

    /**
     * @param  array<string, mixed>  $findings  the ScoringEngine result to narrate
     * @return array{executive_summary: string, risk_mitigations: string, app_justifications: array<int, array{name: string, justification: string}>}
     */
    public function generate(array $findings): array
    {
        $apiKey = config('services.anthropic.key');

        if (blank($apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured; narrative generation is unavailable.');
        }

        $payloadJson = json_encode($findings, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        try {
            $client = new Client(apiKey: $apiKey);

            $message = $client->messages->create(
                model: config('services.anthropic.model', 'claude-sonnet-5'),
                maxTokens: 2048,
                system: self::SYSTEM_PROMPT,
                messages: [
                    ['role' => 'user', 'content' => "Structured findings (JSON):\n\n{$payloadJson}"],
                ],
                outputConfig: ['format' => ['type' => 'json_schema', 'schema' => self::SCHEMA]],
            );
        } catch (AnthropicException $exception) {
            throw new RuntimeException("Claude API call failed: {$exception->getMessage()}", previous: $exception);
        }

        $text = null;

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text = $block->text;
                break;
            }
        }

        if ($text === null) {
            throw new RuntimeException('Claude returned no text content (stop_reason: '.$message->stopReason.').');
        }

        $narrative = json_decode($text, true, flags: JSON_THROW_ON_ERROR);

        $this->assertNoInventedNumbers($narrative, $payloadJson);

        return $narrative;
    }

    /**
     * @param  array<string, mixed>  $narrative
     */
    private function assertNoInventedNumbers(array $narrative, string $sourceJson): void
    {
        preg_match_all('/\d+(?:\.\d+)?/', $sourceJson, $sourceMatches);
        $sourceNumbers = array_flip($sourceMatches[0]);

        $narrativeText = implode(' ', [
            $narrative['executive_summary'] ?? '',
            $narrative['risk_mitigations'] ?? '',
            ...array_column($narrative['app_justifications'] ?? [], 'justification'),
        ]);

        preg_match_all('/\d+(?:\.\d+)?/', $narrativeText, $narrativeMatches);

        foreach ($narrativeMatches[0] as $number) {
            if (! isset($sourceNumbers[$number])) {
                throw new RuntimeException("Narrative contains a number not present in the input findings: \"{$number}\". Rejecting to avoid an invented figure reaching the report.");
            }
        }
    }
}
