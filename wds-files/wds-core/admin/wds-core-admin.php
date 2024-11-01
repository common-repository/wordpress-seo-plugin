<?php
/**
 * WDS_Core_Admin class can be used by any component. It helps build admin pages
 *
 * @package WPMU DEV SEO Core
 */
class WDS_Core_Admin {

	var $slug = '';
	var $title = '';
	var $description = '';
	var $fields = array();
	var $parent_slug = '';
	var $capability = 'manage_options';
	var $options_name = '';
	var $options = '';
	var $contextual_help = '';
	var $additional = '';

	/**
	* PHP5 constructor
	*/
	function __construct( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' ) {
		global $wds_options;

		$this->slug = $name;
		$this->name = str_replace( 'wds_', '', $name );
		$this->title = $title;
		$this->description = $description;
		$this->fields = $fields;
		$this->parent_slug = $parent_slug;
		$this->contextual_help = $contextual_help;
		$this->additional = $additional;

		$this->options_name = $options_name = $name . '_options';

		if ( is_multisite() && WDS_SITEWIDE == true ) {
			$this->options = get_site_option( $options_name );
			$this->capability = 'manage_network_options';
		} else {
			$this->options = get_option( $options_name );
		}

		add_action( 'admin_init', array( &$this, 'init' ) );
		add_action( 'admin_menu', array( &$this, 'add_page' ) );
		add_filter( 'contextual_help', array( &$this, 'contextual_help' ), 10, 3 );
	}

	/**
	* PHP4 constructor
	*/
	function WDS_Core_Admin( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' ) {
		$this->__construct( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' );
	}

	/* Add text to settings form */
	function add_section_text( $args ) {
		$class = $args['callback'][0];
		$fields = $class->fields;
		$section_id = $args['id'];
		echo $fields[$section_id]['intro'];
	}

	/* Add checkbox fields to settings form */
	function add_checkbox_field( $args = array() ) {
		$defaults = array(
			'name' => '', 'title' => '', 'items' => array(),
			'class' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;

		echo "<fieldset><legend class='screen-reader-text'><span>$title</span></legend>";
		foreach( $items as $item => $label ) {
			$options[$item] = isset( $options[$item] ) ? $options[$item] : '';
			$checked = ( !empty( $options[$item] ) ) ? " checked='checked'" : '';
			echo "<label for='$options_name-$name-$item'>
				<input$checked id='$options_name-$name-$item' name='{$options_name}[{$item}]' type='checkbox' /> $label
			</label><br>";
		}
		echo "</fieldset>";
	}

	/* Add dropdown field to settings form */
	function add_dropdown_field( $args = array() ) {
		$defaults = array(
			'name' => '', 'title' => '', 'class' => 'postform',
			'items' => array()
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<select class='$class' id='$name' name='{$options_name}[{$name}]'>";
			foreach( $items as $item => $label ) {
				$selected = ( $options[$name] == $item ) ? " selected='selected'" : '';
				echo "<option value='$item'$selected class='level-0'>$label</option>";
			}
		echo "</select>";
	}

	/* Add textarea field to settings form */
	function add_textarea_field( $args = array() ) {
		$defaults = array(
			'name' => '', 'title' => '', 'description' => '',
			'class' => '', 'rows' => '2', 'cols' => '35'
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<fieldset>
			<legend class='screen-reader-text'><span>$title</span></legend>
			<textarea class='$class' id='$name' name='{$options_name}[{$name}]' rows='$rows' cols='$cols' type='textarea'>{$options[$name]}</textarea>
			<div class='description'>$description</div>
		</fieldset>";
	}

	/* Add text field to settings form */
	function add_text_field( $args = array() ) {
		$defaults = array(
			'name' => '', 'title' => '', 'description' => '',
			'size' => '', 'type' => 'text', 'class' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<input id='$name' name='{$options_name}[{$name}]' size='$size' type='$type' class='regular-text' value='{$options[$name]}' /><p class='description'>$description</p>";
	}

	/* Add radio fields to settings form */
	function add_radio_field( $args = array() ) {
		$defaults = array(
			'name' => '', 'title' => '', 'items' => array(),
			'description' => '', 'class' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<fieldset><legend class='screen-reader-text'><span>$title</span></legend>";
			if( !empty( $description ) ) echo "$description<br>";
				foreach( $items as $item => $label ) {
					$checked = ( $options[$name] == $item ) ? " checked='checked'" : '';
					echo "<label for='$item'><input$checked value='$item' name='{$options_name}[{$name}]' type='radio' /> $label</label><br>";
				}
		echo "</fieldset>";
	}

	/* Add text field to settings form */
	function add_content_field( $args = array() ) {
		$defaults = array(
			'text' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		echo $text;
	}

	/* Register our settings. Add the settings section, and settings fields */
	function init(){
		$options_name = $this->options_name;
		$slug = $this->slug;

		$this->register_setting( $options_name, $options_name, array( &$this, 'validate' ) );

		if ( $this->fields ) {
			foreach ( $this->fields as $section_slug => $section ) {
				add_settings_section( $section_slug, $section['title'], array( &$this, 'add_section_text' ), $slug );
				foreach ( $section['options'] as $option ) {
					add_settings_field( $option['name'], $option['title'], array( &$this, "add_{$option['type']}_field" ),  $slug, $section_slug, $option );
				}
			}
		}

		wp_register_style( 'wdsstyle', WDS_PLUGIN_URL . 'css/admin.css', false, '0.1' );
		wp_register_script( 'wdsscript', WDS_PLUGIN_URL . 'js/admin.js', array( 'jquery' ), '0.1' );
	}

	function register_setting( $option_group, $option_name, $sanitize_callback = '' ) {
		if ( is_multisite() && 'wds_settings_options' == $option_group ) {
			if ( wds_is_wizard_step( 2 ) )
				$option_group = $option_name = 'wds_sitemap_options';

			if ( wds_is_wizard_step( 3 ) )
				$option_group = $option_name = 'wds_onpage_options';

			if ( wds_is_wizard_step( 4 ) )
				$option_group = $option_name = 'wds_seomoz_options';

			if ( wds_is_wizard_step( 5 ) )
				$option_group = $option_name = 'wds_autolinks_options';
		}

		register_setting( $option_group, $option_name, $sanitize_callback );
	}

	/* Add sub page to the Settings Menu */
	function add_page() {
		global $wds_page_hook, $wp_version;

		if ( ! is_multisite() ) {
			$wds_page_hook = add_submenu_page( 'options-general.php', __( 'WPMU DEV SEO Wizard' , 'wds'), __( 'WPMU DEV SEO', 'wds' ), $this->capability, 'wds_wizard', array( &$this, 'options_page' ) );
		} elseif ( version_compare( $wp_version , '3.0.9', '>' ) ) {
			$wds_page_hook = add_submenu_page( 'settings.php', __( 'WPMU DEV SEO Wizard' , 'wds'), __( 'WPMU DEV SEO', 'wds' ), $this->capability, 'wds_wizard', array( &$this, 'options_page' ) );
		} else {
			$wds_page_hook = add_submenu_page( 'ms-admin.php', __( 'WPMU DEV SEO Wizard' , 'wds'), __( 'WPMU DEV SEO', 'wds' ), $this->capability, 'wds_wizard', array( &$this, 'options_page' ) );
		}

		add_action( "admin_print_styles-$wds_page_hook", array( &$this, 'admin_styles' ) );
		add_action( "admin_print_scripts-$wds_page_hook", array( &$this, 'admin_scripts' ) );
	}

	/* Display the admin options page */
	function options_page() {
		$slug = $this->slug;
		$title = $this->title;
		$description = $this->description;
		$options_name = $this->options_name;

		$msg = '';
		if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
			$msg = __( 'Settings updated' , 'wds');

			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
				$msg .= __( ' &amp; W3 Total Cache Page Cache flushed' , 'wds');
			} else if ( function_exists( 'wp_cache_clear_cache' )) {
				wp_cache_clear_cache();
				$msg .= __( ' &amp; WP Super Cache flushed' , 'wds');
			}

			$msg = '<div id="message" style="width:94%;" class="message updated"><p><strong>' . $msg . '.</strong></p></div>';
		}

		$action_url = admin_url( 'options.php' );

		echo "<div class='wrap'>$msg
			<div class='icon32' id='icon-options-general'><br></div>
			<h2>$title</h2>
			$description
			<form action='$action_url' method='post'>";
			settings_fields( $options_name );
			do_settings_sections( $slug );
			if( 'wds_help' !== $slug )
				echo "<p class='submit'>
				<input name='Submit' type='submit' class='button-primary' value='" . esc_attr( __( 'Save Settings' , 'wds') ) . "' />
			</p>";
			echo "
			</form>
			{$this->additional}
		</div>";
	}

	function contextual_help($contextual_help, $screen_id, $screen) {
		global $wds_page_hook;

  	if ( isset($_GET['page']) && $_GET['page'] == 'wds_wizard' && $this->contextual_help ) {
			$contextual_help = $this->contextual_help;
		}

		return $contextual_help;
	}

	/* Enqueue styles */
	function admin_styles() {
		wp_enqueue_style( 'wdsstyle' );
	}

	/* Enqueue scripts */
	function admin_scripts() {
		wp_enqueue_script( 'wdsscript' );
	}

	/* Validate user data for some/all of your input fields */
	function validate($input) {
		return $input; // return validated input
	}

}

class WDS_Core_Admin_Tab extends WDS_Core_Admin {
	/**
	* PHP5 constructor
	*/
	function __construct( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' ) {
		global $wds_options, $wp_version;

		$this->slug = $name;
		$this->name = str_replace( 'wds_', '', $name );
		$this->title = $title;
		$this->description = $description;
		$this->fields = $fields;
		$this->parent_slug = $parent_slug;
		$this->contextual_help = $contextual_help;
		$this->additional = $additional;

		$this->options_name = $options_name = $name . '_options';

		if( is_multisite() && WDS_SITEWIDE == true ) {
			$this->options = get_site_option( $options_name );
			$this->capability = 'manage_network_options';
		} else {
			$this->options = get_option( $options_name );
		}

		add_action( 'admin_init', array( &$this, 'init' ) );
		if ( is_multisite() && version_compare( $wp_version , '3.0.9', '>' ) )
			add_action( 'network_admin_menu', array( &$this, 'add_page' ) );
		else
			add_action( 'admin_menu', array( &$this, 'add_page' ) );
		add_filter( 'contextual_help', array( &$this, 'contextual_help' ), 10, 3 );
	}

	/**
	* PHP4 constructor
	*/
	function WDS_Core_Admin_Tab( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' ) {
		$this->__construct( $name, $title, $description = '', $fields = array(), $parent_slug = '', $contextual_help, $additional = '' );
	}

	/* Display the admin options page */
	function options_page() {
		global $wds_options;

		$slug = $this->slug;
		$title = $this->title;
		$description = $this->description;
		$options_name = $this->options_name;

		$msg = '';
		if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
			$msg = __( 'Settings updated' , 'wds');

			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
				$msg .= __( ' &amp; W3 Total Cache Page Cache flushed' , 'wds');
			} else if ( function_exists( 'wp_cache_clear_cache' )) {
				wp_cache_clear_cache();
				$msg .= __( ' &amp; WP Super Cache flushed' , 'wds');
			}

			$msg = '<div id="message" style="width:94%;" class="message updated"><p><strong>' . $msg . '.</strong></p></div>';
		}

		$tabs = '';
		$step_title = array( 0, 'Settings', 'Sitemaps', 'Title & Meta', 'SEOmoz', 'Automatic Links' );
		for ( $i = 1; $i <= 5; $i++ ) {
			$active = wds_is_wizard_step( $i ) ? ' nav-tab-active' : '';
			$tabs .= "<a href='admin.php?page=wds_wizard&step=$i' class='nav-tab$active' title='$step_title[$i]'>Step $i</a>";
		}

		$action_url = admin_url( 'options.php' );

		echo "<div class='wrap'>$msg
			<div class='icon32' id='icon-options-general'><br></div>
			<h2>" . __( 'WPMU DEV SEO Wizard', 'wds' ) . "</h2>
			<h3 class='nav-tab-wrapper'>$tabs</h3>
			<h2>$title</h2>";

		if ( 'settings' == $this->name || ( isset( $wds_options[$this->name] ) && $wds_options[$this->name] = 'on' ) ) {
			echo "$description
				<form action='$action_url' method='post'>";
				settings_fields( $options_name );
				do_settings_sections( $slug );
				echo "<p class='submit'>
					<input name='Submit' type='submit' class='button-primary' value='" . esc_attr( __( 'Save Settings' , 'wds') ) . "' />
				</p>
				</form>
				{$this->additional}";
		} else {
			echo "You've chosen not to set up '$title', please move onto next step.";
		}

		echo "</div>";
	}

	function contextual_help($contextual_help, $screen_id, $screen) {

		if ( isset($_GET['page']) && $_GET['page'] == 'wds_wizard' && $this->contextual_help ) {
			$contextual_help = $this->contextual_help;
		}

		return $contextual_help;
	}


	/**** ADDED ****/
	/* Methods below this line are new. */


	/**
	 * Brute-register all the settings.
	 *
	 * If we got this far, this is a sane thing to do.
	 * This overrides the `WDS_Core_Admin::register_setting()`.
	 *
	 * In response to "Unable to save options multiple times" bug.
	 */
	function register_setting( $option_group, $option_name, $sanitize_callback = '' ) {
		register_setting( 'wds_settings_options', 'wds_settings_options', $sanitize_callback );
		register_setting( 'wds_sitemap_options', 'wds_sitemap_options', $sanitize_callback );
		register_setting( 'wds_onpage_options', 'wds_onpage_options', $sanitize_callback );
		register_setting( 'wds_seomoz_options', 'wds_seomoz_options', $sanitize_callback );
		register_setting( 'wds_autolinks_options', 'wds_autolinks_options', $sanitize_callback );
	}

	/**
	 * Add text field to settings form with an hidden input.
	 *
	 * This overrides the `WDS_Core_Admin::add_content_field()` by adding a hidden input with option value.
	 * This is done to complement the `register_setting()` change above.
	 * Together, the end result should be the same (sans multiple saves error).
	 */
	function add_content_field( $args = array() ) {
		$defaults = array(
			'text' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "{$text}<input id='$name' name='{$options_name}[{$name}]' size='$size' type='hidden'value='{$options[$name]}' />";
	}

	/**
	 * Add disabled text input field.
	 * For Lite version.
	 */
	function add_text_disabled_field ($args) {
		$defaults = array(
			'name' => '', 'title' => '', 'description' => '',
			'size' => '', 'type' => 'text', 'class' => ''
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<input disabled='disabled' id='$name' name='{$options_name}[{$name}]' size='$size' type='$type' class='regular-text' value='{$options[$name]}' /><p class='description'>$description</p>";
	}

	/**
	 * Add disabled textarea field.
	 * For Lite version.
	 */
	function add_textarea_disabled_field ($args) {
		$defaults = array(
			'name' => '', 'title' => '', 'description' => '',
			'class' => '', 'rows' => '2', 'cols' => '35'
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$options_name = $this->options_name;
		$options = $this->options;
		$options[$name] = isset( $options[$name] ) ? $options[$name] : '';

		echo "<fieldset>
			<legend class='screen-reader-text'><span>$title</span></legend>
			<textarea disabled='disabled' class='$class' id='$name' name='{$options_name}[{$name}]' rows='$rows' cols='$cols' type='textarea'>{$options[$name]}</textarea>
			<div class='description'>$description</div>
		</fieldset>";
	}
}
