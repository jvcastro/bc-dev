<?php
?>
<script>
var th = window.innerHeight;
var tw = window.innerWidth;
var cwinvar_client;
var lister = 0;
var gds = false;
var popup = false;
var support = '<?=$_SESSION['support'];?>';
var email = '<?=$_SESSION['email'];?>';
var bui = 0;
var adminext = '<?php echo $_SESSION['adminext'] ? $_SESSION['adminext']: 'none';?>';
function listhistory(lid)
{
    $.ajax({
        url:'admin.php?act=getrecyclehistory&lid='+lid,
        success: function(resp){
            $("#dialogcontainer").dialog("destroy");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: '',
                minWidth: 800,
                maxHeight:500
            });
            var edate = $(".tolocaldate");
                edate.each(function(){
                    var v = $(this).html();
                    var d = epochtoutc(v);
                    var dt = d.toLocaleString();
                    $(this).html(dt);
                });
        }
    });
}
function resourceupload()
{
    $("#resourceupload").dialog('destroy');
    $("#resourceupload").dialog({
        width:300,
        title:'Upload File'
    });
}
function importdata(){
    $.ajax({
        url: 'admin.php?act=importdata',
        success: dresponsehandler
    });
}
function removeexclusion(eid)
{
    $.ajax({
        url: 'admin.php?act=removeexclusion&id='+eid,
        success: function(resp){dialogwindow('manexcl');}
    });
}
function refreshinfo(nonauto)
{
    var campset = $("#activecamp table").dataTable().fnSettings();
    var sorting = campset.aaSorting;
    var fil =  $("#liveMonitor_filter input").val();
    var fil2 =  $("#liveuserstable_filter input").val();
    var lsorting = $("#liveusers table").dataTable().fnSettings().aaSorting;;
    var ldisp = $("#liveusers table").dataTable().fnSettings()._iDisplayLength;
    var lpage = $("#liveusers table").dataTable().fnSettings()._iDisplayStart;
    var apt = $(".apptitle").html();
    var page_camp = campset._iDisplayStart;
    var disp_camp = campset._iDisplayLength;
    if (apt == 'Live Monitor')
        {
            $.ajax({
            url: "live.php?act=refresh",
            success: function(resp){
                var obj = JSON.parse(resp);
                $("#activecamp").html(obj.activecamp);
                $("#activecamp table").dataTable({
                    "aaSorting": sorting,
                    "iDisplayStart":page_camp,
                    "iDisplayLength":disp_camp,
                    "aLengthMenu": [[5, 8, 10], [5,8,10]]
                })
                $("#liveusers").html(obj.liveusers);
                $("#liveusers table").dataTable({
                    "aaSorting": lsorting,
                    "iDisplayLength": ldisp,
                    "iDisplayStart": lpage
                });
                console.log(obj.elapse);
                $("#liveMonitor_filter input").val(fil);
                $("#liveMonitor_filter input").trigger('keyup');
                $("#liveuserstable_filter input").val(fil2);
                $("#liveuserstable_filter input").trigger('keyup');
                if (!nonauto) {
                    setTimeout("refreshinfo()",5000);
                }
            }
            });
        }
}
function endbarge()
{
    $.ajax({
                    url: "admin.php?act=endbarge&origin="+adminext,
                    success: function(){
                        $("#formloader").dialog("destroy");
                    }
		});
}
function bargethis(ext)
{
    if (adminext == 'none') {
        alert('Set your extension first.');
        //setadminext();
    }
    else {
        $.ajax({
                    url: "admin.php?act=barge&origin="+adminext+"&target="+ext,
                    success: function(resp){
                        $("#formloader").dialog("destroy");
                        $("#formloader").html(resp);
                            $("#formloader").dialog({
                                modal: true,
                                title: "Barge",
                                close: endbarge
                            });
                            $(".jbut").button();
                    }
		});
    }
}
function saveadminext()
{
    var adminexts = $("#setadminext").val()
    $.ajax({
 		 	url: "admin.php?act=saveadminext&adminext="+adminexts,
 			success: function(resp){
                            $("#formloader").dialog("destroy");
                            $("#adminext").html(resp);
                            adminext = resp;
  			}
		});
}
function setadminext()
{
    $.ajax({
 		 	url: "admin.php?act=setadminext",
 			success: function(resp){
                            $("#formloader").dialog("destroy");
                            $("#formloader").html(resp);
                            $("#formloader").dialog({
                                title: "Set Extension",
                                minWidth:400
                            });
                            $(".jbut").button();
  			}
		});
}
function gotovip()
{
    $.ajax({
            url: "vip/account.php",
            success: function(resp){
            $("#displayport").html(resp);
            }
            });
}
function resizedisplayport()
{
}
function appbook(ci,clientid)
{
	 jQuery('#jqdialog').html("<iframe src='../modules-dev/appbook.php?ci="+ci+"&cid="+clientid+"' width='100%' height='100%'  frameborder='0'></iframe>");
	 jQuery("#jqdialog").dialog({width:785,height:540, title:'Edit Appointment Schedules'})
}
function indicator()
{
	if (http.readyState == 1 && http.readyState <= 3)
		{
			if (bui == 0)
			{
			bui = 1;
			jQuery.blockUI({ 
            message: "Please Wait...", 
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
			}
		}
	else {
		jQuery.unblockUI();
		bui =0;
	}
	setTimeout("indicator()",1000);
}
function customdispositions()
{
    $("#customdispositions tbody").sortable({
              axis: 'y',
              stop: function (event, ui) {
	        var oData = $(this).sortable('serialize');
                //$('span').text(data);
        // console.log(oData);
                $.ajax({
                    data: oData,
                    type: 'POST',
                    url: 'admin.php?act=resortdispo',
                    success: function(resp){
                        var sortobj = $.parseJSON(resp);
                        $.each(sortobj,function(index,value){
                            $("#sort"+index).html(value);
                        });
                    }
                });
                }
        });
$("#customdispositions tbody tr").mousedown(function(){
    $(this).css("border","1px outset #000000");
   $(this).css("background-color","#ffffff"); 
   $(this).css("cursor","move");
})
$("#customdispositions tbody tr").mouseup(function(){
    $(this).css("cursor","default");
    $(this).css("border","none");
});
}
function customfieldstable(projid)
{
$("#customfieldstable tbody").sortable({
    axis: 'y',
    stop: function (event, ui) {
        var oData = [];
        $("#customfieldstable tbody .fieldname").each(function( index ) {
          oData.push( $( this ).text() );
        });
        //$('span').text(data);
        // console.log(oData);
        $.ajax({
            data: {"custfield" : oData, "projectid" : projid},
            type: 'POST',
            url: 'admin.php?act=sortcf',
            success: function(resp){
                var sortobj = $.parseJSON(resp);
                $.each(sortobj,function(index,value){
                    $("#sort"+index).html(value);
                });
            }
        });
    }
});
$("#customfieldstable tbody tr").mousedown(function(){
    $(this).css("border","1px outset #000000");
    $(this).css("background-color","#ffffff"); 
    $(this).css("cursor","move");
})
$("#customfieldstable tbody tr").mouseup(function(){
    $(this).css("cursor","default");
    $(this).css("border","none");
});
}
var camploaded = false;
function campaignMenu(target){
        mancampactivesection = target;
        var buttonSelected = target + "_button";
        var pid = $("#cprojid").html();
        if (camploaded == false)
            {
                jQuery.ajax({
           url: 'admin.php?act=managecamp&pid='+pid,
           success: function(resp){
               camploaded = true
               $("#displayport").html(resp);
               $("#campsettingsdisplay input[type=button]").button();
               campaignMenu(target);
                }
                });
            nprojid = pid;
            }
	hideAllContent();
	$(".activeMenu").each(function(){
		$(this).removeClass('activeMenu');
	});
	$("#"+target).fadeIn();
        if(target == 'scripts')
            {
                camploaded = false;
                editscript(pid);
            }
        if (target == 'actiontags')
        {
            camploaded = false;
            actiontags(pid);
        }
        if (target == 'objectives')
            {
                 camploaded = false;
                 objectives(pid);
            }
        if (target == 'snapshot')
            {
                camploaded = false;
                getsnapshot(pid,'agent');
            }
        if (target == 'voicemail')
            {
                camploaded = false;
                voicemail(pid);
            }
	$("#"+target+"_button").addClass('activeMenu');
        if (target == 'tableCampaignDisposition')
            {
                customdispositions();
            }
        if (target == 'tableCustomFields')
        {
            customfieldstable(pid);
        }
}
function objectives(pid)
{
    var params = '?act=objectives&pid='+pid;
                jQuery.ajax({
                    url:'admin.php'+params,
                    success: function(resp){
                        $('#campsettingsdisplay').html(resp);
                        $('.jbut').button();
                    }
                });
}
function voicemail(pid)
{
    pleasewait("Retrieving Voicemail...");
    var params = '?act=voicemail&pid='+pid;
    jQuery.ajax({
        url:'admin.php'+params,
        success: function(resp){
            pleasewait("close");
            $('#campsettingsdisplay').html(resp);
            $('.jbut').button();
        }
    });
}
function deletevm(ob,pid,dt)
{
    pleasewait('Deleting Voicemail..');
    var params = '?act=deletevm&ob='+ob;
    jQuery.ajax({
        url:'admin.php'+params,
        success: function(resp){
            $("#"+dt).remove();
            pleasewait('close');
        }
    });
}
function actiontags(pid)
{
    var params = '?act=actiontags&pid='+pid;
                jQuery.ajax({
                    url:'admin.php'+params,
                    success: function(resp){
                        $('#campsettingsdisplay').html(resp);
                        $('.jbut').button();
                    }
                });
}
function hideAllContent()
{
	$("#campsettingsdisplay div.campsection").each(function(){
           $(this).hide(); 
        });
}
function deactivateClient(id){
    if (!confirm)
            {
                confirmbox("deactivateClient("+id+")","Are you sure?");
            }
     else {
         confirm = false;
	var inactive = $.ajax({
		  type: "POST",
		  url: "admin.php",
		  data: { id: id, deactivate: 'yes' },
		  dataType: 'json',
		  success: function(data, status) {
                            var state = $("#selectCampaignStatus").val();
                            manclient(state);
    	}
	});
     }
}
function deleteClient(id){
    if (!confirm)
            {
                confirmbox("deleteClient("+id+")","Are you sure?");
            }
     else {
         confirm = false;
	var inactive = $.ajax({
		  type: "POST",
		  url: "admin.php",
		  data: { id: id, deactivate: 'del' },
		  dataType: 'json',
		  success: function(data, status) {
                      var state = $("#selectCampaignStatus").val();
                            manclient(state);
    	}
	});
     }
}
function activateClient(id){
    confirm = false;
    var inactive = $.ajax({
             type: "POST",
             url: "admin.php",
             data: { id: id, deactivate: 'no' },
             dataType: 'json',
             success: function(data, status) {
                 var state = $("#selectCampaignStatus").val();
                            manclient('Active');
    }
    });   
}
function setListDeleted(id){
	var inactive = $.ajax({
		  type: "POST",
		  url: "admin.php",
		  data: { id: id, setlistDeleted: 'yes' },
		  dataType: 'json',
		  success: function(data, status) {
	       if(data.count > 0){
		  		 $(".li-"+id).hide('slow');
		  	}
    	}
	});
}
function focusbox(x){x.style.backgroundColor = "#E1E1E1";}
function outfocus(y){y.style.backgroundColor = "#FFFFFF";}
var validlistid = false;
function validate(f,table)
{
    var val = $(f).val();
    $.ajax({
        url:'admin.php?act=validate&table='+table+'&value='+val,
        success: function(resp){
            if (resp != 'okay') {
                validlistid = false;
                $(f).css("border","red 2px solid");
                $(f).attr("title",resp);
                $(f).attr("placeholder",resp);
            }
            else {
                validlistid = true;
               $(f).css("border","lightgreen 2px solid");
               $(f).attr("title","OK");
                $(f).attr("placeholder",'');
            }
        }
    });
}
function getguide(section)
{
    $.ajax({
        url:'admin.php?act=getguide&section='+section,
        success: function(resp){
            $("#pageguide_content").html(resp);
            tl.pg.init({
                auto_show_first: false,
                pg_caption: "Help"
            });
        }
    });
}
function enabledispo(dispoid, pid)
{
    $.ajax({
        url: 'admin.php?act=enabledispo&statusid='+dispoid,
        success:function(){manage_persist(pid);}
    });
}
function disabledispo(dispoid, pid)
{
    $.ajax({
        url: 'admin.php?act=disabledispo&statusid='+dispoid,
        success:function(){manage_persist(pid);}
    });
}
function lookupfields(fieldlist)
	{
		var fields = fieldlist.split(",");
		var fl = '<ul>';
		for (var i = 0;i < fields.length;i++)
			{
				fl += '<li>'+fields[i]+'</li>';
			}
		$("#flists").html(fl);
	}
function removesigimage(sigid,image,ct)
    {
        jQuery.ajax({
                    success: function(data)
                    {
                        jQuery("#div_"+ct).remove();
                    },
                    url: 'admin.php?act=emailsignatures&sub=removeimage&image='+image+'&sigid='+sigid
                 });
    }
function removeattachment(templateid, attch,ct)
	{
		jQuery.ajax({
                    success: function(data)
                    {
                        jQuery("#div_"+ct).remove();
                    },
                    url: 'admin.php?act=removeattachment&attachment='+attch+'&templateid='+templateid
                 });
	}
function saveprofile()
	{
            var semail = jQuery("#prof_email").val();
            var spass = jQuery("#prof_pass").val();
            var stz = jQuery("#prof_timezone").val();
            jQuery.ajax({
                success: function(data)
                            {
                                    jQuery("#displayport").html(data);
                                    email =semail;
                                    setTimeout("window.location=window.location",3000);
                            },
                url: 'admin.php?act=saveprofile&email='+semail+'&pass='+spass+'&timezone='+stz
                                                        });
	}
function profile(msg)
	{
		if (!msg) {
			var msg = null;
		}
		else if (msg.length > 0) 
			{
				alert(msg);
			}
		jQuery.ajax({
			success: function(data)
						{
							jQuery("#displayport").html(data);
						},
			url: 'admin.php?act=profile'
								});
	}
function deletefile(fileid,pid)
	{
		jQuery.ajax({
			success: function(data)
						{
							manage_persist(pid);
						},
			url: 'admin.php?act=deletefile&fileid='+fileid
								});
	}
function deletedispo(statusid,pid)
	{
		jQuery.ajax({
			success: function(data)
						{
							manage_persist(pid);
						},
			url: 'admin.php?act=deletedispo&statusid='+statusid
								});
	}
function changetransferpid()
{
    var pid = $("#optionsinput #transfer_pid").val();
    jQuery.ajax({
        success: function(data)
                {
                    $("#transfer_lid").html(data);
                    var ht = $("#transferlist").html();
                    $("#lid_row").remove();
                    var ithis = "<tr id='lid_row'><td class='center-title'>Transfer to List</td><td class='dataleft datas' id='listinput'>"+ht+"</td></tr>";
                    $("#options_tr").after(ithis);
                },
        url: 'admin.php?act=getlistoptions&pid='+pid
    });
}
function changetransferlid(pid)
	{
		var nt = document.getElementById('transfer_lid');
		var newtlid = nt.options[nt.selectedIndex].value;
		jQuery.ajax({
			success: function(data)
						{
						},
			url: 'admin.php?act=updatecamp&fld=transfer_list&vl='+newtlid+'&pid='+pid
								});
	}
function editclientcontact(cid)
{
    jQuery.ajax({
                url: "admin.php?act=editclientcontact&client_contactid="+cid,
                success:dresponsehandler
            });
}
function deletecontact(cid,clientid)
{
    jQuery.ajax({
                url: "admin.php?act=deletecontact&client_contactid="+cid,
                success:function(resp){
                    clientdetails(clientid);
                }
            });
}
function addcontact(cid)
	{
		var userlogin = document.getElementById('cuserlogin').value;
		var userpass = document.getElementById('cuserpass').value;
		var firstname = document.getElementById('cfirstname').value;
		var lastname = document.getElementById('clastname').value;
		var phone = document.getElementById('cphone').value;
		var email = document.getElementById('cemail').value;
		var usermode = document.getElementById('cusermode').value;
		http.open("GET", url+"?act=addcontact&clientid="+cid+"&userlogin="+userlogin+"&userpass="+userpass+"&firstname="+firstname+"&lastname="+lastname+"&phone="+phone+"&email="+email+"&cusermode="+usermode);
		http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				$("#dialogcontainer").dialog("close");
                /* ADDED BY Vincent Castro */
                alert(resp);
                                clientdetails(cid);
			}
		};
		http.send(null);
	}
function updatecontact(cid,clientid)
	{
		var userlogin = document.getElementById('cuserlogin').value;
		var userpass = document.getElementById('cuserpass').value;
		var firstname = document.getElementById('cfirstname').value;
		var lastname = document.getElementById('clastname').value;
		var phone = document.getElementById('cphone').value;
		var email = document.getElementById('cemail').value;
		var usermode = document.getElementById('cusermode').value;
                var livemonitor = $("#clivemonitor").val();
                $.ajax({
                    url: "admin.php?act=updatecontact&client_contactid="+cid+"&userlogin="+userlogin+"&userpass="+userpass+"&firstname="+firstname+"&lastname="+lastname+"&phone="+phone+"&email="+email+"&cusermode="+usermode+"&livemonitor="+livemonitor,
                    success: function(resp)
                    {
                        $("#dialogcontainer").dialog("close");
                        clientdetails(clientid);
                    }
                });
	}
function createpopup(action,id)
	{
            jQuery.ajax({
                url: "admin.php?act=newcontact&clientid="+id,
                success:dresponsehandler
            });
	}
function clearpopup(cid)
	{
		if (popup == true)
			{
			var ds = document.getElementById('popup');
			ds.parentNode.removeChild(ds);
			clientdetails(cid);
			popup = false;
			}
	}
function getdaysched(dt)
	{
		cd = document.createElement("div");
		cd.id = "daysched";
		cd.style.width = "800px";
		cd.style.position ="absolute";
		cd.style.border = "1px #CCC solid";
		cd.style.left = "200px";
		cd.style.top = "100px";
		cd.style.backgroundColor = "#fff";
		cd.style.height = "400px";
		cd.style.padding = "10px";
		cd.innerHTML = "<b> Agents scheduled for "+dt+"</b><br>";
		cd.innerHTML += "<div style=\"position:absolute; left:775px; top:10px;\"><a href=\"#\" onclick=\"cleardaysched()\">Close</a></div>";
		cd.innerHTML += '<div style="position:absolute; left:15px; top:30px;"><a href="#" onclick="getds(\''+dt+'\')">View</a> | <a href="#" onclick="addsched(\''+dt+'\')">Add Agent</a></div>';
		cd.innerHTML += '<div id=schedscreen style="background-color: #fff; padding:5px; width: 790px; height: 340px; position:absolute; top:50px; left:10px; border:1px #ffc solid;">Loading schedule. Please wait.</div>';
		document.getElementById('container').appendChild(cd);
		getds(dt);
		gds = true;
	}
function addschedule(dt)
	{
		var pr = document.getElementById('projectid');
		var ag = document.getElementById('agentid');
		var hr = document.getElementById('hour');
		var mi = document.getElementById('minutes');
		var ap = document.getElementById('ampm');
		var ehr = document.getElementById('ehour');
		var emi = document.getElementById('eminutes');
		var eap = document.getElementById('eampm');
		var projectid = pr.options[pr.selectedIndex].value;
		var agentid = ag.options[ag.selectedIndex].value;
		var hour = hr.options[hr.selectedIndex].value;
		var minutes = mi.options[mi.selectedIndex].value;
		var ampm = ap.options[ap.selectedIndex].value;
		var ehour = ehr.options[ehr.selectedIndex].value;
		var eminutes = emi.options[emi.selectedIndex].value;
		var eampm = eap.options[eap.selectedIndex].value;
	http.open("GET", url+"?act=addschedule&date="+dt+"&proj="+projectid+"&agentid="+agentid+"&hour="+hour+"&minutes="+minutes+"&ampm="+ampm+"&ehour="+ehour+"&eminutes="+eminutes+"&eampm="+eampm, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('schedscreen').innerHTML=resp;
			}
		else {
			document.getElementById('schedscreen').innerHTML = "Processing...";
			}
		};
	http.send(null);		
	}
function addsched(dt)
	{
	document.getElementById('schedscreen').innerHTML = '';
	http.open("GET", url+"?act=addsched&date="+dt, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('schedscreen').innerHTML=resp
			}
		else {
			document.getElementById('schedscreen').innerHTML = "Add new agent's schedule...";
			}
		};
	http.send(null);		
	}
function getds(dt)
	{
	var ct = ".";
	document.getElementById('schedscreen').innerHTML = '';
	http.open("GET", url+"?act=getdaysched&date="+dt, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('schedscreen').innerHTML=resp
			}
		else
			{
			document.getElementById('schedscreen').innerHTML = "Loading schedule. Please wait."+ct;
			ct +=".";
			}
		};
	http.send(null);	
	}
function cleardaysched()
	{
		if (gds == true)
			{
			var ds = document.getElementById('daysched');
			ds.parentNode.removeChild(ds);
			getapp('schedules');
			gds = false;
			}
	}
function gettimesheet()
	{
	var agent = document.getElementById('agentid');
	var agentid = agent.options[agent.selectedIndex].value;
	var start = document.getElementById('startdate').value;
	var end = document.getElementById('enddate').value;
	http.open("GET", url+"?act=gettimesheet&agentid="+agentid+"&start="+start+"&end="+end, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
		var resp = http.responseText;
		document.getElementById('repcontent').innerHTML=resp
		}};
	http.send(null);	
	}
function refreshleads(proid)
	{
	http.open("GET", url+"?act=refreshleads&projectid="+proid, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
		childmessage('Leads Refreshed');
		}};
	http.send(null);
	}
function changeclient(di, vl, cid)
	{
                var target = document.getElementById(di);
                if (di == 'notes' || di == 'recordings')
                    {
                        if (vl == 'yes') var vl2 = 'no';
                        if (vl == 'no') var vl2 = 'yes';
                        target.innerHTML = '<select type=text value="'+vl+'" onchange="submitchangesclient(this.value,\'clients\',\''+di+'\',\''+cid+'\')"><option value="'+vl+'">'+vl+'</option><option value="'+vl2+'">'+vl2+'</option></select>';
                    }
                else {
		var target = document.getElementById(di);
		target.innerHTML = '<input type=text value="'+vl+'" onblur="submitchangesclient(this.value,\'clients\',\''+di+'\',\''+cid+'\')">';
                }
	}
function changedetails(di, fild, type,id)
	{
		var target = document.getElementById(di);
		var vl = fild;
		target.innerHTML = '<input type=text value="'+vl+'" onblur="submitchangesdet(this.value,\''+type+'\',\''+di+'\',\''+id+'\')">';
	}
function submitchangesclient(vl,type,di,id)
	{
	http.open("GET", url+"?act=updateuserdet&type="+type+"&val="+vl+"&fild="+di+"&id="+id, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
		var target = document.getElementById(di);
		target.innerHTML = '<a onclick="changeclient(\''+di+'\',\''+vl+'\',\''+type+'\',\''+id+'\')">'+vl+'</a>';
		}};
	http.send(null);	
	}
function submitchangesdet(vl,type,di,id)
	{
	http.open("GET", url+"?act=updateuserdet&type="+type+"&val="+vl+"&fild="+di+"&id="+id, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
		var target = document.getElementById(di);
		target.innerHTML = '<a onclick="changedetails(\''+di+'\',\''+vl+'\',\''+type+'\',\''+id+'\')">'+vl+'</a>';
		}};
	http.send(null);	
	}
function removeprojteam(tid,proj)
	{
	$.ajax({
            url: "admin.php"+"?act=removeprojectfromteam&teamid="+tid+"&project="+proj,
            success: function(){
                getapp('manteams');
            }
        });
	}
function addprojecttoteam(tid)
	{
	pr = document.getElementById("addprojecttoteam");
	proj = pr.options[pr.selectedIndex].value;
        $.ajax({
            url:"admin.php"+"?act=addprojecttoteam&teamid="+tid+"&project="+proj,
            success:function(){
                getapp('manteams');
            }
        });
	}
function addprojteam(tid)
	{
	$.ajax({
            url:"admin.php"+"?act=getprojlist&teamid="+tid,
            success:function(resp)
                {
			$('#addprojteam'+tid).html(resp);
		}
        });
	}
function listsearch(lsearch)
	{
		var sscamp = document.getElementById('listsearchstring').value;
		http.open("GET", url+"?act=getapp&app=manlist&listsearch="+sscamp, true);
		http.onreadystatechange = handleappresponse;
		http.send(null);
	}
function campsearches(app)
	{
		var sscamp = document.getElementById('campsearch').value;
		http.open("GET", url+"?act=getapp&app="+app+"&search="+sscamp, true);
		http.onreadystatechange = handleappresponse;
		http.send(null);
	}
function pagin(pagenum, sorty, sear,app)
	{
	if (app == 'maninactive')
		{
			http.open("GET", url+"?act=getapp&app=maninactive&sort="+sorty+"&page="+pagenum+"&search="+sear, true);
		}
	else {
	http.open("GET", url+"?act=getapp&app=mancamp&sort="+sorty+"&page="+pagenum+"&search="+sear, true);
	}
	http.onreadystatechange = handleappresponse;
	http.send(null);
	}
function listpagin(pagenum, sear,app)
	{
    http.open("GET", url+"?act=getapp&app=manlist&page="+pagenum+"&listsearch="+sear, true);
	http.onreadystatechange = handleappresponse;
	http.send(null);
	}
function sortedapp(sorty)
	{
	http.open("GET", url+"?act=getapp&app=mancamp&sort="+sorty, true);
	http.onreadystatechange = handleappresponse;
	http.send(null);	
	}
function deleteproj(pid)
	{
	jQuery('<div/>', {
    id: 'dialog-confirm',
    title: 'Deactivate Campaign?',
	style: 'display:none',
	html: 'This will delete the campaign permanently and cannot be undone. Proceed?'
	}).appendTo('body');
	$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				"Deactivate": function() {
						$.ajax({
							success: function(data)
								{
									getapp('mancamp');
									$("#dialog-confirm").dialog( "close" );
								},
						url: 'admin.php?act=deleteproj&project='+pid
								});
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
function changelist(fld, lis)
	{
	if (lister == 0)
	{
	lister = 1;
	var cur = document.getElementById(fld+lis);
	var cval = cur.innerHTML;
	if (cval == 'YES') 
		{
		var rval = 1;
		var optwo = '<option value="0">NO</option>';
		}
	else 
		{
		var rval = 0;	
		var optwo = '<option value="1">YES</option>';
		}
	cur.innerHTML = '<select onblur="updatelist(\''+lis+'\',this, \'active\')"><option value="'+rval+'" selected>'+cval+'</option>'+optwo+'</select>';
	}
	}
function togglelist(lid)
{
    var val = $("#active"+lid).val();
    $.ajax({
        url: "admin.php?act=updatelist&listid="+lid+"&field=active&val="+val
    });
}
function updatelist(lid,idd,fld)
	{
	lister = 0;
	if (idd.type != 'text')
	{
	var sel = idd.selectedIndex;
	var val = idd.options[sel].value;
	}
	var data = 'act=updatelist&listid='+lid+'&field='+fld+'&val='+val;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					if (fld == 'active')
						{
							if (val == '0') val = 'NO';
							else val = 'YES';
						}
					document.getElementById(fld+lid).innerHTML=val;
					}
	};
	http.send(data);
	}
function removeteam(teamid)
	{
	senddata("?act=removeteam&tid="+teamid);
	}
function remuser(iid,tid)
	{
	var data = 'act=remuser&team='+tid+'&user='+iid;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					resp = http.responseText;
					document.getElementById('displayport').innerHTML=resp;
					}
	};
	http.send(data);
	}
var tiid = new Array();
function hideteams(iid)
	{
		document.getElementById('team'+iid).innerHTML = tiid[iid];
	}
function showteams(iid)
	{
	var data = 'act=showteams&user='+iid;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					resp = http.responseText;
					tiid[iid] = document.getElementById('team'+iid).innerHTML;
					document.getElementById('team'+iid).innerHTML=resp;
					document.getElementById('team'+iid).innerHTML+='<br><a href="#" onclick="hideteams(\''+iid+'\')">Hide Teams</a>';
					}
	};
	http.send(data);	
	}
function updateteamuser(iid)
	{
	var tu = document.getElementById('teamuserform');
	var v = tu.options[tu.selectedIndex].value;
	var data = 'act=updateteamuser&team='+v+'&user='+iid;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					resp = http.responseText;
					tiid[iid] = document.getElementById('team'+iid).innerHTML;
					document.getElementById('team'+iid).innerHTML=resp;
					document.getElementById('team'+iid).innerHTML+='<br><a href="#" onclick="hideteams(\''+iid+'\')">Hide Teams</a>';
					document.getElementById('teamadd'+iid).innerHTML='<img src="icons/add.gif"  onclick="teamaddf(\''+iid+'\')"><a href="#" onclick="teamaddf(\''+iid+'\')">Add to a Team</a>';
					}
	};
	http.send(data);
	}
function cancelteamadd(iid)
{
    document.getElementById('teamadd'+iid).innerHTML='<img src="icons/add.gif"  onclick="teamaddf(\''+iid+'\')"><a href="#" onclick="teamaddf(\''+iid+'\')">Add to a Team</a>';
}
function teamaddf(ussid)
	{
	var data = 'act=getteamlist&iid='+ussid;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					resp = http.responseText;
					document.getElementById('teamadd'+ussid).innerHTML+=resp;
					}
	};
	http.send(data);
	}
function deletefilter(filid,pid)
	{
	var data = 'act=deletefilter&filterid='+filid;
	jQuery.ajax({
			type: 'POST',
			success: function(data)
						{
							manage_persist(pid);
						},
			url: 'admin.php',
			data: 'act=deletefilter&filterid='+filid
								});
	}
function nodrop()
	{
		var data = '<input type=text name=va id=va>';
		jQuery("#vaspan").html(data);
	}
function incpace(projid)
	{
		jQuery.ajax({
			success: function(data)
						{
							if (data == 'fail')
								{
									alert("Cannot go above maximum pacing");
								}
							refreshinfo(true);
						},
			url: 'admin.php?act=incpace&pid='+projid
								});
	}
function decpace(projid)
	{
		jQuery.ajax({
			success: function(data)
						{
							if (data == 'fail')
								{
									alert("Cannot go below 0 pacing");
								}
							refreshinfo(true);
						},
			url: 'admin.php?act=decpace&pid='+projid
								});
	}
function popdispo(projid)
	{
		jQuery.ajax({
			success: function(data)
						{
							jQuery("#vaspan").html(data);
						},
			url: 'admin.php?act=popdispo&pid='+projid
								});
	}
function addfilter(projid)
	{
	var fiel = document.getElementById('field');
	var oper = document.getElementById('operand');
	var val = document.getElementById('va').value;
	var field = fiel.options[fiel.selectedIndex].value;
	var operand = oper.options[oper.selectedIndex].value;
        jQuery.ajax({
			type: 'POST',
			success: function(data)
						{
							manage_persist(projid);
						},
			url: 'admin.php',
			data: 'act=addfilter&pid='+projid+'&field='+field+'&operand='+operand+'&val='+val
								});
	}
var mancampactivesection = 'pdetailsdiv';
function manage_persist(projid)
	{
        jQuery.ajax({
           url: 'admin.php?act=managecamp&pid='+projid,
           success: function(resp){
               camploaded = true;
               $("#displayport").html(resp);
               $("#campsettingsdisplay input[type=button]").button();
               campaignMenu(mancampactivesection);
           }
        });
	nprojid = projid;
	}
function manage(projid)
	{
        jQuery.ajax({
           url: 'admin.php?act=managecamp&pid='+projid,
           success: function(resp){
               camploaded = true;
               $("#displayport").html(resp);
               $("#campsettingsdisplay input[type=button]").button();
               campaignMenu('snapshot');
           }
        });
	nprojid = projid;
	}
function hidag(thid)
	{
	document.getElementById(thid).style.visibility = 'hidden';
	cwinvar_client = 'old';
	}
function showhid(thid)
	{
	document.getElementById(thid).style.visibility = 'visible';
	cwinvar_client = 'new';
	}
function updateteams(teamid)
	{
		var project = document.getElementById('projects'+teamid);
		var newp = project.options[project.selectedIndex].value;
		http.open("GET", url+"?act=updateteam&tid="+teamid+"&project="+newp, true);
		http.onreadystatechange = function(){
				if (http.readyState == 4)
					{
					var resp = http.responseText;
					Ext.Msg.alert('Info',resp);
					}
		};
		http.send(null);
	}
function cmess(meg,prid)
	{
		alert(meg);
		if (prid)
		{
		manage_persist(prid);
		}
	}
function childmessage(meg)
	{
		alert(meg);
	}
Ext.onReady(function()
	{
	var navbar = new Ext.Toolbar({
		renderTo: 'navb',
		autoWidth: true
		});
	navbar.addButton([<?php if (checkrights('livemonitor')) { 
                        ?>new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/livemonitor.png',
							text: 'Live Monitor', clickEvent: 'click', handler: getinfo})
			,<?php }?>
			<?php if (checkrights('manage_campaign')) { 
                        ?>new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/campaigns.png',
							text: 'Campaigns', clickEvent: 'click', menu: [{text: 'Create New', handler: clickhandle, action: 'newcamp', cls: 'x-btn-text-icon', icon:'icons/newcampaigns.png'},{text: 'Campaigns Settings', cls: 'x-btn-text-icon', icon:'icons/activecampaigns.png', handler: clickhandle, action: 'mancamp'}]})
			,<?}?>
			<?php if (checkrights('manage_list')) { 
                        ?>new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/lists.png',
							text: 'Lists',  
							clickEvent: 'click', 
							menu: [
						{cls:'x-btn-text-icon', icon: 'icons/listsnew.png', text: 'New List', handler: clickhandle, action: 'newlist'},{cls:'x-btn-text-icon', icon: 'icons/listsnew.png', text: 'New Exclusion List', handler: clickhandle, action: 'newexclusionlist'},<? /*{cls:'x-btn-text-icon', icon: 'icons/listsnew.png', text: 'CSV Update', handler: clickhandle, action: 'listupdatefile'},
						{cls:'x-btn-text-icon', icon: 'icons/listsmanage.png', text: 'Manage Exclusion List', handler: clickhandle, action: 'manexcl'},*/ ?>
						{cls:'x-btn-text-icon', icon: 'icons/listsmanage.png', text: 'Manage Lists', handler: clickhandle, action: 'manlist'}
						]}),<?}?>
			<?php if (checkrights('manage_users')) { 
                        ?>new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/users.png',
							text: 'Users',  clickEvent: 'click', menu: [
									{cls:'x-btn-text-icon', icon: 'icons/nusers.png', text: 'New User', handler: clickhandle, action: 'newusers'},
									{cls:'x-btn-text-icon', icon: 'icons/musers.png', text: 'Manage Users', handler: clickhandle, action: 'managents'}]}),
                                                                    <?php
                                                                    }
      if ($features->voip && checkrights('manage_campaign'))
      {
                                                                    ?>
                          new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/voip_alt.png',
							text: 'VOICE',  clickEvent: 'click', menu: [
									{cls:'x-btn-text-icon', icon: 'icons/voip_add.png',text: 'Add Voice provider', handler: clickhandle, action: 'newclient'},
									{cls:'x-btn-text-icon', icon: 'icons/voip_alt.png',text: 'Manage Voice Providers', handler: clickhandle, action: 'manclient'}]}),
                                                                    <?}?>
			<?php if (checkrights('manage_client')) { 
                        ?>
			new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/clients2.png',
							text: 'Clients',  clickEvent: 'click', menu: [
									{cls:'x-btn-text-icon', icon: 'icons/clientadd.png',text: 'New Client', handler: clickhandle, action: 'newclient'},
									{cls:'x-btn-text-icon', icon: 'icons/clientmanage.png',text: 'Manage Client', handler: function() {manclient('Active');}}]}),
			<?php } if (checkrights('reports')) { 
                        ?>
			new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/reports.png',
							text: 'Reports',  
							clickEvent: 'click', 
							menu: [
							{cls:'x-btn-text-icon', icon: 'icons/apreport.png',text: 'Agent Performance Report', handler: getreport, reporttype: 'agentperformance'},
							{cls:'x-btn-text-icon', icon: 'icons/cpreport.png',text: 'Campaign Performance Report', handler: getreport, reporttype: 'campperformance'},
							{cls:'x-btn-text-icon', icon: 'icons/cdreport.png',text: 'Call Data Report', handler: getreport, reporttype: 'calldata'}
							]}),<?}?>
			<?php if (checkrights('chat')) { 
                        ?>
			new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/chat.png',
							text: 'Chat',  clickEvent: 'click', menu: [
									{cls:'x-btn-text-icon', icon: 'icons/onlineusers.png', text: 'Chat', handler: converse},
									{cls:'x-btn-text-icon', icon: 'icons/chatlogs.png', text: 'Chat Logs', handler: clickhandle, action: 'chatlog'}]}),<?}?>
			<?php if (checkrights('qa_portal')) { 
                        ?>                                      
			new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/qaportal.png', text: 'QA Portal', clickEvent: 'click', handler: clickhandle, action: 'qaport'}),
                        <?php
                        }
                        if (checkrights('vip_portal'))
{
?>
                        new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/application_key.png', text: 'VIP Portal', clickEvent: 'click', handler: gotovip, action: 'gotovip'}),
                       <?php 
}
                       ?>
                        new Ext.Toolbar.MenuButton({cls:'x-btn-text-icon', icon: 'icons/logout.png', text: 'Logout',  handler: logout, action: 'logout'})]);	
});
function reporthandle(linker)
	{
	var appname = linker.action;
	http.open("GET", url+"?act=getapp&app=reports&sec="+appname, true);
	http.onreadystatechange = handleappresponse;
	http.send(null);
	}
function logout(chuchu)
	{
	document.location="../index.php?act=logout";
	}
function clickhandle(linker)
	{
        if (linker.action == 'support')
		{
			if (email == '' || email.length < 1)
				{
					profile("Please Update your email.");
				}
			else {
			window.open("../support/login.php?lemail="+email+"&lticket="+support, "QA PORTAL","scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no, width=1200, height=600");
			}
		}
	else if (linker.action == 'qaport')
		{
			window.open("qaver/qa.php", "QA PORTAL","scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no");
		}
        else if (linker.action == 'newusers')
            {
                dialogwindow(linker.action);
            }
        else if (linker.action == 'newcamp')
            {
                dialogwindow(linker.action);
            }
        else if (linker.action == 'newlist')
            {
                dialogwindow(linker.action);
            }
         else if (linker.action == 'newclient')
            {
                dialogwindow(linker.action);
            }
        else if (linker.action == 'newexclusionlist')
            {
                dialogwindow(linker.action);
            }
        else if (linker.action == 'listupdatefile')
            {
                dialogwindow(linker.action);
            }
          else if (linker.action == 'manexcl')
            {
                dialogwindow(linker.action);
            }
         else if (linker.action == 'managents')
            {
                $.ajax({
                    url:'admin.php?act=getapp&app=managents&sub=',
                    type:'GET',
                    global: false,
                    success: function(resp)
                    {
                        jQuery("#displayport").html(resp);
                        getguide();
                        $(".datatabs").dataTable({
                            'iDisplayLength':20
                        });
                        $(".jbut").button();
                    }
                })
            }
	else getapp(linker.action);
	}
function barge(exten)
	{
		Ext.Msg.alert("info",exten)
	}
function listMenu(action)
{
    $(".activeMenu").each(function(){
		$(this).removeClass('activeMenu');
	});
    $("#"+action).addClass('activeMenu');
    if (action == 'managelist')
        {
            getapp('manlist');
        }
    if (action == 'manageexclusion')
        {
            $.ajax({
                    url:'admin.php?act=getapp&app=manexcl',
                    type:'GET',
                    global: false,
                    success: function(resp)
                    {
                        jQuery("#managelistresult").html(resp);
                        getguide();
                        $(".datatabs").dataTable({
                            'iDisplayLength':20
                        });
                        $(".jbut").button();
                    }
                })
        }
    if (action == 'dispoupdate')
    {
        $.ajax({
                    url:'admin.php?act=getapp&app=dispoupdate',
                    type:'GET',
                    global: false,
                    success: function(resp)
                    {
                        jQuery("#managelistresult").html(resp);
                        getguide();
                        $(".datatabs").dataTable({
                            'iDisplayLength':20
                        });
                        $(".jbut").button();
                    }
                })
    }
   if (action === 'csvupdate')
       {
           dialogwindow('listupdatefile');
       }
}
function getHTTPObject() {
  var xmlhttp =false;
  if (window.XMLHttpRequest){
    xmlhttp = new XMLHttpRequest();
  }
  else if (window.ActiveXObject){
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    if (!xmlhttp){
        xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
}
  return xmlhttp;
}
var mce = '';
function updatesignature(sigid)
{
    var texts = mce.getContent();
    $("#signature_body").val(texts);
    var dt = $("#emailsigform").serialize();
    $.ajax({
        url: 'admin.php?act=emailsignatures&sub=updatesignature&sigid='+sigid,
        type: 'POST',
        data: dt,
        success: alert
    });
}
function updatetemplate(templateid,test)
	{
		var texts = mce.getContent();
		texts = encodeURI(texts);
		texts = encodeURIComponent(texts);
		var emailfrom = document.getElementById('emailfrom').value;
                var emailfromname = document.getElementById('emailfromname').value;
		var template_disposend = jQuery("#template_disposend").val();
		var template_subject = document.getElementById('template_subject').value;
		var template_name = document.getElementById('template_name').value;
		var mailserver = document.getElementById('mailserver').value;
		var mailcc = jQuery("#emailcc").val();
                var mailbcc = jQuery("#emailbcc").val();
		var mailencryption = jQuery("#mailencryption").val();
		var mailport = document.getElementById('mailport').value;
		var mailuser = document.getElementById('mailuser').value;
		var mailpass = document.getElementById('mailpass').value;
                var mailto = $("#testmailto").val();
                var editable = $("#editable").val();
                var sigid = $("#sigid").val();
		var data = 'act=updatetemplate&templateid='+templateid+'&emailfrom='+emailfrom+'&emailfromname='+emailfromname+'&mailserver='+mailserver+'&mailencryption='+mailencryption+'&mailport='+mailport+'&mailuser='+mailuser+'&mailpass='+mailpass+'&template_subject='+template_subject+'&template_name='+template_name+'&tex='+texts+'&disposend='+template_disposend+'&emailcc='+mailcc+'&emailbcc='+mailbcc+'&test='+test+'&mailto='+mailto+'&editable='+editable+"&sigid="+sigid;
			jQuery.ajax({
			type: 'POST',
			success: function(data)
                                {
                                    if (test == false)
                                        {
                                        alert("Template Updated!");
                                        }
                                    else {
                                        var sdata = 'to='+mailto+'&from='+emailfrom+'&subject='+template_subject+'&message='+texts;
                                        $.ajax({
                                            url: '../interface/emailer.php?act=sendemail&tid='+templateid+'&uid=<?=$_SESSION['auth'];?>',
                                            type: 'POST',
                                            data: sdata,
                                            success: function(resp){alert(resp)}
                                        });
                                    }
                                },
			url: 'admin.php',
			data: data
								});
	}
function testmail()
{
    $("#testmail").dialog();
}
function updatescript(scriptid)
	{
		var texts = mce.getContent();
		texts = encodeURI(texts);
		texts = encodeURIComponent(texts);
		var data = 'act=updatescript&scriptid='+scriptid+'&tex='+texts;
                jQuery.ajax({
                    url: 'admin.php',
                    data: data,
                    type: "POST",
                    success: function(resp){
                        editscriptid(scriptid);
                        alert('Script Updated');
                    }
                });
	}
function emailtemplate(pid, templateid)
	{
                camploaded = false;
		var params = '?act=emailtemplates&templateid='+templateid+'&pid='+pid;
		if (templateid == 0)
			{
			var params = '?act=emailtemplates&act2=createnew&pid='+pid;	
			}
                 $.ajax({
                     url: "admin.php"+params,
                     success:function(resp){
			document.getElementById('campsettingsdisplay').innerHTML=resp;
			mce = mcerender('template_body');
			mce.render();
			getguide('email')
			}
                 });
	}
function emailsig(pid, sigid)
	{
                camploaded = false;
		var params = '?act=emailsignatures&pid='+pid+'&sigid='+sigid;
		if (sigid == 0)
			{
			var params = '?act=emailsignatures&pid='+pid+'&sub=createnew';	
			}
                 $.ajax({
                     url: "admin.php"+params,
                     success:function(resp){
			document.getElementById('campsettingsdisplay').innerHTML=resp;
                        $(".mceListBoxMenu").remove();
			mce = mcerender('signature_body');
			mce.render();
			getguide('emailsig')
			}
                 });
	}
function editscript(pid)
	{
		var params = '?act=editscript&pid='+pid;
                jQuery.ajax({
                    url:'admin.php'+params,
                    success: function(resp){
                        $('#campsettingsdisplay').html(resp);
                        $(".mceListBoxMenu").remove();
			mce = mcerender('scr');
			mce.render();
                        setTimeout("doeditable()",4000);
                    }
                });
	}
function changeoption(field,ob)
{
	if (ccon == 0)
	{
	ccon = 1;
	if (field == 'active')
		{
			var vl = document.getElementById(field+ob).innerHTML;
			if (vl == 'active') var vl2 = 1;
			if (vl == 'inactive') var vl2 = 0;
			document.getElementById(field+ob).innerHTML='<select class=sel onblur=update(\''+field+'\',\''+ob+'\',this.options[this.selectedIndex].value)><option value='+vl2+'selected>'+vl+'</option><option value="0">Inactive</option><option value="1">Active</option></select>';
		}
	else if (field == 'projectdesc')
		{
		var desc = document.getElementById(field+ob);
		var tt = desc.innerHTML;
		desc.innerHTML = '<textarea cols="30" rows="3" onblur=update(\'projectdesc\',\''+ob+'\',this.value)>'+tt+'</textarea>';
		}
        else if (field == 'description')
		{
		var desc = document.getElementById(field+ob);
		var tt = desc.title;
		desc.innerHTML = '<textarea cols="50" rows="3" onblur=update(\'description\',\''+ob+'\',this.value)>'+tt+'</textarea>';
		}
	else if (field == 'amd')
		{
			var am = document.getElementById(field+ob);
			var tt = am.innerHTML;
			am.innerHTML = '<select class=sel onblur=update(\'amd\',\''+ob+'\',this.options[this.selectedIndex].value)><option value="1">Yes</option><option value="0">No</option></select>';
		}
	else if (field == 'recording')
		{
			var am = document.getElementById(field+ob);
			var tt = am.innerHTML;
			am.innerHTML = '<select class=sel onblur=update(\'recording\',\''+ob+'\',this.options[this.selectedIndex].value)><option value="1">Active</option><option value="0">Inactive</option></select>';
		}
	else if (field == 'dialmode')
		{
			var vl = document.getElementById(field+ob).innerHTML;
			document.getElementById('dialmode'+ob).innerHTML='<select class=sel onblur=update(\'dialmode\',\''+ob+'\',this.options[this.selectedIndex].value)><option selected>'+vl+'</option><option>progressive</option><option>predictive</option></select>';
		}
	else if (field == 'dialpace')
		{
			var vl = document.getElementById(field+ob).innerHTML;
			document.getElementById(field+ob).innerHTML='<select class=sel onblur=update(\'dialpace\',\''+ob+'\',this.options[this.selectedIndex].value)><option selected>'+vl+'</option><option>1</option><option>2</option><option>3</option><option >4</option></select>';
		}
        else if (field == 'dropguard')
                {
                        var vl = document.getElementById(field+ob).innerHTML;
                        var vl2 = 'active';
                        if (vl == 'active') vl2 = 'inactive';
			document.getElementById(field+ob).innerHTML='<select class=sel onblur=update(\'dropguard\',\''+ob+'\',this.options[this.selectedIndex].value)><option selected value="'+vl+'">'+vl+'</option><option value="'+vl2+'">'+vl2+'</option></select>'
                }
        else if (field == 'callrecording')
                {
                        var vl = document.getElementById(field+ob).innerHTML;
                        document.getElementById(field+ob).innerHTML='<select class=sel onblur=update(\'callrecording\',\''+ob+'\',this.options[this.selectedIndex].value)><option selected value="'+vl+'">'+vl+'</option><option value="'+vl2+'">'+vl2+'</option></select>'
                }
	else if (field == 'prefix')
		{
			var vl = document.getElementById(field+ob).innerHTML;
			document.getElementById(field+ob).innerHTML='<input type=text class=sel onblur=update(\'prefix\',\''+ob+'\',this.value) value='+vl+'>';
		}
	else if (field == 'recycle')
		{
			var vl = document.getElementById(field+ob).innerHTML;
			document.getElementById(field+ob).innerHTML='<input type=text class=sel onblur=update(\'recycle\',\''+ob+'\',this.value) value='+vl+'>';
		}
	else 
		{
			var vl = document.getElementById(field+ob).innerHTML;
			document.getElementById(field+ob).innerHTML='<input type=text class=sel onblur=update(\''+field+'\',\''+ob+'\',this.value) value='+vl+'>';
		}
	document.getElementById(field+ob).childNodes[0].focus();
	}
}
function update(field,ob,vl)
{
	ccon = 0;
	document.getElementById(field+ob).innerHTML=vl;
	if (field == 'active' || field == 'recording') 
		{
			if (vl == 1) var actual = 'Active';
			else var actual = 'Inactive';
		}
	else if (field == 'amd')
		{
			if (vl == 1) var actual = 'Yes';
			else var actual = 'No';
		}
        else if (field == 'description')
            {
                var actual = vl.substr(0,51);
                $("#"+field+ob).attr("title",vl);
            }
	else var actual = vl;
	document.getElementById(field+ob).innerHTML=actual;
	updatecamp(ob,field,vl); 
}
function mc_update(field,projectid)
{
    var value = $("#"+field+projectid).val();
    $.ajax({
        url: "admin.php?act=updatecamp&pid="+projectid+"&fld="+field+"&vl="+value
    });
}
var http = getHTTPObject();
var url = "admin.php";
var ccon = 0;
var appwindow = "";
function lgt(uid)
{
    $.ajax({
        url: 'live.php?act=disc&id='+uid,
        success: function(){
            refreshinfo(true);
        }
    });
}
function getsnapshot(pid,view)
{
    $.ajax({
            url: 'snapshot.php?projectid='+pid+'&view='+view,
            success: function(resp){
                $('#campsettingsdisplay').html(resp);
                getguide('snapshot');
                $(".datepick").datepicker({ dateFormat: 'yy-mm-dd' });
                $(".jbut").button();
            }
        });
}
function getsnapshot_drop()
{
    var pid = $("#snapprojectid").val();
    var view = $("#snapview").val();
    var range_start = $("#range_start").val();
    var range_end = $("#range_end").val();
    $.ajax({
            url: 'snapshot.php?projectid='+pid+'&view='+view+'&range_start='+range_start+"&range_end="+range_end,
            success: function(resp){
                //$("#displayport").html(resp);
                 $('#campsettingsdisplay').html(resp);
                getguide('snapshot');
                $(".datepick").datepicker({ dateFormat: 'yy-mm-dd' });
                $(".jbut").button();
            }
        });
}
function getinfo()
	{
        $.ajax({
            url: 'live.php',
            success: function(resp){
                $("#displayport").html(resp);
                getguide('live');
                $("#liveMonitor").dataTable({
                    "iDisplayStart":0,
                    "iDisplayLength":8,
                    "aLengthMenu": [[5, 8, 10], [5,8,10]]
                });
                $("#liveusers table").dataTable();
                dynawidth();
                setTimeout("refreshinfo()",5000);
            }
        });
	}
function dynawidth()
{
    var curwidth = $( window ).width();
        if (curwidth < 1280)
            {
              $("#activecamp").css("width","98%");
              $("#liveusers").css("width","98%");
            }
       else {
            $("#activecamp").css("width","63%");
              $("#liveusers").css("width","35%");
       }
}
function monitor(tpid)
	{
	senddata('?act=monitor&pid='+tpid);
	}
function getcallhistory(agentid)
	{
	var params = '?act=getcallhistory&agentid='+agentid;
        $.ajax({
            url: url+params,
            success: function(resp) {
                var agentdisp = resp;
                document.getElementById('agentdisplay').innerHTML=agentdisp;
                var edate = $(".tolocaldate");
                edate.each(function(){
                    var v = $(this).html();
                    var d = epochtoutc(v);
                    var dt = d.toLocaleString();
                    $(this).html(dt);
                });
            }
        });
	}
function epochtoutc(epoch)
{
    var utcSeconds = epoch;
    var d = new Date(0);
    d.setUTCSeconds(utcSeconds);
    return d;
}
function getpage(acct,un)
	{
	var params = '?act='+acct+un;
	senddata(params);
	}
function clientdetails(clientid)
	{
	var params = '?act=clientdetails&clientid='+clientid;
	$.ajax({
            url: 'admin.php'+params,
            success: function(resp)
            {
                $("#displayport").html(resp);
            }
        });
	}
function getclientdetails(userid)
	{
	var params = '?act=getclient&userid='+userid;
	senddata(params);
	}
function getagentdetails(userid)
	{
	var params = '?act=getagent&userid='+userid;
	senddata(params);
	}
function senddata(params,responsehandler)
	{
	if (!responsehandler)
            {
             var responsehandler = handleappresponse;   
            }
        jQuery.ajax({
           url: 'admin.php'+params,
           success: responsehandler
        });
        }
function createnewteam()
	{
	var team = document.getElementById('teamname').value;
	var params = '?act=createnewteam&teamname='+team;
	senddata(params);
	}
function createnewclient(tisid)
	{
	var company = document.getElementById('company').value;
	var address1 = document.getElementById('address1').value;
	var address2 = document.getElementById('address2').value;
	var city = document.getElementById('city').value;
	var state  = document.getElementById('state').value;
	var companyurl = document.getElementById('companyurl').value;
	var email = document.getElementById('email').value;
	var phone = document.getElementById('phone').value;
	var altphone = document.getElementById('altphone').value;
	var params = '?act=createnewclient&company='+company+'&address1='+address1+'&address2='+address2+'&city='+city+'&state='+state+'&companyurl='+companyurl+'&email='+email+'&phone='+phone+'&altphone='+altphone;
        $.ajax({
            url: "admin.php"+params,
            success: function(resp){
                var newcid = resp;
                clientdetails(newcid);
                $("#dialogcontainer").dialog("close");
            }
        });
	}
function createnewuser(tisid)
	{
	var afname = document.getElementById('afname').value;
	var alname = document.getElementById('alname').value;
	var userlogin = document.getElementById('userlogin').value;
	var userpass = document.getElementById('userpass').value;
	var roleid = $("#roleid").val();
	var usertype  = 'user';
	var params = '?act=createnewuser&roleid='+roleid+'&afname='+afname+'&alname='+alname+'&userlogin='+userlogin+'&userpass='+userpass+'&usertype='+usertype;
	senddata(params, function(data){
            jQuery("#respmessage").html(data);
            jQuery(".entryform form")[0].reset();
        });
	}
function createnewproject(tisid)
	{
	var projname = $('#projectname').val();
        var providerid = $("#providerid_nc").val();
	var projdesc = '';
	var dialmode = $('#dialmode').val();
	var dialpace = '1';
        var clone = $( "input:radio[name=clone]:checked" ).val();
	var clientid =$('#clientid').val();
        var cloneproj = $("#campaignSelect").val();
	var params = "?act=createnewproject&projname="+projname+"&projdesc="+projdesc+"&dialmode="+dialmode+"&dialpace="+dialpace+"&clientid="+clientid+"&providerid="+providerid+"&clone="+clone+"&cloneproj="+cloneproj;
	senddata(params, function(data){
            if (data == 'checkname')
               {
                   $("#respmessage").html("Campaign name already Exists!");
                   $("#respmessage").css("color","red");
               }
            else {
                manage_persist(data);
                $('#dialogcontainer').dialog('close');
            }
        });
	}
function getreport(a)
	{
	window.open("reports/"+a.reporttype+".php", a.reporttype,"scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no");
	}
function getapp(appname)
{
	jQuery.ajax({
		url: url+"?act=getapp&app="+appname,
		success: function(resp){
			jQuery("#displayport").html(resp);
                        getguide();
			loadonajax();
			}
		});
}
function manclient(state)
{
    var sel = '';
    if (state == 'Inactive')
        {
            sel = 'selected="selected"';
        }
    jQuery.ajax({
		url: url+"?act=getapp&app=manclient",
		success: function(resp){
			jQuery("#displayport").html(resp);
                        getguide();
			loadonajax();
                },
                complete: function() {
                        var aclient = $("#adminClientList").dataTable({
                            "iDisplayLength": 20
                        });
                        $(".dataTables_filter").append("<br>Status: <select id=\"selectCampaignStatus\"><option value=\"Active\">Active</option><option value=\"Inactive\" "+sel+">Inactive</option><option value=\"\">All</option></select>");
                        aclient.fnFilter( state, 1, false, false, false, false );
                        $("#selectCampaignStatus").change(function(){
                                var selectVal = $(this).val();
                            aclient.fnFilter( selectVal, 1, false, false, false, false );
                        });
			}
		});
}
function updatecamp(projid,field,value)
{
	http.open("GET", url+"?act=updatecamp&pid="+projid+"&fld="+field+"&vl="+value, true);
	http.onreadystatechange = function() {};
	http.send(null);
}
confirm = false;
function deleteuser(aid)
{	
        if (!confirm)
            {
                confirmbox("deleteuser("+aid+")","Are you sure?");
            }
        else {
        confirm = false;
        $.ajax({
            url: "admin.php?act=deleteuser&uid="+aid,
            success: function(){
                getapp('managents');
            }
        });
        }
}
function confirmbox(fn, message)
{
    $("#dialogcontainer").html('<div>'+message + '</div><br /><div><a href="#" onclick="continueconfirm(\''+fn+'\')" class="jbut">Continue</a>' + 
        '<a href="#" onclick="closeconfirm()" class="jbut">Cancel</a></div>');
    $("#dialogcontainer .jbut").button();
    $("#dialogcontainer").dialog({
        height:200
    });
}
function continueconfirm(fn)
{
    closeconfirm();
    confirm = true;
    eval(fn);
}
function closeconfirm()
{
    $("#dialogcontainer").dialog("destroy");
}
function passreset(aid)
	{
	var newpass = document.getElementById('newpass').value;
	if (newpass.length > 3)
	{
	http.open("GET", url+"?act=passreset&uid="+aid+"&pass="+newpass, true);
	http.onreadystatechange = function() {
		if (http.readyState ==4)
			{
			var resp = http.responseText;
			Ext.Msg.alert("Info",resp);
			}
	};
	http.send(null);
	}
	else {
		Ext.Msg.alert('Error!','Passwords should be atleast 4 characters');
	}
	}
function handleappresponse(resp)
{
    var disp = resp;
    document.getElementById('displayport').innerHTML=resp;
}
var navpanel;
var navct = 0;
var navind = 0;
var button = new Array();
function navhistory(clickbutton,clickfunction)
	{
	navct = navct + 1;
	var chandle = function()
		{
				getapp(clickfunction);
		}
	if (navct > 8)
		{
			navind = navct - 8;
			Ext.getCmp('b'+navind).destroy();
		}
	button[navct] = new Ext.Button({
		text: clickbutton,
		handler: chandle,
		clickevent: 'click',
		id: 'b'+navct
		});
	navpanel.add([button[navct]]);
	navpanel.show();
	}
function dograph(gdata) 
{
	  $.jqplot.config.enablePlugins = true;
    var line1 = [gdata];
    plot1 = $.jqplot('chart2', [line1], {
        title:'Call Count Summary',
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer, 
                tickInterval:'1 day',
				min: '<?=$min?>',
                rendererOptions:{
                    tickRenderer:$.jqplot.CanvasAxisTickRenderer},
                tickOptions:{formatString:'%b %#d, %Y', fontSize:'8pt', fontFamily:'Tahoma', angle:-40, fontWeight:'normal', fontStretch:1}
            },
			yaxis:{
				min:0
			}
        },
		seriesDefaults: {
    		color: '#F90',
    		renderer: $.jqplot.BarRenderer,
    		shadow: false,
			barWidth:10
    	}
		});
}
function searchlead()
{
	var ss = document.getElementById('searchstring').value;
	http.open("GET", "admin.php?act=searchlead&searchstring="+ss, true);
		http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('searchresult').innerHTML=resp
			}
		};
	    http.send(null);
}
function editlead(leadid,table)
{
	http.open("GET", "admin.php?act=editlead&table="+table+"&leadid="+leadid, true);
		http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('searchresult').innerHTML=resp
			}
		};
	    http.send(null);
}
function updatelead(leadid,field,value,table)
{
	http.open("GET", "admin.php?act=updatelead&table="+table+"&leadid="+leadid+"&field="+field+"&value="+value, true);
		http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById(field+"ind").innerHTML='<img src="images/check.gif">';
			}
		};
	    http.send(null);
}
var campwindow = new Array();
function freecamp(i)
{
	campwindow[i.pids] = 'close';
}
function livedetail(pid, campaign, anim)
{
	if (campwindow[pid] != 'open')
	{
	campwindow[pid] = 'open';
	var win = new Ext.Window({
            title    : 'Data Status for '+campaign,
			pids	 : pid,
			style	: "background-color:#ffffff",
			minimizable: true,
            closable : true,
            width    : 315,
            height   : 218,
            border : false,
			frame	:true,
            layout   : 'fit',
			renderTo : 'container',
			lurl: 'livedetail.php?projectid='+pid,
			buttons  : [{
				text : 'update',
				handler : function() {win.load(win.lurl)}
				}],
			listeners :
				{
					close: freecamp
				},
           autoLoad : {
			   url: 'livedetail.php?projectid='+pid
		   }
        });
        win.show(anim.id);
	}
}
function attachcomplete(fi)
{
	var atts = document.getElementById('atts');
	atts.innerHTML = atts.innerHTML + '<a href="../attachments/'+fi+'">'+fi+'</a><br />';
}
function addnewcstat()
{
	document.getElementById('addrow').style.display="";
}
function addcstat(cid)
{
	var statusname= document.getElementById('crmstatusname').value;
	var statustype= document.getElementById('crmstatustype').value;
	var statusaction= document.getElementById('crmstatusaction').value;
	Ext.Ajax.request({
   url: 'admin.php',
   success: function (){
	   clientdetails(cid)
	   },
   params: { act: 'addnewcstat', status_name: statusname, status_type: statustype, status_action: statusaction, status_clientid: cid  }
});
}
function crmstat(action,stid,ci)
{
	Ext.Ajax.request({
   url: 'admin.php',
   success: function (){
	   clientdetails(ci)
	   },
   params: { act: 'crmstat', act2: action, statusid : stid  }
});
}
var uniclick = 0;
function unichanges_edit(table,field,target,id,ci)
{
	if (uniclick == 0)
	{
	uniclick = 1;
	var vl =target.innerHTML;
	target.innerHTML = '<input type="text" class="sel" onblur="unichanges_submit(this,\''+table+'\',\''+field+'\',\''+target+'\',\''+id+'\',\''+ci+'\')" value="'+vl+'">';
	}
}
function unichanges_submit(ob,tablef,fieldf,targetf,ident,ci)
{
	var val = ob.value;
	Ext.Ajax.request({
   url: 'admin.php',
   success: function (){
	   uniclick = 0;
	   clientdetails(ci)
	   },
   params: { act: 'unichange', table: tablef, field : fieldf, target: targetf, id: ident, value: val  }
});
}
function canceltemplate(templateid, projectid)
{
    $.ajax({
        url: 'admin.php?act=emailtemplates&act2=canceltemplate&tid='+templateid,
        success: function(){
            manage_persist(projectid);
        }
    });
}
function cancelsignature(sigid,projectid)
{
    $.ajax({
        url: 'admin.php?act=emailsignatures&sub=cancelsignature&sigid='+sigid,
        success: function(){
            manage_persist(projectid);
        }
    });
}
function loadonajax()
{
	jQuery(".datatabs").dataTable({
            'iDisplayLength':20
        });
        jQuery(".jbut").button();
}
function dodialog(win)
{
	jQuery("#"+win).dialog();
}
function addscriptdialog()
{
	jQuery("#addscriptpage").dialog();
}
function editscriptid(scriptid)
{
	jQuery.ajax({
		url: 'admin.php?act=editscriptid&scriptid='+scriptid,
		success: function(resp){
                        $(".mceListBoxMenu").remove();
			document.getElementById('campsettingsdisplay').innerHTML=resp;
			mce = mcerender('scr'); 
			mce.render();
			}
		});
}
function addscriptpage(pid)
{
	var scriptname = jQuery("#scriptnameid").val();
        var parentid = jQuery("#addscriptpage #parentid").val();
	jQuery.ajax({
		url: 'admin.php?act=addscriptpage&parentid='+parentid+'&pid='+pid+'&scriptname='+scriptname,
                type: "POST",
                data: $("#requiredfields :input").serialize(),
		success: function(resp){
			document.getElementById('campsettingsdisplay').innerHTML=resp;
			mce = mcerender('scr'); 
			mce.render();
			jQuery("#dialogcontainer").dialog('close');
			jQuery("#scriptnameid").val('');
			}
		});
}
function mcerender(targetid)
{
	var mce = new tinymce.Editor(targetid,{
					mode: 'textareas', 
					theme: 'advanced',
                                        plugins: 'preview',
					theme_advanced_buttons1_add_before : "preview,separator",
			theme_advanced_buttons1_add : "fontselect,fontsizeselect",
			theme_advanced_buttons2_add : "separator,forecolor,backcolor,liststyle",
			theme_advanced_buttons2_add_before: "cut,copy,separator,",
			theme_advanced_buttons3_add_before : "tablecontrols,fullscreen",
			theme_advanced_buttons3_add : "media",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			extended_valid_elements : "hr[class|width|size|noshade]",
			file_browser_callback : "ajaxfilemanager",
			paste_use_dialog : false,
			theme_advanced_resizing : true,
			theme_advanced_resize_horizontal : true,
			apply_source_formatting : true,
			force_br_newlines : true,
			force_p_newlines : false,
                        remove_script_host: false,
			relative_urls : false,
                        content_css : "styles/style.css"
					});
	return mce;
}
/*
 *Shortcut function to create dialogwindows
 *use this as success callback in ajax functions
 *resp must container a <title> tag
 */
function dresponsehandler(data)
{
            $("#dialogcontainer").html(data);
            var n=data.match(/.*?width.*?(\d+)/im);
            var h=data.match(/.*?height.*?(\d+)/im);
            var stitle=data.match(/.*?title>([\S\s]*?)<.title>/im);
            if (stitle)
                {
                    dc = '';
                    atitle = stitle[1];
                }
            else {
                var dc = 'noTitle';
                atitle = '';
            }
            if (h)
                {
                    var hi = parseInt(h[1]) + 41;
                }
            else {
                var hi = 200;
            }
            var wi = parseInt(n[1]) + 35;
            $("#dialogcontainer").dialog({
                width: wi,
                height: hi,
                resizable: false,
                title: atitle,
                dialogClass: dc,
                show: 500,
                close: function(){
                    toclear = 0;
                }
            });
            $("#dialogcontainer")[0].style.height='auto';
            $("#dialogcontainer")[0].style.width='auto';
        }
function dialogwindow(action)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app="+action,
        success: dresponsehandler 
    });
}
function scriptpage(pid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=scriptpage&pid="+pid,
        success: dresponsehandler 
    });
}
function newcobj(projid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=newcobj&pid="+projid,
        success: dresponsehandler 
    });
}
function newactiontag(projid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=newactiontag&pid="+projid,
        success: dresponsehandler 
    });
}
function editcobj(id)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=editcobj&id="+id,
        success: dresponsehandler 
    });
}
function deletecobj(id)
{
    jQuery.ajax({
        url: "admin.php?act=deletecobj&id="+id,
        success: function(){
            campaignMenu('objectives');
        }
    });
}
function deleteactiontag(id)
{
    jQuery.ajax({
        url: "admin.php?act=deleteactiontag&id="+id,
        success: function(){
            campaignMenu('actiontags');
        }
    });
}
function addcobj(projid)
{
   var dispo = $("#objs #disposition").val();
   var type = $("#objs #type").val();
   var period = $("#objs #period").val();
   var target = $("#objs #target").val();
    jQuery.ajax({
        url: "admin.php?act=addcobj&pid="+projid+"&disposition="+dispo+"&type="+type+"&period="+period+"&target="+target,
        success: function(){
            campaignMenu('objectives');
        }
   });
}
function addactiontag(projid)
{
   var actionevent = $("#actionevent").val();
   var reasonname = $("#reason_name").val();
    jQuery.ajax({
        url: "admin.php?act=addactiontag&pid="+projid+"&actionevent="+actionevent+"&reasonname="+reasonname,
        success: function(){
            campaignMenu('actiontags');
        }
   });
}
function updatecobj(id)
{
   var dispo = $("#objs #disposition").val();
   var type = $("#objs #type").val();
   var period = $("#objs #period").val();
   var target = $("#objs #target").val();
    jQuery.ajax({
        url: "admin.php?act=updatecobj&id="+id+"&disposition="+dispo+"&type="+type+"&period="+period+"&target="+target,
        success: function(){campaignMenu('objectives')}
   });
}
function updatetobj(id)
{
   var dispo = $("#objs #disposition").val();
   var teamid= $("#objs #teamid").val();
   var period = $("#objs #period").val();
   var target = $("#objs #target").val();
    jQuery.ajax({
        url: "admin.php?act=updatetobj&id="+id+"&disposition="+dispo+"&teamid="+teamid+"&period="+period+"&target="+target,
        success: function(){campaignMenu('objectives')}
   });
}
function edittobj(id)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=edittobj&id="+id,
        success: dresponsehandler 
    });
}
function deletetobj(id)
{
    jQuery.ajax({
        url: "admin.php?act=deletetobj&id="+id,
        success: function(){
            campaignMenu('objectives');
        }
    });
}
function newtobj(projid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=newtobj&pid="+projid,
        success: dresponsehandler 
    });
}
function addtobj(projid)
{
   var dispo = $("#objs #disposition").val();
   var teamid= $("#objs #teamid").val();
   var period = $("#objs #period").val();
   var target = $("#objs #target").val();
    jQuery.ajax({
        url: "admin.php?act=addtobj&pid="+projid+"&disposition="+dispo+"&teamid="+teamid+"&period="+period+"&target="+target,
        success: function(){campaignMenu('objectives')}
   });
}
function newdispo(projid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=newdispo&pid="+projid,
        success: dresponsehandler 
    });
}
function newcf(projid)
{
    jQuery.ajax({
        url: "admin.php?act=getapp&app=newcf&pid="+projid,
        success: function(resp){
            dresponsehandler(resp);
        }
    });
}
function addcf(projid)
{
    jQuery.ajax({
        url: "admin.php?act=addcf",
        type: "POST",
        data: {
            "fieldname":$("#fieldname").val(),
            "fieldlabel":$("#fieldlabel").val(),
            "projectid":projid
        },
        success: function(){manage_persist(projid);} 
    });
}
function delcf(projid,fieldname)
{
    jQuery.ajax({
        url: "admin.php?act=delcf",
        type: "POST",
        data: {
            "fieldname":fieldname,
            "projectid":projid
        },
        success: function(){manage_persist(projid);} 
    });
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function savecf(projid,fieldname)
{  
    var fieldnames = $("#cf-form").serializeArray();
    jQuery.ajax({
        url: "admin.php?act=savecf",
        type: "POST",
        data: {
            "fieldname": fieldnames,
            "projectid":projid
        },
        success: function(data){
           $("#cf-label-"+fieldname).html( $('input[name='+fieldname+']').val() ); 
            cancelcf(fieldname);
            // manage_persist(projid);
        } 
    });
}
function editcf(fieldname)
{  

   // $(".cf-edit").hide(); 
   // $(".cf-cancel").hide(); 
   // $(".cf-label").hide(); 
   // $(".cf-label-editable").hide(); 

   $("#cf-edit-"+fieldname).hide(); 
   $("#cf-cancel-"+fieldname).show(); 
   $("#cf-label-"+fieldname).hide(); 
   $("#cf-label-editable-"+fieldname).show(); 
}
function cancelcf(fieldname)
{  
   $("#cf-edit-"+fieldname).show(); 
   $("#cf-cancel-"+fieldname).hide(); 
   $("#cf-label-"+fieldname).show(); 
   $("#cf-label-editable-"+fieldname).hide(); 
}
function progress(t) {
  var file = t.files[0];
  if (file) {
    var fileSize = 0;
    if (file.size > 1024 * 1024)
      fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
    else
      fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
  }
}
jQuery(document).ajaxComplete(function(){
    $(".entryform button, .entryform input:button, .secnav input:button,#updatescriptbut").button();
    $(".domenu").menu();
});
var submapping = false;
function submitmap()
{
    if (!submapping)
        {
          submapping = true;
          var xhr = new XMLHttpRequest();
          var fd = new FormData($("#mapping")[0]);
          xhr.addEventListener("load",function(resp){
              submapping = false;
              respclose(resp);
              getapp('manlist');
          }, false);
          xhr.open("POST", "leadsloader.php");
          xhr.send(fd);
        }
}
function respclose(resp)
{
     var response = resp;
    if (resp.target.responseText){
        var response = resp.target.responseText;
    }
    $("#dialogcontainer").dialog("close");
    alert(response);
}
function respmessage(resp)
{
    var response = resp;
    if (resp.target.responseText){
        var response = resp.target.responseText;
    }
    $("#respmessage").html(response);
}
function campuploadFile(el) {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(el);
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "uploader.php");
  xhr.send(fd);
}
function validupload()
{
    if (validlistid == true)
        {
    var lp = $("#listproj").val();
    if (lp < 1)
        {
            $("#listproj").css("border","red 2px solid");
            $("#listproj").attr("title","Select Campaign");
            $("#listproj").attr("placeholder","Select Campaign");
        }
    else uploadFile();
        }
        else alert('Must use valid listid');
}
function uploadFile() {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById('uploadcsv'));
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "leadsloader.php");
  xhr.send(fd);
}
function exclusionupload() {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById('uploadcsv'));
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "leadsloader.php");
  xhr.send(fd);
}
function dispoupdateupload() {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById('uploadcsv'));
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", dolm, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "leadsloader.php");
  xhr.send(fd);
}
function dolm()
{
    listMenu('dispoupdate');
    $("#dialogcontainer").dialog('destroy');
}
function uploadProgress(evt) {
  if (evt.lengthComputable) {
    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
    var pwidth = percentComplete.toString() + '%';
    jQuery("#pbar")[0].style.width= percentComplete.toString() + '%';
    jQuery("#pbar").html(pwidth);
  }
  else {
    jQuery("#pbar").html('unable to compute');
  }
}
function uploadComplete(evt) {
  /* This event is raised when the server send back a response */
  $("#resourceupload").dialog('destroy');
  dresponsehandler(evt.target.responseText);
  $(".entryform input:button").button();
}
function uploadFailed(evt) {
  alert("There was an error attempting to upload the file.");
}
function uploadCanceled(evt) {
  alert("The upload has been canceled by the user or the browser dropped the connection.");
}
function add_dispo(nprojid)
	{
	var prjid = nprojid;
	var statustype = $("#statustype").val();
        if (statustype == 'transferdateandtimecallback')
                        {
                           statustype = 'transferdateandtime';
                        }
	var statusname = $('#statusname').val();
	var category =  $("#newdispo #category").val();
        var optioninput = jQuery("#optionsinput input").val();
        if (!optioninput)
            {
                optioninput = jQuery("#listinput select#transfer_lid").val();
            }
        var transpid = jQuery("#optionsinput select#transfer_pid").val();
        var tranferstring = '&transfertopid='+transpid
	var data = 'act=addstatus&pid='+nprojid+'&statusname='+statusname+'&statustype='+statustype+'&category='+category+'&options='+optioninput+tranferstring;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			manage_persist(prjid);
			}
		};;
	http.send(data);
	}
var optionsinput = '';
function statusoptions()
{
	sttype =$("#statustype").val();
        $("#newdispo #category").removeAttr("disabled");
	if (sttype.substring(0,8) == 'transfer')
		{
                    $("#newdispo #category").prop("disabled",true);
                    if (sttype == 'transferdateandtimecallback')
                        {
                            $("#newdispo #category").val("team");
                        }
                    else
                        {
                             $("#newdispo #category").val("final");
                        }   
			$("#optionslabel").html("Transfer to Project");
			$("#optionsinput").html($("#transferproject").html());
		}
        else if (sttype == 'link')
            {
                $("#optionslabel").html('Options');
                $("#optionsinput").html('<input id="statusoption" type="text" name="statusoption" value="http://">');
            }
	else {
            $("#optionslabel").html('Options');
            $("#optionsinput").html('<input id="statusoption" type="text" name="statusoption">');
        }
}
function getpagefields()
{
    var scriptid = jQuery("#addscriptpage select#parentid").val();
    jQuery.ajax({
        url: 'admin.php?act=getpagefields&scriptid='+scriptid,
        success: function(resp){
            $("#requiredfields").html(resp);
        }
    });
}
jQuery.fn.extend({
insertAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      this.focus();
      sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  })
}
});
function mceinsert(ht)
{
    var lab = jQuery("#fieldlabel").val();
    var name= jQuery("#fieldname").val();
    try {
    toclear.remove();
    toclear = 0;
    }
    catch(err) {
    }
    var ed = tinyMCE.get('scr');     
    var range = ed.selection.getRng();
    var inputNode = ed.getDoc().createElement ("p" );
    inputNode.className= 'afield';
    if (ht == 'text')
        {
            var htm = '<input class="fi" type="text" name="'+name+'" />';
        }
    if (ht == 'select')
        {
            var htm = '<select class="fi" name="'+name+'"><option></option>';
            jQuery(".seloptions").each(
            function() {
                htm += '<option value="'+$(this).val()+'">'+$(this).val()+'</option>';
            }
            );
            htm+='</select>';
        }
    inputNode.innerHTML = "<label>"+lab+"</label>&nbsp;&nbsp;"+htm;
    // alert(inputNode);
    try {
        range.insertNode(inputNode);
    } catch(err) {
        alert("Select a part in the editor where do you want to put it.");
    }
    $("#dialogcontainer").dialog('close');
    doeditable();
}
var toclear = 0;
function doeditable()
{
    $("#scr_ifr").contents().find(".afield").click(function() {
    var label = $(this).find("label").html();
    var t = $(this).find("select");
    toclear = $(this);
    if (t.length > 0)
        {
            var name = t.attr("name");
            var options = new Array();
            var os = $(this).find("option");
            var ct = 0;
            os.each(function(){
                var opval = $(this).val();
                if (opval.length > 0)
                    {
                        options[ct] = $(this).val();
                        ct++;
                    }
            });
            var res = '';
            for (var i = 0;i < ct;i++)
                {
                    res += 'Option'+i+':'+options[i];
                }
            editdropdown(label,name,options);
        }
    var inp = $(this).find("input");
    if (inp.length > 0)
    {
        var name = inp.attr("name");
        edittextfield(label,name) 
    }
});
}
function removeoption(id)
{
    $("#"+id).remove();
}
function addanotheroption(ct)
{
    ct++;
    $("#addoptionimage").remove();
 var options = '<div id="opdiv'+ct+'">Option'+ct+'<input type=text name="option'+ct+'" id="option'+ct+'" class="seloptions" />'
+'<img src="icons/delete.gif" onclick="removeoption(\'opdiv'+ct+'\')" style="float:right"/></div>'
+'<div id="addoptionimage"><img onclick="addanotheroption('+ct+')" src="icons/add.gif">'
+'</div>';
    jQuery("#otheroptions").append(options);
}
function fieldparams(type)
{
    var pid = $("#pid").val();
    $.ajax({
        url:"admin.php?act=insertfieldparams&type="+type+"&pid="+pid,
        success: dresponsehandler
    });
}
function edittextfield(label,sname)
{
    var pid = $("#pid").val();
    $.ajax({
        url:"admin.php?act=insertfieldparams&type=text&pid="+pid,
        success: function(resp){
            dresponsehandler(resp);
            $("#fieldlabel").val(label);
            $("#fieldname").val(sname);
        }
    });
}
function editdropdown(label,name,options)
{
    var pid = $("#pid").val();
    $.ajax({
        url:"admin.php?act=insertfieldparams&type=select&pid="+pid,
        success: function(resp){
            dresponsehandler(resp);
            $("#fieldlabel").val(label);
    $("#fieldname").val(name);
            for (var i = 0;i < options.length;i++)
                {
                    var ino = i + 1;
                    if (ino == 1)
                        {
                            $("#option1").val(options[i]);
                            $("#opdiv1").append('<img src="icons/delete.gif" onclick="removeoption(\'opdiv1\')" style="float:right"/>');
                        }
                    else {
                        addanotheroption(i);
                        var opid = ino;
                        $("#option"+opid).val(options[i]);
                    }
                }
        }
    });
}
function updateprovider(projid)
{
    $.ajax({
        url: 'admin.php?act=updateprovider',
        type: 'POST',
        data: {
            "projectid":projid,
            "providerid":$("#providerid").val()
        },
        success: function() {manage_persist(projid);}
    });
}
function updatecallrecording(projid)
{
    var fld ='callrecording';
    var value = $("#ucallrecording").val();
    $.ajax({
        url: 'admin.php?act=updatecamp&pid='+projid+'&fld='+fld+'&vl='+value,
        type: 'POST',
        success: function(){manage_persist(projid);}
    });
}
function alert(message)
{
    jQuery('<div/>', {
    id: 'dialog-confirm',
    title: '',
	style: 'display:none',
	html: message
	}).appendTo('body');
	$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:140,
                        dialogClass: "noTitle",
			modal: true,
			buttons: {
				Ok: function() {
					$( this ).dialog( "close" );
                                        $(this).remove();
				}
			}
		});
}
function pleasewait(message)
{
    if (message == 'close')
    {
        $( "#pleasewait" ).dialog('close');
        $( "#pleasewait" ).remove();
    }
    else {
    jQuery('<div/>', {
    id: 'pleasewait',
    title: '',
	style: 'display:none',
	html: message
	}).appendTo('body');
	$( "#pleasewait" ).dialog({
			resizable: false,
			height:140,
                        dialogClass: "noTitle",
			modal: true,
		});
            }
}
function dragstart(event)
{
    event.dataTransfer.clearData();
    event.dataTransfer.setData('text/Plain','\r\n');
}
function dragmerge(event,el)
{
    event.dataTransfer.clearData();
    event.dataTransfer.setData('text/plain',' ['+el+'] ');
}
function dragmerge_a(event,el)
{
    event.dataTransfer.clearData();
    event.dataTransfer.setData('text/plain',' [agent-'+el+'] ');
}
var chats = new Array();
function converse()
{
$("#chatel").dialog("destroy");
$("#chatel").dialog({
    width: 955,
    height:505,
    modal: true
});
refreshonlineusers(true);
}
function refreshonlineusers(norep)
{
if ($("#chatel").css("display")!= 'none')
{
jQuery.ajax({
    url: "../messaging.php?act=refreshonline",
    global: false,
    success: function(resp){
        $("#chatusers").html(resp);
        //setTimeout("refreshonlineusers()",10000);
    }
});
}       
}
function freechat(i)
{
	chats[i.tuid] = 'notopen';
}
function indicator()
{
    var _timestamp = new Date();
    $.ajax({
        url: "../listener.php?act=admincheckin&stamp=<?=$_SESSION['logid'];?>&sid=<?=session_id();?>&_timestamp="+_timestamp.getTime(),
        global: false,
                timeout: 20000,
        success:    function(resp){
                        setTimeout("indicator()",60000);
                    },
        error:      function(jqXHR, exception) {
                        var cause = '';
                        if (jqXHR.status === 0) {
                            cause = 'Network Error.';
                        } else if (jqXHR.status == 404) {
                            cause='Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            cause='Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            cause='Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            cause='Time out error.';
                        } else if (exception === 'abort') {
                            cause='Ajax request aborted.';
                        } else {
                            cause='Uncaught Error.\n' + jqXHR.responseText;
                        }
                        alert("[Error: " +cause+ "] Your connection to the server has timed out. Please notify your I.T. if this is happening frequently. Click 'OK' to resume your session.");
                        setTimeout("indicator()",3000);
                         //window.location = "../login/";
                    }
    });
}
function dresponsehandler(data)
{
            $("#dialogcontainer").html(data);
            var n=data.match(/.*?width.*?(\d+)/im);
            var h=data.match(/.*?height.*?(\d+)/im);
            var stitle=data.match(/.*?title>([\S\s]*?)<.title>/im);
            if (stitle)
                {
                    dc = '';
                    atitle = stitle[1];
                }
            else {
                var dc = 'noTitle';
                atitle = '';
            }
            if (h)
                {
                    var hi = parseInt(h[1]) + 41;
                }
            else {
                var hi = 200;
            }
            var wi = parseInt(n[1]) + 35;
            $("#dialogcontainer").dialog({
                width: wi,
                height: hi,
                resizable: false,
                title: atitle,
                dialogClass: dc,
                show: 500,
                close: function(){
                    toclear = 0;
                }
            });
            $("#dialogcontainer")[0].style.height='auto';
            $("#dialogcontainer")[0].style.width='auto';
        }
$( window ).resize(function() {
 dynawidth();
});
function notification(message,tab)
{
    $.blockUI({ 
        message: '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>'+message+'</p></div>', 
                fadeIn: 700, 
        fadeOut: 700, 
        timeout: 0, 
        showOverlay: false, 
        centerY: false, 
        css: { 
                background: 'transparent',
                position: 'fixed',
                fontSize: '1em',
                bottom: '10px', 
                left: '', 
                right: '10px', 
                border: 'none', 
                padding: '5px', 
                width: '200px',
                top: '',
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                color: '#fff',
                                cursor: 'pointer'
        } 
        }); 
        $('.ui-state-error').click(function(){
        $.unblockUI();
        converse();
        }); 
}
function groupmess()
{
    $("#gmwindow").dialog();
    $(".jbut").button();
}
function sendgroupmess()
{
    $(".gmtoggler").each(function(){
        if($(this).prop("checked") == true)
            {
                var tomem = $(this).attr("title");
                var temess =  $("#gmmessage").val();
                $.post("/chat.php?action=sendchat", {to: tomem, message: temess});
                $("#chatbox_"+tomem+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+displaynames[username]+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+temess+'</span></div>');
            }
    });
}
function wiz_reports(clientid)
{
    $.ajax({
        url: 'admin.php?act=reportgen&clientid='+clientid,
        success: function(resp){
            $("#dialogcontainer").dialog("destroy");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                width:350,
                height:230
            });
            $(".jbut").button();
        }
    });
}
function docustom()
{
    var rtype = $("#rtype").val();
    var rname = $("#repname").val();
    var projectid = $("#projectid").val();
    var params;
    var nextact;
    if (rtype == 'ld')
            {
                    params = '?act=getlist&pid='+projectid;
                    nextact = 'ld';
            }
    if (rtype == 'pd' || rtype == 'ap' || rtype == 'ch')
            {
                    params = '?act=getrange&pid='+projectid;
                    nextact = 'pd';
            }
    $.ajax({
        url: 'admin.php'+params,
        success: function(resp){
            $("#customid").html(resp);
            $(".datepick").datepicker({ dateFormat: 'yy-mm-dd' });
        }
    });
}
function genreport(clientid)
{
	var rtypea = document.getElementById('rtype');
	if (rtypea.type == 'hidden') 
		{
		var rtype = rtypea.value;
		}
	else
		{
		var rname = document.getElementById('repname').value;
		var rtype = rtypea.options[rtypea.selectedIndex].value;
		}
	var proj = document.getElementById('projectid');
	var projectid = proj.options[proj.selectedIndex].value;
	//var start = document.getElementById('startdate').value;
	//var end = document.getElementById('enddate').value;
	if (rtype == 'ld')
		{
			var li = document.getElementById('listid');
			var lid = li.options[li.selectedIndex].value;
			var params = "?act=genreport&rname="+rname+"&rtype="+rtype+"&pid="+projectid+"&lid="+lid;
		}
	if (rtype == 'pd' || rtype == 'ap'|| rtype == 'ch')
		{
			var startd = document.getElementById('startdate').value;
			var endd = document.getElementById('enddate').value;
			var params = "?act=genreport&rname="+rname+"&rtype="+rtype+"&pid="+projectid+"&start="+startd+"&end="+endd;
		}
	if (rtypea.type == 'hidden') 
		{
			params += '&req=ad';
		}
	//alert(params);
	//var params = "?act=genreport&rname="+rname+"&rtype="+rtype+"&pid="+projectid+"&start="+start+"&end="+end;
	http.open("GET", url+params, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				resp = http.responseText;
				if (rtypea.type == 'hidden') 
					{
						document.getElementById('repdisplay').innerHTML=resp;
					}
				else editreport(resp, clientid, rname);
			}
		};
	http.send(null);
}
function editreport(bod, cid, rname)
	{
                $("#dialogcontainer").dialog("destroy");
                        $("#dialogcontainer").html(bod);
                        $("#dialogcontainer").dialog({
                            width:800,
                            height:500,
                            title: rname
                        });
		initmce('repi', cid, rname);
	}
function initmce(ta, cid, rname)
	{
		rmce = new tinymce.Editor(ta,{
		mode: 'textareas', 
		theme: 'advanced',
		plugins: "table,preview,save,style",
		content_css: 'styles/style.css',
		popup_css: 'styles/style.css',
		// Theme options
		width: 789,
		height: 500,
		theme_advanced_toolbar_align : "left",
		theme_advanced_toolbar_location : "top",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_buttons1 : "save,preview,bold, italic, justifyleft, justifycenter, justifyright,justifyfull, bullist,numlist,undo,redo,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor, fontselect, fontsizeselect, styleprops",
		theme_advanced_buttons2 : "tablecontrols",
		theme_advanced_buttons3 : "",
		content_css : "styles/style.css",
		save_onsavecallback: savereport
		});
		rmce_cid = cid;
		rmce_rname = rname;
		rmce.render();
}
function savereport(id, content)
	{
		//var texts = document.getElementById('tinymce').innerHTML;
		var texts = rmce.getContent();
		texts = encodeURI(texts);
		texts = encodeURIComponent(texts);
		var cid = rmce_cid;
		var rname = rmce_rname;
		var data = 'act=savereport&cid='+cid+'&rname='+rname+'&tex='+texts;
		http.open("POST", url, true);
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange =function()
		{
			if (http.readyState ==4)
			{
			resp = http.responseText;
                        $("#dialogcontainer").dialog("destroy");
			manreports(cid);
			//getapp('mancamp');
			}
		}
		;
		http.send(data);
	}
function manreports(cid)
	{
 $.ajax({
        url: 'admin.php?act=manreport&clientid='+cid,
        success: function(resp){
            $("#dialogcontainer").dialog("destroy");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                width:820,
                title: 'Client Reports Generated'
            });
            $(".jbut").button();
            $(".datatabs").dataTable();
        }
    });
	}
function editrep(repid,cid)
	{
	rmce_repid = repid;
	rmce_cid= cid;
	$.ajax({
		url: 'admin.php?act=getrep&repid='+repid,
		success: function(resp){
                    $("#dialogcontainer").dialog("destroy");
                    $("#dialogcontainer").html(resp);
                    $("#dialogcontainer").dialog({
                        width:800,
                        title: 'Client Reports Generated'
                    });
                    $(".jbut").button();
                    fetchmce();
                }
		});
	}
function fetchmce()
	{
		initupmce('repi',rmce_repid);
	}
function initupmce(ta, repid)
	{
		rmce = new tinymce.Editor(ta,{
		mode: 'textareas', 
		theme: 'advanced',
		plugins: "table,preview,save,style",
		content_css: 'styles/style.css',
		popup_css: 'styles/style.css',
		// Theme options
		width: 789,
		height: 500,
		theme_advanced_toolbar_align : "left",
		theme_advanced_toolbar_location : "top",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_buttons1 : "save,preview,bold, italic, justifyleft, justifycenter, justifyright,justifyfull, bullist,numlist,undo,redo,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor, fontselect, fontsizeselect, styleprops",
		theme_advanced_buttons2 : "tablecontrols",
		theme_advanced_buttons3 : "",
		content_css : "styles/style.css",
		save_onsavecallback: updatereport
		});
		rmce_repid = repid;
		rmce.render();
}
function updatereport(id, content)
	{
		var texts = rmce.getContent();
                texts = encodeURIComponent(texts);
		var rname = document.getElementById('rname').value;
		var data = 'act=updatereport&repid='+rmce_repid+'&rname='+rname+'&tex='+texts;
		http.open("POST", url, true);
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange =function()
		{
			if (http.readyState ==4)
			{
			resp = http.responseText;
			alert('Report Saved!');
			}
		}
		;
		http.send(data);
	}
function togglerelease(status,repid,cid)
	{
                $.ajax({
                    url: 'admin.php?act=uprep&repid='+repid+'&status='+status,
                    success: function(resp)
                    {
                        manreports(cid);
                    }
                });
	}
function deleterep(repid,cid)
{
    $.ajax({
        url:'admin.php?act=delrep&repid='+repid,
        success: function(resp){
            manreports(cid)
        }
    })
}
function exporttoclient(cid,repname)
{
    var body = $("#apdiv").html();
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: 'admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
function removedispoupdate(did)
{
     $.ajax({
        url:'admin.php?act=removedispoupdate&id='+did,
        success: function(resp){
            listMenu('dispoupdate');
        }
    })
}
</script>