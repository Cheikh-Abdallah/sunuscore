(function () {
    'use strict';
    if (typeof Chart === 'undefined' || !window.SUNU_STATS) return;
    var S = window.SUNU_STATS;

    var COLORS = {
        primary: '#071A36',
        secondary: '#155EEF',
        green: '#16A34A',
        yellow: '#FACC15',
        red: '#DC2626',
    };

    function el(id) { return document.getElementById(id); }

    if (el('chartResults')) {
        new Chart(el('chartResults'), {
            type: 'doughnut',
            data: {
                labels: S.results.labels,
                datasets: [{
                    data: S.results.values,
                    backgroundColor: [COLORS.secondary, COLORS.primary, COLORS.yellow],
                }],
            },
            options: { plugins: { legend: { position: 'bottom' } } },
        });
    }

    if (el('chartGoals')) {
        new Chart(el('chartGoals'), {
            type: 'bar',
            data: {
                labels: S.goals.labels,
                datasets: [{ label: 'Buts', data: S.goals.values, backgroundColor: COLORS.secondary }],
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    }

    if (el('chartScorers')) {
        new Chart(el('chartScorers'), {
            type: 'bar',
            data: {
                labels: S.scorers.labels,
                datasets: [{ label: 'Buts', data: S.scorers.values, backgroundColor: COLORS.green }],
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }

    if (el('chartCards') && S.cards) {
        new Chart(el('chartCards'), {
            type: 'bar',
            data: {
                labels: S.cards.labels,
                datasets: [
                    { label: 'Jaunes', data: S.cards.yellow, backgroundColor: COLORS.yellow },
                    { label: 'Rouges', data: S.cards.red, backgroundColor: COLORS.red },
                ],
            },
            options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
        });
    }
})();