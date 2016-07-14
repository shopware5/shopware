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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Media Manager Main Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */

//{block name="backend/media_manager/controller/main"}
Ext.define('Shopware.apps.MediaManager.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
	extend: 'Ext.app.Controller',

	/**
	 * Creates the necessary event listener for this
	 * specific controller and opens a new Ext.window.Window
	 * to display the subapplication
     *
     * @return void
	 */
	init: function() {
        var me = this,
            albumStore = me.subApplication.getStore('Album'),
            mediaStore = me.subApplication.getStore('Media'),
            minimizable = me.subApplication.minimizable,
            forceToFront = me.subApplication.forceToFront || false;

        if (me.subApplication.params && me.subApplication.params.albumId !== null) {
            if (Ext.isArray(me.subApplication.params.albumId)) {
                albumStore.getProxy().extraParams.albumId = me.subApplication.params.albumId.join(',');
            } else {
                albumStore.getProxy().extraParams.albumId = me.subApplication.params.albumId;
            }
        } else {
            albumStore.getProxy().extraParams.albumId = null;
        }

        if(me.subApplication.layout && me.subApplication.layout == 'small') {
            me.getView('main.Selection').create({
                albumStore: albumStore,
                mediaStore: mediaStore,
                selectionHandler: me.subApplication.mediaSelectionCallback,
                eventScope: me.subApplication.eventScope,
                selectionMode: me.subApplication.selectionMode,
                validTypes: me.subApplication.validTypes,
                forceToFront: forceToFront,
                minimizable: minimizable
            });
        } else {
            me.mainWindow = me.getView('main.Window').create({
                albumStore: albumStore,
                mediaStore: mediaStore,
                validTypes: me.validTypes
            });
        }

        me.callParent(arguments);
    }
});
//{/block}
