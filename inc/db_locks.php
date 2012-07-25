<?php

function get_lock($string) {
	global $db;
	
	$res = mysql_query("SELECT GET_LOCK('".mysql_real_escape_string($string, $db)."',10) AS `lock`");
	$res = mysql_fetch_assoc($res);
	
	return $res["lock"] == 1;
}

?>