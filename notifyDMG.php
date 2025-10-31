<?php require('head.php');
    AdminOnly($userOn);

if(isset($_GET['sn'])){
    $dmgSN = $_GET['sn'];
    $params = array('5', $dmgSN);
    $origInfo = queryOne($conn, "SELECT name, pn, pca, pcb, sn, type, status, hub, dept FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$dmgSN'");
    $sqlDMG = "UPDATE dbo.SAMPLEEQ_sampleTab SET status = ? WHERE sn = ?"; 
    $setDMG = queryUpdate($conn, $sqlDMG, $params);
        if($setDMG){    
            $docText = "\n" . $dttm . "\t \t \t|\t \t \t" . $_SESSION['online']['icz']."\t \t \t|\t \t \t<<< TECHNICIAN DAMAGE NOTICE |||\n";
            $docText .= implode(', ', $origInfo)."\n";
            $file = './doc/logs.txt';
            $log = fopen($file, 'a');
			if($log){
				        fwrite($log, $docText);
				        fclose($log);
			}
            echo('<div class="err">Sample status has been set to DAMAGED.');
            $subject = $origInfo['name'].' | '.$origInfo['pn'].' | DAMAGED SAMPLE';
            $message = 'Technician '.$_SESSION['online']['username']. ' has changed sample status to damaged<br><ul>';
            $message .= '<li>'.$origInfo['name'].' | '.$origInfo['pn'].'</li>';
            $message .= '<li>SN# : '.$dmgSN.'</li>';
            $message .= '<li>Department : '.$origInfo['dept'].'</li>';
            $message .= '</ul>';
            $message .= '<p>Check sample and request new one if needed</p>';
            mailNotify($subject, $message, $engGroup);
        }
            else {      echo('<div class="err">!! Error !!');   }
}

echo('<a href="index.php">Back to Search</a></div>');

include('footer.php');    
?>
