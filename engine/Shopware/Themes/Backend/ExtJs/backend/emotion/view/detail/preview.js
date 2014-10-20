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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
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

    _deviceWidth: {
        desktop: 1024,
        tablet: 768,
        mobile: 320
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

        if(me.deviceId > -1) {
            var device = 'desktop';
            switch(me.deviceId) {
                case 1:
                    device = 'tablet';
                    break;
                case 2:
                    device = 'mobile';
                    break;
                case 0:
                default:
                    device = 'desktop';
                    break;
            }
            me.width = me._deviceWidth[device];
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
            iconCls: 'sprite-switch',
            menu: Ext.create('Ext.menu.Menu', {
                items: [
                    { text: '{s name="list/action_column/copy_desktop" namespace="backend/emotion/list/grid"}Als Desktop Einkaufswelt{/s}', iconCls: 'sprite-imac-icon', handler: function() {
                        me.setWidth(me._deviceWidth.desktop);
                        me.center();
                    } },
                    { text: '{s name="list/action_column/copy_tablet" namespace="backend/emotion/list/grid"}Als Tablet Einkaufswelt{/s}', iconCls: 'sprite-ipad-icon', handler: function() {
                        me.setWidth(me._deviceWidth.tablet);
                        me.center()
                    } },
                    { text: '{s name="list/action_column/copy_mobile" namespace="backend/emotion/list/grid"}Als mobile Einkaufswelt{/s}', iconCls: 'sprite-iphone-icon', handler: function() {
                        me.setWidth(me._deviceWidth.mobile);
                        me.center();
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

    onReload: function() {
        var me = this,
            iframe = me.previewElement.getEl();

        me.setLoading(true);
        iframe.dom.addEventListener('load', function() {
            iframe.dom.removeEventListener('load');
            me.setLoading(false);
        });

        iframe.dom.contentDocument.location.href = me._previewSrc;
    }
});
//{/block}
