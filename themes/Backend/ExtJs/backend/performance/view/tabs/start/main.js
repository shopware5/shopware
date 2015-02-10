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

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/tabs/start"}
Ext.define('Shopware.apps.Performance.view.tabs.start.Main', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.performance-tabs-start-main',
    title: '{s name=tabs/start/title}{/s}',
    cls: 'performance-view-start',
    bodyCls: 'performance-view-start-body',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    bodyPadding: 20,

    listeners: {
        afterrender: function () {
            var me = this;
            me.fireEvent('init-toggle-productive', me);
        }
    },

    /**
     * Initializes the component, sets up toolbar and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.getItems();

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.getButtons()
        }];

        me.callParent(arguments);
    },

    /**
     * get toolbar buttons
     * @return Array
     */
    getButtons: function() {
        var me = this;

        return ['->', {
            text: '{s name=form/buttons/clear_all}{/s}',
            action: 'clear-all',
            cls: 'primary'
        }];
    },

    /**
     * get Panel Items
     * @return Array
     */
    getItems: function() {
        var me = this;

        var clearText = '';
        clearText += '{s name=tabs/start/info_text_clear_all}{/s}:<br/>';
        clearText += '<ul>';
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line1}{/s}</li>';
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line2}{/s}</li>';
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line3}{/s}</li>';
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line4}{/s}</li>';
        clearText += '</ul>';

        me.toggleButton = me.createToggleButton();
        me.toggleText = me.createToggleText();

        return [
            {
                xtype: 'container',
                cls: 'toggle-container',
                layout: 'hbox',
                padding: 20,
                items: [
                    me.toggleButton,
                    me.toggleText
                ]
            },
            {
                xtype: 'component',
                html: clearText
            }
        ];
    },

    /**
     * @return Ext.Component text
     */
    createToggleText: function() {
        var me = this, html;
        
        html =
            '<h2>{s name=tabs/start/productive_mode_loading}{/s}</h2>';
        
        return Ext.create('Ext.Component', {
            html: html,
            flex: 1
        });
    },

    /**
     * get Button to toggle productive mode
     * @return Ext.Component button
     */
    createToggleButton: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            cls: 'toggle-button',
            height: 24,
            width: 90,
            hidden: true,
            listeners: {
                afterrender: function(comp) {
                    comp.el.on('click', function() {
                        me.fireEvent('toggle-productive', me);
                    });
                }
            }
        });
    },
    
    setState: function(state) {
        var me = this;

        me.toggleButton.show();

        if (state == true) {
            me.toggleText.update(
                '<h2>{s name=tabs/start/production_mode_title}{/s}</h2>' +
                '<br/>{s name=tabs/start/production_mode_description}{/s}'
            );
            me.toggleButton.addCls('active');
        } else {
            me.toggleText.update(
                '<h2>{s name=tabs/start/development_mode_title}{/s}</h2>' +
                '<br/>{s name=tabs/start/development_mode_description}{/s}'
            );
            me.toggleButton.removeCls('active');
        }
    }
});
//{/block}