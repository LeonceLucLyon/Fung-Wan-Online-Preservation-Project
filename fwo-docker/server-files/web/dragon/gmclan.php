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
elseif( ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && (!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w")) )
{
	die("Access denied. Read-Only.");
}

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
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
	mysql_select_db($row_rsSvr[db], $dbWc) or die(mysql_error($dbWc));
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
}

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
//		if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
		break;

	case 'as':
		if($HTTP_POST_VARS[as_id]!='')$HTTP_SESSION_VARS['as']=$HTTP_POST_VARS[as_id];
		break;

	case 'f':
		if(!$wid) die("World not selected.");
		if($HTTP_GET_VARS[i]!='')
			$ClanID=$HTTP_GET_VARS[i];
		else
			$ClanID=$HTTP_POST_VARS[ClanID];

		if ($ClanID == 100)
		{
			header("Location: gmlist.php?wid=$wid");
			exit;
		}

		$query_rs = "SELECT * FROM clan WHERE ClanID='{$ClanID}'";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$num_row = mysql_num_rows($rs);
		$row = mysql_fetch_assoc($rs);
		mysql_free_result($rs);

		break;

	case 's':
		if(!$wid) die("World not selected.");
		$query_rs = "UPDATE clan SET
			Gold='{$HTTP_POST_VARS[Gold]}',
			Type='{$HTTP_POST_VARS[Type]}',
			Prestige='{$HTTP_POST_VARS[Prestige]}',
			Relic1='{$HTTP_POST_VARS[Relic1]}',
			Relic2 = '{$HTTP_POST_VARS[Relic2]}',
			Relic3= '{$HTTP_POST_VARS[Relic3]}',
			Relic4= '{$HTTP_POST_VARS[Relic4]}',
			Relic5 ='{$HTTP_POST_VARS[Relic5]}',
			Relic6 = '{$HTTP_POST_VARS[Relic6]}',
			Relic7= '{$HTTP_POST_VARS[Relic7]}',
			Relic8= '{$HTTP_POST_VARS[Relic8]}',
			Relic9= '{$HTTP_POST_VARS[Relic9]}',
			Relic10 = '{$HTTP_POST_VARS[Relic10]}',
			Relic11 = '{$HTTP_POST_VARS[Relic11]}',
			Relic12 = '{$HTTP_POST_VARS[Relic12]}',
			Relic13= '{$HTTP_POST_VARS[Relic13]}',
			Relic14 = '{$HTTP_POST_VARS[Relic14]}',
			Relic15 = '{$HTTP_POST_VARS[Relic15]}',
			Relic16 = '{$HTTP_POST_VARS[Relic16]}'
			WHERE ClanID='{$HTTP_POST_VARS[ClanID]}'";
			/*
			Relic1Flag='{$HTTP_POST_VARS[Relic1Flag]}',
			Relic2Flag='{$HTTP_POST_VARS[Relic2Flag]}',
			Relic3Flag='{$HTTP_POST_VARS[Relic3Flag]}',
			Relic4Flag='{$HTTP_POST_VARS[Relic4Flag]}',
			Relic5Flag='{$HTTP_POST_VARS[Relic5Flag]}',
			Relic6Flag='{$HTTP_POST_VARS[Relic6Flag]}',
			Relic7Flag='{$HTTP_POST_VARS[Relic7Flag]}',
			Relic8Flag='{$HTTP_POST_VARS[Relic8Flag]}',
			Relic9Flag='{$HTTP_POST_VARS[Relic9Flag]}',
			Relic10Flag='{$HTTP_POST_VARS[Relic10Flag]}',
			Relic11Flag='{$HTTP_POST_VARS[Relic11Flag]}',
			Relic12Flag='{$HTTP_POST_VARS[Relic12Flag]}',
			Relic13Flag='{$HTTP_POST_VARS[Relic13Flag]}',
			Relic14Flag='{$HTTP_POST_VARS[Relic14Flag]}',
			Relic15Flag='{$HTTP_POST_VARS[Relic15Flag]}',
			Relic16Flag='{$HTTP_POST_VARS[Relic16Flag]}',
			BountyID1='{$HTTP_POST_VARS[BountyID1]}',
			BountyTime1='{$HTTP_POST_VARS[BountyTime1]}',
			BountyID2='{$HTTP_POST_VARS[BountyID2]}',
			BountyTime2='{$HTTP_POST_VARS[BountyTime2]}',
			BountyID3='{$HTTP_POST_VARS[BountyID3]}',
			BountyTime3='{$HTTP_POST_VARS[BountyTime3]}',
			BountyID4='{$HTTP_POST_VARS[BountyID4]}',
			BountyTime4='{$HTTP_POST_VARS[BountyTime4]}',
			BountyID5 ='{$HTTP_POST_VARS[BountyID5]}',
			BountyTime5 ='{$HTTP_POST_VARS[BountyTime5]}',
			BountyID6 ='{$HTTP_POST_VARS[BountyID6]}',
			BountyTime6 ='{$HTTP_POST_VARS[BountyTime6]}',
			BountyID7 ='{$HTTP_POST_VARS[BountyID7]}',
			BountyTime7 ='{$HTTP_POST_VARS[BountyTime7]}',
			BountyID8 ='{$HTTP_POST_VARS[BountyID8]}',
			BountyTime8 ='{$HTTP_POST_VARS[BountyTime8]}',
			BountyID9 ='{$HTTP_POST_VARS[BountyID9]}',
			BountyTime9 ='{$HTTP_POST_VARS[BountyTime9]}',
			BountyID10 ='{$HTTP_POST_VARS[BountyID10]}',
			BountyTime10 ='{$HTTP_POST_VARS[BountyTime10]}'
			*/
		$befores = get_str_rs($dbWc, "SELECT * FROM clan WHERE ClanID='{$HTTP_POST_VARS[ClanID]}'");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM clan WHERE ClanID='{$HTTP_POST_VARS[ClanID]}'");
		
		echo "<form name=form1 method=post action='gmclan.php?a=f&wid=$wid'><input type=hidden name=ClanID value={$HTTP_POST_VARS[ClanID]}></form>";
		echo "<script>document.form1.submit()</script>";
		exit();
		break;
}

//$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
//$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error($dbGmAdm));
$htmlWc = "<select name=wid onChange=\"postform(document.form1,'gmclan.php?a=wc')\"><option value=''></option>";
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row_wc)
{
	$selected=($wid==$row_wc[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row_wc[id]}' $selected>{$row_wc[name]}</option>";
}
$htmlWc.="</select>";
//mysql_free_result($rsWc);

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
<form name="form1" method="post" action="">
<h3>Clan</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{
	$SELECTED_1 = $SELECTED_2 = $SELECTED_3 = $SELECTED_4 = $SELECTED_5 = $SELECTED_100 = "";
	eval("\$SELECTED_{$ClanID} = \"SELECTED\";");
?>
Clan
<select name="ClanID" onchange="if(this.value.length>0)postform(document.form1,'gmclan.php?a=f')">
	<option value=''>-- Please Select One --</option>
	<option value=1 <?=$SELECTED_1?>>Supreme Sword Clan</option>
	<option value=2 <?=$SELECTED_2?>>King of Heroes Clan</option>
	<option value=3 <?=$SELECTED_3?>>Matchless Clan</option>
	<option value=4 <?=$SELECTED_4?>>Sword Worship Clan</option>
	<option value=5 <?=$SELECTED_5?>>Swift Meaning Clan</option>
	<option value=100 <?=$SELECTED_100?>>* Game Master *</option>
</select>
<!--ClanID: <input name="ClanID" type="text" size="9" value="<?=$ClanID?>"-->
<!--<input type=button value="Search" onclick="postform(document.form1,'gmclan.php?a=f')">-->
<!--<input type=button onclick="window.open('clanlist.php?a=f&wid=<?=$wid?>&i='+document.form1.ClanID.value)" value="Clan List">-->
<?
	if($HTTP_GET_VARS["a"]=="f")
	{
		if($num_row != 0)
		{
?>
<hr/><b><?=$clan_name[$ClanID]?></b>
(<a href="clanally.php?a=f&wid=<?=$wid?>&i=<?=$ClanID?>">Clan Hall List</a>)

<table border="1" cellspacing=0>
    <tr>
      <td>Gold</td><td><input name="Gold" value="<?=$row[Gold]?>" type="text" size="9"></td>
    </tr>
    <tr>
      <td>Prestige</td><td><input name="Prestige" value="<?=$row[Prestige]?>" type="text" size="9"></td>
    </tr>
    <tr>
      <td>Type</td><td><input name="Type" value="<?=$row[Type]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>Relic1</td><td><input name="Relic1" value="<?=$row[Relic1]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic1])
			{
				$str=getstring($row[Relic1],"item");
				echo "<a href='uitem.php?i={$row[Relic1]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
      <td>Relic1 Flag</td><td><input name="Relic1Flag" value="<?=$row[Relic1Flag]?>" type="text" size="9"></td>
*/
?>
    </tr>
    <tr>
      <td>Relic2</td><td><input name="Relic2" value="<?=$row[Relic2]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic2])
			{
				$str=getstring($row[Relic2],"item");
				echo "<a href='item.php?i={$row[Relic2]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic2 Flag</td><td><input name="Relic2Flag" value="<?=$row[Relic2Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
</tr>
    <tr>
      <td>Relic3</td><td><input name="Relic3" value="<?=$row[Relic3]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic3])
			{
				$str=getstring($row[Relic3],"item");
				echo "<a href='item.php?i={$row[Relic3]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic3 Flag</td><td><input name="Relic3Flag" value="<?=$row[Relic3Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
    <tr>
      <td>Relic4</td><td><input name="Relic4" value="<?=$row[Relic4]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic4])
			{
				$str=getstring($row[Relic4],"item");
				echo "<a href='item.php?i={$row[Relic4]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic4 Flag</td><td><input name="Relic4Flag" value="<?=$row[Relic4Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
    <tr>
      <td>Relic5</td><td><input name="Relic5" value="<?=$row[Relic5]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic5])
			{
				$str=getstring($row[Relic5],"item");
				echo "<a href='item.php?i={$row[Relic5]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic5 Flag</td><td><input name="Relic5Flag" value="<?=$row[Relic5Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
    <tr>
      <td>Relic6</td><td><input name="Relic6" value="<?=$row[Relic6]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic6])
			{
				$str=getstring($row[Relic6],"item");
				echo "<a href='item.php?i={$row[Relic6]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic6 Flag</td><td><input name="Relic6Flag" value="<?=$row[Relic6Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
    <tr>
      <td>Relic7</td><td><input name="Relic7" value="<?=$row[Relic7]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic7])
			{
				$str=getstring($row[Relic7],"item");
				echo "<a href='item.php?i={$row[Relic7]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic7 Flag</td><td><input name="Relic7Flag" value="<?=$row[Relic7Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
    <tr>
      <td>Relic8</td><td><input name="Relic8" value="<?=$row[Relic8]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic8])
			{
				$str=getstring($row[Relic8],"item");
				echo "<a href='item.php?i={$row[Relic8]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
      <td>Relic8 Flag</td><td><input name="Relic8Flag" value="<?=$row[Relic8Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic9</td><td><input name="Relic9" value="<?=$row[Relic9]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic9])
			{
				$str=getstring($row[Relic9],"item");
				echo "<a href='item.php?i={$row[Relic9]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic9 Flag</td><td><input name="Relic9Flag" value="<?=$row[Relic9Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic10</td><td><input name="Relic10" value="<?=$row[Relic10]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic10])
			{
				$str=getstring($row[Relic10],"item");
				echo "<a href='item.php?i={$row[Relic10]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic10 Flag</td><td><input name="Relic10Flag" value="<?=$row[Relic10Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	</tr>
	    <tr>
	      <td>Relic11</td><td><input name="Relic11" value="<?=$row[Relic11]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic11])
			{
				$str=getstring($row[Relic11],"item");
				echo "<a href='item.php?i={$row[Relic11]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic11 Flag</td><td><input name="Relic11Flag" value="<?=$row[Relic11Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic12</td><td><input name="Relic12" value="<?=$row[Relic12]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic12])
			{
				$str=getstring($row[Relic12],"item");
				echo "<a href='item.php?i={$row[Relic12]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic12 Flag</td><td><input name="Relic12Flag" value="<?=$row[Relic12Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic13</td><td><input name="Relic13" value="<?=$row[Relic13]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic13])
			{
				$str=getstring($row[Relic13],"item");
				echo "<a href='item.php?i={$row[Relic13]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic13 Flag</td><td><input name="Relic13Flag" value="<?=$row[Relic13Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic14</td><td><input name="Relic14" value="<?=$row[Relic14]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic14])
			{
				$str=getstring($row[Relic14],"item");
				echo "<a href='item.php?i={$row[Relic14]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic14 Flag</td><td><input name="Relic14Flag" value="<?=$row[Relic14Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic15</td><td><input name="Relic15" value="<?=$row[Relic15]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic15])
			{
				$str=getstring($row[Relic15],"item");
				echo "<a href='item.php?i={$row[Relic15]}&wid=$wid' target='item'>$str</a>";
			}
			?>
	</td>
<?
/*
	      <td>Relic15 Flag</td><td><input name="Relic15Flag" value="<?=$row[Relic15Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
	    </tr>
	    <tr>
	      <td>Relic16</td><td><input name="Relic16" value="<?=$row[Relic16]?>" type="text" size="9" <?=$readonly?>>
			<?
			if($row[Relic16])
			{
				$str=getstring($row[Relic16],"item");
				echo "<a href='item.php?i={$row[Relic16]}&wid=$wid' target='item'>$str</a>";
			}
			?>
</td>
<?
/*
      <td>Relic16 Flag</td><td><input name="Relic16Flag" value="<?=$row[Relic16Flag]?>" type="text" size="9" <?=$readonly?>></td>
*/
?>
    </tr>
<?
/*
    <tr>
      <td>BountyID1</td><td><input name="BountyID1" value="<?=$row[BountyID1]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime1</td><td><input name="BountyTime1" value="<?=$row[BountyTime1]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID2</td><td><input name="BountyID2" value="<?=$row[BountyID2]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime2</td><td><input name="BountyTime2" value="<?=$row[BountyTime2]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID3</td><td><input name="BountyID3" value="<?=$row[BountyID3]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime3</td><td><input name="BountyTime3" value="<?=$row[BountyTime3]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID4</td><td><input name="BountyID4" value="<?=$row[BountyID4]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime4</td><td><input name="BountyTime4" value="<?=$row[BountyTime4]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID5</td><td><input name="BountyID5" value="<?=$row[BountyID5]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime5</td><td><input name="BountyTime5" value="<?=$row[BountyTime5]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID6</td><td><input name="BountyID6" value="<?=$row[BountyID6]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime6</td><td><input name="BountyTime6" value="<?=$row[BountyTime6]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID7</td><td><input name="BountyID7" value="<?=$row[BountyID7]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime7</td><td><input name="BountyTime7" value="<?=$row[BountyTime7]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID8</td><td><input name="BountyID8" value="<?=$row[BountyID8]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime8</td><td><input name="BountyTime8" value="<?=$row[BountyTime8]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID9</td><td><input name="BountyID9" value="<?=$row[BountyID9]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime9</td><td><input name="BountyTime9" value="<?=$row[BountyTime9]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
    <tr>
      <td>BountyID10</td><td><input name="BountyID10" value="<?=$row[BountyID10]?>" type="text" size="9" <?=$readonly?>></td>
      <td>BountyTime10</td><td><input name="BountyTime10" value="<?=$row[BountyTime10]?>" type="text" size="9" <?=$readonly?>></td>
    </tr>
*/
?>
  </table>
    <input type="reset" name="Reset" value="Reset" onclick="return(confirm('Undo all changes?'))">
	<input type=button value="Save" onclick="if(confirm('Overwrite?'))postform(document.form1,'gmclan.php?a=s')">
<?
		}
		else
		{
			echo "<br><font color=red><b>No matched queries</b></font>";
		}
	}
}
?>
</form>
</body>
</html>
