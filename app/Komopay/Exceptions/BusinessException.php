<?php

namespace App\Komopay\Exceptions;

/**
 * 404 / 409 / 422 — domain errors:
 * INSUFFICIENT_BALANCE, LIMIT_EXCEEDED, WALLET_FROZEN, AUTH_PIN_LOCKED,
 * DUPLICATE_IDEMPOTENCY_KEY, PHONE_ALREADY_IN_USE, OPERATOR_INVALID_STATUS, ...
 */
class BusinessException extends KomopayException {}
