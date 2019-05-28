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
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Blog detail page - Sidebar
 * The assigned_articles component contains the configuration elements for the assgined blog articles relations.
 */
//{namespace name=backend/blog/view/blog}
//{block name="backend/blog/view/detail/sidebar/seo"}
Ext.define('Shopware.apps.Blog.view.blog.detail.sidebar.Seo', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.blog-blog-detail-sidebar-seo',

    bodyPadding: 10,
    autoScroll: true,
    border:false,
    /**
     * Helper property which contains the name of the add event which fired when the user
     * clicks the button of the form panel
     */
    addEvent: 'addAssignedArticle',

    /**
     * Helper property which contains the name of the remove event which fired when the user
     * clicks the action column of the grid panel
     */
    removeEvent: 'removeAssignedArticle',

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
        me.registerEvents();
        me.title = '{s name=detail/sidebar/seo/title}Search Engine Optimization{/s}';
        me.items = me.createElements();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user want to add a similar article
             *
             * @event
             */
            'metaDescriptionChanged'
        );
    },

    /**
     * Creates the elements for the similar article panel.
     * @return array
     */
    createElements: function() {
        var me = this;

        me.noticeContainer = me.createNoticeContainer();
        me.formPanel = me.createFormPanel();
        me.previewFieldSet = me.createPreviewFieldSet();

        return [
            me.noticeContainer, me.formPanel, me.previewFieldSet
        ];
    },

    /**
     * Creates the notice container for the similar articles panel.
     * @return Ext.container.Container
     */
    createNoticeContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            style: 'font-style: italic; color: #999; font-size: x-small; margin: 0 0 8px 0;',
            html: '{s name=detail/sidebar/seo/notice}At this point you have the option to perform an on-page optimization of your blog entry, which helps you increasing its relevance within search engines. <br /> <br /> All entries made here will be output in the HTML source code.{/s}'
        });
    },

    /**
     * Creates the form field set for the similar article panel. The form panel is used to
     * edit or add new similar articles to the article on the detail page.
     * @return Ext.form.FieldSet
     */
    createFormPanel: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            padding: 10,
            title:'{s name=detail/sidebar/seo/field_set/on_page/title}On-Page Optimization{/s}',
            defaults: {
                xtype:'textfield',
                anchor: '100%'
            },
            items: me.createFormItems()
        });
    },

    /**
     * Creates the form items.
     * @return
     */
    createFormItems: function() {
        var me = this;
        me.metaDescription = Ext.create('Ext.form.field.TextArea', {
            fieldLabel: '{s name=detail/sidebar/seo/field/description}Description{/s}',
            translationLabel: 'SEO-{s name=detail/sidebar/seo/field/description}Description{/s}',
            enableKeyEvents: true,
            supportText: ' ',
            name: 'metaDescription',
            listeners: {
                keyup: function() {
                    me.fireEvent('metaDescriptionChanged', this);
                }
            },
            anchor: '100%',
            translatable: true
        });

        me.metaTitle = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=detail/sidebar/seo/field/title}Title{/s}',
            translationLabel: 'SEO-{s name=detail/sidebar/seo/field/title}Title{/s}',
            name: 'metaTitle',
            translatable: true,
            anchor: '100%'
        });

        return [
            me.metaTitle,
            {
                xtype: 'textareafield',
                fieldLabel: '{s name=detail/sidebar/seo/field/keywords}Keywords{/s}',
                translationLabel: 'SEO-{s name=detail/sidebar/seo/field/keywords}Keywords{/s}',
                name: 'metaKeyWords',
                translatable: true
            },
            me.metaDescription
        ];
    },

    /**
     * Creates the form field for the preview
     * @return Ext.form.FieldSet
     */
    createPreviewFieldSet: function () {
        var me = this;
        return {
            xtype:'googlepreview',
            fieldSetTitle: '{s name=detail/sidebar/seo/field_set/preview/title}Preview{/s}',
            viewData: me.detailRecord,
            titleField: me.metaTitle,
            fallBackTitleField: me.mainTitleField,
            descriptionField: me.metaDescription,
            supportText: '{s name=detail/sidebar/seo/field_set/preview/supportText}This preview displayed can differ from the version shown in the search engine.{/s}',
            refreshButtonText: '{s name=detail/sidebar/seo/field_set/preview/generate}Generate Preview{/s}'
        };
    }
});
//{/block}
