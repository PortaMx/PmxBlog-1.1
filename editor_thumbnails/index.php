<?php
/**
* @file index.php
* Supress direct acceess to the editor_thumbnails directory.
*
* @author PortaMx - Portal Management Extension
* @author Copyright 2008-2011 by PortaMx - http://portamx.com
*/

if(file_exists(realpath('../Settings.php')))
{
	require(realpath('../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>