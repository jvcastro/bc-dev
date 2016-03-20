<?php
class forms {
    public static function insertfield($type,$pid)
    {
        $adminCustomFieldsGet = mysql_query("SELECT customfields FROM projects WHERE projectid = '$pid'");
        $adminCustomFieldsRow = mysql_fetch_assoc($adminCustomFieldsGet);
        $adminCustomFields = json_decode($adminCustomFieldsRow['customfields']);
        ?>
        <script type="text/javascript">
            $(document).ready(function(){

                var label = $(" #fieldname").find(':selected').data('label');
                $("#fieldlabel").val(label);
                
                $("#fieldname").change(function(){
                    var value = $(this).val();
                    var label = $(this).find(':selected').data('label');

                    $("#fieldlabel").val(label);
                });
            });
        </script>
        <?php
        // print_r($adminCustomFields);
        switch ($type) {
            case "textarea":
            case "text":
                ?>
                    <div class="entryform" style="width:300px;height:100px">
                        <title>Add Text Field</title>
                        <div>Label:<input type="text" name="fieldlabel" id="fieldlabel"></div>
                        <div>Name:
                        <select name="fieldname" id="fieldname">
                        <?php
                            foreach ($adminCustomFields as $key => $value) {
                        ?>
                            <option value="<?=$key?>" data-label="<?=$value?>"><?=$key?></option>
                        <?php
                            }
                        ?>
                        </select>
                        <!-- <input type="text" name="fieldname" id="fieldname" onblur="validate(this,'cffieldname')"> --></div>
                        <div>*for Name: use only numbers and letters. No spaces, dashes, underscores, etc..</div>
                        <div class="buttons"><input type="button" value="Done" onclick="mceinsert('<?=$type?>')"/></div>
                    </div>
                <?php

                break;

            case "select":
                ?>
                    <div class="entryform" style="width:300px;height:100px">
                        <title>Add Drop-down</title>
                        <div>Label:<input type="text" name="fieldlabel" id="fieldlabel" required></div>
                        <div>Name:
                        <select name="fieldname" id="fieldname">
                        <?php
                            foreach ($adminCustomFields as $key => $value) {
                        ?>
                            <option value="<?=$key?>" data-label="<?=$value?>"><?=$key?></option>
                        <?php
                            }
                        ?>
                        </select>
                        <!-- <input type="text" name="fieldname"  onblur="validate(this,'cffieldname')" id="fieldname" required> --></div>
                        <div>*for Name: use only numbers and letters. No spaces, dashes, underscores, etc..</div>
                        <div id="opdiv1">Option1:<input type="text" name="option1" id="option1" class="seloptions"/><img id="addoptionimage" onclick="addanotheroption(1)" src="icons/add.gif"></div>
                        <div id="otheroptions"></div>
                        <div></div>
                        <div><input type="button" value="Done" onclick="mceinsert('<?=$type?>')" style="float:left"/></div>
                    </div>
                <?php
                break;
        }
    }
}
?>
