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
    'username' => 'CartaQliphoth-UT',
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?> | MAL Page</title>
    <meta name="description" content="Editorial MyAnimeList profile page view">
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
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        .qli-mal-page {
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 16% 12%, rgba(126, 166, 216, 0.15), transparent 22%),
                radial-gradient(circle at 84% 10%, rgba(126, 166, 216, 0.10), transparent 20%),
                var(--qli-bg);
            color: var(--qli-ink);
        }

        .qli-mal-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(127,127,127,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(127,127,127,0.03) 1px, transparent 1px);
            background-size: 34px 34px;
            pointer-events: none;
            opacity: 0.22;
        }

        .qli-mal-shell {
            width: min(1320px, calc(100% - 34px));
            margin: 0 auto;
            padding: 18px 0 28px;
            position: relative;
            z-index: 2;
        }

        .qli-mal-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 74px;
            border-bottom: 1px solid var(--qli-line);
            background: color-mix(in srgb, var(--qli-bg) 86%, transparent);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .qli-mal-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .qli-mal-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 72px;
            height: 38px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid var(--qli-line);
            background: var(--qli-paper);
            color: var(--qli-ink);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: transform var(--qli-trans), border-color var(--qli-trans), background var(--qli-trans);
        }

        .qli-mal-back:hover {
            transform: translateY(-2px);
            border-color: var(--qli-accent);
            background: color-mix(in srgb, var(--qli-accent-soft) 40%, var(--qli-paper));
        }

        .qli-mal-title-wrap {
            min-width: 0;
        }

        .qli-mal-kicker {
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--qli-muted);
            font-weight: 800;
        }

        .qli-mal-title {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .qli-mal-topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .qli-mal-topbar-right a {
            color: var(--qli-accent);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .qli-mal-topbar-right a:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .qli-mal-layout {
            display: grid;
            grid-template-columns: 270px minmax(0, 1fr);
            gap: 20px;
            margin-top: 18px;
            align-items: start;
        }

        .qli-mal-sidebar,
        .qli-mal-main {
            display: grid;
            gap: 16px;
        }

        .qli-mal-card {
            background: var(--qli-paper);
            border: 1px solid var(--qli-line);
            border-radius: var(--qli-radius);
            box-shadow: var(--qli-shadow);
            transition: background .25s ease, border-color .25s ease;
        }

        .qli-mal-profile-card {
            padding: 16px;
        }

        .qli-mal-avatar-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 24px;
            overflow: hidden;
            background: color-mix(in srgb, var(--qli-paper-2) 92%, transparent);
            border: 1px solid var(--qli-line);
        }

        .qli-mal-avatar {
            position: absolute;
            inset: 0;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .qli-mal-mini-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .qli-mal-mini-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border-radius: 12px;
            border: 1px solid var(--qli-line);
            background: var(--qli-paper-2);
            color: var(--qli-ink);
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: transform var(--qli-trans), border-color var(--qli-trans), background var(--qli-trans);
        }

        .qli-mal-mini-actions a:hover {
            transform: translateY(-2px);
            border-color: var(--qli-accent);
            background: color-mix(in srgb, var(--qli-accent-soft) 45%, var(--qli-paper-2));
        }

        .qli-mal-side-card {
            padding: 16px 18px;
        }

        .qli-mal-side-title {
            font-size: 11px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--qli-muted);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .qli-mal-kv {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 0;
            border-top: 1px solid var(--qli-line);
        }

        .qli-mal-kv:first-of-type {
            border-top: 0;
        }

        .qli-mal-kv span {
            color: var(--qli-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .qli-mal-kv strong {
            color: var(--qli-ink);
            font-size: 12px;
            font-weight: 800;
            text-align: right;
        }

        .qli-mal-navlink {
            display: block;
            padding: 11px 12px;
            border-radius: 12px;
            border: 1px solid var(--qli-line);
            background: var(--qli-paper-2);
            color: var(--qli-ink);
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: transform var(--qli-trans), border-color var(--qli-trans), background var(--qli-trans);
        }

        .qli-mal-navlink + .qli-mal-navlink {
            margin-top: 8px;
        }

        .qli-mal-navlink:hover,
        .qli-mal-navlink.active {
            transform: translateY(-1px);
            border-color: var(--qli-accent);
            background: color-mix(in srgb, var(--qli-accent-soft) 45%, var(--qli-paper-2));
        }

        .qli-mal-hero {
            position: relative;
            min-height: 290px;
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid var(--qli-line);
            background:
                linear-gradient(180deg, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.72) 100%),
                var(--qli-paper-2);
            background-position: center;
            background-size: cover;
            box-shadow: var(--qli-shadow);
        }

        .qli-mal-hero-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            padding: 28px;
            background: linear-gradient(180deg, rgba(0,0,0,0.04) 0%, rgba(0,0,0,0.72) 100%);
        }

        .qli-mal-hero-copy {
            max-width: 620px;
        }

        .qli-mal-eyebrow {
            font-size: 11px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.82);
            font-weight: 800;
        }

        .qli-mal-hero-title {
            margin: 10px 0 0;
            font-size: clamp(36px, 4vw, 62px);
            line-height: 0.96;
            letter-spacing: -0.05em;
            font-weight: 900;
            color: #ffffff;
        }

        .qli-mal-hero-badge {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 0 14px;
            margin-top: 14px;
            border-radius: 999px;
            border: 1px solid rgba(126,166,216,0.45);
            background: rgba(0,0,0,0.34);
            color: #dff4ff;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .qli-mal-summary-card,
        .qli-mal-stats-card {
            padding: 18px;
        }

        .qli-mal-section-kicker {
            font-size: 10px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--qli-accent);
            font-weight: 800;
        }

        .qli-mal-section-title {
            margin: 8px 0 0;
            font-size: clamp(20px, 2vw, 30px);
            line-height: 1.03;
            letter-spacing: -0.04em;
            font-weight: 900;
        }

        .qli-mal-section-title-tight {
            font-size: 20px;
        }

        .qli-mal-summary {
            margin-top: 12px;
            padding: 16px 18px;
            border-radius: 18px;
            background: color-mix(in srgb, var(--qli-accent-soft) 35%, var(--qli-paper-2));
            border: 1px solid var(--qli-line);
            color: var(--qli-ink);
            font-size: 14px;
            line-height: 1.8;
        }

        .qli-mal-media-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .qli-mal-media-card {
            padding: 18px;
        }

        .qli-mal-covers {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(108px, 1fr));
            gap: 14px;
        }

        .qli-manga-empty {
            grid-column: 1 / -1;
            padding: 16px;
            border-radius: 16px;
            border: 1px dashed var(--qli-line-strong);
            color: var(--qli-muted);
            font-size: 13px;
            text-align: center;
        }

        .qli-manga-cover-card {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .qli-manga-cover-thumb {
            width: 100%;
            aspect-ratio: 3 / 4.3;
            border-radius: 16px;
            border: 1px solid var(--qli-line);
            background-color: var(--qli-paper-2);
            background-position: center;
            background-size: cover;
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
            transition: transform var(--qli-trans), border-color var(--qli-trans);
        }

        .qli-manga-cover-card:hover .qli-manga-cover-thumb {
            transform: translateY(-2px);
            border-color: var(--qli-accent);
        }

        .qli-manga-cover-name {
            font-size: 12px;
            line-height: 1.45;
            color: var(--qli-ink);
            font-weight: 700;
            min-height: 34px;
        }

        .qli-mal-stats-grid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .qli-mal-stat-tile {
            min-height: 94px;
            padding: 14px 14px 12px;
            border-radius: 18px;
            border: 1px solid var(--qli-line);
            background: var(--qli-paper-2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .qli-mal-stat-tile span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--qli-muted);
            font-weight: 800;
        }

        .qli-mal-stat-tile strong {
            margin-top: 10px;
            font-size: 24px;
            line-height: 1;
            letter-spacing: -0.04em;
            font-weight: 900;
            color: var(--qli-ink);
            word-break: break-word;
        }

        .qli-mal-stat-tile-wide {
            grid-column: span 2;
        }

        .qli-mal-stat-tile-wide strong {
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0;
        }

        @media (max-width: 1280px) {
            .qli-mal-stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1100px) {
            .qli-mal-layout {
                grid-template-columns: 1fr;
            }

            .qli-mal-sidebar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                align-items: start;
            }

            .qli-mal-media-grid {
                grid-template-columns: 1fr;
            }

            .qli-mal-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .qli-mal-shell {
                width: min(100% - 20px, 1320px);
            }

            .qli-mal-topbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0 14px;
            }

            .qli-mal-sidebar {
                grid-template-columns: 1fr;
            }

            .qli-mal-hero {
                min-height: 230px;
            }

            .qli-mal-hero-overlay {
                padding: 20px;
            }

            .qli-mal-hero-title {
                font-size: 34px;
            }

            .qli-mal-stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .qli-mal-stat-tile-wide {
                grid-column: span 2;
            }
        }

        @media (max-width: 520px) {
            .qli-mal-topbar-right {
                width: 100%;
            }

            .qli-mal-topbar-right a {
                width: 100%;
            }

            .qli-mal-stats-grid {
                grid-template-columns: 1fr;
            }

            .qli-mal-stat-tile-wide {
                grid-column: span 1;
            }

            .qli-mal-covers {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="qli-mal-page">
        <div class="qli-mal-grid"></div>

        <div class="qli-mal-shell">
            <header class="qli-mal-topbar">
                <div class="qli-mal-topbar-left">
                    <a class="qli-mal-back" href="/">Back</a>
                    <div class="qli-mal-title-wrap">
                        <div class="qli-mal-kicker">Anime / Manga Profile</div>
                        <div class="qli-mal-title">Qliphoth-UTP's Profile</div>
                    </div>
                </div>

                <div class="qli-mal-topbar-right">
                    <a href="#" target="_blank" rel="noopener noreferrer" id="qliMalProfileLink">MAL Profile</a>
                    <a href="#" target="_blank" rel="noopener noreferrer" id="qliMalAnimeListLink">Anime List</a>
                </div>
            </header>

            <div class="qli-mal-layout">
                <aside class="qli-mal-sidebar">
                    <section class="qli-mal-card qli-mal-profile-card">
                        <div class="qli-mal-avatar-wrap">
                            <div class="qli-mal-avatar" id="qliMalAvatar"></div>
                        </div>

                        <div class="qli-mal-mini-actions">
                            <a href="<?= htmlspecialchars($profile['github'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">GH</a>
                            <a href="<?= htmlspecialchars($profile['facebook'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">FB</a>
                            <a href="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>">Mail</a>
                        </div>
                    </section>

                    <section class="qli-mal-card qli-mal-side-card">
                        <div class="qli-mal-side-title">User Details</div>

                        <div class="qli-mal-kv">
                            <span>Name</span>
                            <strong id="qliMalName">Loading.</strong>
                        </div>
                        <div class="qli-mal-kv">
                            <span>Status</span>
                            <strong id="qliMalStatus">Available</strong>
                        </div>
                        <div class="qli-mal-kv">
                            <span>Joined</span>
                            <strong id="qliMalJoined">Public profile</strong>
                        </div>
                    </section>

                    <section class="qli-mal-card qli-mal-side-card">
                        <div class="qli-mal-side-title">Links</div>
                        <a class="qli-mal-navlink active" href="/mal">Anime Profile</a>
                        <a class="qli-mal-navlink" href="#" id="qliMalExternalProfile" target="_blank" rel="noopener noreferrer">View MAL Profile</a>
                        <a class="qli-mal-navlink" href="#" id="qliMalExternalAnime" target="_blank" rel="noopener noreferrer">Open Anime List</a>
                    </section>
                </aside>

                <main class="qli-mal-main">
                    <section class="qli-mal-hero" id="qliMalHeroCover">
                        <div class="qli-mal-hero-overlay">
                            <div class="qli-mal-hero-copy">
                                <div class="qli-mal-eyebrow">MyAnimeList Public View</div>
                                <h1 class="qli-mal-hero-title" id="qliMalHeroTitle">Loading profile.</h1>
                                <div class="qli-mal-hero-badge">blue haired waifs/tsundere enthusiast</div>
                            </div>
                        </div>
                    </section>

                    <section class="qli-mal-card qli-mal-summary-card">
                        <div class="qli-mal-section-kicker">Summary</div>
                        <h2 class="qli-mal-section-title">Profile Summary</h2>
                        <div class="qli-mal-summary" id="qliMalAbout">Loading profile summary.</div>
                    </section>

                    <section class="qli-mal-media-grid">
                        <article class="qli-mal-card qli-mal-media-card">
                            <div class="qli-mal-section-kicker">Anime</div>
                            <h2 class="qli-mal-section-title qli-mal-section-title-tight">Recent Anime Titles</h2>
                            <div class="qli-mal-covers" id="qliMalRecentAnime">
                                <div class="qli-manga-empty">Loading anime covers.</div>
                            </div>
                        </article>

                        <article class="qli-mal-card qli-mal-media-card">
                            <div class="qli-mal-section-kicker">Anime</div>
                            <h2 class="qli-mal-section-title qli-mal-section-title-tight">Top Favorite Anime</h2>
                            <div class="qli-mal-covers" id="qliMalFavorites">
                                <div class="qli-manga-empty">Loading favorites.</div>
                            </div>
                        </article>

                        <article class="qli-mal-card qli-mal-media-card">
                            <div class="qli-mal-section-kicker">Manga</div>
                            <h2 class="qli-mal-section-title qli-mal-section-title-tight">Recent Manga Titles</h2>
                            <div class="qli-mal-covers" id="qliMalRecentManga">
                                <div class="qli-manga-empty">Loading manga covers.</div>
                            </div>
                        </article>

                        <article class="qli-mal-card qli-mal-media-card">
                            <div class="qli-mal-section-kicker">Manga</div>
                            <h2 class="qli-mal-section-title qli-mal-section-title-tight">Top Favorite Manga</h2>
                            <div class="qli-mal-covers" id="qliMalFavoriteManga">
                                <div class="qli-manga-empty">Loading manga favorites.</div>
                            </div>
                        </article>
                    </section>

                    <section class="qli-mal-card qli-mal-stats-card">
                        <div class="qli-mal-section-kicker">Profile Data</div>
                        <h2 class="qli-mal-section-title">MAL Stats Snapshot</h2>

                        <div class="qli-mal-stats-grid">
                            <div class="qli-mal-stat-tile">
                                <span>Watching</span>
                                <strong id="qliMalWatching">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Completed</span>
                                <strong id="qliMalCompleted">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>On Hold</span>
                                <strong id="qliMalOnHold">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Dropped</span>
                                <strong id="qliMalDropped">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Plan to Watch</span>
                                <strong id="qliMalPlan">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Anime Updates</span>
                                <strong id="qliMalAnimeUpdates">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Manga Updates</span>
                                <strong id="qliMalMangaUpdates">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Favorites Found</span>
                                <strong id="qliMalFavoritesCount">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile">
                                <span>Manga Favorites</span>
                                <strong id="qliMalFavoriteMangaCount">0</strong>
                            </div>
                            <div class="qli-mal-stat-tile qli-mal-stat-tile-wide">
                                <span>Profile URL</span>
                                <strong id="qliMalProfileUrlText">Loading.</strong>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <script>
        window.PORTFOLIO_CONFIG = {
            basePath: <?= json_encode($basePath, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
            profile: {
                username: <?= json_encode($profile['username'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
            },
            mal: {
                username: <?= json_encode($mal['username'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
            },
            isMalPage: true
        };
    </script>
    <script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/app.js?v=<?= htmlspecialchars($jsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>