<div class="article">
<h2><span>Log in</span></h2>
<p>

<?php
if (isset($_POST["email"]))
{
	if (count($errors) == 0)
	{	
		if(!isset($_GET["r"]) || md5($_GET["m"].$_GET["r"].$_GET["g"].$_GET["rn"]."abcsechash") != $_GET["h"])
		{		
			if($row_user["lang"] != "en")
				$sub = "$row_user[lang].";
			
			$l_url = "?";
			$l_urljs = "?";
			$l_site_name = "My account";
		}
		else
		{
			if(!$_GET["gvars"])
			{
				$l_url = "$_GET[r]";
				$l_urljs = "$_GET[r]";
			}
			else
			{
				$l_url = "$_GET[r]&amp;";
				$l_urljs = "$_GET[r]&";
				
				$first = true;
				foreach(unserialize($_GET["g"]) as $vname => $vvalue)
				{
					if($first)
						$first = false;
					else
					{
						$l_url .= "&amp;";
						$l_urljs .= "&";
					}
					
					$l_url .= "$vname=".urlencode($vvalue);
					$l_urljs .= "$vname=".urlencode($vvalue);
				}
			}
			
			$l_site_name = $_GET["rn"];
		}
?>
<?php $l_time = 2; ?>
Your are being redirected to <a href="<?=$l_url?>"><?=htmlentities($l_site_name, ENT_COMPAT, "UTF-8")?></a> in <span id="counter"><?=$l_time?></span>.<br />
<script type="text/javascript" src="res/jscr/redirect.js"></script>
<script type="text/javascript">
redirect("<?=$l_urljs?>", <?=$l_time?>);
</script>
<?php
	}
	else
	{
		foreach ($errors as $error)
		{
			echo "$error<br />";
		}
?>

<form action="https://slc24.com/?c=log_in<?php if(isset($_GET["g"])) echo "&amp;m=".urlencode($_GET["m"]); if(isset($_GET["r"])) { echo "&amp;r=".urlencode($_GET["r"]); if(isset($_GET["g"])) echo "&amp;g=".urlencode($_GET["g"]); if(isset($_GET["rn"])) echo "&amp;rn=".urlencode($_GET["rn"]); if(isset($_GET["h"])) echo "&amp;h=".urlencode($_GET["h"]); } ?>" method="post">
<table>
<tr><td>Email</td><td><input name="email" value="<?=htmlentities($_POST["email"], ENT_COMPAT, "UTF-8")?>" /></td></td>
<tr><td>Password</td><td><input type="password" name="password" /></td></td>
<tr><td></td><td><input type="submit" name="submit" value="Log in" /></td></td>
</table>
</form>
<br />
Don't have an account yet? <a href="?c=sign_up">Sign up</a>! It's free.<br />
Forgot your password? <a href="?log_in/forgot_password">Request a new one</a>!

<?php
	}
}
else
{
	if ($activated)
	{
		echo "You've successfully activated your account. You can log in now.<br />";
	}
?>

<form action="https://slc24.com/?c=log_in<?php if(isset($_GET["g"])) echo "&amp;m=".urlencode($_GET["m"]); if(isset($_GET["r"])) { echo "&amp;r=".urlencode($_GET["r"]); if(isset($_GET["g"])) echo "&amp;g=".urlencode($_GET["g"]); if(isset($_GET["rn"])) echo "&amp;rn=".urlencode($_GET["rn"]); if(isset($_GET["h"])) echo "&amp;h=".urlencode($_GET["h"]); } ?>" method="post">
<table>
<tr><td>Email</td><td><input name="email" value="<?=htmlspecialchars($email, ENT_QUOTES)?>" /></td></td>
<tr><td>Password</td><td><input type="password" name="password" /></td></td>
<tr><td></td><td><input type="submit" name="submit" value="Log in" /></td></td>
</table>
</form>
<br />
Don't have an account yet? <a href="?c=sign_up">Sign up</a>! It's free.<br />
Forgot your password? <a href="?log_in/forgot_password">Request a new one</a>!

<?php
}
?>

</p>
</div>