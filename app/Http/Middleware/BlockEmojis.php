<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockEmojis
{
    /**
     * Matches emojis, regional flag pairs, keycap sequences, dingbats/pictographs,
     * zero-width joiners, and emoji variation selectors — while preserving normal text,
     * Filipino letters (Ñ/ñ), and currency symbols (₱).
     */
    private const EMOJI_PATTERN = '/(?:[\x{1F1E6}-\x{1F1FF}]|[0-9#*]\x{FE0F}?\x{20E3}|[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{2300}-\x{23FF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}\x{20E3}])/u';

    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'action_password',
        'action_password_confirmation',
        'loginpassword',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $errors = [];
        $this->scanForEmojis($request->except(['_token', '_method']), '', $errors);

        if (!empty($errors)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Emojis are not allowed in form inputs.',
                    'errors' => array_map(fn ($msg) => [$msg], $errors),
                ], 422);
            }

            return back()
                ->withErrors($errors)
                ->withInput($request->except(self::SENSITIVE_FIELDS));
        }

        return $next($request);
    }

    private function scanForEmojis(mixed $data, string $prefix, array &$errors): void
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $fieldKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
                $this->scanForEmojis($value, $fieldKey, $errors);
            }
            return;
        }

        if (is_string($data) && preg_match(self::EMOJI_PATTERN, $data) === 1) {
            $label = ucwords(str_replace(['_', '.'], ' ', $prefix ?: 'input'));
            $errors[$prefix ?: 'input'] = "Emojis are not allowed in {$label}.";
        }
    }
}
