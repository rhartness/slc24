<?php

function withdraw_slc($address, $amount=0, $type="", $intern_txid=0) {
	global $solidcoin, $db;
	
	if (!is_array($address)) {
		$address = mysql_real_escape_string($address, $db);
		
		$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE address = '$address'";
		$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
		
		if ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a)) {
			if ($amount <= 0) {
				return array("success" => true, "type" => "intern", "to_user" => $row_transfer_deposit_address_a["user"]);
			}
			
			$ins_transfer_deposit_a = "INSERT INTO transfer_deposit (deposit_address, txid, amount, filing_time, type) VALUES ($row_transfer_deposit_address_a[id], $intern_txid, '$amount', NOW(), 'intern')";
			mysql_query($ins_transfer_deposit_a);
			
			$did = mysql_insert_id();
			
			$itxid = add_transaction($row_transfer_deposit_address_a["user"], "in", "intern", $amount, "SLC", "transfer_deposit", $did);
			
			$slt_transfer_deposit_a = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
			$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
			$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
			$total_amount = $row_transfer_deposit_a["amount"];
			
			if ($row_transfer_deposit_address_a["send_mail"] == "yes")
			{
				$slt_user_c = "SELECT * FROM user WHERE id = '$row_transfer_deposit_address_a[user]'";
				$rlt_user_c = mysql_query($slt_user_c);
				$row_user_c = mysql_fetch_assoc($rlt_user_c);
			
				$content = "";
				
				$content .= "Dear user,<br />\n<br />\nYou have received ".nice_format($amount, false, 0, 4)." Solidcoins with your deposit address <a href=\"http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address\">$address</a>.<br />\n<br />\n";
				$content .= "More information about this deposit: <a href=\"http://slc24.com/?c=services/transfer/deposit&amp;id=$did\">http://slc24.com/?c=services/transfer/deposit&amp;id=$did</a><br />\n";
				$content .= "More information about this deposit address: <a href=\"http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address\">http://slc24.com/?c=services/transfer/deposit_address&amp;a=$address</a><br />\n";
				$content .= "Additional information:<br />\n";
				$content .= "Type: internal<br />\n";
				$content .= "Amount received: ".nice_format($amount, false, 0, 4)." Solidcoins<br />\n";
				$content .= "Total amount received: ".nice_format($total_amount, false, 0, 4)." Solidcoins<br />\n";
				if ($row_transfer_deposit_address_a["group"])
					$content .= "Group: ".$row_transfer_deposit_address_a["group"]."<br />\n";
				if ($row_transfer_deposit_address_a["data"])
					$content .= "Data: ".$row_transfer_deposit_address_a["data"]."<br />\n";
				$content .= "Fee: 0 Solidcoins<br />\n<br />\n";
				$content .= "Your sc24 team";
				
				send_mail("Deposit of ".nice_format($amount, false, 0, 4)." Solidcoins arrived", $content, $row_user_c["email"]);
			}
			
			if (strlen($row_transfer_deposit_address_a["callback"]) > 0)
			{
				$callback = $row_transfer_deposit_address_a["callback"];
				
				if (!strpos($callback, "?")) {
					$callback .= "?id=$row_transfer_deposit_address_a[id]&group=".urlencode($row_transfer_deposit_address_a["group"])."&address=$address&total_amount=".nice_format($total_amount, false, 0, 4)."&new_amount=".nice_format($amount, false, 0, 4)."&fee=0&type=intern";
				} else {
					$callback .= "&id=$row_transfer_deposit_address_a[id]&group=".urlencode($row_transfer_deposit_address_a["group"])."&address=$address&total_amount=".nice_format($total_amount, false, 0, 4)."&new_amount=".nice_format($amount, false, 0, 4)."&fee=0&type=intern";
				}
				
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $callback);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				$response = curl_exec($ch);
				
				curl_close($ch);
			}
			
			return array("success" => true, "type" => "intern", "txid" => $itxid, "to_user" => $row_transfer_deposit_address_a["user"]);
		}
		
		$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'SLC' AND address = '$address'";
		$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
		
		if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {
			if ($amount <= 0) {
				return array("success" => true, "type" => "intern", "to_user" => $row_deposit_address_a["user"]);
			}
		
			$itxid = add_transaction($row_deposit_address_a["user"], "in", "intern", $amount, "SLC", "deposit", $intern_txid);
		
			return array("success" => true, "type" => "intern", "txid" => $itxid, "to_user" => $row_deposit_address_a["user"]);
		}
		
		if ($amount <= 0) {
			return array("success" => true, "type" => "extern");
		}

		if (!isset($solidcoin)) {
			$solidcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:7556/");
		}
		
		$txid = $solidcoin->sc_sendtoaddress("main", $address, (int)round($amount * 10000), (int)20000);
		
		$ins_crypto_transaction_a = "INSERT INTO crypto_transaction (txid) VALUES ('$txid')";
		mysql_query($ins_crypto_transaction_a);

		return array("success" => true, "type" => "extern", "txid" => mysql_insert_id(), "to_user" => 0);
	} else {
		if (!is_array($amount) || count($address) != count($amount) || count($amount) == 0 || $type != "extern") {
			return array("success" => false);
		}
		
		$addresses = $address;
		
		$j = 0;
		foreach ($addresses as $i => $address) {
			if ($i != $j) {
				return array("success" => false);
			}
		
			$j++;
		}
		
		$withdrawal_array = array();
		
		foreach ($addresses as $i => $address) {
			$withdrawal_array[$address] = "".round($amount[$i] * 10000);
		}
		
		//print_r($withdrawal_array);
		$txid = $solidcoin->sc_sendmany("main", "", $withdrawal_array);
		//print_r($withdrawal_array);
		
		$ins_crypto_transaction_a = "INSERT INTO crypto_transaction (txid) VALUES ('$txid')";
		mysql_query($ins_crypto_transaction_a);
		
		return array("success" => true, "txid" => mysql_insert_id());
	}
}

function withdraw_btc($address, $amount=0, $type="", $intern_txid=0) {
	global $bitcoin, $db;
	
	$address = mysql_real_escape_string($address, $db);
	
	$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'BTC' AND address = '$address'";
	$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
	
	if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {
		if ($amount <= 0) {
			return array("success" => true, "type" => "intern", "to_user" => $row_deposit_address_a["user"]);
		}
	
		$itxid = add_transaction($row_deposit_address_a["user"], "in", "intern", $amount, "BTC", "deposit", $intern_txid);
	
		return array("success" => true, "type" => "intern", "txid" => $itxid, "to_user" => $row_deposit_address_a["user"]);
	}
	
	if ($amount <= 0) {
		return array("success" => true, "type" => "extern");
	}

	if (!isset($bitcoin)) {
		$bitcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:8332/");
	}
	
	$txid = $bitcoin->sendtoaddress($address, (double)($amount));
	
	$ins_crypto_transaction_a = "INSERT INTO crypto_transaction (txid) VALUES ('$txid')";
	mysql_query($ins_crypto_transaction_a);

	return array("success" => true, "type" => "extern", "txid" => mysql_insert_id(), "to_user" => 0);
}

function withdraw_nmc($address, $amount=0, $type="", $intern_txid=0) {
	global $namecoin, $db;
	
	$address = mysql_real_escape_string($address, $db);
	
	$slt_deposit_address_a = "SELECT * FROM deposit_address WHERE currency = 'NMC' AND address = '$address'";
	$rlt_deposit_address_a = mysql_query($slt_deposit_address_a);
	
	if ($row_deposit_address_a = mysql_fetch_assoc($rlt_deposit_address_a)) {
		if ($amount <= 0) {
			return array("success" => true, "type" => "intern", "to_user" => $row_deposit_address_a["user"]);
		}
	
		$itxid = add_transaction($row_deposit_address_a["user"], "in", "intern", $amount, "NMC", "deposit", $intern_txid);
	
		return array("success" => true, "type" => "intern", "txid" => $itxid, "to_user" => $row_deposit_address_a["user"]);
	}
	
	if ($amount <= 0) {
		return array("success" => true, "type" => "extern");
	}

	if (!isset($bitcoin)) {
		$namecoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:8336/");
	}
	
	$txid = $namecoin->sendtoaddress($address, (double)($amount));
	
	$ins_crypto_transaction_a = "INSERT INTO crypto_transaction (txid) VALUES ('$txid')";
	mysql_query($ins_crypto_transaction_a);

	return array("success" => true, "type" => "extern", "txid" => mysql_insert_id(), "to_user" => 0);
}

?>