<?php require('head.php'); 
AdminOnly($userOn);
$tableBody = ['PN_#', 'Time'];
$manualDoc = (!isMobile()) ? 'doc/pnsnc.jpg' : 'doc/pnsnc_Mobile.jpg';
?>
<h1>RealTime PN check</h1>
<div class="cntr"><a href="<?php echo ($manualDoc); ?>" class="manual" target="blank_" title="info"></a></div>
<?php
    if (!isMobile() && $adminLevel !== 'tech') {
        echo('
        <div class="btn-row">
        <button type="button" onclick="lastRec(this, \'S01-\');">Line 1</button>
        <button type="button" onclick="lastRec(this, \'S02-\');">Line 2</button>
        <button type="button" onclick="lastRec(this, \'S0A-\');">Line A</button>
        <button type="button" onclick="lastRec(this, \'S0B-\');">Line B</button>
        </div>');
    }
?>
<form method="post" id="partsForm" onsubmit="showLoadingAnimation();">
    <select id="line" name="line" required>
        <option value="" selected disabled>Select Line</option>
        <option value="S01-">Line 1</option>
        <option value="S02-">Line 2</option>
        <option value="S0A-">Line A</option>
        <option value="S0B-">Line B</option>
    </select>
<input type="text" id="position" name="ref" placeholder="Reference" required>
<input type="submit" name="submit" value="Get info">
</form>
<?php

if(isset($_POST['submit'])){
    $line = $_POST['line'];
    $ref = trim(strtoupper($_POST['ref']));
    $req = "SELECT TOP 5 PanelId, PartNo, Reference, Cdt FROM [ICZ9-MESSQL].Traceability.dbo.PcbComponentTrace WITH (NOLOCK) 
                WHERE Reference LIKE '%$ref%' AND LineId LIKE '$line%' ORDER BY Id DESC";
    $results = query($conn, $req);
    $table = [];
    if(!empty($results)){
        foreach($results as $res) {
            $oneRef = explode(',', $res['Reference']);
            foreach($oneRef as $rr){
                if($rr === $ref){   $table[] = $res;    }
            }
        }
    }
    if(!empty($table)){
    $prj = '1395K'.substr($table[0]['PanelId'], 6, 7);
    $reference = $table[0]['Reference'];
    echo ('<h2>'.$prj.'</h2><hr>');
    echo ('<div class="err">'.$reference.'</div>');
    tabColumns($tableBody, 'allParts');
    foreach($table as $pns){
            echo('<tr><td>'. $pns['PartNo'] .'</td><td>'.$pns['Cdt']->format('d/m - H:i:s').'</td></tr>');
        }
        echo('</tbody></table>');
    }   else {  echo('None results found'); }
}

include('footer.php'); ?>