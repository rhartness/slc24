<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "exchange";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Trade order - Solidcoin24";
$_PRE["title"] = "Solidcoin24 Exchange";
$_PRE["sidemenu_file"] = "exchange_menu.php";

include_once "inc/db_locks.php";

if (is_numeric($_GET["t"]))
	$trade_id = $_GET["t"];
	
$slt_trade_order_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM trade_order WHERE id = '$trade_id' AND user = '$_SESSION[user_id]'";
$rlt_trade_order_a = mysql_query($slt_trade_order_a);
$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);

if ($row_trade_order_a["currency"] == "BTC")
{
	$currency = "BTC";
	$currencyn = "Bitcoin";
	$currencynp = "Bitcoins";
}
elseif ($row_trade_order_a["currency"] == "NMC")
{
	$currency = "NMC";
	$currencyn = "Namecoin";
	$currencynp = "Namecoins";
}

if ($_GET["ca"] == 1)
{
	if (!get_lock("global")) {
		die("Too many queries. Please try again later.");
	}
	
	$slt_trade_order_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM trade_order WHERE id = '$trade_id' AND user = '$_SESSION[user_id]'";
	$rlt_trade_order_a = mysql_query($slt_trade_order_a);
	$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
		
	if ($row_trade_order_a["active"] == "yes") {
		if ($row_trade_order_a["type"] == "sell")
		{
			$refund = $row_trade_order_a["amount"] - $row_trade_order_a["completed"];
		
			add_transaction($_SESSION["user_id"], "in", "intern", $refund, "SLC", "trade_cancellation", $trade_id);
		
			$udt_trade_order_a = "UPDATE trade_order SET active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
			mysql_query($udt_trade_order_a);
		}
		elseif ($row_trade_order_a["type"] == "buy")
		{
			$refund = ($row_trade_order_a["amount"] - $row_trade_order_a["completed"]) * $row_trade_order_a["price"];
		
			add_transaction($_SESSION["user_id"], "in", "intern", $refund * 1.004, $currency, "trade_cancellation", $trade_id, -$refund * 0.004);
		
			$udt_trade_order_a = "UPDATE trade_order SET active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
			mysql_query($udt_trade_order_a);
		}
	}
}

if ($row_trade_order_a)
	$_PRE["REQUEST_URI"] = "/?c=exchange/order&t=$trade_id";
else
	$_PRE["REQUEST_URI"] = "/?c=exchange/orders";
?>