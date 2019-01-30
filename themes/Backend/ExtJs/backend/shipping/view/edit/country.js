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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/country}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/country"}
Ext.define('Shopware.apps.Shipping.view.edit.Country', {
    /**
     * Based on Ext.panel.Panel
     */
    extend: 'Ext.container.Container',

    /**
     * Alias for easy creation
     */
    alias: 'widget.shipping-view-edit-country',

    /**
     * Name of this view
     */
    name: 'shipping-view-edit-country',

    /**
     * Title as shown in the tab from the panel
     */
    title: '{s name=country_selection_tab_title}Lock categories{/s}',

    /**
     * Display the the contents of this tab immediately
     */
    autoShow: true,

    /**
     * Use the full height
     */
    height: '100%',

    /**
     * Uses the column layout
     */
    layout: {
        type: 'column',
        align: 'stretch',
        padding: 5
    },

    /**
     * Defaults
     */
    defaults: {
        columnWidth: 1
    },

    /**
     * Stores the data dragged into the right hand side grid and the data already assigned to this dispatch
     */
    usedCountriesStore: null,

    /**
     * Contains all known countries
     */
    availableCountries: null,

    /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent : function() {
        var me = this,
            ids = [];

        // Store the already assigned countries to the usedCountriesStore
        me.usedCountriesStore = me.record.getCountries();

        // Build a list of ids to filter them out of the available country store - this is done
        // through php
        me.usedCountriesStore.each(function(element) {
            ids.push(element.get('id'));
        });
        me.availableCountries.remoteSort = true;
        me.availableCountries.filters.clear();
        me.availableCountries.filter('usedIds', ids);
        me.availableCountries.sort([
            {
                property: 'active',
                direction: 'DESC'
            },
            {
                property: 'name',
                direction: 'ASC'
            },
        ]);
        me.usedCountriesStore.sort([
            {
                property: 'active',
                direction: 'DESC'
            },
            {
                property: 'name',
                direction: 'ASC'
            },
        ]);

        // Create the view
        me.items = me.getItems();
        me.callParent(arguments);
    },

    /**
     * Returns all needed items to the parent container
     *
     * @return Array
     */
    getItems: function () {
        var me = this;
        return [
            {
                xtype:'ddselector',
                fromStore: me.availableCountries,
                buttons: ['add', 'remove'],
                gridHeight:200,
                selectedItems: me.usedCountriesStore,
                fromFieldDockedItems:[],

                /**
                 * FromTitle which holds Title on the Left Side
                 *
                 * @string
                 */
                fromTitle: '{s name=tab/country/from_title}Available{/s}',

                /**
                 * toTitle which holds Title on the Right Side
                 *
                 * @string
                 */
                toTitle: '{s name=tab/country/to_title}Selected{/s}'
            }
        ];
    }
});
//{/block}
