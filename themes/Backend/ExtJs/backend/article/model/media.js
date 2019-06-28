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
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Detail
 */
//{block name="backend/article/model/media"}
Ext.define('Shopware.apps.Article.model.Media', {

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
        //{block name="backend/article/model/media/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'mediaId', type: 'int' },
        { name: 'main', type: 'int', defaultValue: 2 },
        { name: 'position', type: 'int' },
        { name: 'description', type: 'string' },
        { name: 'extension', type: 'string' },
        { name: 'path', type: 'string' },
        {
            name: 'hasConfig',
            type: 'boolean',
            convert: function(value, record) {
                if (record.getMappings() && record.getMappings().getCount() > 0) {
                    return true;
                }
                if (record && record.raw && record.raw.mappings && record.raw.mappings.length > 0) {
                    return true;
                }
                return false;
            }
        },


        {
            name: 'original',
            type: 'string'
        },
        {
            name: 'thumbnail',
            type: 'string'
        }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.MediaMapping', name: 'getMappings', associationKey: 'mappings'}
    ]
});
//{/block}
