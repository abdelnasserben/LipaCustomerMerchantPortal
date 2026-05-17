# Customer + Merchant Portal — Frontend Specification Document

**Version:** 1.0 | **Source:** Lipa backend codebase analysis | **Date:** 2026-05-14
**Status:** Single source of truth. Do not call or display anything that is not listed here.
**Branding:** All user-facing copy uses **Lipa**. "KomoPay" is the internal/backend name only — never show it to end users.

---

## Table of Contents

1. [Scope](#1-scope)
2. [HTTP Contract](#2-http-contract)
3. [Authentication](#3-authentication)
4. [Capability Maps](#4-capability-maps)
5. [Exhaustive API Mapping](#5-exhaustive-api-mapping)
6. [Request Schemas](#6-request-schemas)
7. [Response Schemas](#7-response-schemas)
8. [Enums](#8-enums)
9. [Permissions, Session & Auth](#9-permissions-session--auth)
10. [Merchant Operators](#10-merchant-operators)
11. [Recommended UI Flows](#11-recommended-ui-flows)
12. [UI States & Business Errors](#12-ui-states--business-errors)
13. [Where To Start](#13-where-to-start)
14. [Evidence Index](#14-evidence-index)

---

## 1. Scope

This document covers the APIs a **shared Customer + Merchant web/mobile portal** can interact with. Two distinct end-user actor types are addressed:

- **Customer** — `POST /api/v1/auth/customer/*` and every endpoint under `/api/v1/me/*`.
- **Merchant** — `POST /api/v1/auth/merchant/*` and every endpoint under `/api/v1/merchant/*`.

Out of scope: Backoffice, Agent portal, Terminal (Android POS), service-provider callbacks, and server-to-server APIs. These are documented separately (`BO_Frontend_Specification.md`, `Agent_Frontend_Specification.md`, `Terminal_Frontend_Specification.md`).

**Endpoint counts in scope today:**

| Surface | Auth endpoints | Portal endpoints | Total |
|---|---:|---:|---:|
| Customer | 7 | 12 | 19 |
| Merchant | 3 | 12 | 15 |

> The Customer and Merchant are **separate accounts with separate login flows and separate JWT actor types**. A single human who is both a customer and a merchant owner holds two independent sessions. The "common portal" is a shared frontend shell, not a shared session.

### 1.1 Reality check — what is NOT available

The backend is the source of truth. The following do **not** exist and must not be built against:

- No customer **cash-in / cash-out** self-service (cash operations are Agent-only).
- No customer or merchant **card issuance / card sale** (Agent-only).
- No customer **registration/self-enrollment** endpoint — customers are created by an Agent or Backoffice.
- No merchant **self-registration** — merchants are created/onboarded by Backoffice.
- No merchant **refresh** and no merchant **logout** endpoint (see [3.4](#34-merchant-auth-limitations)).
- No merchant **terminal provisioning** from the portal — merchants can only *view* terminals; provisioning is Backoffice.
- No customer-facing **service-provider/bill catalogue** endpoint — bill payment requires a `serviceId` the client must already hold.
- **Bill payment is feature-flagged OFF** by default (`billpay.enabled=false` → `POST /api/v1/me/bill-payments` returns `501 Not Implemented`).
- No **approval inspection** endpoints for customer or merchant.

---

## 2. HTTP Contract

### 2.1 Base Rules

| Rule | Value |
|---|---|
| Body format | JSON |
| Currency | KMF (integer minor-unit `long` amounts) |
| Date-time format | ISO-8601 string (`instant`) |
| Date format | ISO local date `YYYY-MM-DD` |
| Auth header | `Authorization: Bearer <accessToken>` on every protected endpoint |
| Optional tracing header | `X-Correlation-Id: <uuid>`; financial write endpoints generate one if absent and reject a malformed one |
| Required idempotency header | `Idempotency-Key: <opaque-key>` on `POST /api/v1/me/p2p`, `POST /api/v1/me/bill-payments`, `POST /api/v1/merchant/m2m` |
| Rate limit | Every `/api/v1/auth/**` request is limited to **10 requests / minute / IP** by default |

### 2.2 Success Envelopes

Non-paginated success responses use the `ApiResponse` envelope:

```json
{ "data": {}, "timestamp": "2026-05-14T12:00:00Z" }
```

Cursor-paginated list endexpoints use `PagedResponse`:

```json
{
  "data": [],
  "pagination": { "nextCursor": "opaque-string-or-null", "hasMore": false, "limit": 20 }
}
```

Cursor rules:

- `limit` defaults to `20`, backend clamps to `1..100`.
- `cursor` is opaque — pass `pagination.nextCursor` back as-is.
- `GET /api/v1/merchant/terminals` uses `PagedResponse.last(...)`: `hasMore=false`, `nextCursor=null`, `limit` equals the returned list size (not cursor-paginated).
- `GET /api/v1/me/activity` and `GET /api/v1/me/beneficiaries` and `GET /api/v1/me/cards` return a plain `ApiResponse<List<...>>` — **not** paginated.

### 2.3 Error Envelopes

Controller and validation errors return the wrapped envelope:

```json
{
  "error": {
    "code": "VALIDATION_FIELD_REQUIRED",
    "message": "Request validation failed",
    "details": ["field: message"],
    "correlationId": "uuid",
    "timestamp": "2026-05-14T12:00:00Z"
  }
}
```

Security filter exceptions are special:

- `401` / `403` emitted by Spring Security return **raw `ApiError`**, without the outer `{ "error": ... }` wrapper.
- `401` emitted by `JwtAuthenticationFilter` (invalid/expired/revoked bearer) also returns raw `ApiError`.
- `429` from `RateLimitingFilter` returns the wrapped error envelope.

### 2.4 HTTP Status Semantics for Financial Writes

`POST /api/v1/me/p2p` and `POST /api/v1/me/bill-payments` return three distinct codes:

- `201 Created` — executed; wallet & ledger mutated; `replayed=false`.
- `200 OK` — idempotent replay of a previously executed request; `replayed=true`.
- `202 Accepted` — a control fired (`PENDING_PIN` / `PENDING_CONFIRMATION`); **no transaction created, no wallet movement**. Collect the missing step and resubmit with the **same `Idempotency-Key`**.

`POST /api/v1/merchant/m2m` returns only `201` (executed) or `200` (replayed) — **no `202` control gate** is exposed on the M2M path today.

---

## 3. Authentication

Both Customer and Merchant use the shared **phone + auth-PIN** login (`PinLoginService`). Optional TOTP MFA exists for **both** Customer and Merchant — same enrollment contract, same `mfaRequired` login branch, same step-up rules. Legacy SMS OTP is dormant.

### 3.1 Customer Login

`POST /api/v1/auth/customer/login` — public.

Request `LoginRequest`:

```json
{ "phoneCountryCode": "269", "phoneNumber": "3212345", "pin": "1234" }
```

The `LoginResponse` envelope has **three mutually exclusive shapes** — exactly one branch is populated:

**A. Full session** (PIN OK, no MFA):

```json
{ "data": {
  "mfaRequired": false,
  "tokens": {
    "accessToken": "jwt",
    "accessTokenExpiresAt": "2026-05-14T12:15:00Z",
    "refreshToken": "opaque",
    "refreshTokenExpiresAt": "2026-06-13T12:00:00Z"
  }
}, "timestamp": "..." }
```

**B. MFA required** (TOTP-enrolled customer):

```json
{ "data": { "mfaRequired": true, "challengeId": "uuid", "mfaFactor": "TOTP" }, "timestamp": "..." }
```

→ call `POST /api/v1/auth/customer/login/verify-mfa` with `challengeId` + 6-digit `code`.

**C. PIN setup required** (customer has no PIN — created by Agent/Backoffice without one, or after a forced reset):

```json
{ "data": {
  "mfaRequired": false,
  "pinSetupRequired": true,
  "pinSetupToken": "jwt-purpose-PIN_SETUP",
  "pinSetupTokenExpiresAt": "2026-05-14T12:10:00Z"
}, "timestamp": "..." }
```

→ store `pinSetupToken` (single-use, ~10 min TTL) and immediately call `POST /api/v1/auth/customer/auth-pin/setup`. No session is issued on this branch; the submitted `pin` field is ignored.

### 3.2 Customer Token Lifetimes & Lockout

| Actor | Access TTL | Refresh TTL |
|---|---:|---:|
| Customer | **15 min** | 30 days |
| Merchant | 8 hours | 7 days (refresh endpoint not exposed — see 3.4) |

- The short customer access TTL means the frontend **must** refresh proactively via `POST /api/v1/auth/customer/refresh`.
- 3 failed auth-PIN checks lock the PIN for 15 minutes (`AUTH_PIN_LOCKED`).
- `PENDING_KYC`, `SUSPENDED`, `FROZEN`, `CLOSED` customers cannot obtain tokens.
- Refresh-token reuse revokes all active refresh tokens for the actor.

### 3.3 Customer MFA / TOTP / PIN Management

| Endpoint | Bearer required | Purpose |
|---|---|---|
| `POST /api/v1/auth/customer/login/verify-mfa` | public | complete a `mfaRequired` login |
| `POST /api/v1/auth/customer/auth-pin/setup` | `PIN_SETUP` token | set the initial PIN (single-use token) |
| `PUT /api/v1/auth/customer/auth-pin` | Customer JWT | rotate PIN; requires `currentPin` |
| `POST /api/v1/auth/customer/totp-setup` | Customer JWT | start TOTP enrollment — returns `secret` + `qrUri` |
| `POST /api/v1/auth/customer/totp-confirm` | Customer JWT | confirm with first 6-digit code |
| `DELETE /api/v1/auth/customer/totp-setup` | Customer JWT | revoke TOTP; requires a current code as step-up |

PIN rules: 4–8 digits, BCrypt-hashed server-side, never returned. `auth-pin/setup` rejects with `AUTH_PIN_ALREADY_SET` if a PIN exists.

**Forgotten PIN — self-service via TOTP**. A Customer or Merchant who has TOTP enrolled may reset their PIN without contacting Backoffice via `POST /api/v1/auth/{customer|merchant}/auth-pin/reset` (public; body `{ phoneCountryCode, phoneNumber, totpCode, newPin }`). The server verifies the TOTP code and atomically replaces the PIN hash + resets lock counters. No temporary PIN is generated or transmitted — the user supplies `newPin` directly. Actors without TOTP get `422 AUTH_PIN_RESET_TOTP_REQUIRED` and must request a Backoffice forced reset instead. Unknown phone or invalid TOTP both return `401 AUTH_MFA_INVALID` (anti-enumeration). Active sessions are not revoked; the next login still requires the new PIN + TOTP.

### 3.4 Merchant Auth & Its Limitations

`POST /api/v1/auth/merchant/login` — public. Phone + PIN; the `LoginResponse` has **three** shapes (identical to Customer): full session (`tokens`), `pinSetupRequired`, or `mfaRequired` when the merchant has TOTP enrolled. The `mfaRequired` branch is resolved with `POST /api/v1/auth/merchant/login/verify-mfa`.

| Endpoint | Bearer required | Purpose |
|---|---|---|
| `POST /api/v1/auth/merchant/login` | public | phone + PIN login |
| `POST /api/v1/auth/merchant/login/verify-mfa` | public | complete a `mfaRequired` login |
| `POST /api/v1/auth/merchant/auth-pin/setup` | `PIN_SETUP` token | set initial PIN |
| `PUT /api/v1/auth/merchant/auth-pin` | Merchant JWT | rotate PIN; requires `currentPin` |
| `POST /api/v1/auth/merchant/totp-setup` | Merchant JWT | start TOTP enrollment — returns `secret` + `qrUri` |
| `POST /api/v1/auth/merchant/totp-confirm` | Merchant JWT | confirm with first 6-digit code |
| `DELETE /api/v1/auth/merchant/totp-setup` | Merchant JWT | revoke TOTP; requires a current code as step-up |

**Critical frontend constraints — these are real gaps, not omissions in this doc:**

- **No `POST /api/v1/auth/merchant/refresh`.** A merchant access token (8h) cannot be refreshed. When it expires, the merchant must log in again with phone + PIN.
- **No `POST /api/v1/auth/merchant/logout`.** There is no server-side merchant token revocation endpoint. "Logout" in the merchant UI can only clear local storage; the JWT remains valid until natural expiry.
- The merchant refresh TTL (7d) exists in config but is currently unreachable from the portal because no refresh endpoint is wired.

### 3.5 Legacy SMS OTP (dormant)

`POST /api/v1/auth/customer/otp-request` and `/otp-verify` exist but `sms-otp.enabled=false` by default → both return `410 Gone` with `LEGACY_OTP_LOGIN_REMOVED`. Do not build the customer login on these. There is **no** legacy SMS OTP endpoint for merchants — merchant MFA is TOTP-only.

### 3.6 JWT Claims

| Claim | Customer | Merchant |
|---|---|---|
| `sub` / `actorId` | customer UUID | merchant-actor UUID |
| `act` | `CUSTOMER` | `MERCHANT` |
| `mid` | — | merchant UUID (required by all `/merchant/*` endpoints) |
| `jti`, `iat`, `exp` | yes | yes |

Neither carries `perms` or `brole`. A `MERCHANT` JWT without `mid` is rejected with `403`. (The same `MERCHANT` actor type is also used by the Terminal device session, but a terminal JWT cannot call the merchant portal — different issuance path; the portal requires the merchant phone+PIN JWT.)

---

## 4. Capability Maps

### 4.1 Customer Capability Map

| Area | Customer can do |
|---|---|
| Session | PIN-first login, MFA verify, refresh, logout, set/rotate PIN, set up/confirm/revoke TOTP |
| Profile | View own profile, wallet balance, assigned limit profile |
| Activity | View recent activity (dashboard), full cursor-paginated transaction list + detail, ledger statement |
| P2P | Send money to another customer by phone (PIN/confirmation gated) |
| Bill payment | Pay a bill **only when `billpay.enabled=true`** — otherwise `501` |
| Beneficiaries | View recent P2P recipients (derived from history) |
| Cards | List own cards, view a card, self-report a card lost/stolen |

The Customer **cannot**: cash-in/cash-out, buy or replace a card, register themselves, see other wallets, or inspect approvals.

### 4.2 Merchant Capability Map

| Area | Merchant can do |
|---|---|
| Session | PIN-first login, MFA verify, set/rotate PIN, set up/confirm/revoke TOTP (no refresh, no logout) |
| Profile | View own merchant profile, wallet balance |
| Activity | View cursor-paginated transaction list + detail, ledger statement |
| M2M | Transfer to another merchant by phone |
| Terminals | List own terminals, view a terminal detail (read-only) |
| Operators (cashiers) | Create, list, view, suspend, reactivate, revoke merchant operators |

The Merchant **cannot**: take payments from the portal (payments happen on the Terminal), provision terminals, refresh or revoke its own token, or inspect approvals.

### 4.3 Common (shared shell) Functionality

Identical contract shape on both surfaces — build these as shared components:

- **PIN-first login** + `pinSetupRequired` branch + `auth-pin/setup` + `PUT auth-pin`.
- **Profile + balance** cards (`/me` + `/me/balance` vs `/merchant/me` + `/merchant/balance`).
- **Transaction list** (cursor-paginated, same query params `cursor/limit/status/type/from/to`, near-identical row DTO) + **transaction detail by id**.
- **Ledger statement** (cursor-paginated, same `cursor/limit/from/to`).
- **A phone-addressed transfer** with `Idempotency-Key` (`/me/p2p` vs `/merchant/m2m`) — note P2P has a control gate, M2M does not.
- Shared envelopes, error handling, pagination, enums.

---

## 5. Exhaustive API Mapping

`uuid` = UUID string · `instant` = ISO-8601 date-time · `date` = `YYYY-MM-DD` · `long` = KMF minor-unit integer.

### 5.1 Customer — Auth

| Method | Path | Auth | Headers | Request | Response | Primary errors | Notes |
|---|---|---|---|---|---|---|---|
| POST | `/api/v1/auth/customer/login` | Public | — | `LoginRequest` | `200 ApiResponse<LoginResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 INVALID_CREDENTIALS`, `422 AUTH_PIN_LOCKED`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | Branch on `pinSetupRequired` → `mfaRequired` → `tokens`. |
| POST | `/api/v1/auth/customer/login/verify-mfa` | Public | — | `VerifyMfaRequest` | `200 ApiResponse<LoginResponse>` (tokens) | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 INVALID_CREDENTIALS`, `422 ACTOR_*`, `429 TERMINAL_RATE_LIMIT` | Only after a `mfaRequired=true` login. |
| POST | `/api/v1/auth/customer/refresh` | Public | — | `RefreshTokenRequest` | `200 ApiResponse<TokenResponse>` | `401 REFRESH_TOKEN_INVALID`, `401 INVALID_CREDENTIALS`, `422 ACTOR_*`, `429 TERMINAL_RATE_LIMIT` | Replace stored refresh token with the returned one. |
| POST | `/api/v1/auth/customer/logout` | Customer JWT | `Authorization` | none | `204 No Content` | `401 UNAUTHORIZED` | Revokes current access token + active refresh tokens. Clear local tokens. |
| POST | `/api/v1/auth/customer/auth-pin/setup` | `PIN_SETUP` token | `Authorization` | `SetAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `422 AUTH_PIN_ALREADY_SET`, `422 AUTH_PIN_FORMAT` | Consumes the single-use `pinSetupToken`. |
| PUT | `/api/v1/auth/customer/auth-pin` | Customer JWT | `Authorization` | `ChangeAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_PIN_INVALID`, `401 UNAUTHORIZED`, `422 AUTH_PIN_NOT_SET`, `422 AUTH_PIN_LOCKED`, `422 AUTH_PIN_FORMAT` | Requires `currentPin`. |
| POST | `/api/v1/auth/customer/auth-pin/reset` | Public | none | `ResetAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_MFA_INVALID`, `422 AUTH_PIN_RESET_TOTP_REQUIRED`, `422 AUTH_PIN_FORMAT`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `422 ACTOR_PENDING_KYC` | Forgotten-PIN reset gated by TOTP. New PIN supplied in the request; no temporary PIN is generated. |
| POST | `/api/v1/auth/customer/totp-setup` | Customer JWT | `Authorization` | none | `200 ApiResponse<TotpSetupResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN` | Display `qrUri` as QR; treat `secret` as a credential. |
| POST | `/api/v1/auth/customer/totp-confirm` | Customer JWT | `Authorization` | `TotpConfirmRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED` | Activates the pending secret. |
| DELETE | `/api/v1/auth/customer/totp-setup` | Customer JWT | `Authorization` | `TotpRevokeRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED` | Step-up with a current TOTP code. |
| POST | `/api/v1/auth/customer/otp-request` | Public, deprecated | — | `CustomerOtpRequest` | `200` or `410` | `410 LEGACY_OTP_LOGIN_REMOVED`, ... | Disabled by default. Do not use. |
| POST | `/api/v1/auth/customer/otp-verify` | Public, deprecated | — | `CustomerOtpVerifyRequest` | `200` or `410` | `410 LEGACY_OTP_LOGIN_REMOVED`, ... | Disabled by default. Do not use. |

### 5.2 Customer — Portal

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/me` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<CustomerProfileResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Profile + `kycLevel` + `status` + `limitProfileId`. |
| GET | `/api/v1/me/balance` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<CustomerBalanceResponse>` | `401`, `403`, `404 WALLET_NOT_FOUND` | Wallet state in KMF. |
| GET | `/api/v1/me/limits` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<CustomerLimitsResponse>` | `401`, `403`, `404 CONFIG_LIMIT_PROFILE_NOT_FOUND` | `404` when no profile assigned. |
| GET | `/api/v1/me/activity?limit` | Customer JWT | `Authorization` | `limit` (default 10, max 50) | — | `200 ApiResponse<List<CustomerTransactionResponse>>` | `401`, `403` | Dashboard/home use; not paginated. |
| GET | `/api/v1/me/transactions?cursor&limit&status&type&from&to` | Customer JWT | `Authorization` | `cursor`,`limit`,`status`,`type`,`from`,`to` | — | `200 PagedResponse<CustomerTransactionResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401`, `403` | Wallet-scoped, newest first. |
| GET | `/api/v1/me/transactions/{id}` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<CustomerTransactionResponse>` | `401`, `403`, `404 TRANSACTION_NOT_FOUND` | `403`/`404` if not linked to the customer wallet. |
| GET | `/api/v1/me/statements?cursor&limit&from&to` | Customer JWT | `Authorization` | `cursor`,`limit`,`from`,`to` | — | `200 PagedResponse<CustomerStatementEntryResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401`, `403` | Ledger entries, newest first. |
| GET | `/api/v1/me/beneficiaries?limit` | Customer JWT | `Authorization` | `limit` (default 20, max 50) | — | `200 ApiResponse<List<BeneficiaryResponse>>` | `401`, `403` | Recent unique P2P recipients from history. |
| GET | `/api/v1/me/cards` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<List<CustomerCardResponse>>` | `401`, `403` | All cards owned by this customer. |
| GET | `/api/v1/me/cards/{id}` | Customer JWT | `Authorization` | — | — | `200 ApiResponse<CustomerCardResponse>` | `401`, `403`, `404 CARD_NOT_FOUND` | `403`/`404` if not the customer's card. |
| POST | `/api/v1/me/cards/{id}/report-lost` | Customer JWT | `Authorization` | — | none | `200 ApiResponse<CustomerCardResponse>` | `401`, `403`, `404 CARD_NOT_FOUND` | Idempotent when already `LOST`. |
| POST | `/api/v1/me/cards/{id}/report-stolen` | Customer JWT | `Authorization` | — | none | `200 ApiResponse<CustomerCardResponse>` | `401`, `403`, `404 CARD_NOT_FOUND` | Idempotent when already `STOLEN`. |
| POST | `/api/v1/me/p2p` | Customer JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | — | `P2pTransferRequest` | `201`/`200`/`202 ApiResponse<P2pTransferResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `400 TRANSACTION_IDEMPOTENCY_KEY_MISSING`, `401`, `403`, `404 CUSTOMER_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 WALLET_FROZEN`, `422 WALLET_SUSPENDED`, `422 WALLET_CLOSED`, `422 INSUFFICIENT_BALANCE`, `422 LIMIT_EXCEEDED`, `422 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `422 CONFIG_RULE_INACTIVE` | `202` = PIN/confirmation control fired; resubmit same key with the cleared flag. |
| POST | `/api/v1/me/bill-payments` | Customer JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | — | `InitiateBillPaymentRequest` | `201`/`200`/`202 ApiResponse<BillPaymentResponse>` or `501` | `501` when `billpay.enabled=false`; `400 *`, `401`, `403`, `404`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 *` when enabled | Feature-flagged OFF by default. |

### 5.3 Merchant — Auth

| Method | Path | Auth | Headers | Request | Response | Primary errors | Notes |
|---|---|---|---|---|---|---|---|
| POST | `/api/v1/auth/merchant/login` | Public | — | `LoginRequest` | `200 ApiResponse<LoginResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 INVALID_CREDENTIALS`, `422 AUTH_PIN_LOCKED`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | Three shapes: `tokens`, `pinSetupRequired`, or `mfaRequired` (TOTP-enrolled). |
| POST | `/api/v1/auth/merchant/login/verify-mfa` | Public | — | `VerifyMfaRequest` | `200 ApiResponse<LoginResponse>` (tokens) | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 INVALID_CREDENTIALS`, `422 ACTOR_*`, `429 TERMINAL_RATE_LIMIT` | Only after a `mfaRequired=true` login. |
| POST | `/api/v1/auth/merchant/auth-pin/setup` | `PIN_SETUP` token | `Authorization` | `SetAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `422 AUTH_PIN_ALREADY_SET`, `422 AUTH_PIN_FORMAT` | Single-use token. |
| PUT | `/api/v1/auth/merchant/auth-pin` | Merchant JWT | `Authorization` | `ChangeAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_PIN_INVALID`, `401 UNAUTHORIZED`, `422 AUTH_PIN_NOT_SET`, `422 AUTH_PIN_LOCKED`, `422 AUTH_PIN_FORMAT` | Requires `currentPin`. |
| POST | `/api/v1/auth/merchant/auth-pin/reset` | Public | none | `ResetAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_MFA_INVALID`, `422 AUTH_PIN_RESET_TOTP_REQUIRED`, `422 AUTH_PIN_FORMAT`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `422 ACTOR_PENDING_KYC` | Forgotten-PIN reset gated by TOTP. Same contract as Customer. |
| POST | `/api/v1/auth/merchant/totp-setup` | Merchant JWT | `Authorization` | none | `200 ApiResponse<TotpSetupResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN` | Display `qrUri` as QR; treat `secret` as a credential. |
| POST | `/api/v1/auth/merchant/totp-confirm` | Merchant JWT | `Authorization` | `TotpConfirmRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED` | Activates the pending secret. |
| DELETE | `/api/v1/auth/merchant/totp-setup` | Merchant JWT | `Authorization` | `TotpRevokeRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED` | Step-up with a current TOTP code. |

> No merchant `refresh` and no merchant `logout` endpoint exist. See [3.4](#34-merchant-auth--its-limitations).

### 5.4 Merchant — Portal

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/merchant/me` | Merchant JWT | `Authorization` | — | — | `200 ApiResponse<MerchantProfileResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Profile + `canCashOut` + `canReceiveFromMerchant`. |
| GET | `/api/v1/merchant/balance` | Merchant JWT | `Authorization` | — | — | `200 ApiResponse<MerchantBalanceResponse>` | `401`, `403`, `404 WALLET_NOT_FOUND` | Wallet state in KMF. |
| GET | `/api/v1/merchant/transactions?cursor&limit&status&type&from&to` | Merchant JWT | `Authorization` | `cursor`,`limit`,`status`,`type`,`from`,`to` | — | `200 PagedResponse<MerchantTransactionResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401`, `403` | Wallet-scoped, newest first. |
| GET | `/api/v1/merchant/transactions/{id}` | Merchant JWT | `Authorization` | — | — | `200 ApiResponse<MerchantTransactionResponse>` | `401`, `403`, `404 TRANSACTION_NOT_FOUND` | `403`/`404` if not linked to the merchant wallet. |
| GET | `/api/v1/merchant/statements?cursor&limit&from&to` | Merchant JWT | `Authorization` | `cursor`,`limit`,`from`,`to` | — | `200 PagedResponse<MerchantStatementEntryResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401`, `403` | Ledger entries, newest first. |
| GET | `/api/v1/merchant/terminals` | Merchant JWT | `Authorization` | — | — | `200 PagedResponse<MerchantTerminalResponse>` (`.last`) | `401`, `403` | Full list, not cursor-paginated. |
| GET | `/api/v1/merchant/terminals/{id}` | Merchant JWT | `Authorization` | — | — | `200 ApiResponse<MerchantTerminalResponse>` | `401`, `403`, `404 TERMINAL_NOT_FOUND` | `403`/`404` if terminal belongs to another merchant. |
| POST | `/api/v1/merchant/m2m` | Merchant JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | — | `MerchantToMerchantRequest` | `201`/`200 ApiResponse<MerchantToMerchantResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `400 TRANSACTION_IDEMPOTENCY_KEY_MISSING`, `401`, `403`, `404 MERCHANT_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 WALLET_FROZEN`, `422 WALLET_SUSPENDED`, `422 WALLET_CLOSED`, `422 INSUFFICIENT_BALANCE`, `422 LIMIT_EXCEEDED`, `422 CONFIG_RULE_INACTIVE`, `422 MERCHANT_TO_MERCHANT_NOT_ALLOWED` | No `202` control gate. |
| POST | `/api/v1/merchant/operators` | Merchant JWT | `Authorization` | — | `CreateOperatorRequest` | `201 ApiResponse<OperatorResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401`, `403`, `404 MERCHANT_NOT_FOUND`, `422 PHONE_ALREADY_IN_USE` | `merchantId` taken from JWT. |
| GET | `/api/v1/merchant/operators?status&cursor&limit` | Merchant JWT | `Authorization` | `status` (`OperatorStatus`), `cursor`, `limit` | — | `200 PagedResponse<OperatorResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401`, `403` | Cursor-paginated; scoped to the merchant. |
| GET | `/api/v1/merchant/operators/{id}` | Merchant JWT | `Authorization` | — | — | `200 ApiResponse<OperatorResponse>` | `401`, `403`, `404 OPERATOR_NOT_FOUND` | `403`/`404` if operator belongs to another merchant. |
| PATCH | `/api/v1/merchant/operators/{id}/suspend` | Merchant JWT | `Authorization` | — | none | `200 ApiResponse<OperatorResponse>` | `401`, `403`, `404 OPERATOR_NOT_FOUND`, `422 OPERATOR_INVALID_STATUS` | Reversible. |
| PATCH | `/api/v1/merchant/operators/{id}/reactivate` | Merchant JWT | `Authorization` | — | none | `200 ApiResponse<OperatorResponse>` | `401`, `403`, `404 OPERATOR_NOT_FOUND`, `422 OPERATOR_INVALID_STATUS` | Reverses a suspend. |
| PATCH | `/api/v1/merchant/operators/{id}/revoke` | Merchant JWT | `Authorization` | — | none | `200 ApiResponse<OperatorResponse>` | `401`, `403`, `404 OPERATOR_NOT_FOUND`, `422 OPERATOR_INVALID_STATUS` | **Irreversible.** |

> Error codes marked with concrete names are confirmed from `ErrorCode`; status-transition codes (`OPERATOR_INVALID_STATUS`, `MERCHANT_TO_MERCHANT_NOT_ALLOWED`) reflect the documented controller behaviour — treat any `4xx` from the backend as authoritative regardless of the exact code shown.

---

## 6. Request Schemas

### 6.1 Auth (shared Customer + Merchant)

```ts
LoginRequest = {
  phoneCountryCode: string;  // digits, 1..5
  phoneNumber: string;       // digits, 4..15
  pin: string;               // digits, 4..8
}

VerifyMfaRequest = {         // Customer only
  challengeId: uuid;
  code: string;              // exactly 6 digits
}

RefreshTokenRequest = {      // Customer only
  refreshToken: string;
}

SetAuthPinRequest = {
  pin: string;               // digits, 4..8
}

ChangeAuthPinRequest = {
  currentPin: string;        // digits, 4..8
  newPin: string;            // digits, 4..8
}

TotpConfirmRequest = { code: string; }  // exactly 6 digits — Customer & Merchant
TotpRevokeRequest  = { code: string; }  // exactly 6 digits — Customer & Merchant
VerifyMfaRequest   = { challengeId: uuid; code: string; }  // 6 digits — Customer & Merchant

CustomerOtpRequest       = { phoneCountryCode: string; phoneNumber: string; }  // deprecated
CustomerOtpVerifyRequest = { challengeId: uuid; otpCode: string; }             // deprecated
```

### 6.2 Customer — Transfers & Bill Payment

```ts
P2pTransferRequest = {
  recipientCountryCode: string;       // not blank; defaults to "269"
  recipientPhone: string;             // not blank
  amount: long;                       // strictly positive
  description?: string;
  pin?: string;                       // sender's auth PIN — required to clear PENDING_PIN; verified server-side, wrong PIN rejects with 401 AUTH_PIN_INVALID
  confirmationAcknowledged?: boolean; // default false; set true to clear PENDING_CONFIRMATION
}

InitiateBillPaymentRequest = {
  serviceId: uuid;                    // the bill service — client must already hold it
  reference: string;                  // not blank, max 100 (e.g. meter / account number)
  amount: long;                       // >= 1
  pin?: string;                       // customer's auth PIN — required to clear PENDING_PIN; verified server-side, wrong PIN rejects with 401 AUTH_PIN_INVALID
  confirmationAcknowledged?: boolean;
}
```

### 6.3 Merchant — Transfer & Operators

```ts
MerchantToMerchantRequest = {
  recipientCountryCode: string;       // not blank
  recipientPhone: string;             // not blank
  amount: long;                       // >= 1
  description?: string;
}

CreateOperatorRequest = {
  fullName: string;          // not blank, max 255
  phoneCountryCode: string;  // not blank, max 10
  phoneNumber: string;       // not blank, max 20
  pin: string;               // 4..8 digits — operator's terminal PIN
}
```

### 6.4 Empty Bodies

`logout`, `report-lost`, `report-stolen`, and the operator `suspend`/`reactivate`/`revoke` PATCH endpoints take **no JSON body**.

---

## 7. Response Schemas

### 7.1 Auth

```ts
LoginResponse = {
  mfaRequired: boolean;
  challengeId?: uuid;          // present only when mfaRequired
  mfaFactor?: string;          // "TOTP" — present only when mfaRequired
  tokens?: {
    accessToken: string;
    accessTokenExpiresAt: instant;
    refreshToken: string;
    refreshTokenExpiresAt: instant;
  };
  pinSetupRequired: boolean;
  pinSetupToken?: string;            // present only when pinSetupRequired
  pinSetupTokenExpiresAt?: instant;  // present only when pinSetupRequired
}

TokenResponse = {
  tokenType: "Bearer";
  accessToken: string;
  accessTokenExpiresAt: instant;
  refreshToken: string;
  refreshTokenExpiresAt: instant;
}

TotpSetupResponse = { secret: string; qrUri: string; }   // Customer & Merchant
CustomerOtpResponse = { challengeId: uuid; expiresAt: instant; }  // deprecated
```

### 7.2 Customer — Profile, Balance, Limits

```ts
CustomerProfileResponse = {
  id: uuid;
  externalRef: string;
  fullName: string;
  dateOfBirth: date;
  phoneCountryCode: string;
  phoneNumber: string;
  nationalIdType: string;
  kycLevel: string;            // KycLevel
  kycVerifiedAt?: instant;
  status: string;              // CustomerStatus
  walletId?: uuid;
  limitProfileId?: uuid;
  addressIsland?: string;
  addressCity?: string;
  addressDistrict?: string;
  createdAt: instant;
}

CustomerBalanceResponse = {
  walletId: uuid;
  availableBalance: long;
  frozenBalance: long;
  currency: "KMF";
  walletStatus: string;        // WalletStatus
  updatedAt: instant;
}

CustomerLimitsResponse = {
  limitProfileId: uuid;
  profileName: string;
  maxTransactionAmount?: long;
  minTransactionAmount?: long;
  maxDailyAmount?: long;
  maxWeeklyAmount?: long;
  maxMonthlyAmount?: long;
  maxDailyTransactionCount?: int;
  maxMonthlyTransactionCount?: int;
  requiredKycLevel: string;
}
```

### 7.3 Customer — Transactions, Statements, Beneficiaries, Cards

```ts
CustomerTransactionResponse = {
  id: uuid;
  type: string;                // TransactionType
  status: string;              // TransactionStatus
  sourceWalletId: uuid;
  destinationWalletId: uuid;
  initiatorType: string;       // ActorType
  cardId?: uuid;
  terminalId?: uuid;
  requestedAmount: long;
  feeAmount: long;
  netAmountToDestination: long;
  currency: "KMF";
  declineReason?: string;
  correlationId?: uuid;
  createdAt: instant;
  completedAt?: instant;
  declinedAt?: instant;
}

CustomerStatementEntryResponse = {
  id: uuid;
  transactionId: uuid;
  entryType: string;           // DEBIT | CREDIT
  amount: long;
  runningBalance: long;
  currency: "KMF";
  description: string;
  postedAt: instant;
  globalSequence?: long;
}

BeneficiaryResponse = {
  customerId: uuid;
  externalRef: string;
  fullName: string;
  phoneCountryCode: string;
  phoneNumber: string;
}

CustomerCardResponse = {
  id: uuid;
  internalCardLast4: string | null;        // last 4 chars of printed/internal card number
  maskedInternalCardNumber: string | null; // e.g. "•••• 0099"
  cardType: string;            // CardType
  status: string;              // CardStatus
  pinEnabled: boolean;
  activatedAt?: instant;
  expiresAt: date;
  lastUsedAt?: instant;
  replacedByCardId?: uuid;
  issuedAt: instant;
}
```

> Note: `CustomerCardResponse` deliberately does **not** expose `nfcUid`, the full `internalCardNumber`, or any auth-key material.

### 7.4 Customer — P2P & Bill Payment Results

```ts
P2pTransferResponse = {
  outcome: string;             // EXECUTED | PENDING_PIN | PENDING_CONFIRMATION
  matchedThresholdAmount: long | null;  // populated only when a control fired
  transactionId: uuid | null;           // populated only when EXECUTED
  status: string | null;                // TransactionStatus — only when EXECUTED
  requestedAmount: long;                 // always
  feeAmount: long | null;                // only when EXECUTED
  netAmountToDestination: long | null;   // only when EXECUTED
  currency: "KMF";
  completedAt: instant | null;           // only when EXECUTED
  replayed: boolean | null;              // true on idempotent replay
}

BillPaymentResponse = {
  outcome: string;             // EXECUTED | PENDING_PIN | PENDING_CONFIRMATION
  matchedThresholdAmount: long | null;
  billPaymentId: uuid | null;            // only when EXECUTED
  status: string | null;                 // BillPaymentStatus — only when EXECUTED
  requestedAmount: long;
  feeAmount: long | null;
  netAmount: long | null;
  currency: "KMF";
  providerReference: string | null;      // only when EXECUTED
  transactionId: uuid | null;            // only when EXECUTED
  createdAt: instant | null;             // only when EXECUTED
}
```

Two shapes for each: **executed** (`HTTP 201`/`200`, `outcome=EXECUTED`, financial fields populated) vs **control fired** (`HTTP 202`, only `outcome`, `matchedThresholdAmount`, `requestedAmount`, `currency`).

### 7.5 Merchant — Profile, Balance

```ts
MerchantProfileResponse = {
  id: uuid;
  externalRef: string;
  businessName: string;
  legalName: string;
  businessType: string;
  taxId: string;
  phoneCountryCode: string;
  phoneNumber: string;
  addressIsland?: string;
  addressCity?: string;
  addressDistrict?: string;
  category: string;
  kycLevel: string;
  status: string;              // MerchantStatus
  walletId: uuid;
  canCashOut: boolean;
  canReceiveFromMerchant: boolean;
  createdAt: instant;
}

MerchantBalanceResponse = {    // same shape as CustomerBalanceResponse
  walletId: uuid;
  availableBalance: long;
  frozenBalance: long;
  currency: "KMF";
  walletStatus: string;
  updatedAt: instant;
}
```

### 7.6 Merchant — Transactions, Statements, Terminals

```ts
MerchantTransactionResponse = {
  id: uuid;
  type: string;
  status: string;
  sourceWalletId: uuid;
  destinationWalletId: uuid;
  initiatorType: string;
  initiatorId: uuid;
  operatorId?: uuid;           // present for terminal/operator-driven payments
  cardId?: uuid;
  terminalId?: uuid;
  requestedAmount: long;
  feeAmount: long;
  commissionAmount: long;
  netAmountToDestination: long;
  currency: "KMF";
  declineReason?: string;
  correlationId?: uuid;
  createdAt: instant;
  completedAt?: instant;
  declinedAt?: instant;
}

MerchantStatementEntryResponse = {  // same shape as CustomerStatementEntryResponse
  id: uuid; transactionId: uuid; entryType: string; amount: long;
  runningBalance: long; currency: "KMF"; description: string;
  postedAt: instant; globalSequence?: long;
}

MerchantTerminalResponse = {
  id: uuid;
  serialNumber: string;
  deviceModel?: string;
  androidVersion?: string;
  appVersion?: string;
  status: string;              // TerminalStatus
  lastSeenAt?: instant;
  lastKnownLatitude?: number;
  lastKnownLongitude?: number;
  registeredAt: instant;
  revokedAt?: instant;
}
```

### 7.7 Merchant — M2M Result & Operators

```ts
MerchantToMerchantResponse = {
  transactionId: uuid;
  status: string;              // TransactionStatus
  requestedAmount: long;
  feeAmount: long;
  netAmountToDestination: long;
  completedAt: instant;
  replayed: boolean;
}

OperatorResponse = {
  id: uuid;
  merchantId: uuid;
  fullName: string;
  phoneCountryCode: string;
  phoneNumber: string;
  status: string;              // OperatorStatus
  createdAt: instant;
  lastLoginAt?: instant;
}
```

> `OperatorResponse` never includes the PIN hash.

---

## 8. Enums

| Enum | Values |
|---|---|
| `ActorType` | `CUSTOMER`, `MERCHANT`, `AGENT`, `MERCHANT_OPERATOR`, `BACKOFFICE_USER`, `SYSTEM` |
| `CustomerStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `FROZEN`, `CLOSED` |
| `MerchantStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `CLOSED` |
| `OperatorStatus` | `ACTIVE`, `SUSPENDED`, `REVOKED` |
| `KycLevel` | `KYC_NONE`, `KYC_BASIC`, `KYC_VERIFIED`, `KYC_ENHANCED` |
| `WalletStatus` | `ACTIVE`, `FROZEN`, `SUSPENDED`, `CLOSED` |
| `TransactionType` | `CASH_IN`, `PAYMENT`, `CASH_OUT`, `CARD_SALE`, `AGENT_FUND_IN`, `AGENT_FUND_OUT`, `FEE_COLLECTION`, `COMMISSION_PAYOUT`, `REVERSAL`, `P2P_TRANSFER`, `MERCHANT_TO_MERCHANT`, `SERVICE_PAYMENT`, `CARD_REPLACEMENT`, `BILL_PROVIDER_SETTLEMENT`, `PLATFORM_REVENUE_WITHDRAWAL`, `PLATFORM_LIQUIDITY_TOP_UP` |
| `TransactionStatus` | `PENDING`, `AUTHORIZED`, `COMPLETED`, `DECLINED`, `EXPIRED`, `REVERSED` |
| `TransactionControlOutcome` | `EXECUTED`, `PENDING_PIN`, `PENDING_CONFIRMATION`, `PENDING_APPROVAL` |
| `EntryType` | `DEBIT`, `CREDIT` |
| `CardType` | `STANDARD`, `PREMIUM`, `CORPORATE` |
| `CardStatus` | `ISSUED`, `ACTIVE`, `BLOCKED`, `LOST`, `STOLEN`, `EXPIRED`, `CLOSED` |
| `TerminalStatus` | `REGISTERED`, `ACTIVE`, `SUSPENDED`, `REVOKED` |
| `BillPaymentStatus` | `PENDING`, `SUCCESS`, `FAILED` (observed via `BillPaymentResponse.status`; only relevant when bill-pay is enabled) |

For Customer P2P and bill payment, `TransactionControlOutcome` will only ever be `EXECUTED`, `PENDING_PIN`, or `PENDING_CONFIRMATION` — `PENDING_APPROVAL` is not surfaced to these endpoints.

---

## 9. Permissions, Session & Auth

### 9.1 Endpoint Authorization

Customer and Merchant APIs do not use the Backoffice `Permission` enum. Authorization is Spring Security role authority derived from the JWT actor type:

| Surface | Required authority | Extra claim requirement |
|---|---|---|
| `/api/v1/me/**` | `ROLE_CUSTOMER` | — |
| `/api/v1/merchant/**` | `ROLE_MERCHANT` | JWT must carry `mid` (merchantId) |

Every controller additionally re-checks the actor type in code (`requireCustomer` / `requireMerchant`) and throws `403` on mismatch. The `merchantId` for all merchant endpoints is **always taken from the JWT, never the request body** — a merchant can never read or mutate another merchant's data.

### 9.2 Session Model Summary

| Aspect | Customer | Merchant |
|---|---|---|
| Login factor | phone + PIN, optional TOTP MFA | phone + PIN, optional TOTP MFA |
| Access TTL | 15 min | 8 h |
| Refresh | `POST /auth/customer/refresh` (rotating) | **none — re-login on expiry** |
| Logout | `POST /auth/customer/logout` (server revokes) | **none — clear local storage only** |
| Initial PIN | `pinSetupRequired` branch → `auth-pin/setup` | same |
| Forgotten PIN | Self-service `POST auth-pin/reset` (requires TOTP) — else Backoffice forced reset → back to `pinSetupRequired` | same |
| Lockout | 3 wrong PINs → 15 min lock | same |

### 9.3 Frontend Gating Inputs

There is no capability endpoint. Drive UI visibility from profile fields:

- Customer: `status` (gate the whole app on `ACTIVE`), `kycLevel`, `limitProfileId` (a missing limit profile makes `/me/limits` return `404` and may make transfers fail with `CONFIG_LIMIT_PROFILE_NOT_FOUND`).
- Merchant: `status` (gate on `ACTIVE`), `canCashOut`, `canReceiveFromMerchant` (hide/disable the M2M send action when `false` — the backend will also reject it).

Treat any backend `403` or `422` as authoritative; never assume success before the `2xx` response.

---

## 10. Merchant Operators

Merchant operators (cashiers) are managed **only** from the merchant portal — there is no operator self-service and no Backoffice operator CRUD in scope here. The operator then logs in on a **Terminal** device via the separate Terminal auth flow (`/api/v1/terminal/auth/operator-login`), which is documented in `Terminal_Frontend_Specification.md`.

| Action | Endpoint | Effect |
|---|---|---|
| Create | `POST /api/v1/merchant/operators` | Creates a cashier bound to the merchant; sets the initial terminal PIN; returns `201` with `OperatorResponse`. |
| List | `GET /api/v1/merchant/operators?status&cursor&limit` | Cursor-paginated; optional `status` filter (`ACTIVE`/`SUSPENDED`/`REVOKED`). |
| View | `GET /api/v1/merchant/operators/{id}` | Single operator; `403`/`404` if not owned by the merchant. |
| Suspend | `PATCH /api/v1/merchant/operators/{id}/suspend` | Reversible. Operator can no longer open a terminal shift. |
| Reactivate | `PATCH /api/v1/merchant/operators/{id}/reactivate` | Reverses a suspend. |
| Revoke | `PATCH /api/v1/merchant/operators/{id}/revoke` | **Irreversible.** Permanently disables the operator. |

Frontend rules:

- The create form collects `fullName`, `phoneCountryCode`, `phoneNumber`, and a 4–8 digit `pin`. Communicate the PIN to the cashier out-of-band — it is never returned by the API and cannot be re-read.
- A duplicate phone returns `422 PHONE_ALREADY_IN_USE`.
- Confirm `revoke` with an explicit, clearly-worded modal — it cannot be undone.
- `suspend`/`reactivate`/`revoke` against an operator already in a terminal status return a `422` status-transition error — surface it and refresh the row.
- `lastLoginAt` reflects the cashier's last terminal login — useful for an "idle cashier" view.

---

## 11. Recommended UI Flows

### 11.1 Customer — Login

1. `POST /api/v1/auth/customer/login` with phone + PIN.
2. Branch on the response:
   - `pinSetupRequired=true` → store `pinSetupToken`, route to "Set your PIN", call `POST /auth/customer/auth-pin/setup`, then re-login.
   - `mfaRequired=true` → prompt for the 6-digit TOTP code, call `POST /auth/customer/login/verify-mfa`.
   - else → store `tokens`.
3. Because the access TTL is **15 min**, schedule a proactive `POST /auth/customer/refresh` (e.g. at ~12 min) and also refresh-on-`401`.
4. `POST /auth/customer/logout` on sign-out; clear local tokens.

### 11.2 Customer — Dashboard

- On load: `GET /api/v1/me`, `GET /api/v1/me/balance`, `GET /api/v1/me/activity?limit=10` in parallel.
- "Limits" screen: `GET /api/v1/me/limits` (handle `404` = no profile assigned → show "limits not configured").
- "History" screen: `GET /api/v1/me/transactions` with cursor paging; tap a row → `GET /api/v1/me/transactions/{id}`.
- "Statement" screen: `GET /api/v1/me/statements` with `from`/`to` date window.

### 11.3 Customer — Send Money (P2P)

1. Optionally pre-fill recipient from `GET /api/v1/me/beneficiaries`.
2. `POST /api/v1/me/p2p` with `recipientCountryCode`, `recipientPhone`, `amount`, optional `description`, and a fresh `Idempotency-Key`.
3. Handle the response:
   - `201` → executed, show receipt (`transactionId`, `feeAmount`, `netAmountToDestination`).
   - `200` → idempotent replay, show the same receipt (`replayed=true`).
   - `202 outcome=PENDING_PIN` → collect the customer's auth PIN, resubmit **with the same `Idempotency-Key`** and `pin=<rawPin>`. Backend verifies the PIN server-side; a wrong PIN returns `401 AUTH_PIN_INVALID` and counts toward the 3-strikes/15-min lock (`422 AUTH_PIN_LOCKED`). Priority is Approval > PIN > Confirmation, so an unanswered PIN never falls back to a confirmation prompt.
   - `202 outcome=PENDING_CONFIRMATION` → show a confirm prompt citing `matchedThresholdAmount`, resubmit **with the same `Idempotency-Key`** and `confirmationAcknowledged=true`. Only fires when the resolved tier is confirmation (i.e. amount triggers confirmation but not PIN/approval).
4. Each new amount/recipient = a **new** `Idempotency-Key`. A control continuation reuses the **same** key.

### 11.4 Customer — Cards

- `GET /api/v1/me/cards` → list; tap → `GET /api/v1/me/cards/{id}`.
- "Report lost"/"Report stolen" → `POST .../report-lost` / `.../report-stolen` (idempotent). After success, refresh the card; status becomes `LOST`/`STOLEN`.
- There is no card ordering/replacement here — direct the user to an Agent.

### 11.5 Customer — Bill Payment (only if enabled)

- Probe: a `501` on `POST /api/v1/me/bill-payments` means the feature is off — hide the bill-pay entry point entirely.
- When enabled: collect `serviceId` (the client must already know it — there is no catalogue endpoint), `reference`, `amount`; send with `Idempotency-Key`; handle `201`/`200`/`202` exactly like P2P.

### 11.6 Merchant — Login & Session

1. `POST /api/v1/auth/merchant/login` with phone + PIN.
2. Branch:
   - `pinSetupRequired` → `auth-pin/setup` → re-login;
   - `mfaRequired=true` → prompt for the 6-digit TOTP code, call `POST /api/v1/auth/merchant/login/verify-mfa` with `challengeId` + `code` → store `tokens`;
   - else store `tokens`.
3. There is **no refresh** — when the 8h token expires, force a re-login. "Sign out" only clears local storage (no server-side revocation).
4. TOTP enrollment (optional, post-login): `POST /totp-setup` → show `qrUri` as QR → `POST /totp-confirm` with the first code. `DELETE /totp-setup` (with a current code) revokes it. Mirrors the Customer TOTP flow exactly.

### 11.7 Merchant — Dashboard & Operations

- On load: `GET /api/v1/merchant/me`, `GET /api/v1/merchant/balance` in parallel.
- "Transactions"/"Statement": `GET /api/v1/merchant/transactions` + `/statements` (cursor paging).
- "Terminals": `GET /api/v1/merchant/terminals` (full list), tap → `/terminals/{id}`. Read-only — show status and `lastSeenAt`; direct provisioning requests to Backoffice.
- "Cashiers": the operator CRUD in [Section 10](#10-merchant-operators).
- "Pay another merchant": `POST /api/v1/merchant/m2m` with `Idempotency-Key`; handle `201`/`200` (no `202`). Hide the action when `canReceiveFromMerchant`/`canCashOut` constraints make it unavailable — but always treat the backend `422` as final.

---

## 12. UI States & Business Errors

### 12.1 Session States

| Trigger | State | Frontend action |
|---|---|---|
| `401` (any wrapped/raw) | Session expired/invalid | Customer: try `refresh`, else re-login. Merchant: re-login (no refresh). |
| `403 FORBIDDEN` | Wrong actor type / missing `mid` | Force re-login with the correct account type. |
| `422 AUTH_PIN_LOCKED` | PIN locked 15 min | Show countdown; block PIN entry. |
| `422 ACTOR_PENDING_KYC` | Account not yet activated | Block app; "your account is pending verification". |
| `422 ACTOR_SUSPENDED` / `ACTOR_CLOSED` / customer `FROZEN` | Account unusable | Block app; "contact support" — not self-recoverable. |
| `429 TERMINAL_RATE_LIMIT` | Too many auth attempts | Back off; show "try again shortly". |
| `410 LEGACY_OTP_LOGIN_REMOVED` | Legacy OTP used | Never call legacy OTP; route to PIN login. |

### 12.2 Financial / Business Errors

| Code | Meaning | Frontend handling |
|---|---|---|
| `400 TRANSACTION_IDEMPOTENCY_KEY_MISSING` | No `Idempotency-Key` header | Bug — always send a key on P2P/M2M/bill-pay. |
| `409 DUPLICATE_IDEMPOTENCY_KEY` | Concurrent reuse of a key | Retry with the **same** key → hits the replay path. |
| `422 INSUFFICIENT_BALANCE` | Not enough funds | Decline; show balance; suggest a smaller amount. |
| `422 LIMIT_EXCEEDED` | Limit profile threshold hit | Decline; reference `/me/limits` (or merchant equivalent). |
| `422 WALLET_FROZEN` / `WALLET_SUSPENDED` / `WALLET_CLOSED` | Wallet not transactable | Decline; "wallet unavailable — contact support". |
| `422 CONFIG_LIMIT_PROFILE_NOT_FOUND` / `CONFIG_RULE_INACTIVE` | Server-side config gap | Decline; generic "temporarily unavailable"; log for ops. |
| `404 CUSTOMER_NOT_FOUND` / `MERCHANT_NOT_FOUND` | Recipient phone not found | Inline field error on the recipient input. |
| `422 PHONE_ALREADY_IN_USE` | Operator phone collision | Inline error on the operator create form. |
| `404 TRANSACTION_NOT_FOUND` / `CARD_NOT_FOUND` / `TERMINAL_NOT_FOUND` / `OPERATOR_NOT_FOUND` | Resource missing or not owned | "Not found"; navigate back; refresh the list. |
| `501` on `/me/bill-payments` | Bill-pay feature off | Hide the bill-pay feature entirely. |

### 12.3 Control-Gate States (P2P & bill payment)

| `outcome` | HTTP | UI |
|---|---|---|
| `EXECUTED` | `201` (new) / `200` (replay) | Show receipt with financial fields. |
| `PENDING_PIN` | `202` | Collect the customer's auth PIN → resubmit same key with `pin=<rawPin>`. Wrong PIN returns `401 AUTH_PIN_INVALID`; 3 wrong attempts → `422 AUTH_PIN_LOCKED` (15 min). |
| `PENDING_CONFIRMATION` | `202` | Confirm prompt citing `matchedThresholdAmount` → resubmit same key, `confirmationAcknowledged=true`. |

### 12.4 List / Empty States

- Empty `data: []` with `hasMore=false` → render an empty state, no error.
- `GET /me/limits` `404` → "limits not configured", not an error toast.
- `GET /merchant/terminals` returns the full list in one call — no "load more".

---

## 13. Where To Start

The question this document is meant to answer: **Customer first, Merchant first, or a common foundation first?**

### 13.1 Build the common foundation first

A shared **auth + shell + primitives** layer should come before either portal because the two surfaces share the bulk of their contract:

- **Shared auth core** — `LoginRequest` / `LoginResponse` three-branch handling, `pinSetupRequired` → `auth-pin/setup`, `PUT auth-pin`. Identical request/response shapes for both actors; only the URL prefix and the post-login session policy differ.
- **Shared API client primitives** — `ApiResponse` / `PagedResponse` / `ApiError` parsing, cursor pagination helper, `Idempotency-Key` generation, `X-Correlation-Id` plumbing, the enum set, the error-code → UI-state mapping in [Section 12](#12-ui-states--business-errors).
- **Shared UI components** — profile card, balance card, cursor-paginated transaction list + detail, ledger statement view, a phone-addressed "send money" form with the control-gate (`202`) resubmit loop.

Estimated reuse: profile/balance, transaction list+detail, statement, and the transfer form are ~80% identical between the two surfaces. Building either portal first means building these twice.

### 13.2 Then build Customer before Merchant

After the foundation, **Customer** is the better second step:

- It is the larger, more self-contained surface (19 endpoints, 12 portal) and exercises every shared primitive including the `202` control gate (which Merchant M2M does **not** have), TOTP, and refresh — so finishing Customer validates the foundation completely.
- Merchant has known **session gaps** (no refresh, no logout) that may prompt a backend change; starting Customer avoids blocking the frontend on that decision.
- Merchant's distinctive value — **operator (cashier) management** and **terminal visibility** — is a small, well-bounded module that can be added quickly once the foundation and Customer prove the patterns.

### 13.3 Suggested sequencing

1. **Foundation** — API client, envelopes, pagination, enums, error mapping, shared auth core, shared list/detail/transfer components.
2. **Customer portal** — login + PIN setup + TOTP, dashboard (`/me`, `/balance`, `/activity`), history + statement, P2P with the control-gate loop, cards (list/detail/report), limits. Bill payment behind the `501` feature probe.
3. **Merchant portal** — login + PIN setup (accepting the no-refresh/no-logout constraints), dashboard (`/merchant/me`, `/balance`), transactions + statement, M2M, terminals (read-only), **operators CRUD**.
4. **Cross-cutting polish** — proactive customer-token refresh, session-expiry UX divergence between the two actor types, KYC/status gating screens.

> If forced to ship one surface to production first: **Customer**. It is the revenue-facing consumer experience, it is feature-complete on the backend (no flags blocking the core flows), and it has no auth-lifecycle gaps.

---

## 14. Evidence Index

| Area | Source class |
|---|---|
| Customer auth | `security.api.CustomerAuthController`, `security.api.CustomerAuthPinController`, `security.api.CustomerTotpController`, `security.application.PinLoginService`, `security.application.CustomerAuthenticationService`, `security.application.AuthPinService`, `security.application.TotpEnrollmentService` |
| Merchant auth | `security.api.MerchantAuthController`, `security.api.MerchantTotpController`, `security.application.PinLoginService`, `security.application.AuthPinService`, `security.application.TotpEnrollmentService` |
| Auth DTOs | `security.api.LoginRequest`, `LoginResponse`, `TokenResponse`, `VerifyMfaRequest`, `RefreshTokenRequest`, `SetAuthPinRequest`, `ChangeAuthPinRequest`, `TotpSetupResponse`, `TotpConfirmRequest`, `TotpRevokeRequest`, `CustomerOtpRequest`, `CustomerOtpVerifyRequest`, `CustomerOtpResponse` |
| Customer portal reads | `customerportal.api.CustomerMeController`, `CustomerTransactionController`, `CustomerStatementController`, `CustomerLimitsController`, `CustomerActivityController`, `CustomerBeneficiaryController`, `CustomerCardController`, `customerportal.application.CustomerReadService` |
| Customer portal DTOs | `customerportal.api.dto.CustomerProfileResponse`, `CustomerBalanceResponse`, `CustomerLimitsResponse`, `CustomerTransactionResponse`, `CustomerStatementEntryResponse`, `BeneficiaryResponse`, `CustomerCardResponse`, `P2pTransferRequest`, `P2pTransferResponse` |
| Customer P2P | `customerportal.api.CustomerP2pController`, `transaction.application.P2pTransferSubmissionUseCase`, `P2pTransferCommand`, `P2pTransferSubmissionResult` |
| Customer card self-report | `card.application.CustomerCardSelfReportUseCase` |
| Customer bill payment | `servicepayment.api.CustomerBillPaymentController`, `servicepayment.api.dto.InitiateBillPaymentRequest`, `BillPaymentResponse`, `servicepayment.application.BillPaymentSubmissionUseCase` |
| Merchant portal reads | `merchantportal.api.MerchantMeController`, `MerchantTransactionController`, `MerchantStatementController`, `MerchantTerminalController`, `merchantportal.application.MerchantReadService` |
| Merchant portal DTOs | `merchantportal.api.dto.MerchantProfileResponse`, `MerchantBalanceResponse`, `MerchantTransactionResponse`, `MerchantStatementEntryResponse`, `MerchantTerminalResponse`, `MerchantToMerchantRequest`, `MerchantToMerchantResponse`, `CreateOperatorRequest`, `OperatorResponse` |
| Merchant M2M | `merchantportal.api.MerchantM2mController`, `transaction.application.MerchantToMerchantUseCase`, `MerchantToMerchantCommand`, `MerchantToMerchantResult` |
| Merchant operators | `merchantportal.api.MerchantOperatorController`, `identity.application.CreateMerchantOperatorUseCase`, `ManageMerchantOperatorUseCase`, `identity.domain.MerchantOperator`, `OperatorStatus` |
| HTTP envelopes & pagination | `shared.infrastructure.web.ApiResponse`, `PagedResponse`, `ApiError`, `CursorUtils`, `shared.infrastructure.exception.GlobalExceptionHandler` |
| Security, rate limit, public routes | `shared.infrastructure.config.SecurityConfig`, `shared.infrastructure.web.RateLimitingFilter`, `CorrelationIdFilter` |
| JWT claims & authorities | `security.infrastructure.JwtService`, `security.domain.JwtPrincipal` |
| Token TTLs, feature flags, rate limits | `application.yml` (`komopay.jwt.expiration.*`, `komopay.auth.sms-otp.enabled`, `komopay.billpay.enabled`, `komopay.rate-limit.*`) |
| Error codes | `shared.infrastructure.exception.ErrorCode` |
| Enums | `shared.domain.ActorType`, `identity.domain.CustomerStatus`, `MerchantStatus`, `OperatorStatus`, `KycLevel`, `wallet.domain.WalletStatus`, `shared.domain.TransactionType`, `transaction.domain.TransactionStatus`, `transaction.application.TransactionControlOutcome`, `wallet.domain.EntryType`, `card.domain.CardType`, `CardStatus`, `terminal.domain.TerminalStatus`, `servicepayment.domain.BillPaymentStatus` |

---

End of document. All content above is derived from the current Lipa (KomoPay) backend codebase only.
