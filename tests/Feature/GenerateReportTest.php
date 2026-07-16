<?php

namespace Tests\Feature;

use App\Actions\GenerateReport;
use App\Models\EngineVersion;
use App\Models\Organization;
use App\Models\PriceTable;
use App\Models\User;
use App\Scoring\RulesLoader;
use App\Scoring\ScoringEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GenerateReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedEngineAndPriceTable(): void
    {
        $priceTableData = RulesLoader::load(ScoringEngine::PRICE_TABLE_VERSION, 'price-table');

        PriceTable::create([
            'version' => ScoringEngine::PRICE_TABLE_VERSION,
            'as_of_date' => $priceTableData['_meta']['as_of_date'],
            'data' => $priceTableData,
        ]);

        EngineVersion::create([
            'version' => ScoringEngine::VERSION,
            'description' => 'test',
        ]);
    }

    public function test_it_refuses_to_generate_a_report_for_an_incomplete_assessment(): void
    {
        $this->seedEngineAndPriceTable();

        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme-'.uniqid()]);
        $user = User::factory()->create();
        $assessment = $organization->assessments()->create(['created_by' => $user->id, 'status' => 'in_progress']);

        $this->expectException(InvalidArgumentException::class);

        app(GenerateReport::class)->execute($assessment);
    }

    public function test_it_snapshots_answers_apps_price_table_and_engine_version(): void
    {
        $this->seedEngineAndPriceTable();

        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme-'.uniqid()]);
        $user = User::factory()->create();
        $assessment = $organization->assessments()->create([
            'created_by' => $user->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $assessment->answers()->create(['question_key' => 'business_timeline', 'value' => '3-12 months']);
        $assessment->apps()->create([
            'name' => 'Test App',
            'category' => 'Custom application',
            'is_cots' => false,
            'licensing_tied_to_hardware' => false,
        ]);

        $report = app(GenerateReport::class)->execute($assessment);

        $this->assertSame($assessment->id, $report->assessment_id);
        $this->assertSame(ScoringEngine::PRICE_TABLE_VERSION, $report->priceTable->version);
        $this->assertSame(ScoringEngine::VERSION, $report->engineVersion->version);
        $this->assertSame('3-12 months', $report->answers_snapshot['answers']['business_timeline']);
        $this->assertSame('Test App', $report->answers_snapshot['apps'][0]['name']);
        $this->assertNotNull($report->generated_at);

        // The snapshot must be independently scoreable without touching the live assessment.
        $result = ScoringEngine::forVersion()->score(
            $report->answers_snapshot['answers'],
            $report->answers_snapshot['apps']
        );
        $this->assertSame('Rehost', $result['six_r'][0]['strategy']);
    }
}
