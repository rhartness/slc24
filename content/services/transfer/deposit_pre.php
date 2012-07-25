<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "services";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Transfer deposit - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Transfer";
$_PRE["sidemenu_file"] = "transfer_menu.php";

if (is_numeric($_GET["id"])) {
	$id = $_GET["id"];

	$slt_transfer_deposit_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_deposit WHERE id = '$id'";
	$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
	$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
	
	$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE id = $row_transfer_deposit_a[deposit_address]";
	$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
	$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
	
	if ($row_transfer_deposit_address_a["user"] == $_SESSION["user_id"])
		$_PRE["REQUEST_URI"] = "/?c=services/transfer/deposit&id=$id";
	else
		$_PRE["REQUEST_URI"] = "/?c=services/transfer/deposit";
} else {
	$id = 0;
	$_PRE["REQUEST_URI"] = "/?c=services/transfer/deposit";
}

?>