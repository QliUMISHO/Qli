<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300, s-maxage=300, stale-while-revalidate=600');

$username = 'QliUMISHO';
$cacheTtl = 600;
$cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'portfolio_github_' . md5($username) . '.json';

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function githubRequest(string $url, string $token = ''): array
{
    $ch = curl_init($url);

    $headers = [
        'Accept: application/vnd.github+json',
        'User-Agent: QliUMISHO-Portfolio',
        'X-GitHub-Api-Version: 2026-03-10',
    ];

    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($ch);

    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('GitHub request failed: ' . $error);
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerText = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    $responseHeaders = [];
    foreach (explode("\r\n", $headerText) as $line) {
        if (strpos($line, ':') !== false) {
            [$key, $value] = explode(':', $line, 2);
            $responseHeaders[strtolower(trim($key))] = trim($value);
        }
    }

    $decoded = json_decode($body, true);

    return [
        'status' => $statusCode,
        'headers' => $responseHeaders,
        'body' => $decoded,
        'raw' => $body,
    ];
}

function safeString(mixed $value): string
{
    return is_string($value) ? trim($value) : '';
}

function formatIsoShort(string $iso): string
{
    if ($iso === '') {
        return '';
    }

    try {
        $dt = new DateTimeImmutable($iso);
        return $dt->format('M d, Y');
    } catch (Throwable) {
        return '';
    }
}

$token = getenv('GITHUB_TOKEN');
if (!is_string($token) || $token === '') {
    $token = isset($_SERVER['GITHUB_TOKEN']) && is_string($_SERVER['GITHUB_TOKEN']) ? $_SERVER['GITHUB_TOKEN'] : '';
}

if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile) < $cacheTtl)) {
    $cached = file_get_contents($cacheFile);
    if ($cached !== false && $cached !== '') {
        echo $cached;
        exit;
    }
}

try {
    $userResponse = githubRequest('https://api.github.com/users/' . rawurlencode($username), $token);
    $repoResponse = githubRequest(
        'https://api.github.com/users/' . rawurlencode($username) . '/repos?per_page=100&sort=updated',
        $token
    );

    if ($userResponse['status'] >= 400) {
        throw new RuntimeException('GitHub user API returned status ' . $userResponse['status']);
    }

    if ($repoResponse['status'] >= 400) {
        throw new RuntimeException('GitHub repo API returned status ' . $repoResponse['status']);
    }

    $user = is_array($userResponse['body']) ? $userResponse['body'] : [];
    $repos = is_array($repoResponse['body']) ? $repoResponse['body'] : [];

    $publicRepos = [];
    $languageCounts = [];
    $totalStars = 0;
    $totalForks = 0;
    $totalWatchers = 0;

    foreach ($repos as $repo) {
        if (!is_array($repo)) {
            continue;
        }

        if (($repo['private'] ?? false) === true) {
            continue;
        }

        $language = safeString($repo['language'] ?? '');
        if ($language !== '') {
            $languageCounts[$language] = ($languageCounts[$language] ?? 0) + 1;
        }

        $stars = (int) ($repo['stargazers_count'] ?? 0);
        $forks = (int) ($repo['forks_count'] ?? 0);
        $watchers = (int) ($repo['watchers_count'] ?? 0);

        $totalStars += $stars;
        $totalForks += $forks;
        $totalWatchers += $watchers;

        $publicRepos[] = [
            'name' => safeString($repo['name'] ?? ''),
            'description' => safeString($repo['description'] ?? '') !== '' ? safeString($repo['description'] ?? '') : 'No description provided.',
            'url' => safeString($repo['html_url'] ?? ''),
            'homepage' => safeString($repo['homepage'] ?? ''),
            'language' => $language !== '' ? $language : 'Unspecified',
            'stars' => $stars,
            'forks' => $forks,
            'watchers' => $watchers,
            'updatedAt' => formatIsoShort(safeString($repo['updated_at'] ?? '')),
            'updatedAtRaw' => safeString($repo['updated_at'] ?? ''),
            'isFork' => (bool) ($repo['fork'] ?? false),
        ];
    }

    usort($publicRepos, static function (array $a, array $b): int {
        $dateComparison = strcmp($b['updatedAtRaw'], $a['updatedAtRaw']);
        if ($dateComparison !== 0) {
            return $dateComparison;
        }

        $starComparison = $b['stars'] <=> $a['stars'];
        if ($starComparison !== 0) {
            return $starComparison;
        }

        return strcmp($a['name'], $b['name']);
    });

    arsort($languageCounts);

    $techStack = [];
    foreach ($languageCounts as $name => $count) {
        $techStack[] = [
            'name' => $name,
            'count' => $count,
        ];
    }

    $payload = [
        'ok' => true,
        'source' => 'github-rest-api',
        'authenticated' => $token !== '',
        'fetchedAt' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
        'profile' => [
            'name' => safeString($user['name'] ?? 'Raymond A. Auditor'),
            'login' => safeString($user['login'] ?? $username),
            'avatarUrl' => safeString($user['avatar_url'] ?? ''),
            'profileUrl' => safeString($user['html_url'] ?? 'https://github.com/' . $username),
            'bio' => safeString($user['bio'] ?? ''),
            'followers' => (int) ($user['followers'] ?? 0),
            'following' => (int) ($user['following'] ?? 0),
            'publicRepos' => (int) ($user['public_repos'] ?? count($publicRepos)),
        ],
        'stats' => [
            'publicRepos' => (int) ($user['public_repos'] ?? count($publicRepos)),
            'followers' => (int) ($user['followers'] ?? 0),
            'following' => (int) ($user['following'] ?? 0),
            'totalStars' => $totalStars,
            'totalForks' => $totalForks,
            'totalWatchers' => $totalWatchers,
        ],
        'techStack' => $techStack,
        'repos' => array_slice($publicRepos, 0, 9),
    ];

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Failed to encode response.');
    }

    @file_put_contents($cacheFile, $json);
    echo $json;
    exit;
} catch (Throwable $e) {
    if (is_file($cacheFile)) {
        $cached = file_get_contents($cacheFile);
        if ($cached !== false && $cached !== '') {
            echo $cached;
            exit;
        }
    }

    respond(500, [
        'ok' => false,
        'message' => 'Unable to load GitHub data right now.',
        'error' => $e->getMessage(),
    ]);
}