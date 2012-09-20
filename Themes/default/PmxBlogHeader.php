<?php
// ----------------------------------------------------------
// -- PmxBlogHeader.template.php                           --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

function Pmx_Header($head, $isContent = false)
{
	global $context, $txt, $scripturl, $user_info, $settings;

	$allowMan = (AllowedToBlog('manager') && isOwner($head['userid']));
	$ContLink = isset($_GET['cont']) && !empty($_GET['cont']) ? ';cont='.$_GET['cont'] : '';
	$cameFrom = !empty($_GET['cfr']) ? $_GET['cfr'] : ';cfr='. $context['PmxBlog']['mode'];
	$isModerator = isModerator($head['userid']);

	echo '
	<span class="upperframe"><span></span></span>
	<div class="roundframe pmx_roundcore">
	<table style="padding:0px; margin:0px;" width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td rowspan="2" align="center" valign="middle">
				<div style="width:84px; padding:2px 6px 2px 2px;">'.
					($head['settings']{2} == '1'
					? ($context['PmxBlog']['blog_rd_acs']
						? '<a href="'. $scripturl .'?action=pmxblog;sa=view;uid='. $head['userid'].'">'.$head['avatar'].'</a>'
						: $head['avatar']
						)
					: ($context['PmxBlog']['blog_rd_acs']
						? '<a href="'. $scripturl .'?action=pmxblog;sa=view;uid='. $head['userid'].'"><img src="' . $settings['default_images_url'] .'/PmxBlog/noavatar.gif" alt="*" /></a>'
						: ''
						)
					)
				.'</div>
			</td>
			<td class="plainbox" style="background:transparent; padding:0 2px; border-width:0; border-bottom-width:1px;" colspan="2" valign="top" width="'.($user_info['is_guest'] ? '99' : '75').'%">
				<div style="height:20px; padding:5px 2px 5px 0px; font-size:20px; font-style:italic; font-family: Tahoma, Verdana, arial;">'.
					($context['PmxBlog']['blog_rd_acs']
						? '<a href="'. $scripturl .'?action=pmxblog;sa=view;uid='. $head['userid'].'">'.$head['blogname'].'</a>'
						: $head['blogname']
					)
				.'</div>
				<div class="smalltext" style="padding:0 2px 4px 0px;">
				'. $head['blogdesc'] .'
				</div>
			</td>'.

		(!$user_info['is_guest']
		?	'<td valign="top" align="right" nowrap="nowrap" style="padding-left:1px;">
			<div  style="width:170px;">
				<div class="smalltext" style="padding:0px 4px; text-align:right; height:12px;">'.
				(!$user_info['is_guest'] && !isOwner($head['userid'])
				?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'].getOwnerLink($head['userid']).$ContLink.';track"><b>'. $txt['PmxBlog_track'] .'</b></a>&nbsp;&nbsp;'. $txt['PmxBlog_track_val'][$head['tracking']]
				:	$txt['PmxBlog_tracked_user'].$head['tracks']
				).'
				</div>
				<div class="smalltext" style="padding:2px 0px; text-align:right; height:12px;">'.
				(isset($head['is_new_cont']) && $head['is_new_cont'] != '' || isset($head['is_new_cmnt']) && $head['is_new_cmnt'] > 0
				?	'<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'].getOwnerLink($head['userid']).$ContLink.';mkrd"><b>'. $txt['PmxBlog_markread'] .'</b></a>&nbsp;'
				:	''
				).'
				</div>
				<div style="padding:2px 2px 2px 2px; text-align:right; height:16px;">'.
				($head['bloglocked'] != 0
				?	$txt['PmxBlog_bloglockedtxt']
				:	($head['blogenabled'] == 0
					?	$txt['PmxBlog_blogdisabled']
					:	($allowMan || $isModerator
						?	($context['PmxBlog']['action'][0] != 'contnew' && $context['PmxBlog']['action'][0] != 'contedit'
							?	(!$isModerator
								? '<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. ($context['PmxBlog']['mode'] == 'list' ? 'view' : $context['PmxBlog']['mode']) .';cont=new'.getOwnerLink($head['userid']).'">'.$txt['PmxBlog_newblog'].'</a></div>'
								: ''
								)
							:	'<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$context['PmxBlog']['pageopt'].$context['PmxBlog']['UserLink'].'" onclick="return confirm(\''. $txt['PmxBlog_confirmAbort'] .'\')">'. ($context['PmxBlog']['action'][0] == 'contnew' ? $txt['PmxBlog_newblogabort'] : $txt['PmxBlog_editblogabort']) .'</a></div>'
							)
						:	''
						)
					)
				).'</div>'.
			'</div>
			</td>'
		: ''
		).'

		</tr><tr>
			<td valign="top" width="28%" style="padding:2px 2px 1px 2px;">
				<div class="smalltext">'. $txt['PmxBlog_blogowner'] .'</div>
				<div style="padding-top:2px;"><a href="'.$scripturl.'?action=profile;u='.$head['userid'].'"><b'.$head['onlineColor'].'>'. $head['username'] .'</b>
				</a> '. $head['gender'] .'</div>
			</td>
			<td valign="top" width="'.($user_info['is_guest'] ? '65' : '47').'%" style="padding:2px 4px 1px 0px;">
				<div class="smalltext" style="padding:2px;height:14px;float:left;">'.
				$txt['PmxBlog_entries'].
				(isset($head['nbr_content'])
				?	(empty($head['is_new_cont'])
						?	Content_button('content', $head['nbr_content'])
						:	Content_button('content_new', $head['nbr_content'])
					)
				:	Content_button('content', '0')
				).
				(!empty($head['nbr_comment'])
				?	' / '.$head['nbr_comment']
				:	''
				).'</div>
				<div class="smalltext" style="float:right;padding:2px;height:14px;">'.$txt['PmxBlog_blog_views'].$head['blogviews'].'</div>
				<br style="clear:both;" />
				<div class="smalltext" style="padding:1px 2px;height:16px;float:left;">'.
				($head['blogcreated'] != 0
				?
					($head['blogenabled'] != 0 || AllowedTo('admin_forum') || isOwner($head['userid'])
					?	($context['PmxBlog']['action'][0] == 'list'
						?	((!empty($head['nbr_content']) || (empty($head['nbr_content']) && isOwner($head['userid']) && !empty($head['hiddencont'])))
							?	'<a href="'. $scripturl .'?action=pmxblog;sa=view'. getOwnerLink($head['userid']) .'">'. $txt['PmxBlog_viewblog'] .'</a>'
							:	(!empty($head['hiddencont']) ? '<em>'. $txt['PmxBlog_privatecontent'] .'</em>' : '')
							)
						:	(!empty($context['PmxBlog']['ResetArchivdate']) || $context['PmxBlog']['pagemode'] != '' || $context['PmxBlog']['action'][0] == 'singlepage'
							?	'<div class="plainbox funcexit2"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'].getOwnerLink($head['userid']) . $context['PmxBlog']['ResetArchivdate'] .'"><span style="margin-right:15px;">'. $txt['PmxBlog_listblogs'] .'</span></a></div>'
							:	'<div class="plainbox funcexit2"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=list'. $context['PmxBlog']['ResetArchivdate'] .'"><span style="margin-right:15px;">'.$txt['PmxBlog_showall'].'</span></a></div>'
							)
						)
					:	''
					)
				: ''
				).'
				</div>
				<div class="smalltext" style="float:right;padding:2px;height:14px;">'.
				$txt['PmxBlog_blog_rating'].$head['blogvotes'].
				($head['blogvotes'] > 0
				?	' ('.$head['blograting'].'%)'
				:	''
				).'
				</div>
				<br style="clear:both;" />
			</td>'.
		(!$user_info['is_guest']
		? '
			<td valign="top" align="right" nowrap="nowrap">
			<div  style="width:170px;">'.
			($allowMan || AllowedTo('admin_forum')
			?	($context['PmxBlog']['mode'] != 'manager'
				?	'<div style="text-align:right; height:16px; padding:0 2px 2px 2px;">
						<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=manager;setup'. $cameFrom . getOwnerLink($head['userid']).$ContLink.'">'. $txt['PmxBlog_funclaunch'].$txt['PmxBlog_blogset_link'] .'</a></div>
					</div>
					<div style="text-align:right; height:16px; padding:2px 2px 2px 2px;">'.
						(isOwner($head['userid'])
						?	'<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=manager'.getOwnerLink($head['userid']).$ContLink.'">'. $txt['PmxBlog_funclaunch'] .$txt['PmxBlog_manager_link'].'</a></div>'
						:	($isModerator && !empty($isContent)
							? ($context['PmxBlog']['Moderate'] == $head['userid']
								? '<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .getOwnerLink($head['userid']).$ContLink.';mod=0">'. $txt['PmxBlog_funcexit'] .$txt['PmxBlog_moderate_link'].'</a></div>'
								: '<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .getOwnerLink($head['userid']).$ContLink.';mod=1">'. $txt['PmxBlog_funclaunch'] .$txt['PmxBlog_moderate_link'].'</a></div>'
								)
							: ''
							)
						).'
					</div>'
				:	'<div style="padding:0 2px 2px 2px; text-align:right; height:16px;">'.
					($head['blogcreated'] != 0
					?	($context['PmxBlog']['action'][0] != 'setup'
						?	'<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=manager;setup'. $cameFrom .getOwnerLink($head['userid']).$ContLink.'">'. $txt['PmxBlog_funclaunch'].$txt['PmxBlog_blogset_link'] .'</a></div>'
						:	($context['PmxBlog']['action'][0] == 'setup'
							?	'<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $cameFrom . getOwnerLink($head['userid']).$ContLink .'">'. $txt['PmxBlog_funcexit'] . $txt['PmxBlog_blogset_link'].'</a></div>'
							:	''
							)
						).'
					</div>
					<div style="padding:2px 2px 2px 2px; text-align:right; height:16px;">'.
						($context['PmxBlog']['action'][0] == 'setup'
						?	(isOwner($head['userid'])
							?	'<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=manager'.getOwnerLink($head['userid']).$ContLink.'">'. $txt['PmxBlog_funclaunch'].$txt['PmxBlog_manager_link'] .'</a></div>'
							:	''
							)
						:	'<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=view'.getOwnerLink($head['userid']).$ContLink.'">'. $txt['PmxBlog_funcexit'].$txt['PmxBlog_manager_link'].'</a></div>'
						)
					: (isOwner($head['userid'])
						?	'<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa=list">' .$txt['PmxBlog_funcexit'].$txt['PmxBlog_manager_link'].'</a></div>'
						: ''
						)
					).'
					</div>'
				)
			:	($isModerator && !empty($isContent)
				? '<div style="padding:2px 2px 2px 2px; padding-top:17px; text-align:right; height:14px;">'.
					($context['PmxBlog']['Moderate'] == $head['userid']
					? '<div class="plainbox funcexit"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .getOwnerLink($head['userid']).$ContLink.';mod=0">'. $txt['PmxBlog_funcexit'] .$txt['PmxBlog_moderate_link'].'</a></div>'
					: '<div class="plainbox funclaunch"><a class="blogbutton" href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'] .getOwnerLink($head['userid']).$ContLink.';mod=1">'. $txt['PmxBlog_funclaunch'] .$txt['PmxBlog_moderate_link'].'</a></div>'
					).
					'</div>'
				: ''
				)
			).'
			</div>
			</td>'
		:	''
		).'
		</tr>
	</table>
	</div>
	<span class="lowerframe"><span></span></span>';
}

// the Sidebar (calendar, archive, categories)
function Pmx_SideBar()
{
	global $options, $context, $txt, $scripturl, $settings, $sourcedir;

	if(!empty($context['PmxBlog']['Manager']['showcalendar']) || !empty($context['PmxBlog']['Manager']['showarchive']) || !empty($context['PmxBlog']['Manager']['showcategories']))
	{
		echo '
		<td valign="top">
		<div id="upshrinkPmxBlogSideBar" style="margin-left:6px; width:170px;'. (empty($options['collapse_PmxBlogSideBar']) ? '' : ' display:none;') .'">';

		$margintop = '';
		$blogdate = getdate($context['PmxBlog']['Manager']['blogcreated']);
		$currTime = getdate(forum_time());
		if(!empty($context['PmxBlog']['Archivdate']))
		{
			$now = $context['PmxBlog']['Archivdate'];
			$today = 0;
		}
		else
		{
			$now = getdate(forum_time());
			$today = $now['mday'];
		}

		// The Calendar
		if(isset($context['PmxBlog']['Manager']['showcalendar']) && $context['PmxBlog']['Manager']['showcalendar'] == 1)
		{
			include_once($sourcedir .'/Subs-Calendar.php');
			$calOpt = array(
				'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
				'show_week_num' => 1,
				'short_day_titles' => 1,
				'show_holidays' => 0,
				'show_events' => 0,
				'show_birthdays' => 0,
			);
			$calData = getCalendarGrid($now['mon'], $now['year'], $calOpt);
			$title = $txt['months'][(int) $now['mon']] .' '. $now['year'];

			$calendar = '
				<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%">
					<thead>
						<tr class="catbg">
							<th class="first_th" scope="col" width="97%">
								<div style="text-align:center;">';

			if(PmxCompareDate($blogdate, $now, array('year', 'mon')) == -1)
			{
				if($now['mon'] > 1)
					$calaction = ';arch='. mktime(0, 1, 0, ($now['mon'] -1), $now['mday'], $now['year']);
				else
					$calaction = ';arch='. mktime(0, 1, 0, 12, $now['mday'], ($now['year'] -1));
				$calendar .= '
						<span style="margin-right:4px;"><a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_prevmon'] .'">&laquo;</a></span>';
			}

			if(PmxCompareDate($currTime, $now, array('seconds', 'minutes', 'year', 'mon', 'mday')) != 0)
			{
				$calaction = ';arch=0';
				$calendar .= '
						<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_resetdate'] .'">'.$title.'</a>';
			}
			else
				$calendar .= $title;

			if(PmxCompareDate($now, $currTime, array('year', 'mon')) == -1)
			{
				if($now['mon'] < 12)
					$calaction = ';arch='. mktime(0, 1, 0, ($now['mon'] +1), $now['mday'], $now['year']);
				else
					$calaction = ';arch='. mktime(0, 1, 0, 1, $now['mday'], ($now['year'] +1));

				$calendar .= '
						<span style="margin-right:4px;"><a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_nextmon'] .'">&raquo;</a></span>';
			}

			$calendar .= '
								</div>
							</th>
							<th class="last_th" scope="col" width="3%"></th>
						</tr>
					</thead>
					<tr><td colspan="2" class="pmxblog_empty"></td></tr></table>

					<div class="plainbox pmxblog_border">
					<table class="windowbg2" width="100%" align="center" border="0" cellspacing="4" cellpadding="0">
						<tr>';

			foreach($calData['week_days'] as $day)
				$calendar .= '
							<td class="smalltext" align="center">'. substr($txt['days'][$day], 0, 2) .'</td>';

			$calendar .= '
						</tr>';

			foreach($calData['weeks'] as $week)
			{
				$calendar .= '
						<tr>';

				$dayspan = 0;
				foreach($week['days'] as $days)
				{
					if(empty($days['day']))
						$dayspan++;
					else
					{
						if(!empty($dayspan))
						{
							$calendar .= '
							<td class="smalltext" colspan="'. $dayspan .'"></td>';
							$dayspan = 0;
						}
						$calendar .= '
							<td align="right" valign="middle" class="smalltext';

						if($days['day'] == $today)
							$calendar .= ' plainbox" style="padding:0 2px 2px 0;">';
						else
							$calendar .= '" style="padding:0px 2px;">';

						if(!empty($context['PmxBlog']['cal'][$now['year']][$now['mon']][$days['day']]))
						{
							$calaction = ';arch='. mktime(0, 0, 1, $now['mon'], $days['day'], $now['year']);
							$calendar .= '
								<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'"><u><b>'. $days['day'] .'</b></u></a>
							</td>';
						}
						else
							$calendar .= $days['day'] .'
							</td>';
					}
				}

				$calendar .= '
						</tr>';
			}
			$calendar .= '
					</table>
				</div>';

			echo $calendar;
			$margintop = 'margin-top:5px; ';
		}

		// The Archive
		if(isset($context['PmxBlog']['Manager']['showarchive']) && $context['PmxBlog']['Manager']['showarchive'] == 1)
		{
			echo '
				<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="'.$margintop.'">
					<thead>
						<tr class="catbg">
							<th class="first_th" scope="col" width="97%"><div style="text-align:center;">';

			if(PmxCompareDate($blogdate, $now, array('year')) == -1)
			{
				$calaction = ';arch='. mktime(0, 0, 0, $now['mon'], 1, ($now['year'] -1));
				if($calaction < $context['PmxBlog']['Manager']['blogcreated'])
					$calaction = ';arch='. mktime(0, 0, 0, $blogdate['mon'], $blogdate['mday'], $blogdate['year']);
				echo '
					<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_prevyear'] .'">&laquo;</a>';
			}

			echo '
					<span style="margin:0px 4px;">';

			if(PmxCompareDate($currTime, $now, array('seconds', 'minutes', 'year', 'mon', 'mday')) != 0)
			{
				$calaction = ';arch=0';
				echo '
						<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_resetdate'] .'">'. $txt['PmxBlog_archive'] .' '. $now['year'] .'</a>';
			}
			else
				echo $txt['PmxBlog_archive'] .' '. $now['year'];

			echo '
					</span>';

			if(PmxCompareDate($currTime, $now, array('year')) == 1)
			{
				$calaction = mktime(0, 0, 0, $now['mon'], 1, ($now['year'] +1));
				if(PmxCompareDate(getdate($calaction), $currTime, array('year')) == -1)
					$calaction = mktime(0, 0, 0, $currTime['mon'], 1, $currTime['year']);
				echo '
					<a href="'. $scripturl .'?action=pmxblog;sa='.$context['PmxBlog']['mode'] .';arch='.$calaction.$context['PmxBlog']['UserLink'].'" title="'. $txt['PmxBlog_blogview_nextyear'] .'">&raquo;</a>';
			}

			echo '
								</div>
							</th>
							<th class="last_th" scope="col" width="3%"></th>
						</tr>
					</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr></table>
				<div class="plainbox pmxblog_border">
				<div class="windowbg2" style="padding:5px 5px;">
					<span class="smalltext">';

			for ($m=1; $m <= 12; $m++)
			{
				if(isset($context['PmxBlog']['arch'][$now['year']][$m]))
				{
					$calaction = ';arch='. mktime(0, 1, 0, $m, 1, $now['year']);
					echo '
						<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode'].$calaction.$context['PmxBlog']['UserLink'].'"><b>'. $txt['months'][$m] .'</b></a>';
				}
				else
					echo $txt['months'][$m];

				echo '<br />';
			}

			echo '
					</span>
				</div>
			</div>';
			$margintop = 'margin-top:5px; ';
		}

		// Categorie List
		if(isset($context['PmxBlog']['Manager']['showcategories']) && $context['PmxBlog']['Manager']['showcategories'] == 1 && !empty($context['PmxBlog']['categorie']))
		{
			echo '
				<table class="table_grid pmxblog_th" cellspacing="0" cellpadding="0" border="0" width="100%" style="'.$margintop.'">
					<thead>
						<tr class="catbg">
							<th class="first_th" scope="col" width="97%"><div style="text-align:center;">'. $txt['PmxBlog_categorie_title'] .'</div></th>
							<th class="last_th" scope="col" width="3%"></th>
						</tr>
					</thead>
				<tr><td colspan="2" class="pmxblog_empty"></td></tr></table>';

			if(!empty($context['PmxBlog']['categorie']))
			{
				echo '
				<div class="plainbox pmxblog_border">
					<div class="windowbg2" style="padding:5px 5px;">';
				foreach($context['PmxBlog']['categorie'] as $fbcat)
				{
					$d = str_pad('', $fbcat['depth']*2, '.');
					echo '
						<div class="smalltext">'.
							(!empty($fbcat['ContCat']) ? '<a href="'. $scripturl .'?action=pmxblog;sa='. $context['PmxBlog']['mode']. ';ca='. $fbcat['id'] .$context['PmxBlog']['UserLink'].'"><b>'.$d.$fbcat['name'] .'</b></a>' : $d.$fbcat['name']) .
						'</div>';
				}
				echo '
					</div>';
			}
			echo '
			</div>';
		}
		echo '
	</div>
	</td>';
	}
}
?>