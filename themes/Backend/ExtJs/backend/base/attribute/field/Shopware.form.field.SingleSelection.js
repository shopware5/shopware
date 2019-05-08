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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.SingleSelection', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.shopware-form-field-single-selection',
    layout: 'anchor',
    defaults: { anchor: '100%' },
    baseBodyCls: Ext.baseCSSPrefix + 'form-item-body shopware-single-selection-form-item-body',
    allowBlank: true,

    mixins: {
        formField: 'Ext.form.field.Base',
        factory: 'Shopware.attribute.SelectionFactory'
    },

    initComponent: function() {
        var me = this;

        if (!Ext.isDefined(this.store) && Ext.isDefined(this.model)) {
            var factory = Ext.create('Shopware.attribute.SelectionFactory');
            this.store = factory.createEntitySearchStore(this.model);
        }

        var store = me.store;
        me.store = Ext.create('Ext.data.Store', {
            model: store.model,
            proxy: store.getProxy(),
            remoteSort: store.remoteSort,
            remoteFilter: store.remoteFilter,
            sorters: store.getSorters(),
            filters: store.filters.items
        });
        me.items = me.createItems();

        me.callParent(arguments);
        if (me.value) {
            me.setValue(me.value);
        }
    },

    insertGlobeIcon: function(icon) {
        var me = this;
        icon.set({
            cls: Ext.baseCSSPrefix + 'translation-globe sprite-globe',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;top:6px;right:26px;'
        });
        if (me.combo && me.combo.inputEl) {
            icon.insertAfter(me.combo.inputEl);
        }
    },

    createItems: function() {
        var items = [this.createSearchField()];

        if (this.supportText) {
            items.push(this.createSupportText(this.supportText));
        }
        return items;
    },

    createSupportText: function(supportText) {
        return Ext.create('Ext.Component', {
            html: '<div>'+supportText+'</div>',
            cls: Ext.baseCSSPrefix +'form-support-text'
        });
    },

    createSearchField: function() {
        var me = this, events;
        var config = me.getComboConfig();

        var fireComboBoxEvents = function(event) {
            me.combo.on(event, function () {
                var args = [event];
                for (var i = 0; i <= arguments.length; i++) {
                    args.push(arguments[i]);
                }
                return me.fireEvent.apply(me, args);
            });
        };

        if (!config.displayField && !config.tpl) {
            config = me.addDynamicDisplayField(config);
        }

        me.combo = Ext.create('Ext.form.field.ComboBox', config);
        events = Object.keys(me.combo.events);
        Ext.each(events, fireComboBoxEvents);

        return me.combo;
    },

    addDynamicDisplayField: function(config) {
        var me = this;

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '<div class="x-boundlist-item">{literal}{[this.getRecordLabel(values)]}{/literal}</div>',
            '</tpl>',
            {
                getRecordLabel: function(values) {
                    return me.getLabelOfObject(values);
                }
            }
        );

        config.displayTpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '{literal}{[this.getRecordLabel(values)]}{/literal}',
            '</tpl>',
            {
                getRecordLabel: function(values) {
                    return me.getLabelOfObject(values);
                }
            }
        );

        return config;
    },

    getComboConfig: function() {
        var me = this;

        return {
            emptyText: me.emptyText,
            helpText: me.helpText,
            helpTitle: me.helpTitle,
            valueField: 'id',
            queryMode: 'remote',
            store: me.store,
            allowBlank: me.allowBlank,
            isFormField: false,
            style: 'margin-right: 0 !important',
            pageSize: me.store.pageSize,
            labelWidth: 180,
            minChars: 0
        };
    },

    getValue: function() {
        return this.combo.getValue();
    },

    setValue: function(value) {
        var me = this;

        if (value && !Ext.isObject(value)) {
            me.resolveValue(value);
            return;
        }
        if (!value) {
            me.combo.clearValue();
        } else {
            me.combo.setValue(value);
        }
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    },

    resolveValue: function(value) {
        var me = this;

        me.store.load({
            params: { ids: Ext.JSON.encode([value]) },
            callback: function(records) {
                me.combo.setValue(records);
            }
        });
    }
});
