<?php
require("auth.php");
require_once('dbGmAdm.php');

$request_action=$HTTP_GET_VARS[a];

mysql_select_db($database_dbGmAdm, $dbGmAdm);

$enum_status=array(
	0=>'disabled',
	'enabling',
	'running',
	'de-activating'
);

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmevent", ""))
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="e" || $HTTP_GET_VARS[a]=="n" || $HTTP_GET_VARS[a]=="de" || $HTTP_GET_VARS[a]=="ac" || $HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmevent", "w"))
{
	die("Access denied. Read-Only.");
}


$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'gmevent.php?a=wc')\"><option value=''></option>";
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
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_connect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]);
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
	mysql_select_db($row_rsSvr[db], $dbWc);

	$ID=$HTTP_GET_VARS["i"];
	if(!$ID)$ID=$HTTP_POST_VARS['FindID'];

	$Description=$HTTP_POST_VARS["Description"];
	$Status=$HTTP_POST_VARS["Status"];
	$Data1= $HTTP_POST_VARS["Data1"];
	$Data2= $HTTP_POST_VARS["Data2"];
	$Data3= $HTTP_POST_VARS["Data3"];
	$Data4= $HTTP_POST_VARS["Data4"];
	$Data5= $HTTP_POST_VARS["Data5"];
	$Data6= $HTTP_POST_VARS["Data6"];
	$Data7= $HTTP_POST_VARS["Data7"];
	$Data8= $HTTP_POST_VARS["Data8"];
	$Data9= $HTTP_POST_VARS["Data9"];
	$Data10= $HTTP_POST_VARS["Data10"];
	$Data11= $HTTP_POST_VARS["Data11"];
	$Data12= $HTTP_POST_VARS["Data12"];
	$Data13= $HTTP_POST_VARS["Data13"];
	$Data14= $HTTP_POST_VARS["Data14"];
	$Data15= $HTTP_POST_VARS["Data15"];
	$Data16= $HTTP_POST_VARS["Data16"];
	$AScriptID= $HTTP_POST_VARS["AScriptID"];
	$ADBScript= $HTTP_POST_VARS["ADBScript"];
	$DScriptID= $HTTP_POST_VARS["DScriptID"];
	$DDBScript= $HTTP_POST_VARS["DDBScript"];

	if($request_action=="") $request_action="f";

	if($request_action=="s")
	{
		$query_rs = "UPDATE gameevent SET
				ID='{$ID}',
				Description='{$Description}',
				Data1='{$Data1}',
				Data2='{$Data2}',
				Data3='{$Data3}',
				Data4='{$Data4}',
				Data5='{$Data5}',
				Data6='{$Data6}',
				Data7='{$Data7}',
				Data8='{$Data8}',
				Data9='{$Data9}',
				Data10='{$Data10}',
				Data11='{$Data11}',
				Data12='{$Data12}',
				Data13='{$Data13}',
				Data14='{$Data14}',
				Data15='{$Data15}',
				Data16='{$Data16}',
				AScriptID='{$AScriptID}',
				ADBScript='{$ADBScript}',
				DScriptID='{$DScriptID}',
				DDBScript='{$DDBScript}'
				WHERE ID='{$ID}'
		";
		//Status='{$Status}',
		$befores = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		
	}
	elseif($request_action=="a")
	{
		$ID=$HTTP_POST_VARS['newID'];

		if(!$ID)die("Invalid event ID, $ID.");
		$query_rs = "INSERT INTO gameevent(
				ID,
				DESCRIPTION,
				Status,
				Data1,
				Data2,
				Data3,
				Data4,
				Data5,
				Data6,
				Data7,
				Data8,
				Data9,
				Data10,
				Data11,
				Data12,
				Data13,
				Data14,
				Data15,
				Data16,
				AScriptID,
				ADBScript,
				DScriptID,
				DDBScript
			) VALUES (
				'$ID',
				'$Description',
				'$Status',
				'$Data1',
				'$Data2',
				'$Data3',
				'$Data4',
				'$Data5',
				'$Data6',
				'$Data7',
				'$Data8',
				'$Data9',
				'$Data10',
				'$Data11',
				'$Data12',
				'$Data13',
				'$Data14',
				'$Data15',
				'$Data16',
				'$AScriptID',
				'$ADBScript',
				'$DScriptID',
				'$DDBScript'
			)
		";
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		
	}
	elseif($request_action=="d")
	{
		$befores = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		$query_rs = "DELETE FROM gameevent WHERE ID='{$ID}';";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$affected = mysql_affected_rows();
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		
		if($affected == 0)
		{
			die ("<font color=red><b>No matched record. Failed to delete.</b></font><p><a href=\"gmevent.php?wid=$wid\">Back</a>");
		}
	}
	elseif($request_action=="de")
	{
		$rs = mysql_query("SELECT 1 FROM gameevent WHERE ID='{$ID}' AND Status='2';", $dbWc) or die(mysql_error());
		if (mysql_num_rows($rs)==0) die("<font color=red><b>You can't de-activate a game event which is not running, or does not exist.</b></font><p><a href=\"gmevent.php?wid=$wid\">Back</a>");
		$query_rs = "UPDATE gameevent SET Status='3' WHERE ID='{$ID}';";

		$befores = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		
	}
	elseif($request_action=="ac")
	{
		$rs = mysql_query("SELECT 1 FROM gameevent WHERE ID='{$ID}' AND Status='0';", $dbWc);
		if (mysql_num_rows($rs)==0) die("<font color=red><b>You can't enable/activate a game event which is not disabled, or does not exist.</b></font><p><a href=\"gmevent.php?wid=$wid\">Back</a>");
		$query_rs = "UPDATE gameevent SET Status='1' WHERE ID='{$ID}';";
		$befores = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM gameevent WHERE ID='{$ID}';");
		
	}
	elseif($request_action=="f")
	{
		$find_status=$HTTP_POST_VARS['find_status'];
		switch($find_status)
		{
			case '0':
			case '1':
			case '2':
			case '3':
				$cond_status="Where Status='{$find_status}'";
				break;
			default:
				$cond_status='';
		}
		$query_rs = "SELECT * FROM gameevent $cond_status ORDER BY ID";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	}
	elseif($request_action=="e")
	{
		$query_rs = "SELECT * FROM gameevent WHERE ID='$ID'";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		if(mysql_num_rows($rs)==0)
		{
			die ("<font color=red><b>No matched queries.</b></font><p><a href=\"gmevent.php?wid=$wid\">Back</a>");
		}
	}
	elseif($request_action=='n')
	{
		$newID=$HTTP_POST_VARS['FindID'];
		if(!$newID)
		{
			die('Missing ID for new event. You need to enter new ID befores click "Add"');
		}

		$rs_test = mysql_query("SELECT 1 FROM gameevent WHERE ID=$newID", $dbWc) or die(mysql_error());
		if(mysql_num_rows($rs_test)!=0)
		{
			die("<font color=red><b>Event ID, $newID, has been used. Please assign a new event ID.</b></font><p><a href=\"gmevent.php?wid={$wid}\">Back</a>");
		}
	}
	else
	{
		$request_action='';
	}

	if($request_action=='s' || $request_action=='d' || $request_action=='a')
	{
		header ("Location: gmevent.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
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
function chkid(){
	var ret = ! ( isNaN(document.form1.FindID.value) || document.form1.FindID.value.length == 0)
	if(!ret) alert("Please provide event ID.")
	document.form1.FindID.focus()
	return ret
}
//-->
</script>
</head>
<body>
<form name="form1" method="POST">
<h3>Game Event</h3>
(World Controller: <?=$htmlWc?>)
<?
if($wid)
{
	$status_selected = $status_selected1 = $status_selected2 = $status_selected3 ='';
	if($request_action=="f")
		eval("\$status_selected{$HTTP_POST_VARS['find_status']}='SELECTED';");
	else
		$status_selected9='SELECTED';

	echo "<br><br>Event ID: <input name=FindID maxlength=10 size=3>";

if(!$readonly_gmdata)
{
	echo "
		<input type=button onclick=\"if(chkid())postform(document.form1,'gmevent.php?wid=$wid&a=n&i=0')\" value=\"Add\">
		<input type=button onclick=\"javascript:if(chkid() && confirm('Confirm delete?'))postform(document.form1,'gmevent.php?wid=$wid&a=d&i=0')\" value=\"Delete\">
		<input type=button onclick=\"javascript:if(chkid())postform(document.form1,'gmevent.php?wid=$wid&a=e&i=0')\" value=\"Edit\">
	";
}
	echo "
		<input type=button onclick=\"javascript:if(chkid() && confirm('Confirm activate?'))postform(document.form1,'gmevent.php?wid=$wid&a=ac&i=0')\" value=\"Activate\">
		<input type=button onclick=\"javascript:if(chkid() && confirm('Confirm de-activate'))postform(document.form1,'gmevent.php?wid=$wid&a=de&i=0')\" value=\"De-Activate\">
		<hr>
		Event(s) with status:
		<select name=find_status onchange=\"if(this.value!=9)postform(document.form1,'gmevent.php?wid={$wid}&a=f')\">
			<option value='' $status_selected>( all )</option>
			<option value=0 $status_selected0>disabled</option>
			<option value=1 $status_selected1>enabling</option>
			<option value=2 $status_selected2>running</option>
			<option value=3 $status_selected3>de-activating</option>
			<option value='9' $status_selected9>- Select -</option>
		</select>
		<input type=button value=\"List\" onclick=\"postform(document.form1,'gmevent.php?wid={$wid}&a=f')\">
	";

	if($request_action=='e' || $request_action=='n')
	{
		if($request_action=='e')
		{
			$row=mysql_fetch_assoc($rs);
			mysql_free_result($rs);

			$i=$row[ID];
			$id_fld=$i;
			$status_fld = $enum_status[$row[Status]];
		}
		else
		{
			$i=0;
			$id_fld = "$newID <input type=hidden name=newID value='{$newID}'>";
			$status_fld = "Disable<input type=hidden name=Status value='0'>";
		}

		$description= htmlspecialchars($row['Description']);

		$html_entry="
		<hr>
		<table border=\"1\" cellspacing=0>
			<tr>
				<td>ID</td>
				<td>$id_fld</td>
			</tr>
			<tr>
				<td>Description</td>
				<td> <input name=Description maxlength=200 value='$description'> </td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					$status_fld
				</td>
			</tr>
			<tr>
				<td>Data1 </td>
				<td> <input name=Data1 maxlength=10 size=3 value=\"$row[Data1]\"> </td>
			</tr>
			<tr>
				<td>Data2 </td>
				<td> <input name=Data2 maxlength=10 size=3 value=\"$row[Data2]\"> </td>
			</tr>
			<tr>
				<td>Data3 </td>
				<td> <input name=Data3 maxlength=10 size=3 value=\"$row[Data3]\"> </td>
			</tr>
			<tr>
				<td>Data4 </td>
				<td> <input name=Data4 maxlength=10 size=3 value=\"$row[Data4]\"> </td>
			</tr>
			<tr>
				<td>Data5 </td>
				<td> <input name=Data5 maxlength=10 size=3 value=\"$row[Data5]\"> </td>
			</tr>
			<tr>
				<td>Data6 </td>
				<td> <input name=Data6 maxlength=10 size=3 value=\"$row[Data6]\"> </td>
			</tr>
			<tr>
				<td>Data7 </td>
				<td> <input name=Data7 maxlength=10 size=3 value=\"$row[Data7]\"> </td>
			</tr>
			<tr>
				<td>Data8 </td>
				<td> <input name=Data8 maxlength=10 size=3 value=\"$row[Data8]\"> </td>
			</tr>
			<tr>
				<td>Data9 </td>
				<td> <input name=Data9 maxlength=10 size=3 value=\"$row[Data9]\"> </td>
			</tr>
			<tr>
				<td>Data10 </td>
				<td> <input name=Data10 maxlength=10 size=3 value=\"$row[Data10]\"> </td>
			</tr>
			<tr>
				<td>Data11 </td>
				<td> <input name=Data11 maxlength=10 size=3 value=\"$row[Data11]\"> </td>
			</tr>
			<tr>
				<td>Data12 </td>
				<td> <input name=Data12 maxlength=10 size=3 value=\"$row[Data12]\"> </td>
			</tr>
			<tr>
				<td>Data13 </td>
				<td> <input name=Data13 maxlength=10 size=3 value=\"$row[Data13]\"> </td>
			</tr>
			<tr>
				<td>Data14</td>
				<td> <input name=Data14 maxlength=10 size=3 value=\"$row[Data14]\"> </td>
			</tr>
			<tr>
				<td>Data15 </td>
				<td> <input name=Data15 maxlength=10 size=3 value=\"$row[Data15]\"> </td>
			</tr>
			<tr>
				<td>Data16 </td>
				<td> <input name=Data16 maxlength=10 size=3 value=\"$row[Data16]\"> </td>
			</tr>
			<tr>
				<td>A ScriptID </td>
				<td> <input name=AScriptID value=\"$row[AScriptID]\"> </td>
			</tr>
			<tr>
				<td>A DBScript </td>
				<td> <input name=ADBScript value=\"$row[ADBScript]\"> </td>
			</tr>
			<tr>
				<td>D ScriptID </td>
				<td> <input name=DScriptID value=\"$row[DScriptID]\"> </td>
			</tr>
			<tr>
				<td>D DBScript </td>
				<td> <input name=DDBScript value=\"$row[DDBScript]\"> </td>
			</tr>
		</table>
		";

		if($request_action=='e')
		{
			$status = $row['Status'];

			switch($status)
			{
				case 0:
					$action = "<input type=button onclick=\"if(confirm('Confirm activate?'))postform(document.form1,'gmevent.php?wid=$wid&a=ac&i=$i')\" value=\"Activate\">";
					break;
				case 2:
					$action = "<input type=button onclick=\"if(confirm('Confirm de-activate?'))postform(document.form1,'gmevent.php?wid=$wid&a=de&i=$i')\" value=\"De-Activate\">";
					break;
				default:
					$action = "";
			}

			$html_entry.="
			<input type=button onclick=\"if(confirm('Confirm overwrite?'))postform(document.form1,'gmevent.php?wid=$wid&a=s&i=$i')\" value=\"Save\">
			<input type=button onclick=\"if(confirm('Confirm delete?'))postform(document.form1,'gmevent.php?wid=$wid&a=d&i=$i')\" value=\"Delete\">
			{$action}
			";

		}
		else
		{
			$html_entry.="
			<a href=\"javascript:postform(document.form1,'gmevent.php?wid=$wid&a=a')\">Add</a>
			";
		}

		echo $html_entry;
	}
	elseif($request_action=='f')
	{
		if(mysql_num_rows($rs)==0)
		{
			echo "<p><font color=red><b>No matched queries.</b></font>";
		}
		else
		{
			$html_list="<p><table border=1 cellspacing=0><tr><th>ID</th><th>Description</th><th>Status</th><th>Actions</th></tr>";
			while($row=mysql_fetch_assoc($rs))
			{
				$i=$row['ID'];
				$description=htmlspecialchars($row['Description']);

				$status = $row['Status'];

				switch($status)
				{
					case 0:
						$action = "<input type=button onclick=\"if(confirm('Confirm activate?'))postform(document.form1,'gmevent.php?wid={$wid}&a=ac&i=$i')\" value=\"Activate\">";
						break;
					case 2:
						$action = "<input type=button onclick=\"if(confirm('Confirm de-activate?'))postform(document.form1,'gmevent.php?wid={$wid}&a=de&i=$i')\" value=\"De-Activate\">";
						break;
					default:
						$action = "";
				}

				$html_list.="
				    <tr>
					<td>{$row['ID']}</td>
					<td>{$description}&nbsp;</td>
					<td>{$enum_status[$status]}</td>
					<td>";
if(!$readonly_gmdata)
{
	$html_list .= "
						<input type=button onclick=\"postform(document.form1,'gmevent.php?wid={$wid}&a=e&i=$i')\" value=\"Edit\">
						<input type=button onclick=\"if(confirm('Confirm delete?'))postform(document.form1,'gmevent.php?wid={$wid}&a=d&i=$i')\" value=\"Delete\">
	";
}
				$html_list.="
						{$action}
					</td>
				    </tr>
				";
			}
			mysql_free_result($rs);

			echo $html_list;
		}
	}
} //if($wid)
?>
</form>
</body>
</html>
