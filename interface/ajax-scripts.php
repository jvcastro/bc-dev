<script>
var url = "ajax.php";
var newleadid = 0;
var scripttabed = 0;
var urlcheck = "ajax.php?act=check&uid="; 
var urlget = "ajax.php?act=getinfo&uid=";
var userid = '<?=$session->userid;?>';
var recordingmode = '<?=$recordingmode;?>';
var checkingnew = 0;
var dialactions = new Object();
dialactions.next = false;
dialactions.submit = false;
var cbable = true;
<?php
// echo ($session->user['chat'] == 'enabled') ? '<script type="text/javascript" src="../jquery/js/chat.js"></script>' : '';
if ($session->user['chat'] == 'enabled')
{
    if (ui_getOpt($p, 'isChatEnabled'))
    {
        echo "var isChatEnabled = true; \n";
    }
    else
    {
        echo "var isChatEnabled = false; \n";
    }
}
else
{
    echo "var isChatEnabled = false; \n";
}
?>
function togids()
{
    if ($("#togglecheck").prop('checked'))
        {
            $(".simtabtick").prop("checked",true);
        }
    else $(".simtabtick").prop("checked",false);
}
function disablecbclick()
{
    cbable = false;
}
function enablecbclick()
{
    cbable =true;
}
function changecbview()
{
    var view = $("#cbview").val();

    $.ajax({
            url: 'ajax.php?act=getcallbacks&view='+view,
            success: function(resp){
                $("#cbtab").html(resp);
                 jQuery(".jbut").button();
                jQuery(".sortable").tablesorter();
            }
        });
}
function showrecordcontrol()
{
    if (recordingmode == 'forced')
        {
            //$("#recordingcontrol").html('<a href"#" id="recbutton">Call is Recorded</a>');
            $("#recordingcontrol").html(' ');
            //$("#recbutton").button({icons:{primary:"recicon"} });
            //$("#recordingcontrol").show();
        }
    if (recordingmode == 'optional' || recordingmode == 'started')
        {
            $("#recordingcontrol").html('<a href="#" onclick="startrecording()" id="recbutton">Start Recording</a>');
            $("#recbutton").button({icons:{primary:"recstart"} });
            $("#recordingcontrol").show();
        }
    if (recordingmode == 'disabled')
        {
            //$("#recordingcontrol").html('<a href"#" id="recbutton">Call not Recorded</a>');
            //$("#recbutton").button({icons:{primary:"recicon"} });
            //$("#recordingcontrol").show();
            $("#recordingcontrol").html(' ');
        }
    
}
function hiderecordcontrol() {
     $("#recordingcontrol").hide();
}
function checkdialstate()
{
    $.ajax({
        url: 'ajax.php?act=dialstate&userid='+userid,
        success: function(resp){
            if (resp == 'ended')
                {
                    disableb('hbb');
                    enableb('dbb');
                    if (dialmode == 'progressive')
                        {
                            //enableb('cbtab');
                            enablecbclick();
                        }
                    if (dialmode == 'predictive' || dialmode == 'inbound')
                        {
                            enableb('nbb');
                        }
                    astatus = 'hanged';
                }
            if (astatus == 'incall' || astatus == 'dialing')
                {
                   setTimeout("checkdialstate()",3000);
                }
        }
    });
}
function handleHttpResponse(resp)
	{   
            running = 0;
              	var results=resp;
              	if (results == 'false' || results == 'checkforcalls')
					{
					clearfields();
					if (checkingnew ==21)
						{
							disableb('hbb');hideb('nbb');
							disableb('nlbutton');
							showb('pause');
                                                        disableb('dbb');
							setTimeout("checkforcalls()",1000);
							
						}
					if (checking == 1)
						{
						clicked = 1;
						hideb('start');
						disableb('nlbutton');
                                                disableb('dbb');
						showb('pause');
						setTimeout("checkforcalls()",1000);
						}
					else {
					disableb('hbb');hideb('nbb');
                                        disableb('dbb');
                                        //disableb('cbtab');
                                        disablecbclick();
					setTimeout("checkforcalls()",1000);
					}
					}
				else if (results == 'error') {
					Ext.Msg.alert("Error","Error Occured try again");
				}
				else if (results == 'paused'){
					checking = 1;
					clicked = 0;
					astatus = 'paused';
					hideb('pause');
                                        disableb('dbb');
					showb('start');
					showb('nlbutton');
                    enableb('nlbutton');
                    //enableb('cbtab');
                    enablecbclick();

					if ( typeof(tteventsmenu) != 'undefined')
						timeoutPause();
				}
				else if (results == 'callended') {
					astatus = 'hanged';
					disableb('hbb');
					enableb('nbb');
                                        if (dialmode == 'progressive')
                                            {
                                                //enableb('cbtab');
                                                enablecbclick();
                                                enableb('nlbutton');
                                            }
				}
				else if (results == 'nopause'){
					clicked = 1;
					hideb('start');
					disableb('nlbutton');
					showb('pause');
					
				}
				else if (results == 'nohopper'){
					warningmess('No more leads in hopper');
					disableb('hbb');
                                        enableb('nlbutton');
					showb('nbb');
					
				}
				else if (results == 'loggedout')
					{
						alert("You were logged out!");
						try {
						var sId = document.getElementById('leadid').value;
						}
						catch (e)
							{
							sId = 0;
							}
						if (sId != 0)
							{
							document.getElementById('disposition').selectedIndex = 1;
							var thi = document.getElementById('disposition').selectedIndex;
							document.getElementById('disposition').options[thi].value = 'Drop';
							var uId = '<?=$userid;?>';
	
							if (newleadid != 0) {sId = newleadid;}
							newleadid = 0;
							submitter('exitdial',exitapp);
							}
							else {
								var uId = '<?=$userid;?>';
                                                                $.ajax({
                                                                    url: 'ajax.php?dialmode='+dialmode+'&uid=' + uId + '&act=exitdial',
                                                                    success: exitapp
                                                                });
								
							}
					}
                                else if (results == 'newinboundcall')
                                {
                                    astatus = 'New Inbound Call';
                                    enableb('hbb');
                                    hideb('pause');
                                    showb('nbb');
                                    disableb('nbb');
                                    //disableb('cbtab');
                                     disablecbclick();
                                     newinboundcall();
                                }
                                else if (results == 'newinboundcallmanual')
                                {
                                    astatus = 'New Inbound Call';
                                    enableb('hbb');
                                    hideb('pause');
                                    showb('nbb');
                                    disableb('nbb');
                                    //disableb('cbtab');
                                     disablecbclick();
                                     newinboundcallmanual();
                                }
				else {

				if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
						{
							enableb('hbb');
							hideb('pause');
                                                        showb('nbb');
                                                        disableb('nbb');
                                                        //disableb('cbtab');
                                                         disablecbclick();
						}
				if (astatus != 'preview')
						{
						astatus = 'incall';
                                                enableb('hbb');
                                                //disableb('cbtab');
                                                 disablecbclick();
                                                disableb('dbb');
                                                showrecordcontrol();
                                                if (recordingmode == 'started')
                                                    {
                                                        startrecording();
                                                    }
                                                disableb('nlbutton');
                                                checkdialstate();
						}
				else {
					enableb('dbb');
					disableb('hbb');
                                        //enableb('cbtab');
                                         enablecbclick();
                                        hiderecordcontrol();
                                        try {
                                            enableb('nlbutton');
                                        }
                                        catch(e){}
				}
				//xmlDoc=http.responseXML;
				populate(resp);
				}

        }

function populate(jsontext)
{
    clearfields();
    var cf = '';
    var customer = jQuery.parseJSON(jsontext);
    $.each(customer, function(index, value) 
        {
            if (index == 'leadid') cf = value;
            if (index == 'listid')
                {
                     $("#"+index).val(value);
                     $("#listiddisplay").html(value.substr(0,20));
                     $("#listiddisplay").attr("title",value);
                }
            else if (index == 'dtime')
                {
                    $("#datetd").show();
                    $("#calendar").val(value);
                }
                
            else if (index == 'dispo') {
                //$("select#disposition option").filter(function() {
                //    return $(this).text() == value; 
                 //   }).attr('selected', true);
                 $("#previousdispo").html(value);
            }
           else if (index == 'suburb' || index == 'city'){
               if ($("#city").val().length < 2)
                   {
                        $("#city").val(value);
                   }
           }
           else if (index == 'override_pid')
           {
                $("#override_pid").val(value);
           }
           else 
                {
                    if (index == 'resultcomments') index = 'notes';
                  
                    $("#"+index).val(value);
                }
           
        });
        getcustomdata(cf);
        getnotes();
        reloadscript();
}
function getcustomdata(leadid)
{
    $.ajax({
        url: "ajax.php?act=getcustomdata&leadid="+leadid+"&userid="+<?=$userid;?>,
        success: loadcustomdata
    });
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function getnewcustomdata(pid)
{
    $.ajax({
        url: "ajax.php?act=getnewcustomdata&pid="+pid,
        success: loadnewcustomdata
    });
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function handler() { alert('hello'); }
$('.add_to_this').each(function() {
  var link = $('<a>Click here</a>');
  $(this).append(link);
  link.click(handler);
});

function loadnewcustomdata(data)
{
    var cust = jQuery.parseJSON(data);
    $("#othercontent").html('');
    var ct = 0;
    $.each(cust,function(index, value){
        $("#othercontent").append('<div><label>'+index+'</label><input type="text" name="'+index+'" "></div><div class="clear"></div>');
        ct++;
    });
    $("#otherinfo").accordion("resize");
    $("#otherinfo2").accordion("resize");
    if (ct > 0) {
        $("#otherinfo2").show();
        $("#otherinfo2").accordion("resize");
    }
}
function loadcustomdata(data)
{
    var cust = jQuery.parseJSON(data);
    $("#othercontent").html('');
    var ct = 0;
    $.each(cust,function(index, value){
        $("#othercontent").append('<div><label>'+index+'</label><input type="text" name="'+index+'" value="'+value+'"></div><div class="clear"></div>');
        ct++;
    });
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    $("#othercontent input").change(function(){
        var leadid = $("#leadid").val();
        submitcustom(leadid);
        // populatescript();
    });

    $("#otherinfo").accordion("resize");
    $("#otherinfo2").accordion("resize");
    if (ct > 0) {
        $("#otherinfo2").show();
        $("#otherinfo2").accordion("resize");
    }
}

var running = 0;
function checkforcalls()
{
if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
	{
		if (running == 0)
		{
		if (clicked ==1)
			{
			running = 1;
			astatus = 'checking';
			clearfields();
			checking = 0;
			clearfields();
			var sId = '<?=$userid;?>';
			showb('pause');
			
                        $.ajax({
                            url: urlcheck + escape(sId),
                            success: handleHttpResponse
                        });
			}
		else 
			{
			checking = 1;
			setTimeout('checkforcalls();',500);
			}
		}
	}
}

function toggledial() {
	var uid = '<?=$userid;?>';
	if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
	{
	if (clicked == 0) 
		{
		//document.getElementById('dialbutton').style.backgroundImage='url(images/dial_.jpg)';
		
                $.ajax({
                    url:  url + '?uid=' +uid+'&act=doactive',
                    success: handleHttpResponse
                });
		}
	else
		{
		//document.getElementById('dialbutton').style.backgroundImage='url(images/dial.jpg)';
		$.ajax({
                    url:  url + '?uid=' +uid+'&act=dopause',
                    success: handleHttpResponse
                });
                // gettags();
		}
	}
}
<? 
//main function bank  
?>
function hideothers(divId)
{
	var maindivs = new Array('upper','scheds','stats');
	for (var i = 0; i < maindivs.length; i++)
		{
			if (divId != maindivs[i])
				{
					document.getElementById(maindivs[i]).style.display = "none";
				}
			else {
					document.getElementById(maindivs[i]).style.display = "block";
			}
		}
}

function acceptsched(schedid)
{
	http.open("GET", url+"?act=acceptsched&schedid="+schedid, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('schedscreen').innerHTML=resp
			}
		else
			{
			document.getElementById('schedscreen').innerHTML = "Please wait."+ct;
			ct +="..";
			}
		};
	http.send(null);
}
function rejectsched(schedid)
{
	http.open("GET", url+"?act=rejectsched&schedid="+schedid, true);
	http.onreadystatechange = function(){
		if (http.readyState == 4)
			{
				var resp = http.responseText;
				document.getElementById('schedscreen').innerHTML=resp
			}
		else
			{
			document.getElementById('schedscreen').innerHTML = "Please wait."+ct;
			ct +="..";
			}
		};
	http.send(null);
}
var gds = false;
function getusersched(dt,ui)
	{
		cd = document.createElement("div");
		cd.id = "daysched";
		cd.style.width = "800px";
		cd.style.position ="absolute";
		cd.style.border = "1px #CCC solid";
		cd.style.left = "50px";
		cd.style.top = "100px";
		cd.style.backgroundColor = "#fff";
		cd.style.height = "400px";
		cd.style.fontFamily = "Tahoma";
		cd.style.fontSize = "8pt";
		cd.style.padding = "10px";
		cd.innerHTML = "<b> Agents scheduled for "+dt+"</b><br>";
		cd.innerHTML += "<div style=\"position:absolute; left:775px; top:10px;\"><a href=\"#\" onclick=\"cleardaysched()\">Close</a></div>";
		cd.innerHTML += '<div style="position:absolute; left:15px; top:30px;"><a href="#" onclick="getds(\''+dt+'\')">View</a> | <a href="#" onclick="addsched(\''+dt+'\')">Add Agent</a></div>';
		cd.innerHTML += '<div id=schedscreen style="background-color: #fff; padding:5px; width: 790px; height: 340px; position:absolute; top:50px; left:10px; border:1px #ffc solid;">Loading schedule. Please wait.</div>';
		document.body.appendChild(cd);
		getds(dt);
		gds = true;
	}
function cleardaysched()
	{
		if (gds == true)
			{
			var ds = document.getElementById('daysched');
			ds.parentNode.removeChild(ds);
			//getapp('schedules');
			gds = false;
			}
	}
function getds(dt)
	{
	var ct = "..";
	document.getElementById('schedscreen').innerHTML = '';
	http.open("GET", url+"?act=getsched&date="+dt+"&user="+userid, true);
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
	

        
		function hangup() {
			//stoprecording();
			if (dialmode == 'progressive')
			{
			astatus = 'hanged';
                        var sId = document.getElementById('leadid').value;
			var uId = '<?=$userid;?>';
                        $.ajax({
                            url: url + '?uid=' + uId + '&act=hangup&lid=' + escape(sId),
                            success: function () {
                                disableb('hbb');
				enableb('dbb');
                               // enableb('cbtab');
                                enablecbclick();
                                enableb('nlbutton');

                            }
                        });
            
			}
			else
			{
			var sId = document.getElementById('leadid').value;
			var uId = '<?=$userid;?>';
                        $.ajax({
                            url: url + '?uid=' + uId + '&act=hangup&lid=' + escape(sId),
                            success: function () {
                                astatus = 'hanged';
                                disableb('hbb');
				enableb('dbb');
                                enableb('nbb');
                            }
                        });
            
			}
        } 
		function next() {     
            var sId = document.getElementById('leadid').value;
			var uId = '<?=$userid;?>';
                        $.ajax({
                            url: url + '?dialmode='+dialmode+'&uid=' + uId + '&act=next&lid=' + escape(sId),
                            success: handleHttpResponse
                        })
			subformvalues();
			
        } 
		function subformvalues() {
			
            // 03/06 - prevlead() starts by activating main panel first
            var t =  Ext.ComponentMgr.get("maintabpanel");
            t.activate(0);

            var thi = document.getElementById('disposition').selectedIndex;
			var sId = document.getElementById('leadid').value;
			if (thi == 0 && sId !=0 && astatus != 'cbview')
			{
    			dispose(subformvalues);
			}
			else
			{
			
    			var uId = '<?=$userid;?>';
    			if (dialmode == 'progressive' && sId == 0)
    			{
                    if (dialactions.next == false)
                    {
                        dialactions.next = true;
                        $.ajax({
                        url: url + '?dialmode='+dialmode+'&uid=' + uId + '&act=next&lid=' + escape(sId),
                            success: function(resp){
                                dialactions.next = false;
                                window.astatus = 'dialing';
                                handleHttpResponse(resp);
                                //showrecordcontrol();
                            },
                            error: function(){
                              dialactions.next = false;  
                            }
                        })
                    }
                }
    			else
    			{
        			if (newleadid != 0) {sId = newleadid;}
        			newleadid = 0;
                    window.astatus = 'dialing';
                    //hiderecordcontrol();
        			submitter('submit', handleHttpResponse);
    			}
			}
                        
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

function exitapp(resp) {
   	cleanexit = true;
	validNavigation = true;

	console.log("exitapp() validNavigation: "+validNavigation);
	setTimeout("window.location=('../index.php?act=logout&uid=<?=$userid;?>')",1000);	
}

function clearfields() {
		jQuery("input[type=text]").val('');
                jQuery("#leadid").val('');
                jQuery("#listid").val('');
                jQuery("textarea").val('');
                jQuery("#leadid").val('0');
                $("#override_pid").val('');
		document.getElementById('disposition').selectedIndex='0';
                $("#otherinfo2").hide();
                $("#notes").html('');
                $("#listiddisplay").html('');
                $("#previousdispo").html('');
	}


function newlead()
	{
	if (dialmode == 'predictive' && clicked == 1)
	{
		alert("Please Pause First!")
	}
	else if (dialmode == 'blended' && clicked == 1)
	{
		alert("Please Pause First!")
	}
	else if (dialmode == 'inbound')
	{
		alert("This function is disabled when Inbound Campaign")
	}
	else
	{
	
	
	var sId = document.getElementById('leadid').value;
	var thi = document.getElementById('disposition').selectedIndex;
	if (thi == 0 && sId !=0 && astatus != 'cbview')
			{
			dispose(newlead);
			}
	else
	{
        astatus = 'newlead';
        disableb('hbb');
	enableb('dbb');
	var uId = '<?=$userid;?>';
	
	if (newleadid != 0) {sId = newleadid;}
	newleadid = 0;
	submitter('newlead', clearfields);
	}
	}
	}

var theci = 0;
function settheci(slotid)
	{
		theci = slotid;
		//document.getElementById('calendar').value = "0000-00-00";
	}
function submitcustom(leadid)
    {
        var cdata = '{';
        var i = 0;
        $("#othercontent input").each(function(){
            if (i > 0)
                {
                    cdata +=',';
                }
            var nm = $(this).attr("name");
            var vl = $(this).val();
            cdata +='"'+nm+'":"'+vl+'"';
            i++;
        });
        cdata += '}';
        $.ajax({
            url:"ajax.php?act=savecustomdata",
            type: "POST",
            data: {"data":cdata, "leadid":leadid}
        });
    }
function submitter(action, reciver)
	{
        if (dialactions.submit == false)
            {
	dialactions.submit = true;
        var uId = '<?=$userid;?>';
	var nci = "";
	if (theci > 0)
		{
			nci = "&ci="+theci;
		}
	theci=0;
        stoprecording();
	var thi = document.getElementById('disposition').selectedIndex;
	var sId = document.getElementById('leadid').value;
                
        submitcustom(sId);
	var cname = document.getElementById('cname').value;
	var cfname = document.getElementById('cfname').value;
	var clname = document.getElementById('clname').value;
	var company = document.getElementById('company').value;
	var address1 = document.getElementById('address1').value;
	var address2 = document.getElementById('address2').value;
	var city = document.getElementById('city').value;
	var state = document.getElementById('state').value;
	var comments = document.getElementById('comments').value;
	var override_pid = $("#override_pid").val();
	var phone = document.getElementById('phone').value;
	var altphone = document.getElementById('altphone').value;
	var zip = document.getElementById('zip').value;
	var chu = document.getElementById('disposition').options[thi].value;
	var disposition = chu;
        if (disposition.length < 1) {
            disposition = $("#previousdispo").html();
            nci = nci + '&notcalled=1';
        }
	var sic = document.getElementById('sic').value;
	var listid = document.getElementById('listid').value;
	var dtime = document.getElementById('calendar').value;
	var email = document.getElementById('email').value;
	var post = document.getElementById('positiontitle').value;
	var mobile = document.getElementById('mobile').value;
	var title = document.getElementById('title').value;
	var industry = document.getElementById('industry').value;
        var country = document.getElementById('country').value;
	var taction = '?dialmode='+dialmode+'&override_pid='+ override_pid +'&uid=' + uId + '&act='+ action +'&lid=' + escape(sId) + '&cname=' + escape(cname) + '&cfname=' + escape(cfname) + '&clname=' + escape(clname) + '&company=' + escape(company) + '&address1=' + escape(address1) + '&address2=' + escape(address2) + '&city=' + city + '&state=' + state + '&zip=' + zip + '&comments=' + escape(comments)  + '&disposition=' + escape(disposition) + '&phone=' + phone + '&cal=' + dtime + '&email=' + escape(email) + '&title=' +escape(title) + '&list=' + escape(listid) + '&sic=' +escape(sic) +'&callid='+ getcallid+'&altphone='+altphone+'&positiontitle=' + escape(post) + '&mobile='+ mobile+ '&industry='+ escape(industry) + nci;
	jQuery.ajax({
            url: "ajax.php"+taction,
            success:function(resp){
                clearfields();
                dialactions.submit = false;
                reciver(resp);
                
            },
            error: function() {
                dialactions.submit = false;
            }
        });
            }
	}
function previous_session()
{
    $.ajax({
        url: 'ajax.php?act=getprevsession',
        success: function(resp){
            if (resp != 'none')
                {
                    astatus = 'prevlead';
                    enableb('dbb');
                    populate(resp);
                }
        }
    });
}
function prevlead()
	{
        var t =  Ext.ComponentMgr.get("maintabpanel");
        t.activate(0);
	if (dialmode == 'progressive')
	{
	
	var sId = $("#leadid").val();
	var thi = $("#disposition")[0].selectedIndex;
	if (thi == 0 && sId > 0 && astatus != 'cbview')
			{
                        dispose(prevlead);
			}
	else 
	{
	astatus = 'preview';
	var uId = '<?=$userid;?>';
	if (dialmode == 'progressive' && sId == 0)
				{
				
				$.ajax({
                            url: url + '?dialmode='+dialmode+'&uid=' + uId + '&act=prevlead&lid=' + escape(sId),
                            success: handleHttpResponse
                        })
				
				}
	else submitter('prevlead',handleHttpResponse);
	
	}
	}
        }
function createdateinput()
    {
    jQuery('#datetd').show();   jQuery('#dodisposeapplydate').hide();
          $("#accordion").accordion("resize");
    }
function cleardateinput()
    {
    jQuery('#datetd').hide();   jQuery('#dodisposeapplydate').hide();
        $("#accordion").accordion("resize");
        
    }
function althangup()
	{
            //stoprecording();
            var sId = document.getElementById('leadid').value;
			var uId = '<?=$userid;?>';
            http.open("GET", url + '?uid=' + uId + '&act=hangup&lid=' + escape(sId), true);
			http.onreadystatechange = function(){
					if (http.readyState == 4) {
              			if(http.status==200) {
									astatus = 'hanged';
							document.getElementById('althang').style.display = 'none';
							document.getElementById('altbutton').style.display = 'inline';
			  				}
			 			}
				};
            http.send(null);
	}
function altdial()
	{
	//alert(astatus);
	//if (astatus == 'paused' || astatus=='preview' || astatus=='newlead' || astatus =='hanged')
        if (astatus != 'dialing')
	{

	astatus = 'dialing';

	var sId = document.getElementById('altphone').value;
	var lis = document.getElementById('listid').value;
	var leadid = document.getElementById('leadid').value;
	var uId = '<?=$userid;?>';
    http.open("GET", url + '?uid=' + uId + '&act=mandial&phone=' + sId + '&list=' + lis + '&leadid=' +leadid, true);
	http.onreadystatechange = function() {
			 if (http.readyState == 4) {
              if(http.status==200) {
				document.getElementById('althang').style.display = 'inline';
				document.getElementById('altbutton').style.display = 'none';
			  }
			 }
		};
    http.send(null);
	first = 0;
	}
	}
function dodial()
	{
	//alert(astatus);
	//if (astatus == 'paused' || astatus=='preview' || astatus=='newlead' || astatus =='hanged')
        if (astatus != 'dialing')
	{

	astatus = 'dialing';
	var sId = document.getElementById('phone').value;
	var lis = document.getElementById('listid').value;
	var leadid = document.getElementById('leadid').value;
	var uId = '<?=$userid;?>';
    http.open("GET", url + '?uid=' + uId + '&act=mandial&phone=' + sId + '&list=' + lis + '&leadid=' +leadid, true);
	http.onreadystatechange = function() {
			 if (http.readyState == 4) {
              if(http.status==200) {
					enableb('hbb');
					disableb('dbb');
                                        //disableb('cbtab');
                                        disablecbclick();
                                        disableb('nlbutton');
                                        showrecordcontrol();
                                        if (recordingmode == 'started')
                                            {
                                                startrecording();
                                            }
                                        if (dialmode == 'predictive')
                                            {
                                                disableb('nbb');
                                            }
                                        checkdialstate();
			  }
			 }
		};
    http.send(null);
	first = 0;
	}
	}
function team_cbdial(led)
{
    //lock then do cbdial
    $.ajax({
        url:'ajax.php?act=lockteamcb&leadid='+led,
        success: function(resp)
        {
            if (resp == '1')
                {
                    cbdial(led);
                }
            else alert('Team callback failed.');
        }
    });
}
function sim_cbdial(led)
{
    if (astatus=='preview')
        {
            astatus = 'cbview';
        }
    cbdial(led);
}
function cbdial(led)
	{
	if (cbable)
            {
        if (astatus == 'dialingcb')
		{
		alert("Hangup the Call first!");
		}
         var thi = document.getElementById('disposition').selectedIndex;
	var sId = document.getElementById('leadid').value;
        if (thi == 0 && sId !=0 && astatus != 'cbview')
			{
			var t =  Ext.ComponentMgr.get("maintabpanel");
                        t.activate(0);
                        dispose(cbdial,led);
			}
        else {
    if (astatus == 'paused' || astatus =='hanged' || astatus == 'preview' || astatus == 'cbview' || astatus == 'newlead')           
	{
	//astatus = 'dialingcb';
	var uId = '<?=$userid;?>';astatus = 'cbview';
	submitter("getsearchdetails&user="+uId+"&leadid="+led, function(resp){
		
                    enableb('dbb');
                    showb('nbb');
                    try {
                    hideb('start');
                    }
                    catch (e)
                    {
                    }
                    checkingnew = 21;
                    Ext.getCmp('maintabpanel').activate(0);
                    populate(resp);
                    
		});
	
	}
	}
        }
	}

function qptdial(led) {
    astatus = 'prevlead';
    if (cbable) {
        if (astatus == 'dialingcb') {
            alert("Hangup the Call first!");
        }
        var thi = document.getElementById('disposition').selectedIndex;
        var sId = document.getElementById('leadid').value;
        if (thi == 0 && sId != 0 && astatus != 'cbview') {
            var t = Ext.ComponentMgr.get("maintabpanel");
            t.activate(0);
            dispose(cbdial, led);
        } else {
            if (astatus == 'paused' || astatus == 'hanged' || astatus == 'preview' || astatus == 'cbview' || astatus == 'newlead' || astatus == 'prevlead') {
                var uId = '<?=$userid;?>';
                astatus = 'prevlead';
                submitter("getsearchdetails&user=" + uId + "&leadid=" + led, function(resp) {
                    enableb('dbb');
                    showb('nbb');
                    try {
                        hideb('start');
                    } catch (e) {}
                    checkingnew = 21;
                    Ext.getCmp('maintabpanel').activate(0);
                    populate(resp);
                });
            }
        }
    }
}
function dialcb(pone)
	{
	var aphone =document.getElementById('phone').value;
	if (pone != aphone && aphone != '')
		{
		newlead();
		}
	else 
	{
	contcb(pone);
	}
	}
function contcb(pone)
	{
	if (astatus == 'dialingcb')
		{
		alert("Hangup the Call first!");
		}
	if (astatus == 'paused' || astatus=='preview' || astatus=='newlead' || astatus =='hanged')
	{
	astatus = 'dialingcb';
	var uId = '<?=$userid;?>';
	$.ajax({
                            url: url + '?uid=' + uId + '&act=mandial&phone=' + pone,
                            success: handleHttpResponse
                        })

	}
	}
function exitdial() {
	var sId = $("#leadid").val();
	if (sId != 0 || sId != '')
	{
	var thi = document.getElementById('disposition').selectedIndex;
	if (thi == 0 && sId !=0 && astatus != 'cbview')
			{
			dispose(exitdial);
			}
	else
	{
	var uId = '<?=$userid;?>';
	
	if (newleadid != 0) {sId = newleadid;}
	newleadid = 0;
	submitter('exitdial',exitapp);
	}
	
	}
	else {
	var uId = '<?=$userid;?>';
        $.ajax({
            url: 'ajax.php?dialmode='+dialmode+'&uid=' + uId + '&act=exitdial',
            success: exitapp
        });
	
	}
	
}
var http = getHTTPObject();
var xmlDoc;
var clicked = 0;
var checking = 1;
var first = 1;
var astatus = 'paused';
var cb= '1';
var getcallid = 0;
function urlencode(str) {
return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}
function clearscript()
	{
		var nodes;
		
		if (document.getElementById("scriptbod").elements)
		{
		nodes = document.getElementById("scriptbod").elements;
		for(i = 0;i < nodes.length;++i)
		{
		nodes[i].value= '';
		}
		}
	}
function reloadscript()
{
	var n = 0;
	var leadid = document.getElementById("leadid").value;
        var sc = Ext.ComponentMgr.get("cstab");
        
	sc.load({
            url:'script.php', 
            params: 'projid='+projid+'&leadid='+leadid, 
            scripts: true,
            callback: function(e){
                $("input.fi").change(function(){
                    savescript($(this).attr("name"))
                });
                $("select.fi").change(function(){
                    savescript($(this).attr("name"))
                });
                jQuery("#scriptbod").accordion();
                jQuery("#scriptbod").accordion("resize");
                jQuery("#cinfo").accordion();
                populatescript();
            }
        });
        
        
}
function savescript(field)
	{
	var leadid = $("#leadid").val();
        var val = $("[name='"+field+"']").val();
        $.ajax({
            url: 'ajax.php?act=scriptdata',
            type: 'POST',
            data: {
                "field":field,
                "value":val,
                "leadid":leadid
            }
        });
        
	}

function newnextpage(projectid,parentid,_cfname)
{
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    var script_form = $("#scriptbod").serializeArray();
    var leadid = $("#leadid").val();
    $("[name='"+_cfname+"']").val($("#scriptbod").find("[name='"+_cfname+"']").val());
    
    $.ajax({
        url: "script.php?act=getnextpage",
        type:"POST",
        success:function(resp){
            $("#page"+parentid).append(resp);
            jQuery("#scriptbod").accordion("resize");
            // populatescript();
        },
        data: {
            "leadid":leadid,
            "parentid":parentid,
            "projid":projectid,
            "script":script_form
        }
    })
}

function nextpage(projectid,parentid)
{
	/***************************/
	/* ADDED BY Vincent Castro */
	/***************************/
    var script_form = $("#scriptbod").serializeArray();
    var leadid = $("#leadid").val();
    $.ajax({
        url: "script.php?act=getnextpage",
        type:"POST",
        success:function(resp){
            $("#page"+parentid).append(resp);
            jQuery("#scriptbod").accordion("resize");
            // populatescript();
        },
        data: {
            "leadid":leadid,
            "parentid":parentid,
            "projid":projectid,
            "script":script_form
        }
    })
}
function populatescript()
{
    var leadid = $("#leadid").val();
    $.ajax({
        url: "script.php?act=getscriptdata",
        type:"POST",
        dataType: 'json',
        success:function(resp){
            for (var i = 0;i<resp.length;i++)
            {
            $("[name='"+resp[i].name+"']").val(resp[i].value);
            }
        },
        data: {
            "leadid":leadid
        }
    })
}
function getCallbacks()
	{
	var uId = '<?=$userid;?>';
	http.open("GET", url + '?uid=' + uId + '&act=getcallbacks', true);
	http.onreadystatechange = writecalls;
    http.send(null);
	}
function writecalls()
	{ 
     if (http.readyState == 4) {
              if(http.status==200) {
              	var results=http.responseText;
				cb = results;
				}
			}
	}
function cbhi(dd)
	{
		if (cbable) dd.style.backgroundColor="#B1D0F5";
	}
function cbout(dd)
	{
		dd.style.backgroundColor="#fff";
	}
function dloadscript(urls)
{
   var e = document.createElement("script");
   e.src = urls;
   e.type="text/javascript";
   document.getElementsByTagName("head")[0].appendChild(e); 
}
function showupdatepage(statusid)
	{
		$.ajax({
                    url:"ajax.php?act=getstatusoption&statusid="+statusid,
                    success: function(resp){
                        if (resp != 'none') inbrowser(resp);
                    }
                });

	}

function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    return false
		 }

 		 return true					
	}
function warningmess(emessage)
	{
		$.blockUI({ 
            			message: '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>'+emessage+'</p></div>', 
           			 	fadeIn: 700, 
            			fadeOut: 700, 
            			timeout: 5000, 
            			showOverlay: false, 
            			centerY: false, 
            			css: { 
                			background: 'transparent',
							fontSize: '10pt',
                			top: '70px', 
                			left: '', 
                			right: '10px', 
                			border: 'none', 
                			padding: '5px', 
                			width: '200px',
                			'-webkit-border-radius': '10px', 
                			'-moz-border-radius': '10px', 
                			color: '#fff',
					cursor: 'pointer'
							
            			} 
        			}); 
	}
function inbrowser(nurl)
{	
	window.open(nurl);
}
function dispose(cb,cbpar)
{
    $("#callresults").dialog({
                resizable: false,
                title: "Disposition",
                show: 500,
                close: returncallresults
    });
    var ct = 0;
    $("#dodispose").show();
    $("#dodispose").click(function(){
        $("#callresults").dialog("destroy");
        console.log("ct:"+ct);
        if (ct == 0)
            {
            returncallresults();
            
            cb(cbpar);
            }
        ct++;
    });
}
function returncallresults()
{
    $("#callresults").dialog("destroy");
    $("#callresults").appendTo("#maininfocontent");
    $("#callresults").show();
    $("#dodispose").hide();
    $("#accordion").accordion("resize");
}
function addnotes()
{
    var l = $("#leadid").val();
    var no = $("#notesinput").val();
    if (no.length > 1)
     
        {$("#notesinput").val('');
        $.ajax({
            url:'ajax.php?act=addnote&leadid='+l,
            type:'post',
            data:{"note":no},
            success: function(resp){
                var notes = jQuery.parseJSON(resp);
                writenote(notes);
            }
        });}
}
function writenote(noteobj)
{
    if (noteobj)
        {
    var content = '';
    for(var i = noteobj.length;i > 0 ;i--)
        {
              var y= i - 1;
              var n = noteobj[y];
              d = epochtoutc(n.timestamp);
              var dt = d.toLocaleString();
              content += "<b>"+n.user+"</b> ["+dt+"]:<br />"+n.message+"<br /><hr /><br />";
              $("#notes").html(content);
        }
        }
}
function epochtoutc(epoch)
{
    var utcSeconds = epoch;
    var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds(utcSeconds);
   
    return d;
}
function startrecording()
{
    $.ajax({
        url: 'ajax.php?act=startrecording',
        success: function(resp){
     $("#recordingcontrol").html('<a href="#" onclick="stoprecording()" id="recbutton">Stop Recording</a>');
     $("#recbutton").button({icons:{primary:"recstop"}});
    }});
}
function stoprecording()
{
    $.ajax({
        url: 'ajax.php?act=stoprecording',
        success: function(resp){
       $("#recordingcontrol").html('<a href="#" onclick="startrecording()" id="recbutton">Start Recording</a>');
       $("#recbutton").button({icons:{primary:"recstart"}});
    }});
}
function getnotes()
{
    $("#notes").html('');
    var l = $("#leadid").val();
    $.ajax({
        url: 'ajax.php?act=getnotes&leadid='+l,
        success: function(resp){
        var notes = jQuery.parseJSON(resp);
        writenote(notes);
    }});
    $("#notesinput").keypress(function(e) {
    if(e.which == 13) {
        addnotes();
    }
});
}
var that = false;
var checkingnew = 0;
function newlead2()
	{
	var ld = document.getElementById('leadid').value;
	var dp = document.getElementById('disposition').selectedIndex;
	if (dialmode == 'predictive' && clicked == 1)
		{
		alert("Please Pause First!")
		}
	else if (dialmode == 'blended' && clicked == 1)
		{
		alert("Please Pause First!")
		}
	else
		{
		if (ld > 0 && dp == 0 && astatus != 'cbview')
			{
			dispose(newlead2);
			}
		else {
                        if ($("#dialogcontainer").dialog("isOpen")== true) $("#dialogcontainer").dialog("close");
			astatus = 'newlead';
			$.ajax({
                            url: 'ajax.php?act=newlead',
                            success: function(resp){
                                $("#dialogcontainer").html(resp);
                                $("#dialogcontainer").dialog({
                                    width: 800,
                                    maxHeight: 400
                                });
                            }
                        });
			}
		}
	}
function rejectinboundcall(callid)
{
    $("#dialogcontainer").dialog("close");
    $.ajax({
        url: 'ajax.php?act=rejectinboundcall&callid='+callid,
        success: function(resp){
            }
    });
}
function answerinboundcall(callid)
{
    // $("#dialogcontainer").dialog("close");
    $.ajax({
        url: 'ajax.php?act=answerinboundcall&callid='+callid,
        success: function(resp){
            if (resp != 'Pickup Failed')
            {
                inbound_uselead(resp);
                // alert("Call Answered");
            }
            else
            {
                // alert(resp);
                alert("Call was put back to queue.");
                $("#dialogcontainer").dialog("close");
            }
        }
    });
}
function newinboundcall()
{
    var ld = document.getElementById('leadid').value;
    var dp = document.getElementById('disposition').selectedIndex;
    var phone = document.getElementById('phone').value;

    if (ld > 0 && dp == 0 && astatus != 'cbview' && phone != 'anonymous')
			{
			dispose(newinboundcall);
			}
    $.ajax({
        url: 'ajax.php?act=newinboundcall',
        success: function(resp){
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: 'New Inbound Call',
                width: 400,
                maxHeight: 400,
                modal: true,
                closeOnEscape: false,
                open: function(event, ui) { $(".ui-dialog-titlebar-close", ui.dialog || ui).hide(); }
                
            });
        }
    });
}
function newinboundcallmanual()
{
    var ld = document.getElementById('leadid').value;
    var dp = document.getElementById('disposition').selectedIndex;
    var phone = document.getElementById('phone').value;

    if (ld > 0 && dp == 0 && astatus != 'cbview' && phone != 'anonymous')
            {
            dispose(newinboundcallmanual);
            }
    $.ajax({
        url: 'ajax.php?act=newinboundcallmanual',
        success: function(resp){
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: 'New Inbound Call',
                width: 400,
                maxHeight: 400,
                modal: true,
                closeOnEscape: false,
                open: function(event, ui) { $(".ui-dialog-titlebar-close", ui.dialog || ui).hide(); }
                
            });
        }
    });
}

function createnewentry(phone)
	{
	$("#dialogcontainer").dialog("close");
	submitter("createnewentry&user="+userid+"&newphone="+phone, function(resp){
				enableb('dbb');
				showb('nbb');
				if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
				{
				hideb('start');
				}
                                if (astatus == 'New Inbound Call')
                                {
                                    disableb('dbb');
                                    enableb('hbb');
                                    hideb('nbb');
                                }
				checkingnew = 21;
				populate(resp);
		});

	}
function createnewinboundentry(phone)
	{
	$("#dialogcontainer").dialog("close");
	submitter("createnewinboundentry&user="+userid+"&newphone="+phone, function(resp){
				
				hideb('start');
				disableb('dbb');
                                enableb('hbb');
                                showb('nbb');
                                checkingnew = 21;
				populate(resp);
		});

	}
function nlsearch(inbound) {
	var phone = document.getElementById('nlphone').value;
	$.ajax({
            url: "ajax.php?act=nlsearch&user="+userid+"&phone="+phone+"&inbound="+inbound,
            success: function(resp){
                $("#nlmain").html(resp);
                $(".datatabs").dataTable();
                var d = $("#dialogcontainer").dialog();
                 d.dialog("option", "width", 700);
            }
        });
}
function nlgetdetails(leadid) {
            $.ajax({
                url: "ajax.php?act=nlgetdetails&user="+userid+"&leaid="+leadid,
                success: function(resp){
                    $("#dialogcontainer").dialog("close");
                    if (that != false) that.innerHTML = '';
                    document.getElementById(leadid).innerHTML=resp;
                    that = document.getElementById(leadid);
                }
            });
}
function uselead(leadid)
	{
		submitter("getsearchdetails&user="+userid+"&leadid="+leadid, function(resp){
				enableb('dbb');
				showb('nbb');
				if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
				{
				hideb('start');
				}
				checkingnew = 21;
                                $("#dialogcontainer").dialog("close");
				clearfields();
				populate(resp);
			
		});
                
		
	}
function inbound_uselead(leadid)
{
    submitter("inbounduselead&user="+userid+"&leadid="+leadid, function(resp){
                                astatus='Inbound Call';
				enableb('hbb');
				showb('nbb');
				hideb('pause');
				checkingnew = 21;
                                $("#dialogcontainer").dialog("close");
				clearfields();
				populate(resp);
			
		});
}
var chats = new Array();
function converse(withuid,withname)
{
    //startChatSession();
    if (isChatEnabled)
    {
		 jQuery.ajax({
	        url: "../messaging.php",
	        success: dresponsehandler 
	    });
	}

}
function loadchattab(s)
{
 jQuery(".jbut").button();
 jQuery(".datatabs").dataTable();
}
function refreshonlineusers()
{
    var atab = Ext.getCmp('maintabpanel').getActiveTab();
    if (atab.id=='chattab' && isChatEnabled)
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
</script>
<?
include "../converse.php";
?>
<?php

echo "<script>";
echo "var projid = '".$session->projectid."';";
echo "var dialmode = '".$session->project["dialmode"]."';";
echo "var emailtemplate = '$withemail';";
echo "var booking = '".$session->project["booking"]."';";
echo "var projname = '".$session->project["projectname"]."';";
echo "</script>";
?>

<script>
var but_hang;
var but_hangdial;
var navbar;
var exitstring;
var cleanexit = false;
function disableb(comp)
{
    Ext.getCmp(comp).disable();
}
function enableb(comp)
{
     Ext.getCmp(comp).enable();
}
function showb(comp,newt)
	{
	navbar.items.get(comp).show();
	}
function hideb(comp,newt)
	{
	navbar.items.get(comp).hide();
	}
Ext.onReady(function()
{
navbar = new Ext.Toolbar({
		renderTo: 'navb',
		autowidth: true
		});
if (dialmode == 'progressive')
{
	navbar.addButton([
		new Ext.Toolbar.MenuButton({id: 'nbb', text: 'NEXT', handler: subformvalues, cls:'x-btn-text-icon', icon: 'icons/arrow_right.png'}),
		new Ext.Toolbar.MenuButton({text: 'PREVIEW NEXT', handler: prevlead, cls:'x-btn-text-icon', icon: 'icons/doc_page.png'}),
		new Ext.Toolbar.MenuButton({text: 'DIAL', handler: dodial, cls:'x-btn-text-icon', icon: 'icons/dial.png', id: 'dbb', disabled:true}),
		new Ext.Toolbar.MenuButton({id: 'hbb', text: 'END CALL', handler: hangup, cls:'x-btn-text-icon', icon: 'icons/disconnect.png', disabled:true}),
		new Ext.Toolbar.MenuButton({text: 'NEW LEAD', handler: newlead2, cls:'x-btn-text-icon', icon: 'icons/add.png',id:'nlbutton'}),
		new Ext.Toolbar.MenuButton({id: 'nottrackerEXIT', text: 'EXIT', handler: exitdial, cls:'x-btn-text-icon', icon: 'icons/cancel.png'})
	]);
}
if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
{
	
	navbar.addButton([
		new Ext.Toolbar.MenuButton({text: 'START', handler: toggledial, cls:'x-btn-text-icon', icon: 'icons/accept.png', id: 'start'}),
		new Ext.Toolbar.MenuButton({text: 'PAUSE', handler: toggledial, cls:'x-btn-text-icon', icon: 'icons/stop.png', id: 'pause', hidden: true}),
		new Ext.Toolbar.MenuButton({text: 'NEXT', handler: subformvalues, cls:'x-btn-text-icon', icon: 'icons/arrow_right.png', hidden:true, id:'nbb'}),
		new Ext.Toolbar.MenuButton({text: 'DIAL', handler: dodial, cls:'x-btn-text-icon', icon: 'icons/dial.png', id: 'dbb', disabled: true}),
		new Ext.Toolbar.MenuButton({text: 'END CALL', handler: hangup, cls:'x-btn-text-icon', icon: 'icons/disconnect.png', id: 'hbb', disabled: true}),
		new Ext.Toolbar.MenuButton({text: 'NEW LEAD', handler: newlead2, cls:'x-btn-text-icon', icon: 'icons/add.png', id: 'nlbutton'}),
		new Ext.Toolbar.MenuButton({id: 'nottrackerEXIT', text: 'EXIT', handler: exitdial, cls:'x-btn-text-icon', icon: 'icons/cancel.png'})
	]);	
}

<?php 
	include("timetracker/tteventspulldown-js.php");
?>

var mainpanel = new Ext.Panel({
	applyTo: 'upper',
	frame: true,
	title: 'BlueCloudTalk',
	autoWidth: true,
	autoHeight: true,
	layout: 'absolute',
	footer: false
	});
var ctab = new Ext.Panel({title: 'Callbacks', id:'cbtab', autoLoad: {url:'ajax.php', params: 'act=getcallbacks', callback: function(){
            jQuery(".jbut").button();
            jQuery(".sortable").tablesorter();
}}, listeners: {activate: loadcallbacks}});
var simtab = new Ext.Panel({title: 'Related Contacts', id:'simtab', autoLoad: {url:'ajax.php', params: 'act=getsim', callback: loadsimtab}, listeners: {activate: loadsimtab}});
var chattab = new Ext.Panel({title: 'Chat', id:'chattab', contentEl:'chatel', listeners: {activate: function(){refreshonlineusers();$("#chatel").show();}}});
var tabs = new Ext.TabPanel({
        renderTo: 'maincont',
		id: 'maintabpanel',
        width: '100%',
		height: 500,
        activeTab: 0,
        frame:true,
        defaults:{autoScroll: true},
        items:[
            {contentEl:'custominfo', title: 'Customer Information'},
			{ //script tab
                            autoLoad:{
                                url:'script.php', 
                                params: 'projid='+projid,
                                callback: function() {
                                    reloadscript();
                                    
                                }
                            }, 
                            title: 'Script Form', 
                            cls: 'scriptconf',
                            id: 'cstab',
                            listeners: {
                                activate: function() {
                                    jQuery("#scriptbod").accordion("resize");
                                     jQuery("#cinfo").accordion("resize");
                                }
                            }
                        },
			ctab,
			{
                            title: 'Campaign Details : '+projname, 
                            autoLoad: {
                                url:'ajax.php', 
                                params: 'act=getdetails', 
                                callback: function(){
                                    jQuery("a.viewdoc").fancybox({width:800, height:600});
                                    jQuery("a.viewimg").fancybox({width:800, height:600, type: 'image'});
                                    jQuery(".datatabs").dataTable();
                                }}
                            
                        },simtab,chattab
			
        ]
    });
if (emailtemplate > 0)
	{
		var ec = new Ext.Panel({title: 'Email Client',  listeners: {activate:gettemplate}});
		tabs.add(ec);
	}
if (booking == 1)
	{
		var bcal = new Ext.Panel({title: 'Booking Calendar', html:'<iframe height="100%" width="100%" src="../modules/appbook.php?cid=<?=$clientid;?>" frameborder="0"></iframe>'});
		tabs.add(bcal);
	}

<?php 
	include("agentuisettings/uisetopts-js.php");
?>

// Ext.onReady(function()
});

var cbtab;
function loadsimtab(s)
{
    var simtab = s;
    var leadid = $("#leadid").val();
    simtab.load({url:'ajax.php?leadid='+leadid, params: 'act=getsim', callback: function(){
            jQuery(".jbut").button();
            jQuery(".simdatatabs").dataTable();
            
            jQuery("#simtab .dataTables_length").html('<select id="simbulk" onchange="simbulk()">' + 
                '<option></option><option value="dispose">Dispose</option></select>');
            jQuery('.datetimepicker').datetimepicker({
                                            format: 'Y-m-d H:i'
                                        });
}});
}

function simbulk()
{
    var val = $("#simbulk").val();
    if (val == 'dispose')
        {
            bulkdispose();
        }
    if (val == 'donotcall')
        {
            bulkdnc();
        }
}
function bulkdispose()
{
    $("#bulkdispose").dialog();
}
function dobulkdispose()
{
   var ids = '';    
   var ct = 0;
   $(".simtabtick").each(function(){
    if ($(this).prop('checked'))
    {
        if (ct > 0) ids+= ","; 
        ids+= $(this).val();
        ct++;
    }
    
    });
    var dispo = $("#bulkdisposition").val();
    var cal = $("#bulkcalendar").val();
    $.ajax({
        url: "ajax.php?act=bulkdispose&dispo="+dispo+"&cal="+cal+"&ids="+ids,
        success: function(resp){
            alert(resp);
            
        }
    });
}
function loadcallbacks(sc)
{
	cbtab = sc;
        refreshcallbacks();
}
function refreshcallbacks()
{
	$.ajax({
            url: 'ajax.php?act=getcallbacks',
            success: function(resp){
                $("#cbtab").html(resp);
                 jQuery(".jbut").button();
                jQuery(".sortable").tablesorter();
            }
        });
}
var eclient;

function gettemplate(sc)
{
	var n = 0;
	var ebody = "";
	var mailmerge = JSON.stringify($("input").serializeArray());
	var leadid = document.getElementById("leadid").value;
	var emailadd = document.getElementById("email").value;
	eclient = sc;
	sc.load({url:'emailer.php', params: 'act=gettemplate&templateid='+emailtemplate+'&leadid='+leadid+'&email='+emailadd+'&mailmerge='+mailmerge, scripts: true, callback: demail})
}
function changetemplate(t)
{
	var templateid = t.options[t.selectedIndex].value;
	var name = document.getElementById("cname").value;
	var leadid = document.getElementById("leadid").value;
        var mailmerge = JSON.stringify($("input").serializeArray());
	var emailadd = document.getElementById("email").value;
	eclient.load({url:'emailer.php', params: 'act=gettemplate&templateid='+templateid+'&leadid='+leadid+'&email='+emailadd+'&mailmerge='+mailmerge, scripts: true, callback: demail})
}

function sendemail(tid)
{
	var leadid = document.getElementById("leadid").value;
	var from = document.getElementById("emailfrom").value;
	var to = document.getElementById("emailto").value;
	var subject = document.getElementById("subject").value;
	var message = document.getElementById("emailbody").value;
	var texts = mce.getContent();
	texts = encodeURI(texts);
	texts = encodeURIComponent(texts);
	message = texts;
	var http = getHTTPObject();
	var params = 'tid='+tid+'&act=sendemail&from='+from+'&to='+to+'&subject='+subject+'&uid=<?=$userid;?>&leadid='+leadid+'&message='+message;
	$.ajax({
            url: 'emailer.php',
            type: 'POST',
            data: params,
            success: function(resp){
                alert(resp)
            }
        });
//	http.open("POST", 'emailer.php', true);
	///Send the proper header information along with the request
//	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//	http.setRequestHeader("Content-length", params.length);
//	http.setRequestHeader("Connection", "close");
//	http.onreadystatechange = function(){
//		if (http.readyState == 4)
//			{
//				alert("email sent!");
//			}
//		};
    //http.send(params);
}
var mce;

function demail()
{
if ($("#editable").val() == '0')
    {
        var readonli = 1;
    }
else {
    var readonli = 0;
}
mce = new tinymce.Editor('emailbody',{
					mode: 'textareas', 
					theme: 'advanced',
					// Theme options
                                        readonly: readonli,
					theme_advanced_toolbar_align : "left",
					theme_advanced_toolbar_location : "top",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_buttons1 : "bold, italic, justifyleft, justifycenter, justifyright,justifyfull, bullist,numlist,undo,redo,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor, fontselect, fontsizeselect",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					content_css : "styles/style.css",
                                          remove_script_host: false,
                                        relative_urls : false
					});
			mce.render();
$("#sendemailbut").button();

}
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
                position: 'absolute',
                fontSize: '1em',
                top: '20px', 
                left: '', 
                right: '220px', 
                border: 'none', 
                padding: '5px', 
                width: '200px',
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                color: '#fff',
                                cursor: 'pointer'

        } 
        }); 
        $('.ui-state-error').click(function(){
        $.unblockUI();
        Ext.getCmp('maintabpanel').activate(tab);
        }); 
}
function callbackupdate() {
		$.ajax({
  		url: "ajax.php?act=updatecheck",
  		global: false,
		datatype: 'script',
		success: function(resp){
			var newcbs = parseInt(resp);
			if (newcbs > cbs)
				{
                                    refreshcallbacks();
					cbs = newcbs;
					if (cbs > 1) 
						{
							cbstring = 'Callbacks';
						}
					else cbstring = 'Callback';
					$.blockUI({ 
            			message: '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong>You have <span id="alarmcount">'+cbs+'</span> '+cbstring+'.</p></div>', 
           			 	fadeIn: 700, 
            			fadeOut: 700, 
            			timeout: 0, 
            			showOverlay: false, 
            			centerY: false, 
            			css: { 
                			background: 'transparent',
                                        position: 'absolute',
					fontSize: '1em',
                			top: '20px', 
                			left: '', 
                			right: '220px', 
                			border: 'none', 
                			padding: '5px', 
                			width: '200px',
                			'-webkit-border-radius': '10px', 
                			'-moz-border-radius': '10px', 
                			color: '#fff',
							cursor: 'pointer'
							
            			} 
        			}); 
					$('.ui-state-error').click(function(){
						$.unblockUI();
						Ext.getCmp('maintabpanel').activate(2);
						}); 
				}
                        cbs = newcbs;
			setTimeout("callbackupdate()",60000);
			//alert(ob.callbacks);
			}
	});
	}
function switchproject() {
	var sId = $("#leadid").val();
	if (sId != 0 || sId != '')
	{
		var thi = document.getElementById('disposition').selectedIndex;
		if (thi == 0 && sId !=0)
		{
			dispose(switchproject);
		}
		else
		{
            cleanexit = true;
            validNavigation = true;

			console.log("switchproject() validNavigation: "+validNavigation);
            $("#campswitcher").submit();
		}
	
	}
	else {
            cleanexit = true;
            validNavigation = true;

			console.log("switchproject() validNavigation: "+validNavigation);
            $("#campswitcher").submit();
	
	}
	
}
function changeproj() {
	cleanexit = true;
	validNavigation = true;

	var spid = $("#selectprojectid").val();
	var ext = $("#sextension").val();
	var _userid_ = $("#selectprojectid").attr("title");
	var _act_ = "AGENTLOGIN";
        var inb = '';
        $("#selectinbound option:selected").each(
                function(){
                    inb += '&inb[]='+$(this).val();
                }
                );
	console.log("changeproj() validNavigation: "+validNavigation);
	window.location="index.php?act=changeproj&projid="+spid+"&eyebeam="+ext+inb+"&_USERID_="+_userid_+"&_ACT_="+_act_;
}
var stct = 1;
function lookupcalc(pid)
{
	$.ajax({
  		url: "ajax.php?act=getlookup&sid=<?=session_id();?>",
  		global: false,
		success: function(j){
				var resp = jQuery.parseJSON(j);
				var fs = resp.fields;
				var fields = fs.split(",");
				var rowcount = resp.rowcount;
				var datas = resp.data;
				for (var i = 0; i < rowcount;i++)
					{
						for (var f =0; f < fields.length; f++)
						{
							alert(fields[f] + " = " +datas[i][fields[f]]);
						}
						
						

					}
			}
	});
}
function usecal()
{
        
	$("#cslots").dialog("close");
	createdateinput();
}
function doslots(ci) {
    var mon = '';
    $("#cslots").dialog({
        minWidth: 850,
        minHeight: 400
    });
    $('#month_view_calender').html("");
    $.ajax({
        url: "../modules-dev/appbookback.php?mon=" + mon + "&ci=" + ci + "&sid=" + Math.random(),
        success: function(data) {
            $("#month_view_calender").html(data);
            $("#caltable").selectable({
                filter: 'td.selectable',
                stop: appselect
            });
        }
    });
}
function popdate(dt, ci) {
    $("#calendar").val(dt);
    $("#slotdatecalendar").val(dt);
    $("#slotidfrombookingcalendar").val(ci);
    $("#calendar").show();
    $("#slots").dialog("close");
    $("#cslots").dialog("close");
    createdateinput_();
}
function indicator()
{
	var _timestamp = new Date();

	$.ajax({
  		url: "../listener.php?act=checkin&stamp=<?=$_SESSION['logid'];?>&sid=<?=session_id();?>&_timestamp="+_timestamp.getTime(),
  		global: false,
                timeout: 20000,
		success: function(resp){
                    if (resp == 'okay'){
                    setTimeout("indicator()",60000);
                    }
                    else if (resp == 'loggedout'){
                        exitdial();
                    }
                    else {
                        this.error();
                    }
                },
                 error: function(jqXHR, exception) {
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
					alert("[Error: " +cause+ "] Your connection to the server has timed out. Please notify your supervisor if this is happening frequently. Click 'OK' to resume your session.");
                    setTimeout("indicator()",3000);
                     //window.location = "../login/";
                 }
             });
	
}
function loadonajax()
{
	jQuery(".datatabs").dataTable();
}
function bulkcreatedateinput()
	{
	jQuery('#bulkdatetd').show();
	}
function bulkcleardateinput()
	{
	jQuery('#bulkdatetd').hide(); 
        jQuery('#bulkcalendar').val(""); 
    }
function statuser()
{
    var truestate = gettruestatus(astatus);
    $("#dstate").html(truestate);
    setTimeout("statuser()",1000);
}
function gettruestatus(status)
{
    if (status == 'checking')
        {
            return "Waiting";
        }
   if (status == 'preview' || status == 'prevlead')
        {
            return "Preview Lead";
        }
   if (status == 'cbview')
        {
            return "Preview Callback";
        }
   if (status == 'Hanged')
        {
            return "Call Ended";
        }    
   return capitaliseFirstLetter(status);
}
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}
statuser();
function gettags(action)
{
    $.ajax({
        url: 'ajax.php?act=gettags&action='+action,
        success: function(resp)
        {
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                width: 300,
                maxHeight: 200
            });
        }
    });
}
</script>
<?php include ("timetracker/tteventspause-js.php") ?>