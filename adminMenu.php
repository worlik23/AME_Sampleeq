<?php
/*  if($adminLevel === 'sap' OR $adminLevel === '9'){   echo('<a href="sap.php" class="sap"><span>Write off</span></a>');   } */
if(!isMobile()){
    if (in_array($adminLevel, $engTech) && $adminLevel !== 'sap'){
        echo('<a href="multiEdit.php" class="edit"><span>Edit more</span></a>');    
    }
    if(in_array($adminLevel, $engGroup)){
        echo('<a href="print.php" class="labels"><span>Print</span></a>');
        echo('<a href="addSample.php" class="add"><span>Add</span></a>');
        echo('<a href="update_bom.php" class="bom"><span>Upload</span></a>');
        echo('<a href="powerShell.php" class="ssh"><span>SSH</span></a>');
    }
    if($adminLevel === '9'){
	   echo('<a href="editProject.php" class="prjEdit"><span>Edit Project</span></a>');
    }
}
        echo('<div class="userName">'.$userOn['username'].'</div>');
?>
<a href="settings.php" class="settings"><span>Settings</span></a>
<form id="loginForm" method="post"><input type="submit" class="off" title="Logout" name="logout" value="">