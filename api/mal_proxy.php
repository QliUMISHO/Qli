<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function httpGet(string $url): string
{
    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException('Failed to initialize cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: QliPortfolio/1.0',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ],
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error !== '') {
        throw new RuntimeException('Request failed: ' . $error);
    }

    if ($status >= 400) {
        throw new RuntimeException('HTTP ' . $status . ' from upstream.');
    }

    return (string) $response;
}

function httpGetJson(string $url): array
{
    $raw = httpGet($url);
    $json = json_decode($raw, true);

    if (!is_array($json)) {
        throw new RuntimeException('Invalid JSON from upstream.');
    }

    return $json;
}

function normalizeText(?string $value): string
{
    $value = html_entity_decode((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = strip_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return trim($value);
}

function firstNonEmpty(array $values, string $fallback = ''): string
{
    foreach ($values as $value) {
        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return $fallback;
}

function safeUrl(?string $value, string $fallback = ''): string
{
    $value = trim((string) ($value ?? ''));
    if ($value !== '' && preg_match('~^https?://~i', $value)) {
        return $value;
    }
    return $fallback;
}

function mapAnimeItems(array $items, int $limit = 10): array
{
    $mapped = [];
    $seen = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $entry = $item['entry'] ?? $item['anime'] ?? $item;
        if (!is_array($entry)) {
            continue;
        }

        $title = firstNonEmpty([
            $entry['title'] ?? '',
            $entry['title_english'] ?? '',
            $entry['title_japanese'] ?? '',
        ]);

        $image = safeUrl(
            $entry['images']['jpg']['image_url']
            ?? $entry['images']['webp']['image_url']
            ?? $entry['images']['jpg']['large_image_url']
            ?? ''
        );

        $url = safeUrl($entry['url'] ?? '', '#');

        if ($title === '' || $image === '') {
            continue;
        }

        $key = mb_strtolower($title) . '|' . $image;
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        $mapped[] = [
            'title' => $title,
            'image' => $image,
            'url' => $url,
        ];

        if (count($mapped) >= $limit) {
            break;
        }
    }

    return $mapped;
}

try {
    $fixedUsername = 'CartaQliphoth-UT';

    $userJson = httpGetJson('https://api.jikan.moe/v4/users/' . rawurlencode($fixedUsername) . '/full');
    $user = $userJson['data'] ?? null;

    if (!is_array($user)) {
        throw new RuntimeException('User profile not found.');
    }

    $profileUrl = safeUrl($user['url'] ?? '', 'https://myanimelist.net/profile/' . rawurlencode($fixedUsername));
    $animeListUrl = 'https://myanimelist.net/animelist/' . rawurlencode($fixedUsername);

    $avatar = safeUrl(
        $user['images']['jpg']['image_url']
        ?? $user['images']['webp']['image_url']
        ?? $user['images']['jpg']['large_image_url']
        ?? ''
    );

    $favorites = mapAnimeItems($user['favorites']['anime'] ?? [], 10);

    $recentAnime = [];
    if (!empty($user['updates']['anime']) && is_array($user['updates']['anime'])) {
        $recentAnime = mapAnimeItems($user['updates']['anime'], 10);
    }

    if (!$recentAnime) {
        $recentAnime = $favorites;
    }

    $hero = $avatar;
    if (!empty($recentAnime[0]['image'])) {
        $hero = $recentAnime[0]['image'];
    } elseif (!empty($favorites[0]['image'])) {
        $hero = $favorites[0]['image'];
    }

    $joined = 'Public profile';
    if (!empty($user['joined'])) {
        $ts = strtotime((string) $user['joined']);
        if ($ts !== false) {
            $joined = date('M d, Y', $ts);
        }
    }

    $about = normalizeText($user['about'] ?? '');
    if ($about === '') {
        $about = 'No public profile text found.';
    }

    respond([
        'ok' => true,
        'name' => (string) ($user['username'] ?? $fixedUsername),
        'status' => 'Public profile available',
        'joined' => $joined,
        'about' => $about,
        'avatar' => $avatar,
        'hero' => $hero,
        'profile_url' => $profileUrl,
        'anime_list_url' => $animeListUrl,
        'stats' => [
            'watching' => (int) ($user['statistics']['anime']['watching'] ?? 0),
            'completed' => (int) ($user['statistics']['anime']['completed'] ?? 0),
            'on_hold' => (int) ($user['statistics']['anime']['on_hold'] ?? 0),
            'dropped' => (int) ($user['statistics']['anime']['dropped'] ?? 0),
            'plan_to_watch' => (int) ($user['statistics']['anime']['plan_to_watch'] ?? 0),
        ],
        'updates' => [
            'anime' => (int) count($recentAnime),
            'manga' => (int) ($user['statistics']['manga']['reading'] ?? 0),
        ],
        'recent_anime' => $recentAnime,
        'favorites' => $favorites,
    ]);
} catch (Throwable $e) {
    respond([
        'ok' => false,
        'message' => $e->getMessage(),
    ], 500);
}