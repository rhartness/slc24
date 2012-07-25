<div class="article">
<h2><span>Withdraw Namecoins</span></h2>
<p>

<?php
include_once $_SITE["root"]."inc/withdraw.php";
include_once $_SITE["root"]."inc/address.php";
include_once $_SITE["root"]."inc/db_locks.php";
include_once $_SITE["root"]."inc/send_mail.php";
include_once $_SITE["root"]."inc/json/jsonRPCClient.php";

$balance = get_balance($_SESSION["user_id"], "NMC");
$staticfee = 0.1;

$namecoin = new jsonRPCClient("http://mysolidcoin:E4O8BgWc55f@127.0.0.1:8336/");
	
if (!isset($_GET["step"]))
{
	include_once "inc/withdraw.php";
?>
The Namecoins will be transferred immediately to the specified address and removed from your balance.<br /><br />
Current balance: <?=nice_format($balance, false, 0, 8)?> Namecoins<br />
<form action="?c=balance/withdraw_nmc&amp;step=1" method="post">
<table>
<tr><td>Address</td><td><input name="address" size="50" /></td></tr>
<tr><td>Amount</td><td><input id="amount" name="amount" value="0.5" /></td></tr>
<tr><td></td><td><span id="msg"></span></td></tr>
<tr><td></td><td><input type="submit" value="Withdraw" /></td></tr>
</table>
</form>
<?php
}
elseif ($_GET["step"] == 1)
{	
	$address = mysql_real_escape_string($_POST["address"], $db);
	$amount = floor(mysql_real_escape_string($_POST["amount"], $db) * 100000000) / 100000000;
	
	$errors = array();
	
	if (!is_numeric($amount) || ($amount < 0.5 && $amount != "0.5"))
	{
		$errors[] = "Please enter a valid amount above 0.5.";
	}
	if (!check_nmc_address($address))
	{
		$errors[] = "Please enter a valid address.";
	}
	
	if (count($errors) == 0)
	{
		$result = withdraw_nmc($address);
		
		if ($result["type"] == "extern") {
			$amountwithfee = floor(($amount + $staticfee) * 100000000) / 100000000;
		} else {
			$amountwithfee = floor($amount * 100000000) / 100000000;
		}
		
		if ($amountwithfee > floor($balance * 100000000) / 100000000)
		{
			$errors[] = "You don't have that much (you need $amountwithfee).";
		}
	}
	
	if (count($errors) == 0)
	{
?>
<form action="?c=balance/withdraw_nmc&amp;step=2" method="post">
Please confirm: You want to transfer <?=nice_format($amount, false, 0, 8)?> Namecoins to the Namecoin address <?=urlencode($address)?>.<br />
<?php if ($result["type"] == "extern") { ?>
<small>Your account will be charged with <?=$amountwithfee?> Namecoins (<?=$amount?> Namecoins + <?=$staticfee?> Namecoins transaction fee).</small><br /><br />
<?php } else { ?>
<small>Your account will be charged with <?=$amountwithfee?> Namecoins and <b>no transaction fees</b> (this is an internal withdrawal).</small><br /><br />
<?php } ?>
<input type="hidden" name="amount" value="<?=urlencode($amount)?>" />
<input type="hidden" name="address" value="<?=urlencode($address)?>" />
<input type="submit" value="confirm" />
</form>
<?php
	}
	else
	{
		foreach ($errors as $error)
		{
			echo "$error</span><br />";
		}
?>
<br />
The Namecoins will be transferred immediately to the specified address and removed from your balance.<br /><br />
Current balance: <?=nice_format($balance, false, 0, 8)?> Namecoins<br />
<form action="?c=balance/withdraw_nmc&amp;step=1" method="post">
<table>
<tr><td>Address</td><td><input name="address" size="50" value="<?=htmlentities($_POST["address"])?>" /></td></tr>
<tr><td>Amount</td><td><input id="amount" name="amount" value="<?=htmlentities($_POST["amount"])?>" /></td></tr>
<tr><td></td><td><span id="msg"></span></td></tr>
<tr><td></td><td><input type="submit" value="Withdraw" /></td></tr>
</table>
</form>
<?php
	}
}
elseif ($_GET["step"] == 2)
{
	$address = mysql_real_escape_string($_POST["address"], $db);
	$amount = floor(mysql_real_escape_string($_POST["amount"], $db) * 100000000) / 100000000;
	
	$errors = array();
	
	if (!is_numeric($amount) || ($amount < 0.5 && $amount != "0.5"))
	{
		$errors[] = "Please enter a valid amount above 0.5.";
	}
	if (!check_nmc_address($address))
	{
		$errors[] = "Please enter a valid address.";
	}
	
	if (!get_lock("global")) {
		die("Too many queries. Please try again later.");
	}
	
	$balance = get_balance($_SESSION["user_id"], "NMC");
	
	if (count($errors) == 0)
	{
		$result = withdraw_nmc($address);
		
		if ($result["type"] == "extern") {
			$amountwithfee = floor(($amount + $staticfee) * 100000000) / 100000000;
		} else {
			$amountwithfee = floor($amount * 100000000) / 100000000;
		}
		
		if ($amountwithfee > floor($balance * 100000000) / 100000000)
		{
			$errors[] = "You don't have that much.";
		}
	}
	
	$server_balance = $namecoin->getbalance();
	$server_balance = 100000000000;
	
	if (count($errors) == 0 && $server_balance < $amountwithfee * 1.05 + 1)
	{
		$errors[] = "There's currently not enough Namecoins on the Namecoin24 servers to fulfill your withdrawal request. Don't worry, a Namecoin24 admin has been informed and will resolve this for you. You will get an email as soon as the Namecoins are available. Sorry for the inconvenience, but remember that a big part of the amounts are stored in a seperate wallets for your own security.";
	
		send_mail("Emergency! Namecoin balance exhausted ($server_balance left)!", "A user with the email address ".$_SESSION["user_email"]." legitimately tried to withdraw $amount Namecoins. There are still $server_balance Namecoins on the server. The request was denied. Please resolve this and make sure to inform the user.", "admin@solidcoin24.com");
	}
	
	if (count($errors) == 0)
	{		
		echo "$amount Namecoins are being transferred to $address.<br />";
		
		$result = withdraw_nmc($address);
		
		if ($result["type"] == "extern") {
			$itxid = add_transaction($_SESSION["user_id"], "out", "extern", $amountwithfee, "NMC", "withdrawal", 0, $staticfee);
			
			$tx = withdraw_nmc($address, $amount, "", $itxid);
			
			$udt_transaction_a = "UPDATE transaction SET info_id = '$tx[txid]' WHERE id = '$itxid'";
			mysql_query($udt_transaction_a);
		
			echo "<small>Transaction (extern): ".crypte_transaction($tx["txid"])."</small>";
		} else {
			$itxid = add_transaction($_SESSION["user_id"], "out", "intern", $amountwithfee, "NMC", "withdrawal", 0);
			
			$tx = withdraw_nmc($address, $amount, "", $itxid);
			
			$udt_transaction_a = "UPDATE transaction SET info_id = '$tx[txid]' WHERE id = '$itxid'";
			mysql_query($udt_transaction_a);
			
			echo "<small>Transaction (intern): $tx[txid]</small>";
		}
	}
	else
	{
		foreach ($errors as $error)
		{
			echo "$error<br />";
		}
?>
<br />
The Namecoins will be transferred immediately to the specified address and removed from your balance.<br /><br />
Current balance: <?=nice_format($balance, false, 0, 8)?> Namecoins<br />
<form action="?c=balance/withdraw_nmc&amp;step=1" method="post">
<table>
<tr><td>Address</td><td><input name="address" size="50" value="<?=htmlentities($_POST["address"])?>" /></td></tr>
<tr><td>Amount</td><td><input id="amount" name="amount" value="<?=htmlentities($_POST["amount"])?>" /></td></tr>
<tr><td></td><td><span id="msg"></span></td></tr>
<tr><td></td><td><input type="submit" value="Withdraw" /></td></tr>
</table>
</form>
<?php
	}
}

?>

</p>
</div>

<?php
function crypte_transaction($id) {
	$slt_crypto_transaction_a = "SELECT * FROM crypto_transaction WHERE id = '$id'";
	$rlt_crypto_transaction_a = mysql_query($slt_crypto_transaction_a);
	$row_crypto_transaction_a = mysql_fetch_assoc($rlt_crypto_transaction_a);
	
	return $row_crypto_transaction_a["txid"];
}
?>