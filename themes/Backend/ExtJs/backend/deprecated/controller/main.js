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
 * @package    Deprecated
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Deprecated.controller.Main', {
	extend: 'Ext.app.Controller',
	views: [ 'main.Window' ],

	/**
	 * Creates the neccessary event listener for this
	 * specific controller and opens a new Ext.window.Window
	 * to display the subapplication.
     *
     * @public
     * @
	 */
	init: function() {
        var me = this;
        me.mainWindow = this.getView('main.Window').create({
            moduleName: this.subApplication.moduleName,
            controllerName: this.subApplication.controllerName,
            actionName: this.subApplication.actionName || 'index',
            requestConfig: this.subApplication.requestConfig,
            moduleConfig: this.subApplication.moduleConfig
        }).show();
	}
});
