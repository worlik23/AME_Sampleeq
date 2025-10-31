<?php
require('head.php');
AdminOnly($userOn);

if(isset($_GET['editSn'])){
	$_SESSION['editSn'] = $_GET['editSn'];
	$sn = $_SESSION['editSn'];
}
else if(isset($_POST['editSn'])){
	$_SESSION['editSn'] = $_POST['editSn'];
	$sn = $_SESSION['editSn'];
}
if(!empty($sn)){
    $dataBody = ['name', 'pn', 'pca', 'pcb', 'sn', 'type', 'dept', 'status', 'crtdt', 'hub', 'info'];
    $datas = implode(', ', $dataBody); 
    $editedInfo = queryOne($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$sn'");
}  
    else{   echo('<div class="err">SN# error<br><a href="index.php">Back</a></div>');    
            include('footer.php');
            die();  
    }

function selectedEdit($x, $y){
	if($x == $y){	echo('selected');	}
}

if(isset($_POST['editInfo'])){

$pcaRev = $_POST['pca'];
$pcbRev = $_POST['pcb'];
$editType = $_POST['type'];
$editInfo = $_POST['info'];
$editStatus = $_POST['status'];
$editHub = $_POST['hub'];
$editDept = $_POST['dept'];

$sql = "UPDATE dbo.SAMPLEEQ_sampleTab SET pca = ?, pcb = ?, type = ?, info = ?, status = ?, hub = ?, dept = ? WHERE sn = ?";
$params = array($pcaRev, $pcbRev, $editType, $editInfo, $editStatus, $editHub, $editDept, $sn);
$editing = queryUpdate($conn, $sql, $params);

	if($editing){  crtLog($sn, $pcaRev, $pcbRev, $editType, $editDept, $editStatus, $editHub, $editInfo, $conn, $dttm, $editedInfo);	}
        else {  echo('Error');  }
}
?>
<a href="index.php" class="cntrl">&lt;&lt;back</a>
<h1>Edit sample</h1>
<div class="big-header"><?php echo($sn); ?></div><br>
<form id="addSample" method="post" onsubmit="return confirmEdit()">
<div class="editProject">
<select name="projectName">
	<option value="" selected disabled><?php echo($editedInfo['name']); ?></option>
</select>
<select name="projectPN">
	<option value="" selected disabled><?php echo($editedInfo['pn']); ?></option>
</select>
<input type="hidden" name="editSn" value="<?php echo($sn); ?>">
<?php echo(selectionPCA($editedInfo['pca']));
      echo(selectionPCB($editedInfo['pcb']));
?>
<select name="hub" required>
    <option value="S" <?php selectedEdit($editedInfo['hub'], 'S'); ?>>Slatina</option>
    <option value="B" <?php selectedEdit($editedInfo['hub'], 'B'); ?>>Blučina</option>
</select>
<select name="type" required>
    <option value="G" <?php selectedEdit($editedInfo['type'], 'G'); ?>>Golden</option>
    <option value="D" <?php selectedEdit($editedInfo['type'], 'D'); ?>>Dummy</option>
</select>
<select name="status" required>
    <option value="0" <?php selectedEdit($editedInfo['status'], 0); ?> disabled>OUT</option>
    <option value="1" <?php selectedEdit($editedInfo['status'], 1); ?>>OK</option>
	<option value="2" <?php selectedEdit($editedInfo['status'], 2); ?>>Prep</option>
    <option value="3" <?php selectedEdit($editedInfo['status'], 3); ?>>Expired</option>
    <option value="4" <?php selectedEdit($editedInfo['status'], 4); ?>>Rev!</option>
    <option value="5" <?php selectedEdit($editedInfo['status'], 5); ?>>Damaged</option>
	<option value="9" <?php selectedEdit($editedInfo['status'], 9); ?>>W-Off</option>
</select>
<select name="dept" required>
    <option value="aoi" <?php selectedEdit($editedInfo['dept'], 'aoi'); ?>>AOI</option>
    <option value="olbs" <?php selectedEdit($editedInfo['dept'], 'olbs'); ?>>OLBS</option>
    <option value="ict" <?php selectedEdit($editedInfo['dept'], 'ict'); ?>>ICT</option>
	<option value="eoli" <?php selectedEdit($editedInfo['dept'], 'eoli'); ?>>EOLI</option>
</select>
</div>
<textarea name="info" maxlength="535" placeholder="Defects | Info"><?php echo($editedInfo['info']); ?></textarea>
<input type="submit" name="editInfo" value="Edit">
</form>

<?php require('footer.php');?>
<script>
function confirmEdit() {
    return confirm("Are you sure?");
}
</script>

