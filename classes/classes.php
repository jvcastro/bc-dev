<?php
spl_autoload_register(function ($class) {
    if ($class == 'Mail' || $class == 'Mail_mime')
    {
        include_once('Mail.php');
        include_once('Mail/mime.php');
    }
    elseif ($class == 'AMI')
    {
        $dirname = dirname(__FILE__);
        require_once "$dirname/../ami-client.php";
    }
    else {
        //include_once '../classes/' . $class . '.php';
        $dirname = dirname(__FILE__);
        require_once "$dirname/".$class.".php";
        }
});
class errors {
    private $session = array(
        'error' => false,
        'error_message' => NULL
    );
    public function setError($msg)
    {
        $this->session['error'] = true;
        $this->session['error_message'] = $msg;
    }
    public function __construct($msg) {
        $this->session['error'] = true;
        $this->session['error_message'] = $msg;
    }
}
class leadData {
    protected $done = 'leads_done';
    protected $raw = 'leads_raw';
    public $id = NULL;
    public $data = array();
     
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**  As of PHP 5.1.0  */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**  As of PHP 5.1.0  */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
}
class dataTable {
    protected $tablename = null;
    protected $idname = 'id';
    public $data = array();
    
    public function findById($id)
    {
        $res  = mysql_query("SELECT * from ".$this->tablename." where ".$this->idname." = '$id'");
        return $res;
    }
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**  As of PHP 5.1.0  */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**  As of PHP 5.1.0  */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
    public function insert()
        {
            if ($this->id)
            {
                $this->update;
            }
            else 
            {
                
            $qs = array();
            foreach ($this->data as $key=>$dt)
            {
                $qs[]= "$key = '".$dt."'";
            }
            $qstring = implode(",",$qs);
            
            $res = mysql_query("INSERT into $this->tablename SET $qstring ");
            $id = mysql_insert_id();
            $this->id = $id;
            }
        }
        public function update()
        {
             $qs = array();
            foreach ($this->data as $key=>$dt)
            {
                if ($key != $this->idname)
                {
                $qs[]= "$key = '".$dt."'";
                }
            }
            $qstring = implode(",",$qs);
            
            $res = mysql_query("UPDATE ".$this->tablename." SET $qstring where ".$this->idname." = '".$this->id."'");
            $id = mysql_insert_id();
            $this->id = $id;
        }
}
?>
