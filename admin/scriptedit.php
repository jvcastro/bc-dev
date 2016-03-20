<?php
$scripts = new callscripts($pid);
$currentscript = $scripts->getbyid($scriptid);
$scripts->currentscript = $currentscript;
//var_dump($currentscript);
?>
<h3>Script Editor</h3>
<div class="secnav">
</div>
<div id="scriptform">
<form action="post" action="admin.php">
        <textarea cols="100" rows="20" id="scr" visibility="hidden" style="width:100%"><?=$currentscript['scriptbody'];?></textarea><br />
        <input type="button" value="Update" id="updatescriptbut" onclick="updatescript('<?=$scriptid;?>')" />
</form>
</div>
<div id="editormenu">
    <div id="inputfields">
        <input type="hidden" id="pid" value="<?=$pid;?>"></input>
        <ul id="inmenu" class="domenu">
            <li><h3>Insert / Drag Fields</h3></li>
            <li>
                <a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="fieldparams('text')" onclick="fieldparams('text')">Textfield</a>
            </li>
            <li>
                <a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="fieldparams('select')" onclick="fieldparams('select')">Dropdown</a>
            </li>
        </ul>
    </div>
<div id="pages" style="display:none">
<ul class="domenu">
    <li><h3>Script Pages</h3></li>
<?php
if ($currentscript['ismain']) 
{
    echo '<li class="ui-state-disabled">';
    echo '<a href="#">Main</a></li>';
}
else {
    $mainpage = $scripts->getmain();
    echo '<li><a href="#" onclick="editscriptid(\''.$mainpage.'\')">Main</a></li>';
}
?>
<?=$scripts->getchildpages();?>
</ul>
</div>
</div>
<div id="lookupform" class="dialogwindow">
<form name="lookuptable" action="admin.php?act=addlookuptable&projectid=<?=$pid;?>" target="lookupframe" method="post" enctype="multipart/form-data">
<input type="file" name="file" /><input type="submit" value="add" />
</form>
<div id="flists">
</div>
</div>
<iframe src="admin.php" style="display:none" name="lookupframe">
</iframe>
