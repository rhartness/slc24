UPDATE ANYTHING

<?php
include "inc/conn.php";
include "inc/db_locks.php";
include "inc/format.php";
include "inc/fees.php";
include "inc/account.php";
include "inc/send_mail.php";
include "inc/json/jsonRPCClient.php";
include "inc/process_cryptotransaction.php";

get_connection();

if (!get_lock("global")) {
	die("Too many queries. Please try again later.");
}

?>
<pre>
Solidcoin:
<?php
$solidcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:7556/");
$transactions = $solidcoin->sc_listtransactions("main", "", 100);

foreach ($transactions as $transaction) {
	$transaction["amount"] /= 10000;
	if ($transaction["amount"] > 500) {
		if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 6) {
			process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC");
		}
	} elseif ($transaction["amount"] > 5) {
		if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 4) {
			process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC");
		}
	} else {
		if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 2) {
			process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "SLC");
		}
	}
}
?>

Bitcoin:
<?php
$bitcoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:8332/");
$transactions = $bitcoin->listtransactions("", 50);


foreach ($transactions as $transaction) {
	if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 3) {
		process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "BTC");
	}
}
?>

Namecoin:
<?php
$namecoin = new jsonRPCClient("http://USERNAME:PASSWORD@127.0.0.1:8336/");
$transactions = $namecoin->listtransactions("", 30);

foreach ($transactions as $transaction) {
	if ($transaction["category"] == "receive" && $transaction["confirmations"] >= 4) {
		process_cryptotransaction($transaction["txid"], $transaction["address"], $transaction["amount"], $transaction["time"], "NMC");
	}
}
?>