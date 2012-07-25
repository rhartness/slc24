<div class="article">
<h2><span>Log out</span></h2>
<p>

<?php
if ($l == "en") {
	$l_url = "?";
	$l_urljs = "?";
	$l_site_name = "Home";
} else {
	$l_url = "?l=$l";
	$l_urljs = "?l=$l";
	$l_site_name = "Home";
}
?>

<?php $l_time = 2; ?>
Your are being redirected to <a href="<?=$l_url?>"><?=htmlentities($l_site_name, ENT_COMPAT, "UTF-8")?></a> in <span id="counter"><?=$l_time?></span>.<br />
<script type="text/javascript" src="res/jscr/redirect.js"></script>
<script type="text/javascript">
redirect("<?=$l_urljs?>", <?=$l_time?>);
</script>

</p>
</div>