<?php
/**
 * This file implements the Email Elements plugin for b2evolution
 *
 * Email Elements
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package plugins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

class email_elements_plugin extends Plugin
{
	var $code = 'b2evEmailEl';
	var $name = 'Email Elements';
	var $priority = 50;
	var $version = '6.10.6';
	var $group = 'rendering';
	var $short_desc;
	var $long_desc;
	var $help_topic = 'email-elements-plugin';
	var $number_of_installs = 1;

	var $cta_numbers = array( 1, 2, 3 );
	var $button_styles = array( 'primary', 'success', 'warning', 'danger', 'info', 'default', 'link', 'image' );
	var $default_button_styles = array(
			'button'      => 'primary',
			'cta'         => 'primary',
			'like'        => 'success',
			'dislike'     => 'danger',
			'activate'    => 'success',
			'unsubscribe' => 'link',
		);

	/**
	 * Init
	 */
	function PluginInit( & $params )
	{
		$this->short_desc = T_('Email Elements');
		$this->long_desc = T_('Enables users to add UI elements to emails.');
	}


	/**
	 * Define here the default collection/blog settings that are to be made available in the backoffice
	 *
	 * @param array Associative array of parameters.
	 * @return array See {@link Plugin::GetDefaultSettings()}.
	 */
	function get_coll_setting_definitions( & $params )
	{
		$default_params = array_merge( $params, array( 'default_post_rendering' => 'never' ) );
		return parent::get_coll_setting_definitions( $default_params );
	}


	/**
	 * Define here default email settings that are to be made available in the backoffice.
	 *
	 * @param array Associative array of parameters.
	 * @return array See {@link Plugin::GetDefaultSettings()}.
	 */
	function get_email_setting_definitions( & $params )
	{
		// set params to allow rendering for emails by default:
		$default_params = array_merge( $params, array( 'default_email_rendering' => 'opt-out' ) );
		return parent::get_email_setting_definitions( $default_params );
	}


	/**
	 * Display Toolbar
	 *
	 * @param object Blog
	 */
	function DisplayCodeToolbar( $params = array() )
	{
		global $Hit, $baseurl, $debug;

		if( $Hit->is_lynx() )
		{	// let's deactivate toolbar on Lynx, because they don't work there:
			return false;
		}

		$params = array_merge( array(
				'js_prefix' => '', // Use different prefix if you use several toolbars on one page
			), $params );

		$js_code_prefix = $params['js_prefix'].$this->code;

		// Initialize JavaScript to build and open window:
		echo_modalwindow_js();

		$Form = new Form();
		$Form->output = false;
		$cta_select = $Form->select_input_array( 'cta_num', NULL, $this->cta_numbers, T_('CTA number') );
		$style_select = $Form->select_input_array( 'button_type', NULL, $this->button_styles, T_('Link/Button style') );
		$image_link_ID_input = '<div id="image_link_ID_wrapper" style="display:none">'.$Form->text_input( 'image_link_ID', '', NULL, T_('Image Link ID'), '', array( 'required' => true ) ).'</div>';
		$button_text_input = $Form->text_input( 'button_text', '', NULL, T_('Text'), '', array( 'style' => 'width:100%;' ) );
		$button_url_input = $Form->text_input( 'button_url', '', NULL, T_('URL'), '', array( 'style' => 'width:100%;' ) );

		?><script>
		//<![CDATA[
		function email_elements_toolbar( title, prefix )
		{
			var r = '<?php echo format_to_js( $this->get_template( 'toolbar_title_before' ) );?>' + title + '<?php echo format_to_js( $this->get_template( 'toolbar_title_after' ) );?>'
				+ '<?php echo format_to_js( $this->get_template( 'toolbar_group_before' ) ); ?>'

				// Button element
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button (or link) for any use'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|button" value="<?php echo format_to_output( TS_('Button'), 'htmlattr' );?>" />'

				// Call to Action Button element
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button (or link) which additionally records CTA stats'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|cta" value="<?php echo format_to_output( TS_('Call to Action'), 'htmlattr' );?>" />'

				// Like Button element
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button which records a like'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|like" value="<?php echo format_to_output( TS_('Like'), 'htmlattr' );?>" />'

				// Dislike Button element
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button which record a dislike'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|dislike" value="<?php echo format_to_output( TS_('Dislike'), 'htmlattr' );?>" />'

				// Activate
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button which activates the User\'s account'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|activate" value="<?php echo format_to_output( TS_('Activate'), 'htmlattr' );?>" />'

				// Unsubscribe
				+ '<input type="button" title="<?php echo format_to_output( TS_('Button which Unsubscribes the User from the current List'), 'htmlattr' );?>"'
				+ ' class="<?php echo $this->get_template( 'toolbar_button_class' );?>"'
				+ ' data-func="<?php echo $js_code_prefix;?>_insert_button|unsubscribe" value="<?php echo format_to_output( TS_('Unsubscribe'), 'htmlattr' );?>" />'

				+ '<?php echo format_to_js( $this->get_template( 'toolbar_group_after' ) );?>';

				jQuery( '.' + prefix + '<?php echo $this->code;?>_toolbar' ).html( r );
		}

		<?php echo $js_code_prefix;?>_insert_button = function( type )
		{
			var modal_window_title;
			var r = '<form id="email_element_button_wrapper" class="form-horizontal">';

			if( type == 'cta' )
			{
				r += '<?php echo format_to_js( $cta_select );?>';
			}

			r += '<?php echo format_to_js( $style_select );?>';
			r += '<?php echo format_to_js( $image_link_ID_input );?>';

			if( type != 'unsubscribe' )
			{
				r += '<?php echo format_to_js( $button_url_input );?>';
			}

			r += '<?php echo format_to_js( $button_text_input );?>';

			r += '</form>';

			switch( type )
			{
				case 'button':
					modal_window_title = '<?php echo TS_('Add a link button');?>';
					break;

				case 'like':
					modal_window_title = '<?php echo TS_('Add a like button');?>';
					break;

				case 'dislike':
					modal_window_title = '<?php echo TS_('Add a dislike button');?>';
					break;

				case 'cta':
					modal_window_title = '<?php echo TS_('Add a call to action button');?>';
					break;

				case 'activate':
					modal_window_title = '<?php echo TS_('Add an activate button');?>';
					break;

				case 'unsubscribe':
					modal_window_title = '<?php echo TS_('Add an unsubscribe button');?>';
					break;
			}

			openModalWindow( r, '600px', '', true,
					modal_window_title, // Window title
					[ '-', 'email_element_button_buttons' ],
					true );

			// Set max-height to keep the action buttons on screen:
			var modal_window = jQuery( '#email_element_button_wrapper' ).parent();
			var modal_height = jQuery( window ).height() - 20;

			if( modal_window.hasClass( 'modal-body' ) )
			{	// Extract heights of header and footer:
				modal_height -= 55 + 64 +
					parseInt( modal_window.css( 'padding-top' ) ) + parseInt( modal_window.css( 'padding-bottom' ) );
			}
			modal_window.css( {
				'display': 'block',
				'overflow': 'auto',
				'max-height': modal_height
			} );

			// Add insert button:
			var buttons_side_obj = jQuery( '.email_element_button_buttons' ).length ?
						jQuery( '.email_element_button_buttons' ) :
						jQuery( '#email_element_button_buttons' );
			buttons_side_obj.after( '<button id="email_element_button_insert" class="btn btn-primary" data-function="' + type + '"><?php echo TS_('Insert');?></button>' );

			// Set button type dropdown default:
			var button_defaults = { <?php
			$js_default_button_styles = array();
			foreach( $this->default_button_styles as $button_type => $button_style )
			{
				$js_default_button_styles[] = '\''.$button_type.'\': \''.$button_style.'\'';
			}
			echo implode( ', ', $js_default_button_styles );
			?> };
			jQuery( 'select[name=button_type]', '#email_element_button_wrapper' ).val( button_defaults[type] );

			// To prevent link default event:
			return false;
		}

		// Show/Hide additional fields depending on "Link/Button style":
		jQuery( document ).on( 'change', '#email_element_button_wrapper select[name=button_type]', function()
		{
			if( jQuery( this ).val() == 'image' )
			{
				jQuery( 'label[for=button_text]', '#email_element_button_wrapper' ).html( '<?php echo TS_('Alt text'); ?>:' );
				jQuery( '#image_link_ID_wrapper', '#email_element_button_wrapper' ).show();
			}
			else
			{
				jQuery( 'label[for=button_text]', '#email_element_button_wrapper' ).html( '<?php echo TS_('Text'); ?>:' );
				jQuery( '#image_link_ID_wrapper', '#email_element_button_wrapper' ).hide();
			}
		} );

		// Insert a button short tag to textarea
		jQuery( document ).on( 'click', '#email_element_button_insert', function()
		{
			var type = jQuery( this ).data( 'function' );
			var url = jQuery( 'input[name=button_url]', '#email_element_button_wrapper' ).val();
			var text = jQuery( 'input[name=button_text]', '#email_element_button_wrapper' ).val();
			var button_type = jQuery( 'select[name=button_type]', '#email_element_button_wrapper' ).val();
			var myField = <?php echo $params['js_prefix']; ?>b2evoCanvas;
			var shortTag;

			if( button_type == 'image' )
			{	// Append a link ID for image style:
				button_type += '#' + jQuery( 'input[name=image_link_ID]', '#email_element_button_wrapper' ).val();
			}

			// Insert tag text in area
			switch( type )
			{
				case 'button':
					shortTag = '[button' + ':' + button_type + ( url == '' ? '' : ':' + url ) + ']'+text+'[/button]';
					break;

				case 'like':
					shortTag = '[like' + ':' + button_type + ( url == '' ? '' : ':' + url ) + ']'+text+'[/like]';
					break;

				case 'dislike':
					shortTag = '[dislike' + ':' + button_type + ( url == '' ? '' : ':' + url ) + ']'+text+'[/dislike]'
					break;

				case 'cta':
					var cta_num = jQuery( 'select[name=cta_num]', '#email_element_button_wrapper' ).val();
					shortTag = '[cta:' + cta_num + ':' + button_type + ( url == '' ? '' : ':' + url ) + ']'+text+'[/cta]'
					break;

				case 'activate':
					shortTag = '[activate' + ':' + button_type + ( url == '' ? '' : ':' + url ) + ']'+text+'[/activate]';
					break;

				case 'unsubscribe':
					shortTag = '[unsubscribe' + ':' + button_type + ']'+text+'[/unsubscribe]';
					break;
			}
			textarea_wrap_selection( myField, shortTag, '', 0 );
			// Close main modal window
			closeModalWindow();

			// To prevent link default event
			return false;
		} );

		//]]>
		</script><?php

		echo $this->get_template( 'toolbar_before', array( '$toolbar_class$' => $params['js_prefix'].$this->code.'_toolbar' ) );
		echo $this->get_template( 'toolbar_after' );
		?>
		<script>email_elements_toolbar( '<?php echo TS_('Email Elements:'); ?>', '<?php echo $params['js_prefix']; ?>' );</script>
		<?php

		return true;
	}


	/**
	 * Event handler: Called when displaying editor toolbars for email.
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we display a toolbar?
	 */
	function DisplayEmailToolbar( & $params )
	{
		$apply_rendering = $this->get_email_setting( 'email_apply_rendering' );
		if( ! empty( $apply_rendering ) && $apply_rendering != 'never' )
		{	// Print toolbar on screen:
			return $this->DisplayCodeToolbar( $params );
		}
		return false;
	}


	/**
	 * Dummy placeholder. Without it the plugin would ne be considered to be a renderer...
	 *
	 * @see Plugin::RenderItemAsHtml
	 */
	function RenderItemAsHtml( & $params )
	{
		return false;
	}


	/**
	 * Perform rendering of email
	 *
	 * @see Plugin::RenderEmailAsHtml()
	 */
	function RenderEmailAsHtml( & $params )
	{
		$content = & $params['data'];
		$default_destination = isset( $params['EmailCampaign'] ) && !empty( $params['EmailCampaign']->email_defaultdest ) ? $params['EmailCampaign']->email_defaultdest : '';

		$search_pattern = '#\[(button|like|dislike|cta|activate|unsubscribe):?([^\[\]]*?)](.*?)\[\/\1]#';
		preg_match_all( $search_pattern, $content, $matches );

		if( ! empty( $matches[0] ) )
		{
			foreach( $matches[0] as $i => $current_element )
			{
				$type = $matches[1][$i];
				$text = trim( $matches[3][$i] );
				$index_shift = ( $type == 'cta' ? 1 : 0 ); // CTA buttons have additional param for a number after type like [cta:1:...]
				$options = explode( ':', $matches[2][$i], 2 + $index_shift );

				$style = explode( '#', $options[ $index_shift ] );
				$style_link_ID = isset( $style[1] ) ? intval( $style[1] ) : NULL;
				$style = $style[0];
				if( in_array( $style, $this->button_styles ) )
				{	// If 2nd/3rd option is a style then 3rd is an URL:
					$url = isset( $options[ 1 + $index_shift ] ) ? trim( $options[ 1 + $index_shift ] ) : NULL;
				}
				else
				{	// Otherwise 2nd/3rd option is an URL, i.e. this short tag should use default style:
					$url = trim( $matches[2][$i] );
					$style = $this->default_button_styles[ $type ];
				}
				if( empty( $style ) )
				{	// Use default button style if it is not defined in short tag:
					$style = $this->default_button_styles[ $type ];
				}
				if( $style == 'image' )
				{	// Check if correct image link ID is used:
					$image_File = NULL;
					$LinkCache = & get_LinkCache();
					$current_element_error_message = NULL;
					if( ! ( $image_Link = & $LinkCache->get_by_ID( $style_link_ID, false, false ) ) )
					{	// Link is not found in DB:
						$current_element_error_message = sprintf( T_('Link %s doesn\'t exist!'), '#'.$style_link_ID );
					}
					elseif( ! ( $image_File = & $image_Link->get_File() ) || ! $image_File->is_image() )
					{	// File is not image:
						$current_element_error_message = sprintf( T_('File of the Link %s is not an image!'), '#'.$style_link_ID );
					}
					if( $current_element_error_message !== NULL )
					{	// Replace original short tag with error message:
						$content = str_replace( $current_element, '<span class="evo_param_error">'.$current_element.' - '.$current_element_error_message.'</span>', $content );
						continue;
					}
					// If image file is correct we should display it instead of text:
					$text = $image_Link->get_tag( array(
						'before_image'        => '',
						'before_image_legend' => NULL,
						'after_image'         => '',
						'image_link_to'       => false,
						'image_alt'           => $text,
						'add_loadimg'         => false,
					) );
				}

				if( empty( $url ) && in_array( $type, array( 'button', 'like', 'dislike', 'cta' ) ) )
				{	// Use default destination of the Email Campaign if optional URL if not defined:
					$url = $default_destination;
				}
				$url_params = NULL;

				switch( $type )
				{
					case 'like':
					case 'dislike':
						$url_params = array( 'evo_mail_function' => $type );
						break;

					case 'cta':
						$cta_num = trim( $options[0] );
						if( ! in_array( $cta_num, $this->cta_numbers ) )
						{	// Don't allow CTA with wrong number:
							unset( $url );
							break;
						}

						$url_params = array( 'evo_mail_function' => $type.$cta_num );
						break;

					case 'activate':
						// Only EASY activation will work for this case:
						$url = get_htsrv_url().'login.php?action=activateacc_ez&userID=$user_ID$&reminderKey=$reminder_key$';

						$redirect_to = isset( $options[1] ) ? trim( $options[1] ) : NULL;
						if( ! empty( $redirect_to ) )
						{	// Append a redirect URL after activation if it is defined in options of the short tag:
							$url = url_add_param( $url, array( 'redirect_to' => $redirect_to ) );
						}
						break;

					case 'unsubscribe':
						$url = get_htsrv_url().'quick_unsubscribe.php?type=newsletter&newsletter=$newsletter_ID$&user_ID=$user_ID$&key=$unsubscribe_key$';
						break;
				}

				if( ! empty( $text ) && ! empty( $url ) && ! validate_url( $url ) )
				{	// If button/link is correct and can be rendered:
					if( $url_params !== NULL )
					{	// Additional URL param:
						$url = url_add_param( $url, $url_params );
					}
					$link_tag = get_link_tag( $url, $text, in_array( $style, array( 'link', 'image' ) ) ? '' : 'div.btn a+a.btn-'.$style );
					// Render short tag:
					$content = str_replace( $current_element, $link_tag, $content );
				}
				// Otherwise keep the wrong short tag as is without rendering.
			}
		}

		return true;
	}
}