<?php
/**
 *  Class bluestrap
 * Class to simplify ajax: front-back-front-back transactions
 * Class is meant to be released publicly.
 * Author:  obrifs@gmail.com
 * Notes: $act must be camelcased beginning with "action", followed by the crude action
 * followed by table name. e.g. $act = actionNewM 
 * constructor ( target element - use jquery selectors, css file )
 * Table must always have primary key
 */
class bluestrap {
    public $target = 'body';
    public $theme = Null;
    public $data = array();
    public $actioner = NULL;
    private $datacount = 0;
    
    /**
     * 
     * @param type $target DOM div the will contain ajax responses
     * @param type $index File that will process actions (root file that called bluestrap)
     * @param type $cssfile custom css file
     */
    public function __construct($target = 'body', $index = 'index.php', $theme = 'default') {
        $this->theme = $theme;
        $this->loadtheme($theme);
        $this->target = $target;
        $this->actioner = $index;
    }
    public function loadtheme($theme)
    {
        $dirname = dirname(__FILE__);
        require_once "$dirname/bluestrap_themes/$theme/theme.php";
    }
    /**
     * 
     * @param type $name Required - name of transaction
     * @param type $fields array of field array('name','label','type','isSummary') <br>
     * name - fieldname in database<br>label - label in form<br>type - input type: text, select, textarea<br>
     * isSummary - bool, default false, true to include in summary table
     * @param type $table Optional - database tablename. <br> If null, class will use $name as tablename <br> 
     * @param type $menutitle Optional - can be used as menu labels (if null, class will use $name)
     * @param type $keyname Optional - table primary key, if null class will query database for primary key
     * @param type $cssfile Optional - custom css file for this transaction
     */
    public function addTransaction($name, $fields = array(), $table = NULL, $menutitle = NULL, $keyname = NULL, $cssfile = NULL)
    {
        if (!$table) $table = $name;
        if (!$menutitle) $menutitle = $name;
        if (!$keyname) $keyname = $this->getkeyname ($table);
        if (!$keyname) throw new Exception('Table must have primary key');
        if (!$fields) $fields = array();
        $this->data[$name]['name'] = $name;
        $this->data[$name]['table'] = $table;
        $this->data[$name]['menutitle'] = $menutitle;
        $this->data[$name]['fields'] = $fields;
        $this->data[$name]['keyname'] = $keyname;
        $this->datacount = $this->datacount + 1;
    }
    public function addField($name,$field = array())
    {
       if (!$field['label']) $field['label'] = $field['name'];
       if (!$field['type']) $field['type'] = 'text';
       $ret = array_push($this->data[$name]['fields'],$field);
       return $ret;
    }
    public function doAction($action)
    {
        preg_match_all('/((?:^|[A-Z])[a-z]+)/',$action,$matchesa);
        $matches = $matchesa[0];
        if ($matches[0] != 'action') return false;
        $act = strtolower($matches[1]);
        $name = strtolower($matches[2]);
        if (!$this->data[$name]) return false;
        else {
        $this->$act($name);
        exit;
        }
    }
    public function create($name)
    {
        $transaction = $this->data[$name];
        $qs = array();
        foreach ($transaction['fields'] as $field)
        {
            $qs[] = $field['name'] ."='".$_REQUEST[$field['name']]."'";
        }
        $querystring = implode(",",$qs);
        mysql_query("INSERT into ".$transaction['table']." set $querystring");
        $id = mysql_insert_id();
        return $id;
    }
    public function listform($name)
    {
        $transaction = $this->data[$name];
        $res = mysql_query("SELECT * from ".$transaction['table']);
        while ($row = mysql_fetch_assoc($res))
        {
            $rows[] = $row;
        }
        $vars['transaction'] = $transaction;
        $vars['rows'] = $rows;
        $vars['fields'] = $this->getfields($name);
        $vars['name'] = $name;
        theme::listform($vars);
        exit;
    }
    public function createform($name)
    {
        $vars['transaction'] = $this->data[$name];
        $vars['fields'] = $this->getfields($name);
        theme::createform($vars);
        exit;
    }
    public function updateform($name)
    {
        $transaction = $this->data[$name];
        $keyname = $transaction['keyname'];
        $vars['fields'] = $this->getfields($name);
        $res = mysql_query("SELECT * from ".$transaction['table']." where $keyname = '".$_REQUEST[$keyname]."'");
        $row = mysql_fetch_assoc($res);
        $vars['row'] = $row;
        $vars['transaction'] = $transaction;
        theme::updateform($vars);
    }
    public function update($name)
    {
        $transaction = $this->data[$name];
        $keyname = $transaction['keyname'];
        $qs = array();
        foreach ($this->getfields($name) as $field)
        {
            if ($field['name'] != $transaction['keyname'] && isset($_REQUEST[$field['name']]))
            {
            $qs[] = $field['name'] ."='".$_REQUEST[$field['name']]."'";
            }
        }
        $querystring = implode(",",$qs);
        mysql_query("update ".$transaction['table']." set $querystring where $keyname = '".$_REQUEST[$keyname]."'");
        return $_REQUEST[$keyname];
    }
    public function delete($name)
    {
        $transaction = $this->data[$name];
        $keyname = $transaction['keyname'];
        mysql_query("delete  from ".$transaction['table']." where $keyname = '".$_REQUEST[$keyname]."'");
        return $_REQUEST[$keyname];
    }
    
    public function generateScripts()
    {
        $d = '<script>';
        foreach ($this->data as $transaction)
        {
            $ac = $this->actioner;
            $d .= '
            function listform'.$transaction['name'].'(cb)
                {
                    if (!cb) {
                        var cb = function(resp){
                            $("'.$this->target.'").html(resp);
                        }
                    }
                    $.ajax({
                        url: "'.$ac.'?act=actionListform'.ucfirst($transaction['name']).'",
                        type: "GET",
                        success: cb
                    });
                }
            ';
            $d .= '
            function createform'.$transaction['name'].'()
                {
                    $.ajax({
                        url: "'.$ac.'?act=actionCreateform'.ucfirst($transaction['name']).'",
                        type: "GET",
                        success: function(resp){
                            $("'.$this->target.'").html(resp);
                        }
                    });
                }
            ';
             $d .= '
            function create'.$transaction['name'].'()
                {
                    var dat = $("#'.$transaction['name'].'form").serialize();
                    $.ajax({
                        url: "'.$ac.'?act=actionCreate'.ucfirst($transaction['name']).'",
                        type: "POST",
                        data: dat,
                        success: function(resp){
                            $("'.$this->target.'").html(resp);
                                listform'.$transaction['name'].'();
                        }
                    });
                }
            ';
              $d .= '
            function update'.$transaction['name'].'(keyid)
                {
                    var dat = $("#'.$transaction['name'].'form").serialize();
                    $.ajax({
                        url: "'.$ac.'?act=actionUpdate'.ucfirst($transaction['name']).'&'.$transaction['keyname'].'="+keyid,
                        type: "POST",
                        data: dat,
                        success: function(resp){
                            $("'.$this->target.'").html(resp);
                            listform'.$transaction['name'].'();
                        }
                    });
                }
            ';
              $d .= '
            function updateform'.$transaction['name'].'(keyid)
                {
                    
                    $.ajax({
                        url: "'.$ac.'?act=actionUpdateform'.ucfirst($transaction['name']).'&'.$transaction['keyname'].'="+keyid,
                        type: "GET",
                        success: function(resp){
                            $("'.$this->target.'").html(resp);
                        }
                    });
                }
            ';
        }
        $d .= '</script>';
        return $d;
    }
    /**
     * Private Functions / Utilities
     */
    private function getkeyname($table)
    {
        $res = mysql_query("SHOW KEYS from $table where Key_name = 'PRIMARY'");
        $row = mysql_fetch_assoc($res);
        return $row['Column_name'];
    }
    private function getfields($name)
    {
        $transaction = $this->data[$name];
        if (!$transaction['fields'])
        {
        $res = mysql_query("SHOW FIELDS from ".$transaction['table']);
        while ($f = mysql_fetch_assoc($res))
        {
            $fields[$f['Field']]= array('name'=>$f['Field'],'label'=>$f['Field'],'type'=>'text','isSummary'=>true);
        }
        return $fields;
        }
        else return $transaction['fields'];
    }
    
    
}
?>
