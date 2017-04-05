
jQuery(document).ready(function(){
    jQuery.fn.megamenu = function(options) {
        options = jQuery.extend({
            animation: "show",
            mm_timeout: 0
        }, options);
        var megamenu_object = this;
        megamenu_object.find("li.parent").each(function(){
            var mm_item = jQuery(this).children('div');
				mm_item.hide();
				mm_item.wrapInner('<div class="list_item_dropdown clearfix"></div>');
            var timer = 0;
            jQuery(this).bind('mouseenter', function(e){
                var mm_item_obj = jQuery(this).children('div');
                jQuery(this).find("a:first").addClass('arw-hover');
				/* alert($(this).children('.list_item_dropdown').size()); */
                clearTimeout(timer);
                var check_menu = jQuery(this).find('div.list_item_dropdown');
                if(mm_item.length >0 && check_menu.length >0){
                    //alert(check_menu.html().length);
                    if(check_menu.html().length>1){
                        timer = setTimeout(function(){
                            switch(options.animation) {
                                case "show":
                                    mm_item_obj.show().addClass("shown-sub");
                                    break;
                                case "slide":
                                    mm_item_obj.height("auto");
                                    mm_item_obj.stop().slideDown('fast', function(){
                                        mm_item_obj.css("overflow","inherit");
                                    }).addClass("shown-sub");
                                    break;
                                case "fade":
                                    mm_item_obj.stop().fadeTo('fast', 1).addClass("shown-sub");
                                    break;
                            }
                        }, options.mm_timeout);
                    }
                }
            });
            jQuery(this).bind('mouseleave', function(e){
                clearTimeout(timer);
                var mm_item_obj = jQuery(this).children('div');
                jQuery(this).find("a:first").removeClass('arw-hover');
                switch(options.animation) {
                    case "show":
                        mm_item_obj.hide();
                        break;
                    case "slide":
                        mm_item_obj.stop().slideUp( 'fast',  function() {});
                        break;
                    case "fade":
                        mm_item_obj.stop().fadeOut( 'fast', function() {});
                        break;
                }
            });
        });
        this.show();
    };
});
