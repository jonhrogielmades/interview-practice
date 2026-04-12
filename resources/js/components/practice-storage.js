import { getWorkspaceState, requestWorkspace } from "./workspace-api";

export const PRACTICE_STORAGE_KEY = "ai_interview_practice_sessions";
export const PRACTICE_SESSIONS_UPDATED_EVENT = "practice-sessions-updated";

function dispatchPracticeSessionsUpdated(sessions) {
    if (typeof window === "undefined") {
        return;
    }

    window.dispatchEvent(new CustomEvent(PRACTICE_SESSIONS_UPDATED_EVENT, {
        detail: { sessions }
    }));
}

export function readPracticeSessions() {
    return getWorkspaceState().sessions
        .filter((session) => session && typeof session === "object")
        .sort((left, right) => new Date(right.savedAt || 0) - new Date(left.savedAt || 0));
}

export async function writePracticeSessions(sessions) {
    await requestWorkspace("destroySessions", {
        method: "DELETE"
    });

    for (const session of sessions.slice(-100).reverse()) {
        await requestWorkspace("storeSession", {
            method: "POST",
            body: {
                ...session,
                notify: false
            }
        });
    }

    const savedSessions = readPracticeSessions();
    dispatchPracticeSessionsUpdated(savedSessions);
    return savedSessions;
}

export async function appendPracticeSession(session) {
    await requestWorkspace("storeSession", {
        method: "POST",
        body: {
            ...session,
            notify: true
        }
    });

    const sessions = readPracticeSessions();
    dispatchPracticeSessionsUpdated(sessions);
    return sessions;
}

export async function clearPracticeSessions() {
    await requestWorkspace("destroySessions", {
        method: "DELETE"
    });
    dispatchPracticeSessionsUpdated([]);
}

export async function removePracticeSession(sessionId) {
    const targetId = String(sessionId || "").trim();

    if (!targetId) {
        throw new Error("A saved session id is required.");
    }

    const sessions = readPracticeSessions();
    const remainingSessions = sessions.filter((session) => String(session.id || "") !== targetId);

    if (remainingSessions.length === sessions.length) {
        return sessions;
    }

    if (!remainingSessions.length) {
        await clearPracticeSessions();
        return [];
    }

    return writePracticeSessions(remainingSessions);
}
