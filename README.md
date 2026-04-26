# InterviewPilot

InterviewPilot is a modern, AI-powered interview practice platform built with **Laravel 12**, **Tailwind CSS v4**, **Alpine.js**, and **Vite**. It provides users with a comprehensive simulation environment to practice and refine their interviewing skills across various disciplines, leveraging artificial intelligence for dynamic question generation, real-time voice interaction, and detailed performance feedback.

## Key Features

- **Dynamic Interview Simulation:** Practice across multiple categories, including Job Interviews, Scholarship Applications, University Admissions, and Information Technology roles.
- **AI Interviewer Integration:** Features a real-time AI visualizer with spoken-question playback and intelligent, context-aware question generation tailored to your chosen category.
- **Multi-Modal Response System:** Supports text, voice, and hybrid response modes, utilizing advanced browser-based speech-to-text capabilities.
- **Comprehensive Evaluation & Feedback:** Receive detailed assessments on clarity, relevance, grammar, and professionalism. The system utilizes a weighted 1-to-5 rubric for structured, actionable feedback.
- **Non-Verbal Coaching:** Offers simulated coaching insights on eye contact orientation, posture, head movement, and facial composure.
- **Learning & Analytics Dashboard:** Access answer blueprints, delivery rehearsal exercises, saved session histories, progress analytics, and downloadable reports (JSON/CSV) to track improvement over time.
- **Administrative Control Panel:** A fully featured admin dashboard for managing users, monitoring API routing, content planning, and overseeing practice records.
- **Modern User Experience:** Features a premium, fully responsive design with smooth animations, dynamic navigation, and an intuitive UI. Secure authentication via Google OAuth and email.

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Tailwind CSS v4, Alpine.js, Vite
- **Database:** SQLite / MySQL / PostgreSQL
- **AI Integrations:** Supports multiple LLM providers (Gemini, Groq, OpenRouter, Claude, Cohere, etc.)

## Quick Start

Get up and running with the development environment:

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Build frontend assets
npm run build

# 4. Set up environment variables
cp .env.example .env
# For Windows: copy .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations
php artisan migrate

# 7. Start the development server
npm run dev
# In another terminal tab, run: php artisan serve
```

## Environment Configuration

Configure your AI providers by adding your API keys to the `.env` file:

```env
DB_DATABASE=interviewpilot_db

# AI Chatbot Configuration
INTERVIEW_CHATBOT_DEFAULT_PROVIDER=auto
INTERVIEW_CHATBOT_PROVIDER_PRIORITY=gemini,groq,openrouter,claude,wisdomgate,cohere

# Provider API Keys
GEMINI_API_KEY=your_key_here
GROQ_API_KEY=your_key_here
OPENROUTER_API_KEY=your_key_here
ANTHROPIC_API_KEY=your_key_here
CLAUDE_API_KEY=your_key_here
WISDOMGATE_API_KEY=your_key_here
COHERE_API_KEY=your_key_here
```

## Mobile LAN Access (Testing)

InterviewPilot is fully responsive and optimized for mobile devices (including camera and microphone support). To test on your mobile device within the same Wi-Fi network:

1. **Find your local IP address:**
   - Windows: Run `ipconfig` (look for "IPv4 Address").
   - Mac/Linux: Run `ifconfig` or `ip addr`.
2. **Update your `.env`:**
   ```env
   APP_URL=http://YOUR_LAN_IP
   ```
3. **Serve the application:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. **Access on mobile:** Open `http://YOUR_LAN_IP:8000` in Chrome or Edge.

> **Note:** Use HTTP for LAN access to prevent mobile browsers from blocking camera/microphone access due to self-signed HTTPS certificates.

## Project Structure

```text
interviewpilot/
├── app/          # Laravel core application logic
├── bootstrap/    # Framework bootstrap scripts
├── config/       # Application configuration files
├── database/     # Migrations, factories, and seeders
├── public/       # Publicly accessible assets (compiled CSS/JS, images)
├── resources/    # Blade views, raw CSS, and Alpine.js components
├── routes/       # Web and API routing definitions
├── storage/      # Compiled views, logs, and uploaded files
└── tests/        # Automated test suites
```

## License

This project is licensed under the terms described in the [LICENSE](./LICENSE) file.
