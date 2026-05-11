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
$htmlWc="<select name=wid onChange=\"postform(document.form1,'cleancharinv.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);

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
  <table border="0">
    <!--tr>
      <td>Authentication System</td>
      <td><!--select name="authsys_id" id="authsys_id" onChange="MM_jumpMenu('parent',this,0)">
          <option selected>unnamed1</option>
        </select--><?=$htmlAs?></td>
    </tr-->
    <tr>
      <td>World Controller</td>
      <td><?=$htmlWc?></td>
    </tr>
  </table>
  <br>
<?
if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	echo "<input type=\"button\" name=\"Button\" value=\"cleanup invalid item(s) in PCs' inventories\" onClick=\"if(confirm('CleanUp?'))postform(document.form1,'cleancharinv.php?a=d')\">";

	if($HTTP_GET_VARS['a']=='d')
	{
		$sum=0;
		for($n=0;$n<=9;$n++)
		{
//			$rs_affected=mysql_query("UPDATE charinv_$n SET ItemID=0, Quantity=0, Identified=0, Durability=0, Field1=0, Field2=0, Field3=0, Field4=0, Field5=0, Hardness=0 WHERE ItemID='0'", $dbWc);
//			$sum+=mysql_affected_rows();

			$rs_invalid_itemid=mysql_query("SELECT distinct(c.ItemID) FROM charinv_$n c LEFT JOIN item i ON c.itemid=i.itemid WHERE buyprice IS NULL AND c.ItemID!=0", $dbWc) or die(mysql_error());
			if($rs_invalid_itemid)
			{
				while($row=mysql_fetch_assoc($rs_invalid_itemid))
				{
					$rs_affected=mysql_query("UPDATE charinv_$n SET ItemID=0, Quantity=0, Identified=0, Durability=0, Field1=0, Field2=0, Field3=0, Field4=0, Field5=0, Hardness=0 WHERE ItemID='{$row[ItemID]}'", $dbWc);
					$sum+=mysql_affected_rows();
				}
				mysql_free_result($rs_invalid_itemid);
			}
		}
		echo "<p>removed $sum invalid item(s) from PCs' inventories";
	}
}
?>
</body>
</html>
