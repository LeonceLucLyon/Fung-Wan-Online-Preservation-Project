<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$uid = $HTTP_GET_VARS[i];

if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "user", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], "0", "user", "w"))
{
	die("Access denied. Read-Only.");
}


switch($HTTP_GET_VARS['a'])
{
	case 'a':
		$serverid = $HTTP_POST_VARS['serverid_0'];
		$page = $HTTP_POST_VARS['page_0'];
		$action = $HTTP_POST_VARS['action_0'];

		$query_rs = "INSERT INTO perm(userid, serverid, page, action) VALUES('$uid', '$serverid', '$page', '$action')";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error($dbGmAdm));

		$after = get_str_rs($dbGmAdm, "SELECT * FROM perm WHERE id=" . mysql_insert_id($dbGmAdm));

		
		break;

	case 'd':
		$perm_id = $HTTP_GET_VARS[pi];
		$befores = get_str_rs($dbGmAdm, "SELECT * FROM perm WHERE id='{$perm_id}';");

		$query_rs = "DELETE FROM perm WHERE id='$perm_id'";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error($dbGmAdm));
		
		break;

	case 's':
		$perm_id = $HTTP_GET_VARS[pi];
		$serverid = $HTTP_POST_VARS["serverid_{$perm_id}"];
		$page = $HTTP_POST_VARS["page_{$perm_id}"];
		$action = $HTTP_POST_VARS["action_{$perm_id}"];

		$befores = get_str_rs($dbGmAdm, "SELECT * FROM perm WHERE id='{$perm_id}';");

		$query_rs = "UPDATE perm SET serverid = '$serverid', page = '$page', action = '$action' WHERE id='{$perm_id}'";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error());

		$after = get_str_rs($dbGmAdm, "SELECT * FROM perm WHERE id='{$perm_id}';");


		break;
}

$dbGmAdm1 = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm) or die(mysql_error());
mysql_select_db($database_dbGmAdm, $dbGmAdm1);
$rs_user = mysql_query("SELECT * FROM admin WHERE id='$uid'", $dbGmAdm1) or die(mysql_error($dbGmAdm1));
$row_user = mysql_fetch_assoc($rs_user);
mysql_free_result($rs_user);

$dbGmAdm2 = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm) or die(mysql_error());
mysql_select_db($database_dbGmAdm, $dbGmAdm2);
$rs_perm = mysql_query("SELECT * FROM perm WHERE userid='$uid'", $dbGmAdm2) or die(mysql_error($dbGmAdm2));

$arServer = array(array('0', 'All'));

$dbGmAdm3 = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm) or die(mysql_error());
mysql_select_db($database_dbGmAdm, $dbGmAdm3);
$rs_server = mysql_query("SELECT id, name, type FROM gm_server", $dbGmAdm3) or die(mysql_error($dbGmAdm3));
while($row_server = mysql_fetch_assoc($rs_server))
{
	array_push($arServer, array($row_server[id], $row_server[name]) );
	$arServerType[$row_server[id]] = $row_server[type];
	$js_data .= "servertype[{$row_server[id]}]='{$row_server[type]}';";
}
mysql_free_result($rs_server);

$arPage = array(
	array( "*", "All"),
	array( "user", "User"),
	array( "ac", "Account"),
	array( "gmchar", "Character"),
	array( "gmdata", "Game Data"),
	array( "gmevent", "Game Event"),
	array( "motd", "MOTD"),
	array( "conf", "System Admin"),
);

$arPageAs = array(
	array( "ac", "Account"),
);

$arPageWc = array(
	array( "*", "All"),
	array( "gmchar", "Character"),
	array( "gmdata", "Game Data"),
	array( "gmevent", "Game Event"),
	array( "motd", "MOTD"),
	array( "conf", "System Admin"),
);

$arAction = array(
	array( "*", "ReadWrite"),
	array( "r", "ReadOnly")
);

$html_server = "<select name=serverid_0 onchange='chgsel(0)'><option value=''></option>";
foreach($arServer as $row)
{
	$html_server .= "<option value='$row[0]'> $row[1] </option>";
}
$html_server .= "</select>";

$html_page = "<select name=page_0><option value=''></option>";
foreach($arPage as $row)
{
	$html_page .= "<option value='$row[0]'> $row[1] </option>";
}
$html_page .= "</select>";

$html_action = "<select name=action_0><option value=''></option>";
foreach($arAction as $row)
{
	$html_action .= "<option value='$row[0]'> $row[1] </option>";
}
$html_page .= "</select>";

$html_first="
<tr>
	<td> {$html_server} </td>
	<td> {$html_page} </td>
	<td> {$html_action} </td>
	<td> <input type=button value=\"Add\" onclick=\"if(document.form1.serverid_0.value=='' || document.form1.page_0.value=='' || document.form1.action_0.value==''){alert('Please select valid permission to be added.');return false;}postform(document.form1, 'admperm.php?i={$uid}&a=a')\"> </td>
</tr>
";

while($row_perm = mysql_fetch_assoc($rs_perm))
{
	$server_type = '*';
	$id = $row_perm[id];
	$html_server = "<select name=serverid_{$id} onchange='chgsel({$id})'>";
	foreach($arServer as $row_server)
	{
		if($row_perm[serverid] == $row_server[0])
		{
			$selected = "SELECTED";
		}
		else
		{
			$selected = "";
		}
		$html_server .= "<option value='{$row_server[0]}' $selected> {$row_server[1]} </option>";
	}
	$html_server .= "</select>";

	//all
	switch($arServerType[$row_perm[serverid]])
	{
		case 'wc':
			$arPageList = $arPageWc;
			break;
		case 'as':
			$arPageList = $arPageAs;
			break;
		default:
			$arPageList = $arPage;
	}

	$html_page = "<select name=page_$id>";
	foreach($arPageList as $row_page)
	{
		$selected = $row_perm[page] == $row_page[0]? "SELECTED": "";
		$html_page .= "<option value='{$row_page[0]}' $selected> {$row_page[1]} </option>";
	}
	$html_page .= "</select>";

	$html_action = "<select name=action_$id>";
	foreach($arAction as $row_action)
	{
		$selected = $row_perm[action] == $row_action[0]? "SELECTED": "";
		$html_action .= "<option value='{$row_action[0]}' $selected> {$row_action[1]} </option>";
	}
	$html_action .= "</select>";

	$html_perm_list .= "<tr><td> $html_server </td><td> $html_page </td><td> $html_action </td><td> <input type=button value='Save' onclick='if(confirm(\"Overwrite?\"))postform(document.form1, \"admperm.php?i=$uid&a=s&pi=$id\")'> <input type=button value='Delete' onclick='if(confirm(\"Delete?\"))postform(document.form1, \"admperm.php?i=$uid&a=d&pi=$id\")'> </td></tr>";
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
function chgsel(n){
	eval("var o = document.all.page_" + n)
	while(o.options.length) o.options.remove(0)

	eval("var c = document.all.serverid_" + n + ".value")
<?
	echo "if(servertype[c]=='*'){";
	foreach($arPage as $row)
	{
		echo "opt=document.createElement(\"OPTION\");opt.value='{$row[0]}';opt.text='{$row[1]}';o.add(opt);";
	}

	echo "}if(servertype[c]=='as'){";
	foreach($arPageAs as $row)
	{
		echo "opt=document.createElement(\"OPTION\");opt.value='{$row[0]}';opt.text='{$row[1]}';o.add(opt);";
	}

	echo "}if(servertype[c]=='wc'){";
	foreach($arPageWc as $row)
	{
		echo "opt=document.createElement(\"OPTION\");opt.value='{$row[0]}';opt.text='{$row[1]}';o.add(opt);";
	}
	echo "}";
?>
}
var servertype=new Array()
servertype[0]="*"
<?=$js_data?>
//-->
</script>
</head>
<body>
<h3>User Permission</h3>(User: <?=$row_user['name']?>)
<form name="form1" method="post" action="">
<table border=1 cellspacing=0>
<tr>
	<td> Server </td>
	<td> Page </td>
	<td> Permission </td>
	<td> &nbsp; </td>
</tr>
<?=$html_first?>
<?=$html_perm_list?>
</table>
</form>
</body>
</html>
