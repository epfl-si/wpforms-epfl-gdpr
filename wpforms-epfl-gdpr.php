<?php
/**
 * Plugin Name: WPForms EPFL GDPR
 * Plugin URI:  https://github.com/epfl-si/wpforms-epfl-gdpr
 * Description: EPFL GDPR integration with WPForms.
 * Author:      Nicolas Borboën
 * Author URI:  https://go.epfl.ch/nbo
 * Version:     1.0.0
 * Text Domain: wpforms-epfl-gdpr
 * Domain Path: languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * WPForms EPFL GDPR is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms EPFL GDPR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms EPFL GDPR.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    WPFormsEPFLGDPR
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, EPFL
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
define( 'WPFORMS_EPFL_GDPR_VERSION', '1.0.0' );
// Plugin name.
define( 'WPFORMS_EPFL_GDPR_NAME', 'WPForms EPFL GDPR' );
// Latest WP version tested with this plugin.
define( 'WP_LATEST_VERSION_WPFORMS_EPFL_GDPR', '5.4' );
// Minimal WP version required for this plugin.
define( 'WP_MIN_VERSION_WPFORMS_EPFL_GDPR', '5.0' );

// Plugin Folder Path.
if ( ! defined( 'WPFORMS_EPFL_GDPR_PLUGIN_DIR' ) ) {
	define( 'WPFORMS_EPFL_GDPR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'WPFORMS_EPFL_GDPR_PLUGIN_URL' ) ) {
	define( 'WPFORMS_EPFL_GDPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Load the WPForms_EPFL_GDPR class.
 */
function wpforms_epfl_gdpr() {

	// WPForms Pro is required.
	if ( ! class_exists( 'WPForms_Pro' ) ) {
		return;
	}

	load_plugin_textdomain( 'wpforms-epfl-gdpr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once plugin_dir_path( __FILE__ ) . 'class-wpforms-epfl-gdpr.php';
	$wpforms_epfl_gdpr = new WPForms_EPFL_GDPR();
	$wpforms_epfl_gdpr->init();
}

add_action( 'wpforms_loaded', 'wpforms_epfl_gdpr' );
