(function($) {

	'use strict';

	// Form - Color
	$.WS_Form.prototype.form_color = function() {

		// Do not load color picker if no color fields found
		if(!$('[data-type="color"] input', this.form_canvas_obj).length) { return false; }

		// Do not use color picker
		if($.WS_Form.settings_plugin.ui_color == 'off') { return false; }

		if(
			// Use color picker
			($.WS_Form.settings_plugin.ui_color == 'on') ||

			// If browser does not support native color picked, use Coloris
			(
				($.WS_Form.settings_plugin.ui_color == 'native') &&
				!this.native_color
			)
		) {

			this.form_color_process();
		}
	}

	// Form - Color - Process
	$.WS_Form.prototype.form_color_process = function() {

		var ws_this = this;

		// Check to see if Coloris loaded (https://github.com/mdbassit/Coloris)
		if(typeof(Coloris) !== 'undefined') {

			// Get color inputs
			var color_objs = $('[data-type="color"] input', this.form_canvas_obj);

			// Configure Coloris component
			var args = {

				el: color_objs.toArray()
			};

			Coloris(args);

			color_objs.each(function() {

				if(
					// Use color picker
					($.WS_Form.settings_plugin.ui_color == 'on') ||

					// If browser does not support native color picker, use Coloris
					(
						($.WS_Form.settings_plugin.ui_color == 'native') &&
						!ws_this.native_color
					)

				) {

					// Get field
					var field = ws_this.get_field($(this));

					// Theme
					var coloris_theme = ws_this.get_object_meta_value(field, 'coloris_theme', 'default');
					if(!['default', 'large', 'polaroid', 'pill'].includes(coloris_theme)) { coloris_theme = 'default'; }

					// Theme mode
					var coloris_theme_mode = ws_this.get_object_meta_value(field, 'coloris_theme_mode', 'light');
					if(!['light', 'dark', 'auto'].includes(coloris_theme_mode)) { coloris_theme_mode = 'light'; }

					// Format
					var coloris_format = ws_this.get_object_meta_value(field, 'coloris_format', 'hex');
					if(!['hex', 'rgb', 'hsl', 'auto', 'mixed'].includes(coloris_format)) { coloris_format = 'light'; }

					// Format toggle
					var coloris_format_toggle = (ws_this.get_object_meta_value(field, 'coloris_format_toggle', '') == 'on');

					// Alpha
					var coloris_alpha = (ws_this.get_object_meta_value(field, 'coloris_alpha', 'on') == 'on');

					// Build args
					var args = {

						alpha: coloris_alpha,
						format: coloris_format,
						formatToggle: coloris_format_toggle,
						swatches: false,
						theme: coloris_theme,
						themeMode: coloris_theme_mode,
						rtl: ws_form_settings.rtl ? true : false,
						wrap: true
					};

					// Configure Coloris component
					Coloris.setInstance('#' + $(this).attr('id'), args);
				}
			});
		}
	}

	// Get var computed value
	$.WS_Form.prototype.color_var_parse = function(var_name, style_id) {

		// Determine style element
		var element = style_id ? document.querySelector('[data-wsf-style-id="' + style_id + '"]') : document.body;

		// Get parsed var
		var var_parsed = getComputedStyle(element).getPropertyValue(var_name).trim();

		return var_parsed;
	}

	// Parse color
	$.WS_Form.prototype.color_parse = function(color, style_id) {

		// Check for var
		var var_match = color.match(/var\((--[\w-]+)\)/);
  
		if (var_match) {
 
			color = this.color_var_parse(var_match[1], style_id);
		}

		// Check for color-mix
		if(color.match(this.color_mix_regex())) {

			color = this.color_mix_to_hex_color(color);
		}

		return color;
	}

	// Color to hsla_array
	$.WS_Form.prototype.color_to_hsla_array = function(color) {

		// Parse color
		var color = this.color_parse(color);

		// Process hex
		if(this.color_is_hex(color) || this.color_is_hex_alpha(color)) {

			return this.hex_color_to_hsla_array(color);
		}

		// Process RGB
		if(this.color_is_rgb(color) || this.color_is_rgba(color)) {

			return this.rgba_color_to_hsla_array(color);
		}

		// Process HSL
		if(this.color_is_hsl(color) || this.color_is_hsla(color)) {

			return this.hsla_color_to_hsla_array(color);
		}

		return {

			h: 0,
			s: 0,
			l: 0,
			a: 1
		};
	}

	// Color to rgba_array
	$.WS_Form.prototype.color_to_rgba_array = function(color) {

		// Convert vars to actual color
		while(/var\((.*)\)/.exec(color)) {

			var style = getComputedStyle(document.body)
			color = style.getPropertyValue(color);
		}

		if(this.color_is_hex(color) || this.color_is_hex_alpha(color)) {

			return this.hex_color_to_rgba_array(color);
		}

		if(this.color_is_rgb(color) || this.color_is_rgba(color)) {

			var hsla_array = this.rgba_color_to_hsla_array(color);
			return this.hsla_array_to_rgba_array(hsla_array);
		}

		if(this.color_is_hsl(color) || this.color_is_hsla(color)) {

			return this.hsla_color_to_rgba_array(color);
		}

		return {

			r: 0,
			g: 0,
			b: 0,
			a: 1
		};
	};

	// Get alt color
	$.WS_Form.prototype.color_to_color_alt = function(color) {

		if(color == 'transparent') { return color; }

		var regex = /var\((.*)\)/;
		var matches = regex.exec(color);

		if(matches) {

			var var_parameters = matches[1];
			var var_parameters_array = var_parameters.split(',');

			for(var var_parameter_index in var_parameters_array) {

				if(!var_parameters_array.hasOwnProperty(var_parameter_index)) { continue; }

				var var_parameter = var_parameters_array[var_parameter_index].trim();

				if(var_parameter.indexOf('--wsf-') === -1) { continue; }

				if(var_parameter.indexOf('-alt') === -1) {
					var_parameters_array[var_parameter_index] = var_parameter + '-alt';
				} else {
					var_parameters_array[var_parameter_index] = var_parameter.replace('-alt', '');
				}
			}

			return 'var(' + var_parameters_array.join(', ') + ')';
		}

		if(this.color_is_hex(color) || this.color_is_hex_alpha(color)) {

			var hsla_array = this.hex_color_to_hsla_array(color);
			hsla_array = this.hsla_array_to_alt_hsla_array(hsla_array);
			return this.hsla_array_to_hex_color(hsla_array);
		}

		if(this.color_is_rgb(color) || this.color_is_rgba(color)) {

			var hsla_array = this.rgba_color_to_hsla_array(color);
			hsla_array = this.hsla_array_to_alt_hsla_array(hsla_array);
			return this.hsla_array_to_rgba_color(hsla_array);
		}

		if(this.color_is_hsl(color) || this.color_is_hsla(color)) {

			var hsla_array = this.hsla_color_to_hsla_array(color);
			hsla_array = this.hsla_array_to_alt_hsla_array(hsla_array);
			return this.hsla_array_to_hsla_color(hsla_array);
		}

		return color;
	};

	// Calculate contrast ratio between two colors
	$.WS_Form.prototype.color_contrast_ratio = function(color_foreground, color_background) {

		return this.rgba_array_contrast_ratio(

			this.color_to_rgba_array(color_foreground),
			this.color_to_rgba_array(color_background)
		);
	}

	// Check if color is hex
	$.WS_Form.prototype.color_is_hex = function(color) {

		return /^#([a-f0-9]{3}|[a-f0-9]{6})$/i.test(color);
	}

	// Check if color is hex with alpha
	$.WS_Form.prototype.color_is_hex_alpha = function(color) {

		return /^#([a-f0-9]{8})$/i.test(color);
	}

	// Check if color is RGB
	$.WS_Form.prototype.color_is_rgb = function(color) {

		return /^rgb\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*\)$/i.test(color);
	}

	// Check if color is RGBA
	$.WS_Form.prototype.color_is_rgba = function(color) {

		return /^rgba\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*(0|1|0?\.\d+)\s*\)$/i.test(color);
	}

	// Check if color is HSL
	$.WS_Form.prototype.color_is_hsl = function(color) {

		return /^hsl\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*\)$/i.test(color);
	}

	// Check if color is HSLA
	$.WS_Form.prototype.color_is_hsla = function(color) {

		return /^hsla\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*,\s*(0|1|0?\.\d+)\s*\)$/i.test(color);
	}

	// Convert HSLA color to RGBA array
	$.WS_Form.prototype.hsla_color_to_rgba_array = function(hsla_color) {

		return this.hsla_array_to_rgba_array(this.hsla_color_to_hsla_array(hsla_color));
	}

	// Convert hex color to HSLA array
	$.WS_Form.prototype.hex_color_to_hsla_array = function(hex_color) {

		var rgba_array = this.hex_color_to_rgba_array(hex_color);

		var r = rgba_array.r / 255;
		var g = rgba_array.g / 255;
		var b = rgba_array.b / 255;
		var a = rgba_array.a;

		var min = Math.min(r, g, b);
		var max = Math.max(r, g, b);

		var l = (max + min) / 2;
		var h, s;

		if(max === min) {
			h = s = 0;
		} else {
			var diff = max - min;
			s = l > 0.5 ? diff / (2 - max - min) : diff / (max + min);

			switch(max) {
				case r:
					h = (g - b) / diff + (g < b ? 6 : 0);
					break;
				case g:
					h = (b - r) / diff + 2;
					break;
				case b:
					h = (r - g) / diff + 4;
					break;
			}

			h /= 6;
		}

		return {
			'h': Math.round(h * 360),
			's': Math.round(s * 100),
			'l': Math.round(l * 100),
			'a': a
		};
	};

	// Convert hex color to RGBA array
	$.WS_Form.prototype.hex_color_to_rgba_array = function(hex_color, a = false) {

		if(!hex_color) { hex_color = '#000000'; }
		hex_color = this.hex_color_strip_hash(hex_color);
		if(a === false) { a = 1; }

		var hex_array;
		if(hex_color.length === 8) {
			hex_array = [hex_color.substr(0, 2), hex_color.substr(2, 2), hex_color.substr(4, 2), hex_color.substr(6, 2)];
		} else if(hex_color.length === 6) {
			hex_array = [hex_color.substr(0, 2), hex_color.substr(2, 2), hex_color.substr(4, 2)];
		} else if(hex_color.length === 3) {
			hex_array = [hex_color[0] + hex_color[0], hex_color[1] + hex_color[1], hex_color[2] + hex_color[2]];
		} else {
			hex_array = ['00', '00', '00'];
		}

		var rgba = hex_array.map(function(val) { return parseInt(val, 16); });

		if(rgba[3] !== undefined) {
			a = rgba[3] / 255;
		}

		return {
			'r': rgba[0],
			'g': rgba[1],
			'b': rgba[2],
			'a': a
		};
	};

	// Conver hex color to RGBA color
	$.WS_Form.prototype.hex_color_to_rgba_color = function(hex_color, a = false) {

		return this.rgba_array_to_rgba_color(this.hex_color_to_rgba_array(hex_color, a));
	};

	// Strip hash from hex color
	$.WS_Form.prototype.hex_color_strip_hash = function(hex_color) {

		return hex_color.replace(/^#/, '').trim();
	};

	// Convert HSLA array to HSLA color
	$.WS_Form.prototype.hsla_array_to_hsla_color = function(hsla_array) {

		var h = hsla_array.h !== undefined ? hsla_array.h : 0;
		var s = hsla_array.s !== undefined ? hsla_array.s + '%' : '0%';
		var l = hsla_array.l !== undefined ? hsla_array.l + '%' : '0%';
		var a = hsla_array.a !== undefined ? hsla_array.a : 1;

		if(a === 1) {
			return `hsl(${h}, ${s}, ${l})`;
		}

		return `hsla(${h}, ${s}, ${l}, ${a})`;
	};

	// Convert HSLA array to RGBA color
	$.WS_Form.prototype.hsla_array_to_rgba_color = function(hsla_array) {

		return this.rgba_array_to_rgba_color(this.hsla_array_to_rgba_array(hsla_array));
	};

	// Convert HSLA array to RGBA array
	$.WS_Form.prototype.hsla_array_to_rgba_array = function(hsla_array) {

		var h = (hsla_array.h % 360) / 360;
		var s = hsla_array.s / 100;
		var l = hsla_array.l / 100;
		var a = hsla_array.a !== undefined ? hsla_array.a : 1.0;

		var r, g, b;

		if(s === 0) {
			r = g = b = l * 255;
		} else {
			var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
			var p = 2 * l - q;

			var hue2rgb = function(p, q, t) {
				if(t < 0) t += 1;
				if(t > 1) t -= 1;
				if(t < 1 / 6) return p + (q - p) * 6 * t;
				if(t < 1 / 2) return q;
				if(t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
				return p;
			};

			r = hue2rgb(p, q, h + 1 / 3) * 255;
			g = hue2rgb(p, q, h) * 255;
			b = hue2rgb(p, q, h - 1 / 3) * 255;
		}

		return {
			'r': Math.floor(r),
			'g': Math.floor(g),
			'b': Math.floor(b),
			'a': a
		};
	};

	// Convert HSLA array to HSLA color
	$.WS_Form.prototype.hsla_array_to_hsla_color = function(hsla_array) {

		var h = hsla_array.h !== undefined ? hsla_array.h : 0;
		var s = hsla_array.s !== undefined ? hsla_array.s : 0;
		var l = hsla_array.l !== undefined ? hsla_array.l : 0;
		var a = hsla_array.a !== undefined ? hsla_array.a : 1;

		// Format the saturation and lightness as percentages
		s += '%';
		l += '%';

		// Check if the alpha is 1, in which case we return hsl
		if (a === 1) {
			return `hsl(${h}, ${s}, ${l})`;
		} else {
			return `hsla(${h}, ${s}, ${l}, ${a})`;
		}
	}

	// Convert HSLA array to hex color
	$.WS_Form.prototype.hsla_array_to_hex_color = function(hsla_array) {

		var a = hsla_array.a;

		// If alpha is 0, return #000000
		if (a === 0) {
			return '#000000';
		}

		// Convert HSLA to RGBA
		var rgba_array = this.hsla_array_to_rgba_array(hsla_array);

		return this.rgba_array_to_hex_color(rgba_array);
	}

	// Create alt color for HSLA array
	$.WS_Form.prototype.hsla_array_to_alt_hsla_array = function(hsla_array) {

		// Get lightness
		var lightness = hsla_array['l'];

		// Check lightness
		if(lightness < 0) { lightness = 0; }
		if(lightness > 100) { lightness = 100; }

		// Flip lightness
		hsla_array['l'] = 100 - lightness;

		return hsla_array;
	}

	// Convert HSLA color to HSLA array
	$.WS_Form.prototype.hsla_color_to_hsla_array = function(hsla_color) {

		var regex = /^hsla?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i;
		var matches = regex.exec(hsla_color);

		if(matches) {

			var a = matches[4] ? parseFloat(matches[4]) : 1;

			return {
				'h': parseInt(matches[1], 10),
				's': parseInt(matches[2], 10),
				'l': parseInt(matches[3], 10),
				'a': Math.round(a * 100) / 100
			};

		} else {

			return {
				'h': 0,
				's': 0,
				'l': 0,
				'a': 1
			};
		}
	};

	// Blend the foreground color over the background color
	$.WS_Form.prototype.rgba_array_blend_colors = function(rgb_array_foreground, rgba_array_background) {

		var alpha = rgb_array_foreground.a / 255; // Normalize alpha value

		return {
			r: Math.round((1 - alpha) * rgba_array_background.r + alpha * rgb_array_foreground.r),
			g: Math.round((1 - alpha) * rgba_array_background.g + alpha * rgb_array_foreground.g),
			b: Math.round((1 - alpha) * rgba_array_background.b + alpha * rgb_array_foreground.b),
		};
	};

	// Calculate relative luminance
	$.WS_Form.prototype.rgba_array_get_luminance = function(rgba_array) {

		// Convert RGB values to linear space
		var linear_rgb = [rgba_array.r, rgba_array.g, rgba_array.b].map(function(value) {
			var s_rgb = value / 255;
			return s_rgb <= 0.03928 ? s_rgb / 12.92 : Math.pow((s_rgb + 0.055) / 1.055, 2.4);
		});

		// Apply the luminance formula
		return 0.2126 * linear_rgb[0] + 0.7152 * linear_rgb[1] + 0.0722 * linear_rgb[2];
	};

	// Calculate contrast ratio between two RGBA arrays
	$.WS_Form.prototype.rgba_array_contrast_ratio = function(rgb_array_foreground, rgba_array_background) {

		// Blend the foreground over the background
		var blended_fg = this.rgba_array_blend_colors(rgb_array_foreground, rgba_array_background);

		// Calculate luminance for both colors
		var fg_luminance = this.rgba_array_get_luminance(blended_fg);
		var bg_luminance = this.rgba_array_get_luminance(rgba_array_background);

		// Calculate the contrast ratio
		var lighter = Math.max(fg_luminance, bg_luminance);
		var darker = Math.min(fg_luminance, bg_luminance);

		return (lighter + 0.05) / (darker + 0.05);
	};

	// Convert RGBA array to OKLab array
	$.WS_Form.prototype.rgba_array_to_oklab_array = function(rgba_array) {

		var linearize = function (val) {

			val /= 255;
			return val <= 0.04045 ? val / 12.92 : ((val + 0.055) / 1.055) ** 2.4;
		};

		var lr = linearize(rgba_array.r),
		lg = linearize(rgba_array.g),
		lb = linearize(rgba_array.b);

		var l = 0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb;
		var m = 0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb;
		var s = 0.0883024619 * lr + 0.2817188376 * lg + 0.6299787005 * lb;

		var l_ = Math.cbrt(l),
		m_ = Math.cbrt(m),
		s_ = Math.cbrt(s);

		return {

			l: 0.2104542553 * l_ + 0.793617785 * m_ - 0.0040720468 * s_,
			a: 1.9779984951 * l_ - 2.428592205 * m_ + 0.4505937099 * s_,
			b: 0.0259040371 * l_ + 0.7827717662 * m_ - 0.808675766 * s_,
			alpha: rgba_array.a,
		};
	}

	// Convert RGBA array to RGBA color
	$.WS_Form.prototype.rgba_array_to_rgba_color = function(rgba_array) {

		if(rgba_array.a == 1) {
			return `rgb(${rgba_array.r}, ${rgba_array.g}, ${rgba_array.b})`;
		}

		return `rgba(${rgba_array.r}, ${rgba_array.g}, ${rgba_array.b}, ${rgba_array.a})`;
	};

	// Convert RGBA array to hex color
	$.WS_Form.prototype.rgba_array_to_hex_color = function(rgba_array) {

		var r = rgba_array.r;
		var g = rgba_array.g;
		var b = rgba_array.b;
		var a = rgba_array.a;

		// Convert RGB to Hex
		var hex_r = r.toString(16).padStart(2, '0');
		var hex_g = g.toString(16).padStart(2, '0');
		var hex_b = b.toString(16).padStart(2, '0');

		// If alpha is 1, return 6 character hex
		if (a == 1) {
			return `#${hex_r}${hex_g}${hex_b}`;
		}

		// Otherwise, calculate alpha hex and return 8 character hex
		var hex_alpha = Math.round(a * 255).toString(16).padStart(2, '0');

		return `#${hex_r}${hex_g}${hex_b}${hex_alpha}`;
	}

	// Convert RGBA color to HSLA array
	$.WS_Form.prototype.rgba_color_to_hsla_array = function (rgba_color) {

		var rgba_array = this.rgba_color_to_rgba_array(rgba_color);

		return this.rgba_array_to_hsla_array(rgba_array);
	}

	// Convert RGBA color to RGBA array
	$.WS_Form.prototype.rgba_color_to_rgba_array = function (rgba_color) {

		// Extract RGB(A) values from the input string
		var rgba = rgba_color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]*)\)/);

		if (!rgba) {

			return {
				r: 0,
				g: 0,
				b: 0,
				a: 1
			}
		}

		// Parse RGB values and alpha
		var r = parseInt(rgba[1], 10);
		var g = parseInt(rgba[2], 10);
		var b = parseInt(rgba[3], 10);
		var a = rgba[4] !== '' ? parseFloat(rgba[4]) : 1;

		return {
			r: r,
			g: g,
			b: b,
			a: a
		};
	}

	// Convert RGBA color to RGBA array
	$.WS_Form.prototype.rgba_array_to_hsla_array = function(rgba_array) {

		// Parse RGB values and alpha
		var r = parseInt(rgba_array.r, 10);
		var g = parseInt(rgba_array.g, 10);
		var b = parseInt(rgba_array.b, 10);
		var a = rgba_array.a !== '' ? parseFloat(rgba_array.a) : 1;

		// Convert RGB values to the range 0-1
		r /= 255;
		g /= 255;
		b /= 255;

		// Find the min and max values of r, g, b
		var max = Math.max(r, g, b);
		var min = Math.min(r, g, b);

		// Calculate lightness
		var l = (max + min) / 2;

		var h, s;

		if (max === min) {

			// Achromatic (gray), no hue
			h = s = 0;

		} else {

			var d = max - min;

			// Saturation
			s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

			// Hue
			switch (max) {

				case r: h = (g - b) / d + (g < b ? 6 : 0); break;
				case g: h = (b - r) / d + 2; break;
				case b: h = (r - g) / d + 4; break;
			}

			h /= 6;
		}

		return {

			h: Math.round(h * 360),
			s: Math.round(s * 100), 
			l: Math.round(l * 100),
			a
		};
	}

	// Convert OKLab array to RGBA array
	$.WS_Form.prototype.oklab_array_to_rgba_array = function(oklab) {

		var l_ = oklab.l + 0.3963377774 * oklab.a + 0.2158037573 * oklab.b;
		var m_ = oklab.l - 0.1055613458 * oklab.a - 0.0638541728 * oklab.b;
		var s_ = oklab.l - 0.0894841775 * oklab.a - 1.291485548 * oklab.b;

		var l = l_ ** 3,
		m = m_ ** 3,
		s = s_ ** 3;

		var r = 4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s;
		var g = -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s;
		var b = -0.0041960863 * l - 0.7034186147 * m + 1.707614701 * s;

		var gammaCorrect = function (val) {

			return val <= 0.0031308
			? val * 12.92
			: 1.055 * Math.pow(val, 1 / 2.4) - 0.055;
		};

		return {

			r: Math.max(0, Math.min(255, Math.round(gammaCorrect(r) * 255))),
			g: Math.max(0, Math.min(255, Math.round(gammaCorrect(g) * 255))),
			b: Math.max(0, Math.min(255, Math.round(gammaCorrect(b) * 255))),
			a: oklab.alpha,
		};
	}

	$.WS_Form.prototype.color_mix_regex = function() {

		return /color-mix\(in oklab, (#\w{3,6}|rgba?\(\s*\d+,\s*\d+,\s*\d+(?:,\s*[\d.]+)?\)), (#\w{3,6}|rgba?\(\s*\d+,\s*\d+,\s*\d+(?:,\s*[\d.]+)?\)) (\d+)%\)/;
	}

	$.WS_Form.prototype.color_mix_to_hex_color = function(input) {

		return this.rgba_array_to_hex_color(this.color_mix_to_rgba_array(input));
	}

	$.WS_Form.prototype.color_mix_to_rgba_array = function(input) {

		var match = input.match(this.color_mix_regex());

		if (!match) {

			return {

				r: 0,
				g: 0,
				b: 0,
				a: 1
			}
		}

		var color_1 = match[1];
		var color_2 = match[2];
		var weight = parseInt(match[3]) / 100;

		// Convert hex colors or rgba strings
		var rgba_array_1 = this.hex_color_to_rgba_array(color_1);
		var rgba_array_2 = this.hex_color_to_rgba_array(color_2);

		// Convert RGBA array to OKlab array
		var oklab_1 = this.rgba_array_to_oklab_array(rgba_array_1);
		var oklab_2 = this.rgba_array_to_oklab_array(rgba_array_2);

		// Interpolate OKLab values based on the weight
		var l = oklab_1.l * (1 - weight) + oklab_2.l * weight;
		var a = oklab_1.a * (1 - weight) + oklab_2.a * weight;
		var b = oklab_1.b * (1 - weight) + oklab_2.b * weight;
		var alpha = rgba_array_1.a * (1 - weight) + rgba_array_2.a * weight; // Interpolate alpha

		// Convert the mixed OKLab color back to sRGB
		return this.oklab_array_to_rgba_array({

			l: l,
			a: a,
			b: b,
			alpha: alpha
		});
	}

	// WCAG Level AAA contrast check
	$.WS_Form.prototype.wcag_level_aaa_contrast_check = function(color_foreground, color_background, large_text) {

		// Large text?
		if(!large_text) { large_text = false; }

		// Get contrast ratio
		var contrast_ratio = this.color_contrast_ratio(color_foreground, color_background);

		// Large text is defined as 14 point (typically 18.66px) and bold or larger, or 18 point (typically 24px) or larger.
		if(large_text) {

			return contrast_ratio >= 7;

		} else {

			return contrast_ratio >= 4.5;
		}
	}

	// WCAG Level AA contrast check
	$.WS_Form.prototype.wcag_level_aa_contrast_check = function(color_foreground, color_background, large_text) {

		// Large text?
		if(!large_text) { large_text = false; }

		// Get contrast ratio
		var contrast_ratio = this.color_contrast_ratio(color_foreground, color_background);

		// Large text is defined as 14 point (typically 18.66px) and bold or larger, or 18 point (typically 24px) or larger.
		if(large_text) {

			return contrast_ratio >= 4.5;

		} else {

			return contrast_ratio >= 3;
		}
	}

	$.WS_Form.prototype.color_alt_coloris_swatches = function(color) {

		// Convert to HSLA array
		var hsla_array_alt = this.color_to_hsla_array(this.color_to_color_alt(color));

		// Build swatches
		var swatches_hsla_array = [];

		// Generate lighter shades
		for (var i = 10; i >= 1; i--) {

			swatches_hsla_array.push({

				h: hsla_array_alt.h, 
				s: hsla_array_alt.s, 
				l: Math.min(100, hsla_array_alt.l + i * 10), 
				a: hsla_array_alt.a 
			});
		}

		// Add alt color
		swatches_hsla_array.push(hsla_array_alt);

		// Generate darker shades
		for (var i = 1; i <= 10; i++) {

			swatches_hsla_array.push({ 
				h: hsla_array_alt.h, 
				s: hsla_array_alt.s, 
				l: Math.max(0, hsla_array_alt.l - i * 10), 
				a: hsla_array_alt.a 
			});
		}

		// Build swatches
		var swatches = [];

		for(var swatches_hsla_array_index in swatches_hsla_array) {

			if(!swatches_hsla_array.hasOwnProperty(swatches_hsla_array_index)) { continue; }

			var swatch_hsla = swatches_hsla_array[swatches_hsla_array_index];

			// Process RGB
			if(this.color_is_rgb(color) || this.color_is_rgba(color)) {

				swatches.push(this.hsla_array_to_rgba_color(swatch_hsla));
				continue;
			}

			// Process HSL
			if(this.color_is_hsl(color) || this.color_is_hsla(color)) {

				swatches.push(this.hsla_array_to_hsla_color(swatch_hsla));
				continue;
			}

			// Process hex
			swatches.push(this.hsla_array_to_hex_color(swatch_hsla));
		}

		// Get unique values
		swatches = swatches.filter((swatch, index, arr) => arr.indexOf(swatch) === index);

		return swatches;
	}

})(jQuery);
