(function () {
	var page = 1;
	function backup() { var b = document.getElementById('tonkla-db-backup'); return b && b.checked ? 1 : ''; }
	function status(t) { document.getElementById('tonkla-db-status').textContent = t || ''; }
	function post(data) { data.nonce = tonklaDb.nonce; var body = new URLSearchParams(); Object.keys(data).forEach(function (k) { if (Array.isArray(data[k])) { data[k].forEach(function (v) { body.append(k + '[]', v); }); } else { body.append(k, data[k]); } }); return fetch(tonklaDb.ajaxurl, { method: 'POST', credentials: 'same-origin', body: body }).then(function (r) { return r.json(); }); }
	var currentType = 'options';
	function type() { return currentType; }
	function deletable() { var d = document.getElementById('tonkla-db-deletable'); return d && d.checked ? '1' : ''; }
	function load() {
		status('Loading...');
		fetch(tonklaDb.ajaxurl + '?action=tonkla_db_list&nonce=' + encodeURIComponent(tonklaDb.nonce) + '&type=' + encodeURIComponent(type()) + '&deletable=' + encodeURIComponent(deletable()) + '&page=' + page, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); }).then(function (res) {
				var tbody = document.getElementById('tonkla-db-rows'); tbody.innerHTML = '';
				if (!res.success || !res.data.rows.length) { tbody.innerHTML = '<tr><td colspan="5">' + (deletable() ? 'No deletable items found for this type. Untick the filter to see all scanned items.' : 'No results. Run a scan first.') + '</td></tr>'; }
				res.data.rows.forEach(function (row) {
					var orphan = row.belongs_to === 'Unknown / Orphaned candidate' || row.belongs_to === 'Expired transient';
					var tr = document.createElement('tr');
					tr.innerHTML = '<td><input type="checkbox" class="tonkla-db-check" value="" ' + (orphan ? '' : 'disabled') + '></td><td></td><td></td><td></td><td></td>';
					tr.querySelector('.tonkla-db-check').value = row.id;
					tr.children[1].textContent = row.item_name; tr.children[2].textContent = row.belongs_to;
					tr.children[3].textContent = row.reason; tr.children[4].textContent = row.size_bytes;
					tbody.appendChild(tr);
				});
				document.getElementById('tonkla-db-pageinfo').textContent = 'Page ' + res.data.page + ' / ' + res.data.pages + ' (' + res.data.total + ' items)';
				document.getElementById('tonkla-db-delete').disabled = false; status(''); updateSelectedCount();
			}).catch(function () { status('Error loading results.'); });
	}
	document.querySelectorAll('.tonkla-db-scan').forEach(function (btn) { btn.addEventListener('click', function () { status('Scanning...'); post({ action: 'tonkla_db_scan', type: btn.dataset.type }).then(function (res) { status(res.success ? ('Scanned ' + res.data.count + ' items.') : 'Scan failed.'); currentType = btn.dataset.type; page = 1; load(); }); }); });
	var deletableToggle = document.getElementById('tonkla-db-deletable');
	if (deletableToggle) { deletableToggle.addEventListener('change', function () { page = 1; load(); }); }
	document.querySelectorAll('.tonkla-db-general').forEach(function (btn) { btn.addEventListener('click', function () { if (!backup()) { alert('Please confirm the database backup first.'); return; } if (!window.confirm('Delete permanently?')) { return; } post({ action: 'tonkla_db_general', action_name: btn.dataset.action, backup_confirmed: backup() }).then(function (res) { if (res.success) { Object.keys(res.data.counts).forEach(function (k) { var cell = document.querySelector('[data-count="' + k + '"]'); if (cell) { cell.textContent = res.data.counts[k]; } }); alert('Done: ' + res.data.count); } else { alert('Please confirm the database backup first.'); } }); }); });
	function runGeneral(action) {
		return post({ action: 'tonkla_db_general', action_name: action, backup_confirmed: backup() }).then(function (res) {
			if (res.success && res.data.counts) { Object.keys(res.data.counts).forEach(function (k) { var cell = document.querySelector('[data-count="' + k + '"]'); if (cell) { cell.textContent = res.data.counts[k]; } }); }
			return res;
		});
	}
	document.querySelectorAll('.tonkla-db-group-check').forEach(function (groupCheck) { groupCheck.addEventListener('change', function () { document.querySelectorAll('.tonkla-db-general-check[data-group="' + groupCheck.dataset.group + '"]').forEach(function (c) { c.checked = groupCheck.checked; }); }); });
	function updateSelectedCount() { var n = document.querySelectorAll('.tonkla-db-check:checked').length; var el = document.getElementById('tonkla-db-selected-count'); if (el) { el.textContent = n; } }
	document.addEventListener('change', function (e) { if (e.target && (e.target.classList.contains('tonkla-db-check') || e.target.id === 'tonkla-db-checkall')) { updateSelectedCount(); } });
	var generalCheckAll = document.getElementById('tonkla-db-general-checkall');
	if (generalCheckAll) { generalCheckAll.addEventListener('change', function () { document.querySelectorAll('.tonkla-db-general-check').forEach(function (c) { c.checked = generalCheckAll.checked; }); }); }
	var cleanSelected = document.getElementById('tonkla-db-clean-selected');
	if (cleanSelected) { cleanSelected.addEventListener('click', function () {
		if (!backup()) { alert('Please confirm the database backup first.'); return; }
		var actions = []; document.querySelectorAll('.tonkla-db-general-check:checked').forEach(function (c) { actions.push(c.value); });
		if (!actions.length) { alert('Select at least one cleanup item.'); return; }
		if (!window.confirm('Run ' + actions.length + ' cleanup tasks permanently?')) { return; }
		var gStatus = document.getElementById('tonkla-db-general-status');
		cleanSelected.disabled = true;
		var total = 0, done = 0;
		var chain = Promise.resolve();
		actions.forEach(function (action) {
			chain = chain.then(function () {
				if (gStatus) { gStatus.textContent = 'Running ' + (done + 1) + ' / ' + actions.length + ': ' + action; }
				return runGeneral(action).then(function (res) { done++; if (res.success) { total += res.data.count || 0; } });
			});
		});
		chain.then(function () {
			if (gStatus) { gStatus.textContent = 'Done: ' + done + ' tasks, ' + total + ' items processed.'; }
			document.querySelectorAll('.tonkla-db-general-check:checked').forEach(function (c) { c.checked = false; });
			if (generalCheckAll) { generalCheckAll.checked = false; }
			cleanSelected.disabled = false;
		}).catch(function () { if (gStatus) { gStatus.textContent = 'Error while running cleanup.'; } cleanSelected.disabled = false; });
	}); }
	var prev = document.getElementById('tonkla-db-prev'); if (prev) { prev.addEventListener('click', function () { if (page > 1) { page--; load(); } }); }
	var next = document.getElementById('tonkla-db-next'); if (next) { next.addEventListener('click', function () { page++; load(); }); }
	var deleteAll = document.getElementById('tonkla-db-delete-all');
	if (deleteAll) { deleteAll.addEventListener('click', function () {
		if (!backup()) { alert('Please confirm the database backup first.'); return; }
		if (!window.confirm('Delete ALL deletable items (orphan candidates and expired items across every type)? This cannot be undone.')) { return; }
		if (!window.confirm('Please confirm again: permanently delete all deletable items now?')) { return; }
		deleteAll.disabled = true; deleteAll.textContent = 'Deleting...';
		post({ action: 'tonkla_db_delete_all', backup_confirmed: backup() }).then(function (res) {
			alert(res.success ? ('Deleted ' + res.data.deleted + ' items.') : 'Failed (backup confirmation required).');
			window.location.reload();
		}).catch(function () { alert('Error while deleting.'); deleteAll.disabled = false; });
	}); }
	var checkAll = document.getElementById('tonkla-db-checkall'); if (checkAll) { checkAll.addEventListener('change', function () { document.querySelectorAll('.tonkla-db-check:not(:disabled)').forEach(function (c) { c.checked = checkAll.checked; }); }); }
	var del = document.getElementById('tonkla-db-delete'); if (del) { del.addEventListener('click', function () { if (!backup()) { alert('Please confirm the database backup first.'); return; } var ids = []; document.querySelectorAll('.tonkla-db-check:checked').forEach(function (c) { ids.push(c.value); }); if (!ids.length) { alert('Select orphan items first.'); return; } if (!window.confirm('Delete ' + ids.length + ' items permanently?')) { return; } post({ action: 'tonkla_db_delete', ids: ids, backup_confirmed: backup() }).then(function (res) { alert(res.success ? ('Deleted ' + res.data.deleted) : 'Failed (backup confirmation required).'); load(); }); }); }
})();
