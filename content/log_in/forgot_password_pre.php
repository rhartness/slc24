<?php

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "main";
$_PRE["links"] = false;
$_PRE["account"] = false;
$_PRE["page_title"] = "Request new password";
$_PRE["title"] = "Request new password";
$_PRE["sidemenu_file"] = "home_menu.php";

if ($_GET["step"] != 1)
	$_PRE["REQUEST_URI"] = "/?c=log_in/forgot_password";
else
	$_PRE["REQUEST_URI"] = "/?c=log_in/forgot_password&step=1";
?>