<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
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

function textOrNull(?DOMNode $node): ?string
{
    if (!$node) {
        return null;
    }

    $text = trim(preg_replace('/\s+/u', ' ', $node->textContent ?? '') ?? '');
    return $text !== '' ? $text : null;
}

function firstXPathValue(DOMXPath $xpath, array $queries): ?string
{
    foreach ($queries as $query) {
        $list = $xpath->query($query);
        if ($list instanceof DOMNodeList && $list->length > 0) {
            $value = textOrNull($list->item(0));
            if ($value !== null) {
                return $value;
            }
        }
    }

    return null;
}

function firstXPathAttr(DOMXPath $xpath, array $queries, string $attr): ?string
{
    foreach ($queries as $query) {
        $list = $xpath->query($query);
        if ($list instanceof DOMNodeList && $list->length > 0) {
            $node = $list->item(0);
            if ($node instanceof DOMElement && $node->hasAttribute($attr)) {
                $value = trim($node->getAttribute($attr));
                if ($value !== '') {
                    return $value;
                }
            }
        }
    }

    return null;
}

function absoluteUrl(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $url)) {
        return $url;
    }

    return 'https://myanimelist.net' . $url;
}

function collectImagesFromHtml(string $html, int $limit = 10): array
{
    preg_match_all('~https://cdn\\.myanimelist\\.net/images/[^\s"\']+~i', $html, $matches);
    $urls = array_values(array_unique($matches[0] ?? []));

    return array_slice($urls, 0, $limit);
}

try {
    $username = trim((string) ($_GET['username'] ?? ''));

    if ($username === '') {
        respond([
            'ok' => false,
            'message' => 'Missing username.'
        ], 400);
    }

    $profileUrl = 'https://myanimelist.net/profile/' . rawurlencode($username);
    $animeListUrl = 'https://myanimelist.net/animelist/' . rawurlencode($username);

    $profileHtml = fetchHtml($profileUrl);
    $animeHtml = fetchHtml($animeListUrl);

    libxml_use_internal_errors(true);

    $profileDom = new DOMDocument();
    $profileDom->loadHTML($profileHtml);
    $profileXPath = new DOMXPath($profileDom);

    $animeDom = new DOMDocument();
    $animeDom->loadHTML($animeHtml);
    $animeXPath = new DOMXPath($animeDom);

    $name = firstXPathValue($profileXPath, [
        '//h1',
        '//span[contains(@class,"user-status-title")]',
        '//title'
    ]) ?? $username;

    $about = firstXPathValue($profileXPath, [
        '//*[@id="about_me"]',
        '//*[contains(@class,"profile-about-user")]',
        '//div[contains(@class,"user-profile-about")]'
    ]) ?? 'No public profile text found.';

    $avatar = firstXPathAttr($profileXPath, [
        '//img[contains(@class,"user-image")]',
        '//img[contains(@class,"lazyload")]',
        '//img[contains(@src,"cdn.myanimelist.net")]'
    ], 'src') ?? '';

    $heroCandidates = collectImagesFromHtml($profileHtml, 8);
    $hero = $heroCandidates[1] ?? ($heroCandidates[0] ?? $avatar);

    $joined = firstXPathValue($profileXPath, [
        '//*[contains(text(),"Joined")]/following-sibling::*[1]',
        '//*[contains(@class,"user-status-data")]'
    ]) ?? 'Public profile';

    $favorites = [];
    foreach (collectImagesFromHtml($profileHtml, 20) as $index => $image) {
        $favorites[] = [
            'title' => 'Favorite ' . ($index + 1),
            'image' => $image,
            'url' => $profileUrl
        ];
        if (count($favorites) >= 10) {
            break;
        }
    }

    $recentAnime = [];
    foreach (collectImagesFromHtml($animeHtml, 20) as $index => $image) {
        $recentAnime[] = [
            'title' => 'Anime Entry ' . ($index + 1),
            'image' => $image,
            'url' => $animeListUrl
        ];
        if (count($recentAnime) >= 10) {
            break;
        }
    }

    $stats = [
        'watching' => 0,
        'completed' => 0,
        'on_hold' => 0,
        'dropped' => 0,
        'plan_to_watch' => 0,
    ];

    $animeText = strip_tags($animeHtml);

    if (preg_match('/Watching\s+([0-9,]+)/i', $animeText, $m)) {
        $stats['watching'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/Completed\s+([0-9,]+)/i', $animeText, $m)) {
        $stats['completed'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/On-Hold\s+([0-9,]+)/i', $animeText, $m) || preg_match('/On Hold\s+([0-9,]+)/i', $animeText, $m)) {
        $stats['on_hold'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/Dropped\s+([0-9,]+)/i', $animeText, $m)) {
        $stats['dropped'] = (int) str_replace(',', '', $m[1]);
    }
    if (preg_match('/Plan to Watch\s+([0-9,]+)/i', $animeText, $m)) {
        $stats['plan_to_watch'] = (int) str_replace(',', '', $m[1]);
    }

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
        'avatar' => absoluteUrl($avatar),
        'hero' => absoluteUrl($hero),
        'profile_url' => $profileUrl,
        'anime_list_url' => $animeListUrl,
        'stats' => $stats,
        'updates' => $updates,
        'recent_anime' => array_map(static function (array $item): array {
            $item['image'] = absoluteUrl((string) ($item['image'] ?? ''));
            return $item;
        }, $recentAnime),
        'favorites' => array_map(static function (array $item): array {
            $item['image'] = absoluteUrl((string) ($item['image'] ?? ''));
            return $item;
        }, $favorites),
    ]);
} catch (Throwable $e) {
    respond([
        'ok' => false,
        'message' => $e->getMessage(),
    ], 500);
}