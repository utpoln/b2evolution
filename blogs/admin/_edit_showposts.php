<?php
/**
 * This file implements the post browsing
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes:
 */
require_once( dirname(__FILE__).'/'.$admin_dirout.$core_subdir.'_itemlist.class.php' );

echo '<div class="NavBar">';

	/*
	 * @movedTo _b2browse.php
	 */

	// Display title depending on selection params:
	request_title( '<h2>', '</h2>', '<br />', 'htmlbody', true, true, 'b2browse.php', 'blog='.$blog );

	if( !$posts )
	{
		if( $posts_per_page )
		{
			$posts = $posts_per_page;
		}
		else
		{
			$posts = 10;
			$posts_per_page = $posts;
		}
	}

	if( !$poststart )
	{
		$poststart = 1;
	}

	if( !$postend )
	{
		$postend = $poststart + $posts - 1;
	}

	$nextXstart = $postend + 1;
	$nextXend = $postend + $posts;

	$previousXstart = ($poststart - $posts);
	$previousXend = $poststart - 1;
	if( $previousXstart < 1 )
	{
		$previousXstart = 1;
	}

	require dirname(__FILE__). '/_edit_navbar.php';

echo '</div>';

/*
 * Display posts:
 */
while( $Item = $MainList->get_item() )
{
	?>
	<div class="bPost bPost<?php $Item->status( 'raw' ) ?>" lang="<?php $Item->lang() ?>">
		<?php
		// We don't switch locales in the backoffice, since we use the user pref anyway
		$Item->anchor(); ?>
		<div class="bSmallHead">
			<?php
				echo '<div class="bSmallHeadRight">';
				locale_flag( $Item->locale, 'h10px' );
				echo '</div>';

				echo '<strong>';
				$Item->issue_date();
 				echo '</strong> @ <strong>';
 				$Item->issue_time( 'H:i' );
 				echo '</strong>';
				// TRANS: backoffice: each post is prefixed by "date BY author IN categories"
				echo ' ', T_('by'), ' <acronym title="';
				$Item->Author->login();
				echo ', '.T_('level:');
				$Item->Author->level();
				echo '"><strong>';
				$Item->Author->preferred_name();
				echo '</strong></acronym>';

				echo '<div class="bSmallHeadRight">';
				echo T_('Visibility').': ';
				echo '<span class="Status">';
				$Item->status();
				echo '</span>';
				echo '</div>';

				echo '<br />';
				$Item->type( T_('Type').': <strong>', '</strong> &nbsp; ' );
				$Item->priority( T_('Priority').': <strong>', '</strong> &nbsp; ' );
				$Item->assigned_to( T_('Assigned to:').' <strong>', '</strong> &nbsp; ' );
				$Item->extra_status( T_('Task Status').': <strong>', '</strong>' );

				echo '<div class="bSmallHeadRight">';
				$Item->views();
				echo '</div>';

				echo '<br />'.T_('Categories').': ';
				$Item->categories( false );
			?>
		</div>

		<div class="bContent">
			<h3 class="bTitle"><?php $Item->title() ?></h3>
			<div class="bText">
				<?php
					$Item->content();
					link_pages( '<p class="right">'.T_('Pages:'), '</p>' );
				?>
			</div>
		</div>

		<div class="PostActionsArea">
			<?php
			echo '<a href="';
			$Item->permalink();
			echo '" title="'.T_('Permanent link to full entry').'" class="permalink_right">'.get_icon( 'permalink' ).'</a>';

			// Display edit button if current user has the rights:
			$Item->edit_link( ' ', ' ', '#', '#', 'ActionButton', $edit_item_url );

			// Display publish NOW button if current user has the rights:
			$Item->publish_link( ' ', ' ', '#', '#', 'PublishButton');

			// Display deprecate button if current user has the rights:
			$Item->deprecate_link( ' ', ' ', '#', '#', 'DeleteButton');

			// Display delete button if current user has the rights:
			$Item->delete_link( ' ', ' ', '#', '#', 'DeleteButton', false, $delete_item_url );

			if( $Blog->allowcomments != 'never' )
			{
 				echo '<a href="b2browse.php?tab=posts&amp;blog='.$blog.'&amp;p='.$Item->ID.'&amp;c=1" class="ActionButton">';
				// TRANS: Link to comments for current post
				comments_number(T_('no comment'), T_('1 comment'), T_('%d comments'));
				trackback_number('', ' &middot; '.T_('1 Trackback'), ' &middot; '.T_('%d Trackbacks'));
				pingback_number('', ' &middot; '.T_('1 Pingback'), ' &middot; '.T_('%d Pingbacks'));
				echo '</a>';
			} ?>
		</div>

		<?php
		// ---------- comments ----------
		if( $c )
		{ // We have request display of comments
			?>
 			<div class="bFeedback">
			<a name="comments"></a>
			<h4><?php echo T_('Comments'), ', ', T_('Trackbacks'), ', ', T_('Pingbacks') ?>:</h4>
			<?php

			$CommentList = & new CommentList( 0, "'comment','trackback','pingback'", $show_statuses, $Item->ID, '', 'ASC' );

			$CommentList->display_if_empty(
										'<div class="bComment"><p>' .
										T_('No feedback for this post yet...') .
										'</p></div>' );

			while( $Comment = & $CommentList->get_next() )
			{ // Loop through comments:
				?>
				<!-- ========== START of a COMMENT/TB/PB ========== -->
				<div class="bComment">
					<div class="bSmallHead">
						<?php
						$Comment->date();
						echo ' @ ';
						$Comment->time( 'H:i' );
						if( $Comment->author_url( '', ' &middot; Url: ', '' )
								&& $current_User->check_perm( 'spamblacklist', 'edit' ) )
						{ // There is an URL and we have permission to ban...
							// TODO: really ban the base domain! - not by keyword
							?>
							<a href="antispam.php?action=ban&amp;keyword=<?php
								echo rawurlencode(getBaseDomain($Comment->author_url))
								?>"><img src="img/noicon.gif" class="middle" alt="<?php echo /* TRANS: Abbrev. */ T_('Ban') ?>" title="<?php echo T_('Ban this domain!') ?>" /></a>&nbsp;
							<?php
						}
						$Comment->author_email( '', ' &middot; Email: ' );
						$Comment->author_ip( ' &middot; IP: ' );
					 ?>
					</div>
					<div class="bCommentContent">
					<div class="bCommentTitle">
					<?php
						switch( $Comment->get( 'type' ) )
						{
							case 'comment': // Display a comment:
								echo T_('Comment from:') ?>
								<?php break;

							case 'trackback': // Display a trackback:
								echo T_('Trackback from:') ?>
								<?php break;

							case 'pingback': // Display a pingback:
								echo T_('Pingback from:') ?>
								<?php break;
						}
					?>
					<?php $Comment->author() ?>
					</div>
					<div class="bCommentText">
						<?php $Comment->content() ?>
					</div>
					</div>
					<div class="CommentActionsArea">
					<a href="<?php $Comment->permalink() ?>" title="<?php echo T_('Permanent link to this comment')	?>" class="permalink_right"><img src="img/chain_link.gif" alt="<?php echo T_('Permalink') ?>" width="14" height="14" border="0" class="middle" /></a>
					<?php
		 				// Display edit button if current user has the rights:
						$Comment->edit_link( ' ', ' ', '#', '#', 'ActionButton');

						// Display delete button if current user has the rights:
						$Comment->delete_link( ' ', ' ', '#', '#', 'DeleteButton');
					?>
					</div>

				</div>
				<!-- ========== END of a COMMENT/TB/PB ========== -->
				<?php //end of the loop, don't delete
			}

			if( $Item->can_comment() )
			{ // User can leave a comment
			?>
			<!-- ========== FORM to add a comment ========== -->
			<h4><?php echo T_('Leave a comment') ?>:</h4>

			<?php

			$Form = & new Form( $htsrv_url.'comment_post.php', 'comment_checkchanges' );

			$Form->begin_form( 'bComment' );

			$Form->hidden( 'comment_post_ID', $Item->ID );
			$Form->hidden( 'redirect_to', htmlspecialchars($ReqURI) );
			?>
				<fieldset>
					<div class="label"><?php echo T_('User') ?>:</div>
					<div class="info">
						<strong><?php $current_User->preferred_name()?></strong>
						<?php user_profile_link( ' [', ']', T_('Edit profile') ) ?>
						</div>
				</fieldset>
			<?php
			$Form->textarea( 'comment', '', 12, T_('Comment text'),
												T_('Allowed XHTML tags').': '.htmlspecialchars(str_replace( '><',', ', $comment_allowed_tags)), 40, 'bComment' );

			if(substr($comments_use_autobr,0,4) == 'opt-')
			{
				echo $Form->fieldstart;
				echo $Form->labelstart;
			?>
			<label><?php echo T_('Options') ?>:</label>

			<?php
				echo $Form->labelend;
				echo $Form->inputstart;
				$Form->checkbox( 'comment_autobr', 1, T_('Auto-BR'), T_('(Line breaks become &lt;br&gt;)'), 'checkbox' );
				echo $Form->inputend;
				$Form->end_fieldset();

			}

				echo $Form->fieldstart;
				echo $Form->inputstart;
				$Form->submit( array ('submit', T_('Send comment'), 'SaveButton' ) );
				echo $Form->inputend;
				$Form->end_fieldset();

			?>

				<div class="clear"></div>
			<?php
				$Form->end_form();
			?>
			<!-- ========== END of FORM to add a comment ========== -->
			<?php
			} // / can comment
		?>
		</div>
		<?php
	} // / comments requested
?>
</div>
<?php
}

if( $MainList->get_total_num_posts() )
{ // don't display navbar twice if we have no post
	echo '<div class="NavBar">';
	require dirname(__FILE__). '/_edit_navbar.php';
	echo '</div>';
}
?>

<p class="center">
  <a href="<?php echo $add_item_url ?>"><img src="img/new.gif" width="13" height="13" class="middle" alt="" />
    <?php echo T_('New post...') ?></a>
</p>

<?php
/*
 * @movedTo _browse_posts_sidebar.inc.php
 */

/*
 * $Log$
 * Revision 1.115  2006/01/25 18:24:21  fplanque
 * hooked bozo validator in several different places
 *
 * Revision 1.114  2006/01/09 17:21:06  fplanque
 * no message
 *
 * Revision 1.113  2005/12/14 17:00:25  blueyed
 * assign return value of get_next() by reference
 *
 * Revision 1.112  2005/12/12 19:21:20  fplanque
 * big merge; lots of small mods; hope I didn't make to many mistakes :]
 *
 * Revision 1.111  2005/12/05 20:54:05  blueyed
 * Changed Item::views() to take $zero, $one and $more param and default to 'No views', '1 view' and '%d views' (translated). More consistent and easier on the skin.
 *
 * Revision 1.108  2005/10/31 05:51:05  blueyed
 * Use rawurlencode() instead of urlencode()
 *
 * Revision 1.107  2005/09/29 15:07:29  fplanque
 * spelling
 *
 * Revision 1.106  2005/09/06 17:13:53  fplanque
 * stop processing early if referer spam has been detected
 *
 * Revision 1.104  2005/08/31 19:06:41  fplanque
 * minor
 *
 * Revision 1.103  2005/08/30 18:26:04  fplanque
 * comment spam issues
 *
 * Revision 1.102  2005/08/22 18:42:25  fplanque
 * minor
 *
 * Revision 1.101  2005/08/08 22:54:40  blueyed
 * Re-activated /admin/antispam, with slight improvements. Still needs a lot more love.
 *
 * Revision 1.100  2005/08/03 21:05:01  fplanque
 * cosmetic cleanup
 *
 * Revision 1.99  2005/08/02 18:15:59  fplanque
 * cosmetic enhancements
 *
 * Revision 1.98  2005/04/28 20:44:17  fplanque
 * normalizing, doc
 *
 * Revision 1.97  2005/04/15 18:02:57  fplanque
 * finished implementation of properties/meta data editor
 * started implementation of files to items linking
 *
 * Revision 1.96  2005/03/11 18:33:35  fplanque
 * mutiple browsing views part TWO
 *
 * Revision 1.95  2005/03/11 12:40:16  fplanque
 * multiple browsing views, part ONE
 *
 */
?>