<?php
/**
 * Handles all security checks
 */
class Visual_Form_Builder_Security {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Honeypot_check function.
	 *
	 * @access public
	 * @return true
	 */
	public function honeypot_check() {
		if ( ! isset( $_POST['vfb-spam'] ) ) {
			return true;
		}

		if ( isset( $_POST['vfb-spam'] ) && ! empty( $_POST['vfb-spam'] ) ) {
			return __( 'Security check: you filled out a form field that was created to stop spam bots and should be left blank. If you think this is an error, please email the site owner.', 'visual-form-builder' );
		}

		return true;
	}

	/**
	 * Secret_check function.
	 *
	 * @access public
	 * @return true
	 */
	public function secret_check() {
		$required     = isset( $_POST['_vfb-required-secret'] ) && '0' === $_POST['_vfb-required-secret'] ? false : true;
		$secret_field = isset( $_POST['_vfb-secret'] ) ? sanitize_text_field( wp_unslash( $_POST['_vfb-secret'] ) ) : '';

		// If the verification is set to required, run validation check.
		if ( true === $required && ! empty( $secret_field ) ) {
			if ( isset( $_POST[ $secret_field ] ) ) {
				$post_secret_field = sanitize_text_field( wp_unslash( $_POST[ $secret_field ] ) );
				if ( ! is_numeric( $post_secret_field ) || strlen( $post_secret_field ) !== 2 ) {
					return esc_html__( 'Security check: failed secret question. Please try again!', 'visual-form-builder' );
				}
			}
		}

		return true;
	}

	/**
	 * [referer_check description]
	 *
	 * @return [type] [description]
	 */
	public function referer_check() {
		$referrer       = isset( $_POST['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : false;
		$wp_get_referer = wp_get_referer();
		$form_id        = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

		$skip_referrer_check = apply_filters( 'vfb_skip_referrer_check', false, $form_id );

		// Test if referral URL has been set.
		if ( ! $referrer ) {
			return esc_html__( 'Security check: referal URL does not appear to be set.', 'visual-form-builder' );
		}

		// Allow referrer check to be skipped.
		if ( ! $skip_referrer_check ) {
			// Test if the referral URL matches what sent from WordPress.
			if ( $wp_get_referer ) {
				return esc_html__( 'Security check: referal does not match this site.', 'visual-form-builder' );
			}
		}

		return true;
	}

	/**
	 * Make sure the User Agent string is not a SPAM bot.
	 *
	 * Returns true if NOT a SPAM bot
	 *
	 * @access public
	 * @return true
	 */
	public function bot_check() {
		$bots = array(
			'<',
			'>',
			'&lt;',
			'%0A',
			'%0D',
			'%27',
			'%3C',
			'%3E',
			'%00',
			'href',
			'binlar',
			'casper',
			'cmsworldmap',
			'comodo',
			'diavol',
			'dotbot',
			'feedfinder',
			'flicky',
			'ia_archiver',
			'jakarta',
			'kmccrew',
			'nutch',
			'planetwork',
			'purebot',
			'pycurl',
			'skygrid',
			'sucker',
			'turnit',
			'vikspider',
			'zmeu',
		);

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_kses_data( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		do_action( 'vfb_isbot', $user_agent, $bots );

		foreach ( $bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return esc_html__( 'Security check: looks like you are a SPAM bot. If you think this is an error, please email the site owner.', 'visual-form-builder' );
			}
		}

		return true;
	}
}
