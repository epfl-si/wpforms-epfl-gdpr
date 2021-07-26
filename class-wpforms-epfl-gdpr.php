<?php
/**
 * Summary (no period for file headers)
 *
 * Description. (use period)
 *
 * @link URL
 *
 * @package WPFormsEPFLGDPR
 * @since   0.0.2 (when the file was introduced)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-wpforms-epfl-gdpr-options.php';

/**
 * WPForms_EPFL_GDPR
 *
 * @package WPFormsEPFLGDPR
 */
class WPForms_EPFL_GDPR extends WPForms_Payment {

	/**
	 * Initialize
	 */
	public function init() {
		$this->wp_tested_version = WP_LATEST_VERSION_WPFORMS_EPFL_GDPR;
		$this->wp_min_version    = WP_MIN_VERSION_WPFORMS_EPFL_GDPR;
		$this->version           = WPFORMS_EPFL_GDPR_VERSION;
		$this->plugin_name       = WPFORMS_EPFL_GDPR_NAME;
		$this->name              = 'EPFL GDPR';
		$this->slug              = 'wpforms-epfl-gdpr';
		$this->priority          = 10;
		$this->cache_seconds     = 3600;
		// $this->icon              = plugins_url( 'assets/images/EPFL-GDPR-trans.png', __FILE__ );

		// Add additional link to the plugin row
		// add_filter( 'plugin_row_meta', array( $this, 'add_links_to_plugin_row'), 10, 4 );
		add_action( 'admin_menu', array( $this, 'wpforms_epfl_gdpr_admin_menu' ) );
		// add_filter( 'submenu_file', array( $this, 'so3902760_wp_admin_submenu_filter') );

		// add_filters( 'wpforms_overview_table_column_value', $value, $form, $column_name );

		add_action( 'admin_post_save_epfl_gdpr_options', array( $this, 'save_epfl_gdpr_options' ) );

		// add_action( 'wpforms_builder_save_form', array( $this, 'add_date_fields_on_save' ), 10, 2 );
		// Change the admin list of forms.
		add_filter( 'wpforms_overview_table_columns', array( $this, 'change_colums' ), 10, 1 );
		add_filter( 'wpforms_overview_table_column_value', array( $this, 'change_colums_values' ), 10, 3 );
	}

	/**
	 * Function: add_date_fields_on_save
	 *
	 * @param int   $form_id The form ID.
	 * @param array $data The form data.
	 */
	public function add_date_fields_on_save( ?int $form_id, $data ) {
		error_log( '--------------------------------' );
		error_log( var_export( $form_id, true ) );
		error_log( var_export( $data, true ) );
	}

	/**
	 * Function: change_colums
	 *
	 * @param array $columns The columns data.
	 */
	public function change_colums( $columns ) {
		$offset = 2;
		return array_slice( $columns, 0, $offset, true ) +
			array(
				'gdpr'     => 'GDPR',
				'start_dt' => 'Start Date',
				'end_dt'   => 'End Date',
			) +
			array_slice( $columns, $offset, null, true );
	}

	/**
	 * Function: change_colums_values
	 *
	 * @param array  $value The columns data.
	 * @param int    $form The form ID.
	 * @param string $column_name The columns name.
	 */
	public function change_colums_values( $value, $form, $column_name ) {

		if ( 'gdpr' === $column_name || 'start_dt' === $column_name || 'end_dt' === $column_name ) {
			$form_gdpr_options = new WPForms_EPFL_GDPR_Options( $form->ID );
		}

		if ( 'gdpr' === $column_name ) {
			add_thickbox();
			return sprintf(
				'<a href="%s" class="thickbox button button-primary %s" aria-label="%s" data-title="%s">%s</a>',
				// esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&TB_iframe=true&width=600&height=550' ) ),
													// https://wp-httpd/wp-content/plugins/wpforms-epfl-gdpr/test.php
													// plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&TB_iframe=true&width=600&height=550' ) ),
													esc_url( 'https://wp-httpd/wp-admin/admin.php?page=epfl-gdpr-options&wpformsid=' . $form->ID . '	&TB_iframe=true&TB_inline=true&width=600&height=550' ),
				$form_gdpr_options->can_change_options() ? '' : 'disabled',
				esc_attr( sprintf( __( 'More information about %s' ), $this->name ) ),
				esc_attr( $this->name ),
				__( $form_gdpr_options->can_change_options() ? 'options' : 'disabled' )
			);
		}

		if ( 'start_dt' === $column_name ) {
			return $form_gdpr_options->get_start_date();
		}

		if ( 'end_dt' === $column_name ) {
			return $form_gdpr_options->get_end_date();
		}

		return $value;
	}

	/**
	 * Function: wpforms_epfl_gdpr_admin_menu
	 */
	public function wpforms_epfl_gdpr_admin_menu() {

		// Register the parent menu.
		add_menu_page(
			__( 'Parent title', 'wpforms-epfl-gdpr' ),
			'EPFL-GDPR-TO-BE-HIDDEN', // __( 'Parent', 'wpforms-epfl-gdpr' ),
			'manage_options',
			'epfl-gdpr-options',
			__CLASS__ . '::epfl_gdpr_manage_page'
		);

		// Register the hidden submenu.
		add_submenu_page(
			'epfl-gdpr-options', // Use the parent slug as usual.
			__( 'Page title', 'textdomain' ),
			'',
			'manage_options',
			'my_hidden_submenu',
			__CLASS__ . '::display_my_submenu'
		);

			// remove_menu_page('admin.php?page=epfl-gdpr-options');
			// remove_menu_page('admin.php','epfl-gdpr-options');
			// remove_submenu_page( 'admin.php', 'epfl-gdpr-options');
	}

	/**
	 * Function: epfl_gdpr_manage_page
	 *
	 * @param int   $formsid The form ID.
	 * @param array $errors The errors list.
	 */
	public static function epfl_gdpr_manage_page( $formsid = null, $errors = null ) {

		if ( empty( $formsid ) ) {
			$wpformsid = $_GET['wpformsid'] ?? null;
		} else {
			$wpformsid = $formsid;
		}

		// display general information if not editing a specific form,
		// i.e. from the menu or direct access to /admin.php?page=epfl-gdpr-options
		if ( empty( $wpformsid ) ) {
			$test = new \WPForms_EPFL_GDPR();
			return $test->display_epfl_gdpr_info();
		}

		// remove iframe header
		echo '<style>
.wp-toolbar {
	padding-top: 0px;
}
html.wp-toolbar {
	padding-top: 0px;
}

#wpadminbar, .update-nag {
	display: none;
	visibility: hidden;
}</style>';
		?>

		<?php
		// get the form infotmation
		$form_gdpr_options = new WPForms_EPFL_GDPR_Options( $wpformsid );

		// $mywpform = new \WPForms_Form_Handler();
		// $myform = $mywpform->get( $wpformsid);
		// $start_dt = get_post_meta( $wpformsid, 'epfl-gdpr-start-date', true);
		// $end_dt = get_post_meta( $wpformsid, 'epfl-gdpr-end-date', true);
		echo $errors['ok'] ?? null;
		echo '<h1>EPFL GDPR option</h1>';
		echo '<h2>' . $form_gdpr_options->get_post_title() . '</h2>';
		echo '<h3>About</h3>';
		echo '<blockquote>Research & development deployment termsheet responsive web design angel investor marketing crowdfunding bandwidth client founders assets. Alpha bandwidth client user experience bootstrapping buzz release ownership. MVP backing product management research & development business-to-business startup niche market business plan. Funding termsheet channels interaction design focus analytics. Facebook mass market alpha ramen long tail value proposition MVP focus first mover advantage market seed money learning curve user experience conversion. Lean startup business plan network effects churn rate holy grail business-to-consumer focus marketing. Release innovator leverage. Channels client bootstrapping. Agile development rockstar advisor crowdfunding. Funding leverage partner network analytics non-disclosure agreement incubator market channels paradigm shift.</blockquote>';
		echo '<hr />';

		if ( ! $form_gdpr_options->can_change_options() ) {
			echo '<h1>NO RIGHT // FORM HAVE TO BE BLOCKED</h1>';
		}

		echo '<h3>Options</h3>';
		echo "<form id='epfl-gdpr-frm-option' name='epfl-gdpr-start-date' method='post' action='admin-post.php'>";
		echo "<input name='action' type='hidden' value='save_epfl_gdpr_options'>";
		echo "<input type='hidden' name='epfl-gdpr-wpformsid' id='epfl-gdpr-wpformsid' value='" . $form_gdpr_options->epfl_gdpr_wpform_id . "' />";
		echo "Start date: <input type='date' name='epfl-gdpr-start-date' id='epfl-gdpr-start-date' value='" . $form_gdpr_options->get_start_date() . "' />";
		echo '<br />';
		echo "End date: <input type='date' name='epfl-gdpr-end-date' id='epfl-gdpr-end-date' value='" . $form_gdpr_options->get_end_date() . "' />";
		echo '<br />';
		// echo "<input class='button button-primary' type='submit' value='Submit' />";
		submit_button();
		echo '<br />';
		echo '</fieldset>';
		echo '</form>';

		// error_log(var_export( $myform, true ) );
		// echo "$myform->ID";
		// echo "$myform->post_title";
		// echo  var_export( $_GET, true);
		// echo var_export( $_POST, true);
	}

	/**
	 * Function: save_epfl_gdpr_options
	 *
	 * See: https://wordpress.stackexchange.com/questions/177076/post-form-request-with-admin-post
	 */
	public function save_epfl_gdpr_options() {
		error_log( '↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓' );
		error_log( var_export( $_POST, true ) );

		$mypost = get_post( $_POST['epfl-gdpr-wpformsid'] );

		if ( $_POST['epfl-gdpr-wpformsid']['epfl-gdpr-end-date'] < $_POST['epfl-gdpr-wpformsid']['epfl-gdpr-start-date'] ) {
			// 
		}
		if ( $_POST['epfl-gdpr-wpformsid']['epfl-gdpr-end-date'] < $_POST['epfl-gdpr-wpformsid']['epfl-gdpr-start-date'] ) {
			// 
		}
		$form_gdpr_options = new WPForms_EPFL_GDPR_Options( $_POST['epfl-gdpr-wpformsid'] );

		error_log( '↓↓↓↓↓↓↓↓↓↓post xxx↓↓↓↓↓↓↓↓↓↓↓' );
		error_log( var_export( $mypost, true ) );

		update_post_meta( $_POST['epfl-gdpr-wpformsid'], 'epfl-gdpr-start-date', $_POST['epfl-gdpr-start-date'] ?? null );
		update_post_meta( $_POST['epfl-gdpr-wpformsid'], 'epfl-gdpr-end-date', $_POST['epfl-gdpr-end-date'] ?? null );

		$mypostmeta = get_post_meta( $_POST['epfl-gdpr-wpformsid'] );
		error_log( '↓↓↓↓↓↓↓↓↓↓post meta↓↓↓↓↓↓↓↓↓↓↓' );
		error_log( var_export( $mypostmeta, true ) );
		$start_dt = get_post_meta( $_POST['epfl-gdpr-wpformsid'], 'epfl-gdpr-start-date', true );
		$end_dt   = get_post_meta( $_POST['epfl-gdpr-wpformsid'], 'epfl-gdpr-end-date', true );
		error_log( '↓↓↓↓↓↓↓↓↓↓ start / end ↓↓↓↓↓↓↓↓↓↓↓' );
		error_log( var_export( $start_dt, true ) );
		error_log( var_export( $end_dt, true ) );

		$this->epfl_gdpr_manage_page( $_POST['epfl-gdpr-wpformsid'], array( 'ok' => 'HELLO' ) );
		die();
		// $myfom->
		//
		echo "<script>window.top.location.href = '" . admin_url( 'admin.php?page=wpforms-overview' ) . "';</script>";
		die();

		// you can access $_POST, $GET and $_REQUEST values here.
		wp_redirect( admin_url( 'admin.php?page=wpforms-overview' ) );
		// apparently when finished, die(); is required.
		die();
	}

	/**
	 * Function: display_epfl_gdpr_info
	 */
	public function display_epfl_gdpr_info() {
		echo '<h1>EPFL GDPR</h1>';
	}

	/**
	 * Function: display_my_submenu
	 */
	public static function display_my_submenu() {
		echo 'toot';
	}

	/**
	 * Function: xxx
	 */
	public function so3902760_wp_admin_submenu_filter( $submenu_file ) {

		global $plugin_page;

		$hidden_submenus = array(
			'my_hidden_submenu' => true,
		);

		// Select another submenu item to highlight (optional).
		if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
			$submenu_file = 'submenu_to_highlight';
		}

		// Hide the submenu.
		foreach ( $hidden_submenus as $submenu => $unused ) {
			remove_submenu_page( 'epfl-gdpr-options', $submenu );
		}

		return $submenu_file;
	}


	/**
	 * Temporary function to test actions and filters.
	 */
	function test( $entry, $form ) {
		error_log( '--------------------------------------------' );
		error_log( var_export( $entry->meta, true ) );
		error_log( '--------------------------------------------' );
		error_log( var_export( json_decode( $entry->meta ), true ) );
		error_log( '--------------------------------------------' );
		// error_log("--------------------------------------------");
		// error_log(var_export( $form, true ) );
		// error_log("--------------------------------------------");
		// error_log(var_export( $th, true ) );
	}

	/**
	 * Add additional links for WPForms EPFL GDPR in the plugins list.
	 */
	function add_links_to_plugin_row( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $this->plugin_name == $plugin_data['Name'] ) {
			// if (substr_compare( $plugin_meta[2], '<a href="' . $plugin_data['PluginURI'] .'">', 0, 66) == 0) {
			// Kick the "Visit plugin site" link and add the "View details" wich is normally reserved for WP hosted plugin
			// $plugin_meta[2] = sprintf( '<a href="%s" class="thickbox" aria-label="%s" data-title="%s">%s</a>',
			// esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&TB_iframe=true&width=600&height=550' ) ),
			// esc_attr( sprintf( __( 'More information about %s' ), $this->name ) ),
			// esc_attr( $this->name ),
			// __( 'View details' )
			// );
			// }
			// $row_meta = array(
			// 'privacy-policy'      => '<a href="' . esc_url( 'https://www.epfl.ch/about/presidency/presidents-team/legal-affairs/epfl-privacy-policy/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'wpforms-epfl-gdpr' ) . '">' . esc_html__( 'privacy-policy', 'wpforms-epfl-gdpr' ) . '</a>',
			// 'help' => '<a href="' . esc_url( 'https://github.com/epfl-si/wpforms-epfl-gdpr' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'wpforms-epfl-gdpr' ) . '">' . esc_html__( 'Help', 'wpforms-epfl-gdpr' ) . '</a>'
			// );

			// return array_merge( $plugin_meta, $row_meta );
		}
		return (array) $plugin_meta;
	}



	/**
	 * Display content inside the panel content area.
	 *
	 * @TODO: find a way to have a new menu entry "EPFL GDPR"
	 */
	public function builder_content() {

		echo '<p class="lead">' .
			sprintf(
				wp_kses(
					/* translators: %s - Addons page URL in admin area. */
					__( 'This addon allows to use <a href="%1$s">EPFL PayonlineXXXX</a> with the <a href="%2$s">WPForms plugin</a>. Please read <a href="%3$s">PayonlineXXXX Help</a> in order to create a payment instance.', 'wpforms-epfl-gdpr' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				esc_url( __( 'https://payonline.epfl.ch?lang=en', 'wpforms-epfl-gdpr' ) ),
				esc_url( 'https://wpforms.com/' ),
				esc_url( __( 'https://wiki.epfl.ch/payonline-help', 'wpforms-epfl-gdpr' ) )
			) .
			'	<div class="notice">
					<p>' . __( 'General Data Protection Regulation (<b>GDPR</b>): By using this addon, you agree to comply with the directives relating to data protection at EPFL and to apply the seven key principles of article 5 of the GDPR.', 'wpforms-epfl-gdpr' ) . '</p>
				</div>
				<p>' .
				sprintf(
					__( 'WPForms-EPFL-PayonlineXXXX\'s information, help and sources are available on <a href="%1$s">GitHub</a>. Your are using the version <a href="%2$s">v%3$s</a> of the addon.', 'wpforms-epfl-gdpr' ),
					esc_url( 'https://github.com/epfl-si/wpforms-epfl-gdpr' ),
					esc_url( 'https://github.com/epfl-si/wpforms-epfl-gdpr/releases/tag/v' . WPFORMS_EPFL_PAYONLINE_VERSION ),
					WPFORMS_EPFL_PAYONLINE_VERSION
				)
				. '</p>
				<hr>
			</p>';

		wpforms_panel_field(
			'checkbox',
			$this->slug,
			'enable',
			$this->form_data,
			esc_html__( 'Enable EPFL PayonlineXXXX EPFL', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'default' => '0',
			)
		);
		wpforms_panel_field(
			'text',
			$this->slug,
			'id_inst',
			$this->form_data,
			esc_html__( 'EPFL PayonlineXXXX instance ID', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'tooltip' => esc_html__( 'You must create a payment instance (entity that identifies your conference in the PayonlineXXXX service) by using the "New Instance" link in the main menu on <a href="https://payonline.epfl.ch" target="_blank">payonline.epfl.ch</a>', 'wpforms-epfl-gdpr' ),
			)
		);
		wpforms_panel_field(
			'text',
			$this->slug,
			'email',
			$this->form_data,
			esc_html__( 'WPForms EPFL PayonlineXXXX Email Address', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'tooltip' => esc_html__( 'Enter an email address for EPFL notification', 'wpforms-epfl-gdpr' ),
			)
		);
		wpforms_panel_field(
			'select',
			$this->slug,
			'mode',
			$this->form_data,
			esc_html__( 'Mode', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'default' => 'production',
				'options' => array(
					'production' => esc_html__( 'Production', 'wpforms-epfl-gdpr' ),
					'test'       => esc_html__( 'Test / Sandbox', 'wpforms-epfl-gdpr' ),
				),
				'tooltip' => esc_html__( 'Select Production to receive real EPFL or select Test to use the EPFL PayonlineXXXX developer sandbox (id_inst=1234567890)', 'wpforms-epfl-gdpr' ),
			)
		);
		// wpforms_panel_field(
		// 	'select',
		// 	$this->slug,
		// 	'transaction',
		// 	$this->form_data,
		// 	esc_html__( 'Payment Type', 'wpforms-epfl-gdpr' ),
		// 	array(
		// 		'parent'  => 'EPFL',
		// 		'default' => 'product',
		// 		'options' => array(
		// 			'product'  => esc_html__( 'Products and Services', 'wpforms-epfl-gdpr' ),
		// 			'donation' => esc_html__( 'Donation', 'wpforms-epfl-gdpr' ),
		// 		),
		// 		'tooltip' => esc_html__( 'Select the type of payment you are receiving.', 'wpforms-epfl-gdpr' ),
		// 	)
		// );
		// wpforms_panel_field(
		// 	'text',
		// 	$this->slug,
		// 	'cancel_url',
		// 	$this->form_data,
		// 	esc_html__( 'Cancel URL', 'wpforms-epfl-gdpr' ),
		// 	array(
		// 		'parent'  => 'EPFL',
		// 		'tooltip' => esc_html__( 'Enter the URL to send users to if they do not complete the EPFL PayonlineXXXX checkout', 'wpforms-epfl-gdpr' ),
		// 	)
		// );
		wpforms_panel_field(
			'select',
			$this->slug,
			'shipping',
			$this->form_data,
			esc_html__( 'Shipping', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'default' => '0',
				'options' => array(
					'1' => esc_html__( 'Don\'t ask for an address', 'wpforms-epfl-gdpr' ),
					'0' => esc_html__( 'Ask for an address, but do not require', 'wpforms-epfl-gdpr' ),
					'2' => esc_html__( 'Ask for an address and require it', 'wpforms-epfl-gdpr' ),
				),
			)
		);
		wpforms_panel_field(
			'checkbox',
			$this->slug,
			'note',
			$this->form_data,
			esc_html__( 'Don\'t ask buyer to include a note with payment', 'wpforms-epfl-gdpr' ),
			array(
				'parent'  => 'EPFL',
				'default' => '1',
			)
		);

		if ( function_exists( 'wpforms_conditional_logic' ) ) {
			wpforms_conditional_logic()->conditionals_block(
				array(
					'form'        => $this->form_data,
					'type'        => 'panel',
					'panel'       => 'epfl_payonlineXX',
					'parent'      => 'EPFL',
					'actions'     => array(
						'go'   => esc_html__( 'Process', 'wpforms-epfl-gdpr' ),
						'stop' => esc_html__( 'Don\'t process', 'wpforms-epfl-gdpr' ),
					),
					'action_desc' => esc_html__( 'this charge if', 'wpforms-epfl-gdpr' ),
					'reference'   => esc_html__( 'EPFL PayonlineXXXX Standard payment', 'wpforms-epfl-gdpr' ),
				)
			);
		} else {
			echo '<p class="note">' .
				sprintf(
					wp_kses(
						/* translators: %s - Addons page URL in admin area. */
						__( 'Install the <a href="%s">Conditional Logic addon</a> to enable conditional logic for EPFL Payonline EPFL.', 'wpforms-epfl-gdpr' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					admin_url( 'admin.php?page=wpforms-addons' )
				) .
				'</p>';
		}
	}

}

new WPForms_EPFL_GDPR();
