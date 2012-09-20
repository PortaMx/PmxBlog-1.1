<?php

require(dirname(__FILE__) . '/PmxBlogSSI.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title> &lt;&lt; :: PmxBlogSSI.php 1.1 :: &gt;&gt; </title><?php

	echo '
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/style.css" />
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
		<style type="text/css">
			body
			{
				margin: 1ex;
				font-size: 10pt;
				line-height:110%;
			}
		</style>';
?>
	</head>
	<body class="windowbg">
			<div style="font-size:1.5em;"><a href="PmxBlogSSI_example.php" style="text-decoration:none;">PmxBlogSSI.php Functions</a></div><br />
			<b>Current Version: 1.0</b><br />
			<br />
			This file is used to demonstrate the capabilities of PmxBlogSSI.php using PHP include functions.<br /><br />

		<hr />

			<br />
			To use PmxBlogSSI.php in your page add at the very top of your page before the &lt;html&gt; tag on line 1:<br />
			<div style="font-family: monospace;">
				&lt;?php require(&quot;<?php echo ($user_info['is_admin'] ? realpath($boarddir . '/PmxBlogSSI.php') : 'PmxBlogSSI.php'); ?>&quot;); ?&gt;
			</div>
			<br />

		<hr />
		<b>ShowArticles Function: &lt;?php PmxBlogSSI_ShowArticles(owner, mindate, maxdate, categorie, maxlen, output);  ?&gt;</b><br />
		<span style="color:#0000ff;"><i>
		<b>owner:</b> int ID | string name<br />
		<b>mindate:</b> int unixtime | string mm.dd.yyyy (0)<br />
		<b>maxdate:</b> int unixtime | string mm.dd.yyyy (0)<br />
		<b>categorie:</b> int ID | string name (0)<br />
		<b>maxlen:</b> int max number of results (0)<br />
		<b>output:</b> echo|empty (echo)</i></span>
		<br /><span style="color:#ff0000;">PmxBlogSSI_ShowArticles(1)</span>
		<div style="border:solid 1px #ff0000;">
		<?php PmxBlogSSI_ShowArticles(1); ?>
		</div>
		<br />
		<hr />

		<b>Recent Function: &lt;?php PmxBlogSSI_Recent(recentlen, cmntdate_onnews, sort_newsup, output); ?&gt;</b><br />
		<span style="color:#0000ff;"><i>
		<b>recentlen:</b> int len (5)<br />
		<b>cmntdate_onnews:</b> 1|0 (1)<br />
		<b>sort_newsup:</b> 1|0 (1)<br />
		<b>output:</b> echo|empty (echo)	</i></span>
		<br /><span style="color:#ff0000;">PmxBlogSSI_Recent()</span>
		<div style="border:solid 1px #ff0000;">
		<?php PmxBlogSSI_Recent(); ?>
		</div>
		<br />
		<hr />

		<b>FindArticles Function: &lt;?php PmxBlogSSI_FindArticles(owner, mindate, maxdate, categorie, maxlen, output) ?&gt;</b><br />
		<span style="color:#0000ff;"><i>
		<b>owner:</b> int ID | string name<br />
		<b>mindate:</b> int unixtime | string mm.dd.yyyy (0)<br />
		<b>maxdate:</b> int unixtime | string mm.dd.yyyy (0)<br />
		<b>categorie:</b> int ID | string name (0)<br />
		<b>maxlen:</b> int max number of results (0)<br />
		<b>output:</b> echo or empty (echo)</i></span>
		<br /><span style="color:#ff0000;">PmxBlogSSI_FindArticles(1)</span>
		<div style="border:solid 1px #ff0000;">
		<?php PmxBlogSSI_FindArticles(1); ?>
		</div>
		<br />
		<hr />

		<b>GetArticle Function: &lt;?php PmxBlogSSI_GetArticle(artid, output) ?&gt;</b><br />
		<span style="color:#0000ff;"><i>
		<b>artid:</b> int ID<br />
		<b>output:</b> echo or empty (echo)</i></span>
		<br /><span style="color:#ff0000;">PmxBlogSSI_GetArticle(4)</span>
		<div style="border:solid 1px #ff0000;">
		<?php PmxBlogSSI_GetArticle(4); ?>
		</div>
		<br />
		<hr />

		<b>GetLastArticle Function: &lt;?php PmxBlogSSI_GetLastArticle(owner, output) ?&gt;</b><br />
		<span style="color:#0000ff;"><i>
		<b>owner:</b> int ID | string name (0)<br />
		<b>output:</b> echo or empty (echo)</i></span>
		<br /><span style="color:#ff0000;">PmxBlogSSI_GetLastArticle()</span>
		<div style="border:solid 1px #ff0000;">
		<?php PmxBlogSSI_GetLastArticle(); ?>
		</div>
		<br />
		<hr />

		<span style="font-size:9pt;">
			<?php
				echo 'This page took ', round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 4), ' seconds to load.<br />';
			?>
		</span>
	</body>
</html>