<?php require('head.php'); ?>
<h1>Write Off</h1>
<?php

if(isset($_POST['deactivate'])){
	if(!empty($_POST["sapSNs"])){
		$sapSNs = $_POST["sapSNs"];
		$dttm = date('d.m.y - h:i:s');
		$file = './doc/wOff.txt';
		$handle = fopen($file, 'a');
		$docText = "Write OFF - ".$dttm. "\n";

		foreach($sapSNs as $sn){
			$namePN = queryOne($conn, "SELECT name, pn FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$sn'");

			$params = array('0', $sn);
			$sqlDeact = "UPDATE dbo.SAMPLEEQ_sampleTab SET status = ? WHERE sn = ?";
			$deactivation = queryUpdate($conn, $sqlDeact, $params);
				if(!$deactivation){
					echo($namePN['name']. " - ". $namePN['pn'] . " - " . $sn . " Write OFF failed");
					$docText .= $namePN['name']. " - ". $namePN['pn'] . " - " . $sn . " Write OFF failed !! <<<<\n";
				}	else {	$docText .= $namePN['name']. " - ". $namePN['pn'] . " - " . $sn . " OK\n";	}
		}
			if($handle){
				fwrite($handle, $docText);
				fclose($handle);
			}
	}
}

$tableBody = ['Name', 'PN', 'PCA', 'PCB', 'G/D', 'SN', 'Dept', 'Stat', 'Date', 'Hub', 'Info'];
$dataBody = ['name', 'pn', 'pca', 'pcb', 'type', 'sn', 'dept', 'status', 'crtdt', 'hub', 'info'];
$datas = implode(', ', $dataBody);
$sap = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE status = '9'");

showResults($sap, $tableBody, $conn);

?>

<form method="POST" class="sapForm">
<div id="sapSelect"></div>
<input type="submit" id="deactivateBtn" name="deactivate" value="Write OFF" disabled>
</form>

<?php require('footer.php'); ?>
<script src="js/sap.js" type="text/javascript" defer></script>
