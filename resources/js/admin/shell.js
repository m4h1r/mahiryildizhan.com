/**
 * Admin shell: collapsible sidebar (desktop), icon rail + overlay (tablet),
 * drawer (mobile), and the floating tooltip shown on rail icons.
 *
 * The collapsed flag is applied to <html data-rail> by an inline head script
 * before first paint, so there is no flash between states.
 */
const RAIL_KEY = 'admin_sidebar_collapsed';
const DESKTOP = 1024;

const readCollapsed = () => {
	try {
		return localStorage.getItem(RAIL_KEY) === '1';
	} catch (e) {
		return false;
	}
};

window.adminShell = () => ({
	collapsed: readCollapsed(),
	drawerOpen: false,
	wide: window.innerWidth >= DESKTOP,

	init() {
		this.applyRail();

		window.addEventListener('resize', () => {
			const wide = window.innerWidth >= DESKTOP;
			if (wide !== this.wide) {
				this.wide = wide;
				this.drawerOpen = false;
			}
		}, { passive: true });

		this.$watch('drawerOpen', (open) => {
			document.body.classList.toggle('drawer-open', open);
			document.body.style.overflow = open && window.innerWidth < 768 ? 'hidden' : '';
			if (open) {
				this.$nextTick(() => this.$refs.aside?.querySelector('a, button')?.focus({ preventScroll: true }));
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && this.drawerOpen) {
				this.drawerOpen = false;
				return;
			}

			const target = event.target;
			const typing = target instanceof HTMLElement
				&& (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

			if (event.key === '[' && !typing && !event.metaKey && !event.ctrlKey && !event.altKey) {
				event.preventDefault();
				this.toggleNav();
			}
		});

		this.mountTooltips();
	},

	get navExpanded() {
		return this.wide ? !this.collapsed : this.drawerOpen;
	},

	toggleNav() {
		if (window.innerWidth >= DESKTOP) {
			this.collapsed = !this.collapsed;
			try {
				localStorage.setItem(RAIL_KEY, this.collapsed ? '1' : '0');
			} catch (e) {
				// Storage blocked: the toggle still works for this page view.
			}
			this.applyRail();
			return;
		}

		this.drawerOpen = !this.drawerOpen;
	},

	applyRail() {
		document.documentElement.dataset.rail = this.collapsed ? '1' : '0';
	},

	mountTooltips() {
		const aside = this.$refs.aside;
		if (!aside) {
			return;
		}

		const tip = document.createElement('div');
		tip.className = 'shell-tip';
		tip.setAttribute('role', 'presentation');
		tip.hidden = true;
		document.body.appendChild(tip);

		const show = (event) => {
			const link = event.target.closest('[data-tip]');
			if (!link || aside.getBoundingClientRect().width > 140) {
				return;
			}
			const rect = link.getBoundingClientRect();
			tip.textContent = link.dataset.tip;
			tip.hidden = false;
			tip.style.left = `${rect.right + 12}px`;
			tip.style.top = `${rect.top + rect.height / 2 - tip.offsetHeight / 2}px`;
		};
		const hide = () => {
			tip.hidden = true;
		};

		aside.addEventListener('mouseover', show);
		aside.addEventListener('focusin', show);
		aside.addEventListener('mouseout', hide);
		aside.addEventListener('focusout', hide);
		aside.addEventListener('scroll', hide, true);
	},
});

/**
 * Collapsible nav group with remembered state.
 */
window.navGroup = (key, fallback = true) => ({
	open: (() => {
		try {
			const saved = localStorage.getItem(key);
			return saved === null ? fallback : JSON.parse(saved);
		} catch (e) {
			return fallback;
		}
	})(),
	toggle() {
		this.open = !this.open;
		try {
			localStorage.setItem(key, JSON.stringify(this.open));
		} catch (e) {
			// ignore
		}
	},
});
