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
 * Shopware UI - Blog detail main window.
 *
 * Displays all Detail Blog Information
 */
//{block name="backend/blog/view/blog/window"}
Ext.define('Shopware.apps.Blog.view.blog.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/detail_title}Blog article configuration{/s}',
    alias: 'widget.blog-blog-window',
    border: false,
    autoShow: true,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    width: '80%',
    modal:false,
    layout: {
        type: 'fit'
    },

    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create our form panel and assign it to the namespace for later usage
        me.formPanel = me.createFormPanel();

        me.items = [
            {
                xtype:'tabpanel',
                region:'center',
                items:me.getTabs()
            }
        ];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items: me.createFormButtons()
        }];
        me.callParent(arguments);

        //have to load it here because in some cases the view is not totally created before the record starts loading
        if(me.record) {
            me.formPanel.loadRecord(me.record);
            me.attributeForm.loadAttribute(me.record.get('id'));
        }
    },

    /**
     * Creates the tabs for the tab panel of the window.
     * Contains the detail form which is used to display the blog data for an existing blog entry
     * or to create a new blog.
     *
     * The second tab contains a list of all blog comments
     */
    getTabs:function () {
        var me = this;
        return [
            me.formPanel,
            {
                xtype: 'blog-blog-detail-comments',
                title: '{s name=comment/title}Blog article comments{/s}',
                commentStore: me.commentStore
            }

        ];
    },


    /**
     * creates the form panel
     */
    createFormPanel: function() {
        var me = this;
        me.mainView = Ext.create('Shopware.apps.Blog.view.blog.detail.Main',{
            flex: 5,
            record: me.record
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            table: 's_blog_attributes',
            bodyPadding: 10,
            autoScroll: true,
            allowTranslation: true,
        });

        return Ext.create('Ext.form.Panel', {
            layout: {
                align: 'stretch',
                type: 'hbox'
            },
            border: 0,
            bodyPadding: 10,
            title:'{s name=detail_general/title}General Settings{/s}',
            plugins: [{
                ptype: 'translation',
                translationType: 'blog'
            }],
            items: [
                me.mainView,
                {
                    xtype: 'panel',
                    layout: {
                        type: 'accordion',
                        animate: Ext.isChrome
                    },
                    flex: 3,
                    margin: '0 0 0 10',
                    items: [
                        {
                            xtype: 'blog-blog-detail-sidebar-options',
                            detailRecord: me.record,
                            categoryPathStore: me.categoryPathStore,
                            templateStore: me.templateStore
                        },
                        {
                            xtype: 'blog-blog-detail-sidebar-assigned_articles',
                            gridStore: me.record.getAssignedArticles()
                        },
                        {
                            xtype: 'blog-blog-detail-sidebar-seo',
                            detailRecord: me.record,
                            mainTitleField: me.mainView.mainTitle
                        },
                        me.attributeForm
                    ]
                }
            ]
        });
    },
    /**
     * creates the form buttons cancel and save
     */
    createFormButtons: function(){
        var me = this;
        return ['->',
            {
                text:'{s name=detail_general/button/cancel}Cancel{/s}',
                cls: 'secondary',
                scope:me,
                handler:function () {
                    this.destroy();
                }
            }
        /* {if {acl_is_allowed privilege=create}} */
            ,{
                text:'{s name=detail_general/button/save}Save{/s}',
                action:'save',
                cls:'primary'
            }
        /* {/if} */
        ];
    }
});
//{/block}
