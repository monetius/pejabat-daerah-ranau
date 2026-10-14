(() => {
    'use strict';
    const root = document.documentElement;
    const preference = window.matchMedia('(prefers-color-scheme: dark)');
    const buttons = document.querySelectorAll('[data-cms-theme-toggle]');
    const savedTheme = () => {
        try {
            const value = localStorage.getItem('cms-theme');
            return ['light', 'dark'].includes(value) ? value : null;
        } catch {
            return null;
        }
    };
    const applyTheme = theme => {
        root.dataset.cmsTheme = theme;
        const label = theme === 'dark' ? 'Light mode' : 'Dark mode';
        const description = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        buttons.forEach(button => {
            button.setAttribute('aria-label', description);
            button.title = description;
            button.querySelector('[data-cms-theme-label]').textContent = label;
        });
    };
    const preferredTheme = () => savedTheme() || (preference.matches ? 'dark' : 'light');
    applyTheme(preferredTheme());
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = root.dataset.cmsTheme === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('cms-theme', theme); } catch { /* The current page can still change theme. */ }
            applyTheme(theme);
        });
    });
    preference.addEventListener('change', () => {
        if (!savedTheme()) applyTheme(preferredTheme());
    });
    window.addEventListener('storage', event => {
        if (event.key === 'cms-theme' || event.key === null) applyTheme(preferredTheme());
    });
})();
