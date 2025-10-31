<?php
require_once('dbaccess.php');

if(isset($_POST['what'])){
	$where = $_POST['where'];
	$what = $_POST['what'];
	$projectData = queryOne($conn, "SELECT * FROM dbo.SAMPLEEQ_projects WHERE $where = '$what'");
		if(!empty($projectData)){
			echo json_encode([  'project' => $projectData['name'],
                                'pn' => $projectData['pn'],
                                'pca' => $projectData['pca'],
                                'pcb' => $projectData['pcb'],
								'ict' => $projectData['ict'],
								'olbs'=> $projectData['olbs'],
                                'aci' => $projectData['aci']
							]);
		}	else {	echo json_encode(['error' => 'SN autodetect failed']);		}
}

if(isset($_POST['sn'])){
    $sn = strtoupper($_POST['sn']);
    $messSla = messOne($pgSLA, "SELECT cust_sno, family, model, pca_rev, pcb_rev, sno_udt, next_station FROM dw.fact_sn_info f WHERE mcbsno = '$sn' OR cust_sno = '$sn'");
    $messBlu= messOne($pgBLU, "SELECT cust_sno, family, model, rev, pcb_rev, sno_udt, next_station FROM dw_v2.fact_sn fs2 WHERE sno = '$sn' OR cust_sno = '$sn'");
    $sqlFISone = "SELECT ppm.Family, pps.Model, ww.Description, pps.McbSno as sn, pps.Rev, pps.PCB, pps.Udt FROM PCA..PCA_CUST_SNO pcs WITH(NOLOCK)
					LEFT JOIN PCA..PCA_SNO pps WITH(NOLOCK) ON pcs.McbSno = pps.McbSno
					LEFT JOIN PCA..PCA_MODEL ppm WITH(NOLOCK) ON pps.Model = ppm.Model 
                    LEFT JOIN FIS2..WC ww WITH (NOLOCK) ON pps.NWC = ww.WC
                    WHERE pcs.CustSno LIKE ('$sn%')";
    $sqlFIStwo = "WITH Riso AS (SELECT ps.Model, ww.Description, pcs.CustSno as sn, ps.Rev,ps.PCB, ps.Udt from dbo.PCA_CUST_SNO pcs WITH (NOLOCK)
					JOIN PCA_SNO ps WITH (NOLOCK) ON pcs.McbSno=ps.McbSno 
                    LEFT JOIN FIS2..WC ww WITH (NOLOCK) ON ps.NWC = ww.WC
                    WHERE ps.McbSno LIKE('$sn%'))
					SELECT r.*, pm.Family FROM PCA..PCA_MODEL pm WITH (NOLOCK) JOIN Riso r ON pm.Model= r.Model";
    $fisData = (empty(queryOne($connPCA, $sqlFISone))) ? queryOne($connPCA, $sqlFIStwo) : queryOne($connPCA, $sqlFISone);
    $messData = (empty($messSla)) ? $messBlu : $messSla;
    $pca = (empty($messSla)) ? 'rev' : 'pca_rev';
    if(!empty($fisData)){
        $cdt = $fisData['Udt']->format('Y-m-d');
        echo json_encode([  'project' => $fisData['Family'],
                            'pn' => $fisData['Model'],
							'sn' => $fisData['sn'],
                            'pca' => $fisData['Rev'],
                            'pcb' => $fisData['PCB'],
                            'crtdt' => $cdt,
                            'station' => $fisData['Description'],
                            'from' => 'FIS'
							]);
    }   
    elseif (empty($fisData) && !empty($messData)) {      
        echo json_encode([  'project' => $messData['family'],
                            'pn' => $messData['model'],
                            'sn' => $messData['cust_sno'],
                            'pca' => $messData[$pca],
                            'pcb' => $messData['pcb_rev'],
                            'crtdt' => substr($messData['sno_udt'],0,10),
                            'station' => $messData['next_station'],
                            'from' => 'MESS'
                            ]);
    }
    else {  echo json_encode(['error' => 'SN autodetect failed']);  }
}

if(isset($_POST['existSN'])){
    $existSN = strtoupper(trim($_POST['existSN']));
    $exist = searchByTxt($conn, $existSN, "sn", "", "", 'json', 'querySingle');
        if(empty($exist)){  echo json_encode(['success' => 'OK']);   }
            else {  echo json_encode(['error' => $exist]); }
}

if(isset($_POST['qtyTab'])){
    $pn = $_POST['qtyTab'];
    getProjectData($conn, $pn);
}

if(isset($_POST['replaceTab'])){
	$pn = $_POST['replaceTab'];
	$dept = $_POST['dept'];
	showReplacement($conn, $pn, $dept);
}

if(isset($_POST['lastRec'])) {
    $line = $_POST['lastRec'];
    $lastRec = queryOne($conn, "SELECT TOP 1 PanelId, Cdt FROM [ICZ9-MESSQL].Traceability.dbo.PcbComponentTrace WITH (NOLOCK) WHERE LineId LIKE ('$line%') ORDER BY Id DESC");
    $pnRq = substr($lastRec['PanelId'],6,7);
    $pnsncName = querySingle($conn, "SELECT name FROM SAMPLEEQ_projects WHERE substring(pn, 6, 7) = '$pnRq'"); 
    echo($pnsncName.' '.substr($lastRec['PanelId'],9,4).' | '.$lastRec['Cdt']->format('d.m|H:i'));
}

?>
