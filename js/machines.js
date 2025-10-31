let position = document.getElementById('position');

function showPos(x) {
    let isHidden = position.classList.contains('hidden');
    if (x === 'AOI' || x === 'SPI') {
        if (position.hasAttribute('disabled')) {    position.removeAttribute('disabled');       }
        if (isHidden)                          {    position.classList.remove('hidden');        }
    }   
    else {  
        if (!position.hasAttribute('disabled')) {   position.setAttribute('disabled', 'true');  }
        if (!isHidden)                          {   position.classList.add('hidden');          }
    }
}