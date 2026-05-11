<?php
require("auth.php");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS["wid"];
if($wid=="")
{
	die("World controller not set");
}
else
{
	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
	{
		die("Access denied.");
	}
	elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
	{
		die("Access denied. Read-Only.");
	}

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_GET_VARS[a]=="s")
	{
		$query_rs = "UPDATE stances SET
			WeaponGroup='{$HTTP_POST_VARS[WeaponGroup]}',
			BuyPrice='{$HTTP_POST_VARS[BuyPrice]}',
			WeaponRange='{$HTTP_POST_VARS[WeaponRange]}',
			NumMoves='{$HTTP_POST_VARS[NumMoves]}',
			WeaponSpeed='{$HTTP_POST_VARS[WeaponSpeed]}'
			WHERE StanceID='{$HTTP_GET_VARS[i]}'";

		$befores = get_str_rs($dbWc, "SELECT * FROM stances WHERE StanceID='{$HTTP_GET_VARS[i]}'");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM stances WHERE StanceID='{$HTTP_GET_VARS[i]}'");
		
		header ("Location: stances.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
		exit;
	}

	$query_rs = "SELECT * FROM stances WHERE StanceID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red><b>No matched queries.</b></font>");
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
}
$readonly = $readonly_gmdata?"READONLY":"";
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Stance</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<br><br>
<table border="1" cellspacing=0>
    <tr>
      <td>StanceID</td>
      <td><input name="StanceID" type="text" id="StanceID" value="<?=$row[StanceID]?>" readonly="yes"><?=getstring($row[StanceID],'stance')?></td>
    </tr>
    <tr>
      <td>WeaponGroup</td>
      <td><input name="WeaponGroup" type="text" id="WeaponGroup" value="<?=$row[WeaponGroup]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BuyPrice</td>
      <td><input name="BuyPrice" type="text" id="BuyPrice" value="<?=$row[BuyPrice]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>WeaponRange</td>
      <td><input name="WeaponRange" type="text" id="WeaponRange" value="<?=$row[WeaponRange]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>NumMoves</td>
      <td><input name="NumMoves" type="text" id="NumMoves" value="<?=$row[NumMoves]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>WeaponSpeed</td>
      <td><input name="WeaponSpeed" type="text" id="WeaponSpeed" value="<?=$row[WeaponSpeed]?>" <?=$readonly?>></td>
    </tr>
  </table>
<?
if(!$readonly_gmdata)
{
?>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'stances.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
<?
}
?>
</form>
</body>
</html>