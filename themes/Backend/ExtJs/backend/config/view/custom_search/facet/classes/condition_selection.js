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

//{block name="backend/config/view/custom_search/facet/classes/conditionselection"}

Ext.define('Shopware.apps.Config.view.custom_search.facet.classes.ConditionSelection', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.config-facet-condition-selection',
    mixins: {
        formField: 'Ext.form.field.Base'
    },
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this;
        me.conditionPanel = Ext.create('Shopware.apps.ProductStream.view.condition_list.ConditionPanel', {
            bodyPadding: '0 15 0 0',
            bodyBorder: false,
            title: null,
            border: false,
            createPreviewButton: function() {
                return null;
            },
            flex: 1
        });
        me.conditionPanel.toolbar.margin = '0 0 10 0';
        me.items = [me.conditionPanel];
        me.callParent(arguments);
    },

    getValue: function() {
        var value = this.conditionPanel.getConditions();
        return Ext.JSON.encode(value);
    },

    setValue: function(value) {
        this.conditionPanel.removeAll();
        this.conditionPanel.conditions = [];

        if (!value) {
            return;
        }

        try {
            value = Ext.JSON.decode(value);
        } catch (e) {
            return;
        }

        this.conditionPanel.loadConditions(value);
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    }
});

//{/block}
