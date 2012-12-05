/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

;(function($) {
    "use strict";
    $(document).ready(function() {
        // Add event listener for the accept configuration button
        $('.add-configuration').live('click', function(event) {
            event.preventDefault();
            var me = $(this);
            var outerContainer = me.parent('.bundle-article-configuration');
            var bundleArticleId = outerContainer.find('.bundle-article-configuration-id').val();
            var requestUrl = outerContainer.find('.request-url').val();
            var params = {
                'bundleArticleId': bundleArticleId
            };

            outerContainer.find('select').each(function(index, item){
                var $item = $(item);
                var key = $item.attr('name');
                params[key] = $item.val();
            });

            $.ajax({
                'url': requestUrl,
                'data': params,
                'dataType': 'html',
                'type': 'POST',
                success: function() {
                    window.location.reload();
                }
            });
        });
    });
})(jQuery);


;(function($) {
    "use strict";
    $(document).ready(function() {
        $('.bundle-container .checkbox').bind('click', function(event) {
            var $this = $(this),
                parent = $this.parents('li.item');

            var bundleContainer = parent.parents('.bundle-container');
            var percentage = bundleContainer.find('input[name=discount-percentage]').val();
            var discountType = bundleContainer.find('input[name=discount-type]').val();
            var checked = $this.is(':checked');
            var articles = $('.bundle-container li.item');
            var selectedAmount = 0;
            var totalAmount = 0;
            var count = 0;

            articles.each(function(index, item) {
                var article = $(item);
                var articleCheckbox = article.find('input.checkbox');
                var articleIsChecked = (articleCheckbox.is(':checked') || index === 0);
                var price = article.find('input[name=price]').val() * 1;

                totalAmount += price;
                if (articleIsChecked) {
                    selectedAmount += price;
                    count++;
                }
            });

            if (count <= 1) {
                event.preventDefault();
                return;
            }

            percentage = percentage.replace(',', '.');

            var discount = 0;
            var bundlePriceValue = bundleContainer.find('.bundle-price-value');
            var bundleDiscountValue = bundleContainer.find('.discount-value');
            var totalPriceValue = bundleContainer.find('.total-price-value');
            var currencyFormat = bundleContainer.find('.currency-helper').text();

            if (discountType === 'pro') {
                discount = selectedAmount / 100 * percentage;
            } else {
                discount = bundleContainer.find('input[name=discount-value-usage]').val();
            }
            var price = selectedAmount - discount;

            price = formatValue(price, currencyFormat);
            discount = formatValue(discount, currencyFormat);
            selectedAmount = formatValue(selectedAmount, currencyFormat);

            bundleDiscountValue.text(discount);
            bundlePriceValue.text(price);
            totalPriceValue.text(selectedAmount);

            parent.toggleClass('checked-item');
        });

        function formatValue(value, template) {
            value = Math.round(value * 100) / 100;
            value = value.toFixed(2);
            value = template.replace('0,00', value);
            value = value.replace('.', ',');
            return value;
        }
    });
})(jQuery);
