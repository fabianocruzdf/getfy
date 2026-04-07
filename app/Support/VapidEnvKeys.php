<?php

namespace App\Support;

/**
 * Normaliza chaves VAPID vindas do .env para o formato esperado por minishlink/web-push (Base64 URL-safe).
 */
final class VapidEnvKeys
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim($value);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        $v = str_replace(["\r", "\n", ' ', "\t"], '', $v);
        if ($v === '') {
            return null;
        }
        if (str_contains($v, '+') || str_contains($v, '/')) {
            $v = strtr($v, ['+' => '-', '/' => '_']);
        }

        return $v;
    }
}
