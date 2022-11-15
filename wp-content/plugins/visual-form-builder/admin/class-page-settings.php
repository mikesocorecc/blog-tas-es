<?php

/**
 * Class that controls the Settings page view
 */
class Visual_Form_Builder_Page_Settings {
	/**
	 * [display description]
	 *
	 * @return void
	 */
	public function display() {
		$vfb_settings = get_option( 'vfb-settings' );
		?>
<div class="wrap">
	<h2><?php esc_html_e( 'Settings', 'visual-form-builder' ); ?></h2>
	<form id="vfb-settings" method="post">
		<input name="vfb-action" type="hidden" value="vfb_settings" />
			<?php wp_nonce_field( 'vfb-update-settings' ); ?>
		<h3><?php esc_html_e( 'Global Settings', 'visual-form-builder' ); ?></h3>
		<p><?php esc_html_e( 'These settings will affect all forms on your site.', 'visual-form-builder' ); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'CSS', 'visual-form-builder' ); ?></th>
				<td>
					<fieldset>
					<?php
						$disable = array(
							'always-load-css' => esc_html__( 'Always load CSS', 'visual-form-builder' ),
							'disable-css'     => esc_html__( 'Disable CSS', 'visual-form-builder' ),
						);

						foreach ( $disable as $key => $title ) :

							$vfb_settings[ $key ] = isset( $vfb_settings[ $key ] ) ? $vfb_settings[ $key ] : '';
							?>
						<label for="vfb-settings-<?php echo esc_attr( $key ); ?>">
							<input type="checkbox" name="vfb-settings[<?php echo esc_attr( $key ); ?>]" id="vfb-settings-<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $vfb_settings[ $key ], 1 ); ?> /> <?php echo esc_html( $title ); ?>
						</label>
						<br>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Form Output', 'visual-form-builder' ); ?></th>
				<td>
					<fieldset>
					<?php
						$disable = array(
							'address-labels' => esc_html__( 'Place Address labels above fields', 'visual-form-builder' ),
						);

						foreach ( $disable as $key => $title ) :

							$vfb_settings[ $key ] = isset( $vfb_settings[ $key ] ) ? $vfb_settings[ $key ] : '';
							?>
						<label for="vfb-settings-<?php echo esc_attr( $key ); ?>">
							<input type="checkbox" name="vfb-settings[<?php echo esc_attr( $key ); ?>]" id="vfb-settings-<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $vfb_settings[ $key ], 1 ); ?> /> <?php echo esc_html( $title ); ?>
						</label>
						<br>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Disable Saving Entries', 'visual-form-builder' ); ?></th>
				<td>
					<fieldset>
					<?php
						$disable = array(
							'disable-saving-entries' => esc_html__( 'Disables saving entry data for each submission after all emails have been sent.', 'visual-form-builder' ),
						);

						foreach ( $disable as $key => $title ) :
							$vfb_settings[ $key ] = isset( $vfb_settings[ $key ] ) ? $vfb_settings[ $key ] : '';
							?>
						<label for="vfb-settings-<?php echo esc_attr( $key ); ?>">
							<input type="checkbox" name="vfb-settings[<?php echo esc_attr( $key ); ?>]" id="vfb-settings-<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $vfb_settings[ $key ], 1 ); ?> /> <?php echo esc_html( $title ); ?>
						</label>
						<br>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="vfb-settings-spam-points"><?php esc_html_e( 'Spam word sensitivity', 'visual-form-builder' ); ?></label></th>
				<td>
					<?php $vfb_settings['spam-points'] = isset( $vfb_settings['spam-points'] ) ? $vfb_settings['spam-points'] : '4'; ?>
					<input type="number" min="1" name="vfb-settings[spam-points]" id="vfb-settings-spam-points" value="<?php echo esc_attr( $vfb_settings['spam-points'] ); ?>" class="small-text" />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="vfb-settings-max-upload-size"><?php esc_html_e( 'Max Upload Size', 'visual-form-builder' ); ?></label></th>
				<td>
					<?php $vfb_settings['max-upload-size'] = isset( $vfb_settings['max-upload-size'] ) ? $vfb_settings['max-upload-size'] : '25'; ?>
					<input type="number" name="vfb-settings[max-upload-size]" id="vfb-settings-max-upload-size" value="<?php echo esc_attr( $vfb_settings['max-upload-size'] ); ?>" class="small-text" /> MB
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="vfb-settings-sender-mail-header"><?php esc_html_e( 'Sender Mail Header', 'visual-form-builder' ); ?></label></th>
				<td>
					<?php
					// Use the admin_email as the From email.
					$from_email = get_option( 'admin_email' );

					// Get the site domain and get rid of www.
					$sitename = isset( $_SERVER['SERVER_NAME'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) : 'localhost';
					if ( substr( $sitename, 0, 4 ) === 'www.' ) {
							$sitename = substr( $sitename, 4 );
					}

					// Get the domain from the admin_email.
					list( $user, $domain ) = explode( '@', $from_email );

					// If site domain and admin_email domain match, use admin_email, otherwise a same domain email must be created.
					$from_email = ( $sitename === $domain ) ? $from_email : "wordpress@$sitename";

					$vfb_settings['sender-mail-header'] = isset( $vfb_settings['sender-mail-header'] ) ? $vfb_settings['sender-mail-header'] : $from_email;
					?>
					<input type="text" name="vfb-settings[sender-mail-header]" id="vfb-settings-sender-mail-header" value="<?php echo esc_attr( $vfb_settings['sender-mail-header'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Some server configurations require an existing email on the domain be used when sending emails.', 'visual-form-builder' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="vfb-notices vfb-notice-danger" style="width: 50%;">
			<h3><?php esc_html_e( 'Uninstall Visual Form Builder', 'visual-form-builder' ); ?></h3>
			<p><?php esc_html_e( 'Running this uninstall process will delete all Visual Form Builder data for this site. This process cannot be reversed.', 'visual-form-builder' ); ?></p>
				<?php
					submit_button(
						esc_html__( 'Uninstall', 'visual-form-builder' ),
						'delete',
						'visual-form-builder-uninstall',
						false
					);
				?>
		</div> <!-- .vfb-notices -->

			<?php submit_button( esc_html__( 'Save', 'visual-form-builder' ), 'primary', 'submit', false ); ?>
	</form>
</div> <!-- .wrap -->
			<?php
	}
}
