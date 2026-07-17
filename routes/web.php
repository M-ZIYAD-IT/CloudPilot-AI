<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SurveyController;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show')->middleware('signed');

Route::get('/dashboard', function () {
    $organization = app(Organization::class);

    $assessments = $organization->assessments()
        ->with('reports')
        ->latest()
        ->get();

    $activeAssessment = $assessments->firstWhere('status', 'in_progress');
    $completedAssessments = $assessments->where('status', 'completed');
    $latestCompleted = $completedAssessments->first();

    $latestReport = $latestCompleted?->reports
        ->filter(fn ($report) => $report->html_content !== null)
        ->sortByDesc('generated_at')
        ->first();

    $latestReportUrl = $latestReport
        ? URL::temporarySignedRoute('reports.show', now()->addDays(30), ['report' => $latestReport->id])
        : null;

    // The account org can run assessments for different clients (e.g. a
    // consultant), so each assessment's own "who is this for" answer takes
    // priority over the account name wherever we're talking about one
    // specific assessment rather than the account as a whole.
    $clientNameFor = fn (?\App\Models\Assessment $assessment) => $assessment
        ?->answers()->where('question_key', 'client_name')->value('value')
        ?? $organization->name;

    // Older completed assessments aren't deleted when a new one finishes —
    // they just used to be invisible, since the dashboard only ever surfaced
    // the single latest report. Surface the rest here instead of silently
    // dropping access to them.
    $pastReports = $completedAssessments
        ->skip(1)
        ->map(function ($assessment) use ($clientNameFor) {
            $report = $assessment->reports
                ->filter(fn ($report) => $report->html_content !== null)
                ->sortByDesc('generated_at')
                ->first();

            if (! $report) {
                return null;
            }

            return [
                'clientName' => $clientNameFor($assessment),
                'completedAt' => $assessment->completed_at,
                'url' => URL::temporarySignedRoute('reports.show', now()->addDays(30), ['report' => $report->id]),
            ];
        })
        ->filter()
        ->values();

    return view('dashboard', [
        'organization' => $organization,
        'activeAssessment' => $activeAssessment,
        'activeAssessmentClientName' => $clientNameFor($activeAssessment),
        'latestCompleted' => $latestCompleted,
        'latestCompletedClientName' => $clientNameFor($latestCompleted),
        'latestReportUrl' => $latestReportUrl,
        'isGeneratingReport' => $latestCompleted !== null && $latestReport === null,
        'pastReports' => $pastReports,
    ]);
})->middleware(['auth', 'verified', 'scope.organization'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'scope.organization'])->group(function () {
    Route::get('/survey', [SurveyController::class, 'start'])->name('survey.start');
    Route::get('/survey/{assessment}', [SurveyController::class, 'show'])->name('survey.show');
    Route::post('/survey/{assessment}/answers', [SurveyController::class, 'saveAnswers'])->name('survey.answers');
    Route::post('/survey/{assessment}/complete', [SurveyController::class, 'complete'])->name('survey.complete');
    Route::get('/survey/{assessment}/thank-you', [SurveyController::class, 'thankYou'])->name('survey.thank-you');
});

require __DIR__.'/auth.php';
