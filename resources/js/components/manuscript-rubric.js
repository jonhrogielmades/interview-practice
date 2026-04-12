const VERBAL_WEIGHTS = {
    clarity: 0.25,
    relevance: 0.35,
    grammar: 0.20,
    professionalism: 0.20
};

const NON_VERBAL_WEIGHTS = {
    eyeContact: 0.30,
    posture: 0.25,
    headMovement: 0.20,
    facialComposure: 0.25
};

const OVERALL_WEIGHTS = {
    verbal: 0.70,
    nonVerbal: 0.30
};

export function clampInternalScore(value, fallback = 0) {
    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
        return fallback;
    }

    return Math.max(0, Math.min(10, numeric));
}

export function toRubricScore(value) {
    const normalized = Number((clampInternalScore(value) / 2).toFixed(2));

    if (normalized === 0) {
        return 0;
    }

    return Number(Math.max(1, Math.min(5, normalized)).toFixed(1));
}

export function formatRubricScore(value, fallback = "No data") {
    const numeric = Number(value);

    if (!Number.isFinite(numeric) || numeric <= 0) {
        return fallback;
    }

    return `${numeric.toFixed(2)} / 5`;
}

export function getReadinessLabel(value) {
    const numeric = Number(value);

    if (!Number.isFinite(numeric) || numeric <= 0) {
        return "No data yet";
    }

    if (numeric >= 4) return "Highly Acceptable";
    if (numeric >= 3) return "Acceptable";
    if (numeric >= 2) return "Needs Improvement";
    return "Poor";
}

function weightedAverage(values, weights) {
    return Number(Object.entries(weights).reduce((total, [key, weight]) => {
        return total + ((Number(values[key]) || 0) * weight);
    }, 0).toFixed(2));
}

function findAlgorithmScore(process, names) {
    const algorithms = Array.isArray(process?.algorithms) ? process.algorithms : [];
    const matched = algorithms.find((algorithm) => names.includes(String(algorithm?.name || "")));

    return matched ? clampInternalScore(matched.score) : 0;
}

export function buildVisualCriteria(visualSnapshot = {}, processEvaluations = {}) {
    const bodyLanguage = processEvaluations?.bodyLanguage || {};
    const facialExpressions = processEvaluations?.facialExpressions || {};

    const eyeContact = clampInternalScore(
        visualSnapshot?.eyeContactScore
        ?? findAlgorithmScore(bodyLanguage, ["Eye Contact Orientation", "Eye Engagement", "Frame Centering"])
    );
    const posture = clampInternalScore(
        visualSnapshot?.postureScore
        ?? findAlgorithmScore(bodyLanguage, ["Posture Stability", "Head Balance", "Presence Framing"])
    );
    const headMovement = clampInternalScore(
        visualSnapshot?.headMovementScore
        ?? findAlgorithmScore(bodyLanguage, ["Head Movement Control", "Movement Stability"])
    );
    const facialComposure = clampInternalScore(
        visualSnapshot?.facialComposureScore
        ?? findAlgorithmScore(facialExpressions, ["Facial Composure", "Jaw Relaxation", "Brow Calmness"])
    );

    return {
        eyeContact,
        posture,
        headMovement,
        facialComposure
    };
}

export function buildManuscriptRubric(criteriaScores = {}, visualSnapshot = {}, processEvaluations = {}) {
    const verbalCriteria = {
        clarity: toRubricScore(criteriaScores?.clarity),
        relevance: toRubricScore(criteriaScores?.relevance),
        grammar: toRubricScore(criteriaScores?.grammar),
        professionalism: toRubricScore(criteriaScores?.professionalism)
    };
    const internalNonVerbalCriteria = buildVisualCriteria(visualSnapshot, processEvaluations);
    const nonVerbalCriteria = {
        eyeContact: toRubricScore(internalNonVerbalCriteria.eyeContact),
        posture: toRubricScore(internalNonVerbalCriteria.posture),
        headMovement: toRubricScore(internalNonVerbalCriteria.headMovement),
        facialComposure: toRubricScore(internalNonVerbalCriteria.facialComposure)
    };
    const hasNonVerbal = Object.values(internalNonVerbalCriteria).some((value) => value > 0);

    const verbal = weightedAverage(verbalCriteria, VERBAL_WEIGHTS);
    const nonVerbal = hasNonVerbal
        ? weightedAverage(nonVerbalCriteria, NON_VERBAL_WEIGHTS)
        : 0;
    const overall = Number((
        hasNonVerbal
            ? ((verbal * OVERALL_WEIGHTS.verbal) + (nonVerbal * OVERALL_WEIGHTS.nonVerbal))
            : verbal
    ).toFixed(2));

    return {
        criteria: {
            ...verbalCriteria,
            ...nonVerbalCriteria
        },
        internalNonVerbalCriteria,
        verbal,
        nonVerbal,
        overall,
        hasNonVerbal,
        readinessLabel: getReadinessLabel(overall)
    };
}
