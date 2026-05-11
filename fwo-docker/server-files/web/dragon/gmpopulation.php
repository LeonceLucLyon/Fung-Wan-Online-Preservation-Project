<?php
$as = array();
$wc = array();
if(file_exists('svrlst.php')) include('svrlst.php');


require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

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


if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "conf", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="log" || $HTTP_GET_VARS[a]=="b" || $HTTP_GET_VARS[a]=="stop" || $HTTP_GET_VARS[a]=="start" || $HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s" || $HTTP_GET_VARS[a]=="start" || $HTTP_GET_VARS[a]=="stop" || $HTTP_GET_VARS[a]=="log") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}


$rsAuthsys = mysql_query("SELECT * FROM gm_server WHERE type='as';", $dbGmAdm) or die(mysql_error());
mysql_num_rows($rsAuthsys) > 0 or die("No Authsys set up.");
$iframe_count = 0;
$html = "";

foreach($as as $ip)
{
	$iframe_count++;
	$html .= "<tr><td>Authsys</td><td>$ip</td><td><img id='stop{$iframe_count}' src='images/stop.jpg' style='display:none'><img id='run{$iframe_count}' src='images/run.jpg' style='display:none'><span id=\"span{$iframe_count}\"></span><span id=\"load{$iframe_count}\"> - updating... please wait</span><iframe id=iframe{$iframe_count} style='display:none'></iframe>&nbsp;</td></tr>";
	$js_load .= "document.all.load{$iframe_count}.style.display='';document.all.iframe{$iframe_count}.src=\"gmsvrs.php?a=status&ip={$ip}&type=as&n={$iframe_count}\";";
}
//document.all.{$iframe_count}.innerHTML=\"Loading...\";
foreach($wc as $ip)
{
			$iframe_count++;
			$html .= "<tr><td>World Controller</td><td>$ip</td><td><img id='stop{$iframe_count}' src='images/stop.jpg' style='display:none'><img id='run{$iframe_count}' src='images/run.jpg' style='display:none'><span id=\"span{$iframe_count}\"></span><span id=\"load{$iframe_count}\"> - updating... please wait</span><iframe id=\"iframe{$iframe_count}\" style='display:none'></iframe>&nbsp;</td></tr>";
			$js_load .= "document.all.load{$iframe_count}.style.display='';document.all.iframe{$iframe_count}.src=\"gmsvrs.php?a=status&ip={$ip}&type=wc&n={$iframe_count}\";";
}

while($authsys = mysql_fetch_assoc($rsAuthsys))
{
/*
	$iframe_count++;
	$html .= "<tr><td colspan=3>Authsys, $authsys[ip].</td><td><span id=\"span{$iframe_count}\">Loading</span><iframe id=iframe{$iframe_count} onloaded=\"alert(1)\" MARGINHEIGHT=0 MARGINWIDTH=0  width=100 height=20 scrolling=NO FRAMEBORDER=0>Loading</iframe></td></tr>";
	$js_load .= "document.all.span{$iframe_count}.innerHTML=\"Loading...\";document.all.iframe{$iframe_count}.src=\"gmsvrs.php?a=status&ip={$authsys[ip]}&type=as&n={$iframe_count}\";";
*/
	$rsWctrlr = mysql_query("SELECT * FROM gm_server WHERE authsys='{$authsys[id]}';", $dbGmAdm) or die(mysql_error());
	if(mysql_num_rows($rsWctrlr) > 0)
	{
		while($wctrlr = mysql_fetch_assoc($rsWctrlr))
		{
			$dbWc = mysql_connect($wctrlr[ip], $wctrlr[dbuser], $wctrlr[dbpasswd]) or die(mysql_error());
			mysql_select_db($wctrlr[db], $dbWc);
			$rsZs = mysql_query("SELECT DISTINCT Address FROM scene WHERE Address IS NOT NULL ORDER BY Address", $dbWc) or die(mysql_error());
/*
			$iframe_count++;
			$html .= "<tr><td>&nbsp;</td><td colspan=2>World Controller, $wctrlr[ip].</td><td><span id=\"span{$iframe_count}\">Loading</span><iframe id=\"iframe{$iframe_count}\" MARGINHEIGHT=0 MARGINWIDTH=0  width=100 height=20 scrolling=NO FRAMEBORDER=0>Loading</iframe></td></tr>";
			$js_load .= "document.all.span{$iframe_count}.innerHTML=\"Loading...\";document.all.iframe{$iframe_count}.src=\"gmsvrs.php?a=status&ip={$wctrlr[ip]}&type=wc&n={$iframe_count}\";";
*/
			if(mysql_num_rows($rsZs) > 0)
			{
				while($zs = mysql_fetch_assoc($rsZs))
				{
					$iframe_count++;
					$html .= "<tr><!--td>&nbsp;</td><td>&nbsp;</td--><td>Zone Server</td><td>$zs[Address]</td><td><img id='stop{$iframe_count}' src='images/stop.jpg' style='display:none'><img id='run{$iframe_count}' src='images/run.jpg' style='display:none'><span id=\"span{$iframe_count}\"></span><span id=\"load{$iframe_count}\"> - updating... please wait</span><iframe id=\"iframe{$iframe_count}\" style='display:none'></iframe>&nbsp;</td></tr>";
					$js_load .= "document.all.load{$iframe_count}.style.display='';document.all.iframe{$iframe_count}.src=\"gmsvrs.php?a=status&ip={$zs[Address]}&type=zs&n={$iframe_count}\";";
				}
			}
			else
			{
//				$html .= "World controller, $wctrlr[ip], does not have zone server.";
			}
			mysql_free_result($rsZs);
		}
	}
	else
	{
//		$html .= "Authsys, $authsys[ip], does not have world controller.";
	}
	mysql_free_result($rsWctrlr);
}
mysql_free_result($rsAuthsys);

?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript">
<!--
var task;
function postform(form,url){
	form.action=url;form.submit()
}
function set_on(n){
	var ofrm, ospn
	eval("ofrm = document.all.iframe" + n)
	eval("ospn = document.all.span" + n)
	eval("document.all.run" + n + ".style.display=''")
	eval("document.all.stop" + n + ".style.display='none'")
	eval("document.all.load" + n + ".style.display='none'")
	var d = new Date()
	ospn.innerHTML = "[" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "]"
}
function set_off(n){
	var ofrm, ospn
	eval("ofrm = document.all.iframe" + n)
	eval("ospn = document.all.span" + n)
	eval("document.all.run" + n + ".style.display='none'")
	eval("document.all.stop" + n + ".style.display=''")
	eval("document.all.load" + n + ".style.display='none'")
	var d = new Date()
	ospn.innerHTML = "[" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "]"
}
function reloadiframes(){
	<?=$js_load?>
	clearTimeout(task)
	task = setTimeout(reloadiframes, 30000)
	return false
}

//-->
</script>
<style>
li,ol,ul{list-style-position: outside;margin-left:1.5em;margin-top:0}
</style>
</head>
<body onload="reloadiframes()">
<form name="form1" method="post" action="">
<table border=1 cellpadding=5>
<!--tr><td width=20>&nbsp;</td><td width=20>&nbsp;</td><td width=300>&nbsp;</td><td width=100>&nbsp;</td></tr-->
<tr><th>Server Type</th><th>IP Address</th><th width=250>Status [Time]</th></tr>
<?=$html?>
</table>
<input type=button value="Update server status now" onclick="reloadiframes()">
</form>
<img src="images/run.jpg"> => server is running
<br><img src="images/stop.jpg"> => server is not running
<br>Server status is checked every 30 seconds.
</body>
</html>