<?php
// ----------------------------------------------------------
// -- PmxBlogSettings.template.php                         --
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
	global $context, $txt, $scripturl, $settings;

	Navigation($context['PmxBlog']['nav_tabs']);

	if(isBlogEnabled() || isOwner() || AllowedTo('admin_forum'))
	{
		Pmx_Header($context['PmxBlog']['Manager']);

		$cameFrom = !empty($_GET['cfr']) ? ';cfr='. $_GET['cfr'] : ';cfr=view';
		echo '
		<div class="pmxblog_headpad"></div>';

		$curact = AdminTabs($context['PmxBlog']['setting_tabs']);

		if(isBlogLocked())
		{
			echo '
			<div class="plainbox normaltext" style="margin:5px auto; padding:3px 0px; text-align:center; background-color:#ff0000; color:#ffff00;">';

			if(!AllowedTo('admin_forum'))
				echo '<b>'. $txt['PmxBlog_bloglocked_message'] .'</b>';
			else
				echo '<b>'. $txt['PmxBlog_islocked'] .'</b>';

			echo '
			</div>';
		}

		// Manage settings ?
		if($curact == 'settings')
		{
			$cuID = $context['PmxBlog']['UID'];
			$trMode = isset($context['PmxBlog']['trackmode'][$cuID]) ? explode('|', $context['PmxBlog']['trackmode'][$cuID]) : array(0, 0);
			$trData = isset($context['PmxBlog']['tracklist'][$cuID]) ? implode(',', $context['PmxBlog']['tracklist'][$cuID]) : '';

			echo '
			<form id="pmx_form" name="PmxBlog_Manager" action="' . $scripturl . '?action=pmxblog;sa=manager;setup=upd'.$context['PmxBlog']['UserLink']. $cameFrom .'" method="post" style="margin: 0px;">'.
			(isOwner() ? '
			<input name="contenteditor" type="hidden" value="1" />
			<input name="commenteditor" type="hidden" value="1" />' : '') .'
			<div class="windowbg2 pmxblog_core pmx_roundcore">
			<span class="topslice"><span></span></span>
			<table class="windowbg2" cellspacing="0" width="100%" style="table-layout:fixed;">
				<tr>
					<td align="right" width="45%" style="padding-bottom: 5px;">'. $txt['PmxBlog_blogname'] .'</td>
					<td width="55%" style="padding-bottom: 5px;">
						<input name="blogname" type="text" size="42" value="'. $context['PmxBlog']['Manager']['blogname'] .'"', !isOwner() ? 'disabled="disabled"' : '', ' style="margin-left:3px;" />
					</td>
				</tr><tr>
					<td align="right" width="45%">'. $txt['PmxBlog_blogdesc'] .'</td>
					<td width="55%">
						<input name="blogdesc" type="text" size="42" value="'. $context['PmxBlog']['Manager']['blogdesc'] .'"', !isOwner() ? 'disabled="disabled"' : '', ' style="margin-left:3px;" />
					</td>
				</tr><tr>
					<td align="right" width="45%" style="padding-top:10px;">'. $txt['PmxBlog_showAvatar'] .'</td>
					<td width="55%" style="padding-top:10px;">
						'. (isOwner() ? '<input name="showavatar" type="hidden" value="2" />' : '') .'
						<input class="check" name="showavatar" type="checkbox" value="1"', $context['PmxBlog']['Manager']['settings']{2} == '1' ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
					</td>
				</tr><tr>
					<td align="right" width="45%">'. $txt['PmxBlog_showcalendar'] .'</td>
					<td width="55%">
						'. (isOwner() ? '<input name="showcalendar" type="hidden" value="0" />' : '') .'
						<input class="check" name="showcalendar" type="checkbox" value="1"', $context['PmxBlog']['Manager']['showcalendar'] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
					</td>
				</tr><tr>
					<td align="right" width="45%">'. $txt['PmxBlog_showarchive'] .'</td>
					<td width="55%">
						'. (isOwner() ? '<input name="showarchive" type="hidden" value="0" />' : '') .'
						<input class="check" name="showarchive" type="checkbox" value="1"', $context['PmxBlog']['Manager']['showarchive'] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
					</td>
				</tr><tr>
					<td align="right" width="45%">'. $txt['PmxBlog_showcategories'] .'</td>
					<td width="55%">
						'. (isOwner() ? '<input name="showcategories" type="hidden" value="0" />' : '') .'
						<input class="check" name="showcategories" type="checkbox" value="1"', $context['PmxBlog']['Manager']['showcategories'] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
					</td>
				</tr><tr>
					<td align="right" width="45%">'. $txt['PmxBlog_hide_on_edit'] .'</td>
					<td width="55%">
						'. (isOwner() ? '<input name="hidebaronedit" type="hidden" value="0" />' : '') .'
						<input class="check" name="hidebaronedit" type="checkbox" value="1"', $context['PmxBlog']['Manager']['hidebaronedit'] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
					</td>';

			if(getEditorAcs($context['PmxBlog']['wysiwyg_edit']))
				echo '
				</tr><tr>
					<td align="right" width="45%" valign="top" style="padding-top:10px;">'. $txt['PmxBlog_content_editor'] .'</td>
					<td width="55%" valign="top" style="padding-top:10px;">
						<input class="check" name="contenteditor" type="radio" value="1"', $context['PmxBlog']['Manager']['settings']{0} == '1' ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'. $txt['PmxBlog_editor_html'] .'</span><br />
						<input class="check" name="contenteditor" type="radio" value="2"', $context['PmxBlog']['Manager']['settings']{0} == '2' ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'. $txt['PmxBlog_editor_bbc'] .'</span>
					</td>';

			if(getEditorAcs($context['PmxBlog']['wysiwyg_comment']))
				echo '
				</tr><tr>
					<td align="right" width="45%" valign="top" style="padding-top:10px;">'. $txt['PmxBlog_comment_editor'] .'</td>
					<td width="55%" valign="top" style="padding-top:10px;">
						<input class="check" name="commenteditor" type="radio" value="1"', $context['PmxBlog']['Manager']['settings']{1} == '1' ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'. $txt['PmxBlog_editor_html'] .'</span><br />
						<input class="check" name="commenteditor" type="radio" value="2"', $context['PmxBlog']['Manager']['settings']{1} == '2' ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'. $txt['PmxBlog_editor_bbc'] .'</span>
					</td>';

			echo '
				</tr><tr>
					<td align="right" width="45%" style="padding-top:10px;"><b>'. $txt['PmxBlog_blogenabled'] .'</b></td>
					<td width="55%" style="padding-top:10px;">
						<input class="check" name="blogenabled" type="checkbox" value="1"', isBlogEnabled() ? ' checked="checked"': '', ' />
						'. $txt['PmxBlog_checkbox_help'] .'
						<input name="blogcreated" type="hidden" value="'.$context['PmxBlog']['Manager']['blogcreated'].'" />
						'. (isOwner() ? '<input name="bloglocked" type="hidden" value="'. (isBlogLocked() ? '1' : '0') .'" />' : '') .'
					</td>',
					(AllowedTo('admin_forum') && !isOwner()
						? '
				</tr><tr>
					<td align="right" width="45%" style="padding-top:10px;"><b>'. $txt['PmxBlog_bloglocked'] .'</b></td>
					<td width="55%" style="padding-top:10px;">
						<input class="check" name="bloglocked" type="checkbox" value="1"'. (isBlogLocked() ? ' checked="checked"' : ''). ' />
						'. $txt['PmxBlog_lockcheckbox_help'] .'
					</td>'
						: ''),'
				</tr><tr>
					<td align="right" valign="top" width="45%" style="padding-top:10px;">'. $txt['PmxBlog_tracknotify'] .'</td>
					<td width="55%" style="padding-top:10px;">
						<input class="check" name="tracking" type="radio" value="0:'.$trData.'"', $trMode[1] == 0 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_off'].'</span><br />
						<input class="check" name="tracking" type="radio" value="1:'.$trData.'"', $trMode[1] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_email'].'</span><br />
						<input class="check" name="tracking" type="radio" value="2:'.$trData.'"', $trMode[1] == 2 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_pm'].'</span>
					</td>
				</tr><tr>
					<td align="right" valign="top" width="45%" style="padding-top:5px;">'. $txt['PmxBlog_trackself'] .'</td>
					<td width="55%" style="padding-top:5px;">
						<input class="check" name="trackself" type="radio" value="0"', $trMode[0] == 0 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_off'].'</span><br />
						<input class="check" name="trackself" type="radio" value="1"', $trMode[0] == 1 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_email'].'</span><br />
						<input class="check" name="trackself" type="radio" value="2"', $trMode[0] == 2 ? ' checked="checked"': '', !isOwner() ? 'disabled="disabled"' : '', ' /> <span style="vertical-align:3px;">'.$txt['PmxBlog_tracknotify_pm'].'</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center" valign="middle" style="padding-top:10px;">
						<input class="button_submit" type="submit" value="' . $txt['PmxBlog_send'] .'" name="send" />
					</td>
				</tr>
			</table>
			<span class="botslice"><span></span></span>
			</div>
			<div class="smalltext pmx_botline">'.$context['PmxBlog']['copyright'].'</div>
			</form>';
		}

		// Manage categories ?
		elseif($curact == 'categorie')
		{
			// load javascript routines for Categorie handling
			if(isOwner() && !isBlogLocked())
			{
				echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	var isEditMode = false;
	var haveChanged = false;
	var cats = new Array();';
				$i = 0;
				$c = $context['PmxBlog']['categorie'];
				if (!empty($context['PmxBlog']['categorie']))
				{
					while(isset($c[$i]))
					{
						echo '
	cats['.$i.'] = new Object();
	cats['.$i.']["id"] = "'.$c[$i]['id'].'";
	cats['.$i.']["name"] = "'.$c[$i]['name'].'";
	cats['.$i.']["corder"] = "'.$c[$i]['corder'].'";
	cats['.$i.']["depth"] = "'.$c[$i]['depth'].'";
	cats['.$i.']["chgtype"] = "";';
			$i++;
					}
					echo '
	var numcats = '.($i-1).';
	var savecats = numcats;';
				}
				else
					echo '
	var numcats = -1;
	var savecats = -1;';
			echo '
	var confDelText = "'.$txt['PmxBlog_confirmcatdel'].'";
	';

			require_once($settings['default_theme_dir'].'/PmxBlogSettings.javascript.inc');

			echo '
				// ]]></script>';
			}
			echo '
			<div class="title_bar"><h3 class="titlebg">
				<div class="smalltext" style="float:left; width:205px;">'. $txt['PmxBlog_short_action'] .'</div>
				<div class="smalltext" style="float:left;">'. $txt['PmxBlog_categorie_title'] .'</div>
				'.(isOwner() && !isBlogLocked()
				?	'<div class="smalltext" style="float:right; white-space:nowrap;">
						<span id="AddCat" style="cursor:pointer;" onclick="NewCategorie()">'. $txt['PmxBlog_newcat'] .'</span>
					</div>'
				: '') .'
			</h3></div>

			<div class="windowbg2 pmxblog_core pmx_roundcore">
			<span class="topslice"><span></span></span>
			<table class="windowbg2" cellspacing="0" width="100%" style="table-layout:fixed;">
			<tbody id="categorie_edit">';
			if(isOwner() && !isBlogLocked())
				echo '
				<tr id="clone" style="display:none;"><td style="padding:0px 10px;">
					<div style="float:left;width:200px;margin-left:5px;">
						<div style="float:left;width:22px;">
							<img id="MoveCatL999999" style="cursor:pointer;display:none;padding-bottom:2px;" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveleft.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveleft'] .'" />
							<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
						</div>
						<div style="float:left;width:22px;">
							<img id="MoveCatU999999" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveup.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveup'] .'" />
							<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
						</div>
						<div style="float:left;width:22px;">
							<img id="MoveCatD999999" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_movedown.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_movedown'] .'" />
							<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
						</div>
						<div style="float:left;width:22px;">
							<img id="MoveCatR999999" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveright.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveright'] .'" />
							<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
						</div>
						<img id="EditCatName999999" style="cursor:pointer;" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/buttons/modify.gif" alt="'. $txt['PmxBlog_cat_edit'] .'" title="'. $txt['PmxBlog_cat_edit'] .'" />
						<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:3px;height:20px;" />
						<img id="DeleteCat999999" style="cursor:pointer;" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/buttons/delete.gif" alt="'. $txt['PmxBlog_cat_delete'] .'" title="'. $txt['PmxBlog_cat_delete'] .'" />
					</div>
					<div style="float:left;padding-top:3px;">
						<div id="edit999999" style="display:none;">
							<input id="catname999999" value="" type="text" size="30" />
							<img id="UpdateCat999999" style="cursor:pointer;margin-bottom:-4px;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_save.gif" alt="Save" onclick="funcByName(this.getAttribute(\'id\'));return false;" />
						</div>
						<div id="show999999">
							<span id="dpt999999"></span><span id="txt999999"></span>
						</div>
					</div>
					<br style="clear:both;" />
				</td></tr>';

				if (!empty($context['PmxBlog']['categorie']))
				{
					$cl = $context['PmxBlog']['categorie'][0]['depth'];
					$i = 0;
					foreach($context['PmxBlog']['categorie'] as $fbcat)
					{
						$d = ''.str_pad('', $fbcat['depth']*2, '.');
						echo '
						<tr id="entry'.$i.'"><td style="padding:0px 10px;">
							<div style="float:left;width:200px;margin-left:5px;">'.
								(isOwner() && !isBlogLocked() ? '
								<div style="float:left;width:22px;">
									<img id="MoveCatL'.$i.'" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveleft.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveleft'] .'" />
									<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
								</div>
								<div style="float:left;width:22px;">
									<img id="MoveCatU'.$i.'" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveup.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveup'] .'" />
									<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
								</div>
								<div style="float:left;width:22px;">
									<img id="MoveCatD'.$i.'" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_movedown.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_movedown'] .'" />
									<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
								</div>
								<div style="float:left;width:22px;">
									<img id="MoveCatR'.$i.'" style="cursor:pointer;display:none;padding-bottom:2px" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_moveright.gif" alt="'. $txt['PmxBlog_cat_moveleft'] .'" title="'. $txt['PmxBlog_cat_moveright'] .'" />
									<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:1px;height:20px;" />
								</div>
								<img id="EditCatName'.$i.'" style="cursor:pointer;" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/buttons/modify.gif" alt="'. $txt['PmxBlog_cat_edit'] .'" title="'. $txt['PmxBlog_cat_edit'] .'" />
								<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:2px;height:20px;" />
								<img id="DeleteCat'.$i.'" style="cursor:pointer;" onclick="funcByName(this.getAttribute(\'id\'));return false;" src="' . $settings['default_images_url'] .'/buttons/delete.gif" alt="'. $txt['PmxBlog_cat_delete'] .'" title="'. $txt['PmxBlog_cat_delete'] .'" />'
								: '
								<img src="' . $settings['default_images_url'] .'/PmxBlog/cat_spacer.gif" alt="" style="width:180px;height:20px;" />').'
							</div>
							<div style="float:left;padding-top:3px;">
								<div id="edit'.$i.'" style="display:none;">'.$d.'
									<input id="catname'.$i.'" value="'.$fbcat['name'].'" type="text" size="30" />
									<img id="UpdateCat'.$i.'" style="cursor:pointer;margin-bottom:-4px;" src="' . $settings['default_images_url'] .'/PmxBlog/cat_save.gif" alt="Save" onclick="funcByName(this.getAttribute(\'id\'));return false;" />
								</div>
								<div id="show'.$i.'">
									<span id="dpt'.$i.'">'.$d.'</span><span id="txt'.$i.'">'.$fbcat['name'].'</span>
								</div>
							</div>
							<br style="clear:both;" />
						</td></tr>';

						$cl = $fbcat['depth'];
						$i++;
					}
				}
				echo '
				</tbody>
			</table>

			<div id="SaveButton" style="display:none;margin-top:2px;">
				<form id="pmx_form" name="PmxBlog_CatED" action="'.$scripturl.'?action=pmxblog;sa=manager;setup=cat;upd'.$context['PmxBlog']['UserLink']. $cameFrom .'" method="post">
					<div style="display:none">
						<select id="pmxblog_cat_id" name="id[]" multiple="multiple"></select>
						<select id="pmxblog_cat_name" name="name[]" multiple="multiple"></select>
						<select id="pmxblog_cat_corder" name="corder[]" multiple="multiple"></select>
						<select id="pmxblog_cat_depth" name="depth[]" multiple="multiple"></select>
						<select id="pmxblog_cat_chgtype" name="chgtype[]" multiple="multiple"></select>
					</div>
				</form>
				<div style="margin:0 auto; text-align:center;">
					<input style="margin:8px 0px;" type="button" class="button_submit" value="' . $txt['PmxBlog_send'] .'" onclick="submitEditCats()" />
				</div>
			</div>
			<span class="botslice"><span></span></span>
			</div>
			<div class="smalltext pmx_botline">'.$context['PmxBlog']['copyright'].'</div>
			'.
			(isOwner() && !isBlogLocked() ? '
			<script type="text/javascript">
			<!--
				showMover();
			-->
			</script>'
			: '');
		}

		// Manage blog remove ?
		elseif($curact == 'removeblog')
		{
			echo '
			<div class="title_bar"><h3 class="titlebg" text-align:center;">
				'. $txt['PmxBlog_remove_title'] .'
			</h3></div>
			<form id="pmx_form" name="PmxBlogManager_BlogDelete" action="' . $scripturl . '?action=pmxblog;sa=manager;setup=remove'.$context['PmxBlog']['UserLink'].'" method="post" style="margin: 0px;">
				<div class="windowbg2 pmxblog_core pmx_roundcore">
				<span class="topslice"><span></span></span>
					<div style="padding:5px 5px;text-align:center;">
					'. $txt['PmxBlog_blog_remove'] .'<br /><br />
						<input style="font-weight:bold;" type="submit" class="button_submit" value="' . $txt['PmxBlog_remblog'] .'" name="remblog" onclick="return confirm(\''.$txt['PmxBlog_confirmblogdel'].'\')" />
					</div>
				<span class="botslice"><span></span></span>
				</div>
				<div class="smalltext pmx_botline">'.$context['PmxBlog']['copyright'].'</div>
			</form>';
		}
	}
	elseif(!isBlogEnabled())
	{
		echo '
		<div align="center" class="catbg" style="padding:3px; margin-bottom:5px;">
			', $context['PmxBlog']['mode'] == 'manager' ? $txt['PmxBlog_manager_title'] : $txt['PmxBlog_blogview_title'], '
		</div>';
		Pmx_Header($context['PmxBlog']['Manager']);
	}
}
?>