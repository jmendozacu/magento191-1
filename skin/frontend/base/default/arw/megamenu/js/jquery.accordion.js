
jQuery(document).ready(function(){
    jQuery.fn.extend({
        arwAccordionMenu: function(options) {
            var defaults = {
                accordion: 'true',
                speed: 300,
                closedSign: 'collapse',
                openedSign: 'expand'
            };
            var opts = jQuery.extend(defaults, options);
            var $this = jQuery(this);
            $this.find("li").each(function() {
                if(jQuery(this).find("ul").size() != 0){
                    jQuery(this).find("a:first").after("<span class='"+ opts.closedSign +"'>"+ opts.closedSign +"</span>");
                    if(jQuery(this).find("a:first").attr('href') == "#"){
                        jQuery(this).find("a:first").click(function(){return false;});
                    }
                }
            });
            $this.find("li.active").each(function() {
                jQuery(this).parents("ul").slideDown(opts.speed, opts.easing);
                jQuery(this).parents("ul").parent("li").find("a:first").next().html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign);
				jQuery(this).find("ul:first").slideDown(opts.speed, opts.easing);
                jQuery(this).find("a:first").next().html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign);
            });
            if(opts.mouseType==0){
                $this.find("li span").click(function() {
                    if(jQuery(this).parent().find("ul").size() != 0){
                        if(opts.accordion){
                            //Do nothing when the list is open
                            if(!jQuery(this).parent().find("ul").is(':visible')){
                                parents = jQuery(this).parent().parents("ul");
                                visible = $this.find("ul:visible");
                                visible.each(function(visibleIndex){
                                    var close = true;
                                    parents.each(function(parentIndex){
                                        if(parents[parentIndex] == visible[visibleIndex]){
                                            close = false;
                                            return false;
                                        }
                                    });
                                    if(close){
                                        if(jQuery(this).parent().find("ul") != visible[visibleIndex]){
                                            jQuery(visible[visibleIndex]).slideUp(opts.speed, function(){
                                                jQuery(this).parent("li").find("a:first").next().html(opts.closedSign).addClass(opts.closedSign);
                                            });
                                        }
                                    }
                                });
                            }
                        }
                        if(jQuery(this).parent().find("ul:first").is(":visible")){
                            jQuery(this).parent().find("ul:first").slideUp(opts.speed, opts.easing, function(){
                               jQuery(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.closedSign).removeClass(opts.openedSign).addClass(opts.closedSign);
                            });
                        }else{
                            jQuery(this).parent().find("ul:first").slideDown(opts.speed, opts.easing, function(){
                                jQuery(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign);
                            });
                        }
                    }
                });
            }
            if(opts.mouseType>0){
                $this.find("li a").mouseenter(function() {
                    if(jQuery(this).parent().find("ul").size() != 0){
                        if(opts.accordion){
                            if(!jQuery(this).parent().find("ul").is(':visible')){
                                parents = jQuery(this).parent().parents("ul");
                                visible = $this.find("ul:visible");
                                visible.each(function(visibleIndex){
                                    var close = true;
                                    parents.each(function(parentIndex){
                                        if(parents[parentIndex] == visible[visibleIndex]){
                                            close = false;
                                            return false;
                                        }
                                    });
                                    if(close){
                                        if(jQuery(this).parent().find("ul") != visible[visibleIndex]){
                                            jQuery(visible[visibleIndex]).slideUp(opts.speed, function(){
                                                jQuery(this).parent("li").find("a:first").next().html(opts.closedSign).addClass(opts.closedSign);
                                            });
                                        }
                                    }
                                });
                            }
                        }
                        if(jQuery(this).parent().find("ul:first").is(":visible")){
                            jQuery(this).parent().find("ul:first").slideUp(opts.speed, function(){
                                jQuery(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.closedSign).removeClass(opts.openedSign).addClass(opts.closedSign);
                            });
                        }else{
                            jQuery(this).parent().find("ul:first").slideDown(opts.speed, function(){
                                jQuery(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign);
                            });
                        }
                    }
                });
            }
        }
    });
});