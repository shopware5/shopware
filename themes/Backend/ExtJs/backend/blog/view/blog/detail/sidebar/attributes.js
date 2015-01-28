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
 * @package    Blog
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/blog/view/blog}
/**
 * Shopware UI - Blog detail sidebar options window.
 *
 * Displays all Detail Blog Information
 */
//{block name="backend/blog/view/blog/detail/sidebar/attributes"}
Ext.define('Shopware.apps.Blog.view.blog.detail.sidebar.Attributes', {
    extend:'Ext.panel.Panel',
    alias:'widget.blog-blog-detail-sidebar-attributes',
    border: 0,
    bodyPadding: 10,
    collapsed: false,
    title: '{s name=detail/sidebar/attributes/title}Attributes{/s}',
    layout: {
        type: 'border'
    },

    /**
     * Initialize the Shopware.apps.Blog.view.blog.detail.sidebar.attributes and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.attributesPanel = Ext.create('Ext.panel.Panel', {
            title:'{s name=detail/sidebar/attributes/panel/attributes}Blog attributes{/s}',
            margin: '0 0 10 0',
            layout: {
                type: 'anchor'
            },
            bodyPadding: 10,
            region:'center',
            closable: false,
            collapsible: false,
            autoScroll: true,
            defaults:{
                labelWidth:100,
                anchor: '100%',
                labelStyle:'font-weight: 700;',
                xtype:'textfield'
            },
            items: me.createOptionsForm()
        });

        me.items = [ me.attributesPanel ];

        me.callParent(arguments);
    },

    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createOptionsForm:function () {
        var me = this;
        return [
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute1}Attribute 1{/s}',
                name:'attribute[attribute1]'
            },
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute2}Attribute 2{/s}',
                name:'attribute[attribute2]'
            },
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute3}Attribute 3{/s}',
                name:'attribute[attribute3]'
            },
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute4}Attribute 4{/s}',
                name:'attribute[attribute4]'
            },
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute5}Attribute 5{/s}',
                name:'attribute[attribute5]'
            },
            {
                fieldLabel:'{s name=detail/sidebar/attributes/field/attribute6}Attribute 6{/s}',
                name:'attribute[attribute6]'
            }
        ]
    }
});
//{/block}
