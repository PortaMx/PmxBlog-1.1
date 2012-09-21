<?php
// ----------------------------------------------------------
// -- PmxBlog.php                                          --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (!defined('SMF'))
	die('Hacking attempt...');

function PmxBlog_init($CalledFrom = '')
{
	if(defined('PMXBlog'))
		return;

	define('PMXBlog', 1);
	global $forum_version, $context, $modSettings, $settings, $user_info, $scripturl, $boardurl, $boarddir, $txt, $webmaster_email, $sourcedir, $smcFunc;

	// Load langfile by user's language.
	loadLanguage('PmxBlog');

	if(empty($modSettings['pmxblog_enabled']))
		return;

	$context['PmxBlog']['newCont'] = '&nbsp;<img src="' . $settings['default_images_url'] .'/PmxBlog/content_new.gif" alt="" style="margin-bottom:-2px" />';
	$context['PmxBlog']['newCmnt'] = '&nbsp;<img src="' . $settings['default_images_url'] .'/PmxBlog/comment_new.gif" alt="" style="margin-bottom:-2px" />';
	$context['PmxBlog']['action'] = array('','');
	$context['PmxBlog']['mode'] = '';
	$context['PmxBlog']['pagemode'] = '';
	$context['PmxBlog']['pageopt'] = '';
	$context['PmxBlog']['startpage'] = '';
	$context['PmxBlog']['UID'] = $user_info['id'];
	$context['PmxBlog']['tracknotify'] = 0;
	$context['PmxBlog']['trackself'] = 0;
	$context['PmxBlog']['tracklist'] = array();
	$context['PmxBlog']['UserLink'] = '';
	$context['PmxBlog']['blog_acs'] = array();
	$context['PmxBlog']['blog_rd_acs'] = array();
	$context['PmxBlog']['blog_wr_acs'] = array();
	$context['PmxBlog']['page_link'] = '';
	$context['PmxBlog']['thumbnail_dir'] = $boarddir .'/editor_thumbnails';
	$context['PmxBlog']['thumbnail_url'] = $boardurl .'/editor_thumbnails';
	$context['PmxBlog']['upload_dir'] = '/editor_uploads';
	$context['PmxBlog']['images_dir'] = '/images';
	$context['PmxBlog']['Archivdate'] = '';
	$context['PmxBlog']['ResetArchivdate'] = '';
	$context['PmxBlog']['Moderate'] = '';
	$context['PmxBlog']['editholdtime'] = 60 * 60 * 2;	// isedit set after 2 hours
	$context['PmxBlog']['fontnames'] = array('xx-small' => '5pt', 'x-small' => '8pt', 'small' => '9pt', 'larger' => '11pt', 'medium' => '13pt', 'large' => '14pt', 'x-large' => '18pt', 'xx-large' => '24pt');

	// reset the Image cookie
	if(empty($_REQUEST['action']) || (isset($_REQUEST['action']) && $_REQUEST['action'] != 'pmxblog'))
		setcookie('PmxBlogImgcfg', '0', time() - 1000, '/');

	// load all settings
	if(($temp = cache_get_data('PmxBlogSettings', 10)) !== null)
	{
		list(
			$context['PmxBlog']['wysiwyg_edit'],
			$context['PmxBlog']['wysiwyg_comment'],
			$context['PmxBlog']['modgroups'],
			$context['PmxBlog']['theme_copyright'],
			$context['PmxBlog']['copyright'],
			$context['PmxBlog']['blogadmin'],
			$context['PmxBlog']['webmaster_ID'],
			$context['PmxBlog']['webmaster_name'],
			$context['PmxBlog']['webmaster_email'],
			$context['PmxBlog']['blog_acs'],
			$context['PmxBlog']['blog_rd_acs'],
			$context['PmxBlog']['blog_wr_acs'],
			$context['PmxBlog']['content_len'],
			$context['PmxBlog']['overview_pages'],
			$context['PmxBlog']['comment_pages'],
			$context['PmxBlog']['remove_links'],
			$context['PmxBlog']['censor_text'],
			$context['PmxBlog']['content_pages'],
			$context['PmxBlog']['remove_images'],
			$context['PmxBlog']['image_prefix'],
			$context['PmxBlog']['thumb_show'],
			$context['PmxBlog']['thumb_size'],
			$context['PmxBlog']['htmltags']) = $temp;
		unset($temp);
	}
	else
	{
		// get the settings
		$req = $smcFunc['db_query']('',
			'SELECT name, value
				FROM {db_prefix}pmxblog_settings',
			array()
		);
		if($smcFunc['db_num_rows']($req) > 0)
		{
			while($row = $smcFunc['db_fetch_assoc']($req))
			{
				if($row['name'] == 'settings')
					list(
						$context['PmxBlog']['content_len'],
						$context['PmxBlog']['overview_pages'],
						$context['PmxBlog']['comment_pages'],
						$context['PmxBlog']['remove_links'],
						$context['PmxBlog']['censor_text'],
						$context['PmxBlog']['content_pages'],
						$context['PmxBlog']['remove_images'],
						$context['PmxBlog']['image_prefix']) = explode(',', $row['value']);
				elseif($row['name'] == 'blog_acs')
					list($blog_acs,
						$blog_rd_acs,
						$blog_wr_acs) = explode(':', $row['value']);
				elseif($row['name'] == 'wysiwyg_edit')
					$context['PmxBlog']['wysiwyg_edit'] = unserialize($row['value']);
				elseif($row['name'] == 'wysiwyg_comment')
					$context['PmxBlog']['wysiwyg_comment'] = unserialize($row['value']);
				elseif($row['name'] == 'modgroups')
					$context['PmxBlog']['modgroups'] = unserialize($row['value']);
				else
					$context['PmxBlog'][$row['name']] = $row['value'];
			}
			$smcFunc['db_free_result']($req);

			// Prepare Blog access
			$context['PmxBlog']['blog_acs'] = preg_split('/,/', $blog_acs, -1, PREG_SPLIT_NO_EMPTY);
			$context['PmxBlog']['blog_rd_acs'] = preg_split('/,/', $blog_rd_acs, -1, PREG_SPLIT_NO_EMPTY);
			$context['PmxBlog']['blog_wr_acs'] = preg_split('/,/', $blog_wr_acs, -1, PREG_SPLIT_NO_EMPTY);
		}
		$context['PmxBlog']['theme_copyright'] = $context['PmxBlog']['copyright'].'<br />';
		$context['PmxBlog']['copyright'] = str_replace('<a', '<a class="nav"', $context['PmxBlog']['copyright']);

		// Get Blog notify Admin
		$req = $smcFunc['db_query']('',
			'SELECT id_member, real_name, email_address
				FROM {db_prefix}members
				WHERE id_member = {int:num}',
			array('num' => intval($context['PmxBlog']['blogadmin']))
		);
		$row = $smcFunc['db_fetch_assoc']($req);
		$smcFunc['db_free_result']($req);

		$context['PmxBlog']['webmaster_ID'] = $row['id_member'];
		$context['PmxBlog']['webmaster_name'] = $row['real_name'];
		$context['PmxBlog']['webmaster_email'] = $row['email_address'];

		$temp = array(
			$context['PmxBlog']['wysiwyg_edit'],
			$context['PmxBlog']['wysiwyg_comment'],
			$context['PmxBlog']['modgroups'],
			$context['PmxBlog']['theme_copyright'],
			$context['PmxBlog']['copyright'],
			$context['PmxBlog']['blogadmin'],
			$context['PmxBlog']['webmaster_ID'],
			$context['PmxBlog']['webmaster_name'],
			$context['PmxBlog']['webmaster_email'],
			$context['PmxBlog']['blog_acs'],
			$context['PmxBlog']['blog_rd_acs'],
			$context['PmxBlog']['blog_wr_acs'],
			$context['PmxBlog']['content_len'],
			$context['PmxBlog']['overview_pages'],
			$context['PmxBlog']['comment_pages'],
			$context['PmxBlog']['remove_links'],
			$context['PmxBlog']['censor_text'],
			$context['PmxBlog']['content_pages'],
			$context['PmxBlog']['remove_images'],
			$context['PmxBlog']['image_prefix'],
			$context['PmxBlog']['thumb_show'],
			$context['PmxBlog']['thumb_size'],
			$context['PmxBlog']['htmltags']
		);

		// cache the settings for one day
		cache_put_data('PmxBlogSettings', $temp, 86400);
		unset($temp);
	}

	// get total enabled Blogs and there entries
	if(($temp = cache_get_data('PmxBlogTotals', 10)) !== null && isset($temp[$user_info['id']]))
	{
		list(
			$context['PmxBlog']['total_blogs'],
			$context['PmxBlog']['total_entries']) = isset($temp[$user_info['id']]['#']) ? $temp[$user_info['id']]['#'] : array(0, 0);
		list(
			$context['PmxBlog']['blogexist'],
			$context['PmxBlog']['blog_enabled'],
			$context['PmxBlog']['blog_locked']) = isset($temp[$user_info['id']]['&']) ? $temp[$user_info['id']]['&'] : array(0, 0, 0);
		unset($temp);
	}
	else
	{
		$total_blogs = 0;
		$total_entries = 0;
		$req = $smcFunc['db_query']('', '
				SELECT COUNT(*) AS Cont, m.blogenabled, m.bloglocked, m.owner
				FROM {db_prefix}pmxblog_manager AS m
				LEFT JOIN {db_prefix}pmxblog_content AS c ON (m.owner = c.owner AND c.published = 1)
				LEFT JOIN {db_prefix}members AS mem ON (m.owner = mem.id_member)
				WHERE (c.ID IS NULL OR c.allow_view = 0
					OR (c.allow_view = 1 AND {int:idmem} > 0)
					OR (c.allow_view = 2 AND {string:s_idmem} IN (mem.buddy_list))
					OR (c.allow_view = 3 AND c.owner = {int:idmem})
					OR c.owner = {int:idmem})
					GROUP BY m.owner',
			array(
				'idmem' => $user_info['id'],
				's_idmem' => (string) $user_info['id']
			)
		);

		if($smcFunc['db_num_rows']($req) > 0)
		{
			while($row = $smcFunc['db_fetch_assoc']($req))
			{
				$total_blogs += intval(empty($row['bloglocked']) && !empty($row['blogenabled']));
				$total_entries += (empty($row['bloglocked']) && !empty($row['blogenabled']) ? $row['Cont'] : 0);
				$temp[$row['owner']]['&'] = array(
          1,
					$row['blogenabled'],
					$row['bloglocked'],
				);
			}
			$smcFunc['db_free_result']($req);

			// cache the Blog totals for one day
			$temp[$user_info['id']]['#'] = array($total_blogs, $total_entries);
			cache_put_data('PmxBlogTotals', $temp, 86400);

			list(
				$context['PmxBlog']['total_blogs'],
				$context['PmxBlog']['total_entries']) = isset($temp[$user_info['id']]['#']) ? $temp[$user_info['id']]['#'] : array(0, 0);
			list(
				$context['PmxBlog']['blogexist'],
				$context['PmxBlog']['blog_enabled'],
				$context['PmxBlog']['blog_locked']) = isset($temp[$user_info['id']]['&']) ? $temp[$user_info['id']]['&'] : array(0, 0, 0);
			unset($temp);
		}
	}

	$tmp = isset($_REQUEST['action']) && $_REQUEST['action'] == 'pmxblog' && isset($_REQUEST['sa']) && in_array($_REQUEST['sa'], array('admin', 'view', 'manager'));
	if(empty($tmp))
		setcookie('PmxBlogArchiveDate', 0);

	if(isset($_REQUEST['action']) && $_REQUEST['action'] != 'jseditor')
		setcookie('PmxBlogSmileys', 0);
}

/**
* get domain and path for cookies
*/
function pmx_getcookparts()
{
	global $boardurl, $modSettings;

	$url = pmx_parse_url($boardurl);

	// local cookie?
	if(empty($url['path']) || empty($modSettings['localCookies']))
		$url['path'] = '';
	$url['path'] .= '/';

	// global cookie?
	if(!empty($modSettings['globalCookies']) && preg_match('~^\d{1,3}(\.\d{1,3}){3}$~', $url['host']) == 0 && preg_match('~(?:[^\.]+\.)?([^\.]{2,}\..+)\z~i', $url['host'], $parts) == 1)
		$url['host'] = '.'. $parts[1];
	elseif(empty($modSettings['localCookies']) && empty($modSettings['globalCookies']))
		$url['host'] = '';
	elseif(!isset($url['host']) || strpos($url['host'], '.') === false)
		$url['host'] = '';

	return $url;
}

function PmxBlog()
{
	global $context, $sourcedir, $txt, $scripturl, $settings, $modSettings, $options, $user_info, $smcFunc;

	// redirect to boardindex if blog disabled
	if(empty($modSettings['pmxblog_enabled']))
		redirectexit();

	// exit on follow actions or wireless
	if(WIRELESS || isset($_REQUEST['xml']) || isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('dlattach', 'jsoption', '.xml', 'xmlhttp', 'verificationcode')))
		return;

	if(isset($_SESSION['PmxBlog_Request']['curr']))
		$_SESSION['PmxBlog_Request']['last'] = $_SESSION['PmxBlog_Request']['curr'];
	$_SESSION['PmxBlog_Request']['curr'] = $_SERVER['QUERY_STRING'];

	// clear the captcha session
	if(isset($_SESSION['PmxBlog_captcha']) && !in_array(str_replace(';$', '', $_SERVER['QUERY_STRING']) , $_SESSION['PmxBlog_captcha']['request']))
		unset($_SESSION['PmxBlog_captcha']);

	// add PmxBlog styles to header
	if(file_exists($settings['theme_dir'] .'/pmxblog_core.css'))
		$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="'. $settings['theme_url']. '/pmxblog_core.css" />';

	$parts = pmx_getcookparts();
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/PmxBlog.css" />
	<script type="text/javascript"><!-- // --><![CDATA[
		var PmxBlogSideBar = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: '. (empty($options['collapse_PmxBlogSideBar']) ? 'false' : 'true') .',
			aSwappableContainers: [
				\'upshrinkPmxBlogSideBar\'
			],
			aSwapImages: [
				{
					sId: \'upshrinkImgPmxBlogSideBar\',
					srcCollapsed: \''. $settings['default_images_url'] .'/PmxBlog/cat_moveleft.gif\',
					altCollapsed: '. (JavaScriptEscape($txt['PmxBlog_collapse_SB'])) .',
					srcExpanded: \''. $settings['default_images_url'] .'/PmxBlog/cat_moveright.gif\',
					altExpanded: '. (JavaScriptEscape($txt['PmxBlog_collapse_SB'])) .'
				}
			],
			oThemeOptions: {
				bUseThemeSettings: true,
				sOptionName: \'collapse_PmxBlogSideBar\',
				sSessionVar: '. (JavaScriptEscape($context['session_var'])) .',
				sSessionId: '. (JavaScriptEscape($context['session_id'])) .'
			},
		});
	// ]]></script>';

	if(isset($_GET['uid']))
	{
		$uid = (int) $_GET['uid'];
		$context['PmxBlog']['UID'] = $uid;
		$context['PmxBlog']['UserLink'] = ';uid='.$uid;
	}

	// get hidden content
	$context['PmxBlog']['hiddencontent'] = '';
	$request = $smcFunc['db_query']('', '
			SELECT owner, COUNT(ID) AS totcont
			FROM {db_prefix}pmxblog_content
			GROUP BY owner',
		array()
	);
	while($row = $smcFunc['db_fetch_assoc']($request))
		$context['PmxBlog']['hiddencontent'][$row['owner']] = $row['totcont'];
	$smcFunc['db_free_result']($request);

	// get comment logging, high comment ID, nbr of comments and unread marker
	$req = $smcFunc['db_query']('', '
			SELECT c.ID, c.nbr_comment, c.owner, IFNULL(MAX(cm.ID), 0) as cHigh
			FROM {db_prefix}pmxblog_content AS c
			LEFT JOIN {db_prefix}pmxblog_comments AS cm ON (c.ID = cm.contID)
			LEFT JOIN {db_prefix}members AS m ON (c.owner = m.id_member)
			WHERE c.published = 1
				AND (c.allow_view = 0
				OR (c.allow_view = 1 AND {int:idmem} > 0)
				OR (c.allow_view = 2 AND {string:s_idmem} IN (m.buddy_list))
				OR (c.allow_view = 3 AND c.owner = {int:idmem})
				OR (c.owner = {int:idmem}))
				GROUP BY c.ID',
		array(
			'idmem' => $user_info['id'],
			's_idmem' => (string) $user_info['id']
		)
	);
	if($smcFunc['db_num_rows']($req) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($req))
		{
			$context['PmxBlog']['cmnt_log'][$row['ID']] = array(
				'owner' => $row['owner'],
				'contID' => $row['ID'],
				'cmtID' => 0,
				'cmtHigh' => $row['cHigh'],
				'nbr_comment' => $row['nbr_comment'],
			);
		}
		$smcFunc['db_free_result']($req);
	}

	$req = $smcFunc['db_query']('', '
			SELECT contID, cmtID
			FROM {db_prefix}pmxblog_cmnt_log
			WHERE userID = {int:mem}',
		array('mem' => $user_info['id'])
	);
	if($smcFunc['db_num_rows']($req) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($req))
			$context['PmxBlog']['cmnt_log'][$row['contID']]['cmtID'] = $row['cmtID'];
		$smcFunc['db_free_result']($req);
	}

	// moderator switch?
	if(isModerator())
	{
		if(isset($_GET['mod']))
		{
			$_SESSION['PmxBlogModerate'] = (empty($_GET['mod']) ? 0 : base64_encode($user_info['id'] .'.'. $context['PmxBlog']['UID']));
      unset($_GET['mod']);
      redirectexit(http_build_query($_GET, '', ';'));
		}
		elseif(isset($_SESSION['PmxBlogModerate']) && !empty($_SESSION['PmxBlogModerate']))
		{
			$tmp = preg_split('/\./', base64_decode($_SESSION['PmxBlogModerate']), -1, PREG_SPLIT_NO_EMPTY);
			if(!empty($tmp) && count($tmp) == 2 && $tmp[0] == $user_info['id'])
				$context['PmxBlog']['Moderate'] = $tmp[1];
			else
				$_SESSION['PmxBlogModerate'] = 0;
		}
	}

	// check the archive cookie / archive or categorie request
	if(isset($_GET['arch']) && is_numeric($_GET['arch']))
	{
		if(empty($_GET['arch']))
			setcookie('PmxBlogArchiveDate', 0);
		else
		{
			$tmp = getdate($_GET['arch']);
			$year = date('Y', forum_time());
			if($tmp['year'] <= $year && $tmp['year'] > 2006)
			{
				$context['PmxBlog']['Archivdate'] = $tmp;
				setcookie('PmxBlogArchiveDate', $context['PmxBlog']['UID'] .'.'. mktime($tmp['hours'], $tmp['minutes'], $tmp['seconds'], $tmp['mon'], $tmp['mday'], $tmp['year']));
			}
		}
	}
	elseif(isset($_COOKIE['PmxBlogArchiveDate']) && !empty($_COOKIE['PmxBlogArchiveDate']) && is_numeric($_COOKIE['PmxBlogArchiveDate']))
	{
		$tmp = preg_split('/\./', $_COOKIE['PmxBlogArchiveDate'], -1, PREG_SPLIT_NO_EMPTY);
		if(!empty($tmp) && count($tmp) == 2 && $tmp[0] == $context['PmxBlog']['UID'])
		{
			$tmp = getdate($tmp[1]);
			$year = (int) date('Y', forum_time());
			if($tmp['year'] <= $year && $tmp['year'] > 2006)
			{
				$context['PmxBlog']['Archivdate'] = $tmp;
				$cd = getdate(forum_time());
				$cd = mktime(0, 0, 0, $cd['mon'], $cd['mday'], $cd['year']);
				if(PmxCompareDate($cd, $context['PmxBlog']['Archivdate'], array('seconds', 'minutes', 'mday', 'mon', 'year')) != 0)
					$context['PmxBlog']['ResetArchivdate'] = ';arch=0';
			}
		}
		else
			setcookie('PmxBlogArchiveDate', 0);
	}

	// get subaction
	$context['PmxBlog']['subact'] = isset($_GET['sa']) ? $_GET['sa'] : 'none';

	if($context['PmxBlog']['subact'] == 'none')
	{
		if(AllowedToBlog('view'))
			$context['PmxBlog']['subact'] = 'list';
		else
			NotAllowed();
	}
	$context['PmxBlog']['mode'] = $context['PmxBlog']['subact'];

	// prepare navtabs and linktree
	if(!empty($context['PmxBlog']['Archivdate']))
	{
		$cd = getdate(forum_time());
		$cd = ';arch='. mktime(0, 0, 0, $cd['mon'], $cd['mday'], $cd['year']);
		if(PmxCompareDate($cd, $context['PmxBlog']['Archivdate'], array('seconds', 'minutes', 'mday', 'mon', 'year')) != 0)
			$context['PmxBlog']['ResetArchivdate'] = ';arch=0';
	}

	if(count($context['linktree']) > 1)
		array_pop($context['linktree']);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pmxblog',
		'name' => $txt['PmxBlog_blogbutton']
	);

	$context['PmxBlog']['nav_tabs'] = array(
		'tabs' => array(
			'showall' => array(
				'title' => $txt['PmxBlog_show_all_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=list',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogico1.gif',
				'is_selected' => $context['PmxBlog']['subact'] == 'list',
				'is_enabled' => AllowedToBlog('view', $user_info['id']),
			),
			'showunread' => array(
				'title' => $txt['PmxBlog_show_unread_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=unread',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogico3.gif',
				'is_selected' => $context['PmxBlog']['subact'] == 'unread',
				'is_enabled' => AllowedToBlog('view', $user_info['id']) && !$user_info['is_guest'],
			),
			'showtracked' => array(
				'title' => $txt['PmxBlog_show_tracked_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=tracked',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogico3.gif',
				'is_selected' => $context['PmxBlog']['subact'] == 'tracked',
				'is_enabled' => AllowedToBlog('view', $user_info['id']) && !$user_info['is_guest'],
			),
			'yourblogview' => array(
				'title' => $txt['PmxBlog_your_blog_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=view',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogico2.gif',
				'is_selected' => isOwner() && ($context['PmxBlog']['subact'] == 'view' || $context['PmxBlog']['subact'] == 'manager'),
				'is_enabled' => AllowedToBlog('manager', $user_info['id']) && !empty($context['PmxBlog']['blogexist']),
			),
			'yourblogsettings' => array(
				'title' => $txt['PmxBlog_set_newblog_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=manager;setup',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogico2.gif',
				'is_selected' => $context['PmxBlog']['subact'] == 'manager' && isset($_GET['set']),
				'is_enabled' => AllowedToBlog('manager', $user_info['id']) && empty($context['PmxBlog']['blogexist']),
			),
			'blogadmin' => array(
				'title' => $txt['PmxBlog_admin_nav'],
				'href' => $scripturl . '?action=pmxblog;sa=admin',
				'image' => $settings['default_images_url'] .'/PmxBlog/blogadmin.gif',
				'is_selected' => $context['PmxBlog']['subact'] == 'admin',
				'is_enabled' => AllowedTo('admin_forum'),
			),
		),
	);

	// Admin ?
	if(substr($context['PmxBlog']['subact'],0,5)=='admin')
	{
		isAllowedTo('admin_forum');
		require_once($sourcedir.'/PmxBlogAdmin.php');
		PmxBlog_Admin();
	}
	else
	{
		// Get paging cookie
		if(isset($_COOKIE['PmxBlogPaging']))
			$pageinfo = $_COOKIE['PmxBlogPaging'];
		else
			$pageinfo = '0.0.0.';

		list($pagelist['blogpage'],
			$pagelist['contpage'],
			$pagelist['cmntpage'],
			$pagelist['lastaction']) = explode('.', $pageinfo);

		$page = null;
		if(isset($_GET['pg']))
			$page = $_GET['pg'];

		switch($context['PmxBlog']['subact'])
		{
			case 'manager':
			case 'view':
				if(isset($_REQUEST['cont']) || isset($_REQUEST['cmnt']))
					$pagelist['cmntpage'] = isset($page) ? $page : $pagelist['cmntpage'];
				else
				{
					$pagelist['cmntpage'] = 0;
					$pagelist['contpage'] = isset($page) ? $page : $pagelist['contpage'];
				}
			break;
			default:
				$pagelist['cmntpage'] = 0;
				$pagelist['contpage'] = 0;
				if(in_array($context['PmxBlog']['subact'], array('tracked', 'unread', 'view'))
					&& $pagelist['lastaction'] != $context['PmxBlog']['subact'])
					$pagelist['blogpage'] = 0;
				else
					$pagelist['blogpage'] = isset($page) ? $page : $pagelist['blogpage'];
		}
		$pagelist['lastaction'] = ($context['PmxBlog']['subact'] == 'manager' ? $pagelist['lastaction'] : $context['PmxBlog']['subact']);
		setcookie('PmxBlogPaging', implode('.', array_reverse($pagelist)));

		// Blogs overview ?
		if($context['PmxBlog']['subact'] == 'list' || $context['PmxBlog']['subact'] == 'unread' || $context['PmxBlog']['subact'] == 'tracked')
		{
			PmxBlogList($context['PmxBlog']['subact'], $pagelist);
			loadTemplate('PmxBlog');
		}
		elseif($context['PmxBlog']['subact'] == 'manager' || $context['PmxBlog']['subact'] == 'view')
		{
			if($context['PmxBlog']['UID'] > 0)
			{
				require_once($sourcedir.'/PmxBlogManager.php');
				PmxBlogManager($context['PmxBlog']['subact'], $pagelist);
			}
			else
				NotAllowed();
		}
		else
			PmxBlog_Error($txt['PmxBlog_unknown_err_title'], $txt['PmxBlog_unknown_err_msg'], $scripturl . '?action=pmxblog');
	}
}

// All Blog listing
function PmxBlogList($mode, $pagelist)
{
	global $context, $modSettings, $settings, $scripturl, $user_info, $txt, $smcFunc;

	isAllowedToBlog($mode);

	$context['PmxBlog']['action'][0] = 'list';
	$context['PmxBlog']['action'][1] = $mode;
	$context['PmxBlog']['sortmode'] = 0;

	if($context['PmxBlog']['action'][1] == 'unread')
		$context['page_title'] = $txt['PmxBlog_unread_title'];
	elseif($context['PmxBlog']['action'][1] == 'tracked')
		$context['page_title'] = $txt['PmxBlog_tracked_title'];
	else
		$context['page_title'] = $txt['PmxBlog_bloglist_title'];

	// Tracking change / Mark all read
	check_Track_MarkRD($context['PmxBlog']['UID']);

	// get users Tracklist if show tracked
	$TList = array();
	if($mode == 'tracked')
	{
		$request = $smcFunc['db_query']('',
			'SELECT owner, tracking FROM {db_prefix}pmxblog_manager',
			array()
		);
		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			$val = explode(':', $row['tracking']);
			if(isset($val))
			{
				$tl = explode(',', $val[1]);
				if(isset($tl) && in_array($user_info['id'], $tl))
					$TList[] = $row['owner'];
			}
		}
		$smcFunc['db_free_result']($request);
		$context['PmxBlog']['pagemode'] = 'tracked';
	}
	else
	{
		if($mode == 'unread')
			$context['PmxBlog']['pagemode'] = 'unread';
	}

	// get total views & content for all Blog owner
	$request = $smcFunc['db_query']('', '
		SELECT c.owner, SUM(c.views), COUNT(c.ID), MIN(c.ID), MAX(c.ID), SUM(c.nbr_comment)
		FROM {db_prefix}pmxblog_manager AS m
		LEFT JOIN {db_prefix}pmxblog_content AS c ON (c.owner = m.owner)
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.owner)
		WHERE c.published = 1
			AND (c.allow_view = 0
			OR (c.allow_view = 1 AND {int:idmem} > 0)
			OR (c.allow_view = 2 AND {string:s_idmem} IN (mem.buddy_list))
			OR (c.allow_view = 3 AND c.owner = {int:idmem})
			OR (c.owner = {int:idmem}))
		GROUP BY owner',
			array(
				's_idmem' => (string) $user_info['id'],
				'idmem' => $user_info['id']
			)
	);
	while($row = $smcFunc['db_fetch_row']($request))
	{
		$blogviews[$row[0]] = $row[1];
		$nbrCont[$row[0]] = $row[2];
		$cont_low_high[$row[0]] = ($row[3] == $row[4] ? $row[3] : $row[3].'-'.$row[4]);
		$nbrCmnt[$row[0]] = $row[5];
	}
	$smcFunc['db_free_result']($request);

	// get all Blog owner
	$request = $smcFunc['db_query']('',
		'SELECT t.*, t1.real_name, t1.gender, t1.avatar, t2.id_folder, t2.file_hash,
			IFNULL(t2.id_attach, {string:st_null}) AS attach, IFNULL(t2.filename, {string:st_null}) AS filename,
			IFNULL(t3.is_read, {string:st_null}) AS is_read,
			IFNULL(t4.online_color, {string:st_null}) AS online_color,
			IFNULL(t5.id_member, 0) AS logged
		FROM {db_prefix}pmxblog_manager AS t
		LEFT JOIN {db_prefix}members AS t1 ON (t1.id_member = t.owner)
		LEFT JOIN {db_prefix}attachments AS t2 ON (t2.id_member = t.owner)
		LEFT JOIN {db_prefix}pmxblog_cont_log AS t3 ON (t3.owner = t.owner AND t3.userID = {int:uid})
		LEFT JOIN {db_prefix}membergroups AS t4 ON(t1.id_group = t4.id_group OR t1.id_post_group = t4.id_group)
		LEFT JOIN {db_prefix}log_online AS t5 ON(t.owner = t5.id_member)
		GROUP BY t.owner
		ORDER BY t.owner',
		array('st_null' => '', 'uid' => $user_info['id'])
	);

	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		if($user_info['id'] == $row['owner'] || AllowedTo('admin_forum') || (isset($nbrCont[$row['owner']]) && $nbrCont[$row['owner']] > 0 && $row['blogenabled'] != 0 && $row['bloglocked'] == 0))
		{
			$newCmnt = 0;
			$context['PmxBlog']['total_locked'] = 0;
			$havehiddencont = 0;
			if(isset($context['PmxBlog']['hiddencontent'][$row['owner']]) && (AllowedTo('admin_forum') || isOwner($row['owner'])))
				$havehiddencont = $context['PmxBlog']['hiddencontent'][$row['owner']] > (isset($nbrCont[$row['owner']]) ? $nbrCont[$row['owner']] : 0);

			// get total views & content
			$is_new_cont = isset($nbrCont[$row['owner']]) ? !Is_Read_all($row['owner'], $row['is_read'], $cont_low_high[$row['owner']], $nbrCont[$row['owner']]) : false;

			if(isset($nbrCont[$row['owner']]) && !$is_new_cont)
				Pack_Readlist($row['owner'], $row['is_read'], $cont_low_high[$row['owner']]);

			// get total comments & unread comments
			if(!empty($context['PmxBlog']['cmnt_log']))
			{
				foreach($context['PmxBlog']['cmnt_log'] as $cLog)
					if(isset($cLog['owner']) && $cLog['owner'] == $row['owner'])
						$newCmnt += $cLog['cmtHigh'] > $cLog['cmtID'] ? 1 : 0;
			}

			if(($mode == 'unread' && ($is_new_cont || $newCmnt > 0)) || ($mode == 'tracked' && in_array($row['owner'], $TList)) || $mode == 'list')
			{
				if(!isset($nbrCmnt[$row['owner']]))
					$nbrCmnt[$row['owner']] = 0;

				$row['settings'] = (empty($row['settings']) ? '111' : $row['settings']);
				$row['settings'] .= (strlen($row['settings']) == 2 ? '1' : '');

				$context['PmxBlog']['bloglist'][] = array(
					'userid' => $row['owner'],
					'blogname' => $row['blogname'],
					'blogdesc' => $row['blogdesc'],
					'blogenabled' => $row['blogenabled'],
					'bloglocked' => $row['bloglocked'],
					'username' => isset($row['real_name']) ? $row['real_name'] : '&nbsp;',
					'avatar' => PmxBlogAvatar($row['filename'], $row['file_hash'], $row['attach'], $row['avatar']),
					'gender' => !empty($row['gender']) ? '<img src="'. $settings['default_images_url'] .'/'. ($row['gender'] == 1 ? 'Male' : 'Female') .'.gif" alt="" border="0" />' : '',
					'onlineColor' => empty($row['online_color']) ? '' : ($row['logged'] ? ' style="color:'.$row['online_color'].';"' : ''),
					'blogcreated' => $row['blogcreated'],
					'settings' => $row['settings'],
					'tracking' => $user_info['is_guest'] ? 0 : intval(in_array($user_info['id'], $context['PmxBlog']['tracklist'][$row['owner']])),
					'tracks' => count(preg_split('/\,/', substr(strstr($row['tracking'], ':'), 1), -1, PREG_SPLIT_NO_EMPTY)),
					'blograting' => $row['blograting'],
					'blogvotes' => $row['blogvotes'],
					'blogviews' => isset($blogviews[$row['owner']]) ? $blogviews[$row['owner']] : 0,
					'is_read' => $row['is_read'],
					'is_new_cont' => $is_new_cont ? $context['PmxBlog']['newCont'] : '',
					'is_new_cmnt' => !empty($newCmnt),
					'nbr_content' => (!empty($nbrCont[$row['owner']]) ? $nbrCont[$row['owner']] : 0),
					'nbr_comment' => ($user_info['is_guest'] || $newCmnt == 0 ? Content_button('comment', $nbrCmnt[$row['owner']]) : Content_button('comment_new', $nbrCmnt[$row['owner']])),
					'hiddencont' => !empty($havehiddencont) && empty($nbrCont[$row['owner']])
				);
				$context['PmxBlog']['total_locked'] += ($row['bloglocked'] == 0 ? 0 : 1);
			}
		}
	}
	$smcFunc['db_free_result']($request);

	if(isset($_GET['sort']))
	{
		$context['PmxBlog']['sortmode'] = $_GET['sort'];
		setcookie('PmxBlogSortmode', $context['PmxBlog']['sortmode']);
	}
	elseif(isset($_COOKIE['PmxBlogSortmode']))
		$context['PmxBlog']['sortmode'] = $_COOKIE['PmxBlogSortmode'];

	if(isset($context['PmxBlog']['bloglist']))
	{
		switch ($context['PmxBlog']['sortmode'])
		{
			case 1:
				usort($context['PmxBlog']['bloglist'], 'ContentSort');
			break;
			case 2:
				usort($context['PmxBlog']['bloglist'], 'ViewSort');
			break;
			case 3:
				usort($context['PmxBlog']['bloglist'], 'RatingSort');
			break;
			default:
				usort($context['PmxBlog']['bloglist'], 'UserSort');
		}
	}

	// create the pageindex
	$top = $scripturl.'?'.$_SERVER['QUERY_STRING'];
	if(!strpos($top,';#') === false)
		$top = substr($top, 0, strpos($top,';#'));
	$context['PmxBlog']['pagetop'] = '<a href="'.$top.'#top"><img src="'.$settings['default_images_url'].'/PmxBlog/cat_moveup.gif" alt="" title="Top" /></a>';
	$context['PmxBlog']['pagebot'] = '<a href="'.$top.'#bot"><img src="'.$settings['default_images_url'].'/PmxBlog/cat_movedown.gif" alt="" title="Bottom" /></a>';

	$context['PmxBlog']['startpage'] = $pagelist['blogpage'];
	if(isset($context['PmxBlog']['bloglist']) && count($context['PmxBlog']['bloglist']) - $context['PmxBlog']['total_locked'] > $context['PmxBlog']['overview_pages'])
	{
		$context['PmxBlog']['pageindex'] = $txt['pages'].': '.
			str_replace(';start=', ';pg=', constructPageIndex($scripturl.'?action=pmxblog;sa='.$context['PmxBlog']['mode'].$context['PmxBlog']['pagemode'].$context['PmxBlog']['pageopt']. getOwnerLink($context['PmxBlog']['UID']),
					$context['PmxBlog']['startpage'],
					count($context['PmxBlog']['bloglist']) - $context['PmxBlog']['total_locked'],
					$context['PmxBlog']['overview_pages']));
	}
}

// sort Bloglist .. by name
function UserSort($a, $b)
{
	return strcasecmp($a['username'], $b['username']);
}
// .. by content count
function ContentSort($a, $b)
{
	return ($a['nbr_content'] <= $b['nbr_content']);
}
// .. by view count
function ViewSort($a, $b)
{
	return ($a['blogviews'] <= $b['blogviews']);
}
// .. by rating & votes
function RatingSort($a, $b)
{
	return ($a['blograting'] <= $b['blograting'] && $a['blogvotes'] <= $b['blogvotes']);
}

// get Rating for content
function getRating($contrates)
{
	$rateval = preg_split('/,/', $contrates, -1, PREG_SPLIT_NO_EMPTY);
	$ratecount = count($rateval);
	$total = 0;
	foreach($rateval as $val)
		$total += $val;
	if($ratecount > 0 && $total > 0)
		return floor($total/$ratecount);
	else
		return 0;
}

// get Rating for blogs
function getBlogRating($contrates)
{
	$rateval = preg_split('/,/', $contrates, -1, PREG_SPLIT_NO_EMPTY);
	$ratecount = count($rateval);
	$total = 0;
	foreach($rateval as $val)
		$total += $val;
	if($ratecount > 0 && $total > 0)
		return round(($total / $ratecount) * 10,1);
	else
		return 0.00;
}

// Get all Blog Data for a user
function getUserData($userID)
{
	global $context, $settings, $user_info, $txt, $smcFunc;

	check_Track_MarkRD($userID);	// get Tracklist

	$request = $smcFunc['db_query']('', '
			SELECT blogenabled, bloglocked
			FROM {db_prefix}pmxblog_manager
			WHERE owner = {int:uid}',
		array('uid' => $userID)
	);
	if($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['PmxBlog'][$userID]['blogenabled'] = $row['blogenabled'];
		$context['PmxBlog'][$userID]['bloglocked'] = $row['bloglocked'];
		$smcFunc['db_free_result']($request);
	}

	$blogviews = 0;
	$nbrCont = 0;
	$nbr_cmnt = 0;
	$newCmnt = 0;
	$contHigh = 0;
	$contLow = 0;

	$request = $smcFunc['db_query']('', '
			SELECT SUM(c.views), COUNT(c.ID), SUM(c.nbr_comment), MAX(c.ID), MIN(c.ID), c.ID
			FROM {db_prefix}pmxblog_content AS c
			LEFT JOIN {db_prefix}members AS m ON(m.id_member = c.owner)
			WHERE c.published = 1 AND c.owner = {int:uid}
			AND (c.allow_view = 0
			OR (c.allow_view = 1 AND {int:idmem} > 0)
			OR (c.allow_view = 2 AND (c.owner = {int:idmem} OR {string:s_idmem} IN (m.buddy_list)))
			OR (c.allow_view = 3 AND c.owner = {int:idmem}))
			GROUP BY c.ID',
		array(
			'idmem' => $user_info['id'],
			's_idmem' => (string) $user_info['id'],
			'uid' => $userID,
		)
	);

	while($row = $smcFunc['db_fetch_row']($request))
	{
		$blogviews += $row[0];
		$nbrCont += $row[1];
		$nbr_cmnt += $row[2];
		$contHigh = ($row[3] > $contHigh ? $row[3] : $contHigh);
		$contLow = $row[4];
		if(isset($context['PmxBlog']['cmnt_log'][$row[5]]))
			$newCmnt += $context['PmxBlog']['cmnt_log'][$row[5]]['cmtHigh'] > $context['PmxBlog']['cmnt_log'][$row[5]]['cmtID'] ? 1 : 0;
	}
	$smcFunc['db_free_result']($request);
	$cont_low_high = ($contLow == $contHigh ? $contLow : $contLow.'-'.$contHigh);

	$request = $smcFunc['db_query']('',
		'SELECT t.*, t1.real_name, t1.gender, t1.avatar, t2.id_folder, t2.file_hash,
				IFNULL(t4.online_color, {string:st_null}) AS online_color,
				IFNULL(t2.id_attach, {string:st_null}) AS attach,
				IFNULL(t2.filename, {string:st_null}) AS filename,
				IFNULL(t5.id_member, 0) AS logged,
				IFNULL(t6.is_read, {string:st_null}) AS is_read
			FROM {db_prefix}members AS t1
			LEFT JOIN {db_prefix}attachments AS t2 ON (t1.id_member = t2.id_member)
			LEFT JOIN {db_prefix}membergroups AS t4 ON(t1.id_group = t4.id_group OR t1.id_post_group = t4.id_group)
			LEFT JOIN {db_prefix}log_online AS t5 ON(t1.id_member = t5.id_member)
			LEFT JOIN {db_prefix}pmxblog_cont_log AS t6 ON (t6.owner = t1.id_member and t6.userID = {int:idmem})
			LEFT JOIN {db_prefix}pmxblog_manager AS t ON (t1.id_member = t.owner)
			WHERE t1.ID_MEMBER = {int:uid}',
		array('st_null' => '',
			'uid' => $userID,
			'idmem' => $user_info['id']
			)
	);

	if($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['settings'] = (empty($row['settings']) ? '111' : $row['settings']);
		$row['settings'] .= (strlen($row['settings']) == 2 ? '1' : '');

		$havehiddencont = 0;
		if(isset($context['PmxBlog']['hiddencontent'][$row['owner']]))
			$havehiddencont = $context['PmxBlog']['hiddencontent'][$row['owner']] > $nbrCont;

		$context['PmxBlog']['Manager'] = array(
			'userid' => is_null($row['owner']) ? $userID : $row['owner'],
			'blogname' => empty($row['blogname']) ? $row['real_name']."'s Blog" : $row['blogname'],
			'blogdesc' => empty($row['blogdesc']) ? $txt['PmxBlog_default_desc'] : $row['blogdesc'],
			'showarchive' => is_null($row['showarchive']) ? 1 : $row['showarchive'],
			'showcategories' => is_null($row['showcategories']) ? 1 : $row['showcategories'],
			'showcalendar' => is_null($row['showcalendar']) ? 1 : $row['showcalendar'],
			'hidebaronedit' => is_null($row['hidebaronedit']) ? 0 : $row['hidebaronedit'],
			'blogcreated' => is_null($row['blogcreated']) ? forum_time() : $row['blogcreated'],
			'blogenabled' => is_null($row['blogenabled']) ? 0 : $row['blogenabled'],
			'bloglocked' => is_null($row['bloglocked']) ? 0 : $row['bloglocked'],
			'settings' => $row['settings'],
			'tracking' => $user_info['is_guest'] ? 0 : (isset($context['PmxBlog']['tracklist'][$row['owner']]) ? intval(in_array($user_info['id'], $context['PmxBlog']['tracklist'][$row['owner']])) : 0),
			'tracks' => count(preg_split('/\,/', substr(strstr($row['tracking'], ':'), 1), -1, PREG_SPLIT_NO_EMPTY)),
			'blograting' => $row['blograting'],
			'blogvotes' => $row['blogvotes'],
			'blogviews' => !empty($blogviews) ? $blogviews : 0,
			'is_new_cmnt' => $newCmnt,
			'cont_low_high' => is_null($cont_low_high) ? 0 : $cont_low_high,
			'is_read' => $row['is_read'],
			'is_new_cont' => !empty($nbrCont) ? !Is_Read_all($row['owner'], $row['is_read'], $cont_low_high, $nbrCont) : false,
			'username' => isset($row['real_name']) ? $row['real_name'] : '&nbsp;',
			'onlineColor' => is_null($row['online_color']) ? '' : ($row['logged'] ? ' style="color:'.$row['online_color'].';"' : ''),
			'avatar' => PmxBlogAvatar($row['filename'], $row['file_hash'], $row['attach'], $row['avatar']),
			'gender' => !empty($row['gender']) ? '<img src="'. $settings['default_images_url'] .'/'. ($row['gender'] == 1 ? 'Male' : 'Female') .'.gif" alt="" border="0" />' : '',
			'have_blog' => is_null($row['owner']) ? false : true,
			'have_cont' => !empty($nbrCont),
			'nbr_content' => $nbrCont,
			'nbr_comment' => ($user_info['is_guest'] || $newCmnt == 0 ? Content_button('comment', $nbr_cmnt) : Content_button('comment_new', $nbr_cmnt)),
			'hiddencont' => !empty($havehiddencont) && empty($nbrCont[$row['owner']])
		);
	}
	$smcFunc['db_free_result']($request);
}

Function PrepSaveBody($value)
{
	global $context, $boardurl;

	$buf = DropJavascript($value);
	$buf = trim($buf);

	return $buf;
}

Function PrepLoadBody($value, $edit = false)
{
	global $context;

	$mode = substr($value,0, 3);
	if($mode == '<1>' || $mode == '<0>')
		$value = substr($value, 3);

	if(!$edit)
	{
		parsesmileys($value);
		if ($context['PmxBlog']['censor_text'] == 1)
			censorText($value);
		$value = RemoveLinks($value);
	}
	return $value;
}

// remove javascript from content/comments
function DropJavascript($content)
{
	if(AllowedTo('admin_forum'))
		return $content;

	$scriptcount = preg_match_all('!<(script)[^>]*>.+?</\\1>!si', $content, $matches);
	if($scriptcount != 0)
	{
		for($i=0; $i < $scriptcount; $i++)
		{
			$script = $matches[0][$i];
			$content = str_replace($script, '', $content);
		}
	}
	return $content;
}

// get word cont for post_teaser.
function PmxBlog_word_count($text)
{
	return count(preg_split("/\s+/", un_htmlspecialchars(strip_tags($text)), -1, PREG_SPLIT_NO_EMPTY));
}

// Post teaser (shorten posts by given wordcount).
function Post_Teaser($content)
{
	global $context, $settings, $modSettings, $boarddir, $boardurl;

	// Define post teaser tags
	$blocks = 'p|li|dt|dd|address|form|pre|td|div|span|code|blockquote|pmxteaser';
	$teasemark = array('<pmxteaser>', '</pmxteaser>');
	$matches = null;
	$matches2 = null;
	$auto_close = array();
	$wordcount = $context['PmxBlog']['content_len'];

	$Oldmode = substr($content,0, 3);
	if($Oldmode == '<1>' || $Oldmode == '<0>')
		$content = substr($content, 3);

	// set outside marker
	$content = $teasemark[0]. $content .$teasemark[1];

	$word_count = PmxBlog_word_count($content);
	preg_match_all("!.*?<($blocks)[^>]*>.+?</\\1>!si", $content, $matches);
	$matches = $matches[0];
	$block_count = count($matches);

	$current_word_count = 0;
	$block_word_count = array();
	$done = false;
	for ($i = 0; $current_word_count < $wordcount && $i < $block_count && empty($done);)
	{
		$block_word_count[$i] = PmxBlog_word_count($matches[$i]);
		$current_word_count += $block_word_count[$i];

		if ($current_word_count > $wordcount)
		{
			$current_word_count -= $block_word_count[$i];
			$block_word_count[$i] = 0;

			// calculate tags inside a block
			$f = preg_match_all('!<*[^>]*>!s', $matches[$i], $tmp);
			$tmp = $tmp[0];
			$matches[$i] = '';
			$t = 0;
			while($t < $f && empty($done))
			{
				$tcnt = PmxBlog_word_count($tmp[$t]);
				if($current_word_count + $tcnt < $wordcount)
				{
					$current_word_count += $tcnt;
					$block_word_count[$i] += $tcnt;
					$matches[$i] .= $tmp[$t];
				}
				elseif($tmp[$t]{0} != '<')
				{
					while(PmxBlog_word_count($tmp[$t]) + $block_word_count[$i] > $wordcount)
						$tmp[$t] = substr($tmp[$t], 0, strrpos($tmp[$t], ' '));
					$matches[$i] .= $tmp[$t] .' ...';
					$done = true;
				}
				$t++;
			}
		}
		$i++;
	}

	while($i > 0 && !isset($block_word_count[$i]))
		$i--;

	if ($current_word_count >= $wordcount && $i > 0)
	{
		$this_block_distance = $current_word_count - $wordcount;
		$last_block_distance = $wordcount - ($current_word_count - $block_word_count[$i]);
		if ($this_block_distance > $last_block_distance)
			$i--;
	}

	$new_content = $content;
	$tease = false;
	if($block_count == 0)
	{
		$tease = strpos($new_content, '<!--more-->');
		if(!$tease)
		{
			$words = PmxBlog_word_count($new_content);
			if($words > $wordcount)
			{
				$tmp = $new_content;
				while(PmxBlog_word_count($tmp) > $wordcount)
					$tmp = substr($tmp, 0, strrpos($tmp, ' '));
				$tease = strlen($tmp);
			}
		}
		if($tease)
		{
			$teasepos = strpos($new_content, '<div style="page-break-after: always;">');
			if($teasepos === false)
				$new_content = trim(substr($new_content, 0, $tease));
			else
				$new_content = trim(substr($new_content, 0, $teasepos));
				while(substr($new_content, -6) == '<br />')
					$new_content = substr($new_content, 0, strlen($new_content) - 6) .' ...';
		}
	}
	elseif($i <= $block_count)
	{
		for ($j = 0; $j <= $i;)
		{
			if(isset($matches[$j]))
			{
				$tease = strpos($matches[$j], '<!--more-->');
				if(!$tease)
				{
					$words = PmxBlog_word_count($matches[$j]);
					if($words > $wordcount)
					{
						$tmp = $matches[$j];
						while(PmxBlog_word_count($tmp) > $wordcount)
							$tmp = substr($tmp, 0, strrpos($tmp, ' '));
						$tease = strlen($tmp);
					}
				}
				if($tease)
				{
					$teasepos = strpos($new_content, '<div style="page-break-after: always;">');
					if($teasepos === false)
						$tmp = trim(substr($matches[$j], 0, $tease));
					else
						$tmp = trim(substr($matches[$j], 0, $teasepos));
					while(substr($tmp, -6) == '<br />')
						$tmp = substr($tmp, 0, strlen($tmp) - 6);
					$matches[$j] = $tmp .' ...';

					preg_match_all('!<(?:[a-zA-Z1-9]+)[^>]*>!i', $matches[$j], $matches2);
					$matches2 = $matches2[0];
					foreach ($matches2 as $id => $element)
					{
						if (preg_match('!^<([a-zA-Z1-9]+)[^>]*/>$!i', $element))
						{
							unset($matches2[$id]);
							continue;
						}
						$element = preg_replace('!^<([a-zA-Z1-9]+)[^>]*>$!i', "$1", $element);
						$auto_close[] = $element;
					}
					while($j <= $i)
					{
						$j++;
						if(isset($matches[$j]))
							unset($matches[$j]);
					}
				}
				else
				{
					preg_match_all('!<(?:[a-zA-Z1-9]+)[^>]*>!i', $matches[$j], $matches2);
					$matches2 = $matches2[0];
					foreach ($matches2 as $id => $element)
					{
						if (preg_match('!^<([a-zA-Z1-9]+)[^>]*/>$!i', $element))
						{
							unset($matches2[$id]);
							continue;
						}
						$element = preg_replace('!^<([a-zA-Z1-9]+)[^>]*>$!i', "$1", $element);
						$auto_close[] = $element;
					}
				}
			}
			$j++;
		}

		$new_content = '';
		for ($j = 0; $j <= $i; $j++)
		{
			if(isset($matches[$j]))
			{
				$temp = $matches[$j];
				foreach ($auto_close as $id => $element)
				{
					$pos = strpos(" " . $temp, "</$element>");
					if ($pos)
					{
						$temp = substr_replace($temp, '', $pos, strlen("</$element>"));
						unset($auto_close[$id]);
					}
				}
				$new_content .= $matches[$j];
			}
		}
		$auto_close = array_reverse($auto_close);
		foreach ($auto_close as $element)
			$new_content .= "</$element>";
	}

	$new_content = str_replace($teasemark, array('', ''), $new_content);
	$context['PmxBlog']['is_teased'] = $tease;

	// replace images for previev
	$content = RemoveLinks($new_content);

	if(!empty($context['PmxBlog']['thumb_show']))
	{
		$imgcount = preg_match_all('/<img[^>]*>/i', $content, $matches);
		if($imgcount != 0)
		{
			for($i = 0; $i < $imgcount; $i++)
			{
				$img = $matches[0][$i];
				preg_match('/src?=?\"[^\"]*\"/i', $img, $part);
				$part[0] = urldecode($part[0]);
				if(stristr($part[0], $modSettings['smileys_url']) === false)
				{
					$remdata = array('/style?=?\"[^\"]*\"/i', '/width?=?\"[^\"]*\"/i', '/height?=?\"[^\"]*\"/i');
					$newimg = preg_replace($remdata, '', $img);
					$url = preg_split('/\"/', $part[0]);

					$filename = '';
					$is_local = true;
					$thbsize = explode(',', $context['PmxBlog']['thumb_size']);

					if(stristr($url[1], $context['PmxBlog']['upload_dir']) !== false || stristr($url[1], $settings['default_images_url']) !== false)
					{
						$filename = strrchr($url[1], '/');
						$path = str_replace($boardurl, '', substr($url[1], 0, -strlen($filename)));
						if(strrchr($path, '/') != $context['PmxBlog']['images_dir'])
						{
							$custpath = substr($path, (int) strpos($path, $context['PmxBlog']['upload_dir']));
							$path = $boarddir . $custpath;
						}
						else
							$path = $boarddir . $context['PmxBlog']['upload_dir'] . $context['PmxBlog']['images_dir'];
					}
					else
					{
						$is_local = false;
						$filename = strrchr($url[1], '/');
						$path = $context['PmxBlog']['thumbnail_dir'];
					}

					$thumb_exist = false;
					$thumbfName = substr($filename, 0, strrpos($filename, '.')) .'_thumb_'. dechex(crc32($filename . $context['PmxBlog']['UID'])) .'.png';
					$thumbfName = str_replace(' ', '_', urldecode($thumbfName));

					if(file_exists($context['PmxBlog']['thumbnail_dir'] . $thumbfName))
					{
						$sizes = getimagesize($context['PmxBlog']['thumbnail_dir'] . $thumbfName);
						if(!empty($sizes) && $sizes[0] <= $thbsize[0] && $sizes[1] <= $thbsize[1])
						{
							$thumb_exist = true;
							$newimg = preg_replace('/src?=?\"[^\"]*\"/i', 'src="'.$context['PmxBlog']['thumbnail_url'].$thumbfName.'"', $newimg);
						}
					}
					elseif(empty($is_local))
					{
						$extImg = ParseImageUrl($url[1]);
						$im = @imagecreatefromstring($extImg);
						if($im !== false)
						{
							@imagepng($im, $path . $filename);
							@imagedestroy($im);
							$newimg = preg_replace('/src?=?\"[^\"]*\"/i', 'src="'.$context['PmxBlog']['thumbnail_url'].$thumbfName.'"', $newimg);
						}
						else
							$filename = '';
					}

					if(empty($thumb_exist) && !empty($filename))
					{
						$thumb_filename = MakeThumbnail($path, $context['PmxBlog']['thumbnail_dir'], $filename, $thbsize[0], $thbsize[1]);
						if(!empty($thumb_filename))
						{
							if(empty($is_local))
								unlink($context['PmxBlog']['thumbnail_dir'] . $filename);
							$newimg = preg_replace('/src?=?\"[^\"]*\"/i', 'src="'.$context['PmxBlog']['thumbnail_url'].$thumb_filename.'"', $newimg);
						}
						else
							$newimg = preg_replace('/src?=?\"[^\"]*\"/i', 'src="'.$settings['default_images_url'].'/PmxBlog/image_temp.gif"', $newimg);
					}
					elseif(empty($thumb_exist))
						$newimg = preg_replace('/src?=?\"[^\"]*\"/i', 'src="'.$settings['default_images_url'].'/PmxBlog/image_temp.gif"', $newimg);

					$content = str_replace($img, $newimg, $content);
				}
			}
		}
	}

	parsesmileys($content);
	if ($context['PmxBlog']['censor_text'] == 1)
		censorText($content);

	return $content;
}

// create thumbnail for preview
function MakeThumbnail($sPath, $dPath, $fName, $max_width, $max_height)
{
	global $context, $gd2;

	$result = '';
	$default_formats = array(
		'1' => 'gif',
		'2' => 'jpeg',
		'3' => 'png',
	);

	// Is GD installed....?
	$testGD = get_extension_funcs('gd');
	if(!empty($testGD) && !empty($max_width) && !empty($max_height))
	{
		$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
		unset($testGD);

		$newfName = str_replace(' ', '_', urldecode(strrchr($fName, '/')));
		$destName = $dPath . substr($newfName, 0, strrpos($newfName, '.')) .'_thumb_'. dechex(crc32($fName . $context['PmxBlog']['UID'])) .'.png';

		@ini_set('memory_limit', '48M');
		$sizes = getimagesize($sPath . $fName);
		if(!empty($sizes))
		{
			// is it one of the formats supported?
			if(isset($default_formats[$sizes[2]]) && function_exists('imagecreatefrom' . $default_formats[$sizes[2]]))
			{
				$imagecreatefrom = 'imagecreatefrom' . $default_formats[$sizes[2]];
				if($src_img = @$imagecreatefrom($sPath.$fName))
				{
					ThumbResize($src_img, $destName, imagesx($src_img), imagesy($src_img), $max_width, $max_height);
					if(file_exists($destName))
						$result = strrchr($destName, '/');
				}
			}
		}
	}
	return $result;
}

function ThumbResize($src_img, $destName, $src_width, $src_height, $max_width, $max_height)
{
	global $gd2;

	// Determine whether to resize to max width or to max height
	if (!empty($max_width) && !empty($max_height))
	{
		if (!empty($max_width) && (empty($max_height) || $src_height * $max_width / $src_width <= $max_height))
		{
			$dst_width = $max_width;
			$dst_height = floor($src_height * $max_width / $src_width);
		}
		elseif (!empty($max_height))
		{
			$dst_width = floor($src_width * $max_height / $src_height);
			$dst_height = $max_height;
		}

		// Don't bother resizing if it's already smaller...
		if (!empty($dst_width) && !empty($dst_height) && ($dst_width < $src_width || $dst_height < $src_height))
		{
			// (make a true color image, because it just looks better for resizing.)
			if ($gd2)
			{
				$dst_img = imagecreatetruecolor($dst_width, $dst_height);
				imagealphablending($dst_img, false);
				if (function_exists('imagesavealpha'))
					imagesavealpha($dst_img, true);
				imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
			}
		}
		else
			$dst_img = $src_img;
	}
	else
		$dst_img = $src_img;

	imagepng($dst_img, $destName);
	imagedestroy($src_img);
	if ($dst_img != $src_img)
		imagedestroy($dst_img);
}

// replace &nbsp with space
function Strip_nsbp($value)
{
	return str_replace('&nbsp;', ' ', $value);
}

// Notify on tracked blogs
function SendTrackNotify($uid, $allow, $contID, $subject, $cmntPosterID = 0, $cmntPosterName = '', $cmntID = 0, $cmntSubj = '')
{
	global $context, $user_info, $scripturl, $sourcedir, $language, $txt, $smcFunc;

	if($user_info['is_guest'] && $cmntPosterName == '')
		return;

	// content notify ?
	if(empty($cmntID))
	{
		// check if notify send
		$req = $smcFunc['db_query']('', '
				SELECT notify
				FROM {db_prefix}pmxblog_content
				WHERE ID = {int:cid}',
			array('cid' => $contID)
		);

		$row = $smcFunc['db_fetch_assoc']($req);
		$smcFunc['db_free_result']($req);

		// exit if notify send
		if(!empty($row['notify']))
			return;

		// update notify
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}pmxblog_content
				SET notify = 1
				WHERE ID = {int:cid}',
			array('cid' => $contID)
		);
	}

	// check if blog enabled
	if(isBlogEnabled() && !isBlogLocked() && AllowedToBlog('view'))
	{
		// get all user they have a track on the User
		$havetrack = null;
		$acsCheck = null;

		if(isset($context['PmxBlog']['tracklist'][$uid]))
		{
			foreach($context['PmxBlog']['tracklist'][$uid] as $TRuser)
			{
				if(isset($context['PmxBlog']['trackmode'][$TRuser]))
				{
					$trBlog = explode('|', $context['PmxBlog']['trackmode'][$TRuser]);
					if($trBlog[1] > 0 && ($cmntID == 0 || $cmntID > 0 && $TRuser != $cmntPosterID))
					{
						$havetrack[$TRuser] = array('mailto' => $trBlog[1], 'isCmnt' => intval($cmntID != 0), 'lang' => '', 'dosend' => false);
						$acsCheck[] = $TRuser;
					}
				}
			}
		}

		// if notify on comments if enabled, but not notify to itself ..
		if(isset($context['PmxBlog']['trackmode'][$uid]) && $cmntID > 0 && $uid != $cmntPosterID)
		{
			$trCmnt = explode('|', $context['PmxBlog']['trackmode'][$uid]);
			if($trCmnt[0] > 0)
			{
				$havetrack[$uid] = array('mailto' => $trCmnt[0], 'isCmnt' => 2, 'lang' => '', 'dosend' => false);
				$acsCheck[] = $uid;
			}
		}

		// check if notify acepted, get the language and email address
		if(!empty($acsCheck))
		{
			$ulist = implode(',', $acsCheck);

			$req = $smcFunc['db_query']('',
				'SELECT a.owner, m.email_address, m.lngfile, mb.buddy_list
					FROM {db_prefix}pmxblog_manager AS a
					LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
					LEFT JOIN {db_prefix}members AS mb ON (mb.id_member = {int:uid})
					WHERE a.owner IN ({string:ulist})
					GROUP BY a.owner ORDER BY lngfile',
				array('uid' => $uid, 'ulist' => implode(',', $acsCheck))
			);

			while($row = $smcFunc['db_fetch_assoc']($req))
			{
				$bl = explode(',', $row['buddy_list']);
				if($allow < 2 || ($allow == 2 && in_array($row['owner'], $bl)) || $row['owner'] == $uid)
				{
					$havetrack[$row['owner']]['lang'] = $row['lngfile'];
					$havetrack[$row['owner']]['mailto'] = ($havetrack[$row['owner']]['mailto'] == 1 ? $row['email_address'] : $row['owner']);
					$havetrack[$row['owner']]['dosend'] = true;
				}
			}
			$smcFunc['db_free_result']($req);

			// now send notifies
			require_once($sourcedir . '/Subs-Post.php');

			$clng = '*';
			foreach($havetrack as $t)
			{
				if($t['dosend'])
				{
					if($clng != $t['lang'])
						$clng = loadLanguage('PmxBlogNotify', (empty($t['lang']) ? $language : $t['lang']), false);

					switch ($t['isCmnt'])
					{
						case 0:		// content tracking
							$MsgText = Strip_nsbp(sprintf($txt['PmxBlog_tracknotify_msg'], $subject, $context['PmxBlog']['Manager']['username'],
								$scripturl.'?action=pmxblog;sa=view;cont='.$contID.';uid='.$uid,
								$context['PmxBlog']['webmaster_name']));
						break;

						case 1:		// comment track
							$MsgText = Strip_nsbp(sprintf($txt['PmxBlog_tracknotify_cmnt_msg'], $cmntSubj, $subject, $cmntPosterName,
								$context['PmxBlog']['Manager']['username'],
								$scripturl.'?action=pmxblog;sa=view;cont='.$contID.';uid='.$uid,
								$scripturl.'?action=pmxblog;sa=view;cont='.$contID.';cmnt='.$cmntID.';uid='.$uid.'#cmnt'.$cmntID,
								$context['PmxBlog']['webmaster_name']));
						break;

						case 2:		// self comment track
							$MsgText = Strip_nsbp(sprintf($txt['PmxBlog_tracknotify_self_msg'], $cmntPosterName, $cmntSubj, $subject,
								$scripturl.'?action=pmxblog;sa=view;cont='.$contID.';cmnt='.$cmntID.';uid='.$uid.'#cmnt'.$cmntID,
								$context['PmxBlog']['webmaster_name']));
						break;
					}

					if(is_numeric($t['mailto']))
					{
						$PMto = array('to' => array($t['mailto']), 'bcc' => array());
						$PMfrom = array('id' => $context['PmxBlog']['webmaster_ID'], 'name' => $context['PmxBlog']['webmaster_name'], 'username' => $context['PmxBlog']['webmaster_name']);
						sendpm($PMto, ($t['isCmnt'] < 2 ? $txt['PmxBlog_tracknotify_subject'] : $txt['PmxBlog_trackwatch_subject']), $MsgText, false, $PMfrom);
					}
					else
						sendmail($t['mailto'], ($t['isCmnt'] < 2 ? $txt['PmxBlog_tracknotify_subject'] : $txt['PmxBlog_trackwatch_subject']), $MsgText);
				}
			}
		}
	}
}

// Trackin on/off, Mark Read
function check_Track_MarkRD($owner)
{
	global $context, $user_info, $smcFunc;

	// Get tracklist
	$req = $smcFunc['db_query']('',
		'SELECT owner,tracking
			FROM {db_prefix}pmxblog_manager',
		array()
	);

	while($row = $smcFunc['db_fetch_assoc']($req))
	{
		$part = explode(':', $row['tracking']);
		$context['PmxBlog']['trackmode'][$row['owner']] = isset($part[0]) ? $part[0] : '0|0';
		$context['PmxBlog']['tracklist'][$row['owner']] = isset($part[1]) ? preg_split('/,/', $part[1], -1, PREG_SPLIT_NO_EMPTY) : array();
	}
	$smcFunc['db_free_result']($req);

	if(!$user_info['is_guest'])
	{
		// Mark all read
		if(isset($_GET['mkrd']))
		{
			Mark_All_Read($owner);
      unset($_GET['mkrd']);
			if(isset($_GET['sa']) && $_GET['sa'] == 'list' && isset($_GET['uid']))
				unset($_GET['uid']);
      redirectexit(http_build_query($_GET, '', ';'));
		}

		// Tracking change
		if(isset($_GET['track']))
		{
			if(in_array($user_info['id'], $context['PmxBlog']['tracklist'][$owner]))
				$context['PmxBlog']['tracklist'][$owner] = array_diff($context['PmxBlog']['tracklist'][$owner], array($user_info['id']));
			else
				$context['PmxBlog']['tracklist'][$owner][] = $user_info['id'];

			$smcFunc['db_query']('',
				'UPDATE {db_prefix}pmxblog_manager
					SET tracking = {string:newtrack}
					WHERE owner = {int:owner}',
				array('newtrack' => $context['PmxBlog']['trackmode'][$owner] .':'. implode(',', $context['PmxBlog']['tracklist'][$owner]),
						'owner' => $owner)
			);

      unset($_GET['track']);
			if(isset($_GET['sa']) && $_GET['sa'] == 'list' && isset($_GET['uid']))
				unset($_GET['uid']);
      redirectexit(http_build_query($_GET, '', ';'));
		}
	}
}

// Mark all content as read
function Mark_All_Read($owner)
{
	global $context, $user_info, $smcFunc;


	$req = $smcFunc['db_query']('',
		'SELECT MIN(c.ID), MAX(c.ID), l.is_read
			FROM {db_prefix}pmxblog_content AS c
			LEFT JOIN {db_prefix}pmxblog_cont_log AS l ON (c.owner = l.owner AND l.userID = {int:uid})
			WHERE c.owner = {int:owner}
			GROUP BY c.owner',
		array('uid' => $user_info['id'],
				'owner' =>  $owner)
	);

	if($smcFunc['db_num_rows']($req) > 0)
	{
		$row = $smcFunc['db_fetch_row']($req);
		$LH = $row[0].'-'.$row[1];
		$rdlist = $row[2];
		Pack_Readlist($owner, $rdlist, $LH);
	}
	$smcFunc['db_free_result']($req);

	// comments
	$result = array();
	$req = $smcFunc['db_query']('', '
		SELECT c.ID, IFNULL(MAX(m.ID), 0) as cHight
		FROM {db_prefix}pmxblog_content as c
		LEFT JOIN {db_prefix}pmxblog_comments as m  on (c.ID = m.contID)
		WHERE c.owner = {int:owner} AND c.ID IS NOT NULL
		GROUP BY m.contID',
		array('owner' => $owner)
	);

	if($smcFunc['db_num_rows']($req) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($req))
			$result[] = array(
				'contID' => $row['ID'],
				'cHight' => $row['cHight']
			);
		$smcFunc['db_free_result']($req);
	}

	foreach($result as $data)
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pmxblog_cmnt_log
			WHERE userID = {int:mem} and contID = {int:contId}',
			array('mem' => $user_info['id'], 'contId' => $data['contID'])
		);

		$smcFunc['db_insert']('', '
			{db_prefix}pmxblog_cmnt_log',
			array('userID' => 'int', 'contID' => 'int', 'cmtID' => 'int'),
			array($user_info['id'], $data['contID'], $data['cHight']),
			array('userID')
		);
	}
}

// Compress content readlist if all read
function Pack_Readlist($owner, $readlist, $LH)
{
	global $user_info, $smcFunc;

	if($user_info['is_guest'] || $owner == $user_info['id'])
		return;

	if($readlist != $LH)
	{
		$req = $smcFunc['db_query']('',
			'SELECT is_read
				FROM {db_prefix}pmxblog_cont_log
				WHERE owner = {int:owner} AND userID = {int:uid}',
			array('owner' =>  $owner,
				'uid' => $user_info['id'])
		);

		if($smcFunc['db_num_rows']($req) > 0)
		{
			$smcFunc['db_free_result']($req);

			$smcFunc['db_query']('',
				'UPDATE {db_prefix}pmxblog_cont_log
					SET is_read = {string:lowhigh}
					WHERE owner = {int:owner} AND userID = {int:uid}',
				array('owner' =>  $owner,
					'uid' => $user_info['id'],
					'lowhigh' => $LH)
			);
		}
		else
			$smcFunc['db_insert']('',
				'{db_prefix}pmxblog_cont_log',
				array('owner' => 'int', 'userID' => 'int', 'is_read' => 'string'),
				array($owner, $user_info['id'], $LH),
				array('userID', 'owner')
			);
	}
}

// Update readlist if entries view as single page
function Update_Readlist($readlist, $id, $uid)
{
	global $user_info, $smcFunc;

	if($user_info['is_guest'] || $uid == $user_info['id'])
		return;

	$new = array();
	if(isset($readlist) && !empty($readlist))
	{
		$old = array();
		$part = explode(',', $readlist);
		foreach($part as $lh)
		{
			$t = explode('-', $lh);
			if(!isset($t[1]))
				$t[1] = $t[0];
			$old = array_merge($old, $t);
		}

		$p=0;
		while($p < count($old))
		{
			if($id+1 == $old[$p])	// low
			{
				$new[] = array($id, $old[$p+1]);
				$id = -1;
				$p += 2;
			}
			elseif($id-1 == $old[$p+1])	// high
			{
				if(isset($old[$p+2]) && $old[$p+2] == $id+1)	// high = next low ?
				{
					$new[] = array($old[$p], $old[$p+3]);
					$id = -1;
					$p += 4;
				}
				else
				{
					$new[] = array($old[$p], $id);
					$id = -1;
					$p += 2;
				}
			}
			else
			{
				$new[] = ($old[$p] == $old[$p+1]) ? array($old[$p]) : array($old[$p], $old[$p+1]);
				$p += 2;
			}
		}
		if($id != -1)
			$new[] = array($id);
	}
	else
		$new[] = array($id);

	usort($new, 'ReadListSort');
	$newlist = '';
	foreach($new as $part)
		$newlist .= ','.implode('-', $part);
	$newlist = substr($newlist, 1);

	$req = $smcFunc['db_query']('',
		'SELECT owner
			FROM {db_prefix}pmxblog_cont_log
			WHERE owner = {int:owner} AND userID = {int:uid}',
		array('owner' =>  $uid,
			'uid' => $user_info['id'])
	);

	if($smcFunc['db_num_rows']($req) > 0)
	{
		$smcFunc['db_query']('',
			'UPDATE {db_prefix}pmxblog_cont_log
				SET is_read = {string:isread}
				WHERE owner = {int:owner} AND userID = {int:uid}',
			array('isread' => $newlist,
				'uid' => $user_info['id'],
				'owner' => $uid)
		);
		$smcFunc['db_free_result']($req);
	}
	else
		$smcFunc['db_insert']('',
			'{db_prefix}pmxblog_cont_log',
			array('owner' => 'int', 'userID' => 'int', 'is_read' => 'string-255'),
			array($uid, $user_info['id'], $newlist),
			array('owner', 'userID', 'contID')
		);
}

// check if a conten ID read
function Is_Read($uid, $readlist, $id)
{
	global $settings, $user_info;

	if($user_info['is_guest'] || $uid == $user_info['id'])
		return true;

	$isread = false;
	$part = explode(',', $readlist);
	foreach($part as $low_high)
	{
		$lh = explode('-', $low_high);
		if(isset($lh[1]))
			$isread = ($id >= $lh[0] && $id <= $lh[1] ? true : $isread);
		else
			$isread = ($id == $lh[0] ? true : $isread);
	}
	return $isread;
}

// check if any content unread
function Is_Read_all($uid, $readlist, $cont_LH, $nbr)
{
	global $settings, $user_info;

	if($user_info['is_guest'] || $uid == $user_info['id'])
		return true;

	if(empty($readlist) || $readlist == 0)
		return ($nbr == 0 ? true : false);
	else
	{
		$lh = explode('-', $cont_LH);
		$l = Is_Read($uid, $readlist, $lh[0]);				// check low
		$h = (isset($lh[1]) ? Is_Read($uid, $readlist, $lh[1]) : true);	// check high
		if($l && $h)	// second check, count ID's
		{
			$part = explode(',', $readlist);
			$n = 0;
			foreach($part as $low_high)
			{
				$lh=explode('-', $low_high);
				if(isset($lh[1]))
					$n += $lh[1]-$lh[0]+1;
				else
					$n++;
			}
			return ($n >= $nbr);
		}
		else
		return false;
	}
}

// remove external links (href, image) from body
function RemoveLinks($content)
{
	global $context, $boardurl, $settings, $txt;

	// check for Images
	if($context['PmxBlog']['remove_images'] == '1')
	{
		$imgcount = preg_match_all('/<img[^>]*>/i', $content, $matches);
		if($imgcount != 0)
		{
			for($i=0; $i < $imgcount; $i++)
			{
				$img = $matches[0][$i];
				$org = $img;
				preg_match('/src?=?\"[^\"]*\"/i', $img, $part);
				$url = preg_split('/\"/', $part[0]);
				$imageurl = $url[1];
				if(strpos($imageurl, '://') != false)
				{
					if(strpos(strtolower($imageurl), strtolower(strstr($boardurl, '/'))) === false)
						$content = str_replace($img, '<img style="cursor:help;" src="'.$settings['default_images_url'].'/PmxBlog/image_removed.gif" alt="" title="'. $imageurl .'" />', $content);
				}
			}
			unset($matches);
		}
	}

	// check for links
	if($context['PmxBlog']['remove_links'] == 1)
	{
		$linkcount = preg_match_all('!<(a)[^>]*>.+?</\\1>!si', $content, $matches);
		if($linkcount != 0)
		{
			for($i=0; $i < $linkcount; $i++)
			{
				$link = $matches[0][$i];
				preg_match('/href?=?\"[^\"]*\"/i', $link, $part);
				$url = preg_split('/\"/', $part[0]);
				$linkurl = $url[1];
				if(strpos($linkurl, '://') !== false)
				{
					if(strpos(strtolower($linkurl), strtolower(strstr($boardurl, '/'))) === false)
						$content = str_replace($link, '<img style="cursor:help;" src="'. $settings['default_images_url'].'/PmxBlog/link_removed.gif' .'" alt="*" title="'. $linkurl .'" />', $content);
				}
			}
		}
	}

	return $content;
}

// Get image for external server
function ParseImageUrl($url)
{
	// get host and domain from feedurl
	preg_match('@^(?:http://)?([^/|^?]+)@i', $url, $host);
	if(!isset($host[1]))
		return '';

	preg_match('@'. $host[1] .'(.*)@i', $url, $domain);
	if(!isset($domain[1]))
		return '';

	// prepare the http header
	$header = "GET ". $domain[1] ." HTTP/1.1\r\n";
  $header .= "Host: ". $host[1] ."\r\n";
	$header .= "Connection: Close\r\n\r\n";

	// open the socket
	$handle = @fsockopen($host[1], 80, $eNbr, $eStr, 5);
  if(!$handle)
		return '';

	// send http request
	fputs($handle, $header);

	// read the http response
	$content = '';
  while(!feof($handle))
    $content .= fgets($handle, 8092);
  fclose($handle);

	// split into headers and content.
	$parts = explode("\r\n\r\n",trim($content));
	if(!is_array($parts) or count($parts) < 2)
		return '';

	$body = '';
	foreach($parts as $ix => $value)
	{
		if($ix == 0)
			$head = trim($parts[$ix]);
		else
			$body .= $parts[$ix] ."\r\n\r\n";
	}
	$headers = preg_split('~\|~', str_replace(array("\n", "\r"), '|', strtolower($head)), -1, PREG_SPLIT_NO_EMPTY);
	unset($parts);
	unset($head);

	// check header if OK and/or chunked transfer
  $httpResposes = array('http/1.0 100 ok', 'http/1.1 100 ok', 'http/1.0 200 ok', 'http/1.1 200 ok');
	$ischunked = (in_array('transfer-encoding: chunked', $headers));
	if(in_array($headers[0], $httpResposes))
	{
		// chunked transfer ?
		if(!empty($ischunked))
			$body = trim(unchunkReq($body));
		else
			$body = trim($body);
		return $body;
	}
	else
    return '';
}

/**
* Unchunk http content.
* Returns unchunked content on success
*/
function unchunkReq($content = '')
{
	$result = '';
	if(strlen($content) > 0)
	{
		do
		{
			$content = rtrim($content);
			$pos = strpos($content, "\r\n");
			if($pos === false)
				return '';

			// get the chunk len
			$len = hexdec(substr($content, 0, $pos));
			if(!is_numeric($len) or $len < 0)
				return '';

			$result .= substr($content, ($pos + 2), $len);
			$content  = substr($content, ($len + $pos + 2));
			$check = trim($content);
		}
		while(!empty($check));
		unset($content);
	}
	return $result;
}

// check private right to show blog entries
function allowed_BlogCont($av, $bl, $own, $getRes = false)
{
	global $context, $user_info;
	$res = false;
	if((AllowedTo('admin_forum') && $context['PmxBlog']['mode'] == 'manager')
		|| $av == 0
		|| ($av == 1 && !$user_info['is_guest'])
		|| ($av == 2 && !$user_info['is_guest'] && in_array($user_info['id'], $bl))
		|| ($av == 3 && $user_info['id'] == $own)
		|| ($user_info['id'] == $own))
		$res = true;
	return $res;
}

// check right and show Error id fail
function isAllowedToBlog($act)
{
	if(!AllowedToBlog($act))
		NotAllowed();
}

function isOwner($userID = '')
{
	global $context, $user_info;

	if(!empty($userID))
		return ($user_info['id'] == $userID);
	else
		return ($user_info['id'] == $context['PmxBlog']['UID']);
}

function isModerator($owner = '')
{
	global $context, $user_info, $smcFunc;

	$access = false;
	if(!empty($owner))
	{
		if($owner == $user_info['id']) 		// no moderate own blog
			return false;

		// is a admin owner?
		$req = $smcFunc['db_query']('', '
			SELECT id_group, additional_groups
			FROM {db_prefix}members
			WHERE id_member = {int:mem}',
			array('mem' =>  $owner)
		);
		if($row = $smcFunc['db_fetch_assoc']($req))
		{
			$smcFunc['db_free_result']($req);
			if($row['id_group'] == 1 || in_array(1, explode(',', $row['additional_groups'])))
				return false;
		}
	}
	foreach($user_info['groups'] as $g)
		$access = (in_array($g, $context['PmxBlog']['modgroups']) ? true : $access);

	return $access;
}

function isBlogEnabled($memID = null)
{
	global $context;

	if($memID === null)
		$memID = $context['PmxBlog']['UID'];
	return (isset($context['PmxBlog'][$memID]['blogenabled']) && $context['PmxBlog'][$memID]['blogenabled'] != 0 ? true : false);
}

function isBlogLocked($memID = null)
{
	global $context;

	if($memID === null)
		$memID = $context['PmxBlog']['UID'];
	return (isset($context['PmxBlog'][$memID]['bloglocked']) && $context['PmxBlog'][$memID]['bloglocked'] != 0 ? true : false);
}

function getOwnerLink($userID)
{
	global $context, $user_info;

	return ($userID != $user_info['id'] ? ';uid='. $userID : '');
}

// check right for actions
function AllowedToBlog($act, $userID = '')
{
	global $context, $user_info, $txt;

	$access = (AllowedTo('admin_forum') ? true : false);
	if(!$access)
	{
		if($act == 'manager')
		{
			if($userID == '')
				$self = (!$user_info['is_guest'] && ((isset($_GET['uid']) && $_GET['uid'] == $user_info['id']) || !isset($_GET['uid'])));
			else
				$self = $userID == $user_info['id'];
			$groups = $self ? $context['PmxBlog']['blog_acs'] : array();
		}
		elseif(in_array($act, array('unread', 'tracked')))
			$groups = $user_info['is_guest'] ?  array() : $context['PmxBlog']['blog_rd_acs'];
		elseif(in_array($act, array('list', 'view')))
			$groups = $context['PmxBlog']['blog_rd_acs'];
		elseif($act == 'cmnt')
			$groups = $context['PmxBlog']['blog_wr_acs'];

		foreach($user_info['groups'] as $g)
			$access = (in_array($g, $groups) ? true : $access);
	}
	return $access;
}

// show logon or error if rights failed
function NotAllowed($msg = '')
{
	global $txt;

	is_not_guest($txt['PmxBlog_forbidden']);
	if($msg == '')
		fatal_error($txt['PmxBlog_forbidden']);
	else
		fatal_error($msg);
}

// find and resize avatar
function PmxBlogAvatar($filename, $file_hash, $attach, $avatar)
{
	global $context, $modSettings, $settings, $boarddir, $boardurl, $scripturl;

	$avatarsz = array();
	if(!empty($filename))
	{
    $ava_url = empty($modSettings['custom_avatar_enabled']) ? $scripturl . '?action=dlattach;attach='. $attach .';type=avatar' : $modSettings['custom_avatar_url'] .'/'. $filename;
		$ava_dir = empty($modSettings['custom_avatar_enabled']) ? $modSettings['attachmentUploadDir'] .'/'. (empty($file_hash) ? $filename : $attach .'_'. $file_hash) : $modSettings['custom_avatar_dir'] .'/'. $filename;
		$avatarsz = getimagesize($ava_dir);
	}
	elseif(!empty($avatar))
	{
		if(stristr($avatar, 'http://') === FALSE)
		{
			$ava_url = $modSettings['avatar_url'] .'/'. $avatar;
			$ava_dir = $modSettings['avatar_directory'] .'/'. $avatar;
			$avatarsz = getimagesize($ava_dir);
		}
		else
		{
			$extImg = ParseImageUrl($avatar);
			$im = @imagecreatefromstring($extImg);
			if($im !== false)
			{
				$ava_url = $avatar;
				$avatarsz = array(imagesx($im), imagesy($im));
				@imagedestroy($im);
			}
		}
	}

	if(empty($avatarsz))
	{
		$ava_url = $settings['default_images_url'].'/PmxBlog/blog.gif';
		$ava_dir = $settings['default_theme_dir'].'/images/PmxBlog/blog.gif';
		$avatarsz = getimagesize($ava_dir);
	}

	$resz = ($avatarsz[0] > 80 || $avatarsz[0] > 80 ? 80 / (($avatarsz[0] > $avatarsz[1]) ? $avatarsz[0] : $avatarsz[1]) : 1);
	$avatarsz[0] = floor($avatarsz[0] * $resz);
	$avatarsz[1] = floor($avatarsz[1] * $resz);

	return '<img src="'. $ava_url .'" width="'. $avatarsz[0] .'" height="'. $avatarsz[1] .'" alt="avatar" border="0" />';
}

// Get Categorie name by id
function GetCatname($i)
{
	global $context, $txt;
	if ($i == 0)
		$n = $txt['PmxBlog_nocat'];
	else
	{
		$n = '';
		if(!empty($context['PmxBlog']['categorie']))
		{
			foreach($context['PmxBlog']['categorie'] as $c)
				if($c['id'] == $i)
					$n = $c['name'];
		}
	}
	return $n;
}

// sort content readlist
function ReadListSort($a, $b)
{
	if(isset($a[1]) && isset($b[1]))
		return strcmp($a[0], $b[0]) && strcmp($a[1], $b[1]);
	else
		return strcmp($a[0], $b[0]);
}

// create grafical buttons
function make_button($name, $desc, $align = 'super')
{
	global $settings;
	return '<img src="' . $settings['default_images_url'] . $name .'.gif" alt="" /><span style="vertical-align:'. $align .';"><b>'. $desc .'</b></span>';
}

// create grafical buttons
function Content_button($name, $desc)
{
	global $settings;
	return $desc.' <img src="' . $settings['default_images_url'] .'/PmxBlog/'.$name .'.gif" alt="" style="margin-bottom:-2px;" />';
}

// create grafical buttons
function Title_button($name, $desc)
{
	global $settings;
	return '<img src="' . $settings['default_images_url'] .'/PmxBlog/'.$name .'.gif" alt="" style="margin-bottom:-2px;" /> '.$desc;
}

// load the editors
function EditContent_xhtml($content)
{
	global $context;

	if(getEditorAcs($context['PmxBlog']['wysiwyg_edit']) && ($context['PmxBlog']['Manager']['settings']{0} == '1' || (!isOwner() && isModerator())))
	{
		$content = ConvertFontSizeHtml($content);

		$oFCKeditor = new FCKeditor('body');
		$oFCKeditor->BasePath = str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) .'/fckeditor/');
		$oFCKeditor->Height = '450px';
		$oFCKeditor->ToolbarSet = 'Default';
		$oFCKeditor->Value = removeSmileysAlt($content);
		$oFCKeditor->Create();
	}
	else
	{
		$content = ConvertHtmlforBBC($content);

		$context['controls']['richedit'][$context['PmxBlog']['editor']]['value'] = $content;
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['rich_value'] = addcslashes(bbc_to_html($content), "'");
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['height'] = '250px';
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['width'] = '99%';
	}
}

function EditComment_xhtml($comment)
{
	global $context;

	if(getEditorAcs($context['PmxBlog']['wysiwyg_comment']) && ($context['PmxBlog']['Manager']['settings']{1} == '1' || (!isOwner() && isModerator())))
	{
		$oFCKeditor = new FCKeditor('body');
		$oFCKeditor->BasePath = str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) .'/fckeditor/');
		$oFCKeditor->Height = '250px';
		$oFCKeditor->ToolbarSet = 'Basic';
		$oFCKeditor->Value = removeSmileysAlt($comment);
		$oFCKeditor->Create();
	}
	else
	{
		$comment = Smileys_to_BBC($comment);
		$htmltags = 'div|span|p|code|blockquote|marquee';   // add more if need
		$comment = str_replace('\\"', '"', preg_replace('~(<('. $htmltags .')[^>]*>)(.+?)(</\\2>)~e', "str_replace(array('<', '>'), array('&lt;', '&gt;'), '\\1\\3\\4')", $comment));
		$comment = html_to_bbc($comment);

		$context['controls']['richedit'][$context['PmxBlog']['editor']]['value'] = $comment;
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['rich_value'] = addcslashes(bbc_to_html($comment), "'");
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['height'] = '150px';
		$context['controls']['richedit'][$context['PmxBlog']['editor']]['width'] = '99%';
	}
}

function Load_Wysiwyg($what)
{
	global $context, $user_info, $boarddir, $sourcedir, $options;

	if(isset($context['PortaMx']['settings']['xbarkeys']))
		$context['PortaMx']['settings']['xbarkeys'] = 0;		// disable PortaMx xBarKeys

	if($what == 'wysiwyg_edit')
		$userED = $context['PmxBlog']['Manager']['settings']{0} == '1' || (!isOwner() && isModerator());
	else
		$userED = $context['PmxBlog']['Manager']['settings']{1} == '1' || (!isOwner() && isModerator());

	if(getEditorAcs($context['PmxBlog'][$what]) && $userED)
	{
		//set ImagePrefix Cookie for xhtml editor
		if(!AllowedTo('admin_forum') && empty($context['PmxBlog']['image_prefix']))
			setcookie('PmxBlogImgcfg', '_member_/'. strval($user_info['id']), 0, '/');
		require_once($boarddir .'/fckeditor/fckeditor.php');
	}
	else
	{
		// create the SMF editor.
		require_once($sourcedir .'/Subs-Editor.php');

		$user_info['smiley_set'] = 'PortaMx';
		$context['smileys'] = '';
		$editorOptions = array(
			'id' => 'body',
			'value' => '',
			'width' => '100%',
			'height' => '100px',
			'labels' => array(),
			'preview_type' => 0,
		);
		create_control_richedit($editorOptions);
		$context['PmxBlog']['editor'] = $editorOptions['id'];
	}
}

// get access for the html editor
// get access for the html editor
function getEditorAcs($groups)
{
	global $user_info;

	$access = false;
	foreach($user_info['groups'] as $g)
		$access = (in_array($g, $groups) ? true : $access);

	return $access;
}

// Convert SMF pt size for html editor
function ConvertFontSizeHtml($content)
{
	global $context;

	while(preg_match_all('~(<(span)[^>]*>)(.*)(</\\2>)~iU', $content, $matches, PREG_PATTERN_ORDER) != 0)
	{
    $found = false;
		for($i = 0; $i < count($matches[1]); $i++)
		{
			// find size tags
			if(preg_match_all('~(style?=?.|class?=?.)(.+?)\"~', $matches[1][$i], $font, PREG_PATTERN_ORDER) != 0)
			{
				preg_match('~font-size?:?(.+?)\;~', $font[2][0], $size);
				if(empty($size) && count($font[2]) == 2)
					preg_match('~font-size?:?(.+?)\;~', $font[2][1], $size);
				if(!empty($size) && (count($font[0]) == 1 || (count($font[0]) == 2 && ($font[2][1] == 'bbc_size' || $font[2][0] == 'bbc_size'))))
				{
					$size = trim($size[1]);
					foreach($context['PmxBlog']['fontnames'] as $fname => $pt)
					{
						if($size == $pt)
						{
							$tmp = str_replace($size, $fname, $matches[0][$i]);
							if(count($font[0]) == 2 && ($font[2][1] == 'bbc_size' || $font[2][0] == 'bbc_size'))
								$tmp = str_replace($font[0][1], '', $tmp);
							$content = str_replace($matches[0][$i], $tmp, $content);
							$found = true;
						}
					}
				}
			}
		}
    if(empty($found))
      break;
	}
	return $content;
}

// Convert html tags for the BBC editor
function ConvertHtmlforBBC($content)
{
  global $context;

	$content = Smileys_to_BBC($content);												// convert smileys
	$content = str_replace(array("\r", "\n"), '', $content);		// remove cr/lf
	$content = preg_replace('~<hr.?\/>~', '[hr]', $content);		// <hr /> to bbc

	// marquee to [move]
	$content = str_replace('\\"', '"', preg_replace('~(<(marquee)[^>]*>)(.+?)(</\\2>)~e', "str_replace(array('\\1','\\4'), array('[move]', '[/move]'), '\\1\\3\\4')", $content));

	// convert the legal html tags
	while(preg_match_all('~(<('. $context['PmxBlog']['htmltags'] .')[^>]*>)(.*)(</\\2>)~iU', $content, $matches, PREG_PATTERN_ORDER) != 0)
	{
		for($i = 0; $i < count($matches[1]); $i++)
		{
			$tmp = '';
			if($matches[2][$i] == 'span')
			{
				// find size tags
				if(preg_match_all('~(style?=?.|class?=?.)(.+?)\"~', $matches[1][$i], $font, PREG_PATTERN_ORDER) != 0)
				{
					preg_match('~font-size?:?(.+?)\;~', $font[2][0], $size);
					if(empty($size) && count($font[2]) == 2)
						preg_match('~font-size?:?(.+?)\;~', $font[2][1], $size);
					if(!empty($size) && (count($font[0]) == 1 || (count($font[0]) == 2 && ($font[2][1] == 'bbc_size' || $font[2][0] == 'bbc_size'))))
					{
						$size = trim($size[1]);
						foreach($context['PmxBlog']['fontnames'] as $fname => $pt)
						{
							if($size == $fname)
							{
								$tmp = '[size='. $pt .']'. $matches[3][$i] .'[/size]';
								$content = str_replace($matches[0][$i], $tmp, $content);
							}
						}
					}
				}
			}
			if(empty($tmp))
			{
				$matches[1][$i] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $matches[1][$i]);
				$matches[4][$i] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $matches[4][$i]);
				$content = str_replace($matches[0][$i], $matches[1][$i] . $matches[3][$i] . $matches[4][$i], $content);
			}
		}
	}

	// page-break tag
	$content = str_replace('<!--more-->', '&lt;!--more--&gt;', $content);

	// smf do the rest
	$content = html_to_bbc($content);

	return $content;
}

// Remove smiley alt attribute
function removeSmileysAlt($content, $full = false)
{
	global $modSettings;

  $newalt = (empty($full) ? 'alt="*"' : '');
	preg_match_all('/<img[^>]*>/i', $content, $matches);
	foreach($matches[0] as $part)
	{
		if(stristr($part, $modSettings['smileys_url']) !== false)
			$content = str_replace($part, preg_replace('/alt?=?\"[^\"]*\"/i', $newalt, $part), $content);
	}
	return $content;
}

// convert smileys to BBC
function Smileys_to_BBC($content)
{
	global $modSettings;

	$smBBC = array(
		'smiley.gif' => ':)',
		'wink.gif' => ';)',
		'cheesy.gif' => ':D',
		'grin.gif' => ';D',
		'angry.gif' => '>:(',
		'sad.gif' => ':(',
		'shocked.gif' => ':o',
		'cool.gif' => '8)',
		'huh.gif' => '???',
		'rolleyes.gif' => '::)',
		'tongue.gif' => ':P',
		'embarrassed.gif' => ':-[',
		'lipsrsealed.gif' => ':-X',
		'undecided.gif' => ':-\\',
		'kiss.gif' => ':-*',
		'cry.gif' => ':\'(',
		'evil.gif' => '>:D',
		'azn.gif' => '^-^',
		'laugh.gif' => ':))',
		'afro.gif' => 'O0'
	);

	$content = str_replace('&nbsp;', ' ', removeSmileysAlt($content, true));
	preg_match_all('/<img[^>]*>/i', $content, $matches);
	foreach($matches[0] as $part)
	{
		if(stristr($part, $modSettings['smileys_url']) !== false)
		{
			preg_match('/src?=?\"[^\"]*\"/i', $part, $src);
			$fname = str_replace(array('/', '"'), '', strrchr($src[0], '/'));
			$content = (isset($smBBC[$fname]) ? str_replace($part, $smBBC[$fname], $content) : $content);
		}
	}
	return $content;
}

// compare two dates (y, m, d)
function PmxCompareDate($d1, $d2, $what)
{
	$result = 0;
	foreach($what as $key)
		$result = ($result == 0 ? ($d1[$key] == $d2[$key] ? 0 : ($d1[$key] < $d2[$key] ? -1 : 1)) : $result);

	return $result;
}

// show and log any error
function PmxBlog_Error($Ttl, $Msg, $Lnk)
{
	global $txt, $context, $modSettings;

	$context['PmxBlog_Error']['Title'] = $Ttl;
	$context['PmxBlog_Error']['Msg'] = $Msg;
	$context['PmxBlog_Error']['Link'] = $Lnk;
	loadTemplate('PmxBlog');
	$context['sub_template'] = 'PmxBlog_error';
	log_error('PmxBlog - '. $Ttl);
	obExit(null, null, true);
	exit();
}

// check hav a member a blog and is enabled and not locked
function checkMemberBlog($memid)
{
	global $modSettings, $smcFunc;

	if(empty($modSettings['pmxblog_enabled']))
		return false;

	if(($temp = cache_get_data('PmxBlogTotals', 10)) !== null && isset($temp[$memid]))
	{
		list(
			$blogexist,
			$blog_enabled,
			$blog_locked) = isset($temp[$memid]['&']) ? $temp[$memid]['&'] : array(0, 0, 0);
		unset($temp);
	}
	else
	{
		$req = $smcFunc['db_query']('', '
				SELECT blogenabled, bloglocked
				FROM {db_prefix}pmxblog_manager
				WHERE owner = {int:idmem}',
			array(
				'idmem' => $memid,
			)
		);

		$blogexist = 0;
		if($smcFunc['db_num_rows']($req) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($req);
			$smcFunc['db_free_result']($req);

			$blogexist = true;
			$blog_enabled = $row['blogenabled'];
			$blog_locked = $row['bloglocked'];
		}
	}
	return (!empty($blogexist) && !empty($blog_enabled) && empty($blog_locked));
}
?>