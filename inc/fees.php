<?php

function withdrawal_with_fee($amount, $type=0, $user_id=0) {
	return $amount + 0.1;
}

function withdrawals_with_fee_single($amount, $number, $type=0, $user_id=0) {
	return $amount + 0.1;
}

function withdrawals_with_fee_multi($amount, $number, $type=0, $user_id=0) {
	return $amount + 0.1 * $number;
}

function deposit_with_fee($amount, $type=0, $user_id=0) {
	return $amount - 0.01;
}

/*

function withdrawal_with_fee($amount, $type=0, $user_id=0) {
	return $amount * 1.006 + 0.3;
}

function withdrawals_with_fee_single($amount, $number, $type=0, $user_id=0) {
	if ($number < 3) {
		return $amount * 1.006 + 0.3 / $number;
	} else {
		return $amount * 1.006 + 0.1;
	}
}

function withdrawals_with_fee_multi($amount, $number, $type=0, $user_id=0) {
	if ($number < 3) {
		return $amount * 1.006 + 0.3;
	} else {
		return $amount * 1.006 + 0.1 * $number;
	}
}

function deposit_with_fee($amount, $type=0, $user_id=0) {
	return $amount * 0.996 - 0.1;
}

*/

?>