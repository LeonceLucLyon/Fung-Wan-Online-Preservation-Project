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
		$charactername_u16 = U8toU16(trim(stripslashes($HTTP_POST_VARS[charactername])));
//		$charactername_u16 = substr_replace(str_repeat("\0", 38), $charactername_u16, 0, strlen($charactername_u16));
		$hashvalue = sprintf("%u", __crc32_string($charactername_u16));

		$rs = mysql_query("SELECT CharacterName, CharID, Username FROM pcharacter WHERE HashValue='{$hashvalue}' AND CharID<>'{$HTTP_GET_VARS[i]}';", $dbWc) or die(mysql_error());
		if(mysql_num_rows($rs) > 0)
		{
			$row_chr = mysql_fetch_assoc($rs);
			die("Character name, <a href='chgchrnm.php?i={$row_chr['CharID']}&wid={$wid}' target='_blank'>{$HTTP_POST_VARS['charactername']}</a>, is used by {$row_chr['Username']}.");
			mysql_free_result($rs);
		}

		$charnm = '0x'. hexstring($charactername_u16) . '0000';
		$query_rs = "UPDATE pcharacter SET
			CharacterName=$charnm,
			HashValue='{$hashvalue}'
			WHERE CharID='{$HTTP_GET_VARS[i]}'";

		$rs_logon = mysql_query("SELECT 1 FROM authenticated WHERE CharID='{$HTTP_GET_VARS[i]}'", $dbWc) or die(mysql_error());
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
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}';");
		

		$query_rs3 = "UPDATE config SET Data2 = Data2 + 1 WHERE Type=1;";
		$befores = get_str_rs($dbWc, "SELECT * FROM config WHERE Type=1;");
		$rs = mysql_query($query_rs3, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM config WHERE Type=1;");
		

		//$rs = mysql_query("UPDATE ", $dbWc) or die(mysql_error($dbWc));

		header("Location: chgchrnm.php?i={$HTTP_GET_VARS[i]}&wid={$wid}");
		exit();
	}

	$query_rs = "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red>No matched queries.</font>");
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
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
<h3>Change Player Character Name</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post" action="">
   <p>Properties: <a href="pcharacter.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Character</a>
    | <a href="pcharstat.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Stat</a> | <a href="charinv.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Inventory</a>
    | <a href="powerlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Power</a> |
	<a href="skilllist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Skill</a> |
	<a href="effectlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Effect</a>
    | <a href="stancelist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Stance</a>
    | <a href="questdata.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Quest</a>
</p>
	<font color=red><b>Warning: You are changing the name of a character, please do not do this that often as the names of characters are saved on the players' computers and will need to reload if you change the names. This could cause significant network load.</b></font>
	<table border=1 cellspacing=0>
		<tr>
			<td>User Name</td>
			<td><?=$row[Username]?></td>
		</tr>
		<tr>
			<td>Char ID</td>
			<td><?=$row[CharID]?></td>
		</tr>
		<tr>
			<td>Char Name</td>
			<td><input name="charactername" maxlength=20 type="text" id="charactername" value="<?=htmlspecialchars(U16btoU8str($row[CharacterName]))?>"></td>
		</tr>
	</table>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'chgchrnm.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
</form>
</body>
</html>
