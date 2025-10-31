const sampleTab = document.getElementById('sampleTab');
const cards = document.querySelectorAll('.card');
const hashSelect = document.getElementById('hashSelect');

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if(entry.isIntersecting){
            hashSelect.value = entry.target.id;
        }
    });
    }, { threshold: 0.7, root:sampleTab });
cards.forEach(card => observer.observe(card));