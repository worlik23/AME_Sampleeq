const allRows = document.querySelectorAll('tbody > tr');
const sapSelect = document.getElementById('sapSelect');
const deactBtn = document.getElementById('deactivateBtn');

allRows.forEach(tr => {
    tr.addEventListener('click', function () {
        const tdElements = tr.querySelectorAll('td');
        if (tdElements.length > 5) {
            const tdSn = tdElements[5].textContent.trim();
            if (tdSn) {
                if (!checkSelectedSNs(tdSn)) {
                    tr.classList.add('marked');
                } else {
                    tr.classList.remove('marked');
                }
            }
        }
    });
});

function checkSelectedSNs(x) {
    let exist = false;
    let sapSelectedSN = sapSelect.querySelectorAll('input[type="text"]');
    sapSelectedSN.forEach(sn => {
        if (sn.value === x) {
            sn.remove();
            exist = true;
        }
    });
    if(!exist) {
        let newInp = document.createElement('input');
        newInp.type = "text";
		newInp.readOnly = true;
        newInp.name = "sapSNs[]";
        newInp.value = x;
        sapSelect.appendChild(newInp);
    }
	inpCount();
    return exist;
}

function inpCount(){
	let sapCount = sapSelect.querySelectorAll('input[type="text"]').length;
	if(sapCount === 0){	deactBtn.disabled = true; }
			else {	deactBtn.disabled = false;	}
}
