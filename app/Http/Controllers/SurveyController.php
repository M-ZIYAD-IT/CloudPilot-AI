<?php

namespace App\Http\Controllers;

use App\Jobs\EmailReport;
use App\Jobs\GenerateNarrative;
use App\Jobs\RenderReport;
use App\Jobs\ScoreAssessment;
use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $organization = app(Organization::class);

        $assessment = $organization->assessments()
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (! $assessment) {
            $assessment = $organization->assessments()->create([
                'created_by' => $request->user()->id,
                'status' => 'in_progress',
            ]);
        }

        return redirect()->route('survey.show', $assessment);
    }

    public function show(Assessment $assessment): View
    {
        $this->authorizeAssessment($assessment);

        if ($assessment->surveyEvents()->where('event', 'survey_started')->doesntExist()) {
            $assessment->surveyEvents()->create(['event' => 'survey_started']);
        }

        $data = $assessment->answers()->pluck('value', 'question_key')->all();

        // Default the client-name field to the account's own organization name —
        // covers the common self-serve case for free, while still letting a
        // consultant overwrite it with their client's name before submitting.
        if (! array_key_exists('client_name', $data)) {
            $data['client_name'] = $assessment->organization->name;
        }

        $declaredApps = $assessment->apps()->get()->map(fn ($app) => [
            'name' => $app->name,
            'category' => $app->category,
            'is_cots' => $app->is_cots,
            'vendor_supported' => $app->vendor_supported,
            'licensing_tied_to_hardware' => $app->licensing_tied_to_hardware,
        ])->all();

        if ($declaredApps !== []) {
            $data['declared_apps'] = $declaredApps;
        }

        $surveyJson = json_decode(file_get_contents(resource_path('survey/mvp-survey.json')), true);

        return view('survey.show', [
            'assessment' => $assessment,
            'surveyJson' => $surveyJson,
            'surveyData' => $data,
        ]);
    }

    public function saveAnswers(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorizeAssessment($assessment);

        $validated = $request->validate([
            'data' => ['required', 'array'],
            'page' => ['nullable', 'string'],
        ]);

        $this->persistAnswers($assessment, $validated['data']);

        if (! empty($validated['page'])) {
            $assessment->surveyEvents()->create([
                'event' => 'page_completed',
                'page_name' => $validated['page'],
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function complete(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorizeAssessment($assessment);

        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $this->persistAnswers($assessment, $validated['data']);

        $assessment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $assessment->surveyEvents()->create(['event' => 'survey_completed']);

        Bus::chain([
            new ScoreAssessment($assessment),
            new GenerateNarrative($assessment),
            new RenderReport($assessment),
            new EmailReport($assessment),
        ])->dispatch();

        return response()->json(['redirect' => route('survey.thank-you', $assessment)]);
    }

    public function thankYou(Assessment $assessment): View
    {
        $this->authorizeAssessment($assessment);

        return view('survey.thank-you', ['assessment' => $assessment]);
    }

    /**
     * Persist a SurveyJS data payload: everything but `declared_apps` becomes an
     * Answer row keyed by question name; `declared_apps` is synced to the apps
     * table separately since it models up to 5 repeatable records, not a scalar answer.
     */
    private function persistAnswers(Assessment $assessment, array $data): void
    {
        $declaredApps = $data['declared_apps'] ?? null;
        unset($data['declared_apps']);

        foreach ($data as $questionKey => $value) {
            Answer::updateOrCreate(
                ['assessment_id' => $assessment->id, 'question_key' => $questionKey],
                ['value' => $value]
            );
        }

        if (! is_array($declaredApps)) {
            return;
        }

        $assessment->apps()->delete();

        foreach (array_slice($declaredApps, 0, 5) as $app) {
            $assessment->apps()->create([
                'name' => $app['name'] ?? '',
                'category' => $app['category'] ?? '',
                'is_cots' => (bool) ($app['is_cots'] ?? false),
                'vendor_supported' => array_key_exists('vendor_supported', $app) ? (bool) $app['vendor_supported'] : null,
                'licensing_tied_to_hardware' => (bool) ($app['licensing_tied_to_hardware'] ?? false),
            ]);
        }
    }

    private function authorizeAssessment(Assessment $assessment): void
    {
        abort_unless($assessment->organization_id === app(Organization::class)->id, 404);
    }
}
