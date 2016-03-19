<?php
?>

<script>
var clientwin;
var rmce_cid;
var rmce_rname;
var rmce;
var rmce_repid;
var lmceid;
var lmce;
function updateleadpage()
	{
		var texts = lmce.getContent();
		texts = encodeURI(texts);
		texts = encodeURIComponent(texts);
		var data = 'act=updateleadpage&cid='+lmceid+'&tex='+texts;
		http.open("POST", url, true);
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange =function()
		{
			if (http.readyState ==4)
			{
			resp = http.responseText;
			reloadwin(rmce_cid);
			leadpage(lmceid);
			//getapp('mancamp');
			}
			
		}
		;
		http.send(data);
	}
function leadpmce(cid)
	{
		lmceid = cid;
		lmce = new tinymce.Editor('pagebody',{
		mode: 'textareas', 
		theme: 'advanced',
		plugins: "table,preview,save,style",
		content_css: 'styles/style.css',
		popup_css: 'styles/style.css',
		// Theme options
		width: 789,
		height: 285,
		theme_advanced_toolbar_align : "left",
		theme_advanced_toolbar_location : "top",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_buttons1 : "save,preview,bold, italic, justifyleft, justifycenter, justifyright,justifyfull, bullist,numlist,undo,redo,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor, fontselect, fontsizeselect, styleprops",
		theme_advanced_buttons2 : "tablecontrols",
		theme_advanced_buttons3 : "",
		content_css : "styles/style.css",
		save_onsavecallback: updateleadpage
		});
		lmce.render();
	}
function leadpage(cid)
	{
	var data = '?act=leadpagegen&cid='+cid;
	http.open("GET", url+data, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					var resp = http.responseText;
					var hd = '<div id=ldpage><input type=hidden name=clientid value="'+cid+'">';
					var end = '</div>';
					document.getElementById('displayport').innerHTML = hd+resp+end;
					leadpmce(cid);
					}
	};
	http.send(null);	
	}
function editrep(repid,cid)
	{
	rmce_repid = repid;
	rmce_cid= cid;
	manreps.load({
		url: 'admin.php?act=getrep&repid='+repid,
		callback: fetchmce
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
		height: 285,
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
function updatereport()
	{
		var texts = rmce.getContent();
		texts = encodeURI(texts);
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
			reloadwin(rmce_cid);
			Ext.Msg.alert('Update','Report Saved!');
			editscript(pid);
			//getapp('mancamp');
			}
			
		}
		;
		http.send(data);
		
	}
function reloadwin(cid)
	{
		manreps.load({url: 'admin.php?act=manreport&clientid='+cid});
	}
function togglerelease(status,repid,cid)
	{
		ajaxsend('act=uprep&repid='+repid+'&status='+status, reloadwin(cid),cid);
	}
function ajaxsend(params,funct,cid)
	{
	http.open("GET", url+'?'+params, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				reloadwin(cid);
			}
		};
	http.send(null);
	}
var manreps;
function manreports(cid)
	{
	manreps = new Ext.Window({
	title: 'Manage Client Reports ',
	bodyStyle: 'background-color:#FFFFFF',
	layout: 'fit',
	width: 800,
	height: 300,
	autoLoad: {
		url: 'admin.php?act=manreport&clientid='+cid,
		scripts: true
		//callback: calsetup
		}
	
});	
manreps.show();
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
			repwin_02.close();
			Ext.Msg.alert('Update','Report Saved!');
			editscript(pid);
			//getapp('mancamp');
			}
			
		}
		;
		http.send(data);
		
	}
function docustom()
	{
		
		var rtypea = document.getElementById('rtype');
		if (rtypea.type == 'hidden') var rtype = rtypea.value;
		else {
			var rtype = rtypea.options[rtypea.selectedIndex].value;
			var rname = document.getElementById('repname').value;
		}
		var proj = document.getElementById('projectid');
		var projectid = proj.options[proj.selectedIndex].value;
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
	http.open("GET", url+params, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			if (nextact == 'ld')
				{
					var resp = http.responseText;
					document.getElementById('customid').innerHTML=resp;
				}
			else if (nextact == 'pd')
				{
					var resp = http.responseText;
					
					document.getElementById('customid').innerHTML=resp;
				}
			else {
					var resp = http.responseText;
					
					document.getElementById('customid').innerHTML=resp;
				}
			}
		};
	http.send(null);
	}
function wiz_reports(clientid)
{
	repwin_01 = new Ext.Window({
	title: 'Report Generation Tool '+clientid,
	bodyStyle: 'background-color:#FFFFFF',
	layout: 'fit',
	width: 800,
	height: 300,
	autoLoad: {
		url: 'admin.php?act=reportgen&clientid='+clientid,
		scripts: true
		//callback: calsetup
		}
	
});	
repwin_01.show();
}
function calsetup(did,targetid)
{
	var myDP = new Ext.DatePicker({
		format: "Y-m-d",
		listeners: {'select': selechandler},
		target: targetid
		});
	myDP.render(did);
	//myDP.hide();
}
function selechandler(dp,date)
	{
		document.getElementById(dp.target).value = date.format("Y-m-d");
		dp.hide();
	}
function exportreport(clientid)
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
			params += '&req=export';
		}
	window.location=url+params;
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
		repwin_01.close();
		repwin_02 = new Ext.Window({
				title: 'Report Generation Tool ' + rname,
				bodyStyle: 'background-color:#FFFFFF',
				layout: 'fit',
				width: 800,
				height: 300,
				html: bod,
				
			});
		repwin_02.show()
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
		height: 285,
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
function startwiz()
{
clientwin = new Ext.Window({
	title: 'Select or Create a Client',
	layout: 'fit',
	bodyStyle: 'background-color:#FFFFFF',
	width: 680,
	height: 300,
	autoLoad: 'admin.php?act=clientwin'
});
clientwin.addButton({text: 'next', handler: next_clientwin});
clientwin.show();
}

function wizcreatenew()
	{
	var afname = document.getElementById('afname').value;
	var alname = document.getElementById('alname').value;
	var userlogin = document.getElementById('userlogin').value;
	var userpass = document.getElementById('userpass').value;
	var company  = document.getElementById('company').value;
	var params = '?act=wiz_createnewclient&afname='+afname+'&alname='+alname+'&userlogin='+userlogin+'&userpass='+userpass+'&company='+company;
	http.open("GET", url+params, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			var nid = http.responseText
			wiz_campwin(nid);
			}
		};
	http.send(null);
	}

function next_clientwin(){
if (cwinvar_client == 'old')
	{
	var cid = document.getElementById('clientid');
	var pid = cid.options[cid.selectedIndex].value;
	wiz_campwin(pid)
	}
else 
	{
	wizcreatenew();
	}
}
var campwin;
function wiz_campwin(clid)
{
	clientwin.close()
	campwin = new Ext.Window({
	bodyStyle: 'background-color:#FFFFFF',
	title: 'Create Project',
	layout: 'fit',
	width: 680,
	height: 300,
	autoLoad: 'admin.php?act=projectwin&clientid='+clid
});
campwin.addButton({text: 'next', handler: next_campwin});
campwin.show();
}
function next_campwin()
	{
	var projname = document.getElementById('projectname').value;
	var projdesc = document.getElementById('projectdesc').value;
	var dialmode = document.getElementById('dialmode').options[document.getElementById('dialmode').selectedIndex].value;
	var dialpace = document.getElementById('dialpace').options[document.getElementById('dialpace').selectedIndex].value;
	var clientid = document.getElementById('clientid').value;
	var params = "?act=wiz_createnewproject&projname="+projname+"&projdesc="+projdesc+"&dialmode="+dialmode+"&dialpace="+dialpace+"&clientid="+clientid;
	http.open("GET", url+params, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			var pid = http.responseText;
			wiz_newlist(pid);			
			}
		};
	http.send(null);
	}
var listwin;
var nbutton = new Ext.Button({text: 'next', handler: next_listwin, disabled: true, id: 'nbutton'});
var prid;
function wiz_newlist(cid)
	{
	campwin.close();
	prid = cid;
	listwin =  new Ext.Window({
	title: 'Create and Upload List',
	layout: 'fit',
	width: 680,
	height: 520,
	html:'<iframe src="listloader.php?projlist='+cid+'+" style="width:700px; height:500px; overflow:hidden; border:none">'
});

listwin.addButton(nbutton);
listwin.show();
	}
function addnext()
{
Ext.getCmp('nbutton').enable();
}
function next_listwin()
	{
	wiz_scriptwin()
	}
var scriptwin;
function wiz_scriptwin()
	{
	listwin.close();
	scriptwin =  new Ext.Window({
	title: 'Create Script for Project',
	layout: 'fit',
	width: 830,
	height: 500,
	autoLoad: 'admin.php?act=wiz_editscript&pid='+prid});
	scriptwin.addButton({text: 'next', handler: next_scriptwin});
	scriptwin.show()
	}
function next_scriptwin()
	{
	var texts = document.getElementById('scr').value;
	texts = encodeURI(texts);
	texts = encodeURIComponent(texts);
	var data = 'act=updatescript&pid='+prid+'&tex='+texts;
		
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			//wiz_teamwin(prid);
			wiz_dispowin(prid);
			}
		};;
	http.send(data);
	}
var dispowin;
function wiz_dispowin(pr)
	{
	if (scriptwin)
		{
		scriptwin.close();
		}
	dispowin = new Ext.Window({
	title: 'Add Dispositions',
	layout: 'fit',
	width: 830,
	height: 500,
	autoLoad: 'admin.php?act=wiz_dispowin&pid='+prid
	});
	//dispowin.addButton({text: 'Add', handler: add_dispowin});
	dispowin.addButton({text: 'next', handler: wiz_teamwin});
	dispowin.show();
	}
var nprojid = 0;
function add_dispo()
	{
	var prjid = nprojid;
	var status = document.getElementById('statustype');
	var statustype = status.options[status.selectedIndex].value;
	var statusname = document.getElementById('statusname').value;
	var cat =  document.getElementById('category');
	var category = cat.options[cat.selectedIndex].value;
        
        var optioninput = jQuery("#optionsinput input, #optionsinput select").val();
	var data = 'act=addstatus&pid='+nprojid+'&statusname='+statusname+'&statustype='+statustype+'&category='+category+'&options='+optioninput;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			//wiz_teamwin(prid);
			//Ext.Msg.alert('Info','New Disposition Added');
			manage(prjid);
			}
		};;
	http.send(data);
	}
function add_dispowin()
	{
	var status = document.getElementById('statustype');
	var statustype = status.options[status.selectedIndex].value;
	var statusname = document.getElementById('statusname').value;
	var cat =  document.getElementById('category');
	var category = cat.options[cat.selectedIndex].value;
	var dispo = document.getElementById('dispocat');
	var dispocat = dispo.options[dispo.selectedIndex].value;
	var data = 'act=addstatus&pid='+prid+'&statusname='+statusname+'&statustype='+statustype+'&category='+category+'&dispocat='+dispocat;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			//wiz_teamwin(prid);
			//Ext.Msg.alert('Info','New Disposition Added');
			dispowin.load({url: 'admin.php', params: 'act=wiz_dispowin&pid='+prid});
			}
		};;
	http.send(data);
	
	}
function next_dispowin()
	{
	var status = document.getElementById('statustype');
	var statustype = status.options[status.selectedIndex].value;
	var statusname = document.getElementById('statusname').value;
	var cat =  document.getElementById('category');
	var category = cat.options[cat.selectedIndex].value;
	var dispo = document.getElementById('dispocat');
	var dispocat = dispo.options[dispo.selectedIndex].value;
	var data = 'act=addstatus&pid='+prid+'&statusname='+statusname+'&statustype='+statustype+'&category='+category+'&dispocat='+dispocat;
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			//wiz_teamwin(prid);
			Ext.Msg.alert('Info','New Disposition Added');
			wiz_teamwin(prid);
			}
		};;
	http.send(data);
	
	}
var teamwin;
function wiz_teamwin(pr)
	{
	dispowin.close();
	teamwin =  new Ext.Window({
	title: 'Create or assign Team',
	layout: 'fit',
	width: 830,
	height: 500,
	autoLoad: 'admin.php?act=wiz_teamwin&pid='+prid});
	teamwin.addButton({text: 'next', handler: next_teamwin});
	teamwin.show()
	}
function next_teamwin()
	{
	if (cwinvar_client == 'old')
		{
		var project = prid;
		var team = document.getElementById('team');
		teamid = team.options[team.selectedIndex].value;
		http.open("GET", url+"?act=updateteam&tid="+teamid+"&project="+prid, true);
		http.onreadystatechange = function(){
				if (http.readyState == 4)
					{
					var resp = http.responseText;
					wiz_last();
					}
		};
		http.send(null);
		}
	else 
		{
		var team = document.getElementById('teamname').value;
		var params = '?act=wiz_createnewteam&teamname='+team+'&pid='+prid;
		http.open("GET", url+params, true);
		http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
			var pid = http.responseText;
			wiz_last(pid);			
			}
		};
		http.send(null);
		}
	}
function wiz_last()
	{
	teamwin.close()
	var final = new Ext.Window({
	title: 'Project Setup Completed',
	layout: 'fit',
	width: 830,
	height: 500,
	html: '<b>Project Setup wizard is finished</b><br>Next you will have to or assign users to the team you selecte for this project...'});
	final.show();
	}
</script>