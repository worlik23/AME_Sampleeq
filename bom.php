<?php 
require('head.php'); 
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

function writeCells($worksheet, $row, $values = []) {
    $cols = ['ref' => 'A', 'x' => 'B', 'y' => 'C', 'z' => 'D', 'pn' => 'E', 'side' => 'F', 'type' => 'G'];
    foreach($values as $key => $val) {
        $worksheet->getCell($cols[$key].$row)->setValue($val);
    }
}
?>
<h1>AOI COMBAD</h1>
<form method="post" class="row" enctype="multipart/form-data" onsubmit="showLoadingAnimation();">
<select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="projectPN" onchange="checkPN(this.value)" required>
	<option value="" selected disabled>0000X0000000</option>
</select>
<label for="bomFile">BOM.xls
<input type="file" id="bomFile" accept=".xls" name="bomFile" required>
</label>
<label for="cadFile">CAD.cad
<input type="file" id="cadFile" accept=".cad" name="cadFile" required>
</label>
<input type="submit" name="crtCad" value="Create CAD">
</form>

<h2>AXI CAD</h2>
<form method="post" class="row" enctype="multipart/form-data">
<label for="axiCsv">Insert .csv from AOI
<input type="file" id="axiCsv" accept=".csv" name="axiCsv" required>
</label>
<input type="submit" name="axi" value="AXI CAD">
</form>

<?php
if (isset($_POST['crtCad'])) {

$prj = $_POST['projectPN'];

$bom_file = $_FILES['bomFile']['tmp_name'];
$bomSheet = IOFactory::load($bom_file);
$sheet = $bomSheet->getActiveSheet();
$maxRow = $sheet->getHighestRow();
$bomList = [];
$notAllowed = ['Inst. Point', 'Installation Point', '-', ''];
$virtual = [];
$lastPn = '';
// ###########################################################################################################          VERIFY PROJECT NAME     <<<<<   #####
$verifyStart = strpos($sheet->getCell('A3')->getValue(), '1395K');
$verifyEnd = $verifyStart + 12;
$verify = substr($sheet->getCell('A3')->getValue(), $verifyStart, $verifyEnd - $verifyStart);
    if ($verify !== $prj) {
        echo ('<p class="noResult">The uploaded BOM file isn\'t compatible with your selection.<br>BOM file : '. $verify . '<br>Your selection : '. $prj.'</p>');
    }
// ###########################################################################################################          FIRST CYCLE             <<<<<   #####
for($row = 1; $row <= $maxRow; $row++) {
	$colVirtual = $sheet->getCell('B'.$row)->getValue();
	if (!in_array($colVirtual, $virtual)) {
        $virtual[$colVirtual] = $sheet->getCell('C'.$row)->getValue();
    }
}
// ###########################################################################################################          SECOND CYCLE            <<<<<   #####
for($row = 1; $row <= $maxRow; $row++) {
	$colRef = $sheet->getCell('I'.$row)->getValue();
	if (!in_array($colRef, $notAllowed)) {
		$pn = $sheet->getCell('C'.$row)->getValue();
	if (!empty($sheet->getCell('C'.$row)->getValue())) {
        $lastPn = $sheet->getCell('C'.$row)->getValue(); 
    }   
        else {	$pn = $lastPn;	}
	if (!empty($virtual[$pn])) {
        $pn = $virtual[$pn];
    }	
	if (strpos($colRef, ',') !== false) {
		$references = explode(',', $colRef);
		foreach($references as $r) {
			$ref = $r;
			$bomList[$ref] = $pn;
		}
	}  else {	$ref = $colRef;
				$bomList[$ref] = $pn;   }
	}
}

$cadName = $_POST['projectName'].substr($prj, 8,4).'_'.date('myHis');
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
        $filePath = 'xls/'.$cadName.'.xlsx';
        $spreadsheet = new Spreadsheet($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $components = [];
    for($i = 1; $i < count($lines) - 6; $i+=6) {
        if (substr($lines[$i], 11, 2) !== 'TP' && substr($lines[$i], 11, 2) !== 'LG') {
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
            $components[$row]['type'] = substr($lines[$i + 5], 8, strlen(substr($lines[$i + 5], 8)) - 5);
            writeCells($worksheet, $row, $components[$row]);
            }
        }
    }
    }
    $saveAs = new Xlsx($spreadsheet);
        $saveAs->save($filePath);
        echo('<a class="cntr" href="'.$filePath.'">Download CAD</a>');
        
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
    /*
	foreach($bomList as $key => $bom) {
		echo ($key."\t -> \t".$bom.'<br>');
	}*/
}

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
    file_put_contents($axiFile, $axiCad);
    echo('<a class="cntr" href="'.$axiFile.'" download>Download AXI CAD</a>');
}

include('footer.php'); ?>