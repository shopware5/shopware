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
 */

/**
 * The list controller handles the adding / editing of filters
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/filter"}
Ext.define('Shopware.apps.ArticleList.controller.Filter', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * CSS selectors
     */
    refs: [
        { ref:'grid', selector:'multi-edit-main-grid' },
        { ref:'simpleGrid', selector:'multi-edit-add-filter-grid' },
        { ref:'navigationGrid', selector:'multi-edit-navigation-grid' },
        { ref:'runButton', selector:'query-field button[name=run-button]' },
        { ref:'simpleRunButton', selector:'multi-edit-add-filter-grid button[name=run-button-simple]' },
        { ref:'tabPanel', selector:'multi-edit-add-filter-window tabpanel[name=filter-tab-panel]' },
        { ref:'queryField', selector:'query-field' },
        { ref:'filterCombo', selector:'query-field combo[name=filterString]' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'multi-edit-navigation-grid': {
                loadFilter: me.onLoadFilter,
                deleteFilter: me.onDeleteFilter,
                editFilter: me.onEditFilter
            },
            'multiedit-main-window button[action=addFilter]': {
                click: me.onAddFilter
            },
            'multi-edit-add-filter-grid button[action=addSimpleFilter]': {
                click: me.onAddSimpleFilter
            },
            'multi-edit-add-filter-window button[action=saveAdvanced]': {
                click: me.onSaveFilter
            },
            'multi-edit-add-filter-window': {
                tabChanged: me.onTabChanged
            },
            'multi-edit-add-filter-grid': {
                setEditor: me.onSetEditor,
                editRow: me.onEditRow,
                setEditorBeforeEdit: me.onSetEditorBeforeEdit,
                filter: me.onFilter,
                deleteRow: me.onDeleteSimpleFilterRow
            },
            'query-field': {
                filter: me.onFilter
            }
        });

        me.callParent(arguments);
    },

    /**
     * Callback function triggered, when the user deletes a row from the simple editor grid
     * @param rowIdx
     */
    onDeleteSimpleFilterRow: function(rowIdx) {
        var me = this,
            grid = me.getSimpleGrid(),
            store = grid.getStore(),
            record = store.getAt(rowIdx);

        store.remove(record);

        me.onEditRow(rowIdx);
    },

    /**
     * Returns a filter string from the simple filter editor
     */
    getFilterStringFromSimpleFilter: function() {
        var me = this,
            grid = me.getSimpleGrid(),
            store = grid.getStore(),
            valid,
            suggestController = me.getController('Suggest'),
            result = [ ], value;

        // Iterate the store
        store.each(function(record) {

            // Check if value is set. If it is, we need to quote it
            value = record.get('value');
            if (value && value.length > 0) {
                value = ' "' + value + '" ';
            } else {
                value = '';
            }

            // Do not add empty rows to the result set.
            if (record.get('column').length > 0) {
                 // Concat and push the results
                result.push(record.get('column') + ' ' + record.get('operator') + value);
            }
        });

        // All conditions are joined with "AND". We might make this configurable one day
        result = result.join(' AND ');

        valid = suggestController.getParser().parse(result);
        suggestController.setStatus();

        return { filterString: result, valid: valid };
    },

    /**
     * Callback function called, when the user clicks the 'execute' button in the add filter window
     */
    onFilter: function() {
        var me = this,
            tabPanel = me.getTabPanel(),
            activeTab = tabPanel.getActiveTab(),
            suggestController = me.getController('Suggest'),
            filterString;

        if (activeTab.xtype === 'query-field') {
            filterString = me.getFilterCombo().getValue();
        } else {
            filterString = me.getFilterStringFromSimpleFilter().filterString;
        }

        suggestController.loadFilter(filterString, '{s name=unsavedFilter}Unsaved filter{/s}');
    },

    /**
     * Callback triggered after the user edited a row
     *
     * @param rowIdx
     */
    onEditRow: function(rowIdx) {
        var me = this,
            result = me.getFilterStringFromSimpleFilter();

        // We set the combo, even if the filter string is invalid
        // The user might want to have a closer look at the error
        me.getFilterCombo().setValue(result.filterString);
        me.getFilterCombo().collapse();

        me.getSimpleGrid().getStore().commitChanges();
    },

    /**
     * Callback triggered when the user clicks the "add" button in the simple filter view
     */
    onAddSimpleFilter: function() {
        var me = this,
            grid = me.getSimpleGrid(),
            store = grid.store,
            record,
            emptyRecordAt;

        // Find an existing, empty record
        emptyRecordAt = store.findBy(function(record) {
            if (!record.get('column')) {
                return true;
            }
        });

        // If there is an empty record - use that one
        if (emptyRecordAt >= 0) {
            record = store.getAt(emptyRecordAt);
        // Else create a new one
        } else {
            record = Ext.create('Shopware.apps.ArticleList.model.Operation',  { });
            store.add(record);
        }

        grid.rowEditing.startEdit(record, 0);
    },

    /**
     * Callback function triggered, after the user clicked a tab
     * @param tabPanel
     * @param newTab
     * @param oldTab
     */
    onTabChanged: function(tabPanel, newTab, oldTab) {
        var me = this,
            value, oldValue;

        if (newTab.internalTitle === 'simple') {
            oldValue = oldTab.down('combo').getValue();

            value = me.getController('Suggest').getSimpleResult(oldValue);
            if (value || oldValue.trim().length === 0) {
                me.loadSimpleTokens(value);
            } else  {
                Ext.MessageBox.confirm(
                    '{s name=proceed}Proceed?{/s}',
                    '{s name=convertFilterMessage}You cannot convert this query to a simple filter. Do you want to proceed anyway and loose the current filter?{/s}',
                    function (response) {
                        if ( response === 'yes' ) {
                            return;
                        }

                        tabPanel.setActiveTab(oldTab);
                        me.getFilterCombo().setValue(oldValue);

                    }
                );
            }

        } else {
            value = me.getFilterStringFromSimpleFilter();
            me.getFilterCombo().setValue(value.filterString);

        }
    },

    /**
     * Loads the 'simple' addFilter view
     *
     * @param tokens
     */
    loadSimpleTokens: function(tokens) {
        var me = this,
            record,
            simpleGrid = me.getSimpleGrid();

        simpleGrid.getStore().removeAll();

        Ext.each(tokens, function(currentRow) {
            record = Ext.create('Shopware.apps.ArticleList.model.Operation',  { });
            if (currentRow.length === 1) {
                record.set('column', currentRow[0]['token']);
            } else {
                record.set('column', currentRow[0]['token']);
                record.set('operator', currentRow[1]['token']);

                if (currentRow[2]) {
                    var token = currentRow[2]['token'];
                    if (token.substring(0, 1) == token.substring(token.length - 1) && token.substring(0, 1) == '"') {
                        token = token.substring(1, token.length-1);
                    }
                    record.set('value', token);
                }
            }
            simpleGrid.getStore().add(record);
        });

        simpleGrid.getStore().commitChanges();
    },


    /**
     * Called, when the user clicks the 'save' button in the 'addFilter' dialog and is in advanced filter context
     *
     * @param button
     * @param event
     */
    onSaveFilter: function(button, event) {
        var me = this,
            combo = me.getFilterCombo(),
            form = button.up('window').down('form'),
            // Get the currently activated tab
            tabPanel = me.getTabPanel(),
            activeTab = tabPanel.getActiveTab(),
            suggestController = me.getController('Suggest'),
            filterString;

        if (!form.getForm().isValid()) {
            return;
        }

        // Get filter string depending on the tab panel currently active
        if (activeTab.xtype === 'query-field') {
            filterString = me.getFilterCombo().getValue();
        } else {
            filterString = me.getFilterStringFromSimpleFilter().filterString;
        }

        // Check the filter string
        if (!suggestController.isFilterValid(filterString)) {
            var message;
            if (!filterString) {
                message = 'Empty filter';
            } else {
                message = Ext.String.format('{s name=parserError}Could not parse the string \'[0]\': <br><br>[1]{/s}', filterString, me.getController('Suggest').getErrorMessage());
            }
            Shopware.Notification.createGrowlMessage('{s name=error}Error{/s}', message);
            return;
        }

        // update the record and save it
        form.getForm().updateRecord(record);
        var record = form.getForm().getRecord();

        record.save({
            success: function(record, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage('{s name=successTitle}Success{/s}', Ext.String.format('{s name=successMessage}Successfully saved [0]{/s}', ''));
                }
                me.subApplication.filterStore.load();
                me.closeFilterWindow();
            },
            failure: function(record, operation) {
                Shopware.Notification.createStickyGrowlMessage({
                    title: '{s name=error}Error{/s}',
                    text: '{s name=unknownError}An unknown error occurred, please check your server logs{/s}',
                    log: true
                },
                'ArticleList');
            }
        });
    },

    /**
     * Callback function triggered when the user clicks a filter in the left grid
     *
     * @param record
     */
    onLoadFilter: function(record) {
        var me = this;

        me.getController('Suggest').loadFilter(record.get('filterString'), record.get('name'));
    },

    /**
     * Shows an existing instance of the addFilter-dialog or creates a new one
     *
     * @returns Ext.window
     */
    getFilterWindow: function() {
        var me = this;

        if (me.subApplication.addFilterWindow && !me.subApplication.addFilterWindow.isDestroyed) {
            return me.subApplication.addFilterWindow;
        } else {
            me.grammar = me.getController('Suggest').getParser().grammar;
            me.filterableColumns = me.getColumnsFromGrammar();

            me.createSimpleFilterStores();
            me.subApplication.addFilterWindow = me.getView('AddFilter.Window').create({
                filterableColumns: me.filterableColumns,
                columnStore: me.columnStore,
                operatorStore: me.operatorStore
            });
        }

        return me.subApplication.addFilterWindow;
    },

    /**
     * Will close the filter-window safely
     */
    closeFilterWindow: function() {
        var me = this,
            window = me.getFilterWindow();
        // Make sure, that the dropdown is hidden, when the window is closed
        window.down('filterString').collapse();
        window.hide();

        window.down('form').getForm().reset();
        window.down('grid').getStore().removeAll();
    },

    /**
     * Get a list of attributes / operators which an be selected as operators
     *
     * @returns Array
     */
    getColumnsFromGrammar: function() {
        var me = this,
            attributes = me.grammar['attributes'],
            nullary = me.grammar['nullaryOperators'],
            result = [ ], resultStorted = [ ];

        Ext.each(Object.keys(attributes), function(key) {
            result[key] = attributes[key];
        });

        Ext.each(Object.keys(nullary), function(key) {
            result[key] = nullary[key];
        });

        Ext.each(Object.keys(result).sort(), function(key) {
            resultStorted[key] = result[key];
        });

        return resultStorted;
    },

    /**
     * Creates the stores for the "simple" filter editor
     */
    createSimpleFilterStores: function() {
        var me = this;

        me.columnStore = Ext.create('Ext.data.Store', {
            model : 'Shopware.apps.ArticleList.model.Operator'
        });

        Ext.each(Object.keys(me.filterableColumns), function(attribute) {
            me.columnStore.add({ name: attribute })
        });

        me.operatorStore = Ext.create('Ext.data.Store', {
            model : 'Shopware.apps.ArticleList.model.Operator'
        });
    },

    /**
     * Callback function triggered when the user starts to edit a row
     * This will set the row editor fields depending on the selected column and operator…
     *
     * @param columns
     * @param record
     */
    onSetEditorBeforeEdit: function(columns, record) {
        var me = this,
            binaryOperators = Object.keys(me.grammar['binaryOperators']),
            columnName = record.get('column'),
            operatorName = record.get('operator'),
            operatorColumn = columns[1],
            valueColumn = columns[2],
            operators = me.filterableColumns[columnName],
            grid = me.getSimpleGrid();

        // If no operators available, disable value editor and operator editor
        if (!operators instanceof Array) {
            operatorColumn.setEditor(false);
            valueColumn.setEditor(false);
            return;
        }

        operatorColumn.setEditor(grid.getDefaultOperatorEditor());
        valueColumn.setEditor(false);

        // Check if the current operator allows a value field
        if (operatorName && operatorName.length > 0) {
            if (binaryOperators.indexOf(operatorName) != -1) {
                valueColumn.setEditor(grid.getDefaultValueEditor());
            }
            return;
        }

        if (!operators) {
            return;
        }

        // If any binaryOperators is associated with the value field, enable it
        for (var i=0;i<operators.length;i++) {
            var operator = operators[i];
            if (binaryOperators.indexOf(operator) != -1) {
                valueColumn.setEditor(grid.getDefaultValueEditor());
                return;
            }
        }
    },

    /**
     * Fired when the user changed the selected column in batch grid.
     * We will need to set the value editor here.
     *
     * @param columns
     * @param record
     * @param dataIndex
     */
    onSetEditor: function(columns, record, dataIndex) {
        var me = this,
            binaryOperators = Object.keys(me.grammar['binaryOperators']),
            operatorColumn = columns[1],
            valueColumn = columns[2],
            grid = me.getSimpleGrid();

        if (dataIndex === 'operator') {
            var operatorName = record.get('name');

            // If the selected operator is not a binary operator: Disable the value field
            if (binaryOperators.indexOf(operatorName) == -1) {
                valueColumn.setEditor(false);
            // If the selected operator is a binary operator: Enable the value field
            } else {
                valueColumn.setEditor(grid.getDefaultValueEditor());
            }
        } else if (dataIndex === 'column') {
            var columnName = record.get('name'),
                operators = me.filterableColumns[columnName];

            // If no operators available, disable value editor and operator editor
            if (!operators instanceof Array) {
                operatorColumn.setEditor(false);
                valueColumn.setEditor(false);
                return;
            }

            operatorColumn.setEditor(grid.getDefaultOperatorEditor());
            valueColumn.setEditor(false);

            // If any binaryOperators is associated with the value field, enable it
            for (var i=0;i<operators.length;i++) {
                var operator = operators[i];
                if (binaryOperators.indexOf(operator) != -1) {
                    valueColumn.setEditor(grid.getDefaultValueEditor());
                    return;
                }
            }
        }
    },

    /**
     * Callback function triggered when the user clicks the 'addFilter' button
     */
    onAddFilter: function() {
        var me = this,
            record = Ext.create('Shopware.apps.ArticleList.model.Filter', { });

        me.getFilterWindow().show();
        me.getTabPanel().setActiveTab(0);
        me.getQueryField().up('form').getForm().reset().loadRecord(record);
    },

    /**
     * Will be called, after the user clicks on the pencil icon in the filter list
     *
     * @param rowIndex
     */
    onEditFilter: function(rowIndex) {
        var me = this,
            store = me.subApplication.filterStore,
            record = store.getAt(rowIndex),
            form;

        me.getFilterWindow().show();
        me.getTabPanel().setActiveTab(0);
        form = me.getQueryField().up('form').getForm();
        form.reset();

        form.loadRecord(record);

        if (record.get('isSimple')) {
            var simpleTokens = me.getController('Suggest').getTokensFromString(record.get('filterString'));
            me.subApplication.getController('Filter').loadSimpleTokens(simpleTokens );
        }

        me.getFilterCombo().collapse();
    },

    /**
     * Called after the user clicked on the delete icon in the filter list
     *
     * @param rowIndex
     */
    onDeleteFilter: function(rowIndex) {
        var me = this,
            store = me.subApplication.filterStore,
            record = store.getAt(rowIndex);

        Ext.MessageBox.confirm(
            '{s name=deleteFilterTitle}Delete filter?{/s}',
            Ext.String.format('{s name=deleteFilterMessage}Do you want to delete the filter \'[0]\'?{/s}', record.get('name')),
            function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                record.destroy({
                    success: function() {
                        store.load();
                        Shopware.Notification.createGrowlMessage('{s name=successTitle}Success{/s}', Ext.String.format('{s name=successDeleteMessage}Successfully deleted [0]{/s}', ''));
                    }
                });
            }
        );
    }
});
//{/block}
