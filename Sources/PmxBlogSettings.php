<?php
// ----------------------------------------------------------
// -- PmxBlogSettings.php                                  --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (!defined('SMF'))
	die('Hacking attempt...');

// PmxBlog Manager Settings
function PmxBlogSettings($uid)
{
	global $context, $settings, $modSettings, $user_info, $scripturl, $txt, $user_profile, $smcFunc;

	// Settings update
	if($context['PmxBlog']['action'][1] == 'upd')
	{
		if(!empty($context['PmxBlog']['Manager']['have_blog']) && (isOwner() || AllowedTo('admin_forum')))
		{
			$request = $smcFunc['db_query']('', '
				SELECT *
					FROM {db_prefix}pmxblog_manager
					WHERE owner = {int:uid}',
				array('uid' => $uid)
			);

			if($row = $smcFunc['db_fetch_assoc']($request))
			{
				$row['settings'] = (empty($row['settings']) ? '111' : $row['settings']);
				$row['settings'] .= (strlen($row['settings']) == 2 ? '1' : '');
				$tmp = array(
					'blogname' => $row['blogname'],
					'blogdesc' => $row['blogdesc'],
					'showarchive' => $row['showarchive'],
					'showcategories' => $row['showcategories'],
					'showcalendar' => $row['showcalendar'],
					'hidebaronedit' => $row['hidebaronedit'],
					'blogcreated' => $row['blogcreated'],
					'blogenabled' => 0,
					'bloglocked' => 0,
					'settings' => $row['settings'],
					'tracking' => $row['tracking']
				);
				$smcFunc['db_free_result']($request);
			}
		}
		else
			list($tmp['blogname'],
				$tmp['blogdesc'],
				$tmp['showcalendar'],
				$tmp['showarchive'],
				$tmp['showcategories'],
				$tmp['hidebaronedit'],
				$tmp['blogenabled'],
				$tmp['blogcreated'],
				$tmp['bloglocked'],
				$tmp['settings'],
				$tmp['tracking']) = explode(',', ',,0,0,0,0,0,'.forum_time().',0,111,0|0:');

		$valid_keys = array_unique(array_merge(array('trackself', 'contenteditor', 'commenteditor', 'showavatar'), array_keys($tmp)));
		foreach ($_POST as $what => $value)
		{
			if($what != 'settings' && in_array($what, $valid_keys))
			{
				$value = htmlspecialchars(strip_tags($value), ENT_QUOTES);
				if($what == 'tracking')
					$tr = $value;
				elseif($what == 'trackself')
					$trS = $value;
				elseif($what == 'contenteditor')
					$tmp['settings']{0} = $value;
				elseif($what == 'commenteditor')
					$tmp['settings']{1} = $value;
				elseif($what == 'showavatar')
					$tmp['settings']{2} = $value;
				else
					$tmp[$what] = $value;
			}
		}
		if(isset($trS) && isset($tr))
			$tmp['tracking'] = $trS.'|'.$tr;

		$insset = array('owner' => 'int');
		$datset = array('owner' => $uid);
		foreach($tmp as $key => $data)
		{
			$insset[strtolower($key)] = (is_numeric($data) ? 'int' : 'string');
			$datset[strtolower($key)] = (is_numeric($data) ? trim($data) : $data);
		}

		$smcFunc['db_insert']('replace', '
			{db_prefix}pmxblog_manager',
			$insset,
			$datset,
			array('owner')
		);

		if(empty($context['PmxBlog']['Manager']['have_blog']))
		{
			// set all content/comments as read if blog new
			$user = array();
			$req = $smcFunc['db_query']('', '
				SELECT owner
					FROM {db_prefix}pmxblog_manager',
				array()
			);
			while($row = $smcFunc['db_fetch_row']($req))
				$user[] = $row[0];
			$smcFunc['db_free_result']($req);

			foreach($user as $u)
				Mark_All_Read($u);

			$smcFunc['db_insert']('', '
				{db_prefix}pmxblog_cmnt_log
				(owner, userID, contID, last_read)
				SELECT c.owner, '. $user_info['id'] .', c.ID, max(m.ID)
					FROM {db_prefix}pmxblog_content as c
					LEFT JOIN {db_prefix}pmxblog_comments AS m ON (m.contID = c.ID)
					WHERE m.ID IS NOT NULL
					GROUP BY c.ID ORDER BY c.owner',
				array(),
				array(),
				array('owner', 'userID', 'contID')
			);
		}
		// clear the Blog totals cache
		cache_put_data('PmxBlogTotals', null, -1);

		$_GET['setup'] = '';
		redirectexit(http_build_query($_GET, '', ';'));
	}

	// Remove the blog ?
	elseif($context['PmxBlog']['action'][1] == 'remove')
	{
		if((isOwner() && !isBlogLocked()) || AllowedTo('admin_forum'))
		{
			PmxBlogDeleteBlog($uid);
			// clear the Blog totals cache
			cache_put_data('PmxBlogTotals', null, -1);
			redirectexit('action=pmxblog;sa=list');
		}
		else
			PmxBlog_Error($txt['PmxBlog_access_err_title'], $txt['PmxBlog_bloglocked_message'], $scripturl . '?action=pmxblog;sa=manager;set');
	}

	// Manage Categorie ?
	elseif($context['PmxBlog']['action'][1] == 'cat')
	{
		if(isOwner() || AllowedTo('admin_forum'))
		{
			if(isset($_GET['upd']))
			{
				$cats = $_POST;
        $len = count($cats['id']);
				for($i = 0; $i < $len; $i++)
				{
					// update categorie
					if($cats['chgtype'][$i] == 'update')
						$smcFunc['db_query']('', '
							UPDATE {db_prefix}pmxblog_categories
							SET name = {string:name}, corder = {int:corder}, depth = {int:depth}
							WHERE owner = {int:owner} AND ID = {int:cid}',
							array('name' => stripslashes($cats['name'][$i]),
							'corder' => $cats['corder'][$i],
							'depth' => $cats['depth'][$i],
							'owner' => $uid,
							'cid' => $cats['id'][$i]
							)
						);

					// insert
					elseif($cats['chgtype'][$i] == 'insert')
						$smcFunc['db_insert']('',
							'{db_prefix}pmxblog_categories',
							array('owner' => 'int', 'name' => 'string', 'corder' => 'int', 'depth' => 'int'),
							array($uid, stripslashes($cats['name'][$i]), $cats['corder'][$i], $cats['depth'][$i]),
							array('ID', 'owner', 'corder')
						);

					// delete
					elseif($cats['chgtype'][$i] == 'delete')
					{
						$request = $smcFunc['db_query']('', '
							SELECT ID
							FROM {db_prefix}pmxblog_content
							WHERE owner = {int:owner} AND categorie = {int:categorie}',
							array(
								'owner' => $uid,
								'categorie' => $cats['id'][$i]
							)
						);
						// remove the categorie form content
						if($row = $smcFunc['db_fetch_assoc']($request))
						{
							$smcFunc['db_query']('', '
								UPDATE {db_prefix}pmxblog_content
								SET categorie = 0
								WHERE owner = {int:owner} AND ID = {int:cid}',
								array(
									'owner' => $uid,
									'cid' => $row['ID']
								)
							);
							$smcFunc['db_free_result']($request);
						}
						// now delete the categorie
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}pmxblog_categories
							WHERE owner = {int:owner} AND ID = {int:cid}',
							array(
								'owner' => $uid,
								'cid' => $cats['id'][$i]
							)
						);
					}
				}

				unset($_GET['upd']);
				redirectexit(http_build_query($_GET, '', ';'));
			}
		}
		else
			PmxBlog_Error($txt['PmxBlog_access_err_title'], $txt['PmxBlog_bloglocked_message'], $scripturl . '?action=pmxblog;sa=manager;setup');
	}

// Get the categories
	$context['PmxBlog']['categorie'] = array();
	$request = $smcFunc['db_query']('', '
			SELECT ID, name, corder, depth
			FROM {db_prefix}pmxblog_categories
			WHERE owner = {int:owner}
			ORDER BY corder',
		array(
			'owner' => $uid,
		)
	);
	if($smcFunc['db_num_rows']($request) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($request))
			$context['PmxBlog']['categorie'][] = array(
				'id' => $row['ID'],
				'name' => $row['name'],
				'corder' => $row['corder'],
				'depth' => $row['depth'],
			);
		$smcFunc['db_free_result']($request);
	}

	$context['page_title'] = $txt['PmxBlog_manager_settings_title'];
	$context['PmxBlog']['page_link'] = array(
		'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
		'name' => $txt['PmxBlog_manager_settings_title']);
	loadTemplate('PmxBlogSettings');

	$cameFrom = !empty($_GET['cfr']) ? ';cfr='. $_GET['cfr'] : '';

	$context['PmxBlog']['setting_tabs'] = array(
		'title' => $txt['PmxBlog_manager_settings_title'],
		'tabs' => array(
			'settings' => array(
				'title' => $txt['PmxBlog_manager_setcom_title'],
				'href' => $scripturl . '?action=pmxblog;sa=manager;setup'. $cameFrom . $context['PmxBlog']['UserLink'],
				'is_selected' => $context['PmxBlog']['action'][1] == '',
				'is_enabled' => true,
			),
			'categorie' => array(
				'title' => $txt['PmxBlog_manager_setcat_title'],
				'href' => $scripturl . '?action=pmxblog;sa=manager;setup=cat'. $cameFrom . $context['PmxBlog']['UserLink'],
				'is_selected' => $context['PmxBlog']['action'][1] == 'cat',
				'is_enabled' => $context['PmxBlog']['Manager']['have_blog'] || AllowedTo('admin_forum'),
			),
			'removeblog' => array(
				'title' => $txt['PmxBlog_manager_remblog_title'],
				'href' => $scripturl . '?action=pmxblog;sa=manager;setup=delblog'. $cameFrom . $context['PmxBlog']['UserLink'],
				'is_selected' => $context['PmxBlog']['action'][1] == 'delblog',
				'is_enabled' => $context['PmxBlog']['Manager']['have_blog'] && (!isBlogLocked() || AllowedTo('admin_forum')),
			),
		),
	);
}

// Delete a blog and all data on this
function PmxBlogDeleteBlog($uid)
{
	global $context, $smcFunc;

	if(isOwner() || AllowedTo('admin_forum'))
	{
		$tmpcont = array();

		$request = $smcFunc['db_query']('', '
			SELECT ID
			FROM {db_prefix}pmxblog_content
			WHERE owner = {int:uid}',
			array('uid' => $uid)
		);
		if($smcFunc['db_num_rows']($request) > 0)
		{
			while($row = $smcFunc['db_fetch_assoc']($request))
				$tmpcont[] = $row['ID'];

			// remove content
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}pmxblog_content
				WHERE owner = {int:uid}',
				array('uid' => $uid)
			);

			// remove all comments
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}pmxblog_comments
				WHERE contID IN ({array_int:tmpcont})',
				array('tmpcont' => $tmpcont)
			);

			// remove comment log
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}pmxblog_cmnt_log
				WHERE contID IN ({array_int:contids})',
				array('contids' => $tmpcont)
			);

			$smcFunc['db_free_result']($request);
		}

		// remove content log
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pmxblog_cont_log
			WHERE owner = {int:uid}',
			array('uid' => $uid)
		);

		// remove tracking
		$request = $smcFunc['db_query']('', '
			SELECT owner, tracking
			FROM {db_prefix}pmxblog_manager',
			array()
		);
		if($smcFunc['db_num_rows']($request) > 0)
		{
			while($row = $smcFunc['db_fetch_assoc']($request))
				$tmp[$row['owner']] = $row['tracking'];
			foreach($tmp as $u => $v)
			{
				$tr = explode(':', $v);
				if(isset($tr[1]))
				{
					$val = explode(',', $tr[1]);
					if(in_array($uid, $val))
					{
						$val = array_diff($val, array($uid));
						$ntr = $tr[0].':'.implode(',', $val);

						$smcFunc['db_query']('', '
							UPDATE {db_prefix}pmxblog_manager
							SET tracking = {string:tracking}
							WHERE owner = {int:owner}',
							array('tracking' => $ntr,
								'owner' => $u)
						);
					}
				}
			}
			$smcFunc['db_free_result']($request);
		}

		// remove categories
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pmxblog_categories
			WHERE owner = {int:uid}',
			array('uid' => $uid)
		);

		// remove ratings
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pmxblog_ratings
			WHERE owner = {int:uid}',
			array('uid' => $uid)
		);

		// remove manager setting
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pmxblog_manager
			WHERE owner = {int:uid}',
			array('uid' => $uid)
		);
	}
}
?>