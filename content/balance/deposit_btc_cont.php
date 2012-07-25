<div class="article">
<h2><span>Deposit Bitcoins</span></h2>
<p>

<?php
include $_SITE["root"]."inc/deposit.php";
include $_SITE["root"]."inc/json/jsonRPCClient.php";

$balance = get_balance($_SESSION["user_id"], "BTC");

$bitcoin = new jsonRPCClient("http://mysolidcoin:yHOVilk0IgV@127.0.0.1:8332/");

?>

To deposit Bitcoins please transfer a certain amount of Bitcoins to an address that is dedicated to you.<br /><br />
Current balance: <?=nice_format($balance, false, 0, 8)?> Bitcoins<br />
<br />
Your Bitcoins will be available after 3 confirmations.<br />
<table>
<tr><td>Address</td><td>Received</td><td>Booked</td><td>Pending</td></tr>
<?php

$slt_address_a = "SELECT * FROM deposit_address WHERE user = '$_SESSION[user_id]' AND currency = 'BTC' ORDER BY creation_date DESC LIMIT 10";
$rlt_address_a = mysql_query($slt_address_a);

if (mysql_num_rows($rlt_address_a) != 0)
{
	$new = true;
	while ($row_address_a = mysql_fetch_assoc($rlt_address_a))
	{	
		$data = handle_deposit_btc($row_address_a);
		
		$received = $data["received"];
		$pending = $data["pending"];
		$booked = $data["booked"];
		
		if ($row_address_a["creation_date"] > "2011-10-11 00:00:00")
		{
			if ($received == 0)
			{
				$new = false;
				echo "<tr><td>$row_address_a[address]</td><td style=\"text-align: right\">".nice_format($received, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 8)."</td></tr>\r\n";
			}
			elseif ($pending > 0)
				echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'; this.onclick = 'function {}'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td style=\"text-align: right\">".nice_format($received, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 8)."</td></tr>\r\n";
			else
				echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'; this.onclick = 'function {}'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td style=\"text-align: right\">".nice_format($received, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($booked, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format($pending, true, 0, 8)."</td></tr>\r\n";
		}
		else
			echo "<tr><td onclick=\"this.innerHTML='$row_address_a[address]'\">".substr($row_address_a["address"], 0, 5)."...".substr($row_address_a["address"], -3, 3)."</td><td>? (old address)</td><td>? (old address)</td><td>? (old address)</td></tr>\r\n";
	}
	
	if ($new)
	{
		$address = $bitcoin->getnewaddress();
			
		$ins_address_a = "INSERT INTO deposit_address (currency, address, user, booked, creation_date) VALUES ('BTC', '$address', '$_SESSION[user_id]', '0', NOW())";
		mysql_query($ins_address_a);
		
		echo "<tr><td>$address</td><td style=\"text-align: right\">".nice_format(0, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format(0, true, 0, 8)."</td><td style=\"text-align: right\">".nice_format(0, true, 0, 8)."</td></tr>\r\n";
	}
}
else
{
	$address = $bitcoin->getnewaddress();
	
	echo "<tr><td>$address</td><td>0</td><td>0</td><td>0</td></tr>\r\n";
	
	$ins_address_a = "INSERT INTO deposit_address (currency, address, user, booked, creation_date) VALUES ('BTC', '$address', '$_SESSION[user_id]', '0', NOW())";
	mysql_query($ins_address_a);
}

?>
</table>
<br />
Transfer any amount to any of the addresses, preferably to one of the new ones for best anonymity.
</p>
</div>