# InterviewPilot

InterviewPilot is an AI-based interview practice system built with Laravel 12, Tailwind CSS v4, Alpine.js, and Vite.

## Features

- Category-based mock interview practice
- Multi-provider interview chatbot with Gemini, Groq, OpenRouter, Hugging Face, Cohere, and local fallback support
- Session setup defaults for question count, coach focus, and pacing
- Progress tracking and category insights
- Responsive dashboard UI with dark mode support
- Voice-ready practice workflow
- Text, voice, and hybrid response modes
- Job interview practice track for hiring scenarios
- Scholarship interview practice track for academic and grant applications
- College admission interview practice track for university applicants
- IT / programming interview track for technical candidates
- AI-generated question sets per selected category
- Field builder flow for tailoring the target role, course, or specialization
- AI answer review summaries with strengths, improvements, and next steps
- Criteria-based scoring for clarity, relevance, grammar, and professionalism
- Printable feedback summaries from the practice workspace
- Saved session setup persistence for returning users
- Saved session history with question-by-question answer records
- Session cleanup tools for deleting one session or clearing all saved history
- Quick prompts that adapt to the active interview category
- Live provider health checks for configured AI services
- Auto routing across provider priority with graceful fallback
- Local PH coach fallback when external AI keys are unavailable
- AI interviewer voice playback for reading questions aloud
- Camera preview with face visibility status during mock interviews
- Google OAuth sign-in support
- Email sign-in and sign-up flows from the homepage
- Profile management for personal details, address, role, location, and avatar
- Weekly dashboard signals and latest evaluation snapshot
- Dedicated feedback center, session review, and category insights pages
- Mobile LAN access workflow for testing on phones over the same network

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- npm
- SQLite, MySQL, or PostgreSQL

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

For Windows:

```bash
copy .env.example .env
```

## Environment Example

```env
DB_DATABASE=interviewpilot_db
INTERVIEW_CHATBOT_DEFAULT_PROVIDER=auto
INTERVIEW_CHATBOT_PROVIDER_PRIORITY=gemini,groq,openrouter,huggingface,cohere
GEMINI_API_KEY=
GROQ_API_KEY=
OPENROUTER_API_KEY=
HUGGINGFACE_API_KEY=
COHERE_API_KEY=
```

## Build

```bash
npm run build
```

## Mobile LAN Access

To open the app from a phone on the same Wi-Fi network, set `APP_URL` and
`VITE_DEV_SERVER_HOST` in `.env` to your computer's LAN IP, then start:

```bash
composer run dev:lan
```

Open `http://YOUR-LAN-IP:8000` on the phone.

Camera, microphone, and speech APIs on mobile browsers usually require HTTPS
or `localhost`, so those features may stay blocked on plain LAN HTTP.

## Project Structure

```text
interviewpilot/
|- app/
|- public/
|- resources/
|- routes/
|- storage/
`- tests/
```

## License

See [LICENSE](./LICENSE).
