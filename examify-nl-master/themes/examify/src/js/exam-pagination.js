// ---- exam-pagination.js
export default class ExamifyPagination {

  constructor(selector_to_paginate, ul_selector_for_pagination_nav, selector_toggle_pagination, scroll_to_top){
  
    // hide all pagination-placeholders
    $(selector_to_paginate).hide();

    this.pageContents = $(selector_to_paginate);

    // initialize the navbar
    this.initNavBar(ul_selector_for_pagination_nav);

    this.navBars    = $(ul_selector_for_pagination_nav);
    this.navItems   = $(ul_selector_for_pagination_nav + ' li.page-item-nav-elements');
    this.navPrev    = $(ul_selector_for_pagination_nav + ' li#prev-page');
    this.navNext    = $(ul_selector_for_pagination_nav + ' li#next-page'); 
    this.activePage = 0;
    this.nPages     = this.pageContents.length;
    this.scrollToTop = scroll_to_top; // true or false
    this.activeState = $(selector_toggle_pagination + ' input[type="checkbox"]').is(':checked'); // relate it to the checkbox
    this.activePageToSet = parseInt($(selector_toggle_pagination + ' input[name="activePageToSet"]').val()); // relate it to the checkbox

    // always make it at least 1
    if(this.activePageToSet < 1){
      this.activePageToSet = 1;
    }

    // set initialization state, to make sure it is not scrolled to the navbar on loading of the page
    this.Initializing = true;

    var self = this;

    // initialize the click functions
    this.navNext.click(function() {
      self.showPage(self.activePage + 1);
    });

    this.navPrev.click(function() {
      self.showPage(self.activePage - 1);
    });

    this.navItems.click(function() {
      self.showPage( $(this).data('link-to-page') );
    });

    $(selector_toggle_pagination + ' input[type="checkbox"]').change(function() {

      // get the active state from the checkbox
      self.activeState = this.checked;

      // apply the active state
      self.applyActiveState();

    });

    // apply the active state
    self.applyActiveState();

    this.Initializing = false;


  }

  applyActiveState()
  {
    if(!this.activeState){

        // switch it off
        this.pageContents.show();
        this.navBars.hide();

        // scroll to have correct element in place
        if(this.activePage > 1 && !this.Initializing){
          let target = $('.active-pagination-view');
          window.scrollTo(0, target.offset().top - $('.dummy-box').height() - parseInt($('.dummy-box').css('margin-bottom')));
        }

      }
      else {
        // switch it on
        this.pageContents.hide();
        this.navBars.show();

        // in case activePage = 0, set it to the first page
        if(this.activePage == 0){
          this.showPage(this.activePageToSet);
          return;
        }

        // show the page that is active
        this.pageContents.eq(this.activePage - 1).show();
      }

  }

  initNavBar(ul_selector) {
    // add the prev button to the navbar
    var mynav = $(ul_selector);
    mynav.append('<li class="page-item disabled" id="prev-page" ><a class="page-link" tabindex="-1">Vorige</a></li>');

    // now add as many nav items as pageContents
    var i;
    for (i = 1; i <= this.pageContents.length; i++) {
      mynav.append('<li class="page-item page-item-nav-elements" data-link-to-page="' + i + '"><a class="page-link shadow-none" >' + i + '</a></li>');
    }

    // add the next button to the navbar
    mynav.append('<li class="page-item" id="next-page" ><a class="page-link">Volgende</a></li>');
  }

  showPage(pagenr) {

    // if the active page is still empty, it means no previous page is found, then always enable the prev and next 
    if(this.activePage == 0){
      this.navNext.removeClass('disabled');
      this.navPrev.removeClass('disabled');
    }

    // do not do anything if it is the same as current page
    if(pagenr == this.activePage){
      return;
    }

    // check if it is within bounds
    if(pagenr < 1 || pagenr > this.nPages){
      return;
    }

    // remove the active class of the old one
    this.navItems.filter('[data-link-to-page="' + this.activePage + '"]').removeClass('active');

    // add it to the new one
    this.navItems.filter('[data-link-to-page="' + pagenr + '"]').addClass('active');

    // hide the previous one
    this.pageContents.eq(this.activePage - 1).hide();
    this.pageContents.eq(this.activePage - 1).removeClass('active-pagination-view');
    this.pageContents.eq(pagenr - 1).show();
    this.pageContents.eq(pagenr - 1).addClass('active-pagination-view');

    // update the class for the next if needed
    if(pagenr == this.nPages){
      this.navNext.addClass('disabled');
    }

    // in case the current page nr is last, remove the disabled class since we are moving away from this page
    if(this.activePage == this.nPages){
      this.navNext.removeClass('disabled');
    }

    // update the class for the prev if needed
    if(pagenr == 1){
      this.navPrev.addClass('disabled');
    }

    // in case the current page nr is 1, remove the disabled class from the prev button since we are going to leave this page
    if(this.activePage == 1){
      this.navPrev.removeClass('disabled');
    }

    // update the pagenr
    this.activePage = pagenr;

    // update the active page in any element that wants to track the pagination
    $('input[name="tracker_examify_pagination_active_view"]').val(pagenr);

    // get the active text id
    $('input[name="tracker_examify_pagination_active_text_id"]').val(this.pageContents.eq(pagenr - 1).attr('data-exam-text-id'));

    // scroll to the the top of toggle-pagination-mode
    if(this.scrollToTop && !this.Initializing){
      let target = $('#toggle-pagination-mode');
      window.scrollTo(0, target.offset().top - $('.dummy-box').height() - parseInt($('.dummy-box').css('margin-bottom')));
    }

  }

}