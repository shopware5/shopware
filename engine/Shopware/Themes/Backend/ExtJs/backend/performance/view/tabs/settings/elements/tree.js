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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/performance/main} */

//{block name="backend/performance/view/tabs/settings/elements/tree"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.Tree', {
    /**
    * Parent Element Ext.tree.Panel
    * @string
    */
    extend: 'Ext.tree.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias : 'widget.performance-tabs-settings-tree',

    /**
     * True to make the panel collapsible and have an expand/collapse toggle
     * Tool added into the header tool button area.
     * False to keep the panel sized either statically, or by an owning layout manager, with no toggle Tool.
     *
     * @boolean
     */
    collapsible: false,

    region   : 'west',

    /**
     * False to hide the root node.
     * @boolean
     */
    rootVisible: false,

    /**
     * True to use Vista-style arrows in the tree.
     * @boolean
     */
    useArrows: true,

    /**
     * The width of this component in pixels.
     * @integer
     */
    width: 220,
    rootNodeName : 'Shopware',

     /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent : function() {
        var me = this;

        me.selModel = Ext.create('Ext.selection.RowModel', {
        });


         me.columns = [{
             xtype: 'treecolumn',
             text: 'test',
             flex: 2,
             sortable: false,
             dataIndex: 'name'
         }];


        me.callParent(arguments);

    }

});
//{/block}
