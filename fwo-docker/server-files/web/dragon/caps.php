<?php
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
        $type = $HTTP_POST_VARS["CapType_{$HTTP_GET_VARS[i]}"];
        $level = $HTTP_POST_VARS["CapLevel_{$HTTP_GET_VARS[i]}"];
        
        $befores = get_str_rs($dbWORLD, "SELECT * FROM Caps WHERE CapType='{$HTTP_GET_VARS[i]}';");
        $query_rs = "UPDATE Caps SET Level='$level' WHERE CapType='$type'";
        $act_change = mysql_query($query_rs,$dbWORLD) or die (mysql_error($dbWORLD));
        $after = get_str_rs($dbWORLD, "SELECT * FROM Caps WHERE CapType='{$HTTP_GET_VARS[i]}';");
        
       
        break;
}

if(!($qry_caps = mysql_query("SELECT * FROM Caps",$dbWORLD)))
{
    $warn = "<font color=red>\"Caps\" table not found.</font></td></tr>";
    echo $warn;
}
else
{
    while ($rs_caps = mysql_fetch_assoc($qry_caps))
    {
        $html_rs .= '<tr><td><input type="hidden" name="CapType_' . $rs_caps[CapType] . '" value="' . $rs_caps[CapType] . '">' . $rs_caps[CapType] . '</td>';
        $html_rs .= '<td><input type="text" name="CapLevel_' . $rs_caps[CapType] . '" value="' . $rs_caps[Level] . '"></td>';
        if ($rs_caps[CapType]=="1")
        {
            $minLevel = 1;
            $maxLevel = 220;
        }
        elseif ($rs_caps[CapType]=="2")
        {
            $minLevel = 0;
            $maxLevel = 999;
        }
        $html_rs .= '<td><input name="button" type="button" onClick="if(document.form1.CapLevel_' . $rs_caps[CapType] . '.value.length<1){alert (\'Please enter the level.\'); return 0};if(document.form1.CapLevel_' . $rs_caps[CapType] . '.value<' . $minLevel . ' || document.form1.CapLevel_' . $rs_caps[CapType] . '.value>' . $maxLevel . '){alert (\'Please enter the level between ' . $minLevel . ' and ' . $maxLevel . '.\'); postform(document.form1,\'caps.php\'); return 0};if(confirm(\'Overwrite?\'))postform(document.form1,\'caps.php?a=s&i=' . $rs_caps[CapType] . '\')" value="Save"></td></tr>';
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
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Level Cap</h3>

(World Controller: <?=$htmlWc?>)
<hr>
  <table border="1" cellspacing=0>
    <tr>
      <td>Caps Type</td>
      <td>Value</td>
      <td>Action</td>
    </tr>
    <?=$html_rs?>
  </table>
Cap Type 1 = Level Cap
<br>
Cap Type 2 = Experience Point Percentage Modifier
<br><br>
<font color="red">Please restart zoneserver after the changes have been made.</font>
</form>
</body>
</html>
