/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

Ext.define('Shopware.apps.Config.view.element.CustomSortingGrid', {
    extend: 'Shopware.form.field.CustomSortingGrid',
    alias: [
        'widget.config-element-custom-sorting-grid',
        'widget.base-element-custom-sorting-grid'
    ],

    initComponent: function() {
        var me = this,
            factory = Ext.create('Shopware.attribute.SelectionFactory');

        me.store = factory.createEntitySearchStore("Shopware\\Models\\Search\\CustomSorting");
        me.searchStore = factory.createEntitySearchStore("Shopware\\Models\\Search\\CustomSorting");

        me.store.remoteFilter = true;
        me.store.filter({
            property: 'sortings',
            expression: 'NOT LIKE',
            value: '%ManualSorting%'
        });

        me.searchStore.remoteFilter = true;
        me.searchStore.filter({
            property: 'sortings',
            expression: 'NOT LIKE',
            value: '%ManualSorting%'
        });

        me.callParent(arguments);

        if (me.value) {
            me.setValue(me.value);
        }
    }
});
