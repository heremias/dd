(function ($) {

$(document).ready(function(){
    newsCarousel();
    // end ready
});

$(window).resize(function() {
    newsCarousel();
});


// stat carousel

function newsCarousel(){   
    $('#news-carousel').each(function(){
        if ($(window).width() < 992) {
            $('#news-carousel').addClass('owl-carousel');
            $('#news-carousel').owlCarousel({
                    loop:true,
                    nav:false,
                    items: 1,
                    mouseDrag: true,
                    touchDrag: true,
                    pullDrag: true,
                    dots: false,
                    autoplay: false,
                    autoplayTimeout: 3000,
                    center: true,
                });
            }
    
        else{
            $('#news-carousel').removeClass('owl-carousel');
            $('#news-carousel').owlCarousel('destroy');
        }
    });
}



})(jQuery);