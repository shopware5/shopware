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

/**
 * Shopware Model - Voucher model
 * Model for available vouchers
 */
//{block name="backend/newsletter_manager/model/voucher"}
Ext.define('Shopware.apps.NewsletterManager.model.Voucher', {
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
		//{block name="backend/canceled_order/model/voucher/fields"}{/block}
        { name: 'id', type:'int' },
        { name: 'value', type:'string' },
        {
            name: 'description',
            type: 'string',
            convert: function(value, record) {
                if ( value == null) {
                    return value;
                }

                if(record && record.get('id') == -1) {
                    return value;
                }
                return Ext.String.format("{s name=voucherDescription}{literal}{0} ({1}{3}, {2} total){/literal}{/s}", value, record.get('value'), record.get('numberofunits'), record.get('type_sign'));

            }
        },
        { name: 'numberofunits', type:'int' },
        { name: 'type_sign', type:'string' }
    ]
});
//{/block}