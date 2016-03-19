<?php
$projid = '1';
mysql_connect('localhost','obri','niner123');
mysql_select_db('proactiv');
$res = mysql_query("SELECT * from scripts where projectid = '$projid'");
$row = mysql_fetch_array($res);
$script = $row['scriptbody'];
$first = explode("+",$script);
foreach ($first as $one)
	{
		$ser = strpos($one,"-");
		//echo $one;
		$sec = explode("-",$one);
		
		if ($ser) $disp .= '<input type="text" name="'.$sec[0].'"> '.$sec[1];
		else $disp .= $sec[0]." ";
	}
$disp = str_replace("\r","<br>",$disp);
//$disp = str_replace("\n","<br>",$disp);
echo $disp;
echo $script;