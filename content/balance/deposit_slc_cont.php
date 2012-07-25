<div class="article">
<h2><span>Deposit Solidcoins</span></h2>
<p>

<?php
include $_SITE["root"]."inc/deposit.php";
include $_SITE["root"]."inc/json/jsonRPCClient.php";

$balance = get_balance($_SESSION["user_id"], "SLC");

$solidcoin = new jsonRPCClient("http://mysolidcoin:D2bcMz1PhDU@127.0.0.1:7556/");

?>

To deposit Solidcoins please transfer a certain amount of Solidcoins to an address that is dedicated to you.<br /><br />
Current balance: <?=nice_format($balance, false, 0, 4)?> Solidcoins<br />
<br />
Your Solidcoins will be available after 2, 4 or 6 confirmations depending on how much you deposit.<br />
<small>(6 confirmations for deposits greater than 500 Solidcoins and 4 confirmations for deposits greater than 5 Solidcoins)</small><br />
<table>
<tr><td>Address</td><td>Received</td><td>Booked</td><td>Pending</td></tr>
<?php

$slt_address_a = "SELECT * FROM deposit_address WHERE user = '$_SESSION[user_id]' AND currency = 'SLC' ORDER BY creation_date DESC LIMIT 10";
$rlt_address_a = mysql_query($slt_address_a);

if (mysql_num_rows($rlt_address_a) != 0)
{
	$new = true;
	while ($row_address_a = mysql_fetch_assoc($rlt_address_a))
	{	
		$data = handle_deposit_slc($row_address_a);
		$received = $data["received"];
		$pending = $data["pending"];
		$booked = $data["booked"];
		
		if ($row_address_a["creation_date"] > "2011-10-11 00:00:00")
		{
			if ($received == 0)
			{
				$new = false;
				echo "<tr><td>$row_address_a[address]</td><td style=\"text-align: right\">".nice_format($received, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 4)."</td></tr>\r\n";
			}
			elseif ($pending > 0)
				echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'; this.onclick = 'function {}'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td style=\"text-align: right\">".nice_format($received, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 4)."</td></tr>\r\n";
			else
				echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'; this.onclick = 'function {}'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td style=\"text-align: right\">".nice_format($received, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 4)."</td></tr>\r\n";
		}
		else
			echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'; this.onclick = 'function {}'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td>? (old address)</td><td>? (old address)</td><td>? (old address)</td></tr>\r\n";
	}
	
	if ($new)
	{
		$address = $solidcoin->sc_getnewaddress("main");
			
		$ins_address_a = "INSERT INTO deposit_address (currency, address, user, booked, creation_date) VALUES ('SLC', '$address', '$_SESSION[user_id]', '0', NOW())";
		mysql_query($ins_address_a);
		
		echo "<tr><td>$address</td><td style=\"text-align: right\">".nice_format(0, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format(0, true, 0, 4)."</td><td style=\"text-align: right\">".nice_format(0, true, 0, 4)."</td></tr>\r\n";
	}
}
else
{
	$address = $solidcoin->sc_getnewaddress("main");
	
	echo "<tr><td>$address</td><td>0</td><td>0</td><td>0</td></tr>\r\n";
	
	$ins_address_a = "INSERT INTO deposit_address (currency, address, user, booked, creation_date) VALUES ('SLC', '$address', '$_SESSION[user_id]', '0', NOW())";
	mysql_query($ins_address_a);
}

?>
</table>
<br />
Transfer any amount to any of the addresses, preferably to one of the new ones for best anonymity.<br />
<small>Please note: Because we want to avoid fragmentation, we discourage from depositing very small amounts of SLC. For this reason 0.01 SLC are subtracted from your depositing amount at each deposit.</small>
</p>
</div>