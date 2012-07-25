<?php
$_PRE["menu"] = "home";
$_PRE["account"] = true;
$_PRE["page_title"] = "Change password - Solidcoin24";
$_PRE["title"] = "Settings | Solidcoin24";
$_PRE["sidemenu_file"] = "home_menu.php";

if ($_GET["step"] != 1)
	$_PRE["REQUEST_URI"] = "/?c=settings/change_password";
else
	$_PRE["REQUEST_URI"] = "/?c=settings/change_password&step=1";
?>