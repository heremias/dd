(function ($) {

$(document).ready(function(){


  // sticky nav 
  if($('body').hasClass('home')){
  $(window).scroll(function(){
    if(!$('body').hasClass('slideout-open')){
      var sticky = $('#navigation'),
          scroll = $(window).scrollTop(),
          height = $('#navigation').outerHeight() - 20;
      if (scroll >= height) {
        sticky.addClass('fixed');
        $('body').addClass('nav-fixed');
      }
      else{
         sticky.removeClass('fixed');
         $('body').removeClass('nav-fixed');
      }
    }
  });
}



  // remove tansition restriction
  $('body').removeClass('no-transitions-while-loading');


  // close slideout
  $('.close-subnav').click(function(e){
    e.preventDefault();
    $('body').removeClass('slideout-open');
    $('.slideout-wrap ul li a, .slideout-wrap input').attr('tabindex', -1);

    if($(window).scrollTop() == 0){
      if($('body').hasClass('home')){
      $('#navigation').removeClass('fixed');
      }
    }
  });

  // open slideout toggle
  $('a.nav-opener').click(function(e){
    e.preventDefault();
    $('body').addClass('slideout-open');
    $('#navigation').addClass('fixed');
    // $('.slideout-wrap ul li a, .slideout-wrap input').attr('tabindex', 0);
    // $('#block-utilitymenu ul li a').attr('tabindex', 1);
  });

  // set tab index to -1
  // $('.slideout-wrap ul li a, .slideout-wrap input').attr('tabindex', -1);

  // mobile nav class toggles 
  $('.mobile-accordion button').click(function(){
    $(this).parent().parent().toggleClass('accordion-open');
  });


  // full screen search 

   $('#search-pop').click(function(e){
    e.preventDefault();
    $('body').addClass('full-screen-search');
    $('#search-block-form--2 .form-search').focus();
  });
  $('#close-search').click(function(e){
    e.preventDefault();
    $('body').removeClass('full-screen-search');
  });



  // auto set mobile menu
  $('.mobile-accordion a.is-active').parent().parent().find('button').removeClass('collapsed');
  $('.mobile-accordion a.is-active').parent().parent().addClass('accordion-open');
  $('.mobile-accordion a.is-active').parent().addClass('show');

  if($('.first-link a.is-active').length ){
    $('.first-link a.is-active').parent().parent().find('.slidedown').addClass('show');
  }
  
  $('.nav-drop').show();

  // add tabindex for no links in yellow menu 
  $('.sf-depth-1.menuparent.nolink').attr('tabindex', 0);
  // end ready
});


})(jQuery);