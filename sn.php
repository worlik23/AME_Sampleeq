<?php require('head.php');

if(isset($_GET['sn'])){
	$_SESSION['mobileSN'] = $_GET['sn'];
	$sn = $_SESSION['mobileSN'];
}
if(!empty($sn)){
    $tableBody = ['Name', 'PN', 'PCA', 'PCB', 'SN', 'G/D', 'Dept', 'Stat', 'Date', 'Hub', 'Info'];
    $dataBody = ['name', 'pn', 'pca', 'pcb', 'sn', 'type', 'dept', 'status', 'crtdt', 'hub', 'info'];
    $datas = implode(', ', $dataBody);
	$snInfo = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$sn'");
	if(!empty($snInfo)){
		showResults($snInfo, $tableBody, $conn);	
    }	else {	echo('<div class="err">SN# not found<br></div>');	}
}	else {	echo('<div class="err">SN# error<br></div>');	}


 include('footer.php');   ?>
