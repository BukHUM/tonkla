(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function toggleEditor() {
		var templateSelect = document.getElementById('page_template');
		var editor = document.getElementById('postdivrich');
		if (!templateSelect || !editor) {
			return;
		}
		editor.style.display = templateSelect.value === 'page-flexible.php' ? 'none' : '';
	}

	function hideDashboardSubmit() {
		var isOverview = document.body.classList.contains('tonkla-admin-overview')
			|| document.body.classList.contains('toplevel_page_crb_carbon_fields_container_dashboard');
		if (!isOverview) {
			return;
		}
		var sidebar = document.getElementById('postbox-container-1');
		if (sidebar) {
			sidebar.remove();
		}
		document.querySelectorAll('#publish, input[name="publish"]').forEach(function (el) {
			el.remove();
		});
	}

	/**
	 * Move Carbon Actions (#postbox-container-1) after the fields so the save
	 * bar never sits above TinyMCE / tabs.
	 */
	function placeSettingsSaveBar() {
		if (!document.body.classList.contains('tonkla-admin-settings')) {
			return;
		}
		var postBody = document.getElementById('post-body');
		var content = document.getElementById('post-body-content');
		var actions = document.getElementById('postbox-container-1');
		if (!postBody || !content || !actions) {
			return;
		}
		if (content.nextElementSibling !== actions) {
			postBody.appendChild(actions);
		}
	}

	/**
	 * Carbon Theme Options prints `.settings-error.updated` without
	 * `is-dismissible`, so WP never adds a close button — fix that.
	 */
	function makeCarbonNoticesDismissible() {
		var wrap = document.querySelector('.carbon-theme-options, .wrap.carbon-theme-options');
		if (!wrap) {
			return;
		}
		wrap.querySelectorAll('.settings-error').forEach(function (notice) {
			if (notice.classList.contains('updated')) {
				notice.classList.add('notice', 'notice-success', 'is-dismissible');
			} else if (notice.classList.contains('error')) {
				notice.classList.add('notice', 'notice-error', 'is-dismissible');
			} else {
				notice.classList.add('notice', 'is-dismissible');
			}
			if (notice.querySelector('.notice-dismiss')) {
				return;
			}
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'notice-dismiss';
			btn.innerHTML = '<span class="screen-reader-text">ปิดการแจ้งเตือน</span>';
			btn.addEventListener('click', function () {
				notice.style.transition = 'opacity 0.1s linear';
				notice.style.opacity = '0';
				window.setTimeout(function () {
					if (notice.parentNode) {
						notice.parentNode.removeChild(notice);
					}
				}, 100);
			});
			notice.appendChild(btn);
		});
	}

	onReady(function () {
		toggleEditor();
		var templateSelect = document.getElementById('page_template');
		if (templateSelect) {
			templateSelect.addEventListener('change', toggleEditor);
		}
		hideDashboardSubmit();
		placeSettingsSaveBar();
		makeCarbonNoticesDismissible();
		// Carbon may re-render layout shortly after load.
		window.setTimeout(placeSettingsSaveBar, 300);
	});
})();
