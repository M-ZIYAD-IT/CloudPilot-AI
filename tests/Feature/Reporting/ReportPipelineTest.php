<?php

namespace Tests\Feature\Reporting;

use App\Actions\GenerateReport;
use App\Jobs\EmailReport;
use App\Jobs\GenerateNarrative;
use App\Jobs\RenderReport;
use App\Jobs\ScoreAssessment;
use App\Mail\ReportReadyMail;
use App\Models\Assessment;
use App\Models\EngineVersion;
use App\Models\Organization;
use App\Models\PriceTable;
use App\Models\Report;
use App\Models\User;
use App\Reporting\NarrativeGenerator;
use App\Scoring\RulesLoader;
use App\Scoring\ScoringEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReportPipelineTest extends TestCase
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

    private function completedAssessmentWithApp(): Assessment
    {
        $this->seedEngineAndPriceTable();

        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme-'.uniqid()]);
        $user = User::factory()->create();
        $organization->users()->attach($user->id, ['is_owner' => true]);

        $assessment = $organization->assessments()->create([
            'created_by' => $user->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $assessment->answers()->create(['question_key' => 'budget_annual_spend', 'value' => '$50k - $250k']);
        $assessment->apps()->create([
            'name' => 'Test App',
            'category' => 'Custom application',
            'is_cots' => false,
            'licensing_tied_to_hardware' => false,
        ]);

        return $assessment;
    }

    public function test_score_assessment_is_idempotent(): void
    {
        $assessment = $this->completedAssessmentWithApp();

        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));

        $this->assertSame(1, Report::where('assessment_id', $assessment->id)->count());
    }

    public function test_generate_narrative_records_an_error_when_no_api_key_is_configured(): void
    {
        config(['services.anthropic.key' => null]);
        $assessment = $this->completedAssessmentWithApp();
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));

        (new GenerateNarrative($assessment))->handle(app(NarrativeGenerator::class));

        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();
        $this->assertNull($report->narrative);
        $this->assertStringContainsString('ANTHROPIC_API_KEY is not configured', $report->narrative_error);
    }

    public function test_render_report_produces_html_containing_engine_numbers_verbatim(): void
    {
        $assessment = $this->completedAssessmentWithApp();
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));
        (new RenderReport($assessment))->handle();

        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();
        $this->assertNotNull($report->html_content);

        $result = ScoringEngine::forVersion()->score(
            $report->answers_snapshot['answers'],
            $report->answers_snapshot['apps']
        );

        $this->assertStringContainsString((string) $result['readiness']['overall'], $report->html_content);
        $this->assertStringContainsString('Test App', $report->html_content);
        $this->assertStringContainsString('Rehost', $report->html_content);
        $this->assertStringContainsString('Estimate, not a quote', $report->html_content);
    }

    public function test_email_report_sends_a_signed_link(): void
    {
        Mail::fake();
        $assessment = $this->completedAssessmentWithApp();
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));
        (new RenderReport($assessment))->handle();

        (new EmailReport($assessment))->handle();

        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();

        Mail::assertSent(ReportReadyMail::class, function (ReportReadyMail $mail) use ($report, $assessment) {
            return $mail->report->id === $report->id
                && str_contains($mail->signedUrl, 'signature=')
                && $mail->hasTo($assessment->creator->email);
        });
    }

    public function test_signed_report_url_is_publicly_viewable_without_auth(): void
    {
        $assessment = $this->completedAssessmentWithApp();
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));
        (new RenderReport($assessment))->handle();
        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();

        $signedUrl = URL::temporarySignedRoute('reports.show', now()->addDays(30), ['report' => $report->id]);

        $response = $this->get($signedUrl);

        $response->assertOk();
        $response->assertSee('Cloud Migration Readiness Report', false);
    }

    public function test_unsigned_report_url_is_rejected(): void
    {
        $assessment = $this->completedAssessmentWithApp();
        (new ScoreAssessment($assessment))->handle(app(GenerateReport::class));
        (new RenderReport($assessment))->handle();
        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();

        $response = $this->get(route('reports.show', $report));

        $response->assertForbidden();
    }

    public function test_completing_the_survey_triggers_the_full_pipeline_synchronously(): void
    {
        config(['queue.default' => 'sync']);
        Mail::fake();

        $this->seedEngineAndPriceTable();
        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme-'.uniqid()]);
        $user = User::factory()->create();
        $organization->users()->attach($user->id, ['is_owner' => true]);

        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $this->actingAs($user)->postJson(route('survey.complete', $assessment), [
            'data' => ['budget_annual_spend' => '$50k - $250k'],
        ]);

        $report = Report::where('assessment_id', $assessment->id)->firstOrFail();
        $this->assertNotNull($report->html_content);
        Mail::assertSent(ReportReadyMail::class);
    }
}
