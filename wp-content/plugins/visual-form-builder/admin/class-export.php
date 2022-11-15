<?php
/**
 * Class that controls the Export page view
 */
class Visual_Form_Builder_Export {

	/**
	 * Default delimiter for CSV and Tab export
	 *
	 * Override using the vfb_csv_delimiter filter
	 *
	 * (default value: ',')
	 *
	 * @var    string
	 * @access protected
	 */
	protected $delimiter = ',';

	/**
	 * Default_cols
	 *
	 * @var    mixed
	 * @access public
	 */
	public $default_cols;

	/**
	 * __construct function
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->delimiter = apply_filters( 'vfb_csv_delimiter', ',' );

		// Setup our default columns.
		$this->default_cols = array(
			'entries_id'     => esc_html__( 'Entries ID', 'visual-form-builder' ),
			'date_submitted' => esc_html__( 'Date Submitted', 'visual-form-builder' ),
			'ip_address'     => esc_html__( 'IP Address', 'visual-form-builder' ),
			'subject'        => esc_html__( 'Subject', 'visual-form-builder' ),
			'sender_name'    => esc_html__( 'Sender Name', 'visual-form-builder' ),
			'sender_email'   => esc_html__( 'Sender Email', 'visual-form-builder' ),
			'emails_to'      => esc_html__( 'Emailed To', 'visual-form-builder' ),
		);

		add_action( 'admin_init', array( $this, 'export_action' ) );
		add_action( 'wp_ajax_vfb-export-fields', array( $this, 'load_fields' ) );
	}

	/**
	 * Display function.
	 *
	 * @access public
	 * @return void
	 */
	public function display() {
		$forms = $this->get_all_forms();
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Export', 'visual-form-builder' ); ?></h2>
			<form method="post" id="vfbp-export" action="">
				<input name="_vfb_action" type="hidden" value="export" />
				<?php wp_nonce_field( 'vfb_export' ); ?>

				<p><?php esc_html_e( 'Backup and save some or all of your Visual Form Builder data.', 'visual-form-builder' ); ?></p>
				<p><?php esc_html_e( 'Once you have saved the file, you will be able to import Visual Form Builder Pro data from this site into another site.', 'visual-form-builder' ); ?></p>
				<h3><?php esc_html_e( 'Choose what to export', 'visual-form-builder' ); ?></h3>

				<p>
					<label for="content-forms">
						<input type="radio" id="content-forms" name="settings[content]" value="forms" disabled="disabled" /> <?php esc_html_e( 'Forms', 'visual-form-builder' ); ?>
					</label>
				</p>
				<p class="description"><?php esc_html_e( 'This will export a single form with all fields and settings for that form.', 'visual-form-builder' ); ?><br><strong>*<?php esc_html_e( 'Only available in VFB Pro', 'visual-form-builder' ); ?>*</strong></p>

				<p>
					<label for="content-entries">
						<input type="radio" id="content-entries" name="settings[content]" value="entries" checked="checked" /> <?php esc_html_e( 'Entries', 'visual-form-builder' ); ?>
					</label>
				</p>
				<p class="description"><?php esc_html_e( 'This will export entries in either .csv, .txt, or .xls and cannot be used with the Import.', 'visual-form-builder' ); ?></p>

				<h3><?php esc_html_e( 'Select a form', 'vfb-pro' ); ?></h3>
				<select name="settings[form-id]" id="vfb-export-forms-list">
					<?php
					$first_form    = '';
					$entries_count = 0;

					if ( is_array( $forms ) && ! empty( $forms ) ) {
						$first_form = $forms[0];

						foreach ( $forms as $form ) {
							printf(
								'<option value="%1$d">%1$d - %2$s</option>',
								esc_html( $form['form_id'] ),
								esc_html( $form['form_title'] )
							);
						}
					}
					?>
				</select>

				<div class="vfb-export-entries-options">
					<h3><?php esc_html_e( 'Customize your export', 'visual-form-builder' ); ?></h3>

					<p>
						<label class="vfb-export-label" for="format"><?php esc_html_e( 'Format:', 'visual-form-builder' ); ?></label>
						<select name="settings[format]">
							<option value="csv" selected="selected"><?php esc_html_e( 'Comma Separated (.csv)', 'visual-form-builder' ); ?></option>
							<option value="txt" disabled="disabled"><?php esc_html_e( 'Tab Delimited (.txt)', 'visual-form-builder' ); ?></option>
							<option value="xls" disabled="disabled"><?php esc_html_e( 'Excel (.xls)', 'visual-form-builder' ); ?></option>
						</select>
					</p>

					<p>
					<label class="vfb-export-label" for="start-date"><?php esc_html_e( 'Date Range:', 'visual-form-builder' ); ?></label>
						<select name="settings[start-date]">
							<option value="0">Start Date</option>
							<?php $this->months_dropdown(); ?>
						</select>
						<select name="settings[end-date]">
								<option value="0">End Date</option>
								<?php $this->months_dropdown(); ?>
						</select>
					</p>

					<label class="vfb-export-label"><?php esc_html_e( 'Fields:', 'visual-form-builder' ); ?></label>

					<p>
						<a id="vfb-export-select-all" href="#"><?php esc_html_e( 'Select All', 'visual-form-builder' ); ?></a>
						<a id="vfb-export-unselect-all" href="#"><?php esc_html_e( 'Unselect All', 'visual-form-builder' ); ?></a>
					</p>

					<div id="vfb-export-entries-fields">
						<?php $this->fields_list( $first_form['form_id'] ); ?>
					</div>
				</div>

				<?php
				submit_button(
					esc_html__( 'Download Export File', 'visual-form-builder' ),
					'primary',
					'' // leave blank so "name" attribute will not be added.
				);
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Determine which export function to execute based on selected options
	 *
	 * @access public
	 * @return void
	 */
	public function export_action() {
		if ( ! isset( $_POST['_vfb_action'] ) || ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( 'export' !== $_POST['_vfb_action'] ) {
			return;
		}

		check_admin_referer( 'vfb_export' );

		$data = array();

		if ( isset( $_POST['settings'] ) ) {
			// Sanitized below.
			foreach ( wp_unslash( $_POST['settings'] ) as $key => $val ) {
				$data[ $key ] = $val;
			}
		}

		$data       = stripslashes_deep( $data );
		$content    = isset( $data['content'] ) ? $data['content'] : 'forms';
		$form_id    = isset( $data['form-id'] ) ? absint( $data['form-id'] ) : 0;
		$format     = isset( $data['format'] ) ? sanitize_text_field( $data['format'] ) : 'csv';
		$start_date = isset( $data['start-date'] ) ? sanitize_text_field( $data['start-date'] ) : '';
		$end_date   = isset( $data['end-date'] ) ? sanitize_text_field( $data['end-date'] ) : '';
		$fields     = isset( $data['fields'] ) ? $data['fields'] : '';

		if ( 0 == $form_id ) {
			return;
		}

		switch ( $content ) {
			case 'entries':
				// If no fields selected, exit because there's nothing to do.
				if ( empty( $fields ) ) {
					return;
				}

				global $wpdb;
				$where = '';

				if ( 0 !== $form_id ) {
					$where .= $wpdb->prepare( ' AND form_id = %d', $form_id );
				}

				if ( $start_date ) {
					$where .= $wpdb->prepare( ' AND date_submitted >= %s', gmdate( 'Y-m-d', strtotime( $start_date ) ) );
				}

				if ( $end_date ) {
					$where .= $wpdb->prepare( ' AND date_submitted < %s', gmdate( 'Y-m-d', strtotime( '+1 month', strtotime( $end_date ) ) ) );
				}

				$title = $wpdb->get_var( null, 1 );

				$settings['format'] = $format;
				$settings['fields'] = $fields;
				$settings['where']  = $where;

				$this->export_entries( $settings, $title );

				die( 1 );

			break;
		}
	}

	/**
	 * [export_entries description]
	 *
	 * @param [type] $data  [$data description].
	 * @param array  $title [$title description].
	 *
	 * @return [type]         [return description]
	 */
	public function export_entries( $data = array(), $title = '' ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		$format = $data['format'];

		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$filename = "{$sitename}vfb-export.{$title}." . gmdate( 'Y-m-d-Hi' ) . ".{$format}";

		// Set content type based on file format.
		switch ( $format ) {
			case 'csv':
				$content_type = 'text/csv';
				break;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = trailingslashit( $upload_dir['path'] ) . $filename;

		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( "Content-Type: $content_type; charset=" . get_option( 'blog_charset' ), true );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		if ( in_array( $format, array( 'csv', 'txt' ), true ) ) {
			$this->csv_tab( $data['fields'], $data['where'], $format, $file_path );
		}
	}

	/**
	 * [csv_tab description]
	 *
	 * @param [type] $fields    [$fields description].
	 * @param [type] $where     [$where description].
	 * @param [type] $format    [$format description].
	 * @param [type] $file_path [$file_path description].
	 *
	 * @return [type]              [return description]
	 */
	public function csv_tab( $fields, $where, $format, $file_path ) {
		global $wpdb;
		$file = fopen( $file_path, 'w' );

		$headers = $rows = array();
		$entries = $wpdb->get_results( 'SELECT * FROM ' . VFB_WP_ENTRIES_TABLE_NAME . " WHERE entry_approved = 1 $where ORDER BY entries_id ASC" );

		// Get columns.
		$columns = $this->get_cols( $entries );

		// Get JSON data.
		$json = json_decode( $columns, true );

		$rows = $fields_clean = $fields_header = array();

		// Decode special characters.
		foreach ( $fields as $field ) {
			// Strip unique ID for a clean header.
			$search          = preg_replace( '/{{(\d+)}}/', '', $field );
			$fields_header[] = wp_specialchars_decode( $search, ENT_QUOTES );

			// Field with unique ID to use as matching data.
			$fields_clean[] = wp_specialchars_decode( $field, ENT_QUOTES );
		}

		// Build headers.
		fputcsv( $file, $fields_header, $this->delimiter );

		// Build table rows and cells.
		foreach ( $json as $row ) {
			foreach ( $fields_clean as $label ) {
				$label = wp_specialchars_decode( $label );
				// Prepend a space to prevent CSV injection attacks.
				$value = ' ' . wp_specialchars_decode( $row[ $label ] );

				$rows[ $label ] = isset( $row[ $label ] ) && in_array( $label, $fields_clean, true ) ? $value : '';
			}

			fputcsv( $file, $rows, $this->delimiter );
		}

		// Close the file.
		fclose( $file );

		// Reads file in uploads folder and writes to output buffer.
		readfile( $file_path );

		// Delete export file.
		wp_delete_file( $file_path );

		exit();
	}

	/**
	 * [fields_list description]
	 *
	 * @param [type] $form_id [$form_id description].
	 *
	 * @return [type]            [return description]
	 */
	public function fields_list( $form_id ) {
		$entries       = $this->get_entries( $form_id );
		$entries_count = $this->get_entries_count( $form_id );

		if ( 0 == $entries_count ) {
				return esc_html_e( 'No entries.', 'visual-form-builder' );
		}

		if ( is_array( $entries ) && ! empty( $entries ) ) {
			$columns = $this->get_cols( $entries );
			$data    = json_decode( $columns, true );

			$output = '';

			$array = array();
			foreach ( $data as $row ) {
					$array = array_merge( $row, $array );
			}

			$array = array_keys( $array );
			$array = array_values( array_merge( $this->default_cols, $array ) );
			$array = array_map( 'stripslashes', $array );

			foreach ( $array as $id => $value ) {
				$selected = in_array( $value, $this->default_cols, true ) ? ' checked="checked"' : '';

				// Strip unique ID for a clean list.
				$search = preg_replace( '/{{(\d+)}}/', '', $value );
				?>
				<label for="vfb-export-fields-val-<?php echo esc_attr( $id ); ?>">
				<input 
					name="settings[fields][<?php echo esc_attr( $id ); ?>]" 
					class="vfb-export-fields-vals" 
					id="vfb-export-fields-val-<?php echo esc_attr( $id ); ?>" 
					type="checkbox" 
					value="<?php echo esc_attr( $value ); ?>" 
					<?php echo esc_html( $selected ); ?>
				> 
				<?php echo esc_html( $search ); ?>
				</label><br>
				<?php
			}

			return $output;
		}
	}

	/**
	 * [get_entries description]
	 *
	 * @param [type] $form_id [$form_id description].
	 *
	 * @return [type]            [return description]
	 */
	public function get_entries( $form_id ) {
		global $wpdb;

		$entries = $wpdb->get_results( $wpdb->prepare( 'SELECT data FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' WHERE form_id = %d AND entry_approved = 1', $form_id ), ARRAY_A );
		return $entries;
	}

	/**
	 * Build the entries as JSON
	 *
	 * @since 1.7
	 *
	 * @param array $entries The resulting database query for entries.
	 */
	public function get_cols( $entries ) {
		// Initialize row index at 0.
		$row    = 0;
		$output = array();

		// Loop through all entries.
		foreach ( $entries as $entry ) {
			foreach ( $entry as $key => $value ) {
				switch ( $key ) {
					case 'entries_id':
					case 'date_submitted':
					case 'ip_address':
					case 'subject':
					case 'sender_name':
					case 'sender_email':
						$output[ $row ][ stripslashes( $this->default_cols[ $key ] ) ] = $value;
						break;

					case 'emails_to':
						$output[ $row ][ stripslashes( $this->default_cols[ $key ] ) ] = implode( ',', maybe_unserialize( $value ) );
						break;

					case 'data':
						// Unserialize value only if it was serialized.
						$fields = maybe_unserialize( $value );

						// Make sure there are no errors with unserializing before proceeding.
						if ( is_array( $fields ) ) {
							// Loop through our submitted data.
							foreach ( $fields as $field_key => $field_value ) {
								// Cast each array as an object.
								$obj = (object) $field_value;

								// Decode the values so HTML tags can be stripped.
								$val = wp_specialchars_decode( $obj->value, ENT_QUOTES );

								switch ( $obj->type ) {
									case 'fieldset':
									case 'section':
									case 'instructions':
									case 'page-break':
									case 'verification':
									case 'secret':
									case 'submit':
										break;

									case 'address':
										$val = str_replace( array( '<p>', '</p>', '<br>' ), array( '', "\n", "\n" ), $val );

										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] = $val;
										break;

									case 'html':
										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] = $val;
										break;

									default:
										$val = wp_strip_all_tags( $val );
										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] = $val;
										break;
								}
							}
						}
						break;
				}
			}

			$row++;
		}

		return wp_json_encode( $output );
	}

	/**
	 * AJAX function to load new fields list when a new form is selected
	 *
	 * @access public
	 * @return void
	 */
	public function load_fields() {
		global $wpdb;

		// Check AJAX nonce set via wp_localize_script.
		check_ajax_referer( 'vfb_ajax', 'vfb_ajax_nonce' );

		if ( isset( $_GET['action'] ) && 'vfb-export-fields' !== $_GET['action'] ) {
			return;
		}

		$form_id = absint( $_GET['id'] );

		$this->fields_list( $form_id );

		die( 1 );
	}

	/**
	 * Display Year/Month filter
	 *
	 * @since 1.7
	 */
	public function months_dropdown() {
		global $wpdb, $wp_locale;

		$where = apply_filters( 'vfb_pre_get_entries', '' );

		$months = $wpdb->get_results(
			'SELECT DISTINCT YEAR( forms.date_submitted ) AS year, MONTH( forms.date_submitted ) AS month FROM ' . VFB_WP_ENTRIES_TABLE_NAME . " AS forms
	WHERE 1=1 $where
	ORDER BY forms.date_submitted DESC
"
		);

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_POST['m'] ) ? (int) $_POST['m'] : 0;

		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year ) {
				continue;
			}

			$month = zeroise( $arc_row->month, 2 );
			$year  = $arc_row->year;

			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $arc_row->year . '-' . $month ),
				sprintf( esc_html__( '%1$s %2$d' ), esc_html( $wp_locale->get_month( $month ) ), esc_html( $year ) )
			);
		}
	}

	/**
	 * [count_entries description]
	 *
	 * @param  [type] $form_id [description].
	 * @return [type]          [description]
	 */
	public function get_entries_count( $form_id ) {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' WHERE form_id = %d', $form_id ) );

		if ( ! $count ) {
				return 0;
		}

		return $count;
	}

	/**
	 * [get_all_forms description]
	 *
	 * @return [type]  [return description]
	 */
	public function get_all_forms() {
		global $wpdb;

		// Query to get all forms.
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_export', '' );
		$forms = $wpdb->get_results( 'SELECT form_id, form_key, form_title FROM ' . VFB_WP_FORMS_TABLE_NAME . " WHERE 1=1 $where ORDER BY $order", ARRAY_A );

		return $forms;
	}
}
