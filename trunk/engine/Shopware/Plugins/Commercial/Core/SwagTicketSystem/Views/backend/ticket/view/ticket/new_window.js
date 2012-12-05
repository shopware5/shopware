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
//{block name="backend/ticket/view/ticket/new_window"}
Ext.define('Shopware.apps.Ticket.view.ticket.NewWindow', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Enlight.app.Window',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-ticket-new-window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-ticket-new-window',

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
    width:600,

    /**
     * Define window height
     * @integer
     */
    height:210,

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
    stateId:'shopware-ticket-ticket-new-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=new_ticket_window_title}Ticket system - Add new ticket{/s}',

    /**
     * Initializes the component and the
     * main tab panel.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('createTicket', 'createTicketUnregistered');
        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createActionToolbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel to select the form and customer.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        me.fieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name=new_window/form/fieldset}Selection{/s}',
            bodyPadding: 15,
            defaults: { labelWidth: 155, anchor: '100%' },
            items: [{
                xtype: 'combobox',
                fieldLabel: '{s name=new_window/form/form_selection}Select form{/s}',
                name: 'formId',
                store: me.formsStore,
                displayField: 'name',
                valueField: 'id',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
                allowBlank: false
            }, {
                xtype: 'pagingcombobox',
                fieldLabel: '{s name=new_window/form/customer_selection}Select customer{/s}',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
                name: 'customerId',
                allowBlank: true,
                store: me.customerStore,
                valueField: 'id',
                displayField: 'name',
                queryMode: 'remote',
                pageSize: 15
            }]
        });

        return me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 15,
            border: false,
            bodyBorder: 0,
            items: [ me.fieldSet ]
        })
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
            items: [{
                cls: 'secondary small',
                text: '{s name=new_windowc/unregistered}Ticket for unregistered customer{/s}',
                handler: function(btn) {
                    me.fireEvent('createTicketUnregistered', btn, me);
                }

            }, '->', {
                text: '{s name=new_window/cancel}Cancel{/s}',
                cls: 'secondary',
                handler: function(btn) {
                    var win = btn.up('window');
                    win.destroy();
                }
            },  {
                text: '{s name=new_window/save_type}Create new ticket{/s}',
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('createTicket', btn, me);
                }
            }]
        });
    }
});
//{/block}
