/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    CanceledOrder
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Voucher model
 * Model for available vouchers
 */
//{block name="backend/canceled_order/model/voucher"}
Ext.define('Shopware.apps.CanceledOrder.model.Voucher', {
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
        { name: 'id', type: 'int' },
        { name: 'description', type: 'string' },
        { name: 'percental', type: 'int' },
        {
            name: 'value',
            type: 'string',
            convert: function(value, record) {
                if ( value == null) {
                    return value;
                }

                if (record && record.get('id') == -1) {
                    return value;
                }
                if (record.get('percental') == 1) {
                    return Ext.String.format('{s name="sendVoucherWorthPercent"}Send voucher worth [0]% vouchername: [1]{/s}', value, record.get('description'));
                } else {
                    return Ext.String.format('{s name="sendVoucherWorth"}Send voucher worth [0] vouchername: [1]{/s}', value, record.get('description'));
                }
            }
        }
    ]
});
//{/block}
