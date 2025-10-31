<?php
//  ##################################################################################################################################     + ADD MACHINE TO LIST    <<<<<
if (isset($_POST['addMachine'])) {
    $type = (isset($_POST['Type'])) ? $_POST['Type'] : false;
    $line = (isset($_POST['Line'])) ? $_POST['Line'] : false;
    $posi = (isset($_POST['Position'])) ? $_POST['Position'] : 0;
    $host = (isset($_POST['hostname'])) ? trim($_POST['hostname']) : false;
    $path = (isset($_POST['path'])) ? trim($_POST['path']) : false;
    if ($type && $line && $host && $path) {
        if ($path[0] !== '\\') {    $path = '\\'.$path;    }
        if ($path[strlen($path) - 1] !== '\\') {    $path = $path.'\\'; }
        $machineFolder = '\\\\'.$host.$path;
            if (is_dir($machineFolder)) {
                $insertMachine = queryUpdate($conn, "INSERT INTO dbo.ENGEEQ_Machines (Type,Line,Position,hostname,path) 
                                                VALUES (?,?,?,?,?)", [$type, $line, $posi, $host, $path]);
                echo ('Success');
            }   else {  echo ('Machine connection error.');    }
    }
}
?>
<br>
<h3>Add new machine</h3>
<form method="POST">
    <select name="Type" required onChange="showPos(this.value);">
        <option value="" selected>Type</option>
        <option value="AOI">AOI</option>
        <option value="SPI">SPI</option>
        <option value="EOLI">EOLI</option>
        <option value="ICT3">ICT TRI</option>
        <option value="ICT2">ICT Keysight</option>
    </select>
    <select name="Line" required>
        <option value="">Line</option>
        <option value="1">Line 1</option>
        <option value="2">Line 2</option>
        <option value="A">Line A</option>
        <option value="B">Line B</option>
        <option value="C">Line C</option>
    </select>
    <select id="position" name="Position" class="hidden">
        <option value="0" selected>One side line</option>
        <option value="1">1st</option>
        <option value="2">2nd</option>
    </select>
    <input type="text" name="hostname" maxlength="20" minlength="5" placeholder="hostname" required>
    <input type="text" name="path" minlength="5" placeholder="Path to programs folder" required>
    <input type="submit" name="addMachine" value="+Add new machine">
</form>
<script src="js/machines.js?v=1.0" type="text/javascript" defer></script>