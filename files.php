<?php 
require('head.php'); 
AdminOnly($userOn);
if ($adminLevel === '9') {  $tableBody = (!isMobile()) ? ['File name', 'Date', 'Size', '▼', '&times;'] : ['File name', '▼', '&times;'];     }
    else {                  $tableBody = (!isMobile()) ? ['File name', 'Date', 'Size', '▼'] : ['File name', '▼'];     }
$folders = ['aoiCad' => './cad', 
            'boms' => './bomImport', 
            'cirdi' => './cirdi',
            'kostalCad' => './cadImport', 
            'axi' => './axi', 
            'draw' => './draw', 
            'gerber' => './gerber'];
            
$headers = ['aoiCad' => '<h3>AOI CADs.xls</h3>', 
            'boms' => '<h3>PDM BOMs.xlsx</h3>', 
            'cirdi' => '<h3>CIRDI</h3>',
            'kostalCad' => '<h3>GENCADs.cad</h3>', 
            'axi' => '<h3>AXI.aoi files</h3>', 
            'draw' => '<h3>Board drawings</h3>',
            'gerber' => '<h3>Gerbers AOI</h3>'];

function showFolder($header, $folder, $tableBody, $param = '') {
    $fldr = new DirectoryIterator($folder);
    echo ($header);
    tabColumns($tableBody, 'files');
    foreach ($fldr as $file) {
        if ($file->isFile()) {
            $f = $folder.'/'.$file->getFilename();
            $totalSize = ceil($file->getSize() / 1000);
            if ($totalSize < 1000) {
                $size = $totalSize.' kb';
            }   else {  $size = ceil($totalSize / 1000).' Mb';  }
            $button = ($param !== '') ? 'Show' : '▼';
            echo ('<tr><td>'.$file->getFilename().'</td>');
            echo (!isMobile()) ? '<td>'.date('d.m.y', $file->getMTime()).'</td><td>'.$size.'</td>' : '';
            echo('<td><a href="'.$f.'" '.$param.'>'.$button.'</a></td>');
            if ($_SESSION['online']['admin'] === '9') { 
                echo ('<td class="adminCol"><form method="get" onsubmit="return confirmAction();">');
                echo ('<input type="hidden" name="deleteFile" value="'.$f.'">');
                echo ('<input type="submit" name="delete" value="&times;">'); 
                echo('</form></td>');
            }
                echo('</tr>');
        }
    }
    echo('</tbody></table>');
}
?>
<h1>Documents, files, source info</h1>
<h2>Folders</h2>
<form method="get" id="partsForm" action="">
<input type="submit" name="boms" value="PDM BOM">
<input type="submit" name="draw" value="Drawings">
<?php
    if (in_array($adminLevel, $engGroup)) {
        echo('<input type="submit" name="gerber" value="Gerber">
              <input type="submit" name="kostalCad" value="GENCAD">');
    }
    if (in_array($adminLevel, $aoiEng)) {
        echo ('<input type="submit" name="aoiCad" value="AOI CAD">
               <input type="submit" name="axi" value="AXI files">');
    }
    if (in_array($adminLevel, array_merge($ictEng, $olbsEng))) {
        echo ('<input type="submit" name="cirdi" value="CIRDI">');
    }
?>
</form>
<br>
<?php 

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $eraseFile = $_GET['deleteFile'];
    if (file_exists($eraseFile)) {
        if (unlink($eraseFile)) {
            echo ('File '.$eraseFile.' has been removed');
            $txt = "\n".$dttm." | ".$userOn['icz']."\t | \t File : ".$eraseFile." \t \t |<<--- FILE REMOVED ||";
            modifyToLog($txt);
        }
    }
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET) && !isset($_GET['delete'])) {
    $key = array_key_first($_GET);
    $header = $headers[$key];
    $folder = $folders[$key];
    if ($key === 'draw' || $key === 'axi' || $key === 'kostalCad') {      $param = 'target="_blank"'; }
    else {                      $param = '';                }
    showFolder($header, $folder, $tableBody, $param);
}

require('footer.php'); ?>