// {namespace name=backend/customer_stream/translation}

Ext.define('Shopware.apps.Customer.view.main.Toolbar', {

    extend: 'Ext.toolbar.Toolbar',

    alias: 'widget.customer-main-toolbar',

    dock: 'top',

    ui: 'shopware-ui',

    initComponent: function () {
        var me = this, items = [];

        me.saveStreamButton = Ext.create('Ext.button.Split', {
            showText: true,
            textAlign: 'center',
            width: 220,
            iconCls: 'sprite-product-streams',
            text: '{s name=toolbar/stream_actions}Stream actions{/s}',
            name: 'save-stream',
            handler: function () {
                this.showMenu();
            },
            menu: {
                xtype: 'menu',
                items: [{
                    xtype: 'menuitem',
                    iconCls: 'sprite-disk-black',
                    text: '{s name=toolbar/save_stream}Save stream{/s}',
                    action: 'save-stream',
                    handler: function () {
                        me.fireEvent('create-or-update-stream');
                    }
                }, {
                    xtype: 'menuitem',
                    iconCls: 'sprite-plus-circle-frame',
                    text: '{s name=toolbar/save_as_new_stream}Save as a new stream{/s}',
                    action: 'create',
                    handler: function () {
                        me.fireEvent('create-stream');
                    }
                }, {
                    xtype: 'menuitem',
                    iconCls: 'sprite-chart-up-color',
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
                me.fireEvent('reload-view');
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

        /* {if {acl_is_allowed privilege=create}} */
        items.push({
            iconCls: 'sprite-plus-circle-frame',
            text: '{s name=toolbar/button_add}Add{/s}',
            action: 'addCustomer'
        });
        /* {/if} */

        /* {if {acl_is_allowed privilege=delete}} */
        items.push(me.deleteCustomerButton);
        /* {/if} */

        items.push({
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

        me.items = items;

        me.callParent(arguments);
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: function () {
                    me.fireEvent('create-filter-condition', handler);
                }
            });
        });

        items.push({ xtype: 'menuseparator' });
        items.push({
            text: '{s name=toolbar/reset}Reset{/s}',
            handler: function() {
                me.fireEvent('reset-conditions');
            }
        });
        return new Ext.menu.Menu({ items: items });
    }

});
