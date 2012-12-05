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
//{block name="backend/ticket/view/settings/forms"}
Ext.define('Shopware.apps.Ticket.view.settings.Forms', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.panel.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-settings-forms',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-settings-forms',

    /**
     * Layout of the component.
     * @string
     */
    layout: 'border',
    border: 0,
    bodyBorder: 0,

    /**
     * Title of the component.
     * @string
     */
    title: '{s name=settings/forms_title}Form Mapping{/s}',

    /**
     * Initialize the component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('saveMapping');
        me.items = [ me.createNavigation() , me.createFormPanel() ];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel to edit the mapping.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        me.ticketMessage = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/forms/form/message}Ticket message{/s}',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            name: 'message',
            valueField: 'id',
            displayField: 'label',
            queryMode: 'local',
            allowBlank: false
        });

        me.ticketSubject = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/forms/form/subject}Ticket subject{/s}',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            name: 'subject',
            valueField: 'id',
            displayField: 'label',
            queryMode: 'local',
            allowBlank: false
        });

        me.ticketAuthor = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/forms/form/author}Ticket author{/s}',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            name: 'author',
            valueField: 'id',
            displayField: 'label',
            queryMode: 'local',
            allowBlank: false
        });

        me.ticketMail = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/forms/form/email}Ticket eMail{/s}',
            emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
            name: 'email',
            valueField: 'id',
            displayField: 'label',
            queryMode: 'local',
            allowBlank: false
        });

        me.mappingFields = [ me.ticketMessage, me.ticketSubject, me.ticketAuthor, me.ticketMail ];

        me.baseFieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name=settings/forms/form/basic_fieldset}Basic settings{/s}',
            layout: 'anchor',
            bodyPadding: 15,
            defaults: { anchor: '100%' },
            items: [{
                xtype: 'combobox',
                fieldLabel: '{s name=settings/forms/form/ticket_type}Ticket type{/s}',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
                allowBlank: false,
                valueField: 'id',
                displayField: 'name',
                name: 'ticketTypeid',
                store: me.typesStore
            }]
        });

        me.mappingFieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name=settings/forms/form/mapping_fieldset}Mapping configuration{/s}',
            layout: 'anchor',
            bodyPadding: 15,
            defaults: { labelWidth: 155, anchor: '100%' },
            items: me.mappingFields
        });

        return me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            region: 'center',
            bodyBorder: 0,
            bodyPadding: 10,
            disabled: true,
            defaults: { labelWidth: 155, anchor: '100%' },
            items: [ me.baseFieldSet, me.mappingFieldSet, {
                xtype: 'container',
                margin: '10 0 0',
                style: 'position: relative',
                items: {
                    xtype: 'button',
                    cls: 'primary',
                    style: 'position: absolute; right: -5px; top: 0',
                    text: '{s name=settings/forms/form/save}Save mapping{/s}',
                    handler: function(btn) {
                        me.fireEvent('saveMapping', btn, me);
                    }
                }

            } ]
        });
    },

    /**
     * Creates the navigation on the left hand of the module.
     *
     * @public
     * @return [object] - Ext.grid.Grid
     */
    createNavigation: function() {
        var me = this;

        return me.navigationGrid = Ext.create('Ext.grid.Panel', {
            region: 'west',
            hideHeaders: true,
            autoScroll: true,
            title: '{s name=settings/forms/navigation_title}Available forms(s){/s}',
            columns: me.createNavigationColumns(),
            store: me.formsStore,
            width: 250,
            border: false,
            bodyBorder: 0
        });
    },

    /**
     * Creates the column model for the navigation
     * grid.
     *
     * @public
     * @return [array] - Array of the columns
     */
    createNavigationColumns: function() {
        return [{
            dataIndex: 'name',
            header: '{s name=settings/forms/columns/description}Name{/s}',
            flex: 1,
            renderer: function(value, meta, record) {
                return value + ' [' + record.get('isocode') + ']';
            }
        }];
    }
});
//{/block}
