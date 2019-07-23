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
    resizable: true,
    collapsible: false,
    maximizable: true,
    minimizable: true,
    autoShow: true,

    showPreview: false,

    height: '92%',
    width: '92%',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

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
                /*{if {acl_is_allowed privilege=create} || {acl_is_allowed privilege=update}}*/
                {
                    text: '{s name="window/button/save_as_preset"}{/s}',
                    cls: 'secondary',
                    handler: function() {
                        me.fireEvent('saveAsPreset', me.emotion);
                    }
                },
                /*{/if}*/
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

        me.sidebar = Ext.create('Ext.tab.Panel', {
            flex: 1,
            name: 'sidebar',
            items: [
                me.createSettingsTab(),
                me.createLayoutTab(),
                me.createWidgetTab()
            ]
        });

        me.mainForm = Ext.create('Ext.form.Panel', {
            items: [ me.sidebar ],
            border: false,
            layout: { type: 'hbox', align: 'stretch' },
            width: 450,
            collapsible: window.innerWidth < 1920,
            collapseDirection: 'left',
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'emotion',
                translationMerge: false,
                translationKey: me.emotion.get('id')
            }]
        });
        me.attributeForm = me.createAttributeTab();
        me.sidebar.add(me.attributeForm);
        me.mainForm.loadRecord(me.emotion);

        me.attributeForm.loadAttribute(me.emotion.get('id'));

        me.settingsForm.setDevices();

        return me.mainForm;
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
            flex: 1,
            emotion: me.emotion,
            mainWindow: me,
            activePreview: me.showPreview
        });
    },

    createAttributeTab: function() {
        var me = this;

        return Ext.create('Shopware.attribute.Form', {
            table: 's_emotion_attributes',
            bodyPadding: 20,
            fieldSetPadding: 5,
            listeners: {
                activate: function() {
                    me.designer.hide();
                    me.mainForm.setWidth(me.getWidth());
                },
                deactivate: function() {
                    me.mainForm.setWidth(450);
                    me.designer.show();
                }
            },
            style: 'background: rgb(240, 242, 244)',
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            translationForm: me.mainForm,
            autoScroll: true
        });
    }
});
//{/block}
