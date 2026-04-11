(() => {
    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    const formatNumber = (value) => {
        const number = Number(value || 0);
        return new Intl.NumberFormat().format(number);
    };

    const formatSyncTime = (isoString) => {
        if (!isoString) return '--';
        const date = new Date(isoString);
        if (Number.isNaN(date.getTime())) return '--';

        return new Intl.DateTimeFormat(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        }).format(date);
    };

    const enableReveal = () => {
        const elements = $$('.reveal');

        if (!('IntersectionObserver' in window)) {
            elements.forEach((el) => el.classList.add('in-view'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.12
        });

        elements.forEach((el) => observer.observe(el));
    };

    const enableLinksAndScroll = () => {
        $$('.interactive-chip').forEach((item) => {
            const activate = () => {
                const link = item.dataset.link;
                const scrollTarget = item.dataset.scroll;

                if (link) {
                    window.open(link, '_blank', 'noopener,noreferrer');
                    return;
                }

                if (scrollTarget) {
                    const target = document.querySelector(scrollTarget);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            };

            item.addEventListener('click', activate);
            item.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    activate();
                }
            });
        });
    };

    const enableSpotlight = () => {
        $$('.spotlight-card').forEach((card) => {
            card.addEventListener('mousemove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                card.style.setProperty('--mx', `${x}px`);
                card.style.setProperty('--my', `${y}px`);
            });
        });
    };

    const enableCursorGlow = () => {
        const cursorGlow = $('#cursorGlow');
        if (!cursorGlow) return;

        window.addEventListener('mousemove', (event) => {
            cursorGlow.style.left = `${event.clientX}px`;
            cursorGlow.style.top = `${event.clientY}px`;
        });
    };

    const setFooterYear = () => {
        const footerYear = $('#footerYear');
        if (!footerYear) return;
        footerYear.textContent = `© ${new Date().getFullYear()} Raymond A. Auditor / QliUMISHO`;
    };

    const renderStats = (data) => {
        $('#statRepos').textContent = formatNumber(data.stats?.publicRepos ?? 0);
        $('#statFollowers').textContent = formatNumber(data.stats?.followers ?? 0);
        $('#statStars').textContent = formatNumber(data.stats?.totalStars ?? 0);
        $('#statSync').textContent = formatSyncTime(data.fetchedAt);
    };

    const renderProfileMeta = (data) => {
        const metaList = $('#profileMetaList');
        if (!metaList) return;

        const profile = data.profile || {};

        metaList.innerHTML = `
            <div class="profile-meta-row">
                <div class="profile-meta-key">Name</div>
                <div class="profile-meta-value">${escapeHtml(profile.name || 'Raymond A. Auditor')}</div>
            </div>
            <div class="profile-meta-row">
                <div class="profile-meta-key">Username</div>
                <div class="profile-meta-value">${escapeHtml(profile.login || 'QliUMISHO')}</div>
            </div>
            <div class="profile-meta-row">
                <div class="profile-meta-key">GitHub Bio</div>
                <div class="profile-meta-value">${escapeHtml(profile.bio || 'No public bio returned from GitHub API.')}</div>
            </div>
            <div class="profile-meta-row">
                <div class="profile-meta-key">Primary Link</div>
                <div class="profile-meta-value profile-meta-link interactive-chip" data-link="${escapeAttribute(profile.profileUrl || 'https://github.com/QliUMISHO')}" role="button" tabindex="0">Visit GitHub</div>
            </div>
        `;

        enableLinksAndScroll();
    };

    const renderTechStack = (data) => {
        const stackCloud = $('#stackCloud');
        if (!stackCloud) return;

        const techStack = Array.isArray(data.techStack) ? data.techStack : [];

        if (techStack.length === 0) {
            stackCloud.innerHTML = `<div class="loading-chip">No language data available yet.</div>`;
            return;
        }

        stackCloud.innerHTML = techStack.map((item) => {
            const countText = item.count > 1 ? `${item.count} repos` : `${item.count} repo`;
            return `<div class="stack-chip">${escapeHtml(item.name)} • ${escapeHtml(countText)}</div>`;
        }).join('');
    };

    const renderRepos = (data) => {
        const repoGrid = $('#repoGrid');
        if (!repoGrid) return;

        const repos = Array.isArray(data.repos) ? data.repos : [];

        if (repos.length === 0) {
            repoGrid.innerHTML = `<div class="repo-loading">No repositories available.</div>`;
            return;
        }

        repoGrid.innerHTML = repos.map((repo) => {
            const pills = [
                repo.language || 'Unspecified',
                `${repo.stars || 0} ★`,
                `${repo.forks || 0} Forks`,
                repo.isFork ? 'Fork' : 'Original'
            ];

            return `
                <div class="repo-card spotlight-card" data-link="${escapeAttribute(repo.url || '#')}" role="button" tabindex="0">
                    <div class="repo-top">
                        <div class="repo-name">${escapeHtml(repo.name || 'Untitled')}</div>
                        <div class="repo-description">${escapeHtml(repo.description || 'No description provided.')}</div>
                    </div>
                    <div class="repo-footer">
                        <div class="repo-meta-wrap">
                            ${pills.map((pill) => `<div class="repo-pill">${escapeHtml(pill)}</div>`).join('')}
                        </div>
                        <div class="repo-updated">Updated ${escapeHtml(repo.updatedAt || '--')}</div>
                    </div>
                </div>
            `;
        }).join('');

        enableLinksAndScroll();
        enableSpotlight();
    };

    const renderFallback = () => {
        $('#statRepos').textContent = '--';
        $('#statFollowers').textContent = '--';
        $('#statStars').textContent = '--';
        $('#statSync').textContent = '--';

        $('#stackCloud').innerHTML = `<div class="loading-chip">GitHub data unavailable right now.</div>`;
        $('#repoGrid').innerHTML = `<div class="repo-loading">Repositories could not be loaded.</div>`;
    };

    const escapeHtml = (value) => {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const escapeAttribute = (value) => {
        return escapeHtml(value);
    };

    const loadGitHubData = async () => {
        try {
            const response = await fetch('/api/github', {
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error(`GitHub endpoint failed with ${response.status}`);
            }

            const data = await response.json();

            if (!data || data.ok !== true) {
                throw new Error(data?.message || 'Invalid GitHub response');
            }

            renderStats(data);
            renderProfileMeta(data);
            renderTechStack(data);
            renderRepos(data);
        } catch (error) {
            console.error(error);
            renderFallback();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        setFooterYear();
        enableReveal();
        enableLinksAndScroll();
        enableSpotlight();
        enableCursorGlow();
        loadGitHubData();
    });
})();