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
 * Shopware Widget Controller
 *
 * This controller handles the widget window, its widgets, settings and events.
 */

//{namespace name=backend/index/controller/widgets}
//{block name="backend/index/controller/widgets"}

Ext.define('Shopware.apps.Index.controller.Widgets', {

    extend: 'Enlight.app.Controller',

    /**
     * @default null
     * @Ext.container.Viewport
     */
    viewport: null,

    /**
     * @default null
     * @Ext.container.Container
     */
    desktop: null,

    /**
     * Store of all available widgets
     */
    widgetStore: null,

    /**
     * Store of all user widget settings
     */
    widgetSettingsStore: null,

    /**
     * Store of the current users widget settings
     */
    widgetSettings: null,

    /**
     * The main widget window, will be initialized when the widget and widget settings stores are loaded
     */
    widgetWindow: null,

    /**
     * Translation snippets for multilanguage usage
     */
    snippets: {
        error: {
            viewportNotLoaded: '{s name="error/viewportNotLoaded"}Viewport is not loaded.{/s}',
            settingsInitialisation: '{s name="error/settingsInitialisation"}Widget settings could not be initialized.{/s}',
            deleteWidget: '{s name="error/deleteWidget"}An Error occurred while attempting to delete the widget.\n\n{/s}',
            merchantWidgetError: '{s name="error/merchantWidgetError"}An error occurred in the merchants widget{/s}'
        },

        titles: {
            allow_merchant: '{s name=titles/allow_merchant}Unlock merchant{/s}',
            decline_merchant: '{s name=titles/decline_merchant}Decline merchant{/s}'
        }
    },

    /**
     * Initializes the widget controller.
     * Creates the widget store and binds all needed events.
     */
    init: function () {
        var me = this, firstRunWizardStep;

        firstRunWizardStep = parseInt(Ext.util.Cookies.get('firstRunWizardStep'), 10);

        if (!me.subApplication.firstRunWizardEnabled || Ext.isEmpty(firstRunWizardStep) || isNaN(firstRunWizardStep) || firstRunWizardStep == 0) {
            me.loadWidgetPanel();
        }

        me.callParent(arguments);
    },

    initUpdateWizard: function() {
        var me = this;

        if (me.subApplication.updateWizardStarted) {
            return;
        }

        Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.PluginManager',
                params: {
                    hidden: true
                }
            },
            undefined,
            function() {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.UpdateWizard'
                });
            }
        );
    },

    loadWidgetPanel: function () {
        /*{if {acl_is_allowed resource=widgets privilege=read}}*/
        var me = this;

        me.viewport = Shopware.app.Application.viewport;

        if (!me.viewport) {
            me.initUpdateWizard();
            Ext.Error.raise(me.snippets.error.viewportNotLoaded);
        }

        me.desktop = me.viewport.getActiveDesktop();

        me.widgetStore = me.getStore('Widget');
        me.widgetStore.load({
            callback: me.onWidgetStoreLoaded.bind(me)
        });

        me.taskBarBtn = Ext.getCmp('widgetTaskBarBtn');
        me.taskBarBtn.on('click', me.toggleMinimizeWindow.bind(me));

        if (window.Notification){
            me.notificationBtn = Ext.getCmp('notificationTaskBarBtn');
            me.notificationBtn.on('click', me.askForNotificationPermission.bind(me));
        }

        me.control({
            'widget-sidebar-window': {
                minimizeWindow: me.onMinimizeWindow,
                changePosition: me.onChangePosition,
                saveWidgetPosition: me.onSaveWidgetPosition,
                saveWidgetPositions: me.onSaveWidgetPositions,
                addWidget: me.onAddWidget,
                saveWindowSize: me.onSaveWindowSize
            },

            'widget-base': {
                closeWidget: me.onCloseWidget
            },

            'swag-merchant-widget': {
                allowMerchant: function (record) {
                    me.onOpenMerchantDetail('allow', record);
                },
                declineMerchant: function (record) {
                    me.onOpenMerchantDetail('decline', record);
                }
            }
        });

        /*{/if}*/
    },

    /**
     * Called when the available widgets are loaded.
     * Creates the WidgetSettings store to get all user widget settings.
     */
    onWidgetStoreLoaded: function () {
        var me = this;

        me.widgetSettingsStore = me.getStore('WidgetSettings');

        me.widgetSettingsStore.load({
            callback: me.onWidgetSettingsLoaded.bind(me)
        });
    },

    /**
     * Called when the widget settings were loaded.
     * Searches for the settings of the current user and if they could not be found, default settings will be created.
     * After the settings are available, the widget window will be created.
     */
    onWidgetSettingsLoaded: function () {
        var me = this,
            authId = ~~(me.widgetStore.getProxy().getReader().jsonData.authId),
            settings = me.getWidgetSettingsByAuthId(authId),
            minimized;

        if (!settings) {
            me.widgetSettingsStore.add({
                authId: authId,
                height: 690,
                columnsShown: 1,
                dock: 'tl',
                minimized: false
            });

            me.widgetSettingsStore.sync();

            settings = me.getWidgetSettingsByAuthId(authId);
        }

        if (!settings) {
            me.initUpdateWizard();
            Ext.Error.raise(me.snippets.error.settingsInitialisation);
        }

        minimized = settings.get('minimized');

        me.widgetSettings = settings;

        me.widgetWindow = me.getView('widgets.Window').create({
            widgetStore: me.widgetStore,
            desktop: me.desktop,
            widgetSettings: me.widgetSettings,
            minimized: minimized
        });

        if (!minimized) {
            me.taskBarBtn.getEl().addCls('btn-over');
        }
        me.initUpdateWizard();
    },

    /**
     * Returns widgetsettings that have the same authId as the user.
     * If no settings were found, null will be returned.
     *
     * @param authId
     * @returns { Object }|null
     */
    getWidgetSettingsByAuthId: function (authId) {
        var me = this,
            settings = null;

        me.widgetSettingsStore.each(function (record) {
            if (record.get('authId') === authId) {
                settings = record;
                return false;
            }
        });

        return settings;
    },

    /**
     * Minimizes the window and saves the change to the localStorage.
     */
    onMinimizeWindow: function () {
        var me = this,
            btn = me.taskBarBtn,
            win = me.widgetWindow;

        btn.disable();

        win.hide(btn, function () {
            win.minimized = true;

            btn.enable();
            btn.removeCls('btn-over');
        });

        me.widgetSettings.set('minimized', true);
        me.widgetSettingsStore.sync();
    },

    showWindow: function () {
        var me = this,
            btn = me.taskBarBtn,
            win = me.widgetWindow;

        btn.disable();

        win.show(btn, function () {
            win.minimized = false;

            btn.enable();
            btn.addCls('btn-over');
            win.toFront();

            me.onChangePosition(win, me.widgetSettings.get('dock'), true);
        });

        me.widgetSettings.set('minimized', false);
        me.widgetSettingsStore.sync();
    },

    toggleMinimizeWindow: function () {
        var me = this,
            win = me.widgetWindow;

        if (win.minimized) {
            me.showWindow();
        } else {
            me.onMinimizeWindow();
        }

        win.minimized = !win.minimized;
    },

    /**
     * Moves / aligns the window to a given corner.
     *
     * @param { Shopware.apps.Index.view.widgets.Window } win
     * @param { String } align - on what corner the window should be aligned (tl, tr, bl, br)
     *
     *        tl - top left
     *        tr - top right
     *        bl - bottom left
     *        br - bottom right
     *
     * @param { Boolean } animate - flag whether or not the position change should be animated
     */
    onChangePosition: function (win, align, animate) {
        var me = this,
            xOffset = 10,
            yOffset = 10,
            desktopEl = me.desktop.getEl(),
            x = xOffset,
            y = yOffset,
            verticalHandle = 's',
            horizontalHandle = 'e',
            handles = [];

        if (align.indexOf('b') !== -1) {
            y = desktopEl.getHeight() - win.getHeight() - yOffset;
            verticalHandle = 'n';
        }

        if (align.indexOf('r') !== -1) {
            x = desktopEl.getWidth() - win.getWidth() - xOffset;
            horizontalHandle = 'w';
        }

        win.setPosition(x, y, animate !== false);

        me.widgetSettings.set('dock', align);
        me.widgetSettingsStore.sync();

        if (win.resizer) {
            handles.push(verticalHandle);
            handles.push(horizontalHandle);
            handles.push(verticalHandle + horizontalHandle);

            win.handleResizer(handles);
        }
    },

    /**
     * Saves the position of a single widget.
     *
     * @param column
     * @param row
     * @param internalId
     */
    onSaveWidgetPosition: function (column, row, internalId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=savePosition}',
            params: {
                column: column,
                position: row,
                id: internalId
            },

            callback: function () {
                me.widgetStore.load();
            }
        });
    },

    /**
     * Sends an ajax request to save to position changes of multiple widgets.
     * Expects an array of position informations which should should like the following:
     *
     * [
     *   {
     *     column: columnIndex,
     *     position: rowIndex,
     *     id: widgetId
     *   }
     * ]
     *
     * @param widgets
     */
    onSaveWidgetPositions: function (widgets) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=savePositions}',
            jsonData: {
                widgets: widgets
            },

            callback: function () {
                me.widgetStore.load();
            }
        });
    },

    /**
     * Add a new widget of the given type to the first (most left) column.
     * Sends an ajax request to save it server side.
     *
     * @param win
     * @param widgetName
     * @param menuItem
     */
    onAddWidget: function (win, widgetName, menuItem, data) {
        var me = this,
            container = win.containerCollection.getAt(0),
            widget = me.widgetStore.findRecord('name', widgetName);

        data = data || {};

        if (menuItem) {
            menuItem.disable();
        }

        Ext.Ajax.request({
            url: '{url controller=widgets action=addWidgetView}',
            jsonData: {
                id: widget.get('id'),
                column: 0,
                position: container.items.getCount() - 1,
                data: data
            },
            callback: function (options, success, res) {
                if (!success) {
                    return;
                }

                var response = Ext.decode(res.responseText);

                me.widgetStore.load({
                    callback: function () {
                        widget = me.widgetStore.findRecord('name', widgetName);

                        var newWidget = win.createWidget(widgetName, widget.get('id'), me.getWidgetViewById(widget, response.viewId), widget.get('label'));

                        container.insert(newWidget.position.rowId, newWidget);

                        if (menuItem) {
                            menuItem.enable();
                        }
                    }
                });
            }
        });
    },

    /**
     * Helper function to get the widget view by the view id in the widget model
     *
     * @param widget
     * @param id
     */
    getWidgetViewById: function (widget, id) {
        var views = widget.get('views'),
            widgetView = null;

        Ext.each(views, function (view) {
            if (view.id === id) {
                widgetView = view;
            }
        });

        return widgetView;
    },

    /**
     * Closes the widget which contained the close button.
     * Sends an ajax request to save the closing.
     */
    onCloseWidget: function (widget) {
        var me = this,
            container = me.widgetWindow.containerCollection.getAt(widget.position.columnId);

        Ext.Ajax.request({
            url: '{url controller=widgets action=removeWidgetView}',
            params: {
                id: widget.viewId
            },
            callback: function (options, success, res) {
                var response = Ext.decode(res.responseText);

                if (!response.success) {
                    Shopware.notification.createGrowlMessage(me.snippets.error.deleteWidget + response.message);
                    return;
                }

                me.widgetStore.load({
                    callback: function () {
                        container.remove(widget, true);
                    }
                });
            }
        });
    },

    /**
     * Saves the new shown columns count and height in px to the localStorage
     *
     * @param columnsShown
     * @param height
     */
    onSaveWindowSize: function (columnsShown, height) {
        var me = this;

        me.widgetSettings.set('columnsShown', columnsShown);
        me.widgetSettings.set('height', height);
        me.widgetSettingsStore.sync();
    },

    /**
     * Event listener method which will be called after the user
     * clicks on the "allow" or "decline" icon in the action column.
     *
     * Opens the detail window to send an email to the customer.
     *
     * @public
     * @event click
     * @param { string } mode - Allow or decline
     * @param { object } record - Shopware.apps.Index.model.Merchant
     * @return void
     */
    onOpenMerchantDetail: function (mode, record) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=widgets action=requestMerchantForm}',
            params: {
                id: ~~(1 * record.get('id')),
                customerGroup: record.get('validation'),
                mode: mode
            },
            success: function (response) {
                var model = me.getModel('MerchantMail');
                response = Ext.decode(response.responseText);

                if (response.success === true) {
                    me.getView('merchant.Window').create({
                        record: model.create(response.data),
                        mode: mode,
                        title: (mode === 'allow') ? me.snippets.titles.allow_merchant : me.snippets.titles.decline_merchant
                    }).show();
                } else {
                    if (response.message) {
                        message = response.message;
                    } else {
                        message = me.snippets.error.merchantWidgetError;
                    }

                    Shopware.Notification.createStickyGrowlMessage({
                        title: (mode === 'allow') ? me.snippets.titles.allow_merchant : me.snippets.titles.decline_merchant,
                        text: message,
                        log: true
                    });
                }
            }
        });
    },

    askForNotificationPermission: function () {
        if (!'Notification' in window) {
            return;
        }

        var me = this,
            tip,
            position;

        if (Notification.permission === 'default') {
            Ext.MessageBox.confirm(
                '{s name="footer/notification/modal_title" namespace="backend/index/view/detail"}Show notifications on desktop?{/s}',
                '{s name="footer/notification/modal_content" namespace="backend/index/view/detail"}To show growl notifications, {/s}', function (response) {
                if (response !== 'yes') {
                    return false;
                }

                Notification.requestPermission().then( function (result) {
                    if (result === 'denied' ){
                        Ext.MessageBox.alert('{s name="footer/notification/denied_title" namespace="backend/index/view/detail"}Denied{/s}',
                            '{s name="footer/notification/denied_content" namespace="backend/index/view/detail"}If you change your mind later, please go to your browser settings for this site.{/s}');
                    }

                    me.notificationBtn.addCls('btn-over');
                });
            });

            return;
        }

        var tooltipText = '<span>{s name="footer/notification/granted_denied_tooltip" namespace="backend/index/view/detail"}Permission was already granted or denied{/s}</span><div class="x-clear"></div><div class="arrow"></div>';
        if (document.location.protocol !== 'https:') {
            tooltipText = '<span>{s name="footer/notification/missing_https_content_tooltip" namespace="backend/index/view/detail"}To be able to use desktop notifications, please make sure you\'re using an SSL encrypted connection.{/s}</span><div class="x-clear"></div><div class="arrow"></div>'
        }

        // Create tooltip
        tip = Ext.create('Ext.tip.ToolTip', {
            target: me.notificationBtn,
            shadow: false,
            ui: 'shopware-ui',
            cls: 'notification-tooltip',
            html: tooltipText
        });

        position = me.notificationBtn.getPosition();

        position[1] = position[1] - 40;
        tip.showAt(position);
    }
});
//{/block}
