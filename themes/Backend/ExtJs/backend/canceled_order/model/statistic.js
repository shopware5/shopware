/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * @package    CanceledOrder
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - statistics model
 * Model for the statistics store
 */
//{block name="backend/canceled_order/model/statistic"}
Ext.define('Shopware.apps.CanceledOrder.model.Statistic', {
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
        //{block name="backend/canceled_order/model/statistic/fields"}{/block}
        {
            name: 'paymentName',
            type: 'string',
            // In case of invalid paymentIds, 'null' is returned
            // replace that with a message.
            // This issue should only occure with the test data
            convert: function(value, record) {
                if ( value == null) {
                    return 'Unknown Payment';
                }
                return  value;
            }
        },
        { name: 'number', type: 'int' },
    ],
});
//{/block}
