<?php
require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$gm_rites=Array(
			35328 => array("Purge", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35329 => array("Kill", array(-1 => "No", 0 => "Yes")),
			35330 => array("Item", NULL, "-1 = disabled; 0 = give to individual only; >0 = maximum radius(50=1meter); 9999 = all characters in scene"),
			35331 => array("XP", array(-1 => "No", 0 => "Yes")),
			35333 => array("Spawn", NULL, "maximum no of mobs spawned. -1 = disabled"),
			35334 => array("Tag", array(-1 => "No", 0 => "Yes")),
			35336 => array("Goto", array(-1 => "No", 0 => "Yes")),
			35337 => array("Kick", NULL, "maximum duration in minutes. -1 = disabled"),
			35338 => array("Bring", array(-1 => "No", 0 => "Yes")),
			35339 => array("Invisible/Vis", array( -1 => "No", 0 => "Yes")),
			35341 => array("Spoint Max/On/Off", array( -1 => "No", 0 => "Yes")),
			35346 => array("Teleport", array(-1 => "No", 0 => "Yes")),
			35347 => array("Combat", array(-1 => "No", 0 => "Yes")),
			35348 => array("GetFlags", array(-1 => "No", 0 => "Yes")),
			35349 => array("Spoint Show/Hide", array(-1 => "No", 0 => "Yes")),
			35351 => array("N/A"),
			35353 => array("Spoint Status", array(-1 => "No", 0 => "Yes")),
			35354 => array("Clone", array(-1 => "No", 0 => "Yes")),
			35356 => array("Remove", array(-1 => "No", 0 => "Yes")),
			35357 => array("HP", NULL, "-1 = disabled; 0 = affect self only; >0 = maximum radius(50=1meter); 9999 = all characters in scene"),
			35358 => array("CP", NULL, "-1 = disabled; 0 = affect self only; >0 = maximum radius(50=1meter); 9999 = all characters in scene"),
			35359 => array("CheckInv	", array(-1 => "No", 0 => "Yes")),
			35360 => array("DelInv", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35361 => array("Res", array(-1 => "No", 0 => "Yes")),
			35363 => array("Charpower", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35364 => array("Charstance", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35365 => array("Charskill", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35366 => array("Charpoints", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35367 => array("Scene", array(-1 => "No", 0 => "affect self only", 1 => "can affect target")),
			35369 => array("Tap", array(-1 => "No", 0 => "Yes")),
			35370 => array("Mute", NULL, "-1 = disabled; 0 = affect individual only, >0 = maximum radius(50=1meter); 9999 = all characters in scene"),
			35371 => array("Find PC", array(-1 => "No", 0 => "Yes")),
			35372 => array("Find NPC", array(-1 => "No", 0 => "Yes")),
			35376 => array("Scene IGR", array(-1 => "No", 0 => "Yes")),
			35377 => array("GScene", array(-1 => "No", 0 => "Yes")),
			75139 => array("Speak to Clan Advisor", array(-1 => "No", 0 => "Yes")),
			80033 => array("Speak to IGR Master", array(-1 => "No", 0 => "Yes")),
		);



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


if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="1" || $HTTP_GET_VARS[a]=="0") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}

$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
if(mysql_num_rows($rsSvr) > 0)
{
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_connect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	if($HTTP_GET_VARS[a]=='s')
	{
		$eventids = $HTTP_POST_VARS['eventids'];
		foreach($eventids as $eventid)
		{
			$ar_rank1 = $HTTP_POST_VARS["ar_{$eventid}_1"];
			$ar_rank2 = $HTTP_POST_VARS["ar_{$eventid}_2"];
			$ar_rank3 = $HTTP_POST_VARS["ar_{$eventid}_3"];
			$ar_rank4 = $HTTP_POST_VARS["ar_{$eventid}_4"];
			$ar_rank5 = $HTTP_POST_VARS["ar_{$eventid}_5"];

			if($eventid == 35349) // 35351 follows 35349
			{
				$query_rs = "UPDATE gmaccess SET Rank1='$ar_rank1', Rank2='$ar_rank2', Rank3='$ar_rank3', Rank4='$ar_rank4', Rank5='$ar_rank5' WHERE EventID='35351'";
				$befores = get_str_rs($dbWc, "SELECT * FROM gmaccess WHERE EventID='35351'");
				mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, "SELECT * FROM gmaccess WHERE EventID='35351'");
				
			}
			elseif($eventid == 35351) //skip
			{
				continue;
			}

			$query_rs = "UPDATE gmaccess SET Rank1='$ar_rank1', Rank2='$ar_rank2', Rank3='$ar_rank3', Rank4='$ar_rank4', Rank5='$ar_rank5' WHERE EventID='{$eventid}'";

			$befores = get_str_rs($dbWc, "SELECT * FROM gmaccess WHERE EventID='{$eventid}'");
			mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, "SELECT * FROM gmaccess WHERE EventID='{$eventid}'");
			
		}

		header("Location: gmaccess.php");
		exit();
	}

	$rsScene = mysql_query("SELECT * FROM gmaccess ORDER BY EventID", $dbWc) or die(mysql_error());
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
$htmlWc="<select name=wid onchange=\"postform(document.form1,'gmaccess.php')\"><option value=''></option>";
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
function saveid(i)
{
	if(eval("!document.form1.mark_"+i+".value"))
	{
		eval("document.form1.mark_"+i+".value=1")
		document.form1.ids.value+=i+"|"
	}
}
function doCheckAll(nm,v){
	with (document.form1)
		for (var i=0; i < elements.length; i++)
			if (elements[i].type == 'checkbox' && elements[i].name == nm)elements[i].checked = v
}

//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>GM Command Access Control</h3>
(World Controller: <?=$htmlWc?>)
<!--br><input type=button value=Refresh onclick="location.reload()"-->
<br><br>
<?
$current_server_id=0;
$n=0;
$html_access= "";
$yesno = array(0=>'<font style="background-color:red;color:white">No</font>', 'Yes');
if(is_resource($rsScene))
{
	while($row=mysql_fetch_assoc($rsScene))
	{
		if($row[EventID] == '35351') continue;
		$n++;
		$desc = $gm_rites[$row[EventID]][0];
		$description = $gm_rites[$row[EventID]][2];
		if(is_array($gm_rites[$row[EventID]][1]))
		{
			$i = 0;

			for($rank = 1; $rank<=5; $rank++)
			{
				$opt = "";
				reset($gm_rites[$row[EventID]][1]);
				while(list($key, $val) = each($gm_rites[$row[EventID]][1]))
				{
					$i++;
					$selected = $row["Rank{$rank}"]==$key?"SELECTED":"";
					$opt .= "<option value='$key' $selected>$val</option>";
				}
				eval("\$opt{$rank} = \"\\n<select name='ar_{$row[EventID]}_$rank'>$opt</select>\";");
			}
		}
		else
		{
			$opt = "<input name='ar_{$row[EventID]}_\$rank' size=2 maxlength=4 value='\" . \$row[\"Rank\$rank\"] . \"'>";

			$rank = 1;
			eval("\$opt{$rank} = \"$opt\";");
			$rank = 2;
			eval("\$opt{$rank} = \"$opt\";");
			$rank = 3;
			eval("\$opt{$rank} = \"$opt\";");
			$rank = 4;
			eval("\$opt{$rank} = \"$opt\";");
			$rank = 5;
			eval("\$opt{$rank} = \"$opt\";");

		}
		$html_access .= "<tr onmouseover=\"this.className='hl'\" onmouseout=\"this.className=''\"><td>$n<input type=hidden name='eventids[]' value='{$row[EventID]}'></td><td>$desc</td><td>$opt1</td><td>$opt2</td><td>$opt3</td><td>$opt4</td><td>$opt5</td></tr>";

		if(strlen($description)>0) $html_access .= "<tr><td colspan=7 align=right>(for above settings, $description)</td></tr>";
	}

	if(strlen($html_access) == 0)
	{
		echo "<font color=red>No record found.</font>";
	}
	else
	{
		echo "
			<table border=\"1\" cellspacing=0>
			<tr>
				<th rowspan=2>#</th>
				<th rowspan=2>GM CMD</th>
				<th colspan=5>GM</th>
			</tr>
			<tr>
				<th>Rank 1</th>
				<th>Rank 2</th>
				<th>Rank 3</th>
				<th>Rank 4</th>
				<th>Rank 5</th>
			</tr>
			$html_access
			</table>
			<input type=button value=\"Save\" onclick=\"if(confirm('Confirm overwrite?'))postform(document.form1,'gmaccess.php?a=s')\"> <input type=reset onclick=\"return(confirm('Undo all changes?'))\">
			";
	}
}
?>
</form>
</body>
</html>