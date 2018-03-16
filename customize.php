<?php
/**
 * This is the main dispatcher for the customize interface, to edit collection settings from front-office
 *
 * ---------------------------------------------------------------------------------------------------------------
 * IF YOU ARE READING THIS IN YOUR WEB BROWSER, IT MEANS THAT YOU DID NOT LOAD THIS FILE THROUGH A PHP WEB SERVER. 
 * TO GET STARTED, GO TO THIS PAGE: http://b2evolution.net/man/getting-started
 * ---------------------------------------------------------------------------------------------------------------
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2017 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 * Parts of this file are copyright (c)2005-2006 by PROGIDISTRI - {@link http://progidistri.com/}.
 *
 * @package main
 */


/**
 * Do the MAIN initializations:
 */
require_once dirname(__FILE__).'/conf/_config.php';

/**
 * Check this: we are requiring _main.inc.php INSTEAD of _blog_main.inc.php because we are not
 * trying to initialize any particular blog
 */
require_once $inc_path.'_main.inc.php';

$Timer->start( 'customize.php' );

// Enable customizer mode:
set_param( 'customizer_mode', 'enable' );

param( 'customizing_url', 'url', NULL, true );
param( 'blog', 'integer', true, true );
param( 'view', 'string', '', true );

// Getting current blog info:
$BlogCache = & get_BlogCache();
/**
 * @var Blog
 */
$Collection = $Blog = & $BlogCache->get_by_ID( $blog, false, false );
if( empty( $Blog ) )
{
	load_funcs( 'skins/_skin.funcs.php' );
	siteskin_init();
	siteskin_include( '_404_blog_not_found.main.php' ); // error
	exit(0);
	// EXIT.
}

// Try to get a collection access type in order to know if it has been changed temporarily for fix:
$coll_access_type = $Blog->get( 'access_type' );
if( isset( $Blog->orig_access_type, $Blog->orig_siteurl ) )
{	// Fix frame origin blocking when collection used another domain than base site URL:
	$forced_coll_url = $Blog->gen_blogurl();
	$orig_coll_url = $Blog->gen_blogurl( 'original' );

	// Use forced collection URL with same domain in order to avoid restriction of frame origin:
	$customizing_url = url_add_param( $forced_coll_url, preg_replace( '#^'.preg_quote( $orig_coll_url ).'[/\?]?#', '', $customizing_url ) );
}

if( empty( $view ) )
{	// If view is not defined try to get it from user settings per collection or set default:
	$view = $UserSettings->get( 'customizer_view_'.$blog );
	if( empty( $view ) )
	{	// Display collection skin settings by default:
		$view = 'coll_skin';
	}
	memorize_param( 'view', 'string', '', $view );
}

// Allow to enable widgets designer mode only when user opens sub menu "Widgets" from the left panel of customer mode:
set_param( 'designer_mode', $view == 'coll_widgets' ? 'enable' : 'disable' );

// Initialize modes to debug and customize collection settings:
initialize_debug_modes();

load_funcs( 'skins/_skin.funcs.php' );

// Initialize font-awesome icons and use them as a priority over the glyphicons, @see get_icon()
init_fontawesome_icons( 'fontawesome-glyphicons' );

add_js_headline( 'var customizer_url = "'.$customizer_url.'";'
	.'var evo_js_lang_not_controlled_page = \''.TS_('This page is not controlled by b2evolution.').'\'' );
require_css( 'bootstrap-b2evo_base.bmin.css' );
require_js( '#jquery#' );
require_js( 'src/evo_customizer.js' );
require_js( '#bootstrap#' );
require_css( '#bootstrap_css#' );
require_js( 'build/bootstrap-evo_frontoffice.bmin.js' );

// Send the predefined cookies:
evo_sendcookies();

headers_content_mightcache( 'text/html' );		// In most situations, you do NOT want to cache dynamic content!
?>
<!DOCTYPE html>
<html lang="<?php locale_lang() ?>" class="evo_customizer__html">
<head>
	<base href="<?php echo $baseurl; ?>">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, follow" />
	<title><?php printf( T_('Customizing Collection: %s'), $Blog->dget( 'shortname', 'htmlhead' ) ); ?></title>
	<?php include_headlines() /* Add javascript and css files included by plugins and skin */ ?>
	<?php echo_modalwindow_js(); /* Initialize JavaScript to build and open window */ ?>
</head>
<body<?php skin_body_attrs(); ?>>
	<?php
	// ---------------------------- TOOLBAR INCLUDED HERE ----------------------------
	require skin_fallback_path( '_toolbar.inc.php' );
	// ------------------------------- END OF TOOLBAR --------------------------------
	?>
	<div class="evo_customizer__wrapper">
		<div class="evo_customizer__left">
			<iframe id="evo_customizer__backoffice" src="<?php echo $admin_url.'?ctrl=customize&amp;view='.$view.'&amp;blog='.$blog; ?>" data-instance="<?php echo $instance_name; ?>" data-coll-id="<?php echo $Blog->ID; ?>"></iframe>
		</div>
		<div class="evo_customizer__right">
			<iframe id="evo_customizer__frontoffice" src="<?php echo url_add_param( $customizing_url, 'customizer_mode=enable&amp;show_toolbar=hidden&amp;redir=no' ); ?>" data-coll-url="<?php echo format_to_output( $Blog->get( 'url' ), 'htmlattr' ); ?>"></iframe>
			<div id="evo_customizer__frontoffice_loader"></div>
		</div>
		<iframe id="evo_customizer__updater" name="evo_customizer__updater" style="display:none"></iframe>
		<div id="evo_customizer__vtoggler" class="evo_customizer__vtoggler"></div>
	</div>
</body>
</html><?php
$Timer->stop( 'customize.php' );
?>