<?php

function process_cryptotransaction($txid, $address, $amount, $time, $currency="SLC", $print=true) {
	global $db;
	
	$txid = mysql_real_escape_string($txid, $db);
	$address = mysql_real_escape_string($address, $db);
	
	$slt_crypto_transaction_a = "SELECT * FROM crypto_transaction WHERE txid = '$txid'";
	$rlt_crypto_transaction_a = mysql_query($slt_crypto_transaction_a);
	
	if (!mysql_fetch_assoc($rlt_crypto_transaction_a)) {
		$ins_crypto_transaction_a = "INSERT INTO crypto_transaction (txid) VALUES ('$txid')";
		mysql_query($ins_crypto_transaction_a);
		
		$itxid = mysql_insert_id();
		
		if ($currency == "SLC") {
			$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE address = '$address'";
			$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
			
			if ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a)) {				
				$ins_transfer_deposit_a = "INSERT INTO transfer_deposit (deposit_address, txid, amount, filing_time, type) VALUES ($row_transfer_deposit_address_a[id], $itxid, '$amount', FROM_UNIXTIME($time), 'extern')";
				mysql_query($ins_transfer_deposit_a);
				
				$tid = mysql_insert_id();
				
				$slt_transfer_deposit_a = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
				$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
				$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
				$total_amount = $row_transfer_deposit_a["amount"];
				
				if (deposit_with_fee($amount, 0, $user_id) > 0) {
					add_transaction($row_transfer_deposit_address_a["user"], "in", "extern", deposit_with_fee($amount, 1, $user_id), "SLC", "transfer_deposit", $tid, $amount - deposit_with_fee($amount, 0, $user_id), $time);
				}
				
				if ($print) echo "New transfer deposit of $amount to $address (user $row_transfer_deposit_address_a[user]).\n";
				
				if ($row_transfer_deposit_address_a["send_mail"] == "yes")
				{
					$slt_user_c = "SELECT * FROM user WHERE id = '$row_transfer_deposit_address_a[user]'";
					$rlt_user_c = mysql_query($slt_user_c);
					$row_user_c = mysql_fetch_assoc($rlt_user_c);
				
					$content = "";
					
					$content .= "Dear user,<br />\n<br />\nYou have received ".nice_format($amount, false, 0, 4)." Solidcoins with your deposit address <a href=\"http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address\">$address</a>.<br />\n<br />\n";
					$content .= "More information about this deposit: <a href=\"http://slc24.com/?c=services/transfer/deposit&amp;id=$tid\">http://slc24.com/?c=services/transfer/deposit&amp;id=$tid</a><br />\n";
					$content .= "More information about this deposit address: <a href=\"http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address\">http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address</a><br /><br />\n";
					$content .= "Additional information:<br />\n";
					$content .= "Type: external<br />\n";
					$content .= "Amount received: ".nice_format($amount, false, 0, 4)." Solidcoins<br />\n";
					$content .= "Total amount received: ".nice_format($total_amount, false, 0, 4)." Solidcoins<br />\n";
					if ($row_transfer_deposit_address_a["group"])
						$content .= "Group: ".$row_transfer_deposit_address_a["group"]."<br />\n";
					if ($row_transfer_deposit_address_a["data"])
						$content .= "Data: ".$row_transfer_deposit_address_a["data"]."<br />\n";
					$content .= "Fee: ".nice_format($amount - deposit_with_fee($amount, 0, $user_id), false, 0, 4)." Solidcoins<br />\n<br />\n";
					$content .= "Your sc24 team";
					
					send_mail("Deposit of ".nice_format($amount, false, 0, 4)." Solidcoins received", $content, $row_user_c["email"]);

					if ($print) echo "Sent mail to $row_user_c[email].\n";
				}
				
				if (strlen($row_transfer_deposit_address_a["callback"]) > 0)
				{
					$callback = $row_transfer_deposit_address_a["callback"];
					
					if (!strpos($callback, "?")) {
						$callback .= "?id=$row_transfer_deposit_address_a[id]&group=".urlencode($row_transfer_deposit_address_a["group"])."&address=$address&total_amount=".nice_format($total_amount, false, 0, 4)."&new_amount=".nice_format($amount, false, 0, 4)."&fee=".nice_format($amount - deposit_with_fee($amount, 0, $user_id), false, 0, 4)."&type=extern";
					} else {
						$callback .= "&id=$row_transfer_deposit_address_a[id]&group=".urlencode($row_transfer_deposit_address_a["group"])."&address=$address&total_amount=".nice_format($total_amount, false, 0, 4)."&new_amount=".nice_format($amount, false, 0, 4)."&fee=".nice_format($amount - deposit_with_fee($amount, 0, $user_id), false, 0, 4)."&type=extern";
					}
					
					$ch = curl_init();
					
					curl_setopt($ch, CURLOPT_URL, $callback);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

					$response = curl_exec($ch);
					
					curl_close($ch);

					if ($print) echo "Called $callback.\nResponse: $response\n";
				}
				
				return;
			}
			
			$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'SLC' AND address = '$address'";
			$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
			
			if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {	
				if ($amount > 0.01) {
					$id = add_transaction($row_deposit_address_a["user"], "in", "extern", $amount - 0.01, "SLC", "deposit", $itxid, 0.01, $time);
				}
			
				$udt_address_a = "UPDATE deposit_address SET booked = booked + '$amount', used = 'yes' WHERE id = '$row_deposit_address_a[id]'";
				mysql_query($udt_address_a);
			
				if ($print) echo "New deposit of $amount SLC to $address (user $row_deposit_address_a[user]).\n";
			
				return;
			}
			
			if ($print) echo "Unknown action (txid: $txid, address: $address, amount: $amount).\n";
			
			return;			
		}
		
		if ($currency == "BTC") {
			$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'BTC' AND address = '$address'";
			$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
			
			if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {			
				add_transaction($row_deposit_address_a["user"], "in", "extern", $amount, "BTC", "deposit", $itxid, 0, $time);
			
				$udt_address_a = "UPDATE deposit_address SET booked = booked + '$amount', used = 'yes' WHERE id = '$row_deposit_address_a[id]'";
				mysql_query($udt_address_a);
			
				if ($print) echo "New deposit of $amount BTC to $address (user $row_deposit_address_a[user]).\n";
			
				return;
			}
			
			if ($print) echo "Unknown action (txid: $txid, address: $address, amount: $amount).\n";
			
			return;			
		}
		
		if ($currency == "NMC") {
			$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'NMC' AND address = '$address'";
			$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
			
			if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {			
				add_transaction($row_deposit_address_a["user"], "in", "extern", $amount, "NMC", "deposit", $itxid, 0, $time);
			
				$udt_address_a = "UPDATE deposit_address SET booked = booked + '$amount', used = 'yes' WHERE id = '$row_deposit_address_a[id]'";
				mysql_query($udt_address_a);
			
				if ($print) echo "New deposit of $amount NMC to $address (user $row_deposit_address_a[user]).\n";
			
				return;
			}
			
			if ($print) echo "Unknown action (txid: $txid, address: $address, amount: $amount).\n";
			
			return;			
		}
	}
}

?>