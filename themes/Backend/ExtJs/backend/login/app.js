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
 * @package    Login
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Login Bootstrap
 *
 * This file bootstrapps the login module.
 */
//{block name="backend/login/app"}
Ext.define('Shopware.apps.Login', {

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.Login',

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath: '{url action=load}',

    /**
     * Enables our bulk loading mechanism.
     * @booelan
     */
    bulkLoad: true,

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers: [ 'Main' ],

    /**
     * Required stores for controller
     * @array
     */
    stores: [ 'Locale' ],

    /**
     * Required models for controller
     * @array
     */
    models: [ 'Locale' ],

    /**
     * Required views for controller
     *
     * @array
     */
    views: [ 'Main', 'main.Form' ],

    /**
     * Target sub application name
     */
    targetName: 'Shopware.apps.Index',

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
