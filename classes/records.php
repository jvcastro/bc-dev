<?php
class records extends leadData {
       private $fieldmap = array(
           'disposition'=>'dispo',
           'lid'=>'leadid',
           'id'=>'leadid',
           'notes'=>'resultcomments',
           'list'=>'listid'
       );
       private $validfields = array();
       public function __construct($leadid = NULL) {
         if ($leadid)
         {
             
             $res = mysql_query("SELECT * from ".$this->raw." where leadid = '".$leadid."' ");
             if (!mysql_num_rows($res))
             {
                $this->id= false;
             }
             $row = mysql_fetch_assoc($res);
             $this->data = $row;
             $this->id = $row['leadid'];
             $res = mysql_query("SELECT * from ".$this->done." where leadid = '".$leadid."' ");
             
             if (mysql_num_rows($res))
             {
                 $row = mysql_fetch_assoc($res);
                 foreach ($row as $key=>$value)
                 {
                     if (strlen($value) > 0) $this->data[$key] = $value;
                 }
             }
             else {
                 $listidres= mysql_query("select projects from lists where listid = '".$this->listid."'");
                 $listidrow = mysql_fetch_assoc($listidres);
                 $this->projectid = $listidrow['projects'];
             }
         }

       }
       private function field($field)
       {
           if ($this->fieldmap[$field])
           {
               return $this->fieldmap[$field];
           }
           else return $field;
       }
       public function update()
       {
           $qs =array();
           $this->getvalidfields('leads_done');
           foreach ($this->data as $key=>$value)
           {
                $f = $this->field($key);
               if ($key != 'timeofcall' && $f != 'qa') $value = "'".mysql_escape_string($value)."'";
               if (in_array($f,$this->validfields) && $f != 'qa' && $f != 'leadid')
               {
               $qs[$f] = $f ."=". $value;
               }
               if ($f == 'qa' && strlen($value)>0)
               {
                   $this->addnote('QA', $value);
               }
           }
           $qstring = implode(",",$qs);
           
           mysql_query("update leads_done set $qstring where leadid = '".$this->id."'") or die(mysql_error());
           
       }
       public function dispose()
       {
           $this->epoch_timeofcall = time();
           $this->timeofcall = 'NOW()';
           $this->status = 'assigned';
           $this->update();
           mysql_query("update leads_raw set dispo ='".$this->disposition."', lastcall = '".$this->timeofcall."',locked = ".$this->locked." where leadid = ".$this->id);
       }
       public function createdone()
       {
           mysql_query("insert into leads_done set leadid = '".$this->id."'" ) or die(mysql_error());
       }
       public function createraw()
       {
           $qs =array();
           $this->getvalidfields('leads_raw');
           foreach ($this->data as $key=>$value)
           {
                $f = $this->field($key);
               if ($key != 'timeofcall') $value = "'".mysql_escape_string($value)."'";
               if (in_array($f,$this->validfields) && $f != 'leadid')
               {
                  
               $qs[$f] =  $f."=". $value;
               }
               
           }
           $qstring = implode(",",$qs);
           $istring = "insert into leads_raw set $qstring";
           
           mysql_query($istring) or die($istring);
           $this->id = mysql_insert_id();
           $this->leadid = $this->id;
       }
       private function getvalidfields($table)
       {
           $this->validfields = array();
           $result = mysql_query("SHOW COLUMNS FROM $table");
            if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
            }
            if (mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    $this->validfields[] = $row['Field'];
                }
            }
       }
       public static function getlead($leadid)
       {
           $record = new records($leadid);
           return json_encode($record->data);
       }
       public function locklead()
       {
           $epoch = time();
           $res = mysql_query("update leads_raw set locked = $epoch where leadid = ".$this->leadid);
           $res2 = mysql_query("update leads_done set locked = $epoch where leadid = ".$this->leadid);
       }
       public function unlocklead()
       {
           $res = mysql_query("update leads_raw set locked = 0 where leadid = ".$this->leadid);
           $res2 = mysql_query("update leads_done set locked = 0 where leadid = ".$this->leadid);
       }
       public function setdatetime($cal)
       {
           mysql_query("update leads_raw set epoch_callable = '".strtotime($cal)."' where leadid = '".$this->leadid."'");
           mysql_query("update leads_done set epoch_callable = '".strtotime($cal)."' where leadid = '".$this->leadid."'");
       }
       public function addnote($noter,$newnote)
       {
           $prevnote = $this->notes();
            $jnote = json_decode($prevnote,true);
            if (!$jnote) $jnote = array();
            array_push($jnote,array(
                "user"=>$noter,
                "timestamp"=>time(),
                "message"=>$newnote
            ));
            $newnote = json_encode($jnote);
            $this->notes($newnote);
       }
       public function notes($note = NULL)
       {
           
           $res = mysql_query("SELECT * from leads_notes where leadid = '".$this->leadid."'");
           $rows = mysql_num_rows($res);
           if ($rows > 0)
           {
              $row = mysql_fetch_assoc($res);
              if ($note)
              {
                  mysql_query("UPDATE leads_notes set note = '$note' where leadid = '".$this->leadid."'");
                  return $note;
              }
              else return $row['note'];
           }
           else {
               mysql_query("INSERT into leads_notes set leadid = '".$this->leadid."', note = '$note'");
               return $note;
           }
       }
       public function scriptdata($sdata = NULL)
       {
           
           $res = mysql_query("SELECT * from scriptdata where leadid = ".$this->id);
           $row = mysql_fetch_assoc($res);
           if (mysql_num_rows($res) < 1)
           {
               $dt = array();
               mysql_query("INSERT into scriptdata set scriptjson='',leadid=".$this->id);
           }
           else { 
               $dt = json_decode($row['scriptjson'],true);
           
           if (!count($dt))
           {
               $dt = array();
               
           }
           }
           if ($sdata == NULL)
           {
               return $dt;
           }
           else {
           foreach ($sdata as $key=>$val)
           {
               $dt[$key] = $val;
           }
           mysql_query("UPDATE scriptdata set scriptjson = '".json_encode($dt)."' where leadid=".$this->id);
           }
       }
       public function customdata()
          {
           
           $res = mysql_query("SELECT * from leads_custom_fields where leadid = ".$this->id);
           $row = mysql_fetch_assoc($res);
           if (mysql_num_rows($res) < 1)
           {
               $dt = array();
               mysql_query("INSERT into leads_custom_fields set customfields='',leadid=".$this->id);
               return $dt;
           }
           else { 
                $dt = json_decode($row['customfields'],true);
           
                if (!count($dt))
                {
                    $dt = array();

                }
                return $dt;
           }
           
       }
}
?>
