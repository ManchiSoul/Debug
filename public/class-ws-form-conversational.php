<?php

	/**
	 * Form conversational
	 */
	class WS_Form_Conversational {

		protected $form_id = 0;
		protected $form_obj = false;
		protected $ws_form_form;
		protected $preview = false;

		public function __construct() {

			// Handle meta updates
			add_filter('wsf_meta_update', array($this, 'wsf_meta_update'), 10, 5);

			// Initialize rewrite rules
			self::rewrites();

			// Check query for form ID
			add_action('parse_query', array($this, 'parse_query'));
		}

		public function parse_query() {

			// Get form_id (Have to use $_GET here because of the way customizer does a POST request with query vars!)
			$this->form_id = absint(get_query_var('wsf_conversational_form_id'));
			if($this->form_id === 0) {

				$this->form_id = ((isset($_GET) && isset($_GET['wsf_preview_conversational_form_id'])) ? absint($_GET['wsf_preview_conversational_form_id']) : 0);		// phpcs:ignore WordPress.Security.NonceVerification
				if($this->form_id === 0) { return false; }

				$this->preview = true;
			}

			if(WS_Form_Common::customizer_enabled()) {

				// Skin filter
				add_filter('wsf_css_skin_id', function($skin_id) { return 'ws_form_conv'; }, 99999, 1);
			}

			// Load form
			$this->ws_form_form = New WS_Form_Form();
			$this->ws_form_form->id = $this->form_id;

			try {

				if($this->preview) {

					$this->form_obj = $this->ws_form_form->db_read(true);

				} else {

					$this->form_obj = $this->ws_form_form->db_read_published();
				}

			} catch(Exception $e) {}

			// Clear filters (Prevents bugs in other plugins affecting our output)
			remove_all_filters('the_content');
			remove_all_filters('get_the_excerpt');

			// Set up fake post
			add_action('template_redirect', array($this, 'template_redirect'));

			// Determine which template to use (Use priority 1000 to override other template_include overrides, e.g. Oxygen visual builder)
			add_filter('template_include', array($this, 'template_include'), 1000);

			// Empty post thumbnail
			add_filter('post_thumbnail_html', function() { return ''; });

			// Oxygen support
			if(defined('CT_VERSION')) {

				global $ct_replace_render_template;
				$ct_replace_render_template = self::template_include();
			}

			// Dequeue CSS scripts
			add_action('wp_print_styles', array( $this, 'dequeue_styles'));
		}

		public function wsf_meta_update($value, $key, $object, $parent_id, $meta_data_array) {

			// Conversational permalink
			if($key == 'conversational_slug') {

				// Check if conversational enabled
				$conversational = isset($meta_data_array['conversational']) && ($meta_data_array['conversational'] == 'on');

				// Get existing conversational permalinks
				$conversational_permalinks = WS_Form_Common::option_get('conversational_permalinks');
				if(!is_array($conversational_permalinks)) { $conversational_permalinks = array(); }

				$conversational_permalinks_changed = false;

				if($conversational) {

					// Get conversational slug
					$conversational_slug = self::get_slug($value, $parent_id);

					// Add
					if(
						!isset($conversational_permalinks[$parent_id]) ||
						($conversational_permalinks[$parent_id] != $conversational_slug)
					) {

						$conversational_permalinks[$parent_id] = $conversational_slug;

						$conversational_permalinks_changed = true;
					}

				} else {

					// Delete
					if(isset($conversational_permalinks[$parent_id])) {

						unset($conversational_permalinks[$parent_id]);

						$conversational_permalinks_changed = true;
					}
				}

				if($conversational_permalinks_changed) {

					// Set conversational permalinks
					WS_Form_Common::option_set('conversational_permalinks', $conversational_permalinks);

					// Flush permalinks
					WS_Form_Common::option_set('conversational_permalinks_flush', true);
				}
			}

			return $value;
		}

		public function get_slug($slug, $form_id) {

			// Get conversational slug
			$conversational_slug = sanitize_file_name($slug);

			// Fallback
			if($conversational_slug == '') { $conversational_slug = 'wsf-conversational-form-#form_id'; }

			// Parse conversational slug
			$conversational_slug_lookups = array(

				'form_id' => $form_id
			);

			return WS_Form_Common::mask_parse($conversational_slug, $conversational_slug_lookups);
		}

		public function rewrites() {

			// Add rewrites
			$conversational_permalinks = WS_Form_Common::option_get('conversational_permalinks');
			$conversational_permalinks = apply_filters('wsf_conversational_permalinks', $conversational_permalinks);

			// Flush rewrite rules?
			$conversational_permalinks_flush = WS_Form_Common::option_get('conversational_permalinks_flush');
			$conversational_permalinks_flush = apply_filters('wsf_conversational_permalinks_flush', $conversational_permalinks_flush);

			if(
				is_array($conversational_permalinks) &&
				(count($conversational_permalinks) > 0)
			) {

				// Query variable for rewrites
				add_filter('query_vars', array($this, 'rewrite_query_var'));

				// Add rewrite rules
				foreach($conversational_permalinks as $form_id => $conversational_slug) {

					// Get form ID
					$form_id = absint($form_id);
					if($form_id === 0) { continue; }

					// Get permalink
					$regex = sprintf('^%s/?$', $conversational_slug);

					// Get target
					$query = sprintf('index.php?wsf_conversational_form_id=%u', $form_id);

					add_rewrite_rule($regex, $query, 'top');
				}
			}

			if($conversational_permalinks_flush) {

				flush_rewrite_rules(false);

				WS_Form_Common::option_set('conversational_permalinks_flush', false);
			}
		}

		public function rewrite_query_var($vars) {

			$vars[] = 'wsf_conversational_form_id';
			return $vars;
		}

		public function template_redirect() {

			global $wp, $wp_query;

			// Set post ID
			$post_id = -1;

			// Get slug
			$slug = self::get_slug(

				WS_Form_Common::get_object_meta_value($this->form_obj, 'conversational_slug'),
				$this->form_id
			);

			// Post constructor
			$post = new stdClass();
			$post->ID = $post_id;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt = current_time('mysql', true);
			$post->post_title = $this->preview ? __(sprintf('%s - Preview', $this->ws_form_form->label), 'ws-form') : $this->ws_form_form->label;
			$post->post_content = do_shortcode(sprintf('[%s id="%u" conversational="true"%s]', WS_FORM_SHORTCODE, $this->form_id, $this->preview ? ' published="false" preview="true"' : ''));
			$post->post_status = 'publish';
			$post->comment_status = 'closed';
			$post->ping_status = 'closed';
			$post->post_name = $slug;
			$post->post_type = 'page';
			$post->filter = 'raw';

			// Create fake post
			$wp_post = new WP_Post($post);

			// Add post to cache
			wp_cache_add($post_id, $wp_post, 'posts');

			// Update the main query
			$wp_query->post = $wp_post;
			$wp_query->posts = array( $wp_post );
			$wp_query->posts_per_page = 1;
			$wp_query->queried_object = $wp_post;
			$wp_query->queried_object_id = $post_id;
			$wp_query->found_posts = 1;
			$wp_query->post_count = 1;
			$wp_query->max_num_pages = 1; 
			$wp_query->is_page = true;
			$wp_query->is_singular = true; 
			$wp_query->is_single = false; 
			$wp_query->is_attachment = false;
			$wp_query->is_archive = false; 
			$wp_query->is_category = false;
			$wp_query->is_tag = false; 
			$wp_query->is_tax = false;
			$wp_query->is_author = false;
			$wp_query->is_date = false;
			$wp_query->is_year = false;
			$wp_query->is_month = false;
			$wp_query->is_day = false;
			$wp_query->is_time = false;
			$wp_query->is_search = false;
			$wp_query->is_feed = false;
			$wp_query->is_comment_feed = false;
			$wp_query->is_trackback = false;
			$wp_query->is_home = false;
			$wp_query->is_embed = false;
			$wp_query->is_404 = false; 
			$wp_query->is_paged = false;
			$wp_query->is_admin = false; 
			$wp_query->is_preview = false; 
			$wp_query->is_robots = false; 
			$wp_query->is_posts_page = false;
			$wp_query->is_post_type_archive = false;

			// Update globals
			$GLOBALS['wp_query'] = $wp_query;
			$wp->register_globals();
		}

		public function template_include() {

			return WS_FORM_PLUGIN_DIR_PATH . 'public/conversational.php';
		}

		public function dequeue_styles() {

			// Dequeue CSS scripts
			$styles = wp_styles();

			// Check if styles queue is empty
			if(empty($styles->queue)) { return; }

			// URL array exclude
			$base_url = wp_get_upload_dir()['baseurl'];
			$url_array_exclude = array();
			$url_array_exclude[] = isset( $base_url ) ? wp_make_link_relative($base_url) : $theme_uri;
			$url_array_exclude[] = wp_make_link_relative(get_stylesheet_directory_uri());
			$url_array_exclude[] = wp_make_link_relative(get_template_directory_uri());

			// URL array include
			$url_array_include = array();
			$url_array_include[] = 'ws-form/css/public';
			$url_array_include[] = 'public/css/ws-form';

			// Apply filter
			$url_array_exclude = apply_filters('wsf_conversational_dequeue_styles', $url_array_exclude);

			foreach($styles->queue as $handle) {

				// Get relative src path of regisered script
				if (!isset($styles->registered[$handle]->src)) { continue; }
				$style_src = wp_make_link_relative($styles->registered[$handle]->src);

				// Run through included URLs
				foreach ($url_array_include as $url) {

					if(strpos($style_src, $url) !== false) {

						continue 2;
					}
				}

				// Run through excluded URLs
				foreach ($url_array_exclude as $url) {

					if(strpos($style_src, $url) !== false) {

						wp_dequeue_style($handle);
						break;
					}
				}
			}

			// Allow user to enqueue their own styles
			do_action('wsf_conversational_enqueue_styles');
		}
	}
