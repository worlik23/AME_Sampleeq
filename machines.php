<?php require ('head.php');

AdminOnly(in_array($adminLevel, $engGroup) || $adminLevel === 'sap');

$depSelect = ['', 'AOI', 'SPI', 'EOLI', 'ICT3'];
$lineSelect = ['', 1, 2, 'A', 'B', 'C'];

function isAvailable($path) {
    return is_dir($path);
}

?>
<h1>Machines</h1>
<form method="GET" action="" onSubmit="showLoadingAnimation();">
<input type="submit" name="aoi" value="AOI">
<input type="submit" name="spi" value="SPI">
<input type="submit" name="eoli" value="EOLI">
<!--
<input type="submit" name="ict3" value="ICT TRI">
<input type="submit" name="ict2" value="ICT Keysight">
-->
</form>
<br>
<?php
if (isset($_GET['aoi'])) {  showMachines('AOI');   }
if (isset($_GET['spi'])) {  showMachines('SPI');   }
if (isset($_GET['eoli'])){  showMachines('EOLI');  }
if (isset($_GET['ict3'])){  showMachines('ICT3');  }
if (isset($_GET['ict2'])){  showMachines('ICT2');  }

function showMachines($type) {
    global $conn;
    $sql = "SELECT * FROM dbo.ENGEEQ_Machines WHERE Type ='$type'";
    $showAll = query($conn, $sql);
    $tableBody = ['#', 'Type', 'Line', 'Position', 'Status', 'Open'];
    tabColumns($tableBody, 'sampleTab');
    $row = 0;
    foreach ($showAll as $machines) {
        $folder = '\\\\'.$machines['hostname'].$machines['path'];
        $row++;
        if ($type === 'SPI') {  $progType = 'AOI';  }
        elseif ($type === 'ICT2') {    $progType = 'ICT3';     }
            else {  $progType = $type;  }
            echo ('<tr><form method="GET" action="programs'.$progType.'.php" onsubmit="showLoadingAnimation();"><td>'.$row.'</td>');
        foreach ($machines as $key => $col) {
            if (in_array($key, ['hostname', 'path'])) {
                echo ('<input type="hidden" name="'.$key.'" value="'.$col.'">');
            }   else {
                    echo ('<td><input type="hidden" name="'.$key.'" value="'.$col.'">'.$col.'</td>');
                }    
        }
            echo (isAvailable($folder)) ? '<td class="green">Online</td><td><input type="submit" name="showFolders" value="Open"></form>' : '<td class="red">Offline</form>';
            echo ('</td></tr>');
    }
        echo ('</tbody></table>');
}

if ($adminLevel === '9') {  include('machines/addMachine.php');  }

require ('footer.php');   ?>