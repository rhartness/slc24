<?php
get_connection();

language_file("exchange_my_trades");

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "exchange";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "My trades - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Exchange";
$_PRE["sidemenu_file"] = "exchange_menu.php";

$_PRE["REQUEST_URI"] = "/?c=exchange/my_trades";

$currencies = array( "BTC", "NMC" );
$types = array( "buy", "sell" );

if (in_array($_POST["currency"], $currencies))
	$currency = $_POST["currency"];
else
	$currency = "BTC";
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