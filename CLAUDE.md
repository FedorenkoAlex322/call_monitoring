# Project: Realtime Asterisk

## Overview
Laravel 12 application for real-time Asterisk PBX management via Asterisk Realtime Architecture.
PostgreSQL serves as both the application database and Asterisk Realtime backend.

## Tech Stack
- **PHP:** ^8.2
- **Laravel:** ^12.0
- **Database:** PostgreSQL (Asterisk Realtime + application data)
- **Frontend:** Vite 7 + Tailwind CSS 4 + Blade
- **Testing:** PHPUnit ^11.5 (Feature + Unit), SQLite in-memory for tests
- **Queue:** Database driver (planned migration to Redis/RabbitMQ)
- **Code Style:** Laravel Pint (PSR-12 based)

## Development Commands
```bash
npm run setup    # Full project setup (composer, migrate, build)
npm run dev      # Start dev server + queue worker + Vite HMR
composer test    # Run PHPUnit tests
```

## Architecture Conventions
- Follow Laravel conventions: controllers in `app/Http/Controllers/`, models in `app/Models/`
- Use Form Requests for validation
- Use API Resources for JSON responses
- Services in `app/Services/` for business logic
- Repositories in `app/Repositories/` for complex queries
- Actions in `app/Actions/` for single-purpose operations
- Events/Listeners for async operations and Asterisk event handling
- Follow standard Laravel code style (no declare(strict_types=1))
- SOLID, DRY, KISS principles

## Database
- **Connection:** PostgreSQL on 127.0.0.1:5432, database `realtime_asterisk`
- **Testing:** SQLite in-memory (phpunit.xml)
- Asterisk Realtime tables follow Asterisk naming conventions (e.g., `ps_endpoints`, `ps_aors`, `ps_auths`)
- Application tables use Laravel conventions (snake_case, plural)

## Key Patterns
- Asterisk Realtime: PostgreSQL tables directly consumed by Asterisk via `res_config_pgsql`
- Application manages these tables through Laravel Eloquent models
- Real-time updates via WebSocket (planned: Laravel Reverb)
- AMI/ARI integration for live call control (planned)