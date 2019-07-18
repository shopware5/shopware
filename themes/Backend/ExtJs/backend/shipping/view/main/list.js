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
 * @package    Shipping
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/main/list}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/main/list"}
Ext.define('Shopware.apps.Shipping.view.main.List', {
    /**
     * Parent Class
     * @String
     */
    extend : 'Ext.grid.Panel',
    /**
     * Alias
     * @string
     */
    alias : 'widget.shipping-list',

    /**
     * Enables autoscrolling
     * @boolean
     */
    autoScroll : true,

    /**
     * Text to display on empty entries
     * @string
     */
    emptyText: '{s name=grid_empty_text}No records{/s}',
    /**
     * Text displayed if all shops are meant
     * @string
     */
    emptySubshopText: '{s name=grid_empty_shop}All{/s}',
    /**
     * Text displayed if all customergroups are meant
     * @string
     */
    emptyCustomerGroupText: '{s name=grid_empty_customer_group}All{/s}',

    /**
     * USe statfull
     * @boolean
     */
    stateful : true,
    /**
     * Id to store the state
     * @string
     */
    stateId : 'shopware-shipping-list',

    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration
     */
    initComponent : function() {
        var me = this;

        me.store = me.dispatchStore;
        me.selModel = Ext.create('Ext.selection.CheckboxModel');

        me.tbar = me.createToolbar();
        me.bbar = me.getPagingbar();

        // Define the columns and renders
        me.columns = [
            {
                header: '{s name=grid_name}Name{/s}',
                dataIndex : 'dispatch.name',
                renderer: me.nameColumn,
                width: 125
            },
            {
                header:'{s name=grid_internal_comment}Internal comment{/s}',
                dataIndex:'dispatch.comment',
                renderer : me.commentColumn,
                flex:1
            },
            {
                header:'{s name=grid_active}Active{/s}',
                xtype: 'booleancolumn',
                dataIndex:'dispatch.active',
                width:50,
                renderer:me.activeColumn
            },
            {
                header:'{s name=grid_type}Type{/s}',
                dataIndex:'dispatch.type',
                renderer:me.typeColumn,
                width:120
            },
            {
                header:'{s name=grid_shop}Shop{/s}',
                dataIndex:'dispatch.multiShopId',
                renderer:me.multishopIdColumn,
                width:120
            },
            {
                header:'{s name=grid_customer_group}Customer group{/s}',
                dataIndex:'dispatch.customerGroupId',
                renderer:me.customerGroupColumn,
                width:120
            },
            {
                header: '',
                xtype : 'actioncolumn',
                width : 80,
                items : [
                     /* {if {acl_is_allowed privilege=delete}} */
                    {
                        iconCls : 'sprite-minus-circle-frame',
                        action  : 'delete',
                        cls     : 'dispatchDelete',
                        tooltip : '{s name=grid_delete_tooltip}Delete this dispatch costs.{/s}'
                    },
                    /* {/if} */
                    /* {if {acl_is_allowed privilege=update}} */
                    {
                        iconCls : 'sprite-pencil',
                        cls     : 'editButton',
                        tooltip : '{s name=grid_edit_tooltip}Edit these shipping costs{/s}'
                    },
                    /* {/if} */
                     /* {if {acl_is_allowed privilege=create}} */
                    {
                        iconCls :'sprite-blue-document-copy',
                        cls     :'cloneButton',
                        tooltip :'{s name=grid_clone_tooltip}Duplicate these shipping costs{/s}',
                        style   : 'width: 16px; height: 16px'
                    }
                    /* {/if} */
                ]
            }
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar for the position grid.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            /**
             * Contains two buttons one for add one for delete.
             * @array
             */
            items : [
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    iconCls : 'sprite-plus-circle',
                    text : '{s name=grid_add}Add{/s}',
                    action : 'addShipping'
                },
                /* {/if} */
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    iconCls : 'sprite-minus-circle',
                    text : '{s name=grid_delete}Delete{/s}',
                    disabled : true,
                    action : 'deleteShipping'
                }
                /* {/if} */
            ]
        });
    },

    /**
     * Creates the pagingbar for the position grid.
     * @return Ext.toolbar.Pagingbar
     */
    getPagingbar : function() {
        var me = this;
        return  {
            xtype: 'pagingtoolbar',
            displayInfo : true,
            store: me.store
        };
    },

    /**
     * Does a translation from the numeric value to a more readable string
     *
     * @param [string] value - Name of the dispatch type
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    typeColumn: function(value, obj, record) {
        switch (record.get('type')) {
            case 3:
                return '{s name=grid_dispatch_type_discount}Discount rule{/s}';
            case 2:
            case 4:
                return '{s name=grid_dispatch_type_surcharge}Surcharge rule{/s}';
            case 1:
                return '{s name=grid_dispatch_type_alternative}Alternate shipping type{/s}';
            case 0:
            default:
                return '{s name=grid_dispatch_type_default}Default shipping type{/s}';
        }
    },
    /**
     * Translate the shop id to there right name
     *
     * @param [string] value - Name of the shop
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    multishopIdColumn : function(value, obj, record) {
        var me = this,
            shop = me.shopStore.findRecord('id', record.get('multiShopId'));

        // if we have an empty or a zero value show default empty text
        if (null === record.get('multiShopId') || 0 === record.get('multiShopId')) {
            return me.emptySubshopText;
        }
        return shop.get('name');
    },

    /**
     * Converts the numeric id to a readable name
     * @param [string] value - ID of the customer group
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    customerGroupColumn : function(value, metaData, record) {
        var me = this,
            customerGroupStore = me.customerGroupStore;

        var customerGroup = customerGroupStore.findRecord('id', record.get('customerGroupId'));
        if (null == record.get('customerGroupId') || null == customerGroup) {
            return me.emptyCustomerGroupText;
        }
        return customerGroup.get('name') + ' ('+customerGroup.get('key')+')';
    },

    /**
     * Formats the name column
     *
     * @param [string] value - Name of the disptach
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    nameColumn : function (value, metaData, record) {
        // Show the translated name in the list
        return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', record.get('translatedName'));
    },

    /**
     * Formats the comment column
     *
     * @param [string] value - Description for the dispatch
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    commentColumn : function (value, metaData, record) {
        // Show the translated description in the overview
        return record.get('translatedDescription');
    },
    /**
     * Formats the action column
     *
     * @param [string] value - flag if this dispatch is active or not
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    activeColumn : function (value, metaData, record) {
        if(record.get('active') == 0) {
            return '{s name=grid_active_false_label}Inactive{/s}';
        }
        return '{s name=grid_active_true_label}Active{/s}';
    }
});
//{/block}
