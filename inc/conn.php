<?php

if (!function_exists("get_connection"))
{
	function get_connection()
	{
		global $dbn, $db;
		
		$db = mysql_connect("localhost", "root", "PASSWORD");
		mysql_select_db("DB_NAME", $db);
		
		mysql_set_charset("utf8", $db);
		
		return $db;
	}
}

?>