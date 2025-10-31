const projectName = document.getElementById('projectName');
const projectPN = document.getElementById('projectPN');
const sampleType = document.getElementById('sampleType');
const department = document.getElementById('department');
const hub = document.getElementById('hub');
// #############################################################################    FILL PROJECT SELECT         <<<<<
if(projectName){
Object.keys(projects).forEach(names => {
	const option = document.createElement("option");
  	option.value = names;
  	option.textContent = names;
  	projectName.appendChild(option);
});
}

if (document.getElementById('notification')) {
    let count = 0;
    let notification = document.getElementById('notification');
    notification.addEventListener('animationiteration', () => {
        count++;
        if (count >= 5) {
            notification.remove();        
        }
    });
}

function pnSelect(x){
	if(!x){
		projectPN.innerHTML = '<option value="" selected disabled>Select PN#</option>';
		return;
	}
	let prjNameVal = projects[x];
	projectPN.textContent = '';
	prjNameVal.forEach(sn => {
	let snOption = document.createElement('option');
	snOption.value = sn;
	snOption.textContent = sn;
	projectPN.appendChild(snOption);
	});
	if(document.getElementById('department')){
		checkPN(projectPN.value);
	}
}

function isTested(x){
	getTable(x);
}

window.addEventListener('load', function(){

    let selectedName = localStorage.getItem('selectedName');
    let selectedPN = localStorage.getItem('selectedPN');
    let selectedDept = localStorage.getItem('selectedDept');
    let selectedType = localStorage.getItem('selectedType');
	let selectedHub = localStorage.getItem('selectedHub');
        if(selectedName){
            projectName.value = selectedName;
            pnSelect(selectedName);
			projectPN.value = selectedPN;
            if(department){	checkPN(selectedPN);    }
        }
        if(department && selectedDept){
                department.value = selectedDept;
                depType(selectedDept);
                hub.value = selectedHub;
			if(selectedType && selectedDept !== 'olbs'){
				sampleType.value = selectedType;
			}
		}
    localStorage.clear();
});


function saveProject(){
    localStorage.setItem('selectedName', projectName.value);
    localStorage.setItem('selectedPN', projectPN.value);
}
// #############################################################################    DEPARTMENT => SAMPLE TYPE   <<<<<
function depType(x){
	if(x === 'olbs'){	sampleType.value = 'G';
						sampleType.disabled = true;
	}	else {			sampleType.disabled = false;	}
}
// #############################################################################    SAVE SELECTED VALUES   <<<<<
function saveDepartment(){
    localStorage.setItem('selectedName', projectName.value);
    localStorage.setItem('selectedPN', projectPN.value);
	localStorage.setItem('selectedHub', hub.value);
    localStorage.setItem('selectedDept', department.value);
	localStorage.setItem('selectedType', sampleType.value);
}
// #############################################################################    ADD PROJECT | PN NOT REMOVE 5 CHAR <<<<<
function formatPN(inpt){
    let fixedPart = '1395K';
    let inptEdit = inpt.value.slice(5).replace(/\D/g, '');
    inpt.value = `${fixedPart}${inptEdit.slice(0, 7)}`;
}
// #############################################################################    ADD SAMPLE CHECK SN# INFOS   <<<<<
function checkSN(x){
    let sn = false;
	const jsOut = document.getElementById('jsOut');
    let xLen = x.length;
    if(xLen > 8){  sn = x;     }
	if(xLen > 12 && xLen < 27){	
        resetSelects('projectName', 'pca', 'pcb', 'department');
		pnSelect();
        crtdt.value = '';
        jsOut.textContent = '';
	}
    if(xLen > 26){  sn = x;    }
if(sn){
    checkFIS(sn).then(result => {
        if(result.project){
		    projectName.value = result.project;
            pnSelect(result.project);
            if (projectName.value === 'GELATO' || projectName.value === 'DAISY') {
                snText.setAttribute('minlength', 10);
            }   else {  snText.setAttribute('minlength', 29);  }
            projectPN.value = result.pn;
            checkPN(result.pn);
            pca.value = result.pca;
            pcb.value = result.pcb;
            crtdt.value = result.crtdt;
        	jsOut.textContent = 'Autodetect OK';
			jsOut.style.color = 'green';
        	getTable(result.pn);
            setTimeout(()=>waitAndReverse(jsOut, '', ''), 3000);
	    }
            else {	resetSelects('projectName', 'pca', 'pcb');
                    crtdt.value = '';
					pnSelect();
					jsOut.textContent = "Autodetect failed";
					jsOut.style.color = 'red';
                    document.getElementById("projectTable").textContent = '';
                    setTimeout(()=>waitAndReverse(jsOut, '', ''), 3000);
            }
    });
}
}
// #############################################################################    REVERSE (ELEMENT, TEXT, COLOR) FN   <<<<<
function waitAndReverse(x, y, z){
    x.textContent = `${y}`;
    x.style.color = `${z}`;   
}

function loadingFis(){
	outFIS.textContent = '';
    if(document.getElementById('snFIS').value !== ''){
	   document.getElementById('loadingFIS').classList.toggle('hidden');
    }   else {  outFIS.textContent = 'Insert SN#';
                setTimeout(()=>waitAndReverse(outFIS, '', ''), 3000);           
        }
}

function enterInfoFIS(event){
if(event.key === 'Enter') {  infoFIS();    }
}
// ###############################################################################################    INFO FIS FOOTER   <<<<<
function infoFIS(){
	loadingFis();
    let snFIS = document.getElementById('snFIS').value.trim();
    let getFIS = document.getElementById('getFIS');
    let outFis = document.getElementById('outFIS');
    checkFIS(snFIS).then(result => {
        if(result.project){
			loadingFis();
		    outFIS.textContent = `${result.project} ${result.pn} | ${result.sn} | Rev.: ${result.pca}/${result.pcb} | ${result.crtdt} | ${result.station}`;
	    }
        if(result.error){	loadingFis();
							outFIS.textContent = 'Info not found';
        }
    });
}
// #############################################################################    RESET FORM SELECTS   <<<<<
function resetSelects(...selectIds){
	selectIds.forEach(id => {
		let sel = document.getElementById(`${id}`);
			if(sel){	sel.selectedIndex = 0;	}
	});
}
// #############################################################################    REMOVE OPTION FROM SELECT   <<<<<
function removeOption(value){
	let option = department.querySelector(`option[value="${value}"]`);
        if(option)	option.remove();
}
// #############################################################################    CHECK PN# INFOS   <<<<<
function checkPN(x){
	let what = x;
	let where = 'pn';
checkDatabase(where, what).then(result => {
		if(result){
			if(result.ict === 1){
				if(!document.querySelector('option[value="ict"]')){
					let ictOpt = document.createElement('option');
					ictOpt.value = 'ict';
					ictOpt.textContent = 'ICT';
					department.appendChild(ictOpt);
				}
			}	else {	removeOption('ict');	}
			if(result.olbs === 1){
				if(!document.querySelector('option[value="olbs"]')){
					let olbsOpt = document.createElement('option');
					olbsOpt.value = 'olbs';
					olbsOpt.textContent = 'OLBS';
					department.appendChild(olbsOpt);
				}
			}	else {	removeOption('olbs');	}
            if(result.aci === 1){
				if(!document.querySelector('option[value="aci"]')){
					let aciOpt = document.createElement('option');
					aciOpt.value = 'aci';
					aciOpt.textContent = 'ACI';
					department.appendChild(aciOpt);
				}
			}	else {	removeOption('aci');	}
		}
	});
}
// #############################################################################    CHECK IF EXIST SN#   <<<<<
function checkIfExist(x){
    const addBtn = document.getElementById('addBtn');
    const descInfo = document.getElementById('descInfo');
        checkExistingSN(x).then(response =>{
            if(response.success){   
                addBtn.setCustomValidity('');
                addBtn.value = '+Add';
            } 
                else {  
                    addBtn.setCustomValidity('SN# already exist');
                    addBtn.value = 'SN# already exist';
                }
        });
}

function replaceSample(x, y){
	let tr = y.closest('tr');
	let replacement = document.getElementById('replacement');
	if(replacement.value === ''){
		replacement.disabled = false;
		replacement.value = x;
		tr.classList.toggle('marked');
	}
	else if(replacement.value === x){
		replacement.value = '';
		replacement.disabled = true;
		tr.classList.toggle('marked');
	}
}

async function checkExistingSN(x){
    x = x.trim();
try {
    const response = await fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `existSN=${encodeURIComponent(x)}`
    });
        if(!response.ok){	throw new Error(`Chyba HTTP: ${response.status}`);	}
	const data = await response.json();
        if(data){	return data;	}
			else{   console.log("Chyba");
            		return null;
        	}
} 	catch(error){	return error;    }
}

async function checkDatabase(x, y){
    y = y.trim();
try {
    const response = await fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `where=${encodeURIComponent(x)}&what=${encodeURIComponent(y)}`
    });
        if(!response.ok){	throw new Error(`Chyba HTTP: ${response.status}`);	}
	const data = await response.json();
        if(data){	return data;	}
			else{   return error; 	}
} 	catch(error){	return error;    }
}

async function checkFIS(x){
    x = x.trim();
try {
    const response = await fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `sn=${encodeURIComponent(x)}`
    });
        if(!response.ok){	throw new Error(`Chyba HTTP: ${response.status}`);	}
	const data = await response.json();
        if(data){	return data;	}
			else{   console.log("Chyba:");
            		return null;
        	}
} 	catch(error){	return error;    }
}

// #############################################################################    CHECK BD FOR REPLACEMENT SAMPLES   <<<<<
function getReplacement(x){
	let y = document.getElementById('projectPN').value;
    fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `replaceTab=${encodeURIComponent(y)}&dept=${encodeURIComponent(x)}`
    })
	.then(response => response.text())
    .then(data => {
		if(data){   document.getElementById("replaceTab").innerHTML = data;	}
			else {	document.getElementById("replaceTab").innerHTML = ''; 		}
    });
}
// #############################################################################    GET PROJECT INFO TABLE   <<<<<
function getTable(x){
    fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `qtyTab=` + encodeURIComponent(x)
    })
	.then(response => response.text())
    .then(data => {
        document.getElementById("projectTable").innerHTML = data;
    });
}
// #############################################################################    GET LAST PNSNC UPDATE   <<<<<
function lastRec(y, x){
    y.classList.add('loading');
    y.disabled = true;
    fetch("./php/checkProject.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `lastRec=` + encodeURIComponent(x)
    })
	.then(response => response.text())
    .then(data => {
        y.innerHTML = data;
        y.classList.remove('loading');
        y.disabled = false;
    });
}
// #############################################################################    CONFIRM FOR TECHNICIAN   <<<<<
function confirmAction() {
    return confirm("Are you sure?");
}
// ######################################################################    CONFIRM FOR ADMIN 9 (PROJECT)   <<<<<
function confirmEdit(event) {
    let actionText = "";
    if(event.submitter) {
        if(event.submitter.name === "checkRevExp") {
            actionText = `This action will change the status to EXPIRED for all expired samples`;
        } else if (event.submitter.name === "markObsolete") {
            actionText = `This action will change the status to OBSOLETE for samples with obsolete PCA/PCB rev.`;
        }
         else if (event.submitter.name === "editProject") {
            actionText = `This action will change project data.`;
         }
         else if (event.submitter.name === "createAccount") {
            actionText = `New account will be created with default password : 12344321. It's recommended to change password after first login.`;
         }
    }
return confirm(`${actionText}. Do you want to continue?`);
}
// ###################################################################################    VERIFY PASSWORDS   <<<<<
function verifyPass(){
let newPass = document.getElementById('new-password');
let confirmPass = document.getElementById('confirm-password');
	if(newPass.value !== confirmPass.value){
		confirmPass.setCustomValidity("Passwords are not same");
	}	else {	confirmPass.setCustomValidity("");	}
}
// ###################################################################################    MOBILE GO TO SAMPLE   <<<<<
function pushToHash(x){
    location.hash = `#${x}`;
}
// ###################################################################################    PARTS INPUT CHANGE    <<<<<
function  engeeq(x, y, z) {
    let refOrPN = document.getElementById('refOrPN');
    refOrPN.setAttribute('minlength', y);
    refOrPN.maxLength = ('maxlength', z);
    refOrPN.setAttribute('name', `${x}`);
}
// ###################################################################################    LOADING ANIMATION     <<<<<
function showLoadingAnimation() {   document.getElementById('loading').classList.remove('hidden');  }
// ###############################################################################   HIDE LOADING ANIMATION     <<<<<
function hideLoadingAnimation() {   document.getElementById('loading').classList.add('hidden');  }
// ###############################################################################              CHANGE RANGE    <<<<<
function changeRange(x) {
    let trgt = document.getElementById(`${x}`);
    let trgtId = trgt.id; 
    let label = document.getElementById(`lbl-${trgtId}`);
    label.innerHTML = `${trgt.value}`;
}