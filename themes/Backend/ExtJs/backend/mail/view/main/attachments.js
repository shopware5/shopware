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
 * @package    Mail
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/mail/view/attachments}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/attachments"}
Ext.define('Shopware.apps.Mail.view.main.Attachments', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.mail-main-attachments',
    rootVisible: false,
    sortableColumns: false,
    useArrows: true,

    viewConfig: {
        plugins: [{
            ptype: 'treeviewdragdrop',
            appendOnly: true
        }]
    },

    /**
     * Configure the root node of the tree panel. This is necessary
     * due to the fact that the ExtJS 4.0.7 build fires the load
     * event to often if no root node is configured.
     *
     * @object
     */
    root: {
        text: 'Mail',
        expanded: true
    },

    displayField: 'filename',

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.dockedItems = [ me.getToolbar() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column
             *
             * @event onDeleteSingle
             * @param [object] grid - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             */
            'onDeleteSingle'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        var columns = [{
            xtype: 'treecolumn',
            dataIndex: 'filename',
            flex: 1,
            hideable: false,
            text: '{s name=label_filename}File name{/s}'
        }, {
            dataIndex: 'size',
            hideable: false,
            text: '{s name=label_filesize}Size{/s}'
        }];

        /*{if {acl_is_allowed privilege=delete}}*/
        columns.push({
            /**
            * Special column type which provides
            * clickable icons in each row
            */
            xtype: 'actioncolumn',
            width: 26,
            items: [{
                action: 'delete',
                cls: 'deleteBtn',
                tooltip: '{s name=tooltip_delete_attachment}Delete this attachment{/s}',
                iconCls: 'sprite-minus-circle-frame',
                handler: function(grid, rowIndex, colIndex) {
                    me.fireEvent('onDeleteSingle', grid, rowIndex, colIndex);
                },
                getClass: function(value, metadata, record) {
                    if (!record.isLeaf())  {
                        return 'x-hidden';
                    }
                }
            }]
        });
        /*{/if}*/

        return columns;
    },

    /**
     * Creates the toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    getToolbar: function() {
        var buttons = [];

        /*{if {acl_is_allowed privilege=update}}*/
        buttons.push({
            xtype: 'mediafield',
            buttonOnly: true,
            multiSelect: true,
            buttonText: '{s name=button_media}Add attachments{/s}',
            action: 'main-attachments-add',
            buttonIconCls: ''
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=update}}*/
        buttons.push({
            xtype: 'button',
            text: '{s name=button_delete_attachments}Delete all selected attachments{/s}',
            action: 'main-attachments-delete',
            disabled: true
        });
        /*{/if}*/

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: buttons
        };
    }
});
//{/block}
