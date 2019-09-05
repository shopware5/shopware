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

//{namespace name=backend/index/view/detail}

/**
 * Shopware UI - Footer
 *
 * Special Ext.toolbar.Toolbar, which is docked
 * to the bottom and contains a special context
 * menu.
 *
 * Note that the component will be streched to
 * the full viewport width.
 */
//{block name="backend/index/view/footer"}
Ext.define('Shopware.apps.Index.view.Footer', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.footer',
    alternateClassName: 'Shopware.Footer',
    requires: [ 'Shopware.app.WindowManagement' ],

    height: 40,
    dock: 'bottom',
    cls: 'shopware-footer',

    /**
     * Used snippets for this component
     * @object
     */
    snippets: {
        logged_in_as: '{s name=footer/logged_in_as}Logging as{/s}',
        logout_now: '{s name=footer/logout_now}Logout now{/s}',
        minimize_all: '{s name=footer/minimize_all}Minimize all{/s}',
        close_all: '{s name=footer/close_all}Close all{/s}'
    },


    /**
     * Initialize the footer toolbar
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.createBasicItems();
        me.callParent(arguments);

        if(Ext.isObject(Shopware.app.WindowManagement)) {
            Shopware.app.WindowManagement.init(me);
        }
    },

    afterRender: function() {
        var me = this;

        Shopware.app.Application.baseComponentIsReady(me);

        me.callParent(arguments);
    },

    /**
     * Creates the default buttons for the footer taskbar
     *
     * @return void
     */
    createBasicItems: function() {
        var me = this;

        me.logoutBtn = me.createLogoutBtn();
        me.windowBtn = me.createWindowManagementMenu();
        me.widgetBtn = me.createWidgetBtn();
        me.notificationBtn = me.createNotificationMenuButton();

        this.items = [
            me.logoutBtn,
            { xtype: 'tbseparator', cls: 'separator-first' },
            me.windowBtn,
            { xtype: 'tbseparator', cls: 'separator-second' },
            me.widgetBtn,
            { xtype: 'tbseparator', cls: 'separator-third' },
            me.notificationBtn
        ];

    },

    /**
     * Creates the logout button and the responsible tooltip
     * with all neccessary events
     *
     * @return [object] logoutBtn - Ext.button.Button
     */
    createLogoutBtn: function() {
        var me = this, logoutBtn, tip;

        // Create the button
        logoutBtn = Ext.create('Ext.button.Button', {
            cls: 'logout btn-over',
            iconCls: 'logout'
        });

        // Create tooltip
        tip = Ext.create('Ext.tip.ToolTip', {
            target:  logoutBtn,
            shadow: false,
            ui: 'shopware-ui',
            cls: 'logout-tooltip',
            html: me.getLogoutSnippet()
        });

        // Event listener which shows the tooltip
        logoutBtn.on('click', function() {
            var position = logoutBtn.getPosition();
            position[1] = position[1] - 50;
            tip.showAt(position);
        }, this);

        return logoutBtn;
    },

    createWidgetBtn: function() {
        return Ext.create('Ext.button.Button', {
            iconCls: 'widget-sidebar',
            id: 'widgetTaskBarBtn'
        });
    },

    /**
     * Returns the string which is used for the logout
     * tooltip
     *
     * @returns { String } - formatted localized string
     */
    getLogoutSnippet: function() {
        var url = '{url controller="login" action="logout"}',
            s = this.snippets;

        return Ext.String.format('<span>[0] <strong>[1]</strong></span><a href="[2]">[3]</a><div class="x-clear"></div><div class="arrow"></div>', s.logged_in_as, userName, url, s.logout_now);
    },

    /**
     * Creates the button for the window management
     * and the responsible menu
     *
     * return [object] windowBtn - Ext.button.Button
     */
    createWindowManagementMenu: function() {
        var me = this, windowMenu, windowBtn;

        windowMenu = Ext.create('Ext.menu.Menu', {
            shadow: false,
            ui: 'window-managment',
            width: 126,
            plain: true,
            showSeparator: false,
            items: [{
                text: me.snippets.minimize_all,
                iconCls: 'min-all',
                handler: function() {
                    Shopware.app.WindowManagement.minimizeAll();
                }
            }, {
                text: me.snippets.close_all,
                iconCls: 'close-all',
                handler: function() {
                    Shopware.app.WindowManagement.closeAll();
                }
            }]
        });

        windowBtn = Ext.create('Ext.button.Button', {
            cls: 'window',
            iconCls: 'window-managment',
            arrowAlign: 'top',
            menu: windowMenu,
            menuAlign: 'c',
            menuOffset: [-20, -90]
        });

        return windowBtn;
    },

    createNotificationMenuButton: function () {
        if (!window.Notification) {
            return;
        }

        var notificationBtnActive = Notification.permission !== 'default',
            btn = Ext.create('Ext.button.Button', {
                iconCls: 'backend-notification',
                id: 'notificationTaskBarBtn'
            });

        if (notificationBtnActive) {
            btn.cls = 'btn-over';
        }

        return btn;
    }
});
//{/block}
