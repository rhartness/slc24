<div class="article">
<h2><span>Transfer API</span></h2>
<p>
<?php if ($_SESSION["li"] == 1) { ?>
Your ID string is <?php

get_connection();

$slt_user_a = "SELECT id_string FROM user WHERE id = '$_SESSION[user_id]'";
$rlt_user_a = mysql_query($slt_user_a);
$row_user_a = mysql_fetch_assoc($rlt_user_a);

echo $row_user_a["id_string"];

?>.<br /><br />
<?php } ?>
<a href="downloads/api_specification10.pdf">Get the latest API specification here.</a>
</p>

</div>