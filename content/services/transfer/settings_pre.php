<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "services";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Transfer settings - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Transfer";
$_PRE["sidemenu_file"] = "transfer_menu.php";

$_PRE["REQUEST_URI"] = "/?c=services/transfer/settings";

if ($_POST["step"] == 2) {
	$allow_withdrawals = $_POST["allow_withdrawals"] == "yes" ? "yes" : "no";
	
	mysql_query("UPDATE user SET allow_api_withdrawals = '$allow_withdrawals' WHERE id = '$_SESSION[user_id]'");
}


?>