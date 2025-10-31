<?php require('head.php'); 

if(!empty($_GET['with'])){
    $with = implode(', ', $_GET['with']);
    $rqParams = "AND status IN($with)";
    $_SESSION['with'] = $_GET['with'];
} else {	$withs = ['1', '2'];
        	$with = implode(', ', $withs);
        	$rqParams = "AND status IN($with)";
        	$_SESSION['with'] = $withs;
}

$tableBody = ['Name', 'PN', 'PCA', 'PCB', 'G/D', 'SN', 'Dept', 'Stat', 'Date', 'Hub', 'Info'];
$dataBody = ['name', 'pn', 'pca', 'pcb', 'type', 'sn', 'dept', 'status', 'crtdt', 'hub', 'info'];
$datas = implode(', ', $dataBody);

?>
<h1>Search</h1>
<div id="searchForms">
<form class="getProject" method="get" action="" onsubmit="saveDepartment()">
<select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="projectPN" onchange="checkPN(this.value)">
	<option value="" selected disabled>0000X0000000</option>
</select>
<?php echo (isMobile()) ? '<div class="row">' : ''; ?>
<select id="department" name="department" onclick="depType(this.value)">
	<option value="" selected>Dept</option>
	<option value="aoi">AOI</option>
	<option value="olbs">OLBS</option>
	<option value="ict">ICT</option>
    <option value="eoli">EOLI</option>
    <option value="aci">ACI</option>
</select>
<select id="sampleType" name="sampleType">
	<option value="" selected>All</option>
	<option value="G">Golden</option>
	<option value="D">Dummy</option>
</select>
<select id="hub" name="hub">
	<option value="" selected>Hub</option>
    <option value="S">Slatina</option>
    <option value="B">Blučina</option>
</select>
<?php echo (isMobile()) ? '</div>' : ''; ?>
<div class="row">
<?php 
    if($userOn && !in_array($adminLevel, ['tech', 'sap'])){
        echo('<label for="withOk" class="check green">OK<input id="withOk" type="checkbox" name="with[]" value="1" '.checkedIfInArray(1).'></label>');
        echo('<label for="withPrep" class="prgs green">PREP<input id="withPrep" type="checkbox" name="with[]" value="2" '.checkedIfInArray(2).'></label>');  
    }   else {  
            echo('<input type="hidden" name="with[]" value="1">');
            echo('<input type="hidden" name="with[]" value="2">');
        }
?>
<label for="withExp" class="exp green">Exp
    <input type="checkbox" id="withExp" name="with[]" value="3" <?php echo(checkedIfInArray(3)); ?>>
</label>
<label for="withWarn" class="warn green">Rev!
    <input type="checkbox" id="withWarn" name="with[]" value="4" <?php echo(checkedIfInArray(4)); ?>>
</label>

<?php
if($userOn){
    echo('<label for="withDmg" class="dmg green">Damaged<input type="checkbox" id="withDmg" name="with[]" value="5" '.checkedIfInArray(5).'></label>');
    if($adminLevel !== 'tech'){
        echo('<label for="withInact" class="cross green">Inactive<input type="checkbox" id="withInact" name="with[]" value="0" '.checkedIfInArray(0).'></label>');
        echo('<label for="withWoff" class="rqst green">wOff<input type="checkbox" id="withWoff" name="with[]" value="9" '.checkedIfInArray(9).'></label>');
    }
}
?>
</div>
<input type="submit" name="getInfo" value="Get Info">
</form>
<hr>
<h2>Search by SN#</h2>
<form method="get" action="" class="getProject">
<input type="text" name="search" minlength="7" maxlength="40" required>
<input type="submit" name="getSnInfo" value="Search">
</form>
</div>
<?php 
echo (isMobile()) ? '<a href="#searchForms" class="btnUp" title="Get on top"></a>' : '';
?>
<hr>
<?php

if(isset($_GET['getSnInfo'])){
	$search = $_GET['search'];
    searchByTxt($conn, $search, $datas, $tableBody, "", $sessionName = 'searchRq');
}

else if(isset($_GET['getInfo'])){
	$pn = $_GET['projectPN'];
	$department = !empty($_GET['department']) ? $_GET['department'] : '';
	$sampleType = !empty($_GET['sampleType']) ? $_GET['sampleType'] : '';
	$hub = !empty($_GET['hub']) ? $_GET['hub'] : '';

	$ict = getPrjInfo($conn, $pn, 'ict');
	$olbs = getPrjInfo($conn, $pn, 'olbs');

	if($department === 'ict' && $sampleType === 'G'){   $department = 'aoi';  }

		$sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE pn='$pn' AND ('$sampleType'='' OR type='$sampleType')
				AND ('$hub'='' OR hub='$hub') $rqParams AND ('$department'='' OR dept='$department') ORDER BY crtdt DESC";
$_SESSION['searchRq'] = $sql;
$samples = query($conn, $sql);

if($samples)	{    showResults($samples, $tableBody, $conn);	}
	else {  echo (isMobile()) ? '<div class="sampleTab" id="sampleTab">' : '';
            echo('<h2>NO SAMPLES FOUND</h2>');
            echo (isMobile()) ? '</div>' : '';                  
    }
}

else if((!isset($_GET['getInfo']) || !isset($_GET['search'])) && !empty($_SESSION['searchRq'])){
    $searchRq = query($conn, $_SESSION['searchRq']);
    showResults($searchRq, $tableBody, $conn);
}

?>
<?php require('footer.php'); ?>
