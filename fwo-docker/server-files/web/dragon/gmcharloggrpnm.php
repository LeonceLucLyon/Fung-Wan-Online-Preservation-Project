<?php
require("auth.php");
$rpp = 20;
$gmchar_var = 0;

//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "conf", ""))
{
	die("Access denied.");
}

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}

$dbWc = NULL;

if($wid)
{
    /*
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid worldcontroller");
	mysql_free_result($rsSvr);
    */
	$rsLog = mysql_query("SELECT * FROM gm_server WHERE wid='{$wid}' AND type = 'lg' ", $dbGmAdm) ;
	//echo "SELECT * FROM gm_server WHERE ip='{".$row_rsSvr[ip]."}' AND type = 'lg' ";


	while($row_rsLog = mysql_fetch_assoc($rsLog))
	{
		$dbLog = mysql_pconnect($row_rsLog[ip],$row_rsLog[dbuser],$row_rsLog[dbpasswd]);
		mysql_select_db($row_rsLog[db], $dbLog);

	}

}

$query_grps = "SELECT logid as logid,
			groups as groups
			FROM groups
			ORDER BY groups";

$query_grpnm = "SELECT
			groups as groups,
			is_on as is_on,
			group_name as group_name
			FROM groupname
			ORDER BY groups
			";
$html = "<tr><th>Groups</th><th>Group Name</th></tr>";

//echo $query;


	if($wid)
	{
		if(mysql_num_rows($rsLog) == 0)
		{
			$warn = "<tr><td colspan=5><font color=red>No matched fwcharlog DB found.</font></td></tr>";
			echo $warn;
		}
		else
		{
			$rs_grpnm = mysql_query($query_grpnm) or die(mysql_error());
		}
	}



$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'gmcharloggrpnm.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row_wc)
{
	$selected=($wid==$row_wc[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row_wc[id]}' $selected>{$row_wc[name]}</option>";
}
$htmlWc.="</select>";

if($wid)
{
    /*
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid worldcontroller");
	mysql_free_result($rsSvr);
    */
	$rsLog = mysql_query("SELECT * FROM gm_server WHERE wid='{$wid}' AND type = 'lg' ", $dbGmAdm) ;
	//echo "SELECT * FROM gm_server WHERE ip='{".$row_rsSvr[ip]."}' AND type = 'lg' ";

	if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST' || $HTTP_GET_VARS['i'])
	{

		if($HTTP_GET_VARS[a]=="s")
		{

			$indxes = $HTTP_POST_VARS["affected"];
			$update_sqls = array();
			$log_sqls = array();
			$tbls = array();
			foreach($indxes as $indx)
			{
				$LogID=$HTTP_POST_VARS["LogID_{$indx}"];
				$Groups=$HTTP_POST_VARS["Groups_{$indx}"];
				$GroupName=$HTTP_POST_VARS["GroupName_{$indx}"];
				$LogMessage=$HTTP_POST_VARS["LogMessage_{$indx}"];
				$Is_On=($HTTP_POST_VARS["IsOn_{$indx}"]=="on")?1:0;
				$Delete=($HTTP_POST_VARS["Delete_{$indx}"]=="on")?1:0;



				$tbls[] = "groupname";


				if($Delete == 1)
				{
					$query_rs = "DELETE FROM groupname WHERE groups='{$Groups}'";


					$update_sqls[] = $query_rs;


				}
				else
				{

					$query_rs = "UPDATE groupname SET group_name='{$GroupName}',
					is_on = {$Is_On}
					WHERE groups='{$Groups}'";


					$update_sqls[] = $query_rs;






				}
			}

			reset($tbls);

			foreach($update_sqls as $sql)
			{
				//$log_sql = current($log_sqls);
				//next($log_sqls);
				$tbl = current($tbls);
				next($tbls);

				$rs = mysql_query($sql, $dbLog) or die(mysql_error($dbLog));
				//echo"<br>".$sql;
			}

			$query_filter="SELECT SUM(groups) as sum FROM groupname WHERE is_on = 1";
			$rs_filter = mysql_query($query_filter, $dbLog) or die(mysql_error($dbLog));
			$row_filter = mysql_fetch_assoc($rs_filter);
			//echo $row_filter[sum];
			mysql_query("UPDATE groupfilter SET filter = {$row_filter[sum]}", $dbLog);

			header ("Location: gmcharloggrpnm.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
		elseif($HTTP_GET_VARS[a]=="a")
		{



			$LogID=$HTTP_POST_VARS["LogID"];
			$Groups=$HTTP_POST_VARS["Groups"];
			$GroupName=$HTTP_POST_VARS["GroupName"];
			$LogMessage=$HTTP_POST_VARS["LogMessage"];



			$query_grpnm = "INSERT INTO groupname (groups,group_name) VALUES({$Groups}, '{$GroupName}')";

			//echo $query_grpnm;

			mysql_query($query_grpnm, $dbLog) or die(mysql_error($dbLog));

			header ("Location: gmcharloggrpnm.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;

		}

	}

	while($row_rsLog = mysql_fetch_assoc($rsLog))
	{
		$dbLog = mysql_pconnect($row_rsLog[ip],$row_rsLog[dbuser],$row_rsLog[dbpasswd]);
		mysql_select_db($row_rsLog[db], $dbLog);

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
<h3>Player Activity Log Group Name Settings</h3>

(World Controller: <?=$htmlWc?>)
    <?
	if($rs_grpnm)
	{
		if(mysql_num_rows($rs_grpnm) < 1)
		{
			echo "<p><font color=red><b>No matched queries.</b></font>";
		}
		else
		{
?>
<?
    if ($gmchar_var == "1"){
?>
<br><br>
	<table border="0" cellspacing=0>
    <tr>
      <td>Group</td>
      <td>Group Name</td>

    </tr>

    <tr>

	<td><input name="Groups" type="text"  size=12 maxlength=10 ></td>
	<td><input name="GroupName" type="text"  size=12 maxlength=10 ></td>


	<td><input type="button" value="Add Group" onClick="if(document.form1.Groups.value+document.form1.GroupName.value==''){alert('Data not completed.');return 0}postform(document.form1,'gmcharloggrpnm.php?a=a')"></td>
    </tr>
    </table>
<?
}
?>
<br>


<hr>

  <table border="1" cellspacing=0>
    <tr>
      <td>Group</td>
      <td>Group Name</td>
      <td>Set On</td>
      <td>Changed <div style="display:none"><input type="checkbox" name="affected[]"></div></td>
      <?
        if ($gmchar_var == "1"){
      ?>
      <td>Delete</td>
      <?
        }
      ?>
    </tr>

		<?
		$idx = 0;
		while($row = mysql_fetch_assoc($rs_grpnm))
		{
			$idx++;
			$i = $row[groups];

			$selected=($wid==$row_wc[id])?"SELECTED":"";
			$chk_status = ($row[is_on]==1)?"checked":"";

		?>
		<tr onmouseover="this.className='hl'" onmouseout="this.className=''">



      <td><?=$row[groups]?><input name="Groups_<?=$i?>" type="hidden" value="<?=$row[groups]?>"></td>
      <td><? if ($gmchar_var == "1"){ ?><input name="GroupName_<?=$i?>" type="text" value="<?=$row[group_name]?>" size=12 maxlength=10 onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"><? }else{ ?><input name="GroupName_<?=$i?>" type="text" value="<?=$row[group_name]?>" size=12 maxlength=10 readonly="" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"><? } ?></td>

      <td><input type="checkbox" name="IsOn_<?=$i?>" onclick="document.form1.elements('affected[]').item(<?=$idx?>).checked=1" <?=$chk_status?>>
      <td><input type="checkbox" name="affected[]" value="<?=$i?>">
      <?
        if ($gmchar_var == "1"){
      ?>
      <td><input type="checkbox" name="Delete_<?=$i?>"   onclick="document.form1.elements('affected[]').item(<?=$idx?>).checked=1">
      <?
        }
      ?>
    </tr>

		<?
		} //while
		?>
  </table>
	<?=$html_page?>
	<td>*Please use IE to view this page</td>
	<input type="hidden" name="ids" value="">
	<br><br>
	<input type="button" name="Button" value="Save" onClick="var save=false;with(document.form1){for(var n=0;n<elements('affected[]').length;n++){save=elements('affected[]').item(n).checked;if(save)break}if(!save){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'gmcharloggrpnm.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')}">
	<?
	}
}
?>
</form>
</body>
</html>
