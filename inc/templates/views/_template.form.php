<?php
/**
 * This file display the template form
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2020 by Francois Planque - {@link http://fplanque.com/}.
 *
 * @package templates
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


global $edited_Template, $locales, $AdminUI;
global $localtimenow;

// Determine if we are creating or updating...
global $action;
$creating = is_create_action( $action );

$Form = new Form( NULL, 'template_checkchanges', 'post', 'compact' );

$Form->global_icon( T_('Cancel editing').'!', 'close', regenerate_url( 'action,tpl_ID,blog' ) );

if( $action == 'copy' )
{
	$fieldset_title = T_('Duplicate template').get_manual_link( 'template-form');
}
else
{
	$fieldset_title = $creating ?  T_('New Template') . get_manual_link( 'template-form' ) : T_('Template') . get_manual_link( 'template-form' );
}

$Form->begin_form( 'fform', $fieldset_title );

	$Form->hidden( 'ctrl', 'templates' );
	$Form->add_crumb( 'template' );
	if( $action == 'copy' )
	{
		$Form->hidden( 'action', 'duplicate' );
		$Form->hidden( 'tpl_ID', $edited_Template->ID );
	}
	else
	{
		$Form->hidden( 'action',  $creating ? 'create' : 'update' );
		if( ! $creating )
		{
			$Form->hidden( 'tpl_ID', $edited_Template->ID );
		}
	}
	
	// Template name:
	$Form->text_input( 'tpl_name', $edited_Template->get( 'name' ), 50, T_('Name'), '', array( 'maxlength' => 128, 'required' => true ) );

	// Template code:
	$Form->text_input( 'tpl_code', $edited_Template->get( 'code' ), 25, T_('Code'), '', array( 'maxlength' => 128 ) );

	// Context:
	$Form->select_input_array( 'tpl_context', $edited_Template->get( 'context' ), get_template_contexts(), T_('Context') );

	// Owner:
	$GroupCache = & get_GroupCache();
	$Form->select_object( 'tpl_owner_grp_ID', $edited_Template->get( 'owner_grp_ID' ), $GroupCache, T_('Owned by') );
        
        
        
	$Form->begin_line(T_('Date Time'), 'template_date', '', array('required' => true));

	$Form->date_input('template_date', date2mysql($edited_Template->start_timestamp), '', array('required' => true));
	echo ' ' . T_('at') . ' ';

	$Form->time_input('template_time', date2mysql($edited_Template->start_timestamp), '', array('required' => true));

	$Form->end_line();

	// Base template ID:
	$base_template_options = array( NULL => '('.TB_('None').')' );
	$SQL = new SQL('Get possible base templates');
	$SQL->SELECT( 'tpl_ID, tpl_name' );
	$SQL->FROM( 'T_templates' );
	$SQL->WHERE( 'tpl_translates_tpl_ID IS NULL' );
	if( $action != 'copy' )
	{
		$SQL->WHERE_and( 'NOT tpl_ID ='.$DB->quote( $edited_Template->ID ) );
	}
	$SQL->ORDER_BY( 'tpl_name ASC' );
	$base_template_options += $DB->get_assoc( $SQL->get() );
	$Form->select_input_array( 'tpl_translates_tpl_ID', $edited_Template->get('translates_tpl_ID'), $base_template_options, T_('Translation of'), NULL, array( 'force_keys_as_values' => true ) );

	// Locale:
	$locales_options = array();
	foreach( $locales as $locale_key => $locale_data )
	{
		if( $locale_data['enabled'] || $locale_key == $edited_Template->get( 'locale' ) )
		{
			$locales_options[ $locale_key ] = $locale_key;
		}
	}
	$Form->select_input_array( 'tpl_locale', $edited_Template->get( 'locale' ), $locales_options, T_('Locale') );
	
	// Template code:
	$Form->textarea( 'tpl_template_code', $edited_Template->get( 'template_code' ), 20, T_('Template code'), '', 80, '', true );

	$buttons = array();
	if( $current_User->check_perm( 'options', 'edit' ) )
	{	// Allow to save template if current User has a permission:
		if( $action == 'copy' )
		{
			$buttons = array(
					array( 'submit', 'actionArray[create]', T_('Duplicate Template!'), 'SaveButton' ),
					array( 'submit', 'actionArray[create_edit]', T_('Duplicate and continue editing...'), 'SaveButton' )
				);
		}
		else
		{
			$buttons = array(
					array( 'submit', 'actionArray['.( $creating ? 'create' : 'update' ).']', T_('Save!'), 'SaveButton' ),
					array( 'submit', 'actionArray['.( $creating ? 'create' : 'update' ).'_edit]', T_('Save and continue editing...'), 'SaveButton' )
				);
		}
	}

$Form->end_form( $buttons );
?>
