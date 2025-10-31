<?php
$engGroup = ['aoi', 'ict', 'olbs', '9'];
$engTech = ['aoi', 'ict', 'olbs', '9', 'tech', 'sap'];
$aoiEng = ['aoi', '9'];
$ictEng = ['ict', '9'];
$olbsEng = ['olbs', '9']; 

//$serverName = "ICZ248043-01\SQLEXPRESS";
//$connectionInfo = array("Database"=>"samples", "TrustServerCertificate" => true);

$serverName = "icz9-pcadb";
$connectionInfo = array("Database"=>"ICZ_LOCAL", "UID"=>"ICZ9User", "PWD"=>"User@ICZ9_PCA","CharacterSet" => "UTF-8", "TrustServerCertificate" => true);

$serverNamePCA = "icz9-pcadb";
$connectionPCA = array("Database"=>"PCA", "UID"=>"ICZ9User", "PWD"=>"User@ICZ9_PCA","CharacterSet" => "UTF-8", "TrustServerCertificate" => true);

$pgconnSLA = "host=10.14.71.137 port=5493 dbname=bdc user=iczuser password=iczuser+123";
$pgconnBLU = "host=10.14.20.145 port=5493 dbname=bdc user=iczuser password=iczuser+123";

$conn = sqlsrv_connect($serverName, $connectionInfo);
    if($conn === false) {   die(print_r(sqlsrv_errors(), true));    }
$connPCA = sqlsrv_connect($serverNamePCA, $connectionPCA);
    if($connPCA === false) {   die(print_r(sqlsrv_errors(), true));    }

// $pgSLA = pg_connect($pgconnSLA);
//     if (!$pgSLA) {  echo ("SLA --> Chyba připojení.");  }
$pgBLU = pg_connect($pgconnBLU);
    if (!$pgBLU) {  echo ("BLU --> Chyba připojení.");  }

// #############################################################################		TOKEN      <<<<<
function getToken(){                                                	   
    if (!isset($_SESSION['token'])){	
        $_SESSION['token'] = bin2hex(random_bytes(32));		
    }   return ($_SESSION['token']);
}
// ############################################################################# TODAY + YEAR<-BACK 	<<<<<
$dttm = date('d.m.y - H:i:s');
$today = date('Y-m-d');
$yearBack = (new DateTime())->modify('-1 year')->format('Y-m-d');
// ############################################################################# ADMIN VERIFICATION		<<<<<
function AdminOn($conn){
	if(!isset($_SESSION['online'])){
		if(isset($_COOKIE['user_token'])){
			$userToken = $_COOKIE['user_token'];
			$user = queryOne($conn, "SELECT icz, email, pass, admin, username FROM dbo.SAMPLEEQ_users WHERE user_token='$userToken'");
	if($user){		$_SESSION['online'] = $user;
					return $_SESSION['online'];
	}		else {	setcookie("user_token", "", time() - 3600, "/");
				return false;
			}
		}	else {	return false;
			}
    }	else {	return $_SESSION['online'];	}
}
// ################################################################################################# 		ADMIN ONLY <<<<<
function AdminOnly($userOn){
	if(!$userOn){
		echo('  <h1>Limited access</h1><br><div class="noResult">Access to this page is restricted.<br><div class="cntr">');
        include('php/login.php');
        echo ('</div><br>To request access, please contact the administrator 
                <div class="cntr"><a href="https://teams.microsoft.com/l/chat/0/0?users=steiner.richard@inventec.com" target="_blank">Here</a></div></div>');
		include('footer.php');
		die();
	}
}
// ################################################################################################# 		getParamOfGroup <<<<<
function getParamOfGroup($param, $group) {
    global $conn;
$allEngs = [];
    foreach ($group as $eng) {
        $oneG = query($conn, "SELECT $param FROM dbo.SAMPLEEQ_users WHERE admin = '$eng'");
        foreach ($oneG as $one) {
            $allEngs[] = $one['email'];
        }
    }
    return $allEngs;
}

function pcOnly() {
    if (isMobile()) {
        echo('  <h1>Only for PC</h1><br><div class="noResult">Desktop access required.<br>This function is not available on mobile devices.</div>');
		include('footer.php');
		die();
    }
}
// ################################################################################################# 		ADMIN LEVEL <<<<<
function adminLevel($conn, $icz){
	if(!$icz){	$level = false;	}
		else {	$level = querySingle($conn, "SELECT admin FROM dbo.SAMPLEEQ_users WHERE icz = '$icz'");	}
	return ($level);
}
// ################################################################################################# 		SECTION ACCESS <<<<<
function access($x){
    $sections = ['aoi' => ['aoi', 'eoli'],
                 'ict' => ['ict', 'aoi'],
                 '9'   => ['aoi', 'ict', 'olbs', 'eoli'],
                 'olbs'=> ['olbs']
                ];
    $access = $sections[$x];
    return($access);
}
// #############################################################################        QUERY ALL
function query($conn, $sql){
    $stmt = sqlsrv_query($conn, $sql);
        if($stmt === false) {     die( print_r( sqlsrv_errors(), true));    }
            $results = array();
            while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
                $results[] = $row;
            }
    return $results;
}
// _____________________________________________________________________________        PG
function mess($conn, $sql) {
    $stmt = pg_query($conn, $sql);
        if (!$stmt) {   return null;   }
    $results = [];
    while($row = pg_fetch_assoc($stmt)) {
        $results[] = $row;
    }
    return $results;
}
// ############################################################################# ONE ROW QUERY
function queryOne($conn, $sql){
    $stmt = sqlsrv_query($conn, $sql);
    if($stmt === false) {     die( print_r( sqlsrv_errors(), true));    }
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row;
}
// _____________________________________________________________________________        PG
function messOne($conn, $sql) {
    $stmt = pg_query($conn, $sql);
    if (!$stmt) {   return false; }
    $row = [];
    $row = pg_fetch_assoc($stmt);
    return $row; 
}
// ############################################################################# SINLGE VALUE QUERY
function querySingle($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    return $row ? reset($row) : null;
}
// _____________________________________________________________________________        PG
function messSingle($conn, $sql, $column) {
    $stmt = pg_query($conn, $sql);
    if (!$stmt){    return false;   }
    $row = pg_fetch_assoc($stmt);
    return $row[$column] ?? null; 
}
// ############################################################################# QUERY UPDATE
function queryUpdate($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {  die(print_r(sqlsrv_errors(), true));    }
											                 // IF UPDATE, DELETE OR INSERT, RETURN TRUE/FALSE
    if (stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0 || stripos($sql, 'INSERT') === 0) {
        return $stmt ? true : false;
    }
    $results = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
}
// ############################################################################# PROJECT PARAM
function getPrjInfo($conn, $pn, $column){
    $param = querySingle($conn, "SELECT $column FROM dbo.SAMPLEEQ_projects WHERE pn='$pn'");
    return $param;
}

function getSNdata($sn) {
    global $conn;
    $sql = "SELECT name, pn, pca, pcb, crev, type, dept, sn FROM dbo.SAMPLEEQ_sampleTab WHERE sn = '$sn'";
    $sample = queryOne($conn, $sql);
    if (!empty($sample)) {
        $txt = implode(" | ", $sample);
    }   else {  $txt = "Info not found - ";    }
    return $txt."<br>";
}
// ############################################################################# REVISION LEVEL
    $revLevel = ['XXX' => 0, '000' => 0, 'X01' => 1, 'X02' => 2, 'X03' => 3, 'AX1' => 4, 'AX2' => 5, 'AX3' => 6, 'AX4' => 7, 'A01' => 8, 'A02' => 9, 'A03' => 10, 'B01' => 11];
function checkNewestRevision($prj){
    global $conn;
    global $revLevel;
    $newestRev = getPrjInfo($conn, $prj, 'pca');
    $existInBom = querySingle($conn, "SELECT rev FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj' AND rev = '$newestRev'");
    if ($existInBom === null) {
        $existInBom = query($conn, "SELECT DISTINCT rev FROM dbo.SAMPLEEQ_parts WHERE prj = '$prj'");
        foreach ($existInBom as $e) {
            if ($revLevel[$e['rev']] > $revLevel[$newestRev]) {
                $updateRev = $e['rev'];
                $upgradeProjects = queryUpdate($conn, "UPDATE dbo.SAMPLEEQ_projects SET pca = ? WHERE pn = ?", [$updateRev, $prj]);
                    if ($upgradeProjects) {
                        echo ('Project Db Upgrade OK');
                        revisionMail($prj, $updateRev, $details = '');
                        return false;
                    }   else {  echo ('Error, DB update ERR');  
                                return true;
                        }    
            }   else {  echo ('<div class="noResult">Existing rev.'.$e['rev'].' is obsolete</div>');
                        echo ('<div class="cntr"><a href="update_bom.php">You must upload actual BOM here</a></div>');   }
        }
    }   else {  return false;   }   
}

function upgradeRevision($prj, $revision, $details) {
    global $conn;
    global $revLevel;
    $prjRev = getPrjInfo($conn, $prj, 'pca');
    if ($revLevel[$prjRev] < $revLevel[$revision]) {
        $updateProject = queryUpdate($conn, "UPDATE dbo.SAMPLEEQ_projects SET pca = ? WHERE pn = ?", [$revision, $prj]);
        if ($updateProject) {   
            echo ('<div class="correct">Project PCA rev_# AUTO-UPGRADE - OK</div>');
            revisionMail($prj, $revision, $details);
        }
            else {  echo ('<div class="incorrect">Project PCA rev_# AUTO-UPGRADE - ERROR</div>');    }
    }
}

function mailNotify($subject, $message, $details = '', $group) {
    global $conn;
    $receivers = getParamOfGroup("email", $group);
    include('mailer.php');
}

function revisionMail($prj, $revision, $details = ''){
    global $engGroup;
    global $conn;
    $name = getPrjInfo($conn, $prj, 'name');
    $subject = "REVISION UPGRADE | $name | $prj --> $revision";
    $message = "$name | $prj | PCA revision has been updated to --> $revision";
    $message .= "<h3>!!! Please check your samples !!!</h3>";
    mailNotify($subject, $message, $details = '', $engGroup);
}
// ############################################################################# PROJECT NAME FROM PN
function getPrjName($projectPN) {
    global $projects;
    $p = '';
    foreach ($projects as $prjName => $prjPn) {
        if (in_array($projectPN, $prjPn)) {
            $p = $prjName;
            return $p;
        }
    }
    if ($p === '') {  return ('N/A');   }
}
// ############################################################################# PCA REV SELECT
function selectionPCA($x = 'PCA#', $y = true){
	$aRev = ['X01','X02','X03','AX1','AX2','AX3','AX4','A01', 'A02', 'A03', 'B01'];
    $default = ($x === 'PCA#') ? 'selected' : '';
    $required = ($y === true) ? 'required' : '';
    $return = '<select name="pca" id="pca" '.$required.'><option value="" '.$default.'>PCA#</option>';
	foreach($aRev as $a){
		if($x === $a){	$return .= '<option value="'.$a.'" selected>'.$a.'</option>';	}
			else 	 {	$return .= '<option value="'.$a.'">'.$a.'</option>';			}
	}
    $return .= '</select>';
	return($return);
}
// ############################################################################# PCB REV SELECT
function selectionPCB($x = 'PCB#', $y = true){
	$bRev = ['A01','A02','B01'];
    $default = ($x === 'PCB#') ? 'selected' : '';
    $required = ($y === true) ? 'required' : '';
    $return = '<select name="pcb" id="pcb" '.$required.'><option value="" '.$default.'>PCB#</option>';
	foreach($bRev as $b){
    	if($x === $b){	$return .= '<option value="'.$b.'" selected>'.$b.'</option>';	}
			else 	 {	$return .= '<option value="'.$b.'">'.$b.'</option>';			}
	}
    $return .= '</select>';
	return($return);
}

// #############################################################################        STATUS SELECT               <<<<<
function statusSelection($x = '', $y = true){
    $states = [1=>'OK', 2=>'PREP', 3=>'EXP', 4 =>'REV!', 5 =>'DMG', 9 =>'W-off', 0 =>'OUT'];
    $default = ($x === '') ? 'selected' : '';
    $required = ($y === true) ? 'required' : '';
    $return = '<select name="status" '.$required.'><option value="" '.$default.'>Status</option>';
        foreach($states as $key => $value){
            if($x === $key) {    $return .= '<option value="'.$key.'" selected>'.$value.'</option>';    }
                else        {  $return .= '<option value="'.$key.'">'.$value.'</option>';               }
        }   $return .= '</select>';
    return($return);
}

// #############################################################################        CHECK IF EXIST IN ARRAY     <<<<<
function checkedIfInArray($x){
    if(!empty($_SESSION['with'])){
    if(in_array($x, $_SESSION['with'])){    return(' checked');   }
    }
}

// ################################################################################################# 		IS_MOBILE FN <<<<<
function isMobile() {
    return preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i',
    $_SERVER['HTTP_USER_AGENT']);
}
// ################################################################################################# 		ALL PROJECTS <<<<<
$projects = [];
$projectNames = query($conn, "SELECT DISTINCT name FROM dbo.SAMPLEEQ_projects ORDER BY name ASC");
    foreach($projectNames as $name){
        $name = $name['name'];
        $pns = query($conn, "SELECT pn FROM dbo.SAMPLEEQ_projects WHERE name = '$name' ORDER BY pn ASC");
            foreach($pns as $pn){
                $projectPNs = $pn['pn'];
                $projects[$name][] = $projectPNs;
            }
    }
$allProjects = json_encode($projects);

// ############################################################################################# 		SEARCHING BY TEXT  <<<<
function searchByTxt($conn, $search, $datas, $tableBody, $rqParams, $sessionName = '', $typeOfQuery = 'query'){
    $search_1 = mb_substr($search, 0, mb_strlen($search) - 1);
    $search_2 = mb_substr($search, 0, mb_strlen($search) - 2);
    $firstAtt = $typeOfQuery($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search' $rqParams");
    if(!$firstAtt){
        $secondAtt = $typeOfQuery($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_1' $rqParams");
        if(!$secondAtt){
            $thirdAtt = $typeOfQuery($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_2' $rqParams");
            if(!$thirdAtt && $sessionName !== 'json'){
                $likeAtt = $typeOfQuery($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn LIKE '%$search%' $rqParams");
                if(!$likeAtt){  die('Not found');  }
                else {  $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn LIKE '%$search%' $rqParams";      }
            } else {    $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_2' $rqParams";           }
        } else {        $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search_1' $rqParams";           }
    } else {            $sql = "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE sn='$search' $rqParams";             }
    
    if($sessionName && $sessionName === 'json'){   return $typeOfQuery($conn, $sql);   }
    else if($sessionName && $sessionName !== 'json'){   $_SESSION[$sessionName] = $sql;     }
    $results = $typeOfQuery($conn, $sql);
    showResults($results, $tableBody, $conn);     
}
// ############################################################################################# 		SHOW RESULTS TAB  <<<<

function showResults($result, $tableBody, $conn){
if(isMobile()){     return showResultsM($result, $tableBody, $conn);    }
    else {
        if(!empty($tableBody)){    tabColumns($tableBody, 'sampleList');    }
	       if(is_array($result)){
                foreach($result as $res){
                    showRow($res, $conn);
                }
        } else {  showRow($result, $conn);   }     
    } 
}

function showRow($res, $conn){
    $hubs = ['S' => 'SLA', 'B' => 'BLU'];
    $damagedOff = ['0', '9', '5', '6'];
    $classes = ['S' => 'green', 'B' => 'blue', 'G' => 'green', 'D' => 'D', '8' => 'inv', '0' => 'cross', '1' => 'check', 
                '2' => 'prgs', '3' => 'exp', '4' => 'warn', '5' => 'dmg', '9' => 'rqst'];
    $qrfile = '';
    $qrUrl = '';
    $qrCode = '';
    echo('<tr>');
    foreach($res as $key => $data){
        if($key === 'pca'){	echo(getPrjInfo($conn, $res['pn'], 'pca') === $data) ? '<td class="green">' : '<td class="D">';
							echo($data.'</td>');
		}
        else if($key === 'pcb'){    echo(getPrjInfo($conn, $res['pn'], 'pcb') === $data) ? '<td class="green">' : '<td class="D">';
                                    echo($data.'</td>');
		}
        else if($key === 'type')	{   echo('<td class="'.$classes[$data].'">'.$data.'</td>');    }
		else if($key === 'crtdt')	{	echo(showExpiration($data));	}
        else if($key === 'status')	{ 	echo('<td class="'.$classes[$data].'"></td>');    }
        else if($key === 'dept')	{   echo('<td class="'.$data.'">'.strtoupper($data).'</td>');     }
        else if($key === 'info')	{	echo('<td class="info">');
    $qrfile = './QRs/'.sanitStr($res['sn']);
    $qrUrl = 'https://icz19-oaweb.inventec.cz/sampleeq/sn.php?sn='.urlencode($res['sn']);
    $qrCode = (file_exists($qrfile)) ? '<img src="'.$qrfile.'" height="100" alt="QRCode" />' : '';
if(isset($_SESSION['online'])){
	if($_SESSION['online']['admin'] !== 'tech' && $_SESSION['online']['admin'] !== 'sap' && $res['status'] !== '0'){
		echo('<form action="edit.php"><input type="hidden" name="editSn" value="'.htmlspecialchars($res['sn']).'"><input type="submit" value="Edit"></form>');
	}
}
		echo('<div class="detail"><div class="small-header">Info');
if(isset($_SESSION['online'])){
	if($_SESSION['online']['admin'] === 'tech' && !in_array($res['status'], $damagedOff)){
        echo('<a href="notifyDMG.php?sn='.htmlspecialchars($res['sn']).'" onclick="return confirmAction()" class="dmgBtn">Damaged</a></div>');
    }	else {	echo('</div>');	}
}		else {	echo('</div>');	}

		echo(htmlspecialchars($data).'<br><br>'.$qrCode.'<div class="alt">'.$qrUrl.'</div></div></td>');
	   }

	   	else if($key === 'hub'){     echo('<td class="'.$classes[$data].'">'.$hubs[$data].'</td>'); }
    		else {	echo('<td>'.$data.'</td>');		}
	   }   echo('</tr>');
       }

// ############################################################################# AUTO TABLE TH ("</tr></tbody></table>")  <<<<
function tabColumns($x, $y = ''){
    echo('<table class="'.$y.'"><thead><tr>');
		foreach($x as $th){
			echo('<th>'.$th.'</th>');
		}
	echo('</tr></thead><tbody>');
}

function showExpiration($data){
    $element = isMobile() ? 'div' : 'td';
	$date = DateTime::createFromFormat('Y-m-d', $data);
	$today = new DateTime();
	$diff = $today->diff($date);
    $showDate = $date->format('m/y');
		if($diff->days > 365){		return('<'.$element.' class="red">'.$showDate.'</'.$element.'>');		}
		else if ($diff->days > 300){return('<'.$element.' class="orange">'.$showDate.'</'.$element.'>');	}
			else {					return('<'.$element.' class="green">'.$showDate.'</'.$element.'>');	}
}
// ################################################################################################# 	SHOW REPLACEMENT	 <<<<<
function showReplacement($conn, $pn, $dept){

	$tableBody = ['Name', 'PN_#', 'PCA', 'PCB', 'G/D', 'SN#', 'Dept', 'Stat', 'Created', 'Hub', 'Info'];
	$dataBody = ['name', 'pn', 'pca', 'pcb', 'type', 'sn', 'dept', 'status', 'crtdt', 'hub', 'info'];
	$datas = implode(', ', $dataBody);
	$result = query($conn, "SELECT $datas FROM dbo.SAMPLEEQ_sampleTab WHERE status IN('3', '4', '5') AND pn = '$pn' AND dept = '$dept'");
        if(empty($result)){	return;	}
    $pca = getPrjInfo($conn, $pn, 'pca');
    $pcb = getPrjInfo($conn, $pn, 'pcb');

	tabColumns($tableBody, 'sampleList');
    $hubs = ['S' => 'SLA', 'B' => 'BLU'];
    $acroClass = ['S' => 'green', 'B' => 'blue', 'G' => 'green', 'D' => 'D', '1' => 'check', '2' => 'prgs', '3' => 'exp', '4' => 'warn', '5' => 'dmg'];
	foreach($result as $res){
	echo('<tr onclick="replaceSample(\''.$res['sn'].'\', event.target)">');
    foreach($res as $key => $data){
		if($key === 'crtdt'){	echo(showExpiration($data));	}
        else if($key === 'pca'){    
            if($pca === $data){ 
                echo ('<td class="green">'.$data.'</td>');    }
                    else {  echo('<td class="D">'.$data.'</td>'); }
        }
        else if($key === 'pcb'){
            if($pcb === $data){
                echo ('<td class="green">'.$data.'</td>');    }
                    else {  echo('<td class="D">'.$data.'</td>'); }   
        }
        else if($key === 'status'){	echo('<td class="'.$acroClass[$data].'"></td>');	}
        else if($key === 'type'){   echo('<td class="'.$acroClass[$data].'">'.$data.'</td>');    }
        else if($key === 'dept'){   echo('<td class="'.$data.'">'.strtoupper($data).'</td>');     }
        else if($key === 'info'){
            if(!empty($data)){      echo('<td class="info"><div class="detail">
										<div class="small-header">Info</div><div>'.htmlspecialchars($data).'</div></div></td>'); }
                else            {   echo('<td class="empty"></td>');  }
	   }
       else if($key === 'hub'){     echo('<td class="'.$acroClass[$data].'">'.$hubs[$data].'</td>'); }
    else {	echo('<td>'.$data.'</td>');		}
	   }   echo('</tr>');
    }      echo('</tr></tbody></table>');
}

// ################################################################################################# 	GET PROJECT DATA	 <<<<<
function getProjectData($conn, $pn) {

// ##### TABS HEADERS #####
$projectTab = ['PCA', 'PCB', 'ICT', 'OLBS', 'PpP', 'EOLI', 'Side'];
$departments = ['aoi', 'olbs', 'ict', 'eoli'];
// ##### QTY SECTION&PARAMS #####
$hubs = ['S', 'B'];
$types = ['G', 'D'];
$allQty = ['S' => [], 'B' => []];
// ##### PROJECT DATA PARAMS #####
$projectData = ['pca', 'pcb', 'ict', 'olbs', 'ppp', 'ppc', 'side'];
$datas = implode(', ', $projectData);
$headersInfo = queryOne($conn, "SELECT name, cpn FROM dbo.SAMPLEEQ_projects WHERE pn='$pn'");
$req = "SELECT $datas FROM dbo.SAMPLEEQ_projects WHERE pn='$pn'";
$results = queryOne($conn, $req);
$ict = $results['ict'];
$olbs = $results['olbs'];
$eoliMin = $results['ppc'];

	foreach($hubs as $hub){
		foreach($departments as $dept){
			foreach($types as $typ){
				if($dept === 'olbs' && $typ === 'D'){	continue;	}
				if($dept === 'olbs' && $olbs === 0){		$allExpQty[$hub][] = $allQty[$hub][] = 'N/A';	}
				else if($dept === 'ict' && $ict === 0){		$allExpQty[$hub][] = $allQty[$hub][] = 'N/A';	}
                else if($dept === 'ict' && $ict === 1 && $typ === 'G'){ $allQty[$hub][] = $allQty[$hub][0];
																		$allExpQty[$hub][] = $allExpQty[$hub][0];
				}
				else {	$allQty[$hub][] = getSampleCount($conn, $pn, $typ, $dept, $hub);
						$allExpQty[$hub][] = getExpiredQty($conn, $pn, $typ, $dept, $hub);	}
			}
		}
	}
echo ('<h2>'.$headersInfo['name'].'</h2><h3>'.$pn.'</h3><div class="small-header">'.$headersInfo['cpn'].'</div>');
tabColumns($projectTab, 'sampleList');
foreach($results as $data => $res){
    if(in_array($data, ['ict', 'olbs'])){
		echo(($res == 0) ? '<td class="cross"></td>' : '<td class="check"></td>');
	}
		else{	echo('<td>'.$res.'</td>');	}
}	echo('</tr></tbody></table>');

function generateQty($title, $allQty, $allExpQty, $eoliMin){
	$qtyTab = ['AOI', 'OLBS', 'ICT', 'EOLI'];
	$qtyTh = [' colspan="2"', ' rowspan="2"', ' colspan="2"', ' colspan="2"'];
	$qtyTh2nd = (!isMobile()) ? ['Golden', 'Dummy','Golden', 'Dummy','Golden', 'Dummy'] : ['G', 'D','G', 'D','G', 'D'];

	echo('<table class="sampleQty"><thead class="'.clearTxt($title).'">
<tr class="big-header '.clearTxt($title).'"><th colspan="7">'.$title.'</th></tr>
	<tr>');
		foreach($qtyTab as $i => $th){
			echo('<th '.$qtyTh[$i].'">'.$th.'</th>');
		}	echo('</tr><tr>');
		foreach($qtyTh2nd as $th2){	echo('<th>'.$th2.'</th>');	}
			echo('</tr></thead><tbody><tr>');
	foreach($allQty as $j => $qty){
		$class = ($qty === 'N/A') ? 'green' : (($qty == 0 || ($j === 5 && $qty < $eoliMin) || ($j === 6 && $qty < 5)) ? 'red' : 'green');
            echo ('<td class='.$class.'>'.$qty.'</td>');
	}	echo('</tr><tr class="expiredHeader"><td colspan="7">Expired samples</tr><tr class="expired">');
	foreach($allExpQty as $k => $qty){	echo ('<td>'.$qty.'</td>');		}
	echo('</tbody></table>');
}
echo('<div class="qtyTabs">');
    generateQty("Slatina", $allQty['S'], $allExpQty['S'], $eoliMin);
    generateQty("Blučina", $allQty['B'], $allExpQty['B'], $eoliMin);
echo('</div>');
}
// #######################################################################################################		SAMPLE QTY QRY	 <<<<<
function getSampleCount($conn, $pn, $type, $dept, $hub) {
	return querySingle($conn, "SELECT COUNT(*) FROM dbo.SAMPLEEQ_sampleTab WHERE pn='$pn' AND type='$type' AND status IN ('1', '2') AND dept='$dept' AND hub='$hub'");
}
function getExpiredQty($conn, $pn, $type, $dept, $hub){
	return querySingle($conn, "SELECT COUNT(*) FROM dbo.SAMPLEEQ_sampleTab WHERE pn='$pn' AND type='$type'
                                AND status IN ('3', '4', '5') AND dept='$dept' AND hub='$hub'");
}
// ###################################################################################		QR GENERATE & SHOW	<<<<<
function generateQRCode($x){
	$origSN = trim($x);
	$snUrl = urlencode($origSN);
	$qrFileName = sanitStr($origSN);
	$qrTxt = 'https://icz19-oaweb.inventec.cz/sampleeq/sn.php?sn='.$snUrl;
	$file = './QRs/'.$qrFileName;
        if(!file_exists($file)){
	QRcode::png($qrTxt, $file, 'M', 2, 0);
		}
}
// ##################################################################################################       CREATE LOG          <<<<<
function crtLog($sn, $pcaRev, $pcbRev, $editType, $editDept, $editStatus, $editHub, $editInfo, $conn, $dttm, $editedInfo = []){
	$docText = "\n " . $dttm . "\t \t \t | \t \t \t " . $_SESSION['online']['icz']." \t \t \t | EDIT | \t \t  \t ORIGINAL \n";
    $logBody = ['name', 'pn', 'pca', 'pcb', 'sn', 'type', 'dept', 'status', 'hub', 'info'];
    foreach($logBody as $log){
        if($log === 'info'){    $docText .= "\n". $editedInfo[$log] ." | \n";  }
            else {              $docText .= $editedInfo[$log].' | ';        }
    }
    $docText .= "\t \t \t \t \t \t \/ \/ TO \/ \/ \n";
		$docText .= $editedInfo['name'].' | '. $editedInfo['pn'].' | '.$pcaRev.' | '.$pcbRev.' | '.$sn.' | '.$editType.' | '.$editDept.' | '.$editStatus.' | '. $editHub."\n";
		$docText .= $editInfo."\n";
		$file = './doc/logs.txt';
		$log = fopen($file, 'a');
			if($log){
				fwrite($log, $docText);
				fclose($log);
			}
		echo('<div class="err">Edit successful<br><a href="index.php">Back to sample list</a></div>');
    	include('footer.php');
    	exit();
}
// ###################################################################################################      MODIFICATION LOG    <<<<<
function modifyToLog($txt) {
global $dttm;
$file = './doc/modify.txt';
$log = fopen($file, 'a');
	if($log){
        $iczNum = $_SESSION['online']['icz'];
        $userName = $_SESSION['online']['username'];
        $logTxt = "\n $dttm \t | \t $iczNum | $userName \n \t $txt";
		fwrite($log, $txt);
		fclose($log);
	}
}
// ###################################################################################################      PLAIN LOG           <<<<<
function writeToLog($txt) {
$file = './doc/logs.txt';
$log = fopen($file, 'a');
	if($log){
		fwrite($log, $txt);
		fclose($log);
	}
}
// ###################################################################################################      PARTS LOG           <<<<<
function writeToPartLog($txt) {
$file = './doc/partLog.txt';
$log = fopen($file, 'a');
	if($log){
		fwrite($log, $txt);
		fclose($log);
	}
}
// ############################################################################################### 		REMOVE DIACRITICS	 	<<<<<
function clearTxt($text){    return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $text));	}
// #################################################################################################		CLEAN FILENAME		<<<<<
function sanitStr($str) {	return preg_replace('/[^A-Za-z0-9_\-]/', '_', $str) . '.png';	}
// ################################################################################		FILL EMPTY CHARS IN AOI, ICT, OLBS		<<<<<
function fillEmptySN($x){
    $usualSNLen = 29;
    $snLen = strlen($x);
    if($snLen < $usualSNLen) {
        $x .= str_repeat('1', $usualSNLen - $snLen);
    }     return $x;
}
// ####################################################################################		PERCENTUAL PART OF TOTAL AMOUNT		<<<<<
function percent($part, $total){
    return round(($part / $total) * 100, 2);
}
// ####################################################################################		COPY FOLDER + FILES + SUBFOLDERS   <<<<<
function copyFolder($source, $target) {
	if (!is_dir($source)) {		return ('Folder not found');	}
	if (!is_dir($target)) {		mkdir($target, 0777, true);	}
	$iterator = new DirectoryIterator($source);

	foreach ($iterator as $file) {
		if ($file->isDot()) {	continue;	}

		$srcPath = $file->getPathname();
		$targetFldr = $target.DIRECTORY_SEPARATOR.$file->getFilename();

		if ($file->isDir()) {
			copyFolder($srcPath, $targetFldr);
		}	else {	copy($srcPath, $targetFldr);	}
	}
}
// ####################################################################################		GET FOLDER SIZE                    <<<<<
function getFolderSize($folder) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}
// ####################################################################################		SIZE FORMAT TO READABLE FORM       <<<<<
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
        $bytes = $bytes . ' byte';   
    }   else {    $bytes = '0 bytes';     }
    return $bytes;
}