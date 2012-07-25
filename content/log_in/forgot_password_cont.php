<?php
include_once $_SITE["root"]."inc/email.php";
include_once $_SITE["root"]."inc/send_mail.php";
require $_SITE["root"]."inc/recaptchalib/recaptchalib.php";
?>

<div class="article">
<h2><span>Request new password</span></h2>
<p>

<?php
if (!$_GET["step"])
{
?>
<form action="?c=log_in/forgot_password&amp;step=1" method="post">

<h3>Email address</h3>

<input name="email_" /><br /><br />

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
  <br />

  
<input type="submit" value="Request password" />
</form>
<?php
}
else if ($_GET["step"] == 1)
{
	$errors = array();

	$resp = recaptcha_check_answer ("6LdIzsUSAAAAAHCUVhc3O0Fz4Pd0e00S8E38sFLH",
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
	
	if (!$resp->is_valid)
	{
		$errors[] = "Wrong captcha!";
	}
	else
	{
		if (!valid_email($_POST["email_"]))
		{
			$errors[] = "Not a valid email address!";
		}
		else
		{
			$email = mysql_real_escape_string($_POST["email_"], $db);

			$slt_user_a = "SELECT * FROM user WHERE email = '$email'";
			$rlt_user_a = mysql_query($slt_user_a);

			if (mysql_num_rows($rlt_user_a) == 1)
			{
				$row_user_a = mysql_fetch_assoc($rlt_user_a);
				$hash = "";
				
				if ($row_user_a["actu"] == "")
				{
				}
				else
				{
					$errors[] = "This account is not activated.";
				}
			}
			else
			{
				$errors[] = "This email address is not associated with a user account.";
			}
		}
	}
	
	if (count($errors) == 0)
	{
		$hash_mode = "b";

		$password = gen_random_string(8, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ");
		$iterations = 100000;
		$salt = gen_random_string(16, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ");
		
		$hash = $password.$salt;
		for ($i = 0; $i < $iterations; $i++)
		{
			$hash = sha1($hash.$password.$salt);
		}
		
		$udt_user_a = "UPDATE user SET hashed_password = '$hash', hash_salt = '$salt', hash_mode = '$hash_mode' WHERE id = '$row_user_a[id]'";
		mysql_query($udt_user_a);
	
		send_mail("Password request", "Dear slc24 user,<br />\r\n<br />\r\nYour new password is $password.<br />\r\n<br />\r\nRegards,<br />\r\nslc24", $email);
	
		echo "New password has been sent to $email.";
	}
	else
	{	
		foreach ($errors as $error)
		{
			echo $error."<br />";
		}
	}
}
?>

</p>
</div>

<?php

function gen_random_string($length, $characters) {
    $string = "";    

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

    return $string;
}

?>