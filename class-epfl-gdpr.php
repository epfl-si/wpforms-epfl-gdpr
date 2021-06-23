<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EPFL GDPR integration.
 *
 * @package WPFormsEPFLGDPR
 */
class WPForms_EPFL_GDPR extends WPForms_Payment {

	/**
	 * Initialize.
	 *
	 */
	public function init() {

		$this->wp_tested_version  = WP_LATEST_VERSION_WPFORMS_EPFL_GDPR;
		$this->wp_min_version     = WP_MIN_VERSION_WPFORMS_EPFL_GDPR;
		$this->version            = WPFORMS_EPFL_GDPR_VERSION;
		$this->plugin_name        = WPFORMS_EPFL_GDPR_NAME;
		$this->name               = 'EPFL GDPR';
		$this->slug               = 'epfl_gdpr';
		$this->priority           = 10;
		// $this->icon               = plugins_url( 'assets/images/EPFL-GDPR-trans.png', __FILE__ );
		$this->cache_seconds      = 3600; // 43200 = 12 hours cache

		// Add additional link to the plugin row
		add_filter( 'plugin_row_meta', array( $this, 'add_links_to_plugin_row'), 10, 4 );
	}

	/**
	 * Add additional links for WPForms EPFL GDPR in the plugins list.
	 *
	 */
	function add_links_to_plugin_row( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $this->plugin_name == $plugin_data['Name'] ) {
			// if (substr_compare($plugin_meta[2], '<a href="' . $plugin_data['PluginURI'] .'">', 0, 66) == 0) {
			// 	// Kick the "Visit plugin site" link and add the "View details" wich is normally reserved for WP hosted plugin
			// 	$plugin_meta[2] = sprintf( '<a href="%s" class="thickbox" aria-label="%s" data-title="%s">%s</a>',
			// 											esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&TB_iframe=true&width=600&height=550' ) ),
			// 											esc_attr( sprintf( __( 'More information about %s' ), $this->name ) ),
			// 											esc_attr( $this->name ),
			// 											__( 'View details' )
			// 									);
			// }
			// $row_meta = array(
			// 	'privacy-policy'      => '<a href="' . esc_url( 'https://www.epfl.ch/about/presidency/presidents-team/legal-affairs/epfl-privacy-policy/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'wpforms-epfl-gdpr' ) . '">' . esc_html__( 'privacy-policy', 'wpforms-epfl-gdpr' ) . '</a>',
			// 	'help' => '<a href="' . esc_url( 'https://github.com/epfl-si/wpforms-epfl-gdpr' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'wpforms-epfl-gdpr' ) . '">' . esc_html__( 'Help', 'wpforms-epfl-gdpr' ) . '</a>'
			// );

			//return array_merge( $plugin_meta, $row_meta );
		}
		return (array) $plugin_meta;
	}

}

new WPForms_EPFL_GDPR();
