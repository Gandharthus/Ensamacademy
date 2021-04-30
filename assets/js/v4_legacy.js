jQuery(document).ready(function($){    
           
    $('#filtercontainer').each(function(){

      var $container = $('#filtercontainer'),
          filters = {};
     
      $container.isotope({
        itemSelector : '.filteritem',
      });
    

      // filter buttons
      $('.filters a').click(function(){
        var $this = $(this);
        // don't proceed if already selected
        if ( $this.hasClass('active') ) {
          return;
        }
        
        var $optionSet = $this.parents('.option-set');
        // change selected class
        $optionSet.find('.active').removeClass('active');
        $this.addClass('active');
        
        // store filter value in object
        // i.e. filters.color = 'red'
        var group = $optionSet.attr('data-filter-group');
        filters[ group ] = $this.attr('data-filter-value');
        // convert object into array
        var isoFilters = [];
        for ( var prop in filters ) {
          isoFilters.push( filters[ prop ] );
        }
        var selector = isoFilters.join('');
        $container.isotope({ filter: selector });

        return false;
      });   
    }); 

    $('.twitter_carousel').each(function(){
        var $this = $(this);
        $this.flexslider({
        animation: "slide",
        controlNav: false,
        directionNav: false,
        animationLoop: true,
        slideshow: true,
        prevText: "<i class='icon-arrow-1-left'></i>",
        nextText: "<i class='icon-arrow-1-right'></i>",
        start: function() {
                   $this.removeClass('loading');
               }
        });    
    });
    $('.certifications').flexslider({
      animation: "slide",
      controlNav: false,
      directionNav: true,
      animationLoop: true,
      slideshow: false,
      itemWidth: 212,
      itemMargin:10,
      maxItems:4,
      minItems:1,
      prevText: "<i class='icon-arrow-1-left'></i>",
      nextText: "<i class='icon-arrow-1-right'></i>",
    });
});// END ready