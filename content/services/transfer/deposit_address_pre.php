<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "services";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Transfer deposit address - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Transfer";
$_PRE["sidemenu_file"] = "transfer_menu.php";

$address = mysql_real_escape_string($_GET["a"], $db);

$slt_transfer_deposit_address_a = "SELECT *, UNIX_TIMESTAMP(creation_time) AS creation_time_u FROM transfer_deposit_address WHERE address = '$address'";
$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);

if ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a)) {
	$_PRE["REQUEST_URI"] = "/?c=services/transfer/deposit_address&a=".urlencode($address);
}

if ($row_transfer_deposit_address_a["user"] != $_SESSION["user_id"]) {
	$_PRE["REQUEST_URI"] = "/?c=services/transfer/deposit_address";
}

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