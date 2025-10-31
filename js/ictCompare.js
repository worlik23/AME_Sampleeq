let line = document.getElementById('lineSelect');
let prevBtn = document.getElementById('prevBtn');
let nextBtn = document.getElementById('nextBtn');
if (line) {
    let showLine = line.options[line.selectedIndex].value;
    getOnChange(showLine);
}

function prevLine() {
    if (line.selectedIndex > 0) {
        line.selectedIndex--;
        line.dispatchEvent(new Event('change'));
    }
}

function nextLine() {
    if (line.selectedIndex < line.options.length - 1) {
        line.selectedIndex++;
        line.dispatchEvent(new Event('change'));
    }
}

function checkLineBtn(x) {
    if (x === 0) {  prevBtn.disabled = true;    }
        else {      prevBtn.disabled = false;   }
    if (x < line.options.length - 1) {  nextBtn.disabled = false;    }
        else {  nextBtn.disabled = true;    }
}

function getOnChange(a) {
    let or = document.getElementById(`orig${a}`);
    let ed = document.getElementById(`edit${a}`);
    if (or) {   or.scrollIntoView();    }
    if (ed) {   ed.scrollIntoView();    }
    checkLineBtn(line.selectedIndex);
}