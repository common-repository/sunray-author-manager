jQuery(document).ready(function () {

    console.log('Sunray Author Manager');
    
    if (jQuery('.sam-slider').length > 0) {




        jQuery.each(jQuery('.sam-slider'), function (i, slide) {
            var speed = jQuery(slide).data('slide-speed');

            // remove em or strong tags from inside slider
           /* if(jQuery(slide).children("strong").index() == 0) {
                jQuery(slide).html(jQuery(slide).children("strong").html());
            }

            if(jQuery(slide).children("em").index() == 0) {
                jQuery(slide).html(jQuery(slide).children("em").html());
            }*/

            jQuery(slide).slick({
                infinite: true,
                dots: false,
                arrows: true,
                slidesToScroll: 1,
                slidesToShow: 3,
                autoplay: true,
                //autoplaySpeed: sam_options.slider_speed * 1000,
                autoplaySpeed: speed * 1000,
                variableWidth: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            infinite: true
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }]

            });
        });

    }

    //console.log(jQuery.fn.jquery);


    // hides slider until all images loaded

    jQuery(".sam-slider-loading").show();

    var num_images = jQuery(".sam-slide").length;
    var load_counter = 0;
    jQuery(".sam-slide-image").on("load", function () {
        load_counter++;
        if (num_images == load_counter) {
            jQuery('.sam-slider').show();
            jQuery(".sam-slider-loading").hide();
        }
    }).each(function () {
        // attempt to defeat cases where load event does not fire on cached images
        if (this.complete) jQuery(this).trigger("load");
    });
});