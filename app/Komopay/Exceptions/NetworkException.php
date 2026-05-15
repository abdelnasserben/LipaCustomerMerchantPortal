<?php

namespace App\Komopay\Exceptions;

/** Connection refused, DNS, TLS, timeout, malformed body — anything before/after the API envelope. */
class NetworkException extends KomopayException {}
