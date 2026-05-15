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
        $key = "tx.{$type}";
        $translated = __($key);
        return $translated === $key ? $type : $translated;
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
        $key = "status.{$status}";
        $translated = __($key);
        if ($translated !== $key) {
            return $translated;
        }
        return ucfirst(strtolower(str_replace('_', ' ', $status)));
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
