<?php
require('auth.php');
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
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
}

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
//		if($HTTP_POST_VARS[wc_id]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wc_id];
		break;

	case 'as':
		if($HTTP_POST_VARS[as_id]!='')$HTTP_SESSION_VARS['as']=$HTTP_POST_VARS[as_id];
		break;
/*
	case 'a':
		$query_rs = "INSERT INTO subscription(Username,Password, SvcLevel)
			VALUES('{$HTTP_POST_VARS[account_0]}','{$HTTP_POST_VARS[passwd_0]}','{$HTTP_POST_VARS[svclvl_0]}')";
		break;

	case 'f':
		$cond_charid = $HTTP_POST_VARS[charid_0]!=""? " AND CharID LIKE \"$HTTP_POST_VARS[charid_0]\"":"";
		$cond_charnm = $HTTP_POST_VARS[charnm_0]!=""? " AND CharacterName LIKE \"$HTTP_POST_VARS[charnm_0]\"":"";
		if($HTTP_POST_VARS[charnm_0]!="")
		{
			$cond_charnm = " AND CharacterName LIKE \"". addslashes(U8toU16($HTTP_POST_VARS[charnm_0])) ."%\"";
		}
		$cond_usernm = $HTTP_POST_VARS[usernm_0]!=""? " AND Username LIKE \"$HTTP_POST_VARS[usernm_0]\"":"";
		$query_rs = "SELECT * FROM pcharacter WHERE 1 $cond_charid $cond_charnm $cond_usernm";
		break;

	case 's':
		$passwd = $HTTP_POST_VARS["passwd_{$HTTP_GET_VARS[i]}"];
		$svclvl = $HTTP_POST_VARS["svclvl_{$HTTP_GET_VARS[i]}"];
		$account = $HTTP_POST_VARS["account_{$HTTP_GET_VARS[i]}"];
		$query_rs = "UPDATE subscription SET
			Password='$passwd',
			SvcLevel='$svclvl'
			WHERE Username='$account'";
		break;
	case 'd':
		$account = $HTTP_POST_VARS["account_{$HTTP_GET_VARS[i]}"];
		$query_rs = "DELETE FROM subscription WHERE Username='{$account}'";
		break;
*/
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'gmdata.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);

/*
if($query_rs)
{
	if($wid)
	{
		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
		mysql_free_result($rsSvr);
		$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
		mysql_select_db($row_rsSvr[db], $dbWc);
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	}
}
*/

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
function subm(){
	with(document.form1)
	{
		if(document.form1.id_0.value.length < 1)
		{
			alert("Please provide ID for search.")
			document.form1.id_0.focus()
			return false
		}

//		var w = window.open(phppage.value + "?wid=<?=$wid?>&a=f&i=" + document.form1.id_0.value, "_blank")
		var w = window.open("uniqueitem.php?wid=<?=$wid?>&a=f&i=" + document.form1.id_0.value, "_blank")

		return false
	}
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" onsubmit="return subm()">
<h3>Game Data</h3>
(World Controller: <?=$htmlWc?>)
  <br><br>
 <?php
if($wid!="")
{
/*
<!--
  <table border="0">
    <tr>
	<td>
		<Select id=phppage>
			<option value='skills.php'>Skill ID</option>
			<option value="effects.php">Effect ID</option>
			<option value="powers.php">Power ID</option>
			<option value="stances.php">Stance ID</option>
			<option value="npcattrib.php">NPC Attribute ID</option>
		</select>
	</td>
      <td><input name="id_0"> <input type=submit value="Search"></td>
    </tr>
  </table>
-->
*/
	if (!$readonly_gmdata)
	{
	?>
	Search ID: <input name="id_00">
	    <input type="button" value="Item" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'item.php?a=f&i='+v)}">
	    <input type="button" value="Skill" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'skills.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="Effect" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'effects.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="Power" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'powers.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="Stance" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'stances.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="NPCAttrib" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'npcattrib.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="SpawnPtDyn" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'spawnptdyn.php?wid=<?=$wid?>&a=f&i='+v)}">
	    <input type="button" value="NPCAttribDyn" onclick="var v=parseInt(document.form1.id_00.value);if(!v>0){alert('Please enter numeric search ID.')}else{postform(document.form1,'npcattribdyn.php?wid=<?=$wid?>&a=f&i='+v)}">
	<?
	}
?>
<!--
  <table border="0">
    <tr>
	<td>Unique Item ID</td>
	<td><input name="id_0"> <input type=submit value="Search"></td>
    </tr>
  </table>
<hr size=1>
-->

<!--a href="item.php">Item</a></p-->
<hr>
<?if(!$readonly_gmdata){?>
<!--br><a href="javascript:if(confirm('Reset Guild Data?'))location.href='crresetguild.php'">Reset Guild Data</a-->
<br><input type=button onclick="if(confirm('Reset Guild Data?'))location.href='crresetguild.php'" value="Reset Guild Data">
<?}?>
<br><a href="arenascore.php" onmouseover="return escape('list arena scores.')">Arena Score</a>
<br><a href="gmclan.php?wid=<?=$wid?>&" onmouseover="return escape('list clan details and members.')">Clan</a>
<br><a href="gmevent.php?wid=<?=$wid?>&" onmouseover="return escape('activate/de-activate game event.')">Game Event</a>
<br><a href="gmaccess.php" onmouseover="return escape('control access right for the use of ingame \\GM command.')">GM Command Access Control</a>
<br><a href="guild.php?wid=<?=$wid?>" onmouseover="return escape('list guild details.')">Guild</a>
<br><a href="findchar.php?wid=<?=$wid?>&a=f&op5=3&herop1=0&sort=8&rpp=50&view=1" onmouseover="return escape('list characters by hero points.')">Hero Points Listing</a>
<br><a href="findchar.php?wid=<?=$wid?>&a=f&op4=3&redp1=0&sort=6&rpp=50&view=2" onmouseover="return escape('list characters by red PK points.')">Red PK Points Listing</a>
<br><a href="relics.php" onmouseover="return escape('list relics and their locations.')">Relic Location Listing</a>
<br><a href="clanwar.php" onmouseover="return escape('list total wars.')">Total War</a>
<br><a href="towncp.php?wid=<?=$wid?>&" onmouseover="return escape('list towns and their belonging clan.')">Town Listing</a>
<br><a href="uitem.php" onmouseover="return escape('find unique items.')">Unique Items</a>
<br><a href="eventslot.php" onmouseover="return escape('list war event booking slots.')">War Event Booking Slots</a>
<br><a href="warevents.php" onmouseover="return escape('list war events.')">War Events</a>
<br><a href="caps.php" onmouseover="return escape('list Level Cap information.')">Level Cap</a>
<br><a href="itemlst.php" onmouseover="return escape('list Items information.')">Item Lock List</a>
<!--<p><a href="scratchdata.php">Set Scene Scratch Data</a></p>-->
<!--p><a href="cleancharinv.php">Cleanup invalid item(s) from PCs' inventories</a-->
<?
}//if($wid)
?>
</form>
<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>
</body>
</html>
