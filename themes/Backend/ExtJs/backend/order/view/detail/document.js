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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/document"}
Ext.define('Shopware.apps.Order.view.detail.Document', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.container.Container',

    /**
     * Defines the component layout.
     */
    layout: 'auto',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-document-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'document-panel',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    padding: 10,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=document/window_title}Documents{/s}',
        gridTitle: '{s name=document/grid_title}Generated documents{/s}'
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

        me.items = [
            me.createDocumentGrid(),
            me.createDocumentForm()
        ];
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     * Creates the document grid which displays all generated documents.
     * @return Shopware.apps.Order.view.list.Document
     */
    createDocumentGrid: function() {
        var me = this;

        return Ext.create('Shopware.apps.Order.view.list.Document', {
            store: me.record['getReceiptStore'],
            minHeight: 150,
            minWidth: 250,
            region: 'center',
            title: me.snippets.gridTitle,
            style: 'margin-bottom: 10px;'
        });
    },

    /**
     * Creates the form panel for the document generation configuration.
     * @return Ext.form.Panel
     */
    createDocumentForm: function() {
        var me = this;

        me.documentForm = Ext.create('Shopware.apps.Order.view.detail.Configuration', {
            region: 'bottom',
            record: me.record,
            documentTypesStore: me.documentTypesStore
        });

        return me.documentForm;
    }

});
//{/block}
