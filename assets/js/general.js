jQuery(document).ready(function($){
    var chartRatesLabels = [];
    var chartRatesData = [];

    const ctx = document.getElementById('chart-rates').getContext('2d');
    const chartRates = new Chart(ctx, {
        type: 'line',
        options: {
            responsive: false,
            maintainAspectRatio: true
        },
        data: {
            labels: chartRatesLabels,
            datasets: [{
                label: 'USD/EUR',
                data: [],
                borderColor: 'rgb(75, 192, 192)'                
                }]
        }        
    });

    $('tr.row-rate').on('click', function(e){
        e.preventDefault();

    });


function loadHistoricalChartData() {
    console.log("Loading chart data...");
    $.ajax(
        {
            url: ajaxInfo.ajaxUrl,
            data: {
                action: ajaxInfo.action,
                selectedInterval: 'last_week',
                nonce: ajaxInfo.nonce
            },
            type: 'POST',
            dataType: 'json',
            beforeSend: function(){
                console.log("Before send");
                //$loading.fadeToggle();
            },
            success: function (response) {
                console.log("Success");
                console.log(response);
                chartRates.data.labels = response.data.labels;
                chartRates.data.datasets[0].data = response.data.data;
                chartRates.update();
                
            },
            error: function(jqXHR, textStatus, error) {
                console.log("Error");   
                console.log(error);
                err = error.message || error;                 
                
                //$loading.fadeToggle();
            }
        }
    );
}

    loadHistoricalChartData();



});
