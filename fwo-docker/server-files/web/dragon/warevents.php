<?php
require('auth.php');

$wartype = Array(1=>"Skirmish", 2=>"Relic Capture", 3=>"Town Capture");
$warstate = Array(1=>"Declared", 2=>"Accepted", 3=>"Ongoing");

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

switch($HTTP_GET_VARS['a'])
{
//	case 'wc':
		//if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
//		break;
	default:
		$query_rs = "SELECT * FROM warevent ORDER BY StartTime";

		break;
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'warevents.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";

if($query_rs && $wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
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
<h3>War Event</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{
?>
<table border="1" cellspacing=0>
	<tr>
		<td>#</td>
		<td>StartTime</td>
		<td>EndTime</td>
		<td>Challenger</td>
		<td>Opponent</td>
		<td>Type</td>
		<td>State</td>
		<td>Scene</td>
		<td>Link</td>
	</tr>
	<?
	if(mysql_num_rows($rs) > 0)
	{
		$n = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$n++;
			if($row[State] <= 3){
				$state = $warstate[$row[State]];
			}else{
				$state = "Over ($row[State])";
			}

			$start = date("Y/m/d H:i", $row[StartTime]);
			$end = date("Y/m/d H:i", $row[EndTime]);
			$scene = $scene_name[$row[SceneID]];
			if($scene == "") $scene = $row[SceneID];
			$aclan = $clan_name[$row[AClanID]];
			$dclan = $clan_name[$row[DClanID]];
			echo "<tr><td>$n</td><td>$start</td><td>$end</td><td>$aclan</td><td>$dclan</td><td>{$wartype[$row[Type]]}</td><td>$state</td><td>$scene</td><td><a href=\"warevent.php?i=$row[WarEventID]\">Edit</a></td></tr>";
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