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
 */

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/list/window"}

Ext.define('Shopware.apps.Theme.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.theme-list-window',
    height: '80%',
    width: '75%',
    title : '{s name=listing}Theme manager{/s}',
    minWidth: 600,


    configure: function() {
        return {
            listingGrid: 'Shopware.apps.Theme.view.list.Theme',
            listingStore: 'Shopware.apps.Theme.store.Theme',

            extensions: [
                { xtype: 'theme-listing-info-panel' }
            ]
        };
    },

    initComponent: function() {
        var me = this;

        me.dockedItems = [
            me.createToolbar()
        ];

        me.callParent(arguments);
    },

    /**
     * Following functions creates the toolbar elements
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            ui: 'shopware-ui',
            dock: 'top'
        });

        return me.toolbar;
    },

    createToolbarItems: function () {
        var me = this,
            items = [];

        items.push({ xtype: 'tbspacer', width: 6 });
        items.push(me.createShopCombo());
        items.push({ xtype: 'tbspacer', width: 12 });
        /*{if {acl_is_allowed privilege=createTheme}}*/
        items.push(me.createAddButton());
        /*{/if}*/
        items.push(me.createRefreshButton());
        /*{if {acl_is_allowed privilege=configureSystem}}*/
        items.push(me.createSettingsButton());
        /*{/if}*/
        items.push('->');
        items.push(me.createSearchField());

        return items;
    },

    createShopCombo: function () {
        var me = this;

        me.shopStore = Ext.create('Shopware.apps.Base.store.Shop').load({
            callback: function(records) {
                var first = records.shift();
                me.shopCombo.select(first);
            }
        });

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'shop',
            fieldLabel: '{s name=shop_combo}Template-Auswahl für Shop{/s}',
            editable: false,
            labelWidth: 175,
            labelStyle: 'margin-top: 2px',
            store: me.shopStore,
            displayField: 'name',
            valueField: 'id',
            listeners: {
                select: function() {
                    me.fireEvent('shop-changed', me);
                }
            }
        });

        return me.shopCombo;
    },

    createAddButton: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: '{s name=create}Create theme{/s}',
            iconCls: 'sprite-application--plus',
            handler: function() {
                me.fireEvent('create-theme', me);
            }
        });

        return me.addButton;
    },

    createRefreshButton: function() {
        var me = this;

        me.refreshButton = Ext.create('Ext.button.Button', {
            text: '{s name=refresh}Refresh list{/s}',
            iconCls: 'sprite-arrow-circle-135',
            handler: function() {
                me.fireEvent('refresh-list', me);
            }
        });

        return me.refreshButton;
    },

    createSettingsButton: function() {
        var me = this;

        me.settingsButton = Ext.create('Ext.button.Button', {
            text: '{s name=settings}Settings{/s}',
            iconCls: 'sprite-gear',
            handler: function() {
                me.fireEvent('open-settings', me);
            }
        });

        return me.settingsButton;
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            width: 170,
            emptyText: '{s name=search}Search ...{/s}',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function (field, value) {
                    me.fireEvent('search-theme', me, field, value);
                }
            }
        });

        return me.searchField;
    }

});

//{/block}
