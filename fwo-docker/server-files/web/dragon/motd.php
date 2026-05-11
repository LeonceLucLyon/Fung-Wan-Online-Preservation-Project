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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "motd", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}
elseif($HTTP_GET_VARS['a']=='s' && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "motd", "w") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}


switch($HTTP_GET_VARS['a'])
{
	case 'wc':
		break;
	case 's':
		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
		mysql_free_result($rsSvr);
		$wc_ip = $row_rsSvr[ip];
		$wc_db = $row_rsSvr[db];
		$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
		mysql_select_db($row_rsSvr[db], $dbWc);

		for($n=0; $n < 4; $n++)
		{
			$motd_u8 = stripslashes($HTTP_POST_VARS["para{$n}"]);
			$motd_u8 .= chr(0);
			$motd_u16 = u8tou16($motd_u8);

			$motd0 = $motd1 = $motd2 = $motd3 = $motd4 = $motd5 = $motd6 = $motd7 = $motd8 = $motd9 = "NULL";
			$part_count = ceil(strlen($motd_u16) / 500);
			for($part_i = 0; $part_i < $part_count; $part_i++)
			{
				$part_motd = "0x" . hexstring(substr($motd_u16, $part_i * 500, 500));
				eval("\$motd{$part_i} = \$part_motd;");
			}
			$query_rs1 = "DELETE FROM MOTD WHERE ID='{$n}'";
			$befores = get_str_rs($dbWc, "SELECT * FROM MOTD WHERE ID='{$n}';");
			$rs = mysql_query($query_rs1, $dbWc) or die(mysql_error($dbWc));
			

			$query_rs2 = "INSERT INTO MOTD(ID, Count, MsgText1, MsgText2, MsgText3, MsgText4, MsgText5, MsgText6, MsgText7, MsgText8, MsgText9, MsgText10) VALUES('{$n}', $part_count, $motd0, $motd1, $motd2, $motd3, $motd4, $motd5, $motd6, $motd7, $motd8, $motd9);";
			$rs = mysql_query($query_rs2, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, "SELECT * FROM MOTD WHERE ID='{$n}';");
			
		}
		$query_rs3 = "UPDATE config SET Data1 = Data1 + 1 WHERE Type=1;";
		$befores = get_str_rs($dbWc, "SELECT * FROM config WHERE Type=1;");
		$rs = mysql_query($query_rs3, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM config WHERE Type=1;");
		
		header("Location: motd.php?wid={$wid}");
		exit;
		break;
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'motd.php?a=wc')\"><option value=''></option>";
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
<form name="form1" method="post" action="motd.php?wid=<?=$wid?>">
<h3>Ingame MOTD</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{
	if(!$dbWc)
	{
		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
		mysql_free_result($rsSvr);
		$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
		mysql_select_db($row_rsSvr[db], $dbWc);
	}
	$rs = mysql_query("SELECT * FROM MOTD WHERE ID='2';", $dbWc) or die(mysql_error());
	if($rs && mysql_num_rows($rs))
	{
		$data = mysql_fetch_assoc($rs);
		$motd_simpc = u16btou8str($data['MsgText1'] . $data['MsgText2'] . $data['MsgText3'] . $data['MsgText4'] . $data['MsgText5'] . $data['MsgText6'] . $data['MsgText7'] . $data['MsgText8'] . $data['MsgText9'] . $data['MsgText10']);
		mysql_free_result($rs);
	}
	else
	{
		$motd_simplc = '';
	}

	$rs = mysql_query("SELECT * FROM MOTD WHERE ID='0';", $dbWc) or die(mysql_error());
	if($rs && mysql_num_rows($rs))
	{
		$data = mysql_fetch_assoc($rs);
		$motd_eng = u16btou8str($data['MsgText1'] . $data['MsgText2'] . $data['MsgText3'] . $data['MsgText4'] . $data['MsgText5'] . $data['MsgText6'] . $data['MsgText7'] . $data['MsgText8'] . $data['MsgText9'] . $data['MsgText10']);
		mysql_free_result($rs);
	}
	else
	{
		$motd_eng = '';
	}

	$rs = mysql_query("SELECT * FROM MOTD WHERE ID='1';", $dbWc) or die(mysql_error());
	if($rs && mysql_num_rows($rs))
	{
		$data = mysql_fetch_assoc($rs);
		$motd_tradc = u16btou8str($data['MsgText1'] . $data['MsgText2'] . $data['MsgText3'] . $data['MsgText4'] . $data['MsgText5'] . $data['MsgText6'] . $data['MsgText7'] . $data['MsgText8'] . $data['MsgText9'] . $data['MsgText10']);
		mysql_free_result($rs);
	}
	else
	{
		$motd_tradc = '';
	}
	
	$rs = mysql_query("SELECT * FROM MOTD WHERE ID='3';", $dbWc) or die(mysql_error());
	if($rs && mysql_num_rows($rs))
	{
		$data = mysql_fetch_assoc($rs);
		$motd_malay = u16btou8str($data['MsgText1'] . $data['MsgText2'] . $data['MsgText3'] . $data['MsgText4'] . $data['MsgText5'] . $data['MsgText6'] . $data['MsgText7'] . $data['MsgText8'] . $data['MsgText9'] . $data['MsgText10']);
		mysql_free_result($rs);
	}
	else
	{
		$motd_malay = '';
	}

	$selected_0 = $selected_1 = $selected_2 = "";
	eval('$selected_' . $HTTP_POST_VARS['lang'] . ' = "SELECTED";');
?>
<script>
function chklen(n)
{
	eval("var para=document.all.para" + n)
	eval("var len=document.all.len" + n)
	eval("var left=document.all.left" + n)
	if(para.value.length > 499)
	{

	}
	len.innerHTML = para.value.length
	left.innerHTML = 250 * 10 - para.value.length -1
}
</script>
<table border="0">
<!--
	<tr>
		<td>Language</td>
		<td><SELECT name="lang" id="lang" onchange="if(confirm('Reload MOTD for selected language?'))document.form1.submit()">
			<option value="0" <?=$selected_0?>>English</option>
			<option value="1" <?=$selected_1?>>Traditional Chinese</option>
			<option value="2" <?=$selected_2?>>Simplified Chinese</option>
		</SELECT>
		</td>
	</tr>
-->
	<tr valign=top>
		<td><b>English</b></td><td align=right>Length: <span name="len0" id="len0"></span>, Remaining: <span name="left0" id="left0"></span></td>
	</tr>
	<tr valign=top>
		<td colspan=2><textarea name="para0" id="para0" rows=15 cols=100 onmousemove="chklen(0)" onkeyup="chklen(0)" onmouseout="chklen(0)"><?=$motd_eng?></textarea><br><br></td>
	</tr>
	<tr valign=top>
		<td><b>Traditional Chinese</b></td><td align=right>Length: <span name="len1" id="len1"></span>, Remaining: <span name="left1" id="left1"></span></td>
	</tr>
	<tr valign=top>
		<td colspan=2><textarea name="para1" id="para1" rows=15 cols=100 onmousemove="chklen(1)" onkeyup="chklen(1)" onmouseout="chklen(1)"><?=$motd_tradc?></textarea><br><br></td>
	</tr>
	<tr valign=top>
		<td><b>Simplified Chinese</b></td><td align=right>Length: <span name="len2" id="len2"></span>, Remaining: <span name="left2" id="left2"></span></td>
	</tr>
	<tr valign=top>
		<td colspan=2><textarea name="para2" id="para2" rows=15 cols=100 onmousemove="chklen(2)" onkeyup="chklen(2)" onmouseout="chklen(2)"><?=$motd_simpc?></textarea><br><br></td>
	</tr>
	<tr valign=top>
		<td><b>Bahasa Melayu</b></td><td align=right>Length: <span name="len3" id="len3"></span>, Remaining: <span name="left3" id="left3"></span></td>
	</tr>
	<tr valign=top>
		<td colspan=2><textarea name="para3" id="para3" rows=15 cols=100 onmousemove="chklen(3)" onkeyup="chklen(3)" onmouseout="chklen(3)"><?=$motd_malay?></textarea></td>
	</tr>
</table>
<script>chklen(1);chklen(2);chklen(0);chklen(3)</script>
<input type="button" value="Save" onclick="if(document.form1.para0.value.length>250*10-1){alert('English MOTD contents exceed data length limit.');return false}if(document.form1.para1.value.length>250*10-1){alert('Traditional Chinese MOTD contents exceed data length limit.');return false}if(document.form1.para2.value.length>250*10-1){alert('Simplified Chinese MOTD contents exceed data length limit.');return false}if(confirm('Confirm overwrite?')){document.form1.action='motd.php?wid=<?=$wid?>&a=s';document.form1.submit()}">
<input type="reset" value="Undo changes" onclick="if(!confirm('Confirm Undo?'))return false;chklen(1);chklen(2);chklen(0)">
<?
}
?>
</form>
</body>
</html>
