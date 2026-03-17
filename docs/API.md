# API Documentation

Base URL: `http://localhost:8000/api`

## Authentication

All endpoints except `POST /login` require the header:
```
Authorization: Bearer {token}
```

### POST /login

Get an API token.

**Request:**
```json
{
    "email": "admin@asterisk.local",
    "password": "password"
}
```

**Response 200:**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@asterisk.local"
    }
}
```

**Response 401:** `{"message": "Invalid credentials"}`

### GET /me

Returns the authenticated user.

**Response 200:**
```json
{
    "id": 1,
    "name": "Admin",
    "email": "admin@asterisk.local"
}
```

### POST /logout

Revokes the current token.

**Response 200:** `{"message": "Logged out"}`

---

## Account

### GET /account

Returns account details with tariff information.

**Response 200:**
```json
{
    "data": {
        "id": 1,
        "number": "1001",
        "name": "Admin Line",
        "balance": 485.50,
        "status": "active",
        "tariff": {
            "id": 2,
            "name": "Business",
            "price_per_minute": 0.8,
            "connection_fee": 1.0,
            "free_seconds": 10
        }
    }
}
```

### GET /account/balance

Lightweight balance response.

**Response 200:**
```json
{
    "account_id": 1,
    "balance": 485.50,
    "number": "1001"
}
```

---

## Calls

### GET /calls/active

Returns active (in-progress) calls for the authenticated user's account.

**Response 200:**
```json
{
    "data": [
        {
            "id": 42,
            "uniqueid": "sim-01JEXAMPLE",
            "src": "1001",
            "dst": "79001234567",
            "started_at": "2026-03-17T14:30:00.000000Z",
            "duration": 45,
            "status": "active"
        }
    ]
}
```

---

## CDR (Call Detail Records)

### GET /cdrs

Paginated list of completed calls (20 per page).

**Query params:** `page` (int)

**Response 200:**
```json
{
    "data": [
        {
            "id": 41,
            "uniqueid": "sim-01JEXAMPLE",
            "src": "1001",
            "dst": "79009876543",
            "started_at": "2026-03-17T14:25:00.000000Z",
            "answered_at": "2026-03-17T14:25:02.000000Z",
            "ended_at": "2026-03-17T14:26:12.000000Z",
            "duration": 70,
            "billsec": 60,
            "cost": 1.80,
            "disposition": "ANSWERED"
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": null },
    "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
}
```

### GET /cdrs/{id}

Single CDR detail. Returns **403** if the CDR belongs to a different account.

---

## WebSocket Events

Connect to `ws://localhost:6001` using Laravel Echo with Pusher driver.

**Credentials:**
- App Key: `app-key`
- Cluster: `mt1`

### Public Channel: `calls`

| Event | Payload |
|-------|---------|
| `call.started` | `{id, uniqueid, account_id, src, dst, started_at, status}` |
| `call.updated` | `{id, uniqueid, duration, status}` |
| `call.ended` | `{id, uniqueid, account_id, src, dst, duration, billsec, cost, disposition, ended_at}` |

### Private Channel: `private-account.{id}`

| Event | Payload |
|-------|---------|
| `balance.updated` | `{account_id, balance}` |

**Echo subscription example:**
```javascript
Echo.channel('calls')
    .listen('.call.started', (e) => console.log('Call started:', e))
    .listen('.call.ended', (e) => console.log('Call ended:', e));

Echo.private('account.1')
    .listen('.balance.updated', (e) => console.log('Balance:', e.balance));
```
