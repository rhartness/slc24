<?php
session_start();
date_default_timezone_set("GMT");

include "inc/conn.php";
include "inc/account.php";
include "inc/format.php";
include "inc/language.php";

include "snips/prepost.php";

language_file("general");

if (!$access && $login_necessary) {
	$_PRE["menu"] = "home";
	$_PRE["title"] = $_LANG[$l]["g_slc24_welcome"];
	$_PRE["page_title"] = $_LANG[$l]["g_log_in_please"];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$_PRE["page_title"]?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="/favicon.ico" />
<script type="text/javascript" src="js/cufon-yui.js"></script>
<script type="text/javascript" src="js/arial.js"></script>
<script type="text/javascript" src="js/cuf_run.js"></script>
</head>
<body>
<div class="main">
<?php include "snips/topmenu.php"; ?>
  <div class="header">
    <div class="header_resize">
      <div class="logo">
        <h1><?=$_PRE["title"]?></h1>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="content_resize">
      <div class="mainbar">
<?php if ($access) { include "content/$content"."_cont.php"; } elseif ($login_necessary) { ?>
        <div class="article">
		<p>
		<h2><?=$_LANG[$l]["g_log_in_please_header"]?></h2>
		<?=$_LANG[$l]["g_log_in_please_1"]?>
		<h3><?=$_LANG[$l]["g_log_in_please_why"]?></h3>
		<?=$_LANG[$l]["g_log_in_please_2"]?>
		<h3><?=$_LANG[$l]["g_log_in_please_dont_have"]?></h3>
		<?=lang_temp("?c=sign_up<?=$___l?>", $_LANG[$l]["g_log_in_please_3"])?>
		</p>
		</div>
<?php } ?>
      </div>
      <div class="sidebar">
<?php if ($_PRE["search"]) { ?>
        <div class="searchform">
          <form id="formsearch" name="formsearch" method="post" action="#">
            <input name="button_search" src="images/search_btn.gif" class="button_search" type="image" style="position: relative; top: -5px;" />
            <input name="editbox_search" class="editbox_search" id="editbox_search" maxlength="80" value="<?=$_LANG[$l]["g_search"]?>" type="text" onfocus="if (this.value == '<?=$_LANG[$l]["g_search"]?>') this.value = '';" onblur="if (this.value == '') this.value = '<?=$_LANG[$l]["g_search"]?>';" />
          </form>
          <div class="clr"></div>
        </div>
<?php } ?>
<?php if ($_PRE["account"] || (!$access && $login_necessary)) { ?>
        <div class="gadget">
          <h2 class="star"><?=$_LANG[$l]["g_you_account"]?></h2>
<?php if ($_SESSION["li"] != 1) { ?>
		  <form action="https://slc24.com/?c=log_in&m=<?=$_PRE["menu"]?>&r=<?=urlencode($_SERVER["REQUEST_URI"])?>&amp;g=<?=urlencode(serialize($_GET))?>&rn=<?=urlencode($_PRE["page_title"])?>&h=<?=urlencode(md5($_PRE["menu"].$_SERVER["REQUEST_URI"].serialize($_GET).$_PRE["page_title"]."abcsechash"))?><?=$___l?>" method="post">
			<p><input name="email" onfocus="if (this.value == '<?=$_LANG[$l]["g_email"]?>') this.value = '';" onblur="if (this.value == '') this.value = '<?=$_LANG[$l]["g_email"]?>';" /><br /></p>
			<p><input type="password" name="password" onfocus="onPWfucous()" onblur="onPWblur()" /><br /></p>
			<script type="text/javascript">
			function check_form()  {
				if (document.getElementsByName('password')[0].value == "") {
					document.getElementsByName('password')[0].style.backgroundImage = "url('images/pw/<?=$l?>.jpg')";
				}
				
				if (document.getElementsByName('email')[0].value == "") {
					document.getElementsByName('email')[0].value = "<?=$_LANG[$l]["g_email"]?>";
				}
			}
			
			setTimeout('check_form()', 100);
			
			function onPWfucous() {
				document.getElementsByName('password')[0].style.backgroundImage = "";
			}
			
			function onPWblur() {
				if (document.getElementsByName('password')[0].value == "") {
					document.getElementsByName('password')[0].style.backgroundImage = "url('images/pw/<?=$l?>.jpg')";
				}
			}
			</script>
			<p><input type="submit" value="<?=$_LANG[$l]["g_log_in"]?>" /><br /></p>
		  </form>
          <ul class="sb_menu">
            <li><a href="?c=sign_up<?=$___l?>" rel="nofollow"><?=$_LANG[$l]["g_sign_up"]?></a></li>
          </ul>
<?php } else { get_connection(); ?>
          <ul class="sb_menu">
            <li><?=lang_temp(htmlentities($_SESSION["user_email"]), $_LANG[$l]["g_logged_in_as"])?></li>
			<li><a href="?c=balance<?=$___l?>" <?php if ($content == "balance") echo " class=\"active\""; ?>><?=$_LANG[$l]["g_balance"]?></a></li>
            <li style="font-size: 0.9em"><?=nice_format(get_balance($_SESSION["user_id"], "SLC"), false, 0, 4)?> <?=$_LANG[$l]["g_solidcoins"]?><br /><a href="?c=balance/deposit_slc<?=$___l?>" <?php if ($content == "balance/deposit_slc") echo " class=\"active\""; ?>><?=$_LANG[$l]["g_deposit"]?></a> | <a href="?c=balance/withdraw_slc<?=$___l?>" <?php if ($content == "balance/withdraw_slc") echo " class=\"active\""; ?>><?=$_LANG[$l]["g_withdraw"]?></a></li>
            <li style="font-size: 0.9em"><?=nice_format(get_balance($_SESSION["user_id"], "BTC"), false, 0, 8)?> <?=$_LANG[$l]["g_bitcoins"]?><br /><a href="?c=balance/deposit_btc<?=$___l?>" <?php if ($content == "balance/deposit_btc") echo " class=\"active\""; ?>><?=$_LANG[$l]["g_deposit"]?></a> | <a href="?c=balance/withdraw_btc<?=$___l?>" <?php if ($content == "balance/withdraw_btc") echo " class=\"active\""; ?>><?=$_LANG[$l]["g_withdraw"]?></a></li>
            <li><a href="?c=account/log_out<?=$___l?>" rel="nofollow"><?=$_LANG[$l]["g_log_out"]?></a></li>
          </ul>
<?php } ?>
        </div>
<?php } ?>
<?php if (isset($_PRE["sidemenu_file"])) include "content/_menus/".$_PRE["sidemenu_file"]; ?>
<?php if ($_PRE["links"]) { ?>
        <div class="gadget">
          <h2 class="star"><span><?=$_LANG[$l]["g_links"]?></span></h2>
          <ul class="ex_menu">
		    <?php 
			foreach ($_LANG[$l]["links"] as $link) {
				echo "<li><a href=\"$link[1]\">$link[0]</a><br />";
				echo "$link[2]</li>";
			}
			?>
          </ul>
        </div>
<?php } ?>
<?php if (false && (!isset($_POST) || count($_POST) == 0) && (count($_GET) == 0 || (count($_GET) == 1 && isset($_GET["l"])))) { ?>
        <div class="gadget">
          <h2 class="star"><span>Language</span></h2>
          <select onchange="langchange(this.value)"><option value="">English</option><option value="de"<?php if($l == "de") echo " selected=\"selected\""; ?>>Deutsch</option><option value="ru"<?php if($l == "ru") echo " selected=\"selected\""; ?>>Русский</option></select>
		  <script type="text/javascript">
		  function langchange(lang) { if (lang != "") document.location.href = "http://localhost/slc24_v2/?c=<?=$content?>&l="+lang; else document.location.href = "http://localhost/slc24_v2/?c=<?=$content?>" }
		  </script>
        </div>
<?php } ?>
      </div>
      <div class="clr"></div>
    </div>
  </div>
<?php if ($_PRE["info"]) { ?>
  <div class="fbg">
    <div class="fbg_resize">
      <div class="col c1">
        <h2>Solidcoin24</h2>
        <img src="images/pix1.jpg" width="58" height="58" alt="" /> <img src="images/pix2.jpg" width="58" height="58" alt="" /> <img src="images/pix3.jpg" width="58" height="58" alt="" /> <img src="images/pix4.jpg" width="58" height="58" alt="" /> <img src="images/pix5.jpg" width="58" height="58" alt="" /> <img src="images/pix6.jpg" width="58" height="58" alt="" /> </div>
      <div class="col c2">
        <h2><?=$_LANG[$l]["g_our_services"]?></h2>
          <p><?=$_LANG[$l]["g_our_services_info"]?></p>
		  <p><a href="?c=exchange<?=$___l?>"><?=$_LANG[$l]["g_os_exchange"]?></a> | <a href="?c=services/transfer<?=$___l?>"><?=$_LANG[$l]["g_os_transfer"]?></a></p>
      </div>
      <div class="col c3">
        <h2><?=$_LANG[$l]["g_about_us"]?></h2>
        <img src="images/white.jpg" width="66" height="66" alt="" />
        <p><?=$_LANG[$l]["g_about_us_info"]?></p>
        <p><a href="?c=about<?=$___l?>"><?=$_LANG[$l]["g_read_more"]?></a></p>
	  </div>
      <div class="clr"></div>
    </div>
  </div>
<?php } ?>
  <div class="footer">
    <div class="footer_resize">
      <p class="lf"><?=lang_temp("<a href=\"http://www.bluewebtemplates.com/\">Website Templates</a>", $_LANG[$l]["g_footer"])?></p>
<?php include "snips/bottommenu.php"; ?>
      <div class="clr"></div>
    </div>
  </div>
</div>
<?php
if (!isset($_SESSION["time_offset"]) || $_SESSION["time_offset_set_last"] < time() - 3600) {
?>

<script type="text/javascript" src="/res/jscr/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var visitortime = new Date();
	$.ajax({
		type: "GET",
		url: "https://slc24.com/timezone.php",
		data: 'time_offset='+ visitortime.getTimezoneOffset(),
		success: function() {
		}
	});
});
</script>
<?php
}
?>
</body>
</html>
