<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

$username  = 'QliUMISHO';
$userAgent = 'QliPortfolio/1.0';

function jsonResponse(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function httpGet(string $url, string $userAgent): string
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
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8',
            'User-Agent: ' . $userAgent,
            'Referer: https://github.com/',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $error !== '') {
        throw new RuntimeException('Request failed: ' . $error);
    }

    if ($status >= 400) {
        throw new RuntimeException('HTTP error ' . $status . ' for ' . $url);
    }

    return (string) $response;
}

function httpGetJson(string $url, string $userAgent): array
{
    $raw  = httpGet($url, $userAgent);
    $json = json_decode($raw, true);

    if (!is_array($json)) {
        throw new RuntimeException('Invalid JSON from ' . $url);
    }

    return $json;
}

function fetchGithubProfile(string $username, string $userAgent): array
{
    return httpGetJson('https://api.github.com/users/' . rawurlencode($username), $userAgent);
}

function fetchGithubRepos(string $username, string $userAgent): array
{
    $repos = httpGetJson(
        'https://api.github.com/users/' . rawurlencode($username) . '/repos?sort=updated&per_page=12&type=owner',
        $userAgent
    );

    return array_values(array_filter($repos, static function ($repo): bool {
        return is_array($repo) && !($repo['fork'] ?? false);
    }));
}

function fetchRepoLanguages(string $languagesUrl, string $userAgent): array
{
    try {
        $data = httpGetJson($languagesUrl, $userAgent);
        return is_array($data) ? $data : [];
    } catch (Throwable $e) {
        return [];
    }
}

function buildStack(array $repos, string $userAgent): array
{
    $totals = [];

    foreach ($repos as $repo) {
        if (!is_array($repo) || empty($repo['languages_url'])) {
            continue;
        }

        $languages = fetchRepoLanguages((string) $repo['languages_url'], $userAgent);

        foreach ($languages as $language => $bytes) {
            if (!is_string($language) || !is_numeric($bytes)) {
                continue;
            }

            if (!isset($totals[$language])) {
                $totals[$language] = 0;
            }

            $totals[$language] += (int) $bytes;
        }
    }

    arsort($totals);
    $sum = array_sum($totals);

    if ($sum <= 0) {
        return [];
    }

    $stack = [];
    foreach ($totals as $language => $bytes) {
        $stack[] = [
            'name'    => $language,
            'bytes'   => $bytes,
            'percent' => round(($bytes / $sum) * 100, 1),
        ];
    }

    return array_slice($stack, 0, 6);
}

function parseCountFromAriaLabel(string $ariaLabel): int
{
    $ariaLabel = trim($ariaLabel);

    if ($ariaLabel === '') {
        return 0;
    }

    if (stripos($ariaLabel, 'No contributions') !== false) {
        return 0;
    }

    if (preg_match('/(\d+)\s+contribution/i', $ariaLabel, $m)) {
        return (int) $m[1];
    }

    return 0;
}

function normalizeDayLevel(int $count, int $maxCount): int
{
    if ($count <= 0) {
        return 0;
    }

    if ($maxCount <= 1) {
        return 4;
    }

    $ratio = $count / $maxCount;

    if ($ratio <= 0.25) {
        return 1;
    }
    if ($ratio <= 0.50) {
        return 2;
    }
    if ($ratio <= 0.75) {
        return 3;
    }

    return 4;
}

function parseContributionMarkup(string $html, int $year): array
{
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

    if (!$loaded) {
        return [
            'year'  => $year,
            'total' => 0,
            'days'  => [],
        ];
    }

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//*[@data-date]');

    $daysMap = [];
    $maxCount = 0;

    if ($nodes instanceof DOMNodeList) {
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $date = trim((string) $node->getAttribute('data-date'));
            if ($date === '' || strpos($date, $year . '-') !== 0) {
                continue;
            }

            $countAttr = trim((string) $node->getAttribute('data-count'));
            $levelAttr = trim((string) $node->getAttribute('data-level'));
            $ariaLabel = trim((string) $node->getAttribute('aria-label'));

            $count = 0;
            if ($countAttr !== '' && is_numeric($countAttr)) {
                $count = (int) $countAttr;
            } else {
                $count = parseCountFromAriaLabel($ariaLabel);
            }

            $level = null;
            if ($levelAttr !== '' && is_numeric($levelAttr)) {
                $level = (int) $levelAttr;
            }

            if ($count <= 0) {
                $level = 0;
            }

            $daysMap[$date] = [
                'date'  => $date,
                'count' => $count,
                'level' => $level,
            ];

            if ($count > $maxCount) {
                $maxCount = $count;
            }
        }
    }

    ksort($daysMap);
    $days = array_values($daysMap);

    foreach ($days as &$day) {
        if ((int) $day['count'] <= 0) {
            $day['level'] = 0;
            continue;
        }

        if (!is_int($day['level']) || $day['level'] < 0 || $day['level'] > 4) {
            $day['level'] = normalizeDayLevel((int) $day['count'], $maxCount);
        } else {
            $day['level'] = max(0, min(4, (int) $day['level']));
        }
    }
    unset($day);

    return [
        'year'  => $year,
        'total' => array_sum(array_column($days, 'count')),
        'days'  => $days,
    ];
}

function fetchContributionYear(string $username, string $userAgent, int $year): array
{
    $from = sprintf('%d-01-01', $year);
    $to   = sprintf('%d-12-31', $year);

    $urls = [
        'https://github.com/users/' . rawurlencode($username) . '/contributions?from=' . $from . '&to=' . $to,
        'https://github.com/' . rawurlencode($username) . '?tab=overview&from=' . $from . '&to=' . $to,
    ];

    $best = [
        'year'  => $year,
        'total' => 0,
        'days'  => [],
    ];

    foreach ($urls as $url) {
        try {
            $html = httpGet($url, $userAgent);
            $parsed = parseContributionMarkup($html, $year);

            if (count($parsed['days']) > count($best['days'])) {
                $best = $parsed;
            }

            if (!empty($parsed['days'])) {
                return $parsed;
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    return $best;
}

try {
    $profile = fetchGithubProfile($username, $userAgent);
    $repos   = fetchGithubRepos($username, $userAgent);
    $stack   = buildStack($repos, $userAgent);

    $currentYear = (int) date('Y');
    $years = [];
    $contributionsByYear = [];

    for ($year = $currentYear; $year >= ($currentYear - 5); $year--) {
        $yearData = fetchContributionYear($username, $userAgent, $year);
        $years[] = $year;
        $contributionsByYear[(string) $year] = $yearData;
    }

    $defaultYear = (string) $currentYear;
    foreach ($years as $year) {
        if (!empty($contributionsByYear[(string) $year]['days'])) {
            $defaultYear = (string) $year;
            break;
        }
    }

    $repoPayload = array_map(static function (array $repo): array {
        return [
            'name'        => (string) ($repo['name'] ?? ''),
            'html_url'    => (string) ($repo['html_url'] ?? ''),
            'description' => (string) ($repo['description'] ?? ''),
            'language'    => (string) ($repo['language'] ?? ''),
            'updated_at'  => (string) ($repo['updated_at'] ?? ''),
        ];
    }, array_slice($repos, 0, 9));

    jsonResponse([
        'ok' => true,
        'profile' => [
            'avatarUrl' => (string) ($profile['avatar_url'] ?? ''),
            'htmlUrl'   => (string) ($profile['html_url'] ?? ''),
        ],
        'stats' => [
            'publicRepos' => (int) ($profile['public_repos'] ?? 0),
            'followers'   => (int) ($profile['followers'] ?? 0),
        ],
        'stack' => $stack,
        'repos' => $repoPayload,
        'contribution_years' => array_map('strval', $years),
        'default_contribution_year' => $defaultYear,
        'contributions_by_year' => $contributionsByYear,
        'contributions' => $contributionsByYear[$defaultYear] ?? [
            'year'  => (int) $defaultYear,
            'total' => 0,
            'days'  => [],
        ],
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'ok' => false,
        'message' => $e->getMessage(),
    ], 500);
}