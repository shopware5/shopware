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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/detail/preview"}
Ext.define('Shopware.apps.Emotion.view.detail.Preview', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-detail-preview',
    title: "{s name='window/preview/title' namespace='backend/emotion/list/grid'}Shopping world Preview{/s}: ",
    border: false,
    resizable: false,
    maximizable: false,
    layout: 'fit',
    height: '90%',

    _scrollbarOffset: 30,
    _deviceWidth: {
        desktop: 1280,
        tabletLandscape: 1024,
        tabletPortrait: 768,
        mobileLandscape: 480,
        mobilePortrait: 320
    },

    initComponent: function() {
        var me = this;

        me._previewSrc = '{url module=widgets controller=emotion action=preview}/?emotionId=' + me.emotionId;
        me.previewElement = me.createPreviewElement();

        me.dockedItems = [ me.createToolbar() ];
        me.items = [{
            xtype: 'panel',
            layout: 'fit',
            items: [ me.previewElement ]
        }];
        
        if(me.deviceId % 1 !== 0) {
            var devices = me.deviceId.split(',');

            me.deviceId = parseInt(devices[0], 10);
        }
        
        if(me.deviceId > -1) {
            var device = 'desktop';
            switch(me.deviceId) {
                case 1:
                    device = 'tabletLandscape';
                    break;
                case 2:
                    device = 'tabletPortrait';
                    break;
                case 3:
                    device = 'mobileLandscape';
                    break;
                case 4:
                    device = 'mobilePortrait';
                    break;
                case 0:
                default:
                    device = 'desktop';
                    break;
            }
            me.width = me._deviceWidth[device] + me._scrollbarOffset;
        }

        me.callParent(arguments);

        // Set the window title
        me.setTitle(me.title + me.emotionName);
    },

    createPreviewElement: function() {
        var me = this,
            previewElement;

        previewElement = Ext.create('Ext.Component', {
            autoEl : {
                tag : "iframe",
                src : me._previewSrc
            }
        });

        return previewElement;
    },

    createToolbar: function() {
        var me = this,
            toolbar;

        me.reloadButton = Ext.create('Ext.Button', {
            text: '{s name="toolbar/reload"}{/s}',
            iconCls: 'sprite-arrow-circle-315',
            handler: Ext.bind(me.onReload, me)
        });

        me.changeSizeButton = Ext.create('Ext.button.Split', {
            text: '{s name="toolbar/show_as"}{/s}',
            iconCls: 'sprite-view-as',
            menu: Ext.create('Ext.menu.Menu', {
                items: [
                    { text: '{s name="list/action_column/copy_desktop" namespace="backend/emotion/list/grid"}Als Desktop Einkaufswelt{/s}', iconCls: 'sprite-imac', handler: function() {

                        me.checkAvailability(0);
                        me.setWidth(me._deviceWidth.desktop);
                        me.center();
                    } },
                    { text: '{s name="list/action_column/copy_tablet_landscape" namespace="backend/emotion/list/grid"}Als Tablet Landscape Einkaufswelt{/s}', iconCls: 'sprite-ipad--landscape', handler: function() {

                        me.checkAvailability(1);
                        me.setWidth(me._deviceWidth.tabletLandscape);
                        me.center()
                    } },
                    { text: '{s name="list/action_column/copy_tablet" namespace="backend/emotion/list/grid"}Als Tablet Portrait Einkaufswelt{/s}', iconCls: 'sprite-ipad--portrait', handler: function() {

                        me.checkAvailability(2);
                        me.setWidth(me._deviceWidth.tabletPortrait);
                        me.center();
                    } },
                    { text: '{s name="list/action_column/copy_mobile_landscape" namespace="backend/emotion/list/grid"}Als Mobile Landscape Einkaufswelt{/s}', iconCls: 'sprite-iphone--landscape', handler: function() {

                        me.checkAvailability(3);
                        me.setWidth(me._deviceWidth.mobileLandscape);
                        me.center()
                    } },
                    { text: '{s name="list/action_column/copy_mobile" namespace="backend/emotion/list/grid"}Als Mobile Portrait Einkaufswelt{/s}', iconCls: 'sprite-iphone--portrait', handler: function() {

                        me.checkAvailability(4);
                        me.setWidth(me._deviceWidth.mobilePortrait);
                        me.center()
                    } }
                ]
            })
        });

        toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [ me.reloadButton, me.changeSizeButton, me.showShopLayout ]
        });

        return toolbar;
    },

    checkAvailability: function(deviceIdentifier) {
        var me = this;

        /**
         * Device Indentifier:
         *
         * 0 Desktop
         * 1 Tablet Landscape
         * 2 Tablet Portrait
         * 3 Smartphone Landscape
         * 4 Smartphone Portrait
         */

        Ext.Ajax.request({
            url: '{url action=checkAvailability emotionId=""}' + me.emotionId + '/deviceId/' + deviceIdentifier,
            success: function(response, opts) {
                var status = Ext.decode(response.responseText);

                if(!status.success || !status.alreadyExists) {
                    return;
                }

                Shopware.Notification.createGrowlMessage('{s name="save/warning/title"}Warning{/s}', '{s name="save/warning/otherEmotion"}{/s}');
            }
        });
    },

    onReload: function() {
        var me = this,
            iframe = me.previewElement.getEl().dom;

        me.setLoading(true);

        iframe.contentWindow.location.reload();

        me.setLoading(false);
    }
});
//{/block}
