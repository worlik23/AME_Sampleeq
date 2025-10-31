<?php require('head.php');
    AdminOnly($userOn);
?>
<h1>Samples management</h1>
<form id="getProject" method="post" onsubmit="saveDepartment()">
<select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="projectPN" onchange="checkPN(this.value)" required>
	<option value="" selected disabled>0000X0000000</option>
</select>
<select id="department" name="department" onclick="depType(this.value)">
	<option value="" selected>Department</option>
    <option value="aoi">AOI</option>
	<option value="eoli">EOLI</option>
</select>
<select id="sampleType" name="sampleType">
	<option value="" selected diabled>G+D</option>
	<option value="G">Golden</option>
	<option value="D">Dummy</option>
</select>
<select id="hub" name="hub">
    <option value="" selected>Hub</option>
    <option value="S">Slatina</option>
    <option value="B">Blučina</option>
</select>
<input type="submit" name="getInfo" value="Get Info">
</form>

<h2>Search by SN#</h2>
<form method="post">
<input type="text" name="search" minlength="5" maxlength="40" required>
<input type="submit" name="getPn" value="Search">
</form>
<?php

$tableBody = ['Name', 'PN#', 'PCA', 'PCB', 'G/D', 'SN#', 'Dept', 'OK', 'Date', 'Hub', 'Info'];
$dataBody = ['name', 'pn', 'pca', 'pcb', 'type', 'sn', 'dept', 'status', 'crtdt', 'hub', 'info'];
$datas = implode(', ', $dataBody);

if(isset($_POST['getInfo'])){

$pn = $_POST['projectPN'];
$dept = !empty($_POST['department']) ? $_POST['department'] : '';
$sampleType = !empty($_POST['sampleType']) ? $_POST['sampleType'] : '';
$hub = $_POST['hub'];

$pca = getPrjInfo($conn, $pn, 'pca');
$pcb = getPrjInfo($conn, $pn, 'pcb');
$ict = getPrjInfo($conn, $pn, 'ict');
$olbs = getPrjInfo($conn, $pn, 'olbs');

$samples = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE pn='$pn' AND ('$sampleType'='' OR type = '$sampleType')
										AND ('$dept' = '' OR dept = '$dept') AND ('$hub' = '' OR hub = '$hub') AND status NOT IN ('0', '9')
										ORDER BY status DESC");
$_SESSION['editRq'] = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE pn='$pn' AND ('$sampleType'='' OR type = '$sampleType')
										AND ('$dept' = '' OR dept = '$dept') AND ('$hub' = '' OR hub = '$hub') AND status NOT IN ('0', '9')
										ORDER BY status DESC";
if($samples){
    showResults($samples, $tableBody, $dataBody, $conn);
}	else {	echo('<h3>NO SAMPLES FOUND</h3>');	}
}

else if(isset($_POST['getPn'])){
	$search = htmlspecialchars($_POST['search']);
    $search_1 = mb_substr($search, 0, mb_strlen($search) - 1);
    $search_2 = mb_substr($search, 0, mb_strlen($search) - 2);
    $firstAtt = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search' AND status NOT IN ('0', '9') ORDER BY status DESC");
    if(!$firstAtt){
        $secondAtt = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_1' AND status NOT IN ('0', '9') ORDER BY status DESC");
        if(!$secondAtt){
            $thirdAtt = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_2' AND status NOT IN ('0', '9') ORDER BY status DESC");
            if(!$thirdAtt){
                $likeAtt = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn LIKE '%$search%' AND status NOT IN ('0', '9') ORDER BY status DESC");
                if(!$likeAtt){  die('Not found');  }
                else {
                        $_SESSION['editRq'] = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn LIKE '%$search%' AND status NOT IN ('0', '9') ORDER BY status DESC";
                        showResults($likeAtt, $tableBody, $dataBody, $conn);
                }
            } else {	$_SESSION['editRq'] = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_2' AND status NOT IN ('0', '9') ORDER BY status DESC";
                        showResults($thirdAtt, $tableBody, $dataBody, $conn);
			}
        } else {		$_SESSION['editRq'] = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_1' AND status NOT IN ('0', '9') ORDER BY status DESC";
                        showResults($secondAtt, $tableBody, $dataBody, $conn);
		}
    } else {			$_SESSION['editRq'] = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search' AND status NOT IN ('0', '9') ORDER BY status DESC";
                        showResults($firstAtt, $tableBody, $dataBody, $conn);
	}
}

else if((!isset($_POST['getInfo']) || !isset($_POST['getPn'])) AND !empty($_SESSION['editRq'])){
    $editRq = query($conn, $_SESSION['editRq']);
    showResults($editRq, $tableBody, $dataBody, $conn);
}

require('footer.php');?>
