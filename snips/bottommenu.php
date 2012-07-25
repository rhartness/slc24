      <ul class="fmenu">
        <li<?php if ($_PRE["menu"] == "home") echo " class=\"active\""; ?>><a href="<?=$__l?>"><?=$_LANG[$l]["g_menu_home"]?></a></li>
        <li<?php if ($_PRE["menu"] == "exchange") echo " class=\"active\""; ?>><a href="?c=exchange<?=$___l?>"><?=$_LANG[$l]["g_menu_exchange"]?></a></li>
        <li<?php if ($_PRE["menu"] == "services") echo " class=\"active\""; ?>><a href="?c=services<?=$___l?>"><?=$_LANG[$l]["g_menu_services"]?></a></li>
        <li<?php if ($_PRE["menu"] == "support") echo " class=\"active\""; ?>><a href="?c=support<?=$___l?>"><?=$_LANG[$l]["g_menu_support"]?></a></li>
        <li<?php if ($_PRE["menu"] == "about") echo " class=\"active\""; ?>><a href="?c=about<?=$___l?>"><?=$_LANG[$l]["g_menu_about"]?></a></li>
        <li<?php if ($_PRE["menu"] == "legal") echo " class=\"active\""; ?>><a href="?c=legal<?=$___l?>" rel="nofollow"><?=$_LANG[$l]["g_menu_legal"]?></a></li>
      </ul>