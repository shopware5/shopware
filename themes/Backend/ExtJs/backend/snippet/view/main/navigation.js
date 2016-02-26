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
 */

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/navigation"}
Ext.define('Shopware.apps.Snippet.view.main.Navigation', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.snippet-main-navigation',
    title: 'Namespaces',

    collapsible: true,
    collapsed: false,

    displayField: 'namespace',
    useArrows: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        buttonDeleteNamespace: '{s name=button/delete_namespace}Delte namespace{/s}'
    },

    /**
     * Configure the root node of the tree panel. This is necessary
     * due to the fact that the ExtJS 4.0.7 build fires the load
     * event to often if no root node is configured.
     *
     * @object
     */
    root: {
        namespace: 'Namespaces',
        expanded: true
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.listeners = {
            scope: me,
            itemcontextmenu: me.onContextMenu
        };

        me.callParent(arguments);
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        var buttons = [];

        buttons.push({
            xtype: 'button',
            text: 'delete Me',
            iconCls: 'sprite-pencil',
            handler: function() {
                var selection = me.selModel.getSelection();

                if (selection.length === 0) {
                    me.selModel.selectAll();
                    selection = me.selModel.getSelection();
                }

                if (selection.length === 0) {
                    return;
                }

                me.fireEvent('editSelectedSnippets', selection);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Event listener method which will be fired when the user
     * right-clicked a tree item
     *
     * Opens a context menu.
     *
     * @event itemcontextmenu
     * @param [object] view - View which fires the event Ext.view.View
     * @param [object] record - clicked item Ext.data.Model
     * @param [object] item - HTML DOM Element of the clicked item
     * @param [integer] index - Index of the clicked item in the associated store
     * @param [object] event - fired event Ext.EventObj
     * @return void
     */
    onContextMenu: function(view, record, item, index, event) {
        var me = this;

        if (!record.isLeaf()) {
            return;
        }

        event.preventDefault(true);

        var menu = Ext.create('Ext.menu.Menu', {
            items: [{
                text: me.snippets.buttonDeleteNamespace,
                iconCls: 'sprite-pencil',
                handler: function() {
                    if (record.get('id') !== 'root') {
                        me.fireEvent('deleteNamespace', record);
                    }
                }
            }]
        });

        menu.showAt(event.getPageX(), event.getPageY());
    }
});
//{/block}
