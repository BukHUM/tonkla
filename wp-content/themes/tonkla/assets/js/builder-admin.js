/**
 * Tonkla Page Builder admin: icon browser + module picker + color swatches.
 */
(function () {
	'use strict';

	var cfg = window.tonklaBuilder || {};
	var i18n = cfg.i18n || {};
	var colorMap = cfg.colorMap || {};
	var modules = cfg.modules || [];

	var iconCache = null;
	var iconLoadPromise = null;
	var activeIconInput = null;
	var debounceTimer = null;

	var STYLE_PREFIX = {
		solid: 'fa-solid',
		regular: 'fa-regular',
		brands: 'fa-brands',
	};

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function qsa(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function setNativeValue(input, value) {
		var proto = window.HTMLInputElement.prototype;
		var desc = Object.getOwnPropertyDescriptor(proto, 'value');
		if (desc && desc.set) {
			desc.set.call(input, value);
		} else {
			input.value = value;
		}
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function loadIcons() {
		if (iconCache) {
			return Promise.resolve(iconCache);
		}
		if (iconLoadPromise) {
			return iconLoadPromise;
		}
		iconLoadPromise = fetch(cfg.iconsUrl || '')
			.then(function (r) {
				if (!r.ok) {
					throw new Error('icons load failed');
				}
				return r.json();
			})
			.then(function (data) {
				iconCache = Array.isArray(data) ? data : [];
				return iconCache;
			})
			.catch(function () {
				iconCache = [];
				return iconCache;
			});
		return iconLoadPromise;
	}

	function ensureModal(id, wide) {
		var el = document.getElementById(id);
		if (el) {
			return el;
		}
		el = document.createElement('div');
		el.id = id;
		el.className = 'tonkla-modal';
		el.setAttribute('hidden', '');
		el.innerHTML =
			'<button type="button" class="tonkla-modal__backdrop" data-tonkla-modal-close aria-label="' +
			escapeHtml(i18n.close || 'Close') +
			'"></button>' +
			'<div class="tonkla-modal__panel' +
			(wide ? ' tonkla-modal__panel--wide' : '') +
			'" role="dialog" aria-modal="true">' +
			'<header class="tonkla-modal__header">' +
			'<h2 data-tonkla-modal-title></h2>' +
			'<button type="button" class="tonkla-modal__close" data-tonkla-modal-close aria-label="' +
			escapeHtml(i18n.close || 'Close') +
			'">&times;</button>' +
			'</header>' +
			'<div class="tonkla-modal__toolbar" data-tonkla-modal-toolbar></div>' +
			'<div class="tonkla-modal__body" data-tonkla-modal-body></div>' +
			'<footer class="tonkla-modal__footer" data-tonkla-modal-footer hidden></footer>' +
			'</div>';
		document.body.appendChild(el);

		el.addEventListener('click', function (e) {
			if (e.target.closest('[data-tonkla-modal-close]')) {
				closeModal(el);
			}
		});
		return el;
	}

	function openModal(el) {
		el.hidden = false;
		el.classList.add('is-open');
		document.body.classList.add('tonkla-modal-open');
	}

	function closeModal(el) {
		el.classList.remove('is-open');
		el.hidden = true;
		if (!document.querySelector('.tonkla-modal.is-open')) {
			document.body.classList.remove('tonkla-modal-open');
		}
		activeIconInput = null;
	}

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function faClass(name, style) {
		return (STYLE_PREFIX[style] || 'fa-solid') + ' fa-' + name;
	}

	/* ---------- Icon field enhancement ---------- */

	function isIconInput(input) {
		if (!input || input.dataset.tonklaIconReady === '1') {
			return false;
		}
		var type = (input.getAttribute('type') || input.type || '').toLowerCase();
		if (type && type !== 'text') {
			return false;
		}
		// set_classes() lands on the Carbon field wrapper, not the <input>.
		if (input.closest && input.closest('.tonkla-icon-field')) {
			return true;
		}
		var name = input.getAttribute('name') || '';
		var id = input.getAttribute('id') || '';
		// Compact names look like: carbon_fields_compact_input[_page_modules[0][items][0][icon]]
		if (/\[icon_class\]|\[icon\]/.test(name)) {
			return true;
		}
		if (/(^|[_-])icon(_class)?$/i.test(id) || /-(icon|icon_class)(-|$)/i.test(id)) {
			return true;
		}
		// Fallback: label / help text near the input.
		var field = input.closest ? input.closest('.cf-field') : null;
		if (field) {
			var label = field.querySelector('.cf-field__label, label');
			var help = field.querySelector('.cf-field__help, .carbon-help-text');
			var blob = ((label && label.textContent) || '') + ' ' + ((help && help.textContent) || '');
			if (/FontAwesome|เลือกไอคอน|icon_class/i.test(blob)) {
				return true;
			}
		}
		return false;
	}

	function enhanceIconInput(input) {
		if (!isIconInput(input)) {
			return;
		}
		input.dataset.tonklaIconReady = '1';

		var wrap = document.createElement('div');
		wrap.className = 'tonkla-icon-wrap';

		var preview = document.createElement('span');
		preview.className = 'tonkla-icon-wrap__preview is-empty';
		preview.setAttribute('aria-hidden', 'true');
		preview.innerHTML = '<i class="fa-solid fa-icons"></i>';

		var actions = document.createElement('div');
		actions.className = 'tonkla-icon-wrap__actions';

		var browse = document.createElement('button');
		browse.type = 'button';
		browse.className = 'button button-secondary';
		browse.textContent = i18n.browse || 'Browse';

		var clear = document.createElement('button');
		clear.type = 'button';
		clear.className = 'button';
		clear.textContent = i18n.clear || 'Clear';

		actions.appendChild(browse);
		actions.appendChild(clear);

		var parent = input.parentNode;
		if (!parent) {
			return;
		}
		parent.insertBefore(wrap, input);
		wrap.appendChild(preview);
		wrap.appendChild(input);
		wrap.appendChild(actions);

		function refreshPreview() {
			var val = (input.value || '').trim();
			if (!val) {
				preview.classList.add('is-empty');
				preview.innerHTML = '<i class="fa-solid fa-icons"></i>';
				return;
			}
			preview.classList.remove('is-empty');
			preview.innerHTML = '<i class="' + escapeHtml(val) + '"></i>';
		}

		input.addEventListener('input', refreshPreview);
		input.addEventListener('change', refreshPreview);
		browse.addEventListener('click', function () {
			openIconBrowser(input);
		});
		clear.addEventListener('click', function () {
			setNativeValue(input, '');
			refreshPreview();
		});
		refreshPreview();
	}

	function openIconBrowser(input) {
		activeIconInput = input;
		var modal = ensureModal('tonkla-icon-browser');
		qs('[data-tonkla-modal-title]', modal).textContent = i18n.browse || 'Select icon';

		var toolbar = qs('[data-tonkla-modal-toolbar]', modal);
		toolbar.innerHTML =
			'<input type="search" data-tonkla-icon-search placeholder="' +
			escapeHtml(i18n.searchIcons || 'Search…') +
			'" />' +
			'<div class="tonkla-modal__filters" data-tonkla-icon-filters>' +
			'<button type="button" data-style="" class="is-active">' +
			escapeHtml(i18n.allStyles || 'All') +
			'</button>' +
			'<button type="button" data-style="solid">' +
			escapeHtml(i18n.solid || 'Solid') +
			'</button>' +
			'<button type="button" data-style="regular">' +
			escapeHtml(i18n.regular || 'Regular') +
			'</button>' +
			'<button type="button" data-style="brands">' +
			escapeHtml(i18n.brands || 'Brands') +
			'</button>' +
			'</div>';

		var body = qs('[data-tonkla-modal-body]', modal);
		body.innerHTML = '<p class="tonkla-modal__loading">' + escapeHtml(i18n.loading || 'Loading…') + '</p>';

		var state = { style: '', q: '', page: 0 };
		var PAGE = 200;

		function render() {
			loadIcons().then(function (icons) {
				var q = state.q.toLowerCase().trim();
				var filtered = [];
				icons.forEach(function (icon) {
					(icon.s || []).forEach(function (style) {
						if (state.style && style !== state.style) {
							return;
						}
						var hay = (icon.n + ' ' + (icon.t || []).join(' ') + ' ' + style).toLowerCase();
						if (q && hay.indexOf(q) === -1) {
							return;
						}
						filtered.push({ n: icon.n, style: style });
					});
				});

				if (!filtered.length) {
					body.innerHTML =
						'<p class="tonkla-modal__empty">' + escapeHtml(i18n.noResults || 'No results') + '</p>';
					return;
				}

				var slice = filtered.slice(0, (state.page + 1) * PAGE);
				var current = (input.value || '').trim();
				var html = '<div class="tonkla-icon-grid">';
				slice.forEach(function (item) {
					var cls = faClass(item.n, item.style);
					var sel = cls === current ? ' is-selected' : '';
					html +=
						'<button type="button" class="tonkla-icon-grid__item' +
						sel +
						'" data-icon-class="' +
						escapeHtml(cls) +
						'" title="' +
						escapeHtml(cls) +
						'">' +
						'<i class="' +
						escapeHtml(cls) +
						'"></i>' +
						'<span>' +
						escapeHtml(item.n) +
						'</span></button>';
				});
				html += '</div>';
				if (slice.length < filtered.length) {
					html +=
						'<p style="text-align:center;margin-top:12px"><button type="button" class="button" data-tonkla-icon-more>' +
						escapeHtml('โหลดเพิ่ม (' + (filtered.length - slice.length) + ')') +
						'</button></p>';
				}
				body.innerHTML = html;
			});
		}

		toolbar.querySelector('[data-tonkla-icon-search]').addEventListener('input', function (e) {
			state.q = e.target.value;
			state.page = 0;
			render();
		});
		toolbar.querySelector('[data-tonkla-icon-filters]').addEventListener('click', function (e) {
			var btn = e.target.closest('button[data-style]');
			if (!btn) {
				return;
			}
			qsa('button[data-style]', toolbar).forEach(function (b) {
				b.classList.toggle('is-active', b === btn);
			});
			state.style = btn.getAttribute('data-style') || '';
			state.page = 0;
			render();
		});
		body.onclick = function (e) {
			var more = e.target.closest('[data-tonkla-icon-more]');
			if (more) {
				state.page += 1;
				render();
				return;
			}
			var item = e.target.closest('[data-icon-class]');
			if (!item || !activeIconInput) {
				return;
			}
			setNativeValue(activeIconInput, item.getAttribute('data-icon-class'));
			activeIconInput.dispatchEvent(new Event('input', { bubbles: true }));
			closeModal(modal);
		};

		openModal(modal);
		render();
		var search = toolbar.querySelector('[data-tonkla-icon-search]');
		if (search) {
			search.focus();
		}
	}

	/* ---------- Module picker ---------- */

	function initModulePicker() {
		document.addEventListener(
			'click',
			function (e) {
				var btn = e.target.closest('.cf-complex__inserter-button');
				if (!btn) {
					return;
				}
				var complex = btn.closest('.cf-complex');
				if (!complex) {
					return;
				}
				if (!isPageModulesComplex(complex)) {
					return;
				}
				var menu = complex.querySelector('.cf-complex__inserter-menu');
				if (!menu) {
					return;
				}
				var items = qsa('.cf-complex__inserter-item', menu);

				e.preventDefault();
				e.stopPropagation();
				openModulePicker(complex, items);
			},
			true
		);
	}

	function openModulePicker(complex, inserterItems) {
		var modal = ensureModal('tonkla-module-picker', true);
		qs('[data-tonkla-modal-title]', modal).textContent = i18n.pickModule || 'Pick module';
		var toolbar = qs('[data-tonkla-modal-toolbar]', modal);
		toolbar.innerHTML =
			'<input type="search" data-tonkla-module-search placeholder="' +
			escapeHtml(i18n.searchModule || 'Search…') +
			'" />';
		var body = qs('[data-tonkla-modal-body]', modal);

		function render(q) {
			q = (q || '').toLowerCase().trim();
			var html = '<div class="tonkla-module-grid">';
			modules.forEach(function (m) {
				var hay = (m.label + ' ' + m.description).toLowerCase();
				if (q && hay.indexOf(q) === -1) {
					return;
				}
				html +=
					'<button type="button" class="tonkla-module-card" data-module-label="' +
					escapeHtml(m.label) +
					'">' +
					'<img src="' +
					escapeHtml(m.wireframe) +
					'" alt="" width="280" height="140" loading="lazy" />' +
					'<p class="tonkla-module-card__title">' +
					escapeHtml(m.label) +
					'</p>' +
					'<p class="tonkla-module-card__desc">' +
					escapeHtml(m.description) +
					'</p></button>';
			});
			html += '</div>';
			body.innerHTML = html;
		}

		toolbar.querySelector('[data-tonkla-module-search]').addEventListener('input', function (e) {
			render(e.target.value);
		});
		body.onclick = function (e) {
			var card = e.target.closest('[data-module-label]');
			if (!card) {
				return;
			}
			var label = card.getAttribute('data-module-label');
			var match = null;
			inserterItems.forEach(function (it) {
				if ((it.textContent || '').trim() === label) {
					match = it;
				}
			});
			closeModal(modal);
			if (match) {
				match.click();
			}
		};

		render('');
		openModal(modal);
	}

	/* ---------- Color swatches ---------- */

	function enhanceColorSelect(select) {
		if (!select || select.dataset.tonklaColorReady === '1') {
			return;
		}
		if (!select.classList.contains('tonkla-color-select') && !select.closest('.carbon-field')) {
			return;
		}
		// Only color-ish selects: option values match our map keys.
		var opts = Array.prototype.slice.call(select.options || []);
		var hit = opts.some(function (o) {
			return colorMap[o.value];
		});
		if (!hit) {
			return;
		}
		select.dataset.tonklaColorReady = '1';
		select.classList.add('tonkla-color-select');

		var swatch = document.createElement('span');
		swatch.className = 'tonkla-color-swatch';
		swatch.setAttribute('aria-hidden', 'true');
		if (select.nextSibling) {
			select.parentNode.insertBefore(swatch, select.nextSibling);
		} else {
			select.parentNode.appendChild(swatch);
		}

		function update() {
			swatch.style.backgroundColor = colorMap[select.value] || '#e2e8f0';
		}
		select.addEventListener('change', update);
		update();
	}

	/* ---------- Empty state ---------- */

	function isPageModulesComplex(complex) {
		if (!complex) {
			return false;
		}
		if (complex.closest && complex.closest('.tonkla-page-modules')) {
			return true;
		}
		var items = qsa('.cf-complex__inserter-item', complex);
		if (items.length < 4) {
			return false;
		}
		var labels = items.map(function (it) {
			return (it.textContent || '').trim();
		});
		return modules.some(function (m) {
			return labels.indexOf(m.label) !== -1;
		});
	}

	function updateEmptyState() {
		qsa('.cf-complex').forEach(function (complex) {
			if (!isPageModulesComplex(complex)) {
				return;
			}
			var groups = qsa('.cf-complex__group', complex);
			var placeholder = complex.querySelector('.tonkla-builder-empty');
			if (groups.length === 0) {
				if (!placeholder) {
					placeholder = document.createElement('div');
					placeholder.className = 'tonkla-builder-empty';
					placeholder.textContent = i18n.emptyModules || 'No modules yet';
					var actions = complex.querySelector('.cf-complex__actions');
					if (actions) {
						actions.parentNode.insertBefore(placeholder, actions);
					}
				}
			} else if (placeholder) {
				placeholder.remove();
			}
		});
	}

	function getGroupCollapseButton(group) {
		if (!group) {
			return null;
		}
		var icon = group.querySelector(
			'.cf-complex__group-actions .dashicons-arrow-up, .cf-complex__group-actions .dashicons-arrow-down'
		);
		return icon ? icon.closest('button') : null;
	}

	/**
	 * Accordion UX: click module header to expand/collapse; keep only one open.
	 */
	function initModuleAccordion() {
		document.addEventListener('click', function (e) {
			if (e.target.closest('.cf-complex__group-action')) {
				// Native Duplicate / Remove / Collapse — if Expand, close siblings after.
				var actionGroup = e.target.closest('.tonkla-page-modules .cf-complex__group');
				if (actionGroup && e.target.closest('.dashicons-arrow-down')) {
					setTimeout(function () {
						collapseSiblingModules(actionGroup);
					}, 0);
				}
				return;
			}

			var head = e.target.closest('.tonkla-page-modules .cf-complex__group-head');
			if (!head) {
				return;
			}
			e.preventDefault();
			var group = head.closest('.cf-complex__group');
			var btn = getGroupCollapseButton(group);
			if (!btn) {
				return;
			}
			var wasCollapsed = group.classList.contains('cf-complex__group--collapsed');
			btn.click();
			if (wasCollapsed) {
				setTimeout(function () {
					collapseSiblingModules(group);
				}, 0);
			}
		});
	}

	function collapseSiblingModules(openGroup) {
		var complex = openGroup && openGroup.closest('.cf-complex');
		if (!complex) {
			return;
		}
		qsa('.cf-complex__group', complex).forEach(function (g) {
			if (g === openGroup || g.classList.contains('cf-complex__group--collapsed')) {
				return;
			}
			var btn = getGroupCollapseButton(g);
			if (btn) {
				btn.click();
			}
		});
	}

	/* ---------- Scan + observer ---------- */

	function scan(root) {
		var scope = root || document;
		qsa('input.cf-text__input, input[type="text"]', scope).forEach(enhanceIconInput);
		qsa('select', scope).forEach(enhanceColorSelect);
		updateEmptyState();
	}

	function scheduleScan() {
		if (debounceTimer) {
			clearTimeout(debounceTimer);
		}
		debounceTimer = setTimeout(function () {
			debounceTimer = null;
			scan(document);
		}, 150);
	}

	function initObserver() {
		var target = document.querySelector('#poststuff') || document.querySelector('.carbon-container') || document.body;
		if (!window.MutationObserver || !target) {
			return;
		}
		var obs = new MutationObserver(function (mutations) {
			var relevant = mutations.some(function (m) {
				return m.addedNodes && m.addedNodes.length > 0;
			});
			if (relevant) {
				scheduleScan();
			}
		});
		obs.observe(target, { childList: true, subtree: true });
	}

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		var open = document.querySelector('.tonkla-modal.is-open');
		if (open) {
			closeModal(open);
		}
	});

	function boot() {
		var tpl = document.getElementById('page_template');
		if (tpl) {
			tpl.addEventListener('change', function () {
				scheduleScan();
			});
		}
		scan(document);
		initModulePicker();
		initModuleAccordion();
		initObserver();
		// Carbon Fields mounts via React after first paint — re-scan a few times.
		[400, 1000, 2000, 4000].forEach(function (ms) {
			setTimeout(scheduleScan, ms);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
