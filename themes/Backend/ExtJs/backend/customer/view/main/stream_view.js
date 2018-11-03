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
        this.toolbar = this.createToolbar();
        return [this.toolbar];
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

        me.layoutButton = Ext.create('Ext.button.Cycle', {
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
        });
        return me.layoutButton;
    },

    createAutoIndexCheckbox: function() {
        var me = this, value = false;

        if (me.subApp.userConfig && me.subApp.userConfig.autoIndex) {
            value = true;
        }

        me.autoIndexCheckbox = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: '{s name="auto_index"}{/s}',
            name: 'autoIndex',
            inputValue: true,
            uncheckedValue: false,
            /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
                hidden: true,
            /*{/if}*/

            /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
                hidden: true,
            /*{/if}*/

            value: value,
            checked: value,
            listeners: {
                'change': Ext.bind(me.onOnChangeAutoIndex, me)
            }
        });
        return me.autoIndexCheckbox;
    },

    createIndexButton: function() {
        var me = this;

        me.indexSearchButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-blue-document-search-result',
            text: '{s name=analyse_customer}{/s}',
            action: 'index',
            /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
                hidden: true,
            /*{/if}*/

            handler: Ext.bind(me.onIndexSearch, me)
        });
        return me.indexSearchButton;
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
                { property: 'stream.name', direction: 'ASC' }
            ],
            pageSize: 50000,
            listeners: {
                'beforeload': function (store, operation) {
                    if (!operation.forceReload) {
                        operation.addRecords = true;
                    }
                }
            }
        }).load();

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.Preview', {
            store: me.listStore,
            border: true,
            margin: '0 0 0 5',
            flex: 1,
            displayDeleteIcon: false
        });

        var customerStore = Ext.create('Shopware.attribute.SelectionFactory').createEntitySearchStore('Shopware\\Models\\Customer\\Customer');
        me.addCustomerToStreamSelection = Ext.create('Shopware.form.field.CustomerSingleSelection', {
            store: customerStore,
            labelWidth: 150,
            margin: '0 0 0 5',
            disabled: true,
            width: '100%',
            padding: 0,
            listeners: {
                'beforeselect': function(combo, record) {
                    me.fireEvent('add-customer-to-stream', record);
                    return false;
                },
                'collapse': function() {
                    me.listStore.load();
                },
                'disable': function (elem) {
                    if (elem.items) {
                        elem.items.each(function(child) { child.disable(); });
                    }
                },
                'enable': function (elem) {
                    if (elem.items) {
                        elem.items.each(function(child) { child.enable(); });
                    }
                }
            }
        });
        me.addCustomerToStreamSelection.combo.emptyText = '{s name="add_customer"}{/s}';

        me.gridContainer = Ext.create('Ext.container.Container', {
            layout: { type: 'vbox', align: 'stretch' },
            items: [ me.addCustomerToStreamSelection, me.gridPanel ]
        });

        me.streamListing = Ext.create('Shopware.apps.Customer.view.customer_stream.Listing', {
            store: me.streamStore,
            subApp: me.subApp,
            hideHeaders: true,
            border: false,
            flex: 1,
            listeners: {
                'selectionchange': Ext.bind(me.onSelectionChange, me),
                'beforedeselect': Ext.bind(me.onBeforeDeselect, me)
            }
        });

        me.filterPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.ConditionPanel', {
            flex: 1,
            border: false
        });

        me.addConditionButton = Ext.create('Ext.button.Split', {
            text: '{s name="add_condition"}{/s}',
            iconCls: 'sprite-plus-circle-frame',
            handler: function(btn) {
                btn.menu.showBy(btn);
            },
            menu: me.createConditionsMenu()
        });

        me.refreshViewButton = Ext.create('Ext.button.Button', {
            text: '{s name=refresh_preview}{/s}',
            iconCls: 'sprite-arrow-circle-225-left',
            handler: Ext.bind(me.onRefreshView, me)
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            width: 400,
            bodyCls: 'stream-filter-panel-body',
            layout: { type: 'vbox', align: 'stretch', pack: 'start' },
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'top',
                ui: 'shopware-ui',
                cls: 'condition-toolbar',
                border: true,
                items: [ me.addConditionButton, '->', me.refreshViewButton ]
            }],
            items: [ me.filterPanel ]
        });

        me.metaChart = Ext.create('Shopware.apps.Customer.view.chart.MetaChart');

        me.metaChartStore = me.metaChart.store;

        me.streamChartContainer = Ext.create('Ext.container.Container', {
            items: [],
            flex: 1,
            cls: 'stream-chart-container',
            layout: 'border'
        });

        me.saveStreamButton = Ext.create('Ext.button.Button', {
            text: '{s name="save"}{/s}',
            cls: 'primary',
            anchor: '100%',
            /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
                hidden: true,
            /*{/if}*/
            handler: Ext.bind(me.onSaveStream, me)
        });

        me.streamDetailForm = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            overflowY: 'hidden',
            height: 270,
            disabled: true,
            border: false,
            name: 'detail-form',
            items: [
                Ext.create('Shopware.apps.Customer.view.customer_stream.Detail', {
                    record: Ext.create('Shopware.apps.Customer.model.CustomerStream')
                }),
                {
                    xtype: 'container',
                    items: [me.saveStreamButton],
                    layout: 'anchor',
                    flex: 1
                }
            ],
            listeners: {
                'validitychange': function () {
                    me.fireEvent('validitychange');
                }
            }
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [ me.gridContainer, me.metaChart, me.streamChartContainer ],
            layout: 'card',
            flex: 1
        });

        me.regionContainer = Ext.create('Ext.panel.Panel', {
            region: 'center',
            border: true,
            bodyPadding: 5,
            layout: { type: 'hbox', align: 'stretch' },
            items: [
                me.formPanel,
                me.cardContainer
            ],
            margin: '10 10 10 10'
        });

        me.leftContainer = Ext.create('Ext.panel.Panel', {
            region: 'west',
            width: 390,
            collapsible: true,
            title: '{s name=stream_listing}{/s}',
            margin: '10 0 10 10',
            layout: { type: 'vbox', align: 'stretch' },
            items: [
                me.streamListing,
                me.streamDetailForm
            ]
        });
        return [ me.leftContainer, me.regionContainer ];
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

    onBeforeDeselect: function (selModel, record) {
        var me = this;

        if (record) {
            me.streamDetailForm.getForm().updateRecord(record);
            me.formPanel.getForm().updateRecord(record);
        }
    },

    onChangeLayout: function (button, item) {
        this.fireEvent('switch-layout', item.layout);
    },

    onSelectionChange: function(selModel, selection) {
        this.fireEvent('stream-selection-changed', selection);
    },

    onOnChangeAutoIndex: function(checkbox, newValue) {
        this.fireEvent('change-auto-index', checkbox, newValue);
    },

    onIndexSearch: function () {
        this.fireEvent('full-index');
    },

    onSaveStream: function() {
        this.fireEvent('save-stream');
    },

    onRefreshView: function() {
        this.fireEvent('refresh-stream-views');
    },

    addCondition: function(handler) {
        this.filterPanel.createCondition(handler);
    }
});
// {/block}
