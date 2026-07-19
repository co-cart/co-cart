<?php
/**
 * Settings page renderer.
 *
 * Renders tabs, panels, and individual field rows from a schema returned by
 * CoCart_Admin_Settings_Page::get_sections() and get_fields().
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   4.9.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_Admin_Settings_Renderer {

	/**
	 * Detect whether a developer has registered their own callback on a filter,
	 * ignoring CoCart's own settings-bridge callbacks (priority 5).
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $hook_name Filter hook to inspect.
	 *
	 * @return bool True if an external callback is registered.
	 */
	public function has_external_filter( $hook_name ) {
		global $wp_filter;

		if ( empty( $wp_filter[ $hook_name ] ) ) {
			return false;
		}

		foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
			if ( 5 === $priority ) {
				continue;
			}
			if ( ! empty( $callbacks ) ) {
				return true;
			}
		}

		return false;
	} // END has_external_filter()

	/**
	 * Collect registered callbacks for a filter hook, skipping priority 5.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $hook_name Filter hook to inspect.
	 *
	 * @return array<int, array<string, mixed>> Each entry: priority, label, file, line.
	 */
	public function get_filter_locations( $hook_name ) {
		global $wp_filter;

		$locations = array();

		if ( empty( $wp_filter[ $hook_name ] ) ) {
			return $locations;
		}

		foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
			if ( 5 === $priority ) {
				continue;
			}
			foreach ( $callbacks as $callback ) {
				$fn = $callback['function'];
				try {
					if ( is_array( $fn ) ) {
						$ref = new ReflectionMethod( $fn[0], $fn[1] );
					} elseif ( $fn instanceof Closure ) {
						$ref = new ReflectionFunction( $fn );
					} elseif ( is_string( $fn ) ) {
						$ref = new ReflectionFunction( $fn );
					} else {
						continue;
					}
					$file  = $ref->getFileName();
					$line  = $ref->getStartLine();
					$label = is_array( $fn )
						? ( is_object( $fn[0] ) ? get_class( $fn[0] ) : $fn[0] ) . '::' . $fn[1]
						: ( is_string( $fn ) ? $fn : '{closure}' );

					$locations[] = array(
						'priority' => $priority,
						'label'    => $label,
						'file'     => $file ? str_replace( ABSPATH, '', $file ) : '',
						'line'     => $line ? $line : null,
					);
				} catch ( ReflectionException $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Skip callbacks we cannot introspect.
				}
			}
		}

		return $locations;
	} // END get_filter_locations()

	/**
	 * Render the tab navigation bar.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, array<string, mixed>> $sections Sections from get_sections().
	 */
	public function render_tabs( $sections ) {
		$default_tab = $this->get_default_tab( $sections );

		foreach ( $sections as $key => $section ) {
			$id       = esc_attr( $key );
			$label    = $section['label'];
			$selected = ( $key === $default_tab ) ? 'true' : 'false';
			$class    = ( $key === $default_tab ) ? ' is-active' : '';
			$badge    = '';

			if ( ! empty( $section['preview'] ) ) {
				$badge = ' <span class="cocart-settings-tab-badge"><span class="dashicons dashicons-lock"></span></span>';
			}

			printf(
				'<button type="button" class="cocart-settings-tab%s" role="tab" aria-selected="%s" aria-controls="cocart-tab-%s" data-tab="%s">%s%s</button>',
				esc_attr( $class ),
				esc_attr( $selected ),
				esc_attr( $id ),
				esc_attr( $id ),
				wp_kses_post( $label ),
				wp_kses_post( $badge )
			);
		}
	} // END render_tabs()

	/**
	 * Determine which section should be active by default.
	 *
	 * Skips sections flagged as a preview (e.g. "General") so they are only
	 * shown when explicitly selected, falling back to the first section.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, array<string, mixed>> $sections Sections from get_sections().
	 *
	 * @return string Section ID to activate by default.
	 */
	protected function get_default_tab( $sections ) {
		foreach ( $sections as $key => $section ) {
			if ( empty( $section['preview'] ) ) {
				return $key;
			}
		}

		return array_key_first( $sections );
	} // END get_default_tab()

	/**
	 * Render all tab panels.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, array<string, mixed>> $sections Sections from get_sections().
	 * @param array<string, array<string, mixed>> $fields   Fields from get_fields().
	 * @param array<string, mixed>                $settings Saved cocart_settings option.
	 */
	public function render_panels( $sections, $fields, $settings ) {
		$default_tab = $this->get_default_tab( $sections );

		foreach ( $sections as $key => $section ) {
			$section_id     = $key;
			$section_fields = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : array();

			$is_active = ( $section_id === $default_tab );
			$active    = $is_active ? ' is-active' : '';
			printf(
				'<div id="cocart-tab-%s" class="cocart-settings-panel%s" role="tabpanel"%s>',
				esc_attr( $section_id ),
				esc_attr( $active ),
				( $is_active ? '' : ' hidden' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			echo '<table class="cocart-settings-table"><tbody>';

			foreach ( $section_fields as $id => $field ) {
				$value = ! empty( $field['disabled'] )
					? ( $field['default'] ?? '' )
					: $this->get_field_value( $id, $section_id, $settings, $field['default'] ?? '' );
				$this->render_field_row( $field, $value, $section_id, $id );
			}

			echo '</tbody></table>';
			echo '</div><!-- /cocart-tab-' . esc_html( $section_id ) . ' -->';
		}
	} // END render_panels()

	/**
	 * Return the saved value for a field, falling back to default.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string               $id       Field ID (array key from get_fields()).
	 * @param string               $section  Section ID.
	 * @param array<string, mixed> $settings Saved cocart_settings option.
	 * @param mixed                $fallback Default value from the field definition.
	 *
	 * @return mixed
	 */
	public function get_field_value( $id, $section, $settings, $fallback = '' ) {
		if ( is_int( $fallback ) ) {
			return isset( $settings[ $section ][ $id ] ) ? (int) $settings[ $section ][ $id ] : $fallback;
		}

		return ! empty( $settings[ $section ][ $id ] ) ? $settings[ $section ][ $id ] : $fallback;
	} // END get_field_value()

	/**
	 * Check whether a field's filter is currently active and compute the effective value.
	 *
	 * Returns an array: [ 'is_filtered' => bool, 'effective_value' => mixed ]
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition (may have 'filter' key).
	 * @param mixed                $value Current saved value.
	 * @param string               $id    Field ID (array key from get_fields()).
	 *
	 * @return array<string, mixed>
	 */
	public function get_filter_state( $field, $value, $id = '' ) {
		if ( empty( $field['filter']['hook'] ) ) {
			return array(
				'is_filtered'     => false,
				'effective_value' => $value,
			);
		}

		$hook        = $field['filter']['hook'];
		$is_filtered = $this->has_external_filter( $hook );

		if ( ! $is_filtered ) {
			return array(
				'is_filtered'     => false,
				'effective_value' => $value,
			);
		}

		// For inverted boolean filters (disable_* hooks): the filter returns true to disable.
		if ( ! empty( $field['filter']['invert'] ) ) {
			// Default passed to filter: the logical inverse of the saved "enabled" value.
			$saved_enabled = ( 'yes' === $value );
			/**
			 * Filters whether the feature controlled by this field is disabled.
			 *
			 * @since 4.9.0
			 */
			$filter_result = apply_filters( $hook, ! $saved_enabled ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$effective     = $filter_result ? 'no' : 'yes';
		} elseif ( 'checkbox' === $field['type'] && 'cocart_does_product_allow_price_change' === $hook ) {
			// Name Your Price: filter takes extra params for per-product overrides.
			$saved_enabled = ( 'yes' === $value );
			/**
			 * Filters whether a cart item is allowed to override the product price.
			 *
			 * @since 4.9.0
			 */
			$filter_result = apply_filters( $hook, $saved_enabled, array(), null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$effective     = $filter_result ? 'yes' : 'no';
		} elseif ( 'checkbox' === $field['type'] ) {
			$saved_enabled = ( 'yes' === $value );
			/**
			 * Filters whether the feature controlled by this field is enabled.
			 *
			 * @since 4.9.0
			 */
			$filter_result = apply_filters( $hook, $saved_enabled ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$effective     = $filter_result ? 'yes' : 'no';
		} elseif ( 'url' === $field['type'] && 'allowed_origin' === $id ) {
			/**
			 * Filters the allowed HTTP origin result.
			 *
			 * @since 4.9.0
			 */
			$effective = apply_filters( $hook, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		} elseif ( 'number' === $field['type'] && 'cocart_cart_expiration' === $hook ) {
			$is_logged_in = ( 'loggedin_expiration_days' === $id );
			/**
			 * Filters the cart expiration time in seconds.
			 *
			 * @since 4.9.0
			 */
			$filter_result = (int) apply_filters( $hook, (int) $value * DAY_IN_SECONDS, $is_logged_in ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$effective     = (int) round( $filter_result / DAY_IN_SECONDS );
		} else {
			$effective = $value;
		}

		return array(
			'is_filtered'     => true,
			'effective_value' => $effective,
		);
	} // END get_filter_state()

	/**
	 * Render a single `<tr>` for a field.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field   Field definition.
	 * @param mixed                $value   Saved or default value.
	 * @param string               $section Section ID.
	 * @param string               $id      Field ID (array key from get_fields()).
	 */
	public function render_field_row( $field, $value, $section, $id ) {
		$disabled = ! empty( $field['disabled'] );

		$is_filtered = false;
		$locked      = $disabled;

		if ( ! $disabled ) {
			$filter_state = $this->get_filter_state( $field, $value, $id );
			$is_filtered  = $filter_state['is_filtered'];
			$value        = $filter_state['effective_value'];
			$locked       = $is_filtered && ! empty( $field['filter']['locked'] );
		}

		echo '<tr>';
		echo '<th scope="row">';
		$this->render_field_label( $field, $section, $id );
		echo '</th>';
		echo '<td>';
		$this->render_field_input( $field, $value, $locked, $section, $id );
		if ( 'custom' !== $field['type'] ) {
			$this->render_field_description( $field );
			$this->render_field_docs_link( $field );
		}
		if ( $is_filtered && false !== ( $field['filter']['notice'] ?? true ) ) {
			$this->render_filter_notice( $field );
		}
		if ( $disabled ) {
			$this->render_disabled_notice();
		}
		echo '</td>';
		echo '</tr>';
	} // END render_field_row()

	/**
	 * Render the `<label>` element in the `<th>`.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field   Field definition.
	 * @param string               $section Section ID.
	 * @param string               $id      Field ID (array key from get_fields()).
	 */
	protected function render_field_label( $field, $section, $id ) {
		$has_id = ! in_array( $field['type'], array( 'checkbox', 'custom' ), true );
		if ( $has_id ) {
			$input_id = 'cocart-' . esc_attr( $section ) . '-' . esc_attr( str_replace( '_', '-', $id ) );
			printf( '<label for="%s">%s</label>', esc_attr( $input_id ), wp_kses_post( $field['label'] ) );
		} else {
			printf( '<label>%s</label>', wp_kses_post( $field['label'] ) );
		}
	} // END render_field_label()

	/**
	 * Render the input control(s) for a field.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field   Field definition.
	 * @param mixed                $value   Current value.
	 * @param bool                 $locked  Whether the field is locked by a filter or marked disabled.
	 * @param string               $section Section ID.
	 * @param string               $id      Field ID (array key from get_fields()).
	 */
	protected function render_field_input( $field, $value, $locked, $section, $id ) {
		$name        = $section . '_' . $id;
		$input_id    = 'cocart-' . $section . '-' . str_replace( '_', '-', $id );
		$type        = $field['type'];
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$disabled    = ! empty( $field['disabled'] );

		switch ( $type ) {
			case 'text':
			case 'url':
				if ( 'url' === $type && 'allowed_origin' === $id ) {
					$this->render_site_origin_notice();
				}
				printf(
					'<input type="%s" id="%s" name="%s" class="regular-text" value="%s" placeholder="%s" style="width:25em;" %s>',
					esc_attr( $type ),
					esc_attr( $input_id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					esc_attr( $placeholder ),
					disabled( $disabled, true, false )
				);
				break;

			case 'textarea':
				$readonly = $locked ? ' readonly' : '';
				printf(
					'<textarea id="%s" name="%s" rows="5" class="regular-text" placeholder="%s" style="width:25em;"%s %s>%s</textarea>',
					esc_attr( $input_id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$readonly, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					disabled( $disabled, true, false ),
					esc_textarea( (string) $value )
				);
				break;

			case 'number':
				$min  = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
				$max  = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
				$step = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
				printf(
					'<input type="number" id="%s" name="%s" class="small-text" value="%s"%s%s%s %s>',
					esc_attr( $input_id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					$min,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$max,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$step, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					disabled( $disabled, true, false )
				);
				break;

			case 'checkbox':
				$enabled      = ( 'yes' === $value );
				$title        = $enabled ? esc_attr__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_attr__( 'Disabled', 'cart-rest-api-for-woocommerce' );
				$label        = $enabled ? esc_html__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_html__( 'Disabled', 'cart-rest-api-for-woocommerce' );
				$locked_class = $locked ? ' cocart-toggle--locked' : '';
				printf(
					'<label class="cocart-toggle%s" title="%s">',
					esc_attr( $locked_class ),
					esc_attr( $title ) // Already escaped by esc_attr__() above; esc_attr() is a no-op but satisfies PHPCS.
				);
				printf(
					'<input type="checkbox" class="cocart-settings-toggle" name="%s" data-field="%s" value="yes" %s %s>',
					esc_attr( $name ),
					esc_attr( $name ),
					checked( $value, 'yes', false ),
					disabled( $locked, true, false )
				);
				echo '<span class="cocart-toggle-slider"></span>';
				printf( '<span class="cocart-status-label">%s</span>', esc_html( $label ) );
				echo '</label>';
				break;

			case 'select':
				$options = $field['options'] ?? array();
				printf( '<select id="%s" name="%s" %s>', esc_attr( $input_id ), esc_attr( $name ), disabled( $disabled, true, false ) );
				foreach ( $options as $opt_value => $opt_label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $opt_value ),
						selected( $value, $opt_value, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'readonly':
				printf(
					'<input type="text" id="%s" class="regular-text" value="%s" style="width:25em;" readonly>',
					esc_attr( $input_id ),
					esc_attr( (string) $value )
				);
				break;

			case 'custom':
				if ( ! empty( $field['render_cb'] ) && is_callable( $field['render_cb'] ) ) {
					call_user_func( $field['render_cb'], $field, $value );
				}
				break;
		}
	} // END render_field_input()

	/**
	 * Render a notice stating the site's own origins are always allowed automatically.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 */
	protected function render_site_origin_notice() {
		$home_origin = wp_parse_url( home_url() );

		if ( empty( $home_origin['host'] ) ) {
			return;
		}

		$site_origin = ( ! empty( $home_origin['scheme'] ) ? $home_origin['scheme'] : 'https' ) . '://' . $home_origin['host'];

		/* translators: %s: Site origin URL. */
		$message = sprintf( __( 'Your site\'s own origin (%s) is already allowed automatically.', 'cart-rest-api-for-woocommerce' ), '<code>' . esc_html( $site_origin ) . '</code>' );

		printf( '<p>%s</p>', wp_kses_post( $message ) );
	} // END render_site_origin_notice()

	/**
	 * Render the description `<p>` if the field has one.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition.
	 */
	protected function render_field_description( $field ) {
		if ( ! empty( $field['description'] ) ) {
			// Description is already translated by the registering code; escape on output.
			printf( '<p class="description">%s</p>', wp_kses_post( $field['description'] ) );
		}
	} // END render_field_description()

	/**
	 * Render the documentation link badge if a URL is set.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition.
	 */
	protected function render_field_docs_link( $field ) {
		if ( empty( $field['docs_url'] ) ) {
			return;
		}
		$label = isset( $field['docs_label'] ) ? esc_html( $field['docs_label'] ) : esc_html__( 'Docs', 'cart-rest-api-for-woocommerce' );
		printf(
			'<div class="cocart-setting-doc-wrap"><a href="%s" class="button cocart-button-alt" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-external"></span>%s</a></div>',
			esc_url( $field['docs_url'] ),
			$label // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	} // END render_field_docs_link()

	/**
	 * Render the filter-override notice bar below a field.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition (must have 'filter' key).
	 */
	public function render_filter_notice( $field ) {
		if ( empty( $field['filter']['hook'] ) ) {
			return;
		}
		$hook = $field['filter']['hook'];
		printf(
			'<p class="cocart-settings-filter-notice"><span class="dashicons dashicons-info-outline"></span>%s<button type="button" class="cocart-filter-info-btn button-link" data-filter="%s">%s</button></p>',
			wp_kses(
				/* translators: %s: filter hook name */
				sprintf( __( 'Controlled by filter <code>%s</code>.', 'cart-rest-api-for-woocommerce' ), $hook ),
				array( 'code' => array() )
			),
			esc_attr( $hook ),
			esc_html__( 'More info', 'cart-rest-api-for-woocommerce' )
		);
	} // END render_filter_notice()

	/**
	 * Render a notice for fields disabled in Community, with a link
	 * that opens a modal to upgrade CoCart.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 */
	protected function render_disabled_notice() {
		printf(
			'<p class="cocart-settings-upgrade-notice"><span class="dashicons dashicons-lock"></span><button type="button" class="cocart-upgrade-info-btn button-link">%s</button></p>',
			esc_html__( 'Upgrade to unlock', 'cart-rest-api-for-woocommerce' )
		);
	} // END render_disabled_notice()

	/**
	 * Build the filter_info array for wp_localize_script — only for hooks that are currently active.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, array<string, mixed>> $fields Fields from get_fields().
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public function build_filter_info( $fields ) {
		$seen        = array();
		$filter_info = array();

		foreach ( $fields as $section_fields ) {
			foreach ( $section_fields as $field ) {
				if ( ! empty( $field['disabled'] ) || empty( $field['filter']['hook'] ) ) {
					continue;
				}
				$hook = $field['filter']['hook'];
				if ( isset( $seen[ $hook ] ) ) {
					continue;
				}
				$seen[ $hook ] = true;
				if ( $this->has_external_filter( $hook ) ) {
					$filter_info[ $hook ] = $this->get_filter_locations( $hook );
				}
			}
		}

		return $filter_info;
	} // END build_filter_info()
} // END class
