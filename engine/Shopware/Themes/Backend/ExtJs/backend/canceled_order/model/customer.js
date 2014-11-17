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
 * Shopware Model - Customer Model
 * Extends the customer base model, implements BillingAddress association
 */
//{block name="backend/canceled_order/model/customer"}
Ext.define('Shopware.apps.CanceledOrder.model.Customer', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.apps.Base.model.Customer',

    /**
     * Define the associations of the customer model.
     * One customer can have many billing addresses
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.Base.model.BillingAddress', name:'getBilling', associationKey:'billing' }
    ]
});
//{/block}
