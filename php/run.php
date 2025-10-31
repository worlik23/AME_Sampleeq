<?php
require('dbaccess.php');

header('Content-Type: text/plain; charset=utf-8');
 
$host = $_POST['host'] ?? '';
$command = $_POST['cmdIdx'] ?? '';
$mode = 'plain';

$sql = "SELECT hostname FROM dbo.ENGEEQ_Machines";
$allHosts = query($conn, $sql);
$allowed = [];
foreach ($allHosts as $h) {    $allowed[] = $h['hostname'];     }

if ($host === '' || $command === '') { http_response_code(400); exit("Missing host/cmd\n"); }
if (!in_array($host, $allowed, true)) { http_response_code(400); exit("Host not allowed\n"); }
 
$commands = [
  'ip'     => 'ipconfig',
  'host'   => 'hostname',
  'date'   => 'Get-Date',
  'lcl'    => 'Get-Location',
  'pcInfo' => 'Get-ComputerInfo',
  'process' => 'Get-Process'
];
 
if (!array_key_exists($command, $commands)) {
    http_response_code(400);
    exit("Unknown command key: $command\n");
}
 
$cmd = $commands[$command];
 
$psFile = '\\\\ICZ9-WEB\\htdocs\\sampleeq\\php\\RemoteRun.ps1';
$cmdB64 = base64_encode($cmd);
 
$args = [
  'powershell.exe','-NoProfile','-NonInteractive','-ExecutionPolicy','Bypass',
  '-File',$psFile,
  '-ComputerName',$host,
  '-CommandBase64',$cmdB64,
  '-Mode',$mode
];
 
$desc = [1=>['pipe','w'], 2=>['pipe','w']];
$proc = proc_open($args, $desc, $pipes);
if (!is_resource($proc)) { http_response_code(500); exit("Failed to start PowerShell\n"); }
 
$out = stream_get_contents($pipes[1]); fclose($pipes[1]);
$err = stream_get_contents($pipes[2]); fclose($pipes[2]);
$code = proc_close($proc);
 
if ($code !== 0) {
    http_response_code(500);
    echo $err !== '' ? $err : $out;
} else {
    echo $out !== '' ? $out : $err;
}