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
 * @package    Shipping
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/default}*/

/**
 * Shopware Store - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/store/country"}
Ext.define('Shopware.apps.Shipping.store.Tax', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend : 'Shopware.apps.Base.store.Tax',

    model : 'Shopware.apps.Shipping.model.Tax',

    listeners: {
        /**
         * Fires whenever records have been prefetched
         * used to add some default values to the combobox
         *
         * @event load
         * @param [object] store - Ext.data.Store
         * @return void
         */
        load: function(store) {
            var defaultTax = Ext.create('Shopware.apps.Shipping.model.Tax',{
                id : 0,
                name : '{s name=right_empty_tax}Highest tax{/s}'
            });
            store.insert(0,defaultTax);
        }
    }
});
//{/block}
