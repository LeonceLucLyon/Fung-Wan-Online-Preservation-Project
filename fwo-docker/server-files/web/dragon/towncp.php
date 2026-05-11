<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_POST_VARS[wid];
if($HTTP_GET_VARS[wid])$wid=$HTTP_GET_VARS[wid];
if($wid=="")
{
	$wid=$HTTP_SESSION_VARS['wid'];
}
else
{
	$HTTP_SESSION_VARS['wid']=$wid;
}

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
$htmlWc="<select name=wid onChange=\"postform(document.form1,'towncp.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$rs = mysql_query("SELECT * FROM resource WHERE type=1 ORDER BY ClanID, StringID", $dbWc) or die(mysql_error($dbWc));
}
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
<h3>Town List</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?
if($wid!="")
{
?>
<table border="1" cellspacing=0>
	<tr>
		<th>Town</th>
		<th>Belonging Clan</th>
	</tr>
	<?
	if(mysql_num_rows($rs) > 0)
	{
		$n = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$n++;
			$townname = $town_name[$row[StringID]];
			if($townname == "") $townname = $row[StringID];
			echo "<tr><td>$townname</td><td>{$clan_name[$row[ClanID]]}&nbsp;</td></tr>";
		}//end while
	}//end if(mysql_num_rows($rs) > 0)
	else
	{
		echo "<tr><td colspan=9><font color=red><b>No matched queries.</b></font></td></tr>";
	}
	echo "</table>";
}//end if($wid!="")
?>
 </form>
</body>
</html>