<div class="article">
<h2><span>Balance</span></h2>
<p>

<h3>Your balances</h3>

<table>
<tr><td>Solidcoins</td><td><?=nice_format(get_balance($_SESSION["user_id"], "SLC"), false, 0, 4)?></td></tr>
<tr><td>Bitcoins</td><td><?=nice_format(get_balance($_SESSION["user_id"], "BTC"), false, 0, 8)?></td></tr>
<tr><td>Namecoins</td><td><?=nice_format(get_balance($_SESSION["user_id"], "NMC"), false, 0, 8)?></td></tr>
</table>

<h3>History</h3>

<table>

<tr>
<td style="text-align: center;">Type</td>
<td style="text-align: center; padding-left: 10px">Direction</td>
<td style="text-align: center; padding-left: 10px">Date and time</td>
<td style="text-align: center; padding-left: 10px">Amount</td>
<td style="text-align: center; padding-left: 10px">Balance</td>
<td style="text-align: center; padding-left: 10px">Currency</td>
<td style="text-align: center; padding-left: 10px">Info</td>
</tr>

<?php

$slt_transaction_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transaction WHERE user = '$_SESSION[user_id]'";
if (isset($type))
	$slt_transaction_a .= " AND type = '$type'";
if (isset($dir))
	$slt_transaction_a .= " AND direction = '$dir'";
if (isset($currency))
	$slt_transaction_a .= " AND currency = '$currency'";
$slt_transaction_b = $slt_transaction_a;
$slt_transaction_a .= " ORDER BY filing_time DESC, id DESC LIMIT $from,$entries";

$rlt_transaction_a = mysql_query($slt_transaction_a);

while ($row_transaction_a = mysql_fetch_assoc($rlt_transaction_a))
{
	echo "<tr>";
	
	echo "<td style=\"text-align: center\">";
	if ($row_transaction_a["type"] == "intern") echo "intern";
	if ($row_transaction_a["type"] == "extern") echo "extern";
	echo "</td>";
	
	echo "<td style=\"text-align: center; padding-left: 10px\">";
	if ($row_transaction_a["direction"] == "in") echo "in (+)";
	if ($row_transaction_a["direction"] == "out") echo "out (-)";
	echo "</td>";
	
	echo "<td style=\"text-align: center; padding-left: 10px\">";
	echo date("d.m. H:i:s", $row_transaction_a["filing_time_u"] - $_SESSION["time_offset"] * 60);
	echo "</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	if ($row_transaction_a["currency"] == "SLC")
		echo nice_format(nice_format($row_transaction_a["amount"], false, 0, 4), true, 0, 8);
	if ($row_transaction_a["currency"] == "BTC")
		echo nice_format($row_transaction_a["amount"], true, 0, 8);
	if ($row_transaction_a["currency"] == "NMC")
		echo nice_format($row_transaction_a["amount"], true, 0, 8);
	echo "</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	if ($row_transaction_a["currency"] == "SLC")
		echo nice_format(nice_format($row_transaction_a["balance"], false, 0, 4), true, 0, 8);
	if ($row_transaction_a["currency"] == "BTC")
		echo nice_format($row_transaction_a["balance"], true, 0, 8);
	if ($row_transaction_a["currency"] == "NMC")
		echo nice_format($row_transaction_a["balance"], true, 0, 8);
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if ($row_transaction_a["currency"] == "SLC")
		echo "Solidcoins";
	if ($row_transaction_a["currency"] == "BTC")
		echo "Bitcoins";
	if ($row_transaction_a["currency"] == "NMC")
		echo "Namecoins";
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if ($row_transaction_a["info"] == "trade_return")
		echo "<a href=\"?c=exchange/order&amp;t=$row_transaction_a[info_id]\"><img src=\"images/trade.jpg\" alt=\"Trade order\" title=\"Trade order\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">return</span>";
	if ($row_transaction_a["info"] == "trade_placement")
		echo "<a href=\"?c=exchange/order&amp;t=$row_transaction_a[info_id]\"><img src=\"images/trade.jpg\" alt=\"Trade order\" title=\"Trade order\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">placement</span>";
	if ($row_transaction_a["info"] == "trade_cancellation")
		echo "<a href=\"?c=exchange/order&amp;t=$row_transaction_a[info_id]\"><img src=\"images/trade.jpg\" alt=\"Trade order\" title=\"Trade order\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">cancellation</span>";
	if ($row_transaction_a["info"] == "trade_increase")
		echo "<a href=\"?c=exchange/order&amp;t=$row_transaction_a[info_id]\"><img src=\"images/trade.jpg\" alt=\"Trade order\" title=\"Trade order\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">increase</span>";
	if ($row_transaction_a["info"] == "deposit")
	{
		if ($row_transaction_a["type"] == "intern") {
			echo "<span title=\"$row_transaction_a[info_id]\">deposit</span>";
		} else {
			$slt_crypto_transaction_a = "SELECT * FROM crypto_transaction WHERE id = '$row_transaction_a[info_id]'";
			$rlt_crypto_transaction_a = mysql_query($slt_crypto_transaction_a);
			$row_crypto_transaction_a = mysql_fetch_assoc($rlt_crypto_transaction_a);
			
			echo "<span title=\"$row_crypto_transaction_a[txid]\">deposit</span>";
		}
	}
	if ($row_transaction_a["info"] == "withdrawal")
	{
		if ($row_transaction_a["type"] == "intern") {
			echo "<span title=\"$row_transaction_a[info_id]\">withdrawal</span>";
		} else {
			$slt_crypto_transaction_a = "SELECT * FROM crypto_transaction WHERE id = '$row_transaction_a[info_id]'";
			$rlt_crypto_transaction_a = mysql_query($slt_crypto_transaction_a);
			$row_crypto_transaction_a = mysql_fetch_assoc($rlt_crypto_transaction_a);
			
			echo "<span title=\"$row_crypto_transaction_a[txid]\">withdrawal</span>";
		}
	}
	if ($row_transaction_a["info"] == "internal_transfer")
		echo "<span title=\"$row_transaction_a[info_id]\">internal transfer</span>";
	if ($row_transaction_a["info"] == "transfer_withdrawal")
		echo "<a href=\"?c=services/transfer/withdrawal&amp;id=$row_transaction_a[info_id]\"><img src=\"images/transfer.jpg\" alt=\"Transfer\" title=\"Transfer withdrawal\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">withdrawal</span>";
	if ($row_transaction_a["info"] == "transfer_deposit")
		echo "<a href=\"?c=services/transfer/deposit&amp;id=$row_transaction_a[info_id]\"><img src=\"images/transfer.jpg\" alt=\"Transfer\" title=\"Transfer deposit\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a> <span style=\"position: relative; top: -6px\">deposit</span>";
	if ($row_transaction_a["info"] == "sc24_deposit")
		echo "old slc24 site";
	echo "</td>";
	
	echo "</tr>\n";
}

?>

</table>

<h3>Show</h3>

<form action="?c=balance" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px">Currency</td><td><select name="currency" onchange="submit()"><option value="">any</option><option value="SLC" <?php if ($currency == "SLC") echo "selected=\"selected\" "; ?>>Solidcoins</option><option value="BTC" <?php if ($currency == "BTC") echo "selected=\"selected\" "; ?>>Bitcoins</option><option value="NMC" <?php if ($currency == "NMC") echo "selected=\"selected\" "; ?>>Namecoins</option></select></td></tr>
<tr><td style="padding-right: 5px">Entries</td><td>
<select name="entries" onchange="submit()"><option value="10">10</option><option value="20" <?php if ($entries == 20) echo "selected=\"selected\" "; ?>>20</option><option value="50" <?php if ($entries == 50) echo "selected=\"selected\" "; ?>>50</option><option value="100" <?php if ($entries == 100) echo "selected=\"selected\" "; ?>>100</option></select>
from <select name="from" onchange="submit()">
<?php
$rlt_transaction_b = mysql_query($slt_transaction_b);
$num = mysql_num_rows($rlt_transaction_b);
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
<tr><td style="padding-right: 5px">Direction</td><td><select name="dir" onchange="submit()"><option value="">any</option><option value="in" <?php if ($dir == "in") echo "selected=\"selected\" "; ?>>in</option><option value="out" <?php if ($dir == "out") echo "selected=\"selected\" "; ?>>out</option></select></td></tr>
<tr><td style="padding-right: 5px">Type</td><td><select name="type" onchange="submit()"><option value="">any</option><option value="intern" <?php if ($type == "intern") echo "selected=\"selected\" "; ?>>intern</option><option value="extern" <?php if ($type == "extern") echo "selected=\"selected\" "; ?>>extern</option></select></td></tr>
</table>
</tr>
</table>
</form>

</p>
</div>