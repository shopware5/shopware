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
 *
 * @category   Shopware
 * @package    Systeminfo
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Systeminfo backend module
 *
 * Main controller of the systeminfo module.
 * It only creates the main-window.
 */
// {block name="backend/systeminfo/controller/main"}
Ext.define('Shopware.apps.Systeminfo.controller.Main', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

    /**
    * Required views for controller
    * @array
    */
    views: [ 'main.Window' ],

    requires: [ 'Shopware.apps.Systeminfo.controller.Systeminfo' ],

    init: function() {
        var me = this,
            //Contains the necessary configs
            configStore = me.subApplication.getStore('Configs'),
            //Contains the necessary paths
            pathStore = me.subApplication.getStore('Paths'),
            //Contains the necessary files
            fileStore = me.subApplication.getStore('Files'),
            //Contains the necessary versions
            versionStore = me.subApplication.getStore('Versions');
        optimizerStore = me.subApplication.getStore('Optimizers');
        me.mainWindow = me.getView('main.Window').create({
            configStore: configStore,
            pathsStore: pathStore,
            fileStore: fileStore,
            versionStore: versionStore,
            optimizerStore: optimizerStore
        });
        this.callParent(arguments);
    }
});
//{/block}
