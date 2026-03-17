# Project: Realtime Asterisk — Call Monitoring Dashboard

## Overview
Real-time call monitoring and billing dashboard. Simulated VoIP calls
broadcast via WebSocket to a live dashboard showing active calls,
balance updates, and CDR history.

## Tech Stack
- **PHP:** ^8.2, Laravel 12, Sanctum 4
- **Database:** PostgreSQL 16 (with CHECK constraints for data integrity)
- **Cache/Queue/Session:** Redis 7
- **WebSocket:** Soketi (Pusher Protocol v7, Docker: quay.io/soketi/soketi)
- **Frontend:** Blade + Alpine.js 3 + Laravel Echo + Tailwind CSS 4
- **Build:** Vite 7
- **Testing:** PHPUnit 11 (SQLite in-memory)
- **Infrastructure:** Docker Compose (5 services)
- **Code Style:** Laravel Pint (PSR-12), standard Laravel conventions

## Commands
```bash
docker-compose up -d                    # Start infrastructure
docker-compose exec app php artisan migrate --seed  # Setup DB
docker-compose exec app php artisan test            # Run tests
docker-compose exec app php artisan call:simulate   # Demo calls
npm run dev                             # Vite dev server
```

## Architecture
- Controllers in `app/Http/Controllers/` (Api/ for REST, Auth/ for web login)
- Services in `app/Services/` (BillingService, CallSimulationService)
- Events in `app/Events/` (ShouldBroadcast: CallStarted, CallUpdated, CallEnded, BalanceUpdated)
- API Resources in `app/Http/Resources/`
- Form Requests for validation
- Follow standard Laravel code style (no declare(strict_types=1))
- SOLID, DRY, KISS principles
- Atomic transactions with lockForUpdate for billing
- bcmath for money arithmetic

## Database
- **PostgreSQL** on pgsql:5432, database `realtime_asterisk`
- **Tables:** users, accounts, tariffs, cdrs, personal_access_tokens, sessions, cache, jobs
- **Testing:** SQLite in-memory (phpunit.xml)
- CHECK constraints on balance, duration, cost, price fields (skipped on SQLite)

## Broadcasting
- Driver: pusher (pointed at Soketi)
- Public channel: `calls` (call.started, call.updated, call.ended)
- Private channel: `account.{id}` (balance.updated)
- Auth: routes/channels.php

## API (all under auth:sanctum except POST /api/login)
- POST /api/login, GET /api/me, POST /api/logout
- GET /api/account, GET /api/account/balance
- GET /api/calls/active
- GET /api/cdrs, GET /api/cdrs/{id}
