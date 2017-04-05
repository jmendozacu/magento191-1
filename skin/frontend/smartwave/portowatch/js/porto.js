function setNewsletterCookie() {
    jQuery.cookie('newsletter_popup', 'dontshowitagain');
}
jQuery.fn.extend({
    scrollToMe: function () {
        var top = jQuery(this).offset().top - 100;
        jQuery('html,body').animate({scrollTop: top}, 500);
    },
    scrollToJustMe: function () {
        var top = jQuery(this).offset().top;
        jQuery('html,body').animate({scrollTop: top}, 500);
    }
});
function portoAlert(msg) {
    jQuery('<div class="note-msg container alert" style="display:none;position:fixed;top:30px;margin-left:-30px;z-index:9999;">' + msg + '</div>').appendTo("div.main");
    jQuery(".alert").fadeIn(500);
    setTimeout(function () {
        jQuery(".alert").fadeOut(500);
        setTimeout(function () {
            jQuery(".alert").remove();
        }, 500);
    }, 3000);
}
jQuery(function ($) {

//setInterval(function(){
//  console.log("1");
//}, 1000);




    $('div.product-view p.no-rating a, div.product-view .rating-links a').click(function () {
        $('.product-tabs ul li').removeClass('active');
        $('#tab_review_tabbed').addClass('active');
        $('.product-tabs .tab-content').hide();
        $('#tab_review_tabbed_contents').show();
        $('#tab_review_tabbed').scrollToMe();
        return false;
    });

    var scrolled = false;
    $(window).scroll(function () {
        if (200 < $(window).scrollTop() && !scrolled) {
            $('.fixed-header-area').animate({top: '0px'}, 100);
            scrolled = true;
        }
        if (300 > $(window).scrollTop() && scrolled) {
            $('.fixed-header-area').animate({top: '-160px'}, 100);
            scrolled = false;
        }
    });

    $(".word-rotate").each(function () {

        var $this = $(this),
                itemsWrapper = $(this).find(".word-rotate-items"),
                items = itemsWrapper.find("> span"),
                firstItem = items.eq(0),
                firstItemClone = firstItem.clone(),
                itemHeight = 0,
                currentItem = 1,
                currentTop = 0;

        itemHeight = firstItem.height();

        itemsWrapper.append(firstItemClone);

        $this
                .height(itemHeight)
                .addClass("active");

        setInterval(function () {
            currentTop = (currentItem * itemHeight);

            itemsWrapper.animate({
                top: -(currentTop) + "px"
            }, 300, function () {
                currentItem++;
                if (currentItem > items.length) {
                    itemsWrapper.css("top", 0);
                    currentItem = 1;
                }
            });

        }, 2000);

    });
    $(window).stellar({
        responsive: true,
        scrollProperty: 'scroll',
        parallaxElements: false,
        horizontalScrolling: false,
        horizontalOffset: 0,
        verticalOffset: 0
    });
    /********** Fullscreen Slider ************/
    var s_width = $(window).innerWidth();
    var s_height = $(window).innerHeight();
    var s_ratio = s_width / s_height;
    var v_width = 320;
    var v_height = 240;
    var v_ratio = v_width / v_height;
    $(".full-screen-slider div.item").css("position", "relative");
    $(".full-screen-slider div.item").css("overflow", "hidden");
    $(".full-screen-slider div.item").width(s_width);
    $(".full-screen-slider div.item").height(s_height);
    $(".full-screen-slider div.item > video").css("position", "absolute");
    $(".full-screen-slider div.item > video").bind("loadedmetadata", function () {
        v_width = this.videoWidth;
        v_height = this.videoHeight;
        v_ratio = v_width / v_height;
        if (s_ratio >= v_ratio) {
            $(this).width(s_width);
            $(this).height("");
            $(this).css("left", "0px");
            $(this).css("top", (s_height - s_width / v_width * v_height) / 2 + "px");
        } else {
            $(this).width("");
            $(this).height(s_height);
            $(this).css("left", (s_width - s_height / v_height * v_width) / 2 + "px");
            $(this).css("top", "0px");
        }
        $(this).get(0).play();
    });
    $(".header-container.type10 .dropdown-menu .menu-container>a").click(function () {
        if (!$("body").hasClass("cms-index-index")) {
            if ($(this).next().hasClass("show")) {
                $(this).next().removeClass("show");
            } else {
                $(this).next().addClass("show");
            }
        }
        if ($(window).width() <= 991) {
            if ($(".mobile-nav.side-block").hasClass("show")) {
                $(".mobile-nav.side-block").removeClass("show");
                $(".mobile-nav.side-block").slideUp();
            } else {
                $(".mobile-nav.side-block").addClass("show");
                $(".mobile-nav.side-block").slideDown();
            }
        }
    });
    if ($(window).width() >= 992)
        $(".cms-index-index .header-container.type10+.top-container .slider-wrapper").css("min-height", $(".header-container.type10 .menu.side-menu").height() + 20 + "px");
    $(window).resize(function () {
        if ($(window).width() >= 992)
            $(".cms-index-index .header-container.type10+.top-container .slider-wrapper").css("min-height", $(".header-container.type10 .menu.side-menu").height() + 20 + "px");
        else
            $(".cms-index-index .header-container.type10+.top-container .slider-wrapper").css("min-height", "");
        s_width = $(window).innerWidth();
        s_height = $(window).innerHeight();
        s_ratio = s_width / s_height;
        $(".full-screen-slider div.item").width(s_width);
        $(".full-screen-slider div.item").height(s_height);
        $(".full-screen-slider div.item > video").each(function () {
            if (s_ratio >= v_ratio) {
                $(this).width(s_width);
                $(this).height("");
                $(this).css("left", "0px");
                $(this).css("top", (s_height - s_width / v_width * v_height) / 2 + "px");
            } else {
                $(this).width("");
                $(this).height(s_height);
                $(this).css("left", (s_width - s_height / v_height * v_width) / 2 + "px");
                $(this).css("top", "0px");
            }
        });
    });

    /************** Header - Search icon, Links icon click event ***************/
    $(".top-links-icon").click(function (e) {
        $("a.search-icon").parent().children("#search_mini_form").removeClass("show");
        if ($(this).parent().children("ul.links").hasClass("show")) {
            $(this).parent().children("ul.links").removeClass("show");
        } else
            $(this).parent().children("ul.links").addClass("show");
        e.stopPropagation();
    });
    $("a.search-icon").click(function (e) {
        $(".top-links-icon").parent().children("ul.links").removeClass("show");
        if ($("#search_mini_form").hasClass("show")) {
            $('.blacklayout').hide();
            // $('.blacklayout').removeClass('searchopened');
            // $('.header-container').removeClass('searchopened');
            $("#search_mini_form").removeClass("show");
        } else {
            $('.blacklayout').show();
            // $('.blacklayout').addClass('searchopened');
            // $('.header-container').addClass('searchopened');
            $("#search_mini_form").addClass("show");
            $('.algolia-search-input').focus();
        }
        e.stopPropagation();
    });
    $(".closesearchform").click(function (e) {
        $('.blacklayout').hide();
        // $('.blacklayout').removeClass('searchopened');
        // $('.header-container').removeClass('searchopened');
        $("#search_mini_form").removeClass("show");
    });
    $("a.search-icon").parent().click(function (e) {
        e.stopPropagation();
    })
    $("html,body").click(function () {
        $(".top-links-icon").parent().children("ul.links").removeClass("show");
        $("a.search-icon").parent().children("#search_mini_form").removeClass("show");
    });

    /********************* Product Images ***********************/

    $("a.product-image img.defaultImage").each(function () {
        var default_img = $(this).attr("src");
        if (!default_img)
            default_img = $(this).attr("data-src");
        var thumbnail_img = $(this).parent().children("img.hoverImage").attr("src");
        if (!thumbnail_img)
            thumbnail_img = $(this).parent().children("img.hoverImage").attr("data-src");
        if (default_img) {
            if (default_img.replace("/small_image/", "/thumbnail/") == thumbnail_img) {
                $(this).parent().children("img.hoverImage").remove();
                $(this).removeClass("defaultImage");
            }
        }
    });

    /********************* Qty Holder **************************/
    $(".table_qty_inc").unbind('click').click(function () {
        if ($(this).parent().children(".qty").is(':enabled'))
            $(this).parent().children(".qty").val((+$(this).parent().children(".qty").val() + 1) || 0);
    });
    $(".table_qty_dec").unbind('click').click(function () {
        if ($(this).parent().children(".qty").is(':enabled'))
            $(this).parent().children(".qty").val(($(this).parent().children(".qty").val() - 1 > 0) ? ($(this).parent().children(".qty").val() - 1) : 0);
    });

    $(".qty_inc").unbind('click').click(function () {
        if ($(this).parent().parent().children("input.qty").is(':enabled')) {
            $(this).parent().parent().children("input.qty").val((+$(this).parent().parent().children("input.qty").val() + 1) || 0);
            $(this).parent().parent().children("input.qty").focus();
            $(this).focus();
        }
    });
    $(".qty_dec").unbind('click').click(function () {
        if ($(this).parent().parent().children("input.qty").is(':enabled')) {
            $(this).parent().parent().children("input.qty").val(($(this).parent().parent().children("input.qty").val() - 1 > 0) ? ($(this).parent().parent().children("input.qty").val() - 1) : 0);
            $(this).parent().parent().children("input.qty").focus();
            $(this).focus();
        }
    });

    /* moving action links into product image area */
    $(".move-action .item .details-area .actions").each(function () {
        $(this).parent().parent().children(".product-image-area").append($(this));
    });

    $(window).load(function () {
        var isMobile = false; //initiate as false
// device detection
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
                || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4)))
            isMobile = true;
        if (isMobile) {
            $('.mobilemenu-cat').append($('.block.block-layered-nav'));
            var mthis = $(".block-layered-nav dt");
            $(mthis).next("dd").slideUp(200);
            $(mthis).addClass("closed");
        }
    });

    $('#refine').click(function () {
        var mMenu = $('.mobilemenu-cat');
        var button = $('#refine');
        if (button.hasClass('oppened')) {
            mMenu.slideUp('slow');
            button.removeClass('oppened');
        } else {
            mMenu.slideDown('slow');
            button.addClass('oppened');
        }
    });
});

jQuery(document).ready(function ($) {
    screenWidth = jQuery(window).width();
    if (screenWidth > 768) {
        $('#search').keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
            }
        });
    }
});

