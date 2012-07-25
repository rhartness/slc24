<div class="article">
<h2><span>Deposit addresses</span></h2>
<p>

<h3>List</h3>

<table>

<tr>
<td style="text-align: center;">ID</td>
<td style="text-align: center; padding-left: 10px">Group</td>
<td style="text-align: center; padding-left: 10px">Address</td>
<td style="text-align: center; padding-left: 10px">Callback</td>
<td style="text-align: center; padding-left: 10px">Send mail</td>
<td style="text-align: center; padding-left: 10px">Creation time</td>
<td style="text-align: center; padding-left: 10px">Amount</td>
</tr>

<?php

if ($amount > 0)
	$slt_transfer_deposit_address_a = "SELECT a.id AS id, a.`group` AS `group`, a.address AS address, a.callback AS callback, a.send_mail AS send_mail, UNIX_TIMESTAMP(a.creation_time) AS creation_time_u, SUM(t.amount) AS amount FROM transfer_deposit_address a, transfer_deposit t WHERE a.user = '$_SESSION[user_id]' AND t.deposit_address = a.id GROUP BY t.deposit_address HAVING SUM(t.amount) >= $amount ORDER BY a.creation_time DESC";
else
	$slt_transfer_deposit_address_a = "SELECT *, UNIX_TIMESTAMP(creation_time) AS creation_time_u FROM transfer_deposit_address WHERE user = '$_SESSION[user_id]' ORDER BY creation_time DESC";
$slt_transfer_deposit_address_a .= " LIMIT $from,$entries";
$slt_transfer_deposit_address_b = $slt_transfer_deposit_address_a;
$rlt_transfer_deposit_address_a = mysql_query($slt_transfer_deposit_address_a);

while ($row_transfer_deposit_address_a = mysql_fetch_assoc($rlt_transfer_deposit_address_a))
{
	echo "<tr>";
	
	//echo "<td style=\"text-align: center\">";
	//if ($row_transfer_deposit_a["type"] == "intern") echo "intern";
	//if ($row_transfer_deposit_a["type"] == "extern") echo "extern";
	//echo "</td>";
	
	echo "<td>";
	echo $row_transfer_deposit_address_a["id"];
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if (strlen($row_transfer_deposit_address_a["group"]) < 15)
		echo htmlentities($row_transfer_deposit_address_a["group"]);
	else
		echo htmlentities(substr($row_transfer_deposit_address_a["group"], 0, 15)."...");
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	echo "<a href=\"?c=services/transfer/deposit_address&a=$row_transfer_deposit_address_a[address]\">".substr($row_transfer_deposit_address_a["address"], 0, 5)."...".substr($row_transfer_deposit_address_a["address"], -3, 3)."</a>";
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if (strlen($row_transfer_deposit_address_a["callback"]) < 15)
		echo htmlentities($row_transfer_deposit_address_a["callback"]);
	else
		echo htmlentities(substr($row_transfer_deposit_address_a["callback"], 0, 15)."...");
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	if ($row_transfer_deposit_address_a["send_mail"] == "yes") echo "yes"; else echo "no";
	echo "</td>";
	
	echo "<td style=\"padding-left: 10px\">";
	echo date("d.m. H:i:s", $row_transfer_deposit_address_a["creation_time_u"] - $_SESSION["time_offset"] * 60);
	echo "</td>";
	
	if (!isset($row_transfer_deposit_address_a["amount"])) {
		$slt_transfer_deposit_b = "SELECT SUM(amount) AS amount FROM transfer_deposit WHERE deposit_address = '$row_transfer_deposit_address_a[id]'";
		$rlt_transfer_deposit_b = mysql_query($slt_transfer_deposit_b);
		$row_transfer_deposit_b = mysql_fetch_assoc($rlt_transfer_deposit_b);
		
		$tamount = $row_transfer_deposit_b["amount"];
	} else {
		$tamount = $row_transfer_deposit_address_a["amount"];
	}
	
	echo "<td style=\"text-align: right; padding-left: 10px\">";
	echo nice_format($tamount, true, 0, 4);
	echo "</td>";
	
	echo "</tr>\n";
}

?>

</table>

<h3>Show</h3>

<form action="?c=services/transfer/deposit_addresses" method="post">
<table style="width: 70%">
<tr><td style="width: 50%">
<table>
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
</td><td style="width: 50%">
<table>
</table>
</tr>
</table>
</form>

</p>
</div>