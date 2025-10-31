<?php require('head.php');	?>
<h1>Projects</h1>
<table class="sampleTab"><thead>
	<tr>
<?php

$tableBody = ['Name', 'PN#', 'CPN#', 'PCA#', 'PCB#', 'ICT', 'OLBS', 'PpP', 'EOLI', 'Side'];
$dataBody = ['name', 'pn', 'cpn', 'pca', 'pcb', 'ict', 'olbs', 'ppp', 'ppc', 'side'];
$datas = implode(', ',$dataBody);

foreach($tableBody as $table){
	echo('<th>'.$table.'</th>');
}	echo('</tr></thead><tbody>');

$params = "ORDER BY name ASC, pn ASC";

$req = "SELECT $datas FROM dbo.SAMPLEEQ_projects $params";

$results = query($conn, $req);
	if($results){
		foreach($results as $res){
            echo('<tr>');
			foreach($dataBody as $data){
				if($data === 'ict' OR $data === 'olbs'){
					if($res[$data] === 1){	echo('<td class="check"></td>');	}
						else	{	echo('<td class="cross"></td>');		}
				}	else{	echo('<td>'.$res[$data].'</td>');	}
			}	echo('</tr>');
	}
	} else {  	echo('Projects ERROR'); }
?>
</tbody></table>
<?php require('footer.php');?>

