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

/**
 * todo@all: Documentation
 */

//{namespace name="backend/config/view/main"}

//{block name="backend/config/view/base/search"}
Ext.define('Shopware.apps.Config.view.base.Search', {
    extend: 'Ext.form.field.Text',
    alias: 'widget.config-base-search',

    name: 'searchfield',
    cls: 'searchfield',
    width: 100,
    emptyText: '{s name="search/empty_text"}Search...{/s}',
    enableKeyEvents: true,
    checkChangeBuffer: 500
});
//{/block}
