function buildCriterionFeedback(key, score = 0) {
    const normalizedScore = Number(score) || 0;

    if (key === "clarity") {
        if (normalizedScore >= 8) return "Your ideas are organized well, so the answer is easy to follow from start to finish.";
        if (normalizedScore >= 6) return "Your main point is visible, but the answer would land better with a clearer structure and transitions.";
        return "Your answer needs a clearer flow. Lead with the main point, add one example, then close with the result.";
    }

    if (key === "relevance") {
        if (normalizedScore >= 8) return "You stay close to the question and connect your answer to what the interviewer is actually asking.";
        if (normalizedScore >= 6) return "Parts of the answer are relevant, but you can tie your example back to the exact question more directly.";
        return "Your answer drifts away from the prompt. Mirror the question language and explain why your example fits.";
    }

    if (key === "grammar") {
        if (normalizedScore >= 8) return "Your wording is mostly polished and professional, which helps your answer sound more interview-ready.";
        if (normalizedScore >= 6) return "The answer is understandable, but tightening sentence flow and endings would make it sound cleaner.";
        return "Grammar and sentence flow are weakening the message. Use shorter complete sentences and check how each thought ends.";
    }

    if (normalizedScore >= 8) return "Your tone sounds confident, respectful, and appropriate for an interview setting.";
    if (normalizedScore >= 6) return "Your tone is generally professional, but stronger wording would make you sound more confident.";
    return "Your tone feels too casual or uncertain. Use more direct, professional language and sound deliberate in your answer.";
}

export function buildFeedbackSummary(answer = "", scoreData = {}) {
    const strengths = [];
    const improvements = [];
    const criteria = {
        clarity: buildCriterionFeedback("clarity", scoreData.clarity),
        relevance: buildCriterionFeedback("relevance", scoreData.relevance),
        grammar: buildCriterionFeedback("grammar", scoreData.grammar),
        professionalism: buildCriterionFeedback("professionalism", scoreData.professionalism)
    };
    const average = Number(scoreData.average) || 0;
    const wordCount = String(answer).trim().split(/\s+/).filter(Boolean).length;

    if ((Number(scoreData.clarity) || 0) >= 8) strengths.push("Your answer is clear and easy to follow.");
    else improvements.push("Organize your answer into main point, example, and closing.");

    if ((Number(scoreData.relevance) || 0) >= 8) strengths.push("Your response stays relevant to the question.");
    else improvements.push("Use more job-related keywords and details connected to the question.");

    if ((Number(scoreData.grammar) || 0) >= 8) strengths.push("Your wording is mostly polished and professional.");
    else improvements.push("Check sentence endings and use complete thoughts.");

    if ((Number(scoreData.professionalism) || 0) >= 8) strengths.push("Your tone sounds professional and confident.");
    else improvements.push("Use more formal and confident wording.");

    if (wordCount < 25) {
        improvements.push("Add a specific example to make your answer stronger.");
    }

    return {
        strengths,
        improvements,
        overall: average >= 8
            ? "This is a strong answer overall. Keep the same structure and make sure each point stays specific."
            : average >= 6
                ? "This answer has a solid base, but it needs tighter structure and more precise support to feel more convincing."
                : "This answer needs more structure and stronger support before it will feel interview-ready.",
        nextStep: wordCount < 25
            ? "Expand your answer with one concrete example and a short result."
            : "Refine the example you gave so the impact and relevance are more obvious.",
        criteria
    };
}

export function normalizeFeedbackSummary(answer = "", scoreData = {}, feedbackSummary = null) {
    const derived = buildFeedbackSummary(answer, scoreData);
    const strengths = Array.isArray(feedbackSummary?.strengths) && feedbackSummary.strengths.length
        ? feedbackSummary.strengths
        : derived.strengths;
    const improvements = Array.isArray(feedbackSummary?.improvements) && feedbackSummary.improvements.length
        ? feedbackSummary.improvements
        : derived.improvements;

    return {
        strengths: strengths.length ? strengths : ["Your answer has a good starting point."],
        improvements: improvements.length ? improvements : ["Keep practicing to improve consistency."],
        overall: typeof feedbackSummary?.overall === "string" && feedbackSummary.overall.trim()
            ? feedbackSummary.overall.trim()
            : derived.overall,
        nextStep: typeof feedbackSummary?.nextStep === "string" && feedbackSummary.nextStep.trim()
            ? feedbackSummary.nextStep.trim()
            : derived.nextStep,
        criteria: {
            clarity: typeof feedbackSummary?.criteria?.clarity === "string" && feedbackSummary.criteria.clarity.trim()
                ? feedbackSummary.criteria.clarity.trim()
                : derived.criteria.clarity,
            relevance: typeof feedbackSummary?.criteria?.relevance === "string" && feedbackSummary.criteria.relevance.trim()
                ? feedbackSummary.criteria.relevance.trim()
                : derived.criteria.relevance,
            grammar: typeof feedbackSummary?.criteria?.grammar === "string" && feedbackSummary.criteria.grammar.trim()
                ? feedbackSummary.criteria.grammar.trim()
                : derived.criteria.grammar,
            professionalism: typeof feedbackSummary?.criteria?.professionalism === "string" && feedbackSummary.criteria.professionalism.trim()
                ? feedbackSummary.criteria.professionalism.trim()
                : derived.criteria.professionalism
        },
        provider: typeof feedbackSummary?.provider === "string" && feedbackSummary.provider.trim()
            ? feedbackSummary.provider.trim()
            : null
    };
}
