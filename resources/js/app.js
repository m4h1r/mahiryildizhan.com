import './bootstrap';

import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Chart from 'chart.js/auto';
import { DataSet } from 'vis-data';
import { Network } from 'vis-network';

window.Alpine = Alpine;
window.vis = { DataSet, Network };
window.Chart = Chart;

window.loadApexCharts = async () => {
	if (window.ApexCharts) {
		return window.ApexCharts;
	}

	const module = await import('apexcharts');
	window.ApexCharts = module.default;

	return window.ApexCharts;
};

window.loadGraph3DLibs = async () => {
	if (window.THREE && window.ForceGraph3D && window.SpriteText) {
		return {
			THREE: window.THREE,
			ForceGraph3D: window.ForceGraph3D,
			SpriteText: window.SpriteText,
		};
	}

	const [threeModule, forceGraphModule, spriteTextModule] = await Promise.all([
		import('three'),
		import('3d-force-graph'),
		import('three-spritetext'),
	]);

	window.THREE = threeModule;
	window.ForceGraph3D = forceGraphModule.default;
	window.SpriteText = spriteTextModule.default;

	return {
		THREE: window.THREE,
		ForceGraph3D: window.ForceGraph3D,
		SpriteText: window.SpriteText,
	};
};

window.ui = (options = {}) => ({
	dark: false,
	defaultTheme: options.defaultTheme ?? 'system',
	init() {
		const saved = localStorage.getItem('theme');
		if (saved) {
			this.dark = saved === 'dark';
		} else if (this.defaultTheme === 'dark') {
			this.dark = true;
		} else if (this.defaultTheme === 'light') {
			this.dark = false;
		} else {
			this.dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		}
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

const syncThemeIcons = (isDark) => {
	document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
		const darkIcon = button.querySelector('[data-theme-icon="dark"]');
		const lightIcon = button.querySelector('[data-theme-icon="light"]');

		if (darkIcon) {
			darkIcon.classList.toggle('hidden', isDark);
		}

		if (lightIcon) {
			lightIcon.classList.toggle('hidden', !isDark);
		}
	});
};

const getThemeStorageKey = () => document.documentElement.dataset.themeStorageKey || 'theme';

const mountThemeToggle = () => {
	const root = document.documentElement;
	const defaultTheme = root.dataset.defaultTheme ?? 'system';
	const storageKey = getThemeStorageKey();
	const saved = localStorage.getItem(storageKey);

	let isDark = false;
	if (saved) {
		isDark = saved === 'dark';
	} else if (defaultTheme === 'dark') {
		isDark = true;
	} else if (defaultTheme === 'light') {
		isDark = false;
	} else {
		isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
	}

	root.classList.toggle('dark', isDark);
	syncThemeIcons(isDark);

	if (document.body.dataset.themeDelegatedBound !== '1') {
		document.addEventListener('click', (event) => {
			const target = event.target.closest('[data-theme-toggle]');
			if (! target) {
				return;
			}

			event.preventDefault();
			const nowDark = !document.documentElement.classList.contains('dark');
			document.documentElement.classList.toggle('dark', nowDark);
			localStorage.setItem(getThemeStorageKey(), nowDark ? 'dark' : 'light');
			syncThemeIcons(nowDark);
		});

		document.body.dataset.themeDelegatedBound = '1';
	}

	document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
		if (button.dataset.themeToggleBound === '1') {
			return;
		}

		button.dataset.themeToggleBound = '1';
	});
};

window.formState = () => ({
	loading: false,
	submit() {
		this.loading = true;
	},
});

window.expenseStakeholderLookup = (config) => ({
	lookupUrl: config.lookupUrl,
	quickCreateUrl: config.quickCreateUrl,
	stakeholders: config.stakeholders ?? [],
	selectedId: config.initialSelectedId ? String(config.initialSelectedId) : '',
	vkn: config.initialVkn ?? '',
	matchedTitle: '',
	loadingLookup: false,
	showQuickCreateModal: false,
	quickTitle: '',
	quickName: '',
	quickSurname: '',
	quickError: '',
	lookupTimer: null,
	init() {
		this.syncFromSelected();
	},
	findStakeholder(id) {
		return this.stakeholders.find((stakeholder) => String(stakeholder.id) === String(id)) ?? null;
	},
	syncFromSelected() {
		const stakeholder = this.findStakeholder(this.selectedId);

		if (! stakeholder) {
			if (! this.loadingLookup) {
				this.matchedTitle = '';
			}
			return;
		}

		this.vkn = stakeholder.vkn_tckn ?? this.vkn;
		this.matchedTitle = stakeholder.title ?? '';
	},
	onVknInput() {
		this.quickError = '';

		if (this.lookupTimer) {
			window.clearTimeout(this.lookupTimer);
		}

		const normalizedVkn = this.vkn.trim();

		if (normalizedVkn.length < 3) {
			this.loadingLookup = false;
			return;
		}

		this.loadingLookup = true;
		this.lookupTimer = window.setTimeout(() => this.lookupStakeholder(), 400);
	},
	async lookupStakeholder() {
		const normalizedVkn = this.vkn.trim();

		if (normalizedVkn.length < 3) {
			this.loadingLookup = false;
			return;
		}

		try {
			const response = await window.axios.get(this.lookupUrl, {
				params: { vkn: normalizedVkn },
			});
			const payload = response.data;

			if (payload.found && payload.data) {
				this.applyStakeholder(payload.data);
				this.showQuickCreateModal = false;
				return;
			}

			this.selectedId = '';
			this.matchedTitle = '';
			this.quickTitle = this.quickTitle || normalizedVkn;
			this.showQuickCreateModal = true;
		} catch (error) {
			this.quickError = error?.response?.data?.message ?? 'Stakeholder lookup failed.';
		} finally {
			this.loadingLookup = false;
		}
	},
	applyStakeholder(stakeholder) {
		const option = {
			id: stakeholder.id,
			title: stakeholder.title,
			vkn_tckn: stakeholder.vkn_tckn,
		};
		const existingIndex = this.stakeholders.findIndex((item) => String(item.id) === String(option.id));

		if (existingIndex >= 0) {
			this.stakeholders.splice(existingIndex, 1, option);
		} else {
			this.stakeholders.push(option);
		}

		this.selectedId = String(option.id);
		this.vkn = option.vkn_tckn;
		this.matchedTitle = option.title;
		this.quickTitle = option.title;
		this.quickError = '';
	},
	async createQuickStakeholder() {
		this.quickError = '';

		try {
			const response = await window.axios.post(this.quickCreateUrl, {
				vkn_tckn: this.vkn.trim(),
				title: this.quickTitle.trim() || null,
				name: this.quickName.trim() || null,
				surname: this.quickSurname.trim() || null,
			});

			this.applyStakeholder(response.data.data);
			this.showQuickCreateModal = false;
			this.quickName = '';
			this.quickSurname = '';
		} catch (error) {
			const errors = error?.response?.data?.errors;
			if (errors) {
				this.quickError = Object.values(errors).flat().join(' ');
				return;
			}

			this.quickError = error?.response?.data?.message ?? 'Quick create failed.';
		}
	},
	closeQuickCreateModal() {
		this.showQuickCreateModal = false;
		this.quickError = '';
	},
});

window.tiptapSimpleEditor = (config) => ({
	editor: null,
	content: config.content ?? '',
	init() {
		this.editor = new Editor({
			element: this.$refs.editor,
			extensions: [StarterKit],
			content: this.content,
			onUpdate: ({ editor }) => {
				this.content = editor.getHTML();
			},
		});
		this.content = this.editor.getHTML();
	},
});

window.tiptapPostEditor = (config) => ({
	editor: null,
	content: config.content ?? '',
	uploadUrl: config.uploadUrl,
	mediaLibraryUrl: config.mediaLibraryUrl,
	csrfToken: config.csrfToken,
	loadingImage: false,
	loadingLibrary: false,
	showMediaLibrary: false,
	mediaItems: [],
	libraryQuery: '',
	libraryType: '1',
	libraryMeta: {
		page: 1,
		last_page: 1,
		total: 0,
		per_page: 24,
	},
	searchDebounceTimer: null,
	init() {
		this.editor = new Editor({
			element: this.$refs.editor,
			extensions: [StarterKit, Image],
			content: this.content,
			onUpdate: ({ editor }) => {
				this.content = editor.getHTML();
			},
		});

		this.content = this.editor.getHTML();
	},
	triggerImagePicker() {
		this.$refs.imageInput.click();
	},
	async openMediaLibrary() {
		this.showMediaLibrary = true;

		if (!this.mediaLibraryUrl) {
			return;
		}

		if (this.mediaItems.length > 0) {
			return;
		}

		await this.fetchMediaLibrary(1);
	},
	debouncedLibrarySearch() {
		window.clearTimeout(this.searchDebounceTimer);
		this.searchDebounceTimer = window.setTimeout(() => {
			this.fetchMediaLibrary(1);
		}, 250);
	},
	async fetchMediaLibrary(page = 1) {
		if (!this.mediaLibraryUrl) {
			return;
		}

		this.loadingLibrary = true;

		try {
			const response = await window.axios.get(this.mediaLibraryUrl, {
				params: {
					q: this.libraryQuery || undefined,
					type: this.libraryType || undefined,
					page,
					per_page: 24,
				},
			});
			this.mediaItems = response?.data?.data ?? [];
			this.libraryMeta = response?.data?.meta ?? this.libraryMeta;
		} catch (error) {
			window.alert(error?.response?.data?.message ?? 'Media library could not be loaded.');
		} finally {
			this.loadingLibrary = false;
		}
	},
	insertFromMedia(item) {
		if (!item?.url) {
			return;
		}

		this.editor.chain().focus().setImage({ src: item.url, alt: item.alt || item.filename || '' }).run();
		this.content = this.editor.getHTML();
		this.showMediaLibrary = false;
	},
	async uploadInlineImage(event) {
		const [file] = event.target.files ?? [];

		if (! file) {
			return;
		}

		this.loadingImage = true;
		const formData = new FormData();
		formData.append('image', file);

		try {
			const response = await window.axios.post(this.uploadUrl, formData, {
				headers: {
					'Content-Type': 'multipart/form-data',
					'X-CSRF-TOKEN': this.csrfToken,
				},
			});

			this.editor.chain().focus().setImage({ src: response.data.url, alt: file.name }).run();
			this.content = this.editor.getHTML();
		} catch (error) {
			window.alert(error?.response?.data?.message ?? 'Image upload failed.');
		} finally {
			event.target.value = '';
			this.loadingImage = false;
		}
	},
	destroy() {
		this.editor?.destroy();
	},
});

const mountPeopleLiveSearch = () => {
	const input = document.getElementById('people-live-search');
	const form = document.getElementById('people-filter-form');

	if (! input || ! form || input.dataset.liveSearchBound === '1') {
		return;
	}

	let debounceTimer = null;
	input.addEventListener('input', () => {
		window.clearTimeout(debounceTimer);
		debounceTimer = window.setTimeout(() => {
			form.submit();
		}, 220);
	});

	input.dataset.liveSearchBound = '1';
};

document.addEventListener('DOMContentLoaded', mountPeopleLiveSearch);
window.addEventListener('pageshow', mountPeopleLiveSearch);
document.addEventListener('DOMContentLoaded', mountThemeToggle);
window.addEventListener('pageshow', mountThemeToggle);

Alpine.start();
