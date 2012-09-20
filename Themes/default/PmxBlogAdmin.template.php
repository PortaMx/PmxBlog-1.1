<?php
// ----------------------------------------------------------
// -- PmxBlogAdmin.template.php                            --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

function template_main()
{
	global $context, $settings, $scripturl, $txt;
	require_once($settings['default_theme_dir'] . '/PmxBlogMenu.php');

	Navigation($context['PmxBlog']['nav_tabs']);

	$curact = AdminTabs($context['PmxBlog']['admin_tabs']);
	if($curact == 'setup')
	{
		echo '
		<form id="pmx_form" accept-charset="'.$context['character_set'].'" name="PmxBlog_Setting" action="' . $scripturl . '?action=pmxblog;sa=admin;setup=upd" method="post" style="margin: 0px;">
		<div class="windowbg2 pmxblog_core" style="margin-top:4px;">
		<span class="topslice"><span></span></span>
			<table width="100%" border="0" class="windowbg2" style="padding:3px 5px;">
				<tr style="margin-top:10px;"><td valign="top" align="right" style="width:45%; padding-top:10px;">
					'. $txt['PmxBlog_usewysiwyg'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input type="hidden" name="wysiwyg_edit" value="" />
					<select name="wysiwyg_edit[]" size="5" multiple="multiple" style="margin-left:4px; width:50%;">';

		foreach($context['PmxBlog']['SMF_groups'] as $grp)
		{
			if($grp['ID'] != -1)
				echo '
					<option value="'. $grp['ID'] .'"'. (is_array($context['PmxBlog']['wysiwyg_edit']) && in_array($grp['ID'], $context['PmxBlog']['wysiwyg_edit']) ? ' selected="selected"' : '') .'>'. $grp['Name'] .'</option>';
		}

		echo '
					</select>
				</td></tr>

				<tr><td valign="top" align="right" style="width:45%;">
					'. $txt['PmxBlog_usewysiwygcmt'] .'
				</td><td valign="top">
					<input type="hidden" name="wysiwyg_comment" value="" />
					<select name="wysiwyg_comment[]" size="5" multiple="multiple" style="margin-left:4px; width:50%;">';

		foreach($context['PmxBlog']['SMF_groups'] as $grp)
			echo '
					<option value="'. $grp['ID'] .'"'. (is_array($context['PmxBlog']['wysiwyg_comment']) && in_array($grp['ID'], $context['PmxBlog']['wysiwyg_comment']) ? ' selected="selected"' : '') .'>'. $grp['Name'] .'</option>';

		echo '
					</select>
				</td></tr>

				<tr style="margin-top:10px;"><td valign="top" align="right" style="width:45%; padding-top:10px;">
					'. $txt['PmxBlog_moderatorgroups'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input type="hidden" name="modgroups" value="" />
					<select name="modgroups[]" size="5" multiple="multiple" style="margin-left:4px; width:50%;">';

		foreach($context['PmxBlog']['SMF_groups'] as $grp)
		{
			if($grp['ID'] > 0 && $grp['Typ'] == 0)
				echo '
					<option value="'. $grp['ID'] .'"'. (is_array($context['PmxBlog']['modgroups']) && in_array($grp['ID'], $context['PmxBlog']['modgroups']) ? ' selected="selected"' : '') .'>'. $grp['Name'] .'</option>';
		}

		echo '
					</select>
				</td></tr>

				<tr><td align="right" valign="top" style="padding-top:10px; width:45%;">
					'. $txt['PmxBlog_image_prefix'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input name="image_prefix" type="hidden" value="2" />
					<input class="check" name="image_prefix" type="checkbox" value="1"', $context['PmxBlog']['image_prefix'] == 1 ? ' checked="checked"': '', ' />
					'. $txt['PmxBlog_checkbox_help'] .'
				</td></tr>

				<tr><td align="right" style="width:45%;padding-top:10px;">
					'. $txt['PmxBlog_censor_text'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input type="hidden"" name="censor_text" value="0" />
					<input class="check" name="censor_text" type="checkbox" value="1"', $context['PmxBlog']['censor_text'] == 1 ? ' checked="checked"': '', ' />
					'. $txt['PmxBlog_checkbox_help'] .'
				</td></tr>

				<tr style="margin-top:10px;"><td align="right" style="width:45%;">
					'. $txt['PmxBlog_removelinks'].'
				</td><td valign="top">
					<input type="hidden"" name="remove_links" value="0" />
					<input class="check" name="remove_links" type="checkbox" value="1"', $context['PmxBlog']['remove_links'] == 1 ? ' checked="checked"': '', ' />
					'. $txt['PmxBlog_checkbox_help'] .'
				</td></tr>
				<tr><td align="right" style="width:45%;">
					'. $txt['PmxBlog_removeimages'] .'
				</td><td valign="top">
					<input type="hidden"" name="remove_images" value="0" />
					<input class="check" name="remove_images" type="checkbox" value="1"', $context['PmxBlog']['remove_images'] == 1 ? ' checked="checked"': '', ' />
					'. $txt['PmxBlog_checkbox_help'] .'
				</td></tr>

				<tr style="margin-top:10px;"><td align="right" style="width:45%;padding-top:10px;">
					'. $txt['PmxBlog_showthumnails'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input type="hidden"" name="thumb_show" value="0" />
					<input class="check" name="thumb_show" type="checkbox" value="1"', $context['PmxBlog']['thumb_show'] == 1 ? ' checked="checked"' : '', ' />
					'. $txt['PmxBlog_checkbox_help'] .'
				</td></tr>
				<tr><td align="right" style="width:45%;">
					'. $txt['PmxBlog_thumbnail_size'] .'
				</td><td valign="top">
					<input name="thumb_size" type="text" size="8" value="', $context['PmxBlog']['thumb_size'] ,'" style="margin-left:3px;" />
					'. $txt['PmxBlog_thumbnail_size_help'] .'
				</td></tr>

				<tr style="margin-top:10px;"><td align="right" valign="top" style="width:45%;padding-top:10px;">
					'. $txt['PmxBlog_htmltags'] .'
				</td><td valign="top"  style="padding-top:10px;">
					<input name="htmltags" type="text" size="70" value="', $context['PmxBlog']['htmltags'] ,'" style="margin-left:3px; width:90%;" />
					<div style="margin-left:3px; margin-top:2px; width:90%;">'. $txt['PmxBlog_htmltags_help'] .'</div>
				</td></tr>

				<tr><td align="right" style="width:45%;padding-top:10px;">
					'. $txt['PmxBlog_overviewpages'] .'
				</td><td valign="top" style="padding-top:10px;">
					<input style="margin-left:4px;" name="overview_pages" type="text" size="2" value="'. $context['PmxBlog']['overview_pages'] .'" />
				</td></tr>
				<tr><td align="right" style="width:45%;">
					'. $txt['PmxBlog_contentpages'] .'
				</td><td valign="top">
					<input style="margin-left:4px;" name="content_pages" type="text" size="2" value="'. $context['PmxBlog']['content_pages'] .'" />
				</td></tr>
				<tr><td align="right" style="width:45%;">
					'. $txt['PmxBlog_commentpages'] .'
				</td><td valign="top">
					<input style="margin-left:4px;" name="comment_pages" type="text" size="2" value="'. $context['PmxBlog']['comment_pages'] .'" />
				</td></tr>
				<tr><td align="right" style="width:45%;">
					'. $txt['PmxBlog_contentlen'] .'
				</td><td valign="top">
					<input style="margin-left:4px;" name="content_len" type="text" size="2" value="'. $context['PmxBlog']['content_len'] .'" />
				</td></tr>
				<tr><td align="right" style="width:45%;padding-top:10px;">
					'. $txt['PmxBlog_blog_admin'] .'
				</td><td valign="top" style="padding-top:10px;padding-left:5px;">
					<select name="blogadmin">';
					foreach($context['PmxBlog']['Admin_List'] as $adm)
						echo '
						<option value="'.$adm['id'].'"', $context['PmxBlog']['blogadmin'] == $adm['id'] ? ' selected="selected"' : '', '>'.$adm['name'].'</option>';
					echo '
					</select>
				</td></tr>
				<tr><td colspan="2" align="center" valign="middle" style="padding-top:10px;">
					<input class="button_submit" type="submit" value="' . $txt['PmxBlog_send'] .'" name="send" />
				</td></tr>
			</table>
			<span class="botslice"><span></span></span>
			</div>
		</form>';
	}
	elseif($curact = 'acs')
	{
		echo '
		<form id="pmx_form" accept-charset="'.$context['character_set'].'" name="PmxBlog_Setting" action="' . $scripturl . '?action=pmxblog;sa=admin;acs=upd" method="post" style="margin: 0px;">
		<div class="windowbg2 pmxblog_core" style="margin-top:4px;">
		<span class="topslice"><span></span></span>
			<table width="100%" border="0" class="windowbg2" style="padding:3px 5px;">
				<tr><td style="width:33%;">
					'.$txt['PmxBlog_blog_manage'].'
				</td><td style="width:33%;">
					'.$txt['PmxBlog_blog_rd'].'
				</td><td style="width:33%;">
					'.$txt['PmxBlog_blog_wr'].'
				</td></tr>

				<tr><td style="width:33%;">';
					$typM = 0;
					$typR = 0;
					$typW = 0;
					foreach($context['PmxBlog']['SMF_groups'] as $g)
					{
						if($g['ID'] != 1)
						{
							if($g['Typ'] != $typM)
							{
								echo '<br /><hr /><br />';
								$typM = $g['Typ'];
							}
							echo '
							<input class="check" name="blog_acs'.$g['ID'].'" type="checkbox" value="'. $g['ID'] .'"'.
								($g['ID'] == '-1'
								?	' disabled="disabled"'
								:	''
								).
								(in_array($g['ID'], $context['PmxBlog']['blog_acs'])
									?	' checked="checked"'
									:	''
								). ' /> '.$g['Name'].'<br />';
						}
					}
					echo '
				</td><td style="width:33%;">';
					foreach($context['PmxBlog']['SMF_groups'] as $g)
					{
						if($g['ID'] != 1)
						{
							if($g['Typ'] != $typR)
							{
								echo '<br />'.$txt['PmxBlog_post_groups'].'<br />';
								$typR = $g['Typ'];
							}
							echo '
							<input class="check" name="blog_rd_acs'.$g['ID'].'" type="checkbox" value="'. $g['ID'] .'"'.
								(in_array($g['ID'], $context['PmxBlog']['blog_rd_acs'])
								?	' checked="checked"'
								:	''
								).' /> '.$g['Name'].'<br />';
						}
					}
					echo '
				</td><td style="width:33%;">';
					foreach($context['PmxBlog']['SMF_groups'] as $g)
					{
						if($g['ID'] != 1)
						{
							if($g['Typ'] != $typW)
							{
								echo '<br /><hr /><br />';
								$typW = $g['Typ'];
							}
							echo '
							<input class="check" name="blog_wr_acs'.$g['ID'].'" type="checkbox" value="'. $g['ID'] .'"'.
								(in_array($g['ID'], $context['PmxBlog']['blog_wr_acs'])
								?	' checked="checked"'
								:	''
								).' /> '.$g['Name'].'<br />';
						}
					}
					echo '
				</td></tr>
				<tr><td colspan="3" align="center" class="smalltext" style="padding-top:5px;">
					'. $txt['PmxBlog_membergroups'] .'
				</td></tr>
				<tr><td colspan="3" align="center" valign="middle" style="padding-top:10px;">
					<input class="button_submit" type="submit" value="' . $txt['PmxBlog_send'] .'" name="send" />
				</td></tr>
			</table>
			<span class="botslice"><span></span></span>
			</div>
		</form>';
	}
}
?>