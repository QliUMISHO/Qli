(() => {
    const config = window.PORTFOLIO_CONFIG || {};
    const basePath = (config.basePath || '').replace(/\/$/, '');

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

    function swapDisplayedName(value, label, showCaret) {
        const stage = $('#qliNameStage');
        const valueNode = $('#qliNameValue');
        const labelNode = $('#qliNameLabel');

        if (!stage || !valueNode || !labelNode) return;
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
            valueNode.textContent = value;
            labelNode.textContent = label;
            setCaretVisibility(showCaret);

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
        $('#qliStatRepos').textContent = formatNumber(data.stats?.publicRepos || 0);
        $('#qliStatFollowers').textContent = formatNumber(data.stats?.followers || 0);
        $('#qliStatContribs').textContent = formatNumber(data.contributions?.total || 0);
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
        const valueNode = $('#qliNameValue');
        const labelNode = $('#qliNameLabel');

        if (first && valueNode && labelNode) {
            const value = first.dataset.nameValue || '';
            const label = first.dataset.nameLabel || '';
            const showCaret = first.dataset.showCaret === 'true';

            valueNode.textContent = value;
            labelNode.textContent = label;
            setCaretVisibility(showCaret);

            activeNameValue = value;
            activeNameLabel = label;
            activeShowCaret = showCaret;
        }
    }

    async function loadPortfolioData() {
        try {
            const response = await fetch(`${basePath}/api/github.php`, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
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

    function runStartupAnimation() {
        const startup = $('#qliStartup');
        const pageShell = $('#qliPageShell');
        const bar = $('#qliStartupBar');
        const status = $('#qliStartupStatus');

        if (!startup || !pageShell || !bar || !status) {
            pageShell?.classList.remove('qli-page-preload');
            pageShell?.classList.add('qli-page-ready');
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
        bindNameSwitches();
        loadPortfolioData();
        runStartupAnimation();
    });
})();