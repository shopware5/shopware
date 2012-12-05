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
 * @package    Migration
 * @subpackage Form
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Progress dialog
 * A dialog window showing the progress of the import
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/view/progress"}
Ext.define('Shopware.apps.SwagMigration.view.Progress', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls: Ext.baseCSSPrefix + 'migration-progress-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.migration-progress-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * Set border layout for the window
     * @string
     */
    layout: 'fit',
    /**
     * Define window width
     * @integer
     */
    width: 400,
    /**
     * Define window height
     * @integer
     */
    height: 250,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable: false,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable: false,

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=progressTitle}Importing...{/s}',

    /**
     * Makes the progress window modal
     */
    modal: true,

    defaults: {
        style: {
            margin: '10px'
        }
    },


    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createPanel();
        me.callParent(arguments);
    },


    /**
     * Creates the card layout panel
     */
    createPanel: function() {
        var me = this;

        return [{
                xtype: 'label',
                text: 'asdasasd'
            },
            me.createProgressBar()
        ];

    },


    /**
     * Creates the progress bar for the window
     * @return Ext.ProgressBar
     */
    createProgressBar: function() {
        var me = this;

        me.progressBar = Ext.create('Ext.ProgressBar', {

        });


        return me.progressBar;

    }

});
//{/block}
