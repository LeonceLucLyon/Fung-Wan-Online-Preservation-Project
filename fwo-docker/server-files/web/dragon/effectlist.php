<?php
require("auth.php");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

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

	$effectlist_table = "effectlist_" . ($HTTP_GET_VARS[f] % 10);

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	$query_rs1 = "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[f]}'";
	$rs1 = mysql_query($query_rs1, $dbWc) or die(mysql_error());
	$row1 = mysql_fetch_assoc($rs1);
	mysql_free_result($rs1);

	if($HTTP_GET_VARS[a]=="s")
	{
		$rs_logon = mysql_query("SELECT * FROM authenticated WHERE CharID='{$HTTP_GET_VARS[f]}'", $dbWc) or die(mysql_error());
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

		if($HTTP_POST_VARS["ids"])
		{
			$indxes=split("\|",$HTTP_POST_VARS["ids"]);
			foreach($indxes as $indx)
			{
				if($indx=="")break;

				//$indx=$HTTP_GET_VARS["i"];
				$effectid=$HTTP_POST_VARS["effectid_$indx"];
				$duration=$HTTP_POST_VARS["duration_$indx"];
				$timestamp=$HTTP_POST_VARS["timestamp_$indx"];
				$powerrank=$HTTP_POST_VARS["powerrank_$indx"];
				$immunity=$HTTP_POST_VARS["immunity_$indx"];

				$query_rs = "UPDATE $effectlist_table SET
					EffectID ='{$effectid}',
					Duration='{$duration}',
					Immunity='{$immunity}'
					WHERE Indx='$indx'";

					//TimeStamp='{$timestamp}',
					//PowerRank='{$powerrank}',

				$befores = get_str_rs($dbWc, "SELECT p.Username, p.CharacterName, e.* FROM $effectlist_table e, pcharacter p WHERE Indx='$indx' AND e.CharID=p.CharID;");
				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, "SELECT p.Username, p.CharacterName, e.* FROM $effectlist_table e, pcharacter p WHERE Indx='$indx' AND e.CharID=p.CharID;");
				
			}
		}
		header("Location: effectlist.php?f={$HTTP_GET_VARS[f]}&wid=$wid?");
		exit();
	}

	$query_rs = "SELECT * FROM $effectlist_table WHERE CharID='{$HTTP_GET_VARS[f]}' ORDER BY EffectID DESC";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red>No matched queries.</font>");
//	$row=mysql_fetch_assoc($rs);
//	mysql_free_result($rs);
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
function saveid(i)
{
	if(eval("!document.form1.mark_"+i+".value"))
	{
		eval("document.form1.mark_"+i+".value=1")
		document.form1.ids.value+=i+"|"
	}
}
//-->
</script>
</head>
<body>
<h3>Player Character Effect List</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post">
<p>Properties:
	<a href="pcharacter.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Character</a> |
	<a href="pcharstat.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Stat</a> |
	<a href="charinv.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Inventory</a> |
	<a href="powerlist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Power</a> |
	<a href="skilllist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Skill</a> |
	<!--a href="effectlist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>"-->Effect<!--/a--> |
	<a href="stancelist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Stance</a> |
	<a href="questdata.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Quest</a>
</p>
<table border="0">
	<tr>
		<td>User Name</td>
		<td><input name="textfield3" type="text" value="<?=$row1[Username]?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char Name </td>
		<td><input name="textfield5" type="text" value="<?=htmlspecialchars(U16btoU8str($row1[CharacterName]))?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char ID</td>
		<td><input name="textfield4" type="text" value="<?=$row1[CharID]?>" readonly="yes"></td>
	</tr>
</table>
<table border="1" cellspacing=0>
    <tr>
	<td>EffectID</td>
	<td>Duration</td>
	<?
	/*
	<td>TimeStamp</td>
	<td>PowerRank</td>
	*/
	?>
	<td>Immunity</td>
    </tr>
    <?php
while($row=mysql_fetch_assoc($rs))
{
?>
    <tr>
      <!--td><input name="textfield" type="text" value="<?=$row[Indx]?>" size="8"></td-->
      <td><input name="effectid_<?=$row[Indx]?>" type="text" value="<?=$row[EffectID]?>" size="8" onchange="saveid(<?=$row[Indx]?>)"><!--a href="effects.php?i=<?=$row[EffectID]?>&wid=<?=$wid?>"--><?=getstring($row[EffectID],'effect')?><!--/a--></td>
      <td><input name="duration_<?=$row[Indx]?>" type="text" value="<?=$row[Duration]?>" size="8" onchange="saveid(<?=$row[Indx]?>)"></td>
<?
/*
      <td><input name="timestamp_<?=$row[Indx]?>" type="text" value="<?=$row[TimeStamp]?>" size="8" onchange="saveid(<?=$row[Indx]?>)"></td>
      <td><input name="powerrank_<?=$row[Indx]?>" type="text" value="<?=$row[PowerRank]?>" size="8" onchange="saveid(<?=$row[Indx]?>)"></td>
*/
?>
      <td><input name="immunity_<?=$row[Indx]?>" type="text" value="<?=$row[Immunity]?>" size="8" onchange="saveid(<?=$row[Indx]?>)"></td>

	<input type="hidden" name="mark_<?=$row[Indx]?>" value="">
</td>
      <!--td><input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'effectlist.php?a=s&f=<?=$HTTP_GET_VARS[f]?>&i=<?=$row[Indx]?>&wid=<?=$wid?>')"></td-->
    </tr>
    <?php
}
?>
  </table>
<input type="hidden" name="ids" value="">
<!--input type="button" onclick="alert(document.form1.ids.value)"-->
<input type="reset" value="Reset"> <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'effectlist.php?a=s&f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>')">
  </form>
</body>
</html>
