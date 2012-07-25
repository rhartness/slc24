<div class="article">
<h2><span>Trade order</span></h2>
<p>

<h3>Data</h3>

<table>
  <tr><td style="padding-right: 10px">Type</td><td><?php if ($row_trade_order_a["type"] == "buy") echo "buy"; else echo "sell"; ?></td></tr>
  <tr><td style="padding-right: 10px">Price</td><td><?php echo nice_format($row_trade_order_a["price"], false, 0, 8); ?> Solidcoins per <?=$currencyn?></td></tr>
  <tr><td style="padding-right: 10px">Date and time</td><td><?=date("d.m. H:i:s", $row_trade_order_a["filing_time_u"] - $_SESSION["time_offset"] * 60)?></td></tr>
  <tr><td style="padding-right: 10px">Active</td><td><?php if ($row_trade_order_a["active"] == "yes") echo "yes"; else echo "no"; ?></td></tr>
</table>

<br />

<table>
  <tr>
    <td style="padding-right: 10px">Remaining (<?php echo nice_format(($row_trade_order_a["amount"] - $row_trade_order_a["completed"]) / $row_trade_order_a["amount"] * 100, false, 0, 3); ?>%)</td>
    <td style="padding-right: 10px">Completed (<?php echo nice_format($row_trade_order_a["completed"] / $row_trade_order_a["amount"] * 100, false, 0, 3); ?>%)</td>
    <td style="padding-right: 10px">Total</td>
    <td></td>
  </tr>
  <tr>
    <td style="padding-right: 10px"><?php echo nice_format(nice_format($row_trade_order_a["amount"] - $row_trade_order_a["completed"], false, 0, 4), true, 8, 8); ?></td>
    <td style="padding-right: 10px"><?php echo nice_format(nice_format($row_trade_order_a["completed"], false, 0, 4), true, 8, 8); ?></td>
    <td style="text-align: right"><?php echo nice_format(nice_format($row_trade_order_a["amount"], false, 0, 4), true, 8, 8); ?></td>
    <td>Solidcoins</td>
  </tr>
<tr>
  <td style="padding-right: 10px"><?php echo nice_format(nice_format(($row_trade_order_a["amount"] - $row_trade_order_a["completed"]) * $row_trade_order_a["price"], false, 0, 8), true, 8, 8); ?></td>
  <td style="padding-right: 10px"><?php echo nice_format(nice_format($row_trade_order_a["completed"] * $row_trade_order_a["price"], false, 0, 8), true, 8, 8); ?></td>
  <td style="text-align: right"><?php echo nice_format(nice_format($row_trade_order_a["amount"] * $row_trade_order_a["price"], false, 0, 8), true, 8, 8); ?></td>
  <td><?=$currencynp?></td>
</tr>
</table>

<h3>History</h3>

<table>

<tr>
  <td style="text-align: center;">Type</td>
  <td style="text-align: center; padding-left: 10px">Direction</td>
  <td style="text-align: center; padding-left: 10px">Date and time</td>
  <td style="text-align: center; padding-left: 10px">Amount</td>
  <td style="text-align: center; padding-left: 10px">Fee</td>
  <td style="text-align: center; padding-left: 10px">Currency</td>
  <td style="text-align: center; padding-left: 10px">Info</td>
</tr>

<?php

$slt_transaction_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transaction WHERE user = '$_SESSION[user_id]' AND info IN ('trade_return', 'trade_placement', 'trade_cancellation', 'trade_increase') AND info_id = '$trade_id' ORDER BY filing_time DESC, id DESC";
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
		echo nice_format(nice_format($row_transaction_a["fee"], false, 0, 4), true, 0, 8);
	if ($row_transaction_a["currency"] == "BTC")
		echo nice_format($row_transaction_a["fee"], true, 0, 8);
	if ($row_transaction_a["currency"] == "NMC")
		echo nice_format($row_transaction_a["fee"], true, 0, 8);
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
		echo "return";
	if ($row_transaction_a["info"] == "trade_placement")
		echo "placement";
	if ($row_transaction_a["info"] == "trade_cancellation")
		echo "cancellation";
	if ($row_transaction_a["info"] == "trade_increase")
		echo "increase";
	
	echo "</tr>\n";
}

?>

</table>

<h3>Actions</h3>
<?php if ($row_trade_order_a["active"] == "yes") { ?>
<a href="?c=exchange/order&amp;t=<?=$trade_id?>&ca=1">Cancel</a>
<?php } else { ?>
Cancel
<?php } ?>

</p>
</div>