(function ($) {

$(document).ready(function(){
    // carousel 



    $('#featured-news-carousel').owlCarousel({
            animateOut: 'fadeOut',
            loop:true,
            nav:false,
            items: 1,
            dots: false,
            autoplay: false,
            // autoplayTimeout: 3000,
            mouseDrag: true,
            touchDrag: true,
            pullDrag: true,
        });


    // carousel navigation 
    $('#featured-news-carousel .c-dot').click(function(){
        var slide = $(this).attr('data-slide');
        $('#featured-news-carousel').trigger('to.owl.carousel', slide);
      });

    $('#featured-news-carousel .custom-owl-arrows .next').click(function(){
        $('#featured-news-carousel').trigger('next.owl.carousel');
    });
    $('#featured-news-carousel .custom-owl-arrows .prev').click(function(){
        $('#featured-news-carousel').trigger('prev.owl.carousel');
    });


    // end ready
});






})(jQuery);