<?php
declare(strict_types=1);

$basePath = '';
$cssVersion = file_exists(__DIR__ . '/../assets/style.css') ? (string) filemtime(__DIR__ . '/../assets/style.css') : (string) time();
$jsVersion  = file_exists(__DIR__ . '/../assets/app.js') ? (string) filemtime(__DIR__ . '/../assets/app.js') : (string) time();

$profile = [
    'username'     => 'QliUMISHO',
    'personalName' => 'Raymond A. Auditor',
    'codename'     => 'Qliphoth',
    'github'       => 'https://github.com/QliUMISHO',
    'facebook'     => 'https://www.facebook.com/NPCno666v2',
    'email'        => 'mailto:you@example.com',
];

$mal = [
    'username'      => 'QliUMISHO',
    'client_id'     => getenv('MAL_CLIENT_ID') ?: 'SET_IN_SERVER_ENV',
    'client_secret' => getenv('MAL_CLIENT_SECRET') ?: 'SET_IN_SERVER_ENV',
];

function maskSecret(string $value): string
{
    if ($value === '' || $value === 'SET_IN_SERVER_ENV') {
        return 'SET_IN_SERVER_ENV';
    }

    $len = strlen($value);

    if ($len <= 8) {
        return str_repeat('*', $len);
    }

    return substr($value, 0, 4) . str_repeat('*', max(4, $len - 8)) . substr($value, -4);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['personalName'], ENT_QUOTES, 'UTF-8') ?> | Portfolio</title>
    <meta name="description" content="Editorial portfolio powered by PHP and GitHub live data">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/style.css?v=<?= htmlspecialchars($cssVersion, ENT_QUOTES, 'UTF-8') ?>">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('qli-theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-theme', saved);
                }
            } catch (e) {}
        })();
    </script>
</head>
<body>
    <div class="qli-startup" id="qliStartup" aria-hidden="true">
        <div class="qli-startup-bg"></div>
        <div class="qli-startup-grid"></div>

        <div class="qli-startup-center">
            <div class="qli-startup-kicker">PORTFOLIO EXPERIENCE</div>

            <div class="qli-startup-title-wrap">
                <div class="qli-startup-title-line">Qli // Portfolio</div>
                <div class="qli-startup-title-glow"></div>
            </div>

            <div class="qli-startup-subtitle">Initializing interface, style, and live GitHub data.</div>

            <div class="qli-startup-progress">
                <div class="qli-startup-progress-bar" id="qliStartupBar"></div>
            </div>

            <div class="qli-startup-status" id="qliStartupStatus">Loading experience...</div>
        </div>
    </div>

    <div class="qli-page-shell qli-page-preload" id="qliPageShell">
        <div id="page" data-route="a">
            <div class="loader" id="qliRouteLoader"></div>

            <div class="container">
                <section class="a">
                    <div class="qli-shell">
                        <div class="qli-bg-grid"></div>

                        <div class="qli-container">
                            <header class="qli-topbar">
                                <button class="qli-brand qli-brand-button" type="button" data-route-target="b" aria-label="Open anime profile view">
                                    <div class="qli-brand-kicker">DEV PORTFOLIO</div>
                                    <div class="qli-brand-name">Qli // Portfolio Website</div>
                                </button>

                                <div class="qli-topbar-right">
                                    <nav class="qli-nav">
                                        <a href="#intro">Home</a>
                                        <a href="#about">About</a>
                                        <a href="#stack">Stack</a>
                                        <a href="#contributions">Contributions</a>
                                        <a href="#repos">Repos</a>
                                    </nav>

                                    <button class="qli-theme-toggle" id="qliThemeToggle" type="button" aria-label="Toggle theme">
                                        <span class="qli-theme-toggle-track">
                                            <span class="qli-theme-toggle-thumb"></span>
                                        </span>
                                        <span class="qli-theme-toggle-label" id="qliThemeToggleLabel">Dark mode</span>
                                    </button>
                                </div>
                            </header>

                            <section class="qli-hero qli-divider" id="intro">
                                <div class="qli-hero-main">
                                    <div class="qli-hero-kicker">To create clarity, structure, and identity.</div>
                                    <h1 class="qli-hero-title">Designing systems,<br>clean and stylish.</h1>
                                    <p class="qli-hero-copy">
                                        A developer portfolio made by yours truly shaped with an editorial layout, live GitHub data,
                                        and a nice, stylish look.
                                    </p>
                                </div>
                            </section>

                            <section class="qli-profile-band qli-divider">
                                <div class="qli-profile-grid">
                                    <div class="qli-profile-left">
                                        <div class="qli-avatar-wrap">
                                            <div class="qli-avatar-ring"></div>
                                            <div class="qli-avatar-image" id="qliAvatarImage"></div>
                                        </div>

                                        <div class="qli-link-row qli-link-row-under-avatar">
                                            <div class="qli-link-square" data-link="<?= htmlspecialchars($profile['github'], ENT_QUOTES, 'UTF-8') ?>" title="GitHub" aria-label="GitHub">
                                                <svg class="qli-link-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M12 .5C5.65.5.5 5.65.5 12A11.5 11.5 0 0 0 8.36 22.92c.57.1.78-.24.78-.55v-1.94c-3.2.7-3.88-1.36-3.88-1.36-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.7.08-.7 1.16.08 1.78 1.19 1.78 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.74.4-1.26.72-1.55-2.55-.29-5.24-1.27-5.24-5.67 0-1.25.45-2.27 1.18-3.07-.12-.29-.51-1.46.11-3.05 0 0 .97-.31 3.18 1.17a10.94 10.94 0 0 1 5.79 0c2.2-1.48 3.17-1.17 3.17-1.17.63 1.59.24 2.76.12 3.05.74.8 1.18 1.82 1.18 3.07 0 4.41-2.7 5.37-5.27 5.66.41.36.78 1.06.78 2.14v3.17c0 .31.2.66.79.55A11.5 11.5 0 0 0 23.5 12C23.5 5.65 18.35.5 12 .5Z"/>
                                                </svg>
                                            </div>

                                            <div class="qli-link-square" data-link="<?= htmlspecialchars($profile['facebook'], ENT_QUOTES, 'UTF-8') ?>" title="Facebook" aria-label="Facebook">
                                                <svg class="qli-link-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07c0 6.03 4.39 11.03 10.13 11.93v-8.43H7.08v-3.5h3.05V9.41c0-3.03 1.79-4.7 4.54-4.7 1.32 0 2.7.24 2.7.24v2.97h-1.52c-1.5 0-1.97.94-1.97 1.9v2.28h3.35l-.54 3.5h-2.81V24C19.61 23.1 24 18.1 24 12.07Z"/>
                                                </svg>
                                            </div>

                                            <div class="qli-link-square" data-link="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>" title="Email" aria-label="Email">
                                                <svg class="qli-link-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M3 5.5h18A1.5 1.5 0 0 1 22.5 7v10A1.5 1.5 0 0 1 21 18.5H3A1.5 1.5 0 0 1 1.5 17V7A1.5 1.5 0 0 1 3 5.5Zm0 2.05V17h18V7.55l-8.37 6.09a1.1 1.1 0 0 1-1.26 0L3 7.55Zm1.78-.55L12 12.23 19.22 7H4.78Z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="qli-profile-right">
                                        <div class="qli-name-dots">
                                            <div class="qli-dot-switch active" data-name-value="<?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?>" data-name-label="DEV_NAME" data-show-caret="true"></div>
                                            <div class="qli-dot-switch" data-name-value="<?= htmlspecialchars($profile['personalName'], ENT_QUOTES, 'UTF-8') ?>" data-name-label="TRUE NAME" data-show-caret="false"></div>
                                            <div class="qli-dot-switch" data-name-value="<?= htmlspecialchars($profile['codename'], ENT_QUOTES, 'UTF-8') ?>" data-name-label="ALIAS" data-show-caret="false"></div>
                                        </div>

                                        <div class="qli-name-display">
                                            <div class="qli-name-stage" id="qliNameStage">
                                                <div class="qli-name-value" id="qliNameValueWrap">
                                                    <span id="qliNameValue"><?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="qli-terminal-caret is-visible" id="qliTerminalCaret" aria-hidden="true"></span>
                                                </div>
                                                <div class="qli-name-label" id="qliNameLabel">DEV_NAME</div>
                                            </div>
                                        </div>

                                        <div class="qli-stats-grid">
                                            <div class="qli-stat-box">
                                                <div class="qli-stat-label">Repos</div>
                                                <div class="qli-stat-value" id="qliStatRepos">--</div>
                                            </div>
                                            <div class="qli-stat-box">
                                                <div class="qli-stat-label">Followers</div>
                                                <div class="qli-stat-value" id="qliStatFollowers">--</div>
                                            </div>
                                            <div class="qli-stat-box">
                                                <div class="qli-stat-label">Contributions</div>
                                                <div class="qli-stat-value" id="qliStatContribs">--</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="qli-about qli-divider" id="about">
                                <div class="qli-about-grid">
                                    <div class="qli-about-block">
                                        <div class="qli-section-kicker">( About )</div>
                                        <h2 class="qli-section-title">Building useful systems with a restrained visual approach.</h2>
                                        <p class="qli-section-copy">
                                            a short, concise view of my progress as an assistant developer.
                                        </p>
                                    </div>

                                    <div class="qli-about-block">
                                        <div class="qli-section-kicker">( Profile )</div>
                                        <h2 class="qli-section-title">Coder, Systems Manager, and IT.</h2>
                                        <p class="qli-section-copy">
                                            Working as an assistant developer during downtime and as an L1 IT Support.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="qli-data-grid qli-divider" id="stack">
                                <div class="qli-stack-card">
                                    <div class="qli-section-kicker">STACK</div>
                                    <div class="qli-stack-title">Stack split</div>
                                    <div class="qli-stack-subtitle">from my public repos.</div>

                                    <div class="qli-stack-chart-wrap">
                                        <div class="qli-stack-chart" id="qliStackChart"></div>
                                    </div>

                                    <div class="qli-stack-legend" id="qliStackLegend">
                                        <div class="qli-stack-empty">Loading stack...</div>
                                    </div>
                                </div>

                                <div class="qli-contrib-card" id="contributions">
                                    <div class="qli-section-kicker">CONTRIBUTIONS</div>
                                    <div class="qli-contrib-title">GitHub contribution graph</div>
                                    <div class="qli-contrib-subtitle">heatmap of my GitHub activity</div>

                                    <div class="qli-contrib-total" id="qliContribTotal">Loading contributions...</div>

                                    <div class="qli-contrib-surface">
                                        <div class="qli-heatmap-scroll">
                                            <div class="qli-months-row" id="qliContribMonths"></div>

                                            <div class="qli-heatmap-main">
                                                <div class="qli-heatmap-days">
                                                    <div class="qli-day-label"></div>
                                                    <div class="qli-day-label">Mon</div>
                                                    <div class="qli-day-label"></div>
                                                    <div class="qli-day-label">Wed</div>
                                                    <div class="qli-day-label"></div>
                                                    <div class="qli-day-label">Fri</div>
                                                    <div class="qli-day-label"></div>
                                                </div>

                                                <div class="qli-heatmap-grid" id="qliContribGrid"></div>
                                            </div>
                                        </div>

                                        <div class="qli-heatmap-legend-bar">
                                            <span>Less</span>
                                            <div class="qli-legend-swatch level-0"></div>
                                            <div class="qli-legend-swatch level-1"></div>
                                            <div class="qli-legend-swatch level-2"></div>
                                            <div class="qli-legend-swatch level-3"></div>
                                            <div class="qli-legend-swatch level-4"></div>
                                            <span>More</span>
                                        </div>
                                    </div>

                                    <div class="qli-contrib-years-wrap">
                                        <div class="qli-contrib-years-title">Years</div>
                                        <div class="qli-contrib-years" id="qliContribYears"></div>
                                    </div>
                                </div>
                            </section>

                            <section class="qli-repos-section" id="repos">
                                <div class="qli-section-kicker">( Public Repos )</div>
                                <h2 class="qli-section-title qli-repos-title">Selected public repositories</h2>
                                <p class="qli-section-copy qli-repos-copy">
                                    My latest public repositories from GitHub.
                                </p>

                                <div class="qli-repo-grid" id="qliRepoGrid">
                                    <div class="qli-repo-empty">Loading public repositories...</div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>

                <section class="b">
                    <div class="qli-manga-shell">
                        <header class="qli-manga-topbar">
                            <button class="qli-manga-back" type="button" data-route-target="a">Back</button>
                            <div class="qli-manga-title">Qliphoth-UTP's Profile</div>
                            <div class="qli-manga-actions">
                                <a href="#" target="_blank" rel="noopener noreferrer" id="qliMalProfileLink">About Me Design</a>
                                <a href="#" target="_blank" rel="noopener noreferrer" id="qliMalAnimeListLink">Anime List</a>
                            </div>
                        </header>

                        <div class="qli-manga-layout">
                            <aside class="qli-manga-sidebar">
                                <div class="qli-manga-avatar-panel">
                                    <div class="qli-manga-avatar" id="qliMalAvatar"></div>
                                    <div class="qli-manga-mini-actions">
                                        <a href="<?= htmlspecialchars($profile['github'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">GH</a>
                                        <a href="<?= htmlspecialchars($profile['facebook'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">FB</a>
                                        <a href="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>">Mail</a>
                                    </div>
                                </div>

                                <div class="qli-manga-panel">
                                    <div class="qli-manga-panel-title">User Details</div>
                                    <div class="qli-manga-kv">
                                        <span>Name</span>
                                        <strong id="qliMalName">Loading...</strong>
                                    </div>
                                    <div class="qli-manga-kv">
                                        <span>Status</span>
                                        <strong id="qliMalStatus">Available</strong>
                                    </div>
                                    <div class="qli-manga-kv">
                                        <span>Joined</span>
                                        <strong id="qliMalJoined">Public profile</strong>
                                    </div>
                                </div>

                                <div class="qli-manga-panel">
                                    <div class="qli-manga-panel-title">Links</div>
                                    <a class="qli-manga-navlink active" href="#" data-route-target="b">Anime Profile</a>
                                    <a class="qli-manga-navlink" href="#" id="qliMalExternalProfile" target="_blank" rel="noopener noreferrer">View MAL Page</a>
                                    <a class="qli-manga-navlink" href="#" id="qliMalExternalAnime" target="_blank" rel="noopener noreferrer">View Anime List</a>
                                </div>

                                <div class="qli-manga-panel">
                                    <div class="qli-manga-panel-title">Anime Stats</div>
                                    <div class="qli-manga-statline"><span>Watching</span><strong id="qliMalWatching">0</strong></div>
                                    <div class="qli-manga-statline"><span>Completed</span><strong id="qliMalCompleted">0</strong></div>
                                    <div class="qli-manga-statline"><span>On Hold</span><strong id="qliMalOnHold">0</strong></div>
                                    <div class="qli-manga-statline"><span>Dropped</span><strong id="qliMalDropped">0</strong></div>
                                    <div class="qli-manga-statline"><span>Plan to Watch</span><strong id="qliMalPlan">0</strong></div>
                                </div>
                            </aside>

                            <main class="qli-manga-main">
                                <section class="qli-manga-hero">
                                    <div class="qli-manga-hero-cover" id="qliMalHeroCover"></div>
                                    <div class="qli-manga-hero-overlay">
                                        <div class="qli-manga-hero-badge">blue haired waifs/tsundere enthusiast</div>
                                    </div>
                                </section>

                                <section class="qli-manga-block">
                                    <div class="qli-manga-block-title">Profile Summary</div>
                                    <div class="qli-manga-summary" id="qliMalAbout">
                                        Loading profile summary...
                                    </div>
                                </section>

                                <section class="qli-manga-grid-two">
                                    <div class="qli-manga-block">
                                        <div class="qli-manga-block-title">Recent Anime Titles</div>
                                        <div class="qli-manga-covers" id="qliMalRecentAnime">
                                            <div class="qli-manga-empty">Loading anime covers...</div>
                                        </div>
                                    </div>

                                    <div class="qli-manga-block">
                                        <div class="qli-manga-block-title">Top Favorites</div>
                                        <div class="qli-manga-covers" id="qliMalFavorites">
                                            <div class="qli-manga-empty">Loading favorites...</div>
                                        </div>
                                    </div>
                                </section>

                                <section class="qli-manga-block">
                                    <div class="qli-manga-block-title">API App Configuration</div>
                                    <div class="qli-api-grid">
                                        <div class="qli-api-row">
                                            <div class="qli-api-label">App Type</div>
                                            <div class="qli-api-value">web</div>
                                        </div>
                                        <div class="qli-api-row">
                                            <div class="qli-api-label">Client ID</div>
                                            <div class="qli-api-value"><?= htmlspecialchars($mal['client_id'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="qli-api-row">
                                            <div class="qli-api-label">Client Secret</div>
                                            <div class="qli-api-value"><?= htmlspecialchars(maskSecret($mal['client_secret']), ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="qli-api-note">Client ID and Client Secret should not be disclosed.</div>
                                    </div>
                                </section>

                                <section class="qli-manga-block">
                                    <div class="qli-manga-block-title">Scraped MAL Data</div>
                                    <div class="qli-manga-scrape-grid">
                                        <div class="qli-manga-scrape-item">
                                            <span>Anime Updates</span>
                                            <strong id="qliMalAnimeUpdates">0</strong>
                                        </div>
                                        <div class="qli-manga-scrape-item">
                                            <span>Manga Updates</span>
                                            <strong id="qliMalMangaUpdates">0</strong>
                                        </div>
                                        <div class="qli-manga-scrape-item">
                                            <span>Favorites Found</span>
                                            <strong id="qliMalFavoritesCount">0</strong>
                                        </div>
                                        <div class="qli-manga-scrape-item">
                                            <span>Profile URL</span>
                                            <strong id="qliMalProfileUrlText">Loading...</strong>
                                        </div>
                                    </div>
                                </section>
                            </main>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        window.PORTFOLIO_CONFIG = {
            basePath: <?= json_encode($basePath, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
            profile: {
                username: <?= json_encode($profile['username'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
                personalName: <?= json_encode($profile['personalName'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
                codename: <?= json_encode($profile['codename'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
            },
            mal: {
                username: <?= json_encode($mal['username'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
            }
        };
    </script>
    <script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/app.js?v=<?= htmlspecialchars($jsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>