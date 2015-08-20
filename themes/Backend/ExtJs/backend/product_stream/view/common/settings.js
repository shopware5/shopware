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
 * @category   Shopware
 * @package    ProductStream
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

Ext.define('Shopware.apps.ProductStream.view.common.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.product-stream-settings',
    title: 'Settings',
    height: 170,
    margin: '0 0 10',
    bodyPadding: 10,
    collapsible: true,
    plugins: [{
        ptype: 'translation',
        pluginId: 'translation',
        translationType: 'productStream',
        translationMerge: false,
        translationKey: null
    }],

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    loadRecord: function(record) {
        var me = this;

        me.callParent(arguments);

        var sorting = record.get('sorting');
        sorting = Object.keys(sorting)[0];

        me.sortingCombo.setValue(sorting);
    },

    createItems: function() {
        return [
            this.createNameField(),
            this.createDescriptionField(),
            this.createSortingCombo()
        ];
    },

    createSortingCombo: function() {
        var me = this;

        me.sortingStore = Ext.create('Ext.data.Store', {
            fields: ['key', 'value', 'direction'],
            data: me.getSortings()
        });

        me.sortingCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'sorting',
            store: me.sortingStore,
            fieldLabel: 'Sorting',
            valueField: 'key',
            displayField: 'value',
            queryMode: 'local',
            anchor: '100%',
            allowBlank: false,
            forceSelection: true
        });

        return me.sortingCombo;
    },

    getSortings: function() {
        return [
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ReleaseDateSorting', value: 'Release date', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PopularitySorting', value: 'Popularity', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: 'Cheapest price', direction: 'asc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: 'Highest price', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductNameSorting', value: 'Article description', direction: 'asc' }
        ];
    },

    createNameField: function() {
        this.nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            anchor: '100%',
            allowBlank: false,
            fieldLabel: 'Name',
            translatable: true,
        });

        return this.nameField;
    },

    createDescriptionField: function() {
        this.descriptionField = Ext.create('Ext.form.field.TextArea', {
            name: 'description',
            anchor: '100%',
            rows: 3,
            fieldLabel: 'Description',
            translatable: true,
        });

        return this.descriptionField;
    }
});
