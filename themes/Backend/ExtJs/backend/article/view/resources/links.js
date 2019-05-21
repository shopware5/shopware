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
 * Shopware UI - Article resources - Links
 * The link component contains the configuration elements for the article links
 * and article downloads.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/resources/links"}
Ext.define('Shopware.apps.Article.view.resources.Links', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-resources-links',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-resources-links',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=resources/links/field_set}Links{/s}',
        notice: '{s name=resources/links/notice}Optionally, add additional references (e.g. to the supplier).{/s}',
        name: '{s name=resources/links/name}Name{/s}',
        link: '{s name=resources/links/link}Link{/s}',
        button: '{s name=resources/links/button}Add link{/s}',
        grid: {
            title: '{s name=resources/links/grid/title}Created links{/s}',
            delete: '{s name=resources/links/grid/delete}Remove link{/s}',
            external: '{s name=resources/links/grid/external}External{/s}'
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
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Creates the elements for the options panel.
     * @return Ext.container.Container
     */
    createElements: function() {
        var me = this;

        me.linkForm = me.createLinkForm();
        me.linkGrid = me.createLinkGrid();

        me.linkElements = Ext.create('Ext.container.Container', {
            layout: 'column',
            items: [
                {
                    xtype: 'container',
                    columnWidth: 0.35,
                    margin: '0 20 0 0',
                    items: [ me.linkForm ]
                }, {
                    xtype: 'container',
                    columnWidth: 0.65,
                    items: [ me.linkGrid ]
                }
            ]
        });

        return [ me.linkElements ];
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks on the add link button
             *
             * @event
             * @param [Ext.grid.Panel] The link grid
             * @param [Ext.form.Panel] The form panel for the link
             */
            'addLink',
            /**
             * Event will be fired when the user clicks on the remove icon of a grid entry
             *
             * @event
             * @param [Ext.grid.Panel] The link grid
             * @param [Ext.data.Model] The link record
             */
            'removeLink'
        );
    },

    /**
     * Creates the form panel for the link container.
     * @return Ext.form.Panel
     */
    createLinkForm: function() {
        var me = this;

        me.linkFormElements = Ext.create('Ext.form.Panel', {
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
                }, {
                    xtype: 'textfield',
                    name:'link',
                    fieldLabel: me.snippets.link,
                    allowBlank: false,
                    vtype: 'url'
                }, {
                    xtype: 'button',
                    cls: 'small primary',
                    text: me.snippets.button,
                    anchor: 'auto',
                    margin: '0 0 0 160',
                    handler: function() {
                        me.fireEvent('addLink', me.linkGrid, me.linkForm);
                    }
                }
            ]
        });

        return me.linkFormElements;
    },

    /**
     * Creates the grid for the already assigned article links
     * @return Ext.grid.Panel
     */
    createLinkGrid: function() {
        var me = this;

        me.linkGridElements = Ext.create('Ext.grid.Panel', {
            title: me.snippets.grid.title,
            store: me.article.getLink(),
            name: 'link-listing',
            height: 180,
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                }),
                {
                    ptype: 'grid-attributes',
                    table: 's_articles_information_attributes'
                }
            ],
            columns: [
                {
                    header: me.snippets.name,
                    dataIndex: 'name',
                    flex: 2,
                    editor: {
                        xtype: 'textfield'
                    }
                }, {
                    header: me.snippets.link,
                    dataIndex: 'link',
                    flex: 2,
                    editor: {
                        xtype: 'textfield'
                    }
                }, {
                    xtype: 'booleancolumn',
                    header: me.snippets.grid.external,
                    dataIndex: 'target',
                    flex: 1,
                    editor: {
                        xtype: 'checkbox'
                    },
                    // return true / false instead of active/inactive
                    renderer: me.statusColumn
                }, {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.snippets.grid.delete,
                            handler: function (view, rowIndex, colIndex, item, opts, record) {
                                me.fireEvent('removeLink', me.linkGrid, record)
                            }
                        }
                    ]
                }
            ]
        });

        return me.linkGridElements;
    },

    /**
      * Column renderer function for the status column.
      *
      * @param [string] value    - The field value
      * @param [string] metaData - The model meta data
      * @param [string] record   - The whole data model
      */
    statusColumn: function(value, metaData, record) {
        if (record.get('target')) {
            return '<div class="sprite-ui-check-box" style="width: 12px; height: 12px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 12px; height: 12px">&nbsp;</div>';
        }
    }

});
//{/block}
