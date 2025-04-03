document.addEventListener('DOMContentLoaded', function() {
    // Vérification de l'existence des éléments avant de créer les graphiques
    function initChart(ctxId, config) {
        const ctx = document.getElementById(ctxId);
        if (!ctx) {
            console.error(`Élément #${ctxId} non trouvé`);
            return null;
        }
        return new Chart(ctx, config);
    }

    // Compétences - seulement si les données existent
    if (typeof skills_labels !== 'undefined' && typeof skills_data !== 'undefined') {
        initChart('skillsChart', {
            type: 'doughnut',
            data: {
                labels: skills_labels,
                datasets: [{
                    data: skills_data,
                    backgroundColor: [
                        '#AB1BEE', '#D98FFF', '#9D36CC', '#7A2B9E',
                        '#5C1F75', '#3E144D', '#200926'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }

    // Durée des stages
    if (typeof duration_labels !== 'undefined' && typeof duration_data !== 'undefined') {
        initChart('durationChart', {
            type: 'bar',
            data: {
                labels: duration_labels,
                datasets: [{
                    label: 'Nombre de stages',
                    data: duration_data,
                    backgroundColor: '#AB1BEE',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Wishlist
    if (typeof wishlist_labels !== 'undefined' && typeof wishlist_data !== 'undefined') {
        initChart('wishlistChart', {
            type: 'bar', // Changé de 'horizontalBar' (déprécié) à 'bar' standard
            data: {
                labels: wishlist_labels,
                datasets: [{
                    label: 'Nombre de favoris',
                    data: wishlist_data,
                    backgroundColor: '#D98FFF',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y', // Pour avoir des barres horizontales dans Chart.js v3+
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Rémunération
    if (typeof salary_labels !== 'undefined' && typeof salary_data !== 'undefined') {
        initChart('salaryChart', {
            type: 'line',
            data: {
                labels: salary_labels,
                datasets: [{
                    label: 'Nombre de stages',
                    data: salary_data,
                    borderColor: '#9D36CC',
                    backgroundColor: 'rgba(157, 54, 204, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});