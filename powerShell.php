<?php   
require('head.php'); 
AdminOnly(in_array($adminLevel, $engGroup));
$allowMachine = [   
    'aoi' => "'AOI', 'SPI', 'EOLI', 'ACI'", 
    'ict' => "'ICT2', 'ICT3'",
    'olbs' => "'OLBS'",
    '9' => "'AOI', 'SPI', 'EOLI', 'ACI', 'ICT2', 'ICT3', 'OLBS'"
];
$allAllowed = $allowMachine[$adminLevel];
$sql = "SELECT Type, Line, Position, hostname FROM dbo.ENGEEQ_Machines WHERE Type IN($allAllowed)";
$allHosts = query($conn, $sql);
$allowed = [];
    echo ('<div class="row"><select id="selectHost">');
foreach ($allHosts as $h) {
    $allowed[] = $h;
    $hostname = $h['hostname'];
    $type = $h['Type'];
    $line = $h['Line'];
    $position = $h['Position'];
    echo ("<option value=\"$hostname\">$type L$line|$position</option>");
}
?>
</select></div><div class="row">
<label>DATE<input type="radio" name="cmd" value="date" checked></label>
<label>HOSTNAME<input type="radio" name="cmd" value="host"></label>
<label>IP<input type="radio" name="cmd" value="ip"></label>
<label>PROCESS<input type="radio" name="cmd" value="process"></label>
<label>PC INFO<input type="radio" name="cmd" value="pcInfo"></label>
</div>
<form id="f">
  <input type="hidden" id="host" name="host" value="">
  <input type="hidden" id="cmdIdx" name="cmdIdx" value="" required>
  <button>Spustit</button>
</form>
<pre id="out"></pre>
<?php require('footer.php'); ?>
<script>

const cmdIdx = document.getElementById('cmdIdx');
const selectHost = document.getElementById('selectHost');
const pcHost = document.getElementById('host');
const inpts = document.querySelectorAll('input[name="cmd"]');

hostSelection();
cmdSelection(inpts[0].value);

function hostSelection() {
    host.value = selectHost.value;
}
function cmdSelection(x) {
    cmdIdx.value = x;
}

selectHost.addEventListener('change', hostSelection);

inpts.forEach(inpt => {
    inpt.addEventListener('click', () => {
        cmdSelection(inpt.value);
    });
});

document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoadingAnimation();
const fd = new FormData(e.target);
  const res = await fetch('php/run.php', { method:'POST', body: fd });
  document.getElementById('out').textContent = await res.text();
  hideLoadingAnimation();
});
</script>