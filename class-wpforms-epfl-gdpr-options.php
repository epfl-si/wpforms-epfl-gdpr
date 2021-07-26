<?php
/**
 * Summary (no period for file headers)
 *
 * Description. (use period)
 *
 * @link URL
 *
 * @package WPFormsEPFLGDPR
 * @since 0.0.2 (when the file was introduced)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms_EPFL_GDPR_Options
 */
class WPForms_EPFL_GDPR_Options {
	/**
	 * ID of the WPForms
	 *
	 * @var int $epfl_gdpr_wpform_id
	 */
	public int $epfl_gdpr_wpform_id;

	/**
	 * Date from when to enforce GDPR
	 *
	 * @var string $epfl_gdpr_start_date
	 */
	private $epfl_gdpr_start_date;

	/**
	 * Date until when to enforce GDPR
	 *
	 * @var string $epfl_gdpr_end_date
	 */
	private $epfl_gdpr_end_date;

	/**
	 * Constructor
	 *
	 * @param int $formid epfl_gdpr_wpform_id.
	 * @throws \Exception Exception in case the post_type is not 'wpforms'.
	 */
	public function __construct( $formid ) {
		$this->epfl_gdpr_wpform_id = $formid;
		$this->epfl_gdpr_post      = get_post( $formid );
		if ( 'wpforms' !== $this->epfl_gdpr_post->post_type ) {
			throw new \Exception( 'WPForms_EPFL_GDPR_Options error', 1 );
		}
	}

	/**
	 * Getter for post title
	 *
	 * @return string Post title
	 */
	public function get_post_title() {
		return $this->epfl_gdpr_post->post_title;
	}


	/**
	 * Getter for enforcement start date
	 *
	 * @return string date
	 */
	public function get_start_date() {
		$this->epfl_gdpr_start_date = get_post_meta( $this->epfl_gdpr_post->ID, 'epfl-gdpr-start-date', true );
		return $this->epfl_gdpr_start_date;
	}

	/**
	 * Getter for enforcement end date
	 *
	 * @return string date
	 */
	public function get_end_date() {
		$this->epfl_gdpr_end_date = get_post_meta( $this->epfl_gdpr_post->ID, 'epfl-gdpr-end-date', true );
		return $this->epfl_gdpr_end_date;
	}

	/**
	 * Enforcement is active
	 *
	 * @return bool enforcement
	 */
	public function is_active() {
		return ( $this->get_start_date() <= gmdate( 'Y-m-d' ) && $this->get_end_date() >= gmdate( 'Y-m-d' ) );
	}

	/**
	 * User can change options
	 *
	 * @return bool
	 */
	public function can_change_options() {
		if ( empty( $this->get_start_date() ) || empty( $this->get_end_date() ) || $this->get_start_date() > gmdate( 'Y-m-d' ) ) {
			return true;
		}
		return false;
	}

}
