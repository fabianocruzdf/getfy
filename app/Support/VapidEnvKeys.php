<?php

namespace App\Support;

use Jose\Component\Core\Util\Base64UrlSafe;

/**
 * Normaliza chaves VAPID vindas do .env para o formato esperado por minishlink/web-push (Base64 URL-safe).
 */
final class VapidEnvKeys
{
    private const PUBLIC_KEY_DECODED_LEN = 65;

    private const PRIVATE_KEY_DECODED_LEN = 32;

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim($value);
        // BOM UTF-8 (comum ao colar chaves de editores / exports)
        if (str_starts_with($v, "\xEF\xBB\xBF")) {
            $v = substr($v, 3);
        }
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

    /**
     * Verifica se as chaves decodificam para os tamanhos esperados pela curva P-256 (Web Push).
     */
    public static function decodedLengthsValid(string $publicKeyBase64Url, string $privateKeyBase64Url): bool
    {
        try {
            $pub = Base64UrlSafe::decode($publicKeyBase64Url);
            $priv = Base64UrlSafe::decode($privateKeyBase64Url);
        } catch (\Throwable) {
            return false;
        }

        return strlen($pub) === self::PUBLIC_KEY_DECODED_LEN
            && strlen($priv) === self::PRIVATE_KEY_DECODED_LEN;
    }
}
