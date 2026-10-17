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

	$(function () {
		toggleEditor();
		$('#page_template').on('change', toggleEditor);
		hideDashboardSubmit();
		initDemoImportButtons();
	});
})(jQuery);
