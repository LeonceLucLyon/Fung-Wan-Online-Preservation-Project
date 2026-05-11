<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$aid=$HTTP_POST_VARS[aid];
if($HTTP_GET_VARS[aid])$aid=$HTTP_GET_VARS[aid];
if($aid=="")
{
	$aid=$HTTP_SESSION_VARS['aid'];
}
else
{
	$HTTP_SESSION_VARS['aid']=$aid;
}

if(!has_perm($HTTP_SESSION_VARS['userid'], "", "motd", "") )
{
	die("Access denied.");
}

$htmlWc="<select name=\"wc_ip\"><option value=\"\">--Select--</option>";
if($aid!="")
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$aid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid authsys, $aid.");
	mysql_free_result($rsSvr);
	$dbAs = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbAs);
	$rs_wctrlr = mysql_query("SELECT Name, Address FROM server ORDER BY Name", $dbAs) or die(mysql_error($dbAs));
	while($row = mysql_fetch_row($rs_wctrlr))
	{
		$htmlWc.= "<option value=\"{$row[1]}\">{$row[0]}</option>";
	}
}
$htmlWc .= "</select>";

$arAs = get_accessible_server($HTTP_SESSION_VARS['userid'], 'as');


switch($HTTP_GET_VARS['a'])
{
	case 's':
		$wc_ip =  $HTTP_POST_VARS['wc_ip'];

		$motd_u8 = stripslashes($HTTP_POST_VARS["para0"]);
		$motd_u8 .= chr(0);
		$motd_u8 = str_replace("\r\n", "\\n", $motd_u8);
		$motd_u16 = u8tou16($motd_u8);

		$tmpfname =  tempnam("/tmp", "sendwctrlr");
		$tmpf = fopen($tmpfname, "w");
		fwrite($tmpf, $motd_u16);
		fclose($tmpf);

		$shell_cmd = "$SENDWCTRLR $wc_ip $tmpfname";
		unset($shell_out);
		exec($shell_cmd, $shell_out, $ret);

		unlink ($tmpfname);

		if($ret)
		{
			$after = join("\n", $shell_out);
			
			echo nl2br($after);
		}

		echo "<p><a href=\"wbroadcast.php\">Back</a></p>";
		//header("Location: wbroadcast.php?wid={$wid}");
		exit;
		break;
}


$htmlAs="<select name=aid onChange=\"postform(document.form1,'wbroadcast.php?a=as')\"><option value=''></option>";
foreach($arAs as $row)
{
	$selected=($aid==$row[id])?"SELECTED":"";
	$htmlAs.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlAs.="</select>";

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
<form name="form1" method="post" action="wbroadcast.php?wid=<?=$wid?>">
<h3>World Broadcast</h3>
<br>
<table border="0">
	<tr>
		<td>Authsys</td>
		<td><?=$htmlAs?></td>
	</tr>
	<tr>
		<td>World</td>
		<td><?=$htmlWc?></td>
	</tr>
</table>
<br>
 <?php
if($aid!="")
{
?>
<script>
function chklen(n)
{
	eval("var para=document.all.para" + n)
	eval("var len=document.all.len" + n)
	eval("var left=document.all.left" + n)
	var txt1 = ''
	var txt2 = para.value
	while(txt1 != txt2)
	{
		txt1 = txt2
		txt2 = txt2.replace("\r\n","\\n")
	}
	len.innerHTML = para.value.length
	left.innerHTML = <?=$BROADCAST_MESSAGE_MAXLENGTH?> - para.value.length -1
	left.innerHTML = txt1
}
</script>
<table border="0">
	<tr valign=top>
		<td>Messsage</td>
	</tr>
	<tr valign=top>
		<td>
			<textarea name="para0" id="para0" rows=10 cols=60 onmousemove="chklen(0)" onkeyup="chklen(0)" onmouseout="chklen(0)"></textarea><br>
			Length: <span name="len0" id="len0"></span>, Remaining: <span name="left0" id="left0"></span>.
			<br>Maximum length is <?=$BROADCAST_MESSAGE_MAXLENGTH - 1?> characters.
			<br>Note: Newline takes 2 characters.
		</td>
	</tr>
</table>
<script>chklen(0)</script>
<input type="button" value="Broadcast" onclick="if(document.form1.wc_ip.value.length==0){alert('Please select game world to broadcast message.');document.form1.wc_ip.focus()}else{if(document.form1.para0.value.length>=<?=$BROADCAST_MESSAGE_MAXLENGTH?>){alert('Message exceeds data length limit.');return false}if(confirm('Broadcast message?')){document.form1.action='wbroadcast.php?wid=<?=$wid?>&a=s';document.form1.submit()}}">
<?
}
?>
</form>
</body>
</html>