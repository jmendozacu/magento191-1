;
if (typeof ajaxCart == 'undefined') {
    var ajaxCart = {};
}
;
/*var hack_element_fly;*/
ajaxCart = Class.create();
ajaxCart.prototype = {
    options: null,
    miniCartClass: '.mini-cart-header',
    url: null,
    updateUrl: null,
    isProductView: 0,
    productId: 0,
    initialize: function (options) {
        this.url = options['send_url'];
        this.updateUrl = options['update_url'];
        this.options = options;
        this.productId = options['product_id'];
        this.isProductView = options['is_product_view'];
    },
    sendAjax: function (idProduct, param, amEvent, element) {
        /*if(element){
         hack_element_fly = element;
         }*/
        if (idProduct) {
            var postData = 'product_id=' + idProduct;
            postData = this.addProductParam(postData, idProduct);
            if ('' == postData) {
                return true;
            }
            new Ajax.Request(this.url, {
                method: 'post',
                postBody: postData,
                onCreate: function ()
                {
                    this.disableClick();
                    jQuery(".arw-ajaxcart-loader").show();

                }.bind(this),
                onComplete: function ()
                {
                    this.enableClick();
                    if ($('arw_quickview_popup') != undefined) {
                        jQuery('.fancybox-overlay').trigger('click');
                    }
                    jQuery(".arw-ajaxcart-loader").hide();
                }.bind(this),
                onSuccess: function (transport) {
                    jQuery('img.imgfly').remove();
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        if (response.error) {
                            alert(response.error);
                        } else {
                            if (response.redirect) {
                                //if IE7
                                if (document.all && !document.querySelector) {
                                    amEvent = amEvent.substring(21, amEvent.length - 2)
                                    eval(amEvent);
                                } else {
                                    eval(amEvent);
                                }
                                return true;
                            }

                            this.showPopup(response.dataOption, response.add_to_cart, response.action);

                            var maxHeight = parseInt($$('html')[0].getHeight() / 4);
                            var height = document.getElementById('toPopup').getHeight();
                            if (!(height <= maxHeight)) {
                                $('toPopup').setStyle({
                                    overflowY: 'scroll',
                                    maxHeight: maxHeight + 'px'
                                });
                            }
                            if (response.add_to_cart == '1') {
                                if (jQuery('body').hasClass('arexworks-quickview-index')) {
                                    parent.ajaxCartObj.updateSidebarCart();
                                    parent.ajaxCartObj.updateShoppingCart();
                                    parent.ajaxCartObj.updateMinicart();
                                    parent.ajaxCartObj.updateCount(response.count);
                                } else {
                                    /*arw_show_image_fly_when_click_addcart(jQuery(hack_element_fly));*/
                                    this.updateSidebarCart();
                                    this.updateShoppingCart();
                                    this.updateMinicart();
                                    this.updateCount(response.count);
                                }
                            }
                        }
                    }
                }.bind(this),
                onFailure: function ()
                {
                    this.hideAnimation();
                    eval(amEvent);
                }.bind(this)
            });
        }
    },
    disableClick: function () {
        $$('.btn-cart').each(function (btn) {
            btn.setAttribute('disabled', 'disabled');
        });
    },
    enableClick: function () {
        $$('.btn-cart').each(function (btn) {
            btn.removeAttribute('disabled');
        });
    },
    addProductParam: function (postData, idProduct) {
        var form = "";
        if (this.isProductView) {
            if (idProduct == this.productId) {
                form = $('product_addtocart_form');
            } else {
                if ($$('#arw_quickview_popup #product_addtocart_form_quickview')[0]) {
                    form = $$('#arw_quickview_popup #product_addtocart_form_quickview')[0];
                }
            }
        }
        if (idProduct != this.productId) {
            if ($$('#toPopup #product_addtocart_form')[0]) {
                form = $$('#toPopup #product_addtocart_form')[0];
            }
            if ($$('#arw_quickview_popup #product_addtocart_form_quickview')[0]) {
                form = $$('#arw_quickview_popup #product_addtocart_form_quickview')[0];
            }
        }
        if (form) {
            var validator = new Validation(form);
            if (validator.validate()) {
                postData += "&" + jQuery(form).serialize()
            } else {
                return '';
            }
        }
        /*
         * Added by: Tran Trong Thang
         * Email: trantrongthang1207@gmail.com
         */
        if ($$('#product_addtocart_form_' + idProduct)[0]) {
            form = $$('#product_addtocart_form_' + idProduct)[0];
            var validator = new Validation(form);
            if (validator.validate()) {
                postData += "&" + jQuery(form).serialize();
            } else {
                return '';
            }
        }
        postData += '&IsProductView=' + this.isProductView;
        return postData;
    },
    createPopup: function () {
        //$$('body')[0].insert("<div id='toPopup'></div><div class='arw-ajaxcart-loader arw-bg-overlay'><div class='arw-bg-loading'><div></div></div></div><div id='backgroundPopup' class='arw-bg-overlay'></div>");
        $$('body')[0].insert("<div id='toPopup'></div><div class='arw-ajaxcart-loader z-index-9'><div class='arw-bg-loading'><div></div></div></div><div id='backgroundPopup' class='arw-bg-overlay'></div>");
    },
    showPopup: function (data, isadd, action) {
        var popupStatus = 0;
        //jQuery(".arw-ajaxcart-loader").show();
        jQuery('#toPopup').replaceWith('<div id="toPopup">' + data + '</div>');
        jQuery('.quickview-index-view #toPopup a.button').each(function () {
            var href = jQuery(this).attr('href');
            if (typeof href !== typeof undefined && href !== false && href != '#') {
                jQuery(this).click(function () {
                    parent.location.href = href;
                    return false;
                })
            }
        })
        setTimeout(function () {
            if (popupStatus == 0) {
                jQuery('#toPopup').fadeIn(500);
                jQuery('#backgroundPopup').fadeIn(100);
                popupStatus = 1;
            }
        }, 200);
        if (isadd == '1')
        {
            setTimeout(function () {
                if (popupStatus == 1) {
                    jQuery("#toPopup").fadeOut("normal");
                    jQuery("#backgroundPopup").fadeOut("normal");
                    jQuery('#toPopup').replaceWith('<div id="toPopup"></div>');
                    popupStatus = 0;
                }
            }, 1000000);
        }
        jQuery(".cart-continue").click(function () {
            if (popupStatus == 1) {
                jQuery("#toPopup").fadeOut("normal");
                jQuery("#backgroundPopup").fadeOut("normal");
                jQuery('#toPopup').replaceWith('<div id="toPopup"></div>');
                popupStatus = 0;
                parent.jQuery.fancybox.close();
            }
        });
        jQuery("#backgroundPopup").click(function () {
            if (popupStatus == 1) {
                jQuery("#toPopup").fadeOut("normal");
                jQuery("#backgroundPopup").fadeOut("normal");
                jQuery('#toPopup').replaceWith('<div id="toPopup"></div>');
                popupStatus = 0;
            }
        });
        if (isadd == '0')
        {
            this.inPopup(action);
        }
    },
    inPopup: function (action) {
        jQuery('a.btn-cart').click(function () {
            jQuery('#toPopup').hide();
            jQuery('#backgroundPopup').hide();
            eval(action);
        });
        jQuery('a.btn-cancel').click(function () {
            jQuery("#toPopup").fadeOut("normal");
            jQuery("#backgroundPopup").fadeOut("normal");
            jQuery('#toPopup').replaceWith('<div id="toPopup"></div>');

        });
    },
    updateSidebarCart: function () {
        if (jQuery('.sidebar .block-cart').length > 0) {
            var url = this.url.replace(this.url.substring(this.url.length - 6, this.url.length), 'cart');
            new Ajax.Request(url, {
                method: 'post',
                onSuccess: function (transport) {
                    if (transport.responseText) {
                        jQuery('.sidebar .block-cart').replaceWith(transport.responseText);
                    }
                }.bind(this)
            });
        }
    },
    updateCount: function (count) {
        jQuery(this.miniCartClass + ' .total-badge').html(count);
        //jQuery(this.miniCartClass).append(count);
    },
    updateShoppingCart: function () {
        if (jQuery('body.checkout-cart-index div.cart').length > 0) {
            var url = this.url.replace(this.url.substring(this.url.length - 6, this.url.length), 'checkout');
            new Ajax.Request(url, {
                method: 'post',
                onSuccess: function (transport) {
                    if (transport.responseText) {
                        jQuery('body.checkout-cart-index div.cart').replaceWith(transport.responseText);
                        ajaxCartShoppCartLoad('.btn-cart');
                        ajaxCartShoppCartLoad('.link-cart');
                    }
                }.bind(this)
            });
        }
    },
    updateMinicart: function () {
        var url = ajaxCartObj.url.replace(ajaxCartObj.url.substring(ajaxCartObj.url.length - 6, ajaxCartObj.url.length), 'reloadCart');
        var element = $('arw_mini_cart_header');
        new Ajax.Updater(element, url, {
            method: 'post',
            onComplete: function () {
                changeDeleteCartToAjax();
            }
        });

    },
    showMinicart: function () {
        jQuery("#arw_mini_cart_header").stop(true, true).delay(200).slideDown(200, "easeOutBounce");
    },
    hideMinicart: function () {
        jQuery("#arw_mini_cart_header").stop(true, true).delay(200).fadeOut(500, "easeInCubic");
    },
    searchInPriceBox: function (parent, amEvent, element, idProduct) {
        if (parent.getElementsByClassName('special-price')[0])
        {
            var child = parent.getElementsByClassName('special-price')[0];
            var elementInt = 1;
        } else
        {
            var child = parent.getElementsByClassName('price-box')[0];
            var elementInt = 0;
        }

        if (child) {
            var childNext = child.childElements()[elementInt];
            if (childNext) {
                idProduct = childNext.id.replace(/[^\d]/gi, '');
            }
            if (!idProduct || idProduct == '') {
                child.childElements()[0].childElements().each(function (childNext) {
                    idProduct = childNext.id.replace(/[a-z-]*/, '');
                    if (parseInt(idProduct) > 0) {
                        return idProduct;
                    }
                }.bind(this));
            }
            if (!idProduct || idProduct == '') {
                child.select(".price").each(function (childNext) {
                    if (childNext.id)
                        idProduct = childNext.id.replace(/[a-z-]*/, '');
                    if (parseInt(idProduct) > 0) {
                        return idProduct;
                    }
                }.bind(this));
            }
            if (parseInt(idProduct) > 0) {
                var tmp = parseInt(idProduct);
                this.sendAjax(tmp, '', amEvent, element);
                return idProduct;
            } else {
                idProduct = '';
            }
        }
        return '';
    }
};
function searchIdAndSendAjax(event) {
    var element = Event.element(event);

    //showAnimation(element);
    event.preventDefault();
    var addToLinc = 'add-to-links';

    if ($('confirmBox')) {
        jQuery(function ($) {
            $.confirm.hide();
        })
    }
    if (!element.hasClassName('button')) {
        element = $(element.parentNode.parentNode);
    }
    var amEvent = element.getAttribute('amEvent');
    var idProduct = '';
    var el = $(element.parentNode.parentNode);
    if (el) {
        var idProduct = ajaxCartObj.searchInPriceBox(el, amEvent, element, idProduct);
    }
    if (idProduct == '') {
        var el = $(element.parentNode.parentNode.parentNode);
        if (el) {
            var idProduct = ajaxCartObj.searchInPriceBox(el, amEvent, element, idProduct);
        }
    }
    if (idProduct == '') {
        var el = $(element.parentNode);
        var child = el.getElementsByClassName(addToLinc)[0];
        if (child) {
            var childNext = child.childElements()[0];
            if (childNext) {
                var childNext = childNext.childElements()[0];
            }
            if (childNext) {
                var idProduct = childNext.href.match(/product(.?)+/)[0].replace(/[^\d]/gi, '');
            }
            if (parseInt(idProduct) > 0) {
                var tmp = parseInt(idProduct);
                ajaxCartObj.sendAjax(tmp, '', amEvent, element);
                return true;
            } else {
                idProduct = '';
            }
        }
    }
    /*
     * Added by: Tran Trong Thang
     * Email: trantrongthang1207@gmail.com
     */
    if (jQuery(element).is('.addtocartlist')) {
        idProduct = jQuery(element).closest('.wrapper-item, .tvitem').find("input[name='product']").val();
        if (parseInt(idProduct) > 0) {
            var tmp = parseInt(idProduct);
            ajaxCartObj.sendAjax(tmp, '', amEvent, element);
            return true;
        }

    }
    if (idProduct == '' && $$("input[name='product']")[0] && $$("input[name='product']")[0].value) {
        idProduct = $$("input[name='product']")[0].value;
        if (parseInt(idProduct) > 0) {
            var tmp = parseInt(idProduct);
            ajaxCartObj.sendAjax(tmp, '', amEvent, element);
            return true;
        }
    }

    if (idProduct == '' && amEvent) {
        var productString = '/product/';
        var posStart = amEvent.indexOf(productString);
        if (posStart) {
            var posFinish = amEvent.indexOf('/', posStart + productString.length);
            if (posFinish) {
                var idProduct = amEvent.substring(posStart + productString.length, posFinish);
                if (parseInt(idProduct) > 0) {
                    var tmp = parseInt(idProduct);
                    ajaxCartObj.sendAjax(tmp, '', amEvent, element);
                    return true;
                } else {
                    idProduct = '';
                }
            }
        }
    }
    if (idProduct == '') {
        //if IE7
        if (document.all && !document.querySelector) {
            amEvent = amEvent.substring(21, amEvent.length - 2)
        }
        eval(amEvent);
    }
}
;

function ajaxCartShoppCartLoad(buttonClass) {
    $$(buttonClass).each(function (element) {
        if (element.getAttribute('onclick')) {
            var attr = document.createAttribute('amEvent');
            attr.value = element.getAttribute('onclick').toString();
            element.attributes.setNamedItem(attr);
        }
        jQuery(element).attr('onclick', 'return false;');
        element.stopObserving('click');
        Event.observe(element, 'click', searchIdAndSendAjax);

    }.bind(this));
}
;

document.observe("dom:loaded", function () {
    ajaxCartObj.createPopup();
    ajaxCartShoppCartLoad('.btn-cart');
    ajaxCartShoppCartLoad('.link-cart');
});
function changeDeleteCartToAjax() {
    jQuery('.block-cart .btn-remove').each(function () {
        var $onclick = jQuery(this).attr('onclick').replace('return ', '');
        jQuery(this).attr('arwEvent', $onclick).removeAttr('onclick');
    })
}
;
jQuery(document).ready(function () {
    changeDeleteCartToAjax();
})
jQuery(document).on('click', '.block-cart .btn-remove', function (e) {
    e.preventDefault();
    var _this = jQuery(this),
            href = _this.attr('href'),
            href = href.split('cart/delete/id/'),
            product_id = href[1].match(/[0-9]+/),
            delUrl = ajaxCartObj.url.replace('ajaxcart/ajax/index', 'ajaxcart/ajax/delete');
    var is_return = function () {
        eval(_this.attr('arwEvent'));
    };
    if (eval(_this.attr('arwEvent'))) {
        jQuery.ajax({
            type: 'post',
            dataType: 'json',
            data: 'id=' + product_id,
            url: delUrl,
            beforeSend: function () {
                jQuery(".arw-ajaxcart-loader").show();
            },
            success: function (data, status) {
                ajaxCartObj.showPopup(data.message, 1);
                ajaxCartObj.updateSidebarCart();
                ajaxCartObj.updateShoppingCart();
                ajaxCartObj.updateMinicart();
                ajaxCartObj.updateCount(data.cart_count);
            },
            complete: function () {
                jQuery(".arw-ajaxcart-loader").hide();
            }
        });
    }
});

/*function arw_show_image_fly_when_click_addcart($button){
 if($button.length > 0){
 var cart = jQuery('.mini-cart-header'),
 pass = true,
 currentImg = $button.closest('li.item').find('.product-image img:first-child');
 
 if($button.closest('.product-essential').length > 0){
 currentImg = $button.closest('.product-essential').find('.product-image img#image');
 }
 if($button.closest('.col-main-details-style_2').length > 0){
 currentImg = $button.closest('.col-main-details-style_2').find('.arw-fancybox').first().find('img');
 }
 
 if(jQuery('.arexworks-quickview-index').length>0){
 pass = false;
 }
 if (currentImg && pass) {
 var imgclone = currentImg.clone()
 .offset({ top:currentImg.offset().top, left:currentImg.offset().left })
 .addClass('imgfly')
 .css({'opacity':'0.7', 'position':'absolute', 'height':'180px', 'width':'180px', 'z-index':'1000'})
 .appendTo(jQuery('body'))
 .animate({
 'top':cart.offset().top + 10,
 'left':cart.offset().left + 10,
 'width':55,
 'height':55
 }, 1000);
 imgclone.animate({'width':0, 'height':0});
 }
 }
 };*/