import './bootstrap';

import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
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
	quickCreateUrl: config.quickCreateUrl,
	stakeholders: config.stakeholders ?? [],
	selectedId: config.initialSelectedId ? String(config.initialSelectedId) : '',
	vkn: '',
	showQuickCreateModal: false,
	quickVkn: '',
	quickTitle: '',
	quickName: '',
	quickSurname: '',
	quickError: '',
	init() {
		this.syncFromSelected();
	},
	findStakeholder(id) {
		return this.stakeholders.find((s) => String(s.id) === String(id)) ?? null;
	},
	syncFromSelected() {
		const s = this.findStakeholder(this.selectedId);
		this.vkn = s ? (s.vkn_tckn ?? '') : '';
	},
	openQuickCreateModal() {
		this.quickVkn = '';
		this.quickTitle = '';
		this.quickName = '';
		this.quickSurname = '';
		this.quickError = '';
		this.showQuickCreateModal = true;
	},
	closeQuickCreateModal() {
		this.showQuickCreateModal = false;
		this.quickError = '';
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
		// Dynamically add a new <option> to the native select for the newly created stakeholder
		const select = this.$refs.stakeholderSelect;
		if (select && !select.querySelector(`option[value="${String(option.id)}"]`)) {
			select.add(new Option(option.title, String(option.id)));
		}
		this.selectedId = String(option.id);
		this.vkn = option.vkn_tckn ?? '';
		this.quickError = '';
	},
	async createQuickStakeholder() {
		this.quickError = '';
		try {
			const response = await window.axios.post(this.quickCreateUrl, {
				vkn_tckn: this.quickVkn.trim() || null,
				title: this.quickTitle.trim() || null,
				name: this.quickName.trim() || null,
				surname: this.quickSurname.trim() || null,
			});
			this.applyStakeholder(response.data.data);
			this.showQuickCreateModal = false;
			this.quickVkn = '';
			this.quickTitle = '';
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
			extensions: [
				StarterKit,
				Image,
				Youtube.configure({ nocookie: true }),
			],
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
	async insertYoutubeVideo() {
		const url = await window.appDialog.prompt('YouTube video URL');

		if (!url) {
			return;
		}

		this.editor.chain().focus().setYoutubeVideo({ src: url }).run();
		this.content = this.editor.getHTML();
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
			await window.appDialog.alert(error?.response?.data?.message ?? 'Media library could not be loaded.');
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
			await window.appDialog.alert(error?.response?.data?.message ?? 'Image upload failed.');
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

const setupAppDialog = () => {
	const dialog = document.getElementById('app-dialog');

	if (!dialog || dialog.dataset.bound === '1') {
		return;
	}
	dialog.dataset.bound = '1';

	const messageEl = document.getElementById('app-dialog-message');
	const inputEl = document.getElementById('app-dialog-input');
	const cancelBtn = document.getElementById('app-dialog-cancel');
	const okBtn = document.getElementById('app-dialog-ok');
	const okLabelEl = document.getElementById('app-dialog-ok-label');
	const defaultOkLabel = okLabelEl.textContent;

	let resolvePromise = null;
	let currentMode = 'alert';

	const open = (message, { mode = 'alert', defaultValue = '', okLabel = null, danger = false } = {}) => new Promise((resolve) => {
		resolvePromise = resolve;
		currentMode = mode;
		messageEl.textContent = message;

		const showInput = mode === 'prompt';
		const showCancel = mode !== 'alert';

		inputEl.classList.toggle('hidden', !showInput);
		cancelBtn.classList.toggle('hidden', !showCancel);
		okLabelEl.textContent = okLabel ?? defaultOkLabel;
		okBtn.classList.toggle('admin-btn-danger', danger);
		okBtn.classList.toggle('admin-btn-primary', !danger);

		if (showInput) {
			inputEl.value = defaultValue;
		}

		dialog.showModal();

		if (showInput) {
			requestAnimationFrame(() => inputEl.focus());
		}
	});

	cancelBtn.addEventListener('click', () => {
		dialog.close('cancel');
	});

	dialog.addEventListener('close', () => {
		if (!resolvePromise) {
			return;
		}

		const resolve = resolvePromise;
		resolvePromise = null;

		if (currentMode === 'prompt') {
			resolve(dialog.returnValue === 'ok' ? inputEl.value : null);
		} else if (currentMode === 'confirm') {
			resolve(dialog.returnValue === 'ok');
		} else {
			resolve(true);
		}
	});

	window.appDialog = {
		alert: (message) => open(message, { mode: 'alert' }),
		confirm: (message, { okLabel = null, danger = true } = {}) => open(message, { mode: 'confirm', okLabel, danger }),
		prompt: (message, defaultValue = '') => open(message, { mode: 'prompt', defaultValue }),
	};
};

const setupConfirmForms = () => {
	if (document.body.dataset.confirmFormsBound === '1') {
		return;
	}
	document.body.dataset.confirmFormsBound = '1';

	document.addEventListener('submit', async (event) => {
		const form = event.target;

		if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') {
			return;
		}

		event.preventDefault();

		const confirmed = await window.appDialog.confirm(form.dataset.confirm, {
			okLabel: form.dataset.confirmLabel || undefined,
		});

		if (confirmed) {
			form.dataset.confirmed = '1';
			form.requestSubmit();
		}
	});
};

document.addEventListener('DOMContentLoaded', mountPeopleLiveSearch);
window.addEventListener('pageshow', mountPeopleLiveSearch);
document.addEventListener('DOMContentLoaded', mountThemeToggle);
window.addEventListener('pageshow', mountThemeToggle);
document.addEventListener('DOMContentLoaded', setupAppDialog);
window.addEventListener('pageshow', setupAppDialog);
document.addEventListener('DOMContentLoaded', setupConfirmForms);
window.addEventListener('pageshow', setupConfirmForms);

Alpine.start();
