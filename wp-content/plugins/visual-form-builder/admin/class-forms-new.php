<?php
/**
 * Class that controls the Add New Form view
 */
class Visual_Form_Builder_Forms_New {
	/**
	 * Display function.
	 *
	 * @access public
	 * @return void
	 */
	public function display() {
		?>
	<form method="post" id="visual-form-builder-new-form" action="">
		<input name="vfb-action" type="hidden" value="create_form" />
			<?php
			wp_nonce_field( 'create_form' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to create a new form.', 'visual-form-builder' ) );
			}
			?>
		<h3><?php esc_html_e( 'Create a form', 'visual-form-builder' ); ?></h3>

		<table class="form-table">
			<tbody>
			<!-- Form Name -->
			<tr valign="top">
				<th scope="row"><label for="form-name"><?php esc_html_e( 'Name the form', 'visual-form-builder' ); ?></label></th>
			<td>
				<input type="text" autofocus="autofocus" class="regular-text required" id="form-name" name="form_title" />
				<p class="description"><?php esc_html_e( 'Required. This name is used for admin purposes.', 'visual-form-builder' ); ?></p>
			</td>
			</tr>
			<!-- Sender Name -->
			<tr valign="top">
				<th scope="row"><label for="form-email-sender-name"><?php esc_html_e( 'Your Name or Company', 'visual-form-builder' ); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-sender-name" name="form_email_from_name" />
					<p class="description"><?php esc_html_e( 'Required. This option sets the "From" display name of the email that is sent.', 'visual-form-builder' ); ?></p>
				</td>
			</tr>
			<!-- Reply-to Email -->
			<tr valign="top">
				<th scope="row"><label for="form-email-from"><?php esc_html_e( 'Reply-To E-mail', 'visual-form-builder' ); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-from" name="form_email_from" />
					<p class="description"><?php esc_html_e( 'Required. Replies to your email will go here.', 'visual-form-builder' ); ?></p>
					<p class="description"><?php esc_html_e( 'Tip: for best results, use an email that exists on this domain.', 'visual-form-builder' ); ?></p>
				</td>
			</tr>
			<!-- Email Subject -->
			<tr valign="top">
				<th scope="row"><label for="form-email-subject"><?php esc_html_e( 'E-mail Subject', 'visual-form-builder' ); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-subject" name="form_email_subject" />
					<p class="description"><?php esc_html_e( 'This sets the subject of the email that is sent.', 'visual-form-builder' ); ?></p>
				</td>
			</tr>
			<!-- E-mail To -->
			<tr valign="top">
				<th scope="row"><label for="form-email-to"><?php esc_html_e( 'E-mail To', 'visual-form-builder' ); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-to" name="form_email_to[]" />
					<p class="description"><?php esc_html_e( 'Who to send the submitted data to. You can add more after creating the form.', 'visual-form-builder' ); ?></p>
				</td>
			</tr>

			</tbody>
		</table>
		<?php submit_button( esc_html__( 'Create Form', 'visual-form-builder' ) ); ?>
	</form>
		<?php
	}
}
