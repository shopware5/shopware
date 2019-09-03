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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/field/form"}
Ext.define('Shopware.apps.ContentTypeManager.view.field.Form', {
    extend: 'Ext.form.Panel',
    layout: 'anchor',
    padding: '10',
    defaults: {
        anchor: '99%'
    },
    border: 0,
    autoScroll: true,
    bodyStyle : 'background:none',

    initComponent: function() {
        this.handlers = this.registerHandlers();
        this.items = this.getItems();

        this.callParent(arguments);

        this.getForm().loadRecord(this.record);
    },

    getItems: function () {
        return [
            {
                xtype: 'textfield',
                name: 'label',
                fieldLabel: '{s name="field/label"}{/s}',
                allowBlank: false,
                validator: Ext.bind(this.fieldNameValidator, this),
                supportText: this.labelSupportText(this.record.get('name'))
            },
            {
                xtype: 'checkbox',
                name: 'required',
                fieldLabel: '{s name="field/required"}{/s}',
                inputValue: true,
                uncheckedValue: false
            },
            {
                xtype: 'combobox',
                name: 'type',
                fieldLabel: '{s name="field/type"}{/s}',
                store: this.fieldSelectionStore,
                allowBlank: false,
                displayField: 'label',
                valueField: 'id',
                listeners: {
                    change: this.onTypeChange,
                    scope: this
                }
            },
            {
                xtype: 'textfield',
                name: 'helpText',
                fieldLabel: '{s name="field/helpText"}{/s}'
            },
            {
                xtype: 'textfield',
                name: 'description',
                fieldLabel: '{s name="field/supportText"}{/s}'
            },
            {
                xtype: 'checkbox',
                name: 'showListing',
                fieldLabel: '{s name="field/showListing"}{/s}',
                inputValue: true,
                uncheckedValue: false,
                helpText: '{s name="field/resolverHint"}{/s}'
            },
            {
                xtype: 'checkbox',
                name: 'searchAble',
                fieldLabel: '{s name="field/searchable"}{/s}',
                inputValue: true,
                uncheckedValue: false,
                helpText: '{s name="field/resolverHint"}{/s}'
            },
            {
                xtype: 'checkbox',
                name: 'translatable',
                fieldLabel: '{s name="field/translateable"}{/s}',
                inputValue: true,
                uncheckedValue: false
            },
        ];
    },

    fieldNameValidator: function (value) {
        var me = this,
            reservedName = ['id', 'created_at', 'updated_at'];

        value = value.toString().toLowerCase();
        if (reservedName.indexOf(value) !== -1) {
            return '{s name="error/reservedName"}{/s}';
        }

        var reservedFieldNames = [];
        this.fieldListStore.each(function (record) {
            if (record.internalId === me.record.internalId) {
                return;
            }

            reservedFieldNames.push(record.get('label'));

            if (record.get('name')) {
                reservedFieldNames.push(record.get('name'));
            }
        });

        if (reservedFieldNames.indexOf(value) !== -1) {
            return '{s name="error/nameAlreadyExists"}{/s}';
        }

        if (Ext.isNumeric(value)) {
            return '{s name="error/numericOnlyLabel"}{/s}';
        }

        return true;
    },

    onTypeChange: function(combobox, value) {
        var me = this;

        if (this.handlerFieldset) {
            this.handlerFieldset.destroy();
            delete this.handlerFieldset;
        }

        if (value) {
            var record = combobox.store.getById(value);

            var disableFields = [
                me.down('[name="showListing"]'),
                me.down('[name="searchAble"]')
            ];


            if (record.get('hasResolver')) {
                disableFields.forEach(function (item) {
                    me.record.set(item.getName(), false);
                    item.setValue(false);
                    item.setDisabled(true);
                });
            } else {
                disableFields.forEach(function (item) {
                    item.setDisabled(false);
                });
            }

        };

        this.handlers.forEach(function (handler) {
            if (handler.is(value)) {
                me.handlerFieldset = Ext.create('Shopware.apps.ContentTypeManager.view.field.Fieldset', {
                    items: handler.getFields(),
                });
                me.handlerFieldset.setValues(me.record.get('options'));

                me.add(me.handlerFieldset);
            }
        })
    },

    registerHandlers: function () {
        return [
            Ext.create('Shopware.apps.ContentTypeManager.view.field_handler.IntegerHandler'),
            Ext.create('Shopware.apps.ContentTypeManager.view.field_handler.ComboboxHandler')
        ]
    },

    labelSupportText: function (value) {
        value = value || '';

        if (value) {
            return '{s name="field/name"}{/s}: ' + value;
        }
    },
});
// {/block}
