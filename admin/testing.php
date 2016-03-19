<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="ext/resources/css/xtheme-silverCherry.css" />
<link rel="stylesheet" type="text/css" href="custom.css" />
<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext/ext-all.js"></script>
<link href="styles/style.css" rel="stylesheet" />
<style>
td.center-title {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
font-size:8pt;
font-weight:bold;
line-height:12pt;
padding:2px;
text-align:center;
}
td.datas {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
padding:2px;
text-align:center;
}
td.dataleft {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
padding:2px;
text-align:left;
}
body {
font-family:Tahoma;
font-size:8pt;
}
.sel {
	width: 75px;
	height: 15px;
	font-size:10px;
	border:none;
	color:#333;
	background:none;
}
</style>
<?php
mysql_connect('127.0.0.1','root');
mysql_select_db('proactiv');
$agentres = mysql_query("SELECT members.*,memberdetails.* from members left join memberdetails on members.userid = memberdetails.userid where active = 1 and members.userid ='11';");
$row = mysql_fetch_array($agentres);
?>
<table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
<tr><td class="center-title" colspan="3" style="background-color:#DBDBDB">Agent Details</td></tr>
<tr><td class="center-title">FirstName</td><td class="dataleft"><?=$row['afirst'];?></td><td style="border-bottom:1px solid #B3B3B3;border-left:1px solid #B3B3B3; padding:0px; margin:0px; width:92px" rowspan="4"><img src="../images/default_avatar.jpg" height="95" width="91"></td></tr><tr><td class="center-title">Lastname</td><td class="dataleft"><?=$row['alast'];?></td></tr>
<tr><td class="center-title">Reset Password:</td><td class="dataleft"><input type="text" id=newpass><button>Reset</button></td></tr>
<tr><td class="center-title" colspan="2"><a href="#">Call History</a> | <a href="#">Agent's Performance Report</a> | <a href="#">Delete User</a></td></tr>
</table>
<?