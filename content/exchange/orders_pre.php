<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "exchange";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Trade orders - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Exchange";
$_PRE["sidemenu_file"] = "exchange_menu.php";

if ($_GET["active"] != "no")
	$_PRE["REQUEST_URI"] = "/?c=exchange/orders";
else
{
	$_PRE["REQUEST_URI"] = "/?c=exchange/orders&active=no";
	$active = "no";
}

$currencies = array( "BTC", "NMC" );
$types = array( "buy", "sell" );
$actives = array( "yes", "no" );

if (in_array($_POST["currency"], $currencies))
	$currency = $_POST["currency"];
if (in_array($_POST["type"], $types))
	$type = $_POST["type"];
if (in_array($_POST["active"], $actives))
	$active = $_POST["active"];
elseif (!isset($active))
	$active = "yes";

if (is_numeric($_POST["entries"]) && $_POST["entries"] > 0 && $_POST["entries"] <= 200)
	$entries = $_POST["entries"];
else
	$entries = 10;

if (is_numeric($_POST["from"]) && $_POST["from"] > 0)
	$from = $_POST["from"];
else
	$from = 0;
	
?>