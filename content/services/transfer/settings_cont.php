<?php

get_connection();

$slt_user_f = "SELECT * FROM user WHERE id = '$_SESSION[user_id]'";
$rlt_user_f = mysql_query($slt_user_f);
$row_user_f = mysql_fetch_assoc($rlt_user_f);

?>

<div class="article">
<h2><span>Settings</span></h2>
<p>
<form action="?c=services/transfer/settings" method="post">
<input type="hidden" name="step" value="2" />
<table>
<tr><td>Allow API withdrawals</td><td><select onchange="submit()" name="allow_withdrawals"><option option="yes">yes</option><option value="no"<?php if ($row_user_f["allow_api_withdrawals"] == "no") echo " selected=\"selected\"";?>>no</option></select></td></tr>
</table>
</form>
</p>
</div>