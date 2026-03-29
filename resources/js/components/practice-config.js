import { getWorkspaceState, requestWorkspace } from "./workspace-api";

export const practiceData = {
    focusModes: [
        {
            label: "Balanced Coach",
            tip: "Give a direct answer first, then support it with one clear example."
        },
        {
            label: "Confidence Coach",
            tip: "Use strong, positive words and avoid uncertain phrases."
        },
        {
            label: "Clarity Coach",
            tip: "Keep your answer organized: main point, example, closing statement."
        },
        {
            label: "Professional Coach",
            tip: "Use formal wording and highlight responsibility, teamwork, and results."
        }
    ],
    pacingModes: [
        { label: "Standard", seconds: 180 },
        { label: "Quick", seconds: 90 },
        { label: "Extended", seconds: 240 }
    ],
    categories: [
        {
            id: "job",
            name: "Job Interview",
            description: "Philippine hiring questions for fresh graduates, career shifters, and office or remote roles.",
            keywords: ["ojt", "experience", "skills", "responsibility", "results", "teamwork"],
            quickPrompts: [
                "Give me 3 Philippine-style follow-up questions for this category.",
                "Show me a strong sample answer for a Philippine interviewer.",
                "What mistakes should Filipino applicants avoid in this category?"
            ],
            questions: [
                "Tell me about yourself and how your background in the Philippines prepared you for this role.",
                "What skills from your OJT, internship, or part-time work can you bring to our company?",
                "Describe a challenge you handled in school, work, or your community and how you solved it.",
                "Why do you want to work for our company in the Philippines?",
                "How do you see your career growing in the Philippine job market over the next five years?"
            ]
        },
        {
            id: "scholarship",
            name: "Scholarship Interview",
            description: "Philippine scholarship questions about academic goals, financial need, service, and future contribution.",
            keywords: ["goals", "leadership", "service", "achievement", "family", "community"],
            quickPrompts: [
                "Give me 3 scholarship interview questions used in the Philippines.",
                "How can I sound sincere without sounding rehearsed?",
                "What should I mention about family and financial need professionally?"
            ],
            questions: [
                "Why should you be chosen for this scholarship in the Philippines?",
                "How will this scholarship help your studies, family, and long-term goals?",
                "What achievement best shows your discipline and leadership?",
                "How do you balance academics, home responsibilities, and community involvement?",
                "How will you use your education to help your family or community in the future?"
            ]
        },
        {
            id: "admission",
            name: "College Admission",
            description: "Philippine college and university admission questions focused on motivation, readiness, and program fit.",
            keywords: ["motivation", "program", "interest", "future", "readiness", "education"],
            quickPrompts: [
                "Give me 3 Philippine college admission follow-up questions.",
                "How should I explain why I chose this course?",
                "What answer structure works best for admission interviews?"
            ],
            questions: [
                "Why do you want to take this program at a Philippine college or university?",
                "What experiences in senior high school or your community prepared you for this course?",
                "How do you handle academic pressure, deadlines, and responsibilities at home?",
                "What makes you a strong candidate for this program?",
                "What goals do you want to achieve after graduating in the Philippines?"
            ]
        },
        {
            id: "it",
            name: "IT / Programming",
            description: "Philippine tech interview questions about coding, capstone work, debugging, and teamwork.",
            keywords: ["project", "problem-solving", "technology", "teamwork", "capstone", "debugging"],
            quickPrompts: [
                "Give me 3 Philippine IT interview follow-up questions.",
                "How can I explain my capstone project better?",
                "What technical mistakes should I avoid in an entry-level IT interview?"
            ],
            questions: [
                "Tell me about a capstone, freelance, or school project you built and your role in it.",
                "How do you troubleshoot programming bugs when your deadline is near?",
                "Which programming languages, frameworks, or tools are you most comfortable using, and why?",
                "How do you work with a team during software development or group projects?",
                "Why do you want to build your career in the IT industry in the Philippines?"
            ]
        }
    ]
};

export const questionCountOptions = [
    {
        value: 3,
        label: "Quick Drill (3 questions)"
    },
    {
        value: 5,
        label: "Full Mock (5 questions)"
    },
    {
        value: 10,
        label: "Extended Practice (10 questions)"
    },
    {
        value: 15,
        label: "Intensive Round (15 questions)"
    },
    {
        value: 20,
        label: "Marathon Mock (20 questions)"
    }
];

export const responsePreferenceOptions = [
    {
        value: "text",
        label: "Text First"
    },
    {
        value: "voice",
        label: "Voice First"
    },
    {
        value: "hybrid",
        label: "Hybrid"
    }
];

export const SESSION_SETUP_STORAGE_KEY = "ai_interview_session_setup_defaults";
export const SESSION_SETUP_UPDATED_EVENT = "session-setup-updated";

function dispatchSessionSetupUpdated(setup) {
    if (typeof window === "undefined") {
        return;
    }

    window.dispatchEvent(new CustomEvent(SESSION_SETUP_UPDATED_EVENT, {
        detail: { setup }
    }));
}

export function getPracticeCategory(categoryId) {
    return practiceData.categories.find((category) => category.id === categoryId) || null;
}

export function getChatbotQuickPrompts(categoryId = null, question = "") {
    const category = getPracticeCategory(categoryId);
    const prompts = category?.quickPrompts?.slice() || [
        "Give me 3 Philippine interview questions for beginners.",
        "How should I answer Tell me about yourself in the Philippines?",
        "What common interview mistakes should I avoid locally?"
    ];

    if (String(question || "").trim()) {
        prompts.push("Give me a stronger answer for this question.");
        prompts.push("What follow-up questions can a Philippine interviewer ask next?");
    }

    return Array.from(new Set(prompts)).slice(0, 4);
}

export function formatPracticeTime(totalSeconds) {
    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, "0");
    const seconds = String(totalSeconds % 60).padStart(2, "0");
    return `${minutes}:${seconds}`;
}

export function createDefaultSessionSetup() {
    return {
        questionCount: questionCountOptions[0].value,
        focusModeIndex: 0,
        pacingModeIndex: 0,
        preferredCategoryId: practiceData.categories[0].id,
        voiceMode: responsePreferenceOptions[0].value,
        notes: "",
        savedAt: null
    };
}

export function getQuestionCountOption(value) {
    return questionCountOptions.find((option) => option.value === Number(value)) || questionCountOptions[0];
}

export function getResponsePreferenceOption(value) {
    return responsePreferenceOptions.find((option) => option.value === value) || responsePreferenceOptions[0];
}

export function normalizeSessionSetup(input = {}) {
    const defaults = createDefaultSessionSetup();
    const normalized = { ...defaults };

    const questionCount = Number(input.questionCount);
    if (questionCountOptions.some((option) => option.value === questionCount)) {
        normalized.questionCount = questionCount;
    }

    const focusModeIndex = Number(input.focusModeIndex);
    if (Number.isInteger(focusModeIndex) && focusModeIndex >= 0 && focusModeIndex < practiceData.focusModes.length) {
        normalized.focusModeIndex = focusModeIndex;
    }

    const pacingModeIndex = Number(input.pacingModeIndex);
    if (Number.isInteger(pacingModeIndex) && pacingModeIndex >= 0 && pacingModeIndex < practiceData.pacingModes.length) {
        normalized.pacingModeIndex = pacingModeIndex;
    }

    const preferredCategoryId = String(input.preferredCategoryId ?? "");
    if (practiceData.categories.some((category) => category.id === preferredCategoryId)) {
        normalized.preferredCategoryId = preferredCategoryId;
    }

    const voiceMode = String(input.voiceMode ?? "");
    if (responsePreferenceOptions.some((option) => option.value === voiceMode)) {
        normalized.voiceMode = voiceMode;
    }

    normalized.notes = typeof input.notes === "string" ? input.notes.trim().slice(0, 500) : "";
    normalized.savedAt = typeof input.savedAt === "string" ? input.savedAt : null;

    return normalized;
}

export function readSessionSetup() {
    const setup = getWorkspaceState().setup;
    return normalizeSessionSetup(setup || createDefaultSessionSetup());
}

export async function writeSessionSetup(input) {
    const normalized = normalizeSessionSetup({
        ...input,
        savedAt: new Date().toISOString()
    });
    const payload = await requestWorkspace("updateSetup", {
        method: "PUT",
        body: normalized
    });
    const saved = normalizeSessionSetup(payload.setup || normalized);
    dispatchSessionSetupUpdated(saved);
    return saved;
}

export async function clearSessionSetup() {
    const payload = await requestWorkspace("destroySetup", {
        method: "DELETE"
    });
    const cleared = normalizeSessionSetup(payload.setup || createDefaultSessionSetup());
    dispatchSessionSetupUpdated(cleared);
    return cleared;
}
