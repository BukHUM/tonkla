(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		if (typeof qrcode !== 'function') {
			return;
		}

		document.querySelectorAll('.itwt-list-qr[data-qr-url]').forEach(function (el) {
			var url = el.getAttribute('data-qr-url');
			var target = el.querySelector('.itwt-list-qr__canvas');
			if (!url || !target) {
				return;
			}

			try {
				var qr = qrcode(0, 'M');
				qr.addData(url);
				qr.make();
				target.innerHTML = qr.createImgTag(2, 0);
				var img = target.querySelector('img');
				if (img) {
					img.alt = '';
					img.width = 56;
					img.height = 56;
				}
			} catch (e) {
				target.textContent = '—';
			}
		});
	});
})();
