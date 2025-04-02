document.addEventListener('DOMContentLoaded', function() {
    // Animation supplémentaire pour le nombre 404
    const errorNumber = document.querySelector('.error-number');

    // Effet de pulsation douce
    setInterval(() => {
        errorNumber.style.transform = 'scale(1.05)';
        setTimeout(() => {
            errorNumber.style.transform = 'scale(1)';
        }, 500);
    }, 3000);

    // Effet au survol de la planète
    const planet = document.querySelector('.planet');
    planet.addEventListener('mouseover', () => {
        planet.style.transform = 'scale(1.2)';
        planet.style.boxShadow = '0 0 30px var(--primary-light)';
    });

    planet.addEventListener('mouseout', () => {
        planet.style.transform = 'scale(1)';
        planet.style.boxShadow = '0 0 20px var(--primary-light)';
    });
});