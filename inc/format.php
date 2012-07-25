<?php

function currency_format($amount, $currency)
{
	if ($currency == "SLC")
		return number_format($amount, 4, ".", ",");
	if ($currency == "BTC")
		return number_format($amount, 8, ".", ",");
	if ($currency == "NMC")
		return number_format($amount, 8, ".", ",");
	if ($currency == "USD")
		return number_format($amount, 2, ".", ",");
	if ($currency == "EUR")
		return number_format($amount, 2, ".", ",");
	if ($currency == "GPB")
		return number_format($amount, 2, ".", ",");
	if ($currency == "CAD")
		return number_format($amount, 2, ".", ",");
	if ($currency == "AUD")
		return number_format($amount, 2, ".", ",");
	if ($currency == "CHF")
		return number_format($amount, 2, ".", ",");
	if ($currency == "JPY")
		return number_format($amount, 2, ".", ",");
}

function get_num_chars($char, $num)
{
	$cs = "";
	for ($i = 0; $i < $num; $i++) {
		$cs .= $char;
	}
	return $cs;
}

function nice_format($rate, $addhiddens=false, $front=8, $rear=8)
{
	$precision = 1;
	
	for ($i = 0; $i < $rear; $i++) $precision *= 10;

	$rate = round($rate * $precision) / $precision;
	
	if ($rate > 0 && $rate < 1) {
		$rate++;
		$rate = (string)$rate;
		$rate[0] = 0;
	}
	if ($rate < 0 && $rate > -1) {
		$rate--;
		$rate = (string)$rate;
		$rate[1] = 0;
	}
	
	if (!$addhiddens) {
		return $rate;
	} else {	
		$urate = $rate;
		$rate = explode(".", $rate);
		$rrate = "";
		
		if (strlen($rate[0]) < $front) {
			$rrate = "<span style=\"visibility: hidden\">".get_num_chars('0', $front - strlen($rate[0]))."</span>".$rate[0];
		} else {
			$rrate = $rate[0];
		}
		
		if (!$rate[1]) {
			$rrate .= "<span style=\"visibility: hidden\">.".get_num_chars('0', $rear)."</span>";
		} else if (strlen($rate[1]) < $rear) {
			$rrate .= ".".$rate[1]."<span style=\"visibility: hidden\">".get_num_chars('0', $rear - strlen($rate[1]))."</span>";
		} else {
			$rrate .= ".".$rate[1];
		}
		
		return $rrate;
	}
}

?>