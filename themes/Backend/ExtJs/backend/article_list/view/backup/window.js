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
//{block name="backend/article_list/view/backup/window"}
Ext.define('Shopware.apps.ArticleList.view.Backup.Window', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.multi-edit-backup-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * Set border layout for the window
     * @string
     */

    /**
     * Define window width
     * @integer
     */
    width: 820,

    /**
     * Define window height
     * @integer
     */
    height: 400,

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
    stateful: false,

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=backup/windowTitle}Revert changes{/s}',

    resizable: true,

    /**
     * Set the windows layout to "fit"
     */
    layout: 'fit',


    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;


        me.items = [{
            xtype: 'container',
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            items: [
                me.getNotificationBox(),
                me.getLabel(),
                {
                    xtype: 'multi-edit-backup-grid',
                    store: me.backupStore,
                    flex: 1,
                    border: 0
                }
            ]
        }];

        me.callParent(arguments);
    },

    /**
     * Returns a label with additional descriptions
     *
     * @returns { { xtype: string, margin: string, items: Array } }
     */
    getLabel: function() {
        var me = this;

        return {
            xtype: 'container',
            margin: '0 10 10 10',
            items: [{
                xtype: 'label',
                html: '{s name=backup/description}A revert is not a full database backup. Only those fields modified during the batch edit will be reverted.{/s}'
            }]
        };
    },

    /**
     * Returns a warning block-message
     *
     * @returns Ext.container.Container
     */
    getNotificationBox: function() {
        var me = this,
            notification = Shopware.Notification.createBlockMessage('{s name=backup/notice}This functionality is not a replacement for backups{/s}', 'error');

        notification.margin = '10 5';

        return notification;
    }
});
//{/block}
