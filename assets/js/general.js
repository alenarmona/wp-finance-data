jQuery(document).ready(function($){
    var chartRatesLabels = [];
    var chartRatesData = [];

    const ctx = document.getElementById('chart-rates').getContext('2d');
    const chartRates = new Chart(ctx, {
        type: 'line',
        options: {
            responsive: false,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,
                }
            },
        },
        data: {
            labels: [],
            datasets: []
        }        
    });

    $('.row-rate td').on('click', function(e){
        e.preventDefault();
        let crossCurrency = $(e.target).data('cross-currency');
        console.log(crossCurrency);
        chartRates.data.datasets.forEach(dataset => {
            dataset.hidden = true;
            if(dataset.label === crossCurrency) {
                dataset.hidden = false;
            }
        });
        chartRates.update();
    });

    loadHistoricalChartData();

/**
 * ====== Functions ====== 
 */

    /**
     * Load chart data based on selected interval
     */
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
                    chartRates.data.datasets = response.data.datasets;                
                    chartRates.update();
                    
                },
                error: function(jqXHR, textStatus, error) {
                    console.log("Error");   
                    console.log(error);
                    err = error.message || error;                                 
                }
            }
        );
    }
});
