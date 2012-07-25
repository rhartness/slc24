<?php
include_once "../inc/conn.php";
include_once "../inc/db_locks.php";
include_once "../inc/format.php";
include_once "../inc/account.php";
include_once "../inc/fees.php";
include_once "../inc/withdraw.php";
include_once "../inc/address.php";
include_once "../inc/process_cryptotransaction.php";
include_once "../inc/brief_check.php";
include_once "../inc/json/jsonRPCClient.php";

$query = explode("/", $_GET["query"]);
check_authentification();


$currencies1 = array( "slc", "btc", "nmc" );
$currencies2 = array( "btc", "nmc" );
$currencies3 = array( "slc", "btc", "nmc" ); // cryptocurrencies

$static_withdrawal_fee_single = 0.3;
$static_withdrawal_fee_bunch = 0.1;
$withdrawal_fee = 0.006;
$static_deposit_fee = 0.1;
$deposit_fee = 0.004;

get_connection();

// 2 General

if ($query[0] == "balance") { // 2.1 Balance
	require_authentification();
	if ($query[1] == "history") { // 2.1.2 Balance history
		if (isset($query[2])) {
			$currency = $query[2];
			if (!in_array($currency, $currencies1)) {
				json_error("No such currency.");
			}
			$slt_transaction_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transaction WHERE user = '$user_id' AND currency = '$currency' ORDER BY filing_time DESC, id DESC LIMIT 200";
			
		} else {
			$slt_transaction_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transaction WHERE user = '$user_id' ORDER BY filing_time DESC, id DESC LIMIT 200";
		}
		$transactions = array();
		$rlt_transaction_a = mysql_query($slt_transaction_a);
		while ($row_transaction_a = mysql_fetch_assoc($rlt_transaction_a)) {
			if ($row_transaction_a["currency"] == "SLC") {
				$amount = api_sc_format($row_transaction_a["amount"]);
				$balance = api_sc_format($row_transaction_a["balance"]);
			} else {
				$amount = api_cur_format($row_transaction_a["amount"]);
				$balance = api_cur_format($row_transaction_a["balance"]);
			}
			$transactions[] = array(
				"type" => $row_transaction_a["type"], 
				"dir" => $row_transaction_a["direction"], 
				"amount" => $amount, 
				"currency" => strtolower($row_transaction_a["currency"]), 
				"balance" => $balance, 
				"time" => $row_transaction_a["filing_time_u"], 
				"info" => $row_transaction_a["info"], 
				"info_id" => $row_transaction_a["info_id"]);
		}
		json_success($transactions);
	} else { // 2.1.1 Get balance
		if (isset($query[1])) {
			$currency = $query[1];
			if (!in_array($currency, $currencies1)) {
				json_error("No such currency.");
			}
			
			if ($currency == "slc") {
				json_success(array("balance" => api_sc_format(get_balance($user_id, "SLC"))));
			} else {
				json_success(array("balance" => api_cur_format(get_balance($user_id, strtoupper($currency)))));
			}
		} else {
			$slt_account_a = "SELECT currency, amount FROM account WHERE user = '$user_id'";
			$rlt_account_a = mysql_query($slt_account_a);
			
			$balances = array();
			while ($row_account_a = mysql_fetch_assoc($rlt_account_a)) {
				if ($row_account_a["currency"] == "slc") {
					$balances[strtolower($row_account_a["currency"])] = api_sc_format($row_account_a["amount"]);
				} else {
					$balances[strtolower($row_account_a["currency"])] = api_cur_format($row_account_a["amount"]);
				}
			}
			json_success($balances);
		}
	}
}

if ($query[0] == "internal_transfer") { // 2.2 Internal transfer
	require_authentification();
	if ($query[1] == "check_address") { // 2.2.2 Check address for Solidcoin24 affiliation
		$currency = $query[2];
		if (!in_array($currency, $currencies3)) {
			json_error("No such currency.");
		}
		
		$address = mysql_real_escape_string($_GET["a"], $db);
		
		if ($currency == "slc") {
			if (check_slc_address($address)) {
				$result = withdraw_slc($address);
				
				if ($result["type"] == "intern") {
					json_success(array("hash" => md5($result["to_user"]."gFdj#432_pyq54")));
				} else {
					json_success(array("hash" => null));
				}
			} else {
				json_error("Invalid address.");
			}
		}
		if ($currency == "btc") {
			if (check_btc_address($address)) {
				$result = withdraw_btc($address);
				
				if ($result["type"] == "intern") {
					json_success(array("hash" => md5($result["to_user"]."gFdj#432_pyq54")));
				} else {
					json_success(array("hash" => null));
				}
			} else {
				json_error("Invalid address.");
			}
		}
		if ($currency == "nmc") {
			if (check_nmc_address($address)) {
				$result = withdraw_nmc($address);
				
				if ($result["type"] == "intern") {
					json_success(array("hash" => md5($result["to_user"]."gFdj#432_pyq54")));
				} else {
					json_success(array("hash" => null));
				}
			} else {
				json_error("Invalid address.");
			}
		}
		
		json_error("Unknown currency.");
	}
	
	if (isset($query[1])) { // 2.2.1 Transfer to user
		$currency = $query[1];
		if (!in_array($currency, $currencies1)) {
			json_error("No such currency.");
		}
		
		$recipient = mysql_real_escape_string($_GET["r"], $db);
		$amount = $_GET["am"];
		
		if (!is_numeric($amount) || ($currency == "slc" && $amount < 0.0001) || ($currency != "slc" && $amount < 0.00000001)) {
			json_error("Please enter a valid amount.");
		}
		
		$currency = strtoupper($currency);
		
		$balance = get_balance($user_id, $currency);
		
		if ($balance < $amount) {
			json_error("You don't have that much.");
		}
		
		$slt_user_a = "SELECT * FROM user WHERE email = '$recipient'";
		$rlt_user_a = mysql_query($slt_user_a);
		
		if ($row_user_a = mysql_fetch_assoc($rlt_user_a)) {
			$txid_out = add_transaction($user_id, "out", "intern", $amount, $currency, "internal_transfer", 0);
			$txid_in = add_transaction($row_user_a["id"], "in", "intern", $amount, $currency, "internal_transfer", $txid_out);
			
			$udt_transaction_a = "UPDATE transaction SET info_id = '$txid_in' WHERE id = '$txid_out'";
			mysql_query($udt_transaction_a);
			
			json_success(array("txid_in" => $txid_out, "txid_out" => $txid_in));
		} else {
			json_error("A user with that email does not exist.");
		}
	} else {
		json_error("Please specify a currency.");
	}
	
	json_error("Unknown call.");
}

// 3 Exchange

if ($query[0] == "exchange") {
	if ($query[1] == "ticker") { // 3.1.1 Ticker
		if (isset($query[2])) {
			$currency = $query[2];
			if (!in_array($currency, $currencies2)) {
				json_error("No such currency");
			}
			
			$currency = strtoupper($currency);
			
			$slt_trade_vol_avg = "SELECT SUM(amount) AS volume, (SUM(price * amount) / SUM(amount)) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
			$rlt_trade_vol_avg = mysql_query($slt_trade_vol_avg);
			$row_trade_vol_avg = mysql_fetch_assoc($rlt_trade_vol_avg);
			
			$slt_trade_last = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' ORDER BY trade_time DESC LIMIT 1";
			$rlt_trade_last = mysql_query($slt_trade_last);
			$row_trade_last = mysql_fetch_assoc($rlt_trade_last);
			
			$slt_trade_high = "SELECT MAX(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
			$rlt_trade_high = mysql_query($slt_trade_high);
			$row_trade_high = mysql_fetch_assoc($rlt_trade_high);
			
			$slt_trade_low = "SELECT MIN(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
			$rlt_trade_low = mysql_query($slt_trade_low);
			$row_trade_low = mysql_fetch_assoc($rlt_trade_low);
			
			$slt_trade_order_bid = "SELECT MAX(price) AS price FROM trade_order WHERE currency = '$currency' AND type = 'buy' AND active = 'yes' AND finished = 'no'";
			$rlt_trade_order_bid = mysql_query($slt_trade_order_bid);
			$row_trade_order_bid = mysql_fetch_assoc($rlt_trade_order_bid);
			
			$slt_trade_order_ask = "SELECT MIN(price) AS price FROM trade_order WHERE currency = '$currency' AND type = 'sell' AND active = 'yes' AND finished = 'no'";
			$rlt_trade_order_ask = mysql_query($slt_trade_order_ask);
			$row_trade_order_ask = mysql_fetch_assoc($rlt_trade_order_ask);
			
			$ticker = array(
				"ask" => api_price_format($row_trade_order_ask["price"]),
				"bid" => api_price_format($row_trade_order_bid["price"]),
				"last" => api_price_format($row_trade_last["price"]),
				"last_when" => (int)$row_trade_last["trade_time_u"],
				"volume" => api_sc_format($row_trade_vol_avg["volume"]),
				"high" => api_price_format($row_trade_high["price"]),
				"low" => api_price_format($row_trade_low["price"]),
				"average" => api_price_format($row_trade_vol_avg["price"]));
			
			json_success($ticker);
		} else {
			$ticker = array();
			
			foreach ($currencies2 as $currency) {
				$currency = strtoupper($currency);
			
				$slt_trade_vol_avg = "SELECT SUM(amount) AS volume, (SUM(price * amount) / SUM(amount)) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
				$rlt_trade_vol_avg = mysql_query($slt_trade_vol_avg);
				$row_trade_vol_avg = mysql_fetch_assoc($rlt_trade_vol_avg);
				
				$slt_trade_last = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' ORDER BY trade_time DESC LIMIT 1";
				$rlt_trade_last = mysql_query($slt_trade_last);
				$row_trade_last = mysql_fetch_assoc($rlt_trade_last);
				
				$slt_trade_high = "SELECT MAX(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
				$rlt_trade_high = mysql_query($slt_trade_high);
				$row_trade_high = mysql_fetch_assoc($rlt_trade_high);
				
				$slt_trade_low = "SELECT MIN(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
				$rlt_trade_low = mysql_query($slt_trade_low);
				$row_trade_low = mysql_fetch_assoc($rlt_trade_low);
				
				$slt_trade_order_bid = "SELECT MAX(price) AS price FROM trade_order WHERE currency = '$currency' AND type = 'buy' AND active = 'yes' AND finished = 'no'";
				$rlt_trade_order_bid = mysql_query($slt_trade_order_bid);
				$row_trade_order_bid = mysql_fetch_assoc($rlt_trade_order_bid);
				
				$slt_trade_order_ask = "SELECT MIN(price) AS price FROM trade_order WHERE currency = '$currency' AND type = 'sell' AND active = 'yes' AND finished = 'no'";
				$rlt_trade_order_ask = mysql_query($slt_trade_order_ask);
				$row_trade_order_ask = mysql_fetch_assoc($rlt_trade_order_ask);
				
				$ticker[strtolower($currency)] = array(
					"ask" => api_price_format($row_trade_order_ask["price"]),
					"bid" => api_price_format($row_trade_order_bid["price"]),
					"last" => api_price_format($row_trade_last["price"]),
					"last_when" => (int)$row_trade_last["trade_time_u"],
					"volume" => api_sc_format($row_trade_vol_avg["volume"]),
					"high" => api_price_format($row_trade_high["price"]),
					"low" => api_price_format($row_trade_low["price"]),
					"average" => api_price_format($row_trade_vol_avg["price"]));
			}
			json_success($ticker);
		}
	}
	
	if ($query[1] == "orderbook") { // 3.1.2 Orderbook
		if (isset($query[2])) {
			$currency = $query[2];
			if (!in_array($currency, $currencies2)) {
				json_error("No such currency");
			}
			
			$currency = strtoupper($currency);
			
			$orderbook = array();
			
			$slt_trade_order_bid = "SELECT price, SUM(amount - completed) AS amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'buy' GROUP BY ROUND(price * 100000000) ORDER BY price DESC";
			$rlt_trade_order_bid = mysql_query($slt_trade_order_bid);
			
			while ($row_trade_order_bid = mysql_fetch_assoc($rlt_trade_order_bid)) {
				$orderbook["bid"][] = array("price" => api_price_format($row_trade_order_bid["price"]), "amount" => api_sc_format($row_trade_order_bid["amount"]));
			}
			
			$slt_trade_order_ask = "SELECT price, SUM(amount - completed) AS amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'sell' GROUP BY ROUND(price * 100000000) ORDER BY price ASC";
			$rlt_trade_order_ask = mysql_query($slt_trade_order_ask);
			
			while ($row_trade_order_ask = mysql_fetch_assoc($rlt_trade_order_ask)) {
				$orderbook["ask"][] = array("price" => api_price_format($row_trade_order_ask["price"]), "amount" => api_sc_format($row_trade_order_ask["amount"]));
			}
			
			json_success($orderbook);
		} else {
			$orderbook = array( "btc" => array(), "nmc" => array() );
			
			foreach ($currencies2 as $currency) {
				$currency = strtoupper($currency);
				
				$slt_trade_order_bid = "SELECT price, SUM(amount - completed) AS amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'buy' GROUP BY ROUND(price * 100000000) ORDER BY price DESC";
				$rlt_trade_order_bid = mysql_query($slt_trade_order_bid);
				
				while ($row_trade_order_bid = mysql_fetch_assoc($rlt_trade_order_bid)) {
					$orderbook[strtolower($currency)]["bid"][] = array("price" => api_price_format($row_trade_order_bid["price"]), "amount" => api_sc_format($row_trade_order_bid["amount"]));
				}
				
				$slt_trade_order_ask = "SELECT price, SUM(amount - completed) AS amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'sell' GROUP BY ROUND(price * 100000000) ORDER BY price ASC";
				$rlt_trade_order_ask = mysql_query($slt_trade_order_ask);
				
				while ($row_trade_order_ask = mysql_fetch_assoc($rlt_trade_order_ask)) {
					$orderbook[strtolower($currency)]["ask"][] = array("price" => api_price_format($row_trade_order_ask["price"]), "amount" => api_sc_format($row_trade_order_ask["amount"]));
				}
			}
			
			json_success($orderbook);
		}
	}
	
	if ($query[1] == "trades") { // 3.1.3 Trades
		if (isset($query[2])) {
			$currency = $query[2];
			if (!in_array($currency, $currencies2)) {
				json_error("No such currency");
			}
			
			$currency = strtoupper($currency);
			
			$trades = array();
			
			if (isset($_GET["t"])) {
				$timestamp = $_GET["t"];
				if (!is_numeric($timestamp))
					json_error("Invalid timestamp");
				$slt_trade_a = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= '$timestamp' ORDER BY trade_time DESC LIMIT 2000";
			} else {
				$slt_trade_a = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' ORDER BY trade_time DESC LIMIT 2000";
			}
			$rlt_trade_a = mysql_query($slt_trade_a);
			
			while ($row_trade_a = mysql_fetch_assoc($rlt_trade_a)) {
				$trades[] = array("id" => $row_trade_a["id"], "time" => $row_trade_a["trade_time_u"], "price" => api_price_format($row_trade_a["price"]), "amount" => api_sc_format($row_trade_a["amount"]));
			}
			
			json_success($trades);
		} else {
			$trades = array( "btc" => array(), "nmc" => array() );
			
			foreach ($currencies2 as $currency) {
				$currency = strtoupper($currency);
			
				if (isset($_GET["t"])) {
				$timestamp = $_GET["t"];
				if (!is_numeric($timestamp))
					json_error("Invalid timestamp");
				$slt_trade_a = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= '$timestamp' ORDER BY trade_time DESC LIMIT 2000";
				} else {
					$slt_trade_a = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' ORDER BY trade_time DESC LIMIT 2000";
				}
				$rlt_trade_a = mysql_query($slt_trade_a);
				
				while ($row_trade_a = mysql_fetch_assoc($rlt_trade_a)) {
					$trades[strtolower($currency)][] = array("id" => $row_trade_a["id"], "time" => $row_trade_a["trade_time_u"], "price" => api_price_format($row_trade_a["price"]), "amount" => api_sc_format($row_trade_a["amount"]));
				}
			}
			
			json_success($trades);
		}
	}
	
	if ($query[1] == "order") { // 3.2 Trading
		require_authentification();
		if ($query[2] == "new") { // 3.2.1 New order
			$currency = $query[3];
			if (!in_array($currency, $currencies2)) {
				json_error("No such currency");
			}
			
			$currency = strtoupper($currency);
			
			$type = $query[4];
			
			if ($type != "sell" && $type != "buy") {
				json_error("Invalid type");
			}
			
			$price = $_GET["p"];
			$slc_amount = $_GET["am"];
			
			if (!is_numeric($price) || $price < 0) {
				json_error("Invalid price");
			}
			if (!is_numeric($slc_amount) || $slc_amount < 0.01) {
				json_error("Invalid amount");
			}
			
			if (!get_lock("global")) {
				json_error("Too many queries. Please try again later.");
			}
			
			$price = round($price * 100000000) / 100000000;
			
			$cur_amount = $slc_amount * $price;
			
			if ($type == "sell")
			{
				if ($slc_amount > get_balance($user_id, "SLC"))
					json_error("You don't have that much");
				
				$slt_trade_order_a = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND ROUND(price * 100000000) = ROUND($price * 100000000) AND currency = '$currency' AND user = '$user_id'";
				$rlt_trade_order_a = mysql_query($slt_trade_order_a);
			
				if (mysql_num_rows($rlt_trade_order_a) == 0)
				{
					$ins_trade_order_a = "INSERT INTO trade_order (type, user, currency, price, amount, completed, active, finished, filing_time, change_time, finishing_time) ".
						"VALUES ('sell', '$user_id', '$currency', '$price', '$slc_amount', '0', 'yes', 'no', NOW(), NOW(), '0000-00-00 00:00:00')";
					mysql_query($ins_trade_order_a);
					
					$tid = mysql_insert_id();
					
					add_transaction($user_id, "out", "intern", $slc_amount, "SLC", "trade_placement", $tid);
					
					json_success(array("id" => (int)$tid));
				}
				else
				{
					$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
					
					$udt_trade_order_a = "UPDATE trade_order SET amount = amount + $slc_amount, change_time = NOW() WHERE id = $row_trade_order_a[id]";
					mysql_query($udt_trade_order_a);
					
					add_transaction($user_id, "out", "intern", $slc_amount, "SLC", "trade_increase", $row_trade_order_a["id"]);
					
					json_success(array("id" => (int)$row_trade_order_a["id"]));
				}
			}
			else
			{
				if ($cur_amount * 1.004 > get_balance($user_id, $currency))
					json_error("You don't have that much");
					
				$slt_trade_order_a = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND ROUND(price * 100000000) = ROUND($price * 100000000) AND currency = '$currency' AND user = '$user_id'";
				$rlt_trade_order_a = mysql_query($slt_trade_order_a);
			
				if (mysql_num_rows($rlt_trade_order_a) == 0)
				{
					$ins_trade_order_a = "INSERT INTO trade_order (type, user, currency, price, amount, completed, active, finished, filing_time, change_time, finishing_time) ".
						"VALUES ('buy', '$user_id', '$currency', '$price', '$slc_amount', '0', 'yes', 'no', NOW(), NOW(), '0000-00-00 00:00:00')";
					mysql_query($ins_trade_order_a);
					
					$tid = mysql_insert_id();
					
					add_transaction($user_id, "out", "intern", $cur_amount * 1.004, $currency, "trade_placement", $tid, $cur_amount * 0.004);
					
					json_success(array("id" => (int)$tid));
				}
				else
				{
					$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
					
					$udt_trade_order_a = "UPDATE trade_order SET amount = amount + $slc_amount, change_time = NOW() WHERE id = $row_trade_order_a[id]";
					mysql_query($udt_trade_order_a);
					
					add_transaction($user_id, "out", "intern", $cur_amount * 1.004, $currency, "trade_increase", $row_trade_order_a["id"], $cur_amount * 0.004);
					
					json_success(array("id" => (int)$row_trade_order_a["id"]));
				}
			}
			json_error("Unknown call.");
		}
		
		if ($query[2] == "cancel") {
			$id = $_GET["id"];
			
			if (!is_numeric($id)) {
				json_error("Invalid id");
			}
			
			if (!get_lock("global")) {
				json_error("Too many queries. Please try again later.");
			}
			
			$slt_trade_order_a = "SELECT * FROM trade_order WHERE id = '$id'";
			$rlt_trade_order_a = mysql_query($slt_trade_order_a);
			$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
			
			if ($row_trade_order_a["user"] != $user_id) {
				json_error("Invalid id");
			}
			
			if ($row_trade_order_a["active"] == "yes") {
				if ($row_trade_order_a["type"] == "sell") {
					$refund = $row_trade_order_a["amount"] - $row_trade_order_a["completed"];
				
					add_transaction($user_id, "in", "intern", $refund, "SLC", "trade_cancellation", $id);
				
					$udt_trade_order_a = "UPDATE trade_order SET active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
					mysql_query($udt_trade_order_a);
					
					json_success();
				} elseif ($row_trade_order_a["type"] == "buy") {
					$refund = ($row_trade_order_a["amount"] - $row_trade_order_a["completed"]) * $row_trade_order_a["price"];
				
					add_transaction($user_id, "in", "intern", $refund * 1.004, $currency, "trade_cancellation", $id, -$refund * 0.004);
				
					$udt_trade_order_a = "UPDATE trade_order SET active = 'no', finishing_time = NOW() WHERE id = $row_trade_order_a[id]";
					mysql_query($udt_trade_order_a);
					
					json_success();
				}
			}
			
			json_error("Trade already cancelled.");
		}
		
		if ($query[2] == "info") {
			$id = $_GET["id"];
			
			if (!is_numeric($id)) {
				json_error("Invalid id");
			}
			
			$slt_trade_order_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u, UNIX_TIMESTAMP(finishing_time) AS finishing_time_u FROM trade_order WHERE id = '$id'";
			$rlt_trade_order_a = mysql_query($slt_trade_order_a);
			$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
			
			if ($row_trade_order_a["user"] != $user_id) {
				json_error("Invalid id");
			}
			
			json_success(array("creation_time" => $row_trade_order_a["filing_time_u"], "finishing_time" => $row_trade_order_a["finishing_time_u"], "currency" => strtolower($row_trade_order_a["currency"]), "type" => $row_trade_order_a["type"], "price" => api_price_format($row_trade_order_a["price"]), "amount" => api_sc_format($row_trade_order_a["amount"]), "completed" => api_sc_format($row_trade_order_a["completed"]), "active" => $row_trade_order_a["active"]));
		}
		json_error("Unknown call.");
	}
	
	if ($query[1] == "orders") { // 3.2.4 List orders
		require_authentification();
		if (isset($query[2])) {
			$currency = $query[2];
			if (!in_array($currency, $currencies2)) {
				json_error("No such currency");
			}
			
			$currency = strtoupper($currency);
			
			$slt_trade_order_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u, UNIX_TIMESTAMP(finishing_time) AS finishing_time_u FROM trade_order WHERE user = '$user_id' AND currency = '$currency'";
			$rlt_trade_order_a = mysql_query($slt_trade_order_a);
			
			$orders = array();
			
			while ($row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a)) {
				$orders[] = array("id" => $row_trade_order_a["id"], "creation_time" => $row_trade_order_a["filing_time_u"], "finishing_time" => $row_trade_order_a["finishing_time_u"], "currency" => strtolower($row_trade_order_a["currency"]), "type" => $row_trade_order_a["type"], "price" => api_price_format($row_trade_order_a["price"]), "amount" => api_sc_format($row_trade_order_a["amount"]), "completed" => api_sc_format($row_trade_order_a["completed"]), "active" => $row_trade_order_a["active"]);
			}
			
			json_success($orders);
		} else {
			$orders = array( "btc" => array(), "nmc" => array() );
			
			foreach ($currencies2 as $currency) {
				$currency = strtoupper($currency);
			
				$slt_trade_order_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u, UNIX_TIMESTAMP(finishing_time) AS finishing_time_u FROM trade_order WHERE user = '$user_id' AND currency = '$currency'";
				$rlt_trade_order_a = mysql_query($slt_trade_order_a);
				
				while ($row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a)) {
					$orders[strtolower($currency)][] = array("id" => $row_trade_order_a["id"], "creation_time" => $row_trade_order_a["filing_time_u"], "finishing_time" => $row_trade_order_a["finishing_time_u"], "currency" => strtolower($row_trade_order_a["currency"]), "type" => $row_trade_order_a["type"], "price" => api_price_format($row_trade_order_a["price"]), "amount" => api_sc_format($row_trade_order_a["amount"]), "completed" => api_sc_format($row_trade_order_a["completed"]), "active" => $row_trade_order_a["active"]);
				}
			}
			
			json_success($orders);
		}
	}
}

// 4 Transfer

if ($query[0] == "transfer") {
	require_authentification();
	
	$solidcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:7556/");
	
	if ($query[1] == "deposit") { // 4.1 Deposits
		
		if ($query[2] == "new") { // 4.1.1 Create deposit address
			$group = mysql_real_escape_string($_GET["g"], $db);
			if ($_GET["m"] != "yes") $mail = "no"; else $mail = "yes";
			$callback = mysql_real_escape_string($_GET["c"], $db);
			$data = mysql_real_escape_string($_GET["d"], $db);			
					
			if (strlen($data) > 1024) {
				json_error("Data too long.");
			}
			
			if (strlen($group) > 255) {
				json_error("Group too long.");
			}
			
			$address = $solidcoin->sc_getnewaddress("main");
			
			$ins_transfer_deposit_address_a = "INSERT INTO transfer_deposit_address (user, `group`, address, callback, send_mail, creation_time, data) VALUES ('$user_id', '$group', '$address', '$callback', '$mail', NOW(), '$data')";
			mysql_query($ins_transfer_deposit_address_a);
			
			$id = mysql_insert_id();
			
			json_success(array("id" => $id, "address" => $address));
		}
		
		if ($query[2] == "info") {
			
			if (!get_lock("global")) {
				json_error("Too many queries. Please try again later.");
			}
			
			if (isset($_GET["id"])) { // 4.1.2 Get deposit address info by ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id");
				}
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE id = '$id'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid id");
				}
				
				brief_check();
				
				$slt_transfer_deposit_a = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$id'";
				$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
				$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
				$total_amount = $row_transfer_deposit_a["amount"];
					
				$slt_transfer_deposit_b = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]' AND type = 'extern'";
				$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
				$row_transfer_deposit_b = mysql_fetch_assoc($rlt_transfer_deposit_b);
				$total_amount_extern = $row_transfer_deposit_b["amount"];
				
				
				$udt_transfer_deposit_address_a = "UPDATE transfer_deposit_address SET last_check_amount = '$total_amount' WHERE id = $row_transfer_deposit_address_a[id]";
				mysql_query($udt_transfer_deposit_address_a);
				
				$pending_amount = $solidcoin->sc_getreceivedbyaddress("main", $row_transfer_deposit_address_a["address"], 0) / 10000 - $total_amount_extern;
				$new_amount = $total_amount - $row_transfer_deposit_address_a["last_check_amount"];
				
				json_success(array("address" => $row_transfer_deposit_address_a["address"], "group" => $row_transfer_deposit_address_a["group"], "send_mail" => $row_transfer_deposit_address_a["send_mail"], "callback" => $row_transfer_deposit_address_a["callback"], "data" => $row_transfer_deposit_address_a["data"], "total_amount" => api_sc_format($total_amount), "pending_amount" => api_sc_format($pending_amount), "new_amount" => api_sc_format($new_amount)));
			}
			
			if (isset($_GET["a"])) { // 4.1.3 Get deposit address info by address
				$address = mysql_real_escape_string($_GET["a"], $db);
				
				//mysql_query("LOCK TABLES transfer_deposit_address WRITE, transfer_deposit WRITE, transaction WRITE");
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE address = '$address'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid address");
				}
				
				brief_check();
				
				$slt_transfer_deposit_a = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
				$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
				$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
				$total_amount = $row_transfer_deposit_a["amount"];
					
				$slt_transfer_deposit_b = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]' AND type = 'extern'";
				$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
				$row_transfer_deposit_b = mysql_fetch_assoc($rlt_transfer_deposit_b);
				$total_amount_extern = $row_transfer_deposit_b["amount"];
				
				
				$udt_transfer_deposit_address_a = "UPDATE transfer_deposit_address SET last_check_amount = '$total_amount' WHERE id = $row_transfer_deposit_address_a[id]";
				mysql_query($udt_transfer_deposit_address_a);
				
				//mysql_query("UNLOCK TABLES");
				
				$pending_amount = $solidcoin->sc_getreceivedbyaddress("main", $row_transfer_deposit_address_a["address"], 0) / 10000 - $total_amount_extern;
				$new_amount = $total_amount - $row_transfer_deposit_address_a["last_check_amount"];
				
				json_success(array("id" => (int)$row_transfer_deposit_address_a["id"], "group" => $row_transfer_deposit_address_a["group"], "send_mail" => $row_transfer_deposit_address_a["send_mail"], "callback" => $row_transfer_deposit_address_a["callback"], "data" => $row_transfer_deposit_address_a["data"], "total_amount" => api_sc_format($total_amount), "pending_amount" => api_sc_format($pending_amount), "new_amount" => api_sc_format($new_amount)));
			}
			
			if (isset($_GET["g"])) { // 4.1.4 Get deposit address info by group
				$group = mysql_real_escape_string($_GET["g"], $db);
				
				brief_check();
				
				$deposit_address_info = array();
				
				//mysql_query("BEGIN");
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE `group` = '$group' AND user = '$user_id'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				
				while ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a)) {				
					
					$slt_transfer_deposit_a = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
					$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
					$row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a);
					$total_amount = $row_transfer_deposit_a["amount"];
					
					$slt_transfer_deposit_b = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]' AND type = 'extern'";
					$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
					$row_transfer_deposit_b = mysql_fetch_assoc($rlt_transfer_deposit_b);
					$total_amount_extern = $row_transfer_deposit_b["amount"];
					
					
					$udt_transfer_deposit_address_a = "UPDATE transfer_deposit_address SET last_check_amount = '$total_amount' WHERE id = $row_transfer_deposit_address_a[id]";
					mysql_query($udt_transfer_deposit_address_a);
					
					$pending_amount = $solidcoin->sc_getreceivedbyaddress("main", $row_transfer_deposit_address_a["address"], 0) / 10000 - $total_amount_extern;
					$new_amount = $total_amount - $row_transfer_deposit_address_a["last_check_amount"];
					
					$deposit_address_info[] = array("id" => (int)$row_transfer_deposit_address_a["id"], "address" => $row_transfer_deposit_address_a["address"], "send_mail" => $row_transfer_deposit_address_a["send_mail"], "callback" => $row_transfer_deposit_address_a["callback"], "data" => $row_transfer_deposit_address_a["data"], "total_amount" => api_sc_format($total_amount), "pending_amount" => api_sc_format($pending_amount), "new_amount" => api_sc_format($new_amount));
				}
				
				//mysql_query("COMMIT");
				
				//mysql_query("UNLOCK TABLES");
				json_success($deposit_address_info);
			}
			
			//mysql_query("UNLOCK TABLES");
			json_error("Provide an address, an id or a group.");
		}
		
		if ($query[2] == "update") {
		
			if (isset($_GET["id"])) { // 4.1.5 Update deposit address by ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id");
				}
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE id = '$id'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid id");
				}
				
				$group = mysql_real_escape_string($_GET["g"], $db);
				if ($_GET["m"] == "yes") $mail = "yes"; elseif ($_GET["m"] == "no") $mail = "no";
				$callback = mysql_real_escape_string($_GET["c"], $db);
				$data = mysql_real_escape_string($_GET["d"], $db);
					
				if (strlen($data) > 1024) {
					json_error("Data too long.");
				}
				
				if (strlen($group) > 255) {
					json_error("Group too long.");
				}
				
				$ud = false;
				
				$udt_transfer_deposit_address_a = "UPDATE transfer_deposit_address SET ";
				if (isset($_GET["g"]))
					{ $udt_transfer_deposit_address_a .= "`group` = '$group'"; $ud = true; }
				if (isset($_GET["m"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "send_mail = '$mail'"; $ud = true; }
				if (isset($_GET["c"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "callback = '$callback'"; $ud = true; }
				if (isset($_GET["d"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "data = '$data'"; $ud = true; }
				$udt_transfer_deposit_address_a .= " WHERE id = '$row_transfer_deposit_address_a[id]'";
				mysql_query($udt_transfer_deposit_address_a);
				
				json_success();
			}
			
			if (isset($_GET["a"])) { // 4.1.6 Update deposit address by address
				$address = mysql_real_escape_string($_GET["a"], $db);
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE address = '$address'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid address");
				}
				
				$group = mysql_real_escape_string($_GET["g"], $db);
				if ($_GET["m"] == "yes") $mail = "yes"; elseif ($_GET["m"] == "no") $mail = "no";
				$callback = mysql_real_escape_string($_GET["c"], $db);
				$data = mysql_real_escape_string($_GET["d"], $db);
					
				if (strlen($data) > 1024) {
					json_error("Data too long.");
				}
				
				if (strlen($group) > 255) {
					json_error("Group too long.");
				}
				
				$udt_transfer_deposit_address_a = "UPDATE transfer_deposit_address SET ";
				if (isset($_GET["g"]))
					{ $udt_transfer_deposit_address_a .= "`group` = '$group'"; $ud = true; }
				if (isset($_GET["m"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "send_mail = '$mail'"; $ud = true; }
				if (isset($_GET["c"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "callback = '$callback'"; $ud = true; }
				if (isset($_GET["d"])) { if ($ud) $udt_transfer_deposit_address_a .= ", ";
					$udt_transfer_deposit_address_a .= "data = '$data'"; $ud = true; }
				$udt_transfer_deposit_address_a .= " WHERE id = '$row_transfer_deposit_address_a[id]'";
				mysql_query($udt_transfer_deposit_address_a);
				
				json_success();
			}
			
			json_error("Please provide an id or an address.");
		}
		
		if ($query[2] == "deposits") {
		
			if (isset($_GET["id"])) { // 4.1.7 Get deposits by deposit address ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id");
				}
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE id = '$id'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid id");
				}
				
				$deposits = array();
				
				$slt_transfer_deposit_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_deposit WHERE deposit_address = '$id'";
				$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
				
				while ($row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a)) {
					if ($row_transfer_deposit_a["type"] == "intern") {
						$deposits[] = array("txid" => $row_transfer_deposit_a["txid"], "time" => $row_transfer_deposit_a["filing_time_u"], "amount" => api_sc_format($row_transfer_deposit_a["amount"]), "type" => $row_transfer_deposit_a["type"]);
					} else {
						$deposits[] = array("txid" => crypte_transaction($row_transfer_deposit_a["txid"]), "time" => $row_transfer_deposit_a["filing_time_u"], "amount" => api_sc_format($row_transfer_deposit_a["amount"]), "type" => $row_transfer_deposit_a["type"]);
					}
				}
				
				json_success($deposits);
			}
			
			if (isset($_GET["a"])) { // 4.1.8 Get deposits by deposit address
				$address = mysql_real_escape_string($_GET["a"], $db);
				
				$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE address = '$address'";
				$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
				$row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a);
				
				if ($row_transfer_deposit_address_a["user"] != $user_id) {
					json_error("Invalid address");
				}
				
				$slt_transfer_deposit_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
				$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);
				
				while ($row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a)) {
					if ($row_transfer_deposit_a["type"] == "intern") {
						$deposits[] = array("txid" => $row_transfer_deposit_a["txid"], "time" => $row_transfer_deposit_a["filing_time_u"], "amount" => api_sc_format($row_transfer_deposit_a["amount"]), "type" => $row_transfer_deposit_a["type"]);
					} else {
						$deposits[] = array("txid" => crypte_transaction($row_transfer_deposit_a["txid"]), "time" => $row_transfer_deposit_a["filing_time_u"], "amount" => api_sc_format($row_transfer_deposit_a["amount"]), "type" => $row_transfer_deposit_a["type"]);
					}
				}
				
				json_success($deposits);
			}
			
			json_error("Please provide an address or an id.");
		
		}
		
		if ($query[2] == "addresses") { // 4.1.9 Get deposit addresses
		
			$deposit_addresses = array();
		
			$slt_transfer_deposit_address_a = "SELECT * FROM transfer_deposit_address WHERE user = '$user_id'";
			$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);
			
			while ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a)) {
				$deposit_addresses[] = array("id" => $row_transfer_deposit_address_a["id"], "address" => $row_transfer_deposit_address_a["address"], "group" => $row_transfer_deposit_address_a["group"], "send_mail" => $row_transfer_deposit_address_a["send_mail"], "callback" => $row_transfer_deposit_address_a["callback"]);
			}
			
			json_success($deposit_addresses);
		}
		
		json_error("Unknown call.");
	}
	
	if ($query[1] == "withdrawal") { // 4.2 Withdrawals
		
		if ($user_row["allow_api_withdrawals"] != "yes") {
			json_error("Currently disabled. Can be enabled on https://solidcoin24.com/?c=services/transfer/settings");
		}
			
		if ($query[2] == "new") {
			$address = mysql_real_escape_string($_GET["a"], $db);
			
			if (!check_slc_address($address)) {
				json_error("Invalid address.");
			}
			
			$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address'";
			$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
			
			if (mysql_num_rows($rlt_transfer_withdrawal_address_a) != 0) {
				json_error("A withdrawal address with that address already exists.");
			}
			
			$group = mysql_real_escape_string($_GET["g"], $db);
			$data = mysql_real_escape_string($_GET["d"], $db);
			
			$result = withdraw_slc($address);
			
			$type = $result["type"];
			
			$ins_transfer_withdrawal_address_a = "INSERT INTO transfer_withdrawal_address (user, `group`, address, creation_time, data, type) VALUES ('$user_id', '$group', '$address', NOW(), '$data', '$type')";
			mysql_query($ins_transfer_withdrawal_address_a);
			
			$id = mysql_insert_id();
			
			json_success(array("id" => $id, "type" => $type));
		}
		
		if ($query[2] == "withdraw") {
			
			if (!get_lock("global")) {
				json_error("Too many queries. Please try again later.");
			}
		
			if (!isset($_GET["id"])) {
				if (!is_array($_GET["a"])) { // 4.2.2 Create withdrawal address and withdraw
					// check address for validity
				
					$address = mysql_real_escape_string($_GET["a"], $db);
					$group = mysql_real_escape_string($_GET["g"], $db);
					$data = mysql_real_escape_string($_GET["d"], $db);
					
					if (!check_slc_address($address)) {
						json_error("Invalid address.");
					}
					
					//check amount for valitidy
					
					$amount = $_GET["am"];
					
					if (!is_numeric($amount) || $amount < 0.0001) {
						json_error("Invalid amount.");
					}
					
					$amount = round($amount * 10000) / 10000;
					
					if (withdrawal_with_fee($amount, 0, $user_id) > get_balance($user_id, "SLC")) {
						json_error("You don't have that much.");
					}
					
					$return = withdraw_slc($address);
					
					if (!$return["success"]) {
						json_error("An error occurred.");
					}
					
					if (strlen($data) > 1024) {
						json_error("Data too long.");
					}
					
					if (strlen($group) > 255) {
						json_error("Group too long.");
					}
				
					// START
					
					//check if address already exists
					
					//mysql_query("LOCK TABLES transfer_withdrawal_address WRITE, transfer_withdrawal, transaction WRITE, transaction WRITE");
					
					$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address' AND user = '$user_id'";
					$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
					
					if ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a)) { // yes, exists
						$id = $row_transfer_withdrawal_address_a["id"];
						$type = $row_transfer_withdrawal_address_a["type"];
					} else { // no, doesn't exist						
						$return = withdraw_slc($address);
						
						if (!$return["success"]) {
							json_error("An error occurred.");
						}
						
						$type = $return["type"];
						
						$ins_transfer_withdrawal_address_a = "INSERT INTO transfer_withdrawal_address (user, `group`, address, creation_time, data, type) VALUES ('$user_id', '$group', '$address', NOW(), '$data', '$type')";
						mysql_query($ins_transfer_withdrawal_address_a);
						
						$id = mysql_insert_id();
					}
					
					// NO TRANSACTION, NO WITHDRAWAL UNTIL HERE
					
					if ($type == "extern") {
						$itxid = add_transaction($user_id, "out", "extern", withdrawal_with_fee($amount, 1, $user_id), "SLC", "transfer_withdrawal", 0, withdrawal_with_fee($amount, 0, $user_id) - $amount);
						
						$tx = withdraw_slc($address, $amount, "", $itxid);
					} else {
						$itxid = add_transaction($user_id, "out", "intern", $amount, "SLC", "transfer_withdrawal", 0);
						
						$tx = withdraw_slc($address, $amount, "", $itxid);
					}
					
					$ins_transfer_withdrawal_a = "INSERT INTO transfer_withdrawal (withdrawal_address, txid, amount, filing_time) VALUES ($id, $tx[txid], '$amount', NOW())";
					mysql_query($ins_transfer_withdrawal_a);
					
					$wid = mysql_insert_id();
					
					// update transaction
					
					$udt_transaction_a = "UPDATE transaction SET info_id = '$wid' WHERE id = '$itxid'";
					mysql_query($udt_transaction_a);
					
					if ($type == "extern") {
						json_success(array("id" => $id, "txid" => crypte_transaction($tx["txid"]), "type" => $type));
					} else {
						json_success(array("id" => $id, "txid" => $tx["txid"], "type" => $type));
					}
				}
				
				if (is_array($_GET["a"])) { // 4.2.3 Create withdrawal addresses and withdraw
					$total_amount = 0;
					$addresses = $_GET["a"];
					$amounts = $_GET["am"];
					
					$group = mysql_real_escape_string($_GET["g"], $db);
					
					if (count($addresses) != count($amounts)) {
						json_error("You need as many addresses as amounts.");
					}
					
					if (strlen($group) > 255) {
						json_error("Group too long.");
					}
					
					$extern_addresses = array();
					$extern_amounts = array();
					$intern_addresses = array();
					
					$j = 0;
					
					foreach ($addresses as $i => $address) {
						if (!is_numeric($i) || $i != $j) {
							json_error("Invalid array structure.");
						}
					
						$j++;
					
						$address = mysql_real_escape_string($address, $db);
						
						if (!check_slc_address($address)) {
							json_error("Invalid address.");
						}
						
						$addresses[$i] = $address;
						
						$amount = $amounts[$i];
						
						if (!is_numeric($amount) || $amount < 0.0001) {
							json_error("Invalid amount.");
						}
						
						$amount = round($amount * 10000) / 10000;
						
						$total_amount += $amount;
						
						$result = withdraw_slc($address);
						
						if (!$result["success"]) {
							json_error("An error occurred.");
						}
						
						if ($result["type"] == "extern") {
							$extern_addresses[] = $address;
							$extern_amounts[] = $amount;
						}
						if ($result["type"] == "intern") {
							$intern_addresses[] = array($address, $amount);
						}
					}
					
					if (withdrawals_with_fee_multi($total_amount, count($extern_addresses), 0, $user_id) > get_balance($user_id, "SLC")) {
						json_error("You don't have that much.");
					}
					
					//print_r($intern_addresses);
					//print_r($extern_addresses);
					//print_r($extern_amounts);
					
					// START
					
					//mysql_query("LOCK TABLES transfer_withdrawal_address WRITE, transfer_withdrawal, transaction WRITE, transaction WRITE");
				
					$transactions = array();
					
					foreach ($intern_addresses as $address_array) {
						
						$address = $address_array[0];
						$amount = $address_array[1];
						
						$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address' AND user = '$user_id'";
						$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
						
						if ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a)) { // yes, exists
							$id = $row_transfer_withdrawal_address_a["id"];
							$type = $row_transfer_withdrawal_address_a["type"];
						} else { // no, doesn't exist						
							$return = withdraw_slc($address);
							
							if (!$return["success"]) {
								json_error("An error occurred.");
							}
							
							$type = $return["type"];
							
							$ins_transfer_withdrawal_address_a = "INSERT INTO transfer_withdrawal_address (user, `group`, address, creation_time, data, type) VALUES ('$user_id', '$group', '$address', NOW(), '$data', '$type')";
							mysql_query($ins_transfer_withdrawal_address_a);
							
							$id = mysql_insert_id();
						}
						
						// NO TRANSACTION, NO WITHDRAWAL UNTIL HERE
						
						$itxid = add_transaction($user_id, "out", "intern", $amount, "SLC", "transfer_withdrawal", 0);
						
						$tx = withdraw_slc($address, $amount, "", $itxid);
						
						$ins_transfer_withdrawal_a = "INSERT INTO transfer_withdrawal (withdrawal_address, txid, amount, filing_time) VALUES ($id, $tx[txid], '$amount', NOW())";
						mysql_query($ins_transfer_withdrawal_a);
						
						$wid = mysql_insert_id();
						
						// update transaction
						
						$udt_transaction_a = "UPDATE transaction SET info_id = '$wid' WHERE id = '$itxid'";
						mysql_query($udt_transaction_a);
						
						$transactions[] = array("id" => $id, "address" => $address, "amount" => api_sc_format($amount), "txid" => $tx["txid"], "type" => "intern");
					}
					
					//print_r($transactions);
					
					//print_r($extern_addresses);
					//print_r($extern_amounts);
					
					$result = withdraw_slc($extern_addresses, $extern_amounts, "extern");
					$txid = $result["txid"];
					
					foreach ($extern_addresses as $i => $address) {
						$amount = $extern_amounts[$i];
					
						$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address'";
						$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
						
						if ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a)) {
							$id = $row_transfer_withdrawal_address_a["id"];
						} else {							
							$ins_transfer_withdrawal_address_a = "INSERT INTO transfer_withdrawal_address (user, `group`, address, creation_time, data, type) VALUES ('$user_id', '$group', '$address', NOW(), '', 'extern')";
							mysql_query($ins_transfer_withdrawal_address_a);
							
							$id = mysql_insert_id();
						}
						
						$ins_transfer_withdrawal_a = "INSERT INTO transfer_withdrawal (withdrawal_address, txid, amount, filing_time) VALUES ('$id', $txid, '$amount', NOW())";
						mysql_query($ins_transfer_withdrawal_a);
						
						$wid = mysql_insert_id();
						
						add_transaction($user_id, "out", "extern", withdrawals_with_fee_single($amount, count($extern_addresses), 1, $user_id), "SLC", "transfer_withdrawal", $wid, withdrawals_with_fee($amount, count($extern_addresses), 0, $user_id) - $amount);
						
						$transactions[] = array("id" => $id, "address" => $address, "amount" => api_sc_format($extern_amounts[$i]), "txid" => crypte_transaction($txid), "type" => "extern");
					}
					
					json_success($transactions);
				}
				
				json_error("Please provide an address, an array of addresses or an id.");
			}
			
			if (isset($_GET["id"])) { // 4.2.4 Withdraw to withdrawal address by ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id.");
				}
				
				$amount = $_GET["am"];
				
				if (!is_numeric($amount) || $amount < 0.0001) {
					json_error("Invalid amount.");
				}
				
				$amount = round($amount * 10000) / 10000;
				
				if (withdrawal_with_fee($amount, 0, $user_id) > get_balance($user_id, "SLC")) {
					json_error("You don't have that much.");
				}
				
				//mysql_query("LOCK TABLES transfer_withdrawal_address WRITE, transfer_withdrawal, transaction WRITE, transaction WRITE");
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE id = '$id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid id.");
				}
				
				// START
				
				$type = $row_transfer_withdrawal_address_a["type"];
				$address = $row_transfer_withdrawal_address_a["address"];
				
				// NO TRANSACTION, NO WITHDRAWAL UNTIL HERE
				
				if ($type == "extern") {
					$itxid = add_transaction($user_id, "out", "extern", withdrawal_with_fee($amount, 1, $user_id), "SLC", "transfer_withdrawal", 0, withdrawal_with_fee($amount, 0, $user_id) - $amount);
					
					$tx = withdraw_slc($address, $amount, "", $itxid);
				} else {
					$itxid = add_transaction($user_id, "out", "intern", $amount, "SLC", "transfer_withdrawal", 0);
					
					$tx = withdraw_slc($address, $amount, "", $itxid);
				}
				
				$ins_transfer_withdrawal_a = "INSERT INTO transfer_withdrawal (withdrawal_address, txid, amount, filing_time) VALUES ($id, $tx[txid], '$amount', NOW())";
				mysql_query($ins_transfer_withdrawal_a);
				
				$wid = mysql_insert_id();
				
				// update transaction
				
				$udt_transaction_a = "UPDATE transaction SET info_id = '$wid' WHERE id = '$itxid'";
				mysql_query($udt_transaction_a);
				
				//mysql_query("UNLOCK TABLES");
				
				if ($type == "extern") {
					json_success(array("address" => $row_transfer_withdrawal_address_a["address"], "txid" => crypte_transaction($tx["txid"]), "type" => $type));
				} else {
					json_success(array("address" => $row_transfer_withdrawal_address_a["address"], "txid" => $tx["txid"], "type" => $type));
				}
			}
			
			json_error("Please provide an address, an array of addresses or an id.");
		}
		
		if ($query[2] == "info") {
			if (isset($_GET["id"])) { // 4.2.5 Get withdrawal address info by ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id.");
				}
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE id = '$id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid id.");
				}
				
				$slt_transfer_withdrawal_a = "SELECT SUM(amount) AS amount FROM transfer_withdrawal WHERE withdrawal_address = '$row_transfer_withdrawal_address_a[id]'";
				$rlt_transfer_withdrawal_a = mysql_query($slt_transfer_withdrawal_a);
				$row_transfer_withdrawal_a = mysql_fetch_assoc($rlt_transfer_withdrawal_a);
				$total_amount = $row_transfer_withdrawal_a["amount"];
				
				json_success(array("address" => $row_transfer_withdrawal_address_a["address"], "group" => $row_transfer_withdrawal_address_a["group"], "data" => $row_transfer_withdrawal_address_a["data"], "total_amount" => api_sc_format($total_amount), "type" => $row_transfer_withdrawal_address_a["type"]));
			}
			
			if (isset($_GET["a"])) { // 4.2.6 Get withdrawal address info by address
				$address = mysql_real_escape_string($_GET["a"], $db);
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid address.");
				}
				
				$slt_transfer_withdrawal_a = "SELECT SUM(amount) AS amount FROM transfer_withdrawal WHERE withdrawal_address = '$row_transfer_withdrawal_address_a[id]'";
				$rlt_transfer_withdrawal_a = mysql_query($slt_transfer_withdrawal_a);
				$row_transfer_withdrawal_a = mysql_fetch_assoc($rlt_transfer_withdrawal_a);
				$total_amount = $row_transfer_withdrawal_a["amount"];
				
				json_success(array("id" => $row_transfer_withdrawal_address_a["id"], "group" => $row_transfer_withdrawal_address_a["group"], "data" => $row_transfer_withdrawal_address_a["data"], "total_amount" => api_sc_format($total_amount), "type" => $row_transfer_withdrawal_address_a["type"]));
			}
			
			if (isset($_GET["g"])) { // 4.2.7 Get withdrawal address info by group
				$group = mysql_real_escape_string($_GET["g"], $db);
				
				$withdrawal_address_info = array();
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE `group` = '$group' AND user = '$user_id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				
				while ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a)) {
				
					$slt_transfer_withdrawal_a = "SELECT SUM(amount) AS amount FROM transfer_withdrawal WHERE withdrawal_address = '$row_transfer_withdrawal_address_a[id]'";
					$rlt_transfer_withdrawal_a = mysql_query($slt_transfer_withdrawal_a);
					$row_transfer_withdrawal_a = mysql_fetch_assoc($rlt_transfer_withdrawal_a);
					$total_amount = $row_transfer_withdrawal_a["amount"];
					
					$withdrawal_address_info[] = array("id" => $row_transfer_withdrawal_address_a["id"], "address" => $row_transfer_withdrawal_address_a["address"], "data" => $row_transfer_withdrawal_address_a["data"], "total_amount" => api_sc_format($total_amount), "type" => $row_transfer_withdrawal_address_a["type"]);
				}
				
				json_success($withdrawal_address_info);
			}
			
			json_error("Please provide an address, an id or a group.");
		}
		
		if ($query[2] == "update") {
			if (isset($_GET["id"])) { // 4.2.8 Update withdrawal address by ID
				$id = $_GET["id"];
				$group = mysql_real_escape_string($_GET["g"], $db);
				$data = mysql_real_escape_string($_GET["d"], $db);
				
				if (!is_numeric($id)) {
					json_error("Invalid id.");
				}
					
				if (strlen($data) > 1024) {
					json_error("Data too long.");
				}
				
				if (strlen($group) > 255) {
					json_error("Group too long.");
				}
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE id = '$id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid id.");
				}
				
				$ud = false;
				
				$udt_transfer_withdrawal_address_a = "UPDATE transfer_withdrawal_address SET ";
				
				if (isset($_GET["g"]))
					{ $udt_transfer_withdrawal_address_a .= "`group` = '$group'"; $ud = true; }
				if (isset($_GET["d"])) { if ($ud) $udt_transfer_withdrawal_address_a .= ", ";
					$udt_transfer_withdrawal_address_a .= "data = '$data'"; $ud = true; }
					
				$udt_transfer_withdrawal_address_a .= " WHERE id = '$row_transfer_withdrawal_address_a[id]'";
				
				mysql_query($udt_transfer_withdrawal_address_a);
				
				json_success();
			}
			
			if (isset($_GET["a"])) { // 4.2.9 Update withdrawal address by address
				$address = mysql_real_escape_string($_GET["a"], $db);
				$group = mysql_real_escape_string($_GET["g"], $db);
				$data = mysql_real_escape_string($_GET["d"], $db);
					
				if (strlen($data) > 1024) {
					json_error("Data too long.");
				}
				
				if (strlen($group) > 255) {
					json_error("Group too long.");
				}
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address' AND user = '$user_id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid address.");
				}
				
				$ud = false;
				
				$udt_transfer_withdrawal_address_a = "UPDATE transfer_withdrawal_address SET ";
				
				if (isset($_GET["g"]))
					{ $udt_transfer_withdrawal_address_a .= "`group` = '$group'"; $ud = true; }
				if (isset($_GET["d"])) { if ($ud) $udt_transfer_withdrawal_address_a .= ", ";
					$udt_transfer_withdrawal_address_a .= "data = '$data'"; $ud = true; }
					
				$udt_transfer_withdrawal_address_a .= " WHERE id = '$row_transfer_withdrawal_address_a[id]'";
				
				mysql_query($udt_transfer_withdrawal_address_a);
				
				json_success();
			}
			
			json_error("Please provide an address or an id.");
		}
		
		if ($query[2] == "withdrawals") {
		
			if (isset($_GET["id"])) { // 4.2.10 Get withdrawals by withdrawal address ID
				$id = $_GET["id"];
				
				if (!is_numeric($id)) {
					json_error("Invalid id");
				}
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE id = '$id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid id.");
				}
				
				$withdrawals = array();
				
				$slt_transfer_withdrawal_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_withdrawal WHERE withdrawal_address = '$id'";
				$rlt_transfer_withdrawal_a = mysql_query($slt_transfer_withdrawal_a);
				
				while ($row_transfer_withdrawal_a = mysql_fetch_assoc($rlt_transfer_withdrawal_a)) {
					if ($row_transfer_withdrawal_address_a["type"] == "intern") {
						$withdrawals[] = array("txid" => $row_transfer_withdrawal_a["txid"], "time" => $row_transfer_withdrawal_a["filing_time_u"], "amount" => api_sc_format($row_transfer_withdrawal_a["amount"]));
					} else {
						$withdrawals[] = array("txid" => crypte_transaction($row_transfer_withdrawal_a["txid"]), "time" => $row_transfer_withdrawal_a["filing_time_u"], "amount" => api_sc_format($row_transfer_withdrawal_a["amount"]));
					}
				}
			
				json_success($withdrawals);
			}
			
			if (isset($_GET["a"])) { // 4.2.10 Get withdrawals by withdrawal address ID
				$address = mysql_real_escape_string($_GET["a"], $db);
				
				$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE address = '$address' AND user = '$user_id'";
				$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
				$row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a);
				
				if ($row_transfer_withdrawal_address_a["user"] != $user_id) {
					json_error("Invalid address.");
				}
				
				$withdrawals = array();
				
				$slt_transfer_withdrawal_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_withdrawal WHERE withdrawal_address = '$row_transfer_withdrawal_address_a[id]'";
				$rlt_transfer_withdrawal_a = mysql_query($slt_transfer_withdrawal_a);
				
				while ($row_transfer_withdrawal_a = mysql_fetch_assoc($rlt_transfer_withdrawal_a)) {
					if ($row_transfer_withdrawal_address_a["type"] == "intern") {
						$withdrawals[] = array("txid" => $row_transfer_withdrawal_a["txid"], "time" => $row_transfer_withdrawal_a["filing_time_u"], "amount" => api_sc_format($row_transfer_withdrawal_a["amount"]));
					} else {
						$withdrawals[] = array("txid" => crypte_transaction($row_transfer_withdrawal_a["txid"]), "time" => $row_transfer_withdrawal_a["filing_time_u"], "amount" => api_sc_format($row_transfer_withdrawal_a["amount"]));
					}
				}
			
				json_success($withdrawals);
			}
		
		}
		
		if ($query[2] == "addresses") {
			$withdrawal_addresses = array();
		
			$slt_transfer_withdrawal_address_a = "SELECT * FROM transfer_withdrawal_address WHERE user = '$user_id'";
			$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);
			
			while ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a)) {
				$withdrawal_addresses[] = array("id" => $row_transfer_withdrawal_address_a["id"], "address" => $row_transfer_withdrawal_address_a["address"], "group" => $row_transfer_withdrawal_address_a["group"], "type" => $row_transfer_withdrawal_address_a["type"]);
			}
			
			json_success($withdrawal_addresses);
		}
		
		
		json_error("Unknown call.");
		
	}
	
	json_error("Unknown call.");
}

json_error("Unknown call.");

function check_authentification() {
	global $query, $db, $logged_in, $user_id, $user_email, $user_row;
	
	$userdata = (string)base64_decode($query[0]);
	
	$logged_in = false;
	
	$found = false;
	
	if (!$found) {
		if ($userdata[7] == ":") {
			
			foreach ($query as $i => $value) {
				if ($i > 0) {
					$query[$i - 1] = $value;
				}
			}
			unset($query[count($query) - 1]);
			
			$user = substr($userdata, 0, 7);
			$password = substr($userdata, 8);
			
			$found = true;
		}
	}
	
	if (!$found) {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$user = $_SERVER["PHP_AUTH_USER"];
			$password = $_SERVER["PHP_AUTH_PW"];
			
			$found = true;
		}
	}
	
	if (!$found) {
		return false;
	}
	
	get_connection();
	
	$slt_user_a = "SELECT * FROM user WHERE id_string = '$user'";
	$rlt_user_a = mysql_query($slt_user_a);

	if (mysql_num_rows($rlt_user_a) == 1) {
		$row_user_a = mysql_fetch_assoc($rlt_user_a);
		$hash = "";
		
		if ($row_user_a["actu"] == "") {
			if ($row_user_a["hash_mode"] == "a") {
				$iterations = 100000;
				$salt = $row_user_a["hash_salt"];
				
				$hash = $password.$salt;
				for ($i = 0; $i < $iterations; $i++) {
					$hash = sha1($password.$salt);
				}
			}
			if ($row_user_a["hash_mode"] == "b") {
				$iterations = 100000;
				$salt = $row_user_a["hash_salt"];
				
				$hash = $password.$salt;
				for ($i = 0; $i < $iterations; $i++)
				{
					$hash = sha1($hash.$password.$salt);
				}
			}
				
			if ($row_user_a["hashed_password"] == $hash) {
				$logged_in = true;
				$user_id = $row_user_a["id"];
				$user_email = $row_user_a["email"];
				$user_row = $row_user_a;
				return true;
			}
		}
	}
	
	return false;
}

function require_authentification() {
	global $logged_in;
	
	if (!$logged_in) {
		header('WWW-Authenticate: Basic realm="My Realm"');
		header('HTTP/1.0 401 Unauthorized');
		json_error("Authentification required.");
	}
}

function json_error($message="") {
	if ($message != "")
		die(json_encode(array("error" => $message)));
	else
		die(json_encode(array("error" => "")));
}

function json_success($data=array()) {
	global $db, $user_id;
	
	//$ins_api_log = "INSERT INTO api_log (`call`, response, call_time, user) VALUES ('".mysql_real_escape_string($_GET["query"]."?".implode("&", $_GET), $db)."', '".mysql_real_escape_string(json_encode($data), $db)."', NOW(), '$user_id')";
	//mysql_query($ins_api_log);
	
	die(json_encode($data));
}

function api_sc_format($amount) {
	return round($amount * 10000) / 10000;
}

function api_price_format($amount) {
	return round($amount * 100000000) / 100000000;
}

function api_cur_format($amount, $precision=8) {
	return round($amount * 100000000) / 100000000;
}

function crypte_transaction($id) {
	$slt_crypto_transaction_a = "SELECT * FROM crypto_transaction WHERE id = '$id'";
	$rlt_crypto_transaction_a = mysql_query($slt_crypto_transaction_a);
	$row_crypto_transaction_a = mysql_fetch_assoc($rlt_crypto_transaction_a);
	
	return $row_crypto_transaction_a["txid"];
}

?>