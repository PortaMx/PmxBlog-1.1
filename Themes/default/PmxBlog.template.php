<?php
// ----------------------------------------------------------
// -- PmxBlog.template.php                                 --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

global $settings;
require_once($settings['default_theme_dir'] . '/PmxBlogMenu.php');
require_once($settings['default_theme_dir'] . '/PmxBlogHeader.php');

function template_main()
{
	global $context, $txt, $scripturl, $settings, $modSettings, $options, $user_info, $boarddir, $boardurl;

	Navigation($context['PmxBlog']['nav_tabs']);

	if($context['PmxBlog']['action'][0] == 'list')
	{
		echo '
		<div class="title_bar"><h3 class="titlebg" style="text-align:center;">';

		if($context['PmxBlog']['action'][1] == 'unread')
		{
			echo $txt['PmxBlog_unread_title'];
			$PGsort = '=unread';
		}
		elseif($context['PmxBlog']['action'][1] == 'tracked')
		{
			echo $txt['PmxBlog_tracked_title'];
			$PGsort = '=tracked';
		}
		else
		{
			echo $txt['PmxBlog_bloglist_title'];
			$PGsort = '=list';
		}
		echo '
		</h3></div>';

		if(isset($context['PmxBlog']['bloglist']))
		{
			echo '
			<div class="smalltext pmxblog_sortpad" style="margin:0 auto; text-align:center;">'.$txt['PmxBlog_bloglist_sort'];
			for($i=0; $i < 4; $i++)
				echo '&nbsp;'.($context['PmxBlog']['sortmode'] == $i ? '['.$txt['PmxBlog_bloglist_sortmode'][$i].']' : '<a class="nav" href="'.$scripturl.'?action=pmxblog;sa'.$PGsort.$context['PmxBlog']['UserLink'].';sort='.$i.';" style="font-weight:normal;">'.$txt['PmxBlog_bloglist_sortmode'][$i].'</a>').'&nbsp;';
			echo '
			</div>';
		}

		if(isset($context['PmxBlog']['bloglist']))
		{
			if(isset($context['PmxBlog']['pageindex']))
				echo '
				<div class="catbg pmxblog_pageidx upperover">
					<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
					<div class="pmxblog_pagebot">'. $context['PmxBlog']['pagebot'] .'</div>
				</div>';

			$pc = 0;
			$s = $context['PmxBlog']['startpage'] -1;
			$e = $s + $context['PmxBlog']['overview_pages'];
			$t = count($context['PmxBlog']['bloglist']) -1;
			foreach($context['PmxBlog']['bloglist'] as $blog)
			{
				if($pc >= $context['PmxBlog']['startpage'] && $pc < $context['PmxBlog']['startpage'] + $context['PmxBlog']['overview_pages'])
				{
					Pmx_Header($blog, !empty($blog['nbr_content']) && empty($blog['hiddencont']));
					if($pc < $e && $pc < $t)
						echo '<div style="height:4px;"></div>';
				}
				$pc++;
			}

			if(isset($context['PmxBlog']['pageindex']))
				echo '
				<a name="bot"></a>
				<div class="catbg pmxblog_pageidx lowerover">
					<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
					<div class="pmxblog_pagebot">'. $context['PmxBlog']['pagetop'] .'</div>
				</div>';

			echo '
				<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>';
		}
		else
			echo '
			<div class="plainbox" style="padding:4px;">',
				($context['PmxBlog']['action'][1] == 'unread'
				?	$txt['PmxBlog_no_unread']
				:	($context['PmxBlog']['action'][1] == 'tracked'
					?	$txt['PmxBlog_no_tracked']
					:	$txt['PmxBlog_no_blogs']
					)
				).'
			</div>';
	}

	elseif(isBlogEnabled() || $context['PmxBlog']['mode'] == 'manager' || isOwner() || AllowedTo('admin_forum'))
	{
		Pmx_Header($context['PmxBlog']['Manager'], !empty($context['PmxBlog']['content']));

		echo '
		<div style="padding-top:6px;">
			<div class="title_bar"><h3 class="titlebg pmxblog_corepad" style="text-align:center;">';

		if(!empty($context['PmxBlog']['Manager']['showcalendar']) || !empty($context['PmxBlog']['Manager']['showarchive']) || !empty($context['PmxBlog']['Manager']['showcategories']))
			echo '
				<span onclick="PmxBlogSideBar.toggle();" style="cursor:pointer;" title="'. $txt['PmxBlog_collapse_SB'] .'"><img id="upshrinkImgPmxBlogSideBar" align="right" style="margin-top:8px;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_move'. (empty($options['collapse_PmxBlogSideBar']) ? 'right' : 'left') .'.gif" alt="*" /></span>';

		echo '
				'. ($context['PmxBlog']['mode'] == 'manager' ? $txt['PmxBlog_manager_title'] : $txt['PmxBlog_blogview_title']) . (!empty($context['PmxBlog']['Manager']['archStr']) ? ' ('. $context['PmxBlog']['Manager']['archStr'] .')' : '') .'
			</h3></div>
		</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:4px;">
			<tr>
				<td valign="top" width="100%">';

		switch($context['PmxBlog']['action'][0])
		{
		// New Blog
		case "contnew":
			if(isBlogEnabled())
			{
				echo '
				<form id="pmx_form" name="PmxManager_newblog" action="' .$scripturl. '?action=pmxblog;sa=manager;cont=save'.$context['PmxBlog']['UserLink'].'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
				<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;">
					<thead>
						<tr class="catbg">
							<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_newblog'] .'</div></th>
							<th class="last_th" scope="col" width="2%"></th>
						</tr>
					</thead>
					<tr><td colspan="2" class="pmxblog_empty"></td></tr>
				</table>

				<div class="plainbox pmxblog_border">
				<div class="windowbg2" style="padding:10px 5px;">
					<div style="text-align:right; width:15%; margin-right:5px; float:left;">
					'. $txt['PmxBlog_blogtitle'] .'
					</div>
					<input name="subject" type="text" size="50" value="" style="width: 75%;" />
					<div style="padding:2px;"></div>

					<div style="text-align:right; width:15%; margin-right:5px;float:left;">
					'. $txt['PmxBlog_selcat'] .'
					</div>
					<div style="float:left">
						<select size="1" name="category">
							<option value="0" selected="selected">-'.$txt['PmxBlog_nocat'].'-</option>';

					if(!empty($context['PmxBlog']['categorie']))
					{
						foreach($context['PmxBlog']['categorie'] as $fbcat)
						{
							$d = str_pad('', $fbcat['depth']*2, '.');
							echo '
							<option value="'.$fbcat['id'].'">'.$d.$fbcat['name'].'</option>';
						}
					}
					echo '
						</select>
					</div>

					<div style="margin-right:65px;float:right;padding-top:2px;">
						<input class="check" name="published" type="checkbox" value="1" />
						<span style="vertical-align:top">'.$txt['PmxBlog_acticle_publishing'].'</span>
					</div>
					<div style="padding:5px;clear:both;"></div>';

					EditContent_xhtml('');

					if(getEditorAcs($context['PmxBlog']['wysiwyg_edit']) && ($context['PmxBlog']['Manager']['settings']{0} == '1' || (!isOwner() && isModerator())))
						echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function contnew_submit()
					{ document.getElementById("pmx_form").submit(); }
				// ]]></script>';
					else
						echo '
				<input id="contnew_bbc" type="hidden" name="html_to_bbc" value="1" />
				<div style="padding:0 5px;" id="bbcBox_message"></div>
				<div style="padding:5px;" id="smileyBox_message"></div>
				<div style="padding:0 5px;">
					', template_control_richedit($context['PmxBlog']['editor'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function contnew_submit()
					{
						if(oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextPossible && oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextEnabled)
						{
							oEditorHandle_'. $context['PmxBlog']['editor'] .'.doSubmit();
							document.getElementById("contnew_bbc").value = "0";
						}
						document.getElementById("pmx_form").submit();
					}
				// ]]></script>';

				echo '
				<div style="padding:2px;"></div>
				<table border="0" align="center">
					<tr>
						<td valign="top">'. $txt['PmxBlog_allowview'] .'</td>
						<td>
							<input class="check" name="allow_view" type="radio" value="0" checked="checked" />'. $txt['PmxBlog_allow'][0] .'<br />
							<input class="check" name="allow_view" type="radio" value="1" />'. $txt['PmxBlog_allow'][1] .'<br />
							<input class="check" name="allow_view" type="radio" value="2" />'. $txt['PmxBlog_allow'][2] .'<br />
							<input class="check" name="allow_view" type="radio" value="3" />'. $txt['PmxBlog_allow'][3] .'
						</td>
						<td valign="top" width="100px">&nbsp;</td>
						<td valign="top" align="right">'. $txt['PmxBlog_allowcomment'] .'</td>
						<td>
							<input class="check" name="allowcomment" type="radio" value="0" checked="checked" />'. $txt['PmxBlog_allow'][0] .'<br />
							<input class="check" name="allowcomment" type="radio" value="1" />'. $txt['PmxBlog_allow'][1] .'<br />
							<input class="check" name="allowcomment" type="radio" value="2" />'. $txt['PmxBlog_allow'][2] .'<br />
							<input class="check" name="allowcomment" type="radio" value="3" />'. $txt['PmxBlog_allow'][3] .'
						</td>
					</tr>
				</table>
				<br />
				<div align="center" style="vertical-align:middle; padding:3px 5px; margin-top:5px;">
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" name="send" onclick="contnew_submit()" />&nbsp;
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_back'] .'" name="abort" onclick="window.location=\'' . $scripturl . '?action=pmxblog;sa='.$context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'\'" />
				</div>
			</div>
			</div>
			</form>
			<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
			</td>';

			Pmx_SideBar();
			}
		break;

		// Edit content
		case "contedit":
			foreach($context['PmxBlog']['content'] as $blog);
			echo '
			<form id="pmx_form" name="PmxManager_editblog" action="' .$scripturl. '?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cont='. $blog['id'].';upd'.$context['PmxBlog']['UserLink'].'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
			<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;">
				<thead>
					<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_editblog_title'] .'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
					</tr>
				</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr>
			</table>

			<div class="plainbox pmxblog_border">
			<div class="windowbg2" style="padding:10px 5px;">
				<div style="text-align:right; width:15%; margin-right:5px; float:left;">
				'. $txt['PmxBlog_blogtitle'] .'
				</div>
				<input name="subject" type="text" size="50" value="'.$blog['subject'].'" style="width: 75%;" />
				<div style="padding:2px;"></div>

				<div style="text-align:right; width:15%; margin-right:5px;float:left;">
				'. $txt['PmxBlog_selcat'] .'
				</div>
				<div style="float:left">';

			if($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid']))
			{
				echo '
					<select size="1" name="category">
						<option value="0" selected="selected">-'.$txt['PmxBlog_nocat'].'-</option>';

				if(!empty($context['PmxBlog']['categorie']))
				{
					foreach($context['PmxBlog']['categorie'] as $fbcat)
					{
						$d = str_pad('', $fbcat['depth']*2, '.');
						echo '
						<option value="'.$fbcat['id'].'"', $blog['categorie'] == $fbcat['id'] ? ' selected="selected"' : '', '>'.$d.$fbcat['name'].'</option>';
					}
				}
				echo '
					</select>';
			}
			else
				echo '
					<input type="hidden" name="category" value="'. $blog['categorie'] .'" />'. GetCatname($blog['categorie']);

			echo '
				</div>
				<div style="margin-right:65px;float:right;padding-top:2px;">';

			if($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid']))
				echo '
					<input name="published" type="hidden" value="0" />
					<input class="check" name="published" type="checkbox" value="1"'. (!empty($blog['published']) ? ' checked="checked"' : '') .' />
					<span style="vertical-align:top">'. (!empty($blog['published']) ? $txt['PmxBlog_acticle_ispublished'] : $txt['PmxBlog_acticle_publishing']) .'</span>';
			else
				echo '
					<input name="published" type="hidden" value="'. $blog['published'] .'" />
					<input class="check" name="published" type="checkbox" value="1"'. (!empty($blog['published']) ? ' checked="checked"' : '') .' disabled="disabled" />
					<span style="vertical-align:top">'. (!empty($blog['published']) ? $txt['PmxBlog_acticle_ispublished'] : $txt['PmxBlog_acticle_publishing']) .'</span>';

			echo '
				</div>
				<div style="padding:5px;clear:both;"></div>';

				EditContent_xhtml($blog['body']);

				if(getEditorAcs($context['PmxBlog']['wysiwyg_edit']) && ($context['PmxBlog']['Manager']['settings']{0} == '1' || (!isOwner() && isModerator())))
					echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function contedit_submit()
					{ document.getElementById("pmx_form").submit(); }
				// ]]></script>';
				else
					echo '
				<input id="contedit_bbc" type="hidden" name="html_to_bbc" value="1" />
				<div style="padding:0 5px;" id="bbcBox_message"></div>
				<div style="padding:5px;" id="smileyBox_message"></div>
				<div style="padding:0 5px;">
					', template_control_richedit($context['PmxBlog']['editor'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function contedit_submit()
					{
						if(oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextPossible && oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextEnabled)
						{
							oEditorHandle_'. $context['PmxBlog']['editor'] .'.doSubmit();
							document.getElementById("contedit_bbc").value = "0";
						}
						document.getElementById("pmx_form").submit();
					}
				// ]]></script>';

				if($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid']))
					$admEdmode = '';
				else
				{
					$admEdmode =' disabled="disabled"';
					echo '
				<input name="allow_view" type="hidden" value="'. $blog['allow'] .'" />
				<input name="allowcomment" type="hidden" value="'. $blog['allowcomment'][0] .'" />';
				}

				echo '
				<div style="padding:2px;"></div>
				<table border="0" align="center">
					<tr>
						<td valign="top">'. $txt['PmxBlog_allowview'] .'</td>
						<td>
							<input class="check" name="allow_view" type="radio" value="0"', $blog['allow']==0 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][0] .'<br />
							<input class="check" name="allow_view" type="radio" value="1"', $blog['allow']==1 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][1] .'<br />
							<input class="check" name="allow_view" type="radio" value="2"', $blog['allow']==2 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][2] .'<br />
							<input class="check" name="allow_view" type="radio" value="3"', $blog['allow']==3 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][3] .'<br />
						</td>
						<td valign="top" width="100px">&nbsp;</td>
						<td valign="top" align="right">'. $txt['PmxBlog_allowcomment'] .'</td>
						<td>
							<input class="check" name="allowcomment" type="radio" value="0"', $blog['allowcomment'][0]==0 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][0] .'<br />
							<input class="check" name="allowcomment" type="radio" value="1"', $blog['allowcomment'][0]==1 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][1] .'<br />
							<input class="check" name="allowcomment" type="radio" value="2"', $blog['allowcomment'][0]==2 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][2] .'<br />
							<input class="check" name="allowcomment" type="radio" value="3"', $blog['allowcomment'][0]==3 ? ' checked="checked"' : '', $admEdmode, ' />'. $txt['PmxBlog_allow'][3] .'<br />
						</td>
					</tr>
				</table>
				<br />
				<div align="center" style="vertical-align:middle; padding:3px 5px; margin-top:5px;">
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" name="send" onclick="contedit_submit()" />&nbsp;
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_back'] .'" name="abort" onclick="window.location=\'' . $scripturl . '?action=pmxblog;sa='. $context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'\'" />
				</div>
			</div>
			</div>
			</form>
			<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
			</td>';

			Pmx_SideBar();
		break;

		// New Comment
		case "cmntnew":
			if(isBlogEnabled())
			{
			foreach($context['PmxBlog']['content'] as $blog);
			echo '
			<form id="pmx_form" name="PmxManager_newblog" action="' .$scripturl. '?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='.$blog['id'].';store=new'.$context['PmxBlog']['UserLink'].';'. SID .'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
			<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;">
				<thead>
					<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_newcomment_title'] .'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
					</tr>
				</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr>
			</table>

			<div class="plainbox pmxblog_border">
			<div class="windowbg2" style="padding:10px 5px;">'.
				($user_info['is_guest']
				?	'<div style="text-align:right; width:15%; margin-right:5px; float:left;">'. $txt['PmxBlog_username'] .'</div>
					<input name="username" type="text" size="30" value="'.$txt['PmxBlog_guestname'].'" style="width: 50%;" />
					<div style="padding:2px;"></div>'
				:	''
				).'
				<div style="text-align:right; width:15%; margin-right:5px; float:left;">'. $txt['PmxBlog_blogtitle'] .'</div>
				<input name="subject" type="text" size="50" value="Re: '.$blog['subject'].'" style="width: 75%;" />
				<div style="padding:2px;"></div>';

				if(isset($_SESSION['PmxBlog_cmnt_body']))
					EditComment_xhtml(stripslashes($_SESSION['PmxBlog_cmnt_body']));
				else
					EditComment_xhtml('');

				if(getEditorAcs($context['PmxBlog']['wysiwyg_comment']) && ($context['PmxBlog']['Manager']['settings']{1} == '1' || (!isOwner() && isModerator())))
					echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntnew_submit()
					{ document.getElementById("pmx_form").submit(); }
				// ]]></script>';
				else
					echo '
				<input id="cmntnew_bbc" type="hidden" name="html_to_bbc" value="1" />
				<div style="padding:0 5px;" id="bbcBox_message"></div>
				<div style="padding:5px;" id="smileyBox_message"></div>
				<div style="padding:0 5px;">
					', template_control_richedit($context['PmxBlog']['editor'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntnew_submit()
					{
						if(oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextPossible && oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextEnabled)
						{
							oEditorHandle_'. $context['PmxBlog']['editor'] .'.doSubmit();
							document.getElementById("cmntnew_bbc").value = "0";
						}
						document.getElementById("pmx_form").submit();
					}
				// ]]></script>';

				if($user_info['is_guest'])
					captcha_template('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='.$blog['id'].';store=new'.$context['PmxBlog']['UserLink']);

				echo '
				<div align="center" style="vertical-align:middle; padding:3px 5px; margin-top:5px;">
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" name="send" onclick="cmntnew_submit()" />&nbsp;
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_back'] .'" name="abort" onclick="window.location=\'' . $scripturl . '?action=pmxblog;sa='. $context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'\'" />
				</div>';
			echo '
			</div>
			</div>
			</form>

			<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top:6px;">
				<thead>
					<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:left; padding-left:5px;">'. title_button('content', $blog['subject']) .'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
					</tr>
				</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr>
			</table>

				<div class="plainbox pmxblog_border">
				<div class="windowbg2">
					<div class="smalltext" style="padding:0px 5px;">
						<div style="float:left;clear:right;">'. $txt['PmxBlog_selcat'] . GetCatname($blog['categorie']) .'</div>
						<div style="float:right;">'. $txt['PmxBlog_allow_to'] . $txt['PmxBlog_allow'][$blog['allow']] .'</div>
						<div style="clear:both;height:0px"></div>
						<div class="smalltext" style="float:left;clear:right;">
							'.$txt['PmxBlog_created'].$blog['date_created'].'<br />'.$txt['PmxBlog_comments'].$blog['nbr_comment'].'
						</div>
						<div class="smalltext" style="float:right;">
							'.$txt['PmxBlog_views']. $blog['views'].'
						</div>
						<div class="smalltext" style="float:right;">'.
						$txt['PmxBlog_cont_votes'].$blog['votes'].
						($blog['votes'] > 0
						?	','.$txt['PmxBlog_cont_rating'].'<img src="' . $settings['default_images_url'] .'/PmxBlog/blog_rating'.$blog['rating'].'.gif" alt="" />'
						:	''
						).'
						</div>
					</div><br class="clear" />
					<table cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout:fixed;"><tr><td>
						<div class="plainbox pmxblog_rule"></div>
						<div style="padding:0px 4px;">
							<div style="overflow:auto;">'. $blog['body'] .'</div>
						</div>
					</td></tr></table>
				</div>
				</div>
				<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
			</td>';

			Pmx_SideBar();
			}
		break;

		// Reply to Comment
		case "cmntrply":
			if(isBlogEnabled())
			{
			foreach($context['PmxBlog']['comments'] as $cmt);
			echo '
			<form id="pmx_form" name="PmxManager_newblog" action="' .$scripturl. '?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='.$cmt['id'].';store=rply'. $context['PmxBlog']['UserLink'] .'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
			<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;">
				<thead>
					<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_replycomment_title'] .'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
					</tr>
				</thead>
					<tr><td colspan="2" class="pmxblog_empty"></td></tr>
				</table>

			<div class="plainbox pmxblog_border">
			<div class="windowbg2" style="padding:10px 5px;">'.
				($user_info['is_guest']
				?	'<div style="text-align:right; width:15%; margin-right:5px; float:left;">'. $txt['PmxBlog_username'] .'</div>
					<input name="username" type="text" size="30" value="'.$txt['PmxBlog_guestname'].'" style="width: 50%;" />
					<div style="padding:2px;"></div>'
				:	''
				).'
				<div style="text-align:right; width:15%; margin-right:5px; float:left;">'. $txt['PmxBlog_blogtitle'] .'</div>
				<input name="subject" type="text" size="50" value="Re: '. preg_replace('@Re[^:( )]*?:( )@i', '', $cmt['subject']) .'" style="width: 75%;" />
				<div style="padding:2px;"></div>';

				if(isset($_SESSION['PmxBlog_cmnt_body']))
					EditComment_xhtml(stripslashes($_SESSION['PmxBlog_cmnt_body']));
				else
					EditComment_xhtml('');

				if(getEditorAcs($context['PmxBlog']['wysiwyg_comment']) && ($context['PmxBlog']['Manager']['settings']{1} == '1' || (!isOwner() && isModerator())))
					echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntrply_submit()
					{ document.getElementById("pmx_form").submit(); }
				// ]]></script>';
				else
					echo '
				<input id="cmntrply_bbc" type="hidden" name="html_to_bbc" value="1" />
				<div style="padding:0 5px;" id="bbcBox_message"></div>
				<div style="padding:5px;" id="smileyBox_message"></div>
				<div style="padding:0 5px;">
					', template_control_richedit($context['PmxBlog']['editor'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntrply_submit()
					{
						if(oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextPossible && oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextEnabled)
						{
							oEditorHandle_'. $context['PmxBlog']['editor'] .'.doSubmit();
							document.getElementById("cmntrply_bbc").value = "0";
						}
						document.getElementById("pmx_form").submit();
					}
				// ]]></script>';

				if($user_info['is_guest'])
					captcha_template('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='.$cmt['id'].';store=rply'. $context['PmxBlog']['UserLink']);

				echo '
				<br />
				<div align="center" style="vertical-align:middle; padding:3px 5px; margin-top:5px;">
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" name="send" onclick="cmntrply_submit()" />&nbsp;
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_back'] .'" name="abort" onclick="window.location=\'' . $scripturl . '?action=pmxblog;sa='. $context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'\'" />
				</div>
			</div>
			</div>
			</form>

			<div class="titlebg2 pmxblog_corecmnt" style="padding:2px 5px; margin-top:5px;">'.
				Title_button('comment'.($cmt['is_new_cmnt'] ? '_new' : ''), '<span style="font-weight:normal;">'. $cmt['treeS2'] .' - </span>'.$cmt['subject']) .'
			</div>
			<div class="plainbox pmxblog_cmnt">
			<div class="windowbg2" style="padding:2px 5px;">
				<div class="smalltext" style="padding:2px 5px;">
				'. $txt['PmxBlog_by'] . '<a href="'.$scripturl.'?action=profile;u='.$cmt['userid'].'"><b>'. $cmt['realname'] .'</b></a> '. $txt['PmxBlog_on'] . $cmt['date_created'], ($cmt['date_edit'] != 0 && $cmt['is_edit']) ? $txt['PmxBlog_lastedit'] . $cmt['date_edit'] : '', '<br />
				</div>
				<div style="padding:2px 5px;">'. $cmt['body'] .'</div>
			</div>
			</div>
			<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
			</td>';

			Pmx_SideBar();
			}
		break;

//		$context['PmxBlog']['action'][0] = 'editcmnt';
		// Edit Comment
		case "cmntedit":
			foreach($context['PmxBlog']['comments'] as $cmt);
			echo '
			<form id="pmx_form" name="PmxM_comment" action="' .$scripturl. '?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='.$cmt['id'].';upd'.$context['PmxBlog']['UserLink'].'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
			<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;">
				<thead>
					<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_editcomment_title'] .'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
					</tr>
				</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr>
			</table>

			<div class="plainbox pmxblog_border">
			<div class="windowbg2" style="padding:10px 5px;">
				<div style="text-align:right; width:15%; margin-right:5px; float:left;">
				'. $txt['PmxBlog_blogtitle'] .'</div>
				<input name="subject" type="text" size="50" value="'.$cmt['subject'].'" style="width: 75%;" />
				<div style="padding:2px;"></div>';

				EditComment_xhtml($cmt['body']);

				if(getEditorAcs($context['PmxBlog']['wysiwyg_comment']) && ($context['PmxBlog']['Manager']['settings']{1} == '1' || (!isOwner() && isModerator())))
					echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntedit_submit()
					{ document.getElementById("pmx_form").submit(); }
				// ]]></script>';
				else
					echo '
				<input id="cmntedit_bbc" type="hidden" name="html_to_bbc" value="1" />
				<div style="padding:0 5px;" id="bbcBox_message"></div>
				<div style="padding:5px;" id="smileyBox_message"></div>
				<div style="padding:0 5px;">
					', template_control_richedit($context['PmxBlog']['editor'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					function cmntedit_submit()
					{
						if(oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextPossible && oEditorHandle_'. $context['PmxBlog']['editor'] .'.bRichTextEnabled)
						{
							oEditorHandle_'. $context['PmxBlog']['editor'] .'.doSubmit();
							document.getElementById("cmntedit_bbc").value = "0";
						}
						document.getElementById("pmx_form").submit();
					}
				// ]]></script>';

				echo '
				<br />
				<div align="center" style="vertical-align:middle; padding:3px 5px; margin-top:5px;">
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" name="send" onclick="cmntedit_submit()" />&nbsp;
					<input type="button" class="button_submit" value="' . $txt['PmxBlog_back'] .'" name="abort" onclick="window.location=\'' . $scripturl . '?action=pmxblog;sa='. $context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'\'" />
				</div>
			</div>
			</div>
			</form>

			<div class="titlebg2 pmxblog_corecmnt" style="padding:2px 5px; margin-top:5px;">'.
				Title_button('comment'.($cmt['is_new_cmnt'] ? '_new' : ''), '<span style="font-weight:normal;">'. $cmt['treeS2'] .' - </span>'.$cmt['subject']) .'
			</div>
			<div class="plainbox pmxblog_cmnt">
			<div class="windowbg2" style="padding:2px 5px;">
				<div class="smalltext" style="padding:2px 5px;">
				'. $txt['PmxBlog_by'] . '<a href="'.$scripturl.'?action=profile;u='.$cmt['userid'].'"><b>'. $cmt['realname'] .'</b></a> '. $txt['PmxBlog_on'] . $cmt['date_created'], ($cmt['date_edit'] != 0 && $cmt['is_edit']) ? $txt['PmxBlog_lastedit'] . $cmt['date_edit'] : '', '<br />
				</div>
				<div style="padding:2px 5px;">'. $cmt['body'] .'</div>
			</div>
			</div>
			<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
			</td>';

			Pmx_SideBar();
		break;

		// Singlepage or overview
		default:
			if(isset($context['PmxBlog']['content']))
			{
				if($context['PmxBlog']['action'][0] != 'singlepage' && isset($context['PmxBlog']['pageindex']))
					echo '
					<div class="catbg pmxblog_pageidx upper">
						<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
						<div class="pmxblog_pagebot">'. $context['PmxBlog']['pagebot'] .'</div>
					</div>';

				if($context['PmxBlog']['action'][0] == 'singlepage')
					$pc = $context['PmxBlog']['startpage'];
				else
					$pc = 0;

				$i = 0;
				foreach($context['PmxBlog']['content'] as $blog)
				{
					if($pc >= $context['PmxBlog']['startpage'] && $pc < $context['PmxBlog']['startpage'] + $context['PmxBlog']['content_pages'])
					{
						if((isModerator() && $context['PmxBlog']['Moderate'] == $context['PmxBlog']['Manager']['userid']) || ($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid'])))
						{
							echo '
							<form id="pmx_form" name="PmxBlog_blogaccess" action="' .$scripturl. '?action=pmxblog;sa='. $context['PmxBlog']['mode']. ($context['PmxBlog']['action'][0] == 'singlepage' ? ';cont='. $blog['id'] .';qchg' : ';qchg='. $blog['id']). $context['PmxBlog']['UserLink'].'" enctype="multipart/form-data" method="post" style="margin:0px;padding:0px;">';
							$closeForm = '</form>';
						}
						else
							$closeForm = '';

						echo '
						<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-width:0;'. ($i > 0 ? 'margin-top:6px;' : ''). '">
						<thead>
						<tr class="catbg">
						<th class="first_th" scope="col" width="97%"><div style="text-align:left; padding-left:5px;">'.
							(!$blog['singlepage']
								?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'].$context['PmxBlog']['UserLink']. '">'. title_button('content'.($blog['is_new_cont'] ? '_new' : ''), $blog['subject']) .'</a>'
								:	Title_button('content'.($blog['is_new_cont'] ? '_new' : ''), $blog['subject'])
							).
							($blog['published'] == 0
								?	'<span class="smalltext" style="padding-left:20px;">('.$txt['PmxBlog_acticle_notpublished'].')</span>'
								: ''
							).'</div></th>
						<th class="last_th" scope="col" width="2%"></th>
						</tr>
						</thead>
						<tr><td colspan="2" class="pmxblog_empty"></td></tr>
						</table>'.
							($blog['singlepage']
							?	''.
							(!empty($context['PmxBlog']['contprev']) || !empty($context['PmxBlog']['contnext'])
							? '
						<div class="plainbox pmxblog_border">
							<div class="windowbg2" style="height:20px;">
								<div style="float:left;padding:2px 5px; font-weight:bold;">'.
									(!empty($context['PmxBlog']['contprev'])
									?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $context['PmxBlog']['contprev']['contid'] . $context['PmxBlog']['UserLink']. '#top">&laquo;&laquo; '. $context['PmxBlog']['contprev']['subject'] .'</a>'
									:	''
									).'
								</div>
								<div style="float:right;padding:2px 5px; font-weight:bold;">'.
									(!empty($context['PmxBlog']['contnext'])
									?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $context['PmxBlog']['contnext']['contid'] . $context['PmxBlog']['UserLink']. '#top">'. $context['PmxBlog']['contnext']['subject'] .' &raquo;&raquo;</a>'
									:	''
									).'
								</div>
							</div>
						</div>' : '')
						:	'') .'
						<div class="plainbox pmxblog_border">
						<div class="windowbg2" style="padding:2px 0px; clear:both;">';

						if($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid']))
						{
							echo '
							<div class="smalltext" style="float:left;clear:right;padding:0px 4px;">'. $txt['PmxBlog_selcat'];

							if(!empty($context['PmxBlog']['categorie']))
							{
								echo '
								<span style="vertical-align:top;">
								<select name="categorie" size="1" onchange="submit()" style="width:100px;">
									<option value="0"', $blog['categorie']==0 ? ' selected="selected"': '','>'. $txt['PmxBlog_nocat'] .'</option>';
									foreach($context['PmxBlog']['categorie'] as $c)
									{
										$d = str_pad('', $c['depth']*2, '.');
										echo '
										<option value="'.$c['id'].'"', $blog['categorie']==$c['id'] ? ' selected="selected"': '','>'.$d.$c['name'] .'</option>';
									}
								echo '
								</select>
								</span>';
							}
							else
								echo $txt['PmxBlog_nocat'];

							echo '
							</div>
							<div class="smalltext" style="float:left;margin-left:30%;padding:0px 4px;">
								<input name="published" type="hidden" value="0" />
								<input class="check" name="published" type="checkbox" value="1"'. (!empty($blog['published']) ? ' checked="checked"' : '') .'  onclick="submit()" />
								<span style="vertical-align:top">'. (!empty($blog['published']) ? $txt['PmxBlog_acticle_ispublished'] : $txt['PmxBlog_acticle_publishing']) .'</span>
							</div>

							<div class="smalltext" style="float:right;padding:0px 4px;">'. $txt['PmxBlog_allow_to']. '
							<span style="vertical-align:top;">
							<select name="allow_view" size="1" onchange="submit()" style="width:70px;">
								<option value="0"', $blog['allow']==0 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][0] .'</option>
								<option value="1"', $blog['allow']==1 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][1] .'</option>
								<option value="2"', $blog['allow']==2 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][2] .'</option>
								<option value="3"', $blog['allow']==3 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][3] .'</option>
							</select>
							</span>
							</div>';
						}
						else
							echo
							'
							<div class="smalltext" style="float:left;clear:right;padding:0px 4px;">'. $txt['PmxBlog_selcat'] .GetCatname($blog['categorie']) .'</div>
							<div class="smalltext" style="float:right;padding:0px 4px;">'. $txt['PmxBlog_allow_to'] . $txt['PmxBlog_allow'][$blog['allow']] .'</div>';

						echo '
						<div style="clear:both;height:0px"></div>
						<div class="smalltext" style="float:left;clear:right; padding:0px 4px;">
							'.$txt['PmxBlog_created'].$blog['date_created'] . ($blog['is_edit']	?	' &nbsp; - &nbsp; '. $txt['PmxBlog_lastedit'] . $blog['date_edit'] : '') .'<br />'.
							($blog['nbr_comment'] > 0
							?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='.$blog['id'].$context['PmxBlog']['UserLink'].($blog['is_new_cmnt'] ? '#new' : '#cmnt').'">'. $txt['PmxBlog_comments'].$blog['nbr_comment'] .($blog['is_new_cmnt'] ? $context['PmxBlog']['newCmnt'] : '').'</a>'
							:	$txt['PmxBlog_comments'].$blog['nbr_comment']
							).
							($context['PmxBlog']['mode'] != 'manager'
								?	$txt['PmxBlog_cmnt_allow_to'].$txt['PmxBlog_allow'][$blog['allowcomment'][0]].')'
								:	''
							);

							if($context['PmxBlog']['mode'] == 'manager' && ($user_info['id'] == $blog['userid']))
							{
								echo $txt['PmxBlog_allow_cmt'].'
								<span style="vertical-align:middle;">
								<select name="allowcomment" size="1" onchange="submit()" style="width:80px;">
									<option value="0"', $blog['allowcomment'][0]==0 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][0] .'</option>
									<option value="1"', $blog['allowcomment'][0]==1 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][1] .'</option>
									<option value="2"', $blog['allowcomment'][0]==2 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][2] .'</option>
									<option value="3"', $blog['allowcomment'][0]==3 ? ' selected="selected"': '','>'. $txt['PmxBlog_allow'][3] .'</option>
								</select>
								</span>';
							}

							echo
							'</div>
							<div class="smalltext" style="float:right;padding:0px 4px;">
								'.$txt['PmxBlog_views']. $blog['views'].'
							</div>
							<div class="smalltext"><br /></div>';
							if($context['PmxBlog']['action'][0] == 'singlepage')
							{
								echo '
								<form id="pmx_form" action="' .$scripturl. '?action=pmxblog;sa='. $context['PmxBlog']['mode'].';cont='. $blog['id'] .';rating'. $context['PmxBlog']['UserLink'].'" enctype="multipart/form-data" method="post" style="padding:0px; margin:0px">
								<div id="showrating" class="smalltext" style="float:right;padding:0px 4px;">'.
									(!$blog['hasvoted'] && !isOwner() && !$user_info['is_guest']
									?	'<a href="javascript:void(\'\')" onclick="showRating();" style="font-weight:bold;">'.$txt['PmxBlog_cont_votes'].'</a>'
									:	$txt['PmxBlog_cont_votes']
									).$blog['votes'].
									($blog['votes'] > 0
									?	','.$txt['PmxBlog_cont_rating'].'<img src="' . $settings['default_images_url'] .'/PmxBlog/blog_rating'.$blog['rating'].'.gif" alt="" />'
									:	''
									).'
								</div>
								<div id="dorate" class="smalltext" style="float:right;display:none;padding:0px 4px;">
									<a href="javascript:void(\'\')" onclick="checkRate();" style="font-weight:bold;">'.$txt['PmxBlog_cont_rate_sel'].'</a>
									<select id="rateval" name="rateval" size="1" style="width:60px;">
										<option value="0">-'.$txt['PmxBlog_nocat'].'-</option>';
									for($i = 1; $i <= 10; $i++)
										echo '
										<option value="'.$i.'">'.$i.'</option>';
									echo '
									</select>
								</div>
								</form>

								<script type="text/javascript"><!-- // --><![CDATA[
								function showRating()
								{
									document.getElementById("showrating").style.display = "none";
									document.getElementById("dorate").style.display = "";
								}
								function checkRate()
								{
									if(document.getElementById("rateval").selectedIndex == 0)
									{
										document.getElementById("dorate").style.display = "none";
										document.getElementById("showrating").style.display = "";
									}
									else
										document.getElementById("pmx_form").submit();
								}
								// ]]></script>';
							}
							else
								echo '
								<div class="smalltext" style="float:right;padding:0px 4px;">'.
									$txt['PmxBlog_cont_votes'].$blog['votes'].
									($blog['votes'] > 0
									?	','.$txt['PmxBlog_cont_rating'].'<img src="' . $settings['default_images_url'] .'/PmxBlog/blog_rating'.$blog['rating'].'.gif" alt="" />'
									:	''
									).'
								</div>';

							echo '
							<br class="clear" />
							<table cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout:fixed;"><tr><td>
								<div class="plainbox pmxblog_rule "></div>
								<div style="padding:0px 4px;">
									<div style="overflow:auto;">'. $blog['body'] .'</div>
								</div>
							</td></tr></table>

							<div class="smalltext" style="padding:5px 4px 0px;">'.
								(!$blog['singlepage']
								?	'<div style="float:left;clear:right;'.(!$blog['singlepage'] ? 'padding-top:4px;' : '').'">
										<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'].$context['PmxBlog']['UserLink'].'#top">'. $txt['PmxBlog_readmore'] .'</a>
									</div>
									<div class="smalltext" style="float:right;">'.
									((isModerator() && $context['PmxBlog']['Moderate'] == $context['PmxBlog']['Manager']['userid']) || ($context['PmxBlog']['mode'] == 'manager' && isOwner($blog['userid']))
									?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'] .';edit'.$context['PmxBlog']['UserLink']. '">'. $txt['PmxBlog_edit'] .'</a>&nbsp;
										<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'] .';del'.$context['PmxBlog']['UserLink']. '" onclick="return confirm(\''. $txt['PmxBlog_confirmContdel'] .'\')">'. $txt['PmxBlog_delete'] .'</a>'
									:	''
									).'
									</div>'
								:	'<div style="float:left;clear:right;'.(!$blog['singlepage'] ? 'padding-top:4px;' : '').'">'.
									($blog['allowcomment'][1] && (($user_info['is_guest'] && in_array(-1, $context['PmxBlog']['blog_wr_acs'])) || !$user_info['is_guest'])
									?	(isBlogEnabled()
										?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cmnt='. $blog['id'] .';new'.$context['PmxBlog']['UserLink'].'">'. $txt['PmxBlog_writecomment'] .'</a>'
										:	$txt['PmxBlog_writecomment'].'<span style="vertical-align:super;">'.$txt['PmxBlog_no_comment'][1].'</span>'
										)
									:	$txt['PmxBlog_writecomment'].'<span style="vertical-align:super;">'.$txt['PmxBlog_no_comment'][0].'</span>'
									).'
									</div>
									<div class="smalltext" style="float:right;">'.
									((isModerator() && $context['PmxBlog']['Moderate'] == $context['PmxBlog']['Manager']['userid']) || ($context['PmxBlog']['mode'] == 'manager' && isOwner($blog['userid']))
									?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'] .';edit'.$context['PmxBlog']['UserLink']. '">'. $txt['PmxBlog_edit'] .'</a>&nbsp;
										<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cont='. $blog['id'] .';del'.$context['PmxBlog']['UserLink']. '" onclick="return confirm(\''. $txt['PmxBlog_confirmContdel'] .'\')">'. $txt['PmxBlog_delete'] .'</a>'
									:	''
									).'
									</div>'
								).'
							</div>
							<div style="clear:both;padding-top:'.($context['browser']['is_ie'] ? '0' : '3').'px;"></div>
							</div></div>
						'. $closeForm;
					}
					$pc++;
					$i++;
				}
			}
			elseif(!isOwner($context['PmxBlog']['UID']))
			{
				$context['PmxBlog_Error']['Link'] = '';
				template_PmxBlog_error($txt['PmxBlog_unknown_err_title'], ($context['PmxBlog']['UID'] = $user_info['id'] ?	$txt['PmxBlog_nothing'] : $txt['PmxBlog_nothing_read']));
			}

			if($context['PmxBlog']['action'][0] != 'singlepage')
			{
				if(isset($context['PmxBlog']['pageindex']))
				echo '
					<a name="bot"></a>
					<div class="catbg pmxblog_pageidx lower">
						<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
						<div class="pmxblog_pagebot">'. $context['PmxBlog']['pagetop'] .'</div>
					</div>';

				echo '
					<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
				</td>';

				Pmx_SideBar();
			}
			else
			{
				// Show all Comments
				if(isset($context['PmxBlog']['comments']))
				{
					echo '
					<a name="cmnt"></a>';

					if(isset($context['PmxBlog']['pageindex']))
						echo '
					<div class="catbg pmxblog_pageidx upper">
						<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
						<div class="pmxblog_pagebot">'. $context['PmxBlog']['pagebot'] .'</div>
						<div class="pmxblog_pagetop">'. $context['PmxBlog']['pagetop'] .'</div>
					</div>';

					echo '
					<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:4px;">';

					$pc = 0;
					$blog = $context['PmxBlog']['content'][0];
					foreach($context['PmxBlog']['comments'] as $cmt)
					{
						$ml = $cmt['treelevel'] * 15;
						if($pc >= $context['PmxBlog']['startpage'] && $pc < $context['PmxBlog']['startpage'] + $context['PmxBlog']['comment_pages'])
						{
							echo '
							<tr>
								<td style="padding-bottom:2px;padding-left:'.$ml.'px;">
									<a name="cmnt'.$cmt['id'].'"></a>'.
									($cmt['is_new_cmnt'] ? '<a name="new"></a>' : '').'
									<div class="plainbox pmxblog_cmnt">
										<div class="windowbg2">
											<div class="titlebg pmxblog_corecmnt" style="padding:2px 5px;">'.
												Title_button('comment'.($cmt['is_new_cmnt'] ? '_new' : ''), '<span style="font-weight:normal;">'. $cmt['treeS2'] .' - </span>'.$cmt['subject']) .'
											</div>
											<div style="padding:2px 4px;">
												<div class="smalltext" style="clear:both;">
													'. $txt['PmxBlog_by'] .
													($cmt['userid'] == 0
													?	'<b>'.$cmt['realname'].'</b>'
													:	'<a href="'.$scripturl.'?action=profile;u='.$cmt['userid'].'"><b>'. $cmt['realname'] .'</b></a>'
													).
													' '.$txt['PmxBlog_on'] . $cmt['date_created']. ($cmt['is_edit'] ?	' &nbsp; - &nbsp; '. $txt['PmxBlog_lastedit'] . $cmt['date_edit'] : '') .'
												</div>
											</div>

											<div style="padding:2px 4px;">'. $cmt['body'] .'</div>

											<div style="padding:4px 2px 0px;">
												<div class="smalltext" style="float:left;clear:right;">'.
													($blog['allowcomment'][1] && (($user_info['is_guest'] && in_array(-1, $context['PmxBlog']['blog_wr_acs'])) || !$user_info['is_guest'])
													?	(isBlogEnabled()
														?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .';cmnt='. $cmt['id'] .';rply'.$context['PmxBlog']['UserLink'].'">'. $txt['PmxBlog_write_reply'] .'</a>'
														:	$txt['PmxBlog_write_reply'].'<span style="vertical-align:super;">'.$txt['PmxBlog_no_comment'][1].'</span>'
														)
													:	$txt['PmxBlog_write_reply'].'<span style="vertical-align:super;">'.$txt['PmxBlog_no_comment'][0].'</span>'
													).'
												</div>
												<div class="smalltext" style="float:right; padding-right:4px;">'.
													((isModerator() && $context['PmxBlog']['Moderate'] == $context['PmxBlog']['Manager']['userid']) || ($context['PmxBlog']['mode'] == 'manager' && AllowedToBlog('manager') && isOwner($context['PmxBlog']['UID'])) || ($blog['allowcomment'][1] && isOwner($cmt['userid']) && !$user_info['is_guest'])
													?	'<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='. $cmt['id'].';edit'.$context['PmxBlog']['UserLink']. '">'. $txt['PmxBlog_edit'] .'</a>&nbsp;
														<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].';cmnt='. $cmt['id'] .';del'.$context['PmxBlog']['UserLink']. '" onclick="return confirm(\''.$txt['PmxBlog_confirmcmntdel'] .'\')">'. $txt['PmxBlog_delete'] .'</a>'
													:	''
													).'
												</div>
												<div style="clear:both;padding-top:'.($context['browser']['is_ie'] ? '0' : '3').'px;"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>';
						}
						$pc++;
					}
					echo '
					</table>
					<a name="bot"></a>';

					if(isset($context['PmxBlog']['pageindex']))
						echo '
					<div class="catbg pmxblog_pageidx lower">
						<div class="pmxblog_pagenum">'. $context['PmxBlog']['pageindex'] .'</div>
						<div class="pmxblog_pagebot">'. $context['PmxBlog']['cmntpagetop'] .'</div>
						<div class="pmxblog_pagetop">'. $context['PmxBlog']['pagetop'] .'</div>
					</div>';
				}
				echo '
					<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>
				</td>';

				Pmx_SideBar();
			}
		}
		echo '
		</tr>
	</table>';
	}
}

// display captcha image
function captcha_template($backurl)
{
	global $settings, $txt, $boardurl;

	makeCaptcha($backurl);
	$_SESSION['PmxBlog_captcha']['show'] = false;
	$b64 = base64_encode($_SESSION['PmxBlog_captcha']['chars']);
	$res = '';
	for ($i = 0; $i < strlen($b64); $i++)
		$res .= dechex(ord(substr($b64, $i, 1)));
	$captcha_str = $res.dechex(mktime());
	$bdir = str_replace('\\', '/', $settings['default_theme_dir']);

	if(isset($_SESSION['PmxBlog_cmnt_body']))
		unset($_SESSION['PmxBlog_cmnt_body']);

	echo '
	<br />
	<div align="left" valign="middle" style="padding:3px 5px;">
	<div style="padding-right:10px;padding-top:5px;float:left;">'.$txt['PmxBlog_cmnt_captcha'].'</div>
	<div>
		<table cellspacing="0" cellpadding="0" border="0">
			<tr>';
			if(in_array('gd', get_loaded_extensions()))
				echo '
				<td style="margin-top:2px;height:28px;width:130;">
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir, '" alt="" />';
			else
				echo '
				<td "style="margin-top:2px;height:28px;width:130;background-repeat:no-repeat;background-image: url('.$settings['default_images_url'].'/PmxBlog/backgnd.gif)">
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir.'&letter=1', '" alt="" style="margin:-2px -9px -6px 7px;" />
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir.'&letter=2', '" alt="" style="margin:-2px -9px -6px 0px;" />
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir.'&letter=3', '" alt="" style="margin:-2px -9px -6px 0px;" />
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir.'&letter=4', '" alt="" style="margin:-2px -9px -6px 0px;" />
					<img src="', $boardurl . '/Sources/PmxBlogCaptcha.php?vcode='.$captcha_str.'&path='.$bdir.'&letter=5', '" alt="" style="margin:-2px -9px -6px 0px;" />';
			echo '
				</td>
			</tr>
			<tr>
				<td style="padding-top:2px;">
					<input class="normaltext" type="text" name="captcha" size="8" style="width:118px;" />
				</td>
			</tr>
		</table>
	</div>';
}

// Show an error message.....
function template_PmxBlog_error($errtitle = '', $errmsg = '')
{
	global $context, $settings, $options, $txt;

	echo '
	<div style="width:70%; margin:0 auto; text-align:center;">
		<h3 class="titlebg" style="text-align:center;">
			<span class="left"><span></span></span>
			'.(empty($errtitle) ? $context['PmxBlog_Error']['Title'] : $errtitle) .'
		</h3>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div style="padding: 1ex;">'. (empty($errmsg) ? $context['PmxBlog_Error']['Msg'] : $errmsg) .'</div>
			<div align="center" style="margin-top: 1ex;">';
			if(empty($context['PmxBlog_Error']['Link']))
				echo '
				<input type="button" class="button_submit" name="back" value="', $txt['back'], '" onclick=\'window.history.back()\' />';
			else
				echo '
				<input type="button" class="button_submit" name="back" value="', $txt['back'], '" onclick=\'window.location.href="', $context['PmxBlog_Error']['Link'] ,'"\' />';
			echo '
				</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<div class="smalltext" style="text-align:center; margin: 0 auto;">'.$context['PmxBlog']['copyright'].'</div>';
}
?>