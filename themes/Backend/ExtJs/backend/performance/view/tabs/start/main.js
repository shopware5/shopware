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

        /*{if {acl_is_allowed privilege=clear}}*/
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.getButtons()
        }];
        /*{/if}*/

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
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line3}{/s}</li>';
        clearText += '<li>{s name=tabs/start/info_text_clear_all_line4}{/s}</li>';
        clearText += '</ul>';

        me.radioGroup = me.createRadioGroup();
        return [{
            xtype   : 'container',
            cls     : 'radiogroup-container',
            padding : 20,
            items   : [me.radioGroup]
        }, {
            xtype   : 'component',
            flex    : 1,
            html    : clearText
        }];
    },

    /**
     * get Radiogroup to change productive mode
     * @return Ext.Component button
     */
    createRadioGroup: function() {
        var me = this;

        return Ext.create('Ext.form.RadioGroup', {
            columns : 1,
            items   : [
                { name: 'productiveMode', inputValue: true, boxLabel: '<b>{s name=tabs/start/production_mode_title}{/s}</b>'/*{if !{acl_is_allowed privilege=update}}*/, disabled: true/*{/if}*/ },
                { xtype: 'component', cls:'component-first', html: '{s name=tabs/start/production_mode_description}{/s}'},
                { name: 'productiveMode', inputValue: false, boxLabel: '<b>{s name=tabs/start/development_mode_title}{/s}</b>'/*{if !{acl_is_allowed privilege=update}}*/, disabled: true/*{/if}*/ },
                { xtype: 'component', html: '{s name=tabs/start/development_mode_description}{/s}' }
            ],
            listeners: {
                change: function (elem, newValue, oldValue) {
                    if (
                        !Ext.isEmpty(oldValue.productiveMode)
                        && !Ext.isEmpty(newValue.productiveMode)
                        && oldValue.productiveMode !== newValue.productiveMode
                    ) {
                        me.fireEvent('toggle-productive', me)
                    }
                }
            }
        });
    },

    setState: function(state) {
        var me = this,
            productiveMode = (state === true);

        me.radioGroup.setValue({
            productiveMode: productiveMode
        });
    },

    resetState: function(state) {
        var me =  this;

        me.radioGroup.setValue({ productiveMode: !me.radioGroup.getValue().productiveMode });
    }
});
//{/block}
