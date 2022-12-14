<?php
/**
 * Handles the main email
 */
class Visual_Form_Builder_Email {
	/**
	 * Form_id
	 *
	 * @var    mixed
	 * @access protected
	 */
	protected $form_id;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Email function.
	 *
	 * @access public
	 * @return void
	 */
	public function email() {
		$form_id = $this->get_form_id();
		if ( ! $form_id ) {
			return;
		}

		// Save Form ID to pass to phpmailer().
		$this->form_id = $form_id;

		// Main Email.
		$this->notification( $form_id );

		/**
		 * Action that fires after all emails have been processed
		 *
		 * Passes the Entry ID and Form ID
		 */
		do_action( 'vfb_after_email', $form_id );
	}

	/**
	 * Send out main email
	 *
	 * @param   [type] $form_id  [$form_id description].
	 *
	 * @return  void
	 */
	public function notification( $form_id ) {
		global $wpdb;

		// Query to get all forms.
		$order = sanitize_sql_orderby( 'form_id DESC' );
		$form  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . VFB_WP_FORMS_TABLE_NAME . " WHERE form_id = %d ORDER BY $order", $form_id ) );

		$form_settings = (object) array(
			'form_title'                   => wp_unslash( html_entity_decode( $form->form_title, ENT_QUOTES, 'UTF-8' ) ),
			'form_subject'                 => wp_unslash( html_entity_decode( $form->form_email_subject, ENT_QUOTES, 'UTF-8' ) ),
			'form_to'                      => is_array( unserialize( $form->form_email_to ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) ),
			'form_from'                    => wp_unslash( $form->form_email_from ),
			'form_from_name'               => wp_unslash( $form->form_email_from_name ),
			'form_notification_setting'    => wp_unslash( $form->form_notification_setting ),
			'form_notification_email_name' => wp_unslash( $form->form_notification_email_name ),
			'form_notification_email_from' => wp_unslash( $form->form_notification_email_from ),
			'form_notification_subject'    => wp_unslash( html_entity_decode( $form->form_notification_subject, ENT_QUOTES, 'UTF-8' ) ),
			'form_notification_message'    => wp_unslash( $form->form_notification_message ),
			'form_notification_entry'      => wp_unslash( $form->form_notification_entry ),
		);
		// Allow the form settings to be filtered (ex: return $form_settings->'form_title' = 'Hello World';).
		$form_settings = (object) apply_filters_ref_array( 'vfb_email_form_settings', array( $form_settings, $form_id ) );

		// Get global settings.
		$vfb_settings = get_option( 'vfb-settings' );

		// Settings - Max Upload Size.
		$settings_max_upload = isset( $vfb_settings['max-upload-size'] ) ? $vfb_settings['max-upload-size'] : 25;

		// Settings - Spam word sensitivity.
		$settings_spam_points = isset( $vfb_settings['spam-points'] ) ? $vfb_settings['spam-points'] : 4;

		// Sender name field ID.
		$sender = $form->form_email_from_name_override;

		// Sender email field ID.
		$email = $form->form_email_from_override;

		// Notifcation email field ID.
		$notify = $form->form_notification_email;

		$reply_to_name  = $form_settings->form_from_name;
		$reply_to_email = $form_settings->form_from;

		// Use field for sender name.
		if ( ! empty( $sender ) && isset( $_POST[ 'vfb-' . $sender ] ) ) {
			$form_settings->form_from_name = wp_kses_data( wp_unslash( $_POST[ 'vfb-' . $sender ] ) );
			$reply_to_name                 = $form_settings->form_from_name;
		}

		// Use field for sender email.
		if ( ! empty( $email ) && isset( $_POST[ 'vfb-' . $email ] ) ) {
			$form_settings->form_from = sanitize_email( wp_unslash( $_POST[ 'vfb-' . $email ] ) );
			$reply_to_email           = $form_settings->form_from;
		}

		// Use field for copy email.
		$copy_email = ! empty( $notify ) && isset( $_POST[ 'vfb-' . $notify ] ) ? sanitize_email( wp_unslash( $_POST[ 'vfb-' . $notify ] ) ) : '';

		// Query to get all forms.
		$order  = sanitize_sql_orderby( 'field_sequence ASC' );
		$fields = $wpdb->get_results( $wpdb->prepare( 'SELECT field_id, field_key, field_name, field_type, field_options, field_parent, field_required FROM ' . VFB_WP_FIELDS_TABLE_NAME . " WHERE form_id = %d ORDER BY $order", $form_id ) );

		// Setup counter for alt rows.
		$i = $points = 0;

		// Setup HTML email vars.
		$header      = $body = $message = $footer = $html_email = $auto_response_email = '';
		$attachments = array();

		// Prepare the beginning of the content.
		$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		  <html>
		  <head>
		  <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
		  <title>HTML Email</title>
		  </head>
		  <body><table rules="all" style="border-color: #666;" cellpadding="10">' . "\n";

		// Loop through each form field and build the body of the message.
		foreach ( $fields as $field ) {
			// Handle attachments.
			if ( 'file-upload' === $field->field_type ) {
				$value = ( isset( $_FILES[ 'vfb-' . $field->field_id ] ) ) ? wp_unslash( $_FILES[ 'vfb-' . $field->field_id ] ) : '';

				if ( is_array( $value ) && $value['size'] > 0 ) {
					// 25MB is the max size allowed.
					$size            = apply_filters( 'vfb_max_file_size', $settings_max_upload );
					$max_attach_size = $size * 1048576;

					// Display error if file size has been exceeded.
					if ( $value['size'] > $max_attach_size ) {
						wp_die( sprintf( esc_html__( 'File size exceeds %dMB. Please decrease the file size and try again.', 'visual-form-builder' ), absint( $size ) ), '', array( 'back_link' => true ) );
					}

					// Options array for the wp_handle_upload function. 'test_form' => false.
					$upload_overrides = array( 'test_form' => false );

					// We need to include the file that runs the wp_handle_upload function.
					include_once ABSPATH . 'wp-admin/includes/file.php';

					// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array.
					$uploaded_file = wp_handle_upload( $value, $upload_overrides );

					// If the wp_handle_upload call returned a local path for the image.
					if ( isset( $uploaded_file['file'] ) ) {
						// Retrieve the file type from the file name. Returns an array with extension and mime type.
						$wp_filetype = wp_check_filetype( basename( $uploaded_file['file'] ), null );

						// Return the current upload directory location.
						$wp_upload_dir = wp_upload_dir();

						$media_upload = array(
							'guid'           => $wp_upload_dir['url'] . '/' . basename( $uploaded_file['file'] ),
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						// Insert attachment into Media Library and get attachment ID.
						$attach_id = wp_insert_attachment( $media_upload, $uploaded_file['file'] );

						// Include the file that runs wp_generate_attachment_metadata().
						include_once ABSPATH . 'wp-admin/includes/image.php';
						include_once ABSPATH . 'wp-admin/includes/media.php';

						// Setup attachment metadata.
						$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );

						// Update the attachment metadata.
						wp_update_attachment_metadata( $attach_id, $attach_data );

						$attachments[ 'vfb-' . $field->field_id ] = $uploaded_file['file'];

						$data[] = array(
							'id'        => $field->field_id,
							'slug'      => $field->field_key,
							'name'      => $field->field_name,
							'type'      => $field->field_type,
							'options'   => $field->field_options,
							'parent_id' => $field->field_parent,
							'value'     => $uploaded_file['url'],
						);

						$body .= sprintf(
							'<tr>
							<td><strong>%1$s: </strong></td>
							<td><a href="%2$s">%2$s</a></td>
							</tr>' . "\n",
							esc_html( wp_unslash( $field->field_name ) ),
							$uploaded_file['url']
						);
					}
				} else {
					$value = ( isset( $_POST[ 'vfb-' . $field->field_id ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'vfb-' . $field->field_id ] ) ) : '';
					$body .= sprintf(
						'<tr>
						<td><strong>%1$s: </strong></td>
						<td>%2$s</td>
						</tr>' . "\n",
						esc_html( wp_unslash( $field->field_name ) ),
						$value
					);
				}
			} else {
				$value = ( isset( $_POST[ 'vfb-' . $field->field_id ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'vfb-' . $field->field_id ] ) ) : '';

				// If time field, build proper output.
				if ( is_array( $value ) && 'time' === $field->field_type ) {
					$value = $this->format_field( $value, $field->field_type );
				} elseif ( is_array( $value ) && 'address' === $field->field_type ) {
					// If address field, build proper output.
					$value = $this->format_field( $value, $field->field_type );
				} elseif ( is_array( $value ) ) {
					// If multiple values, build the list.
					$value = $this->format_field( $value, $field->field_type );
				} elseif ( 'radio' === $field->field_type ) {
					$value = wp_specialchars_decode( wp_unslash( esc_html( $value ) ), ENT_QUOTES );
				} else {
					$value = html_entity_decode( wp_unslash( esc_html( $value ) ), ENT_QUOTES, 'UTF-8' ); // Lastly, handle single values.
				}

				// Spam Words - Exploits.
				$exploits = array( 'content-type', 'bcc:', 'cc:', 'document.cookie', 'onclick', 'onload', 'javascript', 'alert' );
				$exploits = apply_filters( 'vfb_spam_words_exploits', $exploits, $form_id );

				// Spam Words - Exploits.
				$profanity = array( 'beastial', 'bestial', 'blowjob', 'clit', 'cock', 'cum', 'cunilingus', 'cunillingus', 'cunnilingus', 'cunt', 'ejaculate', 'fag', 'felatio', 'fellatio', 'fuck', 'fuk', 'fuks', 'gangbang', 'gangbanged', 'gangbangs', 'hotsex', 'jism', 'jiz', 'kock', 'kondum', 'kum', 'kunilingus', 'orgasim', 'orgasims', 'orgasm', 'orgasms', 'phonesex', 'phuk', 'phuq', 'porn', 'pussies', 'pussy', 'spunk', 'xxx' );
				$profanity = apply_filters( 'vfb_spam_words_profanity', $profanity, $form_id );

				// Spam Words - Misc.
				$spamwords = array( 'viagra', 'phentermine', 'tramadol', 'adipex', 'advai', 'alprazolam', 'ambien', 'ambian', 'amoxicillin', 'antivert', 'blackjack', 'backgammon', 'holdem', 'poker', 'carisoprodol', 'ciara', 'ciprofloxacin', 'debt', 'dating', 'porn' );
				$spamwords = apply_filters( 'vfb_spam_words_misc', $spamwords, $form_id );

				// Add up points for each spam hit.
				if ( preg_match( '/(' . implode( '|', $exploits ) . ')/i', $value ) ) {
					$points += 2;
				} elseif ( preg_match( '/(' . implode( '|', $profanity ) . ')/i', $value ) ) {
					++$points;
				} elseif ( preg_match( '/(' . implode( '|', $spamwords ) . ')/i', $value ) ) {
					++$points;
				}

				// Sanitize input.
				$value = $this->sanitize_input( $value, $field->field_type );
				// Validate input.
				$this->validate_input( $value, $field->field_name, $field->field_type, $field->field_required );

				$removed_field_types = array( 'verification', 'secret', 'submit' );

				// Don't add certain fields to the email.
				if ( ! in_array( $field->field_type, $removed_field_types, true ) ) {
					if ( 'fieldset' === $field->field_type ) {
						$body .= sprintf(
							'<tr style="background-color:#393E40;color:white;font-size:14px;">
							<td colspan="2">%1$s</td>
							</tr>' . "\n",
							wp_unslash( $field->field_name )
						); } elseif ( 'section' === $field->field_type ) {
						$body .= sprintf(
							'<tr style="background-color:#6E7273;color:white;font-size:14px;">
							<td colspan="2">%1$s</td>
							</tr>' . "\n",
							wp_unslash( $field->field_name )
						); } else {
							// Convert new lines to break tags for textarea in html.
							$display_value = ( 'textarea' == $field->field_type ) ? nl2br( $value ) : $value;

							$body .= sprintf(
								'<tr>
								<td><strong>%1$s: </strong></td>
								<td>%2$s</td>
								</tr>' . "\n",
								wp_unslash( $field->field_name ),
								$display_value
							);
						}
				}

				$data[] = array(
					'id'        => $field->field_id,
					'slug'      => $field->field_key,
					'name'      => $field->field_name,
					'type'      => $field->field_type,
					'options'   => $field->field_options,
					'parent_id' => $field->field_parent,
					'value'     => esc_html( $value ),
				);
			}

			// If the user accumulates more than 4 points, it might be spam.
			if ( $points > $settings_spam_points ) {
				wp_die( esc_html__( 'Your responses look too much like spam and could not be sent at this time.', 'visual-form-builder' ), '', array( 'back_link' => true ) );
			}
		}

		// Setup our entries data.
		$entry = array(
			'form_id'        => $form_id,
			'data'           => serialize( $data ),
			'subject'        => $form_settings->form_subject,
			'sender_name'    => $form_settings->form_from_name,
			'sender_email'   => $form_settings->form_from,
			'emails_to'      => serialize( $form_settings->form_to ),
			'date_submitted' => date_i18n( 'Y-m-d H:i:s' ),
			'ip_address'     => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		);

		// Settings - Disable Saving Entries.
		$settings_disable_saving = isset( $vfb_settings['disable-saving-entries'] ) ? $vfb_settings['disable-saving-entries'] : '';

		// Insert this data into the entries table if setting is not set.
		if ( empty( $settings_disable_saving ) ) {
			$wpdb->insert( VFB_WP_ENTRIES_TABLE_NAME, $entry );
		}

		// Close out the content.
		$footer .= '<tr>
		<td class="footer" height="61" align="left" valign="middle" colspan="2">
		<p style="font-size: 12px; font-weight: normal; margin: 0; line-height: 16px; padding: 0;">This email was built and sent using <a href="http://wordpress.org/extend/plugins/visual-form-builder/" style="font-size: 12px;">Visual Form Builder</a>.</p>
		</td>
		</tr>
		</table>
		</body>
		</html>' . "\n";

		// Build complete HTML email.
		$message = $header . $body . $footer;

		// Wrap lines longer than 70 words to meet email standards.
		$message = wordwrap( $message, 70 );

		// Decode HTML for message so it outputs properly.
		$notify_message = ! empty( $form_settings->form_notification_message ) ? html_entity_decode( $form_settings->form_notification_message ) : '';

		// Initialize header filter vars.
		$header_from_name    = function_exists( 'mb_encode_mimeheader' ) ? mb_encode_mimeheader( wp_unslash( $reply_to_name ) ) : wp_unslash( $reply_to_name );
		$header_from         = $reply_to_email;
		$header_content_type = 'text/html';

		// Either prepend the notification message to the submitted entry, or send by itself.
		if ( ! empty( $form_settings->form_notification_entry ) ) {
			$auto_response_email = $header . $notify_message . $body . $footer;
		} else {
			$auto_response_email = sprintf(
				'%1$s<table cellspacing="0" border="0" cellpadding="0" width="100%%"><tr><td colspan="2" class="mainbar" align="left" valign="top" width="600">%2$s</td></tr>%3$s',
				$header,
				$notify_message,
				$footer
			);
		}

		// Build email headers.
		$from_name = empty( $header_from_name ) ? 'WordPress' : $header_from_name;

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
		$from_email = ( $sitename == $domain ) ? $from_email : "wordpress@$sitename";

		// Settings - Sender Mail Header.
		$settings_sender_header = isset( $vfb_settings['sender-mail-header'] ) ? $vfb_settings['sender-mail-header'] : $from_email;

		// Allow Sender email to be filtered.
		$from_email = apply_filters( 'vfb_sender_mail_header', $settings_sender_header, $form_id );

		$reply_to  = "\"$from_name\" <$header_from>";
		$headers[] = "Sender: $from_email";
		$headers[] = "From: $reply_to";
		$headers[] = "Reply-To: $reply_to";
		$headers[] = "Content-Type: $header_content_type; charset=\"" . get_option( 'blog_charset' ) . '"';

		$form_subject   = wp_specialchars_decode( $form_settings->form_subject, ENT_QUOTES );
		$notify_subject = wp_specialchars_decode( $form_settings->form_notification_subject, ENT_QUOTES );

		// Sanitize main emails_to.
		$emails_to = array_map( 'sanitize_email', $form_settings->form_to );

		// Send the mail.
		foreach ( $emails_to as $email ) {
			wp_mail( $email, $form_subject, $message, $headers, $attachments );
		}

		// Send auto-responder email.
		if ( ! empty( $form_settings->form_notification_setting ) ) {
			$attachments = ! empty( $form_settings->form_notification_entry ) ? $attachments : '';

			// Reset headers for notification email.
			$reply_name  = function_exists( 'mb_encode_mimeheader' ) ? mb_encode_mimeheader( stripslashes( $form_settings->form_notification_email_name ) ) : wp_unslash( $form_settings->form_notification_email_name );
			$reply_email = $form_settings->form_notification_email_from;
			$reply_to    = "\"$reply_name\" <$reply_email>";
			$headers[]   = "Sender: $from_email";
			$headers[]   = "From: $reply_to";
			$headers[]   = "Reply-To: $reply_to";
			$headers[]   = "Content-Type: $header_content_type; charset=\"" . get_option( 'blog_charset' ) . '"';

			// Send the mail.
			wp_mail( $copy_email, $notify_subject, $auto_response_email, $headers, $attachments );
		}
	}

	/**
	 * [format_field description]
	 *
	 * @param  [type] $value [description].
	 * @param  string $type  [description].
	 * @return [type]        [description]
	 */
	public function format_field( $value, $type = '' ) {
		$output = '';

		// Basic check for type when not set.
		if ( empty( $type ) ) {
			if ( is_array( $value ) && array_key_exists( 'address', $value ) ) {
				$type = 'address';
			} elseif ( is_array( $value ) && array_key_exists( 'hour', $value ) && array_key_exists( 'min', $value ) ) {
				$type = 'time';
			} elseif ( is_array( $value ) ) {
				$type = 'checkbox';
			} else {
				$type = 'default';
			}
		}

		// Build array'd form item output.
		switch ( $type ) {

			case 'time':
				$output = ( array_key_exists( 'ampm', $value ) ) ? substr_replace( implode( ':', $value ), ' ', 5, 1 ) : implode( ':', $value );
				break;

			case 'address':
				if ( ! empty( $value['address'] ) ) {
					$output .= $value['address'];
				}

				if ( ! empty( $value['address-2'] ) ) {
					if ( ! empty( $output ) ) {
						$output .= '<br>';
					}
					$output .= $value['address-2'];
				}

				if ( ! empty( $value['city'] ) ) {
					if ( ! empty( $output ) ) {
						$output .= '<br>';
					}
					$output .= $value['city'];
				}
				if ( ! empty( $value['state'] ) ) {
					if ( ! empty( $output ) && empty( $value['city'] ) ) {
						$output .= '<br>';
					} elseif ( ! empty( $output ) && ! empty( $value['city'] ) ) {
						$output .= ', ';
					}
					$output .= $value['state'];
				}
				if ( ! empty( $value['zip'] ) ) {
					if ( ! empty( $output ) && ( empty( $value['city'] ) && empty( $value['state'] ) ) ) {
						$output .= '<br>';
					} elseif ( ! empty( $output ) && ( ! empty( $value['city'] ) || ! empty( $value['state'] ) ) ) {
						$output .= ' ';
					}
					$output .= $value['zip'];
				}
				if ( ! empty( $value['country'] ) ) {
					if ( ! empty( $output ) ) {
						$output .= '<br>';
					}
					$output .= $value['country'];
				}

				break;

			case 'checkbox':
				$output = esc_html( implode( ', ', $value ) );
				break;

			default:
				$output = wp_specialchars_decode( wp_unslash( esc_html( $value ) ), ENT_QUOTES );
				break;
		}

		return $output;
	}

	/**
	 * Validate the input
	 *
	 * @param   [type] $data      [$data description].
	 * @param   [type] $name      [$name description].
	 * @param   [type] $type      [$type description].
	 * @param   [type] $required  [$required description].
	 *
	 * @return  [type]             [return description]
	 */
	public function validate_input( $data, $name, $type, $required ) {
		if ( 'yes' === $required && strlen( $data ) === 0 ) {
			wp_die( esc_html( "<h1>$name</h1><br>" ) . esc_html__( 'This field is required and cannot be empty.', 'visual-form-builder' ), esc_html( $name ), array( 'back_link' => true ) );
		}

		if ( strlen( $data ) > 0 ) {
			switch ( $type ) {
				case 'email':
					if ( ! is_email( $data ) ) {
						wp_die( esc_html( "<h1>$name</h1><br>" ) . esc_html__( 'Not a valid email address', 'visual-form-builder' ), '', array( 'back_link' => true ) );
					}

					break;

				case 'number':
				case 'currency':
					if ( ! is_numeric( $data ) ) {
						wp_die( esc_html( "<h1>$name</h1><br>" ) . esc_html__( 'Not a valid number', 'visual-form-builder' ), '', array( 'back_link' => true ) );
					}

					break;

				case 'phone':
					if ( strlen( $data ) > 9 && preg_match( '/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/', $data ) ) {
						return true;
					} else {
						wp_die( esc_html( "<h1>$name</h1><br>" ) . esc_html__( 'Not a valid phone number. Most US/Canada and International formats accepted.', 'visual-form-builder' ), '', array( 'back_link' => true ) );
					}

					break;

				case 'url':
					if ( ! preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data ) ) {
						wp_die( esc_html( "<h1>$name</h1><br>" ) . esc_html__( 'Not a valid URL.', 'visual-form-builder' ), '', array( 'back_link' => true ) );
					}

					break;
			}

			return true;
		}
	}

	/**
	 * Sanitize the input
	 *
	 * @param   [type] $data  [$data description].
	 * @param   [type] $type  [$type description].
	 *
	 * @return  [type]         [return description]
	 */
	public function sanitize_input( $data, $type ) {
		if ( strlen( $data ) > 0 ) {
			switch ( $type ) {
				case 'text':
					return sanitize_text_field( $data );
				break;

				case 'textarea':
					return wp_strip_all_tags( $data );
				break;

				case 'email':
					return sanitize_email( $data );
				break;

				case 'html':
					return wp_kses_data( force_balance_tags( $data ) );
				break;

				case 'min':
				case 'max':
				case 'digits':
					return preg_replace( '/\D/i', '', $data );
				break;

				case 'address':
					$allowed_html = array( 'br' => array() );
					return wp_kses( $data, $allowed_html );
				break;
			}

			return wp_kses_data( $data );
		}
	}

	/**
	 * Get form ID
	 *
	 * @access private
	 * @return int
	 */
	private function get_form_id() {
		if ( ! isset( $_POST['form_id'] ) ) {
			return false;
		}

		return (int) $_POST['form_id'];
	}

	/**
	 * Basic check to exit if the form hasn't been submitted
	 *
	 * @access public
	 * @return void
	 */
	public function submit_check() {
		// If form ID hasn't been submitted by $_POST, exit.
		if ( ! $this->get_form_id() ) {
			return;
		}

		// If form ID hasn't been submitted by $_POST, exit.
		if ( ! isset( $_POST['vfb-submit'] ) ) {
			return;
		}

		return true;
	}
}
