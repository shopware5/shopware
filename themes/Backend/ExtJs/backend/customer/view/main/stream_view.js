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
 * @package    Customer
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/main/stream_view"}

Ext.define('Shopware.apps.Customer.view.main.StreamView', {

    extend: 'Ext.panel.Panel',

    title: '{s name="stream_view_title"}{/s}',

    cls: 'customer-stream-view',

    layout: 'border',

    alias: 'widget.stream-view',

    activated: false,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createDockedItems();

        me.gridPanel.on('afterrender', function() {
            me.gridPanel.getEl().on('click', Ext.bind(me.onSelectInlineStream, me), me, {
                delegate: '.stream-inline'
            });
        });

        me.indexSearchNoticeTooltip = Ext.create('Ext.tip.ToolTip', {
            shadow: false,
            ui: 'shopware-ui',
            cls: 'stream-index-notice-tooltip',
            html: '{s name="index_notice"}{/s}'
        });

        me.on('activate', function() {
            me.listStore.load();

            if (!me.activated) {
                me.activated = true;
                me.fireEvent('tab-activated');
            }
        });
        me.callParent(arguments);
    },

    createDockedItems: function() {
        return [this.toolbar = this.createToolbar()];
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                me.createLayoutButton(),
                '->',
                me.createAutoIndexCheckbox(),
                { xtype: 'tbspacer', width: 10 },
                me.createIndexButton(),
                { xtype: 'tbspacer', width: 10 },
                me.createProgressBar(),
                { xtype: 'tbspacer', width: 10 }
            ]
        });
    },

    createLayoutButton: function() {
        var me = this;

        return me.layoutButton = Ext.create('Ext.button.Cycle', {
            text: '{s name="switch_layout"}{/s}',
            action: 'layout',
            listeners: {
                change: Ext.bind(me.onChangeLayout, me)
            },
            menu: {
                items: [{
                    text: '{s name=view_table}{/s}',
                    layout: 'table',
                    iconCls: 'sprite-table',
                    checked: true
                }
                /*{if {acl_is_allowed resource=customerstream privilege=charts}}*/
                , {
                    text: '{s name=view_chart}{/s}',
                    layout: 'amount_chart',
                    iconCls: 'sprite-chart-up'
                }, {
                    text: '{s name=view_chart_stream}{/s}',
                    layout: 'stream_chart',
                    iconCls: 'sprite-chart-impressions'
                }
                /*{/if}*/
                ]
            }
        })
    },
    
    createAutoIndexCheckbox: function() {
        var me = this, value = false;

        if (me.subApp.userConfig && me.subApp.userConfig.autoIndex) {
            value = true;
        }

        return me.autoIndexCheckbox = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: '{s name="auto_index"}{/s}',
            name: 'autoIndex',
            inputValue: true,
            uncheckedValue: false,
            /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
                hidden: true,
            /*{/if}*/

            /*{if !{acl_is_allowed resource=customerstream privilege=stream_index}}*/
                hidden: true,
            /*{/if}*/

            value: value,
            checked: value,
            listeners: {
                'change': Ext.bind(me.onOnChangeAutoIndex, me)
            }
        });
    },

    createIndexButton: function() {
        var me = this;

        return me.indexSearchButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-blue-document-search-result',
            text: '{s name=analyse_customer}{/s}',
            action: 'index',
            /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
                hidden: true,
            /*{/if}*/

            handler: Ext.bind(me.onIndexSearch, me)
        });
    },

    createProgressBar: function() {
        var me = this;

        me.indexingBar = Ext.create('Ext.ProgressBar', {
            value: 0,
            height: 20,
            width: 360
        });

        me.indexingBar.addCls('empty');

        return me.indexingBar;
    },

    createItems: function() {
        var me = this;

        me.listStore = Ext.create('Shopware.apps.Customer.store.Preview');
        me.streamStore = Ext.create('Shopware.apps.Customer.store.CustomerStream', {
            sorters: [
                { property: 'name', direction: 'ASC'}
            ]
        }).load();

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.Preview', {
            store: me.listStore,
            margin: 10,
            border: true
        });

        me.streamListing = Ext.create('Shopware.apps.Customer.view.customer_stream.Listing', {
            store: me.streamStore,
            subApp: me.subApp,
            hideHeaders: true,
            border: true,
            title: '{s name=stream_listing}{/s}',
            height: 200,
            listeners: {
                'selectionchange': Ext.bind(me.onSelectStream, me)
            }
        });

        me.saveStreamButton = Ext.create('Ext.button.Button', {
            text: '{s name="save"}{/s}',
            cls: 'primary',
            disabled: true,
            /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
                hidden: true,
            /*{/if}*/
            handler: Ext.bind(me.onSaveStream, me)
        });

        me.saveNewStreamButton = Ext.create('Ext.button.Button', {
            text: '{s name="save_new"}{/s}',
            cls: 'secondary',
            disabled: true,
            /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
                hidden: true,
            /*{/if}*/
            handler: Ext.bind(me.onSaveNewStream, me)
        });

        me.filterPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.ConditionPanel', {
            flex: 1,
            border: false
        });

        me.addConditionButton = Ext.create('Ext.button.Split', {
            text: '{s name="add_condition"}{/s}',
            iconCls: 'sprite-plus-circle-frame',
            menu: me.createConditionsMenu()
        });

        me.refreshViewButton = Ext.create('Ext.button.Button', {
            text: '{s name=refresh_preview}{/s}',
            iconCls: 'sprite-arrow-circle-225-left',
            handler: Ext.bind(me.onRefreshView, me)
        });

        me.saveButtonContainer = Ext.create('Ext.container.Container', {
            padding: 5,
            cls: 'stream-save-button-container',
            layout: { type: 'vbox', align: 'stretch' },
            items: [
                me.saveStreamButton,
                { xtype: 'container', height: 5 },
                me.saveNewStreamButton
            ]
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            flex: 4,
            bodyCls: 'stream-filter-panel-body',
            layout: { type: 'vbox', align: 'stretch' },
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'top',
                ui: 'shopware-ui',
                cls: 'condition-toolbar',
                border: true,
                items: [ me.addConditionButton, '->', me.refreshViewButton ]
            }],
            items: [ me.filterPanel, me.saveButtonContainer ]
        });

        me.metaChart = Ext.create('Shopware.apps.Customer.view.chart.MetaChart');

        me.metaChartStore = me.metaChart.store;

        me.streamChartContainer = Ext.create('Ext.container.Container', {
            items: [],
            flex: 1,
            cls: 'stream-chart-container',
            layout: 'border'
        });

        me.streamDetailForm = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            margin: 10,
            autoScroll: true,
            name: 'detail-form',
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'shopware-ui',
                cls: 'stream-detail-form-toolbar',
                items: ['->', me.createSaveStreamDetailButton()]
            }]
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [ me.gridPanel, me.metaChart, me.streamChartContainer, me.streamDetailForm ],
            region: 'center',
            layout: 'card'
        });

        me.leftContainer = Ext.create('Ext.container.Container', {
            region: 'west',
            width: 400,
            margin: '10 0 10 10',
            layout: { type: 'vbox', align: 'stretch' },
            items: [
                me.formPanel,
                { xtype: 'container', height: 10 },
                me.streamListing
            ]
        });
        return [ me.leftContainer, me.cardContainer ];
    },

    resetFilterPanel: function() {
        this.filterPanel.removeAll();
        this.filterPanel.loadRecord(null);
        this.formPanel.loadRecord(null);
        this.saveStreamButton.setDisabled(true);
        this.saveNewStreamButton.setDisabled(true);
    },

    createConditionsMenu: function() {
        var me = this, items = [];

        Ext.each(me.filterPanel.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: function() {
                    me.addCondition(handler);
                }
            });
        });

        return new Ext.menu.Menu({ items: items });
    },

    createSaveStreamDetailButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: '{s name=save}{/s}',
            cls: 'primary',
            handler: Ext.bind(me.onSaveStreamDetails, me)
        });
    },

    onSelectInlineStream: function(event, element) {
        var me = this;

        element = Ext.get(element);
        event.preventDefault();

        me.streamListing.getSelectionModel().select([
            me.streamListing.getStore().getById(
                window.parseInt(element.getAttribute('data-id'))
            )
        ]);
    },

    onChangeLayout: function (button, item) {
        this.fireEvent('switch-layout', item.layout);
    },

    onSaveStreamDetails: function () {
        this.fireEvent('save-stream-details');
    },

    onSelectStream: function(selModel, selection){
        this.fireEvent('stream-selected', selection);
    },

    onOnChangeAutoIndex: function(checkbox, newValue) {
        this.fireEvent('change-auto-index', checkbox, newValue);
    },

    onIndexSearch: function () {
        this.fireEvent('full-index');
    },

    onSaveStream: function() {
        this.fireEvent('save-edited-stream');
    },

    onSaveNewStream: function() {
        this.fireEvent('save-as-new-stream');
    },

    onRefreshView: function() {
        this.fireEvent('refresh-stream-views');
    },

    addCondition: function(handler) {
        this.saveNewStreamButton.setDisabled(false);
        this.filterPanel.createCondition(handler);
        this.fireEvent('condition-added');
    }
});
// {/block}
