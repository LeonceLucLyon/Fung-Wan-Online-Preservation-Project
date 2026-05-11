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

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'clanwar.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid worldcontroller.");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($wc_ip, $row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($wc_db, $dbWc);

	switch($HTTP_GET_VARS['a'])
	{
	//	case 'wc':
			//if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
	//		break;
		case "s":
			$query_rs = Array();
			$log_rs = Array();
			for($n=1;$_REQUEST["clankey$n"] > 0; $n++)
			{
				$clankey = $_REQUEST["clankey$n"];

				list($hr, $mi, $sc) = split(":", $_REQUEST["starttime$n"]);
				list($yr, $mth, $day) = split("-", $_REQUEST["startdate$n"]);
				$startdt = mktime($hr, $mi, $sc, $mth, $day, $yr);

				list($hr, $mi, $sc) = split(":", $_REQUEST["endtime$n"]);
				list($yr, $mth, $day) = split("-", $_REQUEST["enddate$n"]);
				$enddt = mktime($hr, $mi, $sc, $mth, $day, $yr);

				$query_rs[] = "UPDATE clanwar SET StartTime='$startdt', EndTime='$enddt' WHERE ClanKey='$clankey'";
				$log_rs[] = "SELECT * FROM clanwar WHERE ClanKey='$clankey'";
			}
			$n = 0;
			foreach($query_rs as $sql)
			{
				$befores = get_str_rs($dbWc, $log_rs[$n]);
				$rs = mysql_query($sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, $log_rs[$n]);
				
				$n++;
			}
			header("Location: clanwar.php?wid=$wd");
			exit;
			break;
		default:
			$query_rs = "SELECT * FROM clanwar ORDER BY Status DESC, StartTime";
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
			break;
	}
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<link rel="STYLESHEET" type="text/css" href="calendar.css">
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<script language="JavaScript" src="simplecalendar.js" type="text/javascript"></script>
<body>
<form name="form1" method="post" action="">
<h3>Total War</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{
?>
<table border="1" cellspacing>
	<tr>
		<td>#</td>
		<td>StartTime</td>
		<td>EndTime</td>
		<td>Challenger</td>
		<td>Opponent</td>
		<td>State</td>
	</tr>
	<?
	if(mysql_num_rows($rs) > 0)
	{
		$n = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$n++;
			if($row[Status] == 0){
				$state = "Inactive";
			}else{
				$state = "<span style='background-color:lime'>Active</a>";
			}

			$starttime = date("H:i:s", $row[StartTime]);
			$endtime = date("H:i:s", $row[EndTime]);
			$startdate = date("Y-m-d", $row[StartTime]);
			$enddate = date("Y-m-d", $row[EndTime]);

			$aclan = $clan_name[$row[AClanID]];
			$dclan = $clan_name[$row[DClanID]];

			if($aclan=="") $aclan="&nbsp;-";
			if($dclan=="") $dclan="&nbsp;-";

			echo "<tr><td>$n <input type=hidden name='clankey$n' value='$row[ClanKey]'</td><td>
				<input type=text name=startdate$n size=10 value='$startdate' maxlength=10><a href='javascript: void(0)' onmouseover='if (timeoutId) clearTimeout(timeoutId);window.status=\"Show Calendar\";return true;' onmouseout='if(timeoutDelay)calendarTimeout();window.status=\"\";' onclick='g_Calendar.show(event,\"form1.startdate$n\",false, \"yyyy-mm-dd\"); return false;'><img src='images/calendar.gif' name='imgCalendar' width=34 height=21 border=0 alt=''></a>
				<input type=text name=starttime$n size=8 value='$starttime' maxlength=8>
				</td><td>
				<input type=text name=enddate$n size=10 value='$enddate' maxlength=10><a href='javascript: void(0)' onmouseover='if (timeoutId) clearTimeout(timeoutId);window.status=\"Show Calendar\";return true;' onmouseout='if(timeoutDelay)calendarTimeout();window.status=\"\";' onclick='g_Calendar.show(event,\"form1.enddate$n\",false, \"yyyy-mm-dd\"); return false;'><img src='images/calendar.gif' name='imgCalendar' width=34 height=21 border=0 alt=''></a>
				<input type=text name=endtime$n size=8 value='$endtime' maxlength=8>
				</td><td>$aclan</td><td>$dclan</td><td>$state</td></tr>";
		}//end while
		echo "</table>";
		echo "<input type=button value='Save' onclick=\"if(confirm('confirm overwrite?'))postform(document.form1,'clanwar.php?wid=$wid&a=s')\">";
	}//end if(mysql_num_rows($rs) > 0)
	else
	{
		echo "<tr><td colspan=9><font color=red><b>No matched queries.</b></font></td></tr>";
		echo "</table>";
	}
}//end if($wid!="")
?>
 </form>
</body>
</html>