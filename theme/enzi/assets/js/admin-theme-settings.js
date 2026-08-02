(function ($) {
	'use strict';

	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	function parseColor(value) {
		var input = String(value || '').trim();

		if (!input) {
			return null;
		}

		var rgbaMatch = input.match(
			/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)(?:\s*,\s*([\d.]+))?\s*\)$/i
		);

		if (rgbaMatch) {
			return {
				r: clamp(Math.round(parseFloat(rgbaMatch[1])), 0, 255),
				g: clamp(Math.round(parseFloat(rgbaMatch[2])), 0, 255),
				b: clamp(Math.round(parseFloat(rgbaMatch[3])), 0, 255),
				a: clamp(parseFloat(rgbaMatch[4] !== undefined ? rgbaMatch[4] : 1), 0, 1),
			};
		}

		var hexMatch = input.match(/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i);

		if (hexMatch) {
			var hex = hexMatch[1];

			if (hex.length === 3) {
				hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
			}

			var alpha = 1;

			if (hex.length === 8) {
				alpha = parseInt(hex.substring(6, 8), 16) / 255;
				hex = hex.substring(0, 6);
			}

			return {
				r: parseInt(hex.substring(0, 2), 16),
				g: parseInt(hex.substring(2, 4), 16),
				b: parseInt(hex.substring(4, 6), 16),
				a: clamp(alpha, 0, 1),
			};
		}

		var hslMatch = input.match(/^(\d{1,3})\s+(\d{1,3})%\s+(\d{1,3})%(?:\s*\/\s*([\d.]+))?$/);

		if (hslMatch) {
			return hslToRgb(
				parseInt(hslMatch[1], 10),
				parseInt(hslMatch[2], 10),
				parseInt(hslMatch[3], 10),
				parseFloat(hslMatch[4] !== undefined ? hslMatch[4] : 1)
			);
		}

		return null;
	}

	function hslToRgb(h, s, l, alpha) {
		h = h / 360;
		s = s / 100;
		l = l / 100;

		if (s === 0) {
			var gray = Math.round(l * 255);
			return { r: gray, g: gray, b: gray, a: clamp(alpha, 0, 1) };
		}

		function hue2rgb(p, q, t) {
			if (t < 0) {
				t += 1;
			}
			if (t > 1) {
				t -= 1;
			}
			if (t < 1 / 6) {
				return p + (q - p) * 6 * t;
			}
			if (t < 1 / 2) {
				return q;
			}
			if (t < 2 / 3) {
				return p + (q - p) * (2 / 3 - t) * 6;
			}
			return p;
		}

		var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
		var p = 2 * l - q;

		return {
			r: Math.round(hue2rgb(p, q, h + 1 / 3) * 255),
			g: Math.round(hue2rgb(p, q, h) * 255),
			b: Math.round(hue2rgb(p, q, h - 1 / 3) * 255),
			a: clamp(alpha, 0, 1),
		};
	}

	function formatAlpha(alpha) {
		return String(alpha).replace(/\.?0+$/, '');
	}

	function toRgbaString(color) {
		return (
			'rgba(' +
			color.r +
			', ' +
			color.g +
			', ' +
			color.b +
			', ' +
			formatAlpha(color.a) +
			')'
		);
	}

	function toHex(color) {
		return (
			'#' +
			[color.r, color.g, color.b]
				.map(function (part) {
					return part.toString(16).padStart(2, '0');
				})
				.join('')
		);
	}

	function syncColorField($field, color) {
		$field.find('[data-lastify-color-picker]').val(toHex(color));
		$field.find('[data-lastify-color-alpha]').val(Math.round(color.a * 100));
		$field.find('[data-lastify-color-alpha-label]').text(Math.round(color.a * 100) + '%');
		$field.find('[data-lastify-color-value]').val(toRgbaString(color));
	}

	function getFrame() {
		return wp.media({
			title: 'انتخاب تصویر',
			button: { text: 'استفاده از این تصویر' },
			multiple: false,
			library: { type: 'image' },
		});
	}

	$(document).on('click', '[data-lastify-select-image]', function (event) {
		event.preventDefault();

		var $field = $(this).closest('[data-lastify-image-field]');
		var frame = getFrame();

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$field.find('[data-lastify-image-id]').val(attachment.id);
			$field.find('[data-lastify-image-url]').val(attachment.url);
			$field.find('[data-lastify-image-preview]').html(
				'<img src="' + attachment.url + '" alt="">'
			);
		});

		frame.open();
	});

	$(document).on('click', '[data-lastify-clear-image]', function (event) {
		event.preventDefault();

		var $field = $(this).closest('[data-lastify-image-field]');
		$field.find('[data-lastify-image-id]').val('');
		$field.find('[data-lastify-image-url]').val('');
		$field.find('[data-lastify-image-preview]').empty();
	});

	function reindexHeroSlides() {
		$('#lastify-hero-slides-list [data-lastify-hero-slide-row]').each(function (index) {
			var $row = $(this);

			$row.find('[name]').each(function () {
				var $input = $(this);
				var name = $input.attr('name');

				if (!name) {
					return;
				}

				$input.attr(
					'name',
					name.replace(/lastify_hero_slides\[\d+]/, 'lastify_hero_slides[' + index + ']')
				);
			});

			$row.find('[id]').each(function () {
				var $input = $(this);
				var id = $input.attr('id');

				if (!id) {
					return;
				}

				$input.attr(
					'id',
					id.replace(/lastify_hero_slides\[\d+]/, 'lastify_hero_slides[' + index + ']')
				);
			});

			$row.find('[data-lastify-slide-title]').text('اسلاید ' + (index + 1));
		});
	}

	$(document).on('input change', '[data-lastify-color-picker], [data-lastify-color-alpha]', function () {
		var $field = $(this).closest('[data-lastify-color-field]');
		var current = parseColor($field.find('[data-lastify-color-value]').val()) || {
			r: 0,
			g: 51,
			b: 204,
			a: 1,
		};

		var hexColor = parseColor($field.find('[data-lastify-color-picker]').val());

		if (hexColor) {
			current.r = hexColor.r;
			current.g = hexColor.g;
			current.b = hexColor.b;
		}

		current.a = parseInt($field.find('[data-lastify-color-alpha]').val(), 10) / 100;
		syncColorField($field, current);
	});

	$(document).on('change blur', '[data-lastify-color-value]', function () {
		var $field = $(this).closest('[data-lastify-color-field]');
		var color = parseColor($(this).val());

		if (!color) {
			return;
		}

		syncColorField($field, color);
	});

	$(document).on('click', '#lastify-add-hero-slide', function (event) {
		event.preventDefault();

		var $list = $('#lastify-hero-slides-list');
		var $rows = $list.find('[data-lastify-hero-slide-row]');
		var $source = $rows.last();
		var $clone = $source.clone();

		$clone.find('[data-lastify-image-id]').val('');
		$clone.find('[data-lastify-image-url]').val('');
		$clone.find('[data-lastify-image-preview]').empty();
		$clone.find('input[type="text"], input[type="url"], input[type="number"]').val('');
		$clone.find('input[name$="[id]"]').val('0');
		$clone.find('input[name$="[delete]"]').prop('checked', false);

		$list.append($clone);
		reindexHeroSlides();
	});
})(jQuery);
