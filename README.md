# InterviewPilot

InterviewPilot is an AI-based interview practice system built with Laravel 12, Tailwind CSS v4, Alpine.js, and Vite.

## Features

- Category-based mock interview practice
- Multi-provider interview chatbot with Gemini, Groq, OpenRouter, Hugging Face, Cohere, and local fallback support
- Session setup defaults for question count, coach focus, and pacing
- Progress tracking and category insights
- Responsive dashboard UI with dark mode support
- Voice-ready practice workflow

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
