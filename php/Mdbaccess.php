<?php

function showResultsM($result, $tableBody, $conn){
    $hubs = ['S' => 'SLA', 'B' => 'BLU'];
    $classes = ['S' => 'green', 'B' => 'blue', 'G' => 'green', 'D' => 'D',
                '0' => 'mcross', '1' => 'mcheck', '2' => 'mprgs', '3' => 'mexp', '4' => 'mwarn', '5' => 'mdmg', '9' => 'mrqst'];
    $stat = ['0'=>'mzero','1'=>'mone','2'=>'mtwo','3'=>'mthree','4'=>'mfour','5'=>'mfive','9'=>'mnine'];
	$damagedOff = ['0', '9', '5', '6'];
		$count = 0;
		echo('<div class="sampleTab" id="sampleTab">');
        echo('<div class="hashSelect"><select id="hashSelect">');
        for($i = 1; $i <= count($result); $i++){
            echo('<option value="'.$i.'">'.$i.'</option>');
        }
        echo('</select>/'.count($result).'<button onClick="pushToHash(hashSelect.value)">Get#</button></div>');
		echo('<script> document.addEventListener("DOMContentLoaded", function() { location.hash = "#sampleTab"; });  </script>');
	foreach($result as $res){
		$count++;
		echo('<div id="'.$count.'" class="card '.$stat[$res['status']].'">');
		echo('<div class="cardRow">'.$count.'</div>');
    foreach($res as $key => $data){
        if($key !== 'info' && $key !== 'pcb' && $key !== 'pn' && $key !== 'status'){    echo('<div class="cardRow">');  }
		if($key === 'pcb' || $key === 'pn' || $key === 'status'){	continue;	}
		if($key === 'name'){	echo($res['name'].' | '.$res['pn'].'<div class="'.$classes[$res['status']].'"></div></div>');	}
        else if($key === 'pca'){	echo('PCA/PCB : ');
							echo(getPrjInfo($conn, $res['pn'], 'pca') === $data) ? '<div class="green">' : '<div class="D">';
							echo($data.'</div>');
							echo(getPrjInfo($conn, $res['pn'], 'pcb') === $res['pcb']) ? '<div class="green">' : '<div class="D">';
							echo($res['pcb'].'</div></div>');
		}
        else if($key === 'type')	{   echo('<div class="'.$classes[$data].'">');	echo ($data === 'D') ? 'Dummy</div></div>' : 'Golden</div></div>';    }
		else if($key === 'crtdt')	{	echo(showExpiration($data).'</div>');	}
        else if($key === 'status')	{ 	echo('<div class="'.$classes[$data].'"></div></div>');    }
        else if($key === 'dept')	{   echo('<div class="'.$data.'">'.strtoupper($data).'</div></div>');     }
        else if($key === 'info')	{   echo(!empty($data)) ? '<details><summary><div class="infoBtn">Info</div>' : '<div class="row">';

if(isset($_SESSION['online'])){
	if($_SESSION['online']['admin'] !== 'tech' && $_SESSION['online']['admin'] !== 'sap' && $res['status'] !== '0'){
		echo('<form action="edit.php"><input type="hidden" name="editSn" value="'.htmlspecialchars($res['sn']).'"><input type="submit" value="Edit"></form>');
	}
	if($_SESSION['online']['admin'] === 'tech' && !in_array($res['status'], $damagedOff)){
        echo('<a href="notifyDMG.php?sn='.htmlspecialchars($res['sn']).'" onclick="return confirmAction()" class="dmgBtn">Damaged</a>');
    }	echo(!empty($data)) ? '</summary>' : '</div>';
}		else {	echo(!empty($data)) ? '</summary>' : '</div>';	}

		echo(htmlspecialchars($data));
		echo(!empty($data)) ? '</details>' : '';
	   }

	   	else if($key === 'hub'){     echo('<div class="'.$classes[$data].'">'.$hubs[$data].'</div></div>'); }
    		else {	echo('<div>'.$data.'</div></div>');		}
	   }
	   		echo('</div>');
}
       echo('</div>');
       echo('<script src="js/mobile.js?v=0.1" type="text/javascript" defer></script>');
}
