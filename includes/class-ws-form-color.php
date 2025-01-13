<?php

	class WS_Form_Color {

		public static function get_palette() {

			// Base - WS Form
			$palette = self::get_palette_ws_form();

			// Global settings - Theme
			$palette_theme = self::get_palette_global_settings_color_palette('theme');
			if(!empty($palette_theme)) {

				$palette = $palette_theme;
			}

			// Filter
			$palette = apply_filters('wsf_palette', $palette);

			return $palette;
		}

		public static function get_palette_global_settings_color_palette($type = 'default') {

			if(!function_exists('wp_get_global_settings')) { return array(); }

			$palette = array();

			$global_settings_color_palette_default = wp_get_global_settings(array('color', 'palette', $type));

			if(empty($global_settings_color_palette_default) || !is_array($global_settings_color_palette_default)) {

				return $palette;
			}

			foreach($global_settings_color_palette_default as $color) {

				if(
					!isset($color['slug']) ||
					!isset($color['name']) ||
					!isset($color['color'])
				) {
					continue;
				}

				$palette[] = array(

					'label' => $color['name'],
					'color' => $color['color']
				);
			}

			return $palette;
		}

		public static function get_palette_ws_form() {

			return array(

				array(

					'label' => __('Background', 'ws-form'),
					'color' => self::get_color_background(),
				),

				array(

					'label' => __('Base', 'ws-form'),
					'color' => self::get_color_base(),
				),

				array(

					'label' => __('Base - Contrast', 'ws-form'),
					'color' => self::get_color_base_contrast(),
				),

				array(

					'label' => __('Accent', 'ws-form'),
					'color' => self::get_color_accent(),
				),

				array(

					'label' => __('Neutral', 'ws-form'),
					'color' => self::get_color_neutral(),
				),

				array(

					'label' => __('Primary', 'ws-form'),
					'color' => self::get_color_primary(),
				),

				array(

					'label' => __('Secondary', 'ws-form'),
					'color' => self::get_color_secondary(),
				),

				array(

					'label' => __('Success', 'ws-form'),
					'color' => self::get_color_success(),
				),

				array(

					'label' => __('Information', 'ws-form'),
					'color' => self::get_color_info(),
				),

				array(

					'label' => __('Warning', 'ws-form'),
					'color' => self::get_color_warning(),
				),

				array(

					'label' => __('Danger', 'ws-form'),
					'color' => self::get_color_danger(),
				),
			);
		}

		public static function color_to_color_alt($color) {

			// Transparent
			if($color == 'transparent') { return $color; }

			// Var
			if(preg_match('/var\((.*)\)/', $color, $matches)) {

				$var_parameters = $matches[1];

				$var_parameters_array = explode(',', $var_parameters);

				foreach($var_parameters_array as $var_parameter_index => $var_parameter) {

					$var_parameter = trim($var_parameter);

					if(strpos($var_parameter, '--wsf-') === false) { continue; }

					if(strpos($var_parameter, '-alt') === false) {

						// Add -alt
						$var_parameters_array[$var_parameter_index] = $var_parameter .= '-alt';

					} else {

						// Removee -alt
						$var_parameters_array[$var_parameter_index] = str_replace('-alt', '', $var_parameter);
					}
				}

				return sprintf(

					'var(%s)',
					implode(', ', $var_parameters_array)
				);
			}

			// Hex
			if(self::color_is_hex($color) || self::color_is_hex_alpha($color)) {

				// Convert color to HSLA array
				$hsla_array = self::hex_color_to_hsla_array($color);

				// Get alt color for HLSA array
				$hsla_array = self::hsla_array_alt($hsla_array);

				return self::hsla_array_to_hex_color($hsla_array);
			}

			// RGB
			if(self::color_is_rgb($color) || self::color_is_rgba($color)) {

				// Convert color to HSLA array
				$hsla_array = self::rgba_color_to_hsla_array($color);

				// Get alt color for HLSA array
				$hsla_array = self::hsla_array_alt($hsla_array);

				return self::hsla_array_to_rgba_color($hsla_array);
			}

			// HSL
			if(self::color_is_hsl($color) || self::color_is_hsla($color)) {

				// Convert color to HSLA array
				$hsla_array = self::hsla_color_to_hsla_array($color);

				// Get alt color for HLSA array
				$hsla_array = self::hsla_array_alt($hsla_array);

				return self::hsla_array_to_hsla_color($hsla_array);
			}

			return $color;
		}

		public static function hsla_color_to_hsla_array($hsla_color) {

			if(preg_match('/^hsla?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $hsla_color, $matches)) {

				// HSL
				$a = isset($matches[4]) ? floatval($matches[4]) : 1;

				return array(

					'h' => intval($matches[1]),
					's' => intval($matches[2]),
					'l' => intval($matches[3]),
					'a' => round($a, 2)
				);

			} else {

				return array(

					'h' => 0,
					's' => 0,
					'l' => 0,
					'a' => 1
				);
			}
		}

		public static function hex_color_to_hsla_array($hex_color) {

			// Get RGBA array
			$rgba_array = self::hex_color_to_rgba_array($hex_color);

			// Extract variables
			extract($rgba_array);

			// Normalize
			$r /= 255;
			$g /= 255;
			$b /= 255;

			// Get RGB values
			$min = min(array($r, $g, $b));
			$max = max(array($r, $g, $b));

			$l = ($max + $min) / 2;

			if($max == $min) {

				$h = $s = 0;

			} else {

				$diff = $max - $min;
				$s = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);

				switch($max) {

					case $r:
						$h = ($g - $b) / $diff + ($g < $b ? 6 : 0);
						break;

					case $g:
						$h = ($b - $r) / $diff + 2;
						break;

					case $b:
						$h = ($r - $g) / $diff + 4;
						break;
				}

				$h /= 6;
			}

			// Return the color in HSLA format
			return array(

				'h' => round($h * 360),     // Hue 0 - 360
				's' => round($s * 100),     // Saturation 0 - 100
				'l' => round($l * 100),     // Lightness 0 - 100
				'a' => $a 					// Alpha 0 - 1
			);
		}

		public static function hex_color_to_rgba_array($hex_color, $a = false) {
		 
			// Check #hex
			if(empty($hex_color)) { $hex_color = '#000000'; } 

			// Strip hash
			$hex_color = self::hex_color_strip_hash($hex_color);

			// Alpha default
			if($a === false) { $a = 1; }

			// Build hex array	 
			if(strlen($hex_color) == 8) {

				$hex_array = array($hex_color[0] . $hex_color[1], $hex_color[2] . $hex_color[3], $hex_color[4] . $hex_color[5], $hex_color[6] . $hex_color[7]);

			} elseif ( strlen( $hex_color ) == 6 ) {

				$hex_array = array($hex_color[0] . $hex_color[1], $hex_color[2] . $hex_color[3], $hex_color[4] . $hex_color[5]);

			} elseif ( strlen( $hex_color ) == 3 ) {

				$hex_array = array($hex_color[0] . $hex_color[0], $hex_color[1] . $hex_color[1], $hex_color[2] . $hex_color[2]);

			} else {

				$hex_array = array('00', '00', '00');
			}
	 
			$rgba = array_map('hexdec', $hex_array);

			if(isset($rgba[3])) {

				$a = $rgba[3] / 255;
			}

			return array(

				'r' => $rgba[0],	// Red 0 - 255
				'g' => $rgba[1],	// Green 0 - 255
				'b' => $rgba[2],	// Blue 0 - 255
				'a' => $a		// Alpha 0 - 1
			);
		}

		public static function hex_color_to_rgba_color($hex_color, $a = false) {

			return self::rgba_array_to_rgba_color(self::hex_color_to_rgba_array($hex_color, $a));
		}

		public static function hex_color_strip_hash($hex_color) {

			return trim($hex_color, '# ');
		}

		public static function hex_color_lighten_percentage($hex_color, $percentage) {

			// Get HSLA array
			$hsla_array = self::hex_color_to_hsla_array($hex_color);

			// Lightn
			$hsla_array = self::hsla_array_lighten($hsla_array, $percentage);

			// Return
			return self::hsla_array_to_hex_color($hsla_array);
		}

		public static function hex_color_darken_percentage($hex_color, $percentage) {

			// Get HSLA array
			$hsla_array = self::hex_color_to_hsla_array($hex_color);

			// Darken
			$hsla_array['l'] = ($hsla_array['l'] * ((100 - $percentage) / 100));

			// Return
			return self::hsla_array_to_hex_color($hsla_array);
		}

		public static function hsla_array_to_rgba_color($hsla_array) {

			return self::rgba_array_to_rgba_color(self::hsla_array_to_rgba_array($hsla_array));
		}

		public static function hsla_array_to_hsla_color($hsla_array) {

			extract($hsla_array);

			// Check values
			if(!isset($h)) { $h = 0; }
			if(!isset($s)) { $s = 0; }
			if(!isset($l)) { $l = 0; }
			if(!isset($a)) { $a = 1; }

			// Format the saturation and lightness as percentages
			$s .= '%';
			$l .= '%';

			// Check if the alpha is 1, in which case we return hsl
			if ($a == 1) {

				return "hsl($h, $s, $l)";

			} else {

				return "hsla($h, $s, $l, $a)";
			}
		}

		public static function hsla_array_to_rgba_array($hsla_array) {

			extract($hsla_array);

			// Normalize the input HSL values
			$h = $h % 360; // Hue must be between 0 and 360
			$s = $s / 100; // Saturation is a percentage
			$l = $l / 100; // Lightness is a percentage
			$a = isset($a) ? $a : 1.0; // Alpha defaults to 1.0 if not provided

			// Initialize RGB values
			$r = $g = $b = 0;

			if ($s == 0) {
				// If saturation is 0, the color is achromatic (gray), so R = G = B = L
				$r = $g = $b = $l * 255;
			} else {
				// Perform the conversion from HSL to RGB
				$q = ($l < 0.5) ? ($l * (1 + $s)) : ($l + $s - $l * $s);
				$p = 2 * $l - $q;

				// Helper function to adjust hue
				$hue2rgb = function($p, $q, $t) {
					if ($t < 0) $t += 1;
					if ($t > 1) $t -= 1;
					if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
					if ($t < 1/2) return $q;
					if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
					return $p;
				};

				// Convert the hue to RGB
				$r = $hue2rgb($p, $q, $h / 360 + 1/3) * 255;
				$g = $hue2rgb($p, $q, $h / 360) * 255;
				$b = $hue2rgb($p, $q, $h / 360 - 1/3) * 255;
			}

			return array(

				'r' => floor($r),	// Red 0 - 255
				'g' => floor($g),	// Green 0 - 255
				'b' => floor($b),	// Blue 0 - 255
				'a' => $a 			// Alpha 0 - 1
			);
		}

		public static function hsla_array_to_hex_color($hsla_array) {

			extract($hsla_array);

			// If alpha is 0, return #00000000
			if ($a == 0) {
				return '#000000';
			}

			// Convert HSLA to RGBA
			$rgba_array = self::hsla_array_to_rgba_array($hsla_array);

			return self::rgba_array_to_hex_color($rgba_array);
		}

		public static function hsla_array_lighten($hsla_array, $percentage) {

			// Ensure percentage is between 0 and 100
			$percentage = max(0, min(100, $percentage));

			// Lighten
			$hsla_array['l'] = min(100, $hsla_array['l'] + ($percentage / 100) * (100 - $hsla_array['l']));

			// Return the new HSLA array
			return $hsla_array;
		}

		public static function hsla_array_darken($hsla_array, $percentage) {

			// Ensure percentage is between 0 and 100
			$percentage = max(0, min(100, $percentage));

			// Darken
			$hsla_array['l'] = max(0, $hsla_array['l'] - ($percentage / 100) * $hsla_array['l']);

			// Return the new HSLA array
			return $hsla_array;
		}

		public static function hsla_array_alt($hsla_array) {

			// Get lightness
			$lightness = $hsla_array['l'];

			// Check lightness
			if($lightness < 0) { $lightness = 0; }
			if($lightness > 100) { $lightness = 100; }

			// Flip lightness
			$hsla_array['l'] = 100 - $lightness;

			return $hsla_array;
		}

		public static function rgba_color_to_hsla_array($rgba_color) {

			return self::rgba_array_to_hsla_array(self::rgba_color_to_rgba_array($rgba_color));
		}

		public static function rgba_color_to_rgba_array($rgba_color) {

			if(preg_match('/^rgba?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $rgba_color, $matches)) {

				// RGBA
				$a = isset($matches[4]) ? floatval($matches[4]) : 1;

				return array(

					'r' => intval($matches[1]),
					'g' => intval($matches[2]),
					'b' => intval($matches[3]),
					'a' => round($a, 2)
				);

			} else {

				return array(

					'r' => 0,
					'g' => 0,
					'b' => 0,
					'a' => 1
				);
			}
		}

		public static function rgba_array_to_hsla_array($rgba_array) {

			extract($rgba_array);

			// Normalize
			$r /= 255;
			$g /= 255;
			$b /= 255;

			$max = max(array($r, $g, $b));
			$min = min(array($r, $g, $b));
			$h = $s = $l = ($max + $min) / 2;

			if ($max == $min) {
				$h = $s = 0; // achromatic
			} else {
				$d = $max - $min;
				$s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

				switch ($max) {
					case $r:
						$h = ($g - $b) / $d + ($g < $b ? 6 : 0);
						break;
					case $g:
						$h = ($b - $r) / $d + 2;
						break;
					case $b:
						$h = ($r - $g) / $d + 4;
						break;
				}

				$h /= 6;
			}

			// Return the color in HSLA format
			return array(

				'h' => round($h * 360),     // Hue 0 - 360
				's' => round($s * 100),     // Saturation 0 - 100
				'l' => round($l * 100),     // Lightness 0 - 100
				'a' => $a 					// Alpha 0 - 1
			);
		}

		public static function rgba_array_to_hex_color($rgba_array) {

			extract($rgba_array);

			// Convert RGB to Hex
			$hexR = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
			$hexG = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
			$hexB = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

			// If alpha is 1, return 6 character hex
			if ($a == 1) {

				return "#$hexR$hexG$hexB";
			}

			// Otherwise, calculate alpha hex and return 8 character hex
			$hexAlpha = str_pad(dechex(round($a * 255)), 2, '0', STR_PAD_LEFT);

			return "#$hexR$hexG$hexB$hexAlpha";
		}

		public static function rgba_array_to_rgba_color($rgba_array) {

			// If alpha channel is 1, get rid of it
			if($rgba_array['a'] === 1) {

				array_pop($rgba_array);

				return 'rgb(' . implode(',', $rgba_array) . ')';

			} else {

				return 'rgba(' . implode(',', $rgba_array) . ')';
			}
		}

		// Green (0) --> Lime Green (25) --> Yellow (50) --> Orange (75) --> Red (100)
		public static function get_green_to_red_rgb($value, $min = 0, $max = 100) {

			// Calculate ratio
			$ratio = $value / $max;
			if($ratio < 0) { $ratio = 0; }
			if($ratio > 1) { $ratio = 1; }

			// Red
			$r = ($ratio * 2) * 255;
			$r = ($r > 255) ? 255 : $r;

			// Green
			$g = (2 - ($ratio * 2)) * 255;
			$g = ($g > 255) ? 255 : $g;

			// Blue
			$b = 0;

			return "rgb($r, $g, $b)";
		}

		public static function color_is_hex($color) {

			if(preg_match('/^#([a-f0-9]{3,6})$/i', $color)) {

				$hex = self::hex_color_strip_hash($color);

				return ((strlen($hex) == 3) || (strlen($hex) == 6));
			}

			return false;
		}

		public static function color_is_hex_alpha($color) {

			if(preg_match('/^#([a-f0-9]{8})$/i', $color)) {

				$hex = self::hex_color_strip_hash($color);

				return (strlen($hex) == 8);
			}

			return false;
		}

		public static function color_is_rgb($color) {

			if(preg_match('/^rgba?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $color, $matches)) {

				return !isset($matches[4]);
			}

			return false;
		}

		public static function color_is_rgba($color) {

			if(preg_match('/^rgba?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*,\s*([\d]{1,3})\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $color, $matches)) {

				return isset($matches[4]);
			}

			return false;
		}

		public static function color_is_hsl($color) {

			if(preg_match('/^hsla?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $color, $matches)) {

				return !isset($matches[4]);
			}

			return false;
		}

		public static function color_is_hsla($color) {

			if(preg_match('/^hsla?\(\s*([\d]{1,3})\s*,\s*([\d]{1,3})%\s*,\s*([\d]{1,3})%\s*(?:,\s*(0|1|0?\.\d+))?\s*\)$/i', $color, $matches)) {

				return isset($matches[4]);
			}

			return false;
		}

		public static function get_color_background() {

			return apply_filters('wsf_color_background', 'transparent');
		}

		public static function get_color_background_alt() {

			return apply_filters('wsf_color_background_alt', '#000000');
		}

		public static function get_color_base() {

			return apply_filters('wsf_color_base', '#000000');
		}

		public static function get_color_base_contrast() {

			return apply_filters('wsf_color_base_contrast', '#ffffff');
		}

		public static function get_color_accent() {

			return apply_filters('wsf_color_accent', '#205493');
		}

		public static function get_color_neutral() {

			return apply_filters('wsf_color_neutral', '#767676');
		}

		public static function get_color_primary() {

			return apply_filters('wsf_color_primary', '#205493');
		}

		public static function get_color_secondary() {

			return apply_filters('wsf_color_secondary', '#5b616b');
		}

		public static function get_color_success() {

			return apply_filters('wsf_color_success', '#2e8540');
		}

		public static function get_color_info() {

			return apply_filters('wsf_color_info', '#02bfe7');
		}

		public static function get_color_warning() {

			return apply_filters('wsf_color_warning', '#fdb81e');
		}

		public static function get_color_danger() {

			return apply_filters('wsf_color_danger', '#bb0000');
		}

		// Get shades - Dark
		public static function get_shades_dark() {

			return apply_filters('wsf_styler_color_shades_dark', array(

				// Darken
				'dark-90' => array('label' => __('Dark 90', 'ws-form'), 'mix' => '#000', 'amount' => 90),
				'dark-80' => array('label' => __('Dark 80', 'ws-form'), 'mix' => '#000', 'amount' => 80),
				'dark-70' => array('label' => __('Dark 70', 'ws-form'), 'mix' => '#000', 'amount' => 70),
				'dark-60' => array('label' => __('Dark 60', 'ws-form'), 'mix' => '#000', 'amount' => 60),
				'dark-50' => array('label' => __('Dark 50', 'ws-form'), 'mix' => '#000', 'amount' => 50),
				'dark-40' => array('label' => __('Dark 40', 'ws-form'), 'mix' => '#000', 'amount' => 40),
				'dark-30' => array('label' => __('Dark 30', 'ws-form'), 'mix' => '#000', 'amount' => 30),
				'dark-20' => array('label' => __('Dark 20', 'ws-form'), 'mix' => '#000', 'amount' => 20),
				'dark-10' => array('label' => __('Dark 10', 'ws-form'), 'mix' => '#000', 'amount' => 10),
			));
		}

		// Get shades - Light
		public static function get_shades_light() {

			return apply_filters('wsf_styler_color_shades_light', array(

				// Lighten
				'light-10' => array('label' => __('Light 10', 'ws-form'), 'mix' => '#fff', 'amount' => 10),
				'light-20' => array('label' => __('Light 20', 'ws-form'), 'mix' => '#fff', 'amount' => 20),
				'light-30' => array('label' => __('Light 30', 'ws-form'), 'mix' => '#fff', 'amount' => 30),
				'light-40' => array('label' => __('Light 40', 'ws-form'), 'mix' => '#fff', 'amount' => 40),
				'light-50' => array('label' => __('Light 50', 'ws-form'), 'mix' => '#fff', 'amount' => 50),
				'light-60' => array('label' => __('Light 60', 'ws-form'), 'mix' => '#fff', 'amount' => 60),
				'light-70' => array('label' => __('Light 70', 'ws-form'), 'mix' => '#fff', 'amount' => 70),
				'light-80' => array('label' => __('Light 80', 'ws-form'), 'mix' => '#fff', 'amount' => 80),
				'light-90' => array('label' => __('Light 90', 'ws-form'), 'mix' => '#fff', 'amount' => 90),
			));
		}

		// Get shades
		public static function get_shades() {

			return apply_filters('wsf_styler_color_shades', array_merge(

				self::get_shades_dark(),
				self::get_shades_light()
			));
		}

		// Is color transparent
		public static function color_is_transparent($color) {

			return (
				empty($color) ||
				(strtolower($color) === 'transparent')
			);
		}
	}
