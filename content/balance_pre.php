<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "home";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Balance - Solidcoin24";
$_PRE["title"] = "Finances | Solidcoin24";
$_PRE["sidemenu_file"] = "home_menu.php";

$_PRE["REQUEST_URI"] = "/?c=balance";

$currencies = array( "SLC", "BTC", "NMC" );
$dirs = array( "in", "out" );
$types = array( "intern", "extern" );

if (in_array($_POST["currency"], $currencies))
	$currency = $_POST["currency"];
if (in_array($_POST["dir"], $dirs))
	$dir = $_POST["dir"];
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