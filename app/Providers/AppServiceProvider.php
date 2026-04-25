<?php

namespace App\Providers;

use App\Helpers\InterviewChatbotService;
use App\Helpers\InterviewerSpeechService;
use App\Helpers\InterviewWorkspaceService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $view->with('interviewWorkspaceBootstrap', app(InterviewWorkspaceService::class)->bootstrap());
            $view->with('interviewWorkspaceRoutes', [
                'bootstrap' => route('workspace.bootstrap'),
                'updateSetup' => route('workspace.setup.update'),
                'destroySetup' => route('workspace.setup.destroy'),
                'storeSession' => route('workspace.sessions.store'),
                'destroySessions' => route('workspace.sessions.destroy'),
                'chatbot' => route('workspace.chatbot'),
                'chatbotProvidersStatus' => route('workspace.chatbot.providers.status'),
                'interviewerSpeak' => route('workspace.interviewer.speak'),
            ]);
            $view->with('interviewChatbotBootstrap', app(InterviewChatbotService::class)->frontendBootstrap());
            $view->with('interviewAudioBootstrap', app(InterviewerSpeechService::class)->frontendBootstrap());
        });
    }
}
