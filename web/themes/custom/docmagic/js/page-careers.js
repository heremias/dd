(function(jQuery) {
  //get job title from the page header
  var jobTitle = jQuery('h2.node__title').text().trim();

  //replace {job-title} token with job title
  jQuery('.careers-contact-info a').each(function() {
    jQuery(this).attr('href', jQuery(this).attr('href').replace('{job-title}', jobTitle));
  });
})(jQuery);
