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
 */

/**
 * Shopware Core - Global Error Handling Controller
 *
 * This class handles all error which are raised
 * in the application and it's subapplications.
 *
 * The user of the controller has the ability
 * to log the errors and open them up in a grid panel.
 */
//{namespace name=backend/index/controller/main}
//{block name="backend/index/controller/error_reporter"}
Ext.define('Shopware.apps.Index.controller.ErrorReporter', {
    extend: 'Ext.app.Controller',

    /**
     * Controls if errors are logged to the default window.console
     * @boolean
     */
    displayErrors: true,

    /**
     * Controls if the default javascript error handlers should be overrides
     * with custom handler.
     *
     * Truthy to use the default error handlers, falsy to define your custom error
     * handlers.
     *
     * To provide custom error handlers, please override the method `bindCustomErrorHandler`
     *
     * @type { Boolean }
     * @default true
     */
    useDefaultErrorHandler: true,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication.
     *
     * @public
     * @constructor
     * @return void
     */
    init: function() {
        var me = this;

        if(!me.useDefaultErrorHandler) {
            me.bindCustomErrorHandler();
        }
    },

    /**
     * Binds the custom error reporter.
     *
     * @returns { Void }
     */
    bindCustomErrorHandler: function() {
        var me = this;

        // Override the default error reporter
        window.onerror = function(message, file, lineNumber) {
            //alert('{s name="error_reporter/file"}File:{/s} ' + file + "\r" + '{s name="error_reporter/line"}Line number:{/s} ' + lineNumber + "\n\r" + '{s name="error_reporter/message"}Message:{/s} ' + message);
            return !me.displayErrors;
        };

        Ext.Error.handle = function() {
            return !me.displayErrors;
        }
    }
});
//{/block}
