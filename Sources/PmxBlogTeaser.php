<?php
// ----------------------------------------------------------
// -- PmxBlogTeaser.php                                    --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2012 by: PortaMx corp.                     --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (!defined('SMF'))
	die('Hacking attempt...');


// Post teaser (shorten articles by given wordcount).
function PmxBlogTeaser($content)
{
	global $context, $settings, $user_info, $txt, $smcFunc, $modSettings, $boarddir, $boardurl;

	$cont_mode = substr($content,0, 3);
	if($cont_mode == '<1>' || $cont_mode == '<0>')
		$content = substr($content, 3);

	parsesmileys($content);
	if($context['PmxBlog']['censor_text'] == 1)
		censorText($content);

	$wordcount = $context['PmxBlog']['content_len'];
	$PmxBlogTeaseCount = (empty($modSettings['pmxblog_teasermode']) ? 'PmxBlog_teasecountwords' : 'PmxBlog_teasecountchars');
	$PmxBlogTeaseShorten = (empty($modSettings['pmxblog_teasermode']) ? 'PmxBlog_teasegetwords' : 'PmxBlog_teasegetchars');
	$TeaseMode = intval(!empty($modSettings['pmxblog_teasermode']));
	$content = str_replace(array("\n", "\t", "\r"), '', $content);
	$contentlen = $PmxBlogTeaseCount($content);
	$teased = false;

	// we have a html teaser?
	if(preg_match('/(<span|<div)\s+style=\"page-break-after\:/is', $content, $match) > 0)
	{
		$pgbrk = $smcFunc['strpos']($content, $match[0]);
		$content = PmxBlog_teasegetchars($smcFunc['substr']($content, 0, $pgbrk), $pgbrk);
		$context['PmxBlog']['is_teased'] = $PmxBlogTeaseCount($content);
		$teased = true;
	}
	elseif($PmxBlogTeaseCount($content) > $wordcount)
	{
		$content = $PmxBlogTeaseShorten($content, $wordcount);
		$teased = true;
	}

	if(!empty($teased))
	{
		// insert teaser mark [...]
		$content .= '<span class="smalltext pmxblog_teaser" title="'. sprintf($txt['PmxBlog_teaserinfo'][$TeaseMode], $context['PmxBlog']['is_teased'], $contentlen) .'"> '. $txt['PmxBlog_teasershort'] .'</span>';

		// find not closed tags
		preg_match_all('~<(\w+)[^>]*>~s', $content, $open);
		preg_match_all('~<\/(\w+)[^>]*>~s', $content, $closed);
		foreach($open[1] as $i => $tag)
		{
			if(substr($open[0][$i], -2, 2) == '/>')
				unset($open[1][$i]);
			elseif(($fnd = array_search($tag, $closed[1])) !== false)
			{
				unset($closed[1][$fnd]);
				unset($open[1][$i]);
			}
		}
		foreach(array_reverse($open[1]) as $element)
			$content .= "</$element>";
	}
	else
		$context['PmxBlog']['is_teased'] = 0;

	// replace images for previev
	$content = RemoveLinks($content);

	if(!empty($context['PmxBlog']['thumb_show']))
	{
		$imgcount = preg_match_all('/<img[^>]*>/i', $content, $matches);
		if($imgcount != 0)
		{
			$remdata = array('/width:([0-9\s]+)px(;|)/i', '/height:([0-9\s]+)px(;|)/i', '/width?=?\"[^\"]*\"/i', '/height?=?\"[^\"]*\"/i');
			for($i = 0; $i < $imgcount; $i++)
			{
				$img = $matches[0][$i];
				preg_match('/src?=?\"([^\"]*)\"/i', $img, $part);
				preg_match('/('. preg_quote($boardurl, '/:') .'|http\:\/\/|https\:\/\/|'. preg_quote($context['PmxBlog']['upload_dir'], '/') .')(.*$)/i', urldecode($part[1]), $url);
				if(strpos($url[0], $boardurl . $context['PmxBlog']['upload_dir']) === false && !in_array(substr($url[0], 0, strrpos($url[0], '/')), array($modSettings['smileys_url'], $settings['default_images_url'], $settings['default_images_url'] .'/PmxBlog')))
				{
					$filename = '';
					$newimg = preg_replace($remdata, '', $img);
					$thbsize = explode(',', $context['PmxBlog']['thumb_size']);

					if($url[1] == $boardurl || $url[1] == $context['PmxBlog']['upload_dir'])
					{
						if($url[1] == $context['PmxBlog']['upload_dir'])
							$url[2] = $context['PmxBlog']['upload_dir'] . $url[2];

						$is_local = true;
						$filename = strrchr($url[2], '/');
						$path = substr($url[2], 0, -strlen($filename));
						if(strrchr($path, '/') != $context['PmxBlog']['images_dir'])
						{
							$custpath = substr($path, (int) strpos($path, $context['PmxBlog']['upload_dir']));
							$path = $boarddir . $custpath;
						}
						else
							$path = $boarddir . $context['PmxBlog']['upload_dir'] . $context['PmxBlog']['images_dir'];

						$size = getimagesize($path . $filename);
						if(!empty($size) && $size[0] <= $thbsize[0] && $size[1] <= $thbsize[1])
							continue;
					}
					else
					{
						$is_local = false;
						$filename = strrchr($url[2], '/');
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
						$extImg = ParseImageUrl($url[0]);

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

	return $content;
}

// create thumbnail for preview
function MakeThumbnail($sPath, $dPath, $fName, $max_width, $max_height)
{
	global $context;

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
				if($src_img = $imagecreatefrom($sPath.$fName))
				{
					ThumbResize($src_img, $destName, imagesx($src_img), imagesy($src_img), $max_width, $max_height);
					if(file_exists($destName))
						$result = strrchr($destName, '/');
				}
			}
		}
	}
	unset($testGD);

	return $result;
}

function ThumbResize($src_img, $destName, $src_width, $src_height, $max_width, $max_height)
{
	// Determine whether to resize to max width or to max height
	if (!empty($max_width) && !empty($max_height))
	{
		if (!empty($max_width) && (empty($max_height) || $src_height * $max_width / $src_width <= $max_height))
		{
			$dst_width = $max_width;
			$dst_height = intval(floor($src_height * $max_width / $src_width));
		}
		elseif (!empty($max_height))
		{
			$dst_width = intval(floor($src_width * $max_height / $src_height));
			$dst_height = $max_height;
		}

		// Don't bother resizing if it's already smaller...
		if (!empty($dst_width) && !empty($dst_height) && ($dst_width < $src_width || $dst_height < $src_height))
		{
			// (make a true color image, because it just looks better for resizing.)
			$dst_img = imagecreatetruecolor($dst_width, $dst_height);
			imagealphablending($dst_img, false);
			if (function_exists('imagesavealpha'))
				imagesavealpha($dst_img, true);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
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

// get word cont for post_teaser.
function PmxBlog_teasecountwords($text)
{
	return count(preg_split('/\s+/', preg_replace('/<[^>]*>/', '', $text)));
}

// get charater cont for post_teaser.
function PmxBlog_teasecountchars($text)
{
	global $smcFunc;

	return $smcFunc['strlen'](un_htmlspecialchars(preg_replace('/<[^>]*>/', '', $text)));
}

// get a shorten wordcont string for post_teaser.
function PmxBlog_teasegetwords($text, $wordcount)
{
	global $smcFunc, $context;

	$tags = PmxBlog_tease_gettags($text);
	$words = preg_split('/\s+/', $text, $wordcount +1);
	unset($words[count($words) -1]);
	$text = PmxBlog_tease_settags(implode(' ', $words), $tags);
	$context['PmxBlog']['is_teased'] = PmxBlog_teasecountwords($text);

	return $text;
}

// get a shorten charcont string for post_teaser.
function PmxBlog_teasegetchars($text, $wordcount)
{
	global $context, $smcFunc;

	$tags = PmxBlog_tease_gettags($text);
	if(!empty($tags))
	{
		if(preg_match_all('/<[0-9]+>/', utf8_decode(un_htmlspecialchars($text)), $repl, PREG_OFFSET_CAPTURE) > 0)
		{
			foreach($repl[0] as $nt)
			{
				if($nt[1] < $wordcount)
					$wordcount += strlen($nt[0]);
				else
					break;
			}
		}
		$text = PmxBlog_tease_settags($smcFunc['substr']($text, 0, $wordcount), $tags);
	}
	$context['PmxBlog']['is_teased'] = PmxBlog_teasecountchars($text);

	return $text;
}

// get tags in a post_teaser block.
function PmxBlog_tease_gettags(&$text)
{
	preg_match_all('~<[^>]*>~si', $text, $tags);
	foreach($tags[0] as $i => $tag)
	{
		$repl = '<'. strval($i) .'>';
		$text = substr_replace($text, $repl, strpos($text, $tag), strlen($tag));
	}

	return $tags[0];
}

// set tags in a post_teaser block.
function PmxBlog_tease_settags($text, $tags)
{
	foreach($tags as $i => $tag)
	{
		$repl = '<'. strval($i) .'>';
		if(strpos($text, $repl) === false)
			break;
		$text = substr_replace($text, $tag, strpos($text, $repl), strlen($repl));
	}

	return $text;
}
?>