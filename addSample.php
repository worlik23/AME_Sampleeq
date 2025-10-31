<?php
require('head.php');
AdminOnly($userOn);
require_once('phpqrcode\qrlib.php');
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
?>
<h1>+Add sample</h1>
<form id="addSample" method="post" onsubmit="saveProject()">
<div>
<div class="envelope">
<label for="snText" id="jsOut"></label>
<input type="text" id="snText" name="sn" minlength="29" maxlength="29" placeholder="SN#" onblur="checkSN(this.value); checkIfExist(this.value)" required>
</div>
<select id="projectName" name="name" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="pn" onchange="replacement.innerText=''" required>
	<option value="" selected disabled>Select PN#</option>
</select>
<?php echo(selectionPCA());
      echo(selectionPCB());
?>
</div><div>
<input type="date" id="crtdt" max="<?php echo($today); ?>" min="<?php echo($yearBack); ?>" name="crtdt" required>
<select id="department" name="dept" onblur="depType(this.value)" onchange="getReplacement(this.value)" required>
	<option value="" selected disabled>Department</option>
    <option value="aoi">AOI</option>
	<option value="eoli">EOLI</option>
</select>
<select id="sampleType" name="sampleType" required>
	<option value="" selected>Type</option>
	<option value="G">Golden</option>
	<option value="D">Dummy</option>
</select>
<select id="hub" name="hub" required>
    <option value="" selected>Hub</option>
    <option value="S">Slatina</option>
    <option value="B">Blučina</option>
</select>
<select name="status" required>
    <option value="" selected>Status</option>
    <option value="1">OK</option>
    <option value="2">PREP</option>
</select>
</div>
<textarea id="descInfo" name="info" maxlength="535" placeholder="Defects | Info"></textarea>
<input type="hidden" id="replacement" name="replacement" value="" disabled>
<input type="submit" name="addSample" id="addBtn" value="+Add">
</form>

<?php
$dataBody = ['[name]', '[pn]', '[pca]', '[pcb]', '[crev]', '[sn]', '[type]', '[info]', '[crtdt]', '[status]', '[hub]', '[dept]'];
$datas = implode(', ', $dataBody);

if(isset($_POST['addSample'])){
    $name = $_POST['name'];
    $pn = $_POST['pn'];
    $pca = $_POST['pca'];
    $pcb = $_POST['pcb'];
    $dept = $_POST['dept'];
    $snNum = strtoupper(trim($_POST['sn']));

if(isset($_POST['replacement'])){
	$snReplace = array('9', $_POST['replacement']);
	$diableSql = "UPDATE dbo.SAMPLEEQ_sampleTab SET status = ? WHERE sn = ?";
	$disableSN = queryUpdate($conn, $diableSql, $snReplace);
		if($disableSN){	echo('<div class="err">Replacement successful. Old sample has been disabled</div>');	}
}

if($dept !== 'eoli'){
    if($name === 'Gelato' OR $name === 'Daisy'){    $sn = $snNum;   }
else {
        $pcbQty = querySingle($conn, "SELECT ppp FROM dbo.SAMPLEEQ_projects WHERE pn = '$pn'");
        if((int)$pcbQty > 35){    $sn = substr($snNum, 0, -2);      }
            else             {    $sn = substr($snNum, 0, -1);      }
}
} else {    $sn = $snNum;   }

$snExist = querySingle($conn, "SELECT sn FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$sn'");
    if(!$snExist){
	generateQRCode($sn);

if($sn[0] === '/' && $sn[10] === '-'){
    $crev = $sn[11].$sn[12];
}   else {  $crev = 'XX';    }

    $crtdt = date('Y-m-d', strtotime($_POST['crtdt']));
    $sampleType = isset($_POST['sampleType']) ? $_POST['sampleType'] : 'G';
if($dept === 'ict' && $sampleType === 'G'){ $dept = 'aoi';  }
    $status = $_POST['status'];
    $hub = $_POST['hub'];
    $info = trim($_POST['info']);

$insertParams = array($name, $pn, $pca, $pcb, $crev, $sn, $sampleType, $info, $crtdt, $status, $hub, $dept);
$details = 'AUTOMATIC UPGRADE - Added sample with new revision';
upgradeRevision($pn, $pca, $details);

$sql = "INSERT INTO dbo.SAMPLEEQ_sampleTab ($datas)	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$inserting = queryUpdate($conn, $sql, $insertParams);
    if($inserting){
        $allInfo = implode(" | ", $insertParams);
        $logTxt = "\n".$dttm." | ".$userOn['icz']."\t| ".$allInfo."\t |<--- NEW SAMPLE +++";
        writeToLog($logTxt);
        echo('<div class="err">Sample has been created</div>');
    }
}   else {  echo('<div class="err">Sample already exist</div>'); }
}
?>

<div id="replaceTab"></div>
<div id="projectTable"></div>

<?php require('footer.php');?>
