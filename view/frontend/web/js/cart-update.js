/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'Magento_Checkout/js/model/totals'
], function (totalsService) {
    'use strict';

    // Monitor the cart totals so we can update messaging with any price changes
    totalsService.totals.subscribe(function () {
        if (document.querySelector('#klarna-osm-placement-product')) {
            // eslint-disable-next-line vars-on-top
            var grandTotal = totalsService.getSegment('grand_total').value,
                price = Math.round(grandTotal * 100);

            document.querySelector('#klarna-osm-placement-product').dataset.purchaseAmount = price;
        }
    });
});
