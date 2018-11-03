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
//{block name="backend/media_manager/model/album"}
Ext.define('Shopware.apps.MediaManager.model.Album', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/media_manager/model/album/fields"}{/block}
        'id',
        'text',
        'position',
        {
            name: 'garbageCollectable',
            defaultValue: true,
            type: 'boolean'
        },
        'mediaCount',
        'parentId',
        'createThumbnails',
        'thumbnailSize',
        'iconCls',
        'albumID' ,
        'thumbnailHighDpi',
        'thumbnailQuality',
        'thumbnailHighDpiQuality'
    ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="MediaManager" action="getAlbums"}',
            create: '{url controller="MediaManager" action="saveAlbum"}',
            update: '{url controller="MediaManager" action="saveAlbum"}',
            destroy: '{url controller="MediaManager" action="removeAlbum" targetField=albums}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
