<div class="article">
<h2><span>Trade orders</span></h2>
<p>

<h3>Orders</h3>

<table>

<tr>
  <td></td>
  <td style="text-align: center; padding-left: 10px">Type</td>
  <td style="text-align: center; padding-left: 10px">Date and time</td>
  <td style="text-align: center; padding-left: 10px">Price</td>
  <td style="text-align: center; padding-left: 10px">Currency</td>
  <td style="text-align: center; padding-left: 10px">Solidcoin amount</td>
  <td style="text-align: center; padding-left: 10px">Completed</td>
</tr>

<?php

get_connection();

$slt_trade_oder_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM trade_order WHERE user = '$_SESSION[user_id]'";
if (isset($type))
	$slt_trade_oder_a .= " AND type = '$type'";
if (isset($active))
	$slt_trade_oder_a .= " AND active = '$active'";
if (isset($currency))
	$slt_trade_oder_a .= " AND currency = '$currency'";
$slt_trade_oder_b = $slt_trade_oder_a;
$slt_trade_oder_a .= " ORDER BY filing_time DESC, id DESC LIMIT $from,$entries";

$rlt_trade_oder_a = mysql_query($slt_trade_oder_a);

while ($row_trade_oder_a = mysql_fetch_assoc($rlt_trade_oder_a))
{
	echo "<tr>";
	
	echo "<td><a href=\"?c=exchange/order&amp;t=$row_trade_oder_a[id]\"><img src=\"images/trade.jpg\" alt=\"Trade order\" title=\"Trade order\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>";
	
	echo "<td style=\"text-align: center; padding-left: 10px\">"; if ($row_trade_oder_a["type"] == "sell") echo "sell ►"; else echo "buy ◄"; echo "</td>";
	
	echo "<td style=\"text-align: center; padding-left: 10px\">".date("d.m. H:i:s", $row_trade_oder_a["filing_time_u"] - $_SESSION["time_offset"] * 60)."</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">".nice_format($row_trade_oder_a["price"], true, 3, 8)."</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	if ($row_trade_oder_a["currency"] == "BTC")
		echo "Bitcoins";
	elseif ($row_trade_oder_a["currency"] == "NMC")
		echo "Namecoins";
	echo "</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">".nice_format($row_trade_oder_a["amount"], true, 8, 4)."</td>";
	
	echo "<td style=\"padding-left: 10px\">".nice_format($row_trade_oder_a["completed"] / $row_trade_oder_a["amount"] * 100, false, 0, 3)."%</td>";
	
	echo "</tr>";
}

?>

</table>

<h3>Show</h3>

<form action="?c=exchange/orders" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px">Currency</td><td><select name="currency" onchange="submit()"><option value="">any</option><option value="BTC" <?php if ($currency == "BTC") echo "selected=\"selected\" "; ?>>Bitcoins</option><option value="NMC" <?php if ($currency == "NMC") echo "selected=\"selected\" "; ?>>Namecoins</option></select></td></tr>
<tr><td style="padding-right: 5px">Entries</td><td>
<select name="entries" onchange="submit()"><option value="10">10</option><option value="20" <?php if ($entries == 20) echo "selected=\"selected\" "; ?>>20</option><option value="50" <?php if ($entries == 50) echo "selected=\"selected\" "; ?>>50</option><option value="100" <?php if ($entries == 100) echo "selected=\"selected\" "; ?>>100</option></select>
from <select name="from" onchange="submit()">
<?php
$rlt_trade_oder_b = mysql_query($slt_trade_oder_b);
$num = mysql_num_rows($rlt_trade_oder_b);
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
</td><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px">Type</td><td><select name="type" onchange="submit()"><option value="">any</option><option value="buy" <?php if ($type == "buy") echo "selected=\"selected\" "; ?>>buy ◄</option><option value="sell" <?php if ($type == "sell") echo "selected=\"selected\" "; ?>>sell ►</option></select></td></tr>
<tr><td style="padding-right: 5px">Active</td><td><select name="active" onchange="submit()"><option value="yes">yes</option><option value="no" <?php if ($active == "no") echo "selected=\"selected\" "; ?>>no</option></select></td></tr>
</table>
</tr>
</table>
</form>

</p>
</div>