<?php

function brief_check() {
	global $db;

	$solidcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:7556/");
	$transactions = $solidcoin->sc_listtransactions("main");

	foreach ($transactions as $transaction) {
		$transaction["amount"] /= 10000;
		if ($transaction["amount"] > 500) {
			if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 6) {
				process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC", false);
			}
		} elseif ($transaction["amount"] > 5) {
			if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 4) {
				process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC", false);
			}
		} else {
			if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 2) {
				process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC", false);
			}
		}
	}
}
?>