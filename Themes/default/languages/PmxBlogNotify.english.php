<?php
// ----------------------------------------------------------
// -- FelBlogNotify.english.php                            --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

global $context;

$txt['PmxBlog_tracknotify_subject'] = 'Blog tracking notify';
$txt['PmxBlog_trackwatch_subject'] = 'Blog comment notify';

$txt['PmxBlog_tracknotify_msg'] = "A new article \"%s\" has been made on the blog about \"%s\".\n
You can read this article at:\n%s\n
Regards,\n%s (Blog Administration ".$context['forum_name'].")";

$txt['PmxBlog_tracknotify_cmnt_msg'] = "A new comment \"%s\" to the article \"%s\" has been made by \"%s\" on the blog about \"%s\".\n
You can read this article at:\n%s\n
and the comment at:\n%s \n
Regards,\n%s (Blog Administration ".$context['forum_name'].")";

$txt['PmxBlog_tracknotify_self_msg'] = "On your Blog, \"%s\" has been made a new comment \"%s\" to your article \"%s\".\n
You can read the comment at:\n%s\n
Regards,\n%s (Blog Administration ".$context['forum_name'].")";
?>