/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * shopware AG (c) 2012. All rights reserved.
 *
 * @link http://www.shopware.de/
 * @date 2012-02-20
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
            type: 'string',
            convert: function(value, record) {
                var name, extension;
                if (record.get('path').indexOf('media/image') === -1) {
                    return 'media/image/' + record.get('path') + '.' + record.get('extension');
                } else {
                    return record.get('path');
                }
            }
        },
        {
            name: 'thumbnail',
            type: 'string',
            convert: function(value, record) {
                if (record.get('path').indexOf('media/image') === -1) {
                    return 'media/image/thumbnail/' + record.get('path') + '_140x140.' + record.get('extension');
                } else {
                    var name =  record.get('path').replace('media/image/', '');
                    name = name.replace('.' + record.get('extension'), '');
                    return 'media/image/thumbnail/' + name + '_140x140.' + record.get('extension');
                }
            }
        }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.MediaAttribute', name: 'getAttributes', associationKey: 'attribute'},
        { type: 'hasMany', model: 'Shopware.apps.Article.model.MediaMapping', name: 'getMappings', associationKey: 'mappings'}
    ]
});
//{/block}

