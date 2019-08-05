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
 * Shopware Model - Article backend module.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Detail
 */
//{block name="backend/article/model/mapping"}
Ext.define('Shopware.apps.Article.model.Mapping', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

    /**
     * Fields array which contains the model fields
     * @array
     */
    fields: [
        //{block name="backend/article/model/mapping/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'articleId', type: 'int' },
        { name: 'settings', type: 'boolean', defaultValue: true },
        { name: 'stock', type: 'boolean', defaultValue: false },
        { name: 'prices', type: 'boolean', defaultValue: true },
        { name: 'basePrice', type: 'boolean', defaultValue: true },
        { name: 'purchasePrice', type: 'boolean', defaultValue: true },
        { name: 'attributes', type: 'boolean', defaultValue: true },
        { name: 'translations', type: 'boolean', defaultValue: true }

    ],
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Detail', name: 'getDetails', associationKey: 'variants'}
    ],

    proxy: {
        type: 'ajax',
        api: {
            create: '{url action="acceptMainData"}'
        },
        reader:{
            type:'json',
            root:'data'
        }
    }
});
//{/block}
