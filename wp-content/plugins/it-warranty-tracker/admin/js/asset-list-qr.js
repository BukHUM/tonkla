(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		if (typeof qrcode !== 'function') {
			return;
		}

		var modal = document.getElementById('itwt-asset-qr-modal');
		if (!modal) {
			return;
		}

		var titleEl = document.getElementById('itwt-asset-qr-modal-title');
		var tagEl = document.getElementById('itwt-asset-qr-tag');
		var canvas = document.getElementById('itwt-asset-qr-canvas');
		var linkEl = document.getElementById('itwt-asset-qr-link');
		var printEl = document.getElementById('itwt-asset-qr-print');

		function openModal() {
			modal.hidden = false;
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			var closeBtn = modal.querySelector('.itwt-modal__close');
			if (closeBtn) {
				closeBtn.focus();
			}
		}

		function closeModal() {
			modal.hidden = true;
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			if (canvas) {
				canvas.innerHTML = '';
			}
		}

		function renderQr(url) {
			if (!canvas || !url) {
				return;
			}
			canvas.innerHTML = '';
			try {
				var qr = qrcode(0, 'M');
				qr.addData(url);
				qr.make();
				canvas.innerHTML = qr.createImgTag(4, 0);
				var img = canvas.querySelector('img');
				if (img) {
					img.alt = '';
					img.width = 180;
					img.height = 180;
				}
			} catch (e) {
				canvas.textContent = '—';
			}
		}

		document.querySelectorAll('.itwt-open-asset-qr').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var url = btn.getAttribute('data-qr-url') || '';
				var tag = btn.getAttribute('data-qr-tag') || '';
				var printHref = btn.getAttribute('data-qr-print') || '';

				if (titleEl) {
					titleEl.textContent = tag
						? 'QR Code — ' + tag
						: 'QR Code';
				}
				if (tagEl) {
					tagEl.textContent = tag;
				}
				if (linkEl) {
					linkEl.href = url;
					linkEl.textContent = url;
				}
				if (printEl) {
					if (printHref) {
						printEl.href = printHref;
						printEl.hidden = false;
					} else {
						printEl.hidden = true;
					}
				}
				renderQr(url);
				openModal();
			});
		});

		modal.querySelectorAll('[data-itwt-modal-close]').forEach(function (el) {
			el.addEventListener('click', function () {
				closeModal();
			});
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && modal.classList.contains('is-open')) {
				closeModal();
			}
		});
	});
})();
