param(
    [Parameter(Mandatory=$true)][string]$ComputerName,
    [Parameter(Mandatory=$true)][string]$CommandBase64,
    [ValidateSet('plain','json')][string]$Mode='plain',
    [ValidateSet('auto','powershell','cmd')][string]$Shell='auto'
)
 
$ErrorActionPreference='Stop'
$ProgressPreference='SilentlyContinue'
$InformationPreference='SilentlyContinue'
$VerbosePreference='SilentlyContinue'
$WarningPreference='SilentlyContinue'
3>$null;4>$null;5>$null;6>$null | Out-Null
 
# Pøihlášení pro WinRM
$cred = Import-Clixml '\\ICZ9-WEB\htdocs\sampleeq\php\secure\pscred.xml'
 
# Dekódování pøíkazu
$cmdText = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($CommandBase64))
 
# Lokální detekce shellu (už ne uvnitø ScriptBlocku)
function Detect-Shell([string]$s){
    if ($s -match '%\w+%' -or
        $s -match '(^|[^\w])(ipconfig|ping|whoami|hostname|ver|tasklist|netstat|arp|tracert|dir|type)\b') {
        return 'cmd'
    }
    return 'powershell'
}
$resolvedShell = if ($Shell -ne 'auto') { $Shell } else { Detect-Shell $cmdText }
 
# Vzdálené provedení
$res = Invoke-Command -ComputerName $ComputerName -Credential $cred -HideComputerName -ScriptBlock {
    param($cmdText, $shellPref)
 
    $ErrorActionPreference='Continue'
    $ProgressPreference='SilentlyContinue'
 
    function Run-Cmd([string]$text){
        $full = 'chcp 65001>nul & ' + $text
        $out  = & cmd.exe /c $full 2>&1
        $txt  = $out | Out-String
        $code = if ($LASTEXITCODE -ne $null) { $LASTEXITCODE } else { 0 }
        [pscustomobject]@{ exitCode = $code; text = $txt; shellUsed='cmd' }
    }
 
    function Run-Pwsh([string]$text){
        $Error.Clear()
        $sb   = [scriptblock]::Create($text)
        $out  = & $sb 2>&1
        $txt  = $out | Out-String
        $code = if ($LASTEXITCODE -ne $null) { $LASTEXITCODE }
                elseif ($Error.Count -gt 0) { 1 } else { 0 }
        [pscustomobject]@{ exitCode = $code; text = $txt; shellUsed='powershell' }
    }
 
    if ($shellPref -eq 'cmd') { Run-Cmd  $cmdText }
    else                       { Run-Pwsh $cmdText }
 
} -ArgumentList $cmdText, $resolvedShell
 
# Výstup
if ($Mode -eq 'json') {
    $res | ConvertTo-Json -Depth 3
} else {
@"
$($res.text)
----
Shell: $($res.shellUsed)
ExitCode: $($res.exitCode)
"@
}