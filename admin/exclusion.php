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
                <title>New Exclusion List</title>
                <div id="respmessage"></div>
                
                <div><label>Campaign:</label>
                <?=$selproject;?></div>
                <div><label>Exclusion ListId: </label><input type="text" id="listid" name="listid" /></div>
                <input type="hidden" name="act" value="exclusionupload" />
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <br>
                <div><label>CSV File:</label>
                    <p>
                        File must consist of just a single column containing the phone numbers to exclude. All matching records in the campaign will be tagged as DoNotCall and removed from queue.
                        
                    </p></div> 
                <div>
               <input id="MAX_FILE_SIZE" name="csvfile" type="file" style="float:left;width:100%" /></div>
                <div class="clear"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
                </form><div class="buttons" style="position:relative">
				<input type="button" value="Next" onclick="exclusionupload()"></div>
                </div>