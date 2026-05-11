<?php
require('auth.php');
require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "", "conf", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], "", "conf", "w"))
{
	die("Access denied. Read-Only.");
}

?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
<h3>Game Data Description Update For Game Admin </h3>
<?
if($_REQUEST['a']=='s')
{
	if(is_uploaded_file($_FILES['sqlfile']['tmp_name']))
	{
		$array_lines = file($_FILES['sqlfile']['tmp_name']);
		$count_line = $num_new = $num_upd = $num_unchg = 0;
		$err_msg = "";
		foreach($array_lines as $line)
		{
			$count_line++;
			if(ereg("INSERT INTO string\(id, value, type\) VALUES\(([0-9]+), ([0-9A-Fx]+), '([A-z]+)');", $line, $array_reg))
			//$array_fields = split(",", $line, 3);
			//if(count($array_fields == 3))
			{
				$array_fields = array($array_reg[3], $array_reg[1], $array_reg[2]);
				//$array_fields = array_slice($array_reg, 1, 3);
				//print_r($array_fields);

				$rs = mysql_query("SELECT 1 FROM string WHERE type='$array_fields[0]' AND id='$array_fields[1]' ");
				$num_rows = mysql_num_rows($rs);
				if($num_rows == 1)
				{
					if(mysql_query("UPDATE string SET value=$array_fields[2] WHERE id=$array_fields[1] AND type='$array_fields[0]' "))
					{
						$affected_rows = mysql_affected_rows();
						if($affected_rows == 0) $num_unchg++;
						$num_upd += $affected_rows;
					}
					else
					{
						 echo mysql_error() . "<br>";
					}
				}
				elseif($num_rows == 0)
				{
					mysql_query("INSERT INTO string(type, id, value) VALUES('$array_fields[0]', $array_fields[1], $array_fields[2]) ") or print(mysql_error() . "<br>");;
					$num_new += mysql_affected_rows();
				}
				else
				{
					//err
				}
			}
			else
			{
				if(ereg("INSERT INTO string\(id, value, type\) VALUES\(([0-9]+), NULL, '([A-z]+)');", $line, $array_reg))
				{
					$err_msg .= "<font color=gray>No description for $array_reg[2] ID $array_reg[1].</font></br>";
				}
				elseif(strlen(trim($line))>0)
					$err_msg .= "<font color=red>Invalid line #$count_line, $line</font><br>";
			}
		}

		if(strlen($err_msg)>0) $err_msg .= "-- End --";

		echo "<br>
			Upload Completed.
			<table border=1 cellspacing=0>
			<tr><th>Changes</th><th>Count</t></tr>
			<tr><td>New description(s)</td><td align=right>$num_new</td></tr>
			<tr><td>Updated description(s)</td><td align=right>$num_upd</td></tr>
			<tr><td>Unchanged description(s)</td><td align=right>$num_unchg</td></tr>
			</table>
			<p><a href='updstring.php'>Back</a><hr>$err_msg";
	}
	else
	{
		echo "<br><font color=red>Upload error. Please try again.</font>
			<p><a href='updstring.php'>Back</a>";
	}
}
else
{
	?>
	<form name="form1" method="POST"  enctype="multipart/form-data" action="updstring.php?a=s">
		<table border="0" cellspacing=0>
			<tr>
				<td>Data Description File: <input name=sqlfile type=file><br><input value=Submit type=submit></td>
			</tr>
		</table>
	</p>
	<?
}
?>
</form>
</body>
</html>
<?php
//mysql_free_result($rsGmSvr);
?>