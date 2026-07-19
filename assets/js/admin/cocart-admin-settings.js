/* global cocartSettingsConfig, jQuery */
( function( $ ) {
	var config      = cocartSettingsConfig;
	var $toast      = $( '#cocart-settings-toast' );
	var $saveBtn    = $( '#cocart-save-settings' );
	var toastTimer;
	var isDirty        = false;
	var isInitialising = true;
	var pendingHref    = null;

	// --- Tab switching ---
	function switchTab( tabName ) {
		$( '.cocart-settings-tab' ).each( function() {
			var active = $( this ).data( 'tab' ) === tabName;
			$( this ).toggleClass( 'is-active', active ).attr( 'aria-selected', active ? 'true' : 'false' );
		} );
		$( '.cocart-settings-panel' ).each( function() {
			var active = $( this ).attr( 'id' ) === 'cocart-tab-' + tabName;
			$( this ).toggleClass( 'is-active', active ).prop( 'hidden', ! active );
		} );
		history.replaceState( null, '', location.pathname + location.search.replace( /[?&]tab=[^&]*/g, '' ) + ( location.search ? '&' : '?' ) + 'tab=' + tabName );
	}

	$( '.cocart-settings-tab' ).on( 'click', function() {
		switchTab( $( this ).data( 'tab' ) );
	} );

	// Restore tab from URL on load.
	var urlTab = new URLSearchParams( location.search ).get( 'tab' );
	if ( urlTab && $( '#cocart-tab-' + urlTab ).length ) {
		switchTab( urlTab );
	}

	// --- Dirty tracking ---
	function markDirty() { if ( ! isInitialising ) { isDirty = true; $saveBtn.prop( 'disabled', false ); } }
	function markClean() { isDirty = false; $saveBtn.prop( 'disabled', true ); }

	$( document ).on( 'change input', '.cocart-settings-table input, .cocart-settings-table textarea, .cocart-settings-table select', markDirty );

	isInitialising = false;

	// --- Unsaved changes modal ---
	var $unsavedModal = $( '#cocart-unsaved-modal' );

	function showUnsavedModal( href ) {
		pendingHref = href;
		$unsavedModal.removeAttr( 'hidden' );
		$( '#cocart-unsaved-stay' ).trigger( 'focus' );
	}

	$( '#cocart-unsaved-leave' ).on( 'click', function() {
		markClean();
		$unsavedModal.attr( 'hidden', true );
		if ( pendingHref ) {
			window.location.href = pendingHref;
		}
	} );

	$( '#cocart-unsaved-stay' ).on( 'click', function() {
		pendingHref = null;
		$unsavedModal.attr( 'hidden', true );
	} );

	// Intercept WordPress admin nav links (menu, breadcrumbs, tabs) when dirty.
	$( document ).on( 'click', 'a[href]', function( e ) {
		if ( ! isDirty ) { return; }
		var href = $( this ).attr( 'href' );
		if ( ! href || href.charAt( 0 ) === '#' ) { return; }
		if ( $( this ).attr( 'target' ) === '_blank' ) { return; }
		if ( $( this ).hasClass( 'cocart-settings-tab' ) ) { return; }
		e.preventDefault();
		showUnsavedModal( href );
	} );

	function showToast( message, type ) {
		clearTimeout( toastTimer );
		$toast.removeClass( 'is-success is-error' ).addClass( 'is-active is-' + type ).text( message );
		toastTimer = setTimeout( function() {
			$toast.removeClass( 'is-active' );
		}, 4000 );
	}

	// --- Save settings ---
	$saveBtn.on( 'click', function() {
		var $btn = $( this );

		var data = { action: 'cocart_save_settings', nonce: config.nonce };

		// Standard fields: collect all named inputs/textareas/selects in the settings tables.
		$( '.cocart-settings-table' ).find( 'input[name], textarea[name], select[name]' ).each( function() {
			var $el  = $( this );
			var name = $el.attr( 'name' );

			if ( ! name || $el.prop( 'disabled' ) ) {
				return;
			}

			var inputType = ( $el.attr( 'type' ) || '' ).toLowerCase();

			if ( 'checkbox' === inputType ) {
				data[ name ] = $el.is( ':checked' ) ? 'yes' : 'no';
			} else {
				data[ name ] = $el.val();
			}
		} );

		$btn.prop( 'disabled', true ).addClass( 'is-saving' ).text( config.i18n.saving );

		$.ajax( {
			url:    config.ajaxUrl,
			method: 'POST',
			data:   data,
			success: function( response ) {
				$btn.removeClass( 'is-saving' ).text( config.i18n.save );
				if ( response.success ) {
					markClean();
					showToast( config.i18n.toastSaved, 'success' );
					$btn.trigger( 'cocart:saved' );
				} else {
					$btn.prop( 'disabled', false );
					showToast( config.i18n.toastError, 'error' );
				}
			},
			error: function() {
				$btn.prop( 'disabled', false ).removeClass( 'is-saving' ).text( config.i18n.save );
				showToast( config.i18n.toastError, 'error' );
			},
		} );
	} );

	// --- Reset to defaults ---
	var $resetModal = $( '#cocart-reset-modal' );

	$( '#cocart-reset-settings' ).on( 'click', function() {
		$resetModal.removeAttr( 'hidden' );
		$( '#cocart-reset-cancel' ).trigger( 'focus' );
	} );

	$( '#cocart-reset-cancel' ).on( 'click', function() {
		$resetModal.attr( 'hidden', true );
	} );

	$( '#cocart-reset-confirm' ).on( 'click', function() {
		$resetModal.attr( 'hidden', true );

		var defaults = config.defaults || {};

		// Reset all standard named inputs across every panel.
		$( '.cocart-settings-table' ).find( 'input[name], textarea[name], select[name]' ).each( function() {
			var $el       = $( this );
			var name      = $el.attr( 'name' );
			var inputType = ( $el.attr( 'type' ) || '' ).toLowerCase();

			if ( ! name || $el.prop( 'disabled' ) ) {
				return;
			}

			// Derive section and field id from the name using known section keys.
			var section  = '';
			var fieldId  = name;
			var sections = config.sections || [];
			for ( var si = 0; si < sections.length; si++ ) {
				var sKey = sections[ si ];
				if ( name.indexOf( sKey + '_' ) === 0 ) {
					if ( sKey.length > section.length ) {
						section = sKey;
						fieldId = name.substring( sKey.length + 1 );
					}
				}
			}
			var defVal = ( section && defaults[ section ] && defaults[ section ][ fieldId ] !== undefined ) ?
				defaults[ section ][ fieldId ] :
				'';

			if ( 'checkbox' === inputType ) {
				var checked = ( 'yes' === defVal );
				$el.prop( 'checked', checked );
				$el.closest( 'td' ).find( '.cocart-status-label' ).text(
					checked ? config.i18n.enabled : config.i18n.disabled
				);
			} else {
				$el.val( defVal );
			}
		} );

		markDirty();
		showToast( config.i18n.toastReset, 'success' );
		$( document ).trigger( 'cocart:reset' );
	} );

	// --- JWT unsaved-settings warning modal ---
	var $jwtUnsavedModal = $( '#cocart-jwt-unsaved-modal' );

	$( '#cocart-jwt-unsaved-cancel' ).on( 'click', function() {
		$jwtUnsavedModal.attr( 'hidden', true );
	} );

	function runJwtToggle( $toggle ) {
		var $label   = $toggle.closest( '.cocart-toggle' );
		var $state   = $label.find( '.cocart-status-label' );
		var enabling = $toggle.prop( 'checked' );
		var action   = enabling ? 'cocart_install_jwt' : 'cocart_deactivate_jwt';

		$jwtUnsavedModal.attr( 'hidden', true );
		$toggle.prop( 'disabled', true );
		$state.css( 'visibility', 'hidden' );
		var $spinner = $( '<span class="spinner is-active" style="float:none;margin:0 0 0 6px;vertical-align:middle;"></span>' );
		$label.after( $spinner );

		$.post(
			config.ajaxUrl,
			{ action: action, nonce: config.nonce },
			function( response ) {
				$toggle.prop( 'disabled', false );
				$spinner.remove();
				$state.css( 'visibility', '' );
				if ( response.success ) {
					if ( enabling ) {
						window.location.reload();
					} else {
						$label.attr( 'title', config.i18n.disabled );
						$state.text( config.i18n.disabled );
						showToast( config.i18n.jwtDeactivated, 'success' );
					}
				} else {
					$toggle.prop( 'checked', ! enabling );
					showToast( config.i18n.jwtError, 'error' );
				}
			}
		);
	}

	$( '#cocart-jwt-unsaved-proceed' ).on( 'click', function() {
		runJwtToggle( $( '#auth-jwt-toggle' ).prop( 'checked', true ) );
	} );

	$( '#auth-jwt-toggle' ).on( 'change', function() {
		var $toggle  = $( this );
		var enabling = this.checked;

		if ( enabling && isDirty ) {
			$toggle.prop( 'checked', false );
			$jwtUnsavedModal.removeAttr( 'hidden' );
			$( '#cocart-jwt-unsaved-proceed' ).trigger( 'focus' );
			return;
		}

		runJwtToggle( $toggle );
	} );

	// --- Filter info modal ---
	var $modal      = $( '#cocart-filter-modal' );
	var $modalTitle = $( '#cocart-filter-modal-title' );
	var $modalBody  = $( '#cocart-filter-modal-body' );

	function openFilterModal( filterName ) {
		var entries = config.filterInfo[ filterName ] || [];

		$modalTitle.text( config.i18n.filterModalTitle + ': ' + filterName );

		if ( ! entries.length ) {
			$modalBody.html( '<p>' + config.i18n.filterNone + '</p>' );
		} else {
			var rows = '';
			$.each( entries, function( _, entry ) {
				var location = entry.file ?
					entry.file + ( entry.line ? ':' + entry.line : '' ) :
					config.i18n.filterUnknown;
				var fileCell = '<code>' + $( '<span>' ).text( location ).html() + '</code>';

				rows += '<tr>' +
					'<td><code>' + $( '<span>' ).text( entry.label ).html() + '</code></td>' +
					'<td>' + entry.priority + '</td>' +
					'<td>' + fileCell + '</td>' +
					'</tr>';
			} );
			$modalBody.html(
				'<table class="cocart-filter-modal-table widefat striped">' +
				'<thead><tr>' +
				'<th>' + config.i18n.filterCallback + '</th>' +
				'<th>' + config.i18n.filterPriority + '</th>' +
				'<th>' + config.i18n.filterFile + '</th>' +
				'</tr></thead>' +
				'<tbody>' + rows + '</tbody>' +
				'</table>'
			);
		}

		$modal.removeAttr( 'hidden' );
		$modal.find( '.cocart-modal-close' ).trigger( 'focus' );
	}

	function closeFilterModal() {
		$modal.attr( 'hidden', true );
	}

	$( document ).on( 'click', '.cocart-filter-info-btn', function() {
		openFilterModal( $( this ).data( 'filter' ) );
	} );

	// --- Upgrade feature info modal ---
	var $upgradeModal = $( '#cocart-upgrade-modal' );

	function openUpgradeModal() {
		$upgradeModal.removeAttr( 'hidden' );
		$upgradeModal.find( '.cocart-modal-close' ).trigger( 'focus' );
	}

	function closeUpgradeModal() {
		$upgradeModal.attr( 'hidden', true );
	}

	$( document ).on( 'click', '.cocart-upgrade-info-btn', function() {
		openUpgradeModal();
	} );

	$( document ).on( 'click', '.cocart-modal-close, .cocart-modal-backdrop', function() {
		var $openModal = $( this ).closest( '.cocart-modal' );

		if ( $openModal.is( $modal ) ) {
			closeFilterModal();
		} else if ( $openModal.is( $upgradeModal ) ) {
			closeUpgradeModal();
		}
	} );

	$( document ).on( 'change', '.cocart-settings-toggle', function() {
		var label = $( this ).prop( 'checked' ) ? config.i18n.enabled : config.i18n.disabled;
		$( this ).closest( 'td' ).find( '.cocart-status-label' ).text( label );
	} );

	$( document ).on( 'keydown', function( e ) {
		if ( 27 !== e.which ) {
			return;
		}

		if ( ! $modal.attr( 'hidden' ) ) {
			closeFilterModal();
		} else if ( ! $upgradeModal.attr( 'hidden' ) ) {
			closeUpgradeModal();
		}
	} );

} )( jQuery );
