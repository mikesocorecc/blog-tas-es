<?php
/**
 * Class that builds our Entries table
 *
 * @since 1.2
 */
class Visual_Form_Builder_Forms_List extends WP_List_Table {
	/**
	 * Errors
	 *
	 * @var    mixed
	 * @access public
	 */
	public $errors;

	/**
	 * [__construct description]
	 *
	 * @return  void
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false,
			)
		);

		// Handle our bulk actions.
		$this->process_bulk_action();
	}

	/**
	 * Display column names
	 *
	 * @param   [type] $item         [$item description].
	 * @param   [type] $column_name  [$column_name description].
	 *
	 * @return  [type]                [return description]
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'form_id':
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
	public function column_form_title( $item ) {
		$actions = array();

		// Edit Form.
		$edit_link  = admin_url( 'admin.php?page=visual-form-builder' );
		$form_title = sprintf( '<strong>%s</strong>', $item['form_title'] );

		$form_title_url = add_query_arg(
			array(
				'page'       => 'visual-form-builder',
				'vfb-action' => 'edit',
				'form'       => $item['form_id'],
			),
			admin_url( 'admin.php' )
		);

		$form_title = sprintf( '<a href="%s">%s</a>', esc_url( $form_title_url ), $form_title );

		$edit_url = add_query_arg(
			array(
				'page'       => 'visual-form-builder',
				'vfb-action' => 'edit',
				'form'       => $item['form_id'],
			),
			admin_url( 'admin.php' )
		);

		$actions['edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $edit_url ),
			esc_html__( 'Edit', 'visual-form-builder' )
		);

		// Duplicate Form.
		$copy_url = add_query_arg(
			array(
				'page'       => 'visual-form-builder',
				'vfb-action' => 'copy_form',
				'form'       => $item['form_id'],
			),
			wp_nonce_url( admin_url( 'admin.php' ), 'copy-form-' . $item['form_id'] )
		);

		$actions['copy'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $copy_url ),
			esc_html__( 'Duplicate', 'visual-form-builder' )
		);

		// Delete Form.
		$delete_url = add_query_arg(
			array(
				'page'       => 'visual-form-builder',
				'vfb-action' => 'delete_form',
				'form'       => $item['form_id'],
			),
			wp_nonce_url( admin_url( 'admin.php' ), 'delete-form-' . $item['form_id'] )
		);

		$actions['delete'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $delete_url ),
			esc_html__( 'Delete', 'visual-form-builder' )
		);

		return sprintf( '%1$s %2$s', $form_title, $this->row_actions( $actions ) );
	}

	/**
	 * [column_entries description]
	 *
	 * @param  [type] $item [description].
	 * @return void
	 */
	public function column_entries( $item ) {
		$this->comments_bubble( $item['form_id'], $item['entries'] );
	}

	/**
	 * [comments_bubble description]
	 *
	 * @param  [type] $form_id [description].
	 * @param  [type] $count   [description].
	 * @return void
	 */
	public function comments_bubble( $form_id, $count ) {
		printf(
			'<div class="entries-count-wrapper"><a href="%1$s" title="%2$s" class="vfb-meta-entries-total"><span class="entries-count">%4$s</span></a> %3$s</div>',
			esc_url( add_query_arg( array( 'form-filter' => $form_id ), admin_url( 'admin.php?page=vfb-entries' ) ) ),
			esc_attr__( 'Entries Total', 'visual-form-builder' ),
			esc_html__( 'Total', 'visual-form-builder' ),
			esc_html( number_format_i18n( $count['total'] ) )
		);

		if ( $count['today'] ) {
			echo '<strong>';
		}

		printf(
			'<div class="entries-count-wrapper"><a href="%1$s" title="%2$s" class="vfb-meta-entries-total"><span class="entries-count">%4$s</span></a> %3$s</div>',
			esc_url(
				add_query_arg(
					array(
						'form-filter' => $form_id,
						'today'       => 1,
					),
					admin_url( 'admin.php?page=vfb-entries' )
				)
			),
			esc_attr__( 'Entries Today', 'visual-form-builder' ),
			esc_html__( 'Today', 'visual-form-builder' ),
			esc_html( number_format_i18n( $count['today'] ) )
		);

		if ( $count['today'] ) {
			echo '</strong>';
		}
	}

	/**
	 * Used for checkboxes and bulk editing
	 *
	 * @param   [type] $item  [$item description].
	 *
	 * @return  [type]         [return description]
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['form_id'] );
	}

	/**
	 * Builds the actual columns
	 *
	 * @since 1.2
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'form_title' => esc_html__( 'Form', 'visual-form-builder' ),
			'form_id'    => esc_html__( 'Form ID', 'visual-form-builder' ),
			'entries'    => esc_html__( 'Entries', 'visual-form-builder' ),
		);

		return $columns;
	}

	/**
	 * A custom function to get the entries and sort them
	 *
	 * @param   [type]  $orderby   [$orderby description].
	 * @param   form_id $order     [$order description].
	 * @param   ASC     $per_page  [$per_page description].
	 * @param   [type]  $offset    [$offset description].
	 * @param   [type]  $search    [$search description].
	 *
	 * @return  [type]              [return description]
	 */
	public function get_forms( $orderby = 'form_id', $order = 'ASC', $per_page, $offset = 0, $search = '' ) {
		global $wpdb;

		// Set OFFSET for pagination.
		$offset = ( $offset > 0 ) ? "OFFSET $offset" : '';

		$where = apply_filters( 'vfb_pre_get_forms', '' );

		// If the form filter dropdown is used.
		if ( $this->current_filter_action() ) {
			$where .= ' AND forms.form_id = ' . $this->current_filter_action();
		}

		$sql_order = sanitize_sql_orderby( "$orderby $order" );
		$cols      = $wpdb->get_results( 'SELECT forms.form_id, forms.form_title FROM ' . VFB_WP_FORMS_TABLE_NAME . " AS forms WHERE 1=1 $where $search ORDER BY $sql_order LIMIT $per_page $offset" );

		return $cols;
	}

	/**
	 * Build the different views for the entries screen
	 *
	 * @since   2.7.6
	 * @returns array $status_links Status links with counts
	 */
	public function get_views() {
		$status_links = array();
		$num_forms    = $this->get_forms_count();
		$class        = '';
		$link         = '?page=visual-form-builder';

		$stati = array(
			'all' => _n_noop( 'All <span class="count">(<span class="pending-count">%s</span>)</span>', 'All <span class="count">(<span class="pending-count">%s</span>)</span>' ),
		);

		$total_entries = (int) $num_forms->all;
		$entry_status  = isset( $_GET['form_status'] ) ? sanitize_text_field( wp_unslash( $_GET['form_status'] ) ) : 'all';

		foreach ( $stati as $status => $label ) {
			$class = ( $status === $entry_status ) ? ' class="current"' : '';

			if ( ! isset( $num_forms->$status ) ) {
				$num_forms->$status = 10;
			}

			$link = add_query_arg( 'form_status', $status, $link );

			$status_links[ $status ] = "<li class='$status'><a href='$link'$class>" . sprintf(
				translate_nooped_plural( $label, $num_forms->$status ),
				number_format_i18n( $num_forms->$status )
			) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Get the number of entries for use with entry statuses
	 *
	 * @since   2.1
	 * @returns array $stats Counts of different entry types
	 */
	public function get_entries_count() {
		global $wpdb;

		$total_entries = array();

		$entries = $wpdb->get_results( 'SELECT form_id, COUNT(form_id) as num_entries FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' AS entries WHERE entries.entry_approved = 1 GROUP BY form_id', ARRAY_A );

		if ( $entries ) {
			foreach ( $entries as $entry ) {
				$total_entries[ $entry['form_id'] ] = absint( $entry['num_entries'] );
			}

			return $total_entries;
		}

		return $total_entries;
	}

	/**
	 * Get the number of entries for use with entry statuses
	 *
	 * @since   2.1
	 * @returns array $stats Counts of different entry types
	 */
	public function get_entries_today_count() {
		global $wpdb;

		$total_entries = array();

		$entries = $wpdb->get_results( 'SELECT form_id, COUNT(form_id) as num_entries FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' AS entries WHERE entries.entry_approved = 1 AND date_submitted >= curdate() GROUP BY form_id', ARRAY_A );

		if ( $entries ) {
			foreach ( $entries as $entry ) {
				$total_entries[ $entry['form_id'] ] = absint( $entry['num_entries'] );
			}

			return $total_entries;
		}

		return $total_entries;
	}

	/**
	 * Get the number of forms
	 *
	 * @since   2.2.7
	 * @returns int $count Form count
	 */
	public function get_forms_count() {
		global $wpdb;

		$stats = array();

		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . VFB_WP_FORMS_TABLE_NAME );

		$stats['all'] = $count;

		$stats = (object) $stats;

		return $stats;
	}

	/**
	 * Setup which columns are sortable. Default is by Date.
	 *
	 * @since   1.2
	 * @returns array() $sortable_columns Sortable columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'         => array( 'id', false ),
			'form_id'    => array( 'form_id', false ),
			'form_title' => array( 'form_title', true ),
			'entries'    => array( 'entries_count', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Define our bulk actions
	 *
	 * @since   1.2
	 * @returns array() $actions Bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array();

		// Build the row actions.
		$actions['delete'] = esc_html__( 'Delete Permanently', 'visual-form-builder' );

		return $actions;
	}

	/**
	 * Process ALL actions on the Entries screen, not only Bulk Actions
	 *
	 * @since 1.2
	 */
	public function process_bulk_action() {
		global $wpdb;

		$form_id = '';

		// Set the Entry ID array.
		if ( isset( $_POST['form'] ) ) {
			if ( is_array( $_POST['form'] ) ) {
				$form_id = array_map( 'sanitize_text_field', wp_unslash( $_POST['form'] ) );
			} else {
				$form_id = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['form'] ) );
			}
		}

		switch ( $this->current_action() ) {
			case 'trash':
				check_admin_referer( 'bulk-forms' );

				foreach ( $form_id as $id ) {
					$id = absint( $id );
					$wpdb->update( VFB_WP_FORMS_TABLE_NAME, array( 'form_approved' => 'trash' ), array( 'form_id' => $id ) );
				}
				break;

			case 'delete':
				check_admin_referer( 'bulk-forms' );

				foreach ( $form_id as $id ) {
					$id = absint( $id );
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . VFB_WP_FORMS_TABLE_NAME . ' WHERE form_id = %d', $id ) );
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . VFB_WP_FIELDS_TABLE_NAME . ' WHERE form_id = %d', $id ) );
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . VFB_WP_ENTRIES_TABLE_NAME . ' WHERE form_id = %d', $id ) );
				}
				break;
		}
	}

	/**
	 * Set our forms filter action
	 *
	 * @since   1.2
	 * @returns int Form ID
	 */
	public function current_filter_action() {
		if ( isset( $_POST['form-filter'] ) && -1 != $_POST['form-filter'] ) {
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

			$search   .= "{$searchand}((forms.form_title LIKE '%{$term}%') OR (forms.form_key LIKE '%{$term}%') OR (forms.form_email_subject LIKE '%{$term}%'))";
			$searchand = ' AND ';
		}

		$search = ( ! empty( $search ) ) ? " AND ({$search}) " : '';

		// Set our ORDER BY and ASC/DESC to sort the entries.
		$orderby = ! empty( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'form_id';
		$order   = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc';

		// Get the sorted entries.
		$forms = $this->get_forms( $orderby, $order, $per_page, $offset, $search );

		// Get entries totals.
		$entries_total = $this->get_entries_count();
		$entries_today = $this->get_entries_today_count();

		$data = array();

		// Loop trough the entries and setup the data to be displayed for each row.
		foreach ( $forms as $form ) :

			// Check if index exists first, not every form has entries.
			$entries_total[ $form->form_id ] = isset( $entries_total[ $form->form_id ] ) ? $entries_total[ $form->form_id ] : 0;

			// Check if index exists first, not every form has entries today.
			$entries_today[ $form->form_id ] = isset( $entries_today[ $form->form_id ] ) ? $entries_today[ $form->form_id ] : 0;

			$entries_counts = array(
				'total' => $entries_total[ $form->form_id ],
				'today' => $entries_today[ $form->form_id ],
			);

			$data[] = array(
				'id'         => $form->form_id,
				'form_id'    => $form->form_id,
				'form_title' => wp_unslash( $form->form_title ),
				'entries'    => $entries_counts,
			);
		endforeach;

		// How many forms do we have?
		$total_items = $this->get_forms_count();

		// Add sorted data to the items property.
		$this->items = $data;

		// Register our pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items->all,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items->all / $per_page ),
			)
		);
	}
}
