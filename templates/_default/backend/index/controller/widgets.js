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
 * @package    Index
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/index/view/widgets}

/**
 * Shopware UI - Main Backend Application Bootstrap
 *
 * This file bootstrapps the widget desktop holder container.
 */
//{block name="backend/index/controller/widgets"}
Ext.define('Shopware.apps.Index.controller.Widgets', {
    extend: 'Enlight.app.Controller',

    /**
     * Shopware viewport which holds the whole application.
     * @default null
     * @Ext.container.Viewport
     */
    viewport: null,

    /**
     * Dashboard desktop, which will be contain the widgets.
     * @default null
     * @Ext.container.Container
     */
    desktop: null,

    /**
     * Widget holder which contains the widget columns.
     * @default null
     * @Shopware.apps.Index.view.widgets.Desktop
     */
    widgetHolder: null,

    /**
     * Contains all stores which are necessary for the
     * activated widgets.
     *
     * @array
     */
    necessaryStores: [],

    /**
     * Counts the loaded stores to terminate if all dependencies are loaded.
     * @integer
     */
    loadedStores: 0,

    /**
     * Indicates that all stores are loaded.
     * @boolean
     */
    fullyLoaded: false,

    /**
     * Snippets for the widget controller.
     * @object
     */
    snippets: {
        titles: {
            allow_merchant: '{s name=titles/allow_merchant}Unlock merchant{/s}',
            decline_merchant: '{s name=titles/decline_merchant}Decline merchant{/s}'
        }
    },

    /**
     * Initiliazes the widget system and sets the global settings
     * for the widget system (like the rendered viewport, the
     * active desktop and so on...).
     *
     * @public
     * @return void
     */
    init: function() {
        var me = this;
        me.viewport = Shopware.app.Application.viewport;

        // Raise error if the viewport isn't available
        if(!me.viewport) {
            Ext.Error.raise('Viewport is not loaded');
        }
        me.desktop = me.viewport.getDesktop(0);

        me.control({
            'widgets-container': {
                savePosition: me.onSavePosition
            },
            'swag-merchant-widget': {
                allowMerchant: function( record) {
                    me.onOpenMerchantDetail('allow', record);
                },
                declineMerchant: function(record) {
                    me.onOpenMerchantDetail('decline', record);
                }
            }
        });

        me.viewport.on('resize', me.onResizeDesktop, me);

        me.renderViewport(false);
        me.callParent(arguments);
    },

    /**
     * Renders the widget viewport, which contains a container
     * for each column.
     *
     * @public
     * @return void
     */
    renderViewport: function(resize) {
        var me = this;
        resize = resize || false;

        me.subApplication.widgetStore = me.getStore('Widget').load({
            callback: function() {

                if(!me.subApplication.widgetStore.getCount()) {
                    me.viewport.un('resize', me.onResizeDesktop);
                    return false;
                }

                if(!resize) {
                    Ext.each(me.subApplication.widgetStore.data.items, function(item) {
                        var name = item.get('name');
                        me.terminateStore(name);
                    });
                    me.loadNeccessaryStores();
                } else {
                    me.allStoresLoaded();
                }
            }
        });
    },

    loadNeccessaryStores: function() {
        var me = this;
        Ext.each(me.necessaryStores, function(item) {
            item.store.load();
        });
    },

    /**
     * Terminates the necessary stores for the activate widgets and loads them.
     *
     * Please note that this method just only needs to call one time.
     *
     * @public
     * @param [string] name - Alias name of the widget
     */
    terminateStore: function(name) {
        var me = this, store;

        switch(name) {
            case 'swag-sales-widget':
                store = me.getStore('Turnover');
                store.on('load', me.storeLoaded, me, { single: true });
                me.necessaryStores.push({ name: 'turnoverStore', store: store });
                break;
            case 'swag-visitors-customers-widget':
                store = me.getStore('Visitors');
                store.on('load', me.storeLoaded, me, { single: true });
                me.necessaryStores.push({ name: 'visitorsStore', store: store });
                break;
            case 'swag-last-orders-widget':
                store = me.getStore('Orders');
                store.on('load', me.storeLoaded, me, { single: true });
                me.necessaryStores.push({ name: 'ordersStore', store: store });
                break;
            case 'swag-merchant-widget':
                store = me.getStore('Merchant');
                store.on('load', me.storeLoaded, me, { single: true });
                me.necessaryStores.push({ name: 'merchantStore', store: store });
                break;
            case 'swag-notice-widget':
            case 'swag-upload-widget ':
            default:
                break;
        }
    },

    /**
     * Event listener method which will be called when
     * a widget store is loaded.
     *
     * @private
     * @param [object] data - loaded data
     * @param [object] operation fired Ext.data.Operation
     * @param [boolean] success - If truthy the request was successful.
     */
    storeLoaded: function(data, operation, success) {
        var me = this;

        me.loadedStores++;
        if(me.necessaryStores.length === me.loadedStores) {
            me.allStoresLoaded();
        }
    },

    /**
     * Method which will be called when all widget stores
     * are loaded.
     *
     * @public
     * @return void
     */
    allStoresLoaded: function() {
        var me = this, desktop = me.desktop, config = {
            columnCount: me.getColumnCount(),
            widgetStore: me.subApplication.widgetStore,
            subApplication: me.subApplication
        };

        if(!me.fullyLoaded) {
            Ext.each(me.necessaryStores, function(store) {
                config[store.name] = store.store;
            });
            me.widgetHolder = me.getView('widgets.Desktop').create(config);
            me.isViewportRendered = true;

            desktop.add(me.widgetHolder);
            me.fullyLoaded = true;
        }
    },

    /**
     * Terminates the column count based on the real screen estate
     * of the user's screen to improve the user expericence for
     * all kinds of displays.
     *
     * @public
     * @return [integer]
     */
    getColumnCount: function() {
        var width = Ext.dom.Element.getViewportWidth();

        // Large screens like an cinema display
        if(width > 1920) {
            return 4;
        // WXGA resolution
        } else if(width > 1440) {
            return 3;

        }
        // Normal screens
        return 2;
    },

    /**
     * Event listener method which will be fired when the user
     * resizes the browser chrome / frame.
     *
     * Will kick off the widget holder container and re-renders the
     * whole widget system on the first desktop.
     *
     * @event resize
     * @public
     * @return void
     */
    onResizeDesktop: function() {
        var me = this;

        if(me.widgetHolder) {
            me.widgetHolder.hide();
            me.renderViewport(true);
        }
    },

    /**
     * Event listener method which will be fired when then user drops an
     * widget to another column.
     *
     * The method sends an AJAX request to save the new position on the server side.
     *
     * @param [integer] column - Id of the new column
     * @param [integer] row - Id of the new row
     * @param [integer] widgetId - Id of the widget
     * @param [integer] authId - Id of the authentificated user
     * @param [integer] interalId - Id of the widet view (e.g. the rendered widget)
     */
    onSavePosition: function(column, row, widgetId, authId, interalId) {
        Ext.Ajax.request({
            url: '{url controller=widgets action=savePosition}',
            params: {
                column: column,
                position: row,
                id: interalId
            }
        });
    },

    /**
     * Event listener method which will be called after the user
     * clicks on the "allow" or "decline" icon in the action column.
     *
     * Opens the detail window to send an email to the customer.
     *
     * @public
     * @event click
     * @param [string] mode - Allow or decline
     * @param [object] record - Shopware.apps.Index.model.Merchant
     * @return void
     */
    onOpenMerchantDetail: function(mode, record) {
        var me = this, win;

        Ext.Ajax.request({
            url: '{url controller=widgets action=requestMerchantForm}',
            params: {
                id: ~~(1 * record.get('id')),
                customerGroup: record.get('validation'),
                mode: mode
            },
            success: function(response) {
                var model =  me.getModel('MerchantMail');
                response = Ext.decode(response.responseText);
                model = model.create(response.data);

                win = me.getView('merchant.Window').create({
                    record: model,
                    mode: mode,
                    title: (mode === 'allow') ? me.snippets.titles.allow_merchant : me.snippets.titles.decline_merchant
                }).show();
            }
        });
    }
});
//{/block}