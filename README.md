# InterviewPilot

InterviewPilot is an AI-based interview practice system built with Laravel 12, Tailwind CSS v4, Alpine.js, and Vite. The current prototype is aligned to the capstone manuscript for an "AI-Based Interview Practice System" focused on simulation, automated feedback, learning support, reporting, and administrative monitoring.

## Features

- Category-based interview simulation for job, scholarship, admission, and Information Technology scenarios
- AI visualizer support with spoken-question playback and category-backed question generation
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
npm run build
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

To open the app from a phone on the same Wi-Fi network:

1. Find your computer's LAN IP address:
   - Windows: Run `ipconfig` in Command Prompt, look for "IPv4 Address" under your active network adapter
   - Mac/Linux: Run `ifconfig` or `ip addr`, look for your local network IP

2. Set `APP_URL` in `.env` to your LAN IP:
   ```env
   APP_URL=http://192.168.1.100
   ```

3. Start the development server:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. On your phone, open `http://YOUR-LAN-IP:8000`

**Important Notes:**
- Use HTTP (not HTTPS) for LAN access - mobile browsers may block camera/microphone on self-signed HTTPS
- Grant camera and microphone permissions when prompted
- Use Chrome or Edge on mobile for best speech recognition support
- Camera and voice features work on mobile but may have limitations compared to desktop

## Mobile Responsiveness

The app is fully responsive and works on phones and tablets:
- Touch-friendly buttons (minimum 44px touch targets)
- Responsive layouts that adapt to screen size
- Mobile-optimized camera preview and controls
- Voice input and speech synthesis support
- Optimized for both portrait and landscape orientations

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

## Project Changes History

This project has evolved from a static template into a dynamic, Laravel-based AI Interview Practice application. The key development milestones and changes include:

- **Framework Migration:** Converted the initial static HTML project into a robust Laravel framework application, integrating Blade templating, Alpine.js, and Vite.
- **Landing Page Implementation:** Ported the interactive "InterviewPilot" landing page into the Laravel ecosystem, enabling dynamic navigation, animated transitions, and modals using Tailwind CSS.
- **UI/UX & Responsiveness Overhaul:** Completely redesigned the system for a premium, user-friendly, and fully responsive experience on both mobile and desktop devices. Added smooth animations and modern aesthetics.
- **AI Interview Core Features:** Rebranded the platform to focus on interview preparation. Developed an AI-guided practice flow with category selection (job, scholarship, admission, IT), real-time chatbot interaction, and interviewer voice playback.
- **Advanced Practice Modes:** Introduced text, voice, and hybrid response capabilities utilizing browser-based speech-to-text functionality.
- **Evaluation & Feedback:** Integrated a comprehensive evaluation system aligned with a capstone manuscript's weighted 1-to-5 rubric, scoring clarity, relevance, grammar, and non-verbal cues.
- **Authentication Improvements:** Enhanced the user journey with animated transitions between Sign In and Sign Up flows.
- **"A Developers" Section:** Rebranded the former "Testimonials" area to "A Developers", restricting display to three core cards to better align with the product's focus.
- **Admin & Learning Workflows:** Implemented an admin dashboard for user and API management, along with learning blueprints, session history, and report exports (JSON/CSV) for users.

## License

See [LICENSE](./LICENSE).
