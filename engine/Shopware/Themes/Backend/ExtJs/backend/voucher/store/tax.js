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
 * @package    Voucher
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Voucher backend module.
 *
 * The tax store loads and store the Tax model
 */
//{namespace name=backend/voucher/view/tax}
//{block name="backend/voucher/store/tax"}
Ext.define('Shopware.apps.Voucher.store.Tax', {

    /**
    * Extend for the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Store',
    /**
    * Auto load the store after the component
    * is initialized
    * @boolean
    */
    autoLoad: true,

    remoteFilter : true,
    /**
    * Define the used model for this store
    * @string
    */
    model : 'Shopware.apps.Voucher.model.Tax',

    /**
     * A config object containing one or more event handlers to be added to this object during initialization
     * @object
     */
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
            var defaultTaxModel = Ext.create('Shopware.apps.Voucher.model.Tax',{
                id : 'default',
                name : '{s name=detail_general/tax_combo_box/standard}Standard{/s}'
            }),
            autoTaxModel = Ext.create('Shopware.apps.Voucher.model.Tax',{
                id : 'auto',
                name : '{s name=detail_general/tax_combo_box/auto}Auto detection{/s}'
            }),
            noneTaxModel = Ext.create('Shopware.apps.Voucher.model.Tax',{
                id : 'none',
                name : '{s name=detail_general/tax_combo_box/tax_free}Tax-free{/s}'
            });

            //insert the models at first position to the tax combobox
            store.insert(0,autoTaxModel);
            store.insert(0,defaultTaxModel);
            //insert his model to the end of the list
            store.add(noneTaxModel);
        }
    }

});
//{/block}
