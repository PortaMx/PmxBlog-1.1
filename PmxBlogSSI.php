<?php
// ----------------------------------------------------------
// -- PmxBlogSSI.php                                       --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

// Don't do anything if PMXBlog is already loaded.
if(defined('PMXBlog'))
	return true;

if(!defined('SMF'))
	require_once('SSI.php');

// Call a function passed by GET.
if (isset($_GET['PmxBlogSSI']) && function_exists('PmxBlogSSI_' . $_GET['PmxBlogSSI']))
{
	call_user_func('PmxBlogSSI_' . $_GET['PmxBlogSSI']);
	exit;
}

/**
* PmxBlogSSI_Recent()
* [?] = default input values
* Output the result or returns the result array
	result_array(
		'conthref' =>  the subject (title and news image) with link to the content/comment
		'conturl' => same as cont href but only the url without news image
		'subject' => the subject (title)
		'ownerhref' => Blog owner with link to the profile
		'cont_date' =>	content date (unformated)
		'cmnt_date' =>	comment date (unformated) 
		'unread_cont' =>	true if the content unread
		'unread_cmnt' =>	true if have the content unread comments
	)
**/
function PmxBlogSSI_Recent()
{
	global $context, $smcFunc, $settings, $modSettings, $scripturl, $user_info, $txt;

	$args = func_get_args();
	$arg_values = PmxBlog_getSSIArguments($args, array('$recent_len', 'cmntdate_onnews', 'sort_newsup', 'output'), array(5, 1, 1, 'echo'));
	list($recent_len, $cmntdate_onnews, $sort_newsup, $output) = $arg_values;

	$result = array();
	if(empty($modSettings['pmxblog_enabled']))
		return $result;

	// get latest #n recent entries
	if(AllowedToBlog('view') && $recent_len > 0)
	{
		// uhhh .. what a monstrous query ;-)
		$req = $smcFunc['db_query']('', '
				SELECT c.ID, c.owner, c.subject, c.allow_view, IFNULL(l.is_read, 0) AS is_read, m.real_name, m.buddy_list, 
					c.date_created as cont_date, IFNULL(MAX(cm.date_created), MAX(c.date_created)) AS cmnt_date,
					IFNULL(MAX(lc.cmtID), IFNULL(cm.ID, 0)) AS cmntID,
					IF(IFNULL(MAX(lc.cmtID), 0) < IFNULL(MAX(cm.ID), 0), 1, 0) AS NewCmnt,'. 
					(!empty($sort_newsup) 
					? 'IF(IFNULL(MAX(lc.cmtID), 0) < IFNULL(MAX(cm.ID), 0), MAX(cm.date_created), c.date_created) AS sortDate' 
					: 'c.date_created AS sortDate'
					) .'
				FROM {db_prefix}pmxblog_content AS c
				LEFT JOIN {db_prefix}members AS m ON (c.owner = m.id_member)
				LEFT JOIN {db_prefix}pmxblog_cont_log AS l ON (l.owner = c.owner AND l.userID = {int:idmem})
				LEFT JOIN {db_prefix}pmxblog_comments AS cm ON (c.ID = cm.contID)
				LEFT JOIN {db_prefix}pmxblog_manager AS a ON (c.owner = a.owner)
				LEFT JOIN {db_prefix}pmxblog_cmnt_log AS lc ON (c.ID = lc.contID AND lc.userID = {int:idmem})
				WHERE a.blogenabled > 0 AND a.bloglocked = 0 AND c.published = 1
					AND (c.allow_view = 0
					OR (c.allow_view = 1 AND {int:idmem} > 0)
					OR (c.allow_view = 2 AND {string:s_idmem} IN (m.buddy_list))
					OR (c.allow_view = 3 AND c.owner = {int:idmem})
					OR (c.owner = {int:idmem}))
				GROUP BY c.ID
				ORDER BY sortDate DESC
				LIMIT {int:lim}',
			array(
				's_idmem' => (string) $context['user']['id'],
				'idmem' => $context['user']['id'],
				'lim' => $recent_len,
			)
		);

		if($smcFunc['db_num_rows']($req) > 0)
		{
			while($row = $smcFunc['db_fetch_assoc']($req))
			{
				$NewCont = !$user_info['is_guest'] && !Is_Read($row['owner'], $row['is_read'], $row['ID']);
				$NewCmnt = !$user_info['is_guest'] && !empty($row['NewCmnt']);

				$result[] = array(
					'conthref' =>	(empty($NewCont) && !empty($row['NewCmnt'])
									?	'<a href="'.$scripturl.'?action=pmxblog;sa=view;cont='.$row['ID'].';cmnt='.$row['cmntID'].';uid='.$row['owner'].'#new">'
									:	'<a href="'.$scripturl.'?action=pmxblog;sa=view;cont='.$row['ID'].';uid='.$row['owner'].'#top">'
									).
									(empty($row['subject'])
									?	'???'
									:	($context['PmxBlog']['censor_text'] == 1
										? censorText(stripslashes($row['subject']))
										: stripslashes($row['subject'])
										)
									).
									($NewCont
									?	'<img src="'.$settings['default_images_url'].'/PmxBlog/content_new.gif" alt="" style="margin-bottom:-2px; padding-left:5px;" />'
									:	''
									).
									($NewCmnt
									?	'<img src="'.$settings['default_images_url'].'/PmxBlog/comment_new.gif" alt="" style="margin-bottom:-2px; padding-left:5px;" />'
									:	''
									).
									'</a>',
					'conturl' => (empty($NewCont) && !empty($NewCmnt)
									?	$scripturl.'?action=pmxblog;sa=view;cont='.$row['ID'].';cmnt='.$row['cmntID'].';uid='.$row['owner'].'#new'
									:	$scripturl.'?action=pmxblog;sa=view;cont='.$row['ID'].';uid='.$row['owner'].'#top'
									),
					'subject' => (empty($row['subject'])
									?	'???'
									:	($context['PmxBlog']['censor_text'] == 1
										? censorText(stripslashes($row['subject']))
										: stripslashes($row['subject'])
										)
									),
					'ownerhref' => '<a href="'.$scripturl.'?action=profile;u='.$row['owner'].'">'.$row['real_name'].'</a>',
					'cont_date' => $row['cont_date'],
					'cmnt_date' => $row['cmnt_date'],
					'unread_cont' => $NewCont,
					'unread_cmnt' => $NewCmnt,
				);
			}
		}
		$smcFunc['db_free_result']($req);

		if($output != 'echo')
			return $result;

		echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td width="2%" valign="top">
					<img src="', $settings['default_images_url'] .'/PmxBlog/blogstats.gif" alt="" style="padding-right:5px;" />
				</td>
				<td>
				'. $context['PmxBlog']['total_blogs'] . ($context['PmxBlog']['total_blogs'] == 1 ? $txt['PmxBlog_stats-11'] : $txt['PmxBlog_stats-1']) . $context['PmxBlog']['total_entries'] . ($context['PmxBlog']['total_entries'] == 1 ? $txt['PmxBlog_stats-21'] : $txt['PmxBlog_stats-2']);

			if(!empty($result))
			{
				echo '
					<br />'.$txt['PmxBlog_recent'].'
				</td>
			</tr>
			<tr>
				<td colspan="2">';
				foreach($result as $rcb)
				{
					echo '
					<hr />
					<div style="vertical-align: top;">'. $rcb['conthref'] .'</div>
					<div style="vertical-align:top;">'.$txt['by'].' '.$rcb['ownerhref'].'</div>
					<div style="vertical-align:top;">[';

					if(!empty($rcb['unread_cmnt']) && !empty($cmntdate_onnews))
						echo timeformat($rcb['cmnt_date'], true);
					else
						echo timeformat($rcb['cont_date'], true);

					echo ']</div>';
				}
			}
			echo '
				</td>
			</tr>
		</table>';
	}
}

/**
* PmxBlogSSI_FindArticles($owner[0], $mindate[0], $maxdate[0], $categorie[0], $resultlen[0])
* [?] = default input values
* owner: integer ownerID or ownerName
* mindate: integer unixdate OR string 'mon.day.year' (0 not used)
*				string date: mon{1-12}, day{1-31}, year{4 digit})
* maxdate: integer unixdate OR string 'mon.day.year' (0 not used)
*				string date: mon{1-12}, day{1-31}, year{4 digit})
* categorie: id or name (0 not used)
* resultlen: integer max articles to return (0 = all)
* output: 'echo' or empty
*	result_array[0..n](
*		'id' =>  article ID
*		'subject' => article subject (title)
*	)
**/
function PmxBlogSSI_FindArticles()
{
	global $context, $smcFunc, $modSettings;

	$args = func_get_args();
	$arg_values = PmxBlog_getSSIArguments($args, array('owner', 'mindate', 'maxdate', 'categorie', 'maxlen', 'output'), array(0, '', '', 0, 0, 'echo'));
	list($owner, $mindate, $maxdate, $categorie, $resultlen, $output) = $arg_values;

	$result = array();

	if(empty($modSettings['pmxblog_enabled']) || empty($owner) || !AllowedToBlog('view'))
		return $result;

	if(!is_numeric($owner))
	{
		$req = $smcFunc['db_query']('', '
			SELECT id_member FROM {db_prefix}members
			WHERE member_name = {string:name}',
			array('name' => $owner)
		);
		if($row = $smcFunc['db_fetch_assoc']($req))
		{
			$owner = $row['id_member'];
			$smcFunc['db_free_result']($req);
		}
	}

	// owner have legal value?
	$owner = (int) $owner;
	if(empty($owner))
		return $result;

	$resultlen = (int) $resultlen;
	$andRQ = '';

	// mindate set?
	if(!empty($mindate))
	{
		if(!is_numeric($mindate))
		{
			list($mon, $day, $year) = explode('.', $mindate);
			$mindate = mktime(0, 0, 0, $mon, $day, $year);
		}
		else
			$mindate = (int) $mindate;

		$andRQ .= (!empty($mindate) ? ' AND c.date_created > {int:refmin}' : '');
	}

	// maxdate set?
	if(!empty($maxdate))
	{
		if(!is_numeric($maxdate))
		{
			list($mon, $day, $year) = explode('.', $maxdate);
			$maxdate = mktime(23, 59, 59, $mon, $day, $year);
		}
		else
			$maxdate = (int) $maxdate;

		$andRQ .= (!empty($maxdate) ? ' AND c.date_created < {int:refmax}' : '');
	}

	// categorie set?
	if(!empty($categorie))
	{
		if(!is_numeric($categorie))
		{
			$req = $smcFunc['db_query']('', '
				SELECT ID FROM {db_prefix}pmxblog_categories
				WHERE name = {string:name} AND owner = {int:owner}',
				array(
					'name' => $categorie,
					'owner' => $owner
				)
			);
			if($row = $smcFunc['db_fetch_assoc']($req))
			{
				$categorie = $row['ID'];
				$smcFunc['db_free_result']($req);
			}
		}

		$categorie = (int) $categorie;
		if(!empty($categorie))
			$andRQ .= ' AND c.categorie = {int:catid}';
	}

	// limit set?
	$Limit = !empty($resultlen) ? 'LIMIT {int:lim}' : '';

	// find all article ID's
	$req = $smcFunc['db_query']('', '
			SELECT c.ID, c.subject
			FROM {db_prefix}pmxblog_manager AS a
			LEFT JOIN {db_prefix}pmxblog_content AS c ON (a.owner = c.owner)
			LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
			WHERE a.owner = {int:owner} AND a.blogenabled > 0 AND a.bloglocked = 0 AND c.published = 1
				AND (c.allow_view = 0
				OR (c.allow_view = 1 AND {int:userid} > 0)
				OR (c.allow_view = 2 AND {string:str_userid} IN (m.buddy_list))
				OR (c.allow_view = 3 AND c.owner = {int:userid})
				OR (c.owner = {int:userid}))'. $andRQ .'
				ORDER BY c.date_created DESC
			'. $Limit .'',
		array(
			'str_userid' => (string) $context['user']['id'],
			'userid' => $context['user']['id'],
			'owner' => $owner,
			'refmin' => $mindate,
			'refmax' => $maxdate,
			'catid' => $categorie,
			'lim' => $resultlen
		)
	);

	if($smcFunc['db_num_rows']($req) > 0)
	{
		$i = 0;
		while($row = $smcFunc['db_fetch_assoc']($req))
		{
			$result[$i] = array('id' => $row['ID'], 'subject' => $row['subject']);
			$i++;
		}
		$smcFunc['db_free_result']($req);
	}

	if($output != 'echo')
		return $result;

	echo '
		Array(<br />';
	foreach($result as $key => $value)
		echo '&nbsp;&nbsp;['. $key .'] =&gt; Array([id] =&gt; '. $value['id'] .', [subject] =&gt; "'. $value['subject'] .'")<br  />';
	echo ')';
}

/**
* PmxBlogSSI_GetArticle($artid[0])
* artid: integer article ID
* output: 'echo' or empty
* Returns all article data in the result array
**/
function PmxBlogSSI_GetArticle()
{
	global $smcFunc, $modSettings, $context, $txt;

	$args = func_get_args();
	$arg_values = PmxBlog_getSSIArguments($args, array('artid', 'output'), array(0, 'echo'));
	list($artid, $output ) = $arg_values;

	$result = array();

	if(empty($modSettings['pmxblog_enabled']))
		return $result;

	// get all article data
	$artid = (int)  $artid;

	$req = $smcFunc['db_query']('', '
			SELECT c.*, ca.name, m.real_name
			FROM {db_prefix}pmxblog_content AS c
			LEFT JOIN {db_prefix}pmxblog_manager AS a ON (a.owner = c.owner)
			LEFT JOIN {db_prefix}members AS m ON (c.owner = m.id_member)
			LEFT JOIN {db_prefix}pmxblog_categories AS ca ON (c.categorie = ca.ID OR c.categorie = 0)
			WHERE a.blogenabled > 0 AND a.bloglocked = 0 AND c.published = 1 AND c.ID = {int:artid}
			AND (c.allow_view = 0
			OR (c.allow_view = 1 AND {int:idmem} > 0)
			OR (c.allow_view = 2 AND {string:s_idmem} IN (m.buddy_list))
			OR (c.allow_view = 3 AND c.owner = {int:idmem})
			OR (c.owner = {int:idmem}))',
		array(
			'artid' => $artid,
			's_idmem' => (string) $context['user']['id'],
			'idmem' => $context['user']['id']
		)
	);

	if($row = $smcFunc['db_fetch_assoc']($req))
	{
		$result = array(
			'id' => $row['ID'],
			'ownerid' => $row['owner'],
			'ownername' => $row['real_name'],
			'catid' => $row['categorie'],
			'catname' => empty($row['categorie']) ? $txt['PmxBlog_nocat'] : $row['name'],
			'nbr_comment' => $row['nbr_comment'],
			'allowcomment' => $row['allowcomment'], 
			'allow_view' => $row['allow_view'], 
			'date_created' => $row['date_created'],
			'views' => $row['views'], 
			'subject' => $row['subject'], 
			'body' => $row['body']
		);
		$smcFunc['db_free_result']($req);
	}

	if($output != 'echo')
		return $result;

	if(!empty($result))
		PmxBlogArticles_SSI_Out($result, array(), $artid);
	else
		echo '
		'. $txt['PmxBlog_nothing_read'];
}

/**
* PmxBlogSSI_GetLastArticle($owner[0])
* owner: integer ownerID or ownerName
* output: 'echo' or empty
* Returns all article data in the result array
**/
function PmxBlogSSI_GetLastArticle()
{
	global $smcFunc, $context, $modSettings;

	$args = func_get_args();
	$arg_values = PmxBlog_getSSIArguments($args, array('owner', 'output'), array(0, 'echo'));
	list($owner, $output ) = $arg_values;

	$result = array();
	if(empty($modSettings['pmxblog_enabled']))
		return $result;

	if(!is_numeric($owner))
	{
		$req = $smcFunc['db_query']('', '
			SELECT id_member FROM {db_prefix}members
			WHERE member_name = {string:name}',
			array('name' => $owner)
		);
		if($row = $smcFunc['db_fetch_assoc']($req))
		{
			$owner = $row['id_member'];
			$smcFunc['db_free_result']($req);
		}
	}
	$owner = (int) $owner;
	
	// find last article
	$artid = 0;
	$req = $smcFunc['db_query']('', '
			SELECT MAX(ID) AS artid
			FROM {db_prefix}pmxblog_content AS c
			LEFT JOIN {db_prefix}pmxblog_manager AS a ON (a.owner = c.owner)
			LEFT JOIN {db_prefix}members AS m ON (c.owner = m.id_member)
			WHERE a.blogenabled > 0 AND a.bloglocked = 0 AND c.published = 1
			'. (!empty($owner) ? 'AND a.owner = {int:owner}' : '') .'
			AND (c.allow_view = 0
			OR (c.allow_view = 1 AND {int:idmem} > 0)
			OR (c.allow_view = 2 AND {string:s_idmem} IN (m.buddy_list))
			OR (c.allow_view = 3 AND c.owner = {int:idmem})
			OR (c.owner = {int:idmem}))',
		array(
			'owner' => $owner,
			's_idmem' => (string) $context['user']['id'],
			'idmem' => $context['user']['id']
		)
	);
	if($row = $smcFunc['db_fetch_assoc']($req))
	{
		$artid = $row['artid'];
		$smcFunc['db_free_result']($req);
	}

	PmxBlogSSI_GetArticle($artid, $output);
}

/**
* PmxBlogSSI_ShowArticles()
* owner: integer ownerID or ownerName
* mindate: integer unixdate OR string 'mon.day.year' (0 not used)
*				string date: mon{1-12}, day{1-31}, year{4 digit})
* maxdate: integer unixdate OR string 'mon.day.year' (0 not used)
*				string date: mon{1-12}, day{1-31}, year{4 digit})
* categorie: id or name (0 not used)
* maxlen: integer max articles to show (0 = all)
* output: 'echo' or empty
**/
function PmxBlogSSI_ShowArticles()
{
	global $context, $scripturl, $txt;

	$args = func_get_args();
	$arg_values = PmxBlog_getSSIArguments($args, array('owner', 'mindate', 'maxdate', 'categorie', 'maxlen', 'output'), array(0, '', '', 0, 0, 'echo'));
	list($owner, $mindate, $maxdate, $categorie, $maxlen, $output) = $arg_values;

	// find all articles in given context
	$result = PmxBlogSSI_FindArticles($owner, $mindate, $maxdate, $categorie, $maxlen, '');
	if(empty($result) && $output != 'echo')
		return $result;

	if($output != 'echo')
		return $result;

	// link requested?
	if(isset($_REQUEST['blogcont']))
		$artid = (int) $_REQUEST['blogcont'];
	elseif(!empty($result))
		$artid = $result[0]['id'];
	else
	{
		echo '
		'. $txt['PmxBlog_nothing_read'];
		return $result;
	}

	// get the selected article data
	$article = PmxBlogSSI_GetArticle($artid, '');

	PmxBlogArticles_SSI_Out($article, $result, $artid);
}

/**
* PmxBlogArticles_SSI_Out($article, $titlelist, $currentid)
* article: array(article data)
* titlelist: array( article ID, title)
* currentid: current articles ID
**/
function PmxBlogArticles_SSI_Out($article, $titlelist = array(), $currentid = 0)
{
	global $context, $scripturl, $sourcedir, $txt;

	include_once($sourcedir .'/PmxBlogTeaser.php');

	// output the aricle
	echo '
	<table width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout:fixed">
		<tr>
			<td valign="top" align="left" width="'. (count($titlelist) > 1 ? '80' : '100') .'%">
				<div class="smalltext" style="float:left;'.(count($titlelist) > 1 ? 'margin-right:4px;' : '') .'">
					'. $txt['PmxBlog_blogart'] .'<b><a href="'. $scripturl .'?action=pmxblog;sa=view;cont='. $currentid .';uid='. $article['ownerid'] .'">'. $article['subject'] .'</a></b> '. $txt['by'] .' <a href="'. $scripturl .'?action=profile;u='. $article['ownerid'] .'">'. $article['ownername'] .'</a><br />
					'. $txt['PmxBlog_created'] . timeformat($article['date_created'], true) .'
				</div>
				<div class="smalltext" style="float:right; text-align:right;'.(count($titlelist) > 1 ? 'margin-right:4px;' : '') .'">
					'. $txt['PmxBlog_selcat'] . $article['catname'] .'<br />
					'. $txt['PmxBlog_comments'] . $article['nbr_comment'] .', '. $txt['PmxBlog_views'] . $article['views'] .'
				</div>
				<div style="clear:both;"></div>';
	if(count($titlelist) > 1)
		echo '
				<div style="margin:'. ($context['browser']['is_ie'] ? ($context['browser']['is_ie8'] ? '5px' : '4px') : '0') .' 4px 0 0;"><hr /></div>
				<div style="margin:0 4px 0 0; padding:0 0 4px 0; overflow:auto;">';
	else
		echo '
				<div style="margin:'. ($context['browser']['is_ie'] ? ($context['browser']['is_ie8'] ? '5px' : '4px') : '0') .' 0;"><hr /></div>
				<div style="margin:0; padding:0 0 4px 0; overflow:auto;">';

	echo '
					<div>'. PmxBlogTeaser($article['body']) .'</div>
				</div>
			</td>';

	// more articles in same context?
	if(count($titlelist) > 1)
	{
		// create the article links
		if(!empty($_REQUEST['PmxBlogSSI']))
		{
			$artlink = explode('&blogcont', $_SERVER['QUERY_STRING']);
      $sep = explode('?', $_SERVER['REQUEST_URL']);
			$artlink = $sep[0] .'?'. $artlink[0] .'&blogcont=';
		}
		else
		{
      $artlink = '';
			foreach($_REQUEST as $key => $part)
				if(!empty($part) && $part{0} == '@')		// Show on a PortaMx Singlepage?
					$artlink = substr($part, 1) .';';
			$artlink = $_SERVER['PHP_SELF'] . '?'. $artlink .'blogcont=';
		}

		echo '
			<td width="0" valign="top" class="tborder" style="border-width:0 0 0 1px; width:1px; padding:0;"><div style="width:1px;"></div></td>
			<td valign="top" align="right" width="20%">
				<div class="smalltext" style="float:right; text-align:right; margin-left:4px;">
					<em>'. $txt['PmxBlog_moreincontext'] .'</em>
				</div>
				<div style="clear:both;"></div>
				<div style="margin:'. ($context['browser']['is_ie'] ? ($context['browser']['is_ie8'] ? '5px' : '4px') : '0') .' 0 0 4px;"><hr /></div>
				<div class="middletext" style="margin:0 0 0 4px; font-family: Tahoma; line-height: 130%;">';

		// show all titles with links
		foreach($titlelist as $data)
		{
			if($data['id'] == $currentid)
				echo '<b>'. $data['subject'] .'</b>';
			else
				echo '<a href="'. $artlink . $data['id'] .'">'. $data['subject'] .'</a>';
			echo '<br />';
		}

		echo '
				</div>
			</td>';
	}

	echo '
		</tr>
	</table>';
}

/**
* PmxBlog_getSSIArguments($keylist, $valuelist)
* args array()
* keylist: array()
* valuelist: array()
* return the arg array
**/
function PmxBlog_getSSIArguments($args, $keylist, $valuelist)
{
	if(empty($args))
	{
		foreach($keylist as $key => $value)
			if(!empty($_REQUEST[$value]))
				$valuelist[$key] = $_REQUEST[$value];
	}
	else
	{
		foreach($args as $key => $val)
			$valuelist[$key] = $val;
	}
	return $valuelist;
}
?>