<?php
class labels {
    public $labels = array(
        'cname' => 'Name',
        'cfname' => 'FirstName',
        'clname' => 'LastName',
        'company' => 'Company',
        'epoch_callable' => 'Date Set'
    );
    public static function get($field) {
        $labels = new labels();
        if ($labels->labels[$field])
        {
            return $labels->labels[$field];
        }
        else return $field;
    }
}
?>
