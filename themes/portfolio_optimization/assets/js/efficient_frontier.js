// Generate some random data for the efficient frontier
let efficientFrontierData = [];
for (let i = 0; i < 100; i++) {
  efficientFrontierData.push({x: Math.random(), y: Math.random()});
}

// Set the coordinates for the portfolio
let portfolioLocation = {x: 0.5, y: 0.8};

// Create the chart
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Efficient Frontier',
            data: efficientFrontierData,
            backgroundColor: 'rgba(0, 0, 255, 0.5)',
            borderColor: 'rgba(0, 0, 255, 0.5)',
            showLine: true,
            fill: false
        }, {
            label: 'Portfolio Location',
            data: [portfolioLocation],
            backgroundColor: 'rgba(255, 0, 0, 0.5)',
            borderColor: 'rgba(255, 0, 0, 0.5)'
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Volatility'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Expected Return'
                }
            }
        }
    }
});