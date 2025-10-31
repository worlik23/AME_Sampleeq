<!DOCTYPE html>
<html lang="en">
<?php
session_start();
require('php/dbaccess.php');

if(isset($_POST['logout'])){
	session_unset();
	session_destroy();
	setcookie("user_token", "", time() - 3600, "/");
    header("Location: index.php");
    exit();
}

$userOn = AdminOn($conn);

if ($userOn){    
    if (empty($_SESSION['online']['admin'])) {
        $adminLevel = adminLevel($conn, $userOn['icz']);    
    }   else {  $adminLevel = $_SESSION['online']['admin']; }
}
   else    {   $adminLevel = false;    }

$linkUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$page = substr($_SERVER['REQUEST_URI'], 1);
?>
<head>
  <meta charset="utf-8">
<?php
	if(!isMobile()){ echo('<link rel="stylesheet" href="./css/pc.css?v=4.6" type="text/css">');	}
		else {	echo('<link rel="stylesheet" href="./css/mobile.css?v=4.0" type="text/css">');
				require('./php/Mdbaccess.php');
		}
?>
	<link rel="stylesheet"     href="./css/style.css?v=4.6"   type="text/css">
	<meta name="viewport"      content="width=device-width, initial-scale=1">
	<link rel="icon"           type="image/png"         href="./favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon"           type="image/svg+xml"     href="./favicons/favicon.svg" />
    <link rel="shortcut icon"  href="./favicons/favicon.ico" />
    <link rel="apple-touch-icon"    sizes="180x180"     href="./favicons/apple-touch-icon.png" />
    <link rel="manifest"       href="./favicons/site.webmanifest" />
    <meta name="apple-mobile-web-app-title"             content="SAMPLEEQ" />
	<meta property="og:url"     content="<?php echo($linkUrl); ?>">
    <meta property="og:locale"  content="en_EN">
    <meta property="og:image"   content="https://www.richeeq.eu/SEWebApp/SEWebApp_192x192.png">
    <meta property="og:type"    content="website">
    <meta property="og:title"   content="SAMPLEEQ & ENGEEQ">
    <meta property="og:description"    content="Sample Evidence Query & Engineering toolset">
    <meta name="author"         content="Richard Steiner">
    <meta name="copyright"      content="Richeeq©2025">
	<meta name="description"    content="SAMPLES | List of AOI, ICT & OLBS samples">
    <title>Sampleeq</title>
  </head>
<script>const projects = <?php echo($allProjects); ?>;</script>
<body>
<?php if(isMobile()){   echo('<label for="adminBtn"><input type="checkbox" id="adminBtn"></label>'); } ?>
<header>
<?php
if($userOn)		{	include('adminMenu.php');	}
		else 	{	include('./php/login.php');	}
?>
</form>
<?php
if (isMobile()) {
    echo ('<a class="navBottom" href="https://teams.microsoft.com/l/chat/0/0?users=steiner.richard@inventec.com" target="_blank" title="Support"><span>Help | Chat</span></a>');
}
?>
</header>
<?php if(!isMobile()){ echo('<nav>'); } ?>
<div id="logo" class="logo">SAMLEEQ</div>
<?php if(isMobile()){   echo('<label for="menuBtn"><input type="checkbox" id="menuBtn"></label><nav>'); } ?>
<!-- PŘÍPRAVA PRO NOVINKY ##################<a href="notify.php" rel="follow">News | Notes</a>-->
<a href="index.php" rel="follow">Search</a>
<a href="project.php" rel="follow">Project info</a>
<?php   
    if(!isMobile()){    
        echo('<a href="projects.php">Project list</a>');    
    }
if ($userOn) {
    if (in_array($adminLevel, $engTech)){  
    echo('<hr class="white">');
    echo('<span class="logo">ENGEEQ</span>');
    echo('<a href="parts.php">PARTEEQ</a>');
    echo('<a href="pnsnc.php">PANASONEEQ</a>');
}
if (in_array($adminLevel, $engGroup) || $adminLevel === 'sap'){
    echo ('<a href="machines.php">Machines</a>');
}
if (in_array($adminLevel, ['aoi', '9'])){
    echo('<hr class="white">');
    echo('<span class="logo">AOI</span>');
    echo('<a href="cad.php">CAD|AXI</a>');
}
    echo('<hr class="white">');
    echo('<a href="files.php">Download</a>');
}
if (!isMobile()) {  
    echo ('<a class="navBottom" href="https://teams.microsoft.com/l/chat/0/0?users=steiner.richard@inventec.com" target="_blank" title="Support"><span>Help | chat</span></a>');  
}
?>
</nav>
<main>
<div id="loading" class="hidden"><div id="spinner" class="loading-spinner"></div></div>