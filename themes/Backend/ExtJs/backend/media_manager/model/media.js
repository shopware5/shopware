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
 * @package    MediaManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/media_manager/model/media"}
Ext.define('Shopware.apps.MediaManager.model.Media', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/media_manager/model/media/fields"}{/block}
        'created',
        'description',
        'extension',
        'id',
        { name: 'name', sortType: 'asUCText' },
        'type',
        'path',
        'userId',
        'thumbnail',
        'thumbnails',
        'width',
        'height',
        'albumId',
        'newAlbumID',
        'virtualPath',
        'timestamp',
        'fileSize'
    ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="MediaManager" action="getAlbumMedia"}',
            create: '{url controller="MediaManager" action="saveMedia"}',
            update: '{url controller="MediaManager" action="saveMedia" targetField=media}',
            destroy: '{url controller="MediaManager" action="removeMedia"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
