<?php
require('auth.php');
require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "*", "r"))
{
	die("Access denied.");
}

$ar_act = array(
"1"=>"add admin",
"2"=>"update admin",
"3"=>"delete admin",
"4"=>"add admin permission",
"5"=>"update admin permission",
"6"=>"delete admin permission",
"7"=>"",
"8"=>""
);

$sql = "SELECT *, DATE_FORMAT(datetime, '%Y-%m-%d %H:%i:%s') as fmtdt FROM act_log WHERE id='{$HTTP_GET_VARS[i]}'";

$rs = mysql_query($sql) or die(mysql_error());
$row = mysql_fetch_assoc($rs) or die('Record not found.');
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<link rel="STYLESHEET" type="text/css" href="calendar.css">
<script language="JavaScript" src="simplecalendar.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<body>
<h3>Game Admin Detail Log</h3>
<form name="form1" method="post" action="">
<table border=1 cellspacing=0>
	<tr>
		<td>DateTime</td>
		<td><?=$row[fmtdt]?></td>
	</tr>
	<tr>
		<td>User</td>
		<td><?=$row[user]?></td>
	</tr>
	<tr>
		<td>Client IP</td>
		<td><?=$row[client]?></td>
	</tr>
	<tr>
		<td>Action</td>
		<td><?=$row[act]?></td>
	</tr>
	<tr>
		<td>URI</td>
		<td><?=$row[uri]?></td>
	</tr>
	<tr>
		<td>Server</td>
		<td><?=$row[server]?></td>
	</tr>
	<tr>
		<td>Command</td>
		<td><?=nl2br(htmlentities($row[cmd]))?></td>
	</tr>
	<tr>
		<td>DB.Table</td>
		<td><?=$row[db]?>.<?=$row[tbl]?></td>
	</tr>
	<tr>
		<td>Before</td>
		<td><?=nl2br(htmlspecialchars($row[befores]))?></td>
	</tr>
	<tr>
		<td>After</td>
		<td><?=nl2br(htmlspecialchars($row[after]))?> </td>
	</tr>
	<tr>
		<td colspan=2 align="center"><input type="button" value="Close" onclick="window.close()"></td>
	</tr>
</table>
</form>
</body>
</html>
