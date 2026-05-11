<?php
require("auth.php");
$rpp = 20;

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
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid worldcontroller");
	mysql_free_result($rsSvr);

	$rsLog = mysql_query("SELECT * FROM gm_server WHERE ip='{$row_rsSvr[ip]}' AND type = 'lg' ", $dbGmAdm) ;
	//echo "SELECT * FROM gm_server WHERE ip='{".$row_rsSvr[ip]."}' AND type = 'lg' ";


	while($row_rsLog = mysql_fetch_assoc($rsLog))
	{
		$dbLog = mysql_pconnect($row_rsLog[ip],$row_rsLog[dbuser],$row_rsLog[dbpasswd]);
		mysql_select_db($row_rsLog[db], $dbLog);

	}

}
$query_grps = "SELECT logid as logid,
			groups as groups,
			logmsg as logmsg
			FROM groups
			ORDER BY logid";



$html = "<tr><th>LogID</th><th>Groups</th><th>Group Name</th><th>Log Message</th></tr>";

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
			$rs_grps = mysql_query($query_grps) or die(mysql_error());


		}

	}



$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'gmcharloggrp.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row_wc)
{
	$selected=($wid==$row_wc[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row_wc[id]}' $selected>{$row_wc[name]}</option>";

}
$htmlWc.="</select>";





if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid worldcontroller");
	mysql_free_result($rsSvr);

	$rsLog = mysql_query("SELECT * FROM gm_server WHERE ip='{$row_rsSvr[ip]}' AND type = 'lg' ", $dbGmAdm) ;
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


				$Delete=($HTTP_POST_VARS["Delete_{$indx}"]=="on")?1:0;

				for($i = 0; $i < 32; $i++)
				{
					(int)$a = abs(1 << $i);
					$b = ($HTTP_POST_VARS["Groups{$a}_{$indx}"] == "on")? $a : 0;
					$Groups+=$b;

				}

				$tbls[] = "groups";

				if($Delete == 1)
				{
					$query_rs = "DELETE FROM groups WHERE logid={$LogID}";

					$update_sqls[] = $query_rs;
				}
				else
				{
					$query_rs = "UPDATE groups SET
						logmsg='{$LogMessage}',
						groups = {$Groups}
						WHERE logid={$LogID}";

					$update_sqls[] = $query_rs;
					//echo $query_rs;
				}
			}

			reset($tbls);

			foreach($update_sqls as $sql)
			{

				$tbl = current($tbls);
				next($tbls);

				$rs = mysql_query($sql, $dbLog) or die(mysql_error($dbLog));
				//echo"<br>".$sql;
			}

			header ("Location: gmcharloggrp.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
		elseif($HTTP_GET_VARS[a]=="a")
		{



			$LogID=$HTTP_POST_VARS["LogID"];
			$Groups=$HTTP_POST_VARS["Groups"];
			$GroupName=$HTTP_POST_VARS["GroupName"];
			$LogMessage=$HTTP_POST_VARS["LogMessage"];

			//echo $Groups;


			$query_grpnm = "INSERT INTO groups (logid, groups, logmsg) VALUES({$LogID}, {$Groups}, '{$LogMessage}')";

			mysql_query($query_grpnm, $dbLog) or die(mysql_error($dbLog));


			header ("Location: gmcharloggrp.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
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
<h3>Player Activity Log Settings</h3>

(World Controller: <?=$htmlWc?>)
    <?
	if($rs_grps)
	{
		if(mysql_num_rows($rs_grps) < 1)
		{
			echo "<p><font color=red><b>No matched queries.</b></font>";
		}
		else
		{
			$ar_grpnm = array();
			$query_grpnm = "SELECT groups as groups,
						group_name as group_name
						FROM groupname
						ORDER BY groups";

			$rs_grpnm = mysql_query($query_grpnm) or die(mysql_error());
			while($row_grpnm = mysql_fetch_assoc($rs_grpnm))
			{
				array_push($ar_grpnm, $row_grpnm);

			}
			//print_r($ar_grpnm);

			$htmlGrpnm="<select name=Groups ><option value=''></option>";
			foreach($ar_grpnm as $row)
			{

				$htmlGrpnm.="<option value='{$row[groups]}' >{$row[group_name]}</option>";

			}
			$htmlGrpnm.="</select>";

?>
<br><br>
	<table border="0" cellspacing=0>
    <tr>
      <td>Log ID</td>
      <td>Group Name</td>
      <td>Log Message Template</td>
    </tr>

    <tr>
	<td><input name="LogID" type="text" value="<?=$row_grps[logid]?>" size=12 maxlength=10 onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
	<!--<td><input name="GroupName" type="text" value="<?=$row[group_name]?>" size=12 maxlength=10 onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>-->
	<td><?=$htmlGrpnm?></td>
	<td> <input name="LogMessage" type="text" maxlength=200 size=100 value="<?=$row[logmsg]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>

	<td><input type="button" value="Add" onClick="if(document.form1.LogID.value+document.form1.Groups.value+document.form1.LogMessage.value==''){alert('Data not completed.');return 0}postform(document.form1,'gmcharloggrp.php?a=a')"></td>
    </tr>
    </table>

<hr>

  <table border="1" cellspacing=0>
    <tr>
      <td>Log ID</td>
      <td>Log Message Template</td>
      <td>Changed <div style="display:none"><input type="checkbox" name="affected[]"></div></td>
      <td>Delete</td>
    </tr>


		<?
		$idx = 0;

		while($row_grps = mysql_fetch_assoc($rs_grps))
		{
			$idx++;

			$i = $row_grps[logid];


			$selected=($wid==$row_wc[id])?"SELECTED":"";


			$color_idx = ($idx%2 == 0)?"#FFFF66":"";



		?>

		<tr bgcolor = <?=$color_idx?>>


      <td><?=$row_grps[logid]?><input name="LogID_<?=$i?>" type="hidden" value="<?=$row_grps[logid]?>" size=12 maxlength=10 ></td>




      <td> <input name="LogMessage_<?=$i?>" type="text" maxlength=200 size=100 value="<?=$row_grps[logmsg]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>

      <td><input type="checkbox" name="affected[]" value="<?=$i?>">
      <td><input type="checkbox" name="Delete_<?=$i?>"   onclick="document.form1.elements('affected[]').item(<?=$idx?>).checked=1">
    </tr>
    <tr bgcolor = <?=$color_idx?>><td>Group Name</td>
    <td>
      <?
			$n = 0;
		      foreach($ar_grpnm as $row_grp)
			{
				$n++;
				$grp_status = ((int)$row_grp[groups] & (int)$row_grps[groups])?"checked":"";

			?>
			<input type="checkbox" name="Groups<?=$row_grp[groups]?>_<?=$i?>"   onclick="document.form1.elements('affected[]').item(<?=$idx?>).checked=1" <?=$grp_status?>>

			<?
				echo $row_grp[group_name];

			}?>

      </td><td><td>
    </tr>

		<?
		} //while
		?>
  </table>
	<?=$html_page?>
	<td>*Please use IE to view this page</td>
	<input type="hidden" name="ids" value="">
	<br><br>
	<input type="button" name="Button" value="Save" onClick="var save=false;with(document.form1){for(var n=0;n<elements('affected[]').length;n++){save=elements('affected[]').item(n).checked;if(save)break}if(!save){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'gmcharloggrp.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')}">
	<?
	}
}


?>
</form>
</body>
</html>
