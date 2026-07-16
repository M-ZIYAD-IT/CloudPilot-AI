<?php

namespace App\Scoring;

/**
 * Once a version ships and reports reference it, its rule files must never
 * change in place - bump to v2 instead. That discipline is what makes a
 * report reproducible from its snapshot (assessment answers + engine_version
 * + price_table_id), per the plan's standing rules.
 */
final class ScoringEngine
{
    public const VERSION = 'v1';

    public const PRICE_TABLE_VERSION = 'v1';

    public function __construct(
        private readonly ReadinessScorer $readinessScorer,
        private readonly PlatformRecommender $platformRecommender,
        private readonly ComplianceEvaluator $complianceEvaluator,
        private readonly SixRMapper $sixRMapper,
        private readonly TcoCalculator $tcoCalculator,
        private readonly array $priceTable,
    ) {}

    public static function forVersion(string $version = self::VERSION): self
    {
        return new self(
            new ReadinessScorer(RulesLoader::load($version, 'dimension-weights')),
            new PlatformRecommender(RulesLoader::load($version, 'platform-weights')),
            new ComplianceEvaluator(RulesLoader::load($version, 'compliance-matrix')),
            new SixRMapper,
            new TcoCalculator,
            RulesLoader::load($version, 'price-table'),
        );
    }

    /**
     * @param  array<string, mixed>  $answers  question_key => value, as stored on Answer rows
     * @param  array<int, array<string, mixed>>  $apps  declared apps, as stored on AppEntry rows
     */
    public function score(array $answers, array $apps): array
    {
        return [
            'engine_version' => self::VERSION,
            'readiness' => $this->readinessScorer->score($answers, $apps),
            'platform' => $this->platformRecommender->score($answers),
            'compliance' => $this->complianceEvaluator->evaluate($answers),
            'six_r' => $this->sixRMapper->map($answers, $apps),
            'tco' => $this->tcoCalculator->calculate($answers),
            'price_table' => $this->priceTable,
        ];
    }
}
