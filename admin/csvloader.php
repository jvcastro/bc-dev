<?php
include_once "../dbconnect.php";
$prores = mysql_query("SELECT * from projects where bcid = '$bcid'");
while ($prorow = mysql_fetch_array($prores))
	{
		$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
	}
$listres = mysql_query("SELECT * from lists where bcid = '$bcid'");
while ($listrow = mysql_fetch_array($listres))
	{
		$lists .= '<option value="'.$listrow['listid'].'">'.$listrow['listid'].'</option>';	
	}
	
?>

<style>
				.dupcheck {
					position: relative;
					left: 82px;
				}
				.inp {
					position:relative;
				}
				</style>
                <form enctype="multipart/form-data" action="leadsloader.php" method="POST" target="mapper" onSubmit="window.open('','mapper');">
                <div style="position: relative;">
                <div style="height:20px; background-color:#CCC; text-align:center; border:1px #003 solid; color:#333; padding-top:5px" class="center-title"><b> Load CSV file </b></div><br />
                ListId: <select class="inp" style="left: 52px;" name="listid"><?=$lists;?></select><br /><br />
                
                Duplicate Check: <input name="dupcheck" type="radio" value="listonly"/> Within this List Only<br /><br />
                <div class="dupcheck"><input name="dupcheck" type="radio" value="project"/> Lists assigned to this Project</div><br />
                 <div  class="dupcheck"><input name="dupcheck" type="radio" value="all"/> All Lists in the System</div><br />
                Type of Data: <select name="leadtype" style="vertical-align:top;left: 15px; font-size:12px"  class="inp"><option value="b">Business</option><option value="i">individual</option><option value="m">Mixed</option></select><br>
                 <input type="hidden" name="act" value="upload" />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />
				file to load: <input name="csvfile" type="file" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px"  /><br>
                <div style="width:400px; height:20px; background-color:#CCC; text-align:right; border:1px #003 solid; color:#333; padding-top:5px">
				<input type="submit" value="Next"  style="font-size:10px; width:70px; height:20px; padding-bottom:8px" /></div>
                </div>