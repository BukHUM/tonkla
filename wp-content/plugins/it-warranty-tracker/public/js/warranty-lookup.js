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
	var dialog = document.getElementById('itwt-support-dialog');
	var supportForm = document.getElementById('itwt-support-form');
	var currentTag = '';

	function setStatus(state, message) {
		statusEl.hidden = !message;
		statusEl.dataset.state = state || '';
		statusEl.textContent = message || '';
	}

	function iconSvg(type) {
		if (type === 'ok') {
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9.5 16.2 5.3 12l1.4-1.4 2.8 2.8 7.2-7.2L18.1 7.6z"/></svg>';
		}
		if (type === 'warn') {
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>';
		}
		return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
	}

	function badgeClass(status) {
		return 'itwt-badge itwt-badge--' + status;
	}

	function progressHtml(days) {
		if (days === null || days === undefined || days < 0) {
			return '';
		}
		var max = 365;
		var value = Math.max(0, Math.min(max, days));
		var pct = Math.round((value / max) * 100);
		return (
			'<div class="itwt-progress" role="progressbar" aria-valuemin="0" aria-valuemax="' +
			max +
			'" aria-valuenow="' +
			value +
			'" aria-label="วันคงเหลือ">' +
			'<div class="itwt-progress__bar" style="width:' +
			pct +
			'%"></div></div>'
		);
	}

	function renderAsset(asset) {
		currentTag = asset.service_tag;
		var cov = asset.coverage || {};
		var showRenew = cov.status === 'expiring' || cov.status === 'expired';
		var html = '<article class="itwt-card">';
		html += '<div class="itwt-card__meta">';
		html += '<div class="itwt-card__tag">' + escapeHtml(asset.service_tag) + '</div>';
		html +=
			'<div>' +
			escapeHtml(asset.brand) +
			' · ' +
			escapeHtml(asset.model_name) +
			' · ' +
			escapeHtml(asset.asset_type_label || asset.asset_type) +
			'</div>';
		if (asset.serial_masked) {
			html += '<div>S/N ' + escapeHtml(asset.serial_masked) + '</div>';
		}
		html += '</div>';

		html +=
			'<div class="' +
			badgeClass(cov.status) +
			'">' +
			iconSvg(cov.status === 'covered' ? 'ok' : cov.status === 'expiring' ? 'warn' : 'bad') +
			'<span>' +
			escapeHtml(cov.label || '') +
			'</span></div>';
		html += progressHtml(cov.days_remaining);

		html += '<div class="itwt-actions">';
		html +=
			'<button type="button" class="itwt-btn" data-action="repair">' +
			escapeHtml(cfg.i18n.repair) +
			'</button>';
		html +=
			'<button type="button" class="itwt-btn' +
			(showRenew ? ' itwt-btn--renew' : '') +
			'" data-action="renew">' +
			escapeHtml(cfg.i18n.renewMa) +
			'</button>';
		html +=
			'<button type="button" class="itwt-btn" data-action="line">' +
			escapeHtml(cfg.i18n.lineNotify) +
			'</button>';
		html += '</div>';

		if (asset.specs && asset.specs.length) {
			html += '<h3 class="itwt-section-title">สเปก</h3><ul class="itwt-list">';
			asset.specs.forEach(function (s) {
				html += '<li><strong>' + escapeHtml(s.key) + '</strong>: ' + escapeHtml(s.value) + '</li>';
			});
			html += '</ul>';
		}

		if (asset.contracts && asset.contracts.length) {
			html += '<h3 class="itwt-section-title">ประวัติสัญญา</h3><ul class="itwt-list">';
			asset.contracts.forEach(function (c) {
				html +=
					'<li>' +
					escapeHtml(c.contract_type) +
					' · ' +
					escapeHtml(c.start_date) +
					' → ' +
					escapeHtml(c.end_date) +
					(c.sla_tier ? ' · ' + escapeHtml(c.sla_tier) : '') +
					'</li>';
			});
			html += '</ul>';
		}

		if (asset.service_logs && asset.service_logs.length) {
			html += '<h3 class="itwt-section-title">ประวัติบริการ</h3><ul class="itwt-list">';
			asset.service_logs.forEach(function (l) {
				html +=
					'<li>' +
					escapeHtml(l.service_date) +
					' · ' +
					escapeHtml(l.service_type) +
					' — ' +
					escapeHtml(l.public_summary) +
					'</li>';
			});
			html += '</ul>';
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
		var html = '<div class="itwt-choices"><p>' + escapeHtml(cfg.i18n.chooseAsset) + '</p>';
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
		setStatus('loading', cfg.i18n.loading);
		resultEl.hidden = true;
		resultEl.innerHTML = '';

		fetch(cfg.restUrl + '/lookup?q=' + encodeURIComponent(q), {
			headers: { Accept: 'application/json', 'X-WP-Nonce': cfg.nonce },
		})
			.then(function (res) {
				if (res.status === 429) {
					setStatus('rate', cfg.i18n.rateLimited);
					return null;
				}
				return res.json().then(function (data) {
					return { ok: res.ok, data: data, status: res.status };
				});
			})
			.then(function (payload) {
				if (!payload) {
					return;
				}
				var data = payload.data;
				if (!payload.ok) {
					setStatus('error', (data && data.message) || cfg.i18n.error);
					return;
				}
				if (data.status === 'not_found') {
					setStatus('empty', cfg.i18n.notFound);
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
				setStatus('error', cfg.i18n.error);
			})
			.catch(function () {
				setStatus('error', cfg.i18n.error);
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
					setStatus('error', (payload.data && payload.data.message) || cfg.i18n.error);
					return;
				}
				var msg =
					'รหัสผูก LINE: ' +
					payload.data.code +
					' (หมดอายุ 15 นาที)';
				if (payload.data.deep_link) {
					window.location.href = payload.data.deep_link;
				}
				setStatus('ok', msg);
			})
			.catch(function () {
				setStatus('error', cfg.i18n.error);
			});
	}

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			lookup(input.value.trim());
		});
	}

	if (supportForm) {
		supportForm.addEventListener('submit', function (e) {
			var val = e.submitter ? e.submitter.value : 'submit';
			if (val === 'cancel') {
				return;
			}
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
						msgEl.textContent = (payload.data && payload.data.message) || cfg.i18n.error;
						return;
					}
					msgEl.textContent = payload.data.message || 'OK';
					supportForm.reset();
					setTimeout(function () {
						dialog.close();
					}, 1200);
				})
				.catch(function () {
					var msgEl = document.getElementById('itwt-support-msg');
					msgEl.hidden = false;
					msgEl.textContent = cfg.i18n.error;
				});
		});
	}

	var initial = portal.getAttribute('data-initial-tag');
	if (initial) {
		lookup(initial);
	}
})();
