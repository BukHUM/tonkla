(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	onReady(function () {
		var selectAll = document.getElementById('demo-select-all');
		var unselectAll = document.getElementById('demo-unselect-all');
		if (!selectAll && !unselectAll) {
			return;
		}

		function setAll(checked) {
			document.querySelectorAll('.demo-page-checkbox').forEach(function (el) {
				el.checked = checked;
			});
		}

		if (selectAll) {
			selectAll.addEventListener('click', function (e) {
				e.preventDefault();
				setAll(true);
			});
		}
		if (unselectAll) {
			unselectAll.addEventListener('click', function (e) {
				e.preventDefault();
				setAll(false);
			});
		}
	});
})();
