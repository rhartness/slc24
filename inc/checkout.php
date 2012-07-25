<?php

function handle_checkout_by_address($address)
{
	global $solidcoin, $db;
	
	$slt_transfer_a = "SELECT *, UNIX_TIMESTAMP(creation_time) AS u_creation_time FROM transfer WHERE address = '".mysql_real_escape_string($address, $db)."' AND type = in";
	$rlt_transfer_a = mysql_query($slt_transfer_a);
	
	if (mysql_num_rows($rlt_transfer_a)) {
		$row_transfer_a = mysql_fetch_assoc($rlt_transfer_a);
		return handle_checkout($row_transfer_a);
	}
	return -1;
}

function handle_checkout_by_id($id)
{
	global $solidcoin, $db;
	
	$slt_transfer_a = "SELECT *, UNIX_TIMESTAMP(creation_time) AS u_creation_time FROM transfer WHERE id = '".mysql_real_escape_string($id, $db)."' AND type = in";
	$rlt_transfer_a = mysql_query($slt_transfer_a);
	
	if (mysql_num_rows($rlt_transfer_a)) {
		$row_transfer_a = mysql_fetch_assoc($rlt_transfer_a);
		return handle_checkout($row_transfer_a);
	}
	return -1;
}

function handle_checkout($row_transfer_a, $print=false)
{
	global $solidcoin, $db;
	
	$received = $solidcoin->sc_getreceivedbyaddress("main", $row_transfer_a["address"], 0) / 10000;
	$booked = $row_transfer_a["amount_real"];
	
	$booked_oughta = $solidcoin->sc_getreceivedbyaddress("main", $row_transfer_a["address"], 6) / 10000;
	
	$pending = $received - $booked_oughta;
	$ch = false;
	
	if (round($booked_oughta * 10000) > round($booked * 10000))
	{
		$ch = true;
		$user_id = $row_transfer_a["user"];
		$id = $row_transfer_a["id"];
	
		$udt_transfer_a = "UPDATE transfer SET amount_real = '$booked_oughta' WHERE id = '$id'";
		mysql_query($udt_transfer_a);
		
		$slt_account_a = "SELECT * FROM account WHERE user = '$user_id' AND currency = 'SLC'";
		$rlt_account_a = mysql_query($slt_account_a);
		$row_account_a = mysql_fetch_assoc($rlt_account_a);
		$balance = $row_account_a["amount"];
		
		$fee = 0;
		if (strlen($row_transfer_a["callback"]) > 0)
		{
			$what_i_get = $booked_oughta - $booked;
			$fee = $what_i_get;
			$what_i_get = sc24_without_fee($what_i_get);
			$fee -= $what_i_get;
		
			$udt_account_b = "UPDATE account SET amount = amount + '$what_i_get' WHERE user = $user_id AND currency = 'SLC'";
			mysql_query($udt_account_b);
			
			$ins_transaction_a = "INSERT INTO transaction (type, direction, trade_order, user, filing_time, currency, amount, balance, total_fee, fee_model, finished, info) ".
				"VALUES ('extern', 'in', '$id', '$user_id', NOW(), 'SLC', '$what_i_get', '".($balance + $what_i_get)."', '$fee', 'none', 'no', 'checkout')";
			mysql_query($ins_transaction_a);
		}
		else
		{
			$udt_account_b = "UPDATE account SET amount = amount + '".($booked_oughta - $booked)."' WHERE user = $user_id AND currency = 'SLC'";
			mysql_query($udt_account_b);
			
			$ins_transaction_a = "INSERT INTO transaction (type, direction, trade_order, user, filing_time, currency, amount, balance, total_fee, fee_model, finished, info) ".
				"VALUES ('extern', 'in', '$id', '$user_id', NOW(), 'SLC', '".($booked_oughta - $booked)."', '".($balance + $booked_oughta - $booked)."', '0', 'none', 'no', 'checkout')";
			mysql_query($ins_transaction_a);
		}

		if ($print)
			echo "Transfer of ".($booked_oughta - $booked)." to $user_id.\n";
		
		if ((int)round($row_transfer_a["amount_real"] * 10000) < (int)round($row_transfer_a["amount_set"] * 10000)
			&& (int)round($booked_oughta * 10000) >= (int)round($row_transfer_a["amount_set"] * 10000))
		{
			if ($row_transfer_a["send_mail"] == "yes")
			{
				$slt_user_c = "SELECT * FROM user WHERE id = '$user_id'";
				$rlt_user_c = mysql_query($slt_user_c);
				$row_user_c = mysql_fetch_assoc($rlt_user_c);
			
				send_mail("Payment of ".(round($booked_oughta * 10000) / 10000)." SLC arrived", 
					"Dear user,<br />\n<br />\nThe checkout with the id '$id' is completed.<br />\n<br />\nAdditional information:<br />\n".
					"Creation date and time: ".date($row_user_c["date_format"]." ".$row_user_c["time_format"], $row_transfer_a["u_creation_time"] + $row_user_c["hour_span"] * 3600)."<br />\n".
					"Payment date and time: ".date($row_user_c["date_format"]." ".$row_user_c["time_format"], time() + $row_user_c["hour_span"] * 3600)."<br />\n".
					"Checkout amount: ".(round($row_transfer_a["amount_set"] * 10000) / 10000)." SLC<br />\n".
					"Amount received: ".(round($booked_oughta * 10000) / 10000)." SLC<br />\n".
					"Pending right now: ".(round($pending * 10000) / 10000)." SLC <small>(transactions with less than 6 confirmations)</small><br />\n".
					"Custom id: ".$row_transfer_a["custom_id"]."<br />\n".
					"Extra information: ".$row_transfer_a["extra_info"]."<br />\n<br />\n".
					"Your sc24 team", $row_user_c["email"]);

				if ($print)
					echo "Sent mail.\n";
			}
			if (strlen($row_transfer_a["callback"]) > 0)
			{
				$callback = $row_transfer_a["callback"];
				$callback = str_replace(
						array( "{{}", "{}}", "{id}", "{custom_id}", "{address}", "{extra_info}", "{amount}", "{paid}", "{creation_time}", "{fee}" ), 
						array( "{", "}", $row_transfer_a["id"], urlencode($row_transfer_a["custom_id"]), $row_transfer_a["address"], urlencode($row_transfer_a["extra_info"]), round($row_transfer_a["amount_set"] * 10000) / 10000, round($booked_oughta * 10000) / 10000, $row_transfer_a["u_creation_time"], round(sc24_fee_from_awf($row_transfer_a["amount_set"]) * 10000) / 10000 ), 
						$callback);
				
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $callback);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				$response = curl_exec($ch);
				
				curl_close($ch);

				if ($print)
					echo "Called $callback.\nResponse: $response\n";
			}
		}
	}
	
	return array("changed" => $ch, "pending" => $pending, "paid" => $booked_oughta);
}

function sc24_fee_on_top($amount) {
	return $amount + sc24_fee($amount);
}

function sc24_fee($amount) {
	if ($amount < 1)
		return 0.05;
	if ($amount < 150)
		return $amount * 0.05;
	if ($amount < 200)
		return 7.5;
	if ($amount < 5000)
		return $amount * 0.0375;
	if ($amount < 12500)
		return 187.5;
	return $amount * 0.015;
}

function sc24_without_fee($amount_with_fee) {
	if ($amount_with_fee < sc24_fee_on_top(1))
		return $amount_with_fee - 0.05;
	if ($amount_with_fee < sc24_fee_on_top(150))
		return $amount_with_fee / 1.05;
	if ($amount_with_fee < sc24_fee_on_top(200))
		return $amount_with_fee - 7.5;
	if ($amount_with_fee < sc24_fee_on_top(5000))
		return $amount_with_fee / 1.0375;
	if ($amount_with_fee < sc24_fee_on_top(12500))
		return $amount_with_fee - 187.5;
	return $amount_with_fee / 1.015;
}

function sc24_fee_from_awf($amount_with_fee)
{
	return $amount_with_fee - sc24_without_fee($amount_with_fee);
}

?>