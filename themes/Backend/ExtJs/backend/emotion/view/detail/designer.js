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
//{block name="backend/emotion/view/detail/designer"}
Ext.define('Shopware.apps.Emotion.view.detail.Designer', {
    extend: 'Ext.panel.Panel',
    title: '{s name=title/designer_tab}Designer{/s}',
    alias: 'widget.emotion-detail-designer',
    layout: {
        type: 'auto',
        align: 'stretch',
        pack: 'start'
    },
    bodyPadding: '20 0',
    autoScroll: true,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('openSettingsWindow');

        me.dockedItems = [ me.createToolbar() ];

        me.items = me.createGridView();
        me.callParent(arguments);
    },

    createToolbar: function() {
        var me = this;

        me.addEvents('preview');

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            xtype: 'toolbar',
            dock: 'top',
            ui: 'shopware-ui',
            items: me.createToolbarItems()
        });

        return me.toolbar;
    },

    createToolbarItems: function() {
        var me = this;

        var previewButton = Ext.create('Ext.button.Split', {
            iconCls: 'sprite-globe--arrow',
            text: '{s name="toolbar/preview"}{/s}',
            handler: function() {
                me.fireEvent('preview', me, 0, me.emotion, me.dataviewStore)
            },
            menu: Ext.create('Ext.menu.Menu', {
                items: [
                    { text: '{s name="list/action_column/copy_desktop" namespace="backend/emotion/list/grid"}Als Desktop Einkaufswelt{/s}', iconCls: 'sprite-imac', handler: function() { me.fireEvent('preview', me, 0, me.emotion, me.dataviewStore); } },
                    { text: '{s name="list/action_column/copy_tabletLandscape" namespace="backend/emotion/list/grid"}Als Tablet Landscape Einkaufswelt{/s}', iconCls: 'sprite-ipad--landscape', handler: function() { me.fireEvent('preview', me, 1, me.emotion, me.dataviewStore); } },
                    { text: '{s name="list/action_column/copy_tablet" namespace="backend/emotion/list/grid"}Als Tablet Portrait Einkaufswelt{/s}', iconCls: 'sprite-ipad--portrait', handler: function() { me.fireEvent('preview', me, 2, me.emotion, me.dataviewStore); } },
                    { text: '{s name="list/action_column/copy_mobileLandscape" namespace="backend/emotion/list/grid"}Als mobile Landscape Einkaufswelt{/s}', iconCls: 'sprite-iphone--landscape', handler: function() { me.fireEvent('preview', me, 3, me.emotion, me.dataviewStore); } },
                    { text: '{s name="list/action_column/copy_mobileLandscape" namespace="backend/emotion/list/grid"}Als mobile Landscape Einkaufswelt{/s}', iconCls: 'sprite-iphone--landscape', handler: function() { me.fireEvent('preview', me, 3, me.emotion, me.dataviewStore); } },
                    { text: '{s name="list/action_column/copy_mobile" namespace="backend/emotion/list/grid"}Als mobile Portrait Einkaufswelt{/s}', iconCls: 'sprite-iphone--portrait', handler: function() { me.fireEvent('preview', me, 4, me.emotion, me.dataviewStore); } }
                ]
            })
        });

        return [ previewButton ];
    },

    createGridView: function() {
        var me = this, dataview;

        dataview = Ext.create('Shopware.apps.Emotion.view.detail.Grid', {
            store: me.dataviewStore
        });

        return [ dataview ];
    }
});
//{/block}