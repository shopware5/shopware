//{namespace name=backend/config/view/form}
/**
 * todo@all: Documentation
 */
//{block name="backend/config/view/shop/detail" append}
Ext.define('Shopware.apps.Config.view.shop.Detail-SwagMultiShop', {
    override: 'Shopware.apps.Config.view.shop.Detail',

    getMainField: function() {
        var me = this;
        return {
            xtype: 'config-element-select',
            name: 'mainId',
            value: 0,
            fieldLabel: '{s name=shop/detail/main_shop_label}Main shop{/s}',
            helpText: '{s name=shop/detail/main_shop_help}{/s}',
            store: 'base.Shop',
            listeners:{
                scope: me,
                change: function(combo, value) {
                    var form = combo.up('form'),
                        fields = form.query('[isMainField]'),
                        requiredFields = form.query('[isMainRequired]'),
                        action = value ? 'hide' : 'show';
                    Ext.each(fields, function(field) {
                        field[action]();
                    });
                    Ext.each(requiredFields, function(field) {
                        field['allowBlank'] = !!value;
                    });
                }
            }
        };
    },

    getDefaultField: function() {
        var me = this;
        return {
            xtype: 'config-element-boolean',
            name: 'default',
            fieldLabel: '{s name=shop/detail/default_label}Default{/s}',
            isMainField: true,
            readOnly: true,
            handler: function(button, value) {
                var form = button.up('form'),
                    fallbackField = form.down('[name=fallbackId]'),
                    mainField = form.down('[name=mainId]'),
                    show = value ? 'hide' : 'show';
                fallbackField[show]();
                mainField[show]();
                fallbackField.setValue(null);
            }
        };
    }
});
//{/block}
