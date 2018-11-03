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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/facet/facet"}

Ext.define('Shopware.apps.Config.view.custom_search.facet.Facet', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.config-facet-facet-field',
    mixins: {
        formField: 'Ext.form.field.Base'
    },
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this;
        me.formPanel = Ext.create('Ext.form.Panel', {
            flex: 1,
            border: false,
            layout:  {
                type: 'vbox',
                align: 'stretch'
            },
            items: []
        });
        me.items = [me.formPanel];

        me.callParent(arguments);
    },

    setHandler: function(handler) {
        var me = this;
        me.handler = handler;
        me.formPanel.removeAll();
        me.formPanel.add(handler.createItems());
    },

    getValue: function() {
        var me = this,
            values = { },
            data = { };

        me.formPanel.getForm().getFields().each(function(field) {
            values[field.getName()] = field.getValue();
        });

        data[me.handler.getClass()] = values;

        return Ext.JSON.encode(data);
    },

    setValue: function(value) {
        var me = this;
        value = Ext.JSON.decode(value);

        var keys = Object.keys(value);
        me.formPanel.getForm().setValues(value[keys[0]]);
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    }
});

//{/block}
