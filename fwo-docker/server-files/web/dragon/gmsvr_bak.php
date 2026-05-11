<?php
require('auth.php');
$vers = array("1.0","1.1","1.2");
/*
$fp = fsockopen('192.168.0.16', 80);
fputs($fp, "GET\r\n");
echo fread($fp,20);
*/

if(!file_exists($rsa_file))
{
	echo "OpenSSH key file is not found, one is being created...<br>";
	system("cd; mkdir .ssh; chmod 700 .ssh; ssh-keygen -t rsa -N '' -f $rsa_file ;chmod -R 700 .ssh", $ret);
	echo "<p>Do not delete $rsa_file or you will not be able to give command to all game servers.";
	echo "<p>If this is not the first time the key file is generated, you will need to update the authorizd file in all game servers.";
	echo "<p>Refresh this page to continue.";
	exit();
}

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


switch($HTTP_GET_VARS['a'])
{
	case 'a':
		strlen(trim($HTTP_POST_VARS[name_0]))>0 or die("Please enter the name for the game DB connection.");

		$newdb = mysql_connect($HTTP_POST_VARS[ip_0], $HTTP_POST_VARS[dbuser_0], $HTTP_POST_VARS[dbpasswd_0]) or die("DB connection failed. Please check your configuration.");
		mysql_select_db($HTTP_POST_VARS[db_0], $newdb) or die("Unknown DB, please check the DB provided.");
		mysql_close($newdb);

//require_once('dbGmAdm.php');
		$query_rsGmSvr = "INSERT INTO gm_server(id, name, type, wid, ip, db, dbuser, dbpasswd, authsys) VALUES(0, '{$HTTP_POST_VARS[name_0]}', '{$HTTP_POST_VARS[type_0]}', '{$HTTP_POST_VARS[wctrlr_id_0]}', '{$HTTP_POST_VARS[ip_0]}', '{$HTTP_POST_VARS[db_0]}', '{$HTTP_POST_VARS[dbuser_0]}', '{$HTTP_POST_VARS[dbpasswd_0]}', '{$HTTP_POST_VARS[authsys_0]}')";
		mysql_select_db($database_dbGmAdm, $dbGmAdm);
		$rsGmSvr = mysql_query($query_rsGmSvr, $dbGmAdm) or die(mysql_error($dbGmAdm));
		$after = get_str_rs($dbGmAdm, "SELECT * FROM gm_server WHERE id='" . mysql_insert_id($dbGmAdm) . "';");
		act_log(
			array(
				"server"=>"$hostname_dbGmAdm",
				"db"=>"$database_dbGmAdm",
				"tbl"=>"gm_server",
				"act"=>"add game db host",
				"cmd"=>$query_rsGmSvr,
				"after"=>$after
			)
		);
		header("Location: gmsvr.php?a=f");
		exit;
		break;
	case 'f':
		$cond_name = strlen($HTTP_POST_VARS['name_0']) ? "AND name like '$HTTP_POST_VARS[name_0]'" : "";
		$cond_type = strlen($HTTP_POST_VARS['type_0']) ? "AND type like '$HTTP_POST_VARS[type_0]'" : "";
		$cond_ip = strlen($HTTP_POST_VARS['ip_0']) ? "AND ip like '$HTTP_POST_VARS[ip_0]'" : "";
		$query_rsGmSvr = "SELECT * FROM  gm_server WHERE 1 $cond_name $cond_type $cond_ip";
		$rsGmSvr = mysql_query($query_rsGmSvr, $dbGmAdm) or die(mysql_error());

		break;
	case 's':
		$i = $HTTP_GET_VARS[i];
		strlen(trim($HTTP_POST_VARS["name_$i"]))>0 or die("Please enter the name for the game DB connection.");
		$newdb = mysql_connect($HTTP_POST_VARS["ip_$i"], $HTTP_POST_VARS["dbuser_$i"], $HTTP_POST_VARS["dbpasswd_$i"]) or die("DB connection failed. Please check your configuration.");
		mysql_select_db($HTTP_POST_VARS["db_$i"], $newdb) or die("Unknown DB, please check the DB provided.");
		mysql_close($newdb);
		$query_rsGmSvr = "UPDATE gm_server SET
			name='".$HTTP_POST_VARS["name_{$HTTP_GET_VARS[i]}"]."',
			type='".$HTTP_POST_VARS["type_{$HTTP_GET_VARS[i]}"]."',
			wid='".$HTTP_POST_VARS["wctrlr_id_{$HTTP_GET_VARS[i]}"]."',
			version='".$HTTP_POST_VARS["ver_{$HTTP_GET_VARS[i]}"]."',
			ip='".$HTTP_POST_VARS["ip_{$HTTP_GET_VARS[i]}"]."',
			db='".$HTTP_POST_VARS["db_{$HTTP_GET_VARS[i]}"]."',
			dbuser='".$HTTP_POST_VARS["dbuser_{$HTTP_GET_VARS[i]}"]."',
			dbpasswd='".$HTTP_POST_VARS["dbpasswd_{$HTTP_GET_VARS[i]}"]."',
			authsys='".$HTTP_POST_VARS["authsys_{$HTTP_GET_VARS[i]}"]."'
			WHERE id='{$HTTP_GET_VARS[i]}'";

		mysql_select_db($database_dbGmAdm, $dbGmAdm);
		$befores = get_str_rs($dbGmAdm, "SELECT * FROM gm_server WHERE id='{$HTTP_GET_VARS[i]}';");
		$rsGmSvr = mysql_query($query_rsGmSvr, $dbGmAdm) or die(mysql_error($dbGmAdm));
		$after = get_str_rs($dbGmAdm, "SELECT * FROM gm_server WHERE id='{$HTTP_GET_VARS[i]}';");
		act_log(
			array(
				"server"=>"$hostname_dbGmAdm",
				"db"=>"$database_dbGmAdm",
				"tbl"=>"gm_server",
				"act"=>"update game db host",
				"cmd"=>$query_rsGmSvr,
				"befores"=>$befores,
				"after"=>$after
			)
		);
		header("Location: gmsvr.php?a=f");
		exit;
		break;
	case 'd':
		$query_rsGmSvr = "DELETE FROM gm_server WHERE id='{$HTTP_GET_VARS[i]}'";
		$befores = get_str_rs($dbGmAdm, "SELECT * FROM gm_server WHERE id='{$HTTP_GET_VARS[i]}';");
		$rsGmSvr = mysql_query($query_rsGmSvr, $dbGmAdm) or die(mysql_error());
		$after = get_str_rs($dbGmAdm, "SELECT * FROM gm_server WHERE id='{$HTTP_GET_VARS[i]}';");
		act_log(
			array(
				"server"=>"$hostname_dbGmAdm",
				"db"=>"$database_dbGmAdm",
				"tbl"=>"gm_server",
				"act"=>"delete game db host",
				"cmd"=>$query_rsGmSvr,
				"befores"=>$befores,
				"after"=>$after
			)
		);

		header("Location: gmsvr.php?a=f");
		exit;
		break;
}

$arAuthsys = array();
$rsAuthsys = mysql_query("SELECT id, name FROM gm_server WHERE type='as';", $dbGmAdm) or die(mysql_error());
while($row = mysql_fetch_assoc($rsAuthsys))
{
	array_push($arAuthsys, $row);
}
mysql_free_result($rsAuthsys);
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
<h3>Game DB Server Configuration</h3>
<form name="form1" method="POST">
  <table border="1" cellspacing=0>
    <tr>
      <td>Name</td>
      <td>Type</td>
      <td>Version</td>
      <td>Server IP Addr.</td>
      <td>DB Name</td>
      <td>DB User</td>
      <td>DB Password</td>
      <td>Related Authentication Server</td>
      <td>WCTRLR ID</td>
      <td>Action</td>
    </tr>
    <tr bgcolor="#FFFF66">
      <td>
        <input name="name_0" type="text" id="name_0" size="12" maxlength="20"></td>
      <td>
        <select name="type_0" size="1" id="type_0">
          <option value="" selected></option>
          <option value="as">Authentication Server DB</option>
          <option value="wc">World Controller DB</option>
	  <option value="lg">Log System DB</option>

        </select>
	</td>
      <td>
        <select name="ver_0" size="1" id="ver_0">
          <option value="" selected></option>
	<?
		foreach($vers as $ver)
		{
			echo "<option value=\"$ver\">$ver</option>";
		}
	?>
        </select>
	</td>
      <td>
        <input name="ip_0" type="text" id="ip_0" size="13" maxlength="15"></td>
      <td>
        <input name="db_0" type="text" id="db_0" size="12"></td>
      <td> <input name="dbuser_0" type="text" id="dbuser_0" size="8"></td>
      <td> <input name="dbpasswd_0" type="password" id="dbpasswd_0" size="8"></td>
      <td>
	<select name="authsys_0" id="authsys_0"><option value=''></option>
<?
	foreach($arAuthsys as $row)
	{
		echo "<option value='{$row[id]}'>{$row[name]}</option>";
	}
?>
	</select>
      </td>
      
    <td>
	<select name="wctrlr_id_0" id="wctrlr_id_0"><option value=''></option>
<?
    $get_wid = mysql_query("SELECT * FROM gm_server WHERE type='wc'",$dbGmAdm) or die (mysql_error($dbGmAdm));
	while ($row_wid = mysql_fetch_assoc($get_wid))
	{
        echo "<option value='{$row_wid[id]}'>{$row_wid[name]}</option>";
    }
?>
	</select>
    </td>

      <td>
        <input type="button" name="Button" value="Add" onClick="postform(document.form1,'gmsvr.php?a=a')"> <input type="button" name="Button" value="Find" onClick="postform(document.form1,'gmsvr.php?a=f')">
      </td>
    </tr>
<?php
if($HTTP_GET_VARS[a]=='f')
{
	if(mysql_num_rows($rsGmSvr)==0)
	{
		echo "<tr><td colspan=8><font color=red>No matched queries.</font></td></tr>";
	}
	else
	{
		while($row_rsGmSvr = mysql_fetch_assoc($rsGmSvr))
		{
		?>
			<tr>
			<td><input name="name_<?=$row_rsGmSvr[id]?>" type="text" size="12" maxlength="20" value="<?=$row_rsGmSvr["name"]?>"></td>
			<td><select name="type_<?=$row_rsGmSvr[id]?>">
				<option value="as" <? if($row_rsGmSvr[type]=='as')echo "SELECTED" ?>>Authentication Server DB</option>
				<option value="wc" <? if($row_rsGmSvr[type]=='wc')echo "SELECTED" ?>>World Controller DB</option>
				<option value="lg" <? if($row_rsGmSvr[type]=='lg')echo "SELECTED" ?>>Log System DB</option>
				</select>
			</td>
			<td><select name="ver_<?=$row_rsGmSvr[id]?>">
				<option value=""></option>
				<?
				foreach($vers as $ver)
				{
					$selected = $row_rsGmSvr[version]==$ver?"SELECTED" : "";
					echo "<option value=\"$ver\" $selected>$ver</option>";
				}
				?>
				</select>
			</td>
			<td><input name="ip_<?=$row_rsGmSvr[id]?>" type="text" id="ip_" size="13" maxlength="15" value="<?=$row_rsGmSvr[ip]?>"></td>
			<td><input name="db_<?=$row_rsGmSvr[id]?>" type="text" size="12" value="<?=$row_rsGmSvr[db]?>"></td>
			<td><input name="dbuser_<?=$row_rsGmSvr[id]?>" type="text" size="8" value="<?=$row_rsGmSvr[dbuser]?>"></td>
			<td><input name="dbpasswd_<?=$row_rsGmSvr[id]?>" type="password" size="8" value="<?=$row_rsGmSvr[dbpasswd]?>"></td>
            <td>
			<?
			if(($row_rsGmSvr[type]=='wc') ||($row_rsGmSvr[type]=='lg') )
			{
			?>
			<select name="authsys_<?=$row_rsGmSvr[id]?>"><option value=''></option>
			<?
				foreach($arAuthsys as $row_authsys)
				{
					$selected = $row_authsys[id] == $row_rsGmSvr[authsys]? "SELECTED": "";
					echo "<option value='{$row_authsys[id]}' $selected>{$row_authsys[name]}</option>";
				}
			?>
					</select>
			<?
            }
            else
            {
                echo "&nbsp;";
            }
			?>
			</td>
			
			<td>
			<?
			if($row_rsGmSvr[type]=='lg')
			{
			?>
			<select name="wctrlr_id_<?=$row_rsGmSvr[id]?>"><option value=''></option>
			<?
				$get_wid = mysql_query("SELECT * FROM gm_server WHERE type='wc'",$dbGmAdm) or die (mysql_error($dbGmAdm));
	           while ($row_wid = mysql_fetch_assoc($get_wid))
                {
                    $selected = $row_wid[id] == $row_rsGmSvr[wid]? "SELECTED": "";
                    echo "<option value='{$row_wid[wid]}' $selected>{$row_wid[name]}</option>";
                }
			?>
					</select>
			<?
            }
            else
            {
                echo "&nbsp;";
            }
			?>
			</td>
			
			<td><input name="save_<?=$row_rsGmSvr[id]?>" type="button" id="save_" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'gmsvr.php?a=s&i=<?php echo $row_rsGmSvr[id]?>')"> <input name="delete_" type="button" id="delete_" value="Delete" onClick="if(confirm('Delete?'))postform(document.form1,'gmsvr.php?a=d&i=<?php echo $row_rsGmSvr[id]?>')"></td>
			</tr>
		<?
		}
	}
}
?>
</table>
<p>
<!--input type="reset" name="Reset" value="Reset"-->
<input type="hidden" name="a">
</p>
</form>
</body>
</html>
<?php
//mysql_free_result($rsGmSvr);
?>
