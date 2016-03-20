<?php
session_start();
ini_set("display_errors",'Off');
$path = '/var/www/html/BlueCloud-Dev';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
$bcid = $_SESSION['bcid'];
$act = $_REQUEST['act'];
$debugm = '';
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
require "../../classes/classes.php";
require "../../classes/records.php";
require "../../classes/lists.php";
require "../../classes/projects.php";
require "../../classes/labels.php";
require "../../classes/S3.php";
include_once '../../phpmailer/PHPMailerAutoload.php';
require "../../classes/mailer.php";
include "../phpfunctions.php";
include "qaver.php";
include "qaverswitch.php";
$projects = projectlist($bcid);
$dispolist = dispolist($_POST['projectid']);
?>
<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../../jquery/js/jqprint.js"></script>
<script type="text/javascript" src="../../jquery/js/jqform.js"></script>
<script type="text/javascript" src="../../jquery/datatable/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../../jquery/jplayer/jquery.jplayer.min.js"></script>
<script src="../../jquery/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../../jquery/datetimepicker/jquery.datetimepicker.css" />
<link href="../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="../../jquery/datatable/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
<link href="qaver.css" rel="stylesheet" type="text/css" />
<style>
.dialogform {
	display:none;
}
</style>
</head>
<!-- <body oncopy="return false" oncut="return false" onpaste="return false"> -->
<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">QA Portal</div>
</div>
<?php if (!checkrights('admin_portal')){
?>
    <div id="navmenu" style="text-align:right;padding:5px"><a href="../../index.php">Logout</a></div>
    <?}?>
<hr />
<div id="filters">
<table width="100%">
<form name="filterform" action="qa.php?act=search" method="post">
<tr>
<td>Campaign:</td><td><select name="projectid" id="projectid" onchange="projectchange();">
<option value="<?=$projects[$_POST['projectid']]['projectid'];?>"><?=$projects[$_POST['projectid']]['projectname'];?></option>
<?php
if ($_POST['projectid'] == 'all')
{
	$alsel = 'selected="selected"';
}
else $alsel = '';
?>
<option value="all" <?=$alsel;?>>All</option>
<?=createdropdown($projects,"projectname",'id');?></select></td>
<td>Start</td><td><input type="text" name="start" id="start" class="dateinput" value="<?=$_POST['start'];?>" /></td>
</tr>
<tr>
<td>Disposition:</td><td><select name="disposition" id="dispostion">
<option value="<?=$_POST['disposition'];?>"><?=ucfirst($_POST['disposition']);?></option>
<?=createdropdown($dispolist,"statusname","statusname");?></select></td>
<td>End</td><td><input type="text" name="end" id="end" class="dateinput" value="<?=$_POST['end'];?>" /></td>
</tr>
<tr>
<td>Agent</td><td colspan="1">
<select name="agentid">
<option value="all">All</option>
<?
$agentids = getagentids($bcid);
foreach ($agentids as $agid)
	{
		?>
        <option value="<?=$agid['userid'];?>"><?php echo $agid['alast'] . ", " . $agid['afirst'] . " - (" . $agid['userlogin'] . ")";?></option>
        <?
	}
?>
</select>
</td>
<?php
$datesetselected = '';
if ($_REQUEST['datetype'] == 'dateset')
{
    $datesetselected = 'selected="selected"';
}
?>
<td>Date Type</td><td><select name="datetype" id="datetype"><option value="calldate">Call Date</option><option value="dateset" <?=$datesetselected;?>>Date Set</option></select></td>
</tr>
<tr>
<td><a href="#" onclick="dosearch();">Search</a> | <a href="#" onclick="doexport();">Export</a></td><td colspan="3"></td>
</tr>
</form>
</table>
    <div style="text-align:right"><input type="button" value="Customize View" style="padding:5px" onclick="togglecv()" /></div>
</div>
<div id="cview">
    <h3 style="padding-bottom: 10px;"> Default Fields</h3>
    <?php
    $rct = 0;
    $ctmctr = 0;
    foreach ($viewfields as $key=>$arv)
    {
        if ($rct == 7) {echo '</table>';$rct = 0;}
        if ($rct == 0)
        {
            echo '<table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
        }
        /***************************/
        /* ADDED BY Vincent Castro */
        /***************************/
        if($arv[2] == "custom"){
            if($ctmctr == 0){
                echo '</table><div id="customdatatable"><h3>Custom Fields</h3><table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
            } elseif ($ctmctr > 0) {
                # code...
            }
            $ctmctr++;
            $rct = 1;
        }
        $checked = '';
        if ($arv[1] == 1) $checked = "checked";
        echo '<tr><td class="label">'.$arv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$key.'" '.$checked.'></td></tr>';
        $rct++;
    }
    echo '</table>';
    echo ($ctmctr > 0 ? '</div>' : ''); /* ADDED BY Vincent Castro */
    ?>
    <!--
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    -->
    <div class="custom-control">
        <button class="cc-button" data-action="selectall">Select All</button>
        <button class="cc-button" data-action="clearall">Clear All</button>
    </div>
</div>
<div class="clear"></div>
<hr />
<br />
<?=$dcont;?>
</div>
    <div id="jp" style="display:none"></div>
    <div id="dialogcontainer" style="display:none"></div>
    <div id="recordingscontainer" style="display:none"></div>
</body>
<script>
var viewcv = false
function inbrowser(nurl)
{
    $("#dialogcontainer").dialog("destroy");
    $("#dialogcontainer").html('<iframe src="'+nurl+'" style="border:0px" frameborder="0" width="100%" height="100%"></iframe>');
    $("#dialogcontainer").dialog({
        width: 955,
        height:505,
        modal: true
    });
}
function showupdatepage(statusid)
	{
		$.ajax({
                    url:"qa.php?act=getstatusoption&statusid="+statusid,
                    success: function(resp){
                        if (resp != 'none') inbrowser(resp);
                    }
                });
	}
function togglecv()
{
    $("#cview").toggle({easing:'linear'});
}
function createdateinput()
	{
	jQuery('#datetd').show();
	}
function cleardateinput()
	{
	jQuery('#datetd').hide();
	}
function epochtoutc(epoch)
{
    var utcSeconds = epoch;
    var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds(utcSeconds);
    return d;
}
function sendemailtoclient(i)
	{   
                var to = $("select[name=emailtoclient]").val()
                var leadid = $("#tlid").html();
                var emailcont = printable("#msg_print");
                var odata = {htmlbody: emailcont};
                $.ajax({
                    url:'qa.php?act=emailtoclient&to='+to+'&leadid='+leadid,
                    type: 'POST',
                    data: odata,
                    success:function(resp){
                        alert("Email Sent");
                    }
                });
	}
function emaillead(leadid)
	{
		$("#emailcontacts"+leadid).dialog();
                $("#setc").button();
	}
function qacall(leadid,event)
{
    event.stopPropagation();
    $("#qamailcont").remove();
    $.ajax({
        url: "qa.php?act=dialer&sub=dial_controls&leadid="+leadid,
        success: function(data){
            if (data == 'setext')
                {
                    alert("Set Extension first!");
                    setadminext();
                }
            else {
           $("#dialogcontainer").html(data);
           $("#dialogcontainer").dialog({
               modal: true
           })
           dodial(leadid);
            }
        }
    });
}
function selectslot(leadid,slotid,opt)
{
    $.ajax({
            url: 'qa.php?act=selectslot&slotid='+slotid+'&leadid='+leadid+'&opt='+opt,
            success: function(resp){
                $("#dialogcontainer").dialog("destroy");
                savelead()
            }
        });
}
function saveadminext()
{
    var adminexts = $("#setadminext").val()
    $.ajax({
 		 	url: "../admin.php?act=saveadminext&adminext="+adminexts,
 			success: function(resp){
                            $("#dialogcontainer").dialog("destroy");
                            $("#adminext").html(resp);
                            adminext = resp;
  			}
		});
}
function setadminext()
{
    $.ajax({
 		 	url: "../admin.php?act=setadminext",
 			success: function(resp){
                            $("#dialogcontainer").dialog("destroy");
                            $("#dialogcontainer").html(resp);
                            $("#dialogcontainer").dialog({
                                title: "Set Extension",
                                minWidth:400
                            });
                            $(".jbut").button();
  			}
		});
}
function qamail(leadid,event)
{
    event.stopPropagation();
    $("#qamailcont").remove();
    $.ajax({
        url: "qa.php?act=getlead&leadid="+leadid,
        success: function(data){
        //$("#dispostion").html(data);
                $("#container").append('<div id="qamailcont" style="display:none">'+data+'</div>');
                emaillead(leadid);
                // alert($("#reassign_agent_select").val());
                
        }
    });
}
function doslots(leadid,clientid)
{
        $.ajax({
            url: 'qa.php?act=cslots&clientid='+clientid+'&leadid='+leadid,
            success: function(resp){
                $("#dialogcontainer").dialog("destroy");
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog({minWidth:614});
                var dt = jQuery(".datatabslot").dataTable();
                dt.fnSort([[1,'asc']])
            }
        });
}
function dosearch()
	{
        var vf = $("input.vfs").serialize();
		document.filterform.action = 'qa.php?act=search&'+vf;
		document.filterform.submit();
	}
function doexport()
	{
        var vf = $("input.vfs").serialize();
		document.filterform.action = 'qa.php?act=export&'+vf;
		document.filterform.submit();
	}
function jpplay(media)
{
    $("#jp").jPlayer({
        ready: function () {
          $(this).jPlayer("setMedia", {
            wav: media
          });
        },
        swfPath: "../../jquery/jplayer",
        supplied: "mp3"
      });
    $("#jp").jPlayer("play");
}
function jppause()
{
    $("#jp").jPlayer("pause");
}
function jpstop()
{
    $("#jp").jPlayer("stop");
}
function jpreset()
{
    $("#jp").jPlayer("play",0);
}
var gettinglead = false;
function getlead(leadid)
{
    if (!gettinglead)
        {
            gettinglead =true;
            $.ajax({
                url: "qa.php?act=getlead&leadid="+leadid,
                success: function(data){
                //$("#dispostion").html(data);
                        $("#container").append('<div id=msg style="display:none">'+data+'</div>');
                        $(".qacf").each(function(){
                            $(this).blur(function(){
                                var f = $(this).attr("name");
                                var v = $(this).val();
                                var l = $("#leadidval").val();
                                $.ajax({
                                    url:'qa.php?act=savecf',
                                    type: "POST",
                                    data: {
                                        "field":f,
                                        "value":v,
                                        "leadid":l
                                    }
                                });
                            });
                        });
                        $(".qasf").each(function(){
                            $(this).blur(function(){
                                var f = $(this).attr("name");
                                var v = $(this).val();
                                var l = $("#leadidval").val();
                                $.ajax({
                                    url:'qa.php?act=savesf',
                                    type: "POST",
                                    data: {
                                        "field":f,
                                        "value":v,
                                        "leadid":l
                                    }
                                });
                            });
                        });
                        var lt = $(".tolocaltime").html();
                        var d = epochtoutc(lt);
                        $(".tolocaltime").html(d.toLocaleString());
                        $("#msg").dialog({
                                modal:true,
                                minWidth:820,
                                minHeight:400,
                                close: function(){ $("#msg").remove();}
                                });
                        $('.dtpick').datetimepicker({
                            format: 'Y-m-d H:i'
                        });
                        $("#updatelead").submit(function()
                                {
                                        $(this).ajaxSubmit(); 
                                });
                        gettinglead = false;
                        /***************************/
                        /* ADDED BY Vincent Castro */
                        /***************************/
                        if ($("#disposition select").val() == "ScheduledCallback") {
                            disposelect( $("#disposition select").val() );
                        }
                        }
                    });
        }
	}
function projectchange()
	{
		var v = $("#projectid").val();
		$.ajax({
			url: "qa.php?act=updatedispolist&projectid="+v,
			success: function(data){
 			    $("#dispostion").html(data);
			}
		});

        getcustomdata(v);
	}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function getcustomdata(pid){
    $("#customdatatable").remove();
    $.ajax({
        url: "qa.php?act=getcustomdata&projectid="+pid,
        success: function(data){
            $(".custom-control").before(data);
        }
    });
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function disposelect(data){
    if (data == "ScheduledCallback") {
        category = 'agent';
    } else {
        var category = $(data).find("option:selected").data('category');
    }
    // alert(category);
    $("#reassign_agent").remove();
    $("#oldagent").hide();
    if(category == 'agent'){
        var pid = $("#projectid").val();
        var userid = $("#assignedid").val();
        $.ajax({
            url: "qa.php?act=showagents&projectid="+pid+"&assigned="+userid,
            success: function(data){
                $("#tableheader").after(data);
            }
        });
    } else {
        $("#reassign_agent").remove();
        $("#oldagent").show();
    }
}
function savelead()
	{
		$("#updatelead").ajaxSubmit(function(){
				$.blockUI({ 
            message: "Lead updated successfully!", 
            fadeIn: 700, 
            fadeOut: 700, 
            showOverlay: false, 
            centerY: true, 
			centerX: true,
            css: { 
                width: '350px', 
                border: 'none', 
                padding: '5px',
                backgroundColor: '#330066', 
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                opacity: .6, 
                color: '#fff' 
            }
        	});
			setTimeout($.unblockUI, 3000);
                        $("#msg").dialog("close");
			});
	}
function printable(target)
{
     var orig = $(target).html();
     $(target+" input[type=text]").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="frominput">'+$(this).val()+'</span>');
			});
		$(target+" textarea").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="fromtextarea">'+$(this).val()+'</span>');
			});
		$(target+" select").each(function(){
			$(this).css("display","none");
			$('<span class="fromselect">'+$(this).val()+'</span>').appendTo($(this).parent());
			});
     var anew = $(target).html();
     $(target).html(orig);
     return anew;
}
function printdiv()
	{
            var orig = $("#msg_print").html();
		$("#msg_print input[type=text]").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="frominput">'+$(this).val()+'</span>');
			});
		$("#msg_print textarea").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="fromtextarea">'+$(this).val()+'</span>');
			});
		$("#msg_print select").each(function(){
			$(this).css("display","none");
			$('<span class="fromselect">'+$(this).val()+'</span>').appendTo($(this).parent());
			});
		$("#msg_print").print();
		$("#msg_print").html(orig);
	}
function approveto()
{
    var pid = $("#projectid").val();
    if (isNaN(pid))
    {
        alert("Campaign MUST be selected!");
    }
    else {
        $.ajax({
            url: 'qa.php?act=getclientcontacts&pid='+pid,
            type: 'get',
            success: function(resp){
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog();
            }
        })
    }
}

function doassignto()
{
    var contactid = $("#assigntoclientcontact").val();
    var ct = 0;var bid = new Array();
    $("[name=bulkaction]").each(function(){
        if (this.checked)
            {
                bid[ct] = $(this).val();
                ct++;
            }
    });
    var cts = 0;
    $.ajax({
        url: 'qa.php?act=bulkstatusupdate&status=assignto&contactid='+contactid,
        type: 'POST',
        data: {
            "bcids": bid
        },
        success: function(){
            var i;
            $("#dialogcontainer").dialog("destroy");
            for (i = 0;i < ct;++i)
                {
                    $("span#status"+bid[i]).html("Approved");
                    $("span#status"+bid[i]).css("text-transform","capitalize");
                }
        }
    });
}
function bulkqa(){
    var action = $("#bulkaction").val();
    if (action == 'approvedto')
    {
        approveto();
    } else if (action == 'callback') { /* ADDED BY Vincent Castro */
        transfercallback();
    }
    else {
    var ct = 0;var bid = new Array();
    $("[name=bulkaction]").each(function(){
        if (this.checked)
            {
                bid[ct] = $(this).val();
                ct++;
            }
    });
    var cts = 0;
    if (action != '')
        {
    $.ajax({
        url: 'qa.php?act=bulkstatusupdate&status='+action,
        type: 'POST',
        data: {
            "bcids": bid
        },
        success: function(){
            var i;
            for (i = 0;i < ct;++i)
                {
                    $("span#status"+bid[i]).html(action);
                    $("span#status"+bid[i]).css("text-transform","capitalize");
                }
        }
    });
        }
    }
}
/* ADDED BY Vincent Castro */
function transfercallback(){
    var pid = $("#projectid").val();
    if (isNaN(pid))
    {
        alert("Campaign MUST be selected!");
    } else {
        $.ajax({
            url: 'qa.php?act=showagentsbulk&projectid='+pid,
            type: 'get',
            success: function(resp){
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog();
            }
        })
    }
}

/* ADDED BY Vincent Castro */
function bulktransfercallback(data){
    var checkbox = $(".dataTable input[name=bulkaction]");
    var leadid = [];
    var ctr = 0;
    checkbox.each(function(){
        if(this.checked){
            leadid.push($(this).val());
            // console.log( this.closest("tr").find(".dispo") );
            if( $(this).closest("tr").find("td.dispo").html() != "ScheduledCallback"){
                ctr++;
            }
        }
    });

    if(ctr){
        alert("Only leads with callback can be transfer!");
         $("#dialogcontainer").dialog("close");
         $("#bulkaction").val(0);
    }

    var agentid = $(data).find("option:selected").val();

    if(leadid.length && agentid && ctr == 0){
        $.ajax({
            url: 'qa.php?act=transferagentsbulk',
            data: {'leadid': leadid, 'agentid': agentid},
            type: 'get',
            success: function(resp){
                location.reload();
            }
        })
    }
}

function togglecheckbox()
{
    if ($("#checkboxall").is(":checked"))
    {
        $("[name=bulkaction]").prop("checked",true);
    }
    else {
        $("[name=bulkaction]").prop("checked",false);
    }
}
function player_window(projectid, leadid, projectlinkurl)
{
    // theplayer = window.open('http://116.93.124.48/audioplayer.php?projectid='+projectid+'&leadid='+leadid, 'theplayer', 'titlebar=no,menubar=no,toolbar=no,resizable=no');
    // var player_url = 'http://116.93.124.48/audioplayer.php?projectid='+projectid+'&leadid='+leadid;
    var player_url = projectlinkurl+'?';
    $( "#recordingscontainer" ).html("<center><iframe width='100%' height='100%' src='"+ player_url + $.param( {'projectid':projectid, 'leadid':leadid} ) + "'></iframe></center>");
    $( "#recordingscontainer" ).dialog({
        title: "PLAY RECORDINGS",
        resizable: false,
        width: 400,
        height: 400,
        position: { my: "center", at: "center", of: window },
        modal: true,
        draggable: false,
        closeOnEscape: false,
        dialogClass: "no-close",
        buttons: {
            "Done": function() {
                $( this ).dialog( "close" );
                $( this ).dialog( "destroy" );
            }
        }
    });
}


$(document).ready(function(e) {
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null){
           return null;
        }
        else{
           return results[1] || 0;
        }
    }
    var v = $("#projectid").val();
    if(v != "" && $.urlParam('act') != "search") getcustomdata(v);

    $(".cc-button").click(function(){
        var action = $(this).data('action');
        if(action == "selectall"){
            $(".vfs").prop('checked', true);
        } else if(action == "clearall"){
            $(".vfs").prop('checked', false);
        }
    })
    /** end **/

    $('.dtpick').datetimepicker({
        format: 'Y-m-d H:i',
        step: 15
    });
    $(".dateinput").datepicker({ dateFormat: 'yy-mm-dd' });
	$("div#searchresults table").dataTable({
            "iDisplayLength": 50,
            "fnDrawCallback": function() {
                $(".bulk").click(function(e){
                    e.stopPropagation();
                });
            }
        });
        $(".dataTables_length").html('<select id="bulkaction" onchange="bulkqa()">'+
                '<option value="">Bulk Action</option>'+
                '<option value="approved">Approved</option>'+
                '<option value="approvedto">Approve To</option>'+
                '<option value="failed">Failed</option>'+
                '<option value="incomplete">Incomplete</option>'+
                '<option value="callback">Transfer Callback</option>'+
                '</select>');
});
</script>
</html>