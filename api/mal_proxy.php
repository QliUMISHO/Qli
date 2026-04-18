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

function fetchHtml(string $url): string
{
    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException('Failed to initialize cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
        ],
    ]);

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $error !== '') {
        throw new RuntimeException('Request failed: ' . $error);
    }

    if ($status >= 400) {
        throw new RuntimeException('MAL returned HTTP ' . $status);
    }

    return (string) $body;
}

function htmlDecode(string $value): string
{
    return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function normalizeText(string $value): string
{
    $value = strip_tags($value);
    $value = htmlDecode($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return trim($value);
}

function absoluteUrl(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $url)) {
        return $url;
    }

    if (strpos($url, '//') === 0) {
        return 'https:' . $url;
    }

    if ($url[0] === '/') {
        return 'https://myanimelist.net' . $url;
    }

    return 'https://myanimelist.net/' . ltrim($url, '/');
}

function collectImagesFromHtml(string $html, int $limit = 10): array
{
    preg_match_all('~https://cdn\.myanimelist\.net/images/[^\s"\']+~i', $html, $matches);
    $urls = array_values(array_unique($matches[0] ?? []));

    return array_slice($urls, 0, $limit);
}

function firstMatch(string $html, array $patterns): ?string
{
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $value = $matches[1] ?? '';
            $value = normalizeText((string) $value);
            if ($value !== '') {
                return $value;
            }
        }
    }

    return null;
}

function firstImage(string $html, array $patterns): ?string
{
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $value = trim((string) ($matches[1] ?? ''));
            if ($value !== '') {
                return absoluteUrl($value);
            }
        }
    }

    return null;
}

function collectCoverCards(string $html, string $fallbackUrl, int $limit = 10): array
{
    $items = [];

    $patterns = [
        '~<a[^>]+href="([^"]+)"[^>]*>.*?<img[^>]+(?:data-src|src)="([^"]+)"[^>]+alt="([^"]+)"[^>]*>.*?</a>~isu',
        "~<a[^>]+href='([^']+)'[^>]*>.*?<img[^>]+(?:data-src|src)='([^']+)'[^>]+alt='([^']+)'[^>]*>.*?</a>~isu",
    ];

    foreach ($patterns as $pattern) {
        if (!preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            continue;
        }

        foreach ($matches as $match) {
            $url = absoluteUrl((string) ($match[1] ?? $fallbackUrl));
            $image = absoluteUrl((string) ($match[2] ?? ''));
            $title = normalizeText((string) ($match[3] ?? ''));

            if ($image === '' || $title === '') {
                continue;
            }

            $key = $title . '|' . $image;
            $items[$key] = [
                'title' => $title,
                'image' => $image,
                'url' => $url !== '' ? $url : $fallbackUrl,
            ];

            if (count($items) >= $limit) {
                break 2;
            }
        }
    }

    if (!$items) {
        foreach (collectImagesFromHtml($html, $limit) as $index => $image) {
            $items[] = [
                'title' => 'Entry ' . ($index + 1),
                'image' => absoluteUrl($image),
                'url' => $fallbackUrl,
            ];
        }
    }

    return array_values(array_slice($items, 0, $limit));
}

function statFromText(string $text, string $label): int
{
    $pattern = '/' . preg_quote($label, '/') . '\s+([0-9,]+)/i';
    if (preg_match($pattern, $text, $matches)) {
        return (int) str_replace(',', '', (string) $matches[1]);
    }

    return 0;
}

try {
    $username = trim((string) ($_GET['username'] ?? ''));

    if ($username === '') {
        respond([
            'ok' => false,
            'message' => 'Missing username.',
        ], 400);
    }

    $profileUrl = 'https://myanimelist.net/profile/' . rawurlencode($username);
    $animeListUrl = 'https://myanimelist.net/animelist/' . rawurlencode($username);

    $profileHtml = fetchHtml($profileUrl);
    $animeHtml = fetchHtml($animeListUrl);

    $name = firstMatch($profileHtml, [
        '~<h1[^>]*>(.*?)</h1>~isu',
        '~<title>(.*?)</title>~isu',
    ]) ?? $username;

    if (stripos($name, ' - MyAnimeList.net') !== false) {
        $name = trim(str_ireplace(' - MyAnimeList.net', '', $name));
    }

    $about = firstMatch($profileHtml, [
        '~id="about_me"[^>]*>(.*?)</(?:div|td|section)>~isu',
        '~class="[^"]*profile-about-user[^"]*"[^>]*>(.*?)</(?:div|td|section)>~isu',
        "~class='[^']*profile-about-user[^']*'[^>]*>(.*?)</(?:div|td|section)>~isu",
        '~<meta\s+name="description"\s+content="([^"]+)"~isu',
    ]) ?? 'No public profile text found.';

    $avatar = firstImage($profileHtml, [
        '~<img[^>]+class="[^"]*user-image[^"]*"[^>]+(?:data-src|src)="([^"]+)"~isu',
        "~<img[^>]+class='[^']*user-image[^']*'[^>]+(?:data-src|src)='([^']+)'~isu",
        '~<meta\s+property="og:image"\s+content="([^"]+)"~isu',
    ]) ?? '';

    $heroCandidates = collectImagesFromHtml($profileHtml, 8);
    $hero = absoluteUrl((string) ($heroCandidates[1] ?? ($heroCandidates[0] ?? $avatar)));

    $joined = firstMatch($profileHtml, [
        '~Joined[^<]{0,40}</span>\s*<[^>]+>\s*(.*?)\s*</~isu',
        '~Joined\s*</span>\s*<span[^>]*>(.*?)</span>~isu',
        '~Joined\s*:\s*([^<\n\r]+)~isu',
    ]) ?? 'Public profile';

    $favorites = collectCoverCards($profileHtml, $profileUrl, 10);
    $recentAnime = collectCoverCards($animeHtml, $animeListUrl, 10);

    $animeText = normalizeText($animeHtml);

    $stats = [
        'watching' => statFromText($animeText, 'Watching'),
        'completed' => statFromText($animeText, 'Completed'),
        'on_hold' => max(statFromText($animeText, 'On-Hold'), statFromText($animeText, 'On Hold')),
        'dropped' => statFromText($animeText, 'Dropped'),
        'plan_to_watch' => statFromText($animeText, 'Plan to Watch'),
    ];

    $updates = [
        'anime' => count($recentAnime),
        'manga' => 0,
    ];

    respond([
        'ok' => true,
        'name' => $name,
        'status' => 'Active',
        'joined' => $joined,
        'about' => $about,
        'avatar' => $avatar,
        'hero' => $hero,
        'profile_url' => $profileUrl,
        'anime_list_url' => $animeListUrl,
        'stats' => $stats,
        'updates' => $updates,
        'recent_anime' => $recentAnime,
        'favorites' => $favorites,
    ]);
} catch (Throwable $e) {
    respond([
        'ok' => false,
        'message' => $e->getMessage(),
    ], 500);
}