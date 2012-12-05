//{namespace name=backend/config/view/license}

/**
 * todo@all: Documentation
 */
//{block name="backend/config/view/form/license"}
Ext.define('Shopware.apps.Config.view.form.License', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-license',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.License',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            width: 500,
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            dataIndex: 'label',
            text: '{s name=table/label_text}Label{/s}',
            flex: 1
        },{
            xtype: 'gridcolumn',
            dataIndex: 'module',
            text: '{s name=table/module_text}Module{/s}',
            flex: 1
        },{
            xtype: 'gridcolumn',
            dataIndex: 'host',
            text: '{s name=table/host_text}Host{/s}',
            flex: 1
        },{
            xtype: 'gridcolumn',
            dataIndex: 'type',
            text: '{s name=table/type_text}Type{/s}',
            renderer: function(v) {
                switch(v) {
                    case 1: return '{s name=detail/type_purchase}Purchase{/s}';
                    case 2: return '{s name=detail/type_rent}Rent{/s}';
                    case 3: return '{s name=detail/type_trial}Trial{/s}';
                    default: return v;
                }
            },
            flex: 1
        },{
            xtype: 'datecolumn',
            dataIndex: 'expiration',
            text: '{s name=table/expiration_text}Expiration date{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    onChangeValue: function(field, value) {
        field[value ? 'show' : 'hide']();
    },

    getFormItems: function() {
        var me = this;
        return [{
            name: 'label',
            fieldLabel: '{s name=detail/label_label}Label{/s}'
        }, {
            name: 'module',
            fieldLabel: '{s name=detail/module_label}Module{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            name: 'host',
            fieldLabel: '{s name=detail/host_label}Host{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            xtype: 'config-element-date',
            name: 'added',
            fieldLabel: '{s name=detail/added_label}Added date{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            xtype: 'config-element-date',
            name: 'creation',
            fieldLabel: '{s name=detail/creation_label}Creation date{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            xtype: 'config-element-date',
            name: 'expiration',
            fieldLabel: '{s name=detail/expiration_label}Expiration date{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            xtype: 'config-element-select',
            name: 'type',
            fieldLabel: '{s name=detail/type_label}License type{/s}',
            hidden: true,
            readOnly: true,
            store: [
                [1,  '{s name=detail/type_purchase}Purchase{/s}'],
                [2, '{s name=detail/type_rent}Rent{/s}'],
                [3, '{s name=detail/type_trial}Trial{/s}']
            ],
            listeners: { change: me.onChangeValue }
        },{
            name: 'notation',
            fieldLabel: '{s name=detail/info_label}Notation{/s}',
            hidden: true,
            readOnly: true,
            listeners: { change: me.onChangeValue }
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: '{s name=detail/active_label}Active{/s}'
        },{
            xtype: 'config-element-textarea',
            name: 'license',
            fieldLabel: '{s name=detail/license_label}License{/s}',
            fieldStyle: 'font: 13px monospace !important;'
        }];
    }
});
//{/block}
