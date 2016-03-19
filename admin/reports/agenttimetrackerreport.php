<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
include "../phpfunctions.php";
$bcid = getbcid();
include "ttajax.php";
if (!checkrights('reports'))
{
    echo "Permission Error.";
    exit;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" type="text/css" href="../../jquery/jquery-ui-custom/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="../../jquery/datatable/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="../../jquery/DataTables-1.9.4/extras/TableTools/media/css/TableTools.css">
<link rel="stylesheet" type="text/css" href="../../jquery/ptTimeselect/jquery.ptTimeSelect.css">
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/jquery-ui-custom/jquery-ui.js"></script>
<script type="text/javascript" src="../../jquery/datatable/js/jquery.dataTables.min.js?v2"></script>
<script type="text/javascript" src="../../jquery/DataTables-1.9.4/extras/TableTools/media/js/ZeroClipboard.js"></script>
<script type="text/javascript" src="../../jquery/DataTables-1.9.4/extras/TableTools/media/js/TableTools.js"></script>
<script type="text/javascript" src="../../jquery/ptTimeselect/jquery.ptTimeSelect.js"></script>
<link rel="stylesheet" type="text/css" href="cstyle.css" />
<style>
a.repbutton {
    border: 1px solid #16A6BD;
    color: #16A6BD;
    float: left;
    padding: 10px;
    width: 120px;
    background:#E0FFFF;
    border-radius:10px;
    text-decoration: none;
    font-size:11pt;
}
.clear {
    clear: both;
}
</style>
<script type="text/javascript" >
function ConvertTimeformat(format, str) {
    var hours = Number(str.match(/^(\d+)/)[1]);
    var minutes = Number(str.match(/:(\d+)/)[1]);
    var AMPM = str.match(/\s?([AaPp][Mm]?)$/)[1];
    var pm = ['P', 'p', 'PM', 'pM', 'pm', 'Pm'];
    var am = ['A', 'a', 'AM', 'aM', 'am', 'Am'];
    if (pm.indexOf(AMPM) >= 0 && hours < 12) hours = hours + 12;
    if (am.indexOf(AMPM) >= 0 && hours == 12) hours = hours - 12;
    var sHours = hours.toString();
    var sMinutes = minutes.toString();
    if (hours < 10) sHours = "0" + sHours;
    if (minutes < 10) sMinutes = "0" + sMinutes;
    if (format == '0000') {
        return (sHours + sMinutes);
    } else if (format == '00:00') {
        return (sHours + ":" + sMinutes);
    } else {
        return false;
    }
}
</script>
</head>

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Agent Timesheet Report</div>
</div>
<hr />
<div id="query">
    <input type="hidden" name="act" value="dosearch" />
    <table width="929" border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td width="80">Client</td>
        <td width="345"><select id="clients" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
      <tr>
        <td width="80">Campaign</td>
        <td width="345"><select id="projects" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
      <tr>
        <td width="80">Agent</td>
        <td width="345"><select id="members" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
      <tr>
        <td width="80">Team</td>
        <td width="345"><select id="teams" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
      <tr>
        <td>Date Start</td>
        <td><input style="width: 96px" type="text" name="start" class="dates" id="start" />
            <input style="width: 96px" type="text" name="starttime" class="timeselect" id="starttime" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Date End</td>
        <td><input style="width: 96px"type="text" name="end" class="dates" id="end"/>
            <input style="width: 96px"type="text" name="endtime" class="timeselect" id="endtime" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
      	<td colspan="4" align="left"><button id="clickview" onclick="viewrep( $('#clients').val(),$('#projects').val(),$('#members').val(),$('#teams').val(),$('#start').val()+' '+ConvertTimeformat('00:00',$('#starttime').val()),$('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()) )">View</button></td>
      </tr>
    </table>

</div>
<div id="reporttabs">
    <ul>
        <li><a href="#reporttabs-1">Summary</a></li>
        <li><a href="#reporttabs-2">Breakdown</a></li>
    </ul>
    <div id="reporttabs-1">
       <table id="ttreportsummary" class="dtt_display">
            <thead>
              <tr>
                <th width="25%">Expand All</th>
                <th>Campaign Hours</th>
                <th>Total Hours</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="reporttabs-2">
        <table id="ttreportlog" class="dtt_display">
            <thead>
              <tr>
                <th>Client</th>
                <th>Campaign</th>
                <th>Agent</th>
                <th>Date</th>
                <th>Started</th>
                <th>Duration</th>
                <th>Event</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});

			
document.title = window.name;
function viewrep(_clientid, _projectid, _memberid, _teamid, _start, _end)
{
    // alert("Client: "+_clientid+"Campaign: "+_projectid+"Agent: "+_memberid+"Start: "+_start+"End: "+_end);

    $('#reporttabs').tabs({active: 0});

    // $ottreportsummary.fnClearTable();
    $ottreportsummary.dataTable().fnDestroy();
    $ottreportsummary.width("100%");
    $ottreportsummary.dataTable( {
//        "bDestroy": true,
//        "bJQueryUI": true,
//        "sDom": 'T<"clear"lf>rtip',
//        "oTableTools": {
//            "sSwfPath": "/jquery/DataTables-1.9.4/extras/TableTools/media/swf/copy_csv_xls_pdf.swf"
//        },
        "asStripeClasses": [],
        "aoColumnDefs": [
            { "sWidth": "25%", "aTargets": [ 0 ] }],
        "sAjaxSource": "?act=loadreportsummary"+"&_clientid="+_clientid+"&_projectid="+_projectid+"&_memberid="+_memberid+"&_teamid="+_teamid+"&_start="+_start+"&_end="+_end,
        "fnInitComplete": function () {

            $("#projects").find("option[value!=0]").contents().each(function(){ $ottreportsummary.find("td:contains('"+$(this).eq(0).text()+"')").attr('align','right') });

            $ottreportsummary.$(".agentcampaign").hide();

            // $ottreportsummary.$(".agentname").find('td:eq(0)').prepend('<a href="#"><span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show Campaign Breakdown"></span></a>&nbsp;&nbsp;');
            $ottreportsummary.$(".agentname").find('td:eq(0)').each(function(index) {
                $(this).prepend("<a href='#"+(index+1)+"'><span class='ui-icon ui-icon-circle-plus' style='float: left' title='Show Campaign Breakdown'></span></a>&nbsp;&nbsp;");
            });

            var display = false;
            $(".agentname").find('a').on( 'click', function(){ 
                if (!display)
                {
                    display = true;
                    $( this ).html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide Campaign Breakdown"></span>');
                }
                else {
                    display = false;
                    $( this ).html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show Campaign Breakdown"></span>');
                }
                    
                $( "#"+$(this).parent().parent().attr('id')+".agentcampaign" ).toggle(display);
                // alert(display);
            } );

            var displayall = false;
            $('#expandclick').html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span>');
            $('#expandclick').off();
            $('#expandclick').on( 'click', function(){ 
                 if (!displayall)
                 {
                      displayall = true;
                      $( this ).html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide All Campaign Breakdown"></span>');

                      display = true;
                      $(".agentname").find('a').html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide Campaign Breakdown"></span>');
                 }
                 else {
                      displayall = false;
                      $( this ).html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span>');

                      display = false;
                      $(".agentname").find('a').html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span>');
                 }

                 $ottreportsummary.$(".agentcampaign" ).toggle(displayall);
                 // alert(displayall);
            } );

            // $ottreportsummary.find('thead tr th').eq(0).prepend('<a href="#"" id="expandclick"><span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span></a>&nbsp;&nbsp;');


            $('#reporttabs').tabs({active: 1});

        //    $ottreportlog.fnClearTable();
            $ottreportlog.dataTable().fnDestroy();
            $ottreportlog.width("100%");
            $ottreportlog.dataTable( {
        //        "bDestroy": true,
        //        "bJQueryUI": true,
        //        "sDom": 'T<"clear"lf>rtip',
        //        "oTableTools": {
        //            "sSwfPath": "/jquery/DataTables-1.9.4/extras/TableTools/media/swf/copy_csv_xls_pdf.swf"
        //        },
                "sAjaxSource": "?act=loadreport"+"&_clientid="+_clientid+"&_projectid="+_projectid+"&_memberid="+_memberid+"&_teamid="+_teamid+"&_start="+_start+"&_end="+_end,
                "fnInitComplete": function () {
                    $('#reporttabs').tabs({active: 0});
                }
            } );
        //"fnInitComplete": function () {
        }

    //$ottreportsummary.dataTable( {
    } );



}
function exportrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var mo = $("#mo").val();
	var url =  "agentperformance.php?act=view&act2=excel&projectid="+proj+"&start="+start+"&end="+end+"&mo="+mo;
	window.open(url);
	
}
function exporttoclient(cid,repname)
{
    var body = $("#apdiv").html();
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: '../admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
function updatemo()
{
    var mopid = $("#projectid").val();
    $.ajax({
        url: '../admin.php?act=getstatusbypid',
        type: 'POST',
        data: 'pid='+mopid,
        success: function(resp){
            $("#mo").html(resp);
        }
    })
}
$(document).ready(

    function() {
        
        $("#start").attr('value', "<?=date( 'Y-m-d', strtotime('-1 day') )?>");
        $("#end").attr('value', "<?=date('Y-m-d')?>");

        $("#starttime").attr('value', "12:00 AM");
        $("#endtime").attr('value', "11:59 PM");

        $.extend( $.fn.dataTable.defaults, {
            "sDom": 'T<"clear"lf>rtip',
            "oTableTools": {
                "sSwfPath": "../../jquery/DataTables-1.9.4/extras/TableTools/media/swf/copy_csv_xls_pdf.swf"
            },
            "aLengthMenu": [50, 100, 150, 200],
            "bSort": false,
            "bProcessing": true,
            "aaSorting": [],
            "sPaginationType": "full_numbers",
            "iDisplayLength": 150,
            "sScrollX": "100%",
//            "sScrollXInner": "110%",
//            "bScrollCollapse": true,
//            "bScrollInfinite": true,
            "bAutoWidth": false
        } );

        $('#clickview').button();

        $('#reporttabs').tabs({active: 1});
    
        $.ajax({
            url : '?act=loadrepfilters',
            dataType: 'script',
            success: function(resp) {
                // alert('Loaded report filters.');
            }

        });

        $('#reporttabs').tabs({active: 0});

        $.ajax({
            url : '?act=loadrepsummarycols',
            dataType: 'script',
            success: function(resp) {                
                $ottreportsummary = $('#ttreportsummary').dataTable( {
                    "asStripeClasses": [],
                    "aoColumnDefs": [
                        { "sWidth": "25%", "aTargets": [ 0 ] }],
                    "sAjaxSource": "?act=loadreportsummary"+"&_clientid="+$('#clients').val()+"&_projectid="+$('#projects').val()+"&_memberid="+$('#members').val()+"&_teamid="+$('#teams').val()+"&_start="+$('#start').val()+' '+ConvertTimeformat('00:00',$('#starttime').val())+"&_end="+$('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()),
//                    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
//                        $('td:eq(0)', nRow).prepend( "[+] " );
//                    },                    
                    "fnInitComplete": function () {
                        $ottreportsummary.find("th").eq(0).attr('style','width: 25%');
                        $("#projects").find("option[value!=0]").contents().each(function(){ $ottreportsummary.find("td:contains('"+$(this).eq(0).text()+"')").attr('align','right') });

                        $ottreportsummary.$(".agentcampaign").hide();
                        // $ottreportsummary.$(".agentname").find('td:eq(0)').prepend('<a href="#"><span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show Campaign Breakdown"></span></a>&nbsp;&nbsp;');
                        $ottreportsummary.$(".agentname").find('td:eq(0)').each(function(index) {
                            $(this).prepend("<a href='#"+(index+1)+"'><span class='ui-icon ui-icon-circle-plus' style='float: left' title='Show Campaign Breakdown'></span></a>&nbsp;&nbsp;");
                        });

                        var display = false;
                        $(".agentname").find('a').on( 'click', function(){ 
                            if (!display)
                            {
                                display = true;
                                $( this ).html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide Campaign Breakdown"></span>');
                            }
                            else {
                                display = false;
                                $( this ).html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show Campaign Breakdown"></span>');
                            }
                                
                            $( "#"+$(this).parent().parent().attr('id')+".agentcampaign" ).toggle(display);
                            // alert(display);
                        } );

                        $ottreportsummary.find('thead tr th').eq(0).prepend('<a href="#"" id="expandclick"><span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span></a>&nbsp;&nbsp;');

                        var displayall = false;
                        $('#expandclick').off();
                        $('#expandclick').on( 'click', function(){ 
                             if (!displayall)
                             {
                                  displayall = true;
                                  $( this ).html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide All Campaign Breakdown"></span>');

                                  display = true;
                                  $(".agentname").find('a').html('<span class="ui-icon ui-icon-circle-minus" style="float: left" title="Hide Campaign Breakdown"></span>');
                             }
                             else {
                                  displayall = false;
                                  $( this ).html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span>');

                                  display = false;
                                  $(".agentname").find('a').html('<span class="ui-icon ui-icon-circle-plus" style="float: left" title="Show All Campaign Breakdown"></span>');
                             }

                             $ottreportsummary.$(".agentcampaign" ).toggle(displayall);
                             // alert(displayall);
                        } );

                        $('#ttreportsummary').width("100%");

                        $('#reporttabs').tabs({active: 1});
                        $ottreportlog = $('#ttreportlog').dataTable( {
                            "sAjaxSource": "?act=loadreport"+"&_clientid="+$('#clients').val()+"&_projectid="+$('#projects').val()+"&_memberid="+$('#members').val()+"&_teamid="+$('#teams').val()+"&_start="+$('#start').val()+' '+ConvertTimeformat('00:00',$('#starttime').val())+"&_end="+$('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()),
                            "fnInitComplete": function () {
                                $ottreportlog.width("100%");
                                $ottreportlog.fnDraw();
                                $('#reporttabs').tabs({active: 0});
                                $ottreportsummary.fnDraw();
                            }
                        } );


                    }
                } );

            }
            // success: function(resp) {



        });

        //$('#reporttabs').tabs({active: 1});


        $('.timeselect').ptTimeSelect({ onClose: function (input) {
                console.log( "input: "+ConvertTimeformat("00:00", input.val()) );
            }

        });
    }
);
</script>