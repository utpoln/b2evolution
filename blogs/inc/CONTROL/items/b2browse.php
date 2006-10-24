<?php
/**
 * This file implements the UI controller for the browsing posts.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package admin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


$AdminUI->title = $AdminUI->title_titlearea = T_('Browse blog:');

param( 'action', 'string', 'list' );

$blog = autoselect_blog( param( 'blog', 'integer', 0 ), 'blog_ismember', 1 );

if( ! $blog  )
{ // No blog could be selected
	$Messages->add( sprintf( T_('Since you\'re a newcomer, you\'ll have to wait for an admin to authorize you to post. You can also <a %s>e-mail the admin</a> to ask for a promotion. When you\'re promoted, just reload this page and you\'ll be able to blog. :)'),
									 'href="mailto:'. $admin_email. '?subject=b2-promotion"' ), 'error' );
	$tab = 'postlist2';
}
else
{ // We could select a valid blog which we have permission to access:
	$BlogCache = & get_Cache( 'BlogCache' );
	$Blog = & $BlogCache->get_by_ID( $blog );
	$AdminUI->title .= ' '.$Blog->dget( 'shortname' );


	// This is used in the display templates
	// TODO: have a method of some object ?
	$add_item_url = '?ctrl=edit&amp;blog='.$blog;

	// Store/retrieve preferred tab from UserSettings:
	$tab = $UserSettings->param_Request( 'tab', 'pref_browse_tab', 'string', NULL, true /* memorize */ );

	param( 'show_past', 'integer', '0', true );
	param( 'show_future', 'integer', '0', true );
	if( ($show_past == 0) && ( $show_future == 0 ) )
	{
		$show_past = 1;
		$show_future = 1;
	}

	switch( $tab )
	{
		case 'postlist2':
		case 'posts':
		case 'tracker':
			/*
			 * Let's go the clean new way...
			 */
			require_once $model_path.'items/_itemlist2.class.php';

			// Create empty List:
			$ItemList = & new ItemList2( $Blog, NULL, NULL );

			$ItemList->set_default_filters( array(
					'visibility_array' => array( 'published', 'protected', 'private', 'draft', 'deprecated' ),
				) );

			if( $tab == 'tracker' )
			{	// In tracker mode, we want a different default sort:
				$ItemList->set_default_filters( array(
						'orderby' => 'priority',
						'order' => 'ASC' ) );
			}

			// Init filter params:
			if( ! $ItemList->load_from_Request() )
			{ // If we could not init a filterset from request
				// typically happens when we could no fall back to previously saved filterset...
				// echo ' no filterset!';
			}


			if( $ItemList->single_post )
			{	// We have requested a specific post
				// hack this over to the exp tab
				$tab = 'posts';
			}


			switch( $tab )
			{
				case 'postlist2':
				case 'tracker':
					// DO **NOT** Run the query yet! (we want column definitions to be loaded and act as ORDER BY fields)
					break;

				case 'posts':
					// Run the query:
					$ItemList->query();

					// Old style globals for category.funcs:
					$postIDlist = $ItemList->get_page_ID_list();
					$postIDarray = $ItemList->get_page_ID_array();

					param( 'c',  'integer', 0 ); // Display comments?
					param( 'tb', 'integer', 0 ); // Display trackbacks?
					param( 'pb', 'integer', 0 ); // Display pingbacks?
					break;
			}
			break;


		case 'comments':
			/*
			 * Latest comments:
			 */
			param( 'show_statuses', 'array', array(), true );	// Array of cats to restrict to

			$CommentList = & new CommentList( $blog, "'comment','trackback','pingback'", $show_statuses, '',	'',	'DESC',	'',	20 );
			break;


		default:
			debug_die( 'Unhandled content; tab='.$tab );
	}
}


/*
 * Add sub menu entries.
 * We do this here instead of _header because we need to include all filter params into regenerate_url()
 * Note: this will override default tabs from _header config.
 * TODO: check if this is still needed
 */
$AdminUI->add_menu_entries(
		'edit',
		array(
				'postlist2' => array(
					'text' => T_('Post list'),
					'href' => regenerate_url( 'tab', 'tab=postlist2&amp;filter=restore' ),
					),
				'tracker' => array(
					'text' => T_('Tracker'),
					'href' => regenerate_url( 'tab', 'tab=tracker&amp;filter=restore' ),
					),
				'posts' => array(
					'text' => T_('Full posts'),
					'href' => regenerate_url( 'tab', 'tab=posts&amp;filter=restore' ),
					),
			/*	'commentlist' => array(
					'text' => T_('Comment list'),
					'href' => 'tab=commentlist ), */
				'comments' => array(
					'text' => T_('Comments'),
					'href' => regenerate_url( 'tab', 'tab=comments' ),
					),
			)
	);


$AdminUI->set_path( 'edit', $tab );

// Generate available blogs list:
$blogListButtons = $AdminUI->get_html_collection_list( 'blog_ismember', 1, $dispatcher.'?ctrl=browse&amp;blog=%d&amp;tab='.$tab.'&amp;filter=restore' );

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();


if( $blog )
{ // We could select a valid blog which we have permission to access:
	// Begin payload block:
	$AdminUI->disp_payload_begin();

	switch( $tab )
	{
		case 'comments':
			// Display VIEW:
			$AdminUI->disp_view( 'comments/_browse_comments.inc.php' );
			break;

		default:
			// fplanque> Note: this is depressing, but I have to put a table back here
			// just because IE supports standards really badly! :'(
			echo '<table class="browse" cellspacing="0" cellpadding="0" border="0"><tr>';

			echo '<td class="browse_left_col">';
				switch( $tab )
				{
					case 'postlist2':
						// Display VIEW:
						$AdminUI->disp_view( 'items/_browse_posts_list2.view.php' );
						break;

					case 'posts':
						// Display VIEW:
						$AdminUI->disp_view( 'items/_browse_posts_exp.inc.php' );
						break;

					case 'tracker':
						// Display VIEW:
						$AdminUI->disp_view( 'items/_browse_tracker.inc.php' );
						break;
				}
			echo '</td>';

			echo '<td class="browse_right_col">';
				// Display VIEW:
				$AdminUI->disp_view( 'items/_browse_posts_sidebar.inc.php' );
			echo '</td>';

			echo '</tr></table>';
	}

	// End payload block:
	$AdminUI->disp_payload_end();
}

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();
?>