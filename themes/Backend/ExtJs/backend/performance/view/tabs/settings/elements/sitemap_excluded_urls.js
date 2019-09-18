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

//{namespace name="backend/performance/sitemap"}
//{block name="backend/performance/view/tabs/settings/elements/sitemap_excluded_urls"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.SitemapExcludedUrls', {

    extend: 'Shopware.grid.Searchable',

    alias: 'widget.performance-tabs-settings-elements-sitemap-excluded-urls',

    entityColumnDataIndex: 'resource',

    name: 'excluded_urls',

    height: 250,

    /**
     * Will be filled at runtime with an Object containing an editor configuration.
     */
    shopEditor: null,

    allowedEntities: [
        'product',
        'category',
        'static',
        'landing_page',
        'blog',
        'manufacturer',
    ],

    /**
     * @returns Ext.grid.column.Column[]
     */
    getColumns: function () {
        this.shopEditor = {
            xtype: 'combobox',
            store: Ext.create('Shopware.apps.Base.store.Shop').load(),
            displayField: 'name',
            valueField: 'id',
            getSubmitData: function () {
                return this.getModelData();
            }
        };

        return [
            {
                header: '{s name="excludedUrl/column/shop"}Shop{/s}',
                dataIndex: 'shopId',
                renderer: this.renderShopId,
                editor: this.shopEditor
            }
        ];
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    renderShopId: function (value) {
        if (value === null) {
            return '{s name="renderer/all"}All{/s}';
        }

        var record = this.shopEditor.store.findRecord('id', value);

        if (!record) {
            return value;
        }

        return record.get('name');
    },
});
//{/block}
