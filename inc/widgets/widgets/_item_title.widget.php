<?php
/**
 * This file implements the item_title Widget class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author erhsatingin: Erwin Rommel Satingin.
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'widgets/model/_widget.class.php', 'ComponentWidget' );

/**
 * ComponentWidget Class
 *
 * A ComponentWidget is a displayable entity that can be placed into a Container on a web page.
 *
 * @package evocore
 */
class item_title_Widget extends ComponentWidget
{
	var $icon = 'header';

	/**
	 * Constructor
	 */
	function __construct( $db_row = NULL )
	{
		// Call parent constructor:
		parent::__construct( $db_row, 'core', 'item_title' );
	}


	/**
	 * Get help URL
	 *
	 * @return string URL
	 */
	function get_help_url()
	{
		return get_manual_url( 'item-title-widget' );
	}


	/**
	 * Get name of widget
	 */
	function get_name()
	{
		return T_('Item Title');
	}


	/**
	 * Get a very short desc. Used in the widget list.
	 */
	function get_short_desc()
	{
		return format_to_output( T_('Item Title') );
	}


	/**
	 * Get short description
	 */
	function get_desc()
	{
		return T_('Display the title of the item.');
	}


	/**
	 * Get definitions for editable params
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_param_definitions( $params )
	{
		global $Blog;

		$r = array_merge( array(
				'title' => array(
					'label' => T_( 'Title' ),
					'size' => 40,
					'note' => T_( 'This is the title to display' ),
					'defaultvalue' => '',
				),
			), parent::get_param_definitions( $params ) );

		if( isset( $r['allow_blockcache'] ) )
		{	// Disable "allow blockcache" because this widget displays dynamic data:
			$r['allow_blockcache']['defaultvalue'] = false;
			$r['allow_blockcache']['disabled'] = 'disabled';
			$r['allow_blockcache']['note'] = T_('This widget cannot be cached in the block cache.');
		}

		return $r;
	}


	/**
	 * Display the widget!
	 *
	 * @param array MUST contain at least the basic display params
	 */
	function display( $params )
	{
		global $Item, $disp;

		if( empty( $Item ) )
		{ // Don't display this widget when there is no Item object:
			$this->display_debug_message( 'Widget "'.$this->get_name().'" is hidden because there is no Item.' );
			return false;
		}

		$this->init_display( $params );

		$this->disp_params = array_merge( array(
			  'widget_item_title_display' => true,
				'widget_item_title_params'  => array(),
			), $this->disp_params );

		$widget_params = array(
				// Parameters for item title:
				'before'    => '',
				'after'     => '',
				'link_type' => '#',
				// Parameters for edit link:
				'edit_link_display' => false,
				'before_edit_link'  => '<div class="'.button_class( 'group' ).'">',
				'after_edit_link'   => '</div>',
				'edit_link_text'    => $Item->is_intro() ? get_icon( 'edit' ).' '.T_('Edit Intro') : '#',
				'edit_link_class'   => button_class( 'text' ),
			);

		$this->disp_params['widget_item_title_params'] = array_merge( $widget_params, $this->disp_params['widget_item_title_params'] );
		$widget_params = $this->disp_params['widget_item_title_params'];

		if( $this->disp_params['widget_item_title_display'] )
		{
			echo $this->disp_params['block_start'];
			$this->disp_title();
			echo $this->disp_params['block_body_start'];

			$Item->title( array(
				'before' => $widget_params['before'],
				'after'  => $widget_params['after'],
				'link_type' => $widget_params['link_type'],
			) );

			if( $widget_params['edit_link_display'] )
			{
				$Item->edit_link( array(
					'before' => $widget_params['before_edit_link'],
					'after'  => $widget_params['after_edit_link'],
					'text'   => $widget_params['edit_link_text'],
					'class'  => $widget_params['edit_link_class'],
				) );
			}

			echo $this->disp_params['block_body_end'];
			echo $this->disp_params['block_end'];

			return true;
		}

		return false;
	}
}

?>