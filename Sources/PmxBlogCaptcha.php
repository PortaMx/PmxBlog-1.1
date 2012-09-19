<?php
// ----------------------------------------------------------
// -- PmxBlogCaptcha.php                                   --
// ----------------------------------------------------------
// -- Version: 1.0 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2011 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

if (isset($_REQUEST['vcode']) && isset($_REQUEST['path']))
{
	$captcha = $_REQUEST['vcode'];
	$captcha = substr($captcha , 0, -strlen(dechex(mktime())));
	$captcha_str = '';
	for ($i = 0; $i < strlen($captcha); $i+=2)
		$captcha_str .= chr(hexdec(substr($captcha, $i, 2)));
	$captcha_str = base64_decode($captcha_str);

	$gdfont = $_REQUEST['path'].'//fonts/Hootie.gdf';
	$ttfont = $_REQUEST['path'].'/images/PmxBlog/captcha.ttf';
	$imgfont = $_REQUEST['path'].'/fonts/Hootie/';

	if(isset($_REQUEST['letter']))
	{
		$_REQUEST['letter'] = (int) $_REQUEST['letter'];
		CaptchaImageLetter($imgfont, strtolower($captcha_str{$_REQUEST['letter'] - 1}));
	}
	else
	{
		// it's GD2?
		$testGD = get_extension_funcs('gd');
		$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
		unset($testGD);

		$BgCol = array(236, 237, 243);
		$FgCol = array(64, 101, 136);
		$fontidx = imageloadfont($gdfont);

		// create image
		$img_width = 125;
		$img_height = 28;
		$img = imagecreate($img_width, $img_height);

		$bg_color = imagecolorallocate($img, $BgCol[0], $BgCol[1], $BgCol[2]);
		imagefilledrectangle($img, 0, 0, $img_width, $img_height, $bg_color);

		for ($i = 0; $i < 3; $i++)
			$FgCol[$i] = rand(max($FgCol[$i] - 3, 0), min($FgCol[$i] + 3, 255));
		$fg_color = imagecolorallocate($img, $FgCol[0], $FgCol[1], $FgCol[2]);

		for ($i = 0; $i < 3; $i++)
			$dotbgcolor[$i] = $BgCol[$i] < $FgCol[$i] ? rand(0, max($FgCol[$i] - 20, 0)) : rand(min($FgCol[$i] + 20, 255), 255);
		$RndCol = imagecolorallocate($img, $dotbgcolor[0], $dotbgcolor[1], $dotbgcolor[2]);

		// draw chars
		$cur_x = 5;
		for($i = 0; $i < 5; $i++)
		{
			// support TT fonts?
			$can_TTF = function_exists('imagettftext');
			if (!empty($can_TTF))
			{
				$angle = rand(-10, 10);
				$font_size = rand(14, 18);
				$isDraw = imagettftext($img, $font_size, $angle, $cur_x, 23, imagecolorallocate($img, rand(0, 150), rand(0, 150), rand(0, 150)), $ttfont, $captcha_str{$i});
				if (empty($isDraw))
					$can_TTF = '';
			}

			if (empty($can_TTF))
				imagechar($img, $fontidx, $cur_x, 2, $captcha_str{$i}, imagecolorallocate($img, rand(0, 150), rand(0, 150), rand(0, 150)));
			$cur_x += 24;
		}
		for ($i = rand(0, 2); $i < $img_height; $i += rand(1, 2))
			for ($j = rand(0, 10); $j < $img_width; $j += rand(1, 15))
				imagesetpixel($img, $j, $i, rand(0, 1) ? $fg_color : $RndCol);
		imagerectangle($img, 0, 0, $img_width - 1, $img_height - 1, imagecolorallocate($img, 10, 10, 10));

		// output the image
		if (function_exists('imagegif'))
		{
			header('Content-type: image/gif');
			imagegif($img);
		}
		else
		{
			header('Content-type: image/png');
			imagepng($img);
		}
		imagedestroy($img);
		die();
	}
}

function CaptchaImageLetter($font, $letter)
{
	header('Content-type: image/gif');
	include($font . $letter . '.gif');
	die();
}
?>