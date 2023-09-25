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

//{block name="backend/performance/model/sitemap/custom_urls"}
Ext.define('Shopware.apps.Performance.model.sitemap.CustomUrls', {

    /**
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * @array
     */
    fields: [
        //{block name="backend/performance/model/sitemap/custom_urls/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'url', type: 'string', defaultValue: 'https://example.org' },
        { name: 'priority', type: 'int' },
        { name: 'changeFreq', type: 'string', defaultValue: 'weekly' },
        { name: 'lastMod', type: 'datetime' },
        { name: 'shopId', type: 'int', useNull: true },
    ]
});
//{/block}
