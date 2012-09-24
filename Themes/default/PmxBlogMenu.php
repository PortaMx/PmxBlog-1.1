<?php
// ----------------------------------------------------------
// -- PmxBlogMenu.template.php                             --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

// The Main Menu tabs
function Navigation($tabarray)
{
	global $context, $user_info, $scripturl;

	echo '
	<a name="top"></a>
	<div id="admin_menu" class="pmx_adm_menus">
		<ul class="dropmenu">';

	foreach($tabarray['tabs'] as $key => $tab)
	{
		if($tab['is_enabled'])
			echo '
			<li>
				<a class="'. ($tab['is_selected'] ? 'active ' : '') .'firstlevel" href="'.$tab['href'].'">
					<span class="'.($tab['is_selected'] ? '' : 'last ') .'firstlevel"><img src="'.$tab['image'].'" alt="'.$tab['title'].'" style="margin-bottom:-2px;" /> '.$tab['title'].'</span>
				</a>
			</li>';
	}

	echo '
		</ul>
	</div>';
}

// The Admin Menu tabs
function AdminTabs($tabarray)
{
	$curact = '';
	$subact = '';
	echo '
	<div class="title_bar"><h3 class="titlebg pmxblog_corepad" style="text-align:center;">
		'.$tabarray['title'].'
	</h3></div>
	<div  id="adm_submenus" class="pmx_adm_submenus">
		<ul class="dropmenu">';

	foreach($tabarray['tabs'] as $key => $tab)
	{
		if($tab['is_enabled'])
		{
			echo '
			<li>
				<a class="'. ($tab['is_selected'] ? 'active ' : '') .'firstlevel" href="'.$tab['href'].'">
					<span class="'.($tab['is_selected'] ? '' : 'last ') .'firstlevel">'. $tab['title'] .'</span>
				</a>
			</li>';

			if($tab['is_selected'])
				$curact = $key;
		}
	}

	echo '
		</ul>
	</div>';

	return $curact.$subact;
}
?>