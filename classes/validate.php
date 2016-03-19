<?php
class validate {

    public function lists($listid)
    {
        if (strlen($listid) < 5)
        {
            return "Too short";
        }

        if (!preg_match('/^[a-zA-Z0-9 _]+$/', $listid))
            return "Only letters, digits, underscore & space allowed";

        $ct = checkexisting("lists", "listid", $listid);
        return $ct > 0 ? "Already in use":"okay";
         
    }
    public function projectname($projectname)
    {
        if (strlen($projectname) < 5)
        {
            return "Too short";
        }
        if (!preg_match('/^[a-zA-Z0-9 _]+$/', $projectname))
            return "Only letters, digits, underscore & space allowed";

        $ct = checkexisting("projects", "projectname", $projectname);
        return $ct > 0 ? "Already in use":"okay";
    }
    public function userlogin($userlogin)
    {
        if (strlen($userlogin) < 5)
        {
            return "Too short";
        }
        $ct = checkexisting("members", "userlogin", $userlogin);
        return $ct > 0 ? "Already in use":"okay";
    }
    public function lengthonly($userlogin)
    {
        if (strlen($userlogin) < 3)
        {
            return "Too short";
        }
        else return 'okay';
    }
    public function email($emal)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email Invalid Format";
        }
        else return 'okay';
    }
    public function cffieldname($fn)
    {
        if (preg_match("/([a-zA-Z0-9])/", $fn))
        {
            return 'okay';
        }
        else return "Invalid Field Name";
    }
}
?>
