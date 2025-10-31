<?php require('head.php'); ?>

</div>
<h2>Get project info</h2>
<form id="getProject" method="post" onsubmit="saveProject()">
<select id="projectName" name="projectName" onchange="pnSelect(this.value)" required>
	<option value="" selected disabled>Select project</option>
</select>
<select id="projectPN" name="projectPN" required>
	<option value="" selected disabled>Select PN#</option>
</select>
<input type="submit" name="getInfo" value="Get Info">
</form>
<?php
if(isset($_POST['getInfo'])){
    $pn = $_POST['projectPN'];
    getProjectData($conn, $pn);
}

if(!isMobile()){

echo('<br><br>');

$minimals = query($conn, "SELECT ict, olbs, ppc as eoli FROM dbo.SAMPLEEQ_projects");
$sections = ['aoi', 'olbs', 'ict', 'eoli'];
$available = [];
$min = [];
$totalPct = [];
$hubs = ['S', 'B'];

foreach($hubs as $hub){
foreach($sections as $section){
	$available[$section.$hub] = querySingle($conn,
	"SELECT COUNT(*) FROM dbo.SAMPLEEQ_sampleTab WHERE dept = '$section' AND status IN('1', '2') AND hub = '$hub'");
		$aoiParam = ($section == 'aoi') ? ' * 2' : '';
		if($section == 'aoi' || $section == 'eoli'){ $column = 'ppc';	}
			else {	$column = $section;	}
		if($section != 'eoli'){	$params = 'COUNT(CASE WHEN '.$column. ' > 0 THEN 1 END)'.$aoiParam; }
			else {	$params = "SUM(CAST(ppc AS INT)) + COUNT(ppc) * 5";	}
	$min[$section] = querySingle($conn, "SELECT $params FROM dbo.SAMPLEEQ_projects");
	$totalPct[$section.$hub] = percent($available[$section.$hub], $min[$section]);
}
}

foreach($hubs as $hub){
$hubName = ($hub === 'S') ? 'Slatina' : 'Blučina';
	echo('<div class="big-header '.clearTxt($hubName).'">'.$hubName.'</div><div class="row">');
foreach($sections as $section){
	echo('<div class="dashBoard"><div class="'.$section.'">'.strtoupper($section).' : '.$available[$section.$hub].' / '.$min[$section].'</div>');
	echo('<div class="percent">'.$totalPct[$section.$hub].'%</div>');
	echo('<progress max="100" value="'.$totalPct[$section.$hub].'"></progress></div>');
}	echo('</div>');
}
}

require('footer.php'); ?>

