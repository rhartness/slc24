<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "home";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Withdraw Solidcoins - Solidcoin24";
$_PRE["title"] = "Finances | Solidcoin24";
$_PRE["sidemenu_file"] = "home_menu.php";

if ($_GET["step"] == 1)
	$_PRE["REQUEST_URI"] = "/?c=balance/withdraw_slc&step=1";
elseif ($_GET["step"] == 2)
	$_PRE["REQUEST_URI"] = "/?c=balance/withdraw_slc&step=2";
else
	$_PRE["REQUEST_URI"] = "/?c=balance/withdraw_slc";
?>