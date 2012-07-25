<?php
language_file("exchange");

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "exchange";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = $_LANG[$l]["e_header3"];
$_PRE["title"] = $_LANG[$l]["e_header2"];
$_PRE["sidemenu_file"] = "exchange_menu.php";

if (strtolower($_GET["u"]) == "nmc")
{
	$_PRE["REQUEST_URI"] = "/?c=exchange&u=nmc";
	$currency = "NMC";
	$currencyn = "Namecoin";
	$currencynp = "Namecoins";
}
else
{
	$_PRE["REQUEST_URI"] = "/?c=exchange";
	$currency = "BTC";
	$currencyn = "Bitcoin";
	$currencynp = "Bitcoins";
}

include_once "inc/process_orders.php";

get_connection();

process_orders();

?>