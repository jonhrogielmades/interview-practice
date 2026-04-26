<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\AdminApiManagementController;
use App\Http\Controllers\Admin\AdminContentPageController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMonitoringPageController;
use App\Http\Controllers\Admin\AdminUsersPageController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ChatbotPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeaturePageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// public homepage
Route::get('/', HomeController::class)->name('home');

// AI Translation Endpoint
Route::post('/api/translate', [TranslationController::class, 'translate'])->name('api.translate');

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
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()?->isAdmin() ? 'admin.dashboard' : 'user.dashboard');
    })->name('dashboard');

    Route::middleware('user-area')->group(function () {
        // user dashboard pages
        Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

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

        Route::get('/question-generator', [FeaturePageController::class, 'show'])
            ->defaults('page', 'question-generator')
            ->name('question-generator');

        Route::get('/field-builder', [FeaturePageController::class, 'show'])
            ->defaults('page', 'field-builder')
            ->name('field-builder');

        Route::get('/learning-lab', [FeaturePageController::class, 'show'])
            ->defaults('page', 'learning-lab')
            ->name('learning-lab');

        Route::get('/learning-lab/activities', [FeaturePageController::class, 'show'])
            ->defaults('page', 'learning-lab-activities')
            ->name('learning-lab.activities');

        Route::get('/voice-practice', [FeaturePageController::class, 'show'])
            ->defaults('page', 'voice-practice')
            ->name('voice-practice');

        Route::get('/camera-readiness', [FeaturePageController::class, 'show'])
            ->defaults('page', 'camera-readiness')
            ->name('camera-readiness');

        Route::get('/chatbot', ChatbotPageController::class)->name('chatbot');

        // backward-compatible alias for the old calendar path
        Route::get('/calendar', function () {
            return view('pages.practice', ['title' => 'Practice']);
        })->name('calendar');

        Route::prefix('workspace')->name('workspace.')->group(function () {
            Route::get('/bootstrap', [WorkspaceController::class, 'bootstrap'])->name('bootstrap');
            Route::put('/setup', [WorkspaceController::class, 'updateSetup'])->name('setup.update');
            Route::delete('/setup', [WorkspaceController::class, 'destroySetup'])->name('setup.destroy');
            Route::post('/sessions', [WorkspaceController::class, 'storeSession'])->name('sessions.store');
            Route::delete('/sessions', [WorkspaceController::class, 'destroySessions'])->name('sessions.destroy');
            Route::post('/chatbot', [WorkspaceController::class, 'chatbot'])->name('chatbot');
            Route::post('/chatbot/providers/status', [WorkspaceController::class, 'chatbotProvidersStatus'])->name('chatbot.providers.status');
            Route::post('/interviewer/speak', [WorkspaceController::class, 'interviewerSpeak'])->name('interviewer.speak');
        });
    });

    // profile pages
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::patch('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('users.avatar');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/provider-health', function (Request $request) {
        abort_unless($request->user()?->isAdmin(), 403);

        return redirect()->route('admin.apis');
    })->name('provider-health');

    Route::get('/mobile-lan', function (Request $request) {
        abort_unless($request->user()?->isAdmin(), 403);

        return redirect()->route('admin.mobile-lan');
    })->name('mobile-lan');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', AdminUsersPageController::class)->name('users');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::get('/apis', AdminApiManagementController::class)->name('apis');
        Route::get('/content', AdminContentPageController::class)->name('content');
        Route::get('/monitoring', AdminMonitoringPageController::class)->name('monitoring');
        Route::get('/mobile-lan', [FeaturePageController::class, 'show'])
            ->defaults('page', 'mobile-lan')
            ->name('mobile-lan');
        Route::post('/apis/providers/status', [AdminApiManagementController::class, 'providerStatuses'])->name('apis.providers.status');
        Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role.update');
    });
});
