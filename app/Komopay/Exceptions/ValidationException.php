<?php

namespace App\Komopay\Exceptions;

/** 400 — request validation errors (`VALIDATION_*`, `TRANSACTION_IDEMPOTENCY_KEY_MISSING`, ...). */
class ValidationException extends KomopayException {}
