# InterviewPilot

InterviewPilot is an AI-based interview practice system built with Laravel 12, Tailwind CSS v4, Alpine.js, and Vite. The current prototype is aligned to the capstone manuscript for an "AI-Based Interview Practice System" focused on simulation, automated feedback, learning support, reporting, and administrative monitoring.

## Features

- Category-based interview simulation for job, scholarship, admission, and Information Technology scenarios
- AI avatar interviewer support with spoken-question playback and category-backed question generation
- Text, voice, and hybrid response modes with browser speech-to-text support
- Verbal evaluation across clarity, relevance, grammar, and professionalism
- Selected non-verbal coaching for eye contact orientation, posture, head movement, and facial composure
- Weighted manuscript-aligned 1-to-5 capstone rubric layered on top of the existing runtime scoring
- Learning activities, answer blueprints, delivery rehearsal, and visual-presence coaching
- Saved session history, progress analytics, session review, feedback center, and category insights
- Downloadable JSON and CSV report exports for documentation and review
- Admin dashboard plus user management, API management, content planning, and monitoring records
- Notification center support for reminders and practice-related updates
- Google OAuth, email authentication, profile management, and responsive dashboard UI
- Mobile LAN access workflow for local-network testing on phones

## Manuscript Alignment

The current system is aligned to the manuscript in these ways:

- Interview categories are limited to job, scholarship, admission, and IT practice tracks.
- Practice emphasizes an AI-guided interview flow with category selection, question generation, answer capture, feedback, and review.
- Runtime 10-point signals are translated into the manuscript's weighted 1-to-5 rubric for verbal, non-verbal, and overall readiness reporting.
- Admin surfaces now cover user management, API routing visibility, question-bank and announcement planning, and monitoring records.

The prototype still uses the current Laravel + Blade + browser-capability implementation. Where the manuscript describes separate AI-processing services, this repo currently represents those behaviors through the existing integrated application and frontend logic.

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
INTERVIEW_CHATBOT_PROVIDER_PRIORITY=gemini,groq,openrouter,claude,wisdomgate,cohere
GEMINI_API_KEY=
GROQ_API_KEY=
OPENROUTER_API_KEY=
ANTHROPIC_API_KEY=
CLAUDE_API_KEY=
WISDOMGATE_API_KEY=
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
