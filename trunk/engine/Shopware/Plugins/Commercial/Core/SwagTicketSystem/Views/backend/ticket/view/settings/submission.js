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
//{block name="backend/ticket/view/settings/submission"}
Ext.define('Shopware.apps.Ticket.view.settings.Submission', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.panel.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-settings-submission',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-settings-submission',

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
    title: '{s name=settings/submission_title}Submissions{/s}',

    /**
     * Initialize the component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('addSubmission', 'deleteSubmission', 'saveSubmission');

        me.items = [ me.createNavigation(), me.createSubmissionForm() ];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel which allows to create / edit
     * submissions.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createSubmissionForm: function() {
        var me = this;

        me.contentTabPanel = Ext.create('Ext.tab.Panel', {
            plain: true,
            height: 300,
            layout: 'fit',
            items: [{
                title: '{s name=settings/submission/form/tab/plain_text}Plain text{/s}',
                layout: 'anchor',
                items: [{
                    xtype: 'textarea',
                    name: 'content',
                    height: 255,
                    anchor: '100%'
                }]
            }, {
                title: '{s name=settings/submission/form/tab/html_text}HTML text{/s}',
                layout: 'anchor',
                items: [{
                    xtype: 'tinymce',
                    name: 'contentHTML',
                    height: 255,
                    anchor: '100%'
                }]
            }]
        });

        return me.submissionForm = Ext.create('Ext.form.Panel', {
            disabled: true,
            title: '{s name=settings/submission/form/title}Create / edit submission{/s}',
            region: 'center',
            layout: 'anchor',
            border: 0,
            bodyBorder: 0,
            bodyPadding: 15,
            defaults: { labelWidth: 155 },
            items: [{
                xtype: 'container',
                layout: 'column',
                defaults: { columnWidth: .5, labelWidth: 155 },
                margin: '0 0 6 0',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: '{s name=settings/submission/form/from_address}From address{/s}',
                    name: 'fromMail',
                    margin: '0 8 0 0',
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: '{s name=settings/submission/form/from_name}From name{/s}',
                    name: 'fromName',
                    labelWidth: 100,
                    margin: '0 0 0 8',
                    allowBlank: false
                }]
            }, {
                xtype: 'textfield',
                fieldLabel: '{s name=settings/submission/form/subject}Subject{/s}',
                name: 'subject',
                anchor: '100%',
                allowBlank: false
            }, {
                xtype: 'textfield',
                fieldLabel: '{s name=settings/submission/form/description}Internal description{/s}',
                name: 'description',
                anchor: '100%',
                allowBlank: false
            }, {
                xtype: 'checkbox',
                checkedValue: true,
                uncheckedValue: false,
                fieldLabel: '{s name=settings/submission/form/is_html}HTML eMail{/s}',
                boxLabel: '{s name=settings/submission/form/is_html_box}Send the eMail as an HTML eMail{/s}',
                name: 'isHTML',
                anchor: '100%'
            },
            {
                xtype: 'combobox',
                fieldLabel: '{s name=edit_window/label/locale}Locale{/s}',
                allowBlank: false,
                displayField: 'name',
                forceSelection: true,
                valueField: 'id',
                name: 'shopId',
                store: me.localeStore,
                anchor: '100%',
                queryMode: 'remote',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}'
            },
                me.contentTabPanel,
            {
                xtype: 'container',
                margin: '10 0 0',
                style: 'position: relative',
                items: {
                    xtype: 'button',
                    cls: 'primary',
                    style: 'position: absolute; right: -5px; top: 0',
                    text: '{s name=settings/submission/form/save}Save submission{/s}',
                    handler: function(btn) {
                        me.fireEvent('saveSubmission', btn, me);
                    }
                }

            }]
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
            title: '{s name=settings/submission/navigation_title}Available submission(s){/s}',
            bbar: me.createNavigationToolbar(),
            columns: me.createNavigationColumns(),
            store: me.submissionStore,
            width: 250,
            border: false,
            bodyBorder: 0,
            features: [ Ext.create('Ext.grid.feature.Grouping', {
                groupHeaderTpl: '{literal}{name}{/literal}'
            }) ]
        });
    },

    /**
     * Creates the action toolbar for the navigation grid
     *
     * @public
     * @return [object] Ext.toolbar.Toolbar
     */
    createNavigationToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name=settings/submission/toolbar/delete_submission}Delete submission{/s}',
            disabled: true,
            cls: 'small secondary',
            handler: function(btn) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=settings/submission/delete_confirm}Are you sure to delete the selected submission?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteSubmission', btn, me);
                });

            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [{
                xtype: 'button',
                cls: 'small secondary',
                text: '{s name=settings/submission/toolbar/create_submission}Create submission{/s}',
                handler: function(btn) {
                    me.fireEvent('addSubmission', btn, me);
                }
            }, '->', me.deleteButton ]
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
            dataIndex: 'description',
            header: '{s name=settings/submission/columns/description}Description{/s}',
            flex: 1,
            renderer: function(value, meta, record) {
                var restricted = record.get('systemDependent'),
                    prefix = '[S]&nbsp;';

                if(restricted) {
                    value = '<em style="font-style=italic;">' + prefix + value + '</em>';
                }

                return value;
            }
        }];
    }
});
//{/block}
