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
 * @subpackage Detail
 * @version    $Id$
 * @author     shopware AG
 */

/**
 * Shopware UI - Article resources - Downloads
 * The link component contains the configuration elements for the article links
 * and article downloads.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/resources/downloads"}
Ext.define('Shopware.apps.Article.view.resources.Downloads', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-resources-downloads',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-resources-downloads',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=resources/downloads/field_set}Downloads{/s}',
        notice: '{s name=resources/downloads/notice}Optionally, add additional downloads (e.g. pdf files).{/s}',
        name: '{s name=resources/downloads/name}Name{/s}',
        link: '{s name=resources/downloads/link}File{/s}',
        button: '{s name=resources/downloads/button}Add download{/s}',
        grid: {
            title: '{s name=resources/downloads/grid/title}Created downloads{/s}',
            delete: '{s name=resources/downloads/grid/delete}Remove download{/s}'
        }
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
    initComponent: function () {
        var me = this;
        me.items = me.createElements();
        me.title = me.snippets.title;
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Creates the container for the download form panel and the download grid.
     * @return Ext.container.Container
     */
    createElements: function() {
        var me = this;

        me.downloadForm = me.createDownloadForm();
        me.downloadGrid = me.createDownloadGrid();

        me.downloadElements = Ext.create('Ext.container.Container', {
            layout: 'column',
            items: [
                {
                    xtype: 'container',
                    columnWidth: 0.35,
                    margin: '0 20 0 0',
                    items: [ me.downloadForm ]
                }, {
                    xtype: 'container',
                    columnWidth: 0.65,
                    items: [ me.downloadGrid ]
                }
            ]
        });

        return [ me.downloadElements ];
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks on the add download button
             *
             * @event
             * @param [Ext.grid.Panel] The download grid
             * @param [Ext.form.Panel] The download panel for the link
             */
            'addDownload',
            /**
             * Event will be fired when the user clicks on the remove icon of a grid entry
             *
             * @event
             * @param [Ext.grid.Panel] The download grid
             * @param [Ext.data.Model] The download record
             */
            'removeDownload'
        );
    },

    /**
     * Creates the form panel for the download container.
     * @return Ext.form.Panel
     */
    createDownloadForm: function() {
        var me = this;

        // Media selection field
        me.mediaSelection = Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel: me.snippets.link,
            labelWidth: 155,
            name: 'file',
            multiSelect: false,
            anchor: '100%',
            allowBlank: false,
            buttonOnly: false,
            buttonText: false,
            // Setting width manually as firefox shows a very small button otherwise
            buttonConfig: {
                width: 30
            }
        });

        me.downloadFormElements = Ext.create('Ext.form.Panel', {
            cls: 'shopware-form',
            layout: 'anchor',
            border: false,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'container',
                    cls: Ext.baseCSSPrefix + 'global-notice-text',
                    html: me.snippets.notice
                }, {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: me.snippets.name,
                    allowBlank: true
                },
                me.mediaSelection,
                {
                    xtype: 'button',
                    cls: 'small primary',
                    text: me.snippets.button,
                    anchor: 'auto',
                    margin: '0 0 0 160',
                    handler: function() {
                        me.fireEvent('addDownload', me.downloadGrid, me.downloadForm)
                    }
                }
            ]
        });

        return me.downloadFormElements;
    },

    /**
     * Creates the grid for the already assigned article downloads
     * @return Ext.grid.Panel
     */
    createDownloadGrid: function() {
        var me = this;

        me.downloadGridElements = Ext.create('Ext.grid.Panel', {
            title: me.snippets.grid.title,
            store: me.article.getDownload(),
            name: 'download-listing',
            height: 180,
            plugins: [
                {
                    ptype: 'grid-attributes',
                    table: 's_articles_downloads_attributes'
                },
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                })
            ],
            columns: [
                {
                    header: me.snippets.name,
                    dataIndex: 'name',
                    flex: 1,
                    editor: {
                        xtype: 'textfield'
                    }
                }, {
                    header: me.snippets.link,
                    dataIndex: 'file',
                    flex: 1
                }, {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.snippets.grid.delete,
                            handler: function (view, rowIndex, colIndex, item,opts, record) {
                                me.fireEvent('removeDownload', me.downloadGrid, record)
                            }
                        }
                    ]
                }
            ]
        });

        return me.downloadGridElements;
    }
});
//{/block}
