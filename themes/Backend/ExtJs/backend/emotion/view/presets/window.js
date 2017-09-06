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

//{namespace name=backend/emotion/presets/presets}

/**
 * Emotion Presets Window
 *
 * This file contains the logic for the preset selection view of a shopping world.
 */
//{block name="backend/emotion/view/presets/window"}
Ext.define('Shopware.apps.Emotion.view.presets.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-presets-window',

    stateful: true,
    stateId: 'emotion-presets-window',

    border: false,
    autoShow: true,
    modal: true,

    showPreview: false,

    height: '82%',
    width: 1200,

    layout: 'border',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = '{s name="window/title"}{/s}';

        me.items = [
            me.createList(),
            me.createDetailView()
        ];

        me.buttons = me.getButtons();

        me.addEvents('emotionpresetselect');

        me.callParent(arguments);
    },

    createList: function() {
        var me = this;

        me.listPanel = Ext.create('Shopware.apps.Emotion.view.presets.List',{
            region: 'center',
            flex: 3,
            store: Ext.create('Shopware.apps.Emotion.store.Presets', {
                autoLoad: true
            })
        });

        return me.listPanel;
    },

    createDetailView: function() {
        var me = this;

        me.infoView = Ext.create('Shopware.apps.Emotion.view.presets.Info', {
            collapsible: false,
            resizable: false,
            title: 'Details',
            region: 'east',
            width: 400
        });

        return me.infoView;
    },

    getButtons: function() {
        var me = this;

        return [{
            text: '{s name=use_preset}{/s}',
            cls: 'primary',
            handler: function () {
                me.fireEvent('emotionpresetselect');
            }
        }];
    }
});
//{/block}
