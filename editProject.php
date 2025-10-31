<?php require('head.php');
	AdminOnly($userOn);
    pcOnly();
?>

<h1>Edit Projects</h1>
<?php

function checkBoxOn($x, $y){
    if($x == $y){   return(' checked'); }
}

function crtAutoLog($sn, $original, $change){
    $docText .= $dttm."\t \t | ". $_SESSION['online']['icz']." \t \t | \t \t | \t \t STATUS = 3 \t \t <<< AUTOMATIC CHANGE ||| \n";
	$file = './doc/logs.txt';
    $log = fopen($file, 'a');
		if($log){
			fwrite($log, $docText);
			fclose($log);
		}
}

if(isset($_POST['addProject'])){

    $name = strtoupper(trim($_POST['name']));
    $pn = trim($_POST['pn']);
    $cpn = strtoupper(trim($_POST['cpn']));
    $pcaRev = $_POST['pca'];
    $pcbRev = $_POST['pcb'];
    $ict = isset($_POST['ict']) ? '1' : '0';
    $olbs = isset($_POST['olbs']) ? '1' : '0';
    $aci = isset($_POST['aci']) ? '1' : '0';
    $ppp = trim($_POST['ppp']);
    $ppc = trim($_POST['ppc']);
    $side = $_POST['side'];
    $sql = "INSERT INTO dbo.SAMPLEEQ_projects (name,pn,cpn,pca,pcb,ict,olbs,aci,ppp,ppc,side,img) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
    $params = array($name, $pn, $cpn, $pcaRev, $pcbRev, $ict, $olbs, $aci, $ppp, $ppc, $side, 'N/A');
    $crtNewPrj = queryUpdate($conn, $sql, $params);
        if ($crtNewPrj){
            echo ('Project has been added successfuly');
        }
}

if(isset($_POST['editProject'])){
	$cpn = $_POST['cpn'];
    $pcaRev = $_POST['pca'];
    $pcbRev = $_POST['pcb'];
    $ppp = $_POST['ppp'];
    $ppc = $_POST['ppc'];
    $pn = $_POST['pn'];
    $ict = isset($_POST['ict']) ? '1' : '0';
    $olbs = isset($_POST['olbs']) ? '1' : '0';
    $aci = isset($_POST['aci']) ? '1' : '0'; 

$params = array($cpn, $pcaRev, $pcbRev, $ict, $olbs, $aci, $ppp, $ppc, $pn);
$projectName = getPrjInfo($conn, $pn, 'name');
$currentParams = queryOne($conn, "SELECT * FROM dbo.SAMPLEEQ_projects WHERE pn = '$pn'");
$original = '';
$origLog = '';
foreach ($currentParams as $key => $par) {
    if ($key === 'img' || $key === 'ict' || $key === 'olbs' || $key === 'aci') {   continue;  }
        else {  $origLog .= "$par | ";
                $original .= "<li>$par</li>";  }
}
$sql = "UPDATE dbo.SAMPLEEQ_projects SET cpn = ?, pca = ?, pcb = ?, ict = ?, olbs = ?, aci = ?, ppp = ?, ppc = ? WHERE pn = ?";
$editing = queryUpdate($conn, $sql, $params);
    if($editing){
        $updatedText = array($projectName, $pn, $cpn, $pcaRev, $pcbRev);
        $subject = "$projectName - $pn | Project data has been updated";
        $updated = '';
        $updtLog = '';
        foreach($updatedText as $updt) {
            $updtLog .= "$updt | ";
            $updated .= "<li>$updt</li>";   
        }
        $message = '<div style="display:flex;flex-direction:row;width:95%;justify-content:space-evenly;"><ul>'.$original.'</ul><ul>'.$updated.'</ul></div>';
        $details = '';
        mailNotify($subject, $message, $details, $engGroup);
        $txt = "ORIGINAL : $origLog \n \t EDIT :  $updtLog";
        modifyToLog($txt);
	    echo('<h2>Project has been updated</h2>');
    }
}

$tableBody = ['Name', 'PN#', 'CPN#', 'PCA#', 'PCB#', 'ICT', 'OLBS', 'ACI', 'PpP', 'EOLI', 'Side', 'Change'];
$dataBody = ['name', 'pn', 'cpn', 'pca', 'pcb', 'ict', 'olbs', 'aci', 'ppp', 'ppc', 'side'];
$datas = implode(', ',$dataBody);
tabColumns($tableBody, 'projectList');

$req = "SELECT $datas FROM dbo.SAMPLEEQ_projects ORDER BY name ASC, pn ASC";
$results = query($conn, $req);
	if($results){
		foreach($results as $res){
                echo('<tr><form method="post" onSubmit="return confirmEdit(event)"><input type="hidden" name="pn" value="'.$res['pn'].'">');
        foreach($dataBody as $key){
			if($key === 'cpn'){
				echo('<td><input type="text" name="cpn" value="'.trim($res[$key]).'"></td>');
			}
            else if($key == 'pca'){
                echo('<td>'.selectionPCA($res[$key]).'</td>');
            }
            else if($key == 'pcb'){
                echo('<td>'.selectionPCB($res[$key]).'</td>');
            }
			else if($key == 'ict' || $key == 'olbs' || $key === 'aci'){
                echo('<td><label><input class="toggle" type="checkbox" name="'.$key.'"'.checkBoxOn($res[$key], '1').'></label></td>');
            }
            else if($key === 'ppp' || $key === 'ppc'){
                echo('<td><input type="number" name="'.$key.'" min="1" max="200" value="'.intval(htmlspecialchars($res[$key])).'"></td>');
            }
            else {	echo('<td>'.$res[$key].'</td>');  }
           
		}
         echo('<td><input type="submit" name="editProject" value="Change"></td></form></tr>');
		}
	} 	else {  echo('Projects ERROR'); }
?>
<tr><form method="post">
<td><input type="text" name="name" minlength="4" maxlength="15" pattern="[a-ZA-Z]+" title="Project name" required>
<td><input type="text" name="pn" minlength="12" maxlength="12" pattern="[0-9A-Z]{12}" oninput="formatPN(this)" title="Part number" required>
<td><input type="text" name="cpn" minlength="3" maxlength="8" title="Customer PCAPN_#" required>
<?php echo('<td>'.selectionPCA().'</td><td>'.selectionPCB().'</td>'); ?>
<td><label><input type="checkbox" name="ict" class="toggle" title="ICT testing"></label></td>
<td><label><input type="checkbox" name="olbs" class="toggle" title="OLBS testing"></label></td>
<td><label><input type="checkbox" name="aci" class="toggle" title="ACI"></label></td>
<td><input type="number" name="ppp" min="1" max="200" title="Panel Qty" required></td>
<td><input type="number" name="ppc" min="1" max="200" title="EOLI station Qty" required></td>
<td><select name="side" title="Which side are component placed at" required>
    <option value="" hidden selected>Side</option>
    <option value="TOP">TOP</option>
    <option value="BOT">BOT</option>
    <option value="BOTH">BOTH</option>
</select></td>
<td><input type="submit" name="addProject" value="Add new"></td>
</form></tr>
</tbody></table>
<br>
<!--
<h3>Special access</h3>
<form method="post" class="columnForm" onsubmit="return confirmEdit(event)">
<br>
<input type="submit" name="checkRevExp" value="Mark expired samples">
<br>
<input type="submit" name="markObsolete" value="Mark active obsolete PCA/PCB">
</form> -->
<?php
/*
if(isset($_POST['checkRevExp'])){
	$params = array('1');
	$sql = "UPDATE st SET st.status = '3'
			FROM dbo.SAMPLEEQ_sampleTab st
			JOIN dbo.SAMPLEEQ_projects pr ON pr.pn = st.pn
			WHERE st.status = ? AND pr.pca = st.pca AND pr.pcb = st.pcb
			AND CAST(st.crtdt AS DATE) < DATEADD(DAY, -365, GETDATE())";
    $checkActiveExpired = queryUpdate($conn, $sql, $params);
	if($checkActiveExpired){
		echo('<div class="err">All expired samples status has been changed to expired</div>');
	}
}
if(isset($_POST['markObsolete'])){
    $params = array('1', '3');
	$sql = "UPDATE st SET st.status = '4'
			FROM dbo.SAMPLEEQ_sampleTab st
			JOIN dbo.SAMPLEEQ_projects pr ON pr.pn = st.pn
			WHERE st.status IN(?,?)	AND (pr.pca <> st.pca OR pr.pcb <> st.pcb)";
	$markObsolete = queryUpdate($conn, $sql, $params);
	if($markObsolete){
		echo('<div class="err">Active samples with Obsolete PCA & PCB revisions has been changed</div>');
	}
}
*/
require('footer.php');
?>
