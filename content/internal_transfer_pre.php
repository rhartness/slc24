<?php
get_connection();

$_PRE["info"] = false;
$_PRE["search"] = false;
$_PRE["menu"] = "home";
$_PRE["links"] = false;
$_PRE["account"] = true;
$_PRE["page_title"] = "Internal transfer - Solidcoin24";
$_PRE["title"] = "Welcome | Solidcoin24";
$_PRE["sidemenu_file"] = "home_menu.php";

$_PRE["REQUEST_URI"] = "/?c=internal_transfer";

include_once $_SITE["root"]."inc/db_locks.php";

?>