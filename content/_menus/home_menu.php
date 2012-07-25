<?php
language_file("home_menu");
?>
<div class="gadget">
<h2 class="star"><?=$_LANG[$l]["hm_header"]?></h2>
<ul class="sb_menu">
<li><a href="<?=$__l?>"<?php if ($content == "index") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_home"]?></a></li>
<?php if ($_SESSION["li"] == 1) { ?>
<li><a href="?c=balance<?=$___l?>"<?php if ($content == "balance") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_balance"]?></a></li>
<li><a href="?c=balance/deposit_slc<?=$___l?>"<?php if ($content == "balance/deposit_slc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_deposit_solidcoins"]?></a> | <a href="?c=balance/deposit_btc<?=$___l?>"<?php if ($content == "balance/deposit_btc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_deposit_bitcoins"]?></a> | <a href="?c=balance/deposit_nmc<?=$___l?>"<?php if ($content == "balance/deposit_nmc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_deposit_namecoins"]?></a></li>
<li><a href="?c=balance/withdraw_slc<?=$___l?>"<?php if ($content == "balance/withdraw_slc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_withdraw_solidcoins"]?></a> | <a href="?c=balance/withdraw_btc<?=$___l?>"<?php if ($content == "balance/withdraw_btc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_withdraw_bitcoins"]?></a> | <a href="?c=balance/withdraw_nmc<?=$___l?>"<?php if ($content == "balance/withdraw_nmc") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_withdraw_namecoins"]?></a></li>
<li><a href="?c=internal_transfer<?=$___l?>"<?php if ($content == "internal_transfer") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_internal_transfer"]?></a></li>
<li><a href="?c=settings<?=$___l?>"<?php if ($content == "settings") echo " class=\"active\""; ?>><?=$_LANG[$l]["hm_settings"]?></a></li>
<?php } ?>
</ul>
</div>