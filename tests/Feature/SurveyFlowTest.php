<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\EngineVersion;
use App\Models\Organization;
use App\Models\PriceTable;
use App\Models\SurveyEvent;
use App\Models\User;
use App\Scoring\RulesLoader;
use App\Scoring\ScoringEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SurveyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Completing a survey triggers the Phase 3 report pipeline (QUEUE_CONNECTION=sync
        // in phpunit.xml runs it inline), which needs a price table + engine version to exist -
        // exactly as they would in production, seeded by DatabaseSeeder before real usage.
        Mail::fake();

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

    private function makeUserWithOrganization(): User
    {
        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme-'.uniqid()]);
        $user = User::factory()->create();
        $organization->users()->attach($user->id, ['is_owner' => true]);

        return $user;
    }

    public function test_starting_a_survey_creates_an_in_progress_assessment_and_logs_started_event(): void
    {
        $user = $this->makeUserWithOrganization();

        $response = $this->actingAs($user)->get('/survey');

        $assessment = Assessment::firstOrFail();
        $response->assertRedirect(route('survey.show', $assessment));
        $this->assertSame('in_progress', $assessment->status);

        $this->get(route('survey.show', $assessment));
        $this->assertSame(1, SurveyEvent::where('event', 'survey_started')->count());
    }

    public function test_visiting_the_survey_twice_does_not_duplicate_the_started_event(): void
    {
        $user = $this->makeUserWithOrganization();
        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $this->actingAs($user)->get(route('survey.show', $assessment));
        $this->actingAs($user)->get(route('survey.show', $assessment));

        $this->assertSame(1, SurveyEvent::where('event', 'survey_started')->count());
    }

    public function test_a_user_cannot_view_another_organizations_assessment(): void
    {
        $user = $this->makeUserWithOrganization();
        $otherUser = $this->makeUserWithOrganization();
        $this->actingAs($otherUser)->get('/survey');
        $othersAssessment = Assessment::firstOrFail();

        $response = $this->actingAs($user)->get(route('survey.show', $othersAssessment));

        $response->assertNotFound();
    }

    public function test_saving_answers_persists_them_and_logs_page_completed_when_a_page_is_given(): void
    {
        $user = $this->makeUserWithOrganization();
        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $response = $this->actingAs($user)->postJson(route('survey.answers', $assessment), [
            'data' => ['business_timeline' => 'Urgent (< 3 months)'],
            'page' => 'business',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(
            'Urgent (< 3 months)',
            Answer::where('assessment_id', $assessment->id)->where('question_key', 'business_timeline')->value('value')
        );
        $this->assertSame(1, SurveyEvent::where('event', 'page_completed')->where('page_name', 'business')->count());
    }

    public function test_saving_answers_without_a_page_does_not_log_a_page_completed_event(): void
    {
        $user = $this->makeUserWithOrganization();
        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $this->actingAs($user)->postJson(route('survey.answers', $assessment), [
            'data' => ['business_timeline' => 'Urgent (< 3 months)'],
        ]);

        $this->assertSame(0, SurveyEvent::where('event', 'page_completed')->count());
    }

    public function test_saving_declared_apps_syncs_the_apps_table(): void
    {
        $user = $this->makeUserWithOrganization();
        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $this->actingAs($user)->postJson(route('survey.answers', $assessment), [
            'data' => [
                'declared_apps' => [
                    [
                        'name' => 'SAP ERP',
                        'category' => 'ERP',
                        'is_cots' => true,
                        'vendor_supported' => true,
                        'licensing_tied_to_hardware' => true,
                    ],
                ],
            ],
        ]);

        $this->assertSame(1, $assessment->apps()->count());
        $app = $assessment->apps()->first();
        $this->assertSame('SAP ERP', $app->name);
        $this->assertTrue($app->is_cots);

        // Re-saving with an empty list clears previously declared apps.
        $this->actingAs($user)->postJson(route('survey.answers', $assessment), [
            'data' => ['declared_apps' => []],
        ]);

        $this->assertSame(0, $assessment->apps()->count());
    }

    public function test_completing_the_survey_marks_it_done_and_returns_a_redirect(): void
    {
        $user = $this->makeUserWithOrganization();
        $this->actingAs($user)->get('/survey');
        $assessment = Assessment::firstOrFail();

        $response = $this->actingAs($user)->postJson(route('survey.complete', $assessment), [
            'data' => ['business_timeline' => 'Urgent (< 3 months)'],
        ]);

        $response->assertOk()->assertJson(['redirect' => route('survey.thank-you', $assessment)]);

        $assessment->refresh();
        $this->assertSame('completed', $assessment->status);
        $this->assertNotNull($assessment->completed_at);
        $this->assertSame(1, SurveyEvent::where('event', 'survey_completed')->count());
    }
}
