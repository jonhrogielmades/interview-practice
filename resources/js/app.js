import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', async () => {
    if (window.__INTERVIEW_WORKSPACE_ROUTES__) {
        try {
            const { initializeWorkspaceMigration } = await import('./components/workspace-api');
            await initializeWorkspaceMigration();
        } catch (error) {
            console.error('Workspace bootstrap failed', error);
        }
    }

    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    // Practice page
    if (document.querySelector('#practiceApp')) {
        import('./components/practice').then(module => module.initPractice());
    }

    // Chatbot page
    if (document.querySelector('#chatbotApp')) {
        import('./components/chatbot-page').then(module => module.initChatbotPage());
    }

    // Session setup page
    if (document.querySelector('#sessionSetupApp')) {
        import('./components/session-setup').then(module => module.initSessionSetup());
    }

    // Progress page
    if (document.querySelector('#progressApp')) {
        import('./components/progress').then(module => module.initProgress());
    }

    // Session review page
    if (document.querySelector('#sessionReviewApp')) {
        import('./components/session-review').then(module => module.initSessionReview());
    }

    // Feedback center page
    if (document.querySelector('#feedbackCenterApp')) {
        import('./components/feedback-center').then(module => module.initFeedbackCenter());
    }

    // Category insights page
    if (document.querySelector('#categoryInsightsApp')) {
        import('./components/category-insights').then(module => module.initCategoryInsights());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
