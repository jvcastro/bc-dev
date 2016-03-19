<?php
class clients extends dataTable{
    public $clientnames = array();
     public function __construct($bcid) {
         
          $this->tablename = 'clients';
          $this->idname = 'clientid';
          $res = mysql_query("SELECT * from clients where bcid = '$bcid'");
          while ($row = mysql_fetch_assoc($res))
          {
              $this->clientnames[$row['clientid']] = $row['company'];
              $this->data[$row['clientid']] = $row;
          }
       }
       public static function getclientcontacts($clientid)
       {
           global $bcid;
           $res = mysql_query("select client_contacts.*, members.usertype from client_contacts left join members on client_contacts.userid = members.userid where clientid = $clientid and client_contacts.bcid = $bcid");
           while ($row = mysql_fetch_assoc($res))
           {
               $ret[$row['client_contactid']] = $row;
           }
           return $ret;
       }
       
       public function getclientname($clientid)
       {
           return $this->data[$clientid]["company"];
       }
}
?>
