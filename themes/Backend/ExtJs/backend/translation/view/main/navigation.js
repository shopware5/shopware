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
 * @package    Translation
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/translation/view/main}

/**
 * Shopware UI - Translation Manager Main Navigation
 *
 * todo@all: Documentation
 */
//{block name="backend/translation/view/main/navigation"}
Ext.define('Shopware.apps.Translation.view.main.Navigation',
/** @lends Ext.tree.Panel */
{
    extend: 'Ext.tree.Panel',
    alias: 'widget.translation-main-navigation',
    rootVisible: false,
    singleExpand: false,
    useArrows: true,
    title: '{s name=navigation_title}Available language(s){/s}',
    width: 200,

    /**
     * Configure the root node of the tree panel. This is necessary
     * due to the fact that the ExtJS 4.0.7 build didn't displays
     * the tree if there's no root node configurated.
     *
     * @object
     */
    root: {
        text: '&nbsp;',
        expanded: true
    },

    /**
     * Indicates if the languages are propertly loaded.
     * @boolean
     */
    initialized: false,

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.store = me.treeStore;
        me.store.load();

        me.callParent(arguments);
    },

    /**
     * Find and select first editable language
     * after all nodes have been loaded
     */
    listeners: {
        afteritemexpand: function() {
            var me = this;

            if (me.getRootNode()) {
                var node = me.getFirstLanguage(me.getRootNode());
                if (node) {
                    me.getSelectionModel().select(node);
                    me.fireEvent('itemclick', me, node);
                }
            }
        }
    },

    /**
     * Search recursively through the store to find the first editable language
     * @param node
     * @returns object
     */
    getFirstLanguage: function(node) {
        var me = this;

        if (node.firstChild) {
            return me.getFirstLanguage(node.firstChild);
        }

        return node;
    }
});
//{/block}
