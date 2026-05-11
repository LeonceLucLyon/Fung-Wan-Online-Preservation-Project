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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}
elseif( ($HTTP_GET_VARS[a]=="i" || $HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w") )
{
	die("Access denied. Read-Only.");
}

$page = $HTTP_GET_VARS[p];
$rowpp = $HTTP_POST_VARS[rowpp];
$rowpp = 60;
if(!$page) $page=1;

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'banname.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	if($wid==$row[id])
	{
		$selected="SELECTED";
//		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
//		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
//		mysql_free_result($rsSvr);
		$wc_ip = $row[ip];
		$wc_db = $row[db];
		$wc_ver = $row[version];
		$dbWc = mysql_pconnect($row[ip], $row[dbuser], $row[dbpasswd]);
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
		mysql_select_db($row[db], $dbWc);
	}
	else
	{
		$selected="";
	}

	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);

if($wid)
{
	switch($HTTP_GET_VARS['a'])
	{
		case 'wc':
			if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
			header("Location: banname.php");
			exit();
			break;
	/*
		case 'as':
			if($HTTP_POST_VARS[as_id]!='')$HTTP_SESSION_VARS['as']=$HTTP_POST_VARS[as_id];
			header("Location: banname.php");
			exit();
			break;
	*/
		case 'd':
			$hexname = $HTTP_GET_VARS['h'];
			$query_rs = "DELETE FROM bannames WHERE STRCMP(CharacterName, 0x{$hexname}) = 0;";
			$befores = get_str_rs($dbWc, "SELECT * FROM bannames WHERE STRCMP(CharacterName, 0x{$hexname}) = 0;");
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, "SELECT * FROM bannames WHERE STRCMP(CharacterName, 0x{$hexname}) = 0;");
			
			break;

		case 'i':
			$hexname = hexstring(U8toU16($HTTP_POST_VARS['character_name']));
			if($wc_ver >= "1.2")
			{
				$hashvalue = hash_name(U8toU16($HTTP_POST_VARS['character_name']));
				$query_rs = "INSERT INTO bannames(CharacterName, HashValue) VALUES(0x{$hexname}0000, $hashvalue)";
			}
			else
			{
				$query_rs = "INSERT INTO bannames(CharacterName) VALUES(0x{$hexname}0000)";
			}
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, "SELECT * FROM bannames WHERE STRCMP(CharacterName, 0x{$hexname}0000) = 0;");
			
			break;

		case 'f':

		default:
			if(strlen($HTTP_POST_VARS['character_name'])>0)
			{
				$hexname = hexstring(U8toU16($HTTP_POST_VARS['character_name']));
				$cond = "WHERE CharacterName LIKE 0x{$hexname}";
				if(!strchr($HTTP_POST_VARS['character_name'], "%")) $cond .= "0000";
			}
			else
			{
				$cond = "";
			}
			$start_indx = ($page - 1) * $rowpp;
			$query_rs = "SELECT * FROM bannames $cond ORDER BY CharacterName LIMIT $start_indx, $rowpp;";
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());

			$num_row = mysql_num_rows($rs);

			$rs_count = mysql_query("SELECT COUNT(1) FROM bannames $cond", $dbWc) or die(mysql_error());
			list($total_banname) = mysql_fetch_row($rs_count);
			mysql_free_result($rs_count);
			$lastpage = ceil($total_banname / $rowpp);
			if($page > $lastpage) $page = $lastpage;
			$idx = ($page - 1) * $rowpp;

			break;
	}

	if($query_rs && $HTTP_GET_VARS[a]!='f')
	{
		echo "<form name=form1 method=post action='banname.php?a=f&wid=$wid'><!--input type=hidden name=\"character_name\" value={$HTTP_POST_VARS[character_name]}--></form>";
		echo "<script>document.form1.submit()</script>";
		/*
		if($HTTP_GET_VARS[a] != 'i')
		{

		}
		else
		{
			echo "<script>alert('Please restart worldcontroller in order to reload the banned name list.');document.form1.submit()</script>";
		}
		*/
		//exit();
	}
}

$readonly = $readonly_gmdata? "READONLY":"";
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
<form name="form1" method="post" action="banname.php?a=i">
<h3>Banned Name For Character And Guild</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
function mklink($n){
	global $page;
	$tag = $n == $page?"<b>$n</b>":"$n";
	return "<a href=\"javascript:postform(document.form1, 'banname.php?a=f&p={$n}')\">$tag</a>";
}

if($wid!="")
{

	echo "Name: <input name=\"character_name\" maxlength=20 value=\"{$HTTP_POST_VARS[character_name]}\"> <input type=submit value=\"Ban\"> <input type=button value=\"Search\" onclick=\"postform(document.form1, 'banname.php?a=f')\">
		<br/><br/><font color=red><b>Note: If you add and/or delete banned name(s), please restart the world controller to apply the changes.</b></font>
		<hr>";
	if($rs && mysql_num_rows($rs) > 0)
	{
		$col = 0;
		$count = 0;
		$html_hdr = "<table border=1 cellspacing=0><tr><th>#</th><th>Banned Name</th><th>&nbsp;</th></tr>";
		while($row = mysql_fetch_assoc($rs))
		{
			$count++;
			$character_name = htmlspecialchars(U16btoU8str($row[CharacterName]));
			$hex_name = hexstring($row[CharacterName]);
			$idx++;
			$html_results .= "<tr><td>$idx</td><td>{$character_name}</td><td><input type=button value='delete' onclick='if(confirm(\"Confirm delete?\"))postform(document.form1,\"banname.php?a=d&h={$hex_name}\")'></td></tr>";

			if($count % 20 == 0)
			{
				eval("\$html_$col = \$html_hdr . \$html_results . \"</table>\";");
				$html_results = "";
				$col++;
			}
		}
		//$html_results .= "</table>";
		//echo $html_results;
		if($html_results != "") eval("\$html_$col = \$html_hdr . \$html_results . \"</table>\";");

		echo "<table><tr valign=top><td>$html_0</td><td>$html_1</td><td>$html_2</td></tr></table>";

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

			echo $html_page;
		}
	}
	else
	{
		echo "<font color=red>No record found.</font>";
	}
}
?>

</form>
</body>
</html>