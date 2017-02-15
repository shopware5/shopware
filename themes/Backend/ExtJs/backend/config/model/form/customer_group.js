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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/form/customer_group"}
Ext.define('Shopware.apps.Config.model.form.CustomerGroup', {
    extend: 'Shopware.apps.Base.model.CustomerGroup',

    fields: [
        //{block name="backend/config/model/form/customer_group/fields"}{/block}
        { name: 'minimumOrder', type: 'float', useNull:true },
        { name: 'minimumOrderSurcharge', type: 'float', useNull:true },
        { name: 'deletable', type: 'boolean', convert: function(v, r) { return r.data.key != 'EK'; } }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.Config.model.form.CustomerDiscount',
        name: 'getDiscounts',
        associationKey: 'discounts'
    }]
});
//{/block}
//{block name="backend/config/model/form/customer_discount"}
Ext.define('Shopware.apps.Config.model.form.CustomerDiscount', {
    extend: 'Ext.data.Model',

    fields: [
        'discount', 'value'
    ]
});
//{/block}
