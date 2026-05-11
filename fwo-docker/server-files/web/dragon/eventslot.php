<?php
$rpp = 21;
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

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]);
	if(!$dbWc)
	{
		$HTTP_SESSION_VARS['wid'] = "";
		echo "
			<script>
			function reload(){location.href='{$HTTP_SERVER_VARS[REQUEST_URI]}'}
			setTimeout(reload, 3000)
			</script>
			<p><font color=red>Page will be redirected in 3 seconds.</p>
		";
		die(mysql_error());
	}
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST'){
		if($HTTP_GET_VARS['a'] == 'a')
		{
			$type  = $HTTP_POST_VARS[type];
			$time = $HTTP_POST_VARS[time];
			$day = $HTTP_POST_VARS[day];
			$scene = $HTTP_POST_VARS[scene];
			$offseta = split(":", $time);
			$offset = $offseta[0] * 3600 + $offseta[1] * 60 + $offseta[2];
			$query = "INSERT INTO booking (Day, Offset, SceneID, Type) VALUES('$day', '$offset', '$scene', '$type')";
			#echo $query;
			$befores = get_str_rs($dbWc, "SELECT * FROM booking WHERE Day='$day' AND Offset='$offset' AND SceneID='$scene' AND Type='$type'");
			$rs = mysql_query($query, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, "SELECT * FROM booking WHERE Day='$day' AND Offset='$offset' AND SceneID='$scene' AND Type='$type'");
			
			header ("Location: eventslot.php");
			exit;
		}
		elseif($HTTP_GET_VARS['a'] == 'd')
		{
			$pks = $HTTP_POST_VARS[del];
			if(is_array($pks))
			{
				foreach ($pks as $pk)
				{
					$befores = get_str_rs($dbWc, "SELECT * FROM booking WHERE Indx='$pk'");
					$query = "DELETE FROM booking WHERE Indx='$pk'";
					$rs = mysql_query($query, $dbWc) or die(mysql_error($dbWc));
					$after = get_str_rs($dbWc, "SELECT * FROM booking WHERE Indx='$pk'");
					
				}
				header ("Location: eventslot.php");
				exit;
			}
		}
		elseif($HTTP_GET_VARS['a'] == 's')
		{
			$pks = $HTTP_POST_VARS[pk];
			$types  = $HTTP_POST_VARS[type];
			$times = $HTTP_POST_VARS[time];
			$days = $HTTP_POST_VARS[day];
			$scenes = $HTTP_POST_VARS[scene];
			$n = 0;
			if(is_array($pks))
			{
				foreach ($pks as $pk)
				{
					$offseta = split(":", $times[$n]);
					$offset = $offseta[0] * 3600 + $offseta[1] * 60 + $offseta[2];
					$query = "UPDATE booking SET Day='$days[$n]', SceneID='$scenes[$n]', Type='$types[$n]', Offset='$offset' WHERE Indx='$pk';";
					$befores = get_str_rs($dbWc, "SELECT * FROM booking WHERE Indx='$pk'");
					$rs = mysql_query($query, $dbWc) or die(mysql_error($dbWc));
					$after = get_str_rs($dbWc, "SELECT * FROM booking WHERE Indx='$pk'");
					
					$n++;
				}
				header ("Location: eventslot.php");
				exit;
			}
		}
	}

	switch($HTTP_GET_VARS['a'])
	{
	//	case 'wc':
			//if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
	//		break;
		default:

			$pg = $HTTP_GET_VARS[p];

			$rs = mysql_query("SELECT COUNT(1) FROM booking", $dbWc) or die(mysql_error($dbWc));
			list($total_booking) = mysql_fetch_row($rs);
			$lastpg = ceil($total_booking / $rpp);
			if($pg > $lastpg)
			{
				$pg = $lastpg;
			}
			elseif($pg < 1)
			{
				$pg = 1;
			}
			$start = ($pg -1) * $rpp;

			//$cond_usernm = $HTTP_POST_VARS[usernm_0]!=""? " AND Username LIKE \"$HTTP_POST_VARS[usernm_0]\"":"";
			$query = "SELECT * FROM booking WHERE 1 $cond_charid $cond_charnm $cond_usernm ORDER BY Indx, Day, Offset LIMIT $start, $rpp";
			$rs = mysql_query($query, $dbWc) or die(mysql_error($dbWc));
			break;
	}
}
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'eventslot.php?a=wc')\"><option value=''></option>";
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
<h3>War Event Booking Slots</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{
?>
<table border="1" cellspacing=0>
	<tr>
		<td>#</td>
		<td>Day</td>
		<td>Time</td>
		<td>Scene</td>
		<td>Type</td>
		<!--td>Taken</td-->
		<td>Delete</td>
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

			$time = gmdate("H:i:s", $row[Offset]);
			$time = "<input name=\"time[]\" value=\"$time\" size=\"8\" maxlength=8>";
			$scenetext = $scene_name[$row[SceneID]];
			$scene = "<input name=\"scene[]\" value=\"$row[SceneID]\" size=\"3\">$scenetext";

			$selected1 = $selected2 = $selected3 = "";
			eval("\$selected{$row[Type]} = 'SELECTED';");
			$type_select = "<select name=\"type[]\"><option value=\"1\" $selected1>Skirmish</option>
				<option value=\"2\" $selected2>Relic Capture</option>
				<option value=\"3\" $selected3>Town Capture</option>
			</select>";

			$selected1 = $selected2 = $selected3 = $selected4 = $selected5 = $selected6 = $selected0 = "";
			eval("\$selected{$row[Day]} = 'SELECTED';");
			$day_select = "<select name=\"day[]\"><option value=\"1\" $selected1>Monday</option>
				<option value=\"2\" $selected2>Tuesday</option>
				<option value=\"3\" $selected3>Wednesday</option>
				<option value=\"4\" $selected4>Thursday</option>
				<option value=\"5\" $selected5>Friday</option>
				<option value=\"6\" $selected6>Saturday</option>
				<option value=\"0\" $selected0>Sunday</option>
				</select>";

			if($row[Time] == 0)
			{
				$taken = "(available)";
			}
			else
			{
				$taken = gmdate("d/m/y H:i:s", $row[Time]);
			}

			echo "<tr><td><input type=\"hidden\" name=\"pk[]\" value=\"$row[Indx]\">$n</td><td>$day_select</td><td>$time</td><td>$scene</td><td>$type_select</td><!--td>$taken</td--><td><input type=\"checkbox\" name=\"del[]\" value=\"$row[Indx]\"> </td></tr>";
		}//end while
	}//end if(mysql_num_rows($rs) > 0)
	else
	{
		echo "<tr><td colspan=9><font color=red><b>No matched queries.</b></font></td></tr>";
	}
	echo "</table>";

	function mklink($n){
		global $pg;
		$tag = $n == $pg?"<b>$n</b>":"$n";
		return "<a href=\"eventslot.php?p={$n}\">$tag</a>";
	}

	if($lastpg > 0)
	{
		$s1 = -10;
		$s2 = 10;

		$html_page = "Page: ";

		if($page + $s1 > 1) $html_page .= mklink(1) . "... ";
		for($n = $s1; $n < $s2; $n++)
		{
			$pp = $pg + $n;
			if($pp > $lastpg) break;
			if($pp > 0 )
				$html_page .= mklink($pp) . " ";
		}
		if($pg + $s2 < $lastpg) $html_page .= " ..." . mklink($lastpg);

		$html_results .= $html_page;
	}
	echo $html_results . "<br>";
?>
<input type="button" value="Save All" onclick="if(confirm('Overwrite all?'))postform(document.form1,'eventslot.php?a=s')">
<input type="button" value="Delete Checked Entries" onclick="if(confirm('Delete checked entries?'))postform(document.form1,'eventslot.php?a=d')">
<input type="Reset" value="Reset" onclick="return(confirm('Undo unsaved changes?'))">
<br><font color=red>* Note: Modification on an existing entry will require server restart in order to apply the changes.</font>
 </form>
 <hr>
<form method="POST" action="eventslot.php?a=a" name="form3">
 <input name="new" value="1" type="hidden">
 <b>New Booking Slot</b>
 <table>
	<tr>
		<td>Day</td><td>:</td><td><select name="day">
			<option value="">--Select--</option>
			<option value="1">Monday</option>
			<option value="2">Tuesday</option>
			<option value="3">Wednesday</option>
			<option value="4">Thursday</option>
			<option value="5">Friday</option>
			<option value="6">Saturday</option>
			<option value="0">Sunday</option>
		</select>
		</td>
	</tr>
	<tr>
		<td>Time</td><td>:</td><td><input name="time"> (format: H:M:S, eg 23:59:59)</td>
	</tr>
	<tr>
		<td>SceneID</td><td>:</td><td><input name="scene"></td>
	</tr>
	<tr>
		<td>Type</td><td>:</td><td><select name="type">
			<option value="">--Select--</option>
			<option value="1">Skirmish</option>
			<option value="2">Relic Capture</option>
			<option value="3">Town Capture</option>
		</td>
	</tr>
</table>
<input type="submit" value="Add New" onclick="with(document.form3){var a=day.value.length && scene.value.length && time.value.length && type.value.length; if(!a){alert('New entry is not complete.'); return false}}">
<?
}
?>
 </form>
 </body>
</html>
