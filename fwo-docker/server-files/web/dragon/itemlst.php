<?php
$rpp  = 50;
$offset = 0;

require("auth.php");

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}

if($wid)
{
    $rsLog = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) ;
    
	$row_rsLog=mysql_fetch_assoc($rsLog) or die("invalid worldcontroller");
	mysql_free_result($rsLog);
	
	$dbWORLD = mysql_pconnect($row_rsLog[ip],$row_rsLog[dbuser],$row_rsLog[dbpasswd]);
	mysql_select_db($row_rsLog[db], $dbWORLD);
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
$htmlWc="<select name=wid onChange=\"postform(document.form1,'caps.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row_wc)
{
	$selected=($wid==$row_wc[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row_wc[id]}' $selected>{$row_wc[name]}</option>";

}
$htmlWc.="</select>";

switch($HTTP_GET_VARS['a'])
{
    case 's':
        if ($HTTP_POST_VARS["affected"])
        {
            $indxes = $HTTP_POST_VARS["affected"];

            foreach($indxes as $indx)
            {
                $itemID=$HTTP_POST_VARS["itemid_{$indx}"];
                $itemRelease=($HTTP_POST_VARS["release_{$indx}"]=="on")?1:0;

                $befores = get_str_rs($dbWORLD, "SELECT ItemID, Release FROM item WHERE ItemID='{$itemID}';");
                $query_rs = "UPDATE item SET Release='{$itemRelease}' WHERE ItemID='{$itemID}'";
                $act_change = mysql_query($query_rs,$dbWORLD) or die (mysql_error($dbWORLD));
                $after = get_str_rs($dbWORLD, "SELECT ItemID, Release FROM item WHERE ItemID='{$itemID}';");
            
            }
        }
		break;
}

if (is_numeric($HTTP_POST_VARS[item_id]))
{
    if (is_numeric($HTTP_POST_VARS[item_id_to]))
    {
        $qry_itemid = " AND ItemID BETWEEN $HTTP_POST_VARS[item_id] AND $HTTP_POST_VARS[item_id_to]";
    }
    else
    {
        $qry_itemid = " AND ItemID BETWEEN $HTTP_POST_VARS[item_id] AND $HTTP_POST_VARS[item_id]";
    }
}

if(!($qry_first = mysql_query("SELECT * FROM item WHERE Display = 1 $qry_itemid",$dbWORLD)))
{
    $warn = "<font color=red>\"item\" table not found.</font></td></tr>";
    echo $warn;
}
else
{
    $total_row = mysql_num_rows($qry_first);
    $page = (int) $_REQUEST['page'];
    if($page < 1) $page = 1;
    $total_pg = ceil($total_row / $rpp);
    if($page > $total_pg) $page = 1;
    $offset = ($page - 1) * $rpp;

	$query = " LIMIT $offset, $rpp";
	$qry_caps = mysql_query("SELECT * FROM item WHERE Display = 1 $qry_itemid $query",$dbWORLD) or die(mysql_error($dbWORLD));

    $idx = 0;
    while ($rs_item = mysql_fetch_assoc($qry_caps))
    {
        $idx++;
        $html_rs .= '<tr><td><input type="hidden" name="itemid_' . $rs_item[ItemID] . '" value="' . $rs_item[ItemID] . '">' . $rs_item[ItemID] . '</td>';
        $html_rs .= '<td><input type="hidden" name="itemname_' . $rs_item[ItemID] . '" value="' . getstring($rs_item[ItemID],'item') . '">' . getstring($rs_item[ItemID],'item') . '</td>';
        
        $status = ($rs_item[Release] == "1")?"CHECKED":"";
        $html_rs .= '<td><input name="release_' . $rs_item[ItemID] . '" type="checkbox" ' . $status . ' onclick="document.form1.elements(\'affected[]\').item(' . $idx . ').checked=1"></td>';
        $html_rs .= '<td><input type="checkbox" name="affected[]" value="' . $rs_item[ItemID] . '"></td></tr>';
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
	form.action=url;form.submit()
}
function postfind(form,url){
	postform(document.form1,'itemlst.php')
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Item Lock List</h3>

(World Controller: <?=$htmlWc?>)
<hr>
<table border=1 cellspacing=0>
    <tr>
        <td>Item ID&nbsp;&nbsp;&nbsp;</td><td>From <INPUT type="text" name="item_id" value="<?=$_REQUEST['item_id']?>" size=13></td><td>To <INPUT type="text" name="item_id_to" value="<?=$_REQUEST['item_id_to']?>" size=13></td>
    </tr>
    <!-- /*
    <tr>
        <td>Item Name&nbsp;</td><td colspan=2 align="right"><INPUT type="text" name="item_name" value="<?=$_REQUEST['item_name']?>" size=37></td>
    </tr>
    */ -->
    <tr>
        <td colspan=3><input type="submit" value="Search"></td>
    </tr>
</table>
<br>
  <table border="1" cellspacing=0>
    <tr>
      <td>Item ID</td>
      <td>Item Name</td>
      <td>Release</td>
      <td>Changed<div style="display:none"><input type="checkbox" name="affected[]"></div></td>
    </tr>
    <?=$html_rs?>
  </table>
<?
if($total_row>0) echo "Found $total_row record(s). Page <input name=page value='$page' size=3>/$total_pg <input type=button value='Go' onclick='postfind()'> <a href='javascript:document.form1.page.value--;postfind()'>Previous</a> <a href='javascript:document.form1.page.value++;postfind()'>Next</a>";
?>
<br><br>
<input name="button" type="button" onClick="if(confirm('Overwrite?'))postform(document.form1,'itemlst.php?a=s')" value="Save">&nbsp;<input name="button" type="button" onClick="postform(document.form1,'itemlst.php')" value="Reset">
<br><br>
<font color="red">Please restart zoneserver after the changes have been made.</font>
</form>
</body>
</html>
