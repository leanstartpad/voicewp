<?php
/**
 * This file contains logice related to creating the custom submenu settings page.
 *
 * @package VoiceWP
 */

namespace VoiceWP;

/**
 * Settings class use to create a new settings page.
 */
class Settings {
	/**
	 * The setting type.
	 *
	 * @var string
	 */
	private $_type = '';

	/**
	 * The setting name.
	 *
	 * @var string
	 */
	private $_name = '';

	/**
	 * The setting title.
	 *
	 * @var string
	 */
	private $_title = '';

	/**
	 * The setting fields.
	 *
	 * @var array
	 */
	private $_fields = [];

	/**
	 * The setting args.
	 *
	 * @var string
	 */
	private $_args = '';

	/**
	 * Cached data if the field value.
	 *
	 * @var mixed
	 */
	private $_retrieved_data = null;

	/**
	 * Setup the class.
	 *
	 * @param string $type   The settings type.
	 * @param string $name   The settings name.
	 * @param string $title  The settings title.
	 * @param array  $fields The settings fields.
	 * @param array  $args   The settings args.
	 */
	public function __construct( $type, $name, $title, $fields, $args = [] ) {
		$this->_type   = $type;
		$this->_name   = $name;
		$this->_title  = $title;
		$this->_fields = $fields;
		$this->_args   = $args;

		// Prime the cache.
		$this->get_data();

		if ( 'options' === $this->_type ) {
			add_action( 'admin_menu', [ $this, 'add_options_page' ] );
			add_action( 'admin_init', [ $this, 'add_options_fields' ] );
		}
	}

	/**
	 * Add the settings page.
	 */
	public function add_options_page() {
		// No parent page.
		if ( empty( $this->_args['parent_page'] ) ) {
			return;
		}

		add_submenu_page(
			$this->_args['parent_page'],
			$this->_title,
			$this->_title,
			'manage_options',
			$this->_name,
			function () {
				?>
				<div class="wrap">
					<h2><?php echo esc_html( $this->_title ); ?></h2>

					<form method="POST" action="options.php">
						<?php settings_fields( $this->get_options_group_name() ); ?>
						<?php do_settings_sections( $this->_name ); ?>
						<?php submit_button(); ?>
					</form>
				</div>
				<?php
			}
		);
	}

	/**
	 * Add the settings to the page.
	 */
	public function add_options_fields() {
		if ( empty( $this->_fields ) ) {
			return;
		}

		register_setting( $this->get_options_group_name(), $this->_name );

		add_settings_section(
			$this->get_options_section_name(),
			'',
			'',
			$this->_name
		);

		foreach ( (array) $this->_fields as $name => $setting ) {
			add_settings_field(
				$name,
				$setting['label'],
				function () use ( $name ) {
					$this->render_field( $name );
				},
				$this->_name,
				$this->get_options_section_name()
			);
		}
	}

	/**
	 * Renders the field.
	 *
	 * @param string $field_name The field name to be rendered.
	 */
	public function render_field( $field_name ) {
		$field = $this->get_field( $field_name );

		if ( empty( $field ) ) {
			return;
		}

		// Render the correct field type.
		switch ( $field['type'] ) {
			case 'text':
			default:
				printf(
					'<input type="text" name="%1$s" id="%1$s" value="%2$s" />%3$s',
					esc_attr( $this->_name . '[' . $field_name . ']' ),
					esc_attr( $this->get_field_value( $field_name ) ),
					! empty( $field['description'] ) ? '<p class="description">' . esc_html( $field['description'] ) . '</p>' : ''
				);
				break;
		}
	}

	/**
	 * Get all fields.
	 *
	 * @return array The field array.
	 */
	public function get_fields() {
		return $this->_fields;
	}

	/**
	 * Get a field by name.
	 *
	 * @param string $field_name The field name.
	 * @return array The field array.
	 */
	public function get_field( $field_name ) {
		return wp_parse_args( $this->get_fields()[ $field_name ], [
			'type' => 'text',
		] ) ?? null;
	}

	/**
	 * Get the entire field data.
	 *
	 * @return mixed The field data.
	 */
	public function get_data() {
		if ( null === $this->_retrieved_data ) {
			switch ( $this->_type ) {
				case 'options':
					$this->_retrieved_data = get_option( $this->_name );
					break;
			}
		}
	}

	/**
	 * Get the field value by name.
	 *
	 * @param string $field_name The field name.
	 * @return mixed The field value.
	 */
	public function get_field_value( $field_name ) {
		return $this->_retrieved_data[ $field_name ] ?? null;
	}

	/**
	 * Get the field group name used when registering the settings.
	 *
	 * @return string The field group name.
	 */
	public function get_options_group_name() {
		return $this->_name . '-group';
	}

	/**
	 * Get the field section name used when registering the settings.
	 *
	 * @return string The field section name.
	 */
	public function get_options_section_name() {
		return $this->_name . '-section';
	}
}
