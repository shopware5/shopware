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
 */

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/main}

//{block name="backend/config/view/main/form"}
Ext.define('Shopware.apps.Config.view.main.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.config-main-form',

    autoScroll: true,
    border: false,

    /**
     *
     */
    initComponent:function () {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems(),
            buttons: me.getButtons()
        });

        me.callParent(arguments);

        me.loadRecord(me.formRecord);
    },

    /**
     * @return array
     */
    getButtons: function() {
        var me = this;
        return [{
            text: '{s name=form/reset_text}Reset{/s}',
            cls: 'secondary',
            action: 'reset'
        },{
            text: '{s name=form/save_text}Save{/s}',
            cls: 'primary',
            action: 'save'
        }];
    },

    getItems: function() {
        var me = this,
            type, name, value,
            items = [], fields = [],
            tabs = [], options,
            elementLabel = '',
            elementDescription = '',
            elementName,
            form = me.formRecord;


        var formDescription = form.get('description');
        if(form.associations.containsKey('getTranslation')) {
            if(form.getTranslation().getAt(0) && form.getTranslation().getAt(0).get('description')) {
                formDescription = form.getTranslation().getAt(0).get('description');
            }
        }

        if(formDescription) {
            items.push({
                xtype: 'fieldset',
                margin: 10,
                title: '{s name=form/description_title}Description{/s}',
                html: formDescription
            });
        }

        me.shopStore.each(function(shop) {
            fields = [];
            form.getElements().each(function(element) {
                value = element.getValues().find('shopId', shop.getId(), 0, false, true, true);
                value = element.getValues().getAt(value);
                var initialValue = value;
                if(!value && shop.getId() !== 1) {
                    value = element.getValues().find('shopId', 1, 0, false, true, true);
                    value = element.getValues().getAt(value);
                }

                type = element.get('type').toLowerCase();
                type = 'config-element-' + type;
                name = 'values[' + shop.get('id') + ']['+ element.get('id') + ']';

                options = element.get('options');
                options = Ext.isObject(options) ? options : {};
                options = Ext.applyIf(options, options.attributes || {});
                delete options.attributes;

                elementName = element.get('name');
                elementLabel = element.get('label');
                elementDescription = element.get('description');
                if(element.associations.containsKey('getTranslation')) {
                    if(element.getTranslation().getAt(0) && element.getTranslation().getAt(0).get('label')) {
                        elementLabel = element.getTranslation().getAt(0).get('label');
                    }

                    if(element.getTranslation().getAt(0) && element.getTranslation().getAt(0).get('description')) {
                        elementDescription = element.getTranslation().getAt(0).get('description');
                    }
                }

                /*{if !{acl_is_allowed privilege=sql_rule resource=shipping}}*/
                if (elementName === 'premiumshippiungasketselect') {
                    options.readOnly = true;
                    elementDescription = '{s name="insufficient_permissions" namespace="backend/application/main"}{/s}';
                }
                /*{/if}*/

                var field = Ext.applyIf({
                    xtype: type,
                    name: name,
                    elementName: elementName,
                    fieldLabel: elementLabel,
                    helpText: elementDescription, //helpText
                    value: value ? value.get('value') : element.get('value'),
                    emptyText: shop.get('default') ? null : element.get('value'),
                    disabled: !element.get('scope') && !shop.get('default'),
                    allowBlank: !element.get('required') || !shop.get('default')
                }, options);

                if (field.xtype === 'config-element-boolean' || field.xtype === 'config-element-checkbox') {
                    field = me.convertCheckBoxToComboBox(field, shop, initialValue);
                }

                fields.push(field);
            });
            if(fields.length > 0) {
                tabs.push({
                    xtype: 'config-fieldset',
                    title: shop.get('name'),
                    items: fields
                });
            }
        });

        if(tabs.length > 1) {
            items.push({
                xtype: 'tabpanel',
                bodyStyle: 'background-color: transparent !important',
                border: false,
                activeTab: 0,
                enableTabScroll: true,
                deferredRender: false,
                items: tabs
            });
        } else {
            if(tabs.length > 0) {
                delete tabs[0].title;
            }
            items.push({
                xtype: 'panel',
                bodyStyle: 'background-color: transparent !important',
                border: false,
                layout: 'fit',
                items: tabs
            });
        }
        return items;
    },

    /**
     * helper method to convert the checkbox to a combobox
     * this is done to support the not selected values by the customer
     * cause checkboxes only have two states
     *
     * @param { Shopware.apps.Base.view.element.BooleanSelect } field
     * @param { Shopware.apps.Base.model.Shop } shop
     * @param { int } initialValue
     * @returns Shopware.apps.Base.view.element.BooleanSelect
     */
    convertCheckBoxToComboBox: function (field, shop, initialValue) {
        var booleanSelectValue = field.value;

        if (shop.get('id') !== 1 && initialValue === undefined) {
            // set empty string only for foreign shops as a fallback to the default shop
            // the default shop always got a value
            booleanSelectValue = '';
        }
        else {
            //cast the value to boolean
            booleanSelectValue = Boolean(field.value);
        }

        Ext.apply(field, {
            xtype: 'base-element-boolean-select',
            value: booleanSelectValue,
            emptyText: ''
        });

        return field;
    }
});
//{/block}
