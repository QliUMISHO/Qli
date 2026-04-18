(() => {
    const config = window.PORTFOLIO_CONFIG || {};
    const basePath = (config.basePath || '').replace(/\/$/, '');
    const isMalPage = !!config.isMalPage;

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    const PIE_COLORS = ['#79c7ec', '#8c7df2', '#55dfc6', '#f6a96b', '#f2d35c', '#ff7ea8'];

    let activeNameValue = '';
    let activeNameLabel = '';
    let activeShowCaret = false;
    let nameAnimating = false;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatNumber(value) {
        return new Intl.NumberFormat().format(Number(value || 0));
    }

    function formatDateLabel(dateObj) {
        return dateObj.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function toDate(iso) {
        return new Date(`${iso}T00:00:00`);
    }

    function toIso(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function setTheme(theme) {
        const nextTheme = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', nextTheme);
        try {
            localStorage.setItem('qli-theme', nextTheme);
        } catch (e) {}
        updateThemeToggleLabel();
        if (window.__qliLastData) {
            renderStack(window.__qliLastData);
        }
    }

    function updateThemeToggleLabel() {
        const label = $('#qliThemeToggleLabel');
        if (!label) return;
        label.textContent = getTheme() === 'dark' ? 'Light mode' : 'Dark mode';
    }

    function bindThemeToggle() {
        const toggle = $('#qliThemeToggle');
        if (!toggle) return;

        updateThemeToggleLabel();

        toggle.addEventListener('click', () => {
            setTheme(getTheme() === 'dark' ? 'light' : 'dark');
        });
    }

    function setAvatar(url) {
        const avatar = $('#qliAvatarImage');
        if (!avatar) return;
        avatar.style.backgroundImage = `url("${url}")`;
    }

    function bindLinkSquares() {
        $$('.qli-link-square').forEach((item) => {
            item.addEventListener('click', () => {
                const link = item.dataset.link;
                if (!link) return;
                window.open(link, '_blank', 'noopener,noreferrer');
            });
        });
    }

    function setCaretVisibility(isVisible) {
        const caret = $('#qliTerminalCaret');
        if (!caret) return;
        caret.classList.toggle('is-visible', !!isVisible);
    }

    function setRenderedName(value, isGlitching) {
        const valueNode = $('#qliNameValue');
        if (!valueNode) return;

        const safeValue = escapeHtml(value);

        if (isGlitching) {
            valueNode.innerHTML = `<span class="qli-glitch-text">${safeValue}</span>`;
        } else {
            valueNode.textContent = value;
        }
    }

    function setGlitchState(value, label) {
        const valueNode = $('#qliNameValue');
        if (!valueNode) return;

        const shouldGlitch = String(label || '').toUpperCase() === 'ALIAS' && String(value || '').trim() !== '';

        valueNode.classList.toggle('is-glitching', shouldGlitch);
        valueNode.setAttribute('data-text', shouldGlitch ? String(value) : '');
    }

    function applyNameState(value, label, showCaret) {
        const labelNode = $('#qliNameLabel');
        const shouldGlitch = String(label || '').toUpperCase() === 'ALIAS' && String(value || '').trim() !== '';

        setRenderedName(value, shouldGlitch);

        if (labelNode) {
            labelNode.textContent = label;
        }

        setCaretVisibility(showCaret);
        setGlitchState(value, label);
    }

    function swapDisplayedName(value, label, showCaret) {
        const stage = $('#qliNameStage');

        if (!stage) return;
        if (nameAnimating) return;
        if (
            activeNameValue === value &&
            activeNameLabel === label &&
            activeShowCaret === !!showCaret
        ) {
            return;
        }

        nameAnimating = true;
        stage.classList.remove('is-sliding-in');
        stage.classList.add('is-sliding-out');

        const handleOut = () => {
            applyNameState(value, label, showCaret);

            stage.classList.remove('is-sliding-out');
            stage.classList.add('is-sliding-in');

            const handleIn = () => {
                stage.classList.remove('is-sliding-in');
                activeNameValue = value;
                activeNameLabel = label;
                activeShowCaret = !!showCaret;
                nameAnimating = false;
                stage.removeEventListener('animationend', handleIn);
            };

            stage.addEventListener('animationend', handleIn);
            stage.removeEventListener('animationend', handleOut);
        };

        stage.addEventListener('animationend', handleOut);
    }

    function bindNameSwitches() {
        const dots = $$('.qli-dot-switch');

        dots.forEach((dot) => {
            const activate = () => {
                const nextValue = dot.dataset.nameValue || '';
                const nextLabel = dot.dataset.nameLabel || '';
                const showCaret = dot.dataset.showCaret === 'true';

                dots.forEach((d) => d.classList.remove('active'));
                dot.classList.add('active');

                swapDisplayedName(nextValue, nextLabel, showCaret);
            };

            dot.addEventListener('mouseenter', activate);
            dot.addEventListener('click', activate);
        });
    }

    function renderStats(data) {
        const statRepos = $('#qliStatRepos');
        const statFollowers = $('#qliStatFollowers');
        const statContribs = $('#qliStatContribs');

        if (statRepos) statRepos.textContent = formatNumber(data.stats?.publicRepos || 0);
        if (statFollowers) statFollowers.textContent = formatNumber(data.stats?.followers || 0);
        if (statContribs) statContribs.textContent = formatNumber(data.contributions?.total || 0);
    }

    function renderStack(data) {
        const chart = $('#qliStackChart');
        const legend = $('#qliStackLegend');
        if (!chart || !legend) return;

        const stack = Array.isArray(data.stack) ? data.stack : [];
        if (!stack.length) {
            legend.innerHTML = '<div class="qli-stack-empty">No stack data found.</div>';
            return;
        }

        const top = stack.slice(0, 6);
        let start = 0;

        const stops = top.map((item, index) => {
            const pct = Number(item.percent || 0);
            const end = start + pct;
            const color = PIE_COLORS[index % PIE_COLORS.length];
            const stop = `${color} ${start}% ${end}%`;
            start = end;
            return stop;
        });

        if (start < 100) {
            stops.push(`rgba(127,127,127,0.12) ${start}% 100%`);
        }

        chart.style.background = `
            radial-gradient(circle at center, var(--qli-paper) 0 34%, transparent 35%),
            conic-gradient(${stops.join(', ')})
        `;

        legend.innerHTML = top.map((item, index) => `
            <div class="qli-stack-item">
                <div class="qli-stack-dot" style="background:${PIE_COLORS[index % PIE_COLORS.length]}"></div>
                <div class="qli-stack-name">${escapeHtml(item.name)}</div>
                <div class="qli-stack-pct">${escapeHtml(Number(item.percent || 0).toFixed(1))}%</div>
            </div>
        `).join('');
    }

    function renderContributionYears(data) {
        const wrap = $('#qliContribYears');
        if (!wrap) return;

        const years = Array.isArray(data.contribution_years) ? data.contribution_years : [];
        const defaultYear = String(data.default_contribution_year || years[0] || '');

        wrap.innerHTML = years.map((year) => `
            <button class="qli-year-pill ${String(year) === defaultYear ? 'active' : ''}" type="button" data-year="${escapeHtml(year)}">
                ${escapeHtml(year)}
            </button>
        `).join('');

        $$('.qli-year-pill', wrap).forEach((button) => {
            button.addEventListener('click', () => {
                const selectedYear = String(button.dataset.year || '');
                if (!selectedYear || !window.__qliLastData) return;

                $$('.qli-year-pill', wrap).forEach((item) => item.classList.remove('active'));
                button.classList.add('active');

                const yearData = window.__qliLastData.contributions_by_year?.[selectedYear] || {
                    year: Number(selectedYear),
                    total: 0,
                    days: []
                };

                renderContributions(yearData);
                renderStats({
                    ...window.__qliLastData,
                    contributions: yearData
                });
            });
        });
    }

    function renderContributions(contributionData) {
        const totalNode = $('#qliContribTotal');
        const monthsNode = $('#qliContribMonths');
        const gridNode = $('#qliContribGrid');

        if (!totalNode || !monthsNode || !gridNode) return;

        const total = Number(contributionData?.total || 0);
        const year = Number(contributionData?.year || 0);
        const days = Array.isArray(contributionData?.days) ? contributionData.days : [];

        totalNode.textContent = `${formatNumber(total)} contributions in ${year || 'selected year'}`;

        monthsNode.innerHTML = '';
        gridNode.innerHTML = '';
        gridNode.classList.remove('qli-heatmap-grid-empty');

        if (!days.length) {
            gridNode.classList.add('qli-heatmap-grid-empty');
            gridNode.innerHTML = `<div class="qli-heatmap-empty">No contribution data available for ${escapeHtml(year || 'this year')}.</div>`;
            return;
        }

        const firstDate = toDate(`${year}-01-01`);
        const lastDate = toDate(`${year}-12-31`);

        const start = new Date(firstDate);
        start.setDate(start.getDate() - start.getDay());

        const end = new Date(lastDate);
        end.setDate(end.getDate() + (6 - end.getDay()));

        const dayMap = new Map();
        days.forEach((day) => {
            if (!day?.date) return;
            dayMap.set(day.date, {
                count: Number(day.count || 0),
                level: Math.max(0, Math.min(4, Number(day.level || 0)))
            });
        });

        const cells = [];
        const monthStarts = [];
        let current = new Date(start);
        let index = 0;

        while (current <= end) {
            const iso = toIso(current);
            const weekIndex = Math.floor(index / 7);
            const dayOfWeek = current.getDay();
            const entry = dayMap.get(iso) || { count: 0, level: 0 };

            cells.push({
                iso,
                count: entry.count,
                level: entry.level,
                weekIndex,
                dayOfWeek,
                dateObj: new Date(current)
            });

            if (current.getDate() === 1 || index === 0) {
                monthStarts.push({
                    label: current.toLocaleString(undefined, { month: 'short' }),
                    weekIndex
                });
            }

            current.setDate(current.getDate() + 1);
            index += 1;
        }

        const weeks = Math.ceil(cells.length / 7);
        const cellSize = getComputedStyle(document.documentElement).getPropertyValue('--qli-cell').trim() || '11px';

        monthsNode.style.gridTemplateColumns = `repeat(${weeks}, ${cellSize})`;

        const usedMonths = new Set();
        monthStarts.forEach((month) => {
            const key = `${month.label}-${month.weekIndex}`;
            if (usedMonths.has(key)) return;
            usedMonths.add(key);

            const label = document.createElement('div');
            label.className = 'qli-month-label';
            label.textContent = month.label;
            label.style.gridColumn = `${month.weekIndex + 1} / span 4`;
            monthsNode.appendChild(label);
        });

        cells.forEach((cell) => {
            const item = document.createElement('div');
            item.className = `qli-heatmap-cell level-${cell.level}`;
            item.style.gridColumn = `${cell.weekIndex + 1}`;
            item.style.gridRow = `${cell.dayOfWeek + 1}`;
            item.title = `${cell.count} contribution${cell.count === 1 ? '' : 's'} on ${formatDateLabel(cell.dateObj)}`;
            gridNode.appendChild(item);
        });
    }

    function formatRepoDate(dateString) {
        if (!dateString) return 'No date';
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return 'No date';

        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function renderRepos(data) {
        const repoGrid = $('#qliRepoGrid');
        if (!repoGrid) return;

        const repos = Array.isArray(data.repos) ? data.repos : [];
        if (!repos.length) {
            repoGrid.innerHTML = '<div class="qli-repo-empty">No public repositories found.</div>';
            return;
        }

        repoGrid.innerHTML = repos.map((repo) => {
            const name = escapeHtml(repo.name || 'Untitled repository');
            const url = escapeHtml(repo.html_url || '#');
            const description = escapeHtml(repo.description || 'No description provided.');
            const updatedAt = escapeHtml(formatRepoDate(repo.updated_at || ''));
            const language = escapeHtml(repo.language || 'Public Repo');

            return `
                <a class="qli-repo-card" href="${url}" target="_blank" rel="noopener noreferrer">
                    <div class="qli-repo-head">
                        <div class="qli-repo-name">${name}</div>
                        <div class="qli-repo-arrow">↗</div>
                    </div>

                    <div class="qli-repo-desc">${description}</div>

                    <div class="qli-repo-meta">
                        <span class="qli-repo-badge">
                            <span class="qli-repo-badge-dot"></span>
                            ${language}
                        </span>
                        <span>${updatedAt}</span>
                    </div>
                </a>
            `;
        }).join('');
    }

    function renderAvatarAndDefaultName(data) {
        const avatarUrl = data.profile?.avatarUrl || `https://github.com/${encodeURIComponent(config.profile?.username || 'QliUMISHO')}.png?size=400`;
        setAvatar(avatarUrl);

        const first = $('.qli-dot-switch.active');
        if (!first) return;

        const value = first.dataset.nameValue || '';
        const label = first.dataset.nameLabel || '';
        const showCaret = first.dataset.showCaret === 'true';

        applyNameState(value, label, showCaret);

        activeNameValue = value;
        activeNameLabel = label;
        activeShowCaret = showCaret;
    }

    async function fetchJsonSafely(url, fallbackMessage) {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });

        const raw = await response.text();
        let data = null;

        try {
            data = raw ? JSON.parse(raw) : null;
        } catch (error) {
            const snippet = String(raw || '').replace(/\s+/g, ' ').trim().slice(0, 180);
            throw new Error(`${fallbackMessage}${snippet ? ` ${snippet}` : ''}`);
        }

        if (!response.ok) {
            throw new Error(data?.message || `HTTP ${response.status}`);
        }

        return data;
    }

    async function loadPortfolioData() {
        try {
            const data = await fetchJsonSafely(
                `${basePath}/api/github.php`,
                'GitHub API returned non-JSON output.'
            );

            if (!data || data.ok !== true) {
                throw new Error(data?.message || 'Invalid response');
            }

            window.__qliLastData = data;

            renderAvatarAndDefaultName(data);
            renderStats(data);
            renderStack(data);
            renderContributionYears(data);
            renderContributions(data.contributions);
            renderRepos(data);
        } catch (error) {
            console.error('Portfolio API load failed:', error);
            const totalNode = $('#qliContribTotal');
            const monthsNode = $('#qliContribMonths');
            const gridNode = $('#qliContribGrid');
            const legendNode = $('#qliStackLegend');
            const repoGrid = $('#qliRepoGrid');
            const yearWrap = $('#qliContribYears');

            if (totalNode) totalNode.textContent = 'Failed to load contributions';
            if (monthsNode) monthsNode.innerHTML = '';
            if (gridNode) {
                gridNode.classList.add('qli-heatmap-grid-empty');
                gridNode.innerHTML = '<div class="qli-heatmap-empty">Failed to load contribution graph.</div>';
            }
            if (legendNode) legendNode.innerHTML = '<div class="qli-stack-empty">Failed to load stack.</div>';
            if (repoGrid) repoGrid.innerHTML = '<div class="qli-repo-empty">Failed to load public repositories.</div>';
            if (yearWrap) yearWrap.innerHTML = '<div class="qli-stack-empty">Failed to load years.</div>';
        }
    }

    function normalizeText(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function safeInnerText(htmlString) {
        const div = document.createElement('div');
        div.innerHTML = htmlString || '';
        return normalizeText(div.textContent || '');
    }

    function renderMALCovers(containerId, items) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!Array.isArray(items) || !items.length) {
            container.innerHTML = '<div class="qli-manga-empty">No public entries found.</div>';
            return;
        }

        container.innerHTML = items.slice(0, 10).map((item) => {
            const title = escapeHtml(item.title || 'Untitled');
            const image = escapeHtml(item.image || '');
            const href = escapeHtml(item.url || '#');

            return `
                <a class="qli-manga-cover-card" href="${href}" target="_blank" rel="noopener noreferrer" title="${title}">
                    <div class="qli-manga-cover-thumb" style="background-image:url('${image}')"></div>
                    <div class="qli-manga-cover-name">${title}</div>
                </a>
            `;
        }).join('');
    }

    function renderMALData(data) {
        const profileUrl = data.profile_url || '#';
        const animeListUrl = data.anime_list_url || '#';
        const avatar = data.avatar || '';
        const hero = data.hero || avatar || '';

        const malProfileLink = $('#qliMalProfileLink');
        const malAnimeListLink = $('#qliMalAnimeListLink');
        const malExternalProfile = $('#qliMalExternalProfile');
        const malExternalAnime = $('#qliMalExternalAnime');
        const malName = $('#qliMalName');
        const malJoined = $('#qliMalJoined');
        const malStatus = $('#qliMalStatus');
        const malAbout = $('#qliMalAbout');
        const malWatching = $('#qliMalWatching');
        const malCompleted = $('#qliMalCompleted');
        const malOnHold = $('#qliMalOnHold');
        const malDropped = $('#qliMalDropped');
        const malPlan = $('#qliMalPlan');
        const malAnimeUpdates = $('#qliMalAnimeUpdates');
        const malMangaUpdates = $('#qliMalMangaUpdates');
        const malFavoritesCount = $('#qliMalFavoritesCount');
        const malProfileUrlText = $('#qliMalProfileUrlText');
        const malHeroTitle = $('#qliMalHeroTitle');

        if (malProfileLink) malProfileLink.href = profileUrl;
        if (malAnimeListLink) malAnimeListLink.href = animeListUrl;
        if (malExternalProfile) malExternalProfile.href = profileUrl;
        if (malExternalAnime) malExternalAnime.href = animeListUrl;

        const avatarNode = $('#qliMalAvatar');
        if (avatarNode) {
            avatarNode.style.backgroundImage = avatar ? `url("${avatar}")` : 'none';
            avatarNode.style.backgroundSize = 'cover';
            avatarNode.style.backgroundPosition = 'center';
        }

        const heroNode = $('#qliMalHeroCover');
        if (heroNode) {
            heroNode.style.backgroundImage = hero
                ? `linear-gradient(180deg, rgba(4,10,18,0.10) 0%, rgba(4,10,18,0.76) 100%), url("${hero}")`
                : 'linear-gradient(180deg, rgba(4,10,18,0.10) 0%, rgba(4,10,18,0.76) 100%)';
            heroNode.style.backgroundSize = 'cover';
            heroNode.style.backgroundPosition = 'center';
        }

        if (malHeroTitle) malHeroTitle.textContent = data.name || config.mal?.username || 'Unknown';
        if (malName) malName.textContent = data.name || config.mal?.username || 'Unknown';
        if (malJoined) malJoined.textContent = data.joined || 'Public profile';
        if (malStatus) malStatus.textContent = data.status || 'Active';
        if (malAbout) malAbout.textContent = safeInnerText(data.about || 'No public profile text found.');
        if (malWatching) malWatching.textContent = formatNumber(data.stats?.watching || 0);
        if (malCompleted) malCompleted.textContent = formatNumber(data.stats?.completed || 0);
        if (malOnHold) malOnHold.textContent = formatNumber(data.stats?.on_hold || 0);
        if (malDropped) malDropped.textContent = formatNumber(data.stats?.dropped || 0);
        if (malPlan) malPlan.textContent = formatNumber(data.stats?.plan_to_watch || 0);
        if (malAnimeUpdates) malAnimeUpdates.textContent = formatNumber(data.updates?.anime || 0);
        if (malMangaUpdates) malMangaUpdates.textContent = formatNumber(data.updates?.manga || 0);
        if (malFavoritesCount) malFavoritesCount.textContent = formatNumber((data.favorites || []).length || 0);
        if (malProfileUrlText) malProfileUrlText.textContent = data.profile_url || 'Unavailable';

        renderMALCovers('qliMalRecentAnime', data.recent_anime || []);
        renderMALCovers('qliMalFavorites', data.favorites || []);
    }

    async function loadMALData() {
        try {
            const username = encodeURIComponent(config.mal?.username || config.profile?.username || 'QliUMISHO');
            const data = await fetchJsonSafely(
                `${basePath}/api/mal_proxy.php?username=${username}`,
                'MAL API returned non-JSON output.'
            );

            if (!data || data.ok !== true) {
                throw new Error(data?.message || 'Invalid MAL response');
            }

            renderMALData(data);
        } catch (error) {
            console.error('MAL data load failed:', error);
            const about = $('#qliMalAbout');
            const recent = $('#qliMalRecentAnime');
            const favs = $('#qliMalFavorites');
            const status = $('#qliMalStatus');

            if (status) status.textContent = 'Unavailable';
            if (about) about.textContent = 'Failed to load MyAnimeList profile data.';
            if (recent) recent.innerHTML = '<div class="qli-manga-empty">Failed to load recent anime.</div>';
            if (favs) favs.innerHTML = '<div class="qli-manga-empty">Failed to load favorites.</div>';
        }
    }

    function runStartupAnimation() {
        const startup = $('#qliStartup');
        const pageShell = $('#qliPageShell');
        const bar = $('#qliStartupBar');
        const status = $('#qliStartupStatus');

        if (!startup || !pageShell || !bar || !status) {
            if (pageShell) {
                pageShell.classList.remove('qli-page-preload');
                pageShell.classList.add('qli-page-ready');
            }
            return;
        }

        let alreadyPlayed = false;
        try {
            alreadyPlayed = sessionStorage.getItem('qli-startup-played') === '1';
        } catch (e) {}

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (alreadyPlayed || reducedMotion) {
            startup.classList.add('is-hidden');
            pageShell.classList.remove('qli-page-preload');
            pageShell.classList.add('qli-page-ready');
            return;
        }

        const steps = [
            { progress: 14, text: 'Loading experience...', delay: 650 },
            { progress: 32, text: 'Preparing interface...', delay: 700 },
            { progress: 54, text: 'Applying motion systems...', delay: 750 },
            { progress: 74, text: 'Syncing live portfolio data...', delay: 850 },
            { progress: 92, text: 'Finalizing startup sequence...', delay: 900 },
            { progress: 100, text: 'Portfolio ready.', delay: 2200 }
        ];

        let i = 0;

        function nextStep() {
            if (i >= steps.length) {
                setTimeout(() => {
                    startup.classList.add('is-hidden');
                    pageShell.classList.remove('qli-page-preload');
                    pageShell.classList.add('qli-page-ready');
                    try {
                        sessionStorage.setItem('qli-startup-played', '1');
                    } catch (e) {}
                }, 800);
                return;
            }

            const step = steps[i];
            bar.style.width = `${step.progress}%`;
            status.textContent = step.text;
            i += 1;

            setTimeout(nextStep, step.delay);
        }

        setTimeout(nextStep, 500);
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindThemeToggle();
        bindLinkSquares();

        if (!isMalPage) {
            bindNameSwitches();
            loadPortfolioData();
            runStartupAnimation();
        } else {
            document.body.classList.remove('disable-hover');
        }

        loadMALData();
    });
})();