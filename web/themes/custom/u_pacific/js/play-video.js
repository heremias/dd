(function ($) {

    $(document).ready(function(){
        

    // watch video
    $('.play-video').click(function(e){
        e.preventDefault();
        var video = $(this).attr('data-video');
        videoButton(video); 
    });
    

      // close video
      $('#close-watch').click(function(e){
        e.preventDefault();
        $('.watch-popup iframe').remove();
        $('body').removeClass('watch-video');
      });
    
      // end ready
    });

    function videoButton(video){
      $('body').addClass('watch-video');
      video = video+'?autoplay=1&rel=0&amp;controls=0&amp;showinfo=0';
      $('.watch-popup').append('<iframe src="'+video+'" allow="autoplay; fullscreen"></iframe>');
    }
    
})(jQuery);