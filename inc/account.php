<?php

function add_transaction($user_id, $dir, $type, $amount, $currency, $info, $info_id=0, $fee=0, $time=0, $print=false)
{
	global $db;

	if (!is_numeric($amount) || $amount <= 0)
		die("Enter a numeric greater than 0.");
	
	if (!is_numeric($user_id))
		die("Invalid user id.");
	
	if (!is_numeric($info_id))
		die("Invalid info id.");
	
	if (!is_numeric($fee))
		die("Invalid fee.");

	if ($type != "intern")
		$type = "extern";
	if ($dir != "in")
		$dir = "out";
	
	$currency = mysql_real_escape_string($currency, $db);
	$info = mysql_real_escape_string($info, $db);
	
	$slt_account_a = "SELECT * FROM account WHERE user = '$user_id' AND currency = '$currency'";
	$rlt_account_a = mysql_query($slt_account_a);
	
	if (mysql_num_rows($rlt_account_a) != 0)
	{
		$row_account_a = mysql_fetch_assoc($rlt_account_a);
		
		if ($dir == "in")
			$row_account_a["amount"] += $amount;
		else
			$row_account_a["amount"] -= $amount;
		
		$udt_account_a = "UPDATE account SET amount = $row_account_a[amount] WHERE id = '$row_account_a[id]'";
		mysql_query($udt_account_a);
		
		if ($time == 0 || !is_numeric($time)) {
			$ins_transaction_a = "INSERT INTO transaction (direction, type, user, amount, currency, balance, fee, filing_time, info, info_id) ".
									"VALUES ('$dir', '$type', '$user_id', '$amount', '$currency', '$row_account_a[amount]', '$fee', NOW(), '$info', '$info_id')";
		} else {
			$ins_transaction_a = "INSERT INTO transaction (direction, type, user, amount, currency, balance, fee, filing_time, info, info_id) ".
									"VALUES ('$dir', '$type', '$user_id', '$amount', '$currency', '$row_account_a[amount]', '$fee', FROM_UNIXTIME($time), '$info', '$info_id')";
		}
		if ($print) echo $ins_transaction_a;
		mysql_query($ins_transaction_a);
		
		return mysql_insert_id($db);
	} else {
		$ins_account = "INSERT INTO account (user, currency, amount) VALUES ($user_id, '$currency', '0')";
		mysql_query($ins_account);
		
		$row_account_a["id"] = mysql_insert_id();
		$row_account_a["amount"] = 0;
		
		if ($dir == "in")
			$row_account_a["amount"] += $amount;
		else
			$row_account_a["amount"] -= $amount;
		
		$udt_account_a = "UPDATE account SET amount = $row_account_a[amount] WHERE id = '$row_account_a[id]'";
		mysql_query($udt_account_a);
		
		if ($time == 0 || !is_numeric($time)) {
			$ins_transaction_a = "INSERT INTO transaction (direction, type, user, amount, currency, balance, fee, filing_time, info, info_id) ".
									"VALUES ('$dir', '$type', '$user_id', '$amount', '$currency', '$row_account_a[amount]', '$fee', NOW(), '$info', '$info_id')";
		} else {
			$ins_transaction_a = "INSERT INTO transaction (direction, type, user, amount, currency, balance, fee, filing_time, info, info_id) ".
									"VALUES ('$dir', '$type', '$user_id', '$amount', '$currency', '$row_account_a[amount]', '$fee', FROM_UNIXTIME($time), '$info', '$info_id')";
		}
		if ($print) echo $ins_transaction_a;
		mysql_query($ins_transaction_a);
		
		return mysql_insert_id($db);
	}
}

function get_balance($user_id, $currency)
{
	global $db;

	if (!is_numeric($user_id))
		die("Invalid user id.");
	
	$currency = mysql_real_escape_string($currency, $db);
	
	$slt_account_a = "SELECT * FROM account WHERE user = '$user_id' AND currency = '$currency'";
	$rlt_account_a = mysql_query($slt_account_a);
	
	if (mysql_num_rows($rlt_account_a) != 0)
	{
		$row_account_a = mysql_fetch_assoc($rlt_account_a);
		
		return $row_account_a["amount"];
	}
	return 0;
}

?>