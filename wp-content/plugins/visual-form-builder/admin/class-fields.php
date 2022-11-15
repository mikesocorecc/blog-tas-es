<?php
/**
 * [Visual_Form_Builder_Admin_Fields description]
 */
class Visual_Form_Builder_Admin_Fields {
	/**
	 * [field_output description]
	 *
	 * @param   [type] $form_nav_selected_id  [$form_nav_selected_id description].
	 * @param   [type] $field_id              [$field_id description].
	 *
	 * @return  void
	 */
	public function field_output( $form_nav_selected_id, $field_id = null ) {
		global $wpdb;

		$field_where = ( isset( $field_id ) && ! is_null( $field_id ) ) ? "AND field_id = $field_id" : '';
		// Display all fields for the selected form.
		$fields = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . VFB_WP_FIELDS_TABLE_NAME . " WHERE form_id = %d $field_where ORDER BY field_sequence ASC", $form_nav_selected_id ) );

		$depth  = 1;
		$parent = $last = 0;
		ob_start();

		// Loop through each field and display.
		foreach ( $fields as &$field ) :
			// If we are at the root level.
			if ( ! $field->field_parent && $depth > 1 ) {
				// If we've been down a level, close out the list.
				while ( $depth > 1 ) {
					echo '</li></ul>';
					$depth--;
				}

				// Close out the root item.
				echo '</li>';
			} elseif ( $field->field_parent && $field->field_parent === $last ) {
				// first item of <ul>, so move down a level.
				echo '<ul class="parent">';
				$depth++;
			} elseif ( $field->field_parent && $field->field_parent !== $parent ) {
				// Close up a <ul> and move up a level.
				echo '</li></ul></li>';
				$depth--;
			} elseif ( $field->field_parent && $field->field_parent === $parent ) {
				// Same level so close list item.
				echo '</li>';
			}

			// Store item ID and parent ID to test for nesting.
			$last   = $field->field_id;
			$parent = $field->field_parent;
			?>
	<li id="form_item_<?php echo esc_attr( $field->field_id ); ?>" class="form-item<?php echo in_array( $field->field_type, array( 'submit', 'secret', 'verification' ), true ) ? ' ui-state-disabled' : ''; ?><?php echo ! in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ), true ) ? ' mjs-nestedSortable-no-nesting' : ''; ?>">
	<dl class="menu-item-bar vfb-menu-item-inactive">
		<dt class="vfb-menu-item-handle vfb-menu-item-type-<?php echo esc_attr( $field->field_type ); ?>">
			<span class="item-title"><?php echo esc_html( wp_unslash( $field->field_name ) ); ?><?php echo ( 'yes' === $field->field_required ) ? ' <span class="is-field-required">*</span>' : ''; ?></span>
					<span class="item-controls">
				<span class="item-type"><?php echo esc_html( strtoupper( str_replace( '-', ' ', $field->field_type ) ) ); ?></span>
				<a href="#" title="<?php esc_attr_e( 'Edit Field Item', 'visual-form-builder' ); ?>" id="edit-<?php echo esc_attr( $field->field_id ); ?>" class="item-edit"><?php esc_html_e( 'Edit Field Item', 'visual-form-builder' ); ?></a>
			</span>
		</dt>
	</dl>

	<div id="form-item-settings-<?php echo esc_attr( $field->field_id ); ?>" class="menu-item-settings field-type-<?php echo esc_attr( $field->field_type ); ?>" style="display: none;">
			<?php if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ), true ) ) : ?>

		<p class="description description-wide">
			<label for="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>"><?php echo in_array( $field->field_type, array( 'fieldset', 'verification' ), true ) ? 'Legend' : 'Name'; ?>
				<span class="vfb-tooltip" rel="<?php esc_attr_e( 'For Fieldsets, a Legend is simply the name of that group. Use general terms that describe the fields included in this Fieldset.', 'visual-form-builder' ); ?>" title="<?php esc_attr_e( 'About Legend', 'visual-form-builder' ); ?>">(?)</span>
				<br />
				<input type="text" value="<?php echo esc_html( wp_unslash( $field->field_name ) ); ?>" name="field_name-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>" maxlength="255" />
			</label>
		</p>
			<p class="description description-wide">
				<label for="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>">
					<?php esc_html_e( 'CSS Classes', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'visual-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_html( $field->field_css ); ?>" name="field_css-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>" />
				</label>
			</p>

	<?php elseif ( 'instructions' === $field->field_type ) : ?>
		<!-- Instructions -->
		<p class="description description-wide">
			<label for="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>">
				<?php esc_html_e( 'Name', 'visual-form-builder' ); ?>
				<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Name', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'visual-form-builder' ); ?>">(?)</span>
				<br />
				<input type="text" value="<?php echo esc_html( wp_unslash( $field->field_name ) ); ?>" name="field_name-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>" maxlength="255" />
			</label>
		</p>
		<!-- Description -->
		<p class="description description-wide">
			<label for="edit-form-item-description-<?php echo esc_attr( $field->field_id ); ?>">
						<?php esc_html_e( 'Description (HTML tags allowed)', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Instructions Description', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'The Instructions field allows for long form explanations, typically seen at the beginning of Fieldsets or Sections. HTML tags are allowed.', 'visual-form-builder' ); ?>">(?)</span>
							<br />
				<textarea name="field_description-<?php echo esc_attr( $field->field_id ); ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo esc_attr( $field->field_id ); ?>" /><?php echo esc_html( wp_unslash( $field->field_description ) ); ?></textarea>
			</label>
		</p>
		<!-- CSS Classes -->
	<p class="description description-thin">
			<label for="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>">
					<?php esc_html_e( 'CSS Classes', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'visual-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_attr( $field->field_css ); ?>" name="field_css-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>" />
			</label>
	</p>

	<!-- Field Layout -->
	<p class="description description-thin">
		<label for="edit-form-item-layout">
			<?php esc_html_e( 'Field Layout', 'visual-form-builder' ); ?>
			<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Field Layout', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'visual-form-builder' ); ?>">(?)</span>
			<br />
			<select name="field_layout-<?php echo esc_html( $field->field_id ); ?>" class="widefat" id="edit-form-item-layout-<?php echo esc_attr( $field->field_id ); ?>">
				<option value="" <?php selected( esc_html( $field->field_layout ), '' ); ?>><?php esc_html_e( 'Default', 'visual-form-builder' ); ?></option>
				<optgroup label="------------">
				<option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php esc_html_e( 'Left Half', 'visual-form-builder' ); ?></option>
				<option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php esc_html_e( 'Right Half', 'visual-form-builder' ); ?></option>
				</optgroup>
				<optgroup label="------------">
	<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php esc_html_e( 'Left Third', 'visual-form-builder' ); ?></option>
				<option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php esc_html_e( 'Middle Third', 'visual-form-builder' ); ?></option>
				<option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php esc_html_e( 'Right Third', 'visual-form-builder' ); ?></option>
				</optgroup>
				<optgroup label="------------">
				<option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php esc_html_e( 'Left Two Thirds', 'visual-form-builder' ); ?></option>
				<option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php esc_html_e( 'Right Two Thirds', 'visual-form-builder' ); ?></option>
				</optgroup>
				<?php apply_filters( 'vfb_admin_field_layout', $field->field_layout ); ?>
			</select>
		</label>
	</p>

	<?php else : ?>

		<!-- Name -->
		<p class="description description-wide">
			<label for="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>">
				<?php esc_html_e( 'Name', 'visual-form-builder' ); ?>
				<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Name', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'visual-form-builder' ); ?>">(?)</span>
				<br />
				<input type="text" value="<?php echo esc_html( wp_unslash( $field->field_name ) ); ?>" name="field_name-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-name-<?php echo esc_attr( $field->field_id ); ?>" maxlength="255" />
			</label>
		</p>
		<?php if ( 'submit' === $field->field_type ) : ?>
		<!-- CSS Classes -->
		<p class="description description-wide">
			<label for="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>">
				<?php esc_html_e( 'CSS Classes', 'visual-form-builder' ); ?>
				<span class="vfb-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'visual-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'visual-form-builder' ); ?>">(?)</span>
				<br />
				<input type="text" value="<?php echo esc_html( $field->field_css ); ?>" name="field_css-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>" />
			</label>
		</p>
		<?php elseif ( 'submit' !== $field->field_type ) : ?>
			<!-- Description -->
			<p class="description description-wide">
				<label for="edit-form-item-description-<?php echo esc_attr( $field->field_id ); ?>">
					<?php esc_html_e( 'Description', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Description', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'A description is an optional piece of text that further explains the meaning of this field. Descriptions are displayed below the field. HTML tags are allowed.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<textarea name="field_description-<?php echo esc_html( $field->field_id ); ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo esc_attr( $field->field_id ); ?>" /><?php echo esc_html( wp_unslash( $field->field_description ) ); ?></textarea>
				</label>
			</p>

				<?php
					// Display the Options input only for radio, checkbox, and select fields.
				if ( in_array( $field->field_type, array( 'radio', 'checkbox', 'select' ), true ) ) :
					?>
				<!-- Options -->
				<p class="description description-wide">
					<?php esc_html_e( 'Options', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Options', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'This property allows you to set predefined options to be selected by the user.  Use the plus and minus buttons to add and delete options.  At least one option must exist.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<?php
					// If the options field isn't empty, unserialize and build array.
					if ( ! empty( $field->field_options ) ) {
						if ( is_serialized( $field->field_options ) ) {
							$opts_vals = is_array( unserialize( $field->field_options ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
						}
					} else {
							// Otherwise, present some default options.
							$opts_vals = array( 'Option 1', 'Option 2', 'Option 3' );
					}

					// Basic count to keep track of multiple options.
					$count = 1;
					?>
				<div class="vfb-cloned-options">
							<?php foreach ( $opts_vals as $options ) : ?>
				<div id="clone-<?php echo esc_attr( $field->field_id . '-' . $count ); ?>" class="option">
					<label for="edit-form-item-options-<?php echo esc_attr( $field->field_id . "-$count" ); ?>" class="clonedOption">
						<input type="radio" value="<?php echo esc_html( $count ); ?>" name="field_default-<?php echo esc_attr( $field->field_id ); ?>" <?php checked( $field->field_default, $count ); ?> />
						<input type="text" value="<?php echo esc_html( wp_unslash( $options ) ); ?>" name="field_options-<?php echo esc_attr( $field->field_id ); ?>[]" class="widefat" id="edit-form-item-options-<?php echo esc_attr( $field->field_id . "-$count" ); ?>" />
					</label>

					<a href="#" class="deleteOption vfb-interface-icon vfb-interface-minus" title="Delete Option">
								<?php esc_html_e( 'Delete', 'visual-form-builder' ); ?>
					</a>
					<span class="vfb-interface-icon vfb-interface-sort" title="<?php esc_attr_e( 'Drag and Drop to Sort Options', 'visual-form-builder' ); ?>"></span>
				</div>
									<?php
									$count++;
							endforeach;
							?>
				</div> <!-- .vfb-cloned-options -->
				<div class="clear"></div>
				<div class="vfb-add-options-group">
					<a href="#" class="vfb-button vfb-add-option" title="Add Option">
						<?php esc_html_e( 'Add Option', 'visual-form-builder' ); ?>
						<span class="vfb-interface-icon vfb-interface-plus"></span>
					</a>
				</div>
				</p>
							<?php
							// Unset the options for any following radio, checkboxes, or selects.
							unset( $opts_vals );
					endif;
				?>

				<?php if ( in_array( $field->field_type, array( 'file-upload' ), true ) ) : ?>
				<!-- File Upload Accepts -->
				<p class="description description-wide">
					<?php
					$opts_vals = array( '' );

					// If the options field isn't empty, unserialize and build array.
					if ( ! empty( $field->field_options ) ) {
						if ( is_serialized( $field->field_options ) ) {
							$opts_vals = is_array( unserialize( $field->field_options ) ) ? unserialize( $field->field_options ) : unserialize( $field->field_options );
						}
					}

					// Loop through the options.
					foreach ( $opts_vals as $options ) {
						?>
						<label for="edit-form-item-options-<?php echo esc_attr( $field->field_id ); ?>">
							<?php esc_html_e( 'Accepted File Extensions', 'visual-form-builder' ); ?>
							<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Accepted File Extensions', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the types of files allowed.  Enter extensions without periods and separate multiples using the pipe character ( | ).', 'visual-form-builder' ); ?>">(?)</span>
							<br />
							<input type="text" value="<?php echo esc_attr( $options ); ?>" name="field_options-<?php echo esc_attr( $field->field_id ); ?>[]" class="widefat" id="edit-form-item-options-<?php echo esc_attr( $field->field_id ); ?>" />
						</label>
				</p>
						<?php
					}
					// Unset the options for any following radio, checkboxes, or selects.
					unset( $opts_vals );
				endif;
				?>

				<?php if ( in_array( $field->field_type, array( 'date' ) ) ) : ?>
					<!-- Date Format -->
				<p class="description description-wide">
					<?php
					$opts_vals   = maybe_unserialize( $field->field_options );
					$date_format = ( isset( $opts_vals['dateFormat'] ) ) ? $opts_vals['dateFormat'] : 'mm/dd/yy';
					?>
					<label for="edit-form-item-date-dateFormat-<?php echo esc_attr( $field->field_id ); ?>">
						<?php esc_html_e( 'Date Format', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Date Format', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Set the date format for each date picker.', 'visual-form-builder' ); ?>">(?)</span>
						<br />
						<input type="text" value="<?php echo esc_html( $date_format ); ?>" name="field_options-<?php echo esc_attr( $field->field_id ); ?>[dateFormat]" class="widefat" id="edit-form-item-date-dateFormat-<?php echo esc_attr( $field->field_id ); ?>" />
					</label>
				</p>
					<?php
					// Unset the options for any following radio, checkboxes, or selects.
					unset( $opts_vals );
				endif;
				?>
			<!-- Validation -->
			<p class="description description-thin">
				<label for="edit-form-item-validation">
					<?php esc_html_e( 'Validation', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Validation', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Ensures user-entered data is formatted properly. For more information on Validation, refer to the Help tab at the top of this page.', 'visual-form-builder' ); ?>">(?)</span>
					<br />

					<?php if ( in_array( $field->field_type, array( 'text', 'time', 'number' ), true ) ) : ?>
						<select name="field_validation-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-validation-<?php echo esc_attr( $field->field_id ); ?>">
							<?php if ( 'time' === $field->field_type ) : ?>
							<option value="time-12" <?php selected( $field->field_validation, 'time-12' ); ?>><?php esc_html_e( '12 Hour Format', 'visual-form-builder' ); ?></option>
							<option value="time-24" <?php selected( $field->field_validation, 'time-24' ); ?>><?php esc_html_e( '24 Hour Format', 'visual-form-builder' ); ?></option>
							<?php elseif ( in_array( $field->field_type, array( 'number' ), true ) ) : ?>
							<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php esc_html_e( 'Number', 'visual-form-builder' ); ?></option>
							<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php esc_html_e( 'Digits', 'visual-form-builder' ); ?></option>
							<?php else : ?>
							<option value="" <?php selected( $field->field_validation, '' ); ?>><?php esc_html_e( 'None', 'visual-form-builder' ); ?></option>
							<option value="email" <?php selected( $field->field_validation, 'email' ); ?>><?php esc_html_e( 'Email', 'visual-form-builder' ); ?></option>
							<option value="url" <?php selected( $field->field_validation, 'url' ); ?>><?php esc_html_e( 'URL', 'visual-form-builder' ); ?></option>
							<option value="date" <?php selected( $field->field_validation, 'date' ); ?>><?php esc_html_e( 'Date', 'visual-form-builder' ); ?></option>
							<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php esc_html_e( 'Number', 'visual-form-builder' ); ?></option>
							<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php esc_html_e( 'Digits', 'visual-form-builder' ); ?></option>
							<option value="phone" <?php selected( $field->field_validation, 'phone' ); ?>><?php esc_html_e( 'Phone', 'visual-form-builder' ); ?></option>
							<?php endif; ?>
						</select>
						<?php
					else :
						$field_validation = '';

						switch ( $field->field_type ) {
							case 'email':
							case 'url':
							case 'phone':
								$field_validation = $field->field_type;
								break;

							case 'currency':
								$field_validation = 'number';
								break;

							case 'number':
								$field_validation = 'digits';
								break;
						}
						?>
					<input type="text" class="widefat" name="field_validation-<?php echo esc_attr( $field->field_id ); ?>" value="<?php echo esc_html( $field_validation ); ?>" readonly="readonly" />
					<?php endif; ?>

				</label>
			</p>

			<!-- Required -->
			<p class="field-link-target description description-thin">
				<label for="edit-form-item-required">
					<?php esc_html_e( 'Required', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Required', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Requires the field to be completed before the form is submitted. By default, all fields are set to No.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<select name="field_required-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-required-<?php echo esc_attr( $field->field_id ); ?>">
						<option value="no" <?php selected( esc_attr( $field->field_required ), 'no' ); ?>><?php esc_html_e( 'No', 'visual-form-builder' ); ?></option>
						<option value="yes" <?php selected( esc_attr( $field->field_required ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'visual-form-builder' ); ?></option>
					</select>
				</label>
			</p>

				<?php if ( ! in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ), true ) ) : ?>
				<!-- Size -->
				<p class="description description-thin">
					<label for="edit-form-item-size">
						<?php esc_html_e( 'Size', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Size', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the size of the field.  By default, all fields are set to Medium.', 'visual-form-builder' ); ?>">(?)</span>
						<br />
						<select name="field_size-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-size-<?php echo esc_attr( $field->field_id ); ?>">
							<option value="small" <?php selected( $field->field_size, 'small' ); ?>><?php esc_html_e( 'Small', 'visual-form-builder' ); ?></option>
							<option value="medium" <?php selected( $field->field_size, 'medium' ); ?>><?php esc_html_e( 'Medium', 'visual-form-builder' ); ?></option>
							<option value="large" <?php selected( $field->field_size, 'large' ); ?>><?php esc_html_e( 'Large', 'visual-form-builder' ); ?></option>
						</select>
					</label>
				</p>

			<?php elseif ( in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ), true ) ) : ?>
				<!-- Options Layout -->
				<p class="description description-thin">
					<label for="edit-form-item-size">
						<?php esc_html_e( 'Options Layout', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Options Layout', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the layout of radio buttons or checkboxes.  By default, options are arranged in One Column.', 'visual-form-builder' ); ?>">(?)</span>
						<br />
						<select name="field_size-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-size-<?php echo esc_attr( $field->field_id ); ?>"<?php echo ( 'time' === $field->field_type ) ? ' disabled="disabled"' : ''; ?>>
							<option value="" <?php selected( $field->field_size, '' ); ?>><?php esc_html_e( 'One Column', 'visual-form-builder' ); ?></option>
							<option value="two-column" <?php selected( $field->field_size, 'two-column' ); ?>><?php esc_html_e( 'Two Columns', 'visual-form-builder' ); ?></option>
	<option value="three-column" <?php selected( $field->field_size, 'three-column' ); ?>><?php esc_html_e( 'Three Columns', 'visual-form-builder' ); ?></option>
							<option value="auto-column" <?php selected( $field->field_size, 'auto-column' ); ?>><?php esc_html_e( 'Auto Width', 'visual-form-builder' ); ?></option>
						</select>
					</label>
				</p>

			<?php endif; ?>
				<!-- Field Layout -->
				<p class="description description-thin">
					<label for="edit-form-item-layout">
						<?php esc_html_e( 'Field Layout', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Field Layout', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'visual-form-builder' ); ?>">(?)</span>
						<br />
						<select name="field_layout-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-layout-<?php echo esc_attr( $field->field_id ); ?>">
							<option value="" <?php selected( $field->field_layout, '' ); ?>><?php esc_html_e( 'Default', 'visual-form-builder' ); ?></option>
							<optgroup label="------------">
							<option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php esc_html_e( 'Left Half', 'visual-form-builder' ); ?></option>
							<option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php esc_html_e( 'Right Half', 'visual-form-builder' ); ?></option>
							</optgroup>
							<optgroup label="------------">
	<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php esc_html_e( 'Left Third', 'visual-form-builder' ); ?></option>
							<option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php esc_html_e( 'Middle Third', 'visual-form-builder' ); ?></option>
							<option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php esc_html_e( 'Right Third', 'visual-form-builder' ); ?></option>
							</optgroup>
							<optgroup label="------------">
							<option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php esc_html_e( 'Left Two Thirds', 'visual-form-builder' ); ?></option>
							<option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php esc_html_e( 'Right Two Thirds', 'visual-form-builder' ); ?></option>
							</optgroup>
						</select>
					</label>
				</p>
				<?php if ( ! in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'time', 'address' ), true ) ) : ?>
			<!-- Default Value -->
			<p class="description description-wide">
				<label for="edit-form-item-default-<?php echo esc_attr( $field->field_id ); ?>">
					<?php esc_html_e( 'Default Value', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Default Value', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Set a default value that will be inserted automatically.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_html( $field->field_default ); ?>" name="field_default-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-default-<?php echo esc_attr( $field->field_id ); ?>" maxlength="255" />
				</label>
			</p>
			<?php elseif ( in_array( $field->field_type, array( 'address' ), true ) ) : ?>
			<!-- Default Country -->
			<p class="description description-wide">
				<label for="edit-form-item-default-<?php echo esc_attr( $field->field_id ); ?>">
						<?php esc_html_e( 'Default Country', 'visual-form-builder' ); ?>
						<span class="vfb-tooltip" title="<?php esc_attr_e( 'About Default Country', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'Select the country you would like to be displayed by default.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<select name="field_default-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-default-<?php echo esc_attr( $field->field_id ); ?>">
					<?php
					$countries = include VFB_WP_PLUGIN_DIR . '/inc/countries.php';
					foreach ( $countries as $country ) {
						printf(
							'<option value="%1$s"%2$s>%1$s</option>',
							esc_attr( $country ),
							selected( esc_html( $field->field_default ), $country, 0 )
						);
					}
					?>
					</select>
				</label>
			</p>
			<?php endif; ?>
			<!-- CSS Classes -->
			<p class="description description-wide">
				<label for="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>">
					<?php esc_html_e( 'CSS Classes', 'visual-form-builder' ); ?>
					<span class="vfb-tooltip" title="<?php esc_attr_e( 'About CSS Classes', 'visual-form-builder' ); ?>" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'visual-form-builder' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_html( $field->field_css ); ?>" name="field_css-<?php echo esc_attr( $field->field_id ); ?>" class="widefat" id="edit-form-item-css-<?php echo esc_attr( $field->field_id ); ?>" maxlength="255" />
				</label>
			</p>

		<?php endif; ?>
	<?php endif; ?>

					<?php if ( ! in_array( $field->field_type, array( 'verification', 'secret', 'submit' ), true ) ) : ?>
			<!-- Delete link -->
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=visual-form-builder&amp;action=delete_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'delete-field-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-delete item-delete submitdelete deletion">
							<?php esc_html_e( 'Delete', 'visual-form-builder' ); ?>
				<span class="vfb-interface-icon vfb-interface-trash"></span>
			</a>
					<?php endif; ?>

	<input type="hidden" name="field_id[<?php echo esc_attr( $field->field_id ); ?>]" value="<?php echo esc_html( $field->field_id ); ?>" />
	</div>
					<?php
			endforeach;

		// This assures all of the <ul> and <li> are closed.
		if ( $depth > 1 ) {
			while ( $depth > 1 ) {
				echo '</li></ul>';
				$depth--;
			}
		}

		// Close out last item.
		echo '</li>';
		echo ob_get_clean();
	}
}
