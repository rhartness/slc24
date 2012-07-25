<div class="article">
<h2><span>Deposit address</span></h2>
<p>

<h3>Data</h3>

<?php

$slt_transfer_deposit_b = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
$row_transfer_deposit_b = mysql_fetch_assoc($rlt_transfer_deposit_b);

?>

<table>
  <tr><td style="padding-right: 10px">ID</td><td><?=$row_transfer_deposit_address_a["id"]?></td></tr>
<?php if ($row_transfer_deposit_address_a["group"]) { ?>
  <tr><td style="padding-right: 10px">Group</td><td><?=$row_transfer_deposit_address_a["group"]?></td></tr>
<?php } ?>
  <tr><td style="padding-right: 10px">Address</td><td><?=$address?></td></tr>
  <tr><td style="padding-right: 10px">Send mail</td><td><?php if ($row_transfer_deposit_address_a["send_mail"] == "yes") echo "yes"; else echo "no"; ?></td></tr>
<?php if ($row_transfer_deposit_address_a["callback"]) { ?>
  <tr><td style="padding-right: 10px">Callback</td><td><?=htmlentities($row_transfer_deposit_address_a["callback"])?></td></tr>
<?php } ?>
  <tr><td style="padding-right: 10px">Total amount</td><td><?=nice_format($row_transfer_deposit_b["amount"], false, 0, 4)?></td></tr>
  <tr><td style="padding-right: 10px">Creation time</td><td><?=date("d.m. H:i:s", $row_transfer_deposit_address_a["creation_time_u"] - $_SESSION["time_offset"] * 60)?></td></tr>
<?php if ($row_transfer_deposit_address_a["data"]) { ?>
  <tr><td style="padding-right: 10px">Data</td><td><?=htmlentities($row_transfer_deposit_address_a["data"])?></td></tr>
<?php } ?>
</table>

<h3>History</h3>

<table>

<tr>
<td style="text-align: center;">Type</td>
<td style="text-align: center; padding-left: 10px">Date and time</td>
<td style="text-align: center; padding-left: 10px">Amount</td>
<td style="text-align: center; padding-left: 10px">Txid</td>
<td></td>
</tr>

<?php

$slt_transfer_deposit_a = "SELECT *, UNIX_TIMESTAMP(filing_time) AS filing_time_u FROM transfer_deposit WHERE deposit_address = $row_transfer_deposit_address_a[id]";
if (isset($type))
	$slt_transfer_deposit_a .= " AND type = '$type'";
$slt_transfer_deposit_a .= " ORDER BY filing_time DESC, id DESC LIMIT $from,$entries";

$rlt_transfer_deposit_a = mysql_query($slt_transfer_deposit_a);

while ($row_transfer_deposit_a = mysql_fetch_assoc($rlt_transfer_deposit_a))
{
	echo "<tr>";
	
	echo "<td>";
	if ($row_transfer_deposit_a["type"] == "intern") echo "intern";
	if ($row_transfer_deposit_a["type"] == "extern") echo "extern";
	echo "</td>";
	
	echo "<td style=\"text-align: center; padding-left: 10px\">";
	echo date("d.m. H:i:s", $row_transfer_deposit_a["filing_time_u"] - $_SESSION["time_offset"] * 60);
	echo "</td>";
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	echo nice_format($row_transfer_deposit_a["amount"], true, 0, 4);
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if ($row_transfer_deposit_a["type"] == "intern") 
		echo $row_transfer_deposit_a["txid"];
	else {
		$txid = crypte_transaction($row_transfer_deposit_a["txid"]);
		echo "<span title=\"$txid\">".substr($txid, 0, 10)."...".substr($txid, -3, 3)."</span>";
	}
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\"><a href=\"?c=services/transfer/deposit&amp;id=$row_transfer_deposit_a[id]\"><img src=\"images/transfer.jpg\" alt=\"Deposit\" title=\"Deposit\" style=\"border: 1px solid #B7B7B7; padding: 2px\" /></a></td>";
	
	echo "</tr>\n";
}

?>

</table>

<h3>Show</h3>

<form action="?c=services/transfer/deposit_address&amp;a=<?=$address?>" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px">Type</td><td><select name="type" onchange="submit()"><option value="">any</option><option value="intern" <?php if ($type == "intern") echo "selected=\"selected\" "; ?>>intern</option><option value="extern" <?php if ($type == "extern") echo "selected=\"selected\" "; ?>>extern</option></select></td></tr>
<tr><td style="padding-right: 5px">Entries</td><td>
<select name="entries" onchange="submit()"><option value="10">10</option><option value="20" <?php if ($entries == 20) echo "selected=\"selected\" "; ?>>20</option><option value="50" <?php if ($entries == 50) echo "selected=\"selected\" "; ?>>50</option><option value="100" <?php if ($entries == 100) echo "selected=\"selected\" "; ?>>100</option></select>
from <select name="from" onchange="submit()">
<?php
$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
$num = mysql_num_rows($rlt_transfer_deposit_b);
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
</table>
</tr>
</table>
</form>

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