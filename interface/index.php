<?php
$a = session_start();
if (!$a)
{
	echo "unable to start session";
}
ini_set('display_errors','off');
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
include "../classes/classes.php";
include "phpfunctions.php";
include "agentuisettings/uisetopts.php";
$session = new session(session_id());
if ($_REQUEST['act'] == 'changeproj')
	{
                $cpmsg = '';
		$p = $_REQUEST['projid'];
		$e = $_REQUEST['eyebeam'];
                $res = mysql_query("SELECT * from liveusers where extension = '$e' and userid != '".$session->userid."'");
                $rct = mysql_num_rows($res);
                if ($rct != 0)
                {
                    $cpmsg = "Extension already in use!";
                }
                elseif (strlen($e) <5) {
                    $cpmsg = 'Invalid Extenstion!';
            }
                else {
                    $res = mysql_query("SELECT name,confserver from bc_phones where name = '$e'");
                    $proje = mysql_query("SELECT * from projects where projectid = '$p'");
                    $projrec = mysql_fetch_assoc($proje);
                    $recordingmode = $projrec['callrecording'];
                    $conf = mysql_fetch_assoc($res);
		mysql_query("update liveusers set extension = '$e', projectid = '$p', leadid = NULL, callid = NULL, callerid = NULL, status = 'paused', confserver = '".$conf['confserver']."' where userid = '$session->userid'");
		mysql_query("update projects set lastactive = NOW() where projectid = '$p'");
                }
                $inb = $_GET['inb'];
                if (is_array($inb))
                {
                    $inbstr = implode(",",$inb);
                    mysql_query("update liveusers set projectid_inbound = '$inbstr'  where userid = '$session->userid'");
                }
                else {mysql_query("update liveusers set projectid_inbound = 0  where userid = '$session->userid'");}
                $session = new session(session_id());
	}
if (!$session->userid)
	{
		header("Location: ../login/");
		//var_dump($_SESSION);
	}
$userid = $session->userid;
$bcid = $_SESSION['bcid'];
$templateres = mysql_query("SELECT templateid from templates where projectid = '$session->projectid'");
$trow = mysql_fetch_array($templateres);
$withemail = $trow['templateid'];
$bcdetailsres = mysql_query("SELECT * from bc_clients where bcid = '".$_SESSION['bcid']."'");
$bcdetails = mysql_fetch_assoc($bcdetailsres);
$getproject = mysql_query("SELECT * FROM projects WHERE projectid = '$p'");
while ($getprojectdetails = mysql_fetch_array($getproject)) {
    $getprojectdata[] = $getprojectdetails;
}
foreach ($getprojectdata as $getdata) {
    $clientid = $getdata["clientid"];
}
$getclient_contacts = mysql_query("SELECT * FROM client_contacts WHERE clientid = '$clientid'");
while ($getclient_contactsdetails = mysql_fetch_array($getclient_contacts)) {
    $getclient_contactsdata[] = $getclient_contactsdetails;
}
foreach ($getclient_contactsdata as $getdata) {
    $client_contactid = $getdata["client_contactid"];
    $getclient_contactid .= $client_contactid . ",";
}
$ci = rtrim($getclient_contactid, ",");?>
<head>
<title>BlueCloud International</title>
<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="../admin/ext/resources/css/xtheme-slate.css">
<link rel="stylesheet" type="text/css" href="nav.css" />
<link rel="stylesheet" type="text/css" href="../jquery/css/redmond/jquery-ui-1.8.12.custom.css">
<link rel="stylesheet" type="text/css" href="../jquery/css/redmond/jquery.ui.selectmenu.css">
<link rel="stylesheet" type="text/css" href="../jquery/datatable/css/jquery.dataTables.css"/>
<link rel="stylesheet" type="text/css" href="../jquery/fancybox/jquery.fancybox-1.3.4.css" />
<link rel="stylesheet" type="text/css" href="../jquery/countdown/jquery.countdown.css">
<link href="styles/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="custom.css" />
<!--[if lte IE 7]>
<link type="text/css" rel="stylesheet" media="all" href="css/screen_ie.css" />
<![endif]-->
<style>
.clear {
	clear:both;
}
.no-close .ui-dialog-titlebar-close {
  display: none;
}
#hopper_results {
    border:1px solid #8ac8db;
    padding: 10px;
}
#hopper_accordion {
    width: 1150px;
    left: 10px;
    border:1px solid #c5dbec;
    border-radius: 5px;
    padding:5px 10px 10px 10px;
}
</style>
<link rel="stylesheet" type="text/css" href="../jquery/datetimepicker/jquery.datetimepicker.css" />
<script type="text/javascript" src="../jquery/js/jquery-1.7.2.min.js"></script>
<script src="../jquery/datetimepicker/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="../admin/ext/adapter/jquery/ext-jquery-adapter.js"></script>
<script type="text/javascript" src="../admin/ext/ext-core.js"></script>
<script type="text/javascript" src="../admin/ext/ext-custom.js"></script>
<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../jquery/js/jquery.ui.selectmenu.js"></script>
<?php
// echo ($session->user['chat'] == 'enabled') ? '<script type="text/javascript" src="../jquery/js/chat.js"></script>' : '';
if ($session->user['chat'] == 'enabled')
{
    if (ui_getOpt($p, 'isChatEnabled'))
    {
        echo "<script type='text/javascript' src='../jquery/js/chat.js'></script>";
        echo "<link type='text/css' rel='stylesheet' media='all' href='../jquery/css/chat/chat.css' />";
    }
}
?>
<script type="text/javascript" src="../jquery/js/tablesorter.js"></script>
<script type="text/javascript" src="../jquery/datatable/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="../jquery/countdown/jquery.plugin.min.js"></script>
<script type="text/javascript" src="../jquery/countdown/jquery.countdown.min.js"></script>
<script type="text/javascript" src="../jquery/jquery-cookie/jquery.cookie.js"></script>
<script type="text/javascript" src="../jquery/Idle.js/build/idle.min.js"></script>
<script type="text/javascript">
function focusbox(x){x.style.backgroundColor = "#E1E1E1";}
function outfocus(y){y.style.backgroundColor = "#FFFFFF";}
</script>
<?
//include "ajax.php";
echo "<script>var dialmode = '$ispred';</script>";
include "ajax-scripts.php";
$dispores = mysql_query("SELECT * from statuses where active = 1 and projectid in (0,'".$session->projectid."') order by sort,statusname ASC");
if ($dialmode == 'inbound')
		{
			$dispores = mysql_query("select * from statuses where projectid = '".$session->projectid."' order by statusname ASC");
		}
?>
</head>
<body onLoad="indicator();" style="overflow:auto; overflow-style:auto">
<!--<body style="overflow:auto; overflow-style:auto">-->
<div id="dialogcontainer" style="display:none"></div>
<div id="ttrackerpopupcontainer" style="display:none"><div id='tttimeoutPauseTimer' style='width: 240px; height: 45px;'></div><br><p align='center'><b><?php echo $session->user['alast'].", ".$session->user['afirst']; ?><br>(Pause)</b></p></div>
<div id="container" align="left">
	<div id="top">
    <div style="float:left;width:195px; height:50px; background-image:url(images/bclogo-small.png)"></div>
    <div id="top-right" style="float:right; padding:10px; text-align:right; padding-bottom:5px;">
     <span style="color:#306; font-size:10pt"><?php echo $session->user['afirst']." ".$session->user['alast'];?></span><br>
    <?php
    if ($session->projectid > 0)
    {
        ?>
    <div style="z-index: 9999">
        <form id="campswitcher" action="index.php?act=changeproj">
            <select name="projid" id="switchprojectid" onchange="switchproject()">
    <?php
    $clientnames = new clients($_SESSION['bcid']);
    $pswitch = getprojectlist($session->userid, $session->projectid, true);
    foreach($pswitch as $psel)
    {
        $pselected = '';
        if ($session->projectid == $psel['projectid']) $pselected = 'selected="selected"';
        echo '<option value="'.$psel['projectid'].'" '.$pselected.'>'.$psel['projectname'].' - '.$clientnames->getclientname($psel['clientid']).'</option>';
    }
    ?></select>
            <input type="hidden" name="eyebeam" value="<? echo $_REQUEST['eyebeam'];?>">
            <input type="hidden" name="act" value="changeproj">
            <input type="hidden" name="_USERID_" value="<?php echo $session->userid ?>">
            <input type="hidden" name="_ACT_" value="SWITCHCAMPAIGN">
        </form></div>
    <?
    }
	?>
    </div>
    <div class="clear"></div>
    <div id="main-menu" style="display:none">
    	<ul class="menu">
            <li class="home"><a href="#" onClick="hideothers('upper');"><strong>Talk</strong></a></li> 
            <li class="about"><a href="#" onClick="hideothers('scheds');"><strong>Schedule</strong></a></li> 
            <li class="services"><a href="#"><strong>Statistics</strong></a></li> 
            <li class="contact"><a href="#" onClick="converse('')"><strong>Chat</strong></a></li> 
            <li class="exit"><a href="#" onClick="exitdial()"><strong>Exit</strong></a></li> 
		</ul>
    </div>
    </div>
    <div id="stats">
    </div>
    <div id="scheds">
    <iframe src="../sched/index.php" frameborder="0" id="calframe"  name="calframe" style="width:100%; height:100%"></iframe>
    </div>
	<div style="width:100%;" align="left" id="upper">
    <?
    echo $cpmsg;
	if ($session->projectid < 1)
	{
           $u = $session->userid;
           $stats = stat24($u);
            $inb = getprojectlist($u, NULL, false, true);
	?>
    <div id="agentdash" style="color:#069;width:60%;float:left">
    <br /><br />
    <h3 >Welcome to BlueCloud.</h3><br>
    Main Campaign:<br>
    <select name="selectprojectid" id="selectprojectid"  style="width:200px" title="<?=$u;?>">
    <?=getprojectlist($u);?>
    </select><br>
    <?
    if (strlen($inb) > 5)
    {
       ?>
       Blend-Inbound Campaign:<br>
    <select name="selectinbound[]" id="selectinbound" style="width:200px" multiple="multiple">
    <?=$inb?>
    </select><br>
       <?
    }
    ?>
    <br>
    Enter softphone extension:<br>
    <input type="text" name="sextension" id="sextension"><br><br>
    <span class="mainm"><a href="../index.php?act=logout" class="jbutton">Cancel</a><a href="#" onClick="changeproj()" class="jbutton">Next</a></span>
    </div>
            <div id="gadgets">
                <div id="gadgetslogo">
                    <img src="../logo/?logo=<?=$bcdetails['logo'];?>" />
                </div><br />
                <div>Your statistics for the past 24Hours</div>
                <?php
                foreach ($stats as $key=>$data)
                {
                ?>
                <div class="gadgetwrapper">
                    <div class="gadgetbox">
                    <div class="gadgetvalue"><?php 
                    if ($key != 'Total Calls' && $key != 'Answered Calls') {echo $data['value'] ? timeunits($data['value']): timeunits(0);}
                    else echo $data['value'];
                    ?></div>
                    <div class="gadgetname"><?php echo $key;?></div>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
            <?php
	}
	else {
	?>
    <div style="width:100%; background-repeat:repeat-x; background-color:#F4F4F4;">
        <div style="width:100%px; height:27px;" align="left" id="navb">
        </div>
    <div id="maincont">
    	<div class="dialogclass" id="cslots">
        </div>
        <div id="custominfo">
            <div style="font-size:10pt;float:left;width:300px">Active Campaign: <b><?=$session->project['projectname'];?></b></div>
            <div style="font-size:10pt;float:right;width:300px">Status:<span id="dstate"></span></div>
            <div style="font-size:10pt;float:left;position:relative;left:131px;text-align: right;width:200px;display:none" id="recordingcontrol">
                <?php
                if ($recordingmode == 'optional')
                {
                    ?>
                <a href="#" onclick="startrecording()">
                    <img src="icons/recordstart.png"> Start Recording
                </a>
                    <?php
                }
                ?>
            </div>
            <div style="clear:both"></div>
       	<div style="width:60%; float:left; position:relative; left:10px;" align="left" id="maininfo">
            <div id="accordion">
                <h3>Main Information</h3>
                <div id="maininfocontent">
        	<table style="width:100%; font-size:0.8em" cellpadding="0" cellspacing="5" border="0">
            	<TR>
                	<td class="title">ListId:</td>
                    <td align="left">
                        <input type="hidden" name="listid" id="listid" value="" disabled="disabled"/><span id="listiddisplay"></span></td>
                        <input type="hidden" name="override_pid" id="override_pid" value="" />
                    <td class="title">Previous Disposition</td><td>
                        <span id="previousdispo"></span>
                    </td>
                </TR>
            	<tr>
                	<td class="title">Name:</td>
                    <td align="left"><input type="hidden" name="leadid" id="leadid" value=""/><input type="text" class="box" name="cname" onFocus="focusbox(this);" onBlur="outfocus(this);" id="cname"/></td>
                    <td class="title">Company:</td>
                    <td align="left"><input type="text" class="box" name="company" onFocus="focusbox(this);" onBlur="outfocus(this);" id="company"/></td>
                </tr>
                <tr>
                	<td class="title">Title:</td>
                    <td align="left"><input type="text" class="box" name="title" onFocus="focusbox(this);" onBlur="outfocus(this);" id="title"/></td>
                    <td class="title">Position Title:</td>
                    <td align="left"><input type="text" class="box" name="positiontitle" onFocus="focusbox(this);" onBlur="outfocus(this);" id="positiontitle"/></td>
                </tr>
                <tr>
                	<td class="title">FirstName:</td>
                    <td align="left"><input type="text" class="box" name="cfname" onFocus="focusbox(this);" onBlur="outfocus(this);" id="cfname"/></td>
                    <td class="title">LastName:</td>
                    <td align="left"><input type="text" class="box" name="clname" onFocus="focusbox(this);" onBlur="outfocus(this);" id="clname"/></td>
                </tr>
                <tr>
                	<td class="title">Industry:</td>
                    <td align="left"><input type="text" class="box" name="industry" onFocus="focusbox(this);" onBlur="outfocus(this);" id="industry"/></td>
                    <td class="title">SIC:</strong></td>
                    <td align="left"><input type="text" class="box" name="sic" onFocus="focusbox(this);" onBlur="outfocus(this);" id="sic"/></td>
                </tr>
                <tr>
                	<td class="title">Phone:</td>
                    <td align="left"><input type="text" class="box" name="phone" onFocus="focusbox(this);" onBlur="outfocus(this);" id="phone"/></td>
                    <td class="title">Alt. Phone:</td>
                    <td align="left"><input type="text" class="box" name="altphone" onFocus="focusbox(this);" onBlur="outfocus(this);" id="altphone" style="width:175px" />
                    <button type="button" class="x-btn-text" style="background-image: url(icons/dial.png); width:20px; background-repeat:no-repeat;height:20px;" id="altbutton" onClick="altdial()"></button><button type="button" class="x-btn-text" style="background-image: url(icons/disconnect.png); width:20px; background-repeat:no-repeat;height:20px;display:none" id="althang" onClick="althangup()"></button>
                    </td>
                </tr>
                <tr>
                	<td class="title">Mobile:</td>
                    <td align="left"><input type="text" class="box" name="mobile" onFocus="focusbox(this);" onBlur="outfocus(this);" id="mobile"/></td>
                    <td class="title">Email:</td>
                    <td align="left"><input type="text" class="box" name="email" onFocus="focusbox(this);" onBlur="outfocus(this);" id="email"/></td>
                </tr>
                <tr>
                	<td class="title">Address 1:</td>
                    <td colspan="3" align="left"><input type="text" class="box-l" name="address1" onFocus="focusbox(this);" onBlur="outfocus(this);" id="address1"/></td>
                </tr>
                <tr>
                	<td class="title">Address 2:</td>
                    <td colspan="3" align="left"><input type="text" class="box-l" name="address2" onFocus="focusbox(this);" onBlur="outfocus(this);" id="address2"/></td>
                </tr>
                <tr>
                	<td class="title">City/Suburb:</td>
                    <td colspan="1" align="left"><div id="citysub"><input type="text" class="box" name="city" onFocus="focusbox(this);" onBlur="outfocus(this);" id="city" /></div></td>
                    <td class="title">Postal Code:</td>
                    <td colspan="2" align="left"><input type="text" class="box" name="zip" onFocus="focusbox(this);" onBlur="outfocus(this);" id="zip"/></td>
                </tr>
                <tr>
                	<td class="title">State:</td>
                    <td colspan="1" align="left"><input type="text" class="box" name="state" onFocus="focusbox(this);" onBlur="outfocus(this);" id="state"/></td>
                    <td class="title">Country:</td>
                    <td colspan="1" align="left"><input type="text" class="box" name="country" onFocus="focusbox(this);" onBlur="outfocus(this);" id="country"/></td>
                </tr>
                <tr>
                	<td class="title">Comments:</td>
                    <td colspan="3" align="left"><textarea name="comments" cols="50" class="box-l" id="comments" onFocus="focusbox(this);" onBlur="outfocus(this);"></textarea></td>
                </tr>
            </table>
            <div id="callresults" style="width:70%">
                <table width="100%" style="font-size:0.8em">
                   <tr><td class="title">Disposition:</td><td><select class="box" name="disposition" id="disposition" onFocus="focusbox(this);" onBlur="outfocus(this);" onChange="this.options[this.selectedIndex].onclick()">
                    <option selected> </option>
                    	<?
						while ($disp = mysql_fetch_array($dispores))
							{
							if ($disp['statustype'] == 'dateandtime' || $disp['statustype'] == 'transferdateandtime')
								{
											echo "<option onclick=\"createdateinput()\">";
											echo $disp['statusname'];
											echo "</option>";
								}
							elseif ($disp['statustype'] == 'booking')
										{
											echo "<option onclick=\"doslots('$ci')\">";
											echo $disp['statusname'];
											echo "</option>";
										}
							elseif ($disp['statustype'] == 'link')
								{
								echo "<option onclick=\"showupdatepage('".$disp['statusid']."')\">";
								echo $disp['statusname'];
								echo "</option>";
								}
							elseif ($disp['statustype'] == 'update')
								{
								echo "<option onclick=\"showupdatepage()\">";
								echo $disp['statusname'];
								echo "</option>";
								}	
							else{
								echo "<option onclick=\"cleardateinput();\">";
								echo $disp['statusname'];
								echo "</option>";
							}
							}
						?>
                    </select></td></tr>
                    <tr id="datetd" style="display:none; text-align:left;" class="title">
                       <td class="title">Date:</td>
                       <td><input type="text" id="calendar" name="calendar" /></td>
                    </tr>
                    <tr id="dodisposeapplydate" style="display:none">
                       <td></td>
                       <td><input type="hidden" id="leadidtoapplybookingcalendar" name="leadidtoapplybookingcalendar" /><input type="hidden" id="slotdatecalendar" name="slotdatecalendar" /><input type="hidden" id="slotidfrombookingcalendar" name="slotidfrombookingcalendar" /><input type="button" onclick="applydatebookingcalendar()" class="jbutton" value="Apply"/></td>
                    </tr>
                    <tr id="dodispose" style="display:none">
                       <td></td>
                       <td><input type="button" class="jbutton" value="Done"/></td>
                    </tr>
                    </table>
        </div>
        </div>
            </div>
<?php include("queuepreview/index-include.php") ?>
        </div>
            <div id="otherinfo2" style="display:none">
            <h3>Other Information</h3>
                <div id="othercontent"></div>    
            </div>
            <div id="otherinfo">
                <h3>Agent Notes / History</h3>
                <div id="anotes" style="font-size:0.8em">
                    <div id="contentnotes">
                        <div id="notes" disabled="disabled"></div>
                    </div>
                    <div id="addnotes">
                        <input type="text" id="notesinput"><span id="addnotebutton" onclick="addnotes()"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>	
    <script>
        var addressFormatting = function(text){
    var newText = text;
    //array of find replaces
    var findreps = [
        {find:/^([^\-]+) \- /g, rep: '<span class="ui-selectmenu-item-header" style="line-height: 1em;margin-bottom:0px">$1</span>'},
        {find:/([^\|><]+) \| /g, rep: '<span class="ui-selectmenu-item-content" style="font-size: 0.7em; line-height:1em">$1</span>'},
        {find:/([^\|><\(\)]+) (\()/g, rep: '<span class="ui-selectmenu-item-content" style="font-size: 0.7em; line-height:1em">$1</span>$2'},
        {find:/([^\|><\(\)]+)$/g, rep: '<span class="ui-selectmenu-item-content" style="font-size: 0.7em; line-height:1em">$1</span>'},
        {find:/(\([^\|><]+\))$/g, rep: '<span class="ui-selectmenu-item-footer" style="font-size: 0.7em; line-height:1em">$1</span>'}
    ];
    for(var i in findreps){
        newText = newText.replace(findreps[i].find, findreps[i].rep);
    }
    return newText;
}  
	var cbs = 0;								  
	$("#calendar").datetimepicker({
            format: 'Y-m-d H:i',
            step: 15
        });
	callbackupdate();
        $("#switchprojectid").selectmenu({
                width: 200,
                format: addressFormatting
	});
 $("#accordion").accordion();
 $("#otherinfo").accordion();
 $("#otherinfo2").accordion();
 $(".jbutton").button();
 previous_session();
	</script>
<?php
        if ($session->user['chat'] == 'enabled')
        {
            if (ui_getOpt($p, 'isChatEnabled'))
            {
                include "../messaging.php";
            }
        }
    }
?>
    </div>
</div>
<?php 
    include("bookingrecurringslot/index-include.php"); 
    echo "<input type='hidden' id='ci' value='$ci'"; 
?>
</body>
<script>
     $(".jbutton").button();
    </script>
</html>