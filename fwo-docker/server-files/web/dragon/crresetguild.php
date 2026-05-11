<?
require("auth.php");

//$server = $HTTP_SERVER_VARS[argv][1];
//$dbuser = $HTTP_SERVER_VARS[argv][2];
//$dbpass = $HTTP_SERVER_VARS[argv][3];

$server = "192.168.0.22";
$dbuser = "dbadmin";
$dbpass = "hitech";

function exechostdbsql ($dbserv, $dbusr, $dbpwd, $db, $sql, &$rs)
{
	$rs=array();
	mysql_connect ($dbserv, $dbusr, $dbpwd)
		or die('cant connect db server');
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
			$rs[$idx++] = mysql_fetch_array ($res);
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

function execsql ($sql, &$rs)
{
	global $server, $dbuser, $dbpass;	
	return exechostdbsql ($server, $dbuser, $dbpass, 'fwworlddevdb', $sql, $rs);
}

//execsql("select AttribID, IsDead from npcattrib", $rs_tables);

for ($i = 0; $i < 10; $i++)
{
	$sqlstr = "select * from guildlist_".$i." where CharID <> 0";
	echo $sqlstr."<br>";
	execsql($sqlstr, $rs_guild);

	foreach($rs_guild as $row_guild)
	{
		$update_str = "update guildlist_".$i." set CharID = 0, GuildID = 0, Job = 0, Status = 0 where CharID = ".$row_guild['CharID'];
		echo $update_str."<br>";
		execsql($update_str, $rs_bl);

	}
}


for ($i = 0; $i < 10; $i++)
{
	$update_str = "update pcharstats_".$i." set ClanID = 0, Job = 0, GuildID = 0 where CharID <> 0 and ClanID <> 100";
	echo $update_str."<br>";
	execsql($update_str, $rs_bl);
	$update_str = "update intdata_".$i." set ClanID = 0, job = 0, GuildID = 0 where CharID <> 0 and ClanID <> 100";
	echo $update_str."<br>";
	execsql($update_str, $rs_bl);
}



$update_str = "update clanrating set Rating = 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'";
echo $update_str."\n";
execsql($update_str, $rs_bl);

execsql("update clan set Type = 384 where ClanID = 1", $rs_bl);
execsql("update clan set Type = 395 where ClanID = 2", $rs_bl);
execsql("update clan set Type = 382 where ClanID = 3", $rs_bl);
execsql("update clan set Type = 387 where ClanID = 4", $rs_bl);
execsql("update clan set Type = 400 where ClanID = 5", $rs_bl);
execsql("update npcattribdyn set IsDead = 0 where AttribID = 384", $rs_bl);
execsql("update npcattribdyn set IsDead = 0 where AttribID = 395", $rs_bl);
execsql("update npcattribdyn set IsDead = 0 where AttribID = 382", $rs_bl);
execsql("update npcattribdyn set IsDead = 0 where AttribID = 387", $rs_bl);
execsql("update npcattribdyn set IsDead = 0 where AttribID = 400", $rs_bl);

/*
$change_count = 0;

foreach($rs_tables as $row_table)
{
	$attribid = $row_table['AttribID'];
	$isdead = $row_table['IsDead'];
	if (execsql("select * from npcattribdyn where AttribID = ".$attribid, $rs_attribdyn) == 0)
	{
		execsql("insert into npcattribdyn (AttribID, IsDead) values (".$attribid.",".$isdead.")", $rs_res);
		$change_count++;
	}

}
echo "record affected: ".$change_count."\n";
*/
echo "<script>alert('Finished')</script>";
?>
