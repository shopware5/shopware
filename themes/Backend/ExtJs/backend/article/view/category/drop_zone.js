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
 * @package    Article
 * @subpackage DropZone
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Category
 * The drop zone component handles the drop events of the category tree.
 * If the user drops one or more categories on the drop zone, an event will be fired which
 * handled in the category controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/category/drop_zone"}
Ext.define('Shopware.apps.Article.view.category.DropZone', {
    /**
     * Define that the category drop zone is an extension of the Ext.panel.Panel
     * @string
     */
    extend:'Ext.panel.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-category-drop-zone',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'category-drop-zone',

    /**
     * Layout for the component
     */
    layout: 'anchor',

    /**
     * Defaults for the panel items
     * @object
     */
    defaults: {
        anchor: '100%'
    },

    dropZoneOverCls:'dropzone-over',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=category/drop_zone/title}Allocation by drag&drop{/s}',
        text: '{s name=category/drop_zone/text}Assign this category via drag&drop{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.dropZone = me.createDropZone();
        me.items = [ me.infoText, me.dropZone ];
        me.registerEvents();
        me.registerDropZone();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user drop a category tree node over the
             * drop zone.
             *
             * @event
             * @param [array] record - The drop category model
             */
            'addCategories'
        );
    },

    /**
     * Creates the drop zone container
     * @return Ext.container.Container
     */
    createDropZone: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            anchor: '100%',
            focusable: false,
            margin: 10,
            cls: 'x-container-dropzone',
            items: [
                {
                    xtype: 'box',
                    cls: 'inner-dropzone',
                    html: '<span class="text">' + me.snippets.text  + '</span>'
                }
            ]
        });
    },

    /**
     * Registers the drop zone events
     * @return void
     */
    registerDropZone: function() {
        var me = this;

        me.on('afterrender', function() {
             me.ddZone = new Ext.dd.DropZone(me.dropZone.getEl(), {
                ddGroup: 'Category',

                // If the mouse is over the drop zone container, return that container. This is
                // provided as the "target" parameter in all "onNodeXXXX" node event handling functions
                getTargetFromEvent: function(e) {
                    return me.dropZone.getEl();
                },

                // On entry into a target node, highlight the drop zone.
                onNodeEnter : function(target, dd, e, data){
                    me.dropZone.getEl().addCls(me.dropZoneOverCls);
                },

                // On exit from a target node, un highlight the drop zone.
                onNodeOut : function(target, dd, e, data){
                    me.dropZone.getEl().removeCls(me.dropZoneOverCls);
                },

                // While over a target node, return the default drop allowed class which
                // places a "tick" icon into the drag proxy.
                onNodeOver : function(target, dd, e, data){
                    return Ext.dd.DropZone.prototype.dropAllowed;
                },

                // On node drop we can interrogate the target to find the underlying
                // application object that is the real target of the dragged data.
                // In this case, it is a Record in the Tree Store.
                onNodeDrop : function(target, dd, e, data){
                    var records = data.records;
                    me.fireEvent('addCategories', records);

                    return true;
                }
             });
        });
    }
});
//{/block}
