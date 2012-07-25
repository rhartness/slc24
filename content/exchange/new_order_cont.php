<div class="article">
<h2><span>New order</span></h2>
<p>

<?php
include_once "inc/account.php";
include_once "inc/process_orders.php";
include_once "inc/db_locks.php";

$errors = array();

$currency = $_POST["currency"];
$price = $_POST["price"];
$slc_amount = $_POST["slc_amount"];
$cur_amount = $_POST["cur_amount"];

if ($currency != "BTC" && $currency != "NMC")
	$errors[] = "Invalid currency.";

if (!is_numeric($slc_amount) || $slc_amount < 0.01)
	$errors[] = "Invalid Solidcoin amount. Only numeric values greater than or equal to 0.01 are allowed.";

if (!is_numeric($cur_amount) || $cur_amount <= 0)
	$errors[] = "Invalid Bitcoin amount. Only numeric values greater than 0 are allowed.";

if (!is_numeric($price) || $price < 0)
	$errors[] = "Invalid price.";

if (count($errors) == 0)
{
	get_connection();
	
	$price = round($price * 100000000) / 100000000;

	$cur_amount = $slc_amount * $price;
	
	if ($cur_amount / $_POST["cur_amount"] > 1.1 || $_POST["cur_amount"] / $cur_amount < 1 / 1.1)
		$errors[] = "The price does not match the ratio of the amounts. Make sure JavaScript is enabled!";

	if (!get_lock("global")) {
		die("Too many queries. Please try again later.");
	}
		
	if ($_POST["type"] == "sell")
	{
		if ($slc_amount > get_balance($_SESSION["user_id"], "SLC"))
			$errors[] = "You don't have that much.";
		
		if (count($errors) == 0)
		{
			$slt_trade_order_a = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND ROUND(price * 100000000) = ROUND($price * 100000000) AND currency = '$currency' AND user = '$_SESSION[user_id]'";
			$rlt_trade_order_a = mysql_query($slt_trade_order_a);
		
			if (mysql_num_rows($rlt_trade_order_a) == 0)
			{
				$ins_trade_order_a = "INSERT INTO trade_order (type, user, currency, price, amount, completed, active, finished, filing_time, change_time, finishing_time) ".
					"VALUES ('sell', '$_SESSION[user_id]', '$currency', '$price', '$slc_amount', '0', 'yes', 'no', NOW(), NOW(), '0000-00-00 00:00:00')";
				mysql_query($ins_trade_order_a);
				
				$tid = mysql_insert_id();
				
				add_transaction($_SESSION["user_id"], "out", "intern", $slc_amount, "SLC", "trade_placement", $tid);
			}
			else
			{
				$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
				
				$udt_trade_order_a = "UPDATE trade_order SET amount = amount + $slc_amount, change_time = NOW() WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
				
				add_transaction($_SESSION["user_id"], "out", "intern", $slc_amount, "SLC", "trade_increase", $row_trade_order_a["id"]);
			}
		}
	}
	elseif ($_POST["type"] == "buy")
	{
		if ($cur_amount * 1.004 > get_balance($_SESSION["user_id"], $currency))
			$errors[] = "You don't have that much.";
			
		if (count($errors) == 0)
		{
			$slt_trade_order_a = "SELECT * FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND ROUND(price * 100000000) = ROUND($price * 100000000) AND currency = '$currency' AND user = '$_SESSION[user_id]'";
			$rlt_trade_order_a = mysql_query($slt_trade_order_a);
		
			if (mysql_num_rows($rlt_trade_order_a) == 0)
			{
				$ins_trade_order_a = "INSERT INTO trade_order (type, user, currency, price, amount, completed, active, finished, filing_time, change_time, finishing_time) ".
					"VALUES ('buy', '$_SESSION[user_id]', '$currency', '$price', '$slc_amount', '0', 'yes', 'no', NOW(), NOW(), '0000-00-00 00:00:00')";
				mysql_query($ins_trade_order_a);
				
				$tid = mysql_insert_id();
				
				add_transaction($_SESSION["user_id"], "out", "intern", $cur_amount * 1.004, $currency, "trade_placement", $tid, $cur_amount * 0.004);
			}
			else
			{
				$row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a);
				
				$udt_trade_order_a = "UPDATE trade_order SET amount = amount + $slc_amount, change_time = NOW() WHERE id = $row_trade_order_a[id]";
				mysql_query($udt_trade_order_a);
				
				add_transaction($_SESSION["user_id"], "out", "intern", $cur_amount * 1.004, $currency, "trade_increase", $row_trade_order_a["id"], $cur_amount * 0.004);
			}
		}
	}
	else
		$errors[] = "Invalid type.";
}
if (count($errors) != 0)
{
	foreach ($errors as $error)
	{
		echo "$error<br />";
	}
	
	echo "<br />";
	if ($currency == "NMC")
		echo "<a href=\"?c=exchange&amp;u=nmc\">Back to the trading page</a>";
	else
		echo "<a href=\"?c=exchange\">Back to the trading page</a>";
}
else
{
	if ($currency == "BTC")
	{
		$l_url = "?c=exchange";
		$l_urljs = "?c=exchange";
	}
	else
	{
		$l_url = "?c=exchange&amp;u=nmc";
		$l_urljs = "?c=exchange&u=nmc";
	}
	
	process_orders();
?>
<?php $l_time = 2; $l_site_name = "Exchange"; ?>
Your are being redirected to <a href="<?=$l_url?>"><?=htmlentities($l_site_name, ENT_COMPAT, "UTF-8")?></a> in <span id="counter"><?=$l_time?></span>.<br />
<script type="text/javascript" src="res/jscr/redirect.js"></script>
<script type="text/javascript">
redirect("<?=$l_urljs?>", <?=$l_time?>);
</script>
<?php
}
?>

</p>
</div>