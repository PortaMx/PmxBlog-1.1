<?php
/**
* \file index.php
* Supress direct acceess to the directory.
*
* \author PortaMx - Portal Management Extension
* \author Copyright 2008-2012 by PortaMx - http://portamx.com
* \version 1.51
* \date 31.08.2012
*/

if(file_exists(realpath('../../Settings.php')))
{
	require(realpath('../../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>