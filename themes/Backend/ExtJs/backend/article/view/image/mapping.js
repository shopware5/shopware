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
 * Shopware UI - Article Image Mapping window.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/mapping"}
Ext.define('Shopware.apps.Article.view.image.Mapping', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-image-mapping-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-image-mapping-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border: false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow: true,
    /**
     * Set border layout for the window
     * @string
     */
    layout: 'fit',
    /**
     * Define window width
     * @integer
     */
    width: 480,
    /**
     * Define window height
     * @integer
     */
    height: 380,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable: true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable: true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful: true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId: 'shopware-article-image-mapping-window',

    /**
     * In case of MultiSelection: Contains all selected image records
     */
    selectedMultiItems: [],

    /**
     * Contains all snippets for this component
     * @object
     */
    snippets: {
        title: '{s name=image/mapping/title}Image mapping{/s}',
        cancel: '{s name=cancel_button}Cancel{/s}',
        save: '{s name=general/save_button}Save{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     */
    initComponent: function () {
        var me = this;

        me.store = me.copyStore();
        me.items = [me.createGrid()];
        me.dockedItems = me.createDockedItems();
        me.title = me.snippets.title;

        if (me.hasMultiple) {
            me.title += Ext.String.format('{s name="image/variant_info/mapping_window/title"} ([0] images){/s}', me.selected.length);
            me.mappingGrid.view.emptyText = '<div style="font-size:13px; text-align: center; color: #6c818f; width: 420px; margin: 20px auto;">{s name="image/mapping_window/empty_text"}{/s}</div>';
            me.mappingGrid.view.deferEmptyText = false;
        }

        me.callParent(arguments);
    },

    /**
     * @returns { Ext.data.Store }
     */
    copyStore: function () {
        var me = this,
            store = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.MediaMapping' });

        if (!me.selected) {
            return store;
        }

        if (me.hasMultiple) {
            me.selectedMultiItems = me.selected;
            return store;
        }

        if (!(me.selected.getMappings() instanceof Ext.data.Store) || me.selected.getMappings().getCount() < 1) {
            return store;
        }

        me.selected.getMappings().each(function (mapping) {
            store.add(mapping);
        });

        return store;
    },

    /**
     * Creates the docked items for this component
     * @return { Array }
     */
    createDockedItems: function () {
        var me = this;
        return [me.createTopBar(), me.createBottomBar()];
    },

    /**
     * Creates the grid panel which displays all defined image mappings.
     *
     * @return { Ext.grid.Panel }
     */
    createGrid: function () {
        var me = this;

        me.mappingGrid = Ext.create('Ext.grid.Panel', {
            name: 'mapping-grid',
            store: me.store,
            selModel: me.getGridSelModel(),
            columns: [
                {
                    header: me.snippets.title,
                    dataIndex: 'rules',
                    flex: 1,
                    renderer: me.mappingColumnRenderer
                },
                me.createActionColumn()
            ]
        });
        return me.mappingGrid;
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return { Ext.selection.CheckboxModel } grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange: function (sm, selections) {
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
     * @return { Ext.grid.column.Action }
     */
    createActionColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width: 30,
            items: [me.createDeleteItem()]
        });
    },

    /**
     * Creates the delete action column item for the mapping grid.
     * @return { Object }
     */
    createDeleteItem: function () {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.mappingGrid.store.remove(record);
            }
        };
    },

    /**
     * Renderer function of the mapping column. Iterates the defined
     * rules and displays the configured configurator options as string.
     *
     * @param { Object } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @returns { string }
     */
    mappingColumnRenderer: function (value, metaData, record) {
        if (record instanceof Ext.data.Model &&
            record.getRules() instanceof Ext.data.Store &&
            record.getRules().getCount() > 0) {

            var optionNames = [];
            record.getRules().each(function (item) {
                if (item instanceof Ext.data.Model && item.getOption() && item.getOption().first()) {
                    optionNames.push(item.getOption().first().get('name'));
                }
            });
            return optionNames.join(' > ');

        } else {
            return '';
        }
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createTopBar: function () {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-plus-circle-frame',
            text: '{s name=image/mapping/add_button}Add mapping{/s}',
            handler: function () {
                me.fireEvent('displayNewRuleWindow', me);
            }
        });

        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: '{s name=image/mapping/delete_button}Delete all selected{/s}',
            disabled: true,
            handler: function () {
                var selectionModel = me.mappingGrid.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records && records.length > 0) {
                    me.mappingGrid.getStore().remove(records);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: [me.addButton, me.deleteButton]
        });
    },

    /**
     * Creates the bottom bar for the mapping window.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createBottomBar: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: me.snippets.save,
            handler: function () {
                me.fireEvent('saveMapping', me);
            }
        });

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: me.snippets.cancel,
            handler: function () {
                me.fireEvent('cancel', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: ['->', me.cancelButton, me.saveButton]
        });
    }

});
//{/block}
