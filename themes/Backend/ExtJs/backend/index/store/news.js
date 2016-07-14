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

//{block name="backend/index/store/news"}
Ext.define('Shopware.apps.Index.store.News', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Index.model.News',
    remoteFilter: true,
    clearOnLoad: true,
    proxy: {
        type: 'ajax',
        url: '{url controller="widgets" action="getShopwareNews"}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
