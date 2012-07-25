<?php
language_file("exchange_my_trades");
?>

<div class="article">
<h2><span><?=$_LANG[$l]["emt_header1"]?></span></h2>
<p>

<h3><?=$_LANG[$l]["emt_trades"]?></h3>

<table>

<tr>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["emt_datetime"]?></td>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["emt_type"]?></td>
  <td style="padding-right: 5px; text-align: center"><?=$_LANG[$l]["emt_price"]?></td>
  <td style="padding-right: 5px; text-align: center">Solidcoins</td>
  <td style="padding-right: 5px; text-align: center"><?php if ($currency == "BTC") echo "Bitcoins"; if ($currency == "NMC") echo "Namecoins"; ?></td>
  <td></td>
</tr>

<?php

get_connection();


$slt_trade_a = "SELECT trade.price AS price, trade.amount AS amount, trade.type AS type, UNIX_TIMESTAMP(trade.trade_time) AS trade_time_u, t_sell.id AS sell_id, t_buy.id AS buy_id FROM trade trade, trade_order t_sell, trade_order t_buy WHERE trade.sell_trade_order = t_sell.id AND trade.buy_trade_order = t_buy.id AND (t_buy.user = '$_SESSION[user_id]' OR t_sell.user = '$_SESSION[user_id]')";

if (isset($type))
	$slt_trade_a .= " AND trade.type = '$type'";
if (isset($currency))
	$slt_trade_a .= " AND trade.currency = '$currency'";
$slt_trade_b = $slt_trade_a;
$slt_trade_a .= " ORDER BY trade.trade_time DESC LIMIT $from,$entries";

$rlt_trade_a = mysql_query($slt_trade_a);

while ($row_trade_a = mysql_fetch_assoc($rlt_trade_a))
{	
	echo "<tr>\n";
	
	echo "  <td style=\"padding-right: 5px\">".date("d.m. H:i:s", $row_trade_a["trade_time_u"] - $_SESSION["time_offset"] * 60)."</td>\r";
	echo "  <td style=\"padding-right: 5px\">"; echo ($row_trade_a["type"] == "buy") ? $_LANG[$l]["emt_buy"] : $_LANG[$l]["emt_sell"]; echo "</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\">".nice_format($row_trade_a["price"], true, 3, 8)."</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\">".nice_format($row_trade_a["amount"], true, 0, 4)."</td>";
	echo "  <td style=\"padding-right: 5px; text-align: right\">".nice_format($row_trade_a["amount"] * $row_trade_a["price"], true, 0, 8)."</td>";
	
	echo "<td>";
	
	if ($row_trade_a["sell_id"] == $_SESSION["user_id"]) {
		echo "<a href=\"?c=exchange/order&amp;t=$row_trade_a[sell_id]$___l\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["emt_trade_order"]."\" title=\"".$_LANG[$l]["emt_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a><span style=\"position: relative; top: -6px\"> sell ►</span>";
	} else {
		echo "<a href=\"?c=exchange/order&amp;t=$row_trade_a[buy_id]$___l\"><img src=\"images/trade.jpg\" alt=\"".$_LANG[$l]["emt_trade_order"]."\" title=\"".$_LANG[$l]["emt_trade_order"]."\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a><span style=\"position: relative; top: -6px\"> buy ◄</span>";
	}
		
	echo "</td>";
	
	echo "</tr>\n";
}

?>

</table>

<h3><?=$_LANG[$l]["emt_show"]?></h3>

<form action="?c=exchange/my_trades" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px"><?=$_LANG[$l]["emt_currency"]?></td><td><select name="currency" onchange="submit()"><option value="BTC" <?php if ($currency == "BTC") echo "selected=\"selected\" "; ?>>Bitcoins</option><option value="NMC" <?php if ($currency == "NMC") echo "selected=\"selected\" "; ?>>Namecoins</option></select></td></tr>
<tr><td style="padding-right: 5px"><?=$_LANG[$l]["emt_entries"]?></td><td>
<select name="entries" onchange="submit()"><option value="10">10</option><option value="20" <?php if ($entries == 20) echo "selected=\"selected\" "; ?>>20</option><option value="50" <?php if ($entries == 50) echo "selected=\"selected\" "; ?>>50</option><option value="100" <?php if ($entries == 100) echo "selected=\"selected\" "; ?>>100</option></select>
<?=$_LANG[$l]["emt_from"]?> <select name="from" onchange="submit()">
<?php
$rlt_trade_b = mysql_query($slt_trade_b);
$num = mysql_num_rows($rlt_trade_b);
for ($i = 0; $i <= floor($num/$entries); $i++) {
	if ($from == $i*$entries)
		echo "<option selected=\"selected\">".($i*$entries)."</option>";
	else
		echo "<option>".($i*$entries)."</option>";
}
?>
</select>
</td></tr>
</table>
</td><td style="width: 50%; vertical-align: top">
<table>
<tr><td style="padding-right: 5px"><?=$_LANG[$l]["emt_type"]?></td><td><select name="type" onchange="submit()"><option value=""><?=$_LANG[$l]["emt_any"]?></option><option value="buy" <?php if ($type == "buy") echo "selected=\"selected\" "; ?>><?=$_LANG[$l]["emt_buy"]?></option><option value="sell" <?php if ($type == "sell") echo "selected=\"selected\" "; ?>><?=$_LANG[$l]["emt_sell"]?></option></select></td></tr>
</table>
</tr>
</table>
</form>

</p>
</div>