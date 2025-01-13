<?php

	#[AllowDynamicProperties]
	class WS_Form_Section extends WS_Form_Core {

		public $id;
		public $parent_section_id;
		public $form_id;
		public $group_id;
		public $child_count;
		public $new_lookup;
		public $label;
		public $meta;
	
		public $table_name;

		const DB_INSERT = 'label,child_count,user_id,date_added,date_updated,sort_index,group_id,parent_section_id';
		const DB_UPDATE = 'label,user_id,date_updated';
		const DB_SELECT = 'label,child_count,sort_index,id';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->parent_section_id = 0;
			$this->form_id = 0;
			$this->group_id = 0;
			$this->child_count = 0;
			$this->new_lookup = array();
			$this->new_lookup['section'] = array();
			$this->new_lookup['field'] = array();
			$this->label = __('Section', 'ws-form');
			$this->meta = array();

			$this->table_name = sprintf('%s%ssection', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);;
		}

		// Create section
		public function db_create($next_sibling_id = 0) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			// Check group ID
			self::db_check_group_id();

			global $wpdb;

			// Process sort index
			$sort_index = self::db_object_sort_index_get($this->table_name, 'group_id', $this->group_id, $next_sibling_id);

			// Sanitize label
			self::sanitize_label(__('Section', 'ws-form'));

			// Add section
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, 0, %d, %s, %s, %d, %d, 0);",
				$this->label,
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				$sort_index,
				$this->group_id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding section', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Build meta data array
			$settings_form_admin = WS_Form_Config::get_settings_form_admin();
			$meta_data = $settings_form_admin['sidebars']['section']['meta'];
			$meta_keys = WS_Form_Config::get_meta_keys();
			$meta_data = self::build_meta_data($meta_data, $meta_keys);
			$meta_data = (object) array_merge($meta_data, (array) $this->meta);

			// Build meta data
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'section';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_update_from_object($meta_data);

			return $this->id;
		}

		public function db_create_from_form_object($form_object, $next_sibling_id = 0) {

			// Check group ID
			self::db_check_group_id();

			// See if section ID set (if so we're going to delete it later as this will replace it)
			$section_id_old = $this->id ? $this->id : false;

			// Add form ID to lookups
			$this->new_lookup['form'][$form_object->id] = $this->form_id;

			// Check form object has at least one group
			if(
				!isset($form_object->groups) ||
				!isset($form_object->groups[0]) ||
				!isset($form_object->groups[0]->id)
			) {
				parent::db_throw_error(__('No group found in form object', 'ws-form'));
			}

			// Get groups from existing form
			$ws_form_group = new WS_Form_Group();
			$ws_form_group->form_id = $this->form_id;
			$groups_existing = $ws_form_group->db_read_all();
			$groups_existing = json_decode(wp_json_encode($groups_existing));

			// Check existing form has at least one group
			if(
				(count($groups_existing) == 0) ||
				!isset($groups_existing[0]) ||
				!isset($groups_existing[0]->id)
			) {
				parent::db_throw_error(__('No group found in existing form', 'ws-form'));
			}

			// Add existing group 0 ID to lookups
			$this->new_lookup['group'][$form_object->groups[0]->id] = $groups_existing[0]->id;

			// Check form object has at least one section
			if(
				!isset($form_object->groups[0]->sections) ||
				!isset($form_object->groups[0]->sections[0])
			) {

				parent::db_throw_error(__('No sections found in form object', 'ws-form'));
			}

			// Get sections
			$sections = $form_object->groups[0]->sections;

			// Build array of touched sections
			$filter_section_ids = array();

			// Run through all first group sections in existing template
			foreach($sections as $section) {

				// Process sort index
				$sort_index = self::db_object_sort_index_get($this->table_name, 'group_id', $this->group_id, $next_sibling_id);

				// Set sort index
				$section->sort_index = $sort_index;

				// Create new object and add new section ID to filter
				$filter_section_ids[] = self::db_update_from_object($section, true, true);
			}

			// Check if section ID set, if so this is a request to replace the existing section
			if($section_id_old) {

				$this->id = $section_id_old;
				$this->db_delete();
			}

			// Build array of touched conditional logic
			$filter_conditional_row_indexes = array();

			// Get conditional in form object
			$form_object_conditional = WS_Form_Common::get_object_meta_value($form_object, 'conditional', false);

			// Run through each condition in form object
			if(
				isset($form_object_conditional->groups) &&
				isset($form_object_conditional->groups[0]) &&
				isset($form_object_conditional->groups[0]->rows) &&
				is_array($form_object_conditional->groups[0]->rows) &&
				(count($form_object_conditional->groups[0]->rows) > 0)
			) {

				$rows = $form_object_conditional->groups[0]->rows;

				// Read conditional in existing form
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'form';
				$ws_form_meta->parent_id = $this->form_id;
				$conditional = $ws_form_meta->db_get_object_meta('conditional');

				// Create conditional data grid if it doesn't exist
				if(
					!isset($conditional->groups) ||
					!isset($conditional->groups[0]) ||
					!isset($conditional->groups[0]->rows) ||
					!is_array($conditional->groups[0]->rows)
				) {

					$meta_keys = WS_Form_Config::get_meta_keys();
					$conditional = json_decode(wp_json_encode($meta_keys['conditional']['default']));
				}

				// Get next column row index
				$row_index_base = count($conditional->groups[0]->rows);

				foreach($rows as $row_index => $row) {

					// Add new conditional row
					$conditional->groups[0]->rows[] = $row;

					// Add row index to filter
					$filter_conditional_row_indexes[] = $row_index_base + $row_index;
				}

				$ws_form_meta->db_update_from_array(array('conditional' => $conditional));
			}
			// Create instance to run repair routines
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			// Set new lookups
			$ws_form_form->new_lookup = $this->new_lookup;

			// Fix data - Meta (Reads the form)
			$ws_form_form->db_meta_repair(false, $filter_section_ids);

			// Fix data - Conditionals
			$ws_form_form->db_conditional_repair($filter_conditional_row_indexes);
			// Update checksum
			$ws_form_form->db_checksum();

			return $form_object->label;
		}

		// Read record to array
		public function db_read($get_meta = true, $get_fields = false) {

			// User capability check
			WS_Form_Common::user_must('read_form');

			global $wpdb;

			// Add fields
			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);

			$section_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($section_array)) { parent::db_wpdb_handle_error(__('Unable to read section', 'ws-form')); }

			foreach($section_array as $key => $value) {

				$this->{$key} = $value;
			}

			if($get_meta) {

				// Read meta
				$section_meta = New WS_Form_Meta();
				$section_meta->object = 'section';
				$section_meta->parent_id = $this->id;
				$metas = $section_meta->db_read_all();
				$section_array['meta'] = $metas;
				$this->meta = $metas;
			}

			if($get_fields) {

				// Read fields
				$ws_form_field = New WS_Form_Field();
				$ws_form_field->section_id = $this->id;
				$fields = $ws_form_field->db_read_all($get_meta);
				$section_array['fields'] = $fields;
			}

			// Convert into object
			$section_object = json_decode(wp_json_encode($section_array));

			// Return array
			return $section_object;
		}

		// Check if record exists
		public function db_check() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			global $wpdb;

			$sql = $wpdb->prepare(

				"SELECT id FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);
			$return_array = $wpdb->get_row($sql, 'ARRAY_A');

			return !is_null($return_array);
		}

		// Read record
		public function db_read_all($get_meta = true, $checksum = false, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_form', $bypass_user_capability_check);

			self::db_check_group_id();

			global $wpdb;

			$fields_array = array();

			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE group_id = %d ORDER BY sort_index",
				$this->group_id
			);

			$sections = $wpdb->get_results($sql, 'ARRAY_A');

			if($sections) {

				foreach($sections as $key => $section) {

					if($get_meta) {

						// Get meta data for each section
						$section_meta = New WS_Form_Meta();
						$section_meta->object = 'section';
						$section_meta->parent_id = $section['id'];
						$metas = $section_meta->db_read_all($bypass_user_capability_check);
						$sections[$key]['meta'] = $metas;
					}

					// Checksum
					if($checksum && isset($sections[$key]['date_updated'])) {

						unset($sections[$key]['date_updated']);
					}

					// Get fields
					$ws_form_field = New WS_Form_Field();
					$ws_form_field->section_id = $section['id'];
					$ws_form_field_return = $ws_form_field->db_read_all($get_meta, $checksum, $bypass_user_capability_check);
					$sections[$key]['fields'] = $ws_form_field_return;
				}

				return $sections;

			} else {

				return [];
			}
		}

		// Delete
		public function db_delete($repair = true) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			$parent_section_id = self::db_get_parent_section_id();

			global $wpdb;

			// Delete section
			$sql = $wpdb->prepare(

				"DELETE FROM {$this->table_name} WHERE id = %d;",
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting section', 'ws-form')); }

			// Delete meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'section';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_delete_by_object();

			// Delete section fields
			$ws_form_field = New WS_Form_Field();
			$ws_form_field->form_id = $this->form_id;
			$ws_form_field->section_id = $this->id;
			$ws_form_field->db_delete_by_section(false);

			// Repair conditional, actions and meta data to remove references to this deleted section
			if($repair) {

				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->new_lookup['section'][$this->id] = '';
				$ws_form_form->db_conditional_repair();
				$ws_form_form->db_action_repair();
				$ws_form_form->db_meta_repair();
			}
		}

		// Delete all sections in group
		public function db_delete_by_group($repair = true) {

			self::db_check_group_id();

			global $wpdb;

			if($repair) {

				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $this->form_id;
			}

			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE group_id = %d",
				$this->group_id
			);

			$sections = $wpdb->get_results($sql, 'ARRAY_A');

			if($sections) {

				foreach($sections as $key => $section) {

					// Delete section
					$this->id = $section['id'];
					self::db_delete(false);

					if($repair) {

						$ws_form_form->new_lookup['section'][$this->id] = '';
					}
				}
			}

			// Repair conditional, actions and meta data to remove references to these deleted fields
			if($repair) {

				$ws_form_form->db_conditional_repair();
				$ws_form_form->db_action_repair();
				$ws_form_form->db_meta_repair();
			}
		}

		// Clone - All
		public function db_clone_all($group_id_copy_to, $parent_section_id = 0, $parent_section_id_copied = 0) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			global $wpdb;

			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE group_id = %d AND parent_section_id = %d ORDER BY sort_index",
				$this->group_id,
				$parent_section_id
			);
			$sections = $wpdb->get_results($sql, 'ARRAY_A');

			if($sections) {

				foreach($sections as $key => $section) {

					// Read data required for copying
					$this->id = $section['id'];
					$this->label = $section['label'];
					$this->sort_index = $section['sort_index'];
					$this->group_id = $group_id_copy_to;
					$this->parent_section_id = $parent_section_id_copied;
					$this->child_count = $section['child_count'];

					$section_id_new = self::db_clone();
				}
			}
		}

		// Clone
		public function db_clone() {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			global $wpdb;

			// Clone section
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, %d, %d, %s, %s, %d, %d, %d);",
				$this->label,
				$this->child_count,
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				$this->sort_index,
				$this->group_id,
				$this->parent_section_id
			);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error cloning section', 'ws-form')); }

			// Get new section ID
			$section_id_new = $wpdb->insert_id;

			// Clone meta data
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'section';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_clone_all($section_id_new);

			// Clone fields
			$ws_form_field = New WS_Form_Field();
			$ws_form_field->form_id = $this->form_id;
			$ws_form_field->section_id = $this->id;
			$ws_form_field->db_clone_all($section_id_new);

			return $section_id_new;
		}

		// Get checksum of current form and store it to database
		public function db_checksum() {

			// Check form ID
			self::db_check_form_id();

			// Calculate new form checksum
			$form = New WS_Form_Form();
			$form->id = $this->form_id;
			$checksum = $form->db_checksum();

			return $checksum;
		}

		// Push section from array
		public function db_update_from_object($section_object, $full = true, $new = false, $replace_meta = false) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			// Check for section ID in $section
			if(isset($section_object->id) && !$new) { $this->id = absint($section_object->id); }
			if($new) {

				$this->id = 0;
				$section_object_id_old = (isset($section_object->id)) ? absint($section_object->id) : 0;
				if(isset($section_object->id)) { unset($section_object->id); }
			}

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $section_object, 'section', $this->id);
			if($new && $section_object_id_old) { $this->new_lookup['section'][$section_object_id_old] = $this->id; }

			// Base meta for new records
			if(!isset($section_object->meta) || !is_object($section_object->meta)) { $section_object->meta = new stdClass(); }
			if($new) {

				$settings_form_admin = WS_Form_Config::get_settings_form_admin();
				$meta_data = $settings_form_admin['sidebars']['section']['meta'];
				$meta_keys = WS_Form_Config::get_meta_keys();
				$meta_data_array = self::build_meta_data($meta_data, $meta_keys);
				$section_object->meta = (object) array_merge($meta_data_array, (array) $section_object->meta);
			}

			// Update meta
			if(isset($section_object->meta)) {

				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'section';
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_update_from_object($section_object->meta, false, false, $replace_meta);
			}

			if($full) {

				// Update fields
				if(isset($section_object->fields)) {

					$ws_form_field = New WS_Form_Field();
					$ws_form_field->section_id = $this->id;
					$ws_form_field->db_update_from_array($section_object->fields, $new, $replace_meta);

					if($new) {

						$this->new_lookup['field'] = $this->new_lookup['field'] + $ws_form_field->new_lookup['field'];
					}
				}
			}

			return $this->id;
		}

		// Push all groups from array
		public function db_update_from_array($sections, $new, $replace_meta = false) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			self::db_check_group_id();

			global $wpdb;

			// Change date_updated to null for all records
			$wpdb->update($this->table_name, array('date_updated' => null), array('group_id' => $this->group_id, 'parent_section_id' => $this->parent_section_id));

			foreach($sections as $section) {

				self::db_update_from_object($section, true, $new, $replace_meta);
			}

			// Delete any sections that were not updated
			$wpdb->delete($this->table_name, array('date_updated' => null, 'group_id' => $this->group_id, 'parent_section_id' => $this->parent_section_id));

			return true;
		}

		// Update child_count
		public function db_update_child_count($id) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			self::db_check_id();

			global $wpdb;

			// Get child_count
			$sql = $wpdb->prepare(

				"SELECT IFNULL(COUNT(id), 0) FROM {$this->table_name} WHERE parent_section_id = %d;",
				$this->id
			);
			$child_count = $wpdb->get_var($sql);
			if(is_null($child_count)) { parent::db_wpdb_handle_error(__('Unable to determine section child count', 'ws-form')); }

			// Update section child_count
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET child_count = %u WHERE id = %d;",
				$child_count,
				$this->id
			);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Unable to update section child count', 'ws-form')); }
		}

		// Get group ID
		public function db_get_group_id() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			if($this->id == 0) { parent::db_throw_error(__('Section ID is zero, cannot get group ID', 'ws-form')); }

			global $wpdb;

			$sql = $wpdb->prepare(

				"SELECT group_id FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);

			$group_id = $wpdb->get_var($sql);
			if($group_id === false) { parent::db_wpdb_handle_error(__('Error getting group ID', 'ws-form')); }

			return $group_id;
		}

		// Get section parent ID
		public function db_get_parent_section_id() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			if($this->id == 0) { parent::db_throw_error(__('Section ID is zero, cannot get section parent ID', 'ws-form')); }

			global $wpdb;

			$sql = $wpdb->prepare(

				"SELECT parent_section_id FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);

			$parent_section_id = $wpdb->get_var($sql);
			if($parent_section_id === false) { parent::db_wpdb_handle_error(__('Error getting section parent ID', 'ws-form')); }

			return $parent_section_id;
		}

		// Get breakpoint size meta of last section added
		public function db_set_breakpoint_size_meta() {

			global $wpdb;

			self::db_check_group_id();

			// Get column count of last section added
			$sql = $wpdb->prepare(

				"SELECT id FROM {$this->table_name} WHERE group_id = %d AND parent_section_id = 0 ORDER BY sort_index DESC LIMIT 1",
				$this->group_id
			);

			$last_section_id = $wpdb->get_var($sql);
			if($last_section_id === false) { parent::db_wpdb_handle_error(__('Unable to determine last section added', 'ws-form')); }
			$inherit_last_meta = !is_null($last_section_id);

			if($inherit_last_meta) {

				// Get framework
				$framework = WS_Form_Common::option_get('framework');

				// Get framework breakpoints
				$frameworks = WS_Form_Config::get_frameworks();
				$breakpoints = $frameworks['types'][$framework]['breakpoints'];

				// Get breakpoints column counts
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'section';
				$ws_form_meta->parent_id = $last_section_id;

				// Add framework sizes to section meta to be inherited
				$section_metas = array();
				foreach($breakpoints as $key => $value) {

					$this->meta['breakpoint_size_' . $key] = $ws_form_meta->db_get_object_meta('breakpoint_size_' . $key, '');
				}
			}

			return $this->meta;
		}

		// API - POST - Download - JSON
		public function db_get_form_object($published = false) {

			// User capability check
			if(!$published) {

				WS_Form_Common::user_must('export_form');
			}

			// Check form ID
			self::db_check_form_id();

			// Check section ID
			self::db_check_id();

			// Read section
			$section_object = self::db_read(true, true);

			// Read form
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			// Get form
			if($published) {

				$form_object = $ws_form_form->db_read_published();

			} else {

				$form_object = $ws_form_form->db_read(true, true);
			}

			// Clean form
			unset($form_object->checksum);
			unset($form_object->published_checksum);

			// Stamp form data
			$form_object->identifier = WS_FORM_IDENTIFIER;
			$form_object->version = WS_FORM_VERSION;
			$form_object->time = time();
			$form_object->status = 'draft';
			$form_object->count_submit = 0;
			$form_object->meta->tab_index = 0;
			$form_object->meta->export_object = 'section';

			// Set form label to section label
			$form_object->label = $section_object->label;

			// Remove groups
			while(isset($form_object->groups[1])) {

				unset($form_object->groups[1]);
				$form_object->groups = array_values($form_object->groups);
			}

			// Set group label to section label
			$form_object->groups[0]->label = $section_object->label;

			// Add exported section to group
			$form_object->groups[0]->sections = array($section_object);

			// Filter conditions
			$form_object = self::db_conditional_filter($form_object, $section_object);
			// Add checksum
			$form_object->checksum = md5(wp_json_encode($form_object));

			return $form_object;
		}

		// Filter conditional logic
		public function db_conditional_filter($form_object, $section_object) {

			// Get section ID
			$this->new_lookup['section'][$section_object->id] = $section_object->id;

			// Get field IDs
			foreach($section_object->fields as $field_obj) {

				$this->new_lookup['field'][$field_obj->id] = $field_obj->id;
			}

			// Get conditional configuration
			$settings_conditional = WS_Form_Config::get_settings_conditional(false);

			// Get parse variables repairable
			$parse_variables_repairable = WS_Form_Config::get_parse_variables_repairable();

			// Read conditional
			$conditional = WS_Form_Common::get_object_meta_value($form_object, 'conditional');

			// Data integrity check
			if(!isset($conditional->groups)) { return $form_object; }
			if(!isset($conditional->groups[0])) { return $form_object; }
			if(!isset($conditional->groups[0]->rows)) { return $form_object; }

			// Run through each conditional (data grid rows)
			$rows = $conditional->groups[0]->rows;

			foreach($rows as $row_index => $row) {

				// Data integrity check
				if(!isset($row->data)) { continue; }
				if(!isset($row->data[1])) { continue; }

				// Keep row
				$keep_row = false;

				$data = $row->data[1];

				// Data integrity check
				if(gettype($data) !== 'string') { continue; }
				if($data == '') { continue; }

				// Converts conditional JSON string to object
				$conditional_json_decode = json_decode($data);
				if(is_null($conditional_json_decode)) { continue; }

				// Process IF conditions
				$if = $conditional_json_decode->if;

				// Run through each group in $if
				foreach($if as $key_if => $group) {

					$conditions = $group->conditions;

					// Run through each condition
					foreach($conditions as $key_condition => $condition) {

						if(
							isset($condition->object) &&
							isset($this->new_lookup[$condition->object])
						) {

							// Section or field found
							if(isset($this->new_lookup[$condition->object][$condition->object_id])) {

								// Check to see if this logic is for a field (e.g. the logic has a field selector)
								if(
									isset($settings_conditional['objects'][$condition->object]) &&
									isset($settings_conditional['objects'][$condition->object]['logic']) &&
									isset($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]) &&
									isset($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]['values']) &&
									($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]['values'] === 'fields')
								) {

									$keep_row = true;
								}
							}
						}

						// String replace - Field
						foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

							if(isset($condition->value)) {

								foreach($parse_variables_repairable['field'] as $parse_variable) {

									if(
										(strpos($condition->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
										(strpos($condition->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
									) {

										$keep_row = true;
									}
								}
							}
						}

						// String replace - Section
						foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

							if(isset($condition->value)) {

								foreach($parse_variables_repairable['section'] as $parse_variable) {

									if(
										(strpos($condition->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
									) {

										$keep_row = true;
									}
								}
							}
						}
					}
				}

				// Process THEN actions
				$then = $conditional_json_decode->then;

				// Run through each group in $then
				foreach($then as $key_then => $then_single) {

					if(isset($then_single->object) && isset($this->new_lookup[$then_single->object]) && isset($this->new_lookup[$then_single->object][$then_single->object_id])) {

						$keep_row = true;
					}

					// String replace - Field
					foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

						if(isset($then_single->value)) {

							foreach($parse_variables_repairable['field'] as $parse_variable) {

								if(
									(strpos($then_single->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
									(strpos($then_single->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}

					// String replace - Section
					foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

						if(isset($then_single->value)) {

							foreach($parse_variables_repairable['section'] as $parse_variable) {

								if(
									(strpos($then_single->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}
				}

				// Process ELSE actions
				$else = $conditional_json_decode->else;

				// Run through each group in $else
				foreach($else as $key_else => $else_single) {

					if(isset($else_single->object) && isset($this->new_lookup[$else_single->object]) && isset($this->new_lookup[$else_single->object][$else_single->object_id])) {

						$keep_row = true;
					}

					// String replace - Field
					foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

						if(isset($else_single->value)) {

							foreach($parse_variables_repairable['field'] as $parse_variable) {

								if(
									(strpos($else_single->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
									(strpos($else_single->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}

					// String replace - Section
					foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

						if(isset($else_single->value)) {

							foreach($parse_variables_repairable['section'] as $parse_variable) {

								if(
									(strpos($else_single->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}
				}

				if(!$keep_row) {

					unset($form_object->meta->conditional->groups[0]->rows[$row_index]);
				}
			}

			// Reset row indexes
			$form_object->meta->conditional->groups[0]->rows = array_values($form_object->meta->conditional->groups[0]->rows);

			return $form_object;
		}
		// Check form_id
		public function db_check_form_id() {

			if(absint($this->form_id) === 0) { parent::db_throw_error(__('Invalid form ID', 'ws-form')); }
			return true;
		}

		// Check form_id
		public function db_check_group_id() {

			if(absint($this->group_id) === 0) { parent::db_throw_error(__('Invalid group ID', 'ws-form')); }
			return true;
		}

		// Check id
		public function db_check_id() {

			if(absint($this->id) === 0) { parent::db_throw_error(__('Invalid section ID', 'ws-form')); }
			return true;
		}

		// Get section label
		public function db_get_label() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			return parent::db_object_get_label($this->table_name, $this->id);
		}
	}