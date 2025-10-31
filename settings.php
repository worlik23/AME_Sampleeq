<?php
require('head.php');
	if($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['passCh'])){
		echo('<div class="err">Password has been changed</div>');
	}
AdminOnly($userOn);
?>
<h1>Change password</h1>
<form method="post" class="columnForm">
<div class="big-header">Change password</div>
<input type="hidden" name="login" autocomplete="login" value="<?php echo($_SESSION['online']['icz']); ?>">
<label for="current-password">Current password</label>
	<input type="password" id="current-password" name="current-password" minlength="6" maxlength="25"
			placeholder="••••••" autocomplete="current-password" required>
<label for="new-password">New password</label>
	<input type="password" id="new-password" name="new-password" minlength="6" maxlength="25"
			placeholder="6 - 25 characters" autocomplete="off" required>
<label for="confirm-password">Confirm password</label>
	<input type="password" id="confirm-password" name="confirm-password" minlength="6" maxlength="25"
			placeholder="Repeat new password" onblur="verifyPass()" autocomplete="off" required>
	<input type="submit" name="changePass" value="Change password">
</form>

<?php

if ($adminLevel === '9' && !isMobile()) {
    include ('./php/adminSettings.php');
}

if(isset($_POST['changePass'])){
    $icz = $_POST['login']; 
	$curpass = $_POST['current-password'];
	$newpass = trim($_POST['new-password']);
	$confirmpass = trim($_POST['confirm-password']);
	$passMatch = false;
    if(empty($newpass)){
        $_SESSION['info'] = 'Empty field';
    }
		else {

	if($newpass !== $confirmpass){
        echo('<div class="err">Passwords don\'t match</div>');
    }	else {	$passMatch = true;	}

	if(!empty($newpass) AND $passMatch){
		$verifyCurPass = password_verify($curpass, $_SESSION['online']['pass']);
		if($verifyCurPass){
			$newpass = password_hash($confirmpass, PASSWORD_DEFAULT);
			$changePass = queryUpdate($conn, "UPDATE dbo.SAMPLEEQ_users SET pass = ? WHERE icz = ?"
											, [$newpass, $icz]);
		if($changePass){
			session_unset();
			setcookie("user_token", "", time() - 3600, "/");
			header('Location: settings.php?passCh=1');
			die();
			}
		}	else {	echo('<div class="err">Wrong password</div>');			}
	}		else {	echo('<div class="err">Some error appeared</div>');	}
}
}

include('footer.php'); ?>

