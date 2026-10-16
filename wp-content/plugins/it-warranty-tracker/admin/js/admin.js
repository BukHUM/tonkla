(function () {
	'use strict';

	var alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

	function randomTag() {
		var tag = '';
		for (var i = 0; i < 7; i++) {
			tag += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
		}
		return tag;
	}

	function addYears(startStr, years) {
		if (!startStr) {
			return '';
		}
		var d = new Date(startStr + 'T00:00:00');
		d.setFullYear(d.getFullYear() + years);
		var m = String(d.getMonth() + 1).padStart(2, '0');
		var day = String(d.getDate()).padStart(2, '0');
		return d.getFullYear() + '-' + m + '-' + day;
	}

	function todayIso() {
		var today = new Date();
		return (
			today.getFullYear() +
			'-' +
			String(today.getMonth() + 1).padStart(2, '0') +
			'-' +
			String(today.getDate()).padStart(2, '0')
		);
	}

	function pad2(n) {
		return String(n).padStart(2, '0');
	}

	function isoToDmy(iso) {
		if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
			return '';
		}
		var p = iso.split('-');
		return p[2] + '/' + p[1] + '/' + p[0];
	}

	function dmyToIso(raw) {
		var s = String(raw || '').trim();
		if (!s) {
			return '';
		}
		if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
			return s;
		}
		var m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
		if (!m) {
			return '';
		}
		var day = parseInt(m[1], 10);
		var mon = parseInt(m[2], 10);
		var yr = parseInt(m[3], 10);
		var dt = new Date(yr, mon - 1, day);
		if (dt.getFullYear() !== yr || dt.getMonth() !== mon - 1 || dt.getDate() !== day) {
			return '';
		}
		return yr + '-' + pad2(mon) + '-' + pad2(day);
	}

	function dateRoot(idOrEl) {
		if (!idOrEl) {
			return null;
		}
		if (typeof idOrEl === 'string') {
			var byId = document.getElementById(idOrEl);
			return byId ? byId.closest('[data-itwt-date]') : null;
		}
		if (idOrEl.closest) {
			return idOrEl.closest('[data-itwt-date]');
		}
		return null;
	}

	function getDateIso(idOrEl) {
		var root = dateRoot(idOrEl);
		if (!root) {
			return '';
		}
		var iso = root.querySelector('.itwt-date__iso');
		return iso ? iso.value : '';
	}

	function setDateIso(idOrEl, iso) {
		var root = dateRoot(idOrEl);
		if (!root) {
			return;
		}
		var text = root.querySelector('.itwt-date__text');
		var hidden = root.querySelector('.itwt-date__iso');
		var native = root.querySelector('.itwt-date__native');
		var value = iso || '';
		if (hidden) {
			hidden.value = value;
		}
		if (text) {
			text.value = isoToDmy(value);
			text.classList.toggle('is-invalid', false);
		}
		if (native) {
			native.value = value;
		}
	}

	function syncDateFromText(root) {
		var text = root.querySelector('.itwt-date__text');
		var hidden = root.querySelector('.itwt-date__iso');
		var native = root.querySelector('.itwt-date__native');
		if (!text || !hidden) {
			return;
		}
		var raw = text.value.trim();
		if (!raw) {
			hidden.value = '';
			if (native) {
				native.value = '';
			}
			text.classList.remove('is-invalid');
			return;
		}
		var iso = dmyToIso(raw);
		if (!iso) {
			text.classList.add('is-invalid');
			return;
		}
		text.classList.remove('is-invalid');
		text.value = isoToDmy(iso);
		hidden.value = iso;
		if (native) {
			native.value = iso;
		}
	}

	function initDateFields(scope) {
		var rootEl = scope || document;
		rootEl.querySelectorAll('[data-itwt-date]').forEach(function (root) {
			if (root.getAttribute('data-itwt-date-ready')) {
				return;
			}
			root.setAttribute('data-itwt-date-ready', '1');
			var text = root.querySelector('.itwt-date__text');
			var native = root.querySelector('.itwt-date__native');
			var cal = root.querySelector('.itwt-date__cal');

			if (text) {
				text.addEventListener('blur', function () {
					syncDateFromText(root);
				});
				text.addEventListener('change', function () {
					syncDateFromText(root);
				});
			}
			if (native) {
				native.addEventListener('change', function () {
					setDateIso(root, native.value || '');
				});
			}
			if (cal && native) {
				cal.addEventListener('click', function () {
					syncDateFromText(root);
					if (typeof native.showPicker === 'function') {
						try {
							native.showPicker();
							return;
						} catch (err) {
							/* fall through */
						}
					}
					native.focus();
					native.click();
				});
			}
		});
	}

	function buildDateFieldHtml(name, ariaLabel) {
		return (
			'<span class="itwt-date itwt-date--compact" data-itwt-date>' +
			'<input type="text" class="itwt-date__text" value="" placeholder="วว/ดด/ปปปป" inputmode="numeric" autocomplete="off" maxlength="10" aria-label="' +
			ariaLabel +
			'" />' +
			'<input type="hidden" class="itwt-date__iso" name="' +
			name +
			'" value="" />' +
			'<button type="button" class="itwt-date__cal" aria-label="เปิดปฏิทิน">' +
			'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>' +
			'</button>' +
			'<input type="date" class="itwt-date__native" value="" tabindex="-1" aria-hidden="true" />' +
			'</span>'
		);
	}

	function initTabs() {
		var tabs = Array.prototype.slice.call(document.querySelectorAll('.itwt-tab'));
		if (!tabs.length) {
			return;
		}
		var panels = document.querySelectorAll('.itwt-tab-panel');

		function activate(slug, focus) {
			var found = tabs.some(function (t) {
				return t.getAttribute('data-tab') === slug;
			});
			if (!found) {
				return;
			}
			tabs.forEach(function (t) {
				var on = t.getAttribute('data-tab') === slug;
				t.setAttribute('aria-selected', on ? 'true' : 'false');
				t.tabIndex = on ? 0 : -1;
				if (on && focus) {
					t.focus();
				}
			});
			panels.forEach(function (p) {
				p.hidden = p.getAttribute('data-tab') !== slug;
			});
		}

		tabs.forEach(function (tab, idx) {
			tab.addEventListener('click', function () {
				var slug = tab.getAttribute('data-tab');
				activate(slug, false);
				if (window.history && history.replaceState) {
					history.replaceState(null, '', '#' + slug);
				}
			});
			tab.addEventListener('keydown', function (e) {
				var next = null;
				if ('ArrowRight' === e.key) {
					next = tabs[(idx + 1) % tabs.length];
				} else if ('ArrowLeft' === e.key) {
					next = tabs[(idx - 1 + tabs.length) % tabs.length];
				} else if ('Home' === e.key) {
					next = tabs[0];
				} else if ('End' === e.key) {
					next = tabs[tabs.length - 1];
				}
				if (next) {
					e.preventDefault();
					next.click();
					next.focus();
				}
			});
		});

		if (window.location.hash) {
			activate(window.location.hash.slice(1), false);
		}
	}

	function initLogoPicker() {
		var selectBtn = document.getElementById('itwt-logo-select');
		var removeBtn = document.getElementById('itwt-logo-remove');
		var input = document.getElementById('logo_attachment_id');
		var preview = document.getElementById('itwt-logo-preview');
		if (!selectBtn || !removeBtn || !input || !preview) {
			return;
		}
		var empty = document.querySelector('.itwt-logo-picker__empty');
		var i18n = (window.itwtAdmin && itwtAdmin.i18n) || {};
		var frame = null;

		function setLogo(id, url) {
			input.value = id ? String(id) : '0';
			if (url) {
				preview.src = url;
			} else {
				preview.removeAttribute('src');
			}
			preview.hidden = !url;
			removeBtn.hidden = !url;
			if (empty) {
				empty.hidden = !!url;
			}
		}

		selectBtn.addEventListener('click', function () {
			if (!window.wp || !wp.media) {
				return;
			}
			if (!frame) {
				frame = wp.media({
					title: i18n.logoTitle || '',
					button: { text: i18n.logoButton || '' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
					setLogo(att.id, url);
				});
			}
			frame.open();
		});

		removeBtn.addEventListener('click', function () {
			setLogo(0, '');
			selectBtn.focus();
		});
	}

	function initRequestDrawer() {
		var drawer = document.getElementById('itwt-request-drawer');
		if (!drawer) {
			return;
		}
		var closeBtn = document.getElementById('itwt-drawer-close');
		var backdrop = document.getElementById('itwt-drawer-backdrop');

		function close() {
			drawer.hidden = true;
			document.body.classList.remove('itwt-drawer-open');
		}

		function open(data) {
			document.getElementById('itwt-d-ref').textContent = data.reference || '';
			document.getElementById('itwt-d-id').value = data.id || '';
			document.getElementById('itwt-d-type').textContent = data.request_type || '';
			document.getElementById('itwt-d-contact').textContent = data.contact_name || '';
			document.getElementById('itwt-d-phone').textContent = data.phone || '';
			document.getElementById('itwt-d-date').textContent = data.created_at || '';
			document.getElementById('itwt-d-detail').textContent = data.detail || '';
			document.getElementById('itwt-d-status').value = data.status || 'new';

			var tag = document.getElementById('itwt-d-tag');
			tag.textContent = data.service_tag || '—';
			if (data.asset_url) {
				tag.href = data.asset_url;
			} else {
				tag.removeAttribute('href');
			}

			drawer.hidden = false;
			document.body.classList.add('itwt-drawer-open');
			if (closeBtn) {
				closeBtn.focus();
			}
		}

		document.querySelectorAll('.itwt-open-request').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var raw = btn.getAttribute('data-request') || '{}';
				try {
					open(JSON.parse(raw));
				} catch (e) {
					/* ignore malformed payload */
				}
			});
		});

		if (closeBtn) {
			closeBtn.addEventListener('click', close);
		}
		if (backdrop) {
			backdrop.addEventListener('click', close);
		}
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !drawer.hidden) {
				close();
			}
		});
	}

	function fillCustomerForm(data) {
		var id = document.getElementById('itwt-customer-id');
		var code = document.getElementById('customer_code');
		var company = document.getElementById('company_name');
		var roleEl = document.getElementById('party_role');
		var typeEl = document.getElementById('customer_type');
		var tax = document.getElementById('tax_id');
		var locationEl = document.getElementById('business_location');
		var address = document.getElementById('address');
		var postal = document.getElementById('postal_code');
		var contact = document.getElementById('contact_name');
		var phone = document.getElementById('phone');
		var email = document.getElementById('email');
		var title = document.getElementById('itwt-customer-modal-title');
		var hint = document.getElementById('itwt-customer-code-hint');
		if (!id || !company) {
			return;
		}
		var isEdit = !!(data.id && data.id !== '0');
		id.value = data.id || '0';
		if (code) {
			code.value = data.code || '';
			code.readOnly = true;
			code.placeholder = isEdit ? '' : 'CUS-YYYY-XXXX';
		}
		if (hint) {
			hint.hidden = isEdit;
		}
		if (company) {
			company.value = data.company || '';
		}
		if (roleEl) {
			roleEl.value = data.role || 'customer';
		}
		if (typeEl) {
			typeEl.value = data.type || 'juristic';
		}
		if (tax) {
			tax.value = data.tax || '';
		}
		if (locationEl) {
			locationEl.value = data.location || 'thai';
		}
		if (address) {
			address.value = data.address || '';
		}
		if (postal) {
			postal.value = data.postal || '';
		}
		if (contact) {
			contact.value = data.contact || '';
		}
		if (phone) {
			phone.value = data.phone || '';
		}
		if (email) {
			email.value = data.email || '';
		}
		if (title) {
			var i18n = (window.itwtAdmin && itwtAdmin.i18n) || {};
			title.textContent = isEdit
				? title.getAttribute('data-edit-label') || i18n.editCustomer || title.textContent
				: title.getAttribute('data-create-label') || i18n.addCustomer || title.textContent;
		}
	}

	function resetCustomerForm() {
		fillCustomerForm({
			id: '0',
			code: '',
			company: '',
			role: 'customer',
			type: 'juristic',
			tax: '',
			location: 'thai',
			address: '',
			postal: '',
			contact: '',
			phone: '',
			email: '',
		});
		var form = document.getElementById('itwt-customer-form');
		if (form) {
			form.querySelectorAll('.notice').forEach(function (n) {
				n.remove();
			});
		}
	}

	function setSlaChecks(root, raw) {
		if (!root) {
			return;
		}
		var parts = String(raw || '')
			.split(/[\/,|]/)
			.map(function (s) {
				return s.trim();
			})
			.filter(Boolean);
		var knownLower = {};
		root.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
			var val = String(cb.value || '').trim();
			var key = val.toLowerCase();
			knownLower[key] = true;
			cb.checked = parts.some(function (p) {
				return p.toLowerCase() === key;
			});
		});
		var custom = parts.filter(function (p) {
			return !knownLower[p.toLowerCase()];
		});
		var other = root.querySelector('.itwt-sla-checks__other-input');
		if (other) {
			other.value = custom.join(' / ');
		}
	}

	function fillContractForm(data) {
		var idEl = document.getElementById('contract_id');
		var typeEl = document.getElementById('contract_type');
		var noEl = document.getElementById('contract_no');
		var slaRoot = document.getElementById('contract_sla');
		var statusEl = document.getElementById('contract_status');
		var title = document.getElementById('itwt-contract-modal-title');
		var hints = document.getElementById('itwt-contract-renew-hints');
		var isEdit = !!(data.id && data.id !== '0');

		if (idEl) {
			idEl.value = data.id || '0';
		}
		if (typeEl) {
			typeEl.value = data.type || 'ma';
			typeEl.disabled = false;
		}
		if (noEl) {
			noEl.value = data.no || '';
		}
		setDateIso('contract_start', data.start || '');
		setDateIso('contract_end', data.end || '');
		setSlaChecks(slaRoot, data.sla || '');
		if (statusEl) {
			statusEl.value = data.status || 'active';
		}
		document.querySelectorAll('.itwt-contract-status-field').forEach(function (el) {
			el.hidden = !isEdit;
			if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
				el.disabled = !isEdit;
			}
		});
		if (hints) {
			hints.hidden = isEdit || hints.getAttribute('data-has-content') === '0';
			if (!isEdit && hints.textContent.trim()) {
				hints.hidden = false;
			}
		}
		if (title) {
			title.textContent = isEdit
				? title.getAttribute('data-edit-label') || 'แก้ไขสัญญา'
				: title.getAttribute('data-create-label') || title.textContent;
		}
	}

	function resetContractForm(defaultType) {
		fillContractForm({
			id: '0',
			type: defaultType || 'ma',
			no: '',
			start: '',
			end: '',
			sla: '',
			status: 'active',
		});
	}

	function fillServiceForm(data) {
		var idEl = document.getElementById('service_log_id');
		var typeEl = document.getElementById('service_type');
		var symptom = document.getElementById('symptom');
		var resolution = document.getElementById('resolution');
		var summary = document.getElementById('public_summary');
		var title = document.getElementById('itwt-service-modal-title');
		var isEdit = !!(data.id && data.id !== '0');

		if (idEl) {
			idEl.value = data.id || '0';
		}
		setDateIso('service_date', data.date || todayIso());
		if (typeEl) {
			typeEl.value = data.type || 'claim';
		}
		if (symptom) {
			symptom.value = data.symptom || '';
		}
		if (resolution) {
			resolution.value = data.resolution || '';
		}
		if (summary) {
			summary.value = data.summary || '';
		}
		if (title) {
			title.textContent = isEdit
				? title.getAttribute('data-edit-label') || 'แก้ไขบันทึกบริการ'
				: title.getAttribute('data-create-label') || 'เพิ่มบันทึกบริการ';
		}
	}

	function resetServiceForm() {
		fillServiceForm({
			id: '0',
			date: todayIso(),
			type: 'claim',
			symptom: '',
			resolution: '',
			summary: '',
		});
	}

	function openModal(modal) {
		if (!modal) {
			return;
		}
		modal.hidden = false;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('itwt-modal-open');
		window.setTimeout(function () {
			var focusEl = modal.querySelector(
				'input:not([type="hidden"]), select, textarea, button.itwt-modal__close'
			);
			if (focusEl) {
				focusEl.focus();
			}
		}, 0);
	}

	function closeModal(modal) {
		if (!modal) {
			return;
		}
		modal.hidden = true;
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		if (!document.querySelector('.itwt-modal.is-open')) {
			document.body.classList.remove('itwt-modal-open');
		}
	}

	function initModals() {
		document.addEventListener('click', function (e) {
			var openBtn = e.target.closest('[data-itwt-modal-open]');
			if (openBtn) {
				e.preventDefault();
				var id = openBtn.getAttribute('data-itwt-modal-open');
				var modal = document.getElementById(id);
				if (!modal) {
					return;
				}
				if (id === 'itwt-customer-modal') {
					if (openBtn.getAttribute('data-mode') === 'edit') {
						fillCustomerForm({
							id: openBtn.getAttribute('data-id') || '0',
							code: openBtn.getAttribute('data-code') || '',
							company: openBtn.getAttribute('data-company') || '',
							role: openBtn.getAttribute('data-role') || 'customer',
							type: openBtn.getAttribute('data-type') || 'juristic',
							tax: openBtn.getAttribute('data-tax') || '',
							location: openBtn.getAttribute('data-location') || 'thai',
							address: openBtn.getAttribute('data-address') || '',
							postal: openBtn.getAttribute('data-postal') || '',
							contact: openBtn.getAttribute('data-contact') || '',
							phone: openBtn.getAttribute('data-phone') || '',
							email: openBtn.getAttribute('data-email') || '',
						});
					} else {
						resetCustomerForm();
					}
				}
				if (id === 'itwt-contract-modal') {
					if (openBtn.getAttribute('data-mode') === 'edit') {
						fillContractForm({
							id: openBtn.getAttribute('data-id') || '0',
							type: openBtn.getAttribute('data-type') || 'ma',
							no: openBtn.getAttribute('data-no') || '',
							start: openBtn.getAttribute('data-start') || '',
							end: openBtn.getAttribute('data-end') || '',
							sla: openBtn.getAttribute('data-sla') || '',
							status: openBtn.getAttribute('data-status') || 'active',
						});
					} else {
						resetContractForm(openBtn.getAttribute('data-type') || 'ma');
					}
				}
				if (id === 'itwt-service-modal') {
					if (openBtn.getAttribute('data-mode') === 'edit') {
						fillServiceForm({
							id: openBtn.getAttribute('data-id') || '0',
							date: openBtn.getAttribute('data-date') || '',
							type: openBtn.getAttribute('data-type') || 'claim',
							symptom: openBtn.getAttribute('data-symptom') || '',
							resolution: openBtn.getAttribute('data-resolution') || '',
							summary: openBtn.getAttribute('data-summary') || '',
						});
					} else {
						resetServiceForm();
					}
				}
				openModal(modal);
				return;
			}

			var closeBtn = e.target.closest('[data-itwt-modal-close]');
			if (closeBtn) {
				e.preventDefault();
				closeModal(closeBtn.closest('.itwt-modal'));
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') {
				return;
			}
			var open = document.querySelector('.itwt-modal.is-open');
			if (open) {
				closeModal(open);
			}
		});

		var auto = document.querySelector('[data-itwt-open-modal]');
		if (auto) {
			var autoId = auto.getAttribute('data-itwt-open-modal');
			openModal(document.getElementById(autoId));
		}
	}

	function initListBulk() {
		document.querySelectorAll('.itwt-bulk-form').forEach(function (form) {
			initOneBulkForm(form);
		});
	}

	function initOneBulkForm(form) {
		var panel = form.closest('.itwt-panel') || form.parentElement;
		var selectAll =
			(panel && panel.querySelector('.itwt-cb-select-all')) ||
			form.querySelector('thead .check-column input[type="checkbox"]');
		var rows = form.querySelectorAll('.itwt-cb-row');
		var hint = panel ? panel.querySelector('.itwt-bulk-selected-hint') : null;
		var action = panel ? panel.querySelector('.itwt-bulk-action') : null;

		function selectedCount() {
			var n = 0;
			rows.forEach(function (cb) {
				if (cb.checked) {
					n += 1;
				}
			});
			return n;
		}

		function syncHint() {
			var n = selectedCount();
			if (selectAll) {
				selectAll.checked = rows.length > 0 && n === rows.length;
				selectAll.indeterminate = n > 0 && n < rows.length;
			}
			if (hint) {
				if (n > 0) {
					hint.hidden = false;
					hint.textContent = n + ' รายการที่เลือก';
				} else {
					hint.hidden = true;
					hint.textContent = '';
				}
			}
		}

		if (selectAll) {
			selectAll.addEventListener('change', function () {
				rows.forEach(function (cb) {
					cb.checked = selectAll.checked;
				});
				syncHint();
			});
		}
		rows.forEach(function (cb) {
			cb.addEventListener('change', syncHint);
		});
		syncHint();

		form.addEventListener('submit', function (e) {
			var bulk = action ? action.value : '';
			var n = selectedCount();
			var i18n = (window.itwtAdmin && itwtAdmin.i18n) || {};
			if (form.getAttribute('data-itwt-confirmed')) {
				form.removeAttribute('data-itwt-confirmed');
				return;
			}
			if (!bulk) {
				e.preventDefault();
				showToast('warning', i18n.bulkNeedAction || 'เลือกการดำเนินการแบบกลุ่มก่อน');
				return;
			}
			if (n < 1) {
				e.preventDefault();
				showToast('warning', i18n.bulkNeedItems || 'เลือกอย่างน้อย 1 รายการ');
				return;
			}
			if (bulk === 'delete') {
				e.preventDefault();
				itwtConfirm({
					title: i18n.confirmDeleteTitle || 'ยืนยันการลบ',
					message: i18n.confirmDeleteBulk || i18n.confirmDelete || 'ยืนยันการลบรายการที่เลือก?',
					confirmText: i18n.confirmOk || 'ลบ',
					cancelText: i18n.confirmCancel || 'ยกเลิก',
					danger: true,
				}).then(function (ok) {
					if (!ok) {
						return;
					}
					form.setAttribute('data-itwt-confirmed', '1');
					if (typeof form.requestSubmit === 'function') {
						form.requestSubmit();
					} else {
						form.submit();
					}
				});
			}
		});
	}

	function ensureToastHost() {
		var host = document.getElementById('itwt-toast-host');
		if (host) {
			return host;
		}
		host = document.createElement('div');
		host.id = 'itwt-toast-host';
		host.className = 'itwt-toast-host';
		host.setAttribute('aria-live', 'polite');
		host.setAttribute('aria-relevant', 'additions');
		document.body.appendChild(host);
		return host;
	}

	function dismissToast(el) {
		if (!el || el.classList.contains('is-out')) {
			return;
		}
		el.classList.remove('is-in');
		el.classList.add('is-out');
		window.setTimeout(function () {
			if (el.parentNode) {
				el.parentNode.removeChild(el);
			}
		}, 200);
	}

	function showToast(type, message, options) {
		if (!message) {
			return;
		}
		options = options || {};
		var host = ensureToastHost();
		var el = document.createElement('div');
		el.className = 'itwt-toast itwt-toast--' + (type || 'info');
		el.setAttribute('role', type === 'error' ? 'alert' : 'status');

		var bar = document.createElement('span');
		bar.className = 'itwt-toast__bar';
		bar.setAttribute('aria-hidden', 'true');

		var body = document.createElement('div');
		body.className = 'itwt-toast__body';
		body.textContent = message;

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'itwt-toast__close';
		close.setAttribute('aria-label', 'ปิด');
		close.innerHTML = '&times;';
		close.addEventListener('click', function () {
			dismissToast(el);
		});

		el.appendChild(bar);
		el.appendChild(body);
		el.appendChild(close);
		host.appendChild(el);

		window.requestAnimationFrame(function () {
			el.classList.add('is-in');
		});

		var ttl = options.ttl;
		if (ttl === undefined) {
			if (type === 'error') {
				ttl = 0;
			} else if (type === 'warning') {
				ttl = 7000;
			} else if (type === 'info') {
				ttl = 5000;
			} else {
				ttl = 4000;
			}
		}
		if (ttl > 0) {
			window.setTimeout(function () {
				dismissToast(el);
			}, ttl);
		}
	}

	function initToasts() {
		window.itwtToast = {
			show: showToast,
		};
		var node = document.getElementById('itwt-toast-queue');
		if (!node || !node.textContent) {
			return;
		}
		var items;
		try {
			items = JSON.parse(node.textContent);
		} catch (e) {
			return;
		}
		if (!Array.isArray(items)) {
			return;
		}
		items.forEach(function (item, i) {
			window.setTimeout(function () {
				showToast(item.type || 'info', item.message || '');
			}, i * 80);
		});
		node.parentNode.removeChild(node);
	}

	var confirmOpen = false;

	function i18nText(key, fallback) {
		var i18n = (window.itwtAdmin && itwtAdmin.i18n) || {};
		return i18n[key] || fallback;
	}

	function itwtConfirm(options) {
		options = options || {};
		return new Promise(function (resolve) {
			if (confirmOpen) {
				resolve(false);
				return;
			}
			confirmOpen = true;

			var overlay = document.createElement('div');
			overlay.className = 'itwt-confirm is-open';
			overlay.setAttribute('role', 'presentation');

			var panel = document.createElement('div');
			panel.className = 'itwt-confirm__panel';
			panel.setAttribute('role', 'alertdialog');
			panel.setAttribute('aria-modal', 'true');
			panel.setAttribute('aria-labelledby', 'itwt-confirm-title');
			panel.setAttribute('aria-describedby', 'itwt-confirm-message');

			var title = document.createElement('h2');
			title.id = 'itwt-confirm-title';
			title.className = 'itwt-confirm__title';
			title.textContent = options.title || i18nText('confirmDeleteTitle', 'ยืนยันการลบ');

			var message = document.createElement('p');
			message.id = 'itwt-confirm-message';
			message.className = 'itwt-confirm__message';
			message.textContent =
				options.message || i18nText('confirmDelete', 'ยืนยันการลบรายการนี้?');

			var actions = document.createElement('div');
			actions.className = 'itwt-confirm__actions';

			var cancelBtn = document.createElement('button');
			cancelBtn.type = 'button';
			cancelBtn.className = 'itwt-action';
			cancelBtn.textContent = options.cancelText || i18nText('confirmCancel', 'ยกเลิก');

			var okBtn = document.createElement('button');
			okBtn.type = 'button';
			okBtn.className =
				'itwt-action ' + (options.danger === false ? 'itwt-action--primary' : 'itwt-action--danger');
			okBtn.textContent = options.confirmText || i18nText('confirmOk', 'ลบ');

			actions.appendChild(cancelBtn);
			actions.appendChild(okBtn);
			panel.appendChild(title);
			panel.appendChild(message);
			panel.appendChild(actions);
			overlay.appendChild(panel);
			document.body.appendChild(overlay);
			document.body.classList.add('itwt-modal-open');

			function close(result) {
				confirmOpen = false;
				document.body.classList.remove('itwt-modal-open');
				if (overlay.parentNode) {
					overlay.parentNode.removeChild(overlay);
				}
				document.removeEventListener('keydown', onKey);
				resolve(result);
			}

			function onKey(e) {
				if (e.key === 'Escape') {
					e.preventDefault();
					close(false);
				} else if (e.key === 'Enter') {
					e.preventDefault();
					close(true);
				}
			}

			cancelBtn.addEventListener('click', function () {
				close(false);
			});
			okBtn.addEventListener('click', function () {
				close(true);
			});
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) {
					close(false);
				}
			});
			document.addEventListener('keydown', onKey);
			window.setTimeout(function () {
				okBtn.focus();
			}, 0);
		});
	}

	function initConfirmActions() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-itwt-confirm]');
			if (!btn) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();

			var kind = btn.getAttribute('data-itwt-confirm') || 'delete';
			var opts =
				kind === 'delete'
					? {
							title: i18nText('confirmDeleteTitle', 'ยืนยันการลบ'),
							message: i18nText('confirmDelete', 'ยืนยันการลบรายการนี้?'),
							confirmText: i18nText('confirmOk', 'ลบ'),
							danger: true,
						}
					: {
							title: btn.getAttribute('data-itwt-confirm-title') || '',
							message: btn.getAttribute('data-itwt-confirm-message') || '',
							confirmText: btn.getAttribute('data-itwt-confirm-ok') || i18nText('confirmOk', 'ตกลง'),
							danger: btn.getAttribute('data-itwt-confirm-danger') !== '0',
						};

			itwtConfirm(opts).then(function (ok) {
				if (!ok) {
					return;
				}
				var formId = btn.getAttribute('form');
				var form = formId ? document.getElementById(formId) : btn.closest('form');
				if (form) {
					if (typeof form.requestSubmit === 'function') {
						form.requestSubmit();
					} else {
						form.submit();
					}
					return;
				}
				if (typeof btn.click === 'function' && btn.tagName === 'A' && btn.href) {
					window.location.href = btn.href;
				}
			});
		});

		document.addEventListener(
			'submit',
			function (e) {
				var form = e.target;
				if (!form || !form.getAttribute || !form.getAttribute('data-itwt-confirm-form')) {
					return;
				}
				if (form.getAttribute('data-itwt-confirmed')) {
					form.removeAttribute('data-itwt-confirmed');
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				itwtConfirm({
					title: i18nText('confirmDeleteTitle', 'ยืนยันการลบ'),
					message: i18nText('confirmDelete', 'ยืนยันการลบรายการนี้?'),
					confirmText: i18nText('confirmOk', 'ลบ'),
					danger: true,
				}).then(function (ok) {
					if (!ok) {
						return;
					}
					form.setAttribute('data-itwt-confirmed', '1');
					if (typeof form.requestSubmit === 'function') {
						form.requestSubmit();
					} else {
						form.submit();
					}
				});
			},
			true
		);
	}

	function initCustomerPicker() {
		var root = document.querySelector('[data-itwt-customer-picker]');
		if (!root) {
			return;
		}
		var hidden = root.querySelector('#customer_id');
		var input = root.querySelector('.itwt-combobox__input');
		var list = root.querySelector('.itwt-combobox__list');
		var selectedBox = root.querySelector('[data-itwt-customer-selected]');
		var codeEl = root.querySelector('[data-itwt-customer-code]');
		var nameEl = root.querySelector('[data-itwt-customer-name]');
		var clearBtn = root.querySelector('[data-itwt-customer-clear]');
		if (!hidden || !input || !list) {
			return;
		}

		var cfg = window.itwtAdmin || {};
		var i18n = cfg.i18n || {};
		var timer = null;
		var lastQuery = null;
		var lockedLabel = input.value || '';
		var lockedCode = codeEl ? codeEl.textContent || '' : '';
		var lockedName = nameEl ? nameEl.textContent || '' : '';
		var activeIndex = -1;

		function setExpanded(open) {
			input.setAttribute('aria-expanded', open ? 'true' : 'false');
			list.hidden = !open;
			if (!open) {
				activeIndex = -1;
			}
		}

		function syncSelectedUi() {
			var has = hidden.value !== '0' && hidden.value !== '';
			root.classList.toggle('is-selected', has);
			if (selectedBox) {
				selectedBox.hidden = !has;
				selectedBox.classList.toggle('is-visible', has);
			}
			if (codeEl) {
				codeEl.textContent = has ? lockedCode : '';
			}
			if (nameEl) {
				nameEl.textContent = has ? lockedName : '';
			}
		}

		function clearSelection() {
			hidden.value = '0';
			lockedLabel = '';
			lockedCode = '';
			lockedName = '';
			input.value = '';
			syncSelectedUi();
		}

		function selectItem(item) {
			hidden.value = String(item.id || 0);
			lockedCode = item.code || '';
			lockedName = item.name || '';
			lockedLabel = item.label || (lockedCode && lockedName ? lockedCode + ' — ' + lockedName : '');
			input.value = lockedLabel;
			setExpanded(false);
			list.innerHTML = '';
			syncSelectedUi();
		}

		syncSelectedUi();
		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				clearSelection();
				input.focus();
			});
		}

		function renderItems(items) {
			list.innerHTML = '';
			activeIndex = -1;

			var clear = document.createElement('li');
			clear.className = 'itwt-combobox__option itwt-combobox__option--muted';
			clear.setAttribute('role', 'option');
			clear.textContent = i18n.customerClear || 'ไม่ระบุลูกค้า';
			clear.addEventListener('mousedown', function (e) {
				e.preventDefault();
				clearSelection();
				setExpanded(false);
			});
			list.appendChild(clear);

			if (!items.length) {
				var empty = document.createElement('li');
				empty.className = 'itwt-combobox__option itwt-combobox__option--muted';
				empty.setAttribute('role', 'presentation');
				empty.textContent = i18n.customerNone || 'ไม่พบรายชื่อ';
				list.appendChild(empty);
				setExpanded(true);
				return;
			}

			items.forEach(function (item, idx) {
				var li = document.createElement('li');
				li.className = 'itwt-combobox__option';
				li.setAttribute('role', 'option');
				li.setAttribute('data-index', String(idx));
				li.textContent = item.label;
				li.addEventListener('mousedown', function (e) {
					e.preventDefault();
					selectItem(item);
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

		function fetchCustomers(query) {
			if (!cfg.customersRest) {
				return;
			}
			var url = cfg.customersRest + (cfg.customersRest.indexOf('?') >= 0 ? '&' : '?') + 'search=' + encodeURIComponent(query || '');
			fetch(url, {
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'X-WP-Nonce': cfg.restNonce || '',
				},
			})
				.then(function (res) {
					if (!res.ok) {
						throw new Error('customer search failed');
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
				fetchCustomers(query);
			}, 220);
		}

		input.addEventListener('focus', function () {
			scheduleSearch(input.value.trim() === lockedLabel ? '' : input.value.trim());
		});

		input.addEventListener('input', function () {
			if (hidden.value !== '0' && input.value !== lockedLabel) {
				hidden.value = '0';
				lockedCode = '';
				lockedName = '';
				syncSelectedUi();
			}
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
				if (hidden.value !== '0') {
					input.value = lockedLabel;
				}
			}
		});

		input.addEventListener('blur', function () {
			window.setTimeout(function () {
				setExpanded(false);
				if (hidden.value === '0') {
					if (input.value.trim() === '') {
						lockedLabel = '';
					} else if (input.value !== lockedLabel) {
						// Keep typed text but no id until user picks — clear to avoid stale label.
						input.value = '';
						lockedLabel = '';
					}
				} else {
					input.value = lockedLabel;
				}
			}, 120);
		});

		document.addEventListener('click', function (e) {
			if (!root.contains(e.target)) {
				setExpanded(false);
			}
		});
	}

	function bootAdmin() {
		initToasts();
		initConfirmActions();
		initTabs();
		initLogoPicker();
		initRequestDrawer();
		initModals();
		initListBulk();
		initCustomerPicker();
		window.itwtConfirm = itwtConfirm;

		var genBtn = document.getElementById('itwt-gen-tag');
		var tagInput = document.getElementById('service_tag');
		if (genBtn && tagInput) {
			genBtn.addEventListener('click', function () {
				tagInput.value = randomTag();
			});
		}

		initDateFields(document);

		document.querySelectorAll('form').forEach(function (form) {
			form.addEventListener('submit', function () {
				form.querySelectorAll('[data-itwt-date]').forEach(function (root) {
					syncDateFromText(root);
				});
			});
		});

		document.querySelectorAll('.itwt-warranty-years').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!dateRoot('purchase_date') || !dateRoot('warranty_end_date')) {
					return;
				}
				if (!getDateIso('purchase_date')) {
					setDateIso('purchase_date', todayIso());
				}
				setDateIso(
					'warranty_end_date',
					addYears(getDateIso('purchase_date'), parseInt(btn.getAttribute('data-years'), 10) || 1)
				);
			});
		});

		document.querySelectorAll('[data-itwt-renew-toggle]').forEach(function (cb) {
			cb.addEventListener('change', function () {
				var kind = cb.getAttribute('data-itwt-renew-toggle');
				var input = document.getElementById(kind === 'years' ? 'renew_max_years' : 'renew_max_times');
				if (input) {
					input.disabled = !cb.checked;
				}
			});
		});

		var addComp = document.getElementById('itwt-add-comp');
		var compBuilder = document.getElementById('itwt-comp-builder');
		if (compBuilder) {
			compBuilder.addEventListener('click', function (e) {
				var btn = e.target.closest('.itwt-comp-row__remove');
				if (!btn || !compBuilder.contains(btn)) {
					return;
				}
				var row = btn.closest('.itwt-comp-row');
				if (!row) {
					return;
				}
				row.remove();
				if (!compBuilder.querySelector('.itwt-comp-row')) {
					var empty = buildCompRow(0);
					compBuilder.appendChild(empty);
					initDateFields(empty);
				}
			});
		}
		if (addComp && compBuilder) {
			addComp.addEventListener('click', function () {
				var idx = compBuilder.querySelectorAll('.itwt-comp-row').length;
				var row = buildCompRow(idx);
				compBuilder.appendChild(row);
				initDateFields(row);
			});
		}

		function buildCompRow(idx) {
			var row = document.createElement('div');
			row.className = 'itwt-comp-row';
			row.innerHTML =
				'<input type="text" name="comp_name[' +
				idx +
				']" placeholder="เช่น CPU / PSU" aria-label="รายการ" />' +
				'<input type="text" name="comp_source[' +
				idx +
				']" placeholder="ร้าน / ผู้จำหน่าย" aria-label="ซื้อจาก" />' +
				buildDateFieldHtml('comp_warranty_end[' + idx + ']', 'หมดประกันต้นทาง') +
				'<input type="text" name="comp_note[' +
				idx +
				']" placeholder="หมายเหตุ" aria-label="หมายเหตุ" />' +
				'<button type="button" class="itwt-comp-row__remove" aria-label="ลบแถว">&times;</button>';
			return row;
		}

		document.querySelectorAll('.itwt-years').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!dateRoot('new_start_date') || !dateRoot('new_end_date')) {
					return;
				}
				if (!getDateIso('new_start_date')) {
					setDateIso('new_start_date', todayIso());
				}
				setDateIso(
					'new_end_date',
					addYears(getDateIso('new_start_date'), parseInt(btn.getAttribute('data-years'), 10) || 1)
				);
			});
		});

		document.querySelectorAll('.itwt-contract-years').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!dateRoot('contract_start') || !dateRoot('contract_end')) {
					return;
				}
				if (!getDateIso('contract_start')) {
					setDateIso('contract_start', todayIso());
				}
				setDateIso(
					'contract_end',
					addYears(getDateIso('contract_start'), parseInt(btn.getAttribute('data-years'), 10) || 1)
				);
			});
		});

		var addSpec = document.getElementById('itwt-add-spec');
		var builder = document.getElementById('itwt-spec-builder');

		function escapeAttr(value) {
			return String(value == null ? '' : value)
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function buildSpecIconSelect(idx, selected) {
			var icons = (window.itwtAdmin && itwtAdmin.specIcons) || { '': '— ไม่มีไอคอน —' };
			var html =
				'<select name="spec_icon[' + idx + ']" aria-label="ไอคอน">';
			Object.keys(icons).forEach(function (slug) {
				html +=
					'<option value="' +
					escapeAttr(slug) +
					'"' +
					(selected === slug ? ' selected' : '') +
					'>' +
					escapeAttr(icons[slug]) +
					'</option>';
			});
			html += '</select>';
			return html;
		}

		function buildSpecRow(idx, item) {
			item = item || {};
			var row = document.createElement('div');
			row.className = 'itwt-spec-row';
			row.innerHTML =
				'<input type="text" name="spec_key[' +
				idx +
				']" placeholder="เช่น CPU" aria-label="คีย์" value="' +
				escapeAttr(item.key || '') +
				'" />' +
				'<input type="text" name="spec_value[' +
				idx +
				']" placeholder="เช่น 32 Cores" aria-label="ค่า" value="' +
				escapeAttr(item.value || '') +
				'" />' +
				buildSpecIconSelect(idx, item.icon || '') +
				'<label class="itwt-spec-row__public"><input type="checkbox" name="spec_public[' +
				idx +
				']" value="1" ' +
				(item.public === false ? '' : 'checked') +
				' /> <span>แสดงหน้าเว็บ</span></label>' +
				'<button type="button" class="itwt-spec-row__remove" aria-label="ลบแถวสเปก">&times;</button>';
			return row;
		}

		function ensureSpecHead() {
			if (!builder || builder.querySelector('.itwt-spec-builder__head')) {
				return;
			}
			var head = document.createElement('div');
			head.className = 'itwt-spec-builder__head';
			head.setAttribute('aria-hidden', 'true');
			head.innerHTML = '<span>คีย์</span><span>ค่า</span><span>ไอคอน</span><span>สาธารณะ</span><span></span>';
			builder.insertBefore(head, builder.firstChild);
		}

		if (builder) {
			builder.addEventListener('click', function (e) {
				var btn = e.target.closest('.itwt-spec-row__remove');
				if (!btn || !builder.contains(btn)) {
					return;
				}
				var row = btn.closest('.itwt-spec-row');
				if (!row) {
					return;
				}
				row.remove();
				if (!builder.querySelector('.itwt-spec-row')) {
					ensureSpecHead();
					builder.appendChild(buildSpecRow(0, { key: '', value: '', icon: '', public: true }));
				}
			});
		}

		if (addSpec && builder) {
			addSpec.addEventListener('click', function () {
				ensureSpecHead();
				var idx = builder.querySelectorAll('.itwt-spec-row').length;
				builder.appendChild(buildSpecRow(idx, { key: '', value: '', icon: '', public: true }));
			});
		}

		var loadTpl = document.getElementById('itwt-load-template');
		if (loadTpl && builder && window.itwtAdmin && itwtAdmin.specTemplates) {
			loadTpl.addEventListener('click', function () {
				var typeSelect = document.querySelector('[data-itwt-spec-type]');
				var type = typeSelect ? typeSelect.value : 'hardware';
				var tpl = itwtAdmin.specTemplates[type] || [];
				builder.innerHTML = '';
				ensureSpecHead();
				if (!tpl.length) {
					builder.appendChild(buildSpecRow(0, { key: '', value: '', icon: '', public: true }));
					return;
				}
				tpl.forEach(function (item, idx) {
					builder.appendChild(
						buildSpecRow(idx, {
							key: item.key || '',
							value: item.value || '',
							icon: item.icon || '',
							public: item.public !== false,
						})
					);
				});
			});
		}

		var typesBox = document.getElementById('itwt-asset-types');
		var addTypeBtn = document.getElementById('itwt-add-asset-type');
		if (typesBox && addTypeBtn) {
			addTypeBtn.addEventListener('click', function () {
				var idx = typesBox.querySelectorAll('.itwt-asset-type-row').length;
				var row = document.createElement('div');
				row.className = 'itwt-asset-type-row';
				row.innerHTML =
					'<label><span class="screen-reader-text">Slug</span>' +
					'<input type="text" name="asset_type_slug[' +
					idx +
					']" placeholder="slug" pattern="[a-z0-9_\\-]{1,30}" maxlength="30" required /></label>' +
					'<label><span class="screen-reader-text">ชื่อที่แสดง</span>' +
					'<input type="text" name="asset_type_label[' +
					idx +
					']" placeholder="ชื่อที่แสดง" required /></label>' +
					'<button type="button" class="button itwt-remove-type">ลบ</button>';
				typesBox.appendChild(row);
			});

			typesBox.addEventListener('click', function (e) {
				var btn = e.target.closest('.itwt-remove-type');
				if (!btn) {
					return;
				}
				var rows = typesBox.querySelectorAll('.itwt-asset-type-row');
				if (rows.length <= 1) {
					return;
				}
				btn.closest('.itwt-asset-type-row').remove();
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootAdmin);
	} else {
		bootAdmin();
	}
})();
