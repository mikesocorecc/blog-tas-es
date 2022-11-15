<?php
/**
 * [vfb_register_widgets description]
 *
 * @return  void
 */
function vfb_register_widgets() {
	register_widget( 'Visual_Form_Builder_Widget' );
}
add_action( 'widgets_init', 'vfb_register_widgets' );

/**
 * Class that builds our Import page
 *
 * @since 2.7
 */
class Visual_Form_Builder_Widget extends WP_Widget {
	/**
	 * [__construct description]
	 */
	public function __construct() {
		parent::__construct(
			'vfb_widget',
			__( 'Visual Form Builder', 'visual-form-builder' ),
			array(
				'classname'   => 'vfb_widget_class',
				'description' => esc_html__( 'Visual Form Builder Widget', 'visual-form-builder' ),
			)
		);
	}

	/**
	 * [form description]
	 *
	 * @param   [type] $instance  [$instance description].
	 *
	 * @return  void
	 */
	public function form( $instance ) {
		global $wpdb;

		// Query to get all forms.
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_widget', '' );
		$forms = $wpdb->get_results( 'SELECT * FROM ' . VFB_WP_FORMS_TABLE_NAME . " WHERE 1=1 $where ORDER BY $order" );

		$instance = wp_parse_args( (array) $instance );

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'visual-form-builder' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e( 'Form to display:', 'visual-form-builder' ); ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" class="widefat">
		<?php
		foreach ( $forms as $form ) {
			echo sprintf(
				'<option value="%1$d" id="%2$s"%3$s>%1$d - %4$s</option>',
				absint( $form->form_id ),
				esc_html( $form->form_key ),
				selected( $form->form_id, $instance['id'], 1 ),
				esc_html( wp_unslash( $form->form_title ) )
			);
		}
		?>
		</select>
	</p>
		<?php
	}

	/**
	 * [widget description]
	 *
	 * @param  [type] $args     [description].
	 * @param  [type] $instance [description].
	 */
	public function widget( $args, $instance ) {
		$form_id = absint( $instance['id'] );

		echo esc_html( $args['before_widget'] );

		// Title.
		if ( ! empty( $instance['title'] ) ) {
			echo esc_html( $args['before_title'] . $instance['title'] . $args['after_title'] );
		}

		// Print the output.
		echo do_shortcode( '[vfb id=' . wp_kses_post( $form_id ) . ']' );

		echo esc_html( $args['after_widget'] );
	}

	/**
	 * [update description]
	 *
	 * @param  [type] $new_instance [description].
	 * @param  [type] $old_instance [description].
	 * @return [type]               [description]
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['id']    = ! empty( $new_instance['id'] ) ? absint( $new_instance['id'] ) : '';
		$instance['title'] = ! empty( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}
