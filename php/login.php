<?php
$loginForm = 'placeholder="ICZ';
$passForm = 'Password';

if(isset($_POST["loginBtn"])){
		if(!empty($_POST['login']) && !empty($_POST['pass'])){

$icz = $_POST['login'];
$pass = $_POST['pass'];

    $iczexist = querySingle($conn, "SELECT COUNT(*) FROM dbo.SAMPLEEQ_users WHERE icz='$icz'");
        if(!$iczexist){     $loginForm = 'placeholder="ICZ error';	}
        if($iczexist){
			$loginForm = 'value="'.$icz;
            $user = queryOne($conn,"SELECT * FROM dbo.SAMPLEEQ_users WHERE icz='$icz'");
			$password = password_verify($pass, $user['pass']);

                if(!$password){     $passForm = 'Incorrect password';	}
         }

    if($iczexist AND $password){
		if(isset($_POST['stayOnline'])){
			$userToken = bin2hex(random_bytes(16));
			query($conn, "UPDATE dbo.SAMPLEEQ_users SET user_token='$userToken' WHERE icz='$icz'");
			setcookie("user_token", $userToken, time() + (30 * 24 * 60 * 60), "/");
		}
        	$_SESSION['online'] = $user;
        	header("Location: index.php");
        	exit();
    }
} else {	  if(empty($_POST['login'])){	$loginForm = '> ICZ <';	}
		      if(empty($_POST['pass'])) {	$passForm = 'Password error';	}
    }
}
?>
<form id="loginForm" method="post">
	<input id="login" type="text" name="login" <?php echo($loginForm); ?>" maxlength="6" pattern="\d{6}" autocomplete="login" required>
	<input type="password" id="pass" name="pass" minlength="6" maxlength="25" placeholder="<?php echo($passForm); ?>" autocomplete="current-password" required>
<label for="stayOnline">
	<input type="checkbox" id="stayOnline" name="stayOnline">Remember
</label>
<input type="submit" name="loginBtn" value="Login">