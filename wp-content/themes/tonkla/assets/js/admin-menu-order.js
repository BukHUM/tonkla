(function ($) {
	'use strict';

	function nextSeparatorSlug($list) {
		var used = {};
		$list.children('li').each(function () {
			used[$(this).attr('data-slug') || ''] = true;
		});
		var n = 1;
		var slug;
		do {
			slug = 'separator-tonkla-' + n;
			n += 1;
		} while (used[slug]);
		return slug;
	}

	function syncHiddenInputs($list) {
		$list.children('li').each(function () {
			var slug = $(this).attr('data-slug');
			$(this).find('input[name="tonkla_menu_order[]"]').val(slug);
		});
	}

	function buildSeparatorItem(slug, label, removeLabel) {
		var $li = $('<li/>', {
			class: 'tonkla-menu-sortable__item is-separator',
			'data-slug': slug
		});
		$li.append($('<span/>', { class: 'tonkla-menu-sortable__handle', 'aria-hidden': 'true', text: '⋮⋮' }));
		$li.append($('<span/>', { class: 'tonkla-menu-sortable__label', text: label }));
		$li.append($('<code/>', { class: 'tonkla-menu-sortable__slug', text: slug }));
		$li.append(
			$('<button/>', {
				type: 'button',
				class: 'button-link tonkla-menu-sortable__remove',
				'aria-label': removeLabel,
				html: '&times;'
			})
		);
		$li.append($('<input/>', { type: 'hidden', name: 'tonkla_menu_order[]', value: slug }));
		return $li;
	}

	$(function () {
		var $list = $('#tonkla-menu-sortable');
		if (!$list.length || typeof $.fn.sortable !== 'function') {
			return;
		}

		var i18n = window.tonklaMenuOrder || {};
		var separatorLabel = i18n.separatorLabel || '— เส้นคั่นกลุ่ม —';
		var removeLabel = i18n.removeLabel || 'ลบเส้นคั่น';

		$list.sortable({
			handle: '.tonkla-menu-sortable__handle',
			placeholder: 'tonkla-menu-sortable__placeholder',
			axis: 'y',
			tolerance: 'pointer',
			update: function () {
				syncHiddenInputs($list);
			}
		});

		$('#tonkla-menu-add-separator').on('click', function () {
			var slug = nextSeparatorSlug($list);
			var $item = buildSeparatorItem(slug, separatorLabel, removeLabel);
			$list.append($item);
			$list.sortable('refresh');
			syncHiddenInputs($list);
			$item.find('.tonkla-menu-sortable__remove').trigger('focus');
		});

		$list.on('click', '.tonkla-menu-sortable__remove', function () {
			$(this).closest('li').remove();
			syncHiddenInputs($list);
		});

		$('#tonkla-menu-order-reset').on('click', function () {
			$('#tonkla-menu-order-action').val('reset');
		});
	});
})(jQuery);
