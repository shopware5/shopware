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
 * @package    Ticket
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/types/window"}
Ext.define('Shopware.apps.Ticket.view.settings.types.Window', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Enlight.app.Window',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-types-window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-types-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border:false,

    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,

    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',

    /**
     * Define window width
     * @integer
     */
    width:550,

    /**
     * Define window height
     * @integer
     */
    height:180,

    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-ticket-types-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=types_window_title}Ticket system - Add / edit type{/s}',

    border: false,
    bodyBorder: 0,

    /**
     * Initializes the component and the
     * main tab panel.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('saveType');
        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createActionToolbar() ];

        me.callParent(arguments);

        if(me.record) {
            me.formPanel.loadRecord(me.record);
        }
    },

    /**
     * Creates the form panel to add / edit
     * a type.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        return me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            bodyBorder: 0,
            bodyPadding: 15,
            defaults: { labelWidth: 155, anchor: '100%' },
            items: [{
                xtype: 'textfield',
                fieldLabel: '{s name=settings/types_window/name}Type name{/s}',
                name: 'name',
                allowBlank: false
            }, {
                xtype: 'textfield',
                fieldLabel: '{s name=settings/types_window/color}Grid color{/s}',
                emptyText: '#480011',
                supportText: '{s name=settings/types_window/color_support}Please insert the color as a hex-value.{/s}',
                name: 'gridColor',
                allowBlank: false
            }]
        });
    },

    /**
     * Creates the action toolbar which includes the save button.
     *
     * Note that the component is docked to the bottom of the window.
     *
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: ['->', {
                cls: 'secondary',
                text: '{s name=settings/types_window/cancel}Cancel{/s}',
                handler: function(btn) {
                    var win = btn.up('window');
                    win.destroy();
                }
            }, {
                text: '{s name=settings/types_window/save_type}Save ticket type{/s}',
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('saveType', btn, me);
                }
            }]
        });
    }
});
//{/block}
