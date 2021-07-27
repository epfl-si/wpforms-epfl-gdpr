<?php
/**
 * Main class of WPFormsEPFLGDPR
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
class WPForms_EPFL_GDPR {

	/**
	 * Initialize
	 */
	public function init() {
		$this->wp_tested_version = WP_LATEST_VERSION_WPFORMS_EPFL_GDPR;
		$this->wp_min_version    = WP_MIN_VERSION_WPFORMS_EPFL_GDPR;
		$this->version           = WPFORMS_EPFL_GDPR_VERSION;
		$this->plugin_name       = WPFORMS_EPFL_GDPR_NAME;
		$this->name              = 'EPFL GDPRxx';
		$this->slug              = 'wpforms-epfl-gdpr';
		$this->priority          = 10;
		$this->cache_seconds     = 3600;
		// $this->icon              = plugins_url( 'assets/images/EPFL-GDPR-trans.png', __FILE__ );

		// Add additional link to the plugin row
		add_action( 'admin_menu', array( $this, 'wpforms_epfl_gdpr_admin_menu' ) );

		add_action( 'admin_post_save_epfl_gdpr_options', array( $this, 'save_epfl_gdpr_options' ) );

		// Change the admin list of forms.
		add_filter( 'wpforms_overview_table_columns', array( $this, 'alter_wpforms_overview_table_columns' ), 10, 1 );
		add_filter( 'wpforms_overview_table_column_value', array( $this, 'alter_wpforms_overview_table_columns_values' ), 10, 3 );

		// Remove the menu entry (/wp-admin/admin.php?page=epfl-gdpr-options).
		add_action( 'admin_init', array( $this, 'remove_epfl_gdpr_menu_entry' ), 999 );
	}

	/**
	 * Function: debug
	 *
	 * @param array $data The data to debug.
	 */
	public function debug( ?array $data ) {
		error_log( '--------------------------------' );
		error_log( var_export( $data, true ) );
	}

	/**
	 * Function: alter_wpforms_overview_table_columns
	 *
	 * Add "ours" columns to /wp-admin/admin.php?page=wpforms-overview
	 *
	 * @param array $columns The columns data.
	 */
	public function alter_wpforms_overview_table_columns( $columns ) {
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
	 * Function: alter_wpforms_overview_table_columns_values
	 *
	 * @param array  $value The columns data.
	 * @param object $form The form.
	 * @param string $column_name The columns name.
	 */
	public function alter_wpforms_overview_table_columns_values( $value, $form, $column_name ) {

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
	 *
	 * This generate the content of the /wp-admin/admin.php?page=epfl-gdpr-options
	 * page which is hidden in the menu by the function
	 * "remove_epfl_gdpr_menu_entry" in this file.
	 *
	 * @TODO: add some explaination here.
	 */
	public function display_epfl_gdpr_info() {
		echo '<h1>EPFL GDPR</h1>';
		echo '<div class="update-nag notice notice-info">While your are not suppose to access this page directly, here is some information: @TODO</div>';
		die();
	}

	/**
	 * Function: remove admin menu entry
	 */
	public function remove_epfl_gdpr_menu_entry() {
		remove_menu_page( 'epfl-gdpr-options' );
	}
}
