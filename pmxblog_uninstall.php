<?php
// -------------------------------------------------------------
// -- pmxblog_uninstall.php                                     --
// -------------------------------------------------------------
// -- Version: 0.952 for SMF 2.0 RC2                       --
// -- Copyright 2009 by: PortaMx corp.                        --
// -- Support and Updates at: http://portamx.com              --
// -------------------------------------------------------------

global $db_name, $db_prefix, $user_info, $boardurl, $txt;

// Load the SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');

	// on manual uninstall you have to logged in
	if(!$user_info['is_admin']) 
	{
		if($user_info['is_guest']) 
		{
			echo '<b>', $txt['admin_login'],':</b><br />';
			ssi_login($boardurl.'/pmxblog_update.php');
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

// Load the SMF DB Functions
db_extend('packages');
db_extend('extra');

$req = $smcFunc['db_query']('', '
		SELECT variable, value 
		FROM {db_prefix}settings
		WHERE variable = {string:varname}',
	array(
		'varname' => 'pmxblog_enabled'
	)
);
if($smcFunc['db_num_rows']($req) > 0)
{
	$smcFunc['db_free_result']($req);
	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}settings
			WHERE variable = {string:varname}',
		array(
			'varname' => 'pmxblog_enabled'
		)
	);
}

// set intalled state for PmxBlog patches to 0
$request = $smcFunc['db_query']('', '
	SELECT id_install, version
	FROM {db_prefix}log_packages
	WHERE (package_id = {string:pktid} 
		OR package_id like {string:pktpatchid}) 
		AND version IN ({array_string:vers}) 
		AND install_state > 0',
	array(
		'pktid' => 'portamx_corp:PmxBlog',
		'pktpatchid' => 'portamx_corp:PmxBlog_Pach%',
		'vers' => array('0.945', '0.950', '0.950 RC1', '0.950 RC2'),
	)
);
if($smcFunc['db_num_rows']($request) > 0)
{
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_packages
			SET install_state = 0
			WHERE id_install = {int:id}',
			array('id' => $row['id_install'])
		);
	}
	$smcFunc['db_free_result']($request);
}
?>