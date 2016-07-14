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

/**
 * todo@all: Documentation
 */

//{block name="backend/config/store/form/search_field"}
Ext.define('Shopware.apps.Config.store.form.SearchField', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Config.model.form.SearchField',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 20,
    proxy: {
        type: 'ajax',
        url: '{url action=getTableList}?_repositoryClass=searchField',
        api: {
            create: '{url action=saveTableValues}?_repositoryClass=searchField',
            update: '{url action=saveTableValues}?_repositoryClass=searchField',
            destroy: '{url action=deleteTableValues}?_repositoryClass=searchField'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
