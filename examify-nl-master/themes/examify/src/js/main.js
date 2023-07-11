// load pagination
import ExamifyPagination from './exam-pagination';

// add the autoHeight function to a textarea
jQuery.fn.extend({
  autoHeight: function () {
    function autoHeight_(element) {
      return jQuery(element)
        .css({ 'height': 'auto', 'overflow-y': 'hidden' })
        .height(element.scrollHeight);
    }
    return this.each(function() {
      autoHeight_(this).on('input', function() {
        autoHeight_(this);
      });
    });
  }
});

window.ExamifyPagination = ExamifyPagination;

function iniPagination()
{
  let myExamifyPagination = new ExamifyPagination('.pagination-placeholder', '.my-examify-pagination-ul', '#toggle-pagination-mode', true, true);
}

window.iniPagination = iniPagination;

function updateNewElement(thiselement, value)
{
  thiselement.html(value).promise().done(function(){
    // call the mdboostrap
  // dropdown material select
  thiselement.find('.mdb-select').materialSelect();
  thiselement.find('.timepicker').pickatime({});
  thiselement.find('.datepicker').pickadate({
    min: 0,
    format: 'dd-mm-yyyy'
  });
  thiselement.find('.stepper.initialize-me').mdbStepper();

  iniFormValidation(thiselement);
  iniSelectAll(thiselement);
  alignFooter();
  });
}

window.updateNewElement = updateNewElement;

function controlModal(anchor, value)
{
  console.log('modal-called');
  $(anchor).modal(value);
}

window.controlModal = controlModal;

function customAjaxClick(myButton, extraData = [])
{

  // add the loading class to this button
  myButton.addClass('oc-loading');

  // get the form this button belongs to
  let myForm = myButton.parents('form:first');
  let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

  // do the request
  myForm.request(MyFunctionToRequest, { 
    data: { 'extraData' : JSON.stringify(extraData) },
    files: true,
    success: function(data) {

      console.log(data);
      
      // check if this step is valid
      let invalidFeedbackElement = myForm.find('.invalid-feedback');
      let infoFeedbackElement = myForm.find('.info-feedback');
      let successElement = myForm.find('.success-feedback');

      if(!data['valid']){
        // find the invalid feedback session and show it
        successElement.hide();
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
        invalidFeedbackElement.hide();
        infoFeedbackElement.hide();
        successElement.show();
      }

      // update based on feedback
      if(data['updateWithinForm']){
        $.each(data['updateWithinForm'], function(index, value){
          updateNewElement(myForm.find(index), value);
        });
      }

      if(data['updateElement']){
        $.each(data['updateElement'], function(index, value){
          updateNewElement($(index), value);
        });
      }

      if(data['showElement']){
        $.each(data['showElement'], function(index, value){
          $(value).show();
          $(value).removeClass('d-none');
        });
      }

      if(data['hideElement']){
        $.each(data['hideElement'], function(index, value){
          $(value).hide();
        });
      }

      if(data['removeClass']){
        $.each(data['removeClass'], function(index, value){
          $(index).removeClass(value);
        });
      }

      if(data['addClass']){
        $.each(data['addClass'], function(index, value){
          $(index).addClass(value);
        });
      }


      if(data['hideWithinForm']){
        $.each(data['hideWithinForm'], function(index, value){
          myForm.find(value).hide();
        });
      }

      if(data['showWithinForm']){
        $.each(data['showWithinForm'], function(index, value){
          myForm.find(value).show();
        });
      }

      myButton.removeClass('oc-loading');

      if(data['redirect']){
        if(data['redirect']['after']){
          var delay = data['redirect']['after'];
          setTimeout(function() {
            window.location.replace(data['redirect']['location']);
          }, delay);
        }
        else {
          window.location.replace(data['redirect']['location']);
        }
      }

      // scroll to element of needed
      if(data['scrollToElement']){
        let target = $(data['scrollToElement']);
        $('html, body').animate({
          scrollTop: target.offset().top - $('.fixed-top').height() - parseInt($('.fixed-top').css('margin-bottom'))
        }, 1000);
      }

      // check if a function needs to be called
      if(data['call-js-function']){

        // loop over the function calls
        $.each(data['call-js-function'], function(index, value){
          if(!value){
            window[index]();
          }
          else {
            window[index](...value);
          }
        });
      }

    }
  });
  
  return false;

}

window.customAjaxClick = customAjaxClick;

function scrollToElement(target)
{
  $('html, body').animate({
    scrollTop: target.offset().top - $('.fixed-top').height() - parseInt($('.fixed-top').css('margin-bottom'))
  }, 1000);
}

window.scrollToElement = scrollToElement;

function collapseHideBySelector(mySelector)
{
  $(mySelector).collapse('hide');
}
function collapseShowBySelector(mySelector)
{
  $(mySelector).collapse('show');
}
function collapseToggleBySelector(mySelector)
{
  $(mySelector).collapse('toggle');
}
function scrollToSelector(mySelector)
{
  let target = $(mySelector);
  $('html, body').animate({
    scrollTop: target.offset().top - $('.dummy-box').height() - parseInt($('.dummy-box').css('margin-bottom'))
  }, 1000);
}

function showPhase(phase, replaceContent)
{

  // find open items of the same phase
  let MySelector = '#order-phase-' + phase;
  let ItemToOpen = $(MySelector);

  // check if it is already open 
  let alreadyOpen = ItemToOpen.hasClass('show');

  // after showing, scroll to it
  ItemToOpen.on('shown.bs.collapse', function() {
    scrollToSelector(MySelector);
    ItemToOpen.off('shown.bs.collapse');
  });

  // wrap the content in a collapse and make space for another order-phase
  let nextPhase = parseInt(phase) + 1;
  let MyContent = replaceContent + '<div class="collapse" id="order-phase-' + nextPhase + '" ></div>';

  if(alreadyOpen)
  {
    ItemToOpen.on('hidden.bs.collapse', function() {

      updateNewElement(ItemToOpen, MyContent);
      ItemToOpen.collapse('show');
      ItemToOpen.off('hidden.bs.collapse');
    });

    ItemToOpen.collapse('hide');
  }
  else {
    console.log('show it');
    updateNewElement(ItemToOpen, MyContent);
    ItemToOpen.collapse('show');
  }
}

window.collapseHideBySelector = collapseHideBySelector;
window.collapseShowBySelector = collapseShowBySelector;
window.collapseToggleBySelector = collapseToggleBySelector;
window.scrollToSelector = scrollToSelector;
window.showPhase = showPhase;

function iniSelectAll(thiselement = null)
{
  var mybuttons;

  if(thiselement){
    mybuttons = thiselement.find('.btn.checkbox-select-all');
  }
  else {
    mybuttons = $('.btn.checkbox-select-all');
  }

  if(!mybuttons){ return; }

  mybuttons.each(function() {
    $(this).click(function(){
      var mycheckboxes = $(this).parent().parent().find('input[type="checkbox"]');
      if(mycheckboxes.is(":checked")){
        mycheckboxes.prop('checked',false);
      }
      else {
        mycheckboxes.prop('checked',true);
      }
    });
  });
}

window.iniSelectAll = iniSelectAll;

function iniFormValidation(thiselement = null)
{

  var thisform;

  // overwrite tthe submit function
  if(thiselement){
    thisform = thiselement.find('form.form-validation-js');
  }
  else {
    thisform = $('form.form-validation-js');
  }

  if(!thisform){
    return;
  }

  thisform.submit(function (){

    // prevent the standard page refresh
    event.preventDefault();

    // remove all is-valid and is-invalid from this form
    $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');

    // get the data-request-function attribute
    let MyFunctionToRequest = $(this).find('input[name="_handler"]').val();

    // define the form that is submitted
    let myForm = $(this);

    // add the is-updated-by-user class to all inputs
    myForm.find('input, select').addClass('is-updated-by-user');

    // show the loader 
    $.oc.stripeLoadIndicator.show();

    // do the validation
    myForm.request(MyFunctionToRequest, { 
      success: function(data) {

        console.log(data);

        // in case it is not all valid, show it
        if(!data['allvalid'] && !data['valid']){
          updateFormValidation(myForm, data);

          if(data['redirect']){
            if(data['redirect']['after']){
              var delay = data['redirect']['after'];
              setTimeout(function() {
                window.location.replace(data['redirect']['location']);
              }, delay);
            }
            else {
              window.location.replace(data['redirect']['location']);
            }
          }
        }
        else {
          if(data['update_form']){
            updateFormValidation(myForm, data);
          }
          if(data['update']){

            // loop over the index and update it with the content
            $.each(data['update'], function(index, value){
              updateNewElement($(index), value);
            });

          }
          if(data['updateElement']){

            // loop over the index and update it with the content
            $.each(data['updateElement'], function(index, value){
              updateNewElement($(index), value);
            });

          }

          // check if a function needs to be called
          if(data['call-js-function']){

            // loop over the function calls
            $.each(data['call-js-function'], function(index, value){
              if(!value){
                window[index]();
              }
              else {
                window[index](...value);
              }
            });
          }
      
          if(data['redirect']){
            if(data['redirect']['after']){
              var delay = data['redirect']['after'];
              setTimeout(function() {
                window.location.replace(data['redirect']['location']);
              }, delay);
            }
            else {
              window.location.replace(data['redirect']['location']);
            }
          }
        }
      }
    });

    // show the loader 
    $.oc.stripeLoadIndicator.hide();

  });

  // add the submit function to the inputs
  $('form.form-validation-js .on-change-submit').on('change', function () {
    $(this).addClass('is-updated-by-user');
    validateMyForm($(this).closest('form'));
  });
}

window.iniFormValidation = iniFormValidation;

function validateMyForm(myForm) {
  
  // show the loader 
  $.oc.stripeLoadIndicator.show();

  // perform the ajax request
  myForm.request('onValidate', {success: function(data) {
    updateFormValidation(myForm, data);
  }});

  // hide the loader
  $.oc.stripeLoadIndicator.hide();

}

window.validateMyForm = validateMyForm;

function updateFormValidation(myForm, data){

    // loop over the input validation entries
    $.each(data['inputvalidation'], function( index, value ) {

      // get the input element
      let myInput = myForm.find('[name="' + index + '"].is-updated-by-user');

      // continue if it is not yet updated by the user
      if(myInput.length == 0){
        return;
      }

      // incase myInput is a select element, switch to the parent, which is the wrapper
      if(myInput.is('select')){
        myInput = myInput.parent();
        let parentInput = myInput.parent().find('input');
        parentInput.toggleClass('is-valid', value['valid']);
        parentInput.toggleClass('is-invalid', !value['valid']);
      }

      // change the text
      if(value['message']){
        myInput.closest('.md-form').find('.invalid-feedback, .valid-feedback').html(value['message']);
      }

      // toggle the status  
      myInput.toggleClass('is-valid', value['valid']);
      myInput.toggleClass('is-invalid', !value['valid']);


    });
}

window.updateFormValidation = updateFormValidation;

function iniExamifyChart(mySelector, myForm, extraData = []){
  var ctx = $(mySelector).get(0);
  var ctxB = ctx.getContext('2d');

  // get the data
  let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();

  // do the request
  myForm.request(MyFunctionToRequest, { 
    data: { 'extraData' : extraData },
    success: function(data) {

      if(!data['valid']){

        if(data['hideElement']){
          $.each(data['hideElement'], function(index, value){
            $(value).hide();
          });
        }

        if(data['showElement']){
          $.each(data['showElement'], function(index, value){
            $(value).show();
            $(value).removeClass('d-none');
          });
        }

        console.log(data);
        return;
      }

      console.log(data);

      // set the height if defined
      if(data['chartHeight']){
        $(ctx).parent().css('height', data['chartHeight']);
      }

      if(!data['chartDelay']){
        data['chartDelay'] = 0;
      }

      setTimeout( function() {
        var myBarChart = new Chart(ctxB, data['chartSetup']);
      }, data['chartDelay']);

      if(data['hideElement']){
        $.each(data['hideElement'], function(index, value){
          $(value).hide();
        });
      }

      if(data['showElement']){
        $.each(data['showElement'], function(index, value){
          $(value).show();
          $(value).removeClass('d-none');
        });
      }

    }
  });

}

window.iniExamifyChart = iniExamifyChart;

function iniAllChartForms()
{

    // find all chart forms (which are forms with input hidden name chart_form) and initialize the chars
    let myInputs = $('input[name="chart_form"]');

    if(!myInputs){
      return;
    }
    
    // loop over the inputs and initialize the charts
    $.each(myInputs, function(index, value) {

      // find the other input handler
      let myForm = $(value).parents('form:first');
      let mySelector = $(value).val();

      iniExamifyChart(mySelector, myForm);

    });

}

window.iniAllChartForms = iniAllChartForms;

function iniAllDataForms()
{

    // find all chart forms (which are forms with input hidden name chart_form) and initialize the chars
    let myInputs = $('input[name="data_form"]');

    if(!myInputs){
      return;
    }
    
    // loop over the inputs and initialize the charts
    $.each(myInputs, function(index, value) {

      customAjaxClick($(value));

    });

}

window.iniAllDataForms = iniAllDataForms;

function iniExamifProgressBar(mySelector, myForm, extraData = []){

  // get the data
  let MyFunctionToRequest = myForm.find('input[name="_handler"]').val();
  let MyFormat = myForm.find('input[name="format"]').val();

  // do the request
  myForm.request(MyFunctionToRequest, { 
    data: { 'extraData' : extraData },
    success: function(data) {

      if(!data['valid']){

        if(data['hideElement']){
          $.each(data['hideElement'], function(index, value){
            $(value).hide();
          });
        }

        if(data['showElement']){
          $.each(data['showElement'], function(index, value){
            $(value).show();
            $(value).removeClass('d-none');
          });
        }

        console.log(data);
        return;
      }

      console.log(data);

      // update the step attribute in data

      switch(MyFormat){
        case 'Line':
          var bar = new ProgressBar.Line(mySelector, data['chartSetup']);
          break;
        case 'Circle':
          // update the step attribute based on percentage or points
          switch(data['chartUnit']){
            case 'percentage':
              data['chartSetup']['step'] = function(state, circle) {
                circle.path.setAttribute('stroke', state.color);
                circle.path.setAttribute('stroke-width', state.width);

                var num = circle.value() * data['chartMax'] + Number.EPSILON;
                var value = num.toFixed(data['chartNdecimals']);  
                
                if (value === 0) {
                  circle.setText('');
                } else {
                  circle.setText(value + '%');
                }
              };
              break;
            case 'points':
              data['chartSetup']['step'] = function(state, circle) {
                circle.path.setAttribute('stroke', state.color);
                circle.path.setAttribute('stroke-width', state.width);

                var num = circle.value() * data['chartMax'] + Number.EPSILON;
                var value = num.toFixed(data['chartNdecimals']);  
                if (value === 0) {
                  circle.setText('');
                } else {
                  circle.setText(value);
                }
              };
              break;
            default: 
              console.log('Chart unit not recognized: ' + data['chartUnit']);
          }

          //Object.defineProperty(data['chartSetup']['step'], "name", { value: "step" });

          console.log(data);

          break;
        default: 
          console.log('This progress bar format is not defined: ' + MyFormat);
          return;

        if(data['hideElement']){
          $.each(data['hideElement'], function(index, value){
            $(value).hide();
          });
        }

        if(data['showElement']){
          $.each(data['showElement'], function(index, value){
            $(value).show();
            $(value).removeClass('d-none');
          });
        }
      }

      if(!data['chartDelay']){
            data['chartDelay'] = 0;
          }

      var bar;
      
      bar = new ProgressBar.Circle(mySelector, data['chartSetup']);
      bar.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
      bar.text.style.fontSize = '2rem';
      $(bar.text).html('');

      setTimeout( function() {

        if(data['chartOptions']){
          bar.animate(data['value'], data['chartOptions']);
        }
        else {
          bar.animate(data['value']);
        }

      }, data['chartDelay']);
      
    }
  });

}

window.iniExamifProgressBar = iniExamifProgressBar;

function iniAllProgressBarForms()
{

    // find all chart forms (which are forms with input hidden name chart_form) and initialize the chars
    let myInputs = $('input[name="progressbar_form"]');

    if(!myInputs){
      return;
    }
    
    // loop over the inputs and initialize the charts
    $.each(myInputs, function(index, value) {

      // find the other input handler
      let myForm = $(value).parents('form:first');
      let mySelector = $(value).val();

      iniExamifProgressBar(mySelector, myForm);

    });

}

window.iniAllProgressBarForms = iniAllProgressBarForms;

function iniExamifyQuestions()
{
  // add autoHeight to all 'textarea.auto-height' 
    $('textarea.auto-height').autoHeight();

    // automatically send the form on click of the radio button
    $('form.interactive-question-form input[type=radio], form.interactive-question-form textarea').on('change', function() {
      $(this).closest("form").submit();
    });

}

window.iniExamifyQuestions = iniExamifyQuestions;

function alignFooter()
{
  // get the footer and body
  let MyFooterPlaceholder = $('footer .my-footer-placeholder');
  let MyFooter = MyFooterPlaceholder.children('.container');
  let BodyHeight = $('body').outerHeight();
  let FooterHeight = MyFooter.outerHeight();
  let CurrentFooterMargin = parseInt(MyFooter.css('margin-top'));
  let FooterBottom = MyFooter.position().top + FooterHeight + CurrentFooterMargin;
  let deltaHeight = BodyHeight - FooterBottom + CurrentFooterMargin;
  MyFooter.css('margin-top', Math.max(deltaHeight, 0));
}

window.alignFooter = alignFooter;


$(document).ready(function () {        

		iniExamifyQuestions();
    iniPagination();
    iniAllChartForms();
    iniAllDataForms();
    iniAllProgressBarForms();
    iniSelectAll();
    alignFooter();

    var urlHash = window.location.href.split("#")[1];
    if (urlHash &&  $('#' + urlHash).length )
    {
        let target = $('#' + urlHash);
        $('html, body').animate({
          scrollTop: target.offset().top - $('.dummy-box').height() - parseInt($('.dummy-box').css('margin-bottom'))
        }, 1000);

        // in case the target is a collapsable, show it
        let innercollapse = target.find('.collapse');
        if(innercollapse)
        {
          innercollapse.collapse('show');
        }
      }

});

// on resize, align the footer again
$( window ).resize(function() {
  alignFooter();
});




