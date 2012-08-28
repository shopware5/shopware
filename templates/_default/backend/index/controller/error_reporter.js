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
 * @category   Shopware
 * @package    Index
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
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
Ext.define('Shopware.apps.Index.controller.ErrorReporter', {
    extend: 'Ext.app.Controller',
    views: [ 'ErrorReporter.List' ],
    stores: [ 'ErrorReporter' ],
    models: [ 'ErrorReporter' ],

    /**
     * Name of the controller which will be used for internal use
     * and for the text for the footer button if necessary
     *
     * @string
     */
    name: 'Error Reporter',

    /**
     * Controls if errors are logged to the default window.console
     *
     * @boolean
     */
    displayErrors: true,

    /**
     * Indicates if we need a footer button
     *
     * @boolean
     */
    showFooterButton: true,

    /**
     * Holder property which holds the created footer button
     *
     * @private
     * @null
     */
    _footerBtn: null,

    /**
     * Holder property which holds the created grid panel
     *
     * @private
     * @null
     */
    _grid: null,


    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this, interval, footer;

        // Override the default error reporter
        window.onerror = function(message, file, lineNumber) {
            me.onNativeError(message, file, lineNumber);
            return !me.displayErrors;
        };

        // Bind events
        me.control({
            'window button[action=errorReporterDeleteAll]': {
                click: me.onDeleteAllEntries
            }
        });

        // Check if we need the footer button
        if(!me.showFooterButton) {
            return false;
        }

        // Use setInterval to check if the footer is available
        interval = window.setInterval(function() {
            footer = Ext.ComponentQuery.query('footer');
            if(footer.length) {
                footer = footer[0];

                me.createFooterButton(footer);

                // Clear interval when the footer was found
                clearInterval(interval);
            }
        }, 50);
    },

    /**
     * Creates a new button which will be prepend to the
     * footer toolbar. The button opens the error reporting
     * window which displays the raised errors.
     *
     * @param [object] instanced footer object
     * @return [object] generated button
     */
    createFooterButton: function(footer) {
        var me = this;

        me._footerBtn = Ext.create('Ext.button.Button', {
            text: me.name,
            handler: function() { me.openErrorReporterWindow(); }
        });

        // Prepends the button before all other buttons
        footer.insert(0, me._footerBtn);

        return me._footerBtn;
    },

    /**
     * Creates and opens the error reporting window.
     * The window is based on a gridpanel and a toolbar
     * to add futher functionality to the window.
     *
     * A new growl message will be fired to indicate the user
     * that the error reporting window was successfully opened.
     *
     * @return void
     */
    openErrorReporterWindow: function() {
        var me = this;

        me._grid = me.getView('ErrorReporter.List').create();

        Ext.create('Ext.window.Window', {
            layout: 'fit',
            width: 640,
            height: 480,
            maximizable: true,
            stateful: true,
            stateId: 'errorReporterList',
            border: 0,
            title: 'Error Reporter',
            items: [ me._grid ]
        }).show();

        Shopware.Msg.createGrowlMessage('Error Reporter', 'Der Error Reporter wurde ge&ouml;ffnet.', 'growl');
    },

    /**
     * Logs all javascript errors which are catched by the "window.onerror" method
     *
     * @param [string] msg - error message
     * @param [string] file - full path to the file which raised the error
     * @param [integrier] index - line number within the file
     */
    onNativeError: function(msg, file, index) {
        var store = this.getStore('ErrorReporter');

        var model = Ext.create('Shopware.apps.Index.model.ErrorReporter', {
            'message': msg,
            'filename': file,
            'linenumber': index
        });

        model.save({
            success: function() {
                store.add(model);
            }
        });
    },

    onDeleteAllEntries: function(btn) {
        var win = btn.up('window'),
            grid = win.down('grid'),
            store = grid.getStore();

        Ext.MessageBox.confirm('Delete all entries', 'Do you really want to delete all entries', function(response) {
            if(response !== 'yes') {
                return false;
            }

            grid.setLoading(true);

            store.removeAll();

            Ext.Ajax.request({
                url: '{url controller="error_reporter" action="delete"}',
                success: function() {
                    store.load(function() { grid.setLoading(false); })
                }
            })
        })
    }
});