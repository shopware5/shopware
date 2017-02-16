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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/customer/view/main}

/**
 * Shopware UI - Customer list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    /**
     * Define that the customer main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'customer-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-list-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'border',
    /**
     * Define window width
     * @integer
     */
    width:'90%',
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-customer-main-window',
    /**
     * Set window title which is displayed in the window header
     * @string
     */
    title:'{s name=window_title}Customer list{/s}',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        toolbar:{
            add:'{s name=toolbar/button_add}Add{/s}',
            remove:'{s name=toolbar/button_delete}Delete all selected{/s}',
            customerGroup:'{s name=toolbar/customer_group}Customer group{/s}',
            groupEmpty:'{s name=toolbar/customer_group_empty}Select...{/s}',
            search:'{s name=toolbar/search_empty_text}Search...{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        Ext.suspendLayouts();
        me.listStore = Ext.create('Shopware.apps.CustomerStream.store.Preview', { pageSize: 10}).load({ conditions: null });
        me.gridPanel = Ext.create('Shopware.apps.Customer.view.list.List', { store: me.listStore });

        me.gridPanel.on('selection-changed', function(selection) {
            me.deleteCustomerButton.setDisabled(selection.length == 0);
        });

        me.streamListing = Ext.create('Shopware.apps.CustomerStream.view.list.CustomerStream', {
            store: Ext.create('Shopware.apps.CustomerStream.store.CustomerStream').load(),
            subApp: me.subApp,
            collapsible: true,
            hideHeaders: true,
            title: 'Definierte Streams',
            height: 200,
            iconCls: 'sprite-product-streams',
            selectionChanged: Ext.bind(me.streamSelected, me)
        });

        me.filterPanel = Ext.create('Shopware.apps.CustomerStream.view.detail.ConditionPanel', { flex: 4 });

        me.chartStore = Ext.create('Ext.data.Store', {
            fields:[
                { name:'count_orders', type: 'int'},
                { name:'invoice_amount_avg', type: 'float'},
                { name:'invoice_amount_max', type: 'float'},
                { name:'invoice_amount_min', type: 'float'},
                { name:'invoice_amount_sum', type: 'float'},
                { name:'product_avg', type: 'float'},
                { name:'yearMonth', type: 'string'},
            ],
            proxy:{
                type:'ajax',
                url: '{url controller="CustomerStream" action="loadChart"}',
                reader:{
                    type:'json',
                    root:'data'
                }
            }
        });
        me.chartStore.load();
        me.chart = Ext.create('Ext.chart.Chart', {
            shadow:true,
            margin:30,
            legend: true,
            animate:true,
            snippets:{
                yAxis:'{s name=chart/y_axis}Turnover{/s}',
                xAxis:'{s name=chart/x_axis}Month{/s}'
            },
            store: me.chartStore,
            axes: [{
                type: 'Numeric',
                position: 'left',
                fields: [
                    'count_orders',
                    'invoice_amount_avg',
                    'invoice_amount_max',
                    'invoice_amount_min',
                    'invoice_amount_sum',
                    'product_avg',
                ],
                label: {
                    renderer: Ext.util.Format.numberRenderer('0,0')
                },
                title: 'Umsatz',
                grid: true,
                minimum: 0
            }, {
                type: 'Category',
                position: 'bottom',
                fields: ['yearMonth'],
            }],
            series: [
                me.createLineSeries('count_orders', 'Anzahl Bestellungen'),
                me.createLineSeries('invoice_amount_avg', 'Ø Warenkorb'),
                me.createLineSeries('invoice_amount_max', 'Größte Bestellung'),
                me.createLineSeries('invoice_amount_min', 'Kleinste Bestellung'),
                me.createLineSeries('invoice_amount_sum', 'Gesamt Umsatz'),
                me.createLineSeries('product_avg', 'Ø Warenwert')
            ]
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [me.gridPanel, me.chart] ,
            region: 'center',
            layout: 'card',
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            region: 'west',
            collapsible: true,
            cls: 'shopware-form',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            width: 300,
            title: 'Filter & Customer Streams',
            items: [
                me.filterPanel,
                me.streamListing
            ]
        });
        //add the customer list grid panel and set the store
        me.items = [
            me.formPanel,
            me.cardContainer
        ];
        me.dockedItems = [ me.getToolbar()];

        Ext.resumeLayouts(true);

        me.callParent(arguments);
    },

    createLineSeries: function(field, title) {
        return {
            type: 'line',
            highlight: { size: 7, radius: 7 },
            axis: 'left',
            fill: true,
            title: title,
            smooth: true,
            xField: 'yearMonth',
            yField: field,
            markerConfig: { type: 'circle', size: 4, radius: 4, 'stroke-width': 0 }
        };
    },

    loadPreview: function() {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }
        me.listStore.getProxy().extraParams = me.filterPanel.getSubmitData();
        me.listStore.load();
    },

    streamSelected: function(selModel, selection) {
        var me = this;

        if (me.preventStreamChanged) {
            return;
        }
        if (selection.length <= 0) {
            me.resetConditions();
            return;
        }

        me.loadStream(selection[0]);
    },

    loadStream: function(record) {
        var me = this;

        me.saveStreamButton.setText('Save: ' + record.get('name'));
        me.streamListing.setLoading(true);

        me.setTitle('Kundenliste: ' + record.get('name'));
        me.resetFilterPanel();
        me.formPanel.loadRecord(record);

        me.streamListing.setLoading(false);

        me.listStore.getProxy().extraParams = {
            conditions: record.get('conditions')
        };
        me.listStore.load();
    },

    saveStream: function (record) {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }

        var isNew = (record.get('id') === null);

        me.formPanel.getForm().updateRecord(record);

        record.save({
            callback: function() {
                if (isNew) {
                    me.streamListing.getStore().insert(0, record);
                    // me.streamListing.cellEditor.startEdit(record, 1);
                }
                me.preventStreamChanged = true;
                me.streamListing.selModel.deselectAll(true);
                me.preventStreamChanged = false;
                me.streamListing.selModel.select([record], false, true);
            }
        });
    },

    createOrUpdateStream: function() {
        var me = this;
        var record = me.formPanel.getForm().getRecord();
        if (record) {
            me.saveStream(me.formPanel.getForm().getRecord());
            return;
        }
        me.createStream();
    },

    createStream: function() {
        var me = this;
        var record = Ext.create('Shopware.apps.CustomerStream.model.CustomerStream', {
            id: null,
            name: 'New stream'
        });
        me.saveStream(record);
    },

    indexSearch: function() {
        var me = this;

        var indexingWindow = Ext.create('Shopware.apps.CustomerStream.view.detail.IndexingWindow', {
            width: 500,
            height: 150,
            requests: [{
                text: 'Analyzing customers',
                name: 'search_index',
                url: '{url controller=CustomerStream action=buildSearchIndex}',
                params: { }
            }]
        });
        indexingWindow.on('finish', Ext.bind(me.loadPreview, me));
        indexingWindow.show();
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this, items = [];

        me.saveStreamButton = Ext.create('Ext.button.Split', {
            showText: true,
            textAlign: 'left',
            iconCls: 'sprite-product-streams',
            text: 'Stream speichern',
            name: 'save-stream',
            handler: Ext.bind(me.createOrUpdateStream, me),
            menu: {
                xtype: 'menu',
                items: [{
                    xtype: 'menuitem',
                    iconCls: 'sprite-plus-circle-frame',
                    text: 'Als neuen stream speichern',
                    action: 'create',
                    handler: Ext.bind(me.createStream, me)
                }, {
                    xtype: 'menuitem',
                    iconCls: 'sprite',
                    text: 'Kunden analyzieren',
                    action: 'index',
                    handler: Ext.bind(me.indexSearch, me)
                }]
            }
        });

        items.push(me.saveStreamButton);
        items.push({ xtype: 'tbspacer', width: 10 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 10 });

        items.push({
            xtype: 'button',
            // text: 'Filter hinzufügen',
            iconCls: 'sprite-funnel',
            menu: me.createMenu()
        });

        items.push({
            // text: 'Aktualisieren',
            iconCls: 'sprite-arrow-circle-225-left',
            handler: Ext.bind(me.loadPreview, me)
        });

        items.push({ xtype: 'tbspacer', width: 10 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 10 });

        me.deleteCustomerButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            // text:me.snippets.toolbar.remove,
            text: 'Markierte löschen',
            disabled:true,
            action:'deleteCustomer'
        });

        /*{if {acl_is_allowed privilege=create}}*/
            items.push({
                iconCls:'sprite-plus-circle-frame',
                text:me.snippets.toolbar.add,
                action:'addCustomer'
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
            items.push(me.deleteCustomerButton);
        /*{/if}*/

        items.push({
            // showText: true,
            xtype: 'cycle',
            prependText: '{s name=toolbar/view}Display as{/s} ',
            action: 'layout',
            listeners: {
                change: function (button, item) {
                    me.cardContainer.getLayout().setActiveItem(item.layout == 'table' ? 0 : 1);
                }
            },
            menu: {
                items: [
                    {
                        text: '{s name=view_chart}Umsatz{/s}',
                        layout: 'chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=view_table}Kundenliste{/s}',
                        layout: 'table',
                        iconCls: 'sprite-table',
                        checked: true
                    }
                ]
            }
        });

        items.push('->');

        items.push({
            xtype:'textfield',
            name:'searchfield',
            cls:'searchfield',
            width:170,
            emptyText:me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer:500
        });

        items.push({ xtype:'tbspacer', width:6 });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            items: items
        });
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.filterPanel.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: Ext.bind(me.filterPanel.createCondition, me.filterPanel)
            });
        });

        items.push({ xtype: 'menuseparator' });
        items.push({
            text: 'Reset',
            handler: Ext.bind(me.resetConditions, me)
        });
        return new Ext.menu.Menu({ items: items });
    },

    resetFilterPanel: function() {
        var me = this;

        me.filterPanel.removeAll();
        me.filterPanel.loadRecord(null);
    },

    resetConditions: function() {
        var me = this;
        me.resetFilterPanel();
        me.loadPreview();
    }

});
//{/block}
