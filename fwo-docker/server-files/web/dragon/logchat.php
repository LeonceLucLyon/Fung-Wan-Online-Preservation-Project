<?php
require('auth.php');

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="e") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w"))
{
	die("Access denied. Read-Only.");
}

$selected_idx = $HTTP_POST_VARS['selected_idx'];
if($selected_idx=="")$selected_idx = -1;

if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]);
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

	$rs_zs = mysql_query("SELECT DISTINCT Address FROM scene", $dbWc);
	$zs = array();
	while(list($ip) = mysql_fetch_row($rs_zs))
	{
		$zs[] = $ip;
	}
	sort($zs);
	$zs_cnt = count($zs);
	$selected_ip = $zs[$selected_idx];

	switch($HTTP_GET_VARS['a'])
	{
		case 'f':
			break;
		case 's':	//start log
			$char_id = $HTTP_GET_VARS[i];

			if($HTTP_GET_VARS[f]!="1" && $char_id !='0')
			{
				$rs_logging = mysql_query("SELECT DISTINCT ChatCharID FROM config WHERE Type=1 AND ChatCharID <> 0", $dbWc) or die(mysql_error());
				if(mysql_num_rows($rs_logging) > 0)
				{
					list($logged_char_id) = mysql_fetch_row($rs_logging);
					$rs_char = mysql_query("SELECT CharacterName FROM pcharacter WHERE CharID = '$logged_char_id'", $dbWc) or die(mysql_error());
					if(mysql_num_rows($rs_char) > 0)
					{
						list($charnm) = mysql_fetch_row($rs_char);
						$charnm = u16btou8str($charnm);
						echo "
							<html>
							<head>
							<title><?=BROWSER_TITLE?></title>
							<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
							<link href=\"ga.css\" rel=\"stylesheet\" type=\"text/css\"/>
							</head>
							<body>
							<h3>Player Chat Log</h3>
							(World Controller: <?=$htmlWc?>)
							<br><br>
							<a href='pcharacter.php?wid={$wid}&i=$logged_char_id'>{$charnm}($logged_char_id)</a>'s chat is being logged.
							<br><font color=red>You can only log one character's chat at a time. New log will be appended to existing log.</font>
							<br><input type=\"button\" value=\"Continue\" onclick=\"location.href='logchat.php?wid={$wid}&a=s&i={$char_id}&f=1'\">
							<input type=\"button\" value=\"Abort\" onclick=\"window.close()\">
							";
						exit();
					}
				}
			}

			foreach($zs as $ip)
			{
				if($fp = fsockopen($ip, 22, $errno, $errstr, $ssh_connect_timeoout))
				{
					fclose($fp);
				}
				else
				{
					break;
				}

				$shell_cmd = "ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$ip sudo -u $zs_username $SETCMD 49 $char_id";
				unset($shell_out);
				exec($shell_cmd, $shell_out, $ret);
//				echo "ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$ip sudo -u $zs_username $SETCMD 49 $char_id";
//				exit();
				if($ret == 255){
					die("remote server, IP $ip, command not set up");
				}
				$after = join("\n", $shell_out);
				$act = $char_id == 0? "stop pchar chatlog": "start pchar chatlog";
				
			}
			header("Location: logchat.php");
			exit();
			break;
		case 'l':	//get log
			if($wid)
			{
				//foreach($zs as $ip)
				if(strlen($selected_ip) > 0)
				{
					$ip = $selected_ip;
					$chatlog = "";
					if($fp = fsockopen($ip, 22, $errno, $errstr, $ssh_connect_timeoout))
					{
						fclose($fp);
					}
					else
					{
						$log_contents = "<span style='color: red'>Cannot connect to the server.</span>";
						break;
					}

					unset($shell_out);
					exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} grep -e \"^ChatLog \" $zsconf_location", $shell_out, $ret);
					if($ret == 0)
					{
						list($key, $val) = sscanf($shell_out[0], "%s\t%s");
						$limit = 50;
						$chatlog = $val;

						// paging
						unset($shell_out);
						exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} \"$prog_viewchat -c $chatlog\" #$chatlog", $shell_out, $ret);
						if(ret == 0)
						{
							list($line, $filename) = sscanf($shell_out[0], "\t%d\t%s");
							$lastpage = ceil($line / $limit);
							$page = floor($HTTP_POST_VARS['page']);
							if($page > $lastpage || $page <=0 || !is_numeric($page)) $page = 1;
							$offset = ($page - 1) * $limit + 1;
							$last = $offset + $limit - 1;
							$paging .= "Page <input name=\"page\" type=\"text\" size=3 value=\"$page\">/$lastpage <input type=\"button\" value=\"Go\" onclick=\"postfind()\"> <a href=\"javascript:document.form1.page.value--;postfind()\">Previous</a> <a href=\"javascript:document.form1.page.value++;postfind()\">Next</a> ";
						}
//$last *= 50; $limit *= 20;
						//results
						unset($shell_out);
						$fp = popen("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} \"$prog_viewchat -b$offset -l$limit -n $chatlog\"", "r");
						while(!feof($fp))
						{
							$line = fread($fp, 1024);
							$log_contents .= $line;
						}
						pclose($fp);

						/*
						exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} \"nl -b a -s ', ' $chatlog | head -$last | tail -$limit\"", $shell_out, $ret);
						if($ret == 0)
						{
							foreach($shell_out as $line)
							{
								$log_contents .= $line;
								$log_contents .= "<br>";
							}
						}
						*/

						break;
					}
				}
			}
			break;
		case "e":
			if($wid)
			{
				if(strlen($selected_ip) > 0)
				{
					$ip = $selected_ip;
					unset($shell_out);
					exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} grep -e \"^ChatLog \" $zsconf_location", $shell_out, $ret);
					if($ret == 0)
					{
						list($key, $val) = sscanf($shell_out[0], "%s\t%s");
						$chatlog = $val;
						$shell_cmd = "ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@{$ip} \"> $chatlog\"";
						system($shell_cmd, $ret);
						
					}
				}
			}
			break;

	}
}
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'logchat.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
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
function postfind()
{
	postform(document.form1,'logchat.php?wid=<?=$wid?>&a=l')
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Player Chat Log</h3>
(World Controller: <?=$htmlWc?>)
<?
if($wid!="")
{
	echo "<p>ZoneServer: <select name=\"selected_idx\"><option value=\"\">-Select-</option>";
	for($idx = 0; $idx < $zs_cnt; $idx++)
	{
		$ip = $zs[$idx];
		$selected = $idx == $selected_idx? "SELECTED": "";
		echo "<option value=\"$idx\" $selected>$ip</option>";
	}
	echo "</select><br>
		<input type=button value=\"View Chat Log\" onclick=\"if(document.form1.selected_idx.value==''){alert('Please select a valid zoneserver.');return false}postfind()\">
		<input type=button value=\"Empty Chat Log\" onclick=\"if(document.form1.selected_idx.value==''){alert('Please select a valid zoneserver.');return false}if(confirm('Empty chat log?'))postform(document.form1, 'logchat.php?wid=$wid&a=e')\">
		<p>";

	$rs_logging = mysql_query("SELECT DISTINCT ChatCharID FROM config WHERE Type=1 AND ChatCharID <> 0", $dbWc) or die(mysql_error($dbWc));
	if(mysql_num_rows($rs_logging) > 0)
	{
		list($char_id) = mysql_fetch_row($rs_logging);
		$rs_char = mysql_query("SELECT CharacterName FROM pcharacter WHERE CharID = '$char_id'", $dbWc) or die(mysql_error($dbWc));
		if(mysql_num_rows($rs_char) > 0)
		{
			list($charnm) = mysql_fetch_row($rs_char);
			$charnm = u16btou8str($charnm);
			echo "<a href='pcharacter.php?wid={$wid}&i=$char_id'>{$charnm}($char_id)</a>'s chat is being logged. <input type=button value=\"Stop Logging Chat\" onclick=\"location.href='logchat.php?a=s&i=0&wid={$wid}'\"><p>";
		}
		else
		{
			echo "Invalid CharID ($char_id), chat can't be logged.<input type=button value=\"Stop Logging Chat\" onclick=\"location.href='logchat.php?a=s&i=0&wid={$wid}'\"><p>";
		}

		mysql_free_result($rs_char);
	}
	mysql_free_result($rs_logging);

	if($HTTP_GET_VARS[a] == 'l')
	{
		if(strlen(trim($log_contents))>0)
		{
			echo "<hr>" . nl2br($log_contents) . "</p><p>$paging</p>";
		}
		elseif($selected_idx != -1)
		{
			echo "<font color=red>Chat log is empty.</font>";
		}
	}
}
?>
</form>
</body>
</html>