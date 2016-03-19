<?php
$prores = mysql_query("SELECT * from projects where bcid = '$bcid'");
while ($prorow = mysql_fetch_array($prores))
	{
		$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
	}

	$selproject = '<select name="projects" id="listproj"><option></option>'.$prolist.'</select>';
	$target = 'action="leadsloader.php" target="mapper"  onSubmit="window.open(\'\',\'mapper\');"';
?>
                 <div class="entryform" style="width:300px; height:250px" title="New List">
                <form name="uploadcsv" id="uploadcsv" >
                <?=$iswin;?>
                <title>Upload CSV update file</title>
                <div id="respmessage"></div>
                
                <div><label>Campaign:</label>
                <?=$selproject;?></div>
                <input type="hidden" name="act" value="listupdatefile" />
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <br>
                <div><label>CSV File:</label>
                <p>Must be formatted with two columns, with the first column containing the set of phone numbers to update
                and the second column having the corresponding disposition.
                </p>
                </div> 
                <div>
               <input id="MAX_FILE_SIZE" name="csvfile" type="file" style="float:left;width:100%" /></div>
                <div class="clear"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
                </form><div class="buttons" style="position:relative">
				<input type="button" value="Next" onclick="dispoupdateupload()"></div>
                </div>