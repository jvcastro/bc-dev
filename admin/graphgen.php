<?php
$xaxis = explode("|",html_entity_decode($_GET['xarr']));
$yaxis = explode("|",html_entity_decode($_GET['yarr']));
$size = $_GET['size'];
$title = $_GET['title'];
$xtitle = $_GET['xt'];
$ytitle = $_GET['yt'];
require_once ('../jpgraph/jpgraph.php');
//require_once ('../jpgraph/jpgraph_line.php');
require_once ('../jpgraph/jpgraph_bar.php');
 // Width and height of the graph
if ($size == "small") $graph = new Graph(200,200);
elseif ($size == "medium") $graph = new Graph(300,300);
elseif ($size == "large") $graph = new Graph(400,400);
elseif ($size == "small-wide") $graph = new Graph(300,200);
elseif($size == "medium-wide") $graph = new Graph(480,350);
elseif($size == "large-wide") $graph = new Graph(500,400);
else  $graph = new Graph(300,200);
// Create a graph instance

 
// Specify what scale we want to use,
// int = integer scale for the X-axis
// int = integer scale for the Y-axis
$graph->SetScale('textint');

$graph->title->SetFont(FF_TAHOMA,FS_NORMAL,8);
// Setup a title for the graph

$graph->title->Set($title);
$graph->SetMargin(20,10,20,99); 
 
// Setup titles and X-axis labels
$xa = count($xaxis);
//$xint = ($xa - ($xa % 10))/ 10;
$graph->xaxis->title->Set($xtitle);
$graph->xaxis->SetTickLabels($xaxis);
$graph->xaxis->SetLabelAngle(80);
$graph->xaxis->SetFont(FF_TAHOMA,FS_NORMAL,8);
$graph->xaxis->SetTextLabelInterval(1);

// Setup Y-axis title
$graph->yaxis->title->Set($ytitle);
 
// Create the bar plot
$barplot=new BarPlot($yaxis);
 
// Add the plot to the graph
$graph->Add($barplot);
 
// Display the graph
$graph->Stroke();



?>