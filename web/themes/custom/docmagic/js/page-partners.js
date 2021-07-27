(function(jQuery) {
  //process the partners logos into grid
  jQuery('div.partners-logo-wrap').each(function() {
    var newDiv = jQuery('<div/>');
    jQuery(this).find('a img').each(function() {
      var thisElem = jQuery(this).parent().clone();
      jQuery('<div class="partners-logo">').html(thisElem).appendTo(newDiv);
    });
    jQuery(this).html(newDiv.html().replace(/&nbsp;/g, ''));

  });

})(jQuery);
