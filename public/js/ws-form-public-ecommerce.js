(function($) {

	'use strict';

	// Form e-commerce
	$.WS_Form.prototype.form_ecommerce = function() {

		var ws_this = this;

		// Has e-commerce?
		this.has_ecommerce = $('[data-ecommerce-price],[data-ecommerce-cart-price],[data-ecommerce-cart-total],[data-ecommerce-payment]', this.form_canvas_obj).length;
		if(!this.has_ecommerce) { return; }

		// Add price type
		for(var price_type in $.WS_Form.ecommerce.cart_price_types) {

			if(!$.WS_Form.ecommerce.cart_price_types.hasOwnProperty(price_type)) { continue; }

			this.form_add_hidden_input('wsf_ecommerce_cart[' + price_type + ']', '', ws_this.form_id_prefix + 'ecommerce-cart-' + price_type);
		}

		// Add e-commerce fields to form
		this.form_add_hidden_input('wsf_ecommerce_cart[total]', '', ws_this.form_id_prefix + 'ecommerce-cart-total');
		this.form_add_hidden_input('wsf_ecommerce_transaction', '', ws_this.form_id_prefix + 'ecommerce-transaction');
		this.form_add_hidden_input('wsf_ecommerce_transaction_id', '', ws_this.form_id_prefix + 'ecommerce-transaction-id');
		this.form_add_hidden_input('wsf_ecommerce_status', '', ws_this.form_id_prefix + 'ecommerce-status');
		this.form_add_hidden_input('wsf_ecommerce_payment_method', '', ws_this.form_id_prefix + 'ecommerce-payment-method');

		// Set ecommerce status to new
		this.form_ecommerce_set_status('new');

		// Initialize cart price type array for storing values
		this.ecommerce_cart_price_type = [];

		// Apply input masks
		this.form_ecommerce_input_mask_currency();

		// Event handlers
		$('input[data-ecommerce-price]:not([data-init-ecommerce-event]),input[data-ecommerce-cart-price]:not([data-init-ecommerce-event]),select[data-ecommerce-price]:not([data-init-ecommerce-event])', this.form_canvas_obj).on('keyup change input paste', function(e) {

			// Flag so it only initializes once
			$(this).attr('data-init-ecommerce-event', '');

			// Check for values less than min
			if(typeof($(this).attr('min')) !== 'undefined') {

				var min = parseInt($(this).attr('min'), 10);
				if(
					(e.type == 'input') &&
					(parseInt($(this).val(), 10) < min)

				) { $(this).val(min); }
			}

			// Check for values greater than max
			if(typeof($(this).attr('max')) !== 'undefined') {

				var max = parseInt($(this).attr('max'), 10);
				if(
					(e.type == 'input') &&
					(parseInt($(this).val(), 10) > max)

				) { $(this).val(max); }
			}

			// Recalculate
			ws_this.form_ecommerce_calculate();
		});

		$('input[data-ecommerce-quantity]:not([data-init-ecommerce-event])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-ecommerce-event', '');

			// Recalculate if value changes
			$(this).on('change keyup input paste', function() {

				ws_this.form_ecommerce_calculate();
			});

			// Bounds
			$(this).on('change', function(e) {

				ws_this.form_ecommerce_quantity_bounds($(this));
			});
		});

		// Initial calculation
		this.form_ecommerce_calculate();
	}

	// Form e-commerce - Quantity bounds
	$.WS_Form.prototype.form_ecommerce_quantity_bounds = function(obj) {

		var recalc = false;

		// Check for blank quantity values
		if(obj.val() == '') {

			obj.val(0);

			recalc = true;
		}

		// Check for values less than min
		if(typeof(obj.attr('min')) !== 'undefined') {

			var min = parseInt(obj.attr('min'), 10);
			if(parseInt(obj.val(), 10) < min) {

				obj.val(min);

				recalc = true;
			}
		}

		// Check for values greater than max
		if(typeof(obj.attr('max')) !== 'undefined') {

			var max = parseInt(obj.attr('max'), 10);
			if(parseInt(obj.val(), 10) > max) {

				obj.val(max);

				recalc = true;
			}
		}

		if(recalc) {

			// Initial calculation
			this.form_ecommerce_calculate();
		}
	}

	// Form e-commerce - Input mask currency
	$.WS_Form.prototype.form_ecommerce_input_mask_currency = function(obj) {

		var ws_this = this;

		if(typeof(obj) === 'undefined') {

			obj = $('input[type="text"][data-ecommerce-price],input[data-ecommerce-cart-price],input[data-ecommerce-cart-total]', this.form_canvas_obj);
		}

		// Apply input mask to price fields
		obj.each(function() {

			// Plugin level config
			var currency = ws_this.get_currency();

			// Build input mask settings
			var input_mask_settings = {

				digits: currency.decimals,
				allowMinus: $(this)[0].hasAttribute('data-ecommerce-negative'),
				suffix: currency.suffix,
				groupSeparator: currency.thousand_separator,
				prefix: currency.prefix,
				rightAlign: false,
				radixPoint: currency.decimal_separator,
				removeMaskOnSubmit: true,
				clearMaskOnLostFocus: false
			};

			// Min
			if($(this)[0].hasAttribute('data-ecommerce-min')) { input_mask_settings.min = $(this).attr('data-ecommerce-min'); }

			// Max
			if($(this)[0].hasAttribute('data-ecommerce-max')) { input_mask_settings.max = $(this).attr('data-ecommerce-max'); }

			// Ensure inputmask is loaded
			if(typeof($(this).inputmask) !== 'undefined') {

				// Initialize inputmask on field. Remove invalid event handler to avoid blur bug.
				$(this).inputmask('currency', input_mask_settings).off('invalid');

				if(typeof($(this).attr('required')) !== 'undefined') {

					// Validate on change
					$(this).on('input change paste', function() {

						ws_this.form_ecommerce_input_mask_currency_validate($(this));
					});

					// Initial validation
					ws_this.form_ecommerce_input_mask_currency_validate($(this));
				}
			}
		});
	}

	// Form - Input mask - Validate
	$.WS_Form.prototype.form_ecommerce_input_mask_currency_validate = function(obj) {

		if(this.get_number(obj.val(), 0, true) > 0) {

			this.set_invalid_feedback(obj, '');

		} else {

			this.set_invalid_feedback(obj);
		}
	}

	// Form e-commerce - Calculate
	$.WS_Form.prototype.form_ecommerce_calculate = function() {

		if(!this.form_ecommerce_calculate_enabled) { return; }

		// Totals
		var ecommerce_cart_subtotal = 0;
		var ecommerce_cart_totals = [];
		var ecommerce_cart_total_float = 0;

		var currency = this.get_currency();
		var price_subtotal_array = [];

		var ws_this = this;

		// Run through all the price fields
		$('input[data-ecommerce-price],select[data-ecommerce-price]', this.form_canvas_obj).not('[data-ecommerce-price-bypass],[data-ecommerce-price-bypass-section]').each(function() {

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get repeatable index
			var section_repeatable_index = ws_this.get_section_repeatable_index($(this));

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Price subtotal array index
			var price_subtotal_array_index = field_id + section_repeatable_suffix;

			// Field product type
			var field_product_type = $(this).closest('[data-ecommerce-cart-price-type]');
			if(field_product_type.length) { field_product_type = field_product_type.attr('data-ecommerce-cart-price-type'); } else { field_product_type = 'subtotal'; }

			if(typeof(price_subtotal_array[price_subtotal_array_index]) === 'undefined') {

				price_subtotal_array[price_subtotal_array_index] = {

					subtotal: 0,
					type: field_product_type,
					field_id: field_id,
					section_repeatable_index: section_repeatable_index,
					exclude_price_cart: (typeof($(this).attr('data-wsf-exclude-cart-total')) !== 'undefined')
				};
			}

			// Get price
			switch($(this).attr('type')) {

				case 'checkbox' :
				case 'radio' :

					price_subtotal_array[price_subtotal_array_index].subtotal += $(this).is(':checked') ? ws_this.get_number($(this).attr('data-price'), 0, true) : 0;
					break;

				default :

					if($(this).is('select')) {

						$(this).find('option:selected').each(function() {

							price_subtotal_array[price_subtotal_array_index].subtotal += ws_this.get_number($(this).attr('data-price'), 0, true);
						})

					} else {

						price_subtotal_array[price_subtotal_array_index].subtotal += ws_this.get_number($(this).val());
					}
			}
		});

		// Get price decimal points
		var price_decimals = parseInt($.WS_Form.settings_plugin.price_decimals, 10);

		// Calculate sub-totals
		for(var price_subtotal_array_index in price_subtotal_array) {

			if(!price_subtotal_array.hasOwnProperty(price_subtotal_array_index)) { continue; }

			// Get field ID
			var field_id = price_subtotal_array[price_subtotal_array_index].field_id;

			// Get field repeatable index
			var section_repeatable_index = price_subtotal_array[price_subtotal_array_index].section_repeatable_index;

			// Get price subtotal (price * quantity) calculated above
			var price_subtotal = price_subtotal_array[price_subtotal_array_index].subtotal;

	 		// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
	 		price_subtotal = this.get_number(price_subtotal, 0, false, price_decimals);

			// Price type
			var price_type = price_subtotal_array[price_subtotal_array_index].type;

			// Price type
			var exclude_price_cart = price_subtotal_array[price_subtotal_array_index].exclude_price_cart;

			// Find associated quantity field
			if(section_repeatable_index === 0) {

				var quantity_obj = $('input[data-ecommerce-quantity][data-ecommerce-field-id="' + this.esc_selector(field_id) + '"]', this.form_canvas_obj);

			} else {

				var quantity_obj = $('[data-repeatable-index="' + this.esc_selector(section_repeatable_index) + '"] input[data-ecommerce-quantity][data-ecommerce-field-id="' + this.esc_selector(field_id) + '"]', this.form_canvas_obj);
			}
			if(quantity_obj.length) {

				// Multiply price by quantity
				var quantity = this.get_number(quantity_obj.first().val(), 0, false);
				price_subtotal *= quantity;

		 		// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
		 		price_subtotal = this.get_number(price_subtotal, 0, false, price_decimals);
			};

			// Find associated subtotal field
			if(section_repeatable_index === 0) {

				var field_obj = $('input[data-ecommerce-price-subtotal][data-ecommerce-field-id="' + this.esc_selector(field_id) + '"]', this.form_canvas_obj);

			} else {

				var field_obj = $('[data-repeatable-index="' + this.esc_selector(section_repeatable_index) + '"] input[data-ecommerce-price-subtotal][data-ecommerce-field-id="' + this.esc_selector(field_id) + '"]', this.form_canvas_obj);
			}

			if(field_obj.length) {

				var field_trigger = (this.get_number(field_obj.val(), 0, true) !== this.get_number(price_subtotal, 0, false));
				field_obj.val(this.get_price(price_subtotal, currency));
				if(field_trigger) { field_obj.trigger('change'); }
			}

			// Add to total by price type
			if(!exclude_price_cart) {

				if(typeof(ecommerce_cart_totals[price_type]) === 'undefined') { ecommerce_cart_totals[price_type] = 0; }
				ecommerce_cart_totals[price_type] += price_subtotal;
			}
		}

		// Process price types and calculate cart total
		for(var price_type in $.WS_Form.ecommerce.cart_price_types) {

			if(!$.WS_Form.ecommerce.cart_price_types.hasOwnProperty(price_type)) { continue; }

			// Read price type config
			var price_type_config = $.WS_Form.ecommerce.cart_price_types[price_type];
	
			// Should this price type be included in the ecommerce_cart_total_float calculation?
			var sum = (typeof(price_type_config.sum) !== 'undefined') ? price_type_config.sum : true;

			// Should we render the value of this calculated price type?
			var render = (typeof(price_type_config.render) !== 'undefined') ? price_type_config.render : false;

			// Calculate cart total
			if(price_type == 'subtotal') {

		 		// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
		 		ecommerce_cart_totals[price_type] = this.get_number(ecommerce_cart_totals[price_type], 0, false, price_decimals);

				var price_float = (typeof(ecommerce_cart_totals[price_type]) !== 'undefined') ? ecommerce_cart_totals[price_type] : 0;

			} else {

				// Add up all instances of this price type on the form at that are visible (i.e. includes attrbute data-ecommerce-cart-price)
				var price_float = 0;
				$('input[data-ecommerce-cart-price][data-ecommerce-cart-price-' + price_type + ']', this.form_canvas_obj).each(function() {

					price_float += ws_this.get_number($(this).val());
				});

		 		// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
		 		price_float = this.get_number(price_float, 0, false, price_decimals);
			}

			if(sum) {

				// Add this price type to the cart total
				ecommerce_cart_total_float += price_float;
			}

			// Should WS Form render this value to the price type field? (e.g. Subtotal)
			if(render && sum) {

				var price_string = this.get_price(price_float, currency, false);
				var price_currency = this.get_price(price_float, currency);

				// Render price
				var price_type_selector = '[data-ecommerce-cart-price-' + price_type + ']';
				var price_type_total_id = '#' + this.form_id_prefix + 'ecommerce-cart-' + price_type;
				this.form_ecommerce_price_type_set(price_type, price_type_selector, price_type_total_id, price_float, price_string, price_currency);
			}
		}

 		// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
 		ecommerce_cart_total_float = this.get_number(ecommerce_cart_total_float, 0, false, price_decimals);

		// Convert to string
		var price_string = this.get_price(ecommerce_cart_total_float, currency, false);
		var price_currency = this.get_price(ecommerce_cart_total_float, currency);

		// Save amount
		this.ecommerce_cart_price_type['total'] = {

			'float': ecommerce_cart_total_float,
			'string': price_string,
			'currency': price_currency,
		};

		// Render prices
		var price_type_selector = '[data-ecommerce-cart-total]';
		var price_type_total_id = '#' + this.form_id_prefix + 'ecommerce-cart-total';
		this.form_ecommerce_price_type_set(false, price_type_selector, price_type_total_id, ecommerce_cart_total_float, price_string, price_currency);
	}

	// Form e-commerce - Set price
	$.WS_Form.prototype.form_ecommerce_price_type_set = function(price_type, price_type_selector, price_type_total_id, price_float, price_string, price_currency) {

		var ws_this = this;

		// Set cart total fields
		var field_obj = $(price_type_selector, this.form_canvas_obj);

		field_obj.each(function() {

			if($(this).is('input')) {

				// Get new value
				var value_new = ws_this.get_number(price_currency, 0);

				// Check for negative value
				var allow_negative = $(this)[0].hasAttribute('data-ecommerce-negative');

				if(
					(value_new < 0) &&
					!allow_negative
				) {

					ws_this.error('error_ecommerce_negative_value', ws_this.get_field_id($(this)), 'error-ecommerce');
				}

				// Set input
				var field_trigger = (ws_this.get_number($(this).val(), 0, true) != value_new);

				// We use price_string to ensure:
				// - get_price(value, currency, false)
				// - No currency symbol
				// - Decimal and thousand separator characters are correct
				$(this).val(price_string);

				if(field_trigger) { $(this).trigger('change'); }
			}

			if($(this).is('span')) {

				// Set span
				var ecommerce_price_span = (typeof($(this).attr('data-ecommerce-price-currency')) !== 'undefined') ? price_currency : price_string;
				$(this).html(ecommerce_price_span);
			}
		});

		// Set hidden price type field
		var field_obj = $(price_type_total_id, this.form_canvas_obj);
		var field_trigger = (ws_this.get_number(field_obj.val(), 0, false) != ws_this.get_number(price_float, 0, false));
		field_obj.val(price_float)
		if(field_trigger) { field_obj.trigger('change'); }

		if(price_type !== false) {

			// Save amount
			this.ecommerce_cart_price_type[price_type] = {

				'float': price_float,
				'string': price_string,
				'currency': price_currency,
			};
		}
	}

	// Form e-commerce - Payment error
	$.WS_Form.prototype.form_ecommerce_payment_error = function(error_message) {

		// Log error
		this.error('error_payment', error_message, 'payment');
	}

	// Form e-commerce - Payment processed
	$.WS_Form.prototype.form_ecommerce_payment_process = function(transaction_id, amount, payment_method) {

		// Change status
		this.form_ecommerce_set_status('completed');

		// Set transaction ID
		this.form_ecommerce_set_transaction_id(transaction_id);

		// Set payment method
		this.form_ecommerce_set_payment_method(payment_method);

		// Set payment amount (Overrides calculated amount in case the payment button has a fixed value, we always want to show the processed amount in submissions)
		this.form_ecommerce_set_payment_amount(amount);

		// Submit the form and run actions
		this.form_obj.trigger('submit');
	}

	// Form e-commerce - Set status
	$.WS_Form.prototype.form_ecommerce_set_status = function(status) {

		$('#' + this.form_id_prefix + 'ecommerce-status', this.form_canvas_obj).val(status);
		this.log('log_ecommerce_status', status, 'ecommerce');
	}

	// Form e-commerce - Set transaction ID
	$.WS_Form.prototype.form_ecommerce_set_transaction_id = function(transaction_id) {

		$('#' + this.form_id_prefix + 'ecommerce-transaction-id', this.form_canvas_obj).val(transaction_id);
		this.log('log_ecommerce_transaction_id', transaction_id, 'ecommerce');
	}

	// Form e-commerce - Set transaction
	$.WS_Form.prototype.form_ecommerce_set_transaction = function(transaction) {

		$('#' + this.form_id_prefix + 'ecommerce-transaction', this.form_canvas_obj).val(transaction);
	}

	// Form e-commerce - Set payment method
	$.WS_Form.prototype.form_ecommerce_set_payment_method = function(payment_method) {

		$('#' + this.form_id_prefix + 'ecommerce-payment-method', this.form_canvas_obj).val(payment_method);
		this.log('log_ecommerce_payment_method', payment_method, 'ecommerce');
	}

	// Form e-commerce - Set cart total amount
	$.WS_Form.prototype.form_ecommerce_set_payment_amount = function(amount) {

		this.form_ecommerce_calculate_enabled = false;
		$('#' + this.form_id_prefix + 'ecommerce-cart-total', this.form_canvas_obj).val(amount).trigger('change');
		this.log('log_ecommerce_payment_amount', amount, 'ecommerce');
	}

})(jQuery);
