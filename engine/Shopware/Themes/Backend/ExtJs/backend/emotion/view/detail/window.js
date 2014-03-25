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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/detail/window"}
Ext.define('Shopware.apps.Emotion.view.detail.Window', {
	extend: 'Enlight.app.Window',
    alias: 'widget.emotion-detail-window',
    border: false,
    resizable: false,
    maximizable: false,
    autoShow: true,
    layout: 'fit',
    height: '90%',
    width: 800,
    stateful: true,
    stateId: 'emotion-detail-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this, settings, elements;

        var shopwareComponents = me.getShopwareComponents();
        var pluginComponents = me.getPluginComponents();

        // Create the data store
        var store = Ext.create('Ext.data.Store', {
            fields: [
                'headline', 'children'
            ],
            data: [{
                headline: '{s name=window/default_elements}Default elements{/s}',
                children: shopwareComponents
            }, {
                headline: '{s name=window/third_party_elements}Third party elements{/s}',
                children: pluginComponents
            }]
        });

        settings = me.emotion.data;
        if (me.emotion.getGrid() instanceof Ext.data.Store && me.emotion.getGrid().first() instanceof Ext.data.Model) {
            var gridModel = me.emotion.getGrid().first();
            settings.rows = gridModel.get('rows');
            settings.cols = gridModel.get('cols');
            settings.cellHeight = gridModel.get('cellHeight');
            settings.articleHeight = gridModel.get('articleHeight');
        }

        elements = me.emotion.getElements();

        if (elements instanceof Ext.data.Store && elements.data.length > 0) {
            elements = elements.data.items;
        } else {
            elements = [];
        }

        // Set the title
        if(elements.length) {
            me.title = '{s name=window/title_edit}Edit emotion{/s}';
        } else {
            me.title = '{s name=window/title}New emotion{/s}';
        }

        me.dataviewStore = Ext.create('Ext.data.Store',{
            fields: ['settings', 'elements'],
            data: [{
                settings: settings,
                elements: elements
            }]
        });

        me.tabPanel = me.createTabPanel();
        me.items = [ me.tabPanel ];

        me.hubPlugin = Ext.create('Shopware.window.plugin.Hud', {
            hudStore: store,
            originalStore: me.libraryStore,
            hudOffset: 0,
            hudHeight: 550,
            itemSelector: '.x-library-element',
            tpl: me.createElementLibraryTemplate()
        });
        me.plugins = [ me.hubPlugin ];
        // Build the action toolbar
        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'toolbar',
            ui: 'shopware-ui',
            items: me.createActionButtons()
        }];

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the save button to save the emotion.
             *
             * @event
             * @param [Ext.data.Model] The emotion record
             * @param [Ext.data.Store] The store for the designer tab
             * @param [Ext.form.Panel] The settings panel
             */
            'saveEmotion'
        );
    },

    getShopwareComponents: function() {
        var me = this, components = [];

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get("pluginId") === null;
            }
        });
        return me.libraryStore.data.items;
    },

    getPluginComponents: function() {
        var me = this, components = [];

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get("pluginId") > 0;
            }
        });
        return me.libraryStore.data.items;
    },

    createTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            plain: true,
            activeTab: me.emotion.get('name') ? 0 : 1,
            listeners: {
                scope: me,

                /**
                 * Event handler method which shows/hides the library
                 * panel.
                 *
                 * @event beforetabchange
                 * @param [object] panel - Ext.panel.Panel
                 * @param [object] newCard - Ext.tab.Tab
                 * @return void
                 */
                beforetabchange: function(panel, newCard) {
                    if(newCard.initialTitle === 'settings') {
                        me.libraryPnl.hide();
                    } else {
                        me.libraryPnl.show();
                    }
                }

            },
            items: [{
                xtype: 'emotion-detail-designer',
                initialTitle: 'designer',
                emotion: me.emotion,
                dataviewStore: me.dataviewStore,
                disabled: me.emotion.get('name') ? false : true
            }, {
                xtype: 'emotion-detail-settings',
                initialTitle: 'settings',
                categoryPathStore: me.categoryPathStore,
                emotion: me.emotion,
                dataviewStore: me.dataviewStore
            }]
        });
    },

    createElementLibraryTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="x-library-outer-panel">',
                    '<h2 class="x-library-section-title">',
                        '<div class="x-library-section-inner-title">{headline}:</div>',
                        '<div class="toggle"></div>',
                    '</h2>',
                    '<div class="x-library-inner-panel">',
                        '<ul>',
                            '<tpl for="children">',
                                '<li class="x-library-element" data-componentId="{data.id}">',
                                    '{data.fieldLabel}',
                                '</li>',
                            '</tpl>',
                        '</ul>',
                    '</div>',
                '</div>',
            '</tpl>{/literal}'
        );
    },

    createActionButtons: function() {
        var me = this;

        return ['->', {
            text: '{s name=window/button/save_emotion}Save emotion{/s}',
            cls: 'primary',
            action: 'emotion-detail-settings-save',
            handler: function() {
                me.fireEvent('saveEmotion', me.emotion, me.dataviewStore);
            }
        }];
    }
});
//{/block}