<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('practice page uses sidebar launch guidance and keeps live visual coaching', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('practice'))
        ->assertOk()
        ->assertSeeText('Live Interview Workspace')
        ->assertSeeText('Choose Interview Track')
        ->assertSeeText('Choose category first')
        ->assertSeeText('Pick the interview category, confirm your target field, and the live interview workspace will take over.')
        ->assertSeeText('Live Interview')
        ->assertSeeText('AI Interviewer')
        ->assertSeeText('Opening Conversation')
        ->assertSeeText('Mic On')
        ->assertSeeText('Voice commands work locally')
        ->assertSeeText('Resume/Application Analyzer')
        ->assertSeeText('Mock Panel Interview')
        ->assertSeeText('Difficult Interview Mode')
        ->assertSeeText('Filler Word Tracker')
        ->assertSeeText('Answer Versions')
        ->assertSeeText('Ask Question')
        ->assertSeeText('Send Answer')
        ->assertSeeText('Next Question')
        ->assertSee('id="questionCountSelect"', false)
        ->assertSee('id="practiceFieldProviderSelect"', false)
        ->assertSee('<option value="auto">AI Provider</option>', false)
        ->assertSee('<option value="local">Local PH coach</option>', false)
        ->assertSee('id="openPracticeCategoryModalBtn"', false)
        ->assertSee('id="practiceCategoryModal"', false)
        ->assertSee('id="openPracticeQuestionAgentModalBtn"', false)
        ->assertSee('id="practiceQuestionAgentModal"', false)
        ->assertSee('id="startPracticeBtn"', false)
        ->assertSee('id="nextQuestionBtn"', false)
        ->assertDontSee('id="practiceSetupSection"', false)
        ->assertDontSeeText('AI Avatar Interview Simulation')
        ->assertDontSeeText('AI Question Generator')
        ->assertDontSeeText('Start Practice')
        ->assertDontSeeText('Start Voice Input')
        ->assertDontSeeText('Ask Current Question')
        ->assertDontSeeText('Automated Feedback')
        ->assertSee('id="practiceCategoryList"', false)
        ->assertDontSee('id="feedbackContent"', false)
        ->assertDontSee('id="learningLabSection"', false)
        ->assertDontSee('id="learningModulesList"', false)
        ->assertSee('id="livePresenceSummary"', false)
        ->assertDontSeeText('The AI greets you before question one.')
        ->assertDontSee('id="interviewerWelcomePreview"', false);
});
