<div class="article">
<h2><span>Sign up</span></h2>
<p>
<?php if (!isset($_GET["step"])) { ?>
<form action="?c=sign_up&step=2" method="post">
<table>
<tr><td>Email</td><td><input name="email" /></td></tr>
<tr><td>Email (conf.)</td><td><input name="emailc" /></td></tr>
<tr><td>Password</td><td><input type="password" name="password" /></td></tr>
<tr><td>Password (conf.)</td><td><input type="password" name="passwordc" /></td></tr>
<tr><td>ToS</td><td><input type="checkbox" name="tos" value="1" class="checkbox" /> I accept the <a href="?c=tos" target="_blank">Terms of Service</a></td></tr>
<tr><td>Captcha</td><td>
<script type="text/javascript">
var RecaptchaOptions = {
    theme : 'clean',
    lang : '<?=$l?>'
 };
</script>
<script type="text/javascript"
     src="https://www.google.com/recaptcha/api/challenge?k=6LdIzsUSAAAAAErZKLYKbn91Fd63vnHbzirtcluD">
  </script>
  <noscript>
     <iframe src="https://www.google.com/recaptcha/api/noscript?k=6LdIzsUSAAAAAErZKLYKbn91Fd63vnHbzirtcluD"
         height="300" width="500" frameborder="0"></iframe><br>
     <textarea name="recaptcha_challenge_field" rows="3" cols="40">
     </textarea>
     <input type="hidden" name="recaptcha_response_field"
         value="manual_challenge">
  </noscript>
  </td></tr>
<tr><td></td><td><input type="submit" name="submit" value="Sign up" /></td></tr>
</table>
</form>
<?php } else {
	if (count($errors) == 0) {
		$hash_mode = "b";

		$iterations = 100000;
		$salt = gen_random_string(16, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ");
		
		$hash = $password.$salt;
		for ($i = 0; $i < $iterations; $i++)
		{
			$hash = sha1($hash.$password.$salt);
		}
		
		$new_try = true;
		$idstr = "";
		while ($new_try)
		{
			$idstr = gen_random_string(16, "0123456789ABCDEFGHJIKLMNOPQRSTUVWXYZ");
		
			$slt_user_a = "SELECT EXISTS (SELECT * FROM user WHERE id_string = '$idstr') AS exist";
			$rlt_user_a = mysql_query($slt_user_a);
			$row_user_a = mysql_fetch_assoc($rlt_user_a);
			
			$new_try = false;
			if ($row_user_a["exist"] == 1)
				$new_try = true;
		}
		
		$actu = gen_random_string(24, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ");
		
		send_mail("Sign up at Solidcoin24", "Welcome to Solidcoin24!<br />\n<br />\nGo to the following page to verify your account:<br />\n<a href=\"http://slc24.com/?c=log_in&email=$email&actu=$actu\">http://slc24.com/?c=log_in&email=$email&actu=$actu</a><br />\n<br />\nRegards,<br />\nYour Solidcoin24 team", $email);
		
		$ins_user_a = "INSERT INTO user (email, actu, hashed_password, hash_salt, hash_mode, register_time, id_string) VALUES ('$email', '$actu', '$hash', '$salt', '$hash_mode', NOW(), '$idstr')";
		$rlt_user_a = mysql_query($ins_user_a);
		
		$uid = mysql_insert_id($db);
		
		$slt_user_b = "SELECT COUNT(*) AS number FROM user";
		$rlt_user_b = mysql_query($slt_user_b);
		$row_user_b = mysql_fetch_assoc($rlt_user_b);
		
		if ($row_user_b["number"] > 55)
		{
			$ins_account_a = "INSERT INTO account (user, currency, amount) VALUES ('$uid', 'SLC', '0')";
			mysql_query($ins_account_a);
		}
		elseif ($row_user_b["number"] > 5)
		{
			$ins_account_a = "INSERT INTO account (user, currency, amount) VALUES ('$uid', 'SLC', '1')";
			mysql_query($ins_account_a);
			
			$ins_transaction_a = "INSERT INTO transaction (type, direction, trade_order, user, filing_time, currency, amount, balance, total_fee, fee_model, finished, info) ".
				"VALUES ('intern', 'in', '0', '$uid', NOW(), 'SLC', '1', '1', '0', 'none', 'no', 'bonus')";
			mysql_query($ins_transaction_a);
		}
		else
		{
			$ins_account_a = "INSERT INTO account (user, currency, amount) VALUES ('$uid', 'SLC', '5')";
			mysql_query($ins_account_a);
			
			$ins_transaction_a = "INSERT INTO transaction (type, direction, trade_order, user, filing_time, currency, amount, balance, total_fee, fee_model, finished, info) ".
				"VALUES ('intern', 'in', '0', '$uid', NOW(), 'SLC', '5', '5', '0', 'none', 'no', 'bonus')";
			mysql_query($ins_transaction_a);
		}
		
		$ins_account_b = "INSERT INTO account (user, currency, amount) VALUES ('$uid', 'BTC', '0')";
		mysql_query($ins_account_b);
		
		$ins_account_b = "INSERT INTO account (user, currency, amount) VALUES ('$uid', 'NMC', '0')";
		mysql_query($ins_account_b);
?>
Congratulations!<br />
You've successfully created an account at Solidcoin24.<br />
An email has been sent to <?=htmlentities($_POST["email"])?>. Please click on the link provided in it to activate your account.
<?php } else {
		foreach ($errors as $error) {
			echo $error."<br />";
		}
		echo "<br />";
?>
<form action="?c=sign_up&step=2" method="post">
<table>
<tr><td>Email</td><td><input name="email" value="<?=htmlentities($_POST["email"])?>" /></td></tr>
<tr><td>Email (conf.)</td><td><input name="emailc" /></td></tr>
<tr><td>Password</td><td><input type="password" name="password" /></td></tr>
<tr><td>Password (conf.)</td><td><input type="password" name="passwordc" /></td></tr>
<tr><td>ToS</td><td><input type="checkbox" name="tos" value="1" class="checkbox" <?php if ($_POST["tos"] == 1) echo "checked=\"checked\" "; ?>/> I accept the <a href="?c=tos" target="_blank">Terms of Service</a></td></tr>
<tr><td>Captcha</td><td>
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
  </td></tr>
<tr><td></td><td><input type="submit" name="submit" value="Sign up" /></td></tr>
</table>
</form>
<?php }
} 

function gen_random_string($length, $characters) {
    $string = "";    

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

    return $string;
}

?>
</p>
</div>