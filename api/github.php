<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

$username  = 'QliUMISHO';
$userAgent = 'QliPortfolio/1.0';
$githubToken = trim((string) ($_ENV['GITHUB_TOKEN'] ?? getenv('GITHUB_TOKEN') ?: ''));

function jsonResponse(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function httpGet(string $url, string $userAgent, array $extraHeaders = []): string
{
    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException('Failed to initialize cURL.');
    }

    $headers = array_merge([
        'Accept: application/json',
        'User-Agent: ' . $userAgent,
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ], $extraHeaders);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
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

function httpPostJson(string $url, array $payload, string $userAgent, array $extraHeaders = []): array
{
    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException('Failed to initialize cURL.');
    }

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonPayload === false) {
        throw new RuntimeException('Failed to encode JSON payload.');
    }

    $headers = array_merge([
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: ' . $userAgent,
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ], $extraHeaders);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $error !== '') {
        throw new RuntimeException('Request failed: ' . $error);
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid JSON from ' . $url);
    }

    if ($status >= 400) {
        $message = 'HTTP error ' . $status . ' for ' . $url;
        if (!empty($decoded['message']) && is_string($decoded['message'])) {
            $message .= ': ' . $decoded['message'];
        }
        throw new RuntimeException($message);
    }

    return $decoded;
}

function httpGetJson(string $url, string $userAgent, array $extraHeaders = []): array
{
    $raw  = httpGet($url, $userAgent, $extraHeaders);
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

function fetchContributionYearGraphQL(string $username, string $userAgent, string $token, int $year): array
{
    if ($token === '') {
        throw new RuntimeException('Missing GITHUB_TOKEN environment variable.');
    }

    $from = sprintf('%d-01-01T00:00:00Z', $year);
    $to   = sprintf('%d-12-31T23:59:59Z', $year);

    $query = <<<'GRAPHQL'
query($login: String!, $from: DateTime!, $to: DateTime!) {
  user(login: $login) {
    contributionsCollection(from: $from, to: $to) {
      contributionCalendar {
        totalContributions
        weeks {
          contributionDays {
            date
            contributionCount
          }
        }
      }
    }
  }
}
GRAPHQL;

    $result = httpPostJson(
        'https://api.github.com/graphql',
        [
            'query' => $query,
            'variables' => [
                'login' => $username,
                'from'  => $from,
                'to'    => $to,
            ],
        ],
        $userAgent,
        ['Authorization: Bearer ' . $token]
    );

    if (!empty($result['errors']) && is_array($result['errors'])) {
        $messages = [];
        foreach ($result['errors'] as $error) {
            if (is_array($error) && !empty($error['message']) && is_string($error['message'])) {
                $messages[] = $error['message'];
            }
        }

        throw new RuntimeException(
            'GitHub GraphQL error: ' . ($messages ? implode('; ', $messages) : 'Unknown error')
        );
    }

    $calendar = $result['data']['user']['contributionsCollection']['contributionCalendar'] ?? null;
    if (!is_array($calendar)) {
        throw new RuntimeException('GitHub GraphQL returned no contribution calendar.');
    }

    $days = [];
    $maxCount = 0;

    $weeks = $calendar['weeks'] ?? [];
    if (is_array($weeks)) {
        foreach ($weeks as $week) {
            if (!is_array($week) || !isset($week['contributionDays']) || !is_array($week['contributionDays'])) {
                continue;
            }

            foreach ($week['contributionDays'] as $day) {
                if (!is_array($day)) {
                    continue;
                }

                $date = (string) ($day['date'] ?? '');
                $count = (int) ($day['contributionCount'] ?? 0);

                if ($date === '' || strpos($date, $year . '-') !== 0) {
                    continue;
                }

                if ($count > $maxCount) {
                    $maxCount = $count;
                }

                $days[] = [
                    'date'  => $date,
                    'count' => $count,
                    'level' => 0,
                ];
            }
        }
    }

    usort($days, static function (array $a, array $b): int {
        return strcmp((string) $a['date'], (string) $b['date']);
    });

    foreach ($days as &$day) {
        $day['level'] = normalizeDayLevel((int) $day['count'], $maxCount);
    }
    unset($day);

    return [
        'year'  => $year,
        'total' => (int) ($calendar['totalContributions'] ?? array_sum(array_column($days, 'count'))),
        'days'  => $days,
    ];
}

try {
    $profile = fetchGithubProfile($username, $userAgent);
    $repos   = fetchGithubRepos($username, $userAgent);
    $stack   = buildStack($repos, $userAgent);

    $currentYear = (int) gmdate('Y');
    $years = [];
    $contributionsByYear = [];

    for ($year = $currentYear; $year >= ($currentYear - 5); $year--) {
        $yearData = fetchContributionYearGraphQL($username, $userAgent, $githubToken, $year);
        $years[] = $year;
        $contributionsByYear[(string) $year] = $yearData;
    }

    $defaultYear = (string) $currentYear;
    foreach ($years as $year) {
        if (
            isset($contributionsByYear[(string) $year]['total']) &&
            (int) $contributionsByYear[(string) $year]['total'] > 0
        ) {
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