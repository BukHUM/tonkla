(function ($) {
	'use strict';

	function toggleEditor() {
		var template = $('#page_template').val();
		if (template === 'page-flexible.php') {
			$('#postdivrich').hide();
		} else {
			$('#postdivrich').show();
		}
	}

	function hideDashboardSubmit() {
		if (!$('body').hasClass('toplevel_page_crb_carbon_fields_container_dashboard')) {
			return;
		}
		$('input[name="submit"]').remove();
		$('input[value="Save Changes"]').remove();
		$('.carbon-box-actions').remove();
		$('p.submit').remove();
	}

	function initDemoImportButtons() {
		$(document).on('click', '#demo-select-all', function (e) {
			e.preventDefault();
			$('.demo-page-checkbox').prop('checked', true);
		});

		$(document).on('click', '#demo-unselect-all', function (e) {
			e.preventDefault();
			$('.demo-page-checkbox').prop('checked', false);
		});
	}

	function initColorPreviews() {
		var colorMap = {
			brand: '#10b981',
			slate: '#64748b',
			gray: '#6b7280',
			zinc: '#71717a',
			neutral: '#737373',
			stone: '#78716c',
			red: '#ef4444',
			orange: '#f97316',
			amber: '#f59e0b',
			yellow: '#eab308',
			lime: '#84cc16',
			green: '#22c55e',
			emerald: '#10b981',
			teal: '#14b8a6',
			cyan: '#06b6d4',
			sky: '#0ea5e9',
			blue: '#3b82f6',
			indigo: '#6366f1',
			violet: '#8b5cf6',
			purple: '#a855f7',
			fuchsia: '#d946ef',
			pink: '#ec4899',
			rose: '#f43f5e',
			'light-blue': '#0ea5e9'
		};

		function updateColorPreview(selectElement) {
			var container = selectElement.closest('.carbon-field');
			if (!container) {
				return;
			}
			var previewBox = container.querySelector('.tonkla-color-preview');
			if (!previewBox) {
				return;
			}
			var hex = colorMap[selectElement.value] || '#e2e8f0';
			previewBox.style.backgroundColor = hex;
		}

		function updateAllPreviews() {
			document.querySelectorAll('.carbon-field select').forEach(updateColorPreview);
		}

		document.body.addEventListener('change', function (e) {
			if (e.target && e.target.tagName === 'SELECT') {
				updateColorPreview(e.target);
			}
		});

		setTimeout(updateAllPreviews, 1000);

		var carbonContainer = document.querySelector('.carbon-container');
		if (carbonContainer && typeof MutationObserver !== 'undefined') {
			var observer = new MutationObserver(function (mutations) {
				var shouldUpdate = mutations.some(function (mutation) {
					return mutation.addedNodes.length > 0;
				});
				if (shouldUpdate) {
					updateAllPreviews();
				}
			});
			observer.observe(carbonContainer, { childList: true, subtree: true });
		}
	}

	$(function () {
		toggleEditor();
		$('#page_template').on('change', toggleEditor);
		hideDashboardSubmit();
		initDemoImportButtons();
		initColorPreviews();
	});
})(jQuery);
