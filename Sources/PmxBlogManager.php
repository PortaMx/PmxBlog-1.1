<?php
// ----------------------------------------------------------
// -- PmxBlogManager.php                                   --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (!defined('SMF'))
	die('Hacking attempt...');

// PmxBlog Manager
function PmxBlogManager($mode, $pagelist)
{
	global $context, $settings, $modSettings, $user_info, $scripturl, $sourcedir, $txt, $smcFunc;

	// check access
	isAllowedToBlog($mode);

	$uid = $context['PmxBlog']['UID'];
	if($mode == 'manager' && !isOwner($uid))
	{
		if(isset($_GET['setup']) && !AllowedTo('admin_forum'))
			NotAllowed();
	}

	// Get Data for selected user
	getUserData($uid);

	// User have a Blog ?
	if(!$context['user']['is_guest'] && !$context['PmxBlog']['Manager']['have_blog'])
		$mode = 'manager';
	else
		// Tracking change / Mark all read
		Check_Track_MarkRD($uid);

	$context['PmxBlog']['mode'] = $mode;
	$context['PmxBlog']['Manager']['andRQ'] = '';
	$context['PmxBlog']['Manager']['refmin'] = '';
	$context['PmxBlog']['Manager']['refmax'] = '';
  $context['PmxBlog']['Manager']['archStr'] = '';
	if(!empty($context['PmxBlog']['Archivdate']))
	{
		$now = getdate(forum_time());
		$now['seconds'] = 0;
		$now['minutes'] = 0;
		if(PmxCompareDate($now, $context['PmxBlog']['Archivdate'], array('seconds', 'minutes', 'year', 'mon', 'mday')) != 0)
		{
			// calaendar day ?
			if(PmxCompareDate($now, $context['PmxBlog']['Archivdate'], array('seconds')) != 0)
			{
				$context['PmxBlog']['Manager']['refmin'] = mktime(0, 0, 0, $context['PmxBlog']['Archivdate']['mon'], $context['PmxBlog']['Archivdate']['mday'], $context['PmxBlog']['Archivdate']['year']);
				$context['PmxBlog']['Manager']['refmax'] = mktime(23, 59, 59, $context['PmxBlog']['Archivdate']['mon'], $context['PmxBlog']['Archivdate']['mday'], $context['PmxBlog']['Archivdate']['year']);
				$context['PmxBlog']['Manager']['andRQ'] = ' AND c.date_created > {int:refmin} AND c.date_created < {int:refmax}';
				$context['PmxBlog']['Manager']['archStr'] = strftime('%d %B %Y', mktime(0, 0, 0, $context['PmxBlog']['Archivdate']['mon'], $context['PmxBlog']['Archivdate']['mday'], $context['PmxBlog']['Archivdate']['year']));
			}
			// calaendar month ?
			elseif(PmxCompareDate($now, $context['PmxBlog']['Archivdate'], array('minutes')) != 0)
			{
				$context['PmxBlog']['Manager']['refmin'] = mktime(0, 0, 0, $context['PmxBlog']['Archivdate']['mon'], 1, $context['PmxBlog']['Archivdate']['year']);
				$context['PmxBlog']['Manager']['refmax'] = mktime(0, 0, 0, ($context['PmxBlog']['Archivdate']['mon'] +1), 1, $context['PmxBlog']['Archivdate']['year']);
				$context['PmxBlog']['Manager']['andRQ'] = ' AND c.date_created > {int:refmin} AND c.date_created < {int:refmax}';
				$context['PmxBlog']['Manager']['archStr'] = strftime('%B %Y', mktime(0, 0, 0, $context['PmxBlog']['Archivdate']['mon'], $context['PmxBlog']['Archivdate']['mday'], $context['PmxBlog']['Archivdate']['year']));
			}
			// calaendar year ?
			elseif(PmxCompareDate($now, $context['PmxBlog']['Archivdate'], array('year')) != 0)
			{
				$context['PmxBlog']['Manager']['refmin'] = mktime(0, 0, 0, 1, 1, $context['PmxBlog']['Archivdate']['year']);
				$context['PmxBlog']['Manager']['refmax'] = mktime(23, 59, 59, 12, 31, ($context['PmxBlog']['Archivdate']['year']));
				$context['PmxBlog']['Manager']['andRQ'] = ' AND c.date_created > {int:refmin} AND c.date_created < {int:refmax}';
				$context['PmxBlog']['Manager']['archStr'] = $context['PmxBlog']['Archivdate']['year'];
			}
		}
	}

	// Get categories
	$context['PmxBlog']['categorie'] = array();
	$context['PmxBlog']['cal'] = array();
	$context['PmxBlog']['arch'] = array();

	$request = $smcFunc['db_query']('', '
			SELECT s.*, IF(c.published = 1 AND (c.allow_view = 0
				OR (c.allow_view = 1 AND {int:idmem} > 0)
				OR (c.allow_view = 2 AND {string:s_idmem} IN (m.buddy_list))
				OR (c.allow_view = 3 AND c.owner = {int:idmem})
				OR (c.owner = {int:idmem})), IF(c.categorie IS NULL, 0, 1), 0) AS ContCat, IFNULL(c.date_created, 0) as date_created
			FROM {db_prefix}pmxblog_categories AS s
			LEFT JOIN {db_prefix}pmxblog_content AS c ON (c.owner = s.owner AND c.categorie = s.ID)
			LEFT JOIN {db_prefix}members AS m ON (s.owner = m.id_member)
			WHERE s.owner = {int:owner} AND s.ID > 0
			ORDER BY s.corder',
		array(
			'owner' => $uid,
			'idmem' => $user_info['id'],
			's_idmem' => (string) $user_info['id'],
		)
	);

	if($smcFunc['db_num_rows']($request) > 0)
	{
		$catid = 0;
		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			if($catid != $row['ID'])
			{
				$catid = $row['ID'];
				$context['PmxBlog']['categorie'][] = array(
					'id' => $row['ID'],
					'name' => $row['name'],
					'corder' => $row['corder'],
					'depth' => $row['depth'],
					'ContCat' => $row['ContCat']
				);
			}
			if(!empty($row['date_created']))
			{
				$d = getdate($row['date_created']);
				$context['PmxBlog']['arch'][$d['year']][$d['mon']] = 1;
				$context['PmxBlog']['cal'][$d['year']][$d['mon']][$d['mday']] = 1;
			}
		}
		$smcFunc['db_free_result']($request);
	}

	// check Manager action
	if($mode == 'manager')
	{
		// Quickchange Content parts ?
		isAllowedToBlog($mode);

		if(isset($_GET['qchg']))
		{
			if(empty($_GET['qchg']))
				$id = $_GET['cont'];
			else
				$id = $_GET['qchg'];
			unset($_GET['qchg']);

			$published = 0;
			foreach ($_POST as $what => $value)
			{
				if($what == 'published' && !empty($value))
					$published = $value;

				if($what != 'rateval')
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}pmxblog_content
							SET {raw:colkey} = {int:colval}
							WHERE ID = {int:contid}',
						array('colkey' => $what, 'colval' => $value, 'contid' => $id)
					);
			}

			// if published, notify all user where track this blog
			if(!empty($published))
			{
				$request = $smcFunc['db_query']('', '
						SELECT subject, allow_view
						FROM {db_prefix}pmxblog_content
						WHERE ID = {int:contid}',
						array('contid' => $id)
					);
					$row = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					// clear the Blog totals cache
					cache_put_data('PmxBlogTotals', null, -1);

					SendTrackNotify($uid, $row['allow_view'], $id, stripslashes($row['subject']));
			}
      redirectexit(http_build_query($_GET, '', ';'));
		}

		if(isset($_GET['setup']))
			$context['PmxBlog']['action'] = array('setup', $_GET['setup']);		// Settings
		elseif(isset($_GET['cont']))
			$context['PmxBlog']['action'] = array('cont', $_GET['cont']);		// Content
		elseif(isset($_GET['cmnt']))
			$context['PmxBlog']['action'] = array('cmnt', $_GET['cmnt']);		// Comment
		elseif(!$context['PmxBlog']['Manager']['have_blog'])
			$context['PmxBlog']['action'] = array('setup', '');
	}
	else
	{
		isAllowedToBlog($mode);

		if(isset($_GET['cont']))
			$context['PmxBlog']['action'] = array('cont', $_GET['cont']);		// Content
		elseif(isset($_GET['cmnt']))
			$context['PmxBlog']['action'] = array('cmnt', $_GET['cmnt']);		// Comment
	}

	// Manage settings ?
	if($context['PmxBlog']['action'][0] == 'setup' && (isOwner($uid) || AllowedTo('admin_forum')))
	{
		isAllowedToBlog('manager');
		require_once($sourcedir.'/PmxBlogSettings.php');
		PmxBlogSettings($uid);
	}
	else
	{
		// isset rating ?
		if(isset($_GET['rating']) && $context['PmxBlog']['action'][0] == 'cont')
		{
			$cid = $context['PmxBlog']['action'][1];
			foreach ($_POST as $what => $value)
			{
				if($what == 'rateval')
				{
					$request = $smcFunc['db_query']('', '
						SELECT owner, rating, voter
							FROM {db_prefix}pmxblog_ratings
							WHERE contID = {int:contid}',
						array('contid' => $cid)
					);

					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_assoc']($request);
						$smcFunc['db_free_result']($request);

						$smcFunc['db_query']('', '
							UPDATE {db_prefix}pmxblog_ratings
							SET rating = {string:rating}, voter = {string:voter}
							WHERE contID = {int:contid}',
							array('rating' => $row['rating'] . $value .',',
								'voter' =>  $row['voter'] . $user_info['id'] .',',
								'contid' => $cid)
						);
					}
					else
					{
						$request = $smcFunc['db_query']('', '
							SELECT owner
								FROM {db_prefix}pmxblog_content
								WHERE ID = {int:contid}',
							array('contid' => $cid)
						);

						$row = $smcFunc['db_fetch_assoc']($request);
						$smcFunc['db_free_result']($request);

						$smcFunc['db_insert']('', '
							{db_prefix}pmxblog_ratings',
							array('owner' => 'int', 'contID' => 'int', 'rating' => 'string-255', 'voter' => 'string-255'),
							array($row['owner'], $cid, $value .',', $user_info['id'] .','),
							array('contID', 'owner')
						);
					}

					// update Blograting
					$allrates = '';
					$owner = $row['owner'];

					$request = $smcFunc['db_query']('', '
						SELECT rating
							FROM {db_prefix}pmxblog_ratings
							WHERE owner = {int:owner}',
						array('owner' => $owner)
					);
					while($row = $smcFunc['db_fetch_assoc']($request))
						$allrates .= $row['rating'];
					$smcFunc['db_free_result']($request);

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}pmxblog_manager
						SET blograting = {float:ratings}, blogvotes = {int:votes}
						WHERE owner = {int:owner}',
						array('ratings' => getBlogRating($allrates),
							'votes' =>  count(preg_split('/,/', $allrates, -1, PREG_SPLIT_NO_EMPTY)),
							'owner' => $owner)
					);
				}
			}
			unset($_GET['rating']);
      redirectexit(http_build_query($_GET, '', ';'));
		}

		// Manage Content ?
		if($context['PmxBlog']['action'][0] == 'cont')
		{
			$id = $context['PmxBlog']['action'][1];
			if($id == 'new')
			{
				if(!isOwner())
					NotAllowed();
				if(isBlogLocked())
					PmxBlog_Error($txt['PmxBlog_access_err_title'], $txt['PmxBlog_bloglocked_message'], $scripturl . '?action=pmxblog;sa=view');

				if(isBlogEnabled())
				{
					$context['PmxBlog']['action'][0] = 'contnew';
					$context['page_title'] = $txt['PmxBlog_newblog'];
					$context['PmxBlog']['page_link'] = array(
						'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
						'name' => $txt['PmxBlog_newblog']);
					Load_Wysiwyg('wysiwyg_edit');

					if($context['PmxBlog']['Manager']['hidebaronedit'] == 1)
					{
						$context['PmxBlog']['Manager']['showcalendar'] = 0;
						$context['PmxBlog']['Manager']['showarchive'] = 0;
						$context['PmxBlog']['Manager']['showcategories'] = 0;
					}
				}
				else
					redirectexit('action=pmxblog;sa=manager;setup');
			}

			// save content
			elseif($id == 'store')
			{
				if(!isOwner())
					NotAllowed();

				$allowcomment = 0;
				$categorie = 0;
				$published = 0;
				$notify = 0;
				$body = '';

				if(isset($_POST['html_to_bbc']))
				{
					$_POST['body'] = checkBBCHtmlTags($_POST['body'], empty($_POST['html_to_bbc']));
					unset($_POST['html_to_bbc']);
				}

				$_POST['body'] = ImageRealUrl($_POST['body']);

				foreach ($_POST as $what => $value)
				{
					switch ($what)
					{
						case "subject":
							$subject = htmlspecialchars(strip_tags($value), ENT_QUOTES);
						break;
						case "body":
							if(str_replace('&nbsp;', '', trim(strip_tags($value, '<img>'))) != '')
								$body = PrepSaveBody($value);
						break;
						case "category":
							$categorie = $value;
						break;
						case "allowcomment":
							$allowcomment = $value;
						break;
						case "allow_view":
							$allow_view = $value;
						break;
						case "published":
							$published = $value;
						break;
					}
				}

				if(!empty($body))
				{
					$td = forum_time();
					$ip = $user_info['ip'];
					$edDate = empty($published) ? 0 : $td;

					$smcFunc['db_insert']('', '
						{db_prefix}pmxblog_content',
						array('owner' => 'int',
							'ip_address' => 'string-255',
							'categorie' => 'int',
							'nbr_comment' => 'int',
							'allowcomment' => 'int',
							'allow_view' => 'int',
							'date_created' => 'int',
							'date_lastedit' => 'int',
							'published' => 'int',
							'notify' => 'int',
							'views' => 'int',
							'subject' => 'string-255',
							'body' => 'string-65535'),
						array($uid,
							$ip,
							$categorie,
							0,
							$allowcomment,
							$allow_view,
							$td,
							$edDate,
							$published,
							0,
							0,
							$subject,
							$body),
						array('ID', 'owner', 'categorie', 'date_created')
					);

					$request = $smcFunc['db_query']('', '
						SELECT MAX(ID)
							FROM {db_prefix}pmxblog_content
							WHERE owner = {int:owner}',
						array('owner' => $uid)
					);
					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_row']($request);
						$contID = $row[0];
						$smcFunc['db_free_result']($request);
					}

					// clear the Blog totals cache
					cache_put_data('PmxBlogTotals', null, -1);

					// if published, notify all user where track this blog
					if(!empty($published))
						SendTrackNotify($uid, $allow_view, $contID, stripslashes($subject));

					redirectexit('action=pmxblog;sa=manager;cont='.$contID.($uid != $user_info['id'] ? ';uid='.$uid : ''));
				}
				else
					redirectexit('action=pmxblog;sa='. $context['PmxBlog']['mode'].($uid != $user_info['id'] ? ';uid='.$uid : ''));
			}

			// update content
			elseif(isset($_GET['upd']))
			{
				if(!isModerator() && !isOwner($uid))
					NotAllowed();

				$allowcomment = 0;
				$categorie = 0;
				$published = 0;

				if(isset($_POST['html_to_bbc']))
				{
					$_POST['body'] = checkBBCHtmlTags($_POST['body'], empty($_POST['html_to_bbc']));
					unset($_POST['html_to_bbc']);
				}

				$_POST['body'] = ImageRealUrl($_POST['body']);

				foreach ($_POST as $what => $value)
				{
					switch ($what)
					{
						case "subject":
							$subject = htmlspecialchars(strip_tags($value), ENT_QUOTES);
						break;
						case "body":
							$body = PrepSaveBody($value);
						break;
						case "category":
							$categorie = $value;
						break;
						case "allowcomment":
							$allowcomment = $value;
						break;
						case "allow_view":
							$allow_view = $value;
						break;
						case "published":
							$published = $value;
						break;
					}
				}

				$request = $smcFunc['db_query']('', '
					SELECT published, date_lastedit, date_created
						FROM {db_prefix}pmxblog_content
					WHERE ID = {int:cid}',
					array('cid' => $id)
				);
				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$isPub = $row['published'];
					$lastEdit = $row['date_lastedit'];
					$datecreated = $row['date_created'];
					$smcFunc['db_free_result']($request);

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}pmxblog_content
							SET categorie ={int:categorie},
								allowcomment = {int:allowcomment},
								allow_view = {int:allow_view},
								published = {int:published},
								date_lastedit = {int:date_lastedit},
								date_created = {int:date_created},
								subject = {string:subject},
								body = {string:body}
							WHERE ID = {int:cid}',
						array('categorie' => $categorie,
							'allowcomment' => $allowcomment,
							'allow_view' => $allow_view,
							'published' => (!empty($published) ? $isPub : $published),
							'date_lastedit' => (empty($published) && empty($isPub) ? $lastEdit : forum_time()),
							'date_created' => (empty($published) && empty($isPub) && empty($lastEdit) ? forum_time() : $datecreated),
							'subject' => $subject,
							'body' => $body,
							'cid' => $id)
					);
				}

				// clear the Blog totals cache
				cache_put_data('PmxBlogTotals', null, -1);

				// Notify all user where track this blog
				if($published == 1)
					SendTrackNotify($uid, $allow_view, $id, stripslashes($subject));

				redirectexit('action=pmxblog;sa='. $context['PmxBlog']['mode'].';cont='.$id.($uid != $user_info['id'] ? ';uid='.$uid : ''));
			}

			// delete content
			elseif(isset($_GET['del']))
			{
				if(!isModerator() && !isOwner($uid))
					NotAllowed();

				// delete content, they comments and ratings and cmnt readlogs
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}pmxblog_content
					WHERE ID = {int:cid}',
					array('cid' => $id)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}pmxblog_comments
					WHERE contID = {int:cid}',
					array('cid' => $id)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}pmxblog_ratings
					WHERE contID = {int:cid} AND owner = {int:uid}',
					array('cid' => $id, 'uid' => $uid)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}pmxblog_cmnt_log
					WHERE contID = {int:contid}',
					array('contid' => $id)
				);

				// update Blograting
				$allrates = '';
				$request = $smcFunc['db_query']('', '
					SELECT rating
						FROM {db_prefix}pmxblog_ratings
						WHERE owner = {int:owner}',
					array('owner' => $uid)
				);
				while($row = $smcFunc['db_fetch_assoc']($request))
					$allrates .= $row['rating'];
				$smcFunc['db_free_result']($request);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}pmxblog_manager
					SET blograting = {float:ratings}, blogvotes = {int:votes}
					WHERE owner = {int:owner}',
					array('ratings' => getBlogRating($allrates),
						'votes' =>  count(preg_split('/,/', $allrates, -1, PREG_SPLIT_NO_EMPTY)),
						'owner' => $uid)
				);

				// clear the Blog totals cache
				cache_put_data('PmxBlogTotals', null, -1);
				redirectexit('action=pmxblog;sa='. $context['PmxBlog']['mode'] . ($uid != $user_info['id'] ? ';uid='.$uid : ''));
			}

			else
			{
				// edit or view singlepage
				$context['page_title'] = ($mode == 'manager' ? $txt['PmxBlog_manage_article'] : strip_tags($txt['PmxBlog_blog_rd']));
				$context['PmxBlog']['page_link'] = array(
					'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
					'name' => $mode == 'manager' ? $txt['PmxBlog_manage_article'] : strip_tags($txt['PmxBlog_blog_rd']));

				$isRead = Is_Read($uid, $context['PmxBlog']['Manager']['is_read'], $id);

				// get unread comments
				$newCmnt = 0;
				if(isset($context['PmxBlog']['cmnt_log'][$id]))
					$newCmnt += $context['PmxBlog']['cmnt_log'][$id]['cmtHigh'] > $context['PmxBlog']['cmnt_log'][$id]['cmtID'] ? 1 : 0;

				// get content
				$request = $smcFunc['db_query']('', '
					SELECT c.owner, c.categorie, c.nbr_comment, c.allowcomment, c.allow_view,
							c.date_created, c.date_lastedit, c.published, c.views, c.subject, c.body,
							r.rating, r.voter, m.buddy_list
						FROM {db_prefix}pmxblog_content as c
						LEFT JOIN {db_prefix}pmxblog_ratings as r ON(c.ID = r.contID)
						LEFT JOIN {db_prefix}members as m ON (c.owner = m.id_member)
						WHERE c.ID = {int:cid}',
					array('cid' => $id)
				);

				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					$cont_owner = $row['owner'];
					$buddies = $row['buddy_list'];
					$refDate = $row['date_created'];

					if(allowed_BlogCont($row['allow_view'], explode(',', $row['buddy_list']), $row['owner'])
						&& ($row['published'] == 1 || ($row['published'] == 0 && $row['owner'] == $user_info['id'])))
					{
						$context['PmxBlog']['content'][] = array(
							'id' => $id,
							'userid' => $row['owner'],
							'categorie' => $row['categorie'],
							'nbr_comment' => $row['nbr_comment'],
							'allowcomment' => array($row['allowcomment'], allowed_BlogCont($row['allowcomment'], explode(',', $row['buddy_list']), $row['owner'])),
							'date_created' => timeformat($row['date_created'], true),
							'date_edit' => timeformat($row['date_lastedit'], true),
							'views' => $row['views'],
							'rating' => getRating($row['rating']),
							'votes' => count(preg_split('/,/', $row['voter'], -1, PREG_SPLIT_NO_EMPTY)),
							'hasvoted' => in_array($user_info['id'], preg_split('/,/', $row['voter'], -1, PREG_SPLIT_NO_EMPTY)),
							'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
							'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
							'body' => PrepLoadBody($row['body'], isset($_GET['edit'])),
							'published' => $row['published'],
							'allow' => $row['allow_view'],
							'singlepage' => true,
							'is_new_cont' => !$isRead,
							'is_new_cmnt' => !$user_info['is_guest'] && $newCmnt > 0
						);

						if($cont_owner != $uid)
							PmxBlog_Error($txt['PmxBlog_unknown_err_title'], $txt['PmxBlog_unknown_err_msg'], $scripturl . '?action=pmxblog');

						if(isset($_GET['edit']))
						{
							$context['PmxBlog']['pageopt'] = ';cont='.$id;
							$context['PmxBlog']['action'][0] = 'contedit';
							$context['page_title'] = $txt['PmxBlog_editblog_title'];
							$context['PmxBlog']['page_link'] = array(
								'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
								'name' => $txt['PmxBlog_editblog_title']);

							Load_Wysiwyg('wysiwyg_edit');

							if($context['PmxBlog']['Manager']['hidebaronedit'] == 1)
							{
								$context['PmxBlog']['Manager']['showcalendar'] = 0;
								$context['PmxBlog']['Manager']['showarchive'] = 0;
								$context['PmxBlog']['Manager']['showcategories'] = 0;
							}
						}
						else
						{
							// Update content view and log
							if(!$isRead)
								Update_Readlist($context['PmxBlog']['Manager']['is_read'], $id, $uid);

							$lastRQ = isset($_SESSION['PmxBlog_Request']['last']) ? $_SESSION['PmxBlog_Request']['last'] : '';
							if($uid != $user_info['id'] && empty($context['PmxBlog']['Moderate']) && empty($_REQUEST['cmnt']) && $_SESSION['PmxBlog_Request']['curr'] != $lastRQ)
								$smcFunc['db_query']('', '
									UPDATE {db_prefix}pmxblog_content
										SET views = views + 1
										WHERE ID = {int:cid}',
									array('cid' => $id)
								);

							// Update comment log
							$newCmnt = 0;
							if(!$user_info['is_guest'])
							{
								if(isset($context['PmxBlog']['cmnt_log'][$id]))
								{
									if($context['PmxBlog']['cmnt_log'][$id]['owner'] == $uid && $context['PmxBlog']['cmnt_log'][$id]['cmtHigh'] > $context['PmxBlog']['cmnt_log'][$id]['cmtID'])
									{
										$smcFunc['db_query']('', '
											DELETE FROM {db_prefix}pmxblog_cmnt_log
											WHERE userID = {int:mem} and contID = {int:contId}',
											array('mem' => $user_info['id'], 'contId' => $context['PmxBlog']['cmnt_log'][$id]['contID'])
										);

										$smcFunc['db_insert']('', '
											{db_prefix}pmxblog_cmnt_log',
											array('userID' => 'int', 'contID' => 'int', 'cmtID' => 'int'),
											array($user_info['id'], $context['PmxBlog']['cmnt_log'][$id]['contID'], $context['PmxBlog']['cmnt_log'][$id]['cmtHigh']),
											array('userID')
										);
									}
								}
							}

							$context['PmxBlog']['action'][0] = 'singlepage';
							$context['PmxBlog']['pageopt'] = ';cont='.$id;

							// get previous / next content
							$context['PmxBlog']['contprev'] = null;
							$context['PmxBlog']['contnext'] = null;
							$contcat[] = null;

							$request = $smcFunc['db_query']('', '
								SELECT c.ID, c.allow_view, c.published, c.subject, c.date_created, c.categorie
									FROM {db_prefix}pmxblog_content AS c
									WHERE c.owner = {int:owner}'. $context['PmxBlog']['Manager']['andRQ'] .'
									ORDER BY c.date_created ASC',
								array(
									'owner' => $cont_owner,
									'refmin' => $context['PmxBlog']['Manager']['refmin'],
									'refmax' => $context['PmxBlog']['Manager']['refmax']
								)
							);

							while($row = $smcFunc['db_fetch_assoc']($request))
							{
								if(allowed_BlogCont($row['allow_view'], explode(',', $buddies), $cont_owner)
									&& ($row['published'] == 1 || ($row['published'] == 0 && $cont_owner == $user_info['id'])))
								{
									if($row['ID'] != $id && $row['date_created'] < $refDate)
										$context['PmxBlog']['contnext'] = array(
											'contid' => $row['ID'],
											'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
										);
									elseif($row['ID'] != $id && $row['date_created'] > $refDate && empty($context['PmxBlog']['contprev']))
										$context['PmxBlog']['contprev'] = array(
											'contid' => $row['ID'],
											'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
										);

									$contcat[] = $row['categorie'];
									$d = getdate($row['date_created']);
									$context['PmxBlog']['arch'][$d['year']][$d['mon']] = 1;
									$context['PmxBlog']['cal'][$d['year']][$d['mon']][$d['mday']] = 1;
								}
							}
							$smcFunc['db_free_result']($request);

							// mask out categories without content
							foreach($context['PmxBlog']['categorie'] as $key => $val)
								$context['PmxBlog']['categorie'][$key]['ContCat'] = (in_array($val['id'], $contcat) ? 1 : 0);

							$sumCmnt = 0;

							// get all Comments for this content
							$request = $smcFunc['db_query']('', '
								SELECT c.ID, c.author, c.username, IFNULL(m.real_name, 0) as real_name, c.contID, c.treelevel, c.treeS2,
										c.date_created, c.date_lastedit, c.subject, c.body
									FROM {db_prefix}pmxblog_comments AS c
									LEFT JOIN {db_prefix}members AS m ON(c.author = m.id_member)
									WHERE contID = {int:contid}
									ORDER BY treeS2 ASC',
								array('contid' => $id)
							);

							if($smcFunc['db_num_rows']($request) > 0)
							{
								$pgID = 0;
								$pg = 0;
								if(isset($_GET['cmnt']) && is_numeric($_GET['cmnt']))
									$pgID = (int) $_GET['cmnt'];

								while($row = $smcFunc['db_fetch_assoc']($request))
								{
									// get unread comments
									$newCmnt = 0;
									if(isset($context['PmxBlog']['cmnt_log'][$id]))
										$newCmnt += $row['ID'] > $context['PmxBlog']['cmnt_log'][$row['contID']]['cmtID'] ? 1 : 0;
									$sumCmnt = ($row['ID'] == $pgID ? $pg : $sumCmnt);

									$context['PmxBlog']['comments'][] = array(
										'id' => $row['ID'],
										'realname' => (empty($row['real_name']) ? $row['username'] : $row['real_name']),
										'userid' => $row['author'],
										'treelevel' => $row['treelevel'],
										'treeS2' => $row['treeS2'],
										'date_created' => timeformat($row['date_created'], true),
										'date_edit' => timeformat($row['date_lastedit'], true),
										'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
										'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
										'body' => PrepLoadBody($row['body']),
										'is_new_cmnt' => !$user_info['is_guest'] && $newCmnt > 0
									);
									$pg++;
								}
								$smcFunc['db_free_result']($request);
							}

							if($sumCmnt > 0)
							{
								$pg = floor($sumCmnt / $context['PmxBlog']['comment_pages']) * $context['PmxBlog']['comment_pages'];
								$pagelist['cmntpage'] = $pg;
							}
						}
					}
				}
			}
		}

		// Manage Comment ?
		elseif($context['PmxBlog']['action'][0] == 'cmnt')
		{
			isAllowedToBlog('cmnt');
			$id = $context['PmxBlog']['action'][1];

			// get unread comments
			$newCmnt = 0;
			if(isset($context['PmxBlog']['cmnt_log'][$id]))
				$newCmnt += $context['PmxBlog']['cmnt_log'][$id]['cmtHigh'] > $context['PmxBlog']['cmnt_log'][$id]['cmtID'] ? 1 : 0;

			// New comment ?
			if(isset($_GET['new']))
			{
				$context['PmxBlog']['action'][0] = 'cmntnew';

				$request = $smcFunc['db_query']('', '
					SELECT c.owner, c.categorie, c.nbr_comment, c.allowcomment, c.allow_view,
							c.date_created, c.date_lastedit, c.published, c.views, c.subject, c.body,
							r.rating, r.voter, m.buddy_list
						FROM {db_prefix}pmxblog_content as c
						LEFT JOIN {db_prefix}pmxblog_ratings AS r ON (c.ID = r.contID)
						LEFT JOIN {db_prefix}members as m ON (c.owner = m.id_member)
						WHERE ID = {int:cid}',
					array('cid' => $id)
				);

				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					$cont_owner = $row['owner'];

					if(allowed_BlogCont($row['allow_view'], explode(',', $row['buddy_list']), $row['owner'])
						&& allowed_BlogCont($row['allowcomment'], explode(',', $row['buddy_list']), $row['owner'])
						&& ($row['published'] == 1 || ($row['published'] == 0 && $row['owner'] == $user_info['id'])))
					{
						$context['PmxBlog']['content'][] = array(
							'id' => $id,
							'userid' => $row['owner'],
							'categorie' => $row['categorie'],
							'nbr_comment' => $row['nbr_comment'],
							'allowcomment' => array($row['allowcomment'], allowed_BlogCont($row['allowcomment'], explode(',', $row['buddy_list']), $row['owner'])),
							'date_created' => timeformat($row['date_created'], true),
							'views' => $row['views'],
							'rating' => getRating($row['rating']),
							'votes' => count(preg_split('/,/', $row['voter'], -1, PREG_SPLIT_NO_EMPTY)),
							'date_edit' => timeformat($row['date_created'], true),
							'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
							'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
							'body' => PrepLoadBody($row['body']),
							'published' => $row['published'],
							'allow' => $row['allow_view'],
							'is_new_cmnt' => !$user_info['is_guest'] && $newCmnt > 0,
							'singlepage' => true
						);
          }
					else
						NotAllowed();

					if($cont_owner != $uid)
						PmxBlog_Error($txt['PmxBlog_unknown_err_title'], $txt['PmxBlog_unknown_err_msg'], $scripturl . '?action=pmxblog');

					$context['PmxBlog']['pageopt'] = ';cont='.$id;
					$context['page_title'] = $txt['PmxBlog_newcomment_title'];
					$context['PmxBlog']['page_link'] = array(
						'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
						'name' => $txt['PmxBlog_newcomment_title']);

					Load_Wysiwyg('wysiwyg_comment');
				}
			}

			// Reply to comment?
			elseif(isset($_GET['rply']))
			{
				$context['PmxBlog']['action'][0] = 'cmntrply';

				$request = $smcFunc['db_query']('', '
					SELECT c.ID, c.author, c.username, c.contID, c.treeS2, c.date_created, c.date_lastedit, c.subject, c.body,
						m.real_name, m.buddy_list,
						a.owner, a.allowcomment, a.allow_view, a.published
						FROM {db_prefix}pmxblog_comments AS c
						LEFT JOIN {db_prefix}pmxblog_content AS a ON(c.contID = a.ID)
						LEFT JOIN {db_prefix}members AS m ON(a.owner = m.id_member)
						WHERE c.ID = {int:cid}',
					array('cid' => $id)
				);

				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					$cont_owner = $row['owner'];

					if(allowed_BlogCont($row['allow_view'], explode(',', $row['buddy_list']), $row['owner'])
						&& allowed_BlogCont($row['allowcomment'], explode(',', $row['buddy_list']), $row['owner'])
						&& ($row['published'] == 1 || ($row['published'] == 0 && $row['owner'] == $user_info['id'])))
					{
						$context['PmxBlog']['comments'][] = array(
							'id' => $row['ID'],
							'contID' => $row['contID'],
							'realname' => ($row['author'] == 0 ? $row['username'] : $row['real_name']),
							'userid' => $row['author'],
							'treeS2' => $row['treeS2'],
							'date_created' => timeformat($row['date_created'], true),
							'date_edit' => timeformat($row['date_lastedit'], true),
							'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
							'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
							'body' => PrepLoadBody($row['body']),
							'is_new_cmnt' => !$user_info['is_guest'] && $newCmnt > 0,
							'published' => $row['published']
						);
						$cID = $row['contID'];
						$bdl = $row['buddy_list'];
						$context['PmxBlog']['pageopt'] = ';cont='.$cID;
            $context['PmxBlog']['comments'][0]['subject'] = preg_replace('@\s\[[^\]]*\]@i', '', $context['PmxBlog']['comments'][0]['subject']);
					}
					else
						NotAllowed();

					if($cont_owner != $uid)
						PmxBlog_Error($txt['PmxBlog_unknown_err_title'], $txt['PmxBlog_unknown_err_msg'], $scripturl . '?action=pmxblog');

					$context['page_title'] = $txt['PmxBlog_replycomment_title'];
					$context['PmxBlog']['page_link'] = array(
						'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
						'name' => $txt['PmxBlog_replycomment_title']);
					Load_Wysiwyg('wysiwyg_comment');
				}
			}

			// save comment or replay
			elseif(isset($_GET['store']))
			{
				if($user_info['is_guest'] && !empty($modSettings['reg_verification']))
				{
					require_once($sourcedir . '/Subs-Editor.php');
					$verificationOptions = array(
						'id' => 'pmxblog',
					);
					$context['visual_verification'] = create_control_verification($verificationOptions, true);
					if(is_array($context['visual_verification']))
					{
						if(!empty($_POST['body']))
							$_SESSION['PmxBlog_cmnt_body'] = $_POST['body'];
						PmxBlog_Error($txt['PmxBlog_captcha_err_title'], $txt['PmxBlog_captcha_err_msg'], $rdir);
					}
					elseif(isset($_SESSION['PmxBlog_cmnt_body']))
						unset($_SESSION['PmxBlog_cmnt_body']);
				}

				$body = '';
				if(isset($_POST['html_to_bbc']))
				{
					$_POST['body'] = checkBBCHtmlTags($_POST['body'], empty($_POST['html_to_bbc']));
					unset($_POST['html_to_bbc']);
				}

				$_POST['body'] = ImageRealUrl($_POST['body']);

				foreach ($_POST as $what => $value)
				{
					switch ($what)
					{
						case "subject":
							$subject = preg_replace('@\s\[[^\]]*\]@i', '', htmlspecialchars(strip_tags($value), ENT_QUOTES));
              $subject = preg_replace('@Re:\s@i', '', $subject);
						break;
						case "body":
							if(str_replace('&nbsp;', '', trim(strip_tags($value, '<img>'))) != '')
								$body = PrepSaveBody($value);
						break;
						case "username":
							$username = $value;
						break;
						case "captcha":
							$captcha = strtoupper($value);
						break;
					}
				}

				$treelevel = 0;
				$treeS2 = 0;
				$parent = 0;

				if($_GET['store'] == 'new')
				{
					$request = $smcFunc['db_query']('', '
						SELECT MAX(treeS2) as treeS2
							FROM {db_prefix}pmxblog_comments
							WHERE contID = {int:contid}',
						array('contid' => $id)
					);

					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_assoc']($request);
						$treeS2 = $row['treeS2'] +1;
						$smcFunc['db_free_result']($request);
					}
					$contID = $id;
					$ptrS2 = '';
				}

				elseif($_GET['store'] == 'rply')
				{
					$request = $smcFunc['db_query']('', '
						SELECT contID, parent, treelevel, treeS2
							FROM {db_prefix}pmxblog_comments
							WHERE ID = {int:contid}',
						array('contid' => $id)
					);

					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_assoc']($request);
						$smcFunc['db_free_result']($request);

						$contID = $row['contID'];
						$parent = $row['parent'];
						$treelevel = $row['treelevel'] +1;
						$treeS2 = $row['treeS2'] +1;
					}

					if($body != '')
					{
						// shift comments up
						$tl = array($id);
						$ptrS2 = strval($treeS2 -1);

						$request = $smcFunc['db_query']('', '
							SELECT ID, parent, treeS2
								FROM {db_prefix}pmxblog_comments
								WHERE contID = {int:contid}
									AND treeS2 >= {int:tree}
								ORDER BY treeS2 ASC',
							array('contid' => $contID,
								'tree' => $treeS2)
						);

						if($smcFunc['db_num_rows']($request) > 0)
						{
							while($row = $smcFunc['db_fetch_assoc']($request))
							{
								if(in_array($row['parent'], $tl))
								{
									$treeS2 = $row['treeS2'] +1;
									$tl[] = $row['ID'];
								}
							}
							$smcFunc['db_free_result']($request);

							$smcFunc['db_query']('', '
								UPDATE {db_prefix}pmxblog_comments
									SET treeS2 = treeS2 + 1
									WHERE contID = {int:contid}
										AND treeS2 >= {int:tree}',
								array('contid' => $contID, 'tree' => $treeS2)
							);
						}
						$parent = $id;
					}
				}

				if(!empty($body))
				{
					$subject = 'Re: '. $subject .(!empty($ptrS2) ? ' ['. $ptrS2 .']' : '');
					$td = forum_time();
					$poster = $user_info['id'];
					$ip = $user_info['ip'];
					if(!$user_info['is_guest'])
						$username = $user_info['username'];

					$smcFunc['db_insert']('', '
						{db_prefix}pmxblog_comments',
						array(
							'author' => 'int',
							'username' => 'string-255',
							'ip_address' => 'string-255',
							'contID' => 'int',
							'parent' => 'int',
							'treelevel' => 'int',
							'treeS2' => 'int',
							'date_created' => 'int',
							'date_lastedit' => 'int',
							'subject' => 'string-255',
							'body' => 'string-65535'),
						array(
							$poster,
							$username,
							$ip,
							$contID,
							$parent,
							$treelevel,
							$treeS2,
							$td,
							$td,
							$subject,
							$body),
						array('ID')
					);

					// get insert cmnt id
					$lastcmtID = $smcFunc['db_insert_id']('{db_prefix}pmxblog_comments', 'ID');

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}pmxblog_content SET
							nbr_comment = nbr_comment + 1
							WHERE ID = {int:contid}',
						array('contid' => $contID)
					);

					// Notify all user where track this blog
					$cmntID = 0;
					$request = $smcFunc['db_query']('', '
						SELECT MAX(ID)
							FROM {db_prefix}pmxblog_comments',
						array()
					);

					if($row = $smcFunc['db_fetch_row']($request))
					{
						$cmntID = $row[0];
						$smcFunc['db_free_result']($request);
					}

					$request = $smcFunc['db_query']('', '
						SELECT owner, allow_view, subject
							FROM {db_prefix}pmxblog_content
							WHERE ID = {int:contid}',
						array('contid' => $contID)
					);

					if($row = $smcFunc['db_fetch_row']($request))
					{
						$owner = $row[0];
						$allow_view = $row[1];
						$contSubj = $row[2];
						$smcFunc['db_free_result']($request);
					}

					if($cmntID > 0);
						SendTrackNotify($owner, $allow_view, $contID, $contSubj, $poster, $username, $cmntID, $subject);
					// get total comments
					$sumCmnt = 1;
					if(isset($context['PmxBlog']['cmnt_log'][$contID]))
						$sumCmnt += $context['PmxBlog']['cmnt_log'][$contID]['nbr_comment'];
				}
				redirectexit('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cont='.$contID.';cmnt='.$cmntID.($uid != $user_info['id'] ? ';uid='.$uid : '').'#cmnt'.$cmntID);
			}

			// delete comment or reply
			elseif(isset($_GET['del']))
			{
				$before = 0;
				$after = 0;

				$request = $smcFunc['db_query']('', '
					SELECT author, contID
						FROM {db_prefix}pmxblog_comments
						WHERE ID = {int:cid}',
					array('cid' => $id)
				);

				if($row = $smcFunc['db_fetch_row']($request))
				{
					$author = $row[0];
					$contID = $row[1];
					$smcFunc['db_free_result']($request);

					// find comment before & after from this content
					$request = $smcFunc['db_query']('', '
						SELECT ID
							FROM {db_prefix}pmxblog_comments
							WHERE contID = {int:contid}
							ORDER BY ID',
						array('contid' => $contID)
					);
					while($row = $smcFunc['db_fetch_assoc']($request))
					{
						if($row['ID'] < $id)
							$before = $row['ID'];
						elseif($row['ID'] > $id && $after == 0)
							$after = $row['ID'];
					}
					$smcFunc['db_free_result']($request);

					// get the owner of content
					$request = $smcFunc['db_query']('', '
						SELECT owner
							FROM {db_prefix}pmxblog_content
							WHERE ID = {int:contid}
							ORDER BY ID',
						array('contid' => $contID)
					);
					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_assoc']($request);
						$smcFunc['db_free_result']($request);
					}

					if(!$user_info['is_guest'] && (isOwner($row['owner']) || isOwner($row['author']) || isModerator()))
					{
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}pmxblog_comments
								WHERE ID = {int:cid}',
							array('cid' => $id)
						);

						$smcFunc['db_query']('', '
							UPDATE {db_prefix}pmxblog_content
								SET nbr_comment = nbr_comment - 1
								WHERE ID = {int:cid}',
							array('cid' => $contID)
						);
					}
					else
						NotAllowed();
				}

				if($before == 0 && $after == 0)
					redirectexit('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cont='.$contID.($uid != $user_info['id'] ? ';uid='.$uid : '').'#cmnt');
				else
				{
					if($after == 0)
						$after = $before;
					redirectexit('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cont='.$contID.';cnmt='.$after.($uid != $user_info['id'] ? ';uid='.$uid : '').'#cmnt'.$after);
				}
			}

			// edit comment or reply
			elseif(isset($_GET['edit']))
			{
				$context['PmxBlog']['action'][0] = 'cmntedit';

				$request = $smcFunc['db_query']('', '
					SELECT a.owner, c.ID, c.author, c.contID, c.username, m.real_name, c.treeS2, c.date_created, c.date_lastedit, c.subject, c.body
						FROM {db_prefix}pmxblog_comments AS c
						LEFT JOIN {db_prefix}pmxblog_content AS a ON (a.ID = c.contID)
						LEFT JOIN {db_prefix}members AS m ON (c.author = m.id_member)
						WHERE c.ID = {int:cid}',
					array('cid' => $id)
				);

				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$contID = $row['contID'];
					$context['PmxBlog']['comments'][] = array(
						'id' => $row['ID'],
						'contID' => $row['contID'],
						'realname' => ($row['author'] == 0 ? $row['username'] : $row['real_name']),
						'userid' => $row['author'],
						'treeS2' => $row['treeS2'],
						'date_created' => timeformat($row['date_created'], true),
						'date_edit' => timeformat($row['date_lastedit'], true),
						'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
						'subject' => stripslashes($row['subject']),
						'body' => PrepLoadBody($row['body'], true),
						'is_new_cmnt' => !$user_info['is_guest'] && $newCmnt > 0
					);
					$context['PmxBlog']['pageopt'] = ';cont='.$row['contID'];
					$smcFunc['db_free_result']($request);
				}

				if(!$user_info['is_guest'] && (isOwner($row['owner']) || isOwner($row['author']) || isModerator()))
				{
					$context['page_title'] = $txt['PmxBlog_editcomment_title'];
					$context['PmxBlog']['page_link'] = array(
						'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
						'name' => $txt['PmxBlog_editcomment_title']);
					Load_Wysiwyg('wysiwyg_comment');
				}
				else
					NotAllowed();
			}

			// update a comment or reply
			elseif(isset($_GET['upd']))
			{
				$request = $smcFunc['db_query']('', '
					SELECT a.owner, c.author
						FROM {db_prefix}pmxblog_comments AS c
						LEFT JOIN {db_prefix}pmxblog_content AS a ON(a.ID = c.contID)
						WHERE c.ID = {int:cid}',
					array('cid' => $id)
				);
				if($smcFunc['db_num_rows']($request) > 0)
				{
					$row = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);
				}

				if(!$user_info['is_guest'] && (isOwner($row['owner']) || isOwner($row['author']) || isModerator()))
				{
					if(isset($_POST['html_to_bbc']))
					{
						$_POST['body'] = checkBBCHtmlTags($_POST['body'], empty($_POST['html_to_bbc']));
						unset($_POST['html_to_bbc']);
					}

					$_POST['body'] = ImageRealUrl($_POST['body']);

					foreach ($_POST as $what => $value)
					{
						switch ($what)
						{
							case "subject":
								$subject = htmlspecialchars(strip_tags($value), ENT_QUOTES);
							break;
							case "body":
								$body = PrepSaveBody($value);
							break;
						}
					}

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}pmxblog_comments
							SET date_lastedit = {int:date_lastedit},
								subject = {string:subject},
								body = {string:body}
							WHERE ID = {int:cid}',
						array(
							'date_lastedit' => forum_time(),
							'subject' => $subject,
							'body' => $body,
							'cid' => $id)
					);

					$request = $smcFunc['db_query']('', '
						SELECT contID
							FROM {db_prefix}pmxblog_comments
							WHERE ID = {int:cid}',
						array('cid' => $id)
					);
					if($smcFunc['db_num_rows']($request) > 0)
					{
						$row = $smcFunc['db_fetch_assoc']($request);
						$contID = $row['contID'];
						$smcFunc['db_free_result']($request);
					}

					redirectexit('action=pmxblog;sa='.$context['PmxBlog']['mode'].';cont='.$contID.';cmnt='.$id.($uid != $user_info['id'] ? ';uid='.$uid : '').'#cmnt'.$id);
				}
				else
					NotAllowed();
			}
		}

		else
		// Content overview
		{
			$cID = 0;
			$contcat = array();
			require_once($sourcedir .'/PmxBlogTeaser.php');

			// Content from categorie ?
			if(isset($_GET['ca']) && is_numeric($_GET['ca']))
			{
				$cID = (int) $_GET['ca'];
				$context['PmxBlog']['pagemode'] = ';ca='. $cID;
				$context['PmxBlog']['Manager']['andRQ'] .= ' AND c.categorie = {int:catid}';
				$context['PmxBlog']['Manager']['archStr'] .= (!empty($context['PmxBlog']['Manager']['archStr']) ? ' - ' : '') . GetCatname($cID);
			}

			$request = $smcFunc['db_query']('', '
				SELECT c.ID, c.owner, c.categorie, c.nbr_comment, c.allowcomment, c.allow_view,
							c.date_created, c.date_lastedit, c.published, c.views, c.subject, c.body,
							r.rating, r.voter, m.buddy_list
					FROM {db_prefix}pmxblog_content as c
					LEFT JOIN {db_prefix}pmxblog_ratings AS r ON (c.ID = r.contID)
					LEFT JOIN {db_prefix}members as m ON (c.owner = m.id_member)
					WHERE'. ($uid != $user_info['id'] ? ' c.published = 1 AND' : '') .' c.owner = {int:uid}
					AND (c.allow_view = 0
					OR (c.allow_view = 1 AND {int:idmem} > 0)
					OR (c.allow_view = 2 AND (c.owner = {int:idmem} OR {string:s_idmem} IN (m.buddy_list)))
					OR (c.allow_view = 3 AND c.owner = {int:idmem}))'. $context['PmxBlog']['Manager']['andRQ'] .'
					ORDER BY c.date_created DESC',
				array(
					'idmem' => $user_info['id'],
					's_idmem' => (string) $user_info['id'],
					'uid' => $uid,
					'catid' => $cID,
					'refmin' => $context['PmxBlog']['Manager']['refmin'],
					'refmax' => $context['PmxBlog']['Manager']['refmax']
				)
			);

			while($row = $smcFunc['db_fetch_assoc']($request))
			{
				$context['PmxBlog']['content'][] = array(
					'id' => $row['ID'],
					'userid' => $row['owner'],
					'categorie' => $row['categorie'],
					'nbr_comment' => $row['nbr_comment'],
					'allowcomment' => array($row['allowcomment'], allowed_BlogCont($row['allowcomment'], explode(',', $row['buddy_list']), $row['owner'])),
					'date_created' => timeformat($row['date_created'], true),
					'date_edit' => timeformat($row['date_lastedit'], true),
					'views' => $row['views'],
					'rating' => getRating($row['rating']),
					'votes' => count(preg_split('/,/', $row['voter'], -1, PREG_SPLIT_NO_EMPTY)),
					'hasvoted' => in_array($user_info['id'], preg_split('/,/', $row['voter'], -1, PREG_SPLIT_NO_EMPTY)),
					'is_edit' => ($row['date_lastedit'] > $row['date_created'] + $context['PmxBlog']['editholdtime']),
					'subject' => (empty($row['subject']) ? 'untitled' : ($context['PmxBlog']['censor_text'] == 1 ? censorText(stripslashes($row['subject'])) : stripslashes($row['subject']))),
					'body' => PmxBlogTeaser($row['body']),
					'allow' => $row['allow_view'],
					'published' => $row['published'],
					'singlepage' => false,
					'is_new_cont' => !Is_Read($uid, $context['PmxBlog']['Manager']['is_read'], $row['ID']),
					'is_new_cmnt' => !$user_info['is_guest'] && isset($context['PmxBlog']['cmnt_log'][$row['ID']]['cmtHigh']) && $context['PmxBlog']['cmnt_log'][$row['ID']]['cmtHigh'] > $context['PmxBlog']['cmnt_log'][$row['ID']]['cmtID']
				);

				$contcat[] = $row['categorie'];
				$d = getdate($row['date_created']);
				$context['PmxBlog']['arch'][$d['year']][$d['mon']] = 1;
				$context['PmxBlog']['cal'][$d['year']][$d['mon']][$d['mday']] = 1;
			}
			$smcFunc['db_free_result']($request);

			// mask out categories without content
			foreach($context['PmxBlog']['categorie'] as $key => $val)
				$context['PmxBlog']['categorie'][$key]['ContCat'] = (in_array($val['id'], $contcat) ? 1 : 0);

			$context['page_title'] = ($mode == 'manager' ? $txt['PmxBlog_manage_cont'] : strip_tags($txt['PmxBlog_viewblog']));
			$context['PmxBlog']['page_link'] = array(
				'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
				'name' => $mode == 'manager' ? $txt['PmxBlog_manage_cont'] : strip_tags($txt['PmxBlog_viewblog']));
		}

		// create the pageindex
		$context['PmxBlog']['pagebot'] = '<a href="#bot"><img src="'.$settings['default_images_url'].'/PmxBlog/cat_movedown.gif" alt="" title="Bottom" /></a>';
		$context['PmxBlog']['pagetop'] = '<a href="#top"><img src="'.$settings['default_images_url'].'/PmxBlog/cat_moveup.gif" alt="" title="Top" /></a>';

		if(isset($context['PmxBlog']['comments']) && count($context['PmxBlog']['comments']) > $context['PmxBlog']['comment_pages'])
		{
			$context['PmxBlog']['startpage'] = $pagelist['cmntpage'];
			$context['PmxBlog']['pageindex'] = $txt['pages'].': '.
				str_replace(';start=', ';pg=', constructPageIndex($scripturl.'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$context['PmxBlog']['pagemode'].$context['PmxBlog']['pageopt']. ($context['PmxBlog']['UID'] != $user_info['id'] ? ';uid='.$context['PmxBlog']['UID'] : ''),
							$context['PmxBlog']['startpage'],
							count($context['PmxBlog']['comments']),
							$context['PmxBlog']['comment_pages']));
			$context['PmxBlog']['pageindex'] = str_replace('">', '#cmnt">', $context['PmxBlog']['pageindex']);
			$context['PmxBlog']['cmntpagetop'] = '<a href="#cmnt"><img src="'.$settings['default_images_url'].'/PmxBlog/cat_moveup.gif" alt="" title="Comment Top" /></a>';
		}
		elseif(isset($context['PmxBlog']['content']) && count($context['PmxBlog']['content']) > $context['PmxBlog']['content_pages'])
		{
			$context['PmxBlog']['startpage'] = $pagelist['contpage'];
			$context['PmxBlog']['pageindex'] = $txt['pages'].': '.
				str_replace(';start=', ';pg=', constructPageIndex($scripturl.'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$context['PmxBlog']['pagemode'].$context['PmxBlog']['pageopt']. ($context['PmxBlog']['UID'] != $user_info['id'] ? ';uid='.$context['PmxBlog']['UID'] : ''),
							$context['PmxBlog']['startpage'],
							count($context['PmxBlog']['content']),
							$context['PmxBlog']['content_pages']));
		}

		// load the template .. done
		loadTemplate('PmxBlog');
	}
}

// check html tags from BBC editor
function checkBBCHtmlTags($content, $convTags)
{
	global $context, $user_info, $sourcedir;

	if($convTags)
	{
		while(preg_match_all('~(\&lt;('. $context['PmxBlog']['htmltags'] .').*\&gt;)(.*)(\&lt;/\\2\&gt;)~iU', $content, $matches, PREG_PATTERN_ORDER) != 0)
		{
			for($i = 0; $i < count($matches[1]); $i++)
			{
				$matches[1][$i] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $matches[1][$i]);
				$matches[4][$i] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $matches[4][$i]);
				$content = str_replace($matches[0][$i], $matches[1][$i] . $matches[3][$i] . $matches[4][$i], $content);
			}
		}
	}

	require_once($sourcedir . '/Subs.php');
	$user_info['smiley_set'] = 'PortaMx';
	$tmp = parse_bbc($content);
	return $tmp;
}

// do's same as realpath, but on url's
function ImageRealUrl($content)
{
	$images = preg_match_all('/<img[^>]*>/i', $content, $match);
	if($images != 0)
	{
		for($i = 0; $i < $images; $i++)
		{
			$img = $match[0][$i];
			preg_match('/src?=?\"[^\"]*\"/i', $img, $src);
			if(strpos($src[0], '..') !== false)
			{
				$parts = explode('/', $src[0]);
        while(list($idx, $val) = each($parts))
				{
					if($parts[$idx] == '..')
					{
						array_splice($parts, $idx-1, 2);
						reset($parts);
					}
				}
				if(!empty($parts))
					$content = str_replace($src[0], implode('/', $parts), $content);
			}
		}
	}

	return $content;
}
?>