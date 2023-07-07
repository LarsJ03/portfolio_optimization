$(document).ready(function() {
    let isProcessing = false;

    $('#optimize-button').on('click', function(event) {
    console.log('Button clicked');
    // rest of your code...

        // If isProcessing is true, return and don't proceed.
        if (isProcessing) {
            return;
        }

        isProcessing = true;

        // Show the loading spinner
        $('#loading-spinner').show();

        // Send the AJAX request
        $.request('onOptimizePortfolio', {
            success: function(response) {
                // The request was successful, hide the loading spinner
                $('#loading-spinner').hide();
                console.log('hello')

                // Check if the response is a string
                if (typeof response === 'string') {
                    // Check if the response is valid JSON
                    try {
                        JSON.parse(response);
                       
                        console.info("Response is valid JSON:", JSON.parse(response));
                    } catch(e) {
                        // Response is not valid JSON
                        console.info("Response is a string but not valid JSON:", response);
                    }
                } else {
                    console.info("Response is not a string:", response);
                }

                isProcessing = false;
            },
            error: function(response) {
                // There was an error, hide the loading spinner
                $('#loading-spinner').hide();

                // Display the error message
                alert('There was an error: ' + response);

                isProcessing = false;
            }
        });
    });
});



