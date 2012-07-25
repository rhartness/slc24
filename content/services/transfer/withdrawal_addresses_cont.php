<div class="article">
<h2><span>Withdrawal addresses</span></h2>
<p>

<h3>List</h3>

<table>

<tr>
<td style="text-align: center;">ID</td>
<td style="text-align: center; padding-left: 10px">Type</td>
<td style="text-align: center; padding-left: 10px">Group</td>
<td style="text-align: center; padding-left: 10px">Address</td>
<td style="text-align: center; padding-left: 10px">Creation time</td>
<td style="text-align: center; padding-left: 10px">Amount</td>
</tr>

<?php

if ($amount > 0) {
	$slt_transfer_withdrawal_address_a = "SELECT a.id AS id, a.type AS type, a.`group` AS `group`, a.address AS address, UNIX_TIMESTAMP(a.creation_time) AS creation_time_u, SUM(t.amount) AS amount FROM transfer_withdrawal_address a, transfer_withdrawal t WHERE a.user = '$_SESSION[user_id]' AND t.withdrawal_address = a.id";
	if ($type)
		$slt_transfer_withdrawal_address_a .= " AND a.type = '$type'";
	$slt_transfer_withdrawal_address_a .= " GROUP BY t.withdrawal_address HAVING SUM(t.amount) >= $amount ORDER BY a.creation_time DESC";
} else {
	$slt_transfer_withdrawal_address_a = "SELECT *, UNIX_TIMESTAMP(creation_time) AS creation_time_u FROM transfer_withdrawal_address WHERE user = '$_SESSION[user_id]'";
	if ($type)
		$slt_transfer_withdrawal_address_a .= " AND type = '$type'";
	$slt_transfer_withdrawal_address_a .= "ORDER BY creation_time DESC";
}
$slt_transfer_withdrawal_address_a .= " LIMIT $from,$entries";
$slt_transfer_withdrawal_address_b = $slt_transfer_withdrawal_address_a;
$rlt_transfer_withdrawal_address_a = mysql_query($slt_transfer_withdrawal_address_a);

while ($row_transfer_withdrawal_address_a = mysql_fetch_assoc($rlt_transfer_withdrawal_address_a))
{
	echo "<tr>";
	
	echo "<td>";
	echo $row_transfer_withdrawal_address_a["id"];
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if ($row_transfer_withdrawal_address_a["type"] == "intern") echo "intern";
	if ($row_transfer_withdrawal_address_a["type"] == "extern") echo "extern";
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	echo $row_transfer_withdrawal_address_a["group"];
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	echo "<a href=\"?c=services/transfer/withdrawal_address&a=$row_transfer_withdrawal_address_a[address]\">".substr($row_transfer_withdrawal_address_a["address"], 0, 5)."...".substr($row_transfer_withdrawal_address_a["address"], -3, 3)."</a>";
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	echo date("d.m. H:i:s", $row_transfer_withdrawal_address_a["creation_time_u"] - $_SESSION["time_offset"] * 60);
	echo "</td>";
	
	$slt_transfer_withdrawal_b = "SELECT SUM(amount) AS amount FROM transfer_withdrawal WHERE withdrawal_address = '$row_transfer_withdrawal_address_a[id]'";
	$rlt_transfer_withdrawal_b = mysql_query($slt_transfer_withdrawal_b);
	$row_transfer_withdrawal_b = mysql_fetch_assoc($rlt_transfer_withdrawal_b);
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	echo nice_format($row_transfer_withdrawal_b["amount"], true, 0, 4);
	echo "</td>";
	
	echo "</tr>\n";
}

?>

</table>

<h3>Show</h3>

<form action="?c=services/transfer/withdrawal_addresses" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
<tr><td style="padding-right: 5px">Type</td><td><select name="type" onchange="submit()"><option value="">any</option><option value="intern" <?php if ($type == "intern") echo "selected=\"selected\" "; ?>>intern</option><option value="extern" <?php if ($type == "extern") echo "selected=\"selected\" "; ?>>extern</option></select></td></tr></tr>
<tr><td style="padding-right: 5px">Amount</td><td><select name="amount" onchange="submit()"><option value="">any</option><option value="0.0001" <?php if ($amount == "0.0001") echo "selected=\"selected\" "; ?>>more than 0</option><option value="1.0001" <?php if ($amount == "1.0001") echo "selected=\"selected\" "; ?>>more than 1</option><option value="10.0001" <?php if ($amount == "10.0001") echo "selected=\"selected\" "; ?>>more than 10</option><option value="100.0001" <?php if ($amount == "100.0001") echo "selected=\"selected\" "; ?>>more than 100</option><option value="1000.0001" <?php if ($amount == "1000.0001") echo "selected=\"selected\" "; ?>>more than 1000</option></select></td></tr>
<tr><td style="padding-right: 5px">Entries</td><td>
<select name="entries" onchange="submit()"><option value="10">10</option><option value="20" <?php if ($entries == 20) echo "selected=\"selected\" "; ?>>20</option><option value="50" <?php if ($entries == 50) echo "selected=\"selected\" "; ?>>50</option><option value="100" <?php if ($entries == 100) echo "selected=\"selected\" "; ?>>100</option></select>
from <select name="from" onchange="submit()">
<?php
$rlt_transfer_deposit_address_b = mysql_query($slt_transfer_deposit_address_b);
$num = mysql_num_rows($rlt_transfer_deposit_address_b);
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
</td><td style="width: 50%;">
</tr>
</table>
</form>

</p>
</div>