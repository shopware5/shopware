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

//{block name="backend/config/view/custom_search/sorting/includes/direction_combo"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.includes.DirectionCombo', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.custom-search-direction-combo',
    displayField: 'label',
    valueField: 'key',
    queryMode: 'local',
    fieldLabel: '{s name="direction_combo"}{/s}',
    allowBlank: false,
    name: 'direction',
    value: 'DESC',

    initComponent: function() {
        var me = this;

        me.store = Ext.create('Ext.data.Store', {
            fields: ['key', 'label'],
            data: [
                { key: 'DESC', label: me.getDescendingLabel() },
                { key: 'ASC', label: me.getAscendingLabel() }
            ]
        });

        me.callParent(arguments);
    },

    getAscendingLabel: function() {
        return '{s name="ascending"}{/s}';
    },

    getDescendingLabel: function() {
        return '{s name="descending"}{/s}';
    }
});

//{/block}
