(function () {
	'use strict';

	var MAX_ASSETS = 50;

	function initQrCodes() {
		if (typeof qrcode !== 'function') {
			return;
		}

		document.querySelectorAll('.itwt-label').forEach(function (label) {
			var url = label.getAttribute('data-qr-url');
			var target = label.querySelector('.itwt-label__qr');
			if (!url || !target) {
				return;
			}

			try {
				var qr = qrcode(0, 'M');
				qr.addData(url);
				qr.make();
				target.innerHTML = qr.createImgTag(3, 0);
				var img = target.querySelector('img');
				if (img) {
					img.alt = '';
					img.removeAttribute('width');
					img.removeAttribute('height');
				}
			} catch (e) {
				target.textContent = 'QR error';
			}
		});
	}

	function initAssetPicker() {
		var root = document.querySelector('[data-itwt-print-picker]');
		if (!root) {
			return;
		}

		var hidden = root.querySelector('#itwt-print-ids');
		var chips = root.querySelector('[data-itwt-print-chips]');
		var input = root.querySelector('.itwt-combobox__input');
		var list = root.querySelector('.itwt-combobox__list');
		if (!hidden || !chips || !input || !list) {
			return;
		}

		var cfg = window.itwtAdmin || {};
		var i18n = cfg.i18n || {};
		var timer = null;
		var lastQuery = null;
		var activeIndex = -1;
		var selected = {};

		chips.querySelectorAll('.itwt-print-picker__chip').forEach(function (chip) {
			var id = parseInt(chip.getAttribute('data-id') || '0', 10);
			if (id > 0) {
				selected[id] = {
					id: id,
					tag: (chip.querySelector('code') || {}).textContent || '',
					label: chip.getAttribute('data-label') || '',
				};
			}
		});

		function selectedIds() {
			return Object.keys(selected)
				.map(function (k) {
					return parseInt(k, 10);
				})
				.filter(function (id) {
					return id > 0;
				});
		}

		function syncHidden() {
			hidden.value = selectedIds().join(',');
		}

		function setExpanded(open) {
			input.setAttribute('aria-expanded', open ? 'true' : 'false');
			list.hidden = !open;
			if (!open) {
				activeIndex = -1;
			}
		}

		function removeId(id) {
			delete selected[id];
			var chip = chips.querySelector('.itwt-print-picker__chip[data-id="' + id + '"]');
			if (chip) {
				chip.remove();
			}
			syncHidden();
		}

		function addItem(item) {
			var id = parseInt(item.id, 10);
			if (!id || selected[id]) {
				return;
			}
			if (selectedIds().length >= MAX_ASSETS) {
				window.alert(i18n.assetMax || 'เลือกได้สูงสุด 50 รายการ');
				return;
			}

			selected[id] = item;
			var chip = document.createElement('span');
			chip.className = 'itwt-print-picker__chip';
			chip.setAttribute('data-id', String(id));
			chip.setAttribute('data-label', item.label || item.tag || '');

			var code = document.createElement('code');
			code.textContent = item.tag || String(id);
			chip.appendChild(code);

			var desc = (item.brand || '') + (item.model ? (item.brand ? ' ' : '') + item.model : '');
			if (!desc && item.label && item.tag && item.label.indexOf(item.tag + ' — ') === 0) {
				desc = item.label.slice(item.tag.length + 3);
			}
			if (desc) {
				var descEl = document.createElement('span');
				descEl.className = 'itwt-print-picker__chip-desc';
				descEl.textContent = desc;
				chip.appendChild(descEl);
			}

			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'itwt-print-picker__chip-remove';
			btn.setAttribute('data-itwt-print-remove', '');
			btn.setAttribute('aria-label', i18n.assetRemove || 'ลบออก');
			btn.innerHTML = '&times;';
			chip.appendChild(btn);

			chips.appendChild(chip);
			syncHidden();
		}

		function renderItems(items) {
			list.innerHTML = '';
			activeIndex = -1;

			var available = items.filter(function (item) {
				return !selected[parseInt(item.id, 10)];
			});

			if (!available.length) {
				var empty = document.createElement('li');
				empty.className = 'itwt-combobox__option itwt-combobox__option--muted';
				empty.setAttribute('role', 'presentation');
				empty.textContent = i18n.assetNone || 'ไม่พบสินทรัพย์';
				list.appendChild(empty);
				setExpanded(true);
				return;
			}

			available.forEach(function (item, idx) {
				var li = document.createElement('li');
				li.className = 'itwt-combobox__option';
				li.setAttribute('role', 'option');
				li.setAttribute('data-index', String(idx));
				li.textContent = item.label || item.tag || String(item.id);
				li.addEventListener('mousedown', function (e) {
					e.preventDefault();
					addItem(item);
					input.value = '';
					setExpanded(false);
					list.innerHTML = '';
					input.focus();
				});
				list.appendChild(li);
			});
			setExpanded(true);
		}

		function highlight(index) {
			var options = list.querySelectorAll('.itwt-combobox__option:not(.itwt-combobox__option--muted)');
			options.forEach(function (el) {
				el.classList.remove('is-active');
			});
			if (index < 0 || index >= options.length) {
				activeIndex = -1;
				return;
			}
			activeIndex = index;
			options[index].classList.add('is-active');
			options[index].scrollIntoView({ block: 'nearest' });
		}

		function fetchAssets(query) {
			if (!cfg.assetsRest) {
				return;
			}
			var url =
				cfg.assetsRest +
				(cfg.assetsRest.indexOf('?') >= 0 ? '&' : '?') +
				'search=' +
				encodeURIComponent(query || '');
			fetch(url, {
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'X-WP-Nonce': cfg.restNonce || '',
				},
			})
				.then(function (res) {
					if (!res.ok) {
						throw new Error('asset search failed');
					}
					return res.json();
				})
				.then(function (data) {
					if (lastQuery !== query) {
						return;
					}
					renderItems((data && data.items) || []);
				})
				.catch(function () {
					if (lastQuery !== query) {
						return;
					}
					renderItems([]);
				});
		}

		function scheduleSearch(query) {
			lastQuery = query;
			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				fetchAssets(query);
			}, 220);
		}

		chips.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-itwt-print-remove]');
			if (!btn) {
				return;
			}
			var chip = btn.closest('.itwt-print-picker__chip');
			if (!chip) {
				return;
			}
			removeId(parseInt(chip.getAttribute('data-id') || '0', 10));
		});

		input.addEventListener('focus', function () {
			scheduleSearch(input.value.trim());
		});

		input.addEventListener('input', function () {
			scheduleSearch(input.value.trim());
		});

		input.addEventListener('keydown', function (e) {
			var options = list.querySelectorAll('.itwt-combobox__option:not(.itwt-combobox__option--muted)');
			if (e.key === 'ArrowDown') {
				e.preventDefault();
				if (list.hidden) {
					scheduleSearch(input.value.trim());
					return;
				}
				highlight(activeIndex + 1 >= options.length ? 0 : activeIndex + 1);
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				highlight(activeIndex <= 0 ? options.length - 1 : activeIndex - 1);
			} else if (e.key === 'Enter') {
				if (!list.hidden && activeIndex >= 0 && options[activeIndex]) {
					e.preventDefault();
					options[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
				}
			} else if (e.key === 'Escape') {
				setExpanded(false);
			}
		});

		input.addEventListener('blur', function () {
			window.setTimeout(function () {
				setExpanded(false);
			}, 150);
		});

		syncHidden();
	}

	document.addEventListener('DOMContentLoaded', function () {
		initAssetPicker();
		initQrCodes();
	});
})();
