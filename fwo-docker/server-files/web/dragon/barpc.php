<?php
require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

//if($HTTP_SESSION_VARS["wc"]=="")
$wid=$HTTP_GET_VARS["wid"];
if($wid=="")
{
	die("World controller not set");
}
else
{

	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
	{
		die("Access denied.");
	}
	elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w"))
	{
		die("Access denied. Read-Only.");
	}

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid world controller");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);


	if($HTTP_GET_VARS[a]=="s")
	{

		if($HTTP_POST_VARS['suspend']=='0'){
			$lockouttime = 0;
		}else{
			$lockouttime = $HTTP_POST_VARS['lockouttime'];
		}

		$query_rs = "UPDATE pcharacter SET
			LockOutTime='{$lockouttime}'
			WHERE CharID='{$HTTP_GET_VARS[i]}'";

		$rs_logon = mysql_query("SELECT * FROM authenticated WHERE CharID='{$HTTP_GET_VARS[i]}'", $dbWc) or die(mysql_error());
		$is_logon=mysql_num_rows($rs_logon);
		mysql_free_result($rs_logon);

		if($is_logon && $HTTP_GET_VARS[force]!=1)
		{
			echo "<form name=form1 action=\"{$HTTP_SERVER_VARS[REQUEST_URI]}&force=1\" method='Post'>";
			echo generate_form('',$HTTP_POST_VARS);
			echo "<input type=button value='Force Save' onclick='if(confirm(\"Do not force save if the character is being used or this will cause data error.\"))document.form1.submit()'></form>";
			//post_form('document.form1',$HTTP_SERVER_VARS[REQUEST_URI]."&force=1");
			die("game character is being used, write access deny");
		}

		$befores = get_str_rs($dbWc, "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after   = get_str_rs($dbWc, "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}';");
		

		header("Location: barpc.php?i={$HTTP_GET_VARS[i]}&wid={$wid}");
		exit();
	}

	$query_rs = "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
}

$y = date('Y',$row[LockOutTime]);
$m = date('n',$row[LockOutTime]);
$d = date('j',$row[LockOutTime]);
$h = date('G',$row[LockOutTime]);
$mi = date('i',$row[LockOutTime]);
$s = date('s',$row[LockOutTime]);


?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript" src="common.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>

<body onload="showdt()">
<script language="javascript">
var d = <?=$row[LockOutTime] * 1000 ?>;
function dateadd(i, n, d){
	switch(i){
		case 's':
			return d + n * 1000
		case 'n':
			return d + (n * 60000)
		case 'h':
			return d + (n * 3600000)
		case 'd':
			return d + (n * 86400000)
		case 'y':
			n *= 12
		case 'm':
			var D = new Date()
			for(;n > 0;n--){
				D.setTime(d)
				switch(D.getMonth()){
					case 0:
					case 2:
					case 4:
					case 6:
					case 7:
					case 9:
					case 11:
						d += 31 * 86400000
						break
					case 3:
					case 5:
					case 8:
					case 10:
						d += 30 * 86400000
						break
					case 1:
						if((D.getUTCFullYear() % 4 == 0 && D.getUTCFullYear() % 100 != 0) || (D.getYear() % 400 == 0))
							d += 29 * 86400000
						else
							d += 28 * 86400000
				}
			}
	}
	return d
}
function showdt(){
	var DD = new Date(d);
	with(document.form1){
		y.value=DD.getFullYear()
		m.value=parseInt(DD.getMonth()) + 1
		da.value=DD.getDate()
		h.value=DD.getHours()
		mi.value=DD.getMinutes()
		s.value=DD.getSeconds()
		lockouttime.value = Math.floor(d / 1000)
	}
}
function caldt(){
	with(document.form1){
		d = dateadd(i.value, n.value, d)
	}
	showdt()
}
function chkdt(){
	with(document.form1)
		var DT = new Date(y.value, parseInt(m.value)-1, da.value, h.value, mi.value, s.value)
		d = DT.getTime()
		showdt()
}
function settd(){
	var DT = new Date()
	d = DT.getTime()
	showdt()
}
function setdt(end,type){
	with(document.form1){
		if(end)
			switch(type){
				case 'y':
					m.value=12
				case 'm':
					switch(parseInt(m.value)){
						case 1:
						case 3:
						case 5:
						case 7:
						case 8:
						case 10:
						case 12:
							da.value=31
							break
						case 4:
						case 6:
						case 9:
						case 11:
							da.value=30
							break
						case 2:
							if( (parseInt(y.value) % 4 == 0 && parseInt(y.value) % 100 != 0) || (parseInt(y.value) % 400 == 0) )
								da.value = 29
							else
								da.value = 28
					}
				case 'd':
					h.value=23
				case 'h':
					mi.value=59
					s.value=59
			}
		else
			switch(type){
				case 'y':
					m.value=1
				case 'm':
					da.value=1
				case 'd':
					h.value=0
				case 'h':
					mi.value=0
					s.value=0
			}
	}
	chkdt()
}

</script>
<h3>Suspend Player Character</h3>
(Worldcontroller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post">
  <table border="1" cellspacing=0>
    <tr>
      <td>User Name</td>
      <td><input name="username" type="text" id="username" value="<?=$row[Username]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>Char ID</td>
      <td><input name="CharID" type="text" id="CharID" value="<?=$row[CharID]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>Char Name</td>
      <td><input name="charactername" type="text" id="charactername" value="<?=htmlspecialchars(U16btoU8str($row[CharacterName]))?>" readonly="yes"></td>
    </tr>
	<tr>
		<td valign=top>Suspend Character</td>
		<td>
			<input type=radio name=suspend onclick="datetime.style.display=''" <?if($row[LockOutTime]>0)echo "CHECKED"?> value=1>Yes
			<input type=radio name=suspend onclick="datetime.style.display='none'" <?if($row[LockOutTime]==0)echo "CHECKED"?> value=0>No
		</td>
	</tr>
	</table>
			<div id=datetime <?if($row[LockOutTime]==0)echo "style=\"display:none\""?>>
			<b>Lock Out Time</b>
			<table border=1 cellspacing=0>

				<tr><td>Year</td><td> <input size=4 name=y value=<?=$y?> onblur="chkdt()"><input type=button value="Beginning of year" onclick="setdt(0,'y')"> <input type=button value="End of year" onclick="setdt(1, 'y')"> </td></tr>
				<tr><td>Month</td><td> <input size=4 name=m value=<?=$m?> onblur="chkdt()"> <input type=button value="Beginning of mth" onclick="setdt(0,'m')"> <input type=button value="End of mth" onclick="setdt(1,'m')"> </td></tr>
				<tr><td>Day</td><td> <input size=4 name=da value=<?=$d?> onblur="chkdt()"> <input type=button value="Beginning of day" onclick="setdt(0,'d')"> <input type=button value="End of day" onclick="setdt(1,'d')"> </td></tr>
				<tr><td>Hour</td><td> <input size=4 name=h value=<?=$h?> onblur="chkdt()"> </td></tr>
				<tr><td>Minute</td><td> <input size=4 name=mi value=<?=$mi?> onblur="chkdt()"> <input type=button value="set current date/time" onclick="settd()"></td></tr>
				<tr><td>Second</td><td> <input size=4 name=s value=<?=$s?> onblur="chkdt()"> </td></tr>
				<tr><td>Increase</td><td> <select name=i><option value="y">Year</option><option value="m">Month</option><option value="d" selected>Day</option><option value="h">Hour</option><option value="n">Minute</option><option value="s">Second</option></select> by <input size=4 name=n> <input type=button value=Calculate onclick="caldt()"></td></tr>
			</table>
			</div>

  </table>
	<input type='hidden' name='lockouttime' value='<?=$row[LockOutTime]?>'>
	<input type="reset" name="Reset" value="Reset">
	<input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'barpc.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
  </p>
</form>
</body>
</html>
