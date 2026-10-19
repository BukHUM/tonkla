(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function toggleEditor() {
		var templateSelect = document.getElementById('page_template');
		var editor = document.getElementById('postdivrich');
		if (!templateSelect || !editor) {
			return;
		}
		editor.style.display = templateSelect.value === 'page-flexible.php' ? 'none' : '';
	}

	function hideDashboardSubmit() {
		var isOverview = document.body.classList.contains('tonkla-admin-overview')
			|| document.body.classList.contains('toplevel_page_crb_carbon_fields_container_dashboard');
		if (!isOverview) {
			return;
		}
		var sidebar = document.getElementById('postbox-container-1');
		if (sidebar) {
			sidebar.remove();
		}
		document.querySelectorAll('#publish, input[name="publish"]').forEach(function (el) {
			el.remove();
		});
	}

	/**
	 * Fields first, save last: Carbon Theme Options uses WP columns-2 (Actions
	 * sidebar floats top-right and reads before the form). Flatten to one column
	 * and pin Actions after #post-body-content; strip Carbon sticky .fixed.
	 */
	function placeSettingsSaveBar() {
		if (!document.body.classList.contains('tonkla-admin-settings')) {
			return;
		}
		var postBody = document.getElementById('post-body');
		var content = document.getElementById('post-body-content');
		var actions = document.getElementById('postbox-container-1');
		if (!postBody || !content || !actions) {
			return;
		}

		postBody.classList.remove('columns-2');
		postBody.classList.add('columns-1');

		actions.classList.remove('fixed');
		actions.style.position = '';
		actions.style.top = '';
		actions.style.right = '';
		actions.style.width = '';
		actions.style.marginTop = '';

		if (content.nextElementSibling !== actions) {
			postBody.appendChild(actions);
		}
	}

	/**
	 * Carbon Theme Options prints `.settings-error.updated` without
	 * `is-dismissible`, so WP never adds a close button — fix that.
	 */
	function makeCarbonNoticesDismissible() {
		var wrap = document.querySelector('.carbon-theme-options, .wrap.carbon-theme-options');
		if (!wrap) {
			return;
		}
		wrap.querySelectorAll('.settings-error').forEach(function (notice) {
			if (notice.classList.contains('updated')) {
				notice.classList.add('notice', 'notice-success', 'is-dismissible');
			} else if (notice.classList.contains('error')) {
				notice.classList.add('notice', 'notice-error', 'is-dismissible');
			} else {
				notice.classList.add('notice', 'is-dismissible');
			}
			if (notice.querySelector('.notice-dismiss')) {
				return;
			}
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'notice-dismiss';
			btn.innerHTML = '<span class="screen-reader-text">ปิดการแจ้งเตือน</span>';
			btn.addEventListener('click', function () {
				notice.style.transition = 'opacity 0.1s linear';
				notice.style.opacity = '0';
				window.setTimeout(function () {
					if (notice.parentNode) {
						notice.parentNode.removeChild(notice);
					}
				}, 100);
			});
			notice.appendChild(btn);
		});
	}

	/**
	 * Remember Carbon Fields tabs across save/reload (CF always opens tab 0).
	 */
	function persistCarbonTabs() {
		if (!document.body.classList.contains('tonkla-admin-settings')) {
			return;
		}

		var page = '';
		try {
			page = new URLSearchParams(window.location.search).get('page') || '';
		} catch (err) {
			page = '';
		}
		var storageKey = 'tonkla_cf_tab_' + (page || 'default');

		function readSaved() {
			try {
				return sessionStorage.getItem(storageKey) || '';
			} catch (err) {
				return '';
			}
		}

		function writeSaved(label) {
			if (!label) {
				return;
			}
			try {
				sessionStorage.setItem(storageKey, label);
			} catch (err) {
				/* ignore quota / private mode */
			}
		}

		function tabLabel(btn) {
			return (btn.textContent || '').replace(/\s+/g, ' ').trim();
		}

		function saveCurrentTab() {
			var current = document.querySelector('.cf-container__tabs-item--current button');
			if (current) {
				writeSaved(tabLabel(current));
			}
		}

		function restoreTab() {
			var saved = readSaved();
			if (!saved) {
				return false;
			}
			var buttons = document.querySelectorAll('.cf-container__tabs-item button');
			if (!buttons.length) {
				return false;
			}
			for (var i = 0; i < buttons.length; i++) {
				if (tabLabel(buttons[i]) !== saved) {
					continue;
				}
				var item = buttons[i].closest('.cf-container__tabs-item');
				if (item && !item.classList.contains('cf-container__tabs-item--current')) {
					buttons[i].click();
				}
				return true;
			}
			return false;
		}

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.cf-container__tabs-item button');
			if (!btn) {
				return;
			}
			writeSaved(tabLabel(btn));
		});

		document.addEventListener('submit', function () {
			saveCurrentTab();
		}, true);

		var attempts = 0;
		var maxAttempts = 50;

		function tryRestore() {
			if (restoreTab() || attempts >= maxAttempts) {
				return;
			}
			attempts += 1;
			window.setTimeout(tryRestore, 100);
		}

		tryRestore();

		var root = document.getElementById('post-body') || document.body;
		if (typeof MutationObserver === 'function' && root) {
			var restoreTimer = null;
			var observer = new MutationObserver(function () {
				window.clearTimeout(restoreTimer);
				restoreTimer = window.setTimeout(function () {
					restoreTab();
				}, 50);
			});
			observer.observe(root, { childList: true, subtree: true });
			window.setTimeout(function () {
				observer.disconnect();
			}, 8000);
		}
	}

	/**
	 * PDPA position wireframes ↔ Carbon select sync.
	 */
	function initPdpaPositionWireframes() {
		var root = document.querySelector('.tonkla-pdpa-wireframes');
		if (!root) {
			return;
		}

		function findPositionSelect() {
			var byClass = document.querySelector('.tonkla-pdpa-position-field select');
			if (byClass) {
				return byClass;
			}
			var fields = document.querySelectorAll('.cf-field select');
			for (var i = 0; i < fields.length; i++) {
				var name = fields[i].getAttribute('name') || '';
				var id = fields[i].id || '';
				if (name.indexOf('pdpa_position') !== -1 || id.indexOf('pdpa_position') !== -1) {
					return fields[i];
				}
			}
			return null;
		}

		var select = findPositionSelect();
		var cards = root.querySelectorAll('.tonkla-pdpa-wireframe');

		function syncActive(value) {
			cards.forEach(function (card) {
				var on = card.getAttribute('data-position') === value;
				card.classList.toggle('is-active', on);
				card.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}

		if (select) {
			syncActive(select.value);
			select.addEventListener('change', function () {
				syncActive(select.value);
			});
		}

		cards.forEach(function (card) {
			card.addEventListener('click', function () {
				var pos = card.getAttribute('data-position');
				if (!pos) {
					return;
				}
				select = findPositionSelect() || select;
				if (select) {
					select.value = pos;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					select.dispatchEvent(new Event('input', { bubbles: true }));
				}
				syncActive(pos);
			});
		});
	}

	onReady(function () {
		toggleEditor();
		var templateSelect = document.getElementById('page_template');
		if (templateSelect) {
			templateSelect.addEventListener('change', toggleEditor);
		}
		hideDashboardSubmit();
		placeSettingsSaveBar();
		makeCarbonNoticesDismissible();
		persistCarbonTabs();
		initPdpaPositionWireframes();
		// Carbon may re-apply .fixed / columns-2 after load — keep save bar last.
		window.setTimeout(placeSettingsSaveBar, 100);
		window.setTimeout(placeSettingsSaveBar, 400);
		window.setTimeout(initPdpaPositionWireframes, 400);

		var postBody = document.getElementById('post-body');
		if (postBody && typeof MutationObserver === 'function') {
			var saveBarTimer = null;
			var observer = new MutationObserver(function () {
				window.clearTimeout(saveBarTimer);
				saveBarTimer = window.setTimeout(placeSettingsSaveBar, 50);
			});
			observer.observe(postBody, {
				attributes: true,
				attributeFilter: ['class', 'style'],
				subtree: true,
				childList: true
			});
		}
	});
})();
