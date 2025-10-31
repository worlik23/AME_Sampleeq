<?php
$updateStatus = [];
$tableBody = ['E-m@il', 'ICZ','Nickname', 'Level', 'Password', 'Change', '&times;'];
$newAccBody = ['E-m@il', 'ICZ', 'Alias', 'Level', 'Create Account'];

function adminLevelSelect($userLevel = false) {
    if ($_SESSION['online']['icz'] === '248043') {
        $levels = ['qua', 'tech', 'sap', 'aoi', 'ict', 'olbs', '9'];
    }   else {  $levels = ['qua', 'tech', 'sap', 'aoi', 'ict', 'olbs'];    }
        $return = '<select name="adminChangeLevel">';
    foreach ($levels as $level) {
        $return .= '<option value="'.$level.'"';
        $return .= ($userLevel && $level === $userLevel) ? ' selected disabled>' : '>';
        $return .= strtoupper($level).'</option>';
    }
    $return .= '</select>';
    return ($return);
}

if (isset($_POST['adminChangeUser'])) {
    $usrIcz = $_POST['adminChangeICZ'];
    $usrAlias = $_POST['adminChangeAlias'];
    $actualName = querySingle($conn, "SELECT username FROM dbo.SAMPLEEQ_users WHERE icz = '$usrIcz'");
    if ($actualName && $actualName !== $usrAlias) {
        $params = array($usrAlias, $usrIcz);
        $sql = "UPDATE dbo.SAMPLEEQ_users SET username = ? WHERE icz = ?";
        $updateAlias = queryUpdate($conn, $sql, $params);
        if ($updateAlias) {
            $updateStatus[] = 'Users alias has been changed'; 
            $logTxt = "\n".$dttm." | ".$userOn['icz']." has changed alias of user --->\t|".$usrIcz." from ".$actualName." to ".$usrAlias."\t |<--- USER ALIAS CHANGED";
            writeToLog($logTxt);
        }
    }
    $usrChangeLevel = (isset($_POST['adminChangeLevel'])) ? $_POST['adminChangeLevel'] : false;
    $newPass = (isset($_POST['adminChangePass']) && !empty($_POST['adminChangePass'])) ? trim($_POST['adminChangePass']) : false;
    if ($newPass) {
        $newPassword = password_hash($newPass, PASSWORD_DEFAULT);
        $params = array($newPassword, $usrIcz);
        $sql = "UPDATE dbo.SAMPLEEQ_users SET pass = ? WHERE icz = ?";
        $changePass = queryUpdate($conn, $sql, $params);
        if ($changePass) {
            $updateStatus[] = 'Users password has been changed'; 
            $logTxt = "\n".$dttm." | ".$userOn['icz']." has changed password of user --->\t|".$usrIcz."\t\t\t |<--- USER PASSWORD CHANGED";
            writeToLog($logTxt);
        }
    }
    if ($usrChangeLevel) {
        $usrActualLevel = querySingle($conn, "SELECT admin FROM dbo.SAMPLEEQ_users WHERE icz='$usrIcz'");
        if ($usrActualLevel !== $usrChangeLevel) {
            $params = array($usrChangeLevel, $usrIcz);
            $sql = "UPDATE dbo.SAMPLEEQ_users SET admin = ? WHERE icz = ?";
            $changeLevel = queryUpdate($conn, $sql, $params);
            if ($changeLevel) { 
                $updateStatus[] = 'Users admin level has been changed.';
                $logTxt = "\n".$dttm." | ".$userOn['icz']." has changed status of user --->\t|".$usrIcz." from ".$usrActualLevel." to ".$usrChangeLevel."\t |<--- USER ADMIN LEVEL CHANGED";
                writeToLog($logTxt);
            }
        }
    }
}

if (isset($_POST['adminDeleteUser'])) {
    $usrIcz = $_POST['adminChangeICZ'];
    $sql = "DELETE FROM dbo.SAMPLEEQ_users WHERE icz = ?";
    $params = array($usrIcz);
    $deleteAccount = queryUpdate($conn, $sql, $params);
    if ($deleteAccount) {
        $updateStatus[] = 'User account has been removed from database';
        $logTxt = "\n".$dttm." | ".$userOn['icz']." has removed user --->\t|".$usrIcz."\t\t\t |<--- USER REMOVED";
        writeToLog($logTxt);
    }
}

if (isset($_POST['createAccount'])) {
    $updateStatus = [];
    $emailStr = (!empty($_POST['newUserEmail'])) ? trim($_POST['newUserEmail']) : false;
    if (!$emailStr) {       $usrEmail = false;
                            $updateStatus[] = 'Empty e-mail adress';
    }
    $validDomain = strpos($emailStr, '@inventec.com');
    if (!$validDomain) {    $usrEmail = false;  
                            $updateStatus[] = 'E-mail has invalid format (@inventec.com)';
    }
    if ($emailStr && $validDomain && filter_var($emailStr, FILTER_VALIDATE_EMAIL)) {
        $usrEmail = $emailStr;
    }   else {  $usrEmail = false;  }
    $usrIcz = (!empty($_POST['newUserIcz'])) ? trim($_POST['newUserIcz']) : false;
    $usrName = (!empty($_POST['newUserAlias'])) ? trim($_POST['newUserAlias']) : false;
    $usrLevel = (!empty($_POST['adminChangeLevel'])) ? trim($_POST['adminChangeLevel']) : false;
    if (!$usrIcz)   {   $updateStatus[] = 'ICZ is empty';       }
        else {          $existICZ = querySingle($conn, "SELECT username FROM dbo.SAMPLEEQ_users WHERE icz = '$usrIcz'");    }
    if ($existICZ) {    $updateStatus[] = 'ICZ already exists';
                        $usrIcz = false;   
    }
    if (!$usrName)  {   $updateStatus[] = 'User Alias is empty';    }
    if ($usrEmail && $usrIcz && $usrName && $usrLevel) {
        $newPass = '12344321';
        $usrPass = password_hash($newPass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO dbo.SAMPLEEQ_users ([icz],[email],[admin],[pass],[username]) VALUES (?, ?, ?, ?, ?)";
        $params = array($usrIcz, $usrEmail, $usrLevel, $usrPass, $usrName);
        $createAccount = queryUpdate($conn, $sql, $params);
        if ($createAccount) {   
            $updateStatus[] = 'New account has been created';
            $logTxt = "\n".$dttm." | ".$userOn['icz']." has created new user account --->\t|".$usrIcz."\t\t\t |<--- NEW USER ACCOUNT CREATED";
            writeToLog($logTxt);  
        }   
            else    {   $updateStatus[] = 'Error';   }
    }
}
    
    echo ('<h2>Create new account</h2>');
    tabColumns($newAccBody, 'sampleTab');
    echo ('<tr><form method="post" onsubmit="return confirmEdit(event);">');
    echo ('<td><input type="email" name="newUserEmail" pattern="^[a-zA-Z0-9.]+@inventec\.com$" autocomplete="off" required></td>');
    echo ('<td><input type="text" name="newUserIcz" minlength="6" maxlength="6" pattern="\d{6}" autocomplete="off" required></td>');
    echo ('<td><input type="text" name="newUserAlias" placeholder="Nickname" pattern="[A-Za-z0-9]{4,50}" autocomplete="off" required></td>');
    echo ('<td>'.adminLevelSelect(false).'</td>');
    echo ('<td><input type="submit" name="createAccount" value="Create account"></td></form></tr>');
    echo ('</tbody></table>');

if (!empty($updateStatus)) {
        foreach ($updateStatus as $status) {    echo ('<div class="noResult">'.$status.'</div>');    }
}

$allUsers = query($conn, "SELECT icz, email, username, admin, pass FROM dbo.SAMPLEEQ_users WHERE admin NOT IN('9')");
    tabColumns($tableBody, 'sampleTab');
foreach ($allUsers as $usr) {
    echo ('<tr><form method="post" onsubmit="return confirmAction();"><td>'.$usr['email'].'</td>');
    echo ('<td><input type="hidden" name="adminChangeICZ" value="'.$usr['icz'].'">'.$usr['icz'].'</td>');
    echo ('<td><input type="text" name="adminChangeAlias" value="'.$usr['username'].'" pattern="[A-Za-z0-9]{4,50}" autocomplete="off"></td>');
    echo ('<td>'.adminLevelSelect($usr['admin']).'</td>');
    echo ('<td><input type="password" name="adminChangePass" minlength="6" maxlength="25" placeholder="••••••" autocomplete="off"></td>');
    echo ('<td><input type="submit" name="adminChangeUser" value="Change"></td>');
    echo ('<td><input type="submit" name="adminDeleteUser" value="&times;"></td></form></tr>');
}
    echo ('</tbody></table>');

?>