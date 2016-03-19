<?php
//echo "project id = ".$_REQUEST['projid'];
include "../dbconnect.php";
include "../classes/classes.php";
$projid = $_REQUEST['projid'];
$act = $_REQUEST['act'];
$callscripts = new callscripts($projid);
$res = mysql_query("SELECT * from scriptdata where leadid = '".$_REQUEST['leadid']."'");
$row = mysql_fetch_array($res);
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
$selectcf = mysql_query("SELECT * from leads_custom_fields where leadid = '".$_REQUEST['leadid']."'");
$fetchcf = mysql_fetch_assoc($selectcf);
$countRows = mysql_num_rows($selectcf);
// $getcf =  get_object_vars(json_decode($fetchcf['customfields']));
$sdata =  json_decode($fetchcf['customfields']);
if ($act == 'getscriptdata')
{
    // $sdata = json_decode($row['scriptjson']);
    /*foreach ($sdata as $key=>$value)
    {
        $newsdata[$key] = $value;
    }*/
    // $sdata = array_diff($getcf, $newsdata);
    // print_r($getcf);
    // print_r($sdata);
    // print_r($newsdata);
    /*$sdata = array_unique(array_merge($getcf, $newsdata));*/

    // echo $fetchcf['customfields'];
    // print_r($getcf);
    // print_r($newsdata);
    if (!$sdata)
    {
        $sdata = array();
    }
    $ct = 0;
    foreach ($sdata as $key=>$value)
    {
        $dt[$ct]['name'] = $key;
        $dt[$ct]['value'] = $value;
        $ct++;
    }

    echo json_encode($dt);
    exit;
}
$lead1 = new records($_REQUEST['leadid']);
$lead = $lead1->data;
?>
<style>
strong {
	font-weight:bold;
}
em {font-style:italic}
</style>
<?php
if ($act == 'getnextpage')
{
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    /* SAVE SCRIPT */
    $script = $_REQUEST['script'];
    foreach ($script as $key => $value) {
        if(!empty($value['value'])){
            $renderData[$value['name']] = $value['value'];
        }
    }
    $data = $renderData;
    if($countRows == 0){
      customdata::add($_REQUEST['leadid'],$data);
    } else {
      customdata::updatecf($_REQUEST['leadid'],$data);
    }
    $parentid = $_REQUEST['parentid'];
    $nextpage = $callscripts->getnextpage($parentid, $sdata);
    foreach ($nextpage as $id => $next)
    {
        echo '<div id="page'.$id.'" class="scriptpage">';
        echo $next;
        echo '<br /><input type=button value=Next onclick="nextpage(\''.$projid.'\',\''.$id.'\')"><br>';
        echo '</div>';
    }
    exit;
}
$mainpage = $callscripts->getmain();
$script = $callscripts->getbody($mainpage);
$lab = 'Save';
if ($callscripts->pagecount() > 1)
{
    $lab = "Next";

}
echo '<div id="cinfo"><h3>Customer Information</h3>
    <div>
    <table><tr><td>Name:</td><td>'.$lead['cname']. ' '.$lead['cfname'].' '.$lead['clname'].'</td></tr>
        <tr><td>Company:</td><td>'.$lead['company'].'</td></tr>
        <tr><td>Phone:</td><td>'.$lead['phone'].'</td></tr>
        <tr><td>Address:</td><td>'.$lead['address1'].' '.$lead['address2'].', '.$lead['city'].' '.$lead['state'].' '.$lead['country'].' '.$lead['zip'].'</td></tr>
            </table>
    </div>
</div>';
echo '<form id="scriptbod" name="scriptbod">
    <h3>Calling Script</h3><div id="page'.$mainpage.'">';
echo $script;
echo '<input type=button value='.$lab.' onclick="nextpage(\''.$projid.'\',\''.$mainpage.'\')"><br>';
echo '</div>';
echo '</form>';
//$data = json_decode($row[]
?>
<style type="text/css">
.afield {
  position: relative;
  /*display: inline;*/
}
.afield span {
  position: absolute;
  width:140px;
  color: #FFFFFF;
  background: #000000;
  height: 30px;
  line-height: 30px;
  text-align: center;
  visibility: hidden;
  border-radius: 6px;
  font-size: small;
}
.afield span:after {
  content: '';
  position: absolute;
  top: 50%;
  right: 100%;
  margin-top: -8px;
  width: 0; height: 0;
  border-right: 8px solid #000000;
  border-top: 8px solid transparent;
  border-bottom: 8px solid transparent;
}
.afield:hover span {
  visibility: visible;
  opacity: 0.8;
  left: 100%;
  top: 50%;
  margin-top: -15px;
  margin-left: 15px;
  z-index: 999;
}
</style>
<script type="text/javascript">
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
    $(document).ready(function(){
        populatescript();

        $("#scriptbod input, #scriptbod select").change(function(){
            // var input = $(this).attr('name');
            $("[name='"+$(this).attr('name')+"']").val($("#scriptbod").find("[name='"+$(this).attr('name')+"']").val());
            console.log("Changing script custom field: "+$(this).attr('name'));
            submitcustom($("#leadid").val());
        });
        $("#othercontent input").change(function(){
            // var input = $(this).attr('name');
            $("[name='"+$(this).attr('name')+"']").val($("#othercontent").find("[name='"+$(this).attr('name')+"']").val());
            console.log("Changing other information custom field: "+$(this).attr('name'));
        });
        $("#scriptbod input[type=text], #scriptbod select").hover(function(){
            var input = $(this).attr('name');
            var textlength = input.length;
            var position = $(this).position();
            var width = $(this).width();
            var base = 100;
            $(this).before("<span class='tooltips' style='left:"+(position.left+width)+";width:"+(base+(textlength*9))+"px'>Custom Field: "+input+"</span>");
        }).mouseout(function(){
            $(".tooltips").remove();
        });
    });
</script>
