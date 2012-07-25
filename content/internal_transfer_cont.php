<div class="article">
<h2><span>Internal transfer</span></h2>
<p>
<?php

get_connection();

$currencies = array("SLC", "BTC", "NMC");

if (!isset($_POST["step"])) {
?>
<form action="" method="post">
<input type="hidden" name="step" value="1" />
<table>
<tr><td style="padding-right: 10px">Recipient email</td><td><input name="recipient" /></td></tr>
<tr><td>Amount</td><td><input name="amount" /></td></tr>
<tr><td>Currency</td><td><select name="currency"><option value="SLC">Solidcoins</option><option value="BTC">Bitcoins</option><option value="NMC">Namecoins</option></select></td></tr>
<tr><td></td><td><input type="submit" value="Transfer" /></td></tr>
</table>
</form>
<?php
} elseif ($_POST["step"] == 1) {
	$recipient = mysql_real_escape_string($_POST["recipient"], $db);
	$currency = $_POST["currency"];
	$amount = $_POST["amount"];
	
	if (!in_array($currency, $currencies)) {
		$errors[] = "No such currency.";
	}
	
	if (!is_numeric($amount) || ($currency == "SLC" && $amount < 0.0001) || ($currency != "SLC" && $amount < 0.00000001)) {
		$errors[] = "Wrong amount.";
	}
	
	if (count($errors) == 0) {
		if ($currency == "SLC") {
			$amount = round($amount * 10000) / 10000;
		} else {
			$amount = round($amount * 100000000) / 100000000;
		}
			
		if (!get_lock("global")) {
			die("Too many queries. Please try again later.");
		}
		
		if ($amount > get_balance($_SESSION["user_id"], $currency)) {
			$errors[] = "You don't have that much.";
		}
		
		$slt_user_a = "SELECT * FROM user WHERE email = '$recipient'";
		$rlt_user_a = mysql_query($slt_user_a);
		
		if (!($row_user_a = mysql_fetch_assoc($rlt_user_a))) {
			$errors[] = "A user with that email address does not exist.";
		}
		
		if (count($errors) == 0) {
			add_transaction($_SESSION["user_id"], "out", "intern", $amount, $currency, "internal_transfer");
			add_transaction($row_user_a["id"], "in", "intern", $amount, $currency, "internal_transfer");
			
			if ($currency == "SLC") {
				echo nice_format($amount, false, 0, 4)." Solidcoins were transferred to ".htmlentities($_POST["recipient"]).".";
			} elseif ($currency == "BTC") {
				echo nice_format($amount, false, 0, 8)." Bitcoins were transferred to ".htmlentities($_POST["recipient"]).".";
			} elseif ($currency == "NMC") {
				echo nice_format($amount, false, 0, 8)." Namecoins were transferred to ".htmlentities($_POST["recipient"]).".";
			}
		}
	}
	
	if (count($errors) != 0) {
		foreach ($errors as $error) {
			echo $error."<br />";
		}
?>
<form action="" method="post">
<input type="hidden" name="step" value="1" />
<table>
<tr><td style="padding-right: 10px">Recipient email</td><td><input name="recipient" value="<?=htmlentities($_POST["recipient"])?>" /></td></tr>
<tr><td>Amount</td><td><input name="amount" value="<?=htmlentities($_POST["amount"])?>" /></td></tr>
<tr><td>Currency</td><td><select name="currency"><option value="SLC">Solidcoins</option><option value="BTC"<?php if ($currency == "BTC") echo " selected=\"selected\""; ?>>Bitcoins</option><option value="NMC"<?php if ($currency == "NMC") echo " selected=\"selected\""; ?>>Namecoins</option></select></td></tr>
<tr><td></td><td><input type="submit" value="Transfer" /></td></tr>
</table>
</form>
<?php
	}
} else {
	echo "Error.";
}

?>
</p>
</div>