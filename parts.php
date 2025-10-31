<?php   require('head.php'); 
        AdminOnly($userOn);
function rfPart($x) {
    if(empty($_SESSION['refOrPart'])) {
        $_SESSION['refOrPart'] = 'ref';
    }
    if ($x === $_SESSION['refOrPart']) {
        echo ('checked');
    }   else {      return;     }
}

$revLevel = ['000' => 0, 'X01' => 1, 'X02' => 2, 'X03' => 3, 'AX1' => 4, 'AX2' => 5, 'AX3' => 6, 'AX4' => 7, 'A01' => 8, 'A02' => 9, 'A03' => 10];
$tableBody = (!isMobile()) ? ['Ref_ID', 'PN_#', 'Revision', 'Date', 'Info'] : ['Ref', 'PN_#', 'Rev', 'Date'];
$manualDoc = (!isMobile()) ? 'doc/parts.jpg' : 'doc/parts_Mobile.jpg';

if (isset($_POST['getPartInfo'])) {
    $prj = $_POST['projectPN'];
    $revisions = query($conn, "SELECT DISTINCT rev FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj'");
    $newestRev = PHP_INT_MIN;
    foreach ($revisions as $rev) {
        if ($revLevel[$rev['rev']] > $newestRev) {
            $newestRev = $revLevel[$rev['rev']];
        }   else {  continue;   }
    }
    if (isset($_POST['ref'])) {
        $refOrPart = 'ref';
        $value = (!empty($_POST['ref'])) ? trim(strtoupper($_POST['ref'])) : '';
        if ($value === '') {
            $table = query($conn, "SELECT ref, pn, info, rev, cdt FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' ORDER BY ref ASC, pn ASC");
        }   else {
                $table = query($conn, "SELECT ref, pn, info, rev, cdt FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' AND ref = '$value' ORDER BY rev DESC");
            }
    }   
    else if (isset($_POST['part'])) {
        $refOrPart = 'part';
        $value = (!empty($_POST['part'])) ? trim(strtoupper($_POST['part'])) : '';
        if ($value === '') {
            $table = query($conn, "SELECT ref, pn, info, rev, cdt FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' ORDER BY ref ASC, pn ASC");
        }   else {
                $table = query($conn, "SELECT ref, pn, info, rev, cdt FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' AND pn = '$value' ORDER BY ref ASC, cdt DESC");                   
            }
    $_SESSION['refOrPart'] = ($refOrPart) ? $refOrPart : 'ref';
    }
}
?>
<h1>PARTEEQ</h1>
<div class="cntr"><a href="<?php echo ($manualDoc); ?>" class="manual" target="blank_" title="info"></a></div>
<form method="post" id="partsForm" onSubmit="showLoadingAnimation();saveProject();">
    <select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	   <option value="" selected disabled>Select project</option>
    </select>
    <select id="projectPN" name="projectPN">
	   <option value="" selected disabled>0000X0000000</option>
    </select>
        <label for="ref">Position
    <input type="radio" id="ref" name="refIdPartNum" onClick="engeeq('ref', 1, 7);" <?php rfPart('ref'); ?>>
        </label>
        <label for="part">Part number
    <input type="radio" id="part" name="refIdPartNum" onClick="engeeq('part', 1, 12);" <?php rfPart('part'); ?>>
        </label>
    <input type="text" id="refOrPN" placeholder="Ref_ID | PN_#" name="<?php echo($_SESSION['refOrPart']); ?>">
    <input type="submit" name="getPartInfo" value="Get info">
</form>
<br>
<?php 
    if (isset($_POST['getPartInfo'])) {
        if (!empty($table)){
        tabColumns($tableBody, 'allParts');
            foreach($table as $pns){
                $ref = $pns['ref'];
                $class = ($revLevel[$pns['rev']] !== $newestRev) ? ' class="red"' : '';
                echo ('<tr'.$class.'><td>'.$pns['ref'].'</td><td>'. $pns['pn'].'</td><td>'.$pns['rev'].'</td>');
                echo (!isMobile()) ? '<td>'.$pns['cdt']->format('d.m.y').'</td>' : '<td>'.$pns['cdt']->format('m/y').'</td>';
                echo (!isMobile()) ? '<td>'. $pns['info'].'</td>' : '';
                echo ('</tr>');   
            }
                echo('</tbody></table>');
        }   else {
                $bomExist = query($conn, "SELECT 1 FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj'");
                if (!$bomExist) {   echo ('<div class="noResult">BOM data not exist<br><a href="update_bom.php">Upload BOM</a></div>');    }
                    else {          echo ('<div class="noResult">No match found</div>');    }
            }
    }

require('footer.php'); ?>