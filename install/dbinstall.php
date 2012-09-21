<?php
/******************************
* file dbinstall.php          *
* Database tables install     *
* Coypright by PortaMx corp.  *
*******************************/

global $db_prefix, $user_info, $boardurl, $boarddir, $sourcedir, $txt, $dbinstall_string;

// Load the SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	function _dbinst_write($string) { echo $string; }

	require_once(dirname(__FILE__) . '/SSI.php');

	// on manual installation you have to logged in
	if(!$user_info['is_admin'])
	{
		if($user_info['is_guest'])
		{
			echo '<b>', $txt['admin_login'],':</b><br />';
			ssi_login($boardurl.'/dbinstall.php');
			die();
		}
		else
		{
			loadLanguage('Errors');
			fatal_error($txt['cannot_admin_forum']);
		}
	}
}

// no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> SSI.php not found. Please verify you put this in the same place as SMF\'s index.php.');
else
{
	function _dbinst_write($string)
	{
		global $dbinstall_string;
		$dbinstall_string .= $string;
	}
}

// split of dbname (mostly for SSI)
$pref = explode('.', $db_prefix);
if(!empty($pref[1]))
	$pref = $pref[1];
else
	$pref = $db_prefix;

$dbinstall_string = '';

// Load the SMF DB Functions
db_extend('packages');
db_extend('extra');

/********************
* Define the tables *
*********************/
// PmxBlog Table defs
$tabledate = array(
	// tablename
	'pmxblog_settings' => array(
		// column defs
		array(
			array('name' => 'ID', 'type' => 'int', 'null' => false, 'auto' => true),
			array('name' => 'name', 'type' => 'varchar', 'size' => '25', 'default' => '', 'null' => false),
			array('name' => 'value', 'type' => 'text', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('ID')),
		),
		// options
		array()
	),

	'pmxblog_manager' => array(
		// column defs
		array(
			array('name' => 'owner', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'blogname', 'type' => 'tinytext', 'null' => false),
			array('name' => 'blogdesc', 'type' => 'tinytext', 'null' => false),
			array('name' => 'showarchive', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'showcategories', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'showcalendar', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'blogcreated', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'hidebaronedit', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'blogenabled', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'bloglocked', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'tracking', 'type' => 'tinytext', 'null' => false),
			array('name' => 'blograting', 'type' => 'decimal(3,1)', 'default' => '0', 'null' => false),
			array('name' => 'blogvotes', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'settings', 'type' => 'varchar', 'size' => '10', 'default' => '', 'null' => false),
			array('name' => 'userpicture', 'type' => 'tinytext', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('owner')),
		),
		// options
		array()
	),

	'pmxblog_ratings' => array(
		// column defs
		array(
			array('name' => 'owner', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'contID', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'rating', 'type' => 'text', 'null' => false),
			array('name' => 'voter', 'type' => 'text', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'unique', 'name' => 'contID', 'columns' => array('contID')),
			array('type' => 'index', 'name' => 'owner', 'columns' => array('owner')),
		),
		// options
		array()
	),

	'pmxblog_categories' => array(
		// column defs
		array(
			array('name' => 'ID', 'type' => 'int', 'null' => false, 'auto' => true),
			array('name' => 'owner', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'name', 'type' => 'tinytext', 'null' => false),
			array('name' => 'corder', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'depth', 'type' => 'smallint', 'default' => '0', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('ID')),
			array('type' => 'index', 'name' => 'owner', 'columns' => array('owner')),
			array('type' => 'index', 'name' => 'corder', 'columns' => array('corder')),
		),
		// options
		array()
	),

	'pmxblog_content' => array(
		// column defs
		array(
			array('name' => 'ID', 'type' => 'int', 'null' => false, 'auto' => true),
			array('name' => 'owner', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'ip_address', 'type' => 'varchar', 'size' => '16', 'default' => '', 'null' => false),
			array('name' => 'categorie', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'nbr_comment', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'allowcomment', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'allow_view', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'date_created', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'date_lastedit', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'published', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'notify', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'views', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'subject', 'type' => 'tinytext', 'null' => false),
			array('name' => 'body', 'type' => 'text', 'null' => false),
			array('name' => 'depth', 'type' => 'smallint', 'default' => '0', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('ID')),
			array('type' => 'index', 'name' => 'owner', 'columns' => array('owner')),
			array('type' => 'index', 'name' => 'categorie', 'columns' => array('categorie')),
			array('type' => 'index', 'name' => 'date_created', 'columns' => array('date_created')),
		),
		// options
		array()
	),

	'pmxblog_comments' => array(
		// column defs
		array(
			array('name' => 'ID', 'type' => 'int', 'null' => false, 'auto' => true),
			array('name' => 'author', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'username', 'type' => 'varchar', 'size' => '25', 'default' => '', 'null' => false),
			array('name' => 'ip_address', 'type' => 'varchar', 'size' => '16', 'default' => '', 'null' => false),
			array('name' => 'contID', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'parent', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'treelevel', 'type' => 'smallint', 'default' => '0', 'null' => false),
			array('name' => 'treeS2', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'date_created', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'date_lastedit', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'subject', 'type' => 'tinytext', 'null' => false),
			array('name' => 'body', 'type' => 'text', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('ID')),
			array('type' => 'index', 'name' => 'author', 'columns' => array('author')),
			array('type' => 'index', 'name' => 'contID', 'columns' => array('contID')),
			array('type' => 'index', 'name' => 'parent', 'columns' => array('parent')),
			array('type' => 'index', 'name' => 'date_created', 'columns' => array('date_created')),
		),
		// options
		array()
	),

	'pmxblog_cmnt_log' => array(
		// column defs
		array(
			array('name' => 'userID', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'contID', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'cmtID', 'type' => 'int', 'default' => '0', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'index', 'name' => 'userID', 'columns' => array('userID')),
			array('type' => 'index', 'name' => 'contID', 'columns' => array('contID')),
		),
		// options
		array()
	),

	'pmxblog_cont_log' => array(
		// column defs
		array(
			array('name' => 'owner', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'userID', 'type' => 'int', 'default' => '0', 'null' => false),
			array('name' => 'is_read', 'type' => 'tinytext', 'null' => false),
		),
		// index defs
		array(
			array('type' => 'index', 'name' => 'owner', 'columns' => array('owner')),
			array('type' => 'index', 'name' => 'userID', 'columns' => array('userID')),
		),
		// options
		array()
	),
);

// prepare data
$Version = 'v1.1';
$cpy = '<a href="http://portamx.com/license" target="_blank">PmxBlog '.$Version.' &copy; 2008-2012</a>, <a href="http://portamx.com/" target="_blank">PortaMx corp.</a>';

// pepare the data array
$settings_data = array(
	'settings' => '60,20,20,0,0,10,0,0',
	'wysiwyg_edit' => 'a:1:{i:0;s:1:"1";}',
	'wysiwyg_comment' => 'a:1:{i:0;s:1:"1";}',
	'modgroups' => 'a:0:{}',
	'blog_acs' => ',:,:,',
	'blogadmin' => '1',
	'thumb_show' => '1',
	'thumb_size' => '100,75',
	'htmltags' => 'div|span|pre|p|h1|h2|h3|h4|h5|h6|blockquote|code|address',
	'copyright' => $cpy,
);

$settings_upddata = array(
	'copyright' => $cpy,
);

// backup & convert the table if exist
$newline = '';
$created = array();
$updated = array();

// loop througt each table
foreach($tabledate as $tblname => $tbldef)
{
	// check if the table exist
	_dbinst_write($newline .'Processing Table "'. $pref . $tblname .'".<br />');
	$exist = false;
	$drop = false;
	$newline = '<br />';

	$tablelist = $smcFunc['db_list_tables'](false, $pref. $tblname);
	if(!empty($tablelist) && in_array($pref . $tblname, $tablelist))
	{
		// exist .. check the cols, the type and value
		_dbinst_write('.. Table exist, checking columns and indexes.<br />');
		$exist = true;
		list($cols, $index, $params) = $tbldef;
		$structure = $smcFunc['db_table_structure']('{db_prefix}'. $tblname, true);

		$drop = check_columns($cols, $structure['columns']);
		if(empty($drop))
			$drop = check_indexes($index, $structure['indexes'], $pref . $tblname);

		if(empty($drop))
			_dbinst_write('.. Table successful checked.<br />');
	}

	if(!empty($drop))
	{
		// drop table
		$smcFunc['db_drop_table']('{db_prefix}'. $tblname);
		$exist = false;
		_dbinst_write('.. Table not identical, dropped.<br />');
	}

	if(empty($exist))
	{
		// create the table
		$created[] = $tblname;
		list($cols, $index, $params) = $tbldef;
		$smcFunc['db_create_table']('{db_prefix}'. $tblname, $cols, $index, $params, 'error');
		_dbinst_write('.. Table successful created.<br />');

		if($tblname == 'pmxblog_settings')
		{
			// load the settings table
			foreach($settings_data as $name => $value)
			{
				$smcFunc['db_insert']('', '
					{db_prefix}pmxblog_settings',
					array(
						'name' => 'string',
						'value' => 'string'
					),
					array(
						$name,
						$value
					),
					array('ID')
				);
			}
			_dbinst_write('.. Table successful initiated.<br />');
		}
	}
	else
	{
		if($tblname == 'pmxblog_settings')
		{
			// update the settings table
			foreach($settings_upddata as $name => $value)
			{
				$smcFunc['db_insert']('', '
					{db_prefix}pmxblog_settings',
					array(
						'name' => 'string',
						'value' => 'string'
					),
					array(
						$name,
						$value
					),
					array('ID')
				);
			}
			_dbinst_write('.. Table successful updated.<br />');
		}
	}
}

// on update setup the dbuninstall string to current version
$dbupdates = array();
foreach($tabledate as $tblname => $tbldef)
{
	if(!in_array($tblname, $created))
		$dbupdates[] = array('remove_table', $pref . $tblname);
}

if(!empty($dbupdates))
{
	$found = array();
	// get last exist version
	$request = $smcFunc['db_query']('', '
		SELECT id_install, themes_installed
		FROM {db_prefix}log_packages
		WHERE package_id LIKE {string:pkgid} AND version LIKE {string:vers}
		ORDER BY id_install DESC
		LIMIT 1',
		array(
			'pkgid' => 'portamx_corp:pmxblog%',
			'vers' => '1.%',
		)
	);
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		$found['id'] = $row['id_install'];
		$found['themes'] = $row['themes_installed'];
	}
	$smcFunc['db_free_result']($request);

	if(!empty($found['id']))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_packages
			SET package_id = {string:pkgid}, db_changes = {string:dbchg},'. (!empty($found['themes']) ? ' themes_installed = {string:thchg},' : '') .' install_state = 1
			WHERE id_install = {int:id}',
			array(
				'id' => $found['id'],
				'pkgid' => 'portamx_corp:pmxblog',
				'thchg' => (!empty($found['themes']) ? $found['themes'] : ''),
				'dbchg' => serialize($dbupdates),
			)
		);
	}
}

// enable PmxBlog
$req = $smcFunc['db_query']('', '
		SELECT variable, value
		FROM {db_prefix}settings
		WHERE variable = {string:varname}',
	array(
		'varname' => 'pmxblog_enabled'
	)
);
if($smcFunc['db_num_rows']($req) > 0)
	$smcFunc['db_free_result']($req);
else
	$smcFunc['db_insert']('replace', '
		{db_prefix}settings',
		array(
			'variable' => 'string',
			'value' => 'string'
		),
		array(
			'pmxblog_enabled',
			'1'
		),
		array('variable')
	);

// done
_dbinst_write('<br />dbinstall done.');

if(!empty($dbinstall_string))
{
	$filename = str_replace('dbinstall.php', '', __FILE__) .'installdone.html';
	$instdone = file_get_contents($filename);
	$instdone = str_replace('<div></div>', '<div style="text-align:left;"><strong>Database install results:</strong><br />'. $dbinstall_string .'</div>', $instdone);
	$fh = fopen($filename, 'w');
	if($fh)
	{
		fwrite($fh, $instdone);
		fclose($fh);
	}
	else
		log_error($dbinstall_string);
}

// clear cache
clean_cache();

/************************
* Column check function *
*************************/
function check_columns($cols, $data)
{
	// col count same?
	if(count($cols) != count($data))
		$drop = true;
	else
	{
		// yes, check each col
		$drop = false;
		foreach($cols as $col)
		{
			if(array_key_exists($col['name'], $data))
			{
				$check = $data[$col['name']];
				foreach($col as $def => $val)
					$drop = (isset($check[$def]) && ($check[$def] == $val || ($check[$def] == "''" && empty($val)))) ? $drop : true;
			}
			else
				$drop = true;
		}
	}
	return $drop;
}

/**
* Index check function
**/
function check_indexes($indexes, $data, $tblname)
{
	// index count same?
	if(count($indexes) != count($data))
		$drop = true;
	else
	{
		// yes, check each index
		$drop = false;
		foreach($indexes as $index => $values)
		{
			// find the index type
			$check = '';
			foreach($data as $fnd)
			{
				if(strcasecmp($fnd['name'], $values['name']) == 0 || strcasecmp($fnd['name'],$tblname .'_'. $values['name']) == 0)
				{
					$check = $fnd;
					$check['name'] = $values['name'];
					break;
				}
				elseif(strcasecmp($fnd['name'], $tblname .'_pkey') == 0 && strtolower($values['name']) == 'primary')
				{
					$check = $fnd;
					$check['name'] = 'primary';
					break;
				}
			}

			// now check the values
			if(!empty($check))
			{
				foreach($values as $def => $value)
				{
					// index cols?
					if(is_array($value))
					{
						if(array_diff($check[$def], $value) != array())
							$drop = true;
					}
					// no, type and name
					elseif((isset($check[$def]) && ($check[$def] == $value || $check[$def] == strtoupper($value))) === false)
						$drop = true;
				}
			}
			else
				$drop = true;
		}
	}
	return $drop;
}
?>