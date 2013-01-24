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
 * @package    Category
 * @subpackage Settings
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware UI - Category Article Mapping
 *
 * Shows the drag and drop selector to map articles to a category
 */
//{block name="backend/category/view/tabs/article_mapping"}
Ext.define('Shopware.apps.Category.view.category.tabs.ArticleMapping', {
   /**
    * Parent Element Ext.container.Container
    * @string
    */
    extend:'Ext.form.Panel',
    /**
     * Register the alias for this class.
     * @string 
     */
    alias:'widget.category-category-tabs-article_mapping',

    cls: 'shopware-form',

    /**
     * Specifies the border for this component. The border can be a single numeric 
     * value to apply to all sides or it can be a CSS style specification for each 
     * style, for example: '10 5 3 10'.
     * 
     * Default: 0
     * @integer
     */
    border: 0,
    /**
     * Display the the contents of this tab immediately
     * @boolean
     */
    autoShow : true,
    /**
     * used layout column
     * 
     * @string
     */
    layout: 'fit',
    /**
     * Body padding
     * @integer
     */
    bodyPadding: 10,
    /**
     * article mapping record
     */
    record: null,

    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.ArticleMapping and defines the necessary
     * default configuration
     */
    initComponent:function () 
    {
        var me = this;
        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * creates all fields for the tab
     */
    getItems:function () {
        var me = this;

        me.ddSelector = Ext.create('Shopware.DragAndDropSelector',{
            fromTitle: '{s name=tabs/article_mapping/available_articles}Available Articles{/s}',
            toTitle: '{s name=tabs/article_mapping/mapped_articles}Mapped Articles{/s}',
            fromStore: me.articleStore,
            fromColumns: me.getColumns(),
            toColumns: me.getColumns(),
            hideHeaders: false,
            buttons:[ 'add','remove' ],
            selectedItems: me.record.getArticles(),
            fromFieldDockedItems: [ me.getFromToolbar(), me.getFromPagingToolbar() ],
            toFieldDockedItems: [ me.getToToolbar() ],
            buttonsText: {
                add: "{s name=tabs/article_mapping/button_add}Add{/s}",
                remove: "{s name=tabs/article_mapping/button_remove}Remove{/s}"
            }
        });
        return [me.ddSelector];
    },

    getFromPagingToolbar: function() {
        var me = this;
        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.articleStore,
            dock: 'bottom'
        };
    },

    /**
     * Columns of the left and right grid
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: '{s name=tabs/article_mapping/columns/article_number}Article Number{/s}',
                flex: 1,
                renderer: me.articleNumberRenderer
            },
            {
                header: '{s name=tabs/article_mapping/columns/article_name}Article Name{/s}',
                flex: 1,
                dataIndex: 'name'
            },
            {
                header: '{s name=tabs/article_mapping/columns/supplier_name}Supplier Name{/s}',
                flex: 1,
                renderer: me.supplierRenderer
            }
        ];
    },


    /**
     * Renderer function of the articleNumber column of the grid
     *
     * @param value
     * @param record
     */
    articleNumberRenderer: function(value, metaData, record) {
        var detailData = record.getDetail().first();
        if (detailData) {
            return detailData.get('number');
        } else {
            return 'undefined';
        }
    },


    /**
     * Renderer function of the supplier column of the grid
     *
     * @param value
     * @param record
     */
    supplierRenderer: function(value, metaData, record) {
        var supplier = record.getSupplier().first();
        if (supplier) {
            return supplier.get('name');
        } else {
            return 'undefined';
        }
    },

    /**
     * Creates the Toolbar for the ddselector to add an searchfield to the left grid
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getFromToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui:'shopware-ui',
            items:[
                '->',
                {
                    xtype:'textfield',
                    name:'searchfield',
                    action:'searchArticle',
                    width:170,
                    cls:'searchfield',
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    emptyText:'{s name=tabs/article_mapping/search}Search...{/s}'
                },
                { xtype:'tbspacer', width:6 }
            ]
        });
    },

    /**
     * Creates the Toolbar for the ddselector to add an searchfield to the right grid
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui:'shopware-ui',
            items:[
                '->',
                {
                    xtype:'textfield',
                    name:'searchfield',
                    action:'searchSelectedArticle',
                    width:170,
                    cls:'searchfield',
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    emptyText:'{s name=tabs/article_mapping/search}Search...{/s}'
                },
                { xtype:'tbspacer', width:6 }
            ]
        });
    }
});
//{/block}
