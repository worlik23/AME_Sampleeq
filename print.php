<?php require('head.php');
    AdminOnly($userOn);
?>
<h1>Labels</h1>

<?php

function qrURL($sn){
    return (urlencode($sn));
}
function showDate($data){
    $date = DateTime::createFromFormat('Y-m-d', $data)->format('m/y');
    return($date);
}
function goldDummy($x){
    $y = ($x === 'G') ? 'GOLD' : 'DUMMY';
    return ($y);    
}

function startLbl(&$label){
    $label = "'^XA^MMC^PW1063^LL2693^LS0";
    return($label);
}

function nextRowLbl($labelRow, $i, &$label){
    $mxRows = ['^FT905', '^FT652', '^FT408', '^FT165'];
    $qrRows = ['^FT884', '^FT634', '^FT384', '^FT134'];
    $txtRows = ['^FT940', '^FT685', '^FT431', '^FT177'];

    $label .= $mxRows[$i].',2550^BXR,6,200,0,0,1,_,1^FH\^FD'.$labelRow['sn'];
    $label .= '^FS'.$qrRows[$i].',256^BQN,2,5^FH\^FDLA,https://icz19-oaweb.inventec.cz/sampleeq/sn.php?sn='.$labelRow['qr'];
    $label .= '^FS'.$txtRows[$i].',275^A0R,89,79^FH\^CI28^FD'.$labelRow['txt'].'^FS^CI27"';
    return($label);
}

function endLbl(&$label){
    $label .= "^PQ1,1,1,Y
^XZ'";
    return($label);
}

$dataBody = ['sn', 'name', 'pn', 'pca', 'pcb', 'crev', 'crtdt', 'type', 'dept'];
$datas = implode(', ', $dataBody);
///////////////////////////////////////////////////////// ***** PRINTING ***** /////////////////////////////////////////////////////////
if(isset($_POST['print']) && !empty($_POST['batches'])){

$reToken = ($_POST['reToken'] === $_SESSION['token']) ? true : false;
$host = $_POST['host'];
$labelRows = [];
$labelIdx = 0;
    if($reToken){
$batches = array_unique($_POST['batches']);
    foreach($batches as $batch){
        $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$batch'";
        $sample = queryOne($conn, $sql);
            if(!empty($sample)){
                $labelRows[$labelIdx]['qr'] = qrUrl($sample['sn']);
                $pcabRev = $sample['pca'].'/'.$sample['pcb'];
                $dept = strtoupper($sample['dept']);
                $labelRows[$labelIdx]['txt'] = $sample['name'].' '.$sample['pn'].' |'.$pcabRev.'|rev_'.$sample['crev'].'| '.showDate($sample['crtdt']).' | '.goldDummy($sample['type']).' | '.$dept;
                $labelRows[$labelIdx]['sn'] = $sample['sn'];
            }
        $labelIdx++;
    }
    $i = 0;
    foreach($labelRows as $labelRow){
        if($i == 0){    
            $label = '';
            startLbl($label);     
        }
        nextRowLbl($labelRow, $i, $label);
        if($i == 3 || ($labelRow == $labelRows[$labelIdx - 1])){    
            endLbl($label);       
            tisk($host, $label);
          $i = 0;
        }   else {  $i++;   }
    }
        unset($_SESSION['token']);
    }   else {  echo ('REPEAT_PROTECTION : This form has been sent already');    }
}

/////////////////////////////////////////////////// ***** SOCKET IP STUFF ***** ////////////////////////////////////////////////////////
function tisk($host, $label) {
$port = "9100";
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        echo "socket_create() failed: reason: " . socket_strerror(socket_last_error    ()) . "\n";
    }
$result = socket_connect($socket, $host, $port);
    
    if ($result === false) {    
        echo ("socket_connect() failed.\nReason: ($result) " . socket_strerror    (socket_last_error($socket)) . "\n");   
    }

socket_write($socket, $label, strlen($label));
socket_close($socket);

}

?>
<div class="row">
<input type="text" id="batch" placeholder="Insert SN# & press enter" value="" autocomplete="off">
<button id="nextBtn">+Add</button>
</div>
<div id="errorReport"></div>
<hr>
<form id="batchForm" method="post" class="columnForm">
    <input type="hidden" value="<?php echo getToken(); ?>" name="reToken">
    <div id="batchList"></div>
<hr>
<div class="row">
    <select name="host" required>
        <option value="" selected>Select printer</option>
        <option value="10.13.59.187">Slatina</option>
        <option value="10.14.33.23">Blučina</option>
    </select>
    <input type="submit" name="print" value="Print">
</div>
</form>

<?php include('footer.php'); ?>
<script src="js/multiEdit.js?v=0.1" type="text/javascript" defer></script>