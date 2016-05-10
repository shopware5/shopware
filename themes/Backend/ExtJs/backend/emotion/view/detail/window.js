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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Emotion Detail Window
 *
 * This file contains the logic for the detail view of a shopping world.
 * It includes the settings and the designer view.
 */
//{block name="backend/emotion/view/detail/window"}
Ext.define('Shopware.apps.Emotion.view.detail.Window', {

    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-detail-window',

    stateful: true,
    stateId: 'emotion-detail-window',

    border: false,
    resizable: false,
    collapsible: false,
    maximizable: true,
    minimizable: true,
    autoShow: true,

    showPreview: false,

    layout: 'border',
    height: '92%',
    width: '92%',

    snippets: {
        windowTitle: '{s name="global/title"}{/s}',
        saveBtnLabel: '{s name="window/button/save_emotion"}{/s}',
        errorTitle: '{s name="save/error/title"}{/s}',
        errorMessage: '{s name="save/error/message_load"}{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.windowTitle;

        me.dockedItems = me.createDockedItems();

        me.registerEvents();

        me.callParent(arguments);

        if (me.emotion) {
            me.loadEmotion(me.emotion);
        }

        me.on('resize', function() {
            me.designer.grid.refresh();
        });
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Event will be fired when the user clicks the save button to save the shopping world.
             *
             * @event saveEmotion
             * @param [Ext.data.Model] The emotion record
             */
            'saveEmotion'
        );
    },

    loadEmotion: function(emotion, activeTab) {
        var me = this;

        try {
            me.emotion = emotion;

            if (me.emotion.get('name')) {
                me.title = me.title + ' - ' + me.emotion.get('name');
            }

            me.removeAll();
            me.add(me.createItems());

            if (Ext.isDefined(activeTab)) {
                me.sidebar.setActiveTab(activeTab);
            }

        } catch (e) {
            Shopware.Notification.createGrowlMessage(
                me.snippets.errorTitle,
                me.snippets.errorMessage
            );

            me.destroy();
        }
    },

    createItems: function() {
        var me = this;

        return [
            me.createSidebar(),
            me.createDesigner()
        ];
    },

    createDockedItems: function() {
        var me = this;

        return me.toolBar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            items: [
                '->',
                {
                    text: me.snippets.saveBtnLabel,
                    cls: 'primary',
                    action: 'emotion-detail-settings-save',
                    handler: function () {
                        me.fireEvent('saveEmotion', me.emotion);
                    }
                }
            ]
        });
    },

    createSidebar: function () {
        var me = this;

        return me.sidebar = Ext.create('Ext.tab.Panel', {
            region: 'west',
            width: '25%',
            minWidth: 450,
            style: {
                borderStyle: 'solid',
                borderColor: '#a4b5c0',
                borderWidth: '0 1px 0 0'
            },
            items: [
                me.createSettingsTab(),
                me.createLayoutTab(),
                me.createWidgetTab()
            ]
        });
    },

    createSettingsTab: function() {
        var me = this;

        return me.settingsForm = Ext.create('Shopware.apps.Emotion.view.detail.Settings', {
            emotion: me.emotion,
            categoryStore: me.categoryStore,
            shopStore: me.shopStore,
            mainWindow: me
        });
    },

    createLayoutTab: function() {
        var me = this;

        return me.layoutForm = Ext.create('Shopware.apps.Emotion.view.detail.Layout', {
            emotion: me.emotion,
            mainWindow: me
        });
    },

    createWidgetTab: function() {
        var me = this;

        return me.widgetsTab = Ext.create('Shopware.apps.Emotion.view.detail.Widgets', {
            emotion: me.emotion,
            libraryStore: me.libraryStore,
            mainWindow: me
        });
    },

    createDesigner: function() {
        var me = this;

        return me.designer = Ext.create('Shopware.apps.Emotion.view.detail.Designer', {
            region: 'center',
            emotion: me.emotion,
            mainWindow: me,
            activePreview: me.showPreview
        });
    }
});
//{/block}
