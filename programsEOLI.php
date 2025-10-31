<?php require ('head.php');
    AdminOnly(in_array($adminLevel, $engGroup) || $adminLevel === 'sap');
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
    $prjPics = new DirectoryIterator($host.$path.'prjimg');
    $folders = [];
    $wrongFolders = [];
    $tableBody = ['Folder name', 'Project', 'PN', 'Pic T', 'Pic B'];
    tabColumns($tableBody, 'sampleTab');
    foreach ($prjPics as $a => $f) {
        if (!$f->isDot() && $f->isDir()) {
            $name = $f->getFilename();
            $imgFolder = explode("@", $name);
            $imgFldr = $imgFolder[0];
            $baseProject = substr($imgFldr, 0, strlen($imgFldr) - 1);
            $side = substr($imgFldr, -1);
            $pn = (strlen($name) > 15) ? substr($name, 0, 12) : $name; 
            $img = $f->getPathname().DIRECTORY_SEPARATOR.$imgFldr.DIRECTORY_SEPARATOR.'View.bmp';
            $folders[$pn]['name'] = $name;
            $folders[$pn]['prj'] = getPrjName(substr($name, 0, 12));
            $folders[$pn]['img'.$side] = (file_exists($img)) ? '<a href="machines/images.php?path='.urlencode($img).'" target="_blank">Image</a>' : 'N/A';
        }  
    }
    foreach ($folders as $key => $vals) {
        echo ('<tr>');
        echo ('<td>'.$vals['name'].'</td><td>'.$vals['prj'].'</td><td>'.$key.'</td>');
        echo (isset($vals['imgT'])) ? '<td>'.$vals['imgT'].'</td>' : '<td>N/A</td>';
        echo (isset($vals['imgB'])) ? '<td>'.$vals['imgB'].'</td>' : '<td>N/A</td>';
        echo ('</tr>');
    }
}
require ('footer.php');   ?>