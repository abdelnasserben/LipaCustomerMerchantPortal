<?php

namespace App\Services;

use Carbon\Carbon;

class FormatService
{
    public static function kmf(int|float $amount, bool $signed = false): string
    {
        $abs = abs($amount);
        $formatted = number_format($abs, 0, ',', ' ');
        $sign = $amount < 0 ? '−' : ($signed ? '+' : '');
        return "{$sign}{$formatted} KMF";
    }

    public static function kmfShort(int|float $amount): string
    {
        $abs = abs($amount);
        if ($abs >= 1000000) {
            return number_format($abs / 1000000, 1) . 'M KMF';
        }
        if ($abs >= 1000) {
            return number_format($abs / 1000, 0) . 'K KMF';
        }
        return self::kmf($amount);
    }

    public static function phone(string $cc, string $number): string
    {
        $chunks = str_split($number, 2);
        return '+' . $cc . ' ' . implode(' ', $chunks);
    }

    public static function shortId(string $id): string
    {
        if (strlen($id) <= 8) return $id;
        return substr($id, 0, 6) . '…' . substr($id, -4);
    }

    public static function relativeTime(string $iso): string
    {
        try {
            return Carbon::parse($iso)->diffForHumans();
        } catch (\Throwable) {
            return $iso;
        }
    }

    public static function dateTime(string $iso, string $format = 'd M Y à H:i'): string
    {
        try {
            return Carbon::parse($iso)->format($format);
        } catch (\Throwable) {
            return $iso;
        }
    }

    public static function date(string $iso): string
    {
        try {
            return Carbon::parse($iso)->format('d M Y');
        } catch (\Throwable) {
            return $iso;
        }
    }

    public static function txTypLabel(string $type): string
    {
        return match ($type) {
            'P2P_TRANSFER'          => 'P2P Transfer',
            'CASH_IN'               => 'Cash-in',
            'CASH_OUT'              => 'Cash-out',
            'CARD_SALE'             => 'Card Sale',
            'PAYMENT'               => 'Payment',
            'MERCHANT_TO_MERCHANT'  => 'M2M Transfer',
            'COMMISSION_PAYOUT'     => 'Commission',
            'REVERSAL'              => 'Reversal',
            'SERVICE_PAYMENT'       => 'Bill Payment',
            'AGENT_FUND_IN'         => 'Agent Fund-in',
            'AGENT_FUND_OUT'        => 'Agent Fund-out',
            default                 => $type,
        };
    }

    public static function statusPillClass(string $status): string
    {
        return match ($status) {
            'COMPLETED', 'ACTIVE'   => 'pill-success',
            'PENDING', 'AUTHORIZED' => 'pill-pending',
            'DECLINED', 'EXPIRED'   => 'pill-declined',
            'REVERSED'              => 'pill-warn',
            'SUSPENDED'             => 'pill-warn',
            'REVOKED', 'BLOCKED',
            'LOST', 'STOLEN',
            'CLOSED'                => 'pill-declined',
            'FROZEN'                => 'pill-info',
            'REGISTERED'            => 'pill-neutral',
            default                 => 'pill-neutral',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'PENDING_KYC'   => 'Pending KYC',
            'KYC_NONE'      => 'No KYC',
            'KYC_BASIC'     => 'KYC Basic',
            'KYC_VERIFIED'  => 'KYC Verified',
            'KYC_ENHANCED'  => 'KYC Enhanced',
            default         => ucfirst(strtolower(str_replace('_', ' ', $status))),
        };
    }

    public static function cardMask(array $card): string
    {
        return '•••• •••• •••• ' . ($card['last4'] ?? '****');
    }

    public static function initials(string $name): string
    {
        $parts = explode(' ', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
        }
        return strtoupper(mb_substr($name, 0, 2));
    }
}
