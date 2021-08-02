(function ($) {


$(document).ready(function(){
    subnavSticky();

    $('#superfish-college-of-the-pacific-menu-toggle').click(function(){
      $('body').toggleClass('submenu-fullscreen');
    });
});
$(window).resize(function(){
    subnavSticky();
});
  // yellow menu sticky 
  function subnavSticky(){
    var navheight = $('#navigation').height();
    var yellowheight = $('#sub-menu').outerHeight();
    var admin = 0;

    if ($(window).width() >= 992){
      if ($('body').hasClass('toolbar-horizontal')){
        admin = 89;
      }
      if ($('body').hasClass('toolbar-vertical')){
        admin = 46;
      }
      $('#desktop-tools').css('top', yellowheight + navheight + admin);
      $('#block-pageheaderblock').css('margin-top', yellowheight + navheight - admin);

      if($('#block-emergencyalert').length){
        $('#block-emergencyalert').css('margin-top', yellowheight + navheight - admin);
        $('#block-emergencyalert').css('margin-bottom', (yellowheight + navheight - admin) * -1);
      }

    }
    else{
      $('#sub-menu').removeAttr('style');
      $('#block-pageheaderblock').removeAttr('style');
      if($('#block-emergencyalert').length){
      $('#block-emergencyalert').removeAttr('style');
      }
    }
  }
})(jQuery);