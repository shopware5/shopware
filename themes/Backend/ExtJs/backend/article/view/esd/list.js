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
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article esd page
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/esd/list"}
Ext.define('Shopware.apps.Article.view.esd.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-esd-list',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-esd-list',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        columns:{
            name:'{s name=esd/list/column/name}Articlename{/s}',
            downloads:'{s name=esd/list/column/downloads}Downloads{/s}',
            addedDate:'{s name=esd/list/column/addedDate}Added{/s}',
            serials:'{s name=esd/list/column/serials}Serials{/s}',
            file:'{s name=esd/list/column/file}File exists{/s}',

            remove: '{s name=esd/list/column/remove}Remove ESD{/s}',
            edit: '{s name=esd/list/column/edit}Edit ESD{/s}'
        },
        toolbar:{
            add:'{s name=esd/list/toolbar/button_add}Add as new ESD-Article{/s}',
            remove:'{s name=esd/list/toolbar/button_delete}Delete selected ESD-Articles{/s}',
            search:'{s name=esd/list/toolbar/search_empty_text}Search...{/s}',
            choose:'{s name=esd/list/toolbar/choose}Choose Variant{/s}'
        }
    },

    /**
     * Add attributes plugin
     */
    plugins: [
        {
            ptype: 'grid-attributes',
            table: 's_articles_esd_attributes'
        }
    ],

    /**
     * Initialize the Shopware.apps.Article.view.esd.List and defines the necessary default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();

        me.store = me.esdStore;

        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete button in the toolbar or
             * use the action column of the grid to remove one or multiple esds
             * @event deleteEsd
             * @param [array] Record - The selected records
             */
            'deleteEsd',

            /**
             * Event will be fired when the user clicks the add button in the toolbar
             * or makes a double click within the grid to add a new plain esd.
             * @param [string] articleDetailId
             * @event addEsd
             */
            'addEsd',

            /**
             * Event will be fired when the user insert a value into the search field of the toolbar
             * to filter the listing.
             * @event searchEsds
             */
            'searchEsd',

            /**
             * Event will be fired when the user clicks on the edit action column of the
             * grid, to edit a single esd.
             * @event editEsd
             * @param [Ext.data.Model] Record - The selected record
             */
            'editEsd'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: me.snippets.columns.name,
                dataIndex: 'name',
                sortable: true,
                flex: 3,
                renderer: me.articleNameColumnRenderer
            }, {
                header: me.snippets.columns.downloads,
                dataIndex: 'downloads',
                sortable: true,
                flex: 1,
                renderer: me.colorColumnRenderer
            }, {
                header: me.snippets.columns.addedDate,
                xtype: 'datecolumn',
                dataIndex: 'date',
                sortable: true,
                flex: 2
            } , {
                header: me.snippets.columns.serials,
                renderer: me.serialsColumnRenderer,
                flex: 2

            }, {
                header: me.snippets.columns.file,
                dataIndex: 'file',
                renderer: me.activeColumnRenderer,
                flex: 1
            },
            {
                /**
                 * Special column type which provides clickable icons in each row
                 */
                xtype:'actioncolumn',
                width:70,
                items:[
                    {
                        iconCls:'sprite-pencil',
                        action:'editEsd',
                        tooltip:me.snippets.columns.edit,
                        handler:function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('editEsd', record);
                        }
                    },
                    {
                        iconCls:'sprite-minus-circle-frame',
                        action:'deleteEsd',
                        tooltip:me.snippets.columns.remove,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var records = [ record ];
                            me.fireEvent('deleteEsd', records);
                        }
                    }
                ]
            }
        ];
    },


    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            }
        });
    },

    /**
     * Creates the grid toolbar with the different buttons.
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this;

        //creates the delete button to remove all selected esds in one request.
        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text: me.snippets.toolbar.remove,
            disabled: true,
            action:'deleteEsd',
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteEsd', records);
                }
            }
        });

        //creates the add button for the toolbar to grant the user the option to add esds manual.
        me.addButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-plus-circle-frame',
            text: me.snippets.toolbar.add,
            action:'addEsd',
            disabled: true,
            handler: function() {
                me.fireEvent('addEsd', me.combo.getValue());
                this.disable();
            }
        });

        //creates the search field to filter the listing.
        me.searchField = Ext.create('Ext.form.field.Text', {
            name:'searchfield',
            cls:'searchfield',
            width:170,
            emptyText:me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer:500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchEsd', value);
                }
            }
        });

        me.combo = Ext.create('Ext.form.ComboBox', {
            store: me.filteredStore,
            forceSelection: true,
            queryMode: 'local',
            valueField: 'id',
            width: 450,
            displayField: 'name',
            fieldLabel: me.snippets.toolbar.choose,
            emptyText: me.snippets.toolbar.choose,
            anchor: '100%',
            displayTpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                '{literal}' +
                    '{name}' +
                    '<tpl if="additionalText"> - {additionalText}</tpl>' +
                '{/literal}',
                '</tpl>'
            ),
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                    '{literal}' +
                        '<div class="x-boundlist-item">{name}' +
                        '<tpl if="additionalText">' +
                            '<span style="font-size:10px; font-weight: 800;"> - {additionalText}</span>' +
                        '</tpl>' +
                        '</div>' +
                    '{/literal}',
                '</tpl>'
            ),
            listeners: {
                select: function(field, records) {
                    me.addButton.enable();
                }
            }
        });


        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items:[
                me.combo,
                me.addButton,
                me.deleteButton,
                '->',
                me.searchField,
                { xtype:'tbspacer', width:6 }
            ]
        });
    },

    /**
     * Creates the paging toolbar for the grid to allow store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock:'bottom',
            displayInfo:true
        });
    },

    /**
     * Formats the name column
     *
     * @param [string] value - Name of the disptach
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    articleNameColumnRenderer: function (value, metaData, record) {
        if (!record.get('additionalText')) {
            return value;
        }

        return Ext.String.format('{literal}{0} - {1}{/literal}', value, record.get('additionalText'));
    },

    /**
     * Formats the serial column
     *
     * @param [string] value - Name of the disptach
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    serialsColumnRenderer: function (value, metaData, record) {
        if (!record.get('hasSerials')) {
            return '<div class="sprite-cross" style="width: 25px; height: 25px">&nbsp;</div>';
        }

        if (record.get('serialsUsed') >= record.get('serialsTotal')) {
            return Ext.String.format('{literal}<span style="color:red;">{0} / {1}</span>{/literal}', record.get('serialsUsed'), record.get('serialsTotal'));
        }

        return Ext.String.format('{literal}{0} / {1}{/literal}', record.get('serialsUsed'), record.get('serialsTotal'));
    },

    /**
     * @param [object] - value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    },

    /**
     * @param [object] - value
     */
    colorColumnRenderer: function(value) {
        if (value > 0){
            return '<span style="color:green;">' + value + '</span>';
        } else {
            return '<span style="color:red;">' + value + '</span>';
        }
    }
});
//{/block}
