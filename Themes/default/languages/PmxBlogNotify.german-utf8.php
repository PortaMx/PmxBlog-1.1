<?php
// ----------------------------------------------------------
// -- PmxBlogNotify.german-utf8.php                        --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

global $context;

$txt['PmxBlog_tracknotify_subject'] = 'Blog Verfolgungs Nachricht';
$txt['PmxBlog_trackwatch_subject'] = 'Blog Kommentar Nachricht';

$txt['PmxBlog_tracknotify_msg'] = "Ein neuer Beitrag \"%s\" wurde im Blog von \"%s\" geschrieben.\n
Du kannst den Beitrag lesen unter:\n%s\n
Gruss,\n%s (Blog Administration ".$context['forum_name'].")";

$txt['PmxBlog_tracknotify_cmnt_msg'] = "Ein neuer Kommentar \"%s\" zu dem Beitrag \"%s\" wurde von \"%s\" im Blog von \"%s\" geschrieben.\n
Du kannst den Beitrag lesen unter:\n%s\n
und den Kommentar unter:\n%s  \n
Gruss,\n%s (Blog Administration ".$context['forum_name'].")";

$txt['PmxBlog_tracknotify_self_msg'] = "In deinem Blog wurde von \"%s\" der Kommentar \"%s\" zu deinem Beitrag \"%s\" geschrieben.\n
Du kannst den Kommentar lesen unter:\n%s\n
Gruss,\n%s (Blog Administration ".$context['forum_name'].")";
?>