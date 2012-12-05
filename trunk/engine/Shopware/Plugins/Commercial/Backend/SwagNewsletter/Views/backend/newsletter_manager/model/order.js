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
 * @package    NewsletterManager
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_newsletter/main"}


/**
 * Shopware Model - NewsletterManager backend module.
 */
//{block name="backend/newsletter_manager/model/order"}
Ext.define('Shopware.apps.NewsletterManager.model.Order', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
    * The fields used for this model
    * @array
    */
    fields: [
		//{block name="backend/order/model/order/fields"}{/block}
        { name : 'id', type: 'int' },
        { name : 'customerId', type: 'int' },
        { name : 'newsletterId', type: 'int' },
        { name : 'customer', type: 'string' },
        { name : 'status', type: 'int' },
        { name : 'cleared', type: 'int' },
        { name : 'partnerId', type: 'int' },
        { name : 'subject', type: 'string' },
        { name : 'currency', type: 'string' },
        { name : 'currencyFactor', type: 'float' },
        { name : 'shopId', type: 'int' },
        { name : 'invoiceAmount', type: 'float' },
        { name : 'orderTime', type: 'date', dateFormat: 'Y-m-d H:i:s' },
        { name : 'newsletterDate', type: 'date' },
        {
            name : 'invoiceAmountEuro',
            type: 'float',
            convert: function(value, record) {
                var factor = record.get('currencyFactor');
                if (!Ext.isNumeric(factor)) {
                    factor = 1;
                }
                return Ext.util.Format.round(record.get('invoiceAmount') / factor, 2);
            }
        },
        {
            name: 'grouping',
            type: 'string',
            convert: function(value, record) {
                if(record) {
                    var subject = record.get('subject');
                    if(!subject) {
                        subject = "{s name='newsletterNotFound'}Unknown Newsletter{/s}";
                        return Ext.String.format('<i>[0]</i> - (ID: [1])', subject, record.get('partnerId'));
                    }

                    return Ext.String.format('[2] - &laquo;<i>[0]</i>&raquo; - (ID: [1])', subject, record.get('partnerId'), Ext.util.Format.date(record.get('newsletterDate')));
                }
            }
        }
    ]

});
//{/block}