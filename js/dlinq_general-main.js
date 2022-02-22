console.log('special js');

jQuery(document).ready(function() {
    // Click event for any anchor tag that's href starts with #
    jQuery('a[href^="#"]').click(function(event) {
        if(!jQuery(this).data('toggle')){
            console.log(this);
             // The id of the section we want to go to.
            var id = jQuery(this).attr("href");

            // An offset to push the content down from the top.
            var offset = 560;

            // Our scroll target : the top position of the
            // section that has the id referenced by our href.
            var target = jQuery(id).offset().top - offset;

            // The magic...smooth scrollin' goodness.
            jQuery('html, body').animate({scrollTop:target}, 500);

            //prevent the page from jumping down to our section.
            event.preventDefault();

        }
       
    });
});


if (window.location.hash) {
    var hash = window.location.hash;

    if (jQuery(hash).length) {
        jQuery('html, body').animate({
            scrollTop: jQuery(hash).offset().top -150
        }, 900, 'swing');
    }
}