<?php 
require('head.php');
AdminOnly(in_array($adminLevel, ['9', 'aoi']));
require('php/bomChecker.php'); 
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

function writeCells($worksheet, $row, $values = []) {
    $cols = ['ref' => 'A', 'x' => 'B', 'y' => 'C', 'z' => 'D', 'pn' => 'E', 'type' => 'F', 'side' => 'G'];
    foreach($values as $key => $val) {
        if ($key === 'side') {  continue;   }
        $worksheet->getCell($cols[$key].$row)->setValue($val);
    }
}
?>
<h1>AOI CAD generator</h1>
<div class="cntr"><a href="doc/cad_axi.jpg" class="manual" target="blank_" title="info"></a></div>
<form method="post" class="row" enctype="multipart/form-data" onsubmit="showLoadingAnimation();">
<select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="projectPN" onchange="checkPN(this.value)" required>
	<option value="" selected disabled>0000X0000000</option>
</select>
    <label for="cadFile">Kostal.cad
<input type="file" id="cadFile" accept=".cad" name="cadFile" required>
    </label>
<input type="submit" name="crtCad" value="Create CAD">
</form>

<h2>AXI Generator</h2>
<form method="post" class="row" enctype="multipart/form-data">
<label for="axiCsv">AOI_file.csv
<input type="file" id="axiCsv" accept=".csv" name="axiCsv" required>
</label>
<input type="submit" name="axi" value="AXI CAD">
</form>

<?php
if (isset($_POST['crtCad'])) {

$prj = $_POST['projectPN'];
$prjName = $_POST['projectName'];
$topRows = 0;
$botRows = 0;
$cad_file = $_FILES['cadFile']['tmp_name'];
$file = fopen($cad_file, 'r');
    $content = fread($file, filesize($cad_file));
    fclose($file);
    $start = strpos($content, '$COMPONENTS');
    $end = strpos($content, '$ENDCOMPONENTS');
if ($start !== false && $end !== false) {
    $content = substr($content, $start + 11, $end - ($start + 11));
    $lines = explode("\r", $content);
    $row = 0;
    $components = [];
$error = checkNewestRevision($prj);
    if ($error === false) {
    $newestRev = getPrjInfo($conn, $prj, 'pca');
    $cadName = $_POST['projectName'].substr($prj, 8,4).'_'.$newestRev;
    $cadImport = 'cadImport/'.$cadName.'.cad';
    $cadUploadLog = "\n".$dttm." | ".$_SESSION['online']['icz']." | \t".$prjName." - ".$prj." \t | \t".$cadImport."\t | \t NEW CAD HAS BEEN UPLOADED <<<<< \n";
    if (move_uploaded_file($cad_file, $cadImport)) {
        writeToLog($cadUploadLog);
    }   
        $cadTop = 'cad/'.$cadName.'_TOP.xlsx';
    $spreadsheetTop = new Spreadsheet($cadTop);
    $worksheetTop = $spreadsheetTop->getActiveSheet();
    $cadBot = 'cad/'.$cadName.'_BOT.xlsx';
    $spreadsheetBot = new Spreadsheet($cadBot);
    $worksheetBot = $spreadsheetBot->getActiveSheet();
    
    $bomList =[];
    $bomData = query($conn, "SELECT ref, pn FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' AND rev = '$newestRev'");
    foreach ($bomData as $bom) {
        $bomList[$bom['ref']] = $bom['pn'];
    }
        $noComponents = ['LG', 'TP', 'MP'];
        for($i = 1; $i < count($lines) - 6; $i+=6) {
        if (!in_array(substr($lines[$i], 11, 2), $noComponents)) {
            $refer = trim(substr($lines[$i], 10));
            if (isset($bomList[$refer])) {
            $row++;
            $components[$row]['ref'] = substr($lines[$i], 10);
            $components[$row]['pn'] = $bomList[$refer];
            $pos = explode(" ", substr($lines[$i + 2], 6));
            $components[$row]['x'] = $pos[1];
            $components[$row]['y'] = $pos[2];
            $components[$row]['side'] = substr($lines[$i + 3], 7, 3);
            $angle = explode('.', substr($lines[$i + 4], 9));
            $components[$row]['z'] = $angle[0];
            $typeContent = substr($lines[$i + 5], 8, strlen(substr($lines[$i + 5], 8)) - 5);
                if (strpos($typeContent, 'sc_') !== false) {
                    $typeArray = explode('_', $typeContent);
                    $type = $typeArray[0].'_'.$typeArray[1].'_'.$typeArray[2]; 
                }   
                    else {  $type = $typeContent;   }
            $components[$row]['type'] = $type;
                if ($components[$row]['side'] === 'TOP') {
                    $topRows++;
                    writeCells($worksheetTop, $topRows, $components[$row]);
                }   
                else {
                    $botRows++;
                    writeCells($worksheetBot, $botRows, $components[$row]);
                }
            }
        }
    }
    }   else {  echo ($error); }
    if ($topRows > 1 || $botRows > 1) {     echo ('<div class="cntr">');
        if ($topRows > 1) {
            $saveAsTop = new Xlsx($spreadsheetTop);
            $saveAsTop->save($cadTop);
                echo('<a href="'.$cadTop.'">Download CAD - TOP</a>');
        }
        if ($botRows > 1) {
            $saveAsBot = new Xlsx($spreadsheetBot);
            $saveAsBot->save($cadBot);
                echo('<a href="'.$cadBot.'">Download CAD - BOT</a>');
        }
                echo ('</div>');    
    }
    
        $tableBody = ['Ref_ID', 'PN', 'X', 'Y', 'Rotation', 'Side', 'Type'];
        tabColumns($tableBody, 'allParts');
        foreach($components as $component) {
            echo('<tr><td>'.$component['ref'].'</td>');
            echo('<td>'.$component['pn'].'</td>');
            echo('<td>'.$component['x'].'</td>');
            echo('<td>'.$component['y'].'</td>');
            echo('<td>'.$component['z'].'</td>');
            echo('<td>'.$component['side'].'</td>');
            echo('<td>'.$component['type'].'</td></tr>');
        }
        echo('</tbody></table>');
    }
}
// ##########################################################################################################################          AXI-GENERATOR       <<<<<
if (isset($_POST['axi'])) {
    $tmp_file = $_FILES['axiCsv']['tmp_name'];
    $file = fopen($tmp_file, 'r');
    $content = fread($file, filesize($tmp_file));
    fclose($file);
    $datas = [];
    $axiCad = '';
    $lines = explode("\n", $content);
    foreach($lines as $key => $line) {
        $columns = explode(',',$line);
        if (!empty($columns[1])){
            $datas[] = ['ref' => $columns[1], 'pn' => $columns[2], 'x' => $columns[5], 'y' => $columns[6], 'rot' => $columns[9]];
        }
    }
    foreach($datas as $data) {
        foreach ($data as $d){
            $axiCad .= $d . "\t";
        }
            $axiCad .= "\n";
    }
    $axiFile = 'axi/'.date('dm-His').'.aoi';
    if (file_put_contents($axiFile, $axiCad)) {
        echo('<div class="cntr"><a href="'.$axiFile.'" download>Download AXI CAD</a></div>');
    }   else {  echo ('<div class="cntr">Some error occured, please go screaming on Richeeq :-/ </div>');  }
}

include('footer.php'); ?>