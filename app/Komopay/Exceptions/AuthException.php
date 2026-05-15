<?php

namespace App\Komopay\Exceptions;

/** 401 / 403 — bad credentials, expired/revoked JWT, wrong actor type. */
class AuthException extends KomopayException {}
