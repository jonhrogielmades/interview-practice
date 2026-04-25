const LEGACY_SESSION_SETUP_KEY = "ai_interview_session_setup_defaults";
const LEGACY_PRACTICE_SESSIONS_KEY = "ai_interview_practice_sessions";
const DEFAULT_WORKSPACE = {
    setup: null,
    sessions: []
};

let workspaceState = buildInitialWorkspaceState();
let migrationPromise = null;

function normalizeWorkspaceState(value) {
    const setup = value?.setup && typeof value.setup === "object" ? { ...value.setup } : null;
    const sessions = Array.isArray(value?.sessions)
        ? value.sessions.filter((session) => session && typeof session === "object")
        : [];

    return {
        setup,
        sessions
    };
}

function hasMeaningfulWorkspaceData(workspace) {
    return Boolean(workspace?.setup?.savedAt) || (Array.isArray(workspace?.sessions) && workspace.sessions.length > 0);
}

function safeReadLegacyJson(storageKey, fallback) {
    try {
        const raw = window.localStorage.getItem(storageKey);
        return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
        console.error(`Failed to parse legacy workspace key: ${storageKey}`, error);
        return fallback;
    }
}

function readLegacyWorkspace() {
    if (typeof window === "undefined" || !window.localStorage) {
        return DEFAULT_WORKSPACE;
    }

    const setup = safeReadLegacyJson(LEGACY_SESSION_SETUP_KEY, null);
    const sessions = safeReadLegacyJson(LEGACY_PRACTICE_SESSIONS_KEY, []);

    return normalizeWorkspaceState({ setup, sessions });
}

function buildInitialWorkspaceState() {
    if (typeof window === "undefined") {
        return DEFAULT_WORKSPACE;
    }

    const bootstrapped = normalizeWorkspaceState(window.__INTERVIEW_WORKSPACE__);

    if (hasMeaningfulWorkspaceData(bootstrapped)) {
        return bootstrapped;
    }

    const legacy = readLegacyWorkspace();
    return hasMeaningfulWorkspaceData(legacy) ? legacy : bootstrapped;
}

function getWorkspaceRoutes() {
    return window.__INTERVIEW_WORKSPACE_ROUTES__ || {};
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
}

function updateWindowWorkspace(workspace) {
    window.__INTERVIEW_WORKSPACE__ = workspace;
}

function clearLegacyWorkspaceKeys() {
    if (typeof window === "undefined" || !window.localStorage) {
        return;
    }

    window.localStorage.removeItem(LEGACY_SESSION_SETUP_KEY);
    window.localStorage.removeItem(LEGACY_PRACTICE_SESSIONS_KEY);
}

export function getWorkspaceState() {
    return normalizeWorkspaceState(workspaceState);
}

export function setWorkspaceState(workspace) {
    workspaceState = normalizeWorkspaceState(workspace);
    updateWindowWorkspace(workspaceState);
    return getWorkspaceState();
}

export async function requestWorkspace(routeKey, options = {}) {
    const routes = getWorkspaceRoutes();
    const url = routes[routeKey];

    if (!url) {
        throw new Error(`Workspace route "${routeKey}" is not configured.`);
    }

    const response = await fetch(url, {
        method: options.method || "GET",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
            ...(options.headers || {})
        },
        credentials: "same-origin",
        body: options.body ? JSON.stringify(options.body) : undefined
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = payload?.message
            || Object.values(payload?.errors || {}).flat()[0]
            || "The workspace request could not be completed.";
        throw new Error(message);
    }

    if (payload.workspace) {
        setWorkspaceState(payload.workspace);
    }

    return payload;
}

export async function requestWorkspaceBlob(routeKey, options = {}) {
    const routes = getWorkspaceRoutes();
    const url = routes[routeKey];

    if (!url) {
        throw new Error(`Workspace route "${routeKey}" is not configured.`);
    }

    const headers = {
        Accept: options.accept || "*/*",
        "X-CSRF-TOKEN": getCsrfToken(),
        ...(options.headers || {})
    };

    let body = options.body;

    if (body && !(body instanceof FormData) && typeof body !== "string" && !(body instanceof Blob)) {
        headers["Content-Type"] = "application/json";
        body = JSON.stringify(body);
    }

    const response = await fetch(url, {
        method: options.method || "GET",
        headers,
        credentials: "same-origin",
        body,
        signal: options.signal
    });

    if (!response.ok) {
        const contentType = response.headers.get("Content-Type") || "";
        let payload = {};

        if (contentType.includes("application/json")) {
            payload = await response.json().catch(() => ({}));
        } else {
            payload = {
                message: await response.text().catch(() => "")
            };
        }

        const message = payload?.message
            || Object.values(payload?.errors || {}).flat()[0]
            || "The workspace request could not be completed.";
        throw new Error(message);
    }

    return {
        blob: await response.blob(),
        contentType: response.headers.get("Content-Type") || ""
    };
}

async function migrateLegacyWorkspaceToDatabase() {
    const routes = getWorkspaceRoutes();
    const serverWorkspace = normalizeWorkspaceState(window.__INTERVIEW_WORKSPACE__);
    const legacyWorkspace = readLegacyWorkspace();

    if (!routes.updateSetup || !routes.storeSession) {
        return getWorkspaceState();
    }

    if (hasMeaningfulWorkspaceData(serverWorkspace) || !hasMeaningfulWorkspaceData(legacyWorkspace)) {
        setWorkspaceState(serverWorkspace);
        return getWorkspaceState();
    }

    if (legacyWorkspace.setup?.savedAt) {
        await requestWorkspace("updateSetup", {
            method: "PUT",
            body: legacyWorkspace.setup
        });
    }

    for (const session of legacyWorkspace.sessions.slice().reverse()) {
        await requestWorkspace("storeSession", {
            method: "POST",
            body: {
                ...session,
                notify: false
            }
        });
    }

    clearLegacyWorkspaceKeys();
    return getWorkspaceState();
}

export async function initializeWorkspaceMigration() {
    if (migrationPromise) {
        return migrationPromise;
    }

    migrationPromise = migrateLegacyWorkspaceToDatabase().catch((error) => {
        console.error("Workspace migration failed", error);
        return getWorkspaceState();
    });

    return migrationPromise;
}
