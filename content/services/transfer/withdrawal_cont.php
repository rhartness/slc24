<div class="article">
<h2><span>Withdrawal</span></h2>
<p>

<h3>Data</h3>

<table>
  <tr><td style="padding-right: 10px">Type</td><td><?php if ($row_transfer_withdrawal_address_a["type"] == "intern") echo "intern"; else echo "extern"; ?></td></tr>
  <tr><td style="padding-right: 10px">Withdrawal address (ID)</td><td><a href="?c=services/transfer/withdrawal_address&amp;a=<?=$row_transfer_withdrawal_address_a["address"]?>"><?=$row_transfer_withdrawal_address_a["address"]?></a> (<?=$row_transfer_withdrawal_address_a["id"]?>)</td></tr>
  <tr><td style="padding-right: 10px">Amount</td><td><?=nice_format($row_transfer_withdrawal_a["amount"], false, 0, 4)?></td></tr>
  <tr><td style="padding-right: 10px">Txid</td><td><?php if ($row_transfer_withdrawal_address_a["type"] == "intern") echo $row_transfer_withdrawal_a["txid"]; else echo crypte_transaction($row_transfer_withdrawal_a["txid"]); ?></td></tr>
  <tr><td style="padding-right: 10px">Date and time</td><td><?=date("d.m. H:i:s", $row_transfer_withdrawal_a["filing_time_u"] - $_SESSION["time_offset"] * 60)?></td></tr>
</table>

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