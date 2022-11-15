<?php
/**
 * Class that builds our Entries detail page
 *
 * @since 1.4
 */
class Visual_Form_Builder_Entries_Detail {
	/**
	 * [__construct description]
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'entries_detail' ) );
	}

	/**
	 * [entries_detail description]
	 */
	public function entries_detail() {
		global $wpdb;

		check_admin_referer( 'vfb_view_entry' );

		$entry_id = absint( $_GET['entry'] );

		$entries = $wpdb->get_results( $wpdb->prepare( 'SELECT forms.form_title, entries.* FROM ' . VFB_WP_FORMS_TABLE_NAME . ' AS forms INNER JOIN ' . VFB_WP_ENTRIES_TABLE_NAME . ' AS entries ON entries.form_id = forms.form_id WHERE entries.entries_id  = %d', $entry_id ) );

		// Get the date/time format that is saved in the options table.
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		// Loop trough the entries and setup the data to be displayed for each row.
		foreach ( $entries as $entry ) {
			$data = unserialize( $entry->data );
			?>
			<form id="entry-edit" method="post" action="">
				<h3><span><?php echo esc_html( $entry->form_title ); ?> : <?php esc_attr_e( 'Entry', 'visual-form-builder' ); ?> # <?php echo esc_html( $entry->entries_id ); ?></span></h3>
					<div id="vfb-poststuff" class="metabox-holder has-right-sidebar">
						<div id="side-info-column" class="inner-sidebar">
							<div id="side-sortables">
								<div id="submitdiv" class="postbox">
									<h3><span><?php esc_html_e( 'Details', 'visual-form-builder' ); ?></span></h3>
									<div class="inside">
										<div id="submitbox" class="submitbox">
											<div id="minor-publishing">
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Form Title', 'visual-form-builder' ); ?>: </strong><?php echo esc_html( $entry->form_title ); ?></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Date Submitted', 'visual-form-builder' ); ?>: </strong><?php echo esc_html( gmdate( "$date_format $time_format", strtotime( $entry->date_submitted ) ) ); ?></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'IP Address', 'visual-form-builder' ); ?>: </strong><?php echo esc_html( $entry->ip_address ); ?></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Email Subject', 'visual-form-builder' ); ?>: </strong><?php echo esc_html( $entry->subject ); ?></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Sender Name', 'visual-form-builder' ); ?>: </strong><?php echo esc_html( $entry->sender_name ); ?></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Sender Email', 'visual-form-builder' ); ?>: </strong><a href="mailto:<?php echo esc_html( $entry->sender_email ); ?>"><?php echo esc_html( $entry->sender_email ); ?></a></span>
													</div>
													<div class="misc-pub-section">
														<span><strong><?php esc_html_e( 'Emailed To', 'visual-form-builder' ); ?>: </strong><?php echo preg_replace( '/\b([A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i', '<a href="mailto:$1">$1</a>', esc_html( implode( ',', unserialize( wp_unslash( $entry->emails_to ) ) ) ) ); ?></span>
													</div>
													<div class="clear"></div>
												</div> <!--#misc-publishing-actions -->
											</div> <!-- #minor-publishing -->

											<div id="major-publishing-actions">
												<div id="delete-action">
													<?php
														printf(
															'<a class="submitdelete deletion entry-delete" href="%2$s&action=%3$s&entry=%4$d">%1$s</a>',
															esc_html__( 'Move to Trash', 'visual-form-builder' ),
															wp_nonce_url( admin_url( 'admin.php?page=vfb-entries' ), 'vfb_trash_entry' ),
															'trash',
															absint( $entry_id )
														);
													?>
												</div>
												<div id="publishing-action">
													<?php submit_button( esc_html__( 'Print', 'visual-form-builder' ), 'secondary', 'submit', false, array( 'onclick' => 'window.print();return false;' ) ); ?>
												</div>
												<div class="clear"></div>
											</div> <!-- #major-publishing-actions -->
										</div> <!-- #submitbox -->
									</div> <!-- .inside -->
								</div> <!-- #submitdiv -->
							</div> <!-- #side-sortables -->
						</div> <!-- #side-info-column -->

						<div id="vfb-entries-body-content">
			<?php
			$count         = 0;
			$open_fieldset = $open_section = false;

			foreach ( $data as $k => $v ) :
				if ( ! is_array( $v ) ) :
					if ( $count === 0 ) {
						echo '<div class="postbox">
							<h3><span>' . esc_html( $entry->form_title ) . ' : ' . esc_html__( 'Entry', 'visual-form-builder' ) . ' #' . esc_html( $entry->entries_id ) . '</span></h3>
							<div class="inside">';
					}

					printf( '<h4>%s</h4>', esc_html( ucwords( $k ) ) );
					echo esc_html( $v );
					$count++;
				else :
					// Cast each array as an object.
					$obj = (object) $v;

					if ( 'fieldset' === $obj->type ) :
						// Close each fieldset.
						if ( true === $open_fieldset ) {
							echo '</table>';
						}

						printf( '<h3>%s</h3><table class="form-table">', esc_html( $obj->name ) );

						$open_fieldset = true;
					endif;

					switch ( $obj->type ) {
						case 'fieldset':
						case 'section':
						case 'submit':
						case 'page-break':
						case 'verification':
						case 'secret':
							break;

						case 'file-upload':
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo esc_attr( $obj->id ); ?>]"><?php echo esc_html( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><a href="<?php esc_attr( $obj->value ); ?>" target="_blank"><?php echo esc_html( $obj->value ); ?></a></td>
							</tr>
							<?php
							break;

						case 'textarea':
						case 'html':
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo esc_attr( $obj->id ); ?>]"><?php echo esc_html( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><?php echo wpautop( esc_html( $obj->value ) ); ?></td>
							</tr>
							<?php
							break;

						default:
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo esc_attr( $obj->id ); ?>]"><?php echo esc_html( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><?php echo esc_html( $obj->value ); ?></td>
							</tr>
							<?php
							break;
					}
				endif;
			endforeach;

			if ( $count > 0 ) {
				echo '</div></div>';
			}
		}
		echo '</table></div>';
		echo '<br class="clear"></div>';

		echo '</form>';
	}
}
