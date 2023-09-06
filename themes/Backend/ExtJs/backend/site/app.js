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
 * @package    Site
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/site/app"}
Ext.define('Shopware.apps.Site', {
    /**
    * Extends from our special controller, which handles the
    * sub-application behavior and the event bus
    * @string
    */
    extend: 'Enlight.app.SubApplication',

    /**
    * The name of the module. Used for internal purpose
    * @string
    */
    name: 'Shopware.apps.Site',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    bulkLoad: true,

    /**
     * Sets the path which will be used to request the dependencies.
     * @string
     */
    loadPath: '{url action=load}',

    /**
     * Required controllers for sub-application
     * @array
     */
    controllers: [ 'Tree', 'Form', 'Main' ],

    /**
     * Required views for controller
     * @array
     */
    views: [ 'main.Window', 'site.Tree', 'site.Form', 'site.GroupDialog' ],

    /**
     * Required stores for controller
     * @array
     */
    stores: [ 'Nodes', 'Groups', 'Selected', 'Shop' ],

    /**
     * Required models for sub-application
     * @array
     */
    models: [ 'Nodes', 'Groups', 'Selected' ],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}
