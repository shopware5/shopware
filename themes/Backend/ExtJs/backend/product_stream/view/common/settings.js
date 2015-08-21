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
//{namespace name=backend/product_stream/main}

Ext.define('Shopware.apps.ProductStream.view.common.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.product-stream-settings',
    title: '{s name=settings}Settings{/s}',
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
        if (!sorting) {
            return;
        }

        var sortingValue = me.findSorting(sorting);

        me.sortingCombo.setValue(sortingValue);
    },

    findSorting: function(sorting) {
        var me = this;

        var key = Object.keys(sorting)[0];
        var properties = sorting[key];
        var direction = properties.direction;
        var store = me.getSortings();
        var match = null;

        store.forEach(function(item) {
            if (item.key === key && item.direction === direction) {
                match = item.value;
                return false;
            }
        });

        return match;
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
            fieldLabel: '{s name=sorting}Sorting{/s}',
            valueField: 'value',
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
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ReleaseDateSorting', value: '{s name=release_date}Release date{/s}', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PopularitySorting', value: '{s name=popularity}Popularity{/s}', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: '{s name=cheapest_price}Cheapest price{/s}', direction: 'asc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting', value: '{s name=highest_price}Highest price{/s}', direction: 'desc' },
            { key: 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductNameSorting', value: '{s name=article_description}Article description{/s}', direction: 'asc' }
        ];
    },

    createNameField: function() {
        this.nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            anchor: '100%',
            allowBlank: false,
            fieldLabel: '{s name=name}Name{/s}',
            translatable: true,
        });

        return this.nameField;
    },

    createDescriptionField: function() {
        this.descriptionField = Ext.create('Ext.form.field.TextArea', {
            name: 'description',
            anchor: '100%',
            rows: 3,
            fieldLabel: '{s name=description}Description{/s}',
            translatable: true,
        });

        return this.descriptionField;
    }
});
