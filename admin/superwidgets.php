<?php
function adminlist($bcid = 0)
	{
	global $bclist;
	if ($bcid == 0)
		{
			$adres = mysql_query("SELECT * from members where usertype = 'admin' and super = '0'");
		}
	else $adres = mysql_query("SELECT * from members where usertype = 'admin' and super = '0' and bcid = '$bcid'");
	$admin = '';
	while ($ad = mysql_fetch_array($adres))
		{
			if ($ad['active'] == '0') 
				{
					$color = "red";
					$activ = '<a href="#" onclick=activate(\''.$ad['userid'].'\')>activate<a>';
				}
			else 
				{
					$color = "yellowgreen";
					$activ = '<a href="#" onclick=deactivate(\''.$ad['userid'].'\')>deactivate<a>';
				}
			$admin .= '<tr><td class="datas"><font color="'.$color.'">';
			$admin .= $ad['userlogin'].'</td><td class=datas id="pass'.$ad['userid'].'"><a onclick="changedetails(\'pass'.$ad['userid'].'\',\''.$ad['userpass'].'\',\'members\',\''.$ad['userid'].'\')">'.$ad['userpass'].'</a></td>';
			$admin .= '<td class="datas">'.$bclist[$ad['bcid']]['company'].'</td>';
			$admin .= '<td class="datas">'.$activ.'</td></tr>';
		}
	?>
	<table width="650" cellspacing="0" cellpadding="0" style="">
	<thead>
	<tr><th colspan="4" style="" class="center-title heading">Admin List</th></tr>
	</thead>

	<tr><td class="center-title">Login</td><td class="center-title">Password</td><td class="center-title">Company</td><td class="center-title">Action</td></tr>
	<?=$admin;?>
	</table>
	<?
	}