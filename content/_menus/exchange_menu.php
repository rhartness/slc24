<?php
language_file("exchange_menu");
?>
<div class="gadget">
<h2 class="star"><?=$_LANG[$l]["em_header"]?></h2>
<ul class="sb_menu">
<li><a href="?c=exchange<?=$___l?>"<?php if ($content == "exchange" && $currency == "BTC") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_trade_bitcoins"]?></a> | <a href="?c=exchange&amp;u=nmc<?=$___l?>"<?php if ($content == "exchange" && $currency == "NMC") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_trade_namecoins"]?></a></li>
<?php if ($_SESSION["li"] == 1) { ?>
<li><a href="?c=exchange/orders<?=$___l?>"<?php if ($content == "exchange/orders" && $active == "yes") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_orders_open"]?></a> | <a href="?c=exchange/orders&amp;active=no<?=$___l?>"<?php if ($content == "exchange/orders" && $active == "no") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_orders_finished"]?></a></li>
<li><a href="?c=exchange/my_trades<?=$___l?>"<?php if ($content == "exchange/my_trades") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_my_trades"]?></a></li>
<?php } ?>
<li><a href="?c=exchange/api<?=$___l?>"<?php if ($content == "exchange/api") echo " class=\"active\""; ?>><?=$_LANG[$l]["em_trading_api"]?></a></li>
</ul>
</div>