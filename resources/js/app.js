import './bootstrap';
import Alpine from 'alpinejs';

import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;

const sharedImports = {
    flatpickr: null,
};

const loadFlatpickr = async () => {
    if (!sharedImports.flatpickr) {
        sharedImports.flatpickr = import('flatpickr').then(({ default: flatpickr }) => {
            window.flatpickr = flatpickr;
            return flatpickr;
        });
    }

    return sharedImports.flatpickr;
};

window.InterviewPilot = window.InterviewPilot || {};
window.InterviewPilot.loadFlatpickr = loadFlatpickr;

Alpine.start();

const initializePageLoader = () => {
    const loader = document.querySelector('[data-page-loader]');

    if (!loader) {
        return;
    }

    let activeSince = Date.now();
    let hideTimer = null;

    const setLoaderActive = (isActive) => {
        window.clearTimeout(hideTimer);
        hideTimer = null;

        if (isActive) {
            activeSince = Date.now();
        }

        loader.classList.toggle('is-active', isActive);
        loader.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    };

    const hideLoader = (minimumVisibleMs = 100) => {
        const remaining = Math.max(0, minimumVisibleMs - (Date.now() - activeSince));

        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(() => setLoaderActive(false), remaining);
    };

    const showLoader = ({ persist = false, visibleForMs = 100 } = {}) => {
        setLoaderActive(true);

        if (!persist) {
            hideTimer = window.setTimeout(() => hideLoader(0), visibleForMs);
        }
    };

    const isDisabledControl = (element) => {
        return element.matches(':disabled, [aria-disabled="true"]');
    };

    const shouldIgnoreTrigger = (element) => {
        return Boolean(element.closest('[data-no-page-loader], [data-page-loader-ignore]'));
    };

    const linkWillNavigate = (link) => {
        const href = String(link.getAttribute('href') || '').trim();
        const target = String(link.getAttribute('target') || '_self').toLowerCase();

        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return false;
        }

        if (href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        if (target && target !== '_self') {
            return false;
        }

        if (link.hasAttribute('download')) {
            return false;
        }

        try {
            const url = new URL(href, window.location.href);
            const current = window.location;
            return url.origin !== current.origin
                || url.pathname !== current.pathname
                || url.search !== current.search;
        } catch (error) {
            return true;
        }
    };

    window.InterviewPilotLoader = {
        show: showLoader,
        hide: hideLoader,
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const target = event.target instanceof Element ? event.target : null;
        const link = target?.closest('a[href]');

        if (!link || shouldIgnoreTrigger(link) || isDisabledControl(link) || !linkWillNavigate(link)) {
            return;
        }

        showLoader({ persist: true });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target instanceof HTMLFormElement ? event.target : null;
        const target = String(form?.getAttribute('target') || '_self').toLowerCase();

        if (!form || event.defaultPrevented || shouldIgnoreTrigger(form) || (target && target !== '_self')) {
            return;
        }

        showLoader({ persist: true });
    });

    window.addEventListener('beforeunload', () => showLoader({ persist: true }));
    window.addEventListener('pageshow', () => hideLoader(0));

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.setTimeout(() => hideLoader(0), 100);
        }, { once: true });
    } else {
        window.setTimeout(() => hideLoader(0), 100);
    }
};

initializePageLoader();

const initializeWhenPresent = (selector, loadModule, initializeModule, errorLabel) => {
    if (!document.querySelector(selector)) {
        return;
    }

    void loadModule()
        .then((module) => initializeModule(module))
        .catch((error) => console.error(errorLabel, error));
};

const scheduleBackgroundTask = (task, errorLabel) => {
    const runTask = () => {
        void Promise.resolve()
            .then(task)
            .catch((error) => console.error(errorLabel, error));
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(runTask, { timeout: 800 });
        return;
    }

    window.setTimeout(runTask, 1);
};

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.__INTERVIEW_WORKSPACE_ROUTES__) {
        scheduleBackgroundTask(async () => {
            const { initializeWorkspaceMigration } = await import('./components/workspace-api');
            await initializeWorkspaceMigration();
        }, 'Workspace bootstrap failed');
    }

    initializeWhenPresent('#mapOne', () => import('./components/map'), (module) => module.initMap(), 'Map initialization failed');
    initializeWhenPresent('#chartOne', () => import('./components/chart/chart-1'), (module) => module.initChartOne(), 'Chart One initialization failed');
    initializeWhenPresent('#chartTwo', () => import('./components/chart/chart-2'), (module) => module.initChartTwo(), 'Chart Two initialization failed');
    initializeWhenPresent('#chartThree', () => import('./components/chart/chart-3'), (module) => module.initChartThree(), 'Chart Three initialization failed');
    initializeWhenPresent('#practiceApp', () => import('./components/practice'), (module) => module.initPractice(), 'Practice initialization failed');
    initializeWhenPresent('#chatbotApp', () => import('./components/chatbot-page'), (module) => module.initChatbotPage(), 'Chatbot initialization failed');
    initializeWhenPresent('#sessionSetupApp', () => import('./components/session-setup'), (module) => module.initSessionSetup(), 'Session setup initialization failed');
    initializeWhenPresent('#progressApp', () => import('./components/progress'), (module) => module.initProgress(), 'Progress initialization failed');
    initializeWhenPresent('#sessionReviewApp', () => import('./components/session-review'), (module) => module.initSessionReview(), 'Session review initialization failed');
    initializeWhenPresent('#feedbackCenterApp', () => import('./components/feedback-center'), (module) => module.initFeedbackCenter(), 'Feedback center initialization failed');
    initializeWhenPresent('#categoryInsightsApp', () => import('./components/category-insights'), (module) => module.initCategoryInsights(), 'Category insights initialization failed');
    initializeWhenPresent('#calendar', () => import('./components/calendar-init'), (module) => module.calendarInit(), 'Calendar initialization failed');
});
