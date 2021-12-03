jQuery(document).ready(function($){
    const ctx = document.getElementById('chart-rates').getContext('2d');
    const chartRates = new Chart(ctx, {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
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

    //Select currency
    $('.row-rate td').on('click', function(e){
        e.preventDefault();
        let crossCurrency = $(e.target).data('cross-currency');

        $('.row-rate').removeClass('active');
        $(e.target).closest('tr').addClass('active');
        console.log(crossCurrency);
        chartRates.data.datasets.forEach(dataset => {
            dataset.hidden = true;
            if(dataset.label === crossCurrency) {
                dataset.hidden = false;
            }
        });
        chartRates.update();
    });

    //Select interval
    $('.chart-filter li a').on('click', function(e){
        e.preventDefault();

        let selectedInterval = $(e.target).data('interval');

        $('.chart-filter li a').removeClass('active');
        $(e.target).addClass('active');

        loadHistoricalChartData(selectedInterval);
        
    });

    loadHistoricalChartData("1w");

/**
 * ====== Functions ====== 
 */

    /**
     * Load chart data based on selected interval
     */
    function loadHistoricalChartData(selectedInterval) {
        console.log("Loading chart data...");

        $.ajax(
            {
                url: ajaxInfo.ajaxUrl,
                data: {
                    action: ajaxInfo.action,
                    selectedInterval: selectedInterval,
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
