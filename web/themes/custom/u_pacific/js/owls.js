(function ($) {

$(document).ready(function(){

    // carousel 
    $('.paragraph--type--carousel .owl-carousel').each(function(i){


        var ifMultiple = false;
        if($(this).children('.item').length > 1) {
            ifMultiple = true;
          }

    $(this).owlCarousel({
            animateOut: 'fadeOut',
            loop:true,
            nav:false,
            items: 1,
            dots: false,
            autoplay: false,
            // autoplayTimeout: 3000,
            mouseDrag: false,
            touchDrag: true,
            pullDrag: true,
        });
    });

    // carousel navigation 
    $('.c-dot').click(function(){
        var slide = $(this).attr('data-slide');
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('to.owl.carousel', slide);
      });

    $('.paragraph--type--carousel .custom-owl-arrows .next').click(function(){
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('next.owl.carousel');
    });
    $('.paragraph--type--carousel .custom-owl-arrows .prev').click(function(){
        var carousel = $(this).attr('data-carousel');
        $('#'+carousel).trigger('prev.owl.carousel');
    });


    statCarousel();

    // end ready
});

$(window).resize(function() {
    statCarousel();
});


// stat carousel

function statCarousel(){   
    $('.paragraph--type--stat-grid .owl').each(function(){
        var id = $(this).attr('id');
        if ($(window).width() < 768) {
            $('#'+id).addClass('owl-carousel');
            $('#'+id).owlCarousel({
                    loop:true,
                    nav:false,
                    items: 1,
                    mouseDrag: false,
                    touchDrag: true,
                    pullDrag: true,
                    dots: true,
                    autoplay: false,
                    autoplayTimeout: 3000,
                });
            }
    
        else{
            $('#'+id).removeClass('owl-carousel');
            $('#'+id).owlCarousel('destroy');
        }
    });
}



})(jQuery);