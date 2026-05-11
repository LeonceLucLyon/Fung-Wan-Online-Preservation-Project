<?php
require("auth.php");
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
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}


$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'item.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);


if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST' || $HTTP_GET_VARS['i'])
	{
		if($HTTP_GET_VARS[a]=="s")
		{
			if($HTTP_POST_VARS["ids"])
			{
				$indxes = split("\|",$HTTP_POST_VARS["ids"]);

				$ppls_items=null;
				foreach($indxes as $indx)
				{
					if($indx=="")break;
					$Weight=$HTTP_POST_VARS["Weight_{$indx}"];
					$LevelGroup=$HTTP_POST_VARS["LevelGroup_{$indx}"];
					$BuyPrice=$HTTP_POST_VARS["BuyPrice_{$indx}"];
					$PopLimit=$HTTP_POST_VARS["PopLimit_{$indx}"];
					$Identify=$HTTP_POST_VARS["Identify_{$indx}"];
					$Field1=$HTTP_POST_VARS["Field1_{$indx}"];
					$Field2=$HTTP_POST_VARS["Field2_{$indx}"];
					$Field3=$HTTP_POST_VARS["Field3_{$indx}"];
					$Field4=$HTTP_POST_VARS["Field4_{$indx}"];
					$Field5=$HTTP_POST_VARS["Field5_{$indx}"];
					$Field6=$HTTP_POST_VARS["Field6_{$indx}"];
					$Field7=$HTTP_POST_VARS["Field7_{$indx}"];
					$Field8=$HTTP_POST_VARS["Field8_{$indx}"];
					$Field9=$HTTP_POST_VARS["Field9_{$indx}"];
					$Field10=$HTTP_POST_VARS["Field10_{$indx}"];
					$Field11=$HTTP_POST_VARS["Field11_{$indx}"];
					$Field12=$HTTP_POST_VARS["Field12_{$indx}"];
					$DecayValue=$HTTP_POST_VARS["DecayValue_{$indx}"];
					$DecayRate=$HTTP_POST_VARS["DecayRate_{$indx}"];

					$query_rs = "UPDATE item SET
						Weight='{$Weight}',
						LevelGroup='{$LevelGroup}',
						BuyPrice='{$BuyPrice}',
						PopLimit='{$PopLimit}',
						Identify='{$Identify}',
						Field1='{$Field1}',
						Field2='{$Field2}',
						Field3='{$Field3}',
						Field4='{$Field4}',
						Field5='{$Field5}',
						Field6='{$Field6}',
						Field7='{$Field7}',
						Field8='{$Field8}',
						Field9='{$Field9}',
						Field10='{$Field10}',
						Field11='{$Field11}',
						Field12='{$Field12}',
						DecayValue='{$DecayValue}',
						DecayRate='{$DecayRate}'
						WHERE ItemID='{$indx}'";

					$befores = get_str_rs($dbWc, "SELECT * FROM item WHERE ItemID='$indx'");
					$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
					$after = get_str_rs($dbWc, "SELECT * FROM item WHERE ItemID='$indx'");
					
				}
			}
			header ("Location: item.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
		elseif($HTTP_GET_VARS[a]=="d")
		{
		}

		$searchby=$HTTP_POST_VARS[searchby];
		$itemid=$HTTP_POST_VARS[itemid];
		$hbyte=$HTTP_POST_VARS[hbyte];
		$lbyte=$HTTP_POST_VARS[lbyte];

		$group_id=is_numeric($hbyte)?(float)$hbyte * 65536: 0;
		$type_id=is_numeric($lbyte)?(float)$lbyte: 0;

		if($HTTP_GET_VARS[i])
		{
			$sql_cond="ItemID='{$HTTP_GET_VARS[i]}'";
		}
		elseif($searchby == '')
		{
			$sql_cond="ItemID='$itemid'";
		}
		elseif($type_id==0){
			$sql_cond="ItemID>$group_id AND ItemID<($group_id + 65536)";
		}
		else
		{
			$sql_cond="ItemID='". ($group_id+$type_id) ."'";
		}

		$query_rs = "SELECT * FROM item WHERE $sql_cond";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	}
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
	form.method='post';form.action=url;form.submit()
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
<form name="form1" method="post" action="">
<h3>Game Item</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
	$searchby_checked = $searchby_checked0 = '';
	eval("\$searchby_checked{$HTTP_POST_VARS['searchby']} = 'CHECKED';");
?>
Search item(s) by
<table border=1 cellspacing=0>
	<tr>
		<td><input type=radio name=searchby value='' <?=$searchby_checked?> onclick="with(document.form1){hbyte.value='';lbyte.value=''}"></td></td><td><table><tr><td width=80>Item ID</td><td><input name=itemid value="<?=$HTTP_GET_VARS[i]?$HTTP_GET_VARS[i]:$HTTP_POST_VARS[itemid]?>" size=8></td></tr></table></td>
	</tr>
	<tr>
		<td valign=top rowspan=2><input type=radio name=searchby value=0 <?=$searchby_checked0?> onclick="document.form1.itemid.value=''"></td>
		<td>
			<table>
				<tr>
					<td width=80>Item group</td><td><input name=hbyte value="<?=$HTTP_POST_VARS[hbyte]?>" size=8></td>
				</tr>
				<tr>
					<td>Item type</td><td><input name=lbyte value="<?=$HTTP_POST_VARS[lbyte]?>" size=8></td>
				</tr>
			</table>
		</td>
</table>
<input type=button value="Submit" onclick="postform(document.form1,'item.php?a=f')">
	<?
	if($rs)
	{
		if(mysql_num_rows($rs) < 1)
		{
			echo "<p><font color=red><b>No matched queries.</b></font>";
		}
		else
		{
	?>
<hr>
  <table border="1" cellspacing=0>
    <tr>
      <td>Item ID</td>
      <td>Weight</td>
      <td>LevelGrp</td>
      <td>BuyPrice</td>
      <td>PopLimit</td>
      <td>Identify</td>
      <td>Field1</td>
      <td>Field2</td>
      <td>Field3</td>
      <td>Field4</td>
      <td>Field5</td>
      <td>Field6</td>
      <td>Field7</td>
      <td>Field8</td>
      <td>Field9</td>
      <td>Field10</td>
      <td>Field11</td>
      <td>Field12</td>
      <td>DecayValue</td>
      <td>DecayRate</td>
    </tr>
		<?
		while($row=mysql_fetch_assoc($rs))
		{
			$i=$row[ItemID];
			$is_unique_item = (($i >> 16) > 100) || (($i >> 16) == 21);
			$readonly = ($readonly_gmdata)?"READONLY":"";

			if($is_unique_item)
			{
				$unique_item = "<a href='uitem.php?i={$i}&wid=$wid'>$i</a>";
			}
			else
			{
				$unique_item = "$i";
			}
		?>
    <tr>
      <td><nobr><?=$unique_item?> <?=getstring($row[ItemID],'item')?></nobr></td>
      <td><input name="Weight_<?=$i?>" type="text" value="<?=$row[Weight]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="LevelGroup_<?=$i?>" type="text" value="<?=$row[LevelGroup]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="BuyPrice_<?=$i?>" type="text" value="<?=$row[BuyPrice]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="PopLimit_<?=$i?>" type="text" value="<?=$row[PopLimit]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Identify_<?=$i?>" type="text" value="<?=$row[Identify]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field1_<?=$i?>" type="text" value="<?=$row[Field1]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field2_<?=$i?>" type="text" value="<?=$row[Field2]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field3_<?=$i?>" type="text" value="<?=$row[Field3]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field4_<?=$i?>" type="text" value="<?=$row[Field4]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field5_<?=$i?>" type="text" value="<?=$row[Field5]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field6_<?=$i?>" type="text" value="<?=$row[Field6]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field7_<?=$i?>" type="text" value="<?=$row[Field7]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field8_<?=$i?>" type="text" value="<?=$row[Field8]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field9_<?=$i?>" type="text" value="<?=$row[Field9]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field10_<?=$i?>" type="text" value="<?=$row[Field10]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field11_<?=$i?>" type="text" value="<?=$row[Field11]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="Field12_<?=$i?>" type="text" value="<?=$row[Field12]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="DecayValue_<?=$i?>" type="text" value="<?=$row[DecayValue]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>></td>
      <td><input name="DecayRate_<?=$i?>" type="text" value="<?=$row[DecayRate]?>" size=5 onchange="saveid(<?=$i?>)" <?=$readonly?>>
	      <input type="hidden" name="mark_<?=$i?>" value="">
	</td>
    </tr>
		<?
		}
		?>
  </table>
		<?
		if(!$readonly_gmdata)
		{
		?>
  	<input type="hidden" name="ids" value="">
	<input type="reset" name="Reset" value="Reset" onclick="return(confirm('undo all changes?'))">
	<input type="button" name="Button" value="Save" onClick="if(document.form1.ids.value==''){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'item.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')">
  	<?
		}
	}
}
}
?>
</form>
</body>
</html>