<?php

$alphanum = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

function check_slc_address($address) {
	global $alphanum;
	
	for ($i = 0; $i < strlen($address); $i++) {
		$ok = false;
		for ($j = 0; $j < strlen($alphanum); $j++) {
			if ($address[$i] == $alphanum[$j]) {
				$ok = true;
				break;
			}
		}
		if (!$ok) return false;
	}

	return $address[0] == "s" && strlen($address) > 20 && strlen($address) < 50;
}

function check_btc_address($address) {
	global $alphanum;
	
	for ($i = 0; $i < strlen($address); $i++) {
		$ok = false;
		for ($j = 0; $j < strlen($alphanum); $j++) {
			if ($address[$i] == $alphanum[$j]) {
				$ok = true;
				break;
			}
		}
		if (!$ok) return false;
	}

	return $address[0] == "1";
}

function check_nmc_address($address) {
	global $alphanum;
	
	for ($i = 0; $i < strlen($address); $i++) {
		$ok = false;
		for ($j = 0; $j < strlen($alphanum); $j++) {
			if ($address[$i] == $alphanum[$j]) {
				$ok = true;
				break;
			}
		}
		if (!$ok) return false;
	}

	return $address[0] == "N" || $address[0] == "M";
}

?>