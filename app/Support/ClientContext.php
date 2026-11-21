<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientContext
{
    /**
     * Resolve the real client IP address behind reverse proxies (Wasmer Edge, Cloudflare, Nginx).
     */
    public static function ip(?Request $request = null): string
    {
        $request = $request ?? request();
        if (!$request) {
            return '127.0.0.1';
        }

        // Priority 1: Single-IP headers set by edge proxies / CDNs
        $directHeaders = [
            'CF-Connecting-IP',
            'True-Client-IP',
            'Fastly-Client-IP',
            'X-Real-IP',
            'X-Client-IP',
        ];

        foreach ($directHeaders as $header) {
            $value = self::normalizeIp((string) $request->header($header, ''));
            if ($value !== '' && self::isValidPublicIp($value)) {
                return $value;
            }
        }

        // Priority 2: Standard X-Forwarded-For chain (leftmost public IP is the original client)
        $forwardedFor = (string) $request->header('X-Forwarded-For', '');
        if ($forwardedFor !== '') {
            $parts = array_map(fn ($p) => self::normalizeIp($p), explode(',', $forwardedFor));
            foreach ($parts as $candidate) {
                if (self::isValidPublicIp($candidate)) {
                    return $candidate;
                }
            }
            // If all IPs in chain are private (e.g., LAN proxy), return first valid non-loopback IP
            foreach ($parts as $candidate) {
                if (self::isValidIp($candidate) && !self::isLoopback($candidate)) {
                    return $candidate;
                }
            }
        }

        // Priority 3: RFC 7239 Forwarded header (e.g. Forwarded: for=203.0.113.43;proto=https)
        $forwarded = (string) $request->header('Forwarded', '');
        if ($forwarded !== '' && preg_match('/for="?\[?([a-fA-F0-9:.]+)\]?"?/i', $forwarded, $matches)) {
            $candidate = self::normalizeIp($matches[1]);
            if (self::isValidIp($candidate) && !self::isLoopback($candidate)) {
                return $candidate;
            }
        }

        return self::normalizeIp((string) ($request->ip() ?: '127.0.0.1')) ?: '127.0.0.1';
    }

    /**
     * Strip IPv4-mapped IPv6 prefix (::ffff:103.31.73.4 -> 103.31.73.4) and trim whitespace.
     */
    public static function normalizeIp(string $ip): string
    {
        $clean = trim($ip);
        if (str_starts_with(strtolower($clean), '::ffff:')) {
            $mapped = substr($clean, 7);
            if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                return $mapped;
            }
        }
        return $clean;
    }

    /**
     * Parse a User-Agent string into a clean human-readable browser, OS, and device summary.
     */
    public static function parseDevice(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);
        if ($ua === '') {
            return [
                'browser' => 'Unknown Browser',
                'os'      => 'Unknown OS',
                'device'  => 'Desktop',
                'label'   => 'Unknown Device',
            ];
        }

        // Browser detection (order matters: Edge/Opera/Brave contain Chrome/Safari tokens)
        $browser = 'Browser';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) {
            $browser = 'Opera';
        } elseif (str_contains($ua, 'Brave')) {
            $browser = 'Brave';
        } elseif (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Chromium')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/')) {
            $browser = 'Internet Explorer';
        } elseif (str_contains($ua, 'curl/')) {
            $browser = 'cURL';
        }

        // OS detection
        $os = 'Unknown OS';
        $device = 'Desktop';
        if (preg_match('/iPhone|iPod/i', $ua)) {
            $os = 'iOS';
            $device = 'Mobile';
        } elseif (preg_match('/iPad/i', $ua)) {
            $os = 'iPadOS';
            $device = 'Tablet';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
            $device = preg_match('/Mobile/i', $ua) ? 'Mobile' : 'Tablet';
        } elseif (preg_match('/Windows NT/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/CrOS/i', $ua)) {
            $os = 'ChromeOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        return [
            'browser' => $browser,
            'os'      => $os,
            'device'  => $device,
            'label'   => "{$browser} · {$os}",
        ];
    }

    private static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    private static function isValidPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    private static function isLoopback(string $ip): bool
    {
        return $ip === '::1' || str_starts_with($ip, '127.');
    }
}
