<?php
include_once "inc/account.php";

function handle_deposit_by_address($address)
{
	global $db;

	$address = mysql_real_escape_string($address, $db);

	$slt_address_a = "SELECT * FROM deposit_address WHERE address = '$address'";
	$rlt_address_a = mysql_query($slt_address_a);
	
	if (mysql_num_rows($rlt_address_a) != 0)
	{
		$row_address_a = mysql_fetch_assoc($rlt_address_a);
		
		if ($row_address_a["currency"] == "SLC")
			return handle_deposit_slc($row_address_a);
		if ($row_address_a["currency"] == "BTC")
			return handle_deposit_btc($row_address_a);
		if ($row_address_a["currency"] == "NMC")
			return handle_deposit_nmc($row_address_a);
	}
	
	return array("received" => 0, "booked" => 0, "pending" => 0);
}

function handle_deposit_by_id($id)
{
	global $db;

	if (!is_numeric($id))
		$id = 0;

	$slt_address_a = "SELECT * FROM deposit_address WHERE address = '$address'";
	$rlt_address_a = mysql_query($slt_address_a);
	
	if (mysql_num_rows($rlt_address_a) != 0)
	{
		$row_address_a = mysql_fetch_assoc($rlt_address_a);
		
		if ($row_address_a["currency"] == "SLC")
			return handle_deposit_slc($row_address_a);
		if ($row_address_a["currency"] == "BTC")
			return handle_deposit_btc($row_address_a);
		if ($row_address_a["currency"] == "NMC")
			return handle_deposit_nmc($row_address_a);
	}
	
	return array("received" => 0, "booked" => 0, "pending" => 0);
}

function handle_deposit_slc($row_address_a)
{
	global $db, $solidcoin;

	$received = $solidcoin->sc_getreceivedbyaddress("main", $row_address_a["address"], 0) / 10000;
	$booked = $row_address_a["booked"];
	$pending = $received - $booked;
	
	return array("received" => $received, "booked" => $booked, "pending" => $pending);
}

function handle_deposit_btc($row_address_a)
{
	global $db, $bitcoin;

	$received = $bitcoin->getreceivedbyaddress($row_address_a["address"], 0);
	$booked = $row_address_a["booked"];
	$pending = $received - $booked;
	
	return array("received" => $received, "booked" => $booked, "pending" => $pending);
}

function handle_deposit_nmc($row_address_a)
{
	global $db, $namecoin;

	$received = $namecoin->getreceivedbyaddress($row_address_a["address"], 0);
	$booked = $row_address_a["booked"];
	$pending = $received - $booked;
	
	return array("received" => $received, "booked" => $booked, "pending" => $pending);
}

?>