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
 * @author shopware AG
 */

/**
 * Shopware UI - Price variation main window.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/configurator/price_variation"}
Ext.define('Shopware.apps.Article.view.variant.configurator.PriceVariation', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-price-variation-mapping-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-price-variation-mapping-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:600,
    /**
     * Define window height
     * @integer
     */
    height:380,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-article-article-price-variation-window',

    /**
     * Contains all snippets for this component
     * @object
     */
    snippets: {
        title: '{s name=price/variation/title}Price variation{/s}',
        close: '{s name=general/close_button}Close{/s}',
        options: '{s name=price/variation/options}Options{/s}',
        isGross: '{s name=price/variation/is_gross}Mode{/s}',
        net: '{s name=price/variation/mode_net}Net{/s}',
        gross: '{s name=price/variation/mode_gross}Gross{/s}',
        variation: '{s name=price/variation/variation}Variation{/s}',
        success: {
            title: '{s name=variant/success/title}Success{/s}',
            variationRemove: '{s name=variant/success/variation_removed}The configurator price variation was removed{/s}'
        },
        failure: {
            title: '{s name=variant/failure/title}Failure{/s}',
            variationRemove: '{s name=variant/failure/variation_removed}An error occurred while removing the configurator price variation:{/s}'
        }
    },

    /**
     * Price Variations store
     * Injected on creation
     */
    variationsStore: null,

    /**
     * Customer Groups store
     * Injected on creation
     */
    modeStore: null,

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
    initComponent: function() {
        var me = this;

        me.modeStore = Ext.create('Ext.data.ArrayStore', {
            fields: ['id', 'name'],
            data: [
                [ 0, me.snippets.net ],
                [ 1, me.snippets.gross ]
            ]
        });

        me.items = [ me.createGrid() ];
        me.dockedItems = me.createDockedItems();
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     * Creates the docked items for this component
     * @return Array
     */
    createDockedItems: function() {
        var me = this;
        return [ me.createTopBar(), me.createBottomBar() ];
    },

    /**
     * Creates the grid panel which displays all defined image mappings.
     * @return Ext.grid.Panel
     */
    createGrid: function() {
        var me = this;

        me.modeCombo = Ext.create('Ext.form.field.ComboBox', {
            allowBlank: false,
            valueField: 'id',
            displayField: 'name',
            queryMode: 'local',
            store: me.modeStore
        });


        me.mappingGridEditor = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            listeners: {
                edit: function(editor, e) {
                    e.record.save();
                }
            }
        });

        me.mappingGrid = Ext.create('Ext.grid.Panel', {
            name: 'mapping-grid',
            store: me.variationsStore,
            modeStore: me.modeStore,
            selModel: me.getGridSelModel(),
            plugins: [
                me.mappingGridEditor
            ],
            columns: [
                {
                    header: me.snippets.options,
                    dataIndex: 'option_names',
                    flex: 1,
                    renderer: me.mappingColumnRenderer
                },
                {
                    header: me.snippets.isGross,
                    dataIndex: 'isGross',
                    width: 120,
                    editor: me.modeCombo,
                    renderer: me.modeRenderer
                },
                {
                    header: me.snippets.variation,
                    dataIndex: 'variation',
                    width: 100,
                    xtype: 'numbercolumn',
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false
                    }
                },
                me.createActionColumn()
            ]
        });
        return me.mappingGrid;
    },

    modeRenderer: function(value, meta, record) {
        var me = this;

        var mode = me.modeStore.getById(value);

        return mode.get('name');
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    if (me.deleteButton === null) {
                        return;
                    }
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            }
        });
    },

    /**
     * Creates the action column for the mapping grid.
     * @return Ext.grid.column.Action
     */
    createActionColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width: 30,
            items: [ me.createDeleteItem() ]
        });
    },

    /**
     * Creates the delete action column item for the mapping grid.
     * @return Object
     */
    createDeleteItem: function() {
        var me = this;

        return {
            iconCls:'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.removeRecords(record);
            }
        };
    },

    /**
     * Renderer function of the mapping column. Iterates the defined
     * rules and displays the configured configurator options as string.
     * @return string
     */
    mappingColumnRenderer: function(value, metaData, record) {
        var me = this, result = [];

        Ext.each(value, function(item) {
            if (item.group && item.option) {
                result.push(
                    item.group +
                    ': ' +
                    '<strong>' + item.option + '</strong>'

                );
            }
        });

        return result.join(' & ');

    },

    createTopBar: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-plus-circle-frame',
            text: '{s name=price/variation/add_button}Add variation{/s}',
            handler: function() {
                me.fireEvent('displayNewPriceVariationWindow', me);
            }
        });

        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text: '{s name=price/variation/delete_button}Delete all selected{/s}',
            disabled: true,
            handler: function() {
                var selectionModel = me.mappingGrid.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records && records.length > 0) {
                    me.removeRecords(records);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: [  me.addButton, me.deleteButton ]
        });
    },

    /**
     * Creates the bottom bar for the mapping window.
     */
    createBottomBar: function() {
        var me = this;

        me.closeButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: me.snippets.close,
            handler: function() {
                me.fireEvent('closeListPriceVariation', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [ '->', me.closeButton ]
        });
    },

    removeRecords: function(records) {
        var me = this;

        me.mappingGrid.getStore().remove(records);
        me.mappingGrid.getStore().sync({
            success: function (operation) {
                Shopware.Notification.createGrowlMessage(
                    me.snippets.success.title,
                    me.snippets.success.variationRemove,
                    me.snippets.growlMessage
                );
            },
            failure: function (batch) {
                Shopware.Notification.createGrowlMessage(
                    me.snippets.failure.title,
                    me.snippets.failure.variationRemove,
                    me.snippets.growlMessage
                );
            }
        });
    }
});
//{/block}
