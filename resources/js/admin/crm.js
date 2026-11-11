/**
 * CRM helpers for the person card (admin only).
 */

/**
 * Tag/chip input. Posts one hidden input per tag: name="interests[]".
 */
window.chipInput = (initial = []) => ({
	tags: Array.isArray(initial) ? initial.filter(Boolean) : [],
	draft: '',
	add() {
		const parts = this.draft.split(',').map((t) => t.trim()).filter(Boolean);
		parts.forEach((tag) => {
			if (!this.tags.some((t) => t.toLocaleLowerCase('tr') === tag.toLocaleLowerCase('tr'))) {
				this.tags.push(tag);
			}
		});
		this.draft = '';
	},
	remove(index) {
		this.tags.splice(index, 1);
	},
	backspace() {
		if (this.draft === '' && this.tags.length) {
			this.tags.pop();
		}
	},
});

/**
 * Contact channel repeater (phones, e-mails, social profiles).
 */
window.channelRepeater = (initial = []) => ({
	rows: (Array.isArray(initial) ? initial : []).map((row) => ({ key: crypto.randomUUID?.() ?? Math.random(), ...row })),
	add(type = 'instagram') {
		this.rows.push({ key: crypto.randomUUID?.() ?? Math.random(), id: null, type, value: '', label: '', is_primary: false });
		this.$nextTick(() => {
			const inputs = this.$root.querySelectorAll('[data-channel-value]');
			inputs[inputs.length - 1]?.focus();
		});
	},
	remove(index) {
		this.rows.splice(index, 1);
	},
});

/**
 * Searchable person picker backed by /admin/people/search.
 */
window.personPicker = (config) => ({
	url: config.url,
	excludeId: config.excludeId ?? null,
	selected: config.selected ?? null, // {id, label}
	query: '',
	results: [],
	open: false,
	loading: false,
	active: -1,
	timer: null,
	search() {
		window.clearTimeout(this.timer);
		if (this.query.trim().length < 2) {
			this.results = [];
			this.open = false;
			return;
		}
		this.timer = window.setTimeout(async () => {
			this.loading = true;
			try {
				const { data } = await window.axios.get(this.url, { params: { q: this.query } });
				this.results = (data || [])
					.filter((p) => p.id !== this.excludeId)
					.map((p) => ({ id: p.id, label: `${p.name} ${p.surname}`.trim(), year: p.birthday ? String(p.birthday).slice(0, 4) : '' }));
				this.open = true;
				this.active = this.results.length ? 0 : -1;
			} finally {
				this.loading = false;
			}
		}, 220);
	},
	choose(person) {
		this.selected = person;
		this.query = '';
		this.results = [];
		this.open = false;
	},
	clear() {
		this.selected = null;
		this.$nextTick(() => this.$refs.input?.focus());
	},
	move(step) {
		if (!this.results.length) return;
		this.active = (this.active + step + this.results.length) % this.results.length;
	},
	enter() {
		if (this.open && this.results[this.active]) {
			this.choose(this.results[this.active]);
		}
	},
});

const cssVar = (name, fallback) => {
	const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
	return value || fallback;
};

/**
 * Big Five radar on the person card. Data: [{label, score, confidence}].
 */
window.bigFiveRadar = (rows) => ({
	chart: null,
	init() {
		if (!window.Chart || !rows?.length) return;
		const brand = cssVar('--brand-primary', '#0071e3');
		const ink = cssVar('--ink-muted', '#5b6b7a');
		const line = cssVar('--glass-line', 'rgba(0,0,0,.1)');

		this.chart = new window.Chart(this.$refs.canvas, {
			type: 'radar',
			data: {
				labels: rows.map((r) => r.label),
				datasets: [{
					data: rows.map((r) => r.score ?? 0),
					backgroundColor: /^#[0-9a-f]{6}$/i.test(brand) ? `${brand}38` : 'rgba(0, 113, 227, 0.22)',
					borderColor: brand,
					borderWidth: 2,
					pointBackgroundColor: brand,
					pointRadius: rows.map((r) => 2 + Math.round((r.confidence ?? 0.5) * 4)),
				}],
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				layout: { padding: 4 },
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: (ctx) => {
								const row = rows[ctx.dataIndex];
								const conf = row.confidence != null ? ` · %${Math.round(row.confidence * 100)}` : '';
								return ` ${row.score}${conf}`;
							},
						},
					},
				},
				scales: {
					r: {
						min: 0,
						max: 100,
						ticks: { stepSize: 25, display: false },
						grid: { color: line },
						angleLines: { color: line },
						pointLabels: { color: ink, font: { size: 11, weight: '600' } },
					},
				},
			},
		});
	},
	destroy() {
		this.chart?.destroy();
	},
});
