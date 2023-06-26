
    $(document).ready(function() {
        $('#optimize-button').click(function() {
            // Show the loading spinner
            $('#loading-spinner').show();

            // Send the AJAX request
            $.request('onOptimizePortfolio', {
                success: function(response) {
                    // The request was successful, hide the loading spinner
                    $('#loading-spinner').hide();

                    // Print out the response
                    console.log(response);

                    // Assuming response.result is a JSON string
                    var data;
                    try {
                        data = JSON.parse(response.result);
                    } catch(e) {
                        console.error("Error parsing response:", e);
                        return;
                    }

                    // Create a table to display the data
                    var table = $('<table>');
                    table.append('<tr><th>Stock ID</th><th>Stock Name</th><th>Weight</th></tr>');
                    data.forEach(function(row) {
                        var tr = $('<tr>');
                        tr.append($('<td>').text(row.stock_id));
                        tr.append($('<td>').text(row.stock_name));
                        tr.append($('<td>').text(row.weight));
                        table.append(tr);
                    });

                    $('main').append(table);

                },
                error: function(response) {
                    // There was an error, hide the loading spinner
                    $('#loading-spinner').hide();

                    // Display the error message
                    alert('There was an error: ' + response);
                }
            });
        });
    });