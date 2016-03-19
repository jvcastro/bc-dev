<?php
if ($_SESSION['super'] != '1')
	{
		header("Location: ../login/");
	}
?>
<script>
var mceditor;
var adminext = '<?php echo $_SESSION['adminext'] ? $_SESSION['adminext']: 'none';?>';
function endbarge()
{
    $.ajax({
                    url: "super.php?act=endbarge&origin="+adminext,
                    success: function(){
                        $("#formloader").dialog("destroy");
                    }
		});
}
function bargethis(ext)
{
    if (adminext == 'none') {
        alert('Set your extension first.');
        setadminext();
        
    }
    else {
        $.ajax({
                    url: "super.php?act=barge&origin="+adminext+"&target="+ext,
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
 		 	url: "super.php?act=saveadminext&adminext="+adminexts,
 			success: function(resp){
                            $("#formloader").dialog("destroy");
                            $("#adminext").html(resp);
  			}
		});
}
function setadminext()
{
    $.ajax({
 		 	url: "super.php?act=setadminext",
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
function updatestatic(staticid)
{
    var pdtitle = $("#newstatic [name=title]").val();
    var pdname = $("#newstatic [name=name]").val();
    var pdst = mceditor.getContent();
    var pd = 'title='+escape(pdtitle)+'&name='+escape(pdname)+'&staticcontent='+escape(pdst);
    $.ajax({
            url: "super.php?act=updatestatic&staticid="+staticid,
            type: "POST",
            data: pd,
            success: function(resp){
                dostatic();
                //
                
            }
    });
}
function editstatic(staticid)
{
    $.ajax({
            url: "super.php?act=editstatic&staticid="+staticid,
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
                mceditor = mcerender('staticcontent');    
                mceditor.render();
                //loadjscssfile("","js");
                //
                
            }
    });
}
function addstatic()
{
    var pdtitle = $("#newstatic [name=title]").val();
    var pdname = $("#newstatic [name=name]").val();
    var pdst = mceditor.getContent();
    var pd = 'title='+escape(pdtitle)+'&name='+escape(pdname)+'&staticcontent='+escape(pdst);
    $.ajax({
        url: "super.php?act=addstatic",
        type: "POST",
        data: pd,
        success: function(resp)
        {
            dostatic();
        }
    });
}
function newstatic()
{
    $.ajax({
            url: "super.php?act=newstatic",
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
                mceditor = mcerender('staticcontent');    
                mceditor.render();
                //loadjscssfile("","js");
                //
                
            }
    });
}
function newhelptool()
{
    $.ajax({
            url: "super.php?act=newhelptool",
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
                mceditor = mcerender('helptext');    
                mceditor.render();
                //loadjscssfile("","js");
                //
                
            }
    });
}
function dostatic()
{
    $.ajax({
            url: "super.php?act=static",
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
            }
    });
}
function dohelp()
{
    $.ajax({
            url: "super.php?act=helptool",
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
            }
    });
}
function edithelptool(staticid)
{
    $.ajax({
            url: "super.php?act=edithelptool&staticid="+staticid,
            type: "GET",
            success: function(resp){
                $("#maincontent").html(resp);
                $(".jbut").button();
                mceditor = mcerender('helptext');    
                mceditor.render();
                //loadjscssfile("","js");
                //
                
            }
    });
}
function updatehelptool(staticid)
{
    var pdsection = $("#newstatic [name=section]").val();
    var pdselector = $("#newstatic [name=selector]").val();
    var pdpos = $("#newstatic [name=position]").val(); 
    var pdname = $("#newstatic [name=name]").val();
    var pdst = mceditor.getContent();
    var pd = 'section='+escape(pdsection)+'&selector='+escape(pdselector)+'&name='+escape(pdname)+'&position='+pdpos+'&helptext='+escape(pdst);
    $.ajax({
            url: "super.php?act=updatehelptool&staticid="+staticid,
            type: "POST",
            data: pd,
            success: function(resp){
                dohelp();
                //
                
            }
    });
}
function addhelptool()
{
    var pdsection = $("#newstatic [name=section]").val();
    var pdselector = $("#newstatic [name=selector]").val();
    var pdpos = $("#newstatic [name=position]").val(); 
    var pdname = $("#newstatic [name=name]").val();
    var pdst = mceditor.getContent();
    var pd = 'section='+escape(pdsection)+'&selector='+escape(pdselector)+'&name='+escape(pdname)+'&position='+pdpos+'&helptext='+escape(pdst);
    $.ajax({
            url: "super.php?act=addhelptool",
            type: "POST",
            data: pd,
            success: function(resp){
                dohelp();
                //
                
            }
    });
}
function addnewrate()
	{
            var fdata = $("#newrateform").serialize()
		$.ajax({
 		 	url: "super.php?act=addnewrate",
                        type: "POST",
                        data: fdata,
 			success: function(resp){
                            $("#formloader").dialog("destroy");
                            rates();
                            editrate(resp);
  			}
		});
	}
function newrate()
	{
		$.ajax({
 		 	url: "super.php?act=newrate",
 			success: function(resp){
                            $("#formloader").dialog("destroy");
                            $("#formloader").html(resp);
                            $("#formloader").dialog({
                                title: "New Rate",
                                minWidth:400
                            });
                            $(".jbut").button();
  			}
		});
	}
function deleteratetable(id,rateid)
{
   $.ajax({
        url:'super.php?act=deleteratetable&id='+id+'&rateid='+rateid,
        type: 'GET',
        success: function(resp){
            $("#formloader").html(resp);
            $(".jbut").button();
        }
     }); 
}
function addratetable(rateid)
{
    $.ajax({
        url:'super.php?act=addratetable&rateid='+rateid,
        type: 'GET',
        success: function(resp){
            $("#formloader").html(resp);
            $(".jbut").button();
        }
     });
}
function editrate(rateid)
{
    $.ajax({
        url:'super.php?act=editrate&rateid='+rateid,
        type: 'GET',
        success: function(resp){
            $("#formloader").dialog("destroy");
            $("#formloader").html(resp);
            $("#formloader").dialog({
                title: 'Edit Rate',
                minWidth: '500'
            });
            $(".jbut").button();
        }
     });
}
function deleterate(rateid)
{
    $.ajax({
        url:'super.php?act=deleterate&rateid='+rateid,
        type: 'GET',
        success: function(resp){
            rates();
        }
     });
}
function rates()
{
    $.ajax({
        url:'super.php?act=rates',
        type: 'GET',
        success: function(resp){
            $("#maincontent").html(resp);
            $(".jbut").button();
        }
     });
}
function logoupdate(bcid)
{
    var logo = $("#logo").val();
    $.ajax({
        url:'super.php?act=updatelogo&bcid='+bcid+'&logo='+logo,
        type: 'GET',
        success: function(resp){
            $("#formloader").dialog("close");
            editclient(bcid);
        }
     });
}
function editlogo(bcid)
{
    $.ajax({
        url:'super.php?act=editlogo&bcid='+bcid,
        type: 'GET',
        success: function(resp){
            $("#formloader").dialog("close");
            $("#formloader").html(resp);
            $("#formloader").dialog({width:350});
            $(".jbut").button();
        }
     });
}
var tchanged = false;
function callratechange(it,callrateid)
{
        if (tchanged == true) 
        {
            var field = it.name;
            var value = it.value;
            
            $.ajax({
                url: "super.php?act=callratechange&field="+field+"&value="+value+"&callrateid="+callrateid,
                type: 'GET',
                global: false
            });
            tchanged = false;
        }
}
function ratechange(it)
{
        if (tchanged == true) 
        {
            var field = it.name;
            var value = it.value;
            var rateid = $("input[name=rateid]").val();
            $.ajax({
                url: "super.php?act=ratechange&field="+field+"&value="+value+"&rateid="+rateid,
                type: 'GET',
                global: false
            });
            tchanged = false;
        }
}
function clientchange(it)
{
    if (tchanged == true) 
        {
            var field = it.name;
            var value = it.value;
            var bcid = $("input[name=bc]").val();
            $.ajax({
                url: "super.php?act=updateclient&bcid="+bcid+"&field="+field+"&value="+value,
                type: 'GET',
                global: false
            });
            tchanged = false;
        }
}
function triggerchange()
{
    tchanged = true;
}
function partners()
{
     $.ajax({
        url:'super.php?act=partners',
        type: 'GET',
        success: function(resp){
            $("#maincontent").html(resp);
            $(".jbut").button();
        }
     });
}
function addnewadmin(bcid)
{
     $.ajax({
        url:'super.php?act=addnewadmin&bcid='+bcid,
        type: 'POST',
        data: $("#createnewadminform").serialize(),
        success: function(){
            $("#formloader").dialog('close');
            loadtab('users',bcid);
            
        }
     });
}
function addnewpartner()
{
     $.ajax({
        url:'super.php?act=addnewpartner',
        type: 'POST',
        data: $("#createnewadminform").serialize(),
        success: function(){
            $("#formloader").dialog('close');
           partners();
            
        }
     });
}
function updatepartner(cpid)
{
     $.ajax({
        url:'super.php?act=updatepartner&cpid='+cpid,
        type: 'POST',
        data: $("#createnewadminform").serialize(),
        success: function(){
            $("#formloader").dialog('close');
           partners();
            
        }
     });
}
function resetpassadmin(userid)
{
    $.ajax({
        url:'super.php?act=resetpassword&userid='+userid,
        type: 'GET',
        success: function(resp){
            if (resp == 'noemail')
            {
                alert("No Email Configured for User");
            }
           else {
               alert("New Password Sent!");
           }
            
        }
     });
}
function updateadmin(userid)
{
    var bcid= $("[name=bcid]").val();
     $.ajax({
        url:'super.php?act=updateadmin&userid='+userid,
        type: 'POST',
        data: $("#createnewadminform").serialize(),
        success: function(){
            $("#formloader").dialog('close');
           loadtab('users',bcid);
            
        }
     });
}
function deletepartner(cpid)
{
     $.ajax({
        url:'super.php?act=deletepartner&cpid='+cpid,
        type: 'GET',
        success: function(){
            $("#formloader").dialog('close');
           partners();
            
        }
     });
}
function newadmin(bcid)
{
    $.ajax({
        url:'super.php?act=newadmin&bcid='+bcid,
        success: function(resp){
            $("#formloader").dialog('close');
            $("#formloader").html(resp);
            $("#formloader").dialog({title:"New Admin User",width:410});
            $(".jbut").button();
        }
    });
}
function editadmin(userid)
{
    $.ajax({
        url:'super.php?act=editadmin&userid='+userid,
        success: function(resp){
            $("#formloader").dialog('close');
            $("#formloader").html(resp);
            $("#formloader").dialog({title:"Edit Admin User",width:410});
            $(".jbut").button();
        }
    });
}
function newpartner()
{
    $.ajax({
        url:'super.php?act=newpartner',
        success: function(resp){
            $("#formloader").dialog('close');
            $("#formloader").html(resp);
            $("#formloader").dialog({title:"New Account Manager",width:410});
            $(".jbut").button();
        }
    });
}
function editpartner(cpid)
{
    $.ajax({
        url:'super.php?act=editpartner&cpid='+cpid,
        success: function(resp){
            $("#formloader").dialog('close');
            $("#formloader").html(resp);
            $("#formloader").dialog({title:"Edit Account Manager",width:410});
            $(".jbut").button();
        }
    });
}
var inputopen = '';
function clienteditext(){
    var post = $("[name=addextension]").serialize()
    $.ajax({
        url: 'super.php?act=updateext',
        type: 'post',
        data: post,
        success: function(){
            loadtab('extensions');
            $("#formloader").dialog("close");
        }
    });
}
function editext(extname, sub){
 
    $.ajax({
        url: 'super.php?act=editext&ext='+extname+'&sub='+sub,
        success: function(resp){
            $("#formloader").html(resp);
            $("#formloader").dialog({
                title: 'Edit Extension'
            });
            $(".subbut").button();
        }
    });
}
function deluser(userid,bcid)
{
    
    $.ajax({
        url: 'super.php?act=deluser&userid='+userid,
        success: function(){
            loadtab('users',bcid);
        }
    });
}
function viewtab(tab,bcid)
{
    $("#tabcont div.tabc").each(function(){
        $(this).hide();
        
    });
    $("#tabs div.active").each(function(){
        $(this).attr("class","inactive");
        
    });
    $("#"+tab+"tab").attr("class","active");
    loadtab(tab,bcid);
    $("#tab"+tab).show();
}
function loadtab(tab,bcid)
{
    if (tab == 'extensions')
        {
            $.ajax({
                url: "super.php?act=extensions&sub=1&bcid="+bcid,
                success: function(resp){
                        $("#tabextensions").html(resp);
                        var dt = $("#tabextensions table").dataTable();
                        var currentclient = $("#editclientdrop").val();
                        dt.fnFilter(currentclient,3);
                        $(".dataTables_length").html('<select id="bulkaction" onchange="extbulkdelete()"><option value="">Bulk Action</option><option value="delete">Delete</option></select>');
                        $(".dataTables_info").html(" ");
                        $(".jbut").button();
                        }
                });
        }
    if (tab == 'users'){
    $.ajax({
                url: "super.php?act=users&bcid="+bcid,
                success: function(resp){
                        $("#tabusers").html(resp);
                        var dt = $("#tabusers table").dataTable();
                        var currentclient = $("#editclientdrop").val();
                        dt.fnFilter(currentclient,5);
                        dt.fnSetColumnVis(5,false);
                        $(".dataTables_length").html('<select id="bulkaction" onchange="userbulkaction()"><option value="">Bulk Action</option><option value="enable">Activate</option><option value="disable">Deactivate</option></select>');
                        $(".dataTables_info").html(" ");
                        $(".jbut").button();
                        }
                });
    
    }
}
function uploadFile(elemen,url) {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById(elemen));


  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", url);
  xhr.send(fd);
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
  var logoloc = evt.target.responseText;
  if (logoloc == 'toobig')
      {
          uploadFailed("Logo File size exceeded.");
      }
  else if (logoloc == 'error')
      {
           ;
          uploadFailed("Error in Upload.");
      }
  else {
  jQuery("#pbar")[0].style.width= '100%';
  jQuery("#pbar").html('100%');
  var logoloc = evt.target.responseText;
  $("#logo").val(logoloc);
  }
}

function uploadFailed(emessage) {
  $("#uploaderr").show();
  $("#uploaderr").html(emessage);
}

function uploadCanceled(evt) {
  alert("The upload has been canceled by the user or the browser dropped the connection.");
}
function viewrep(actions)
{

	
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
	var client = $("#client").val();
        var coptions = '';
        if (actions == 'viewcallcosts' || actions == 'viewcallcostsdet')
            {
                coptions = '';
            }
	$.ajax({
  		url: "super.php?act="+actions+"&start="+start+"&end="+end+"&client="+client+coptions,
  		success: function(data){
    	 $('#resultdetails').html(data);
         $('.jbut').button();
  	}
	});
}
function addprov()
	{
		$.ajax({
 		 		url: "super.php?act=addprovider",
 				success: function(resp){
					$("#formloader").dialog("destroy");
                                        $("#formloader").html(resp);
                                        $("#formloader").dialog({
                                            minWidth: 410,
                                            title: 'Add Voice Provider'
                                        });
                                        $(".jbut").button();
					
  					}
				});
	}
function addext()
	{
		$.ajax({
 		 		url: "super.php?act=addext",
 				success: function(resp){
					$("#formloader").dialog("destroy");
                                        $("#formloader").html(resp);
                                        $("#formloader").dialog({
                                            minWidth: 310,
                                            title: 'Add Extension'
                                        });
                                        $(".jbut").button();
					
  					}
				});
	}
function extensions()
	{
		$.ajax({
 		 		url: "super.php?act=extensions",
 				success: function(resp){
					changecontent(resp);
					$("table").tablesorter();
					$("th").css('cursor','pointer');
  					}
				});
	}
function delpack(packageid)
	{
		$.ajax({
 		 		url: "super.php?act=delpack&packageid="+packageid,
 				success: function(resp){
					loadform('Packages');
  					}
				});
	}
function gettransactions()
	{
            var sbcid = $("#selectedbcid").val();
            if (!sbcid) var sbcid = 'all';
            $.ajax({
            url: "super.php?act=gettrans&selectedbcid="+sbcid,
            success: function(resp){
                        inputopen = 'no';
                        $("#formloader").dialog('close');
                        changecontent(resp);
                        $(".transtable").dataTable();
                    }
            });

	}
function changecontent(resp)
	{
		$("#vismain").html(resp);
	}
function submitpackage()
	{
		var params = new Object();
		params['qty'] = document.getElementById('p_qty').value;
		params['packagetype'] = document.getElementById('p_packagetype').value;
		params['packagename'] = document.getElementById('p_packagename').value;
		params['packagecost'] = document.getElementById('p_packagecost').value;
		params['packagedescripton'] = document.getElementById('p_packagedescription').value;
		$.ajax({
 		 	url: "super.php?act=addpackage",
			data: params,
 			success: function(resp){
				inputopen = 'no';

				loadform('Packages');
  			}
		});
		
	}
function submitpayment()
	{
		var params = new Object();
		params['bcid'] = document.getElementById('p_bcid').value;
		params['amount'] = document.getElementById('p_amount').value;
		params['paymentmode'] = document.getElementById('p_paymentmode').value;
		params['referencenumber'] = document.getElementById('p_referencenumber').value;
		params['comments'] = document.getElementById('p_comments').value;
		$.ajax({
 		 	url: "super.php?act=addpay",
			data: params,
 			success: function(resp){
				inputopen = 'no';

				loadform('Rates');
  			}
		});
		
	}

function submitratechange(tid, rateid)
	{
		
		var td = document.getElementById(tid);
		var input = document.getElementById("inputid");
		td.innerHTML = input.value;
		
		$.ajax({
 		 	url: "super.php?act=ratechange&field="+tid+"&value="+input.value+"&rateid="+rateid,
 			success: function(resp){
				inputopen = 'no';
    			$("#formloader").html(resp);
  			}
		});
	}
function loadform(type)
	{
		
		$("#formloader").dialog('close');
		$.ajax({
 		 	url: "super.php?act="+type,
 			success: function(resp){
    			$("#formloader").html(resp);
				$("#formloader").dialog({width: 700, title: type});
  			}
		});
	}
 
function deactivate(userid)
	{
		http.open("GET", url+"?act=updateuserdet&type=members&val=0&fild=active&id="+userid, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
			window.location="super.php";
		}};
	http.send(null);	
	}
function activate(userid)
	{
		http.open("GET", url+"?act=updateuserdet&type=members&val=1&fild=active&id="+userid, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
			window.location="super.php";
		}};
	http.send(null);	
	}
function createcancel()
	{
		document.getElementById('creation').innerHTML='<a href="#" onclick="createnew()">Create New</a>';
	}
function submitnewclient()
{
    var ncdata = $("#newclientform").serialize();
    $.ajax({
                    url: "super.php",
                    type: 'POST',
                    data: ncdata,
                    success: function(resp){
                        $("#formloader").dialog("destroy");
                        if (resp == 'error')
                            {
                                alert("There was an error adding client. Try again.");
                            }
                        else if (resp == 'exists')
                            {
                                alert("Client (email) already exists!");
                            }
                       else {
                           editclient(resp);
                       }
                    }
		});
}
function newclient()
	{
		$.ajax({
 		 		url: "super.php?act=newclientform",
 				success: function(resp){
				inputopen = 'no';
    			$("#formloader").dialog('close');
                                $("#formloader").html(resp);
				$("#formloader").dialog({title:"New Client",width:410});
                                $(".jbut").button();
  					}
				});
	}
function editclient(id)
	{
		$.ajax({
 		 		url: "super.php?act=editclient&clientid="+id,
 				success: function(resp){
				changecontent(resp);
                                $(".jbut").button();
  					}
				});
	}
function changeviewclient()
{
    var i = $("#editclientdrop").val();
    editclient(i);
}
function createnew()
	{
		$("#newadmin").dialog({width: 270, title:"Add New Admin"});
	}
function changedetails(di, fild, type,id)
	{
		var target = document.getElementById(di);
		var vl = fild;
		target.innerHTML = '<input type=text value="'+vl+'" onblur="submitchangesdet(this.value,\''+type+'\',\''+di+'\',\''+id+'\')">';
		
	}
function submitchangesdet(vl,type,di,id)
	{
	if (di.substr(0,4) == 'pass') 
		{
			var dd = 'userpass';
		}
	else dd = di;
	http.open("GET", url+"?act=updateuserdet&type="+type+"&val="+vl+"&fild="+dd+"&id="+id, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
		{
		var target = document.getElementById(di);
		target.innerHTML = '<a onclick="changedetails(\''+di+'\',\''+vl+'\',\''+type+'\',\''+id+'\')">'+vl+'</a>';
		}};
	http.send(null);	
	}
function getHTTPObject() {
  var xmlhttp;

  if(window.XMLHttpRequest){
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
var http = getHTTPObject();
var url = 'admin.php';
function clientlettersearch(letter)
{
    var exp= "^"+letter+".+";
    if (letter == 'All') {
        exp = '';
        dtable.fnFilter(exp);
    }
    dtable.fnFilter(exp,1,true,true,true,true);
}
function activetoggle()
{
    var vl = $("#clientabletoggle").val();
    //dtable.fnFilter(vl,7,false,true,true,false);
    if (vl == 'Active') window.location = 'super.php';
    else window.location = 'super.php?inactive';
}
function extbulkdelete(bc)
{
    var action = $("#bulkaction").val();
    
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
        url: 'super.php?extbulkdelete',
        type: 'POST',
        data: {
            "bcids": bid
        },
        success: function(){
            loadtab('extensions',bc);
        }
    });
        }
}
function userbulkaction()
{
    var action = $("#bulkaction").val();
    var ct = 0;var bid = new Array();
    $("[name=bulkaction]").each(function(){
        if (this.checked)
            {
                bid[ct] = $(this).val();
                ct++;
            }
    });
    if (action != '')
        {
            $.ajax({
                url: 'super.php?userbulk'+action,
                type: 'POST',
                data: {
                    "bcids": bid
                    },
                success: function(){

                    }
                });
        }
}
function loadjscssfile(filename, filetype){
 if (filetype=="js"){ //if filename is a external JavaScript file
  var fileref=document.createElement('script')
  fileref.setAttribute("type","text/javascript")
  fileref.setAttribute("src", filename)
 }
 else if (filetype=="css"){ //if filename is an external CSS file
  var fileref=document.createElement("link")
  fileref.setAttribute("rel", "stylesheet")
  fileref.setAttribute("type", "text/css")
  fileref.setAttribute("href", filename)
 }
 if (typeof fileref!="undefined")
  document.getElementsByTagName("head")[0].appendChild(fileref)
}
function mcerender(targetid)
{
	var mce = new tinymce.Editor(targetid,{
					mode: 'textareas', 
					theme: 'advanced',
					// Theme options
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
                        
			relative_urls : true,
					content_css : "styles/style.css"

					});
	return mce;
}
function clientsbulkaction()
{
    var action = $("#bulkaction").val();
    
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
        url: 'super.php?bulk'+action,
        type: 'POST',
        data: {
            "bcids": bid
        },
        success: function(){
            window.location = "super.php";
        }
    });
        }
}

var dtable = '';
var dt = '';
$(document).ready(function(e) {
                $(".jbut").button();
    $(".dateinput").datepicker({ dateFormat: 'yy-mm-dd' });
  <?php
  if ($_REQUEST['act'] == 'extensions')
  {
  ?>
  dt = $(".extensionstable").dataTable();
        $(".dataTables_length").html('<select id="bulkaction" onchange="extbulkdelete()"><option value="">Bulk Action</option><option value="delete">Delete</option></select>');
        $(".dataTables_info").html(" ");
  $(".jbut").button();
  
 <?php
  }
  elseif ($_REQUEST['act'] == 'providers')
  {
  ?>
  dt = $(".providerstable").dataTable();
        $(".dataTables_length").html('<select id="bulkaction" onchange="extbulkdelete()"><option value="">Bulk Action</option><option value="delete">Delete</option></select>');
        $(".dataTables_info").html(" ");
  $(".jbut").button();
  
 <?php
  }
  else {
      $inactiveselected = '';
      $cf = 'Active';
      if ($toginactive)
      {
          $inactiveselected = 'selected';
          $cf = 'Inactive';
      }
 ?>
     dtable = $(".clientstable").dataTable();
      if (dtable)
          {
  $(".dataTables_filter").html('View: <select id="clientabletoggle" onchange="activetoggle()"><option value="Active">Active</option><option value="Inactive" <?=$inactiveselected;?> >Inactive</option></select>');
   $(".dataTables_length").html('<select id="bulkaction" onchange="clientsbulkaction()"><option value="">Bulk Actions</option><option value="enable">Activate</option><option value="disable">Deactivate</option></select>');
  //dtable.fnFilter("Active",7,false,true,true,false);
  dtable.fnSetColumnVis( 9, false );
   $("#csform").submit(function(e){
      e.preventDefault();
      var vl = $("#cssearch").val();
      dtable.fnFilter(vl);
  });
  }    
 <?php
  }
  ?>

});

</script>
