<?php
declare(strict_types=1);

$githubUsername = 'YOUR_GITHUB_USERNAME';
$fullName = 'Your Name';
$headline = 'PHP Developer • Web Engineer • Systems Builder';
$tagline = 'I build elegant web systems, real-time applications, and production-ready digital experiences.';
$email = 'you@example.com';
$location = 'Philippines';
$linkedin = 'https://www.linkedin.com/in/YOUR_LINKEDIN/';
$github = 'https://github.com/' . $githubUsername;
$about = 'I design and build full-stack systems with a strong focus on PHP, JavaScript, CSS, GitHub-based workflows, deployment, and polished UI/UX. I enjoy turning complex ideas into beautiful, reliable products.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?> | Portfolio</title>
    <meta name="description" content="<?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css?v=1.0.0">
</head>
<body>
    <div class="pm-site-shell">
        <div class="pm-bg-orb pm-bg-orb-a"></div>
        <div class="pm-bg-orb pm-bg-orb-b"></div>
        <div class="pm-grid-noise"></div>

        <div class="pm-topbar">
            <div class="pm-topbar-inner pm-container">
                <div class="pm-brand-wrap">
                    <div class="pm-brand-badge">PM</div>
                    <div class="pm-brand-text"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="pm-nav-wrap">
                    <div class="pm-nav-link" data-scroll="hero">Home</div>
                    <div class="pm-nav-link" data-scroll="about">About</div>
                    <div class="pm-nav-link" data-scroll="stack">Tech Stack</div>
                    <div class="pm-nav-link" data-scroll="projects">Projects</div>
                    <div class="pm-nav-link" data-scroll="contact">Contact</div>
                </div>
            </div>
        </div>

        <div class="pm-page-body">
            <div class="pm-section pm-hero-section" id="hero">
                <div class="pm-container">
                    <div class="pm-hero-card pm-glass">
                        <div class="pm-hero-left">
                            <div class="pm-pill">Available for freelance and project work</div>
                            <div class="pm-hero-title"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="pm-hero-subtitle"><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="pm-hero-copy"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></div>

                            <div class="pm-hero-actions">
                                <div class="pm-btn pm-btn-primary" data-scroll="projects">View Projects</div>
                                <div class="pm-btn pm-btn-secondary" data-scroll="contact">Hire Me</div>
                            </div>

                            <div class="pm-hero-meta">
                                <div class="pm-meta-chip">Location: <?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="pm-meta-chip">Focus: PHP, JS, CSS, Deployment</div>
                                <div class="pm-meta-chip">GitHub Sync Enabled</div>
                            </div>
                        </div>

                        <div class="pm-hero-right">
                            <div class="pm-stat-card pm-float-card">
                                <div class="pm-stat-label">Public Repos</div>
                                <div class="pm-stat-value" id="pmRepoCount">--</div>
                            </div>
                            <div class="pm-stat-card pm-float-card">
                                <div class="pm-stat-label">Followers</div>
                                <div class="pm-stat-value" id="pmFollowerCount">--</div>
                            </div>
                            <div class="pm-stat-card pm-float-card">
                                <div class="pm-stat-label">Primary Stack</div>
                                <div class="pm-stat-value pm-stat-small" id="pmPrimaryStack">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pm-section" id="about">
                <div class="pm-container">
                    <div class="pm-section-header">
                        <div class="pm-section-kicker">About Me</div>
                        <div class="pm-section-title">Engineering with aesthetics and precision</div>
                        <div class="pm-section-copy"><?= htmlspecialchars($about, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="pm-about-grid">
                        <div class="pm-about-card pm-glass">
                            <div class="pm-card-title">What I Do</div>
                            <div class="pm-card-copy">
                                I build portfolio sites, business systems, dashboards, APIs, real-time communication tools, and modern user experiences with a deep focus on structure, reliability, and visual detail.
                            </div>
                        </div>

                        <div class="pm-about-card pm-glass">
                            <div class="pm-card-title">How I Work</div>
                            <div class="pm-card-copy">
                                I prefer clean architecture, maintainable code, Git-based version control, and deployment workflows that are practical for real projects.
                            </div>
                        </div>

                        <div class="pm-about-card pm-glass">
                            <div class="pm-card-title">Why This Portfolio Is Dynamic</div>
                            <div class="pm-card-copy">
                                Your technologies and featured repositories update from GitHub, so your website stays relevant as your coding activity evolves.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pm-section" id="stack">
                <div class="pm-container">
                    <div class="pm-section-header">
                        <div class="pm-section-kicker">Tech Stack</div>
                        <div class="pm-section-title">Live technology profile from GitHub</div>
                        <div class="pm-section-copy">
                            These technologies are detected from your public repositories and their language data.
                        </div>
                    </div>

                    <div class="pm-stack-panel pm-glass">
                        <div class="pm-stack-loading" id="pmStackLoading">Loading GitHub technology data...</div>
                        <div class="pm-stack-grid" id="pmTechStack"></div>
                    </div>
                </div>
            </div>

            <div class="pm-section" id="projects">
                <div class="pm-container">
                    <div class="pm-section-header">
                        <div class="pm-section-kicker">Projects</div>
                        <div class="pm-section-title">Featured repositories</div>
                        <div class="pm-section-copy">
                            Your latest and most relevant repositories appear here automatically.
                        </div>
                    </div>

                    <div class="pm-project-grid" id="pmProjectGrid">
                        <div class="pm-project-loading pm-glass">Loading repositories...</div>
                    </div>
                </div>
            </div>

            <div class="pm-section" id="contact">
                <div class="pm-container">
                    <div class="pm-contact-card pm-glass">
                        <div class="pm-section-kicker">Contact</div>
                        <div class="pm-section-title">Let’s build something strong and beautiful</div>
                        <div class="pm-contact-grid">
                            <div class="pm-contact-item">
                                <div class="pm-contact-label">Email</div>
                                <div class="pm-contact-value">
                                    <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </div>
                            </div>

                            <div class="pm-contact-item">
                                <div class="pm-contact-label">GitHub</div>
                                <div class="pm-contact-value">
                                    <a href="<?= htmlspecialchars($github, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                        <?= htmlspecialchars($githubUsername, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </div>
                            </div>

                            <div class="pm-contact-item">
                                <div class="pm-contact-label">LinkedIn</div>
                                <div class="pm-contact-value">
                                    <a href="<?= htmlspecialchars($linkedin, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pm-contact-actions">
                            <a class="pm-btn pm-btn-primary" href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">Send Email</a>
                            <a class="pm-btn pm-btn-secondary" href="<?= htmlspecialchars($github, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Open GitHub</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pm-footer">
            <div class="pm-container">
                <div class="pm-footer-inner">
                    <div class="pm-footer-text">© <?= date('Y') ?> <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>. Built with PHP, CSS, JavaScript, GitHub, and Vercel deployment workflow.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.PM_PORTFOLIO_CONFIG = {
            githubUsername: <?= json_encode($githubUsername, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
        };
    </script>
    <script src="/assets/app.js?v=1.0.0"></script>
</body>
</html>