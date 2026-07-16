<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
