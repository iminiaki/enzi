(function () {
	'use strict';

	const applyTheme = (theme) => {
		const root = document.documentElement;
		const isDark = theme === 'dark';

		root.classList.toggle('dark', isDark);
		root.style.colorScheme = theme;
		root.dataset.theme = theme;

		document.querySelectorAll('.diako-login-theme-toggle .theme-icon-sun').forEach((icon) => {
			icon.classList.toggle('is-hidden', !isDark);
		});

		document.querySelectorAll('.diako-login-theme-toggle .theme-icon-moon').forEach((icon) => {
			icon.classList.toggle('is-hidden', isDark);
		});

		try {
			localStorage.setItem('diako-theme', theme);
		} catch (e) {}
	};

	const initTheme = () => {
		try {
			const stored = localStorage.getItem('diako-theme');
			const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
			applyTheme(stored || (prefersDark ? 'dark' : 'light'));
		} catch (e) {}
	};

	document.addEventListener('DOMContentLoaded', () => {
		initTheme();

		document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
			button.addEventListener('click', () => {
				const isDark = document.documentElement.classList.contains('dark');
				applyTheme(isDark ? 'light' : 'dark');
			});
		});

		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
			try {
				if (localStorage.getItem('diako-theme')) {
					return;
				}
			} catch (e) {}

			applyTheme(event.matches ? 'dark' : 'light');
		});
	});
})();
