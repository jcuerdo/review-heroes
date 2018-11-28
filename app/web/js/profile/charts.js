var user = window.location.href.substring(window.location.href.lastIndexOf('/') + 1);

$.get('/profile/' + user + '/participation-stats', function( data ) {
    new Chart(document.getElementById('participationEvolutionChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: data.date,
            datasets: [{
                label: 'participations',
                data: data.count,
                backgroundColor: "rgba(51,102,255,0.4)"
            }]
        }
    });
});

$.get('/profile/' + user + '/build-stats', function( data ) {
    new Chart(document.getElementById('buildsEvolutionChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: data.date,
            datasets: [
                {
                    label: 'Failure',
                    data: data.build_failure,
                    backgroundColor: "rgba(204,0,0,0.4)"
                },
                {
                    label: 'Success',
                    data: data.build_success,
                    backgroundColor: "rgba(102,204,0,0.4)"
                }
            ]
        }
    });
});