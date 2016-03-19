<?php
/**
 * This is a subsystem for integration the bluestrap class
 * returns to normal procedure if $act is malformed/not meant for bluestrap
 */
$bluestrap = new bluestrap('#displayport','admin.php');
$bluestrap_um = new bluestrap('#userRightSide','admin.php');
if ($_SESSION['usertype'] == 'bcclient')
{ 
$bluestrap_um->addTransaction('roles');
}
if ($_SESSION['usertype'] == 'bcclient' || $_SESSION['usertype'] == 'admin')
{
    $bluestrap_um->addTransaction('teams');
}
$bs = $bluestrap->doAction($act);
$bs = $bluestrap_um->doAction($act);
$bsscripts = $bluestrap->generateScripts();
$bsscripts .= $bluestrap_um->generateScripts();
?>
