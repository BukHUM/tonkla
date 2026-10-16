(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
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
	});
})();
