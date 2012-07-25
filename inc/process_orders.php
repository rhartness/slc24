<?php
include_once "inc/account.php";

function process_orders()
{
	global $db;
	
	$slt_trade_order_a = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' ORDER BY filing_time DESC";
	$rlt_trade_order_a = mysql_query($slt_trade_order_a);
	
	while ($row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a))
	{
		process_order($row_trade_order_a["id"]);
	}
}

function process_order($id)
{
	global $db;
	
	if (!is_numeric($id)) die("Invalid trade order id.");
	
	$slt_trade_order_a = "SELECT * FROM trade_order WHERE id = '$id'";
	$rlt_trade_order_a = mysql_query($slt_trade_order_a);
	$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
	
	$currency = $row_trade_order_a["currency"];
	
	if ($row_trade_order_a["type"] == "buy")
	{
		$slt_trade_order_b = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND currency = '$currency' AND filing_time < '$row_trade_order_a[filing_time]' AND ROUND(price * 100000000) <= ".round($row_trade_order_a["price"] * 100000000)." ORDER BY price ASC, filing_time ASC LIMIT 1";
		$rlt_trade_order_b = mysql_query($slt_trade_order_b);
		$row_trade_order_b = mysql_fetch_assoc($rlt_trade_order_b);
		
		$price = $row_trade_order_b["price"];
		$amount = min($row_trade_order_a["amount"] - $row_trade_order_a["completed"], $row_trade_order_b["amount"] - $row_trade_order_b["completed"]);
		
		if ($amount > 0)
		{
			$cur_amount = $amount * $price;
			$slc_amount = $amount;
			
			$slc_trid = add_transaction($row_trade_order_a["user"], "in", "intern", $slc_amount, "SLC", "trade_return", $row_trade_order_a["id"], 0);
			
			$ref_trid = 0;
			if ($amount * ($row_trade_order_a["price"] - $price) * 100000000 >= 1)
			{
				$ref_trid = add_transaction($row_trade_order_a["user"], "in", "intern", $amount * ($row_trade_order_a["price"] - $price), $row_trade_order_b["currency"], "trade_return", $row_trade_order_a["id"], 0);
			}
			
			$cur_trid = add_transaction($row_trade_order_b["user"], "in", "intern", $cur_amount * 0.997, $row_trade_order_b["currency"], "trade_return", $row_trade_order_b["id"], $cur_amount * 0.003);
			
			if ($row_trade_order_a["amount"] - $row_trade_order_a["completed"] - $amount <= 0.0000001)
			{
				$udt_trade_order_a = "UPDATE trade_order SET completed = amount, finished = 'yes', active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
			}
			else
			{
				$udt_trade_order_a = "UPDATE trade_order SET completed = completed + $amount WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
			}
			
			if ($row_trade_order_b["amount"] - $row_trade_order_b["completed"] - $amount <= 0.0000001)
			{
				$udt_trade_order_b = "UPDATE trade_order SET completed = amount, finished = 'yes', active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_b[id]";
				mysql_query($udt_trade_order_b);
			}
			else
			{
				$udt_trade_order_b = "UPDATE trade_order SET completed = completed + $amount WHERE id = $row_trade_order_b[id]";
				mysql_query($udt_trade_order_b);
			}
			
			$ins_trade_a = "INSERT INTO trade (type, buy_trade_order, sell_trade_order, currency, slc_transaction, cur_transaction, refund, trade_time, price, amount) ".
				"VALUES ('buy', $row_trade_order_a[id], $row_trade_order_b[id], '$currency', '$slc_trid', '$cur_trid', '$ref_trid', NOW(), $price, $amount)";
			mysql_query($ins_trade_a);
		}
	}
	elseif ($row_trade_order_a["type"] == "sell")
	{
		$slt_trade_order_b = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND currency = '$currency' AND filing_time < '$row_trade_order_a[filing_time]' AND ROUND(price * 100000000) >= ".round($row_trade_order_a["price"] * 100000000)." ORDER BY price DESC, filing_time ASC LIMIT 1";
		$rlt_trade_order_b = mysql_query($slt_trade_order_b);
		$row_trade_order_b = mysql_fetch_assoc($rlt_trade_order_b);
		
		$price = $row_trade_order_b["price"];
		$amount = min($row_trade_order_a["amount"] - $row_trade_order_a["completed"], $row_trade_order_b["amount"] - $row_trade_order_b["completed"]);
		
		if ($amount > 0)
		{
			$cur_amount = $amount * $price;
			$slc_amount = $amount;
			
			$cur_trid = add_transaction($row_trade_order_a["user"], "in", "intern", $cur_amount * 0.997, $row_trade_order_a["currency"], "trade_return", $row_trade_order_a["id"], $cur_amount * 0.003);
			
			$slc_trid = add_transaction($row_trade_order_b["user"], "in", "intern", $slc_amount, "SLC", "trade_return", $row_trade_order_b["id"], 0);
			
			if ($row_trade_order_a["amount"] - $row_trade_order_a["completed"] - $amount <= 0.00000001)
			{
				$udt_trade_order_a = "UPDATE trade_order SET completed = amount, finished = 'yes', active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
			}
			else
			{
				$udt_trade_order_a = "UPDATE trade_order SET completed = completed + $amount WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
			}
			
			if ($row_trade_order_b["amount"] - $row_trade_order_b["completed"] - $amount <= 0.00000001)
			{
				$udt_trade_order_b = "UPDATE trade_order SET completed = amount, finished = 'yes', active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_b[id]";
				mysql_query($udt_trade_order_b);
			}
			else
			{
				$udt_trade_order_b = "UPDATE trade_order SET completed = completed + $amount WHERE id = $row_trade_order_b[id]";
				mysql_query($udt_trade_order_b);
			}
			
			$ins_trade_a = "INSERT INTO trade (type, buy_trade_order, sell_trade_order, currency, slc_transaction, cur_transaction, refund, trade_time, price, amount) ".
				"VALUES ('sell', $row_trade_order_b[id], $row_trade_order_a[id], '$currency', '$slc_trid', '$cur_trid', '$ref_trid', NOW(), $price, $amount)";
			mysql_query($ins_trade_a);
		}
	}
}

?>