<?php
class leadsloadersettings {
    public static function phonenumbers() // array of phone number fields.  Used in cleaning uploaded lists
    {
        $ret = array(
            'phone',
            'altphone',
            'mobile',
            'fax',
        );
        return $ret;
    }
    public static function duplicatecheck($list = true) //array of fields to check for duplicates
    {
        if ($list)
        {
        $ret = array(
            'phone'
        );
        }
        else {
            $ret = array(
                'phone'=>'plain'
                
            );
            
        }
        return $ret;
    }
}
?>
