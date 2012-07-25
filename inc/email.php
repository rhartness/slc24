<?php

function valid_email($email)
{
	return ereg(
		"^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$",
		$email
	);
}

?>
