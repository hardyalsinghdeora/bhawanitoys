<?php
/**
 * Widgit Simple Settings Class
 *
 * @package     Widgit\SimpleSettings
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Widgit Simple Settings handler class
 *
 * @access      public
 * @since       1.0.0
 */
class Simple_Settings {


	/**
	 * The settings class version
	 *
	 * @var         string $version The settings class version
	 * @access      private
	 * @since       1.0.0
	 */
	private $version = '1.2.1';


	/**
	 * The plugin slug
	 *
	 * @var         string $slug The plugin slug
	 * @access      private
	 * @since       1.0.0
	 */
	private $slug;


	/**
	 * The plugin slug for names
	 *
	 * @var         string $func The plugin slug for names
	 * @access      private
	 * @since       1.0.0
	 */
	private $func;


	/**
	 * The default tab to display
	 *
	 * @var         string $default_tab The default tab to display
	 * @access      private
	 * @since       1.0.0
	 */
	private $default_tab;


	/**
	 * Whether or not to display the page title
	 *
	 * @var         bool $show_title Whether or not to display the page title
	 * @access      private
	 * @since       1.0.0
	 */
	private $show_title;


	/**
	 * The page title
	 *
	 * @var         bool page_title The page title
	 * @access      private
	 * @since       1.0.0
	 */
	private $page_title;


	/**
	 * The sysinfo object
	 *
	 * @var         object $sysinfo The sysinfo object
	 * @access      private
	 * @since       1.0.0
	 */
	private $sysinfo;


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $slug The plugin slug.
	 * @param       string $default_tab The default settings tab to display.
	 * @return      void
	 */
	public function __construct( $slug = false, $default_tab = 'general' ) {
		// Bail if no slug is specified.
		if ( ! $slug ) {
			return;
		}

		// Setup plugin variables.
		$this->slug        = $slug;
		$this->func        = str_replace( '-', '_', $slug );
		$this->default_tab = $default_tab;

		// Run action and filter hooks.
		$this->hooks();

		// Setup the Sysinfo class.
		if ( ! class_exists( 'Simple_Settings_Sysinfo' ) ) {
			require_once 'modules/sysinfo/class-simple-settings-sysinfo.php';
		}
		$this->sysinfo = new Simple_Settings_Sysinfo( $this->slug, $this->func, $this->version );
	}


	/**
	 * Run action and filter hooks
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 */
	private function hooks() {
		// Add the plugin setting page.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 10 );

		// Register the plugin settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( $this->func . '_settings_sanitize_text', array( $this, 'sanitize_text_field' ) );

		// Add styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );

		// Process actions.
		add_action( 'admin_init', array( $this, 'process_actions' ) );

		// Handle tooltips.
		add_action( $this->func . '_after_setting_output', array( $this, 'add_setting_tooltip' ), 10 );
	}


	/**
	 * Add settings pages
	 *
	 * @access      public
	 * @since       1.0.0
	 * @global      string ${this->func . '_settings_page'} The settings page slug
	 * @return      void
	 */
	public function add_settings_page() {
		global ${$this->func . '_settings_page'};

		$menu = apply_filters(
			$this->func . '_menu',
			array(
				'type'       => 'menu',
				'parent'     => 'options-general.php',
				'page_title' => __( 'Simple Settings', 'simple-settings' ),
				'show_title' => false,
				'menu_title' => __( 'Simple Settings', 'simple-settings' ),
				'capability' => 'manage_options',
				'icon'       => '',
				'position'   => null,
			)
		);

		$this->show_title = $menu['show_title'];
		$this->page_title = $menu['page_title'];

		if ( 'submenu' === $menu['type'] ) {
			${$this->func . '_settings_page'} = add_submenu_page( $menu['parent'], $menu['page_title'], $menu['menu_title'], $menu['capability'], $this->slug . '-settings', array( $this, 'render_settings_page' ) );
		} else {
			${$this->func . '_settings_page'} = add_menu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $this->slug . '-settings', array( $this, 'render_settings_page' ), $menu['icon'], $menu['position'] );
		}
	}


	/**
	 * Render settings page
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function render_settings_page() {
		if ( isset( $_REQUEST[ $this->func . '_settings_nonce' ] ) ) {
			check_admin_referer( $this->func . '_settings_nonce', $this->func . '_settings_nonce' );
		}

		$get                 = wp_unslash( $_GET );
		$active_tab          = isset( $get['tab'] ) && array_key_exists( $get['tab'], $this->get_settings_tabs() ) ? $get['tab'] : $this->default_tab;
		$registered_sections = $this->get_settings_tab_sections( $active_tab );
		$sections            = $registered_sections;
		$key                 = 'main';

		if ( is_array( $sections ) ) {
			$key = key( $sections );
		}

		$section = isset( $get['section'] ) && ! empty( $registered_sections ) && array_key_exists( $get['section'], $registered_sections ) ? $get['section'] : $key;
		?>
		<div class="wrap">
			<?php if ( $this->show_title ) { ?>
				<h2><?php echo esc_html( $this->page_title ); ?></h2>
			<?php } ?>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->get_settings_tabs() as $tab_id => $tab_name ) {
					$tab_url = add_query_arg(
						array(
							'settings-updated' => false,
							'tab'              => $tab_id,
						)
					);

					// Remove the section from the tabs so we always end up at the main section.
					$tab_url = remove_query_arg( 'section', $tab_url );

					$active = $active_tab === $tab_id ? ' nav-tab-active' : '';
					?>
					<a href="<?php echo esc_url( $tab_url ); ?>" title="<?php echo esc_attr( $tab_name ); ?>" class="nav-tab<?php echo esc_attr( $active ); ?>"><?php echo wp_kses_post( $tab_name ); ?></a>
					<?php
				}
				?>
			</h2>
			<?php
			$number_of_sections = count( (array) $sections );
			$number             = 0;

			if ( $number_of_sections > 1 ) {
				echo '<div><ul class="subsubsub">';

				foreach ( $sections as $section_id => $section_name ) {
					echo '<li>';

					$number++;
					$class   = '';
					$tab_url = add_query_arg(
						array(
							'settings-updated' => false,
							'tab'              => $active_tab,
							'section'          => $section_id,
						)
					);

					if ( $section === $section_id ) {
						$class = 'current';
					}

					echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a>';

					if ( $number !== $number_of_sections ) {
						echo ' | ';
					}

					echo '</li>';
				}

				echo '</ul></div>';
			}
			?>
			<div id="tab_container" class="simple-settings-options-table">
				<form method="post" action="options.php">
					<table class="form-table">
						<?php
						settings_fields( $this->func . '_settings' );

						do_action( $this->func . '_settings_tab_top_' . $active_tab . '_' . $section );

						do_settings_sections( $this->func . '_settings_' . $active_tab . '_' . $section );

						do_action( $this->func . '_settings_tab_bottom_' . $active_tab . '_' . $section );
						?>
					</table>
					<?php
					if ( ! in_array( $active_tab, apply_filters( $this->func . '_unsavable_tabs', array() ), true ) ) {
						wp_nonce_field( $this->func . '_settings_nonce', $this->func . '_settings_nonce' );
						submit_button();
					}
					?>
				</form>
			</div>
		</div>
		<?php
	}


	/**
	 * Retrieve the settings tabs
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      array $tabs The registered tabs for this plugin
	 */
	private function get_settings_tabs() {
		return apply_filters( $this->func . '_settings_tabs', array() );
	}


	/**
	 * Retrieve settings tab sections
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $tab The current tab.
	 * @return      array $section The section items
	 */
	public function get_settings_tab_sections( $tab = false ) {
		$tabs     = false;
		$sections = $this->get_registered_settings_sections();

		if ( $tab && ! empty( $sections[ $tab ] ) ) {
			$tabs = $sections[ $tab ];
		} elseif ( $tab ) {
			$tabs = false;
		}

		return $tabs;
	}


	/**
	 * Retrieve the plugin settings
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array $settings The plugin settings
	 */
	public function get_registered_settings() {
		return apply_filters( $this->func . '_registered_settings', array() );
	}


	/**
	 * Retrieve the plugin settings sections
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      array $sections The registered sections
	 */
	private function get_registered_settings_sections() {
		global ${$this->func . '_sections'};

		if ( ! empty( ${$this->func . '_sections'} ) ) {
			return ${$this->func . '_sections'};
		}

		${$this->func . '_sections'} = apply_filters( $this->func . '_registered_settings_sections', array() );

		return ${$this->func . '_sections'};
	}


	/**
	 * Retrieve an option
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key The key to retrieve.
	 * @param       mixed  $default The default value if key doesn't exist.
	 * @global      array ${$this->func . '_options'} The options array
	 * @return      mixed $value The value to return
	 */
	public function get_option( $key = '', $default = false ) {
		global ${$this->func . '_options'};

		$value = ! empty( ${$this->func . '_options'}[ $key ] ) ? ${$this->func . '_options'}[ $key ] : $default;
		$value = apply_filters( $this->func . '_get_option', $value, $key, $default );

		return apply_filters( $this->func . '_get_option_' . $key, $value, $key, $default );
	}


	/**
	 * Update an option
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key The key to update.
	 * @param       mixed  $value The value to set key to.
	 * @return      bool true if updated, false otherwise
	 */
	public function update_option( $key = '', $value = false ) {
		// Bail if no key is set.
		if ( empty( $key ) ) {
			return false;
		}

		if ( empty( $value ) ) {
			$remove_option = $this->delete_option( $key );
			return $remove_option;
		}

		// Fetch a clean copy of the options array.
		$options = get_option( $this->func . '_settings' );

		// Allow devs to modify the value.
		$value = apply_filters( $this->func . '_update_option', $value, $key );

		// Try to update the option.
		$options[ $key ] = $value;
		$did_update      = update_option( $this->func . '_settings', $options );

		// Update the global.
		if ( $did_update ) {
			global ${$this->func . '_options'};
			${$this->func . '_options'}[ $key ] = $value;
		}

		return $did_update;
	}


	/**
	 * Delete an option
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key The key to delete.
	 * @return      bool true if deleted, false otherwise
	 */
	public function delete_option( $key = '' ) {
		// Bail if no key is set.
		if ( empty( $key ) ) {
			return false;
		}

		// Fetch a clean copy of the options array.
		$options = get_option( $this->func . '_settings' );

		// Try to unset the option.
		if ( isset( $options[ $key ] ) ) {
			unset( $options[ $key ] );
		}

		$did_update = update_option( $this->func . '_settings', $options );

		// Update the global.
		if ( $did_update ) {
			global ${$this->func . '_options'};
			${$this->func . '_options'} = $options;
		}

		return $did_update;
	}


	/**
	 * Retrieve all options
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array $settings The options array
	 */
	public function get_settings() {
		$settings = get_option( $this->func . '_settings' );

		if ( empty( $settings ) ) {
			$settings = array();

			update_option( $this->func . '_settings', $settings );
		}

		return apply_filters( $this->func . '_get_settings', $settings );
	}


	/**
	 * Add settings sections and fields
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function register_settings() {
		if ( get_option( $this->func . '_settings' ) === false ) {
			add_option( $this->func . '_settings' );
		}

		foreach ( $this->get_registered_settings() as $tab => $sections ) {
			foreach ( $sections as $section => $settings ) {
				// Check for backwards compatibility.
				$section_tabs = $this->get_settings_tab_sections( $tab );

				if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
					$section  = 'main';
					$settings = $sections;
				}

				add_settings_section(
					$this->func . '_settings_' . $tab . '_' . $section,
					__return_null(),
					'__return_false',
					$this->func . '_settings_' . $tab . '_' . $section
				);

				foreach ( $settings as $option ) {
					// For backwards compatibility.
					if ( empty( $option['id'] ) ) {
						continue;
					}

					$name = isset( $option['name'] ) ? $option['name'] : '';

					add_settings_field(
						$this->func . '_settings[' . $option['id'] . ']',
						$name,
						function_exists( $this->func . '_' . $option['type'] . '_callback' ) ? $this->func . '_' . $option['type'] . '_callback' : ( method_exists( $this, $option['type'] . '_callback' ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' ) ),
						$this->func . '_settings_' . $tab . '_' . $section,
						$this->func . '_settings_' . $tab . '_' . $section,
						apply_filters(
							$this->func . '_settings_allowed_args',
							array(
								'section'       => $section,
								'id'            => isset( $option['id'] ) ? $option['id'] : null,
								'desc'          => ! empty( $option['desc'] ) ? $option['desc'] : '',
								'name'          => isset( $option['name'] ) ? $option['name'] : null,
								'size'          => isset( $option['size'] ) ? $option['size'] : null,
								'options'       => isset( $option['options'] ) ? $option['options'] : '',
								'std'           => isset( $option['std'] ) ? $option['std'] : '',
								'min'           => isset( $option['min'] ) ? $option['min'] : null,
								'max'           => isset( $option['max'] ) ? $option['max'] : null,
								'step'          => isset( $option['step'] ) ? $option['step'] : null,
								'select2'       => isset( $option['select2'] ) ? $option['select2'] : null,
								'placeholder'   => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
								'multiple'      => isset( $option['multiple'] ) ? $option['multiple'] : null,
								'allow_blank'   => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
								'readonly'      => isset( $option['readonly'] ) ? $option['readonly'] : false,
								'disabled'      => isset( $option['disabled'] ) ? $option['disabled'] : false,
								'buttons'       => isset( $option['buttons'] ) ? $option['buttons'] : null,
								'wpautop'       => isset( $option['wpautop'] ) ? $option['wpautop'] : null,
								'teeny'         => isset( $option['teeny'] ) ? $option['teeny'] : null,
								'tab'           => isset( $option['tab'] ) ? $option['tab'] : null,
								'tooltip_title' => isset( $option['tooltip_title'] ) ? $option['tooltip_title'] : false,
								'tooltip_desc'  => isset( $option['tooltip_desc'] ) ? $option['tooltip_desc'] : false,
								'field_class'   => isset( $option['field_class'] ) ? $option['field_class'] : '',
							),
							$option
						)
					);
				}
			}
		}

		register_setting( $this->func . '_settings', $this->func . '_settings', array( $this, 'settings_sanitize' ) );
	}


	/**
	 * Settings sanitization
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $input The value entered in the field.
	 * @global      array ${$this->func . '_options'} The options array
	 * @return      string $input The sanitized value
	 */
	public function settings_sanitize( $input = array() ) {
		global ${$this->func . '_options'};

		if ( isset( $_REQUEST[ $this->func . '_settings_nonce' ] ) ) {
			check_admin_referer( $this->func . '_settings_nonce', $this->func . '_settings_nonce' );
		}

		$doing_section = false;
		$post          = wp_unslash( $_POST );

		if ( ! empty( $post['_wp_http_referer'] ) ) {
			$doing_section = true;
		}

		$setting_types = $this->get_registered_settings_types();
		$input         = $input ? $input : array();

		if ( $doing_section ) {
			parse_str( $post['_wp_http_referer'], $referrer );

			$tab     = isset( $referrer['tab'] ) ? $referrer['tab'] : $this->default_tab;
			$section = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

			if ( ! empty( $post[ $this->func . '_section_override' ] ) ) {
				$section = sanitize_text_field( $post[ $this->func . '_section_override' ] );
			}

			$setting_types = $this->get_registered_settings_types( $tab, $section );

			$input = apply_filters( $this->func . '_settings_' . $tab . '_sanitize', $input );
			$input = apply_filters( $this->func . '_settings_' . $tab . '_' . $section . '_sanitize', $input );
		}

		$output = array_merge( ${$this->func . '_options'}, $input );

		foreach ( $setting_types as $key => $type ) {
			if ( empty( $type ) ) {
				continue;
			}

			// Bypass non-setting settings.
			$non_setting_types = apply_filters(
				$this->func . '_non_setting_types',
				array(
					'header',
					'descriptive_text',
					'hook',
				)
			);

			if ( in_array( $type, $non_setting_types, true ) ) {
				continue;
			}

			if ( array_key_exists( $key, $output ) ) {
				$output[ $key ] = apply_filters( $this->func . '_settings_sanitize_' . $type, $output[ $key ], $key );
				$output[ $key ] = apply_filters( $this->func . '_settings_sanitize', $output[ $key ], $key );
			}

			if ( $doing_section ) {
				switch ( $type ) {
					case 'checkbox':
					case 'multicheck':
						if ( array_key_exists( $key, $input ) && '-1' === $output[ $key ] ) {
							unset( $output[ $key ] );
						}
						break;
					case 'text':
						if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
							unset( $output[ $key ] );
						}
						break;
					default:
						if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
							unset( $output[ $key ] );
						}
						break;
				}
			} else {
				if ( empty( $input[ $key ] ) ) {
					unset( $output[ $key ] );
				}
			}
		}

		if ( $doing_section ) {
			add_settings_error( $this->slug . '-notices', '', __( 'Settings updated.', 'simple-settings' ), 'updated' );
		}

		return $output;
	}


	/**
	 * Flattens the set of registered settings and their type so we can easily sanitize all settings
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       mixed $filter_tab bool|string A tab to filter by.
	 * @param       mixed $filter_section bool|string A section to filter by.
	 * @return      array Key is the setting ID, value is the type of setting it is registered as
	 */
	public function get_registered_settings_types( $filter_tab = false, $filter_section = false ) {
		$settings      = $this->get_registered_settings();
		$setting_types = array();

		foreach ( $settings as $tab_id => $tab ) {
			if ( false !== $filter_tab && $filter_tab !== $tab_id ) {
				continue;
			}

			foreach ( $tab as $section_id => $section_or_setting ) {
				// See if we have a setting registered at the tab level for backwards compatibility.
				if ( false !== $filter_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
					$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
					continue;
				}

				if ( false !== $filter_section && $filter_section !== $section_id ) {
					continue;
				}

				foreach ( $section_or_setting as $section => $section_settings ) {
					$setting_types[ $section_settings['id'] ] = $section_settings['type'];
				}
			}
		}

		return $setting_types;
	}


	/**
	 * Sanitize text fields
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $input The value entered in the field.
	 * @return      string $input The sanitized value
	 */
	public function sanitize_text_field( $input ) {
		$tags = array(
			'p'      => array(
				'class' => array(),
				'id'    => array(),
			),
			'span'   => array(
				'class' => array(),
				'id'    => array(),
			),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'title'  => array(),
				'class'  => array(),
				'id'     => array(),
			),
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
			'img'    => array(
				'src'   => array(),
				'title' => array(),
				'alt'   => array(),
				'class' => array(),
				'id'    => array(),
			),
			'div'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'ul'     => array(
				'class' => array(),
				'id'    => array(),
			),
			'ol'     => array(
				'class' => array(),
				'id'    => array(),
			),
			'li'     => array(
				'class' => array(),
				'id'    => array(),
			),
		);

		$allowed_tags = apply_filters( $this->func . '_allowed_html_tags', $tags );

		return trim( wp_kses( $input, $allowed_tags ) );
	}


	/**
	 * Sanitize HTML Class Names
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string|array $class HTML Class Name(s).
	 * @return      string $class
	 */
	public function sanitize_html_class( $class = '' ) {
		if ( is_string( $class ) ) {
			$class = sanitize_html_class( $class );
		} elseif ( is_array( $class ) ) {
			$class = array_values( array_map( 'sanitize_html_class', $class ) );
			$class = implode( ' ', array_unique( $class ) );
		}

		return $class;
	}


	/**
	 * Sanitizes a string key
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes,
	 * underscores, stops, colons and slashes are allowed
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key String key.
	 * @return      string Sanitized key
	 */
	public function sanitize_key( $key ) {
		$raw_key = $key;
		$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

		return apply_filters( $this->func . '_sanitize_key', $key, $raw_key );
	}


	/**
	 * Header callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @return      void
	 */
	public function header_callback( $args ) {
		do_action( $this->func . '_after_setting_output', '', $args );
	}


	/**
	 * Checkbox callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The plugin options
	 * @return      void
	 */
	public function checkbox_callback( $args ) {
		global ${$this->func . '_options'};

		$name    = ' name="' . $this->func . '_settings[' . $this->sanitize_key( $args['id'] ) . ']"';
		$class   = $this->sanitize_html_class( $args['field_class'] );
		$checked = isset( ${$this->func . '_options'}[ $args['id'] ] ) ? checked( 1, ${$this->func . '_options'}[ $args['id'] ], false ) : '';
		?>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="-1" />
		<input type="checkbox" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $checked ); ?> class="<?php echo esc_attr( $class ); ?>"/>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Color callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the settings.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function color_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$default = isset( $args['std'] ) ? $args['std'] : '';
		$class   = $this->sanitize_html_class( $args['field_class'] );
		?>
		<input type="text" class="simple-settings-color-picker <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>" />&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Descriptive text callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @return      void
	 */
	public function descriptive_text_callback( $args ) {
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Editor callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function editor_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];

			if ( empty( $args['allow_blank'] ) && empty( $value ) ) {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$rows    = isset( $args['size'] ) ? $args['size'] : '20';
		$wpautop = isset( $args['wpautop'] ) ? $args['wpautop'] : true;
		$buttons = isset( $args['buttons'] ) ? $args['buttons'] : true;
		$teeny   = isset( $args['teeny'] ) ? $args['teeny'] : false;
		$class   = $this->sanitize_html_class( $args['field_class'] );

		wp_editor(
			stripslashes( $value ),
			$this->func . '_settings_' . esc_attr( $args['id'] ),
			array(
				'wpautop'       => $wpautop,
				'media_buttons' => $buttons,
				'textarea_name' => $this->func . '_settings[' . esc_attr( $args['id'] ) . ']',
				'textarea_rows' => absint( $rows ),
				'teeny'         => $teeny,
				'editor_class'  => $class,
			)
		);
		?>
		<br /><label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * HTML callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function html_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
		?>
		<textarea class="large-text simple-settings-html" cols="50" rows="5" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo esc_textarea( stripslashes( $value ) ); ?></textarea>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>';
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Multicheck callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function multicheck_callback( $args ) {
		global ${$this->func . '_options'};

		$class = $this->sanitize_html_class( $args['field_class'] );

		if ( ! empty( $args['options'] ) ) {
			?>
			<input type="hidden" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" value="-1" />
			<?php
			foreach ( $args['options'] as $key => $option ) {
				if ( isset( ${$this->func . '_options'}[ $args['id'] ][ $key ] ) ) {
					$enabled = $option;
				} else {
					$enabled = isset( $args['std'][ $key ] ) ? $args['std'][ $key ] : null;
				}
				?>
				<input name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>][<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>]" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>][<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>]" class="<?php echo esc_attr( $class ); ?>" type="checkbox" value="<?php echo esc_attr( $option ); ?>" <?php checked( $option, $enabled ); ?> />&nbsp;
				<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>][<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>]"><?php echo wp_kses_post( $option ); ?></label><br />
				<?php
			}
			?>
			<p class="description" style="display: inline-block"><?php echo wp_kses_post( $args['desc'] ); ?></p>
			<?php
			do_action( $this->func . '_after_setting_output', $args );
		}
	}


	/**
	 * Number callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function number_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$name     = ' name="' . $this->func . '_settings[' . $this->sanitize_key( $args['id'] ) . ']"';
		$max      = isset( $args['max'] ) ? $args['max'] : 999999;
		$min      = isset( $args['min'] ) ? $args['min'] : 0;
		$step     = isset( $args['step'] ) ? $args['step'] : 1;
		$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$readonly = true === $args['readonly'] ? ' readonly="readonly"' : '';
		$class    = $this->sanitize_html_class( $args['field_class'] );
		?>
		<input type="number" step="<?php echo esc_attr( $step ); ?>" max="<?php echo esc_attr( $max ); ?>" min="<?php echo esc_attr( $min ); ?>" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $this->sanitize_html_class( $size ) ); ?>-text" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo esc_attr( $readonly ); ?>/>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Password callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the settings.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function password_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$class = $this->sanitize_html_class( $args['field_class'] );
		?>
		<input type="password" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $this->sanitize_html_class( $size ) ); ?>-text" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" value="<?php echo esc_attr( $value ); ?>" />&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Radio callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function radio_callback( $args ) {
		global ${$this->func . '_options'};

		if ( ! empty( $args['options'] ) ) {
			$class = $this->sanitize_html_class( $args['field_class'] );

			$html = '';

			foreach ( $args['options'] as $key => $option ) {
				$checked = false;

				if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) && ${$this->func . '_options'}[ $args['id'] ] === $key ) {
					$checked = true;
				} elseif ( isset( $args['std'] ) && $args['std'] === $key && ! isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
					$checked = true;
				}
				?>
				<input name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>][<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>]" type="radio" class="<?php echo esc_attr( $class ); ?>" value="<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>" <?php echo esc_attr( checked( true, $checked, false ) ); ?>/>&nbsp;
				<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>][<?php echo esc_attr( $this->sanitize_key( $key ) ); ?>]"><?php echo wp_kses_post( $option ); ?></label><br />
				<?php
			}
			?>
			<p class="description"><?php do_action( $this->func . '_after_setting_output', $args ); ?></p>
			<?php
		}
	}


	/**
	 * Select callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function select_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		$select2     = isset( $args['select2'] ) ? 'simple-settings-select2' : '';
		$width       = isset( $args['size'] ) ? ' style="width: ' . $args['size'] . '"' : '';
		$class       = $this->sanitize_html_class( $args['field_class'] );

		$nonce = isset( $args['data']['nonce'] ) ? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '"' : '';

		// If the field allows multiples, save as an array.
		$name_attr = $this->func . '_settings[' . $this->sanitize_key( $args['id'] ) . ']';
		$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;
		?>
		<select <?php echo esc_attr( $nonce ); ?> id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $name_attr ); ?>" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $select2 ); ?>" data-placeholder="<?php echo esc_html( $placeholder ); ?>"<?php echo esc_attr( $width ) . ( ( $args['multiple'] ) ? ' multiple="true"' : '' ); ?> />
		<?php
		foreach ( $args['options'] as $option => $name ) {
			if ( ! $args['multiple'] ) {
				$selected = selected( $option, $value, false );
				?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $name ); ?></option>
				<?php
			} else {
				?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php echo ( ( in_array( $option, $value, true ) ) ? 'selected="true"' : '' ); ?>><?php echo esc_html( $name ); ?></option>
				<?php
			}
		}
		?>
		</select>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Sysinfo callback
	 *
	 * @since       1.1.0
	 * @param       array $args Arguments passed by the settings.
	 * @return      void
	 */
	public function sysinfo_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( $_REQUEST[ $this->func . '_settings_nonce' ] ) ) {
			check_admin_referer( $this->func . '_settings_nonce', $this->func . '_settings_nonce' );
		}

		if ( ! isset( ${$this->func . '_options'}[ $args['tab'] ] ) || ( isset( ${$this->func . '_options'}[ $args['tab'] ] ) && isset( $_GET['tab'] ) && ${$this->func . '_options'}[ $args['tab'] ] === $_GET['tab'] ) ) {
			?>
			<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="<?php echo esc_attr( $this->func ); ?>-system-info" title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'simple-settings' ); ?>"><?php echo esc_attr( $this->sysinfo->get_system_info() ); ?></textarea>
			<p class="submit">
			<input type="hidden" name="<?php echo esc_attr( $this->slug ); ?>-settings-action" value="download_system_info" />
			<a class="button button-primary" href="<?php echo esc_url( add_query_arg( $this->slug . '-settings-action', 'download_system_info' ) ); ?>"><?php esc_attr_e( 'Download System Info File', 'simple-settings' ); ?></a>
			</p>
			<?php
			wp_nonce_field( esc_attr( $this->func ) . 'system-info-nonce', esc_attr( $this->func ) . 'system-info-nonce' );
			do_action( $this->func . '_after_setting_output', $args );
		}
	}


	/**
	 * Text callback
	 *
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function text_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$name        = ' name="' . $this->func . '_settings[' . $this->sanitize_key( $args['id'] ) . ']"';
		$readonly    = true === $args['readonly'] ? ' readonly="readonly"' : '';
		$size        = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$class       = $this->sanitize_html_class( $args['field_class'] );
		$disabled    = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		?>
		<input type="text" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $this->sanitize_html_class( $size ) ); ?>-text" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"<?php echo esc_attr( $name ); ?> placeholder="<?php echo esc_attr( stripslashes( $placeholder ) ); ?>" value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo esc_attr( $readonly ); ?> />&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Textarea callback
	 *
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function textarea_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$class = $this->sanitize_html_class( $args['field_class'] );
		?>
		<textarea class="<?php echo esc_attr( $class ); ?> large-text" cols="50" rows="5" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo esc_textarea( stripslashes( $value ) ); ?></textarea>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Upload callback
	 *
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function upload_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$class = $this->sanitize_html_class( $args['field_class'] );
		?>
		<input type="text" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $this->sanitize_html_class( $size ) ); ?>-text" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" value="<?php echo esc_attr( stripslashes( $value ) ); ?>" />&nbsp;
		<span>&nbsp;<input type="button" class="<?php echo esc_attr( $this->func ); ?>_settings_upload_button button-secondary" value="<?php esc_attr_e( 'Upload File', 'simple-settings' ); ?>" /></span>&nbsp;
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"><?php echo wp_kses( $args['desc'] ); ?></label>
		<?php
		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * License field callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @global      array ${$this->func . '_options'} The Beacon options
	 * @return      void
	 */
	public function license_key_callback( $args ) {
		global ${$this->func . '_options'};

		if ( isset( ${$this->func . '_options'}[ $args['id'] ] ) ) {
			$value = ${$this->func . '_options'}[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$class = $this->sanitize_html_class( $args['field_class'] );
		?>
		<input type="text" class="<?php echo esc_attr( $class ) . ' ' . esc_attr( $this->sanitize_html_class( $size ) ); ?>-text" id="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" name="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]" value="<?php echo esc_attr( $value ); ?>" />&nbsp;
		<?php
		if ( get_option( $args['options']['is_valid_license_option'] ) ) {
			?>
			<input type="submit" class="button-secondary" name="<?php echo esc_attr( $args['id'] ); ?>_deactivate" value="<?php esc_attr_e( 'Deactivate License', 'simple-settings' ); ?>"/>
			<?php
		}
		?>
		<label for="<?php echo esc_attr( $this->func ); ?>_settings[<?php echo esc_attr( $this->sanitize_key( $args['id'] ) ); ?>]"> <?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php
		wp_nonce_field( $this->sanitize_key( $args['id'] ) . '-nonce', $this->sanitize_key( $args['id'] ) . '-nonce' );

		do_action( $this->func . '_after_setting_output', $args );
	}


	/**
	 * Hook callback
	 *
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @return      void
	 */
	public function hook_callback( $args ) {
		do_action( $this->func . '_' . $args['id'], $args );
	}


	/**
	 * Missing callback
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed by the setting.
	 * @return      string The text to display when a callback is missing
	 */
	public function missing_callback( $args ) {
		// Translators: The passed ID that has no corresponding callback.
		return sprintf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'simple-settings' ), $args['id'] );
	}


	/**
	 * Check if we should load admin scripts
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $hook The hook for the current page.
	 * @return      bool true if we should load scripts, false otherwise
	 */
	public function load_scripts( $hook ) {
		global $typenow, $pagenow, ${$this->func . '_settings_page'};

		$ret   = false;
		$pages = apply_filters( $this->func . '_admin_pages', array( ${$this->func . '_settings_page'} ) );

		if ( in_array( $hook, $pages, true ) ) {
			$ret = true;
		}

		return (bool) apply_filters( $this->func . 'load_scripts', $ret );
	}


	/**
	 * Processes all actions sent via POST and GET by looking for the '$func-settings-action'
	 * request and running do_action() to call the function
	 *
	 * @since       1.1.0
	 * @return      void
	 */
	public function process_actions() {
		if ( isset( $_REQUEST[ $this->sanitize_key( $args['id'] ) . '-nonce' ] ) ) {
			check_admin_referer( $this->sanitize_key( $args['id'] ) . '-nonce', $this->sanitize_key( $args['id'] ) . '-nonce' );
		}

		$post = wp_unslash( $_POST );

		if ( ! isset( $post['submit'] ) ) {
			if ( isset( $post[ $this->slug . '-settings-action' ] ) ) {
				do_action( $this->func . '_settings_' . $post[ $this->slug . '-settings-action' ], $post );
			}

			$get = wp_unslash( $_GET );

			if ( isset( $get[ $this->slug . '-settings-action' ] ) ) {
				do_action( $this->func . '_settings_' . $get[ $this->slug . '-settings-action' ], $get );
			}
		}
	}


	/**
	 * Enqueue scripts
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $hook The current page hook.
	 * @return      void
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! apply_filters( $this->func . '_load_admin_scripts', $this->load_scripts( $hook ), $hook ) ) {
			return;
		}

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$ui_style    = ( get_user_option( 'admin_color' ) === 'classic' ) ? 'classic' : 'fresh';
		$url_path    = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) );
		$select2_cdn = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/';
		$cm_cdn      = 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.46.0/';

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_media();
		wp_enqueue_style( 'jquery-ui-css', $url_path . '/assets/css/jquery-ui-' . $ui_style . '.min.css', array(), '1.0.0' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'select2', $select2_cdn . 'css/select2.min.css', array(), '4.0.7' );
		wp_enqueue_script( 'select2', $select2_cdn . 'js/select2.min.js', array( 'jquery' ), '4.0.7', true );

		wp_enqueue_style( $this->slug . '-cm', $cm_cdn . 'codemirror.css', array(), '5.46.0' );
		wp_enqueue_script( $this->slug . '-cm', $cm_cdn . 'codemirror.js', array( 'jquery' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-html', $cm_cdn . 'mode/htmlmixed/htmlmixed.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-xml', $cm_cdn . 'mode/xml/xml.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-js', $cm_cdn . 'mode/javascript/javascript.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-css', $cm_cdn . 'mode/css/css.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-php', $cm_cdn . 'mode/php/php.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );
		wp_enqueue_script( $this->slug . '-cm-clike', $cm_cdn . 'mode/clike/clike.js', array( 'jquery', $this->slug . '-cm' ), '5.46.0', true );

		wp_enqueue_style( $this->slug . '-simple-settings', $url_path . '/assets/css/admin' . $suffix . '.css', array(), $this->version );
		wp_enqueue_script( $this->slug . '-simple-settings', $url_path . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->slug . '-simple-settings',
			'simple_settings_vars',
			apply_filters(
				$this->func . 'localize_script',
				array(
					'func'               => $this->func,
					'image_media_button' => __( 'Insert Image', 'simple-settings' ),
					'image_media_title'  => __( 'Select Image', 'simple-settings' ),
				)
			)
		);
	}


	/**
	 * Add tooltips
	 *
	 * @access      public
	 * @since       1.2.0
	 * @param       array $args Arguments passed to the field.
	 * @return      void
	 */
	public function add_setting_tooltip( $args ) {
		if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
			?>
			<span alt="f223" class="simple-settings-help-tip dashicons dashicons-editor-help" title="<strong><?php echo esc_attr( $args['tooltip_title'] ); ?></strong>: <?php echo esc_attr( $args['tooltip_desc'] ); ?>"></span>
			<?php
		}
	}
}
