<?php
/**
 * Handles form confirmation actions
 */
class Visual_Form_Builder_Confirmation {

	/**
	 * Form
	 *
	 * @var    mixed
	 * @access public
	 */
	public $form_id;

	/**
	 * [__construct description]
	 *
	 * @param   [type] $form_id  [$form_id description].
	 *
	 * @return  void
	 */
	public function __construct( $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * Text message confirmation
	 *
	 * @return  [type]  [return description]
	 */
	public function text() {
		$data = $this->get_settings();

		$type    = isset( $data['form_success_type'] ) ? $data['form_success_type'] : 'text';
		$message = isset( $data['form_success_message'] ) ? wp_unslash( html_entity_decode( wp_kses_stripslashes( $data['form_success_message'] ) ) ) : '';

		if ( 'text' !== $type ) {
			return;
		}

		return $message;
	}

	/**
	 * [wp_page description]
	 *
	 * @return void
	 */
	public function wp_page() {
		$data = $this->get_settings();

		$type = isset( $data['form_success_type'] ) ? $data['form_success_type'] : 'text';
		$page = isset( $data['form_success_message'] ) ? $data['form_success_message'] : '';

		if ( 'page' !== $type ) {
			return;
		}

		$permalink = get_permalink( $page );
		wp_safe_redirect( esc_url_raw( $permalink ) );

		exit();
	}

	/**
	 * [redirect description]
	 *
	 * @return  [type]  [return description]
	 */
	public function redirect() {
		$data = $this->get_settings();

		$type     = isset( $data['form_success_type'] ) ? $data['form_success_type'] : 'text';
		$redirect = isset( $data['form_success_message'] ) ? $data['form_success_message'] : '';

		if ( 'redirect' !== $type ) {
			return;
		}

		wp_safe_redirect( esc_url_raw( $redirect ) );

		exit();
	}

	/**
	 * Get confirmaton settings
	 *
	 * @access public
	 * @return void
	 */
	public function get_settings() {
		global $wpdb;

		$form_id = $this->get_form_id();
		if ( ! $form_id ) {
			return;
		}

		$order = sanitize_sql_orderby( 'form_id DESC' );
		$form  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . VFB_WP_FORMS_TABLE_NAME . " WHERE form_id = %d ORDER BY $order", $form_id ), ARRAY_A );

		if ( null !== $form ) {
			return $form;
		} else {
			return false;
		}
	}

	/**
	 * Get just created Entry ID.
	 *
	 * @access public
	 * @return void
	 */
	public function get_entry_id() {
		$form_id = $this->get_form_id();
		if ( ! $form_id ) {
			return;
		}

		$vfbdb    = new VFB_Pro_Data();
		$settings = $vfbdb->get_form_settings( $form_id );

		if ( ! isset( $settings['data']['last-entry'] ) ) {
			return 0;
		}

		return $settings['data']['last-entry'];
	}

	/**
	 * Get form ID
	 *
	 * @access private
	 * @return int
	 */
	public function get_form_id() {
		if ( ! isset( $this->form_id ) ) {
			return false;
		}

		return (int) $this->form_id;
	}

	/**
	 * Basic check to exit if the form hasn't been submitted
	 *
	 * @access public
	 * @return void
	 */
	public function submit_check() {
		// If class form ID hasn't been set, exit.
		if ( ! $this->get_form_id() ) {
			return;
		}

		// If form ID hasn't been submitted by $_POST, exit.
		if ( ! isset( $_POST['vfb-submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['form_id'] ) ) {
			return;
		}

		// If class form ID doesn't match $_POST form ID, exit.
		if ( $this->get_form_id() !== absint( $_POST['form_id'] ) ) {
			return;
		}

		return true;
	}
}
