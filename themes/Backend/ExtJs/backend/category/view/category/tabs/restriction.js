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
 * @package    Category
 * @subpackage Settings
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware UI - Category Restriction
 *
 * Used to restrict a Category temporary used to restrict a category by customerGroups
 */
//{block name="backend/category/view/tabs/restriction"}
Ext.define('Shopware.apps.Category.view.category.tabs.restriction', {
   /**
    * Parent Element Ext.container.Container
    * @string
    */
    extend:'Ext.form.Panel',
    /**
     * Register the alias for this class.
     * @string
     */
    alias:'widget.category-category-tabs-restriction',

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
     * enable auto scroll
     * @boolean
     */
    autoScroll: true,
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
     * selected customergroups record
     */
    record: null,

    /**
     * Translations
     * @object
     */
    snippets: {
        availableCustomerGroups:'{s name=view/settings_block_category_available_customer_groups}Available customer groups{/s}',
        chosenCustomerGroups:'{s name=view/settings_block_category_chosen_customer_groups}Block category for{/s}'
    },

    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.restriction and defines the necessary
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
            fromTitle: me.snippets.availableCustomerGroups,
            toTitle: me.snippets.chosenCustomerGroups,
            fromStore: me.customerGroupsStore,
            buttons:[ 'add', 'remove' ],
            selectedItems: me.record.getCustomerGroups(),
            showPagingToolbar: true,
            buttonsText:{
                add:"{s name=tabs/restriction/button_add}Add{/s}",
                remove:"{s name=tabs/restriction/button_remove}Remove{/s}"
            }
        });
        return [me.ddSelector];
    }
});
//{/block}
