function selectPracticeMode(myButton)
{

	// add the loading class to this button
	myButton.addClass('oc-loading');

	let myStepper = myButton.parents('ul.stepper:first');

	// get the form this button belongs to
	let myForm = myButton.parents('form:first');
	let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

	// get this step
	let thisStep = myButton.parents('li.step:first');
	let thisContent = thisStep.find('.collapse');

	// do the request
	myForm.request(MyFunctionToRequest, { 
		success: function(data) {
			// check if this step is valid
			let invalidFeedbackElement = myForm.find('.invalid-feedback');

			if(!data['valid']){
				// find the invalid feedback session and show it
				invalidFeedbackElement.html(data['message']);
				invalidFeedbackElement.show();
			}
			else {
				invalidFeedbackElement.hide();

				// set the title
				thisStep.find('.step-title').attr('data-step-label', data['step-title']);

				// load the next step select options
				let nextStep = thisStep.next();

				// set the pracitce mode input of the next step
				nextStep.find('input[name="practice_mode"]').val(data['mode']);

				let mySelectBox = nextStep.find('.select-placeholder');

				// empty it
				mySelectBox.html('');

				let mySelectHTML = '';
				mySelectHTML += '<select class="mdb-select md-form my-0 mx-1" name="course_id" searchable="Type hier om te zoeken" >';
				mySelectHTML += '<option value="" disabled selected>Selecteer een vak</option>';

				// fill it with the data
				var i;
				let myCourses = data['courses'];

				for(i = 0; i < myCourses.length; i++){
					mySelectHTML += '<option value="' + myCourses[i]['id'] + '">' + myCourses[i]['name'] + ' [' + myCourses[i]['level'] + ']</option>';
				}

				mySelectHTML += '</select>';
				mySelectBox.html(mySelectHTML);

				mySelectBox.find('select').materialSelect();

				// go to step 2
				showPrevNextStep(myButton, true);

				//$('#stepper-practice-session-selector').nextStep();
			}

			myButton.removeClass('oc-loading');
		}
	});
	
	return false;

}

function selectCourse(myButton)
{
	// add the loading class to this button
	myButton.addClass('oc-loading');

	let myStepper = myButton.parents('ul.stepper:first');

	// get the form this button belongs to
	let myForm = myButton.parents('form:first');
	let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

	// get this step
	let thisStep = myButton.parents('li.step:first');
	let thisContent = thisStep.find('.collapse');

	// do the request
	myForm.request(MyFunctionToRequest, { 
		success: function(data) {
			// check if this step is valid
			let invalidFeedbackElement = myForm.find('.invalid-feedback');

			if(!data['valid']){
				// find the invalid feedback session and show it
				invalidFeedbackElement.html(data['message']);
				invalidFeedbackElement.show();
			}
			else {
				invalidFeedbackElement.hide();

				// set the title
				thisStep.find('.step-title').attr('data-step-label', data['step-title']);

				// load the next step select options
				let nextStep = thisStep.next();

				// set the practice mode and course id
				nextStep.find('input[name="practice_mode"]').val(data['mode']);

				// empty the feedback
				let invalidFeedbackElementNextStep = nextStep.find('.invalid-feedback');
				let infoFeedbackElementNextStep = nextStep.find('.info-feedback');

				if(invalidFeedbackElementNextStep){
					invalidFeedbackElementNextStep.html('');
					invalidFeedbackElementNextStep.hide();
				}

				if(infoFeedbackElementNextStep){
					infoFeedbackElementNextStep.html('');
					infoFeedbackElementNextStep.hide();
				}

				let mySelectBox = nextStep.find('.select-placeholder');

				// empty it
				mySelectBox.html('');

				let mySelectHTML = '';
				mySelectHTML += '<select class="mdb-select md-form my-0 mx-1" name="exam_id" searchable="Type hier om te zoeken" >';
				mySelectHTML += '<option value="" disabled selected>Selecteer een examen</option>';

				if(data['requires-licence']){
					$('#requires-licence').show();
					$('#has-explanation-available').hide();
				}
				else {
					$('#requires-licence').hide();
					$('#has-explanation-available').show();
				}

				// fill it with the data
				var i;
				let myExams = data['exams'];

				console.log(data);

				for(i = 0; i < myExams.length; i++){
					mySelectHTML += '<option value="' + myExams[i]['id'] + '"';

					if(myExams[i]['isFinished'] == 0){
						//mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/primary.png" class="rounded-circle"';
					}
					else if(myExams[i]['isFinished'] == 2){
						//mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/primary-light.png" class="rounded-circle"';
					}
					else if(myExams[i]['isFinished'] == -1 ){
						mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/gold.png" class="rounded-circle"';
					}
					else if(myExams[i]['isFinished'] == 1){
						mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/done.png" class="rounded-circle"';
					}
					else if(myExams[i]['isFinished'] == 3){
						mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/purple.png" class="rounded-circle"';
					}
					else if(myExams[i]['isFinished'] == 4){
						mySelectHTML += ' data-icon="/themes/examify/assets/img/icons/purple-done-half.png" class="rounded-circle"';
					}
					
					mySelectHTML += '>' + myExams[i]['year'] + ' / Tijdvak ' + myExams[i]['tijdvak'] + '</option>';
					
				}

				mySelectHTML += '</select>';
				mySelectBox.html(mySelectHTML);

				mySelectBox.find('select').materialSelect();

				// go to step 2
				showPrevNextStep(myButton, true);

				//$('#stepper-practice-session-selector').nextStep();
			}

			myButton.removeClass('oc-loading');
		}
	});
	
	return false;
}

function selectExam(myButton)
{
	// add the loading class to this button
	myButton.addClass('oc-loading');

	let myStepper = myButton.parents('ul.stepper:first');

	// get the form this button belongs to
	let myForm = myButton.parents('form:first');
	let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

	// get this step
	let thisStep = myButton.parents('li.step:first');
	let thisContent = thisStep.find('.collapse');

	// get the nextStep to store the input attributes
	let nextStep = thisStep.next();

	// do the request
	myForm.request(MyFunctionToRequest, { 
		success: function(data) {
			// check if this step is valid
			let invalidFeedbackElement = myForm.find('.invalid-feedback');
			let infoFeedbackElement = myForm.find('.info-feedback');

			myForm.find('select').change( function() {
				// clear the feedback elements
				invalidFeedbackElement.html('');
				invalidFeedbackElement.hide();
				infoFeedbackElement.html('');
				infoFeedbackElement.hide();
			});

			if(!data['valid']){
				// find the invalid feedback session and show it
				if(data['message'])
				{
					invalidFeedbackElement.html(data['message']);
					invalidFeedbackElement.show();
				}

				if(data['message-info'])
				{
					infoFeedbackElement.html(data['message-info']);
					infoFeedbackElement.show();
				}

			}
			else {
				invalidFeedbackElement.html('');
				infoFeedbackElement.hide();

				// set the title
				thisStep.find('.step-title').attr('data-step-label', data['step-title']);

				// set automatically also this as suggestion for the practice-session-name
				nextStep.find('input[name="practice-session-name"]').val(data['coursename'] + ' / ' + data['step-title']);
				nextStep.find('label').addClass('active');

				// set the input values
				nextStep.find('input[name="practice_mode"]').val(data['mode']);
				nextStep.find('input[name="exam_id"]').val(data['examid']);


				// show the next step
				showPrevNextStep(myButton, true);

				// now hide the info feedback element since otherwise the DOM cannot find the button anymore
				infoFeedbackElement.html('');
				invalidFeedbackElement.hide();

			}

			myButton.removeClass('oc-loading');
		}
	});
	
	return false;
}

function startSession(myButton)
{
	// add the loading class to this button
	myButton.addClass('oc-loading');

	let myStepper = myButton.parents('ul.stepper:first');

	// get the form this button belongs to
	let myForm = myButton.parents('form:first');
	let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

	// get this step
	let thisStep = myButton.parents('li.step:first');

	// get the nextStep to store the input attributes
	let nextStep = thisStep.next();

	// do the request
	myForm.request(MyFunctionToRequest, { 
		success: function(data) {
			// check if this step is valid
			let invalidFeedbackElement = myForm.find('.invalid-feedback');
			let infoFeedbackElement = myForm.find('.info-feedback');

			if(!data['valid']){
				// find the invalid feedback session and show it
				if(data['message'])
				{
					invalidFeedbackElement.html(data['message']);
					invalidFeedbackElement.show();
				}

				if(data['message-info'])
				{
					infoFeedbackElement.html(data['message-info']);
					infoFeedbackElement.show();
				}

			}
			else {
				invalidFeedbackElement.html('');
				infoFeedbackElement.hide();
				infoFeedbackElement.html('');
				invalidFeedbackElement.hide();

				window.location.replace(data['session-link']);

			}

			myButton.removeClass('oc-loading');
		}
	});
	
	return false;
}

function showPrevNextStep(myButton, nextIsTrue)
{
	let myStepper = myButton.parents('ul.stepper:first');
	let thisStep = myButton.parents('li.step:first');
	let thisStepNr = myStepper.find('li.step').index(thisStep) + 1; // + 1 since it has index 0 as first

	// show the previous step
	if(nextIsTrue){
		showStep(myStepper, thisStepNr + 1);
	}
	else {

		// first hide the warning messages of the current one
		let myForm = myButton.parents('form:first');
		
		// check if this step is valid
		let invalidFeedbackElement = myForm.find('.invalid-feedback');
		let infoFeedbackElement = myForm.find('.info-feedback');
		invalidFeedbackElement.html('');
		invalidFeedbackElement.hide();
		infoFeedbackElement.html('');
		infoFeedbackElement.hide();

		showStep(myStepper, thisStepNr - 1);
	}

	return false;
}

function showStep(myStepper, step_nr)
{

	// reduce the step nr by 1 since the index starts at 0
	step_nr = step_nr - 1;

	// threat it as a selector
	if(typeof myStepper == "string"){
		myStepper = $(myStepper);
	}

	let mySteps = myStepper.find('li.step');
	let myStep = mySteps.eq(step_nr);
	let myLargerSteps = myStepper.find('li.step:gt(' + step_nr + ')');
	let mySmallerSteps = myStepper.find('li.step:lt(' + step_nr + ')');
	let myOtherSteps = $.merge(myLargerSteps, mySmallerSteps);

	// update the step title attribute to empty
	myStep.find('.step-title').attr('data-step-label', '');

	// remove the 'done' from all steps greather than
	mySteps.removeClass('done active');
	mySmallerSteps.addClass('done');
	mySteps.eq(step_nr).addClass('active');

	// hide all content
	myOtherSteps.find('.collapse').collapse('hide');
	myStep.find('.collapse').collapse('show');

}