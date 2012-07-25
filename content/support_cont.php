<?php
include_once $_SITE["root"]."inc/email.php";
include_once $_SITE["root"]."inc/send_mail.php";
require $_SITE["root"]."inc/recaptchalib/recaptchalib.php";

language_file("support");
?>

<div class="article">
<h2><span><?=$_LANG[$l]["s_header1"]?></span></h2>
<p>

<?php
if (!$_GET["step"])
{
?>
<form action="?c=support&amp;step=1" method="post">

<h3><?=$_LANG[$l]["s_your_issue"]?></h3>
<select name="issue_type">
  <option value="feature request"><?=$_LANG[$l]["s_issue_type1"]?></option>
  <option value="site partnership"><?=$_LANG[$l]["s_issue_type2"]?></option>
  <option value="advertisement"><?=$_LANG[$l]["s_issue_type3"]?></option>
  <option value="transfer API"><?=$_LANG[$l]["s_issue_type4"]?></option>
  <option value="sign up"><?=$_LANG[$l]["s_issue_type5"]?></option>
  <option value="log in"><?=$_LANG[$l]["s_issue_type6"]?></option>
  <option value="withdrawing"><?=$_LANG[$l]["s_issue_type7"]?></option>
  <option value="depositing"><?=$_LANG[$l]["s_issue_type8"]?></option>
  <option value="bug"><?=$_LANG[$l]["s_issue_type9"]?></option>
  <option selected="selected" value="other"><?=$_LANG[$l]["s_issue_type10"]?></option>
</select>

<h3><?=$_LANG[$l]["s_message"]?></h3>
<textarea name="issue" style="width: 500px; height: 150px; font-family: Verdana, Arial, sanf-serif; font-size: 14px;"></textarea>

<h3><?=$_LANG[$l]["s_email"]?></h3>
<input name="email_" value="<?=$_SESSION["user_email"]?>" /><br /><br />
<?php
if ($_SESSION["li"] != 1)
{
?>
<script type="text/javascript">
var RecaptchaOptions = {
    theme : 'clean',
    lang : '<?=$l?>'
 };
</script>
<script type="text/javascript"
     src="https://www.google.com/recaptcha/api/challenge?k=YOUR_ID">
  </script>
  <noscript>
     <iframe src="https://www.google.com/recaptcha/api/noscript?k=YOUR_ID"
         height="300" width="500" frameborder="0"></iframe><br>
     <textarea name="recaptcha_challenge_field" rows="3" cols="40">
     </textarea>
     <input type="hidden" name="recaptcha_response_field"
         value="manual_challenge">
  </noscript>
  <br />
<?php
}
?> 
<input type="submit" value="<?=$_LANG[$l]["s_button_send"]?>" />
</form>
<?php
}
else if ($_GET["step"] == 1)
{
	$errors = array();

	$resp = recaptcha_check_answer ("YOUR_ID",
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
	
	if ($_SESSION["li"] != 1 && !$resp->is_valid)
	{
		$errors[] = $_LANG[$l]["s_err_wrong_captcha"];
	}
	
	if (!valid_email($_POST["email_"]))
	{
		$errors[] = $_LANG[$l]["s_err_email"];
	}
	
	if (count($errors) == 0)
	{		
		send_mail("Help request (".$_POST["issue_type"].")", str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br />", $_POST["issue"]), "admin@solidcoin24.com", $_POST["email"], $_POST["email_"]);
	
		echo $_LANG[$l]["s_err_succ"];
	}
	else
	{	
	foreach ($errors as $error)
	{
		echo $error."<br />";
	}
?>
<br />
<form action="?c=support&amp;step=1" method="post">

<h3><?=$_LANG[$l]["s_your_issue"]?></h3>
<select name="issue_type">
  <option value="feature request"><?=$_LANG[$l]["s_issue_type1"]?></option>
  <option value="site partnership"><?=$_LANG[$l]["s_issue_type2"]?></option>
  <option value="advertisement"><?=$_LANG[$l]["s_issue_type3"]?></option>
  <option value="transfer API"><?=$_LANG[$l]["s_issue_type4"]?></option>
  <option value="sign up"><?=$_LANG[$l]["s_issue_type5"]?></option>
  <option value="log in"><?=$_LANG[$l]["s_issue_type6"]?></option>
  <option value="withdrawing"><?=$_LANG[$l]["s_issue_type7"]?></option>
  <option value="depositing"><?=$_LANG[$l]["s_issue_type8"]?></option>
  <option value="bug"><?=$_LANG[$l]["s_issue_type9"]?></option>
  <option selected="selected" value="other"><?=$_LANG[$l]["s_issue_type10"]?></option>
</select>

<h3><?=$_LANG[$l]["s_message"]?></h3>
<textarea name="issue" style="width: 500px; height: 150px; font-family: Verdana, Arial, sanf-serif; font-size: 14px;"><?=htmlentities($_POST["issue"])?></textarea>

<h3><?=$_LANG[$l]["s_email"]?></h3>
<input name="email_" value="<?=$_SESSION["user_email"]?>" /><br /><br />
<?php
if ($_SESSION["li"] != 1)
{
?>
<script type="text/javascript">
var RecaptchaOptions = {
    theme : 'clean',
    lang : '<?=$l?>'
 };
</script>
<script type="text/javascript"
     src="https://www.google.com/recaptcha/api/challenge?k=YOUR_ID">
  </script>
  <noscript>
     <iframe src="https://www.google.com/recaptcha/api/noscript?k=YOUR_ID"
         height="300" width="500" frameborder="0"></iframe><br>
     <textarea name="recaptcha_challenge_field" rows="3" cols="40">
     </textarea>
     <input type="hidden" name="recaptcha_response_field"
         value="manual_challenge">
  </noscript>
  <br />
<?php
}
?> 
<input type="submit" value="<?=$_LANG[$l]["s_button_send"]?>" />
</form>
<?php
	}
}
?>

</p>
</div>