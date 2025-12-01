class pjTailwind {
	constructor() {
		this.activeLayer = null;
		this.backdropEl = null;
		this._bindEvents();
	}

	open(selector, type = 'modal', trigger = null) {
		const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
		if (el) this._openLayer(el, type, trigger);
	}
	close(selector) {
		const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
		if (el) this._closeLayer(el);
	}
	toggle(selector, type = 'modal', trigger = null) {
		const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
		if (!el) return;
		el.classList.contains('show')
			? this._closeLayer(el)
			: this._openLayer(el, type, trigger);
	}
	_bindEvents() {
		document.addEventListener('click', (e) => this._handleClick(e));
		document.addEventListener('keydown', (e) => this._handleKeydown(e));
	}

	_handleClick(e) {
		const t = e.target;
		if (t.closest('[data-modal]')) {
			e.preventDefault();
			const trigger = t.closest('[data-modal]');
			const selector = trigger.getAttribute('data-modal');
			this.open(selector, 'modal', trigger);
			return;
		}
		if (t.closest('[data-offcanvas]')) {
			e.preventDefault();
			const trigger = t.closest('[data-offcanvas]');
			const selector = trigger.getAttribute('data-offcanvas');
			this.open(selector, 'offcanvas', trigger);
			return;
		}
		const layer = t.closest('.modal, .offcanvas');
		if (layer && (t.classList.contains('btn-close') || !t.closest('.modal-content, .offcanvas'))) {
			this.close(layer);
		}
	}

	_handleKeydown(e) {
		if (this.activeLayer && e.key === 'Escape') {
			this.close(this.activeLayer);
		}
	}

	_openLayer(layer, type, trigger = null) {
		if (layer.classList.contains('show')) return;

		if (type === 'offcanvas') this._createBackdrop(layer);

		if (type === 'modal') {
			layer.style.display = 'block';
			layer.getBoundingClientRect(); // reflow برای transition
			layer.dispatchEvent(new CustomEvent('pjmodal_show', { detail: { trigger } }));
		}

		layer.classList.add('show');
		if( layer.hasAttribute('aria-hidden') ){
			layer.ariaHidden = 'false';
		}
		document.body.classList.add('overflow-hidden');
		this.activeLayer = layer;
	}

	_closeLayer(layer) {
		if (!layer.classList.contains('show')) return;
		layer.classList.remove('show');
		if( layer.hasAttribute('aria-hidden') ){
			layer.ariaHidden = 'true';
		}
		document.body.classList.remove('overflow-hidden');

		const finish = () => {
			layer.removeEventListener('transitionend', finish);
			layer.classList.remove('hiding');
			if (layer.classList.contains('modal')) {
				layer.style.display = 'none';
				layer.dispatchEvent(new CustomEvent('pjmodal_hide'));
			} else {
				this._removeBackdrop();
			}
			if (this.activeLayer === layer) this.activeLayer = null;
		};

		layer.classList.add('hiding');
		layer.addEventListener('transitionend', finish, { once: true });

		if (getComputedStyle(layer).transitionDuration === '0s') finish();
	}

	_createBackdrop(offcanvasEl) {
		if (this.backdropEl) return;
		const backdrop = document.createElement('div');
		backdrop.className = 'offcanvas-backdrop';
		offcanvasEl.parentNode?.insertBefore(backdrop, offcanvasEl);
		requestAnimationFrame(() => backdrop.classList.add('opacity-100'));
		backdrop.addEventListener('click', () => this.close(this.activeLayer));
		this.backdropEl = backdrop;
	}

	_removeBackdrop() {
		if (!this.backdropEl) return;
		const el = this.backdropEl;
		el.classList.remove('opacity-100');
		el.classList.add('opacity-0');
		el.addEventListener(
			'transitionend',
			() => {
				el.remove();
				this.backdropEl = null;
			},
			{ once: true }
		);

		if (getComputedStyle(el).transitionDuration === '0s') {
			el.remove();
			this.backdropEl = null;
		}
	}
}

const pjtail = new pjTailwind();