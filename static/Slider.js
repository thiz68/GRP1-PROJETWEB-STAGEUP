const slider = document.getElementById('salaire_min');
const valueDisplay = document.getElementById('sliderValue');

valueDisplay.textContent = slider.value + ' €';

slider.addEventListener('input', () => {
    valueDisplay.textContent = slider.value + ' €';
});