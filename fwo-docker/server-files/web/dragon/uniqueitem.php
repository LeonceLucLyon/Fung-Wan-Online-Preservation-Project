<?php
require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

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

	if($HTTP_GET_VARS[a]=="s")
	{
		if($HTTP_POST_VARS[CharID]==0)
		{
			for($n=0;$n<10;$n++)
			{
				$rs = mysql_query("UPDATE charinv_{$n} SET ItemID=0, Quantity=0, Identified=0, Field1=0, Field2=0, Field3=0, Field4=0, Field5=0 WHERE ItemID='{$HTTP_GET_VARS[i]}';", $dbWc) or die(mysql_error());
			}
		}

		$query_rs = "UPDATE uniqueitem SET
			CharID='{$HTTP_POST_VARS[CharID]}',
			revertToID='{$HTTP_POST_VARS[revertToID]}',
			Time='{$HTTP_POST_VARS[Time]}',
			SetID='{$HTTP_POST_VARS[SetID]}',
			OriginatorID='{$HTTP_POST_VARS[OriginatorID]}',
			NPCFlag='{$HTTP_POST_VARS[NPCFlag]}'
			WHERE ItemID='{$HTTP_GET_VARS[i]}'";

		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		header ("Location: uniqueitem.php?i={$HTTP_GET_VARS[i]}&wid=$wid");

	}
	elseif($HTTP_GET_VARS[a]=="d")
	{
	}

	$query_rs = "SELECT * FROM uniqueitem WHERE ItemID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs)==0) die("<font color=red><b>No matched queries.</b></font>");
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
WorldController: <?=$row_rsSvr[name]?>
<form name="form1" method="post" action="">
  <table border="1">
    <tr>
      <td>Item ID</td>
      <td><input name="ItemID" type="text" id="ItemID" value="<?=$row[ItemID]?>" READONLY><?=getstring($row[ItemID],'item')?></td>    </tr>
    <tr>
      <td>CharID</td>
      <td><input name="CharID" type="text" id="CharID" value="<?=$row[CharID]?>" READONLY>
<?
/*
	if($row[CharID])
	{
		echo "<a href='charinv.php?f={$row[CharID]}&wid=$wid'>$row[CharID]</a>";
   }
*/
?>
	</td>
    </tr>
    <tr>
      <td>revert To ID</td>
      <td><input name="revertToID" type="text" id="revertToID" value="<?=$row[revertToID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Time</td>
      <td><input name="Time" type="text" id="Time" value="<?=$row[Time]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>SetID</td>
      <td><input name="SetID" type="text" id="SetID" value="<?=$row[SetID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>OriginatorID</td>
      <td><input name="OriginatorID" type="text" id="OriginatorID" value="<?=$row[OriginatorID]?>"  <?=$readonly?>></td>
    </tr>
    <tr>
      <td>NPCFlag</td>
      <td><input name="NPCFlag" type="text" id="NPCFlag" value="<?=$row[NPCFlag]?>" <?=$readonly?>></td>
    </tr>
  </table>
  <p>
<!--
    <input type="reset" name="Reset" value="Reset">
	  <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'uniqueitem.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
-->
<?
if($row[CharID])
{
?>
 <input type=button value="Set no owner" onclick="if(confirm('Overwrite?')){document.form1.CharID.value=0;postform(document.form1,'uniqueitem.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')}">
<?
}
else
{
?>
 <input type=button value="Set to OriginatorID" onclick="if(confirm('Overwrite?')){document.form1.CharID.value=<?=$row[OriginatorID]?>;postform(document.form1,'uniqueitem.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')}">
<?
}
?>
</p>
</form>
</body>
</html>
