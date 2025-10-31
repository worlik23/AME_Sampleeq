<?php require ('head.php');
    AdminOnly(in_array($adminLevel, $engGroup));

function getPrjName($projectPN) {
    global $projects;
    $p = '';
    foreach ($projects as $prjName => $prjPn) {
        if (in_array($projectPN, $prjPn)) {
            $p = $prjName;
            return $p;
        }
    }
    if ($p === '') {  return false;   }
}   

?>
<h1>Machine programs</h1>

<?php

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['showFolders'])) {
    $type = $_GET['Type'];
    $low = strtolower($type);
    echo ('<a class="backBtn" href="machines.php?'.$low.'='.$type.'">&lt;&lt;Back to machines</a>');
    $line = $_GET['Line'];
    $posi = $_GET['Position'];
    $machiName = $type.'-L'.$line.'-'.$posi;
    $host = '\\\\'.$_GET['hostname'];
    $path = $_GET['path'];
    $paths = [];
    $fldrs = [];
    $class = [];
    $status = [];
    $prj = [];
    $imgs = [];
    $docs = [];
    $allFolders = new DirectoryIterator($host.$path);
    foreach ($allFolders as $k => $f) {
        $err = [];
        if ($f->isDir() && !$f->isDot()) { 
            $folder = $f->getFilename();
//  ###################################################################################################################################    ONLY ICT TRI PROGRAMS    <<<<<
    if ($type === 'ICT3' && substr($folder,0,3) !== 'T35') { continue;  }
    if ($type === 'ICT3' && substr($folder,0,3) === 'T35') {
        $fldrs[] = $folder;
        $prj[] = getPrjName('1395K'.substr($folder, 1,7));
        $class[] = '';
        $status[] = $docs[] = $imgs[] = 'N/A';
        continue;
    }
    if ($type === 'ICT2') {
        $fldrs[] = $folder;
        $class[] = '';
        $prj[] = $status[] = $imgs[] = 'N/A';
        $doc = '';
        foreach (new DirectoryIterator($f->getPathname()) as $files) {
            if ($files->getFilename() === 'testplan' && $doc === '') {
                $plan = $files->getPathname();
                $doc = '<a href="machines/images.php?testplan='.urlencode($plan).'" target="_blank">TESTPLAN</a>';
            }
        }
        if ($doc !== '') {  $docs[] = $doc;     }
            else {  $docs[] = 'N/A'; }
        continue;
    }
//  #################################################################################################################################    ONLY AOI + SPI PROGRAMS    <<<<<
    if (in_array($type, ['AOI', 'SPI'])) {
            $fldrs[] = $folder;
            if (strlen($folder) === 17) {
                $projectPN = substr($folder, 0, 12);
                if (!getPrjName($projectPN)) {  
                    $prj[] = 'N/A';
                    $err[] = 'Invalid PN';
                    $class[] = ' class="progRed"'; 
                }   else {  
                        $prj[] = getPrjName($projectPN);    
                    }
                if ($folder[14] !== $line) {   
                    $class[] = ' class="progOrange"';    
                    $err[] = 'Copied';
                }
            }   else    {   
                    $class[] = ' class="progRed"';
                    $prj[] = 'N/A';       
                    $err[] = 'Invalid name';
                }
                if (!empty($err)) { $status[] = implode(',', $err); }
                    else {  $status[] = 'Correct';  
                            $class[] = '';  }
                if ($type === 'AOI') {  $path = $f->getPathname().'\\'.$f->getFilename();   }
                    else {  $path = $f->getPathname();   }
                $paths[] = $path;
                if ($type === 'AOI') {  
                    $ext = ['jpg', 'his'];  
                }
                if ($type === 'SPI') {  
                    $ext = ['jpg', 'log'];
                }
                $img = '';
                $doc = '';
                if (!empty($ext)) {
            if (is_dir($path)) {
                    foreach (new DirectoryIterator($path) as $file) {
                        if (in_array($file->getExtension(), $ext)) {
                            $fName = $file->getFilename();
                            if ($file->getExtension() === 'jpg' && $img === '') {
                                $img = '<a href="machines/images.php?path='.urlencode($path.'\\'.$fName).'" target="_blank">Image</a>';
                            }   
                            else if ($file->getExtension() !== 'jpg' && $doc === '') {  
                                $doc = '<a href="machines/machineLog.php?file='.urlencode($path.'\\'.$fName).'" target="_blank">'.$fName.'</a>';    
                            }
                        }
                    }   
                }
            }
               if ($img === '') { $imgs[] = 'No Image';  }
                else {  $imgs[] = $img;  }
               if ($doc === '') { $docs[] = 'No File';  }
                else {  $docs[] = $doc;  }
        }
    }
    }
        echo ('<h2>'.$machiName.'</h2>');
        $tableBody = ['Program name', 'Project', 'Image', 'File', 'Status'];
        tabColumns($tableBody);
    foreach ($fldrs as $key => $p) {
        echo ('<tr'.$class[$key].'>');
        echo ('<td>'.$p.'</td><td>'.$prj[$key].'</td><td>'.$imgs[$key].'</td><td>'.$docs[$key].'</td><td>'.$status[$key].'</td></tr>');
    }
        echo ('</tbody></table>');
}   else {  echo ('<a class="backBtn" href="machines.php">&lt;&lt;Back to machines</a>');    }

require ('footer.php');   ?>