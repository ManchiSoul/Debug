<?php
	
	// Helper functions for developers

	/**
	 * Gets an array of all registered field types
	 *
	 * @return Array $field_types
	 */
	function wsf_config_get_field_types() {

		return WS_Form_Config::get_field_types_flat();	
	}

	/**
	 * Gets a form object by ID
	 *
	 * @param integer $form_id     The form ID
	 * @param boolean $get_meta    If set to true then form, group, section and field meta data will be included
	 * @param boolean $get_groups  If set to true, then group, section and field data will be included
	 *
	 * @return Object WS_Form_Form
	 */
	function wsf_form_get_object($form_id, $get_meta = true, $get_groups = true) {

		// Get form ID
		$form_id = absint($form_id);

		// Check form ID
		if($form_id === 0) { throw new Exception('Invalid form ID'); }

		// Create new form instance
		$ws_form_form = new WS_Form_Form();

		// Set form ID
		$ws_form_form->id = $form_id;

		// Return for object
		return $ws_form_form->db_read($get_meta, $get_groups, false, false, true );
	}

	/**
	 * Get the label of a form by ID
	 *
	 * @param integer $form_id     The form ID
	 *
	 * @return string|boolean      Form label or false if not found
	 */
	function wsf_form_get_label_by_id($form_id) {

		// Get form ID
		$form_id = absint($form_id);

		// Check form ID
		if($form_id === 0) { throw new Exception('Invalid form ID'); }

		// Create new form instance
		$ws_form_form = new WS_Form_Form();

		// Set form ID
		$ws_form_form->id = $form_id;

		// Return form label
		try {

			return $ws_form_form->db_get_label();

		} catch ( Exception $e ) {

			return false;
		}
	}

	/**
	 * Get the submission count of a form by ID
	 *
	 * @param integer $form_id     The form ID
	 *
	 * @return integer Total submissions
	 */
	function wsf_form_get_count_submit_by_id($form_id) {

		// Get form ID
		$form_id = absint($form_id);

		// Check form ID
		if($form_id === 0) { throw new Exception('Invalid form ID'); }

		// Create new form stat instance
		$ws_form_form_stat = new WS_Form_Form_Stat();

		// Set form ID
		$ws_form_form_stat->form_id = $form_id;

		// Get counts
		$counts = $ws_form_form_stat->db_get_counts();

		// Return submit count 
		return absint( $counts['count_submit'] );
	}

	/**
	 * Get an array containing all forms
	 *
	 * @param boolean $published  If true, only returned published forms
	 * @param string order_by     Defaults to order by label
	 *
	 * @return Array WS_Form_Form
	 */
	function wsf_form_get_all($published = false, $order_by = 'label') {

		// Build WHERE SQL
		$where_sql = $published ? 'status="publish"' : 'NOT status="trash"';

		// Build order_by_sql
		$order_by_sql = in_array($order_by, array('id', 'label', 'date_added', 'date_updated'), true) ? $order_by : 'label';

		// Initiate instance of Form class
		$ws_form_form = new WS_Form_Form();

		// Read all forms
		return $ws_form_form->db_read_all('', $where_sql, $order_by_sql, '', '', false, true);
	}

	/**
	 * Get an array containing all forms in a simple key value format
	 *
	 * @param boolean $published   If true, only returned published forms
	 * @param string order_by      Defaults to order by label
	 *
	 * @return Array WS_Form_Form  id => label
	 */
	function wsf_form_get_all_key_value($published = false, $order_by = 'label') {

		// Get all forms
		$forms = wsf_form_get_all($published = false, $order_by = 'label');

		// Build return array
		$return_array = array();

		foreach($forms as $form) {

			$return_array[$form['id']] = sprintf(__('%s (ID: %u)', 'ws-form'), esc_html($form['label']), $form['id']);
		}

		return $return_array;
	}

	/**
	 * Get a group (tab) object by ID
	 * Tabs in WS Form are known as groups in core
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $group_id        The group ID
	 *
	 * @return Object WS_Form_Group
	 */
	function wsf_group_get_object($form_object, $group_id = false) {

		// Check form object
		wsf_form_check($form_object);

		// Get group ID
		$group_id = absint($group_id);

		// Check group ID
		if($group_id === 0) { throw new Exception('Invalid group ID'); }

		// Return group object
		return wsf_group_get_objects($form_object, $group_id);
	}

	/**
	 * Get groups (tabs) or a group (tab) by ID from a form object
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $group_id        The group ID
	 *
	 * @return Array WS_Form_Group          If no tab specified
	 * @return WS_Form_Group                If group_id specified
	 */
	function wsf_group_get_objects($form_object, $group_id = false) {

		// Check form object
		wsf_form_check($form_object);

		// Get group ID
		$group_id = absint($group_id);

		// Check group ID
		if($group_id === 0) { throw new Exception('Invalid group ID'); }

		// Set up return groups array
		$return_groups = [];

		$groups = $form_object->groups;

		foreach($groups as $group) {

			if($group_id !== false) {

				if($group->id == $group_id) { return $group; }

			} else {

				$return_groups[$group->id] = $group;
			}
		}

		if(count($return_groups) === 0) {

			throw new Exception('Group not found');
		}

		return $return_groups;
	}

	/**
	 * Get a section object by ID
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $section_id      The section ID
	 *
	 * @return Object WS_Form_Section
	 */
	function wsf_section_get_object($form_object, $section_id = false) {

		// Check form object
		wsf_form_check($form_object);

		// Get section ID
		$section_id = absint($section_id);

		// Check section ID
		if($section_id === 0) { throw new Exception('Invalid section ID'); }

		// Return section object
		return wsf_section_get_objects($form_object, $section_id);
	}

	/**
	 * Get sections or a section by ID from a form object
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $section_id      The section ID
	 *
	 * @return Array WS_Form_Section        If no section specified
	 * @return WS_Form_Section              If section_id specified
	 */
	function wsf_section_get_objects($form_object, $section_id = false) {

		// Check form object
		wsf_form_check($form_object);

		$return_sections = [];

		$groups = $form_object->groups;

		foreach($groups as $group) {

			$sections = $group->sections;

			foreach($sections as $section) {

				if($section_id !== false) {

					if($section->id == $section_id) { return $section; }

				} else {

					$return_sections[$section->id] = $section;
				}
			}
		}

		if(count($return_sections) === 0) {

			throw new Exception('Section not found');
		}

		return $return_sections;
	}

	/**
	 * Get a field object by ID
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $field_id        The field ID
	 *
	 * @return Object WS_Form_Field
	 */
	function wsf_field_get_object($form_object, $field_id = false) {

		// Check form object
		wsf_form_check($form_object);

		// Get field ID
		$field_id = absint($field_id);

		// Check field ID
		if($field_id === 0) { throw new Exception('Invalid field ID'); }

		// Return field object
		return wsf_field_get_objects($form_object, $field_id);
	}

	/**
	 * Get fields by label
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param string       $field_label     The field label
	 *
	 * @return Array WS_Form_Field
	 */
	function wsf_field_get_objects_by_label($form_object, $field_label = false) {

		// Check form object
		wsf_form_check($form_object);

		// Check field label
		if(empty($field_label)) { throw new Exception('Invalid field label'); }

		// Return fields matching label
		return wsf_field_get_objects($form_object, false, $field_label);
	}

	/**
	 * Get field objects by meta
	 *
	 * @param WS_Form_Form $form_object      The form object
	 * @param string       $field_meta_key   The field meta key
	 * @param string       $field_meta_value The field meta value
	 *
	 * @return Array WS_Form_Field
	 */
	function wsf_field_get_objects_by_meta($form_object, $field_meta_key = false, $field_meta_value = false) {

		// Check form object
		wsf_form_check($form_object);

		// Check field meta key
		if(empty($field_meta_key)) { throw new Exception('Invalid field meta key'); }

		// Check field meta value
		if(empty($field_meta_value)) { throw new Exception('Invalid field meta value'); }

		// Return fields matching meta
		return wsf_field_get_objects($form_object, false, false, $field_meta_key, $field_meta_value);
	}

	/**
	 * Get field objects by class (Field wrapper or field class name)
	 *
	 * @param WS_Form_Form $form_object      The form object
	 * @param string       $class_name       The field class name or wrapper class name
	 *
	 * @return Array WS_Form_Field
	 */
	function wsf_field_get_objects_by_class($form_object, $class_name = false) {

		// Check form object
		wsf_form_check($form_object);

		// Check class name
		if(empty($class_name)) { throw new Exception('Invalid class name'); }

		try {

			// Return fields matching class name by field class
			$fields = wsf_field_get_objects($form_object, false, false, 'class_field', $class_name);

		} catch (Exception $e) {

			// Return fields matching class name by field wrapper class
			$fields = wsf_field_get_objects($form_object, false, false, 'class_field_wrapper', $class_name);
		}

		return $fields;
	}

	/**
	 * Get fields or a field by ID from a form object
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param integer      $field_id        Filter by field ID
	 * @param string       $field_label     Filter by field label
	 * @param string       $field_class     Filter by field class
	 *
	 * @return Array WS_Form_Field          If no field ID specified
	 * @return WS_Form_Field                If field ID specified
	 */
	function wsf_field_get_objects($form_object, $field_id = false, $field_label = false, $field_meta_key = false, $field_meta_value = false) {

		// Check form object
		wsf_form_check($form_object);

		$return_fields = [];

		$groups = $form_object->groups;

		// Process groups
		foreach($groups as $group) {

			$sections = $group->sections;

			// Process sections
			foreach($sections as $section) {

				$fields = $section->fields;

				// Process fields
				foreach($fields as $field) {

					// Check by field ID
					if($field_id !== false) {

						if($field->id == $field_id) { return $field; }

					// Check by field label
					} else if($field_label !== false) {

						if($field->label == $field_label) {

							$return_fields[] = $field;
						}

					// Check by field meta key
					} else if($field_meta_key !== false) {

						// Get meta value
						$meta_value = wsf_helper_get_object_meta_value($field, $field_meta_key);

						switch($field_meta_key) {

							// Space separated values support
							case 'class_field' :
							case 'class_field_wrapper' :

								if(in_array($field_meta_value, explode(' ', $meta_value))) {

									$return_fields[] = $field;
								}

								break;

							// Direct check
							default :

								if($field_meta_value == $meta_value) {

									$return_fields[] = $field;
								}
						}

					} else {

						$return_fields[$field->id] = $field;
					}
				}
			}
		}

		if(count($return_fields) === 0) {

			throw new Exception('Field not found');
		}

		return $return_fields;
	}

	/**
	 * Clear all rows in a field datagrid
	 *
	 * @param WS_Form_Field $field_object    The field object
	 * @param integer       $group_id        The group ID
	 *
	 * @return WS_Form_Field          The modified field
	 */
	function wsf_field_rows_clear($field_object, $group_id = false) {

		// Check field object
		wsf_field_check($field_object);

		// Get datagrid from field
		$datagrid = wsf_field_get_datagrid($field_object);

		// Get groups from datagrid
		$groups = wsf_datagrid_get_groups($datagrid, $group_id);
		
		// Process each row
		foreach($groups as $group) {

			$group->rows = array();
		}

		return $field_object;
	}

	/**
	 * Add a row to a field datagrid
	 *
	 * @param WS_Form_Field        $field_object    The field object
	 * @param WS_Form_Datagrid_Row $row             The row object
	 *
	 * @return WS_Form_Field              The modified field
	 */
	function wsf_field_row_add($field_object, $row = false) {

		// Check field object
		wsf_field_check($field_object);

		// Check datagrid row
		wsf_datagrid_row_check($row);

		// Get datagrid from field
		$datagrid = wsf_field_get_datagrid($field_object);

		// Get first group from datagrid
		$group = $datagrid->groups[0];

		// Check group
		wsf_datagrid_group_check($group);

		// Set defaults if not already set
		if(!isset($row->default)) { $row->default = false; }
		if(!isset($row->required)) { $row->required = false; }
		if(!isset($row->disabled)) { $row->disabled = false; }
		if(!isset($row->hidden)) { $row->hidden = false; }

		// Set row ID
		$row->id = wsf_group_row_id_next($group);

		// Add to rows
		$group->rows[] = $row;

		return $field_object;
	}

	/**
	 * Get a field datagrid
	 *
	 * @param WS_Form_Field $field_object  The field object
	 *
	 * @return WS_Form_Datagrid     The datagrid
	 */
	function wsf_field_get_datagrid($field_object) {

		// Check field object
		wsf_field_check($field_object);

		// Check for meta data
		if(!isset($field_object->meta)) { throw new Exception('Field meta data not found'); }

		switch($field_object->type) {

			case 'select' : $meta_key = 'data_grid_select'; break;
			case 'price_select' : $meta_key = 'data_grid_select_price'; break;
			case 'radio' : $meta_key = 'data_grid_radio'; break;
			case 'price_radio' : $meta_key = 'data_grid_radio_price'; break;
			case 'checkbox' : $meta_key = 'data_grid_checkbox'; break;
			case 'price_checkbox' : $meta_key = 'data_grid_checkbox_price'; break;
			default : $meta_key = 'data_grid';
		}

		if(!isset($field_object->meta->{$meta_key})) { throw new Exception('Field meta key ' . $meta_key . ' not found'); }

		return $field_object->meta->{$meta_key};
	}

	/**
	 * Get a field datagrid group
	 *
	 * @param WS_Form_Datagrid $datagrid_object  The datagrid object
	 * @param integer          $group_id         Group ID to filter by
	 *
	 * @return WS_Form_Datagrid_Group     The datagrid group object
	 */
	function wsf_datagrid_get_groups($datagrid_object, $group_id = false) {

		// Check the datagrid object
		wsf_datagrid_check($datagrid_object);

		// Get the groups
		$groups = $datagrid_object->groups;

		// Check the group ID
		if($group_id !== false) {

			if(!isset($groups[$group_id])) { throw new Exception('Group ID not found'); }

			return array($groups[$group_id]);
		}

		return $groups;
	}

	/**
	 * Get next datagrid group row ID
	 *
	 * @param WS_Form_Datagrid_Group $group_object  The datagrid group object
	 *
	 * @return WS_Form_Datagrid_Group     The datagrid group object
	 */
	function wsf_group_row_id_next($group_object) {

		$rows = $group_object->rows;

		$id_max = 0;
		foreach($rows as $row) {

			if(!isset($row->id)) { throw new Exception('Row ID not found'); }

			if($row->id > $id_max) { $id_max = $row->id; }
		}

		return ++$id_max;
	}

	/**
	 * Check form object is valid
	 * Throws an exception if form object is invalid
	 *
	 * @param WS_Form_Form $form_object    Form object
	 *
	 * @return None
	 */
	function wsf_form_check($form_object) {

		// Check form object
		if(
			!is_object($form_object) ||
			!property_exists($form_object, 'id') ||
			!property_exists($form_object, 'label')
		) {
			throw new Exception('Invalid form object');
		}
	}

	/**
	 * Check group object is valid
	 * Throws an exception if group object is invalid
	 *
	 * @param WS_Form_Group $group_object    Group object
	 *
	 * @return None
	 */
	function wsf_group_check($group_object) {

		// Check group object
		if(
			!is_object($group_object) ||
			!property_exists($group_object, 'id') ||
			!property_exists($group_object, 'label')
		) {
			throw new Exception('Invalid group object');
		}
	}

	/**
	 * Check section object is valid
	 * Throws an exception if section object is invalid
	 *
	 * @param WS_Form_Section $section_object    Group object
	 *
	 * @return None
	 */
	function wsf_section_check($section_object) {

		// Check section object
		if(
			!is_object($section_object) ||
			!property_exists($section_object, 'id') ||
			!property_exists($section_object, 'label')
		) {
			throw new Exception('Invalid section object');
		}
	}

	/**
	 * Check a field object
	 * Throws an exception if form object is invalid
	 *
	 * @param WS_Form_Field $field_object  The field object
	 *
	 * @return None
	 */
	function wsf_field_check($field_object) {

		if(
			!is_object($field_object) ||
			!property_exists($field_object, 'id') ||
			!property_exists($field_object, 'label') ||
			!property_exists($field_object, 'type')
		) {
			throw new Exception('Invalid field');
		}
	}

	/**
	 * Check a submit object
	 * Throws an exception if form object is invalid
	 *
	 * @param WS_Form_Submit $submit_object  The submit object
	 *
	 * @return None
	 */
	function wsf_submit_check($submit_object) {

		if(
			!is_object($submit_object) ||
			!property_exists($submit_object, 'id')
		) {
			throw new Exception('Invalid submit');
		}
	}

	/**
	 * Check a datagrid object
	 *
	 * @param WS_Form_Datagrid $datagrid  The datagrid object
	 *
	 * @return boolean true
	 */
	function wsf_datagrid_check($datagrid_object) {

		if(
			!is_object($datagrid_object) ||
			!isset($datagrid_object->groups)
		) { throw new Exception('Invalid datagrid'); }

		return true;
	}

	/**
	 * Check a datagrid group object
	 *
	 * @param WS_Form_Datagrid_Group $datagrid_group  The datagrid group object
	 *
	 * @return boolean true
	 */
	function wsf_datagrid_group_check($datagrid_group_object) {

		if(
			!is_object($datagrid_group_object) ||
			!isset($datagrid_group_object->rows)
		) { throw new Exception('Invalid datagrid group'); }

		return true;
	}

	/**
	 * Check a datagrid row object
	 *
	 * @param WS_Form_Datagrid_Row $datagrid_row_object  The datagrid row object
	 *
	 * @return boolean true
	 */
	function wsf_datagrid_row_check($datagrid_row_object) {

		if(
			!is_object($datagrid_row_object) ||
			!isset($datagrid_row_object->data) ||
			!is_array($datagrid_row_object->data)
		) { throw new Exception('Invalid datagrid row'); }

		return true;
	}

	/**
	 * Get a submit object by ID
	 *
	 * @param integer $submit_id  The submit ID
	 *
	 * @return WS_Form_Submit
	 */
	function wsf_submit_get_object($submit_id) {

		$ws_form_submit = New WS_Form_Submit();
		$ws_form_submit->id = $submit_id;
		$ws_form_submit->db_read(true, true, true);

		return $ws_form_submit;
	}

	/**
	 * Get a submit object by hash
	 *
	 * @param string $submit_hash  The submit hash
	 *
	 * @return WS_Form_Submit
	 */
	function wsf_submit_get_by_hash($submit_hash) {

		$ws_form_submit = New WS_Form_Submit();
		$ws_form_submit->hash = $submit_hash;
		$ws_form_submit->db_read_by_hash(true, true, false, true);

		return $ws_form_submit;
	}

	/**
	 * Get a submit value by meta key
	 *
	 * @param WS_Form_Submit $submit_object  The submit object
	 * @param string $meta_key               The meta key (e.g. 'field_123')
	 * @param string $default_value          Default value if not found
	 * @param boolen $protected              Set to true for accessing protected data (e.g. password)
	 *
	 * @return $value (String or Object depending on the field type)
	 */
	function wsf_submit_get_value($submit_object, $meta_key, $default_value = '', $protected = false) {

		wsf_submit_check($submit_object);

		return WS_Form_Action::get_submit_value(

			$submit_object,
			$meta_key,
			$default_value,
			$protected
		);
	}

	/**
	 * Get a repeatable submit value by meta key
	 *
	 * @param WS_Form_Submit $submit_object  The submit object
	 * @param string $meta_key               The meta key (e.g. 'field_123')
	 * @param string $default_value          Default value if not found
	 * @param boolen $protected              Set to true for accessing protected data (e.g. password)
	 *
	 * @return Array $meta_value (String or Object depending on the field type)
	 */
	function wsf_submit_get_value_repeatable($submit_object, $meta_key, $default_value = '', $protected = false) {

		wsf_submit_check($submit_object);

		return WS_Form_Action::get_submit_value_repeatable(

			$submit_object,
			$meta_key,
			$default_value,
			$protected
		);
	}

	/**
	 * Get a submit field value by class (Field wrapper or field class name)
	 *
	 * @param WS_Form_Form   $form_object      The form object
	 * @param WS_Form_Submit $submit_object    The submit object
	 * @param string         $class_name       The field class name or wrapper class name
	 * @param string         $default_value    Default value if not found
	 * @param boolean        $protected        Set to true for accessing protected data (e.g. password)
	 *
	 * @return mixed $field_value or Array of field values if more than one field found
	 */
	function wsf_submit_get_value_by_field_class($form_object, $submit_object, $class_name = false, $default_value = '', $protected = false) {

		// Check form object
		wsf_form_check($form_object);

		// Check submit object
		wsf_submit_check($submit_object);

		// Check class name
		if(empty($class_name)) { throw new Exception('Invalid class name'); }

		// Get fields
		$fields = wsf_field_get_objects_by_class($form_object, $class_name);

		// Check fields
		if(
			!is_array($fields) ||
			!isset($fields[0]) ||
			!property_exists($fields[0], 'id')
		) {
			throw new Exception('Invalid return from wsf_field_get_objects_by_class');
		}

		// Build return array
		$return_array = array();

		foreach($fields as $field) {

			// Check field
			if(
				!is_object($field) ||
				!property_exists($field, 'id')
			) {

				throw new Exception('Invalid field');
			}

			// Get submit value
			$return_array[] = wsf_submit_get_value($submit_object, sprintf('field_%u', $field->id), $default_value, $protected);
		}

		// Return value
		return (count($return_array) == 1) ? $return_array[0] : $return_array;
	}

	/**
	 * Get a repeatable submit field value by class (Field wrapper or field class name)
	 *
	 * @param WS_Form_Form   $form_object      The form object
	 * @param WS_Form_Submit $submit_object    The submit object
	 * @param string         $class_name       The field class name or wrapper class name
	 * @param string         $default_value    Default value if not found
	 * @param boolean        $protected        Set to true for accessing protected data (e.g. password)
	 *
	 * @return mixed $field_value
	 */
	function wsf_submit_get_value_repeatable_by_field_class($form_object, $submit_object, $class_name = false, $default_value = '', $protected = false) {

		// Check form object
		wsf_form_check($form_object);

		// Check submit object
		wsf_submit_check($submit_object);

		// Check class name
		if(empty($class_name)) { throw new Exception('Invalid class name'); }

		// Get fields
		$fields = wsf_field_get_objects_by_class($form_object, $class_name);

		// Check fields
		if(
			!is_array($fields) ||
			!isset($fields[0]) ||
			!property_exists($fields[0], 'id')
		) {
			throw new Exception('Invalid return from wsf_field_get_objects_by_class');
		}

		// Build return array
		$return_array = array();

		foreach($fields as $field) {

			// Check field
			if(
				!is_object($field) ||
				!property_exists($field, 'id')
			) {

				throw new Exception('Invalid field');
			}

			// Get submit value
			$return_array[] = wsf_submit_get_value_repeatable($submit_object, sprintf('field_%u', $field->id), $default_value, $protected);
		}

		// Return value
		return (count($return_array) == 1) ? $return_array[0] : $return_array;
	}

	/**
	 * Set a submit meta value by field ID
	 *
	 * @param WS_Form_Submit $submit  The submit object
	 * @param string $meta_key        The meta key
	 * @param string $meta_value      Meta value to set
	 *
	 * @return boolean true
	 */
	function wsf_submit_set_value($submit_object, $meta_key = '', $meta_value = '') {

		// Check submit object
		wsf_submit_check($submit_object);

		// Get submit ID
		$submit_id = absint(isset($submit_object->id) ? $submit_object->id : false);

		// Check submit ID
		if($submit_id === 0) { throw new Exception('Invalid submit ID'); }

		// Check meta key
		if(empty($meta_key)) { throw new Exception('Invalid meta key'); }

		// Build meta data
		$meta = array(array(

			$meta_key => $meta_value
		));

		// Update submit meta data
		$ws_form_submit_meta = New WS_Form_Submit_Meta();
		$ws_form_submit_meta->parent_id = $submit_id;
		return $ws_form_submit_meta->db_update_from_array($meta);
	}

	/**
	 * Set a submit meta value by field ID
	 *
	 * @param WS_Form_Submit $submit  The submit object
	 * @param integer $field_id       The field ID
	 * @param $meta_value             Meta value to set
	 *
	 * @return boolean true
	 */
	function wsf_submit_set_value_by_field_id($submit_object, $field_id, $meta_value = '') {

		// Check submit object
		wsf_submit_check($submit_object);

		// Get submit ID
		$submit_id = absint(isset($submit_object->id) ? $submit_object->id : false);

		// Check submit ID
		if($submit_id === 0) { throw new Exception('Invalid submit ID'); }

		// Check field ID
		$field_id = absint($field_id);
		if($field_id === 0) { throw new Exception('Invalid field ID'); }

		// Build meta data
		$meta = array(array(

			'id' => $field_id,
			'value' => $meta_value
		));

		// Update submit meta data
		$ws_form_submit_meta = New WS_Form_Submit_Meta();
		$ws_form_submit_meta->parent_id = $submit_id;
		return $ws_form_submit_meta->db_update_from_array($meta);
	}

	/**
	 * Get a meta value by meta key for an object
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param string       $property        The object property
	 * @param string       $default_value   The default value to return if the property is not found
	 *
	 * @return string      $meta_value      Returns $default_value if not found
	 */
	function wsf_helper_get_object_property($object, $property, $default_value = '') {

		return WS_Form_Common::get_object_property($object, $property, $default_value);
	}

	/**
	 * Get a meta value by meta key for an object
	 *
	 * @param WS_Form_Form $form_object     The form object
	 * @param string       $meta_key        The meta key
	 * @param string       $default_value   The default value to return if the meta key is not found
	 *
	 * @return string      $meta_value      Returns $default_value if not found
	 */
	function wsf_helper_get_object_meta_value($object, $meta_key = false, $default_value = '') {

		return WS_Form_Common::get_object_meta_value($object, $meta_key, $default_value);
	}


	// Legacy wrappers
	function wsf_form_get_form_object($form_id, $get_meta = true, $get_groups = true) {

		return wsf_form_get_object($form_id, $get_meta, $get_groups);
	}

	function wsf_form_get_form_label($form_id) {

		return wsf_form_get_label_by_id($form_id);
	}

	function wsf_form_get_tabs($form_object, $group_id = false) {

		return wsf_group_get_objects($form_object, $group_id);
	}

	function wsf_form_get_tab($form_object, $group_id = false) {

		return wsf_group_get_object($form_object, $group_id);
	}

	function wsf_form_get_group($form_object, $group_id = false) {

		return wsf_group_get_object($form_object, $group_id);
	}

	function wsf_form_get_section($form_object, $section_id = false) {

		return wsf_section_get_object($form_object, $section_id);
	}

	function wsf_form_get_field($form_object, $field_id = false) {

		return wsf_field_get_object($form_object, $field_id);
	}

	function wsf_form_get_fields_by_label($form_object, $field_label = false) {

		return wsf_field_get_objects_by_label($form_object, $field_label);
	}

	function wsf_form_get_fields_by_meta($form_object, $field_meta_key = false, $field_meta_value = false) {

		return wsf_field_get_objects_by_meta($form_object, $field_meta_key, $field_meta_value);
	}

	function wsf_form_get_groups($form_object, $group_id = false) {

		return wsf_group_get_objects($form_object, $group_id);
	}

	function wsf_form_get_sections($form_object, $section_id = false) {

		return wsf_section_get_objects($form_object, $section_id);
	}

	function wsf_form_get_fields($form_object, $field_id = false) {

		return wsf_field_get_objects($form_object, $field_id);
	}

	function wsf_submit_set_field_value($submit_object, $field_id, $meta_value = '') {

		return wsf_submit_set_value_by_field_id($submit_object, $field_id, $meta_value);
	}

	function wsf_form_get_count_submit($form_id) {

		return wsf_form_get_count_submit_by_id($form_id);
	}
