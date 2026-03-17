# Realtime Asterisk — Call Monitoring Dashboard

Real-time call monitoring and billing dashboard built with Laravel 12, WebSockets (Soketi), and Alpine.js.

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12, Sanctum
- **Database:** PostgreSQL 16
- **Cache/Queue/Session:** Redis 7
- **WebSocket:** Soketi (Pusher-compatible)
- **Frontend:** Blade + Alpine.js + Laravel Echo + Tailwind CSS 4
- **Build:** Vite 7
- **Testing:** PHPUnit 11 (39 tests, 83 assertions)
- **Infrastructure:** Docker Compose

## Quick Start

### 1. Clone and setup

```bash
git clone https://github.com/FedorenkoAlex322/call_monitoring.git
cd call_monitoring
cp .env.example .env
```

### 2. Start Docker infrastructure

```bash
docker-compose up -d
```

Services: PostgreSQL (5432), Redis (6379), Soketi (6001), Nginx (8000), PHP-FPM.

### 3. Install dependencies and migrate

```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
npm install && npm run build
```

### 4. Access

| Service | URL |
|---------|-----|
| Dashboard | http://localhost:8000 |
| API | http://localhost:8000/api |
| WebSocket | ws://localhost:6001 |
| Soketi Metrics | http://localhost:9601 |

**Login:** `admin@asterisk.local` / `password`

### 5. Simulate calls

```bash
docker-compose exec app php artisan call:simulate --calls=3 --duration=15 --interval=2
```

Open the dashboard in a browser to see calls appearing in real-time.

### 6. Run tests

```bash
docker-compose exec app php artisan test
```

## API Endpoints

All endpoints (except login) require `Authorization: Bearer {token}` header.

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Get API token (email + password) |
| GET | `/api/me` | Current user info |
| POST | `/api/logout` | Revoke token |

### Account & Billing

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/account` | Account details with tariff |
| GET | `/api/account/balance` | Current balance |

### Calls & CDR

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/calls/active` | Active calls list |
| GET | `/api/cdrs` | CDR history (paginated, 20/page) |
| GET | `/api/cdrs/{id}` | Single CDR detail |

## WebSocket Channels

| Channel | Type | Events |
|---------|------|--------|
| `calls` | Public | `call.started`, `call.updated`, `call.ended` |
| `private-account.{id}` | Private | `balance.updated` |

## Architecture

```
┌─────────────┐     ┌──────────┐     ┌─────────┐
│  Dashboard   │◄────│  Soketi  │◄────│  Redis  │
│  (Echo+Alpine)│     │ (WS:6001)│     │ (PubSub)│
└─────────────┘     └──────────┘     └────┬────┘
                                          │
┌─────────────┐     ┌──────────┐     ┌────┴────┐
│   API Client │────►│  Nginx   │────►│ Laravel │
│  (Sanctum)   │     │ (:8000)  │     │ (PHP-FPM)│
└─────────────┘     └──────────┘     └────┬────┘
                                          │
                                     ┌────┴────┐
                                     │PostgreSQL│
                                     │ (:5432)  │
                                     └─────────┘
```

### Key Design Decisions

- **Atomic billing:** `endCall()` wraps CDR update + balance charge in a single DB transaction with `lockForUpdate`
- **Money precision:** `bcmath` for all cost calculations, `decimal` columns in DB
- **CHECK constraints:** PostgreSQL enforces non-negative balance, duration, cost at DB level
- **Event broadcasting:** `ShouldBroadcast` events dispatched from services, delivered via Soketi
- **Call simulation:** Artisan command generates realistic call events for demo/testing

## Project Structure

```
app/
├── Console/Commands/SimulateCallsCommand.php   # Call simulator
├── Events/                                      # Broadcast events
│   ├── CallStarted.php
│   ├── CallUpdated.php
│   ├── CallEnded.php
│   └── BalanceUpdated.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/                                 # REST API
│   │   │   ├── AuthController.php
│   │   │   ├── AccountController.php
│   │   │   ├── CallController.php
│   │   │   └── CdrController.php
│   │   ├── Auth/LoginController.php             # Web auth
│   │   └── DashboardController.php              # Dashboard
│   ├── Requests/LoginRequest.php
│   └── Resources/                               # API Resources
├── Models/
│   ├── Account.php
│   ├── Cdr.php
│   ├── Tariff.php
│   └── User.php
└── Services/
    ├── BillingService.php                       # Cost calculation
    └── CallSimulationService.php                # Call lifecycle
```

## License

MIT
