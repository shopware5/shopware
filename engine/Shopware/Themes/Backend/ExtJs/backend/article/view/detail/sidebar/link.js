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
 * @package    Article
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Sidebar
 * The link component contains the configuration elements for the article links
 * and article downloads.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/sidebar/link"}
Ext.define('Shopware.apps.Article.view.detail.sidebar.Link', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.panel.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-sidebar-link',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-sidebar-link',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/sidebar/links/title}Attachments and links{/s}',
        link: {
            title:'{s name=detail/sidebar/links/link/field_set}Links{/s}',
            notice:'{s name=detail/sidebar/links/link/notice}Optionally, add additional references (e.g. to the supplier).{/s}',
            name:'{s name=detail/sidebar/links/link/name}Name{/s}',
            link:'{s name=detail/sidebar/links/link/link}Link{/s}',
            button:'{s name=detail/sidebar/links/link/button}Add link{/s}',
            grid: {
                title:'{s name=detail/sidebar/links/link/grid/title}Created links{/s}',
                delete:'{s name=detail/sidebar/links/link/grid/delete}Remove link{/s}',
                external:'{s name=detail/sidebar/links/link/grid/external}External{/s}',
                edit:'{s name=detail/sidebar/links/link/grid/edit}Edit link{/s}'
            }
        },
        download: {
            title:'{s name=detail/sidebar/links/download/field_set}Downloads{/s}',
            notice:'{s name=detail/sidebar/links/download/notice}Optionally, add additional downloads (e.g. pdf files).{/s}',
            name:'{s name=detail/sidebar/links/download/name}Name{/s}',
            link:'{s name=detail/sidebar/links/download/link}File{/s}',
            button:'{s name=detail/sidebar/links/download/button}Add download{/s}',
            grid: {
                title:'{s name=detail/sidebar/links/download/grid/title}Created downloads{/s}',
                delete:'{s name=detail/sidebar/links/download/grid/delete}Remove download{/s}',
                edit:'{s name=detail/sidebar/links/download/grid/edit}Edit download{/s}'
            }
        }
    },

    /**
     * Contains the field set defaults.
     */
    defaults: {
        padding: 10
    },
    bodyPadding: 10,
    autoScroll: true,


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
        me.items = me.createElements();
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Creates the elements for the options panel.
     * @return array
     */
    createElements: function() {
        var me = this;

        me.linkContainer = me.createLinkContainer();
        me.downloadContainer = me.createDownloadContainer();

        return [ me.linkContainer, me.downloadContainer ];
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
    		/**
    		 *
    		 * @event
    		 * @param [Ext.grid.Panel] The link grid
    		 * @param [Ext.form.Panel] The form panel for the link
    		 */
    		'addLink',
            /**
             *
             * @event
             * @param [Ext.grid.Panel] The link grid
             * @param [Ext.data.Model] The link record
             */
            'removeLink',
            /**
             *
             * @event
             * @param [Ext.grid.Panel] The download grid
             * @param [Ext.form.Panel] The download panel for the link
             */
            'addDownload',
            /**
             *
             * @event
             * @param [Ext.grid.Panel] The download grid
             * @param [Ext.data.Model] The download record
             */
            'removeDownload'
    	);
    },

    /**
     * Creates the container for the link form panel and the link grid.
     * @return Ext.form.FieldSet
     */
    createLinkContainer: function() {
        var me = this;

        me.linkForm = me.createLinkForm();
        me.linkGrid = me.createLinkGrid();

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            title: me.snippets.link.title,
            defaults: {
                labelWidth: 100,
                anchor: '100%'
            },
            items: [
                me.linkForm,
                me.linkGrid
            ]
        });
    },

    /**
     * Creates the form panel for the link container.
     * @return Ext.form.Panel
     */
    createLinkForm: function() {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            border: false,
            defaults: {
                labelWidth: 100,
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'container',
                    cls: Ext.baseCSSPrefix + 'global-notice-text',
                    html: me.snippets.link.notice
                }, {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: me.snippets.link.name,
                    allowBlank: true
                }, {
                    xtype: 'textfield',
                    name:'link',
                    fieldLabel: me.snippets.link.link,
                    allowBlank: false,
                    vtype: 'url'
                }, {
                    xtype: 'button',
                    iconCls: 'sprite-chain-plus',
                    cls: 'small secondary',
                    text: me.snippets.link.button,
                    handler: function() {
                        me.fireEvent('addLink', me.linkGrid, me.linkForm);
                    }
                }
            ]
        });
    },

    /**
     * Creates the grid for the already assigned article links
     * @return Ext.grid.Panel
     */
    createLinkGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            title: me.snippets.link.grid.title,
            cls: Ext.baseCSSPrefix + 'free-standing-grid',
            name: 'link-listing',
            height: 100,
            store: me.article.getLink(),
            minHeight: 100,
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                })
            ],
            maxHeight: 100,
            margin: '20 0 0',
            columns: [
                {
                    header: me.snippets.link.name,
                    dataIndex: 'name',
                    flex: 2
                }, {
                    header: me.snippets.link.link,
                    dataIndex: 'link',
                    flex: 2
                }, {
                    xtype: 'booleancolumn',
                    header: me.snippets.link.grid.external,
                    dataIndex: 'target',
                    flex: 1,
                    editor: {
                        xtype: 'checkbox'
                    },
                    // return true / false instead of active/inactive
                    renderer: function(value) {
                        return value;
                    }
                }, {

                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.snippets.link.grid.delete,
                            handler: function (view, rowIndex, colIndex, item, opts, record) {
                                me.fireEvent('removeLink', me.linkGrid, record)
                            }
                        }
                    ]
                }
            ]
        });
    },


    /**
     * Creates the container for the download form panel and the download grid.
     * @return Ext.form.FieldSet
     */
    createDownloadContainer: function() {
        var me = this;

        me.downloadForm = me.createDownloadForm();
        me.downloadGrid = me.createDownloadGrid();

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            title: me.snippets.download.title,
            defaults: {
                labelWidth: 100,
                anchor: '100%'
            },
            items: [
                me.downloadForm,
                me.downloadGrid
            ]
        });
    },

    /**
     * Creates the form panel for the download container.
     * @return Ext.form.Panel
     */
    createDownloadForm: function() {
        var me = this;

        // Media selection field
        me.mediaSelection = Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel: me.snippets.download.link,
            name: 'file',
            multiSelect: false,
            anchor: '100%',
            allowBlank: false,
            buttonOnly: true,
            // Setting width manually as firefox shows a very small button otherwise
            buttonConfig: {
                width: 170
            }
        });

        return Ext.create('Ext.form.Panel', {
            cls: 'shopware-form',
            layout: 'anchor',
            border: false,
            defaults: {
                labelWidth: 100,
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'container',
                    cls: Ext.baseCSSPrefix + 'global-notice-text',
                    html: me.snippets.download.notice
                }, {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: me.snippets.download.name,
                    allowBlank: true
                },
                me.mediaSelection,
                {
                    xtype: 'button',
                    iconCls: 'sprite-chain-plus',
                    cls: 'small secondary',
                    text: me.snippets.download.button,
                    handler: function() {
                        me.fireEvent('addDownload', me.downloadGrid, me.downloadForm)
                    }
                }
            ]
        });
    },

    /**
     * Creates the grid for the already assigned article downloads
     * @return Ext.grid.Panel
     */
    createDownloadGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            title: me.snippets.download.grid.title,
            cls: Ext.baseCSSPrefix + 'free-standing-grid',
            store: me.article.getDownload(),
            name: 'download-listing',
            height: 100,
            minHeight: 100,
            maxHeight: 100,
            margin: '20 0 0',
            columns: [
                {
                    header: me.snippets.download.name,
                    dataIndex: 'name',
                    flex: 1
                }, {
                    header: me.snippets.download.link,
                    dataIndex: 'file',
                    flex: 1
                }, {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.snippets.download.grid.delete,
                            handler: function (view, rowIndex, colIndex, item,opts, record) {
                                me.fireEvent('removeDownload', me.downloadGrid, record)
                            }
                        }
                    ]
                }
            ]
        });
    }
});
//{/block}
