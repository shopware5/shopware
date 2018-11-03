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
 * @package    Premium
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/premium/main}

/**
 * Shopware UI - Premium view list
 *
 * This grid contains all premium-articles and its information.
 * It also has an actioncolumn to delete or update an article.
 */
//{block name="backend/premium/view/premium/list"}
Ext.define('Shopware.apps.Premium.view.premium.List', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',
    border: 0,

    ui: 'shopware-ui',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('premium-main-list')
    * @string
    */
    alias: 'widget.premium-main-list',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    /**
    * Sets up the ui component
    * @return void
    */
    initComponent: function() {
        var me = this;
        me.registerEvents();

        me.dockedItems = [];
        me.store = me.premiumStore;
        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.dockedItems.push(me.toolbar);

        // Add paging toolbar to the bottom of the grid panel
        me.dockedItems.push({
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        });

        me.callParent(arguments);
    },

    /**
     * Creates the selectionModel of the grid with a listener to enable the delete-button
     */
    getGridSelModel: function(){
        var selModel = Ext.create('Ext.selection.CheckboxModel',{
            listeners: {
                selectionchange: function(sm, selections){
                    var owner = this.view.ownerCt,
                            btn = owner.down('button[action=deleteMultipleArticles]');

                    //If no article is marked
                    if(btn){
                        btn.setDisabled(selections.length == 0);
                    }
                }
            }
        });

        return selModel;
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
                 * @event deleteColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'deleteColumn',

                /**
                 * Event will be fired when the user clicks the edit icon in the
                 * action column
                 *
                 * @event editColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'editColumn'
        );

        return true;
    },

    /**
     *  Creates the columns
     */
    getColumns: function(){
        var me = this;
        var buttons = new Array();

        /*{if {acl_is_allowed privilege=delete}}*/
        buttons.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle',
            action: 'delete',
            cls: 'delete',
            tooltip: '{s name=column/actioncolumn/delete}Delete article{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('deleteColumn', view, rowIndex,  item, colIndex);
            }
        }));
        /*{/if}*/

        /*{if {acl_is_allowed privilege=update}}*/
        buttons.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-pencil',
            cls: 'editBtn',
            tooltip: '{s name=column/actioncolumn/edit}Edit article{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('editColumn', view, item, rowIndex, colIndex);
            }
        }));
        /*{/if}*/

        var columns = [
            {
                header: '{s name=column/name}Name{/s}',
                dataIndex: 'name',
                flex: 1,
                //Renderer to format the column
                renderer: this.nameColumn
            },{
                header: '{s name=column/export_ordernumber}Export order number{/s}',
                dataIndex: 'orderNumberExport',
                flex: 1
            }, {
                header: '{s name=column/subshop}Subshop{/s}',
                dataIndex: 'subShopName',
                flex: 1,
                renderer: this.renderSubShop
            },{
                header: '{s name=column/startprice}Minimum order value{/s}',
                dataIndex: 'startPrice',
                flex: 1
            }, {
                xtype: 'actioncolumn',
                width: 60,
                items: buttons
            }
        ];

        return columns;
    },

    renderSubShop: function(value,a,record){
        if(value){
            return value;
        }else if(record.data.shopId == 0){
            return "{s name=premium/subShop/comboBox_general}Universally valid{/s}";
        }
    },

    /**
     * Creates the toolbar with a save-button, a delete-button and a textfield to search for articles
     */
    getToolbar: function(){

        var searchField = Ext.create('Ext.form.field.Text',{
            name : 'searchfield',
            cls : 'searchfield',
            action : 'searchPremiumArticle',
            width : 170,
            enableKeyEvents : true,
            emptyText : '{s name=toolbar/search}Search...{/s}',
            listeners: {
                buffer: 500,
                keyup: function() {
                    if(this.getValue().length >= 3 || this.getValue().length<1) {
                        /**
                         * @param this Contains the searchfield
                         */
                        this.fireEvent('fieldchange', this);
                    }
                }
            }
        });
        searchField.addEvents('fieldchange');
        var items = [];
        /*{if {acl_is_allowed privilege=create}}*/
            items.push(Ext.create('Ext.button.Button',{
                iconCls: 'sprite-plus-circle',
                text: '{s name=toolbar/add}Add{/s}',
                action: 'add'
            }));
        /*{/if}*/
        /*{if {acl_is_allowed privilege=delete}}*/
        items.push(Ext.create('Ext.button.Button',{
            iconCls: 'sprite-minus-circle',
            text: '{s name=toolbar/delete}Delete selected articles{/s}',
            disabled: true,
            action: 'deleteMultipleArticles'
        }));
        /*{/if}*/

        items.push('->');
        items.push(searchField);
        items.push({
            xtype: 'tbspacer',
            width: 6
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: items
        });
        return toolbar;
    },

    /**
    * Formats the name column
    *
    * @param value
    * @return [string]
    */
    nameColumn: function(value,metaData,record) {
            return Ext.String.format('<b>{literal}{0}</b> <span>({1}){/literal}</span>', value, record.data.orderNumber);
    }

});
//{/block}
