<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "services";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Transfer withdrawals - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Transfer";
$_PRE["sidemenu_file"] = "transfer_menu.php";

$_PRE["REQUEST_URI"] = "/?c=services/transfer/withdrawals";

$types = array( "intern", "extern" );

if (in_array($_POST["type"], $types))
	$type = $_POST["type"];

if (is_numeric($_POST["entries"]) && $_POST["entries"] > 0 && $_POST["entries"] <= 200)
	$entries = $_POST["entries"];
else
	$entries = 10;

if (is_numeric($_POST["from"]) && $_POST["from"] > 0)
	$from = $_POST["from"];
else
	$from = 0;

?>