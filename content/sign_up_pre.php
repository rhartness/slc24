<?php
$_PRE["info"] = true;
$_PRE["menu"] = "home";
$_PRE["links"] = true;
$_PRE["page_title"] = "Sign up | Solidcoin24";
$_PRE["title"] = "Sign up | Solidcoin24";
$_PRE["sidemenu_file"] = "home_menu.php";

$_PRE["REQUEST_URI"] = "/?c=sign_up";
		
if ($_GET["step"] == 2)
{
	include_once $_SITE["root"]."inc/email.php";
	include_once $_SITE["root"]."inc/send_mail.php";
	require $_SITE["root"]."inc/recaptchalib/recaptchalib.php";

	get_connection();
	
	$_PRE["REQUEST_URI"] = "/?c=sign_up&step=2";

	$errors = array();

	$email = mysql_real_escape_string($_POST["email"], $db);

	$captcha = false;

	$resp = recaptcha_check_answer ("6LdIzsUSAAAAAHCUVhc3O0Fz4Pd0e00S8E38sFLH",
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	$captcha = $resp->is_valid;

	if ($captcha) // TODO: CAPTCHA
	{
		$password = mysql_real_escape_string($_POST["password"], $db);

		$alpha_miniscule = "abcdefghijklmnopqrstuvwxyz";
		$alpha_capital = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$alpha = $alpha_miniscule.$alpha_capital;
		$numeric = "1234567890";

		$contains_alpha = false;
		for ($i = 0; $i < strlen($alpha); $i++)
		{
			for ($j = 0; $j < strlen($password); $j++)
			{
				if ($alpha[$i] == $password[$j])
				{
					$contains_alpha = true;
					break;
				}
			}
			if ($contains_alpha)
				break;
		}

		$contains_numeric = false;
		for ($i = 0; $i < strlen($numeric); $i++)
		{
			for ($j = 0; $j < strlen($password); $j++)
			{
				if ($numeric[$i] == $password[$j])
				{
					$contains_numeric = true;
					break;
				}
			}
			if ($contains_numeric)
				break;
		}

		if (!$contains_alpha)
		{
			$errors[] = "Your password must contain at least one alphabetical character.";
		}

		if (!$contains_numeric)
		{
			$errors[] = "Your password must contain at least one number.";
		}

		if (strlen($password) < 8)
		{
			$errors[] = "Your passwort must be at least 8 characters long.";
		}

		if (!valid_email($email))
		{
			$errors[] = "Please enter a valid email address.";
		}

		if ($_POST["tos"] != "1")
		{
			$errors[] = "Please accept the Terms of Service";
		}

		$non_accaptable_addresses = array("trash-mail", "sofort-mail", "guerrillamailblock", "guerrillamail", "bsnow", "10minutemail",
		"spambog", "discardmail", "dontsendmespam", "jetable", "mailinator", "dodgit", "spam", "trash2009", "e4ward", "kasmail", 
		"spamgourmet", "spambog", "spoofmail", "hidemail", "emailto", "wegwerfadresse", "nervmich", "dumpmail", "spamoff", "twinmail",
		"mailmetrash", "suremail", "mailinator", "yopmail");

		foreach($non_accaptable_addresses as $address)
		{
			if(stripos($email, $address.".") !== false)
			{
				$errors[] = "Sorry but we cannot accept email addresses from the domain $address.* because we might have to inform you about important issues regarding your account in some cases.";
				break;
			}
		}

		if ($email != $_POST["emailc"])
		{
			$errors[] = "Please make sure your email address is correct.";
		}

		if ($_POST["password"] != $_POST["passwordc"])
		{
			$errors[] = "Please make sure your password is correct.";
		}

		$slt_user_a = "SELECT * FROM user WHERE email = '$email'";
		$rlt_user_a = mysql_query($slt_user_a);

		if (mysql_num_rows($rlt_user_a) != 0)
		{
			$errors[] = "A user with this email address already exists.";
		}
	}
	else
	{
		$errors[] = "The captcha has not been solved.";
	}
}

?>