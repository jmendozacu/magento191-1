//<![CDATA[
jQuery(document).ready(function () {

    jQuery(document).on('click', '.btn-cart', function () {

           var productId = jQuery(this).data("id");
           var productName = jQuery(this).data("name");

           dataLayer.push({
               'event': 'addToCart',
               'ecommerce': {
                   'currencyCode': 'AUD',
                   'add': {
                       'products': [{
                           'name': productName,
                           'id': productId,
                           'price': '',
                           'brand': '',
                           'category': '',
                           'variant': '',
                           'quantity': 1
                       }]
                   }
               }
           });
       });

    jQuery(document).on('click', '.add-to-basket', function () {

        var productId = jQuery(this).data("id");
        var productName = jQuery(this).data("name");

        dataLayer.push({
            'event': 'addToCart',
            'ecommerce': {
                'currencyCode': 'AUD',
                'add': {
                    'products': [{
                        'name': productName,
                        'id': productId,
                        'price': '',
                        'brand': '',
                        'category': '',
                        'variant': '',
                        'quantity': 1
                    }]
                }
            }
        });
    });
});


//]]>