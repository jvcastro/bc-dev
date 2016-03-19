<?php // content="text/plain; charset=utf-8"
$datas = explode("|",html_entity_decode($_GET['datas']));
$labels = explode("|",html_entity_decode($_GET['labels']));
include ("../jpgraph/jpgraph.php");
include ("../jpgraph/jpgraph_pie.php");
require_once ('../jpgraph/jpgraph_pie3d.php');
// Some data and the labels
//$datas   = array(19,12,4,7,3,12,3);
//$labels = array("First\n(%.1f%%)",
//                "Second\n(%.1f%%)","Third\n(%.1f%%)",
//                "Fourth\n(%.1f%%)","Fifth\n(%.1f%%)",
 //               "Sixth\n(%.1f%%)","Seventh\n(%.1f%%)");
 
// Create the Pie Graph.
$graph = new PieGraph(300,200);
$graph->SetShadow();
// Set A title for the plot
//$graph->title->Set('Dispositions');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,8);
$graph->title->SetColor('darkgray');
$graph->SetMargin(0,0,00,00);
// Create pie plot
$p1 = new PiePlot($datas);
$p1->SetCenter(0.5,0.55);
$p1->SetSize(0.2);
 
// Setup the labels to be displayed
$p1->SetLabels($labels);
 
// This method adjust the position of the labels. This is given as fractions
// of the radius of the Pie. A value < 1 will put the center of the label
// inside the Pie and a value >= 1 will pout the center of the label outside the
// Pie. By default the label is positioned at 0.5, in the middle of each slice.
$p1->SetLabelPos(1);
$p1->SetGuideLines(true,true);
$p1->SetGuideLinesAdjust(0.8);
// Setup the label formats and what value we want to be shown (The absolute)
// or the percentage.
$p1->SetLabelType(PIE_VALUE_PER);
$p1->value->Show();
$p1->value->SetFont(FF_ARIAL,FS_NORMAL,5);
$p1->value->SetColor('darkgray');

// Add and stroke
$graph->Add($p1);
$graph->Stroke();
 
?>