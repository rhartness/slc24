<?php

language_file("support");

$_PRE["info"] = true;
$_PRE["search"] = true;
$_PRE["menu"] = "support";
$_PRE["links"] = true;
$_PRE["account"] = true;
$_PRE["page_title"] = $_LANG[$l]["s_header3"];
$_PRE["title"] = $_LANG[$l]["s_header2"];

if ($_GET["step"] != 1)
	$_PRE["REQUEST_URI"] = "/?c=support";
else
	$_PRE["REQUEST_URI"] = "/?c=support&step=1";
?>