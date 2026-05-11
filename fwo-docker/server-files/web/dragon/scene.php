<?php
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


if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "conf", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="1" || $HTTP_GET_VARS[a]=="0") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}

$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
if(mysql_num_rows($rsSvr) > 0)
{
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_connect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	
    if($HTTP_GET_VARS[a]=='1')
	{
		$zs = $HTTP_GET_VARS[ip];
		$scene_id = $HTTP_GET_VARS[i];

		exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$zs whoami", $shell_out, $ret);
		if($ret == 255) die("<font color=red>ZoneServer, $zs, is not setup for remote game admin.</font><p><a href='scene.php'>Back</a>");

		$query_rs = "UPDATE scene SET AccessFlag=1 WHERE SceneID='{$scene_id}'";
		$befores = get_str_rs($dbWc, "SELECT * FROM scene WHERE SceneID='{$scene_id}'");
		mysql_query($query_rs, $dbWc) or die(mysql_error());

		$after   = get_str_rs($dbWc, "SELECT * FROM scene WHERE SceneID='{$scene_id}'");
		

		// Distribute the number of players in starter scene
		if (($scene_id >= 150 && $scene_id <=199) || $scene_id <=3 )
		{
            // Update the SceneID
            $qry_sceneid = mysql_query("SELECT * FROM pcharacter WHERE (SceneID >= 150 AND SceneID <= 199) OR SceneID <= 3",$dbWc) or die(mysql_error());
            $no_player = mysql_num_rows($qry_sceneid);

            $qry_avail_scene = mysql_query("SELECT * FROM scene WHERE ((SceneID >=150 AND SceneID <= 199) OR SceneID <=3) AND AccessFlag=1",$dbWc) or die(mysql_error());
            $no_scene = mysql_num_rows($qry_avail_scene);

            $each = $no_player / $no_scene;

            $offset2 = 0;
            $total_row = ceil($each);
            while ($rw2 = mysql_fetch_assoc($qry_avail_scene))
            {
                $blah = mysql_query("SELECT * FROM pcharacter WHERE (SceneID >= 150 AND SceneID <= 199) OR SceneID <=3 LIMIT $offset2, $total_row",$dbWc) or die(mysql_error());
                while ($rw3 = mysql_fetch_assoc($blah))
                {
                    $run_update = mysql_query("UPDATE pcharacter SET SceneID = $rw2[SceneID] WHERE CharID= $rw3[CharID]", $dbWc) or die(mysql_error($dbWc));
                }
                $offset2 = $offset2 + $total_row;
            }

            // Update the BindSceneID
            $qry_bindsceneid = mysql_query("SELECT * FROM pcharacter WHERE (BindSceneID >= 150 AND BindSceneID <= 199) OR BindSceneID <= 3",$dbWc) or die(mysql_error());
            $no_bindsceneplayer = mysql_num_rows($qry_bindsceneid);

            $qry_avail_scene2 = mysql_query("SELECT * FROM scene WHERE ((SceneID >=150 AND SceneID <= 199)  OR SceneID <= 3) AND AccessFlag=1",$dbWc) or die(mysql_error());
            $no_availscene = mysql_num_rows($qry_avail_scene2);

            $each = $no_bindsceneplayer / $no_availscene;

            $offset2 = 0;
            $total_row = ceil($each);
            while ($rw4 = mysql_fetch_assoc($qry_avail_scene2))
            {
                $blah2 = mysql_query("SELECT * FROM pcharacter WHERE (BindSceneID >= 150 AND BindSceneID <= 199) OR BindSceneID <=3 LIMIT $offset2, $total_row",$dbWc) or die(mysql_error());
                while ($rw5 = mysql_fetch_assoc($blah2))
                {
                    $run_update2 = mysql_query("UPDATE pcharacter SET BindSceneID = $rw4[SceneID] WHERE CharID = $rw5[CharID]", $dbWc) or die(mysql_error($dbWc));
                }
                $offset2 = $offset2 + $total_row;
            }
        }
        
        

		$shell_cmd = "ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$zs sudo -u $zs_username $SETCMD 5 65";
		unset($shell_out);
		exec($shell_cmd, $shell_out, $ret);
        $after = join("\n", $shell_out);
		if($ret != 0) die("<font color=red>Failed to setcmd to server, $zs.</font><p><a href='scene.php'>Back</a>");
		

		header("Location: scene.php");
		exit();
	}
	elseif($HTTP_GET_VARS[a]=='0')
	{

		$zs = $HTTP_GET_VARS[ip];
  		$scene_id = $HTTP_GET_VARS[i];
		
		$qry_starter = mysql_query("SELECT * FROM scene WHERE ((SceneID >=150 AND SceneID <= 199) OR SceneID <=3) AND AccessFlag=1",$dbWc) or die(mysql_error());

        if ((($scene_id >= 150 && $scene_id <=199) || $scene_id <=3) && mysql_num_rows($qry_starter)==1)
        {
            header("Location: scene.php?z=1");
            exit();
        }
        else
        {

    		exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$zs whoami", $shell_out, $ret);
    		if($ret == 255) die("<font color=red>ZoneServer, $zs, is not setup for remote game admin.</font><p><a href='scene.php'>Back</a>");

    		$query_rs = "UPDATE scene SET AccessFlag=0 WHERE SceneID='{$scene_id}'";
    		$befores = get_str_rs($dbWc, "SELECT * FROM scene WHERE SceneID='{$scene_id}'");
    		mysql_query($query_rs, $dbWc) or die(mysql_error());

    		$after = get_str_rs($dbWc, "SELECT * FROM scene WHERE SceneID='{$scene_id}'");


    		// Distribute the number of players in starter scene
    		if (($scene_id >= 150 && $scene_id <=199) || $scene_id <=3 )
    		{
                // Update the SceneID
                $qry_sceneid = mysql_query("SELECT * FROM pcharacter WHERE (SceneID >= 150 AND SceneID <= 199) OR SceneID <= 3",$dbWc) or die(mysql_error());
                $no_player = mysql_num_rows($qry_sceneid);

                $qry_avail_scene = mysql_query("SELECT * FROM scene WHERE ((SceneID >=150 AND SceneID <= 199) OR SceneID <=3) AND AccessFlag=1",$dbWc) or die(mysql_error());
                $no_scene = mysql_num_rows($qry_avail_scene);

                $each = $no_player / $no_scene;

                $offset2 = 0;
                $total_row = ceil($each);
                while ($rw2 = mysql_fetch_assoc($qry_avail_scene))
                {
                    $blah = mysql_query("SELECT * FROM pcharacter WHERE (SceneID >= 150 AND SceneID <= 199) OR SceneID <=3 LIMIT $offset2, $total_row",$dbWc) or die(mysql_error());
                    while ($rw3 = mysql_fetch_assoc($blah))
                    {
                        $run_update = mysql_query("UPDATE pcharacter SET SceneID = $rw2[SceneID] WHERE CharID= $rw3[CharID]", $dbWc) or die(mysql_error($dbWc));
                    }
                    $offset2 = $offset2 + $total_row;
                }

                // Update the BindSceneID
                $qry_bindsceneid = mysql_query("SELECT * FROM pcharacter WHERE (BindSceneID >= 150 AND BindSceneID <= 199) OR BindSceneID <= 3",$dbWc) or die(mysql_error());
                $no_bindsceneplayer = mysql_num_rows($qry_bindsceneid);

                $qry_avail_scene2 = mysql_query("SELECT * FROM scene WHERE ((SceneID >=150 AND SceneID <= 199)  OR SceneID <= 3) AND AccessFlag=1",$dbWc) or die(mysql_error());
                $no_availscene = mysql_num_rows($qry_avail_scene2);

                $each = $no_bindsceneplayer / $no_availscene;

                $offset2 = 0;
                $total_row = ceil($each);
                while ($rw4 = mysql_fetch_assoc($qry_avail_scene2))
                {
                    $blah2 = mysql_query("SELECT * FROM pcharacter WHERE (BindSceneID >= 150 AND BindSceneID <= 199) OR BindSceneID <=3 LIMIT $offset2, $total_row",$dbWc) or die(mysql_error());
                    while ($rw5 = mysql_fetch_assoc($blah2))
                    {
                        $run_update2 = mysql_query("UPDATE pcharacter SET BindSceneID = $rw4[SceneID] WHERE CharID = $rw5[CharID]", $dbWc) or die(mysql_error($dbWc));
                    }
                    $offset2 = $offset2 + $total_row;
                }
            }


           


    		$shell_cmd = "ssh -o 'StrictHostKeyChecking=no' -i $rsa_file $remote_username@$zs sudo -u $zs_username $SETCMD 5 65";
    		unset($shell_out);
    		exec($shell_cmd, $shell_out, $ret);
    		$after = join("\n", $shell_out);
    		if($ret != 0) die("<font color=red>Failed to setcmd to server, $zs.</font><p><a href='scene.php'>Back</a>");
    		
    		header("Location: scene.php");
    		exit();
		}
	}

	$rsScene = mysql_query("SELECT * FROM scene ORDER BY SceneID", $dbWc) or die(mysql_error());
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
$htmlWc="<select name=wid onchange=\"postform(document.form1,'scene.php')\"><option value=''></option>";
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
function saveid(i)
{
	if(eval("!document.form1.mark_"+i+".value"))
	{
		eval("document.form1.mark_"+i+".value=1")
		document.form1.ids.value+=i+"|"
	}
}
function doCheckAll(nm,v){
	with (document.form1)
		for (var i=0; i < elements.length; i++)
			if (elements[i].type == 'checkbox' && elements[i].name == nm)elements[i].checked = v
}

//-->
</script>
</head>

<?
$onload = "onLoad=\"";

if ($HTTP_GET_VARS[z])
{
        $onload .= "javascript:alert('Scene was not disabled! At least one scene must be ON between scene 1 to scene 3 and scene 150 to scene 199.');";
}

$onload .= "\"";

if (!$HTTP_GET_VARS[z])
{
    $onload = "";
}
?>

<body <?=$onload?>>
<form name="form1" method="post" action="">
<h3>Scene Controller</h3>
(World Controller: <?=$htmlWc?>)
<br><input type=button value=Refresh onclick="location.reload()">
<br><br>
<?
$current_server_id=0;
$n=0;
$html_scene = "";
$yesno = array(0=>'<font style="background-color:red;color:white">No</font>', 'Yes');
if(is_resource($rsScene))
{
	while($row=mysql_fetch_assoc($rsScene))
	{
		$n++;
		if($row[AccessFlag])
		{
			$toggleflag = "<input type=button value=\"Disable\" onclick=\"if(confirm('Confirm disable?'))postform(document.form1, 'scene.php?a=0&i={$row[SceneID]}&ip={$row[Address]}')\">";
		}
		else
		{
			$toggleflag = "<input type=button value=\"Enable\" onclick=\"if(confirm('Confirm enable?'))postform(document.form1, 'scene.php?a=1&i={$row[SceneID]}&ip={$row[Address]}')\">";
		}

		$html_scene .= "<tr><td>$row[SceneID]</td><td>$row[SceneFileName]</td><td>{$row[Address]}</td><td>{$yesno[$row[AccessFlag]]}</td><td>$toggleflag</td></tr>";
	}

	if(strlen($html_scene) == 0)
	{
		echo "<font color=red>No record found.</font>";
	}
	else
	{
		echo "
			<table border=\"1\" cellspacing=0>
			<tr>
				<th>Scene ID</th>
				<th>Scene Filename</th>
				<th>ServerIP</th>
				<th>Accessible</th>
				<th>Action</th>
			</tr>
			$html_scene
			</table>
			";
	}
}
?>
</form>
</body>
</html>
