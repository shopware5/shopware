Ext.define('Shopware.apps.Customer.view.main.Toolbar', {

    extend: 'Ext.toolbar.Toolbar',

    alias: 'widget.customer-main-toolbar',

    dock: 'top',

    ui: 'shopware-ui',

    snippets:{
        toolbar:{
            add:'{s name=toolbar/button_add}Add{/s}',
            remove:'{s name=toolbar/button_delete}Delete all selected{/s}',
            customerGroup:'{s name=toolbar/customer_group}Customer group{/s}',
            groupEmpty:'{s name=toolbar/customer_group_empty}Select...{/s}',
            search:'{s name=toolbar/search_empty_text}Search...{/s}'
        }
    },


    initComponent: function () {
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
            iconCls: 'sprite-funnel',
            menu: me.createMenu()
        });

        items.push({
            iconCls: 'sprite-arrow-circle-225-left',
            handler: Ext.bind(me.reloadView, me)
        });

        me.indexingBar = Ext.create('Ext.ProgressBar', {
            text: 'Indexierung',
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
            iconCls:'sprite-minus-circle-frame',
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
                    me.fireEvent('switch-layout', item.layout);
                }
            },
            menu: {
                items: [
                    {
                        text: '{s name=view_chart}Umsatz{/s}',
                        layout: 'amount_chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=view_chart_stream}Stream Umsatz{/s}',
                        layout: 'stream_chart',
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
        me.items = items;

        me.callParent(arguments);
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                // handler: Ext.bind(me.filterPanel.createCondition, me.filterPanel)
            });
        });

        items.push({ xtype: 'menuseparator' });
        items.push({
            text: 'Reset',
            handler: Ext.bind(me.resetConditions, me)
        });
        return new Ext.menu.Menu({ items: items });
    }

});