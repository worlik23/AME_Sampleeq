<?php
require('head.php');
AdminOnly($userOn);
pcOnly();
?>
<h1>Batch edit</h1>
<div class="cntr"><a href="doc/multiEdit.jpg" class="manual" target="blank_" title="info"></a></div>
<?php
// #######################################################################      DISPLAY EDITED SAMPLES      <<<<<<<<
if(isset($_POST['displayInfo']) && !empty($_POST['batches'])){

$tableBody = ['Name', 'PN', 'PCA', 'PCB', 'G/D', 'SN', 'Dept', 'Stat', 'Date', 'Hub', 'Info'];
tabColumns($tableBody, 'sampleList');
$dataBody = ['name', 'pn', 'pca', 'pcb', 'type', 'sn', 'dept', 'status', 'crtdt', 'hub', 'info'];
$datas = implode(', ', $dataBody);
$batches = array_unique($_POST['batches']);
foreach($batches as $batch){
    $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$batch'";
    $sam = query($conn, $sql);
    showResults($sam, $tableBody = false, $conn);
}       echo('</tr></tbody></table>');
	echo('<form method="post">');
		foreach($batches as $b){
			echo('<input type="hidden" name="batchSN[]" value="'.$b.'">');
		}
    if (in_array($adminLevel, $engGroup)) {
	   echo(selectionPCA(false, false) . selectionPCB(false, false));
       echo(statusSelection(false, false));
    }
    echo('<select name="hub">');
    echo('<option value="">Hub</option>');
	echo('<option value="S">Slatina</option>');
	echo('<option value="B">Blucina</option>');
	echo('</select>');
    
	echo('<input type="submit" name="editBatch" value="Edit all">');
	echo('</form>');
	echo('<div class="err"><a href="multiEdit.php">Reset</a></div>');
}
// #######################################################################      EDIT DISPLAYED SAMPLES      <<<<<<<<
if(isset($_POST['editBatch'])){
    $allSN = $_POST['batchSN'];
    $hub = $_POST['hub'];
    $pca = $_POST['pca'];
    $pcb = $_POST['pcb'];
    $stat = $_POST['status'];
    $updates = [];
    $selects = [];

    if(!empty($hub)){    $updates[] = " hub = '$hub'";      $selects[] = 'hub';     }
    if(!empty($pca)){    $updates[] = " pca = '$pca'";      $selects[] = 'pca';     }
    if(!empty($pcb)){    $updates[] = " pcb = '$pcb'";      $selects[] = 'pcb';     }
    if(!empty($stat)){   $updates[] = " status = '$stat'";  $selects[] = 'status';  }
    if(!empty($updates)){   
        $update = implode(', ', $updates);
        $docTxt = "\n ".$dttm." \t \t |\t \t".$_SESSION['online']['icz']." \t \t | \t \t".$update."\t \t | \t \t <<< BATCH EDIT ||| \n";
        $emailTxt = $docTxt."<br>";
        foreach($allSN as $sn){
            $sql = "UPDATE dbo.SAMPLEEQ_sampleTab SET " . $update . " WHERE sn = ?";
            $params = array($sn);
            $editing = queryUpdate($conn, $sql, $params);
            if($editing){
                        $emailTxt .= getSNdata($sn);
                        $docTxt .= $sn . "\t \t - \t \t OK \n";
            }   else {  $docTxt .= $sn . "\t \t - \t \t ERROR \n";    }
        }
    if(!empty($stat) && $stat === '9') {  
        $wOffSubject = 'Samples to WRITE-OFF';
        $details = 'SAMPLEEQ & ENGEEQ | Richeeq©2025';
        mailNotify($wOffSubject, $emailTxt, $details, array('9'));   
    }
    $file = './doc/logs.txt';
	$log = fopen($file, 'a');
	   if($log){
			fwrite($log, $docTxt);
			fclose($log);
	   }
    }
}
?>

<form id="batchForm" method="post" class="columnForm">
	<div id="batchList"></div>
<hr>
<div id="continueToEdit" class="hidden">
<input type="submit" id="editAll" name="displayInfo" value="Edit samples">
</div>
</form><br>
<div class="row">
<input type="text" id="batch" placeholder="Insert SN# & press enter" value="">
<button id="nextBtn">+Add</button>
</div>
<div id="errorReport"></div>
<?php include('footer.php'); ?>
<script src="js/multiEdit.js?v=1.1" type="text/javascript" defer></script>
