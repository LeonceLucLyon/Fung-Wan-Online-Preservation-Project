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

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
}

if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST')
{
	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
	{
		$HTTP_SESSION_VARS['wid'] = '';
		die("Access denied.");
	}

	$start_date = trim($_POST['StartDate']);
	$end_date = trim($_POST['EndDate']);
	$start_time = trim($_POST['StartTime']);
	$end_time = trim($_POST['EndTime']);
	$start_date = split('-', $start_date);
	$start_time= split(':', $start_time);
	$end_date = split('-', $end_date);
	$end_time = split(':', $end_time);
	if(count($start_date) != 3 || count($end_date) !=3 || count($start_time) != 2 || count($end_time) != 2)
	{
		die("Invalid date/time format for start/end time.<p><a href='javascript:history.back()'>Back</a>");
	}

	foreach($start_date as $e)
	{
		if(!is_numeric($e)) die("Invalid date/time format for start/end time.<p><a href='javascript:history.back()'>Back</a>");
	}
	foreach($start_time as $e)
	{
		if(!is_numeric($e)) die("Invalid date/time format for start/end time.<p><a href='javascript:history.back()'>Back</a>");
	}
	foreach($end_date as $e)
	{
		if(!is_numeric($e)) die("Invalid date/time format for start/end time.<p><a href='javascript:history.back()'>Back</a>");
	}
	foreach($end_time as $e)
	{
		if(!is_numeric($e)) die("Invalid date/time format for start/end time.<p><a href='javascript:history.back()'>Back</a>");
	}

	$end_datetime = mktime($end_time[0], $end_time[1], 0, $end_date[1], $end_date[2], $end_date[0]);
	$start_datetime = mktime($start_time[0], $start_time[1], 0, $start_date[1], $start_date[2], $start_date[0]);

	$query_rs = "UPDATE warevent SET
		Duration='$HTTP_POST_VARS[duration]',
		Type='$HTTP_POST_VARS[type]',
		State='$HTTP_POST_VARS[state]',
		SceneID='$HTTP_POST_VARS[scene]',
		AClanID='$HTTP_POST_VARS[aclan]',
		DClanID='$HTTP_POST_VARS[dclan]',
		AGuildID='$HTTP_POST_VARS[aguild]',
		DGuildID='$HTTP_POST_VARS[dguild]',
		ACharID='$HTTP_POST_VARS[achar]',
		DCharID='$HTTP_POST_VARS[dchar]',
		ATokens='$HTTP_POST_VARS[atoken]',
		DTokens='$HTTP_POST_VARS[dtoken]',
		Data1='$HTTP_POST_VARS[data1]',
		Data2='$HTTP_POST_VARS[data2]',
		Data3='$HTTP_POST_VARS[data3]',
		Data4='$HTTP_POST_VARS[data4]',
		Data5='$HTTP_POST_VARS[data5]',
		Data6='$HTTP_POST_VARS[data6]',
		Data7='$HTTP_POST_VARS[data7]',
		Data8='$HTTP_POST_VARS[data8]',
		Data9='$HTTP_POST_VARS[data9]',
		Data10='$HTTP_POST_VARS[data10]',
		ResData='$HTTP_POST_VARS[resdata]',
		PrevState='$HTTP_POST_VARS[prevstate]',
		StartTime='$start_datetime',
		EndTime='$end_datetime'
		WHERE WarEventID = '$HTTP_GET_VARS[i]'
	";

	$befores = get_str_rs($dbWc, "SELECT * FROM warevent WHERE WarEventID = '$HTTP_GET_VARS[i]' ");
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
	$after = get_str_rs($dbWc, "SELECT * FROM warevent WHERE WarEventID = '$HTTP_GET_VARS[i]' ");
	
	header("Location: warevent.php?i=$HTTP_GET_VARS[i]");
	exit;
}

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
		//if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
		break;
	default:
		$query_rs = "SELECT * FROM warevent WHERE WarEventID='{$HTTP_GET_VARS[i]}' ";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		break;
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
$htmlWc="<select name=wid onChange=\"postform(document.form1,'warevent.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";

?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
<form name="form1" method="post">
<table border="0">
	<tr>
		<td>World Controller</td>
		<td><?=$htmlWc?></td>
	</tr>
</table>
<br>
 <?php
if($wid!="")
{

	if(mysql_num_rows($rs) == 1)
	{
		$row = mysql_fetch_assoc($rs);
?>
<table border="1">

	<tr>
		<td>StartTime</td><td>:</td><td colspan="2"><input type="text" name="StartDate" size="8" value="<?=date("Y-m-d", $row[StartTime])?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.StartDate',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a> &nbsp; <input name="StartTime" size=1 maxlength=5 value="<?=date("H:i", $row[StartTime])?>"> (11pm => 23, 12am => 0)</td>
	</tr>
	<tr>
		<td>EndTime</td><td>:</td><td colspan="2"><input type="text" name="EndDate" size="8" value="<?=date("Y-m-d", $row[EndTime])?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.EndDate',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a> &nbsp; <input name="EndTime" size=1 maxlength=5 value="<?=date("H:i", $row[EndTime])?>"> (11pm => 23, 12am => 0)</td>
	</tr>

	<tr>
		<td>Duration</td><td>:</td><td><input name="duration" value="<?=$row[Duration]?>"></td>
	</tr>
	<tr>
		<td>Type</td><td>:</td><td><input name="type" value="<?=$row[Type]?>"></td>
	</tr>
	<tr>
		<td>State</td><td>:</td><td><input name="state" value="<?=$row[State]?>"></td>
	</tr>
	<tr>
		<td>Scene</td><td>:</td><td><input name="scene" value="<?=$row[SceneID]?>"></td>
	</tr>
	<tr>
		<td></td><td></td><td>Challenger</td><td>Opponent</td>
	</tr>
	<tr>
		<td>Clan</td><td>:</td><td bgcolor="red"><input name="aclan" value="<?=$row[AClanID]?>"></td><td bgcolor="blue"><input name="dclan" value="<?=$row[DClanID]?>"></td>
	</tr>
	<tr>
		<td>Guild</td><td>:</td><td bgcolor="red"><input name="aguild" value="<?=$row[AGuildID]?>"></td><td bgcolor="blue"><input name="dguild" value="<?=$row[DGuildID]?>"></td>
	</tr>
	<tr>
		<td>Char</td><td>:</td><td bgcolor="red"><input name="achar" value="<?=$row[ACharID]?>"></td><td bgcolor="blue"><input name="dchar" value="<?=$row[DCharID]?>"></td>
	</tr>
	<tr>
		<td>Token</td><td>:</td><td bgcolor="red"><input name="atoken" value="<?=$row[ATokens]?>"></td><td bgcolor="blue"><input name="dtoken" value="<?=$row[DTokens]?>"></td>
	</tr>
	<tr>
		<td>Data1</td><td>:</td><td><input name="data1" value="<?=$row[Data1]?>"></td>
	</tr>
	<tr>
		<td>Data2</td><td>:</td><td><input name="data2" value="<?=$row[Data2]?>"></td>
	</tr>
	<tr>
		<td>Data3</td><td>:</td><td><input name="data3" value="<?=$row[Data3]?>"></td>
	</tr>
	<tr>
		<td>Data4</td><td>:</td><td><input name="data4" value="<?=$row[Data4]?>"></td>
	</tr>
	<tr>
		<td>Data5</td><td>:</td><td><input name="data5" value="<?=$row[Data5]?>"></td>
	</tr>
	<tr>
		<td>Data6</td><td>:</td><td><input name="data6" value="<?=$row[Data6]?>"></td>
	</tr>
	<tr>
		<td>Data7</td><td>:</td><td><input name="data7" value="<?=$row[Data7]?>"></td>
	</tr>
	<tr>
		<td>Data8</td><td>:</td><td><input name="data8" value="<?=$row[Data8]?>"></td>
	</tr>
	<tr>
		<td>Data9</td><td>:</td><td><input name="data9" value="<?=$row[Data9]?>"></td>
	</tr>
	<tr>
		<td>Data10</td><td>:</td><td><input name="data10" value="<?=$row[Data10]?>"></td>
	</tr>
	<tr>
		<td>ResData</td><td>:</td><td><input name="resdata" value="<?=$row[ResData]?>"></td>
	</tr>
	<tr>
		<td>PrevState</td><td>:</td><td><input name="prevstate" value="<?=$row[PrevState]?>"></td>
	</tr>
	</table>
	<input type="button" value="Update" onclick="if(confirm('Overwrite?'))postform(document.form1, 'warevent.php?i=<?=$HTTP_GET_VARS[i]?>')">
<?
	}else{
		echo "<p>No data found.</p>";
	}
}//end if($wid!="")
?>
 </form>
</body>
</html>