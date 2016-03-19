<?php
class callscripts {
    private $scripts = array();
    public $currentscript = NULL;
    public function __construct($pid) {
        $scripts = array();
        $res = mysql_query("SELECT * from scripts where projectid = $pid");
        while ($row = mysql_fetch_assoc($res))
        {
            $scripts[$row['scriptid']] = $row;
            if ($row['parentid'] < 1) $scripts[$row['scriptid']]['ismain'] = true;
        }
        $this->scripts = $scripts;
    }
    public static function getscript($id)
    {
        $res= mysql_query("SELECT * from scripts where scriptid = $id");
        $row= mysql_fetch_assoc($res);
        return $row;
    }
    public static function getpages($pid)
    {
        $scripts = new callscripts($pid);
        $ret = array();
        foreach ($scripts->scripts as $script)
        {
            if ($script['ismain'] && strlen($script['scriptname']) < 2 ) $ret[$script['scriptid']] = 'Main';
            else $ret[$script['scriptid']] = $script['scriptname'];
        }
        return $ret;
    }
    public function getmain()
    {
        foreach ($this->scripts as $script)
        {
            if ($script['ismain']) return $script['scriptid'];
        }
    }
    public function getbyid($scriptid)
    {
        return $this->scripts[$scriptid];
    }
    public function getparent($scriptid)
    {
        $s = $this->getbyid($scriptid);
        return $s['parentid'];
    }
    public function getbody($scriptid)
    {
        return $this->scripts[$scriptid]["scriptbody"];
    }
    public function pagecount()
    {
        return count($this->scripts);
    }
    public function getnextpage($parentid,$sdata)
    {
        $ret = array();
        $np = array();
        foreach ($this->scripts as $script)
                {
                    if ($script['parentid']== $parentid) 
                        {
                            if(callscripts::checkrequirements($sdata, $script["requiredfields"])) 
                            {
                                $np[$script["scriptid"]]= $script["scriptbody"];
                            }
                        
                        }
                }
       return $np;
                
    }
    public static function checkrequirements($sdata,$requirements)
    {
        $sd = json_decode($sdata,true);
        $req = json_decode($requirements,true);
        foreach ($req as $key=>$value)
        {
            if ($sd[$key] != $value || !$sd[$key])
            {
                return false;
            }
        }
        return true;
    }
    public function getchildpages($parentid = NULL,$spacer = NULL)
	{
                $children = array();
                if ($parentid == NULL) 
                {
                    $parentid = $this->getmain ();
                    
                }
                foreach ($this->scripts as $script)
                {
                    if ($script['parentid']== $parentid) $children[] = $script;
                }
		if (count($children) == 0)
			{
				return NULL;
			}
		else {
			
			foreach ($children as $child)
				{
                                        
					if ($this->currentscript['scriptid'] == $child['scriptid'])
						{
							$ret .= '<li class="ui-state-disabled">';
                                                        $ret .= '<a href="#">'.$spacer.$child['scriptname'].'</a>';
						}
					else
                                        {
                                            $ret .= '<li>';
                                            $ret .= '<a href="#" onclick="editscriptid(\''.$child['scriptid'].'\')">'.$spacer.$child['scriptname'].'</a>';
                                            
                                        }
                                        
                                        $sp = "&nbsp;&nbsp;".$spacer;
					$ret .= $this->getchildpages($child['scriptid'],$sp);
                                        $ret .='</li>';
				}
                        
			return $ret;
		}
	}
        public function getfields($scriptid)
        {
            $script = $this->getbyid($scriptid);
            $options = array();
            preg_match_all("/<input.*name=\"(.*?)\".*type=\"text\"/",$script['scriptbody'],$textfields);
            preg_match_all("/<select.*name=\"(.*?)\"/",$script['scriptbody'],$dropdowns);
            preg_match_all("/[\[](.*?)[\]]/",$script['scriptbody'],$fields);
            foreach ($dropdowns[1] as $drops)
            {
                preg_match_all("/<select.*name=\"".$drops."\">(.*?)<\/select>/",$script['scriptbody'],$options[$drops]);
            }
            $ret['textfields'] = $textfields;
            $ret['dropdowns'] = $dropdowns;
            $ret['options'] = $options;
            $ret['oldfields']= $fields;
            
            return $ret;
        }
}

?>
