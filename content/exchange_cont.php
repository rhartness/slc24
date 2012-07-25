<?php
language_file("exchange");
?>

<div class="article">
<h2><span><?=lang_temp($currencynp, $_LANG[$l]["e_header1"])?></span></h2>

<?php

if ($_SESSION["li"] != 1) 
	echo lang_temp("?c=sign_up$___l", $_LANG[$l]["e_log_in"]);
	
?>

<table style="width: 100%; border-collapse: collapse;">

<?php 
if ($_SESSION["li"] == 1)
{
	get_connection();

	$slc_balance = get_balance($_SESSION["user_id"], "SLC");
	$cur_balance = get_balance($_SESSION["user_id"], $currency);
	
	$slt_trade_order_buy = "SELECT MAX(price) as price FROM trade_order WHERE currency = '$currency' AND type = 'buy' AND active = 'yes' AND finished = 'no'";
	$rlt_trade_order_buy = mysql_query($slt_trade_order_buy);
	$row_trade_order_buy = mysql_fetch_assoc($rlt_trade_order_buy);
	
	$slt_trade_order_sell = "SELECT MIN(price) as price FROM trade_order WHERE currency = '$currency' AND type = 'sell' AND active = 'yes' AND finished = 'no'";
	$rlt_trade_order_sell = mysql_query($slt_trade_order_sell);
	$row_trade_order_sell = mysql_fetch_assoc($rlt_trade_order_sell);
?>

<tr>

<td style="vertical-align: top;">
<h3><a href="#" onclick="if (document.getElementById('buy_window').style.display == 'none') { document.getElementById('buy_window').style.display = 'block'; this.style.color = 'inherit'; selected = 'buy'; } else { document.getElementById('buy_window').style.display = 'none'; this.style.color = ''; selected = 'sell'; }"><?=lang_temp("Solidcoins", $_LANG[$l]["e_header_buy"])?></a></h3>
<div id="buy_window" style="display: none" onclick="selected = 'buy';">
<form action="?c=exchange/new_order<?=$___l?>" method="post">
<input type="hidden" name="type" value="buy" />
<input type="hidden" name="currency" value="<?=$currency?>" />
<table>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_amount"]?></td><td><input id="buy_slc_amount" name="slc_amount" style="width: 80px" value="0" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('buy', 1)" /></td><td style="padding-left: 5px;">Solidcoins</td></tr>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_price"]?></td><td><input id="buy_price" name="price" style="width: 80px" value="<?=nice_format($row_trade_order_sell["price"], false, 0, 8)?>" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('buy', 2)" /></td><td style="padding-left: 5px;"><?=lang_temp(array($currencynp, "Solidcoin"), $_LANG[$l]["e_per"])?></td></tr>
<tr><td style="padding-right: 5px;" rowspan="2"><?=$_LANG[$l]["e_costs"]?></td><td><input id="buy_cur_amount" name="cur_amount" style="width: 80px" value="0" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('buy', 3)" /></td><td style="padding-left: 5px;"><?=$currencynp?></td></td></tr>
<tr><td style="text-align: right" id="buy_fee">0</td><td style="padding-left: 5px;"><?=lang_temp($currencynp, $_LANG[$l]["e_incl_fees"])?></td></tr>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_remaining"]?></td><td style="text-align: right" id="buy_remaining"><?=nice_format($cur_balance, false, 0, 8)?></td><td style="padding-left: 5px;"><?=$currencynp?></td></td></tr>
<tr><td colspan="3"><input type="submit" value="<?=$_LANG[$l]["e_button_buy"]?>" style="width: 75%" /> <input type="button" value="<?=$_LANG[$l]["e_button_max"]?>" onclick="set_field('buy', 3, <?=max((round($cur_balance / 1.004 * 100000000) - 1) / 100000000, 0)?>)" style="width: 23%; margin-left: 2%" /><td></td></tr>
</table>
</form>
</div>
</td>

<td style="vertical-align: top;">
<h3><a href="#" onclick="if (document.getElementById('sell_window').style.display == 'none') { document.getElementById('sell_window').style.display = 'block'; this.style.color = 'inherit'; selected = 'sell'; } else { document.getElementById('sell_window').style.display = 'none'; this.style.color = ''; selected = 'buy'; }"><?=lang_temp("Solidcoins", $_LANG[$l]["e_header_sell"])?></a></h3>
<div id="sell_window" style="display: none" onclick="selected = 'sell';">
<form action="?c=exchange/new_order<?=$___l?>" method="post">
<input type="hidden" name="type" value="sell" />
<input type="hidden" name="currency" value="<?=$currency?>" />
<table>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_amount"]?></td><td><input id="sell_slc_amount" name="slc_amount" style="width: 80px" value="0" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('sell', 1)" /></td><td style="padding-left: 5px;">Solidcoins</td></tr>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_price"]?></td><td><input id="sell_price" name="price" style="width: 80px" value="<?=nice_format($row_trade_order_buy["price"], false, 0, 8)?>" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('sell', 2)" /></td><td style="padding-left: 5px;"><?=lang_temp(array($currencynp, "Solidcoin"), $_LANG[$l]["e_per"])?></td></tr>
<tr><td style="padding-right: 5px;" rowspan="2"><?=$_LANG[$l]["e_return"]?></td><td><input id="sell_cur_amount" name="cur_amount" style="width: 80px" value="0" onclick="if (this.value == '0') this. value = '';" onblur="if (this.value == '') this.value = '0';" onkeyup="set_field('sell', 3)" /></td><td style="padding-left: 5px;"><?=$currencynp?></td></td></tr>
<tr><td style="text-align: right" id="sell_fee">0</td><td style="padding-left: 5px;"><?=lang_temp($currencynp, $_LANG[$l]["e_incl_fees"])?></td></td></tr>
<tr><td style="padding-right: 5px;"><?=$_LANG[$l]["e_remaining"]?></td><td style="text-align: right" id="sell_remaining"><?=nice_format($slc_balance, false, 0, 4)?></td><td style="padding-left: 5px;">Solidcoins</td></td></tr>
<tr><td colspan="3"><input type="submit" value="<?=$_LANG[$l]["e_button_sell"]?>" style="width: 75%" /> <input type="button" value="<?=$_LANG[$l]["e_button_max"]?>" onclick="set_field('sell', 1, <?=nice_format($slc_balance, false, 0, 4)?>)" style="width: 23%; margin-left: 2%" /><td></td></tr>
</table>
</form>
</div>
</td>

<script type="text/javascript">

var no_set_field = <?php echo ($_SESSION["li"] == 1) ? "false" : "true"; ?>;
var c_balance = <?=$cur_balance?>;
var s_balance = <?=$slc_balance?>;

</script>

<script type="text/javascript" src="https://slc24.com/js/exchange.js"></script>

</tr>

<?php } ?>

<tr>

<td style="vertical-align: top; width: 50%">
<h3><?=$_LANG[$l]["e_sell_orders"]?></h3>

<table style="width: 100%; border-collapse: collapse;">
<tr><td style="width: 33%; text-align: center"><?=$_LANG[$l]["e_price"]?></td><td style="width: 33%; text-align: center">Solidcoins</td><td style="width: 33%; text-align: center"><?=$currencynp?></td><td></td></tr>
<?php

get_connection();

$slt_trade_order_a = "SELECT ROUND(price * 100000000) AS price, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'sell' GROUP BY ROUND(price * 100000000) ORDER BY price ASC LIMIT 10";
$rlt_trade_order_a = mysql_query($slt_trade_order_a);

$slc = 0; $cur = 0; $price = 0;
while ($row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a))
{
	$price = $row_trade_order_a["price"];
	$show = true;
	if ($_SESSION["li"] == 1)
	{
		$slt_trade_order_b = "SELECT *, ((amount - completed) * price) AS cur_amount, (amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND ROUND(price * 100000000) = $row_trade_order_a[price] AND currency = '$currency' AND user = '$_SESSION[user_id]'";
		$rlt_trade_order_b = mysql_query($slt_trade_order_b);
		
		if (mysql_num_rows($rlt_trade_order_b) != 0)
		{
			$row_trade_order_b = mysql_fetch_assoc($rlt_trade_order_b);
			
			$slc_this = $slc = $slc + $row_trade_order_a["slc_amount"];
			$cur_this = $cur = $cur + $row_trade_order_a["cur_amount"];
			
			echo "<tr>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_a\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_a["price"] / 100000000, true, 3, 8)."</td>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_b\" onclick=\"set_field('', 1, ".nice_format($slc_this, false, 0, 4).")\">".nice_format($slc_this, true, 0, 4)."</td>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_c\" onclick=\"set_field('', 3, ".nice_format($cur_this, false, 0, 8).")\">".nice_format($cur_this, true, 0, 8)."</td>\n";
			echo "  <td><a href=\"?c=exchange/order&amp;t=$row_trade_order_b[id]"."$___l\" onmouseover=\"document.getElementById('t_$row_trade_order_b[id]_b').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_b[id]_c').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_b[id]_b').innerHTML = '".htmlentities(nice_format($row_trade_order_b["slc_amount"], true, 0, 4))."'; document.getElementById('t_$row_trade_order_b[id]_c').innerHTML = '".htmlentities(nice_format($row_trade_order_b["cur_amount"], true, 0, 8))."'\" onmouseout=\"document.getElementById('t_$row_trade_order_b[id]_b').style.color = ''; document.getElementById('t_$row_trade_order_b[id]_c').style.color = ''; document.getElementById('t_$row_trade_order_b[id]_b').innerHTML = '".htmlentities(nice_format($slc_this, true, 0, 4))."'; document.getElementById('t_$row_trade_order_b[id]_c').innerHTML = '".htmlentities(nice_format($cur_this, true, 0, 8))."'\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>\n";
			echo "</tr>\n";
			
			continue;
		}
	}

	$slc = $slc + $row_trade_order_a["slc_amount"];
	$cur = $cur + $row_trade_order_a["cur_amount"];
	
	echo "<tr>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_a["price"] / 100000000, true, 3, 8)."</td>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 1, ".nice_format($slc, false, 0, 4).")\">".nice_format($slc, true, 0, 4)."</td>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 3, ".nice_format($cur, false, 0, 4).")\">".nice_format($cur, true, 0, 8)."</td>\n";
	echo "  <td></td>\n";
	echo "</tr>\n";
}

$slt_trade_order_c = "SELECT *, ROUND(price * 100000000) AS price, ((amount - completed) * price) AS cur_amount, (amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND currency = '$currency' AND user = '$_SESSION[user_id]' AND ROUND(price * 100000000) > $price ORDER BY price ASC";
$rlt_trade_order_c = mysql_query($slt_trade_order_c);

while ($row_trade_order_c = mysql_fetch_assoc($rlt_trade_order_c))
{
	echo "<tr>\n";
	echo "  <td style=\"text-align: center\" colspan=\"4\">...</td>\n";
	echo "</tr>\n";
	
	$slt_trade_order_d = "SELECT *, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND currency = '$currency' AND ROUND(price * 100000000) < $row_trade_order_c[price]";
	$rlt_trade_order_d = mysql_query($slt_trade_order_d);
	$row_trade_order_d = mysql_fetch_assoc($rlt_trade_order_d);
	
	$slc_this = $slc = $row_trade_order_d["slc_amount"] + $row_trade_order_c["slc_amount"];
	$cur_this = $cur = $row_trade_order_d["cur_amount"] + $row_trade_order_c["cur_amount"];
	
	echo "<tr>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_a\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_c["price"] / 100000000, true, 3, 8)."</td>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_b\" onclick=\"set_field('', 1, ".nice_format($slc_this, false, 0, 4).")\">".nice_format($slc_this, true, 0, 4)."</td>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_c\" onclick=\"set_field('', 3, ".nice_format($cur_this, false, 0, 8).")\">".nice_format($cur_this, true, 0, 8)."</td>\n";
	echo "  <td><a href=\"?c=exchange/order&amp;t=$row_trade_order_c[id]"."$___l\" onmouseover=\"document.getElementById('t_$row_trade_order_c[id]_b').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_c[id]_c').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_c[id]_b').innerHTML = '".htmlentities(nice_format($row_trade_order_c["slc_amount"], true, 0, 4))."'; document.getElementById('t_$row_trade_order_c[id]_c').innerHTML = '".htmlentities(nice_format($row_trade_order_c["cur_amount"], true, 0, 8))."'\" onmouseout=\"document.getElementById('t_$row_trade_order_c[id]_b').style.color = ''; document.getElementById('t_$row_trade_order_c[id]_c').style.color = ''; document.getElementById('t_$row_trade_order_c[id]_b').innerHTML = '".htmlentities(nice_format($slc_this, true, 0, 4))."'; document.getElementById('t_$row_trade_order_c[id]_c').innerHTML = '".htmlentities(nice_format($cur_this, true, 0, 8))."'\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>\n";
	echo "</tr>\n";
}

echo "<tr>\n";
echo "  <td style=\"text-align: center\" colspan=\"4\">...</td>\n";
echo "</tr>\n";

$slt_trade_order_d = "SELECT *, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'sell' AND currency = '$currency'";
$rlt_trade_order_d = mysql_query($slt_trade_order_d);
$row_trade_order_d = mysql_fetch_assoc($rlt_trade_order_d);

echo "<tr>\n";
echo "  <td></td>\n";
echo "  <td style=\"text-align: right\" onclick=\"set_field('', 1, ".nice_format($row_trade_order_d["slc_amount"], false, 0, 4).")\">".nice_format($row_trade_order_d["slc_amount"], true, 0, 4)."</td>\n";
echo "  <td style=\"text-align: right\" onclick=\"set_field('', 3, ".nice_format($row_trade_order_d["cur_amount"], false, 0, 8).")\">".nice_format($row_trade_order_d["cur_amount"], true, 0, 8)."</td>\n";
echo "  <td></td>\n";
echo "</tr>\n";

?>
</table>

</td>

<td style="vertical-align: top; width: 50%">
<h3><?=$_LANG[$l]["e_buy_orders"]?></h3>

<table style="width: 100%; border-collapse: collapse;">
<tr><td style="width: 33%; text-align: center"><?=$_LANG[$l]["e_price"]?></td><td style="width: 33%; text-align: center">Solidcoins</td><td style="width: 33%; text-align: center"><?=$currencynp?></td><td></td></tr>
<?php

$slt_trade_order_a = "SELECT ROUND(price * 100000000) AS price, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND currency = '$currency' AND type = 'buy' GROUP BY ROUND(price * 100000000) ORDER BY price DESC LIMIT 10";
$rlt_trade_order_a = mysql_query($slt_trade_order_a);

$slc = 0; $cur = 0; $price = 0;
while ($row_trade_order_a = mysql_fetch_assoc($rlt_trade_order_a))
{
	$price = $row_trade_order_a["price"];
	$show = true;
	if ($_SESSION["li"] == 1)
	{
		$slt_trade_order_b = "SELECT *, ((amount - completed) * price) AS cur_amount, (amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND ROUND(price * 100000000) = $row_trade_order_a[price] AND currency = '$currency' AND user = '$_SESSION[user_id]'";
		$rlt_trade_order_b = mysql_query($slt_trade_order_b);
		
		if (mysql_num_rows($rlt_trade_order_b) != 0)
		{
			$row_trade_order_b = mysql_fetch_assoc($rlt_trade_order_b);
			
			$slc_this = $slc = $slc + $row_trade_order_a["slc_amount"];
			$cur_this = $cur = $cur + $row_trade_order_a["cur_amount"];
			
			echo "<tr>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_a\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_a["price"] / 100000000, true, 3, 8)."</td>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_b\" onclick=\"set_field('', 1, ".nice_format($slc_this, false, 0, 4).")\">".nice_format($slc_this, true, 0, 4)."</td>\n";
			echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_b[id]_c\" onclick=\"set_field('', 3, ".nice_format($cur_this, false, 0, 8).")\">".nice_format($cur_this, true, 0, 8)."</td>\n";
			echo "  <td><a href=\"?c=exchange/order&amp;t=$row_trade_order_b[id]"."$___l\" onmouseover=\"document.getElementById('t_$row_trade_order_b[id]_b').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_b[id]_c').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_b[id]_b').innerHTML = '".htmlentities(nice_format($row_trade_order_b["slc_amount"], true, 0, 4))."'; document.getElementById('t_$row_trade_order_b[id]_c').innerHTML = '".htmlentities(nice_format($row_trade_order_b["cur_amount"], true, 0, 8))."'\" onmouseout=\"document.getElementById('t_$row_trade_order_b[id]_b').style.color = ''; document.getElementById('t_$row_trade_order_b[id]_c').style.color = ''; document.getElementById('t_$row_trade_order_b[id]_b').innerHTML = '".htmlentities(nice_format($slc_this, true, 0, 4))."'; document.getElementById('t_$row_trade_order_b[id]_c').innerHTML = '".htmlentities(nice_format($cur_this, true, 0, 8))."'\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>\n";
			echo "</tr>\n";
			
			continue;
		}
	}

	$slc = $slc + $row_trade_order_a["slc_amount"];
	$cur = $cur + $row_trade_order_a["cur_amount"];
	
	echo "<tr>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_a["price"] / 100000000, true, 3, 8)."</td>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 1, ".nice_format($slc, false, 0, 4).")\">".nice_format($slc, true, 0, 4)."</td>\n";
	echo "  <td style=\"text-align: right\" onclick=\"set_field('', 3, ".nice_format($cur, false, 0, 4).")\">".nice_format($cur, true, 0, 8)."</td>\n";
	echo "  <td></td>\n";
	echo "</tr>\n";
}

$slt_trade_order_c = "SELECT *, ROUND(price * 100000000) AS price, ((amount - completed) * price) AS cur_amount, (amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND currency = '$currency' AND user = '$_SESSION[user_id]' AND ROUND(price * 100000000) < $price ORDER BY price DESC";
$rlt_trade_order_c = mysql_query($slt_trade_order_c);

while ($row_trade_order_c = mysql_fetch_assoc($rlt_trade_order_c))
{
	echo "<tr>\n";
	echo "  <td style=\"text-align: center\" colspan=\"4\">...</td>\n";
	echo "</tr>\n";
	
	$slt_trade_order_d = "SELECT *, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND currency = '$currency' AND ROUND(price * 100000000) > $row_trade_order_c[price]";
	$rlt_trade_order_d = mysql_query($slt_trade_order_d);
	$row_trade_order_d = mysql_fetch_assoc($rlt_trade_order_d);
	
	$slc_this = $slc = $row_trade_order_d["slc_amount"] + $row_trade_order_c["slc_amount"];
	$cur_this = $cur = $row_trade_order_d["cur_amount"] + $row_trade_order_c["cur_amount"];
	
	echo "<tr>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_a\" onclick=\"set_field('', 2, ".nice_format($row_trade_order_a["price"] / 100000000, false, 0, 8).")\">".nice_format($row_trade_order_c["price"] / 100000000, true, 3, 8)."</td>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_b\" onclick=\"set_field('', 1, ".nice_format($slc_this, false, 0, 4).")\">".nice_format($slc_this, true, 0, 4)."</td>\n";
	echo "  <td style=\"text-align: right\" id=\"t_$row_trade_order_c[id]_c\" onclick=\"set_field('', 3, ".nice_format($cur_this, false, 0, 8).")\">".nice_format($cur_this, true, 0, 8)."</td>\n";
	echo "  <td><a href=\"?c=exchange/order&amp;t=$row_trade_order_c[id]"."$___l\" onmouseover=\"document.getElementById('t_$row_trade_order_c[id]_b').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_c[id]_c').style.color = '#CB49FF'; document.getElementById('t_$row_trade_order_c[id]_b').innerHTML = '".htmlentities(nice_format($row_trade_order_c["slc_amount"], true, 0, 4))."'; document.getElementById('t_$row_trade_order_c[id]_c').innerHTML = '".htmlentities(nice_format($row_trade_order_c["cur_amount"], true, 0, 8))."'\" onmouseout=\"document.getElementById('t_$row_trade_order_c[id]_b').style.color = ''; document.getElementById('t_$row_trade_order_c[id]_c').style.color = ''; document.getElementById('t_$row_trade_order_c[id]_b').innerHTML = '".htmlentities(nice_format($slc_this, true, 0, 4))."'; document.getElementById('t_$row_trade_order_c[id]_c').innerHTML = '".htmlentities(nice_format($cur_this, true, 0, 8))."'\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>\n";
	echo "</tr>\n";
}

echo "<tr>\n";
echo "  <td style=\"text-align: center\" colspan=\"4\">...</td>\n";
echo "</tr>\n";

$slt_trade_order_d = "SELECT *, SUM((amount - completed) * price) AS cur_amount, SUM(amount - completed) AS slc_amount FROM trade_order WHERE active = 'yes' AND finished = 'no' AND type = 'buy' AND currency = '$currency'";
$rlt_trade_order_d = mysql_query($slt_trade_order_d);
$row_trade_order_d = mysql_fetch_assoc($rlt_trade_order_d);

$slc_this = $slc = $row_trade_order_d["slc_amount"] + $row_trade_order_c["slc_amount"];
$cur_this = $cur = $row_trade_order_d["cur_amount"] + $row_trade_order_c["cur_amount"];

echo "<tr>\n";
echo "  <td></td>\n";
echo "  <td style=\"text-align: right\" onclick=\"set_field('', 1, ".nice_format($row_trade_order_d["slc_amount"], false, 0, 4).")\">".nice_format($row_trade_order_d["slc_amount"], true, 0, 4)."</td>\n";
echo "  <td style=\"text-align: right\" onclick=\"set_field('', 3, ".nice_format($row_trade_order_d["cur_amount"], false, 0, 8).")\">".nice_format($row_trade_order_d["cur_amount"], true, 0, 8)."</td>\n";
echo "  <td></td>\n";
echo "</tr>\n";

?>
</table>

</td>

</tr>

</table>

<h3><?=$_LANG[$l]["e_last_24h"]?></h3>

<?php

$slt_trade_vol_avg = "SELECT SUM(amount) AS volume, (SUM(price * amount) / SUM(amount)) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
$rlt_trade_vol_avg = mysql_query($slt_trade_vol_avg);
$row_trade_vol_avg = mysql_fetch_assoc($rlt_trade_vol_avg);

$slt_trade_high = "SELECT MAX(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
$rlt_trade_high = mysql_query($slt_trade_high);
$row_trade_high = mysql_fetch_assoc($rlt_trade_high);

$slt_trade_low = "SELECT MIN(price) AS price FROM trade WHERE currency = '$currency' AND UNIX_TIMESTAMP(trade_time) >= ".(time() - 24*3600)."";
$rlt_trade_low = mysql_query($slt_trade_low);
$row_trade_low = mysql_fetch_assoc($rlt_trade_low);

?>

<table>
<tr> <td><?=$_LANG[$l]["e_lowest"]?></td><td onclick="set_field('', 2, <?=nice_format($row_trade_low["price"], false, 0, 8)?>)"><?=nice_format($row_trade_low["price"], false, 0, 8)?></td> <td style="padding-left: 50px;"><?=$_LANG[$l]["e_highest"]?></td><td onclick="set_field('', 2, <?=nice_format($row_trade_high["price"], false, 0, 8)?>)"><?=nice_format($row_trade_high["price"], false, 0, 8)?></td> </tr>
<tr> <td><?=$_LANG[$l]["e_average"]?></td><td onclick="set_field('', 2, <?=nice_format($row_trade_vol_avg["price"], false, 0, 8)?>)"><?=nice_format($row_trade_vol_avg["price"], false, 0, 8)?></td> <td style="padding-left: 50px;"><?=$_LANG[$l]["e_volume"]?></td><td onclick="set_field('', 1, <?=nice_format($row_trade_vol_avg["volume"], false, 0, 8)?>)"><?=nice_format($row_trade_vol_avg["volume"], false, 0, 0)?></td> </tr>
</table>

<h3><?=$_LANG[$l]["e_last_trades"]?></h3>

<table>

<tr>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["e_datetime"]?></td>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["e_type"]?></td>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["e_price"]?></td>
  <td style="padding-right: 5px; text-align: center">Solidcoins</td>
  <td style="padding-right: 5px; text-align: center"><?=$currencynp?></td>
  <td></td>
</tr>

<?php

$slt_trade_a = "SELECT *, UNIX_TIMESTAMP(trade_time) AS trade_time_u FROM trade WHERE currency = '$currency' ORDER BY trade_time DESC LIMIT 15";
$rlt_trade_a = mysql_query($slt_trade_a);

while ($row_trade_a = mysql_fetch_assoc($rlt_trade_a))
{
	echo "<tr>\n";
	
	echo "  <td style=\"padding-right: 5px\">".date("d.m. H:i:s", $row_trade_a["trade_time_u"] - $_SESSION["time_offset"] * 60)."</td>\r";
	echo "  <td style=\"padding-right: 5px\">"; echo ($row_trade_a["type"] == "buy") ? $_LANG[$l]["e_buy"] : $_LANG[$l]["e_sell"]; echo "</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\" onclick=\"set_field('', 2, ".nice_format($row_trade_a["price"], false, 3, 8).")\">".nice_format($row_trade_a["price"], true, 3, 8)."</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\" onclick=\"set_field('', 1, ".nice_format($row_trade_a["amount"], false, 0, 4).")\">".nice_format($row_trade_a["amount"], true, 0, 4)."</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\" onclick=\"set_field('', 3, ".nice_format($row_trade_a["amount"] * $row_trade_a["price"], false, 0, 8).")\">".nice_format($row_trade_a["amount"] * $row_trade_a["price"], true, 0, 8)."</td>";
	
	echo "<td>";
	if ($_SESSION["li"] == 1)
	{
		$slt_trade_order = "SELECT * FROM trade_order WHERE id = $row_trade_a[buy_trade_order]";
		$rlt_trade_order = mysql_query($slt_trade_order);
		$row_trade_order = mysql_fetch_assoc($rlt_trade_order);
		
		if ($row_trade_order["user"] == $_SESSION["user_id"])
		{
			echo "<a href=\"?c=exchange/order&amp;t=$row_trade_order[id]$___l\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a>";
		}
		else
		{
			$slt_trade_order = "SELECT * FROM trade_order WHERE id = $row_trade_a[sell_trade_order]";
			$rlt_trade_order = mysql_query($slt_trade_order);
			$row_trade_order = mysql_fetch_assoc($rlt_trade_order);
			
			if ($row_trade_order["user"] == $_SESSION["user_id"])
			{
				echo "<a href=\"?c=exchange/order&amp;t=$row_trade_order[id]$___l\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["e_trade_order"]."\" title=\"".$_LANG[$l]["e_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a>";
			}
		}
	}
	echo "</td>";
	
	echo "</tr>\n";
}

?>

</table>

</div>