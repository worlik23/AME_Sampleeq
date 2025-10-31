<?php require('head.php');
AdminOnly(in_array($adminLevel, $engGroup));
require('php/bomChecker.php');
?>
<h1>Upload BOM file</h1>
<div class="cntr"><a href="doc/upload_bom.jpg" class="manual" target="blank_" title="info"></a></div>
<form method="post" enctype="multipart/form-data" onSubmit="showLoadingAnimation();">
    <select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	   <option value="" selected disabled>Select project</option>
    </select>
    <select id="projectPN" name="projectPN">
	   <option value="" selected disabled>0000X0000000</option>
    </select>
    <label for="bomFile">Upload BOM.csv
    <input type="file" id="bomFile" name="bomFile" accept=".xls" required>
        </label>
    <input type="submit" name="updateBOM" value="Update BOM">
</form>
<br><br>
<?php 

if (isset($_FILES['bomFile']) && $_FILES['bomFile']['error'] === UPLOAD_ERR_OK) {
    $prj = $_POST['projectPN'];
    $prjName = $_POST['projectName'];
    $bomFile = $_FILES['bomFile']['tmp_name'];
    analyseBOM($conn, $prjName, $prj, $bomFile, $dttm);
}

require('footer.php'); ?>