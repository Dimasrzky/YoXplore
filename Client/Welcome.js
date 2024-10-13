let text = document.getElementById('text');
let welcome1 = document.getElementById('welcome1')
let welcome2 = document.getElementById('welcome2');
let welcome3 = document.getElementById('welcome3');

window.addEventListener('scroll', () => {
    let value = window.scrollY;

    text.style.marginTop = value * 2.5 + 'px';
    welcome1.style.top = value * -1 + 'px';
    welcome1.style.left = value * 1 + 'px';
    welcome2.style.top = value * -1.5 + 'px';
    welcome2.style.left = value * -1.5 + 'px';
    welcome3.style.top = vluue * -1.5 + 'px';
})

window.addEventListener('scroll', function() {
    let helloHeading = document.querySelector('.sec h2');
    let paragraphs = document.querySelectorAll('.sec p'); 

    let helloHeadingPosition = helloHeading.getBoundingClientRect().top;
    let screenPosition = window.innerHeight / 1.2;

    if (helloHeadingPosition < screenPosition && helloHeadingPosition > 0) {
        helloHeading.classList.add('show-animate');
    } else {
        helloHeading.classList.remove('show-animate');
    }

    paragraphs.forEach(paragraph => {
        let paragraphPosition = paragraph.getBoundingClientRect().top;

        if (paragraphPosition < screenPosition && paragraphPosition > 0) {
            paragraph.classList.add('show-animate');
        } else {
            paragraph.classList.remove('show-animate');
        }
    });
});
