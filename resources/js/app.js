import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.ui = () => ({
	dark: false,
	init() {
		const saved = localStorage.getItem('theme');
		this.dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
		this.applyTheme();
	},
	toggleTheme() {
		this.dark = !this.dark;
		localStorage.setItem('theme', this.dark ? 'dark' : 'light');
		this.applyTheme();
	},
	applyTheme() {
		document.documentElement.classList.toggle('dark', this.dark);
	},
});

window.formState = () => ({
	loading: false,
	submit() {
		this.loading = true;
	},
});

Alpine.start();
