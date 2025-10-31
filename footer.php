</main>
<?php 
if(!isMobile()){
    echo('
        <footer>
            <label for="fisBtn">
        <input type="checkbox" id="fisBtn">FIS
            </label>
        <div id="fis">
        <input type="text" name="sn" id="snFIS" onkeydown="enterInfoFIS(event)">
        <button id="getFIS" onclick="infoFIS()">Get info</button>
        <div id="outFIS"></div>
        <div id="loadingFIS" class="hidden"></div>
        </div>
        </footer>');
}
?>
</body>
</html>
<script src="js/general.js?v=4.6" type="text/javascript" defer></script>
