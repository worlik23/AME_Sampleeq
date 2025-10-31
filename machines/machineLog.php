<!DOCTYPE html>
<html lang="en">
<?php require ('../php/dbaccess.php'); ?>
<head>
  <meta charset="utf-8">
<?php
	if(!isMobile()){ echo('<link rel="stylesheet" href="../css/pc.css" type="text/css">');	}
		else {	echo('<link rel="stylesheet" href="../css/mobile.css" type="text/css">');
		}
?>
	<link rel="stylesheet" 					href="../css/style.css" type="text/css">
	<meta name="viewport" 					content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" 				href="../favicon.ico">
	<meta name="msapplication-TileColor" 	content="#e5001f">
	<meta property="og:url" 				content="<?php echo($linkUrl); ?>">
    <meta name="author"        				content="Richard Steiner">
    <meta name="copyright"      			content="Richeeq©2025">
	<meta name="description"    			content="SAMPLES | List of AOI, ICT & OLBS samples">
    <title>Sampleeq</title>
  </head>
<script>const projects = <?php echo($allProjects); ?>;</script>
<body>
<?php

$tableBody = ['Update Date','User Name','Type','Message','Part Code','Ref Id','Matching Count','Previous Value','Current Value'];
$userNames = query($conn, "SELECT icz, username FROM dbo.SAMPLEEQ_users");
$userName = [];
foreach ($userNames as $users) {
    $userName[$users['icz']] = $users['username'];
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
    $file = $_GET['file'];
    echo ('<header><div class="logo">Data</div><button class="close" onClick="window.close();">&times;</button></header><main class="logTab">');
        $content = file_get_contents($file);
}
    if ($content) {
        tabColumns($tableBody, 'allParts');
        $lines = explode("\n", $content);
        $start = count($lines) - 1;
        $end = (count($lines) > 200) ? count($lines) - 200 : 0;
        for ($i = $start; $end <= $i; $i--) {
        $cols = array_filter(explode(',', $lines[$i]), function($val) { return trim($val) !== ''; });
            if (count($cols) < 7) { echo ('<tr class="log1">');    }
            else if (count($cols) < 9) {    echo ('<tr class="log2">');    }
            else {  echo ('<tr class="log">');  }
            foreach ($cols as $key => $col) {
                if ($key === 1 && strlen($col) === 9 && !empty($userName[substr($col, 3,6)])) {
                    echo ('<td>'.$userName[substr($col, 3, 6)].'</td>');
                }
                else if ($key < 9) {
                    echo ('<td>'.$col.'</td>');
                }
            }
            echo ('</tr>');
        }
    }
?>
</main>
</body>