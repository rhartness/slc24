<?php

function language_file($n, $lw=false, &$lg=false)
{
	global $_LANG, $l;
	
	if(!$lw)
		$lw = $l;
	
	if(file_exists($_SITE["root"]."lang/$lw/$n.php"))
	{
		include_once $_SITE["root"]."lang/$lw/$n.php";
		$lg = $lw;
	}
	elseif(file_exists($_SITE["root"]."lang/en/$n.php"))
	{
		include_once $_SITE["root"]."lang/en/$n.php";
		$lg = "en";
	}
	
	foreach ($_LANG[$lg] as $temp => $val) {
		if (!isset($_LANG[$lw][$temp])) {
			$_LANG[$lw][$temp] = $val;
		}
	}
}

function lang_temp($vars, $phrase)
{
	$ophrase = "";
	while($ophrase != ($phrase = str_replace("##", "[#]#", $phrase)))
		$ophrase = $phrase;
	
	if(is_array($vars))
	{
		foreach($vars as $vid => $var)
		{
			$vars[$vid] = str_replace("#", "[#]", $var);
		}
	
		$search = array();
		$replace = array();
		for($i=1;$i<=count($vars);$i++)
		{
			$search[] = "#$i";
			$replace[] = $vars[$i-1];
		}
	}
	else
	{
		$vars = str_replace("#", "[#]", $vars);
	
		$search = array("#1");
		$replace = array($vars);
	}
	$search = array_reverse($search);
	$replace = array_reverse($replace);
	$phrase = str_replace($search, $replace, $phrase);
	$phrase = str_replace("[#]", "#", $phrase);
	return $phrase;
}

?>
