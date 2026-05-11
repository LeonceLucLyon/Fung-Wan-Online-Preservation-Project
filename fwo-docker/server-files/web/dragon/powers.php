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
	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
	{
		die("Access denied.");
	}
	elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
	{
		die("Access denied. Read-Only.");
	}

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_GET_VARS[a]=="s")
	{
		$query_rs = "UPDATE powers SET
			ClanID='{$HTTP_POST_VARS[ClanID]}',
			StanceID='{$HTTP_POST_VARS[StanceID]}',
			StanceRank='{$HTTP_POST_VARS[StanceRank]}',
			BuyPrice='{$HTTP_POST_VARS[BuyPrice]}',
			AttackBonus='{$HTTP_POST_VARS[AttackBonus]}',
			TargetType='{$HTTP_POST_VARS[TargetType]}',
			Friendliness='{$HTTP_POST_VARS[Friendliness]}',
			FXID='{$HTTP_POST_VARS[FXID]}',
			AnimID='{$HTTP_POST_VARS[AnimID]}',
			ChiCost='{$HTTP_POST_VARS[ChiCost]}',
			HPCost='{$HTTP_POST_VARS[HPCost]}',
			ExecTime='{$HTTP_POST_VARS[ExecTime]}',
			PowerID1='{$HTTP_POST_VARS[PowerID1]}',
			PowerID2='{$HTTP_POST_VARS[PowerID2]}',
			WeaponFlag='{$HTTP_POST_VARS[WeaponFlag]}',
			EffectID1='{$HTTP_POST_VARS[EffectID1]}',
			Affecting1='{$HTTP_POST_VARS[Affecting1]}',
			EFFID1='{$HTTP_POST_VARS[EFFID]}',
			Duration1='{$HTTP_POST_VARS[Duration1]}',

			EffectID2='{$HTTP_POST_VARS[EffectID2]}',
			Affecting2='{$HTTP_POST_VARS[Affecting2]}',
			EFFID2='{$HTTP_POST_VARS[EFFID2]}',
			Duration2='{$HTTP_POST_VARS[Duration2]}',

			EffectID3='{$HTTP_POST_VARS[EffectID3]}',
			Affecting3='{$HTTP_POST_VARS[Affecting3]}',
			EFFID3='{$HTTP_POST_VARS[EFFID3]}',
			Duration3='{$HTTP_POST_VARS[Duration3]}',

			EffectID4='{$HTTP_POST_VARS[EffectID4]}',
			Affecting4='{$HTTP_POST_VARS[Affecting4]}',
			EFFID4='{$HTTP_POST_VARS[EFFID4]}',
			Duration4='{$HTTP_POST_VARS[Duration4]}',

			EffectID5='{$HTTP_POST_VARS[EffectID5]}',
			Affecting5='{$HTTP_POST_VARS[Affecting5]}',
			EFFID5='{$HTTP_POST_VARS[EFFID5]}',
			Duration5='{$HTTP_POST_VARS[Duration5]}',

			Strength='{$HTTP_POST_VARS[Strength]}',
			Constitution='{$HTTP_POST_VARS[Constitution]}',
			Agility='{$HTTP_POST_VARS[Agility]}',
			Mind='{$HTTP_POST_VARS[Mind]}',
			Perception='{$HTTP_POST_VARS[Perception]}',
			CoolDown='{$HTTP_POST_VARS[CoolDown]}',
			UpgradeLevel='{$HTTP_POST_VARS[UpgradeLevel]}',
			UpgradeID='{$HTTP_POST_VARS[UpgradeID]}',
			Interrupt='{$HTTP_POST_VARS[Interrupt]}'

			WHERE PowerID='{$HTTP_GET_VARS[i]}'";

		$befores = get_str_rs($dbWc, "SELECT * FROM powers WHERE PowerID='{$HTTP_GET_VARS[i]}'");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM powers WHERE PowerID='{$HTTP_GET_VARS[i]}'");
		
		header ("Location: powers.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
		exit;
	}

	$query_rs = "SELECT * FROM powers WHERE PowerID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red><b>No matched queries.</b></font>");
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
}
$readonly = $readonly_gmdata?"READONLY":"";
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
<h3>Power</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<br><br>
  <table border="1" cellspacing=0>
    <tr>
      <td>PowerID</td>
      <td><input name="PowerID" type="text" id="PowerID" value="<?=$row[PowerID]?>" readonly="yes"><?=getstring($row[PowerID],'power')?></td>
    </tr>
    <tr>
      <td>ClanID</td>
      <td><input name="ClanID" type="text" id="ClanID" value="<?=$row[ClanID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>StanceID</td>
      <td><input name="StanceID" type="text" id="StanceID" value="<?=$row[StanceID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>StanceRank</td>
      <td><input name="StanceRank" type="text" id="StanceRank" value="<?=$row[StanceRank]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BuyPrice</td>
      <td><input name="BuyPrice" type="text" id="BuyPrice" value="<?=$row[BuyPrice]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>AttackBonus</td>
      <td><input name="AttackBonus" type="text" id="AttackBonus" value="<?=$row[AttackBonus]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>TargetType</td>
      <td><input name="TargetType" type="text" id="TargetType" value="<?=$row[TargetType]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Friendliness</td>
      <td><input name="Friendliness" type="text" id="Friendliness" value="<?=$row[Friendliness]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>FXID</td>
      <td><input name="FXID" type="text" id="FXID" value="<?=$row[FXID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>AnimID</td>
      <td><input name="AnimID" type="text" id="AnimID" value="<?=$row[AnimID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>ChiCost</td>
      <td><input name="ChiCost" type="text" id="ChiCost" value="<?=$row[ChiCost]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>HPCost</td>
      <td><input name="HPCost" type="text" id="HPCost" value="<?=$row[HPCost]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>ExecTime</td>
      <td><input name="ExecTime" type="text" id="ExecTime" value="<?=$row[ExecTime]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>PowerID1</td>
      <td><input name="PowerID1" type="text" id="PowerID1" value="<?=$row[PowerID1]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>PowerID2</td>
      <td><input name="PowerID2" type="text" id="PowerID2" value="<?=$row[PowerID2]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>WeaponFlag</td>
      <td><input name="WeaponFlag" type="text" id="WeaponFlag" value="<?=$row[WeaponFlag]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EffectID1</td>
      <td><input name="EffectID1" type="text" id="EffectID1" value="<?=$row[EffectID1]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Affecting1</td>
      <td><input name="Affecting1" type="text" id="Affecting1" value="<?=$row[Affecting1]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EFFID1</td>
      <td><input name="EFFID1" type="text" id="EFFID1" value="<?=$row[EFFID1]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Duration1</td>
      <td><input name="Duration1" type="text" id="Duration1" value="<?=$row[Duration1]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EffectID2</td>
      <td><input name="EffectID2" type="text" id="EffectID2" value="<?=$row[EffectID2]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Affecting2</td>
      <td><input name="Affecting2" type="text" id="Affecting2" value="<?=$row[Affecting2]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EFFID2</td>
      <td><input name="EFFID2" type="text" id="EFFID2" value="<?=$row[EFFID2]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Duration2</td>
      <td><input name="Duration2" type="text" id="Duration2" value="<?=$row[Duration2]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EffectID3</td>
      <td><input name="EffectID3" type="text" id="EffectID3" value="<?=$row[EffectID3]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Affecting3</td>
      <td><input name="Affecting3" type="text" id="Affecting3" value="<?=$row[Affecting3]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EFFID3</td>
      <td><input name="EFFID3" type="text" id="EFFID3" value="<?=$row[EFFID3]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Duration3</td>
      <td><input name="Duration3" type="text" id="Duration3" value="<?=$row[Duration3]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EffectID4</td>
      <td><input name="EffectID4" type="text" id="EffectID4" value="<?=$row[EffectID4]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Affecting4</td>
      <td><input name="Affecting4" type="text" id="Affecting4" value="<?=$row[Affecting4]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EFFID4</td>
      <td><input name="EFFID4" type="text" id="EFFID4" value="<?=$row[EFFID4]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Duration4</td>
      <td><input name="Duration4" type="text" id="Duration4" value="<?=$row[Duration4]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EffectID5</td>
      <td><input name="EffectID5" type="text" id="EffectID5" value="<?=$row[EffectID5]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Affecting5</td>
      <td><input name="Affecting5" type="text" id="Affecting5" value="<?=$row[Affecting5]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>EFFID5</td>
      <td><input name="EFFID5" type="text" id="EFFID5" value="<?=$row[EFFID5]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Duration5</td>
      <td><input name="Duration5" type="text" id="Duration5" value="<?=$row[Duration5]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Strength</td>
      <td><input name="Strength" type="text" id="Strength" value="<?=$row[Strength]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Constitution</td>
      <td><input name="Constitution" type="text" id="Constitution" value="<?=$row[Constitution]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Agility</td>
      <td><input name="Agility" type="text" id="Agility" value="<?=$row[Agility]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Mind</td>
      <td><input name="Mind" type="text" id="Mind" value="<?=$row[Mind]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Perception</td>
      <td><input name="Perception" type="text" id="Perception" value="<?=$row[Perception]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>CoolDown</td>
      <td><input name="CoolDown" type="text" id="CoolDown" value="<?=$row[CoolDown]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>UpgradeLevel</td>
      <td><input name="UpgradeLevel" type="text" id="UpgradeLevel" value="<?=$row[UpgradeLevel]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>UpgradeID</td>
      <td><input name="UpgradeID" type="text" id="UpgradeID" value="<?=$row[UpgradeID]?>" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Interrupt</td>
      <td><input name="Interrupt" type="text" id="Interrupt" value="<?=$row[Interrupt]?>" <?=$readonly?>></td>
    </tr>
  </table>
<?
if(!$readonly_gmdata)
{
?>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'powers.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
<?
}
?>
</form>
</body>
</html>
