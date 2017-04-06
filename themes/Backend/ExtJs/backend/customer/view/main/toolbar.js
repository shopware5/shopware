//{namespace name=backend/customer/view/toolbar}

Ext.define('Shopware.apps.Customer.view.main.Toolbar', {

    extend: 'Ext.toolbar.Toolbar',

    alias: 'widget.customer-main-toolbar',

    dock: 'top',

    ui: 'shopware-ui',


    initComponent: function () {
        var me = this, items = [];

        me.saveStreamButton = Ext.create('Ext.button.Split', {
            showText: true,
            textAlign: 'left',
            iconCls: 'sprite-product-streams',
            text: '{s name=toolbar/save_stream}Save stream{/s}',
            name: 'save-stream',
            handler: function () {
                me.fireEvent('create-or-update-stream');
            },
            menu: {
                xtype: 'menu',
                items: [{
                    xtype: 'menuitem',
                    iconCls: 'sprite-plus-circle-frame',
                    text: '{s name=toolbar/save_as_new_stream}Save as a new stream{/s}',
                    action: 'create',
                    handler: function () {
                        me.fireEvent('create-stream');
                    }
                }, {
                    xtype: 'menuitem',
                    iconCls: 'sprite',
                    text: '{s name=toolbar/analyse_customer}Analyse customer{/s}',
                    action: 'index',
                    handler: function () {
                        me.fireEvent('index-search');
                    }
                }]
            }
        });

        items.push(me.saveStreamButton);
        items.push({ xtype: 'tbspacer', width: 10 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 10 });

        items.push({
            xtype: 'button',
            iconCls: 'sprite-funnel',
            menu: me.createMenu()
        });

        items.push({
            iconCls: 'sprite-arrow-circle-225-left',
            handler: function () {
                me.fireEvent('reload-view')
            }
        });

        me.indexingBar = Ext.create('Ext.ProgressBar', {
            text: '{s name=toolbar/indexing}Indexing{/s}',
            value: 0,
            height: 20,
            width: 300
        });

        items.push({ xtype: 'tbspacer', width: 5 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 5 });

        items.push(me.indexingBar);

        items.push({ xtype: 'tbspacer', width: 5 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 5 });

        me.deleteCustomerButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: '{s name=toolbar/button_delete}Delete all selected{/s}',
            disabled: true,
            action: 'deleteCustomer'
        });

        /*{if {acl_is_allowed privilege=create}}*/
        items.push({
            iconCls: 'sprite-plus-circle-frame',
            text: '{s name=toolbar/button_add}Add{/s}',
            action: 'addCustomer'
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
                    me.fireEvent('switch-layout', item.layout);
                }
            },
            menu: {
                items: [
                    {
                        text: '{s name=toolbar/view_chart}Revenue{/s}',
                        layout: 'amount_chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=toolbar/view_chart_stream}Stream revenue{/s}',
                        layout: 'stream_chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=toolbar/view_table}List of customers{/s}',
                        layout: 'table',
                        iconCls: 'sprite-table',
                        checked: true
                    }
                ]
            }
        });

        items.push('->');

        items.push({
            xtype: 'textfield',
            name: 'searchfield',
            cls: 'searchfield',
            width: 170,
            emptyText: '{s name=toolbar/search_empty_text}Search...{/s}',
            enableKeyEvents: true,
            checkChangeBuffer: 500
        });

        items.push({ xtype: 'tbspacer', width: 6 });
        me.items = items;

        me.callParent(arguments);
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler
                // handler: Ext.bind(me.filterPanel.createCondition, me.filterPanel)
            });
        });

        items.push({ xtype: 'menuseparator' });
        items.push({
            text: '{s name=toolbar/reset}Reset{/s}',
            handler: Ext.bind(me.resetConditions, me)
        });
        return new Ext.menu.Menu({ items: items });
    }

});