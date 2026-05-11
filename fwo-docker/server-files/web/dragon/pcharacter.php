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
		$hashvalue = unsign_signed_integer(crc32($charactername_u16));

		$rs = mysql_query("SELECT CharacterName, CharID, Username FROM pcharacter WHERE HashValue='{$hashvalue}' AND CharID<>'{$HTTP_GET_VARS[i]}';", $dbWc) or die(mysql_error());
		if(mysql_num_rows($rs) > 0)
		{
			$row_chr = mysql_fetch_assoc($rs);
			die("Character name, <a href='pcharacter.php?i={$row_chr['CharID']}&wid={$wid}' target='_blank'>{$HTTP_POST_VARS['charactername']}</a>, is used by {$row_chr['Username']}.");
			mysql_free_result($rs);
		}

		$charnm = '0x'. hexstring($charactername_u16) . '0000';
		$query_rs = "UPDATE pcharacter SET
			CharacterName=$charnm,
			HashValue='{$hashvalue}',
			SceneID='{$HTTP_POST_VARS[sceneid]}',
			x='{$HTTP_POST_VARS[x]}',
			y='{$HTTP_POST_VARS[y]}',
			z='{$HTTP_POST_VARS[z]}',
			Facing='{$HTTP_POST_VARS[facing]}',
			BindSceneID='{$HTTP_POST_VARS[bindsceneid]}',
			BindX='{$HTTP_POST_VARS[bindx]}',
			BindY='{$HTTP_POST_VARS[bindy]}',
			BindZ='{$HTTP_POST_VARS[bindz]}',
			ModelType='{$HTTP_POST_VARS[modeltype]}',
			ZoneFlag='{$HTTP_POST_VARS[zoneflag]}',
			RespawnFlag='{$HTTP_POST_VARS[respawnflag]}',
			TemplateID='{$HTTP_POST_VARS[templateid]}',
			Face='{$HTTP_POST_VARS[face]}',
			Armor = '{$HTTP_POST_VARS[armor]}',
			LeftShoulder='{$HTTP_POST_VARS[leftshoulder]}',
			RightShoulder='{$HTTP_POST_VARS[rightshoulder]}',
			LeftBracer='{$HTTP_POST_VARS[leftbracer]}',
			RightBracer='{$HTTP_POST_VARS[rightbracer]}',
			LeftLeg='{$HTTP_POST_VARS[leftleg]}',
			RightLeg='{$HTTP_POST_VARS[rightleg]}',
			VisualFlag='{$HTTP_POST_VARS[visualflag]}',
			UserMsgFlag1='{$HTTP_POST_VARS[usermsgflag1]}',
			UserMsgFlag2='{$HTTP_POST_VARS[usermsgflag2]}',
			UserMsgFlag3='{$HTTP_POST_VARS[usermsgflag3]}',
			UserMsgFlag4='{$HTTP_POST_VARS[usermsgflag4]}',
			UserMsgFlag5='{$HTTP_POST_VARS[usermsgflag5]}',
			SysMsg1='{$HTTP_POST_VARS[sysmsg1]}',
			SysMsg1Param1='{$HTTP_POST_VARS[sysmsg1param1]}',
			SysMsg1Param2='{$HTTP_POST_VARS[sysmsg1param2]}',
			SysMsg2='{$HTTP_POST_VARS[sysmsg2]}',
			SysMsg2Param1='{$HTTP_POST_VARS[sysmsg2param1]}',
			SysMsg2Param2='{$HTTP_POST_VARS[sysmsg2param2]}',
			SysMsg3='{$HTTP_POST_VARS[sysmsg3]}',
			SysMsg3Param1='{$HTTP_POST_VARS[sysmsg3param1]}',
			SysMsg3Param2='{$HTTP_POST_VARS[sysmsg3param2]}',
			SysMsg4='{$HTTP_POST_VARS[sysmsg4]}',
			SysMsg4Param1='{$HTTP_POST_VARS[sysmsg4param1]}',
			SysMsg4Param2='{$HTTP_POST_VARS[sysmsg4param2]}',
			SysMsg5='{$HTTP_POST_VARS[sysmsg5]}',
			SysMsg5Param1='{$HTTP_POST_VARS[sysmsg5param1]}',
			SysMsg5Param2='{$HTTP_POST_VARS[sysmsg5param2]}',
			UserID1='{$HTTP_POST_VARS[userid1]}',
			UserID2='{$HTTP_POST_VARS[userid2]}',
			UserID3='{$HTTP_POST_VARS[userid3]}',
			UserID4='{$HTTP_POST_VARS[userid4]}',
			UserID5='{$HTTP_POST_VARS[userid5]}'
			WHERE CharID='{$HTTP_GET_VARS[i]}'";

			if($readonly_gmdata)
			{
				$query_rs = "UPDATE pcharacter SET
					SceneID='{$HTTP_POST_VARS[sceneid]}',
					x='{$HTTP_POST_VARS[x]}',
					y='{$HTTP_POST_VARS[y]}',
					z='{$HTTP_POST_VARS[z]}',
					Facing='{$HTTP_POST_VARS[facing]}',
					BindSceneID='{$HTTP_POST_VARS[bindsceneid]}',
					BindX='{$HTTP_POST_VARS[bindx]}',
					BindY='{$HTTP_POST_VARS[bindy]}',
					BindZ='{$HTTP_POST_VARS[bindz]}'
					WHERE CharID='{$HTTP_GET_VARS[i]}'";
			}

/*
			HashValue='{$HTTP_POST_VARS[hashvalue]}',
			Body='{$HTTP_POST_VARS[body]}',
			Job='{$HTTP_POST_VARS[job]}',
			QuestData='{$HTTP_POST_VARS[questdata]}',
			UserMsg1='{$HTTP_POST_VARS[usermsg1]}',
			UserMsg2='{$HTTP_POST_VARS[usermsg2]}',
			UserMsg3='{$HTTP_POST_VARS[usermsg3]}',
			UserMsg4='{$HTTP_POST_VARS[usermsg4]}',
			UserMsg5='{$HTTP_POST_VARS[usermsg5]}',
*/
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
/*
		$query_rs1 = "UPDATE pcharacter SET HashValue='{$hashvalue}' WHERE CharID='{$HTTP_GET_VARS[i]}';";
		$rs = mysql_query($query_rs1, $dbWc);
		
*/
		$befores = get_str_rs($dbWc, "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}';");
		

		header("Location: pcharacter.php?i={$HTTP_GET_VARS[i]}&wid={$wid}");
		exit();
	}

	$query_rs = "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red>No matched queries.</font>");
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
<h3>Player Character Details</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post" action="">
   <p>Properties: <!--a href="pcharacter.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>"-->Character<!--/a-->
	| <a href="pcharstat.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Stat</a>
	| <a href="charinv.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Inventory</a>
	| <a href="powerlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Power</a>
	| <a href="skilllist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Skill</a>
	| <a href="effectlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Effect</a>
	| <a href="stancelist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Stance</a>
	| <a href="questdata.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Quest</a>
</p>
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
      <td><input name="charactername" type="text" id="charactername" value="<?=htmlspecialchars(U16btoU8str($row[CharacterName]))?>" readonly="yes">
	      <a href="chgchrnm.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Change</a>
      </td>
    </tr>
    <tr>
	    <td>SlotID</td>
	    <td><?=$row[SlotID]?></td>
    </tr>
    <tr>
      <td>Last login time</td>
      <td><?=date("F j, Y, g:i a",$row["LoginTime"])?></td>
    </tr>
    <tr>
      <td>Creation Date</td>
      <td><?=date("F j, Y, g:i a",$row["CreateDate"])?></td>
    </tr>
    <tr>
      <td>Scene</td>
      <td><input name="sceneid" type="text" id="sceneid" value="<?=$row[SceneID]?>"></td>
    </tr>
    <tr>
      <td>Coordinate</td>
      <td>X
        <input name="x" type="text" id="x" value="<?=$row[x]?>" size="6">
        Y
        <input name="y" type="text" id="y" value="<?=$row[y]?>" size="6">
        Z
        <input name="z" type="text" id="z" value="<?=$row[z]?>" size="6"> </td>
    </tr>
    <tr>
      <td>Facing</td>
      <td><input name="facing" type="text" id="facing" value="<?=$row[Facing]?>"></td>
    </tr>
    <tr>
      <td>BindScene</td>
      <td><input name="bindsceneid" type="text" id="bindsceneid" value="<?=$row[BindSceneID]?>"></td>
    </tr>
    <tr>
      <td>Bind coor</td>
      <td>X
        <input name="bindx" type="text" id="bindx" value="<?=$row[BindX]?>" size="6">
        Y
        <input name="bindy" type="text" id="bindy" value="<?=$row[BindY]?>" size="6">
        Z
        <input name="bindz" type="text" id="bindz" value="<?=$row[BindZ]?>" size="6">
      </td>
    </tr>
<?
if(!$readonly_gmdata)
{
?>
    <tr>
      <td>ModelType</td>
      <td><input name="modeltype" type="text" id="modeltype" value="<?=$row[ModelType]?>"></td>
    </tr>
    <tr>
      <td>ZoneFlag</td>
      <td><input name="zoneflag" type="text" id="zoneflag" value="<?=$row[ZoneFlag]?>"></td>
    </tr>
    <tr>
      <td>RespawnFlag</td>
      <td><input name="respawnflag" type="text" id="respawnflag" value="<?=$row[RespawnFlag]?>"></td>
    </tr>
    <tr>
      <td>TemplateID</td>
      <td><input name="templateid" type="text" id="templateid" value="<?=$row[TemplateID]?>"></td>
    </tr>
    <tr>
      <td>Face</td>
      <td><input name="face" type="text" id="face" value="<?=$row[Face]?>"></td>
    </tr>
<!-- added armor on 030429 -->
    <tr>
      <td>Armor</td>
      <td><input name="armor" type="text" id="armor" value="<?=$row[Armor]?>"></td>
    </tr>


    <tr>
      <td>LeftShoulder</td>
      <td><input name="leftshoulder" type="text" id="leftshoulder" value="<?=$row[LeftShoulder]?>"></td>
    </tr>
    <tr>
      <td>RightShoulder</td>
      <td><input name="rightshoulder" type="text" id="rightshoulder" value="<?=$row[RightShoulder]?>"></td>
    </tr>
    <tr>
      <td>LeftBracer</td>
      <td><input name="leftbracer" type="text" id="leftbracer" value="<?=$row[LeftBracer]?>"></td>
    </tr>
    <tr>
      <td>RightBracer</td>
      <td><input name="rightbracer" type="text" id="rightbracer" value="<?=$row[RightBracer]?>"></td>
    </tr>
<!--
    <tr>
      <td>Body</td>
      <td><input name="body" type="text" id="body" value="<?=$row[Body]?>"></td>
    </tr>
-->
    <tr>
      <td>LeftLeg</td>
      <td><input name="leftleg" type="text" id="leftleg" value="<?=$row[LeftLeg]?>"></td>
    </tr>
    <tr>
      <td>RightLeg</td>
      <td><input name="rightleg" type="text" id="rightleg" value="<?=$row[RightLeg]?>"></td>
    </tr>
<!--
    <tr>
      <td>HashValue</td>
      <td><input name="hashvalue" type="text" id="hashvalue" value="<?=$row[HashValue]?>"></td>
    </tr>
-->
    <tr>
      <td>VisualFlag</td>
      <td><input name="visualflag" type="text" id="visualflag" value="<?=$row[VisualFlag]?>"></td>
    </tr>
    <!--tr>
      <td>Job</td>
      <td><input name="job" type="text" id="job" value="<?=$row[job]?>"></td>
    </tr-->
    <!--tr>
      <td><a href="questdata.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">QuestData</a></td>
      <td><input name="questdata" type="text" id="questdata" value="<?=$row[QuestData]?>" readonly="yes">
      </td>
    </tr-->
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserMsg1</td>
      <td><input name="usermsg1" type="text" id="usermsg1" value="<?=$row[UserMsg1]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>UserMsgFlag1</td>
      <td><input name="usermsgflag1" type="text" id="usermsgflag1" value="<?=$row[UserMsgFlag1]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserMsg2</td>
      <td><input name="usermsg2" type="text" id="usermsg2" value="<?=$row[UserMsg2]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>UserMsgFlag2</td>
      <td><input name="usermsgflag2" type="text" id="usermsgflag2" value="<?=$row[UserMsgFlag2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserMsg3</td>
      <td><input name="usermsg3" type="text" id="usermsg3" value="<?=$row[UserMsg3]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>UserMsgFlag3</td>
      <td><input name="usermsgflag3" type="text" id="usermsgflag3" value="<?=$row[UserMsgFlag3]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserMsg4</td>
      <td><input name="usermsg4" type="text" id="usermsg4" value="<?=$row[UserMsg4]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>UserMsgFlag4</td>
      <td><input name="usermsgflag4" type="text" id="usermsgflag4" value="<?=$row[UserMsgFlag4]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserMsg5</td>
      <td><input name="usermsg5" type="text" id="usermsg5" value="<?=$row[UserMsg5]?>" readonly="yes"></td>
    </tr>
    <tr>
      <td>UserMsgFlag5</td>
      <td><input name="usermsgflag5" type="text" id="usermsgflag5" value="<?=$row[UserMsgFlag5]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>SysMsg1</td>
      <td><input name="sysmsg1" type="text" id="sysmsg1" value="<?=$row[SysMsg1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg1Param1</td>
      <td><input name="sysmsg1param1" type="text" id="sysmsg1param1" value="<?=$row[SysMsg1Param1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg1Param2</td>
      <td><input name="sysmsg1param2" type="text" id="sysmsg1param2" value="<?=$row[SysMsg1Param2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>SysMsg2</td>
      <td><input name="sysmsg2" type="text" id="sysmsg2" value="<?=$row[SysMsg2]?>"></td>
    </tr>
    <tr>
      <td>SysMsg2Param1</td>
      <td><input name="sysmsg2param1" type="text" id="sysmsg2param1" value="<?=$row[SysMsg2Param1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg2Param2</td>
      <td><input name="sysmsg2param2" type="text" id="sysmsg2param2" value="<?=$row[SysMsg2Param2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>SysMsg3</td>
      <td><input name="sysmsg3" type="text" id="sysmsg3" value="<?=$row[SysMsg3]?>"></td>
    </tr>
    <tr>
      <td>SysMsg3Param1</td>
      <td><input name="sysmsg3param1" type="text" id="sysmsg3param1" value="<?=$row[SysMsg3Param1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg3Param2</td>
      <td><input name="sysmsg3param2" type="text" id="sysmsg3param2" value="<?=$row[SysMsg3Param2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>SysMsg4</td>
      <td><input name="sysmsg4" type="text" id="sysmsg4" value="<?=$row[SysMsg4]?>"></td>
    </tr>
    <tr>
      <td>SysMsg4Param1</td>
      <td><input name="sysmsg4param1" type="text" id="sysmsg4param1" value="<?=$row[SysMsg4Param1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg4Param2</td>
      <td><input name="sysmsg4param2" type="text" id="sysmsg4param2" value="<?=$row[SysMsg4Param2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>SysMsg5</td>
      <td><input name="sysmsg5" type="text" id="sysmsg5" value="<?=$row[SysMsg5]?>"></td>
    </tr>
    <tr>
      <td>SysMsg5Param1</td>
      <td><input name="sysmsg5param1" type="text" id="sysmsg5param1" value="<?=$row[SysMsg5Param1]?>"></td>
    </tr>
    <tr>
      <td>SysMsg5Param2</td>
      <td><input name="sysmsg5param2" type="text" id="sysmsg5param2" value="<?=$row[SysMsg5Param2]?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>UserID1</td>
      <td><input name="userid1" type="text" id="userid1" value="<?=$row[UserID1]?>"></td>
    </tr>
    <tr>
      <td>UserID2</td>
      <td><input name="userid2" type="text" id="userid2" value="<?=$row[UserID2]?>"></td>
    </tr>
    <tr>
      <td>UserID3</td>
      <td><input name="userid3" type="text" id="userid3" value="<?=$row[UserID3]?>"></td>
    </tr>
    <tr>
      <td>UserID4</td>
      <td><input name="userid4" type="text" id="userid4" value="<?=$row[UserID4]?>"></td>
    </tr>
    <tr>
      <td>UserID5</td>
      <td><input name="userid5" type="text" id="userid5" value="<?=$row[UserID5]?>"></td>
    </tr>
<?
}
?>
  </table>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'pcharacter.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
</form>
</body>
</html>
