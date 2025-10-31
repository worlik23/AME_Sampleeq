<?php 
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function saveToDb($conn, $prj, $ref, $pn, $info, $revision, $dttm, $prjName) {
    $error = false;
    global $revLevel;
    if (strlen($ref) > 7) {                         $error = 'Reference '.$ref.' name length is too long';    }
    if (strlen($pn) > 12 || strlen($pn) < 12) {     $error = 'Part number error - incorrect format';          }
    if (!$error) {
    $sqlParams = array($prj, $ref, $pn);
    $exist = querySingle($conn, "SELECT rev FROM dbo.SAMPLEEQ_parts WHERE prj = ? AND ref = ? AND pn = ?", $sqlParams);
        if ($exist && $revLevel[$exist] < $revLevel[$revision]) {   
            $updateParams = array($revision, $prj, $ref, $pn);
            $updateRev = queryUpdate($conn, "UPDATE dbo.SAMPLEEQ_parts SET rev = ? WHERE prj = ? AND ref = ? AND pn = ?", $updateParams);
            if ($updateRev) {
                $log = "\n".$dttm." | ".$_SESSION['online']['icz']." | ". $prjName.' '.$prj.' | '.$pn." | ".$ref." | ".$exist." --> ".$revision." | <<--- CHANGE REVISION";        
                writeToPartLog($log);
                echo ('<tr class="green"><td>UPDATED</td><td>'.$ref.'</td><td>'.$pn.'</td><td>'.$exist.' --> '.$revision.'</td><td>'.$info.'</td></tr>');
                return true;  
            }
        }
    if (!$exist) {
        $sql = "INSERT INTO dbo.SAMPLEEQ_parts (prj, ref, pn, info, rev) VALUES ('$prj', '$ref', '$pn', '$info', '$revision')";
        $insert = query($conn, $sql);
        $log = "\n".$dttm." | ".$_SESSION['online']['icz']." | ".$prjName.' '.$prj.' | '.$pn." | ".$ref." | ".$revision." | <<--- NEW PART";        
        writeToPartLog($log); 
        echo ('<tr class="green"><td>CREATED</td><td>'.$ref.'</td><td>'.$pn.'</td><td>'.$revision.'</td><td>'.$info.'</td></tr>');
        return true;
    }   
        else {  
                echo ('<tr><td>EXIST</td><td>'.$ref.'</td><td>'.$pn.'</td><td>'.$exist.'</td><td>'.$info.'</td></tr>');   
        }
    }   else    {  echo ($error);  }
}

function analyseBOM($conn, $prjName, $prj, $bomFile, $dttm) {

$logOk = false;
$tableBody = ['Status', 'Ref_ID', 'PN_#', 'Revision', 'Description'];
$bomSheet = IOFactory::load($bomFile);
$sheet = $bomSheet->getActiveSheet();
$maxRow = $sheet->getHighestRow();
$notAllowed = ['Inst. Point', 'Installation Point', '-', ''];
// ###########################################################################################################          VERIFY PROJECT NAME     <<<<<   #####
$verifyStart = strpos($sheet->getCell('A3')->getValue(), '1395K');
$verifyEnd = $verifyStart + 12;
$verify = substr($sheet->getCell('A3')->getValue(), $verifyStart, $verifyEnd - $verifyStart);
$revisionStart = strpos($sheet->getCell('A3')->getValue(), 'Revision:') + 9;
$revisionEnd = $revisionStart + 3;
$revision = substr($sheet->getCell('A3')->getValue(), $revisionStart, $revisionEnd - $revisionStart);
    if ($verify && $verify === $prj) {
        $details = 'AUTO-UPDATE | NEW BOM REVISION UPLOADED BY '.$_SESSION['online']['username'];
        upgradeRevision($prj, $revision, $details);
        $bomName = $prjName.'_'.substr($prj, 8,4).'_'.$revision;
        $bomImport = 'bomImport/'.$bomName.'.xls';
        $logText = "\n".$dttm." | ".$_SESSION['online']['icz']." | \t".$prjName." - ".$prj." \t | \t".$bomImport."\t | \t NEW BOM HAS BEEN UPLOADED <<<<< \n";
// ###########################################################################################################          FIRST CYCLE             <<<<<   #####
for($row = 12; $row <= $maxRow; $row++) {
    if ($sheet->getCell('I'.$row)->getValue() === '-') {
        $phantom[$sheet->getCell('B'.$row)->getValue()][] = ['pn' => $sheet->getCell('C'.$row)->getValue(), 'info' => $sheet->getCell('F'.$row)->getValue()];
    }
}
    tabColumns($tableBody, 'allParts');
for ($row = 10; $row <= $maxRow; $row++) {
    if (!empty($sheet->getCell('I'.$row)->getValue()) && $sheet->getCell('I'.$row)->getValue() !== '-') {
    $reference = $sheet->getCell('I'.$row)->getValue();
    if (strpos($reference, ',') !== false) {
        $refs = explode(',', $reference);
    }   else {  $refs = [0 => $reference]; }
    if (!empty($sheet->getCell('C'.$row)->getValue())) {
        $partNum = $sheet->getCell('C'.$row)->getValue();
        $lastPN = $partNum;
    }   else {  $partNum = $lastPN;	}
    foreach($refs as $ref) {
    if (isset($phantom[$partNum])) {
        foreach ($phantom[$partNum] as $ph) {
            $pn = $ph['pn'];
            $info = $ph['info'];
            $logOk = saveToDb($conn, $prj, $ref, $pn, $info, $revision, $dttm, $prjName); 
        }
    }
        else {  $pn = $partNum;
                $info = $sheet->getCell('F'.$row)->getValue();
                $logOk = saveToDb($conn, $prj, $ref, $pn, $info, $revision, $dttm, $prjName);
        }
    }
    }
}

    echo('</tbody></table>');
        if ($logOk === true && move_uploaded_file($bomFile, $bomImport)) {
            writeToLog($logText);
        }
    }
        else {  echo ('<p class="noResult">');
                echo ('The uploaded BOM file data isn\'t for selected PN<br>');
                echo ('BOM file : '. $verify . '<br>Your selection : '. $prj.'</p>');       
        }
}
?>