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
    <meta name="description" content="MyAnimeList profile page view">
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
    <div class="qli-manga-shell" style="min-height:100vh;">
        <header class="qli-manga-topbar">
            <a class="qli-manga-back" href="/">Back</a>
            <div class="qli-manga-title">Qliphoth-UTP's Profile</div>
            <div class="qli-manga-actions">
                <a href="#" target="_blank" rel="noopener noreferrer" id="qliMalProfileLink">MAL Profile</a>
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
                        <strong id="qliMalName">Loading.</strong>
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
                    <a class="qli-manga-navlink active" href="/mal">Anime Profile</a>
                    <a class="qli-manga-navlink" href="#" id="qliMalExternalProfile" target="_blank" rel="noopener noreferrer">View MAL Profile</a>
                    <a class="qli-manga-navlink" href="#" id="qliMalExternalAnime" target="_blank" rel="noopener noreferrer">Open Anime List</a>
                </div>
            </aside>

            <main class="qli-manga-main">
                <section class="qli-manga-hero" id="qliMalHeroCover">
                    <div class="qli-manga-hero-overlay">
                        <div>
                            <div class="qli-manga-eyebrow">MyAnimeList Public View</div>
                            <h1 class="qli-manga-hero-title" id="qliMalHeroTitle">Loading profile.</h1>
                            <div class="qli-manga-hero-badge">blue haired waifs/tsundere enthusiast</div>
                        </div>
                    </div>
                </section>

                <section class="qli-manga-block">
                    <div class="qli-manga-block-title">Profile Summary</div>
                    <div class="qli-manga-summary" id="qliMalAbout">
                        Loading profile summary.
                    </div>
                </section>

                <section class="qli-manga-grid-two">
                    <div class="qli-manga-block">
                        <div class="qli-manga-block-title">Recent Anime Titles</div>
                        <div class="qli-manga-covers" id="qliMalRecentAnime">
                            <div class="qli-manga-empty">Loading anime covers.</div>
                        </div>
                    </div>

                    <div class="qli-manga-block">
                        <div class="qli-manga-block-title">Top Favorite Anime</div>
                        <div class="qli-manga-covers" id="qliMalFavorites">
                            <div class="qli-manga-empty">Loading favorites.</div>
                        </div>
                    </div>
                </section>

                <section class="qli-manga-grid-two">
                    <div class="qli-manga-block">
                        <div class="qli-manga-block-title">Recent Manga Titles</div>
                        <div class="qli-manga-covers" id="qliMalRecentManga">
                            <div class="qli-manga-empty">Loading manga covers.</div>
                        </div>
                    </div>

                    <div class="qli-manga-block">
                        <div class="qli-manga-block-title">Top Favorite Manga</div>
                        <div class="qli-manga-covers" id="qliMalFavoriteManga">
                            <div class="qli-manga-empty">Loading manga favorites.</div>
                        </div>
                    </div>
                </section>

                <section class="qli-manga-block">
                    <div class="qli-manga-block-title">API App Configuration</div>
                    <div class="qli-api-grid">
                        <div class="qli-api-row">
                            <span>Watching</span>
                            <strong id="qliMalWatching">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Completed</span>
                            <strong id="qliMalCompleted">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>On Hold</span>
                            <strong id="qliMalOnHold">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Dropped</span>
                            <strong id="qliMalDropped">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Plan to Watch</span>
                            <strong id="qliMalPlan">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Anime Updates</span>
                            <strong id="qliMalAnimeUpdates">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Manga Updates</span>
                            <strong id="qliMalMangaUpdates">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Favorites Found</span>
                            <strong id="qliMalFavoritesCount">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Manga Favorites</span>
                            <strong id="qliMalFavoriteMangaCount">0</strong>
                        </div>
                        <div class="qli-api-row">
                            <span>Profile URL</span>
                            <strong id="qliMalProfileUrlText">Loading.</strong>
                        </div>
                    </div>
                </section>
            </main>
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