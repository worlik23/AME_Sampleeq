<?php require ('head.php');
    AdminOnly(in_array($adminLevel, $engGroup) || $adminLevel === 'sap');
?>
<h1>Machine programs</h1>
<?php
//      ################################################################################################################            CHANGE PROGRAM RANGE        <<<<<<<        
if (isset($_POST['changeProgram'])) {
    $progType = $_POST['type'];
    $progLine = $_POST['line'];
    $progPos = $_POST['pos'];
    $progName = $_POST['program'];
    $progReady = $_POST['ready'];
    $params = [$progReady, $progType, $progLine, $progPos, $progName];
    $sql = "UPDATE dbo.ENGEEQ_AOIProg SET ready=? WHERE type=? AND line=? AND pos=? AND program=?";
    $updateProgram = queryUpdate($conn, $sql, $params);
    if ($updateProgram) {
        echo ('Program status has been changed');
    }   else {  echo ('AWW SHIT - ERROR');   }
}
//      ################################################################################################################            INSERT PROGRAM TO DB        <<<<<<<
function insertFolderToDb($notIncluded = []) {
    global $conn;
    if (strlen($notIncluded[3]) > 17) {  
        echo ('<div class="incorrect">INVALID PROGRAM NAME --->'.$notIncluded[3].'</div>');
        return;
    }
    $sql = "INSERT INTO dbo.ENGEEQ_AOIProg (type, line, pos, program, prj) VALUES(?,?,?,?,?)";
    $createProgram = queryUpdate($conn, $sql, $notIncluded);
        if ($createProgram) {   echo ('<div class="correct">New program has been added to AOI Db ---> '.$notIncluded[3].'</div>');    }
            else {  echo ('<div class="incorrect">Db ERROR</div>');    }
}
//      ################################################################################################################            EXISTING RECORD PROGRAM     <<<<<<<
function machinExists($type, $line, $posi, $folder){
    global $conn;
    $exists = queryOne($conn, "SELECT * FROM dbo.ENGEEQ_AOIProg WHERE type='$type' AND line='$line' AND pos = '$posi' AND program = '$folder'");
    if (!empty($exists)) {
        return $exists;
    }   else {  return;  }
}
//      ################################################################################################################            SHOW FOLDERS & RECORDS      <<<<<<<
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['showFolders'])) {
    $type = $_GET['Type'];
    $low = strtolower($type);
    echo ('<a class="backBtn" href="machines.php?'.$low.'='.$type.'">&lt;&lt;Back to machines</a>');
    $line = $_GET['Line'];
    $posi = $_GET['Position'];
    $host = '\\\\'.$_GET['hostname'];
    $path = $_GET['path'];
    $machineDbs = query($conn, "SELECT * FROM dbo.ENGEEQ_AOIProg WHERE type = '$type' AND line = '$line' AND pos = '$posi'");
    foreach ($machineDbs as $mach) {
        foreach($mach as $key => $val) {
            $machineDb[$mach['program']][] = $val;
        }
    }
    $imgs = [];
    $docs = [];
    $machiName = $type.'-L'.$line.'-'.$posi;
    $allFolders = new DirectoryIterator($host.$path);
    foreach ($allFolders as $k => $f) {
        $insertToDb = false;
        if ($f->isDir() && !$f->isDot()) { 
            $folder = $f->getFilename();
            $pathName = $f->getPathname();
            $path = ($type === 'AOI') ? $pathName.'\\'.$folder : $pathName;
            $ext = ($type === 'AOI') ? ['jpg', 'his'] : ['jpg', 'log'];
            if (strlen($folder) === 17) {
                $project = getPrjName(substr($folder, 0, 12));
                if($project === 'N/A') {        $err[] = 'Unknown PN';      }
                if ($folder[14] !== $line) {    $err[] = 'Copied';          }
            }   else {   $err[] = 'Invalid name';   }
            if (empty($err)) {  $insertToDb = true;
                                $info[] = 'Correct';  
            }   else {  $insertToDb = false; 
                        $info[] = implode(',', $err);
                }
                $paths[] = $path; 
                $img = $doc = '';
            if(is_Dir($path)){
            foreach (new DirectoryIterator($path) as $file) {
            if (in_array($file->getExtension(), $ext)) {
                if ($file->getExtension() === 'jpg' && $img === '') {
                    $fName = $file->getFilename();
                    $img = '<a href="machines/images.php?path='.urlencode($path.'\\'.$fName).'" target="_blank">Image</a>';
                }   
                else if ($file->getExtension() !== 'jpg' && $doc === '') {  
                    $fName = $file->getFilename();
                    $doc = '<a href="machines/machineLog.php?file='.urlencode($path.'\\'.$fName).'" target="_blank">'.$fName.'</a>';    
                }
            }
            }
            $imgs[] = ($img === '') ? 'No Image' : $img;  
            $docs[] = ($doc === '') ? 'No File' : $doc;
            
            $exist = machinExists($type, $line, $posi, $folder);
            if (!empty($exist)){
                $fldrs[$folder] = array_merge(machinExists($type, $line, $posi, $folder), ['img' => $img, 'doc' => $doc]);
            }   else {  
                        $notIncluded = [$type, $line, $posi, $folder, $project];    
                        insertFolderToDb($notIncluded);
                        $fldrs[$folder] = ['program' => $folder, 'prj' => $project, 'ready' => 0, 'img' => $img, 'doc' => $doc]; 
                }    
                }
            }
        }
        echo ('<h2>'.$machiName.'</h2>');
        $tableBody = (in_array($adminLevel, $aoiEng)) ? ['Program name', 'Project', 'Ready', 'Image', 'File', 'Confirm'] : ['Program name', 'Project', 'Image'];
        tabColumns($tableBody, 'sampleList');
        $keysToForm = ['type' => $type,'line' => $line, 'pos' => $posi];
    foreach ($fldrs as $key => $p) {
        $rangeVal = $p['ready'];
        $project = getPrjName(substr($p['program'], 0, 12));
        if (strlen($p['program']) > 17) {   
            $invalid[$p['program']] = $fldrs[$p['program']];
            $invalid[$p['program']]['note'] = 'Invalid Name'; 
            continue;    
        }
        if (strlen($p['program']) > 13){
            if ($p['program'][14] !== $line) {  
                $invalid[$p['program']] = $fldrs[$p['program']];
                $invalid[$p['program']]['note'] = 'Wrong line';
                continue;    
            }
        }
        if ($project === 'N/A') {  
            $invalid[$p['program']] = $fldrs[$p['program']];
            $invalid[$p['program']]['note'] = 'Invalid PN';
            continue;    
        }
        echo ('<tr><form method="POST">');
            foreach ($keysToForm as $name => $value) {
                echo ('<input type="hidden" name="'.$name.'" value="'.$value.'">');
            }   echo ('<input type="hidden" name="program" value="'.$p['program'].'">');
        echo ('<td>'.$p['program'].'</td>
                <td>'.$p['prj'].'</td>');
            
        echo (in_array($adminLevel, $aoiEng)) ? '<td><label class="range-label">
                    <input type="range" id="'.$p['program'].'" onInput="changeRange(this.id);" name="ready" value="'.$rangeVal.'" step="5" min="0" max="100">
                    <span id="lbl-'.$p['program'].'">'.$rangeVal.'</span>%</label></td>' : '';
                echo ('<td>'.$p['img'].'</td>');
                echo (in_array($adminLevel, $aoiEng)) ? '<td>'.$p['doc'].'</td>' : '';
                echo (in_array($adminLevel, $aoiEng)) ? '<td><input type="submit" name="changeProgram" value="Ok"></form></td></tr>' : '';
    }
        echo ('</tbody></table>');
    if (!empty($invalid)) {
        $tableBody = ['Program name', 'Project', 'Note', 'Image', 'File'];
        tabColumns($tableBody);
            foreach ($invalid as $row) {
                echo ('<tr><td>'.$row['program'].'</td><td>'.$row['prj'].'</td><td>'.$row['note'].'</td><td>'.$row['img'].'</td><td>'.$row['doc'].'</td>');    
            }   echo ('</tbody></table>');   
    }
}
    else {  echo ('<a class="backBtn" href="machines.php">&lt;&lt;Back to machines</a>');    }

    
require ('footer.php');   ?>