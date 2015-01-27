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

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/window"}
Ext.define('Shopware.apps.ArticleList.view.main.Window', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.multiedit-main-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow: true,

    /**
     * Set border layout for the window
     * @string
     */
    layout: 'border',

    /**
     * Define window width
     * @integer
     */
    width: 1100,

    /**
     * Define window height
     * @integer
     */
    height: '90%',

    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable: true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable: true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful: true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId: 'shopware-multiedit-main-window',

    /**
     * Title of the window.
     * @string
     */
    base_title: '{s name=main_window_title}Product overview{/s}',

    resizable: true,

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.items = [{
            xtype: 'multi-edit-main-grid',
            region: 'center',
            columnConfig: me.columnConfig
        }, {
            xtype: 'multi-edit-sidebar',
            region: 'west'

        }];

        me.title = me.base_title;

        me.callParent(arguments);
    },

    /**
     *  Set the window title
     *
     * @param addition
     */
    setWindowTitle: function(addition) {
        var me = this;

        if (addition) {
            me.setTitle(me.base_title + ' - ' + Ext.util.Format.stripTags(addition));
        } else {
            me.setTitle(me.base_title);
        }
    }
});
//{/block}
