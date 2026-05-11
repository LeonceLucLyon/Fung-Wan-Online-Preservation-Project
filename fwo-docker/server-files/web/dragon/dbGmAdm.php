<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
require("config.php");

$dbGmAdm = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm) or die(mysql_error());

function getstring($id,$type)
{
	if($id==0)return "";
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;
	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn);
	$rs = mysql_query("SELECT value FROM string WHERE id='$id' AND type='$type';",$dbconn);

	$row=mysql_fetch_row($rs);

	mysql_free_result($rs);
	return $row[0]!=""? $row[0]:$id;
}

function getid($str,$type)
{
	if(strlen($str)==0)return "";
	$ret = array();
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;
	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn);
	$str = U8toU16($str);
	$rs = mysql_query("SELECT id FROM string WHERE type='$type' AND value='$str';",$dbconn);
	while($row=mysql_fetch_row($rs))
	{
		$ret[] = $row[0];
	}
	mysql_free_result($rs);
	return $ret;
}

function hasperm($usrid, $perm, $server, $exact = 0)
{
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error());
	if($exact){
		$rs = mysql_query("SELECT 1 FROM perm WHERE userid='$usrid' AND (serverid='$server' or serverid=0) AND (perm & $perm) = $perm ;", $dbconn) or die(mysql_error());
	}else{
		$rs = mysql_query("SELECT 1 FROM perm WHERE userid='$usrid' AND (serverid='$server' or serverid=0) AND (perm & $perm) > 0 ;", $dbconn) or die(mysql_error());
	}
	$ret = mysql_num_rows($rs);
	mysql_free_result($rs);
	return $ret;
}

function has_perm($userid, $server, $page, $action){
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error());
//	$rs = mysql_query("SELECT 1 FROM perm WHERE userid='$usrid' AND (serverid='$server' or serverid=0) AND (perm & $perm) = $perm ;", $dbconn) or die(mysql_error());

	$cond_server = ( strlen($server) > 0 ) ? "AND ( serverid = '$server' OR serverid = 0 )" : "";
	$cond_action = ( strlen($action) > 0 ) ? "AND ( action like '$action' OR action = '*' )" : "";
	$cond_page = ( strlen($page) > 0 ) ? "AND ( page like '$page' OR page = '*' )" : "";

	$sql = "SELECT 1 FROM perm WHERE userid = '$userid' $cond_page $cond_server $cond_action;";
	$rs = mysql_query($sql, $dbconn) or die(mysql_error());

	$ret = mysql_num_rows($rs);
	mysql_free_result($rs);
	return $ret;
}

function add_perm($userid, $server, $page, $action){
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error());

	mysql_query("INSERT INTO perm (userid, serverid, page, action) VALUE('$userid', '$server', '$page', '$action');", $dbconn) or die(mysql_error());

//	mysql_affected($rs);
//	return $ret;
}

function del_perm($userid, $server, $page, $action){
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error());
//	return $ret;
}


function get_accessible_server($userid, $type)
{
	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;
	$perm = array(); $ret = array();

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error());

	$rs = mysql_query("SELECT DISTINCT serverid FROM perm WHERE userid='$userid' ORDER BY serverid;", $dbconn) or die(mysql_error());
	while($row = mysql_fetch_row($rs))
	{
		array_push($perm, $row[0]);
	}
	mysql_free_result($rs);

	if(count($perm) == 0) return $ret;

	if(in_array(0, $perm))
	{
		$rs = mysql_query("SELECT * FROM gm_server WHERE type='$type';", $dbconn) or die(mysql_error());
	}
	else
	{
		$range = join(',', $perm);
		$rs = mysql_query("SELECT * FROM gm_server WHERE type='$type' AND id IN ($range);", $dbconn) or die(mysql_error());
	}

	while ( $row = mysql_fetch_assoc($rs) )
	{
		$ret[] = $row;
	}

	return $ret;
}

function act_log($ar_info)
{
	if(array_key_exists("befores", $ar_info) && array_key_exists("after", $ar_info) && strlen($ar_info["befores"])>0 && strcmp($ar_info["befores"], $ar_info["after"])==0)
	{
		return 0;
	}

	global $hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm, $database_dbGmAdm;
	global $HTTP_SESSION_VARS, $HTTP_SERVER_VARS;

	$user = $HTTP_SESSION_VARS['id'];
	$url = $HTTP_SERVER_VARS['REQUEST_URI'];
	$src_ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
	$db_nm = $database_dbGmAdm;

	$dbconn = mysql_connect($hostname_dbGmAdm, $username_dbGmAdm, $password_dbGmAdm);
	mysql_select_db($database_dbGmAdm, $dbconn) or die(mysql_error($dbconn));

	$flds="";
	$vals="";
	while(list($fld, $val) = each($ar_info)){
		$flds .= "$fld,";
		$vals .= "\"" . addslashes($val) . "\",";
	}
	$flds=substr($flds, 0, -1);
	$vals=substr($vals, 0, -1);
	$sql = "INSERT INTO act_log (datetime, user, client, uri, $flds) VALUES (NOW(), \"$user\", \"$src_ip\", \"$url\", $vals);";

	//$sql = "INSERT INTO actlog(timestamp, user, action, info, state1, state2) VALUES(NOW(), '$user', $action, $info, $state1, $state2);";
	$rs = mysql_query($sql, $dbconn) or die(mysql_error($dbconn));

	$ret = mysql_affected_rows($dbconn);
	mysql_close($dbconn);
	return $ret;
}

function exechostdbsql ($dbserv, $dbusr, $dbpwd, $db, $sql, &$rs)
{
	$rs=array();
	mysql_connect ($dbserv, $dbusr, $dbpwd)
		or die('cant connect to db server');
	mysql_select_db ($db)
		or die('Invalid db');
	$res = mysql_query ($sql)
		or die('Invalid SQL: '.$sql.mysql_error());
	if (is_resource ($res))
	{
		//$num_rows
		$no = mysql_num_rows ($res);
		$idx = 0;
		while($idx<$no)
		{
			$rs[$idx++] = mysql_fetch_assoc($res);
		}
		mysql_free_result($res);
	}
	else
	{
		//$num_affected
		$no = mysql_affected_rows();
	}
	mysql_close();
	return $no;	//$num_affected;
}

function execdbsql ($lnk, $sql, &$rs)
{
	$rs=array();
/*
	mysql_connect ($dbserv, $dbusr, $dbpwd)
		or die('cant connect db server');
	mysql_select_db ($db)
		or die('Invalid db');
*/
	$res = mysql_query ($sql, $lnk) or die('Invalid SQL: ' . $sql . mysql_error($lnk));
	if (is_resource ($res))
	{
		//$num_rows
		$no = mysql_num_rows ($res);
		$idx = 0;
		while($idx<$no)
		{
			$rs[$idx++] = mysql_fetch_assoc($res);
		}
		mysql_free_result($res);
	}
	else
	{
		//$num_affected
		$no = mysql_affected_rows($lnk);
	}
//	mysql_close();
	return $no;	//$num_affected;
}

function dump_rs_string($rs)
{
	//$col_br = count($rs) == 1? "\n": "";
	$col_br = '';
	foreach($rs as $row)
	{
		$line = "";
		while(list($key, $val) = each($row))
		{
			if(in_array($key, array("CharacterName", "MsgText1", "MsgText2", "MsgText3", "MsgText4", "MsgText5", "MsgText6", "MsgText7", "MsgText8", "MsgText9", "MsgText10")))
			{
				$val = U16btoU8str($val);
			}
			else
			{
				$val = addslashes($val);
				$val = addcslashes($val, "\0..\37!@\177..\377");
			}
			$line .= "[$key]=>\"$val\", $col_br";
		}
		$out .= substr($line, 0, -2) . "\n";
	}
	return $out;
}

function get_str_rs($lnk, $sql)
{
	execdbsql($lnk, $sql, $rs);
	return dump_rs_string($rs);
}

?>