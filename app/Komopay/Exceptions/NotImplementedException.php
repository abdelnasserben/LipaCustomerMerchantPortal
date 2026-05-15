<?php

namespace App\Komopay\Exceptions;

/** 501 — feature-flagged endpoint disabled (e.g. bill payment when `billpay.enabled=false`). */
class NotImplementedException extends KomopayException {}
