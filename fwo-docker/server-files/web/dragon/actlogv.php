<?php
require('auth.php');
require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "*", "r"))
{
	die("Access denied.");
}

$page = $HTTP_GET_VARS[p];
if(!$page) $page=1;
$rowpp = 100;
$start_row = ($page - 1) * 100 + 1;

$ar_act = array(
"1"=>"add admin account",
"update admin account",
"delete admin account",
"add admin permission",
"update admin permission",
"delete admin permission",
"add game account",
"update game account",
"delete game account",
"update pchar",
"update pchar stats",
"update pchar quest",
"update pchar inv",
"update pchar power",
"update pchar skill",
"update pchar effect",
"update pchar stance",
"suspend pchar",
"update game event",
"activate game event",
"de-activate game event",
"update MOTD",
"add game db host",
"update game db host",
"delete game db host",
"enable scene",
"disable scene",
"start zs",
"stop zs",
"broadcast to zoneserver",
"kick pchar",
"force kick pchar",
"add banned name",
"delete banned name",
"reset quest",
"start pchar chatlog",
"stop pchar chatlog",
"delete pchar chatlog",
"update clan",
"add event slot",
"update event slot",
"delete event slot",
"update arena score",
"add game event",
"delete game event",
"update unique item",
"update item",
"clone character",
"update GM command access right",
"update skill",
"update effect",
"update power",
"update stance",
"update npc attribute",
"update spawn pt dyn",
"update npc attrib dyn",
"update guild",
"delete guild",
"update Caps value",
"update item status",
);

asort($ar_act);

$_POST = $HTTP_POST_VARS;
$p_act = trim($_POST[act]);
$p_date = trim($_POST[date]);
$p_date2 = trim($_POST[date2]);
$p_time = trim($_POST[time]);
$p_time2 = trim($_POST[time2]);
$p_usr = trim($_POST[usr]);
$p_svr = trim($_POST[svr]);
$p_cmd = trim($_POST[cmd]);

if(strlen($p_time)==0) $p_time = "00:00:00";
if(strlen($p_time2)==0) $p_time2 = "23:59:59";
if(strlen($p_date)>0 && strlen($p_date2)==0) $p_date2=$p_date;

strlen($p_act) and $cond_act = "AND act = \"{$ar_act[$p_act]}\"";
strlen($p_date) and $cond_date = "AND datetime >= \"$p_date $p_time\" AND datetime <= \"$p_date2 $p_time2\"";
strlen($p_usr) and $cond_usr = "AND user = \"$p_usr\"";
strlen($p_svr) and $cond_svr = "AND server = \"$p_svr\"";
strlen($p_cmd) and $cond_cmd = "AND cmd LIKE \"$p_cmd\"";

if($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST')
{
	$sql = "SELECT count(1) FROM act_log WHERE 1 $cond_act $cond_date $cond_usr $cond_svr $cond_cmd";
	$rs = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($rs);
	$total_found = $row[0];
	mysql_free_result($rs);

	$lastpage = ceil($total_found / $rowpp);
	if($page > $lastpage) $page = $lastpage;

	if($total_found == 0)
	{
		$html_results = "<font color=red><b>No match queries.</b></font>";
	}
	else
	{
		$start_row = ($page - 1) * $rowpp;
		$sql = "SELECT id, datetime, user, act, server, db, tbl FROM act_log WHERE 1 $cond_act $cond_date $cond_usr $cond_svr $cond_cmd ORDER BY id LIMIT $start_row, $rowpp";
		$rs = mysql_query($sql) or die(mysql_error());

		$html_results = "<table cellspacing=1 cellpadding=2 bgcolor=\"black\"><tr bgcolor=\"#CCCCCC\"><td>Date</td><td>Time</td><td>User</td><td>Action</td><td>Server</td><td>DB.Table</td><td>&nbsp;</td></tr>";
		while($row = mysql_fetch_assoc($rs))
		{
			$date = substr($row[datetime], 0, 4) . "-" . substr($row[datetime], 4, 2) . "-" . substr($row[datetime], 6, 2);
			$time = substr($row[datetime], 8, 2) . ":" . substr($row[datetime], 10, 2) . ":" . substr($row[datetime], 12, 2);
			if($date == $date_prev && $time == $time_prev && $row[user] == $user_prev)
			{

			}
			else
			{
				$user_prev = $row[user];
				$date_prev = $date;
				$time_prev = $time;
				$color = "white"; //$color=="white"?"#e0e0e0":"white";
			}
			$html_results .= "<tr onmouseover=\"this.className='hl'\" onmouseout=\"this.className=''\" bgcolor=\"$color\"><td>{$date}</td><td>{$time}</td><td>{$row[user]}</td><td>{$row[act]}</td><td>{$row[server]}</td><td>{$row[db]}.{$row[tbl]}</td><td><a href=\"actlogd.php?i={$row[id]}\" target=\"_blank\">Details</a></td></tr>";
		}
		$html_results .= "</table>";
		mysql_free_result($rs);

		function mklink($n){
			global $page;
			$tag = $n == $page?"<b>$n</b>":"$n";
			return "<a href=\"javascript:postform(document.form1, 'actlogv.php?p={$n}')\">$tag</a>";
		}

		if($lastpage > 0)
		{
			$s1 = -10;
			$s2 = 10;

			$html_page = "Page: ";

			if($page + $s1 > 1) $html_page .= mklink(1) . "... ";
			for($n = $s1; $n < $s2; $n++)
			{
				$pp = $page + $n;
				if($pp > $lastpage) break;
				if($pp > 0 )
					$html_page .= mklink($pp) . " ";
			}
			if($page + $s2 < $lastpage) $html_page .= " ..." . mklink($lastpage);

			$html_results .= $html_page;
		}
	}
}

foreach ($ar_act as $key=>$val)
{
	$SELECTED = ($key == $p_act)? "SELECTED":"";
	$html_actionlist .= "<option value=\"$key\" $SELECTED>$val</option>";
}

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
<h3>Game Admin Log</h3>
<form name="form1" method="post" action="">
<table border=1 cellspacing=0>
	<tr>
		<td>Action</td>
		<td>
			<select name="act">
				<option> </option>
				<?=$html_actionlist?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Date</td>
		<td>
			<table cellspacing=0 cellpadding=0>
			<tr>
			<td>From&nbsp;</td>
			<td>
				<input type="text" name="date" size="12" value="<?=$p_date?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.date',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a>
				Time:
				<input type="text" name="time" size="8" value="<?=$p_time?>" maxlength=8>
			</td>
			</tr>
			<tr>
			<td>
			To
			</td>
			<td>
			<input type="text" name="date2" size="12" value="<?=$p_date2?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.date2',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a>
			Time: <input type="text" name="time2" size="8" value="<?=$p_time2?>" maxlength=8>
			</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>User</td>
		<td>
			<input name="usr" value="<?=htmlentities(stripslashes($p_usr))?>">
		</td>
	</tr>
	<tr>
		<td>Server</td>
		<td>
			<input name="svr" value="<?=htmlentities(stripslashes($p_svr))?>">
		</td>
	</tr>
	<!--
	<tr>
		<td>Command</td>
		<td>
			<input name="cmd" value="<?=htmlentities(stripslashes($p_cmd))?>">
		</td>
	</tr>
	-->
</table>
<input type="Submit" value="Search">
<p>
<?=$html_results?>
</form>
</body>
</html>
