(function ($) {

$(document).ready(function(){

    // carousel 
    $('.home-slider .owl-carousel').each(function(i){



    $(this).owlCarousel({
      // animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            loop:true,
            nav:false,
            items: 1,
            dots: false,
            mouseDrag: false,
            touchDrag: false,
            pullDrag: false,
            // autoHeight: false,
            autoplay:1000,
            // autoplayTimeout:1000,
        });
    });

    // carousel navigation 
    $('.c-dot').click(function(){
        var slide = $(this).attr('data-slide');
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('to.owl.carousel', slide);
      });

    $('.home-slider .custom-owl-arrows .next').click(function(){
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('next.owl.carousel');
    });
    $('.home-slider .custom-owl-arrows .prev').click(function(){
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('prev.owl.carousel');
    });


    // end ready
});


$(document).ready(function(){
    progSticky();
});

$(window).resize(function(){
    progSticky();
});

  // yellow menu sticky 
  function progSticky(){
      var height = $(window).height()/2 + 116;
    $(window).scroll(function(e){ 
      if ($(this).scrollTop() > height){ 
        $('body').addClass('prog-fixed');

      }
      if ($(this).scrollTop() < height){
        $('body').removeClass('prog-fixed');
      } 
    });
  }






})(jQuery);