<?php
session_start();

if (is_numeric($_GET["time_offset"])) {
	$_SESSION["time_offset_set_last"] = time();
	$_SESSION["time_offset"] = $_GET["time_offset"];
}

?>