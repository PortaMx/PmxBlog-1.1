<?php
// ----------------------------------------------------------
// index.php
// Supress direct acceess to the image directory. 
// ----------------------------------------------------------
// -- Version: 0.953 for SMF 2.0                           --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2010 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if(file_exists(realpath('../../../../Settings.php')))
{
	require(realpath('../../../../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>
