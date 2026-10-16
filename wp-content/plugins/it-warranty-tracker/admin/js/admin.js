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

	document.addEventListener('DOMContentLoaded', function () {
		var genBtn = document.getElementById('itwt-gen-tag');
		var tagInput = document.getElementById('service_tag');
		if (genBtn && tagInput) {
			genBtn.addEventListener('click', function () {
				tagInput.value = randomTag();
			});
		}

		document.querySelectorAll('.itwt-years').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var start = document.querySelector('input[name="new_start_date"]');
				var end = document.getElementById('new_end_date');
				if (!start || !end) {
					return;
				}
				if (!start.value) {
					var today = new Date();
					start.value =
						today.getFullYear() +
						'-' +
						String(today.getMonth() + 1).padStart(2, '0') +
						'-' +
						String(today.getDate()).padStart(2, '0');
				}
				end.value = addYears(start.value, parseInt(btn.getAttribute('data-years'), 10) || 1);
			});
		});

		document.querySelectorAll('.itwt-ma-years').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var start = document.getElementById('ma_start');
				var end = document.getElementById('ma_end');
				if (!start || !end) {
					return;
				}
				if (!start.value) {
					var today = new Date();
					start.value =
						today.getFullYear() +
						'-' +
						String(today.getMonth() + 1).padStart(2, '0') +
						'-' +
						String(today.getDate()).padStart(2, '0');
				}
				end.value = addYears(start.value, parseInt(btn.getAttribute('data-years'), 10) || 1);
			});
		});

		var addSpec = document.getElementById('itwt-add-spec');
		var builder = document.getElementById('itwt-spec-builder');
		if (addSpec && builder) {
			addSpec.addEventListener('click', function () {
				var idx = builder.querySelectorAll('.itwt-spec-row').length;
				var row = document.createElement('div');
				row.className = 'itwt-spec-row';
				row.innerHTML =
					'<input type="text" name="spec_key[' +
					idx +
					']" placeholder="คีย์" />' +
					'<input type="text" name="spec_value[' +
					idx +
					']" placeholder="ค่า" />' +
					'<label><input type="checkbox" name="spec_public[' +
					idx +
					']" value="1" checked /> แสดงหน้าเว็บ</label>';
				builder.appendChild(row);
			});
		}

		var loadTpl = document.getElementById('itwt-load-template');
		if (loadTpl && builder && window.itwtAdmin && itwtAdmin.specTemplates) {
			loadTpl.addEventListener('click', function () {
				var typeSelect = document.querySelector('[data-itwt-spec-type]');
				var type = typeSelect ? typeSelect.value : 'hardware';
				var tpl = itwtAdmin.specTemplates[type] || [];
				builder.innerHTML = '';
				tpl.forEach(function (item, idx) {
					var row = document.createElement('div');
					row.className = 'itwt-spec-row';
					row.innerHTML =
						'<input type="text" name="spec_key[' +
						idx +
						']" value="' +
						(item.key || '') +
						'" />' +
						'<input type="text" name="spec_value[' +
						idx +
						']" value="' +
						(item.value || '') +
						'" />' +
						'<label><input type="checkbox" name="spec_public[' +
						idx +
						']" value="1" ' +
						(item.public ? 'checked' : '') +
						' /> แสดงหน้าเว็บ</label>';
					builder.appendChild(row);
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
	});
})();
