$(document).ready(function () {        

    // dropdown material select
    $('.mdb-select').materialSelect();
    $('.timepicker').pickatime({});
    $('.datepicker').pickadate({
    	min: 0,
    	format: 'dd-mm-yyyy'
    });
    $('.stepper.initialize-me').mdbStepper();

});

