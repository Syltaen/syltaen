<?php


function the_pagenav($current, $max, $noecho = false) {

	function get_pagenav_link($pa) {
		$datagot = $_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING'] : "";
		return get_the_permalink() . ''.$pa.'/'.$datagot;
	}

	// DISABLED
	$dis = array(0,0,0,0,0);
	if ($current <= 1) { $dis[0] = 1; $dis[1] = 1; }
	if ($current >= $max) $dis[2] = 1;
	if ($current >= $max) { $dis[3] = 1; $dis[4] = 1; }

	$echo = "";

	$echo .= "<nav class='pagenav'>Page <span>$current</span> sur $max";
		$echo .= "<ul>";
			$echo .= "<li><a class='first_page";
			$echo .= $dis[0] ? " disabled" : "";
			$echo .= "' title='Première page'";
			$echo .= $dis[0] ? "" : " href='".get_pagenav_link(1)."'";
			$echo .= ">Première page</a></li>";

			$echo .= "<li><a class='prev_page";
			$echo .= $dis[1] ? " disabled" : "";
			$echo .= "' title='Page précédente'";
			$echo .= $dis[1] ? "" : " href='".get_pagenav_link($current - 1)."'";
			$echo .= ">Page précédente</a></li>";

			$echo .= "<li><div class='current_page'>";
			$echo .= $current;
			$echo .= "</div></li>";

			$echo .= "<li><a class='next_page";
			$echo .= $dis[3] ? " disabled" : "";
			$echo .= "' title='Page suivante'";
			$echo .= $dis[3] ? "" : " href='".get_pagenav_link($current + 1)."'";
			$echo .= ">Page suivante</a></li>";


			$echo .= "<li><a class='last_page";
			$echo .= $dis[4] ? " disabled" : "";
			$echo .= "' title='Dernière page'";
			$echo .= $dis[4] ? "" : " href='".get_pagenav_link($max)."'";
			$echo .= ">Dernière page</a></li>";
		$echo .= "</ul>";
	$echo .= "</nav>";

	if (!$noecho) {
		echo $echo;
	} else {
		return $echo;
	}
}

