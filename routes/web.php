<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChatbotPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeaturePageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

// public homepage
Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/signin', function () {
        return redirect()->route('home', ['auth' => 'signin']);
    })->name('signin');

    Route::post('/signin', [AuthenticatedSessionController::class, 'store'])->name('login');

    Route::get('/signup', function () {
        return redirect()->route('home', ['auth' => 'signup']);
    })->name('signup');

    Route::post('/signup', [RegisteredUserController::class, 'store'])->name('register');

    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    // dashboard pages
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/session-setup', function () {
        return view('pages.session-setup', ['title' => 'Session Setup']);
    })->name('session-setup');

    // practice pages
    Route::get('/practice', function () {
        return view('pages.practice', ['title' => 'Practice']);
    })->name('practice');

    Route::get('/progress', function () {
        return view('pages.progress', ['title' => 'Progress']);
    })->name('progress');

    Route::get('/session-review', function () {
        return view('pages.session-review', ['title' => 'Session Review']);
    })->name('session-review');

    Route::get('/feedback-center', function () {
        return view('pages.feedback-center', ['title' => 'Feedback Center']);
    })->name('feedback-center');

    Route::get('/category-insights', function () {
        return view('pages.category-insights', ['title' => 'Category Insights']);
    })->name('category-insights');

    Route::get('/provider-health', [FeaturePageController::class, 'show'])
        ->defaults('page', 'provider-health')
        ->name('provider-health');

    Route::get('/question-generator', [FeaturePageController::class, 'show'])
        ->defaults('page', 'question-generator')
        ->name('question-generator');

    Route::get('/field-builder', [FeaturePageController::class, 'show'])
        ->defaults('page', 'field-builder')
        ->name('field-builder');

    Route::get('/learning-lab', [FeaturePageController::class, 'show'])
        ->defaults('page', 'learning-lab')
        ->name('learning-lab');

    Route::get('/voice-practice', [FeaturePageController::class, 'show'])
        ->defaults('page', 'voice-practice')
        ->name('voice-practice');

    Route::get('/camera-readiness', [FeaturePageController::class, 'show'])
        ->defaults('page', 'camera-readiness')
        ->name('camera-readiness');

    Route::get('/mobile-lan', [FeaturePageController::class, 'show'])
        ->defaults('page', 'mobile-lan')
        ->name('mobile-lan');

    Route::get('/chatbot', ChatbotPageController::class)->name('chatbot');

    // backward-compatible alias for the old calendar path
    Route::get('/calendar', function () {
        return view('pages.practice', ['title' => 'Practice']);
    })->name('calendar');

    // profile pages
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::patch('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('users.avatar');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('workspace')->name('workspace.')->group(function () {
        Route::get('/bootstrap', [WorkspaceController::class, 'bootstrap'])->name('bootstrap');
        Route::put('/setup', [WorkspaceController::class, 'updateSetup'])->name('setup.update');
        Route::delete('/setup', [WorkspaceController::class, 'destroySetup'])->name('setup.destroy');
        Route::post('/sessions', [WorkspaceController::class, 'storeSession'])->name('sessions.store');
        Route::delete('/sessions', [WorkspaceController::class, 'destroySessions'])->name('sessions.destroy');
        Route::post('/chatbot', [WorkspaceController::class, 'chatbot'])->name('chatbot');
        Route::post('/chatbot/providers/status', [WorkspaceController::class, 'chatbotProvidersStatus'])->name('chatbot.providers.status');
    });
});
