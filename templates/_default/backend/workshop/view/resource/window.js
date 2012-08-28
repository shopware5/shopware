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
 * @package    Workshop
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Media Manager Album Add
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
Ext.define('Shopware.apps.Workshop.view.resource.Window', {
	extend: 'Ext.window.Window',
    title: 'Workshop - Add / Edit resources',
    alias: 'widget.workshop-resource-window',
    border: false,
    width: 400,
    autoShow: true,

    initComponent: function() {
        var me = this;

        me.nameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'Name des Albums',
            labelWidth: 200,
            name: 'name',
            allowBlank: false
        });

        me.privilegeGrid = me.createPrivilegeGrid();

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 12,
            defaults: {
                labelStyle: 'font-weight: 700'
            },
            items: [ me.nameField, me.privilegeGrid ]
        });

        me.items = [ me.formPanel ];
        me.buttons = me.createActionButtons();
        me.callParent(arguments);
        me.formPanel.loadRecord(me.record);
    },

    createActionButtons: function() {
        this.closeBtn = Ext.create('Ext.button.Button', {
            text: 'Cancel',
            handler: function(btn) {
                var win = btn.up('window');
                win.destroy();
            }
        });

        this.addBtn = Ext.create('Ext.button.Button', {
            text: 'Save',
            action: 'workshop-resource-save',
            cls: 'primary'
        });

        return [ this.closeBtn, this.addBtn ];
    },

    createPrivilegeGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            title: 'Privileges',
            store: me.record.getPrivilegesStore,
            tbar: me.getToolbar(),
            selModel: me.getGridSelModel(),
            columns: [
                {
                    header: 'Name',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    /**
                     * Special column type which provides
                     * clickable icons in each row
                     */
                    xtype:'actioncolumn',
                    width:70,
                    items:[
                        {
                            iconCls:'delete',
                            action:'deletePrivilege',
                            cls:'delete',
                            tooltip:'{s name=column/delete}Delete privilege{/s}',
                            handler:function (view, rowIndex, colIndex, item) {
                            }
                        }
                    ]
                }
            ],
            height: 200
        });

    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    var owner = this.view.ownerCt,

                    btn = owner.down('button[action=deletePrivileges]');

                    btn.setDisabled(selections.length == 0);
                }
            }
        });
        return selModel;
    },


    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar',
            {
                dock:'top',
                items:[
                    {
                        iconCls:'add',
                        text: '{s name=toolbar/button_add}Add{/s}',
                        action:'addPrivilege'
                    } ,
                    {
                        iconCls:'delete',
                        text: '{s name=toolbar/button_delete}Delete all selected{/s}',
                        disabled:true,
                        action:'deletePrivileges'
                    }
                ]
            });
    }

});