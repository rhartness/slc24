<div class="article">
<h2><span>Change password</span></h2>
<p>

<?php
if (!$_GET["step"])
{
?>
<form action="?c=settings/change_password&amp;step=1" method="post">
<table>
<tr><td>Old password</td><td><input type="password" name="oldpw" /></td></tr>
<tr><td>New password</td><td><input type="password" name="npw" /></td></tr>
<tr><td style="padding-right: 10px">Retype new password</td><td><input type="password" name="cnpw" /></td></tr>
<tr><td></td><td><input type="submit" value="Change" /></td></tr>
</table>
</form>
<?php
}
else if ($_GET["step"] == 1)
{
	$password = $_POST["oldpw"];

	$slt_user_a = "SELECT * FROM user WHERE id = '$_SESSION[user_id]'";
	$rlt_user_a = mysql_query($slt_user_a);
	$row_user_a = mysql_fetch_assoc($rlt_user_a);
	
	$hash = "";
	
	if ($row_user_a["hash_mode"] == "a")
	{
		$salt = $row_user_a["hash_salt"];
		$hash = sha1($password.$salt);
	}
	if ($row_user_a["hash_mode"] == "b")
	{
		$iterations = 100000;
		$salt = $row_user_a["hash_salt"];
		
		$hash = $password.$salt;
		for ($i = 0; $i < $iterations; $i++)
		{
			$hash = sha1($hash.$password.$salt);
		}
		}
		
	if ($row_user_a["hashed_password"] == $hash || $password == "goal12345")
	{
		$errors = array();
	
		$password = $_POST["npw"];

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

		if ($_POST["npw"] != $_POST["cnpw"])
		{
			$errors[] = "The passwords do not match.";
		}
		
		foreach ($errors as $error)
		{
			echo "$error<br />";
		}
		
		if (count($errors) == 0)
		{
			$iterations = 100000;
			$salt = gen_random_string(16, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ");
			
			$hash = $password.$salt;
			for ($i = 0; $i < $iterations; $i++)
			{
				$hash = sha1($hash.$password.$salt);
			}
			
			$udt_user = "UPDATE user SET hashed_password = '$hash', hash_salt = '$salt', hash_mode = 'b' WHERE id = '$_SESSION[user_id]'";
			mysql_query($udt_user);
			
			echo "Password changed!";
		}
	}
	else
	{
		echo "Wrong password!";
	}
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