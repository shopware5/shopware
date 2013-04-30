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

    /**
     * Base class of the component
     * @string
     */
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
     * Layout configuration
     * @object
     */
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },

    /**
     * Body padding
     * @integer
     */
    bodyPadding: 10,

    /**
     * Available action buttons
     * @array
     */
    actionButtons: [ 'add', 'remove' ],

    /**
     * Default text which are used for the tooltip on the button.
     * @object
     */
    buttonsText: {
        add: "{s name=tabs/article_mapping/button_add}Add{/s}",
        remove: "{s name=tabs/article_mapping/button_remove}Remove{/s}"
    },


    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.ArticleMapping and defines the necessary
     * default configuration
     *
     * @returns { Void }
     */
    initComponent:function () {
        var me = this;

        me.items = [ me.createFromGrid(), me.createActionButtons(), me.createToGrid() ];

        me.callParent(arguments);
    },

    /**
     * Creates the `from` grid
     * @returns { Ext.grid.Panel }
     */
    createFromGrid: function() {
        var me = this, localFromStore;

        localFromStore = Ext.create('Ext.data.Store', {
            fields: [ 'ordernumber', 'name', 'supplier' ],
            data: [
                { ordernumber: 'SW1000', name: 'Super Ingo', supplier: 'Shopware' }
            ]
        });

        return Ext.create('Ext.grid.Panel', {
            title: '{s name=tabs/article_mapping/available_articles}Available Articles{/s}',
            flex: 1,
            store: localFromStore,
            tbar: me.createSearchToolbar(),
            bbar: me.createPagingToolbar(localFromStore),
            columns: me.getColumns()
        });
    },

    /**
     * Creates the `to` grid
     * @returns { Ext.grid.Panel }
     */
    createToGrid: function() {
        var me = this, localToStore;

        localToStore = Ext.create('Ext.data.Store', {
            fields: [ 'ordernumber', 'name', 'supplier' ],
            data: [
                { ordernumber: 'SW1000', name: 'Super Ingo', supplier: 'Shopware' }
            ]
        });

        return Ext.create('Ext.grid.Panel', {
            title: '{s name=tabs/article_mapping/mapped_articles}Mapped Articles{/s}',
            flex: 1,
            store: localToStore,
            tbar: me.createSearchToolbar(),
            bbar: me.createPagingToolbar(localToStore),
            columns: me.getColumns()
        });
    },

    /**
     * Creates the action buttons which are located between the `fromGrid` (on the left side)
     * and the `toGrid` (on the right side).
     *
     * The buttons are placed in an `Ext.container.Container` to apply the necessary layout
     * on it.
     *
     * @returns { Ext.container.Container }
     */
    createActionButtons: function() {
        var me = this,
            buttons = [];

        Ext.Array.forEach(me.actionButtons, function(name) {

            var button = Ext.create('Ext.Button', {
                tooltip: me.buttonsText[name],
                cls: Ext.baseCSSPrefix + 'form-itemselector-btn',
                iconCls: Ext.baseCSSPrefix + 'form-itemselector-' + name,
                navBtn: true,
                margin: '4 0 0 0'
            });
            button.addListener('click',  me['on' + Ext.String.capitalize(name) + 'BtnClick'], me);
            buttons.push(button);
        });


        return Ext.create('Ext.container.Container', {
            margins: '0 4',
            items: buttons,
            width: 22,
            layout: {
                type: 'vbox',
                pack: 'center'
            }
        });
    },

    /**
     * Creates a paging toolbar based of the incoming store
     *
     * @param { Ext.data.Store } store
     * @returns { Ext.toolbar.Paging }
     */
    createPagingToolbar: function(store) {

        return Ext.create('Ext.toolbar.Paging', {
            store: store
        });
    },

    /**
     * Creates a toolbar which could be docked to the top of
     * a grid panel and contains a searchfield to filter
     * the associated grid panel.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createSearchToolbar: function() {
        var me = this, searchField;

        searchField = Ext.create('Ext.form.field.Text', {
            name: 'searchfield',
            cls: 'searchfield',
            width: 270,
            emptyText: 'Search...',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchOrders', value);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            padding: '2 0',
            items: [ '->', searchField, ' ' ]
        });
    },

    /**
     * Creates the necessary columns for both grids. Please
     * note that the `name` column has a specific renderer.
     *
     * @returns { Array }
     */
    getColumns: function() {
        var me = this;

        return [{
            header: '{s name=tabs/article_mapping/columns/article_number}Article Number{/s}',
            flex: 1,
            dataIndex: 'ordernumber'
        }, {
            header: '{s name=tabs/article_mapping/columns/article_name}Article Name{/s}',
            flex: 2,
            dataIndex: 'name',
            renderer: me.nameColumnRenderer
        }, {
            header: '{s name=tabs/article_mapping/columns/supplier_name}Supplier Name{/s}',
            flex: 1,
            dataIndex: 'supplier'
        }];
    },

    /**
     * Renders the incoming column value into `strong` tags.
     *
     * @param { String } value
     * @returns { String } formatted string
     */
    nameColumnRenderer: function(value) {
        return Ext.String.format('<strong>[0]</strong>', value);
    }
});
//{/block}
