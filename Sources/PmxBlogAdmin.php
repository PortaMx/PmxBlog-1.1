<?php
// ----------------------------------------------------------
// -- PmxBlogAdmin.php                                     --
// ----------------------------------------------------------
// -- Version: 1.0 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2011 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (!defined('SMF'))
	die('Hacking attempt...');

// PmxBlog Admin settings
function PmxBlog_Admin()
{
	global $context, $txt, $scripturl, $smcFunc;

	if(isset($_GET['setup']) && $_GET['setup'] == 'upd')
	{
		list(
			$tmp['content_len'],
			$tmp['overview_pages'],
			$tmp['comment_pages'],
			$tmp['remove_links'],
			$tmp['censor_text'],
			$tmp['content_pages'],
			$tmp['remove_images'],
			$tmp['image_prefix']) = explode(',', '50,20,10,0,10,10,0,2');

		$valid_keys = array_unique(array_merge(array('blogadmin', 'thumb_show', 'thumb_size', 'htmltags', 'wysiwyg_edit', 'wysiwyg_comment', 'modgroups'), array_keys($tmp)));
		foreach ($_POST as $what => $value)
		{
			if($what != 'settings' && in_array($what, $valid_keys))
			{
				if($what == 'wysiwyg_edit' || $what == 'wysiwyg_comment' || $what == 'modgroups')
					$data[$what] = (empty($value) ? serialize(array()) : serialize($value));
				elseif($what == 'blogadmin')
					$blogadmin = $value;
				elseif($what == 'thumb_show')
					$thumb_show = $value;
				elseif($what == 'thumb_size')
					$thumb_size = $value;
				elseif($what == 'htmltags')
					$htmltags = $value;
				else
					$tmp[$what] = $value;
			}
		}
		// make setting string for DB insert
		$val='';
		foreach($tmp as $n => $v)
			$val = $v.','.$val;
		$val = substr($val, 0, strlen($val)-1);

		$dbinserts = array(
			'settings' => $val, 
			'blogadmin' => $blogadmin, 
			'thumb_show' => $thumb_show, 
			'thumb_size' => $thumb_size,
			'htmltags' => $htmltags,
			'wysiwyg_edit' => $data['wysiwyg_edit'],
			'wysiwyg_comment' => $data['wysiwyg_comment'],
			'modgroups' => $data['modgroups']
		);
		foreach($dbinserts as $key => $data)
		{
			$datset = array('name' => $key, 'value' => $data);
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}pmxblog_settings
					SET value = {string:value}
					WHERE name = {string:name}',
				$datset
			);
		}

		$oldthbsize = explode(',', $context['PmxBlog']['thumb_size']);
		$newthbsize = explode(',', $thumb_size);
		if(PmxCompareDate($oldthbsize, $newthbsize, array(0, 1)) != 0)
		{
			$path = $context['PmxBlog']['thumbnail_dir'] .'/';
			if($dh = @opendir($path))
			{
				while(($file = readdir($dh)) !== false)
					if(is_file($path . $file) && !in_array($file, array('.', '..', 'index.php')))
						@unlink($path . $file);
			}	  
		}

		// clear the settings cache
		cache_put_data('PmxBlogSettings', null, -1);
		redirectexit('action=pmxblog;sa=admin;setup');
	}
	elseif(isset($_GET['acs']) && $_GET['acs'] == 'upd')
	{
		$acs['blog_acs'] = array();
		$acs['blog_rd_acs'] = array();
		$acs['blog_wr_acs'] = array();
		foreach ($_POST as $what => $value)
		{
			$p = strpos($what, '_acs');
			if($p !== false)
				$acs[substr($what,0, $p+4)][] = $value;
		}
		// merge access groups for locking
		$acs['blog_wr_acs'] = array_unique(array_merge($acs['blog_wr_acs'], $acs['blog_acs']));
		$acs['blog_rd_acs'] = array_unique(array_merge($acs['blog_rd_acs'], $acs['blog_wr_acs']));

		// make accessgroup string for DB insert
		$acsgrp = implode(',', $acs['blog_acs']).':'.implode(',', $acs['blog_rd_acs']).':'.implode(',', $acs['blog_wr_acs']);
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}pmxblog_settings
				SET value = {string:value}
				WHERE name = {string:name}',
			array('value' => $acsgrp, 'name' => 'blog_acs')
		);

		// clear the settings cache
		cache_put_data('PmxBlogSettings', null, -1);
		redirectexit('action=pmxblog;sa=admin;acs');
	}

	// Get Admin users
	 $req = $smcFunc['db_query']('', '
			SELECT id_member, real_name, email_address
				FROM {db_prefix}members
				WHERE id_group = {string:admin_value}
				OR FIND_IN_SET({string:admin_value}, additional_groups) > 0
				ORDER BY id_member',
			array(
				'admin_value' => '1'
			)
	 );

	if($smcFunc['db_num_rows']($req) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($req))
		{
			$context['PmxBlog']['Admin_List'][] = array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'email' => $row['email_address']);
		}
		$smcFunc['db_free_result']($req);
	}

	// Get SMF User groups
	$context['PmxBlog']['SMF_groups'] = array(
		array('ID' => -1, 'Name' => $txt['PmxBlog_guests'], 'Typ' => 0),
		array('ID' => 0, 'Name' => $txt['PmxBlog_nogroup'], 'Typ' => 0)
		);

	$req = $smcFunc['db_query']('',
		'SELECT id_group, group_name, min_posts
			FROM {db_prefix}membergroups
			ORDER BY min_posts',
		array()
	);
	if($smcFunc['db_num_rows']($req) > 0)
	{
		while($row = $smcFunc['db_fetch_assoc']($req))
		{
			$context['PmxBlog']['SMF_groups'][] = array(
				'ID' => $row['id_group'],
				'Name' => $row['group_name'],
				'Typ' => $row['min_posts'] < 0 ? 0 : 1);
		}
		$smcFunc['db_free_result']($req);
	}

	// prepare tabs
	$curact = isset($_GET['acs']) ? 'acs' : 'setup';
	$context['PmxBlog']['admin_tabs'] = array(
		'title' => $txt['PmxBlog_settings_title'],
		'tabs' => array(
			'set' => array(
				'title' => $txt['PmxBlog_setcom_title'],
				'href' => $scripturl . '?action=pmxblog;sa=admin;setup'.$context['PmxBlog']['UserLink'],
				'is_selected' => $curact == 'setup',
				'is_enabled' => true,
			),
			'acs' => array(
				'title' => $txt['PmxBlog_setacs_title'],
				'href' => $scripturl . '?action=pmxblog;sa=admin;acs'.$context['PmxBlog']['UserLink'],
				'is_selected' => $curact == 'acs',
				'is_enabled' => true,
			),
		),
	);

	$context['page_title'] = $txt['PmxBlog_settings_title'];
	$context['PmxBlog']['page_link'] = array(
		'url' => $scripturl.'?'.$_SERVER['QUERY_STRING'],
		'name' => $txt['PmxBlog_settings_title']);

	loadTemplate('PmxBlogAdmin');
}
?>