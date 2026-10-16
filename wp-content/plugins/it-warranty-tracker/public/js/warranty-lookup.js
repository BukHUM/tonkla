(function () {
	'use strict';

	var cfg = window.itwtLookup || {};
	var portal = document.getElementById('itwt-portal');
	if (!portal) {
		return;
	}

	var form = document.getElementById('itwt-search-form');
	var input = document.getElementById('itwt-q');
	var statusEl = document.getElementById('itwt-status');
	var resultEl = document.getElementById('itwt-result');
	var skeletonEl = document.getElementById('itwt-skeleton');
	var dialog = document.getElementById('itwt-support-dialog');
	var supportForm = document.getElementById('itwt-support-form');
	var currentTag = '';
	var display = cfg.display || {};
	var i18n = cfg.i18n || {};

	function setStatus(state, message) {
		statusEl.hidden = !message;
		statusEl.dataset.state = state || '';
		statusEl.textContent = message || '';
	}

	function setSkeleton(on) {
		if (!skeletonEl) {
			return;
		}
		skeletonEl.hidden = !on;
	}

	function iconSvg(type) {
		if (type === 'ok') {
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9.5 16.2 5.3 12l1.4-1.4 2.8 2.8 7.2-7.2L18.1 7.6z"/></svg>';
		}
		if (type === 'warn') {
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>';
		}
		if (type === 'info') {
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
		}
		return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
	}

	function specIconSvg(icon) {
		var paths = {
			cpu: 'M9 3v2H7a2 2 0 0 0-2 2v2H3v2h2v2H3v2h2v2a2 2 0 0 0 2 2h2v2h2v-2h2v2h2v-2h2a2 2 0 0 0 2-2v-2h2v-2h-2v-2h2V9h-2V7a2 2 0 0 0-2-2h-2V3h-2v2h-2V3H9zm0 4h6v10H9V7z',
			memory:
				'M4 7h16v10H4V7zm2 2v6h2V9H6zm4 0v6h2V9h-2zm4 0v6h2V9h-2zm4 0v6h2V9h-2zM7 5h2v2H7V5zm4 0h2v2h-2V5zm4 0h2v2h-2V5zM7 17h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z',
			storage:
				'M4 6h16a1 1 0 0 1 1 1v4H3V7a1 1 0 0 1 1-1zm-1 7h18v4a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-4zm3 1.5a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5z',
			mainboard:
				'M3 4h18v16H3V4zm2 2v3h4V6H5zm6 0v3h8V6h-8zM5 11v3h5v-3H5zm7 0v7h7v-7h-7zM5 16v2h5v-2H5z',
			chassis:
				'M8 2h8a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zm2 3v2h4V5h-4zm0 4v8h4V9h-4zm1.5 10.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2z',
			power: 'M13 2 4 14h7l-1 8 10-14h-7l0-6z',
			network:
				'M12 3a3 3 0 0 1 3 3v1h2a2 2 0 0 1 2 2v2h1a1 1 0 1 1 0 2h-1v2a2 2 0 0 1-2 2h-2v1a3 3 0 1 1-6 0v-1H8a2 2 0 0 1-2-2v-2H5a1 1 0 1 1 0-2h1V9a2 2 0 0 1 2-2h2V6a3 3 0 0 1 3-3zm-1 14v1a1 1 0 1 0 2 0v-1h-2zM8 9v6h8V9H8z',
			os: 'M4 5h7v7H4V5zm9 0h7v7h-7V5zM4 14h7v5H4v-5zm9 0h7v5h-7v-5z',
			other: 'M12 2 2 7l10 5 10-5-10-5zm0 9L2 6v11l10 5 10-5V6l-10 5z',
		};
		var d = paths[icon] || '';
		if (!d) {
			return '';
		}
		return (
			'<span class="itwt-specs__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="' +
			d +
			'"/></svg></span>'
		);
	}

	function coverageIconType(status) {
		if (status === 'covered') {
			return 'ok';
		}
		if (status === 'expiring') {
			return 'warn';
		}
		if (status === 'upcoming') {
			return 'info';
		}
		return 'bad';
	}

	function endDateLabel(status) {
		if (status === 'upcoming') {
			return i18n.coverageStarts || 'เริ่มคุ้มครอง';
		}
		if (status === 'covered' || status === 'expiring') {
			return i18n.coverageUntil || 'คุ้มครองถึง';
		}
		return i18n.warrantyEnd || 'หมดประกันเมื่อ';
	}

	function endDateValue(asset, cov) {
		if (cov.status === 'upcoming' && cov.next_start) {
			return cov.next_start;
		}
		return asset.warranty_end || cov.latest_end || '';
	}

	function badgeClass(status) {
		return 'itwt-badge itwt-badge--' + status;
	}

	function uiFlags(asset) {
		var ui = asset.ui || {};
		return {
			showSupport: ui.show_support !== false && display.showSupport !== false,
			showLine:
				ui.show_line === true ||
				(ui.show_line === undefined && display.showLine === true),
			showPurchase: ui.show_purchase === true || display.showPurchase === true,
			showWarrantyEnd: ui.show_warranty_end !== false && display.showWarrantyEnd !== false,
			showSla: ui.show_sla !== false && display.showSla !== false,
			showCoverageBar: ui.show_coverage_bar !== false && display.showCoverageBar !== false,
			showContracts: ui.show_contracts !== false,
		};
	}

	function renewHintHtml(cov) {
		if (cov.renewable === false) {
			return (
				'<p class="itwt-renew-hint itwt-renew-hint--blocked">' +
				escapeHtml(i18n.renewHintBlocked || 'ไม่สามารถต่อประกันได้') +
				'</p>'
			);
		}
		if (
			cov.status === 'covered' ||
			cov.status === 'upcoming' ||
			cov.status === 'expiring' ||
			cov.status === 'expired'
		) {
			return (
				'<p class="itwt-renew-hint">' +
				escapeHtml(i18n.renewHint || 'สามารถต่อประกันได้เมื่อหมดระยะประกัน') +
				'</p>'
			);
		}
		return '';
	}

	function progressHtml(days, flags) {
		if (!flags.showCoverageBar || days === null || days === undefined || days < 0) {
			return '';
		}
		var max = 365;
		var value = Math.max(0, Math.min(max, days));
		var pct = Math.round((value / max) * 100);
		return (
			'<div class="itwt-progress-wrap">' +
			'<div class="itwt-progress-wrap__label">' +
			'<span>' +
			escapeHtml(i18n.daysLeft || 'วันคงเหลือ') +
			'</span>' +
			'<span>' +
			escapeHtml(String(days)) +
			'</span>' +
			'</div>' +
			'<div class="itwt-progress" role="progressbar" aria-valuemin="0" aria-valuemax="' +
			max +
			'" aria-valuenow="' +
			value +
			'" aria-label="' +
			escapeAttr(i18n.daysLeft || 'วันคงเหลือ') +
			'">' +
			'<div class="itwt-progress__bar" style="width:' +
			pct +
			'%"></div></div></div>'
		);
	}

	function metaItem(label, value) {
		if (!value) {
			return '';
		}
		return (
			'<div class="itwt-meta-item">' +
			'<span class="itwt-meta-item__label">' +
			escapeHtml(label) +
			'</span>' +
			'<span class="itwt-meta-item__value">' +
			escapeHtml(value) +
			'</span></div>'
		);
	}

	function renderAsset(asset) {
		currentTag = asset.service_tag;
		var cov = asset.coverage || {};
		var flags = uiFlags(asset);
		var isRenewable = cov.renewable !== false;
		var renewPrimary =
			isRenewable && (cov.status === 'expiring' || cov.status === 'expired');
		var subtitle = [asset.brand, asset.model_name, asset.asset_type_label || asset.asset_type]
			.filter(Boolean)
			.join(' · ');
		var endValue = endDateValue(asset, cov);

		var html = '<article class="itwt-card">';
		html += '<div class="itwt-card__hero">';
		html += '<div class="itwt-card__identity">';
		html += '<p class="itwt-card__tag">' + escapeHtml(asset.service_tag) + '</p>';
		html += '<p class="itwt-card__subtitle">' + escapeHtml(subtitle) + '</p>';
		html += '</div>';
		html += '<div class="itwt-card__coverage">';
		html +=
			'<div class="' +
			badgeClass(cov.status) +
			'">' +
			iconSvg(coverageIconType(cov.status)) +
			'<span>' +
			escapeHtml(cov.label || '') +
			'</span></div>';
		if (flags.showWarrantyEnd && endValue) {
			html +=
				'<p class="itwt-card__end">' +
				escapeHtml(endDateLabel(cov.status)) +
				': <strong>' +
				escapeHtml(endValue) +
				'</strong></p>';
		}
		if (cov.status === 'upcoming' && cov.latest_end) {
			html +=
				'<p class="itwt-card__end itwt-card__end--secondary">' +
				escapeHtml(i18n.coverageUntil || 'คุ้มครองถึง') +
				': <strong>' +
				escapeHtml(cov.latest_end) +
				'</strong></p>';
		}
		html += progressHtml(cov.days_remaining, flags);
		html += '</div></div>';

		var meta = '';
		if (flags.showPurchase && asset.purchase_date) {
			meta += metaItem(i18n.purchaseDate || 'วันซื้อ', asset.purchase_date);
		}
		if (asset.serial_masked) {
			meta += metaItem(i18n.serial || 'S/N', asset.serial_masked);
		}
		var slaValue = (cov.sla_tier || asset.sla_tier || '').trim();
		if (flags.showSla && slaValue) {
			meta += metaItem(i18n.slaService || i18n.sla || 'เงื่อนไขบริการ', slaValue);
		}
		if (meta) {
			html += '<div class="itwt-card__meta">' + meta + '</div>';
		}

		if (flags.showSupport || flags.showLine) {
			html += '<div class="itwt-actions" role="group" aria-label="actions">';
			if (flags.showSupport) {
				html +=
					'<button type="button" class="itwt-btn itwt-btn--repair' +
					(!renewPrimary ? ' itwt-btn--primary' : ' itwt-btn--secondary') +
					'" data-action="repair">' +
					escapeHtml(i18n.repair) +
					'</button>';
				if (isRenewable) {
					html +=
						'<button type="button" class="itwt-btn itwt-btn--renew' +
						(renewPrimary ? ' itwt-btn--primary' : ' itwt-btn--secondary') +
						'" data-action="renew">' +
						escapeHtml(i18n.renewMa) +
						'</button>';
				}
			}
			if (flags.showLine) {
				html +=
					'<button type="button" class="itwt-btn itwt-btn--line" data-action="line">' +
					escapeHtml(i18n.lineNotify) +
					'</button>';
			}
			html += '</div>';
		}

		if (asset.specs && asset.specs.length) {
			html +=
				'<section class="itwt-section"><h3 class="itwt-section-title">' +
				escapeHtml(i18n.specs || 'สเปก') +
				'</h3><dl class="itwt-specs">';
			asset.specs.forEach(function (s) {
				html += '<div class="itwt-specs__row">';
				html += '<dt class="itwt-specs__key">';
				html += specIconSvg(s.icon || '');
				html += '<span>' + escapeHtml(s.key) + '</span></dt>';
				html += '<dd class="itwt-specs__val">' + escapeHtml(s.value) + '</dd>';
				html += '</div>';
			});
			html += '</dl></section>';
		}

		if (flags.showContracts) {
			html +=
				'<section class="itwt-section"><h3 class="itwt-section-title">' +
				escapeHtml(i18n.contracts || 'สัญญา MA') +
				'</h3>';
			if (asset.contracts && asset.contracts.length) {
				html += '<ul class="itwt-contracts">';
				asset.contracts.forEach(function (c) {
					html += '<li class="itwt-contract">';
					html += '<div class="itwt-contract__top">';
					html +=
						'<span class="itwt-contract__type">' +
						escapeHtml(c.contract_type_label || c.contract_type) +
						'</span>';
					if (c.status_label) {
						html +=
							'<span class="itwt-chip itwt-chip--status-' +
							escapeAttr(c.status || '') +
							'">' +
							escapeHtml(c.status_label) +
							'</span>';
					}
					html += '</div>';
					html +=
						'<div class="itwt-contract__dates">' +
						escapeHtml(c.start_date) +
						' → ' +
						escapeHtml(c.end_date) +
						'</div>';
					if (flags.showSla && c.sla_tier) {
						html +=
							'<div class="itwt-contract__sla"><span class="itwt-chip">' +
							escapeHtml(i18n.sla || 'SLA') +
							'</span> ' +
							escapeHtml(c.sla_tier) +
							'</div>';
					}
					html += '</li>';
				});
				html += '</ul>';
			} else {
				html +=
					'<p class="itwt-empty-note">' +
					escapeHtml(i18n.contractsEmpty || 'ยังไม่มีสัญญา MA') +
					'</p>';
			}
			html += renewHintHtml(cov);
			html += '</section>';
		} else {
			html += renewHintHtml(cov);
		}

		if (asset.service_logs && asset.service_logs.length) {
			html +=
				'<section class="itwt-section"><h3 class="itwt-section-title">' +
				escapeHtml(i18n.serviceLogs || 'ประวัติบริการ') +
				'</h3><ul class="itwt-logs">';
			asset.service_logs.forEach(function (l) {
				html += '<li class="itwt-log">';
				html += '<div class="itwt-log__top">';
				html +=
					'<span class="itwt-log__type">' +
					escapeHtml(l.service_type_label || l.service_type) +
					'</span>';
				html += '<span class="itwt-log__date">' + escapeHtml(l.service_date) + '</span>';
				html += '</div>';
				html += '<p class="itwt-log__summary">' + escapeHtml(l.public_summary) + '</p>';
				html += '</li>';
			});
			html += '</ul></section>';
		}

		html += '</article>';
		resultEl.innerHTML = html;
		resultEl.hidden = false;

		resultEl.querySelectorAll('[data-action]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var action = btn.getAttribute('data-action');
				if (action === 'line') {
					requestLineCode();
					return;
				}
				openSupport(action === 'renew' ? 'renew_ma' : 'repair');
			});
		});
	}

	function renderChoices(choices) {
		var html = '<div class="itwt-choices"><p>' + escapeHtml(i18n.chooseAsset) + '</p>';
		choices.forEach(function (c) {
			html +=
				'<button type="button" class="itwt-btn" data-tag="' +
				escapeAttr(c.service_tag) +
				'">' +
				escapeHtml(c.brand) +
				' — ' +
				escapeHtml(c.model_name) +
				' (' +
				escapeHtml(c.service_tag) +
				')</button>';
		});
		html += '</div>';
		resultEl.innerHTML = html;
		resultEl.hidden = false;
		resultEl.querySelectorAll('[data-tag]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				lookup(btn.getAttribute('data-tag'));
			});
		});
	}

	function escapeHtml(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escapeAttr(str) {
		return escapeHtml(str).replace(/'/g, '&#39;');
	}

	function lookup(q) {
		setStatus('loading', i18n.loading);
		setSkeleton(true);
		resultEl.hidden = true;
		resultEl.innerHTML = '';

		fetch(cfg.restUrl + '/lookup?q=' + encodeURIComponent(q), {
			headers: { Accept: 'application/json', 'X-WP-Nonce': cfg.nonce },
		})
			.then(function (res) {
				if (res.status === 429) {
					setSkeleton(false);
					setStatus('rate', i18n.rateLimited);
					return null;
				}
				return res.json().then(function (data) {
					return { ok: res.ok, data: data, status: res.status };
				});
			})
			.then(function (payload) {
				setSkeleton(false);
				if (!payload) {
					return;
				}
				var data = payload.data;
				if (!payload.ok) {
					setStatus('error', (data && data.message) || i18n.error);
					return;
				}
				if (data.status === 'not_found') {
					setStatus('empty', i18n.notFound);
					return;
				}
				if (data.status === 'choices') {
					setStatus('', '');
					renderChoices(data.choices || []);
					return;
				}
				if (data.status === 'ok' && data.asset) {
					setStatus('', '');
					renderAsset(data.asset);
					return;
				}
				setStatus('error', i18n.error);
			})
			.catch(function () {
				setSkeleton(false);
				setStatus('error', i18n.error);
			});
	}

	function openSupport(type) {
		if (!dialog || typeof dialog.showModal !== 'function') {
			return;
		}
		document.getElementById('itwt-support-tag').value = currentTag;
		document.getElementById('itwt-request-type').value = type;
		document.getElementById('itwt-form-started').value = String(Math.floor(Date.now() / 1000));
		document.getElementById('itwt-support-msg').hidden = true;
		dialog.showModal();
	}

	function requestLineCode() {
		fetch(cfg.restUrl + '/line/link-code', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				Accept: 'application/json',
				'X-WP-Nonce': cfg.nonce,
			},
			body: JSON.stringify({ service_tag: currentTag }),
		})
			.then(function (res) {
				return res.json().then(function (data) {
					return { ok: res.ok, data: data };
				});
			})
			.then(function (payload) {
				if (!payload.ok) {
					setStatus('error', (payload.data && payload.data.message) || i18n.error);
					return;
				}
				var msg = 'รหัสผูก LINE: ' + payload.data.code + ' (หมดอายุ 15 นาที)';
				if (payload.data.deep_link) {
					window.location.href = payload.data.deep_link;
				}
				setStatus('ok', msg);
			})
			.catch(function () {
				setStatus('error', i18n.error);
			});
	}

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			lookup(input.value.trim());
		});
	}

	function closeSupportDialog() {
		if (!dialog) {
			return;
		}
		if (supportForm) {
			supportForm.reset();
			var msgEl = document.getElementById('itwt-support-msg');
			if (msgEl) {
				msgEl.hidden = true;
				msgEl.textContent = '';
			}
		}
		dialog.close();
	}

	if (dialog) {
		dialog.querySelectorAll('[data-itwt-dialog-close]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				closeSupportDialog();
			});
		});
		dialog.addEventListener('cancel', function () {
			if (supportForm) {
				supportForm.reset();
			}
		});
	}

	if (supportForm) {
		supportForm.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(supportForm);
			var body = {
				service_tag: fd.get('service_tag'),
				request_type: fd.get('request_type'),
				contact_name: fd.get('contact_name'),
				phone: fd.get('phone'),
				detail: fd.get('detail'),
				consent: fd.get('consent') === '1',
				website: fd.get('website') || '',
				form_started: parseInt(fd.get('form_started'), 10) || 0,
			};

			fetch(cfg.restUrl + '/support-request', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Accept: 'application/json',
					'X-WP-Nonce': cfg.nonce,
				},
				body: JSON.stringify(body),
			})
				.then(function (res) {
					return res.json().then(function (data) {
						return { ok: res.ok, data: data };
					});
				})
				.then(function (payload) {
					var msgEl = document.getElementById('itwt-support-msg');
					msgEl.hidden = false;
					if (!payload.ok) {
						msgEl.textContent = (payload.data && payload.data.message) || i18n.error;
						return;
					}
					var ref = payload.data.reference || '';
					msgEl.textContent =
						payload.data.message ||
						(ref ? 'ส่งคำขอแล้ว รหัสอ้างอิง ' + ref : 'ส่งคำขอแล้ว');
					supportForm.reset();
					setTimeout(function () {
						dialog.close();
					}, 1800);
				})
				.catch(function () {
					var msgEl = document.getElementById('itwt-support-msg');
					msgEl.hidden = false;
					msgEl.textContent = i18n.error;
				});
		});
	}

	var initial = portal.getAttribute('data-initial-tag');
	if (initial) {
		lookup(initial);
	} else if (i18n.emptyHint) {
		setStatus('empty', i18n.emptyHint);
	}
})();
