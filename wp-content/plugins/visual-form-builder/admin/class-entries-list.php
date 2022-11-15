<?php
/**
 * Class that builds our Entries table
 *
 * @since 1.2
 */
class Visual_Form_Builder_Entries_List extends WP_List_Table {
	/**
	 * [__construct description]
	 */
	public function __construct() {
		global $status, $page;

		// CSV delimiter.
		$this->delimiter = apply_filters( 'vfb_csv_delimiter', ',' );

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'entry',
				'plural'   => 'entries',
				'ajax'     => false,
			)
		);

		// Handle our bulk actions.
		$this->process_bulk_action();

		// Handle row action links.
		$this->process_row_action_links();
	}

	/**
	 * Display column names. We'll handle the Form column separately.
	 *
	 * @param   [type] $item         [$item description].
	 * @param   [type] $column_name  [$column_name description].
	 *
	 * @return  [type]                [return description]
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'subject':
			case 'sender_name':
			case 'sender_email':
			case 'emails_to':
			case 'date':
			case 'ip_address':
			case 'entry_id':
				return $item[ $column_name ];
		}
	}

	/**
	 * Builds the on:hover links for the Form column
	 *
	 * @param   [type] $item  [$item description].
	 *
	 * @return  [type]         [return description]
	 */
	public function column_form( $item ) {
		// Build row actions.
		if ( ! $this->get_entry_status() || 'all' === $this->get_entry_status() ) {
			$view_url = add_query_arg(
				array(
					'page'       => 'vfb-entries',
					'vfb-action' => 'view',
					'entry'      => $item['entry_id'],
				),
				wp_nonce_url( admin_url( 'admin.php' ), 'vfb_view_entry' )
			);

			$actions['view'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $view_url ),
				esc_html__( 'View', 'visual-form-builder' )
			);
		}

		if ( ! $this->get_entry_status() || 'all' === $this->get_entry_status() ) {
			$trash_url = add_query_arg(
				array(
					'page'       => 'vfb-entries',
					'vfb-action' => 'trash',
					'entry'      => $item['entry_id'],
				),
				wp_nonce_url( admin_url( 'admin.php' ), 'vfb_trash_entry' )
			);

			$actions['trash'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $trash_url ),
				esc_html__( 'Trash', 'visual-form-builder' )
			);
		} elseif ( $this->get_entry_status() && 'trash' === $this->get_entry_status() ) {
			$restore_url = add_query_arg(
				array(
					'page'       => 'vfb-entries',
					'vfb-action' => 'restore',
					'entry'      => $item['entry_id'],
				),
				wp_nonce_url( admin_url( 'admin.php' ), 'vfb_undo_trash_entry' )
			);

			$actions['restore'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $restore_url ),
				esc_html__( 'Restore', 'visual-form-builder' )
			);

			$delete_url = add_query_arg(
				array(
					'page'       => 'vfb-entries',
					'vfb-action' => 'delete',
					'entry'      => $item['entry_id'],
				),
				wp_nonce_url( admin_url( 'admin.php' ), 'vfb_delete_entry' )
			);

			$actions['delete'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $delete_url ),
				esc_html__( 'Delete Permanently', 'visual-form-builder' )
			);
		}

		return sprintf( '%1$s %2$s', $item['form'], $this->row_actions( $actions ) );
	}

	/**
	 * Used for checkboxes and bulk editing
	 *
	 * @param   [type] $item  [$item description].
	 *
	 * @return  [type]         [return description]
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['entry_id'] );
	}

	/**
	 * Builds the actual columns
	 *
	 * @return  [type]  [return description]
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'form'         => __( 'Form', 'visual-form-builder' ),
			'subject'      => __( 'Email Subject', 'visual-form-builder' ),
			'sender_name'  => __( 'Sender Name', 'visual-form-builder' ),
			'sender_email' => __( 'Sender Email', 'visual-form-builder' ),
			'emails_to'    => __( 'Emailed To', 'visual-form-builder' ),
			'ip_address'   => __( 'IP Address', 'visual-form-builder' ),
			'date'         => __( 'Date Submitted', 'visual-form-builder' ),
			'entry_id'     => __( 'Entry ID', 'visual-form-builder' ),
		);

		return $columns;
	}

	/**
	 * A custom function to get the entries and sort them
	 *
	 * @param   [type] $orderby   [$orderby description].
	 * @param   date   $order     [$order description].
	 * @param   ASC    $per_page  [$per_page description].
	 * @param   [type] $offset    [$offset description].
	 * @param   [type] $search    [$search description].
	 *
	 * @return  [type]           [return description]
	 */
	public function get_entries( $orderby = 'date', $order = 'ASC', $per_page, $offset = 0, $search = '' ) {
		global $wpdb;

		// Set OFFSET for pagination.
		$offset = ( $offset > 0 ) ? "OFFSET $offset" : '';

		switch ( $orderby ) {
			case 'date':
				$order_col = 'date_submitted';
				break;

			case 'form':
				$order_col = 'form_title';
				break;

			case 'subject':
			case 'ip_address':
			case 'sender_name':
			case 'sender_email':
				$order_col = $orderby;
				break;

			case 'entry_id':
				$order_col = 'entries_id';
				break;
		}

		$where = '';

		// If the form filter dropdown is used.
		if ( $this->current_filter_action() ) {
			$where .= $wpdb->prepare( 'AND forms.form_id = %d', $this->current_filter_action() );
		}

		// Get the month and year from the dropdown.
		$m = isset( $_POST['m'] ) ? (int) $_POST['m'] : 0;

		// If a month/year has been selected, parse out the month/year and build the clause.
		if ( $m > 0 ) {
			$year  = substr( $m, 0, 4 );
			$month = substr( $m, -2 );

			$where .= $wpdb->prepare( ' AND YEAR(date_submitted) = %d AND MONTH(date_submitted) = %d', $year, $month );
		}

		// Get the month/year from the dropdown.
		$today = isset( $_GET['today'] ) ? (int) $_GET['today'] : 0;

		// Parse month/year and build the clause.
		if ( $today > 0 ) {
			$where .= ' AND entries.date_submitted >= curdate()';
		}

		// Entries type filter.
		$where .= ( $this->get_entry_status() && 'all' !== $this->get_entry_status() ) ? $wpdb->prepare( ' AND entries.entry_approved = %s', $this->get_entry_status() ) : '';

		// Always display approved entries, unless an Entries Type filter is set.
		if ( ! $this->get_entry_status() || 'all' === $this->get_entry_status() ) {
			$where .= $wpdb->prepare( ' AND entries.entry_approved = %d', 1 );
		}

		$sql_order = sanitize_sql_orderby( "$order_col $order" );
		$cols      = $wpdb->get_results( 'SELECT forms.form_title, entries.entries_id, entries.form_id, entries.subject, entries.sender_name, entries.sender_email, entries.emails_to, entries.date_submitted, entries.ip_address FROM ' . VFB_WP_FORMS_TABLE_NAME . ' AS forms INNER JOIN ' . VFB_WP_ENTRIES_TABLE_NAME . " AS entries ON entries.form_id = forms.form_id WHERE 1=1 $where $search ORDER BY $sql_order LIMIT $per_page $offset" );

		return $cols;
	}

	/**
	 * Get the entry status: All, Spam, or Trash
	 *
	 * @since   2.1
	 * @returns string Entry status
	 */
	public function get_entry_status() {
		if ( ! isset( $_GET['entry_status'] ) ) {
			return false;
		}

		return esc_html( sanitize_text_field( wp_unslash( $_GET['entry_status'] ) ) );
	}

	/**
	 * Build the different views for the entries screen
	 *
	 * @return  [type]  [return description]
	 */
	public function get_views() {
		$status_links = array();
		$num_entries  = $this->get_entries_count();
		$class        = '';
		$link         = '?page=vfb-entries';

		$stati = array(
			'all'   => _n_noop( 'All <span class="count">(<span class="pending-count">%s</span>)</span>', 'All <span class="count">(<span class="pending-count">%s</span>)</span>' ),
			'trash' => _n_noop( 'Trash <span class="count">(<span class="trash-count">%s</span>)</span>', 'Trash <span class="count">(<span class="trash-count">%s</span>)</span>' ),
		);

		$total_entries = (int) $num_entries->all;
		$entry_status  = isset( $_GET['entry_status'] ) ? esc_html( sanitize_text_field( wp_unslash( $_GET['entry_status'] ) ) ) : 'all';

		foreach ( $stati as $status => $label ) {
			$class = ( $status === $entry_status ) ? ' class="current"' : '';

			if ( ! isset( $num_entries->$status ) ) {
				$num_entries->$status = 10;
			}

			$link = add_query_arg( 'entry_status', $status, $link );

			$status_links[ $status ] = "<li class='$status'><a href='$link'$class>" . sprintf(
				translate_nooped_plural( $label, $num_entries->$status ),
				number_format_i18n( $num_entries->$status )
			) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Get the number of entries for use with entry statuses
	 *
	 * @return  [type]  [return description]
	 */
	public function get_entries_count() {
		global $wpdb;

		$stats = array();

		$entries = $wpdb->get_results( 'SELECT entries.entry_approved, COUNT( * ) AS num_entries FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' AS entries WHERE 1=1 GROUP BY entries.entry_approved', ARRAY_A );

		$total    = 0;
		$approved = array(
			'0'            => 'moderated',
			'1'            => 'approved',
			'spam'         => 'spam',
			'trash'        => 'trash',
			'post-trashed' => 'post-trashed',
		);
		foreach ( (array) $entries as $row ) {
			// Don't count trashed toward totals.
			if ( 'trash' !== $row['entry_approved'] ) {
				$total += $row['num_entries'];
			}
			if ( isset( $approved[ $row['entry_approved'] ] ) ) {
				$stats[ $approved[ $row['entry_approved'] ] ] = $row['num_entries'];
			}
		}

		$stats['all'] = $total;
		foreach ( $approved as $key ) {
			if ( empty( $stats[ $key ] ) ) {
				$stats[ $key ] = 0;
			}
		}

		$stats = (object) $stats;

		return $stats;
	}

	/**
	 * Setup which columns are sortable. Default is by Date.
	 *
	 * @return  [type]  [return description]
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'form'         => array( 'form', false ),
			'subject'      => array( 'subject', false ),
			'sender_name'  => array( 'sender_name', false ),
			'sender_email' => array( 'sender_email', false ),
			'date'         => array( 'date', true ),
			'entry_id'     => array( 'entry_id', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Define our bulk actions
	 *
	 * @return  [type]  [return description]
	 */
	public function get_bulk_actions() {
		if ( ! $this->get_entry_status() || 'all' === $this->get_entry_status() ) {
			$actions['trash'] = __( 'Move to Trash', 'visual-form-builder' );
		} elseif ( $this->get_entry_status() && 'trash' === $this->get_entry_status() ) {
			$actions['restore'] = __( 'Restore', 'visual-form-builder' );
			$actions['delete']  = __( 'Delete Permanently', 'visual-form-builder' );
		}

		return $actions;
	}

	/**
	 * Process our bulk actions
	 *
	 * @return  void
	 */
	public function process_bulk_action() {
		global $wpdb;

		$entry_id = '';

		if ( isset( $_POST['entry'] ) ) {
			if ( is_array( $_POST['entry'] ) ) {
				$entry_id = array_map( 'sanitize_text_field', wp_unslash( $_POST['entry'] ) );
			} else {
				$entry_id = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['entry'] ) );
			}
		}

		switch ( $this->current_action() ) {
			case 'trash':
				check_admin_referer( 'bulk-entries' );

				foreach ( $entry_id as $id ) {
					$id = absint( $id );
					$wpdb->update( VFB_WP_ENTRIES_TABLE_NAME, array( 'entry_approved' => 'trash' ), array( 'entries_id' => $id ) );
				}
				break;

			case 'restore':
				check_admin_referer( 'bulk-entries' );

				foreach ( $entry_id as $id ) {
					$id = absint( $id );
					$wpdb->update( VFB_WP_ENTRIES_TABLE_NAME, array( 'entry_approved' => 1 ), array( 'entries_id' => $id ) );
				}
				break;

			case 'delete':
				check_admin_referer( 'bulk-entries' );

				foreach ( $entry_id as $id ) {
					$id = absint( $id );
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' WHERE entries_id = %d', $id ) );
				}
				break;
		}
	}

	/**
	 * Process action row links below form title
	 *
	 * This is different than bulk action processing
	 *
	 * @return  [type]  [return description]
	 */
	public function process_row_action_links() {
		global $wpdb;

		if ( ! isset( $_GET['vfb-action'] ) ) {
			return;
		}

		if ( ! isset( $_GET['entry'] ) ) {
			return;
		}

		$action   = sanitize_text_field( wp_unslash( $_GET['vfb-action'] ) );
		$entry_id = absint( $_GET['entry'] );

		switch ( $action ) {
			case 'trash':
				check_admin_referer( 'vfb_trash_entry' );
				$wpdb->update( VFB_WP_ENTRIES_TABLE_NAME, array( 'entry_approved' => 'trash' ), array( 'entries_id' => $entry_id ) );

				break;

			case 'restore':
				check_admin_referer( 'vfb_undo_trash_entry' );

				$wpdb->update( VFB_WP_ENTRIES_TABLE_NAME, array( 'entry_approved' => 1 ), array( 'entries_id' => $entry_id ) );
				break;

			case 'delete':
				check_admin_referer( 'vfb_delete_entry' );

				$wpdb->delete( VFB_WP_ENTRIES_TABLE_NAME, array( 'entries_id' => $entry_id ) );
				break;
		}
	}

	/**
	 * Adds our forms filter dropdown
	 *
	 * @param   [type] $which  [$which description].
	 *
	 * @return  void
	 */
	public function extra_tablenav( $which ) {
		global $wpdb;

		$cols = $wpdb->get_results( 'SELECT DISTINCT forms.form_title, forms.form_id FROM ' . VFB_WP_FORMS_TABLE_NAME . ' AS forms ORDER BY forms.form_id ASC' );

		// Only display the dropdown on the top of the table.
		if ( 'top' === $which ) {
			echo '<div class="alignleft actions">';
			$this->months_dropdown();
			echo '<select id="form-filter" name="form-filter">
		<option value="-1"' . selected( $this->current_filter_action(), -1 ) . '>' . esc_html__( 'View all forms', 'visual-form-builder' ) . '</option>';

			foreach ( $cols as $form ) {
				printf(
					'<option value="%1$d"%2$s>%1$d - %3$s</option>',
					esc_html( $form->form_id ),
					selected( $this->current_filter_action(), esc_html( $form->form_id ) ),
					esc_html( $form->form_title )
				);
			}

			echo '</select>
		<input type="submit" value="' . esc_html__( 'Filter', 'visual-form-builder' ) . '" class="button-secondary" />
		</div>';
		}
	}

	/**
	 * Display Year/Month filter
	 *
	 * @param   [type] $post_type  [$post_type description].
	 *
	 * @return  [type]              [return description]
	 */
	public function months_dropdown( $post_type = '' ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results(
			'
				SELECT DISTINCT YEAR( forms.date_submitted ) AS year, MONTH( forms.date_submitted ) AS month
				FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' AS forms
				ORDER BY forms.date_submitted DESC
			'
		);

		$month_count = count( $months );

		if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
			return;
		}

		$m = isset( $_POST['m'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['m'] ) ) : 0;
		?>
	<select name='m'>
		<option<?php selected( $m, 0 ); ?> value='0'><?php esc_html_e( 'Show all dates' ); ?></option>
		<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year ) {
				continue;
			}

			$month = zeroise( $arc_row->month, 2 );
			$year  = $arc_row->year;

			printf(
				"<option %s value='%s'>%s</option>\n",
				selected( esc_html( $m ), $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				sprintf( esc_html__( '%1$s %2$d' ), esc_html( $wp_locale->get_month( $month ) ), esc_html( $year ) )
			);
		}
		?>
	</select>
		<?php
	}

	/**
	 * Set our forms filter action
	 *
	 * @return  [type]  [return description]
	 */
	public function current_filter_action() {
		if ( isset( $_POST['form-filter'] ) && -1 !== $_POST['form-filter'] ) {
			return absint( $_POST['form-filter'] );
		}

		return false;
	}

	/**
	 * Prepares our data for display
	 *
	 * @since 1.2
	 */
	public function prepare_items() {
		global $wpdb;

		// get the current user ID.
		$user = get_current_user_id();

		// get the current admin screen.
		$screen = get_current_screen();

		// retrieve the "per_page" option.
		$screen_option = $screen->get_option( 'per_page', 'option' );

		// retrieve the value of the option stored for the current user.
		$per_page = get_user_meta( $user, $screen_option, true );

		// get the default value if none is set.
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = 20;
		}

		// Get the date/time format that is saved in the options table.
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		// What page are we looking at?
		$current_page = $this->get_pagenum();

		// Use offset for pagination.
		$offset = ( $current_page - 1 ) * $per_page;

		// Get column headers.
		$columns = $this->get_columns();
		$hidden  = get_hidden_columns( $this->screen );

		// Get sortable columns.
		$sortable = $this->get_sortable_columns();

		// Build the column headers.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get entries search terms.
		$search_terms = ( ! empty( $_POST['s'] ) ) ? explode( ' ', sanitize_text_field( wp_unslash( $_POST['s'] ) ) ) : array();

		$searchand = $search = '';
		// Loop through search terms and build query.
		foreach ( $search_terms as $term ) {
			$term = esc_sql( $wpdb->esc_like( $term ) );

			$search   .= "{$searchand}((entries.subject LIKE '%{$term}%') OR (entries.sender_name LIKE '%{$term}%') OR (entries.sender_email LIKE '%{$term}%') OR (entries.emails_to LIKE '%{$term}%') OR (entries.data LIKE '%{$term}%'))";
			$searchand = ' AND ';
		}

		$search = ( ! empty( $search ) ) ? " AND ({$search}) " : '';

		// Set our ORDER BY and ASC/DESC to sort the entries.
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'date';
		$order   = ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc';

		// Get the sorted entries.
		$entries = $this->get_entries( $orderby, $order, $per_page, $offset, $search );

		$data = array();

		// Loop trough the entries and setup the data to be displayed for each row.
		foreach ( $entries as $entry ) {
			$data[] =
			array(
				'entry_id'     => $entry->entries_id,
				'id'           => $entry->entries_id,
				'form'         => wp_unslash( $entry->form_title ),
				'subject'      => wp_unslash( $entry->subject ),
				'sender_name'  => wp_unslash( $entry->sender_name ),
				'sender_email' => wp_unslash( $entry->sender_email ),
				'emails_to'    => implode( ',', unserialize( wp_unslash( $entry->emails_to ) ) ),
				'date'         => gmdate( "$date_format $time_format", strtotime( $entry->date_submitted ) ),
				'ip_address'   => $entry->ip_address,
			);
		}

		$where = '';

		// If the form filter dropdown is used.
		if ( $this->current_filter_action() ) {
			$where .= 'AND form_id = ' . $this->current_filter_action();
		}

		// Get the month/year from the dropdown.
		$m = isset( $_POST['m'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['m'] ) ) : 0;

		// Parse month/year and build the clause.
		if ( $m > 0 ) {
			$year  = substr( $m, 0, 4 );
			$month = substr( $m, -2 );

			$where .= " AND YEAR(date_submitted) = $year AND MONTH(date_submitted) = $month";
		}

		// Get the month/year from the dropdown.
		$today = isset( $_GET['today'] ) ? (int) $_GET['today'] : 0;

		// Parse month/year and build the clause.
		if ( $today > 0 ) {
			$where .= ' AND entries.date_submitted >= curdate()';
		}

		// Entry type filter.
		$where .= ( $this->get_entry_status() && 'all' !== $this->get_entry_status() ) ? $wpdb->prepare( ' AND entries.entry_approved = %s', $this->get_entry_status() ) : '';

		// Always display approved entries, unless an Entries Type filter is set.
		if ( ! $this->get_entry_status() || 'all' === $this->get_entry_status() ) {
			$where .= $wpdb->prepare( ' AND entries.entry_approved = %d', 1 );
		}

		// How many entries do we have?
		$total_items = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . VFB_WP_ENTRIES_TABLE_NAME . " AS entries WHERE 1=1 $where" );

		// Add sorted data to the items property.
		$this->items = $data;

		// Register our pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
