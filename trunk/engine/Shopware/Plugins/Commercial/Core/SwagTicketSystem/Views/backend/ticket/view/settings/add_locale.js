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
//{block name="backend/ticket/view/settings/add_locale"}
Ext.define('Shopware.apps.Ticket.view.settings.AddLocale', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Enlight.app.Window',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-settings-add-locale-window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-settings-add-locale-window',

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
    width:450,

    /**
     * Define window height
     * @integer
     */
    height:200,

    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:false,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:false,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:false,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-ticket-settings-add-locale-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=window_title_add_locale}Ticket system - Add a shop specific submission{/s}',

    /**
     * Initializes the component and the
     * main tab panel.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('saveLocale');

        me.items = [ me.createFormPanel() ];
        me.bbar = me.createActionToolbar();
        me.callParent(arguments);
    },

    /**
     * Creates the form panel.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        var individualStore = Ext.create('Ext.data.Store', {
            fields: [ 'id', 'name' ],
            data: [{
                id: 0, name: '{s name=settings/locale_window/no}No{/s}'
            }, {
                id: 1, name: '{s name=settings/locale_window/yes}Yes{/s}'
            }]
        });

        return me.formPanel = Ext.create('Ext.form.Panel', {
            border: 0,
            bodyBorder: 0,
            bodyPadding: 15,
            defaults: { labelWidth: 155, anchor: '100%', allowBlank: false, xtype: 'combobox' },
            items: [{
                fieldLabel: '{s name=settings/locale_window/label/shop}Select shop{/s}',
                name: 'newShopId',
                displayField: 'name',
                valueField: 'id',
                store: me.unusedLocaleStore,
                emptyText: '{s name=toolbar/combo_empty}{/s}',
                queryMode: 'remote'
            }, {
                fieldLabel: '{s name=settings/locale_window/label/based_on}Based on{/s}',
                name: 'baseShopId',
                displayField: 'name',
                valueField: 'id',
                store: me.localeStore,
                emptyText: '{s name=toolbar/combo_empty}{/s}',
                queryMode: 'locale'
            }, {
                fieldLabel: '{s name=settings/locale_window/label/individual_submissions}Apply individual submission{/s}',
                displayField: 'name',
                valueField: 'id',
                name: 'duplicateIndividualSubmissions',
                store: individualStore,
                emptyText: '{s name=toolbar/combo_empty}{/s}'
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
                text: '{s name=settings/locale_window/save_submission}Save shop specific submission{/s}',
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('saveLocale', btn, me);
                }
            }]
        });
    }
});
//{/block}
