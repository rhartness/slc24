<div class="gadget">
<h2 class="star">Transfer menu</h2>
<ul class="sb_menu">
<li><a href="?c=services/transfer<?=$___l?>"<?php if ($content == "services/transfer") echo " class=\"active\""; ?>>Home</a></li>
<?php if ($_SESSION["li"] == 1) { ?>
<li><a href="?c=services/transfer/deposits<?=$___l?>"<?php if ($content == "services/transfer/deposits") echo " class=\"active\""; ?>>Deposits</a> | <a href="?c=services/transfer/deposit_addresses<?=$___l?>"<?php if ($content == "services/transfer/deposit_addresses") echo " class=\"active\""; ?>>Deposit addresses</a></li>
<li><a href="?c=services/transfer/withdrawals<?=$___l?>"<?php if ($content == "services/transfer/withdrawals") echo " class=\"active\""; ?>>Withdrawals</a> | <a href="?c=services/transfer/withdrawal_addresses<?=$___l?>"<?php if ($content == "services/transfer/withdrawal_addresses") echo " class=\"active\""; ?>>Withdrawal addresses</a></li>
<?php } ?>
<li><a href="?c=services/transfer/api<?=$___l?>"<?php if ($content == "services/transfer/api") echo " class=\"active\""; ?>>Transfer API</a></li>
</ul>
</div>