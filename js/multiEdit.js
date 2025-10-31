const batchForm = document.getElementById('batchForm');
const batchList = document.getElementById('batchList');
const batch = document.getElementById('batch');
const nextBtn = document.getElementById('nextBtn');
const editAll = document.getElementById('editAll');

function backToOrigin(){
    nextBtn.textContent = '+Add to batch';
    nextBtn.style.fontWeight = '';
}

function checkBatchList() {
    if (batchList.children.length > 0) {
        continueToEdit.classList.remove('hidden');
    }   else {  
            if (!continueToEdit.classList.contains('hidden')) {
                continueToEdit.classList.add('hidden');
            }
        }
}

nextBtn.addEventListener('click', () => {
    if (batch.value.length >= 9) {    crtNxtInpt(batch.value);   }
        else {  nextBtn.textContent = 'SN# is too short';
                nextBtn.style.fontWeight = 'bold';
                setTimeout(()=>backToOrigin(x), 2500);
        }
});

batch.addEventListener('input', () => {
    if (batch.value.length == 29)   {   crtNxtInpt(batch.value);    }
});

batch.addEventListener('keydown', (event) => {
    let x = batch.value;
    if (event.key === 'Enter'){
        if(batch.value.length >= 9) {   crtNxtInpt(batch.value);   }
        else {  nextBtn.textContent = 'SN# is too short';
                nextBtn.style.fontWeight = 'bold';
                setTimeout(()=>backToOrigin(x), 2500);
        }
    }
});

function crtNxtInpt(x){
    x = x.trim();
    checkExistingSN(x).then(response =>{
        if(response.error){     crtBatchRow(response.error);     }
            else {  nextBtn.textContent = 'SN# not found';
                    nextBtn.style.fontWeight = 'bold';
                    setTimeout(()=>backToOrigin(x), 1000);
            }
    });
}

function crtBatchRow(x){
    let row = document.createElement('div');
    row.classList.add('row');
    let dltBtn = document.createElement('button');
    dltBtn.onclick = function(){    row.remove();    };
    dltBtn.innerHTML = '&times;';
    let nxtInput = document.createElement('input');
    nxtInput.type = 'text';
    nxtInput.name = 'batches[]';
    nxtInput.value = `${x}`;
    row.appendChild(nxtInput);
    row.appendChild(dltBtn);
    batchList.appendChild(row);
    batch.value = '';
    batch.focus();
    checkBatchList();
}
