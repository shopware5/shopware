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
 * @package    PluginManager
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/list/home_page"}
Ext.define('Shopware.apps.PluginManager.view.list.HomePage', {
    extend: 'Ext.container.Container',
    alias: 'widget.plugin-manager-home-page',
    cls: 'plugin-manager-listing-page plugin-manager-home-page',

    autoScroll: true,

    initComponent: function () {
        var me = this;

        me.content = Ext.create('Ext.container.Container', {
            padding: '30 0 0',
            items: [
                { xtype: 'component', cls: 'headline', html: '{s name="store_newcomer"}New in the store{/s}' },
                me.createNewcomerListing(),
                { xtype: 'component', cls: 'headline', html: '{s name="ready_for_integration"}Ready for integration{/s}' },
                me.createDummyListing()
            ]
        });

        me.items = [me.content];

        me.callParent(arguments);
    },

    displayContent: function () {
        var me = this;
        this.content.show();
    },

    hideContent: function () {
        var me = this;
        me.content.hide();
    },

    createNewcomerListing: function () {
        var me = this;

        me.newcomerStore = Ext.create('Shopware.apps.PluginManager.store.StorePlugin', {
            pageSize: 5,
            filters: [{
                property: 'newcomer',
                value: true
            }]
        });

        me.newcomerListing = Ext.create('PluginManager.components.Listing', {
            store: me.newcomerStore,
            name: 'new-comer-listing',
            padding: 30,
            width: 1007
        });

        me.newcomerStore.on('load', function() {
            var moreLink = Ext.create('PluginManager.container.Container', {
                html: '<div class="button">{s name="display_all_newcomer"}Display all new{/s}</div>',
                cls: 'more-link',
                handler: function() {
                    me.fireEvent('display-newcomer');
                }
            });

            me.newcomerListing.listingContainer.add(moreLink);
        });

        me.newcomerStore.load();

        return me.newcomerListing;
    },

    createDummyListing: function () {
        var me = this;

        me.dummyStore = Ext.create('Shopware.apps.PluginManager.store.StorePlugin', {
            pageSize: 50,
            filters: [{
                property: 'dummy',
                value: true
            }]
        }).load();

        me.dummyListing = Ext.create('PluginManager.components.Listing', {
            store: me.dummyStore,
            name: 'dummy-listing',
            scrollContainer: me,
            padding: 30,
            width: 1007
        });

        return me.dummyListing;
    }
});
//{/block}