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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/elements/base"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Base', {

    extend: 'Ext.Component',

    alias: 'widget.detail-element-base',

    cls: Ext.baseCSSPrefix + 'emotion-element',

    compCls: 'base-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHBJREFUeNpi7Ju95D8DBaAoNYYRmU+peUwMgwyMOmjUQaMOGnXQqIMGu4NYqG0get02GmWjaYhUMOzaQyw0NNsRmOMOEBmqDkBq/2iiHnXQqINGHTTqoFEHjTpotIFGGTAANiuIVksPB/UPiygDCDAAYIwS2uYlRscAAAAASUVORK5CYII=',

    minRows: 1,

    maxRows: null,

    minCols: 1,

    maxCols: null,

    instanceOnly: false,

    initComponent: function() {
        var me = this;

        /**
         * Possibility of creating just the basic instance of the class.
         * Used to get access to the settings of the "abstract" class
         * without invoking the complete grid logic.
         */
        if (me.instanceOnly) {
            me.callParent(arguments);
            return;
        }

        me.component = me.record.getComponent().first();

        me.data = me.record.data;
        me.tpl = me.createTemplate();

        me.initElement();

        me.callParent(arguments);
    },

    initElement: function() {
        var me = this,
            compCls = me.component.get('cls');

        if (Ext.isDefined(compCls)) {
            me.compCls = compCls
        }

        me.addCls(me.compCls);

        me.style = me.getStyles();
    },

    updateElement: function() {
        var me = this;

        me.style = me.getStyles();
    },

    createTemplate: function() {
        var me = this,
            elementLabelCls = Ext.baseCSSPrefix + 'emotion-element-label',
            elementIconCls = Ext.baseCSSPrefix + 'emotion-element-icon',
            elementPreviewCls = Ext.baseCSSPrefix + 'emotion-element-preview',
            elementOverlayCls = Ext.baseCSSPrefix + 'emotion-element-overlay',
            elementInfoCls = Ext.baseCSSPrefix + 'emotion-element-info',
            elementOptionsCls = Ext.baseCSSPrefix + 'emotion-element-options';

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',

                // Preview of the element
                '<div class="' + elementPreviewCls + '">',
                    me.createPreview(),
                '</div>',

                '<div class="' + elementOverlayCls + '">',
                    '<div class="' + elementInfoCls + '">',

                        '<div class="' + elementIconCls + '">',
                            '<img src="' + me.getIcon() + '" />',
                        '</div>',

                        // Label of the element
                        '<div class="' + elementLabelCls + '">',
                            me.getLabel(),
                        '</div>',

                        // Option buttons of the element
                        '<div class="' + elementOptionsCls + '">',
                            me.createOptions(),
                        '</div>',

                    '</div>',
                '</div>',
            '</tpl>{/literal}');
    },

    createOptions: function() {
        var me = this,
            options = '';

        options += '<div class="x-emotion-element-pencil" data-emotionid="' + me.record.internalId + '"></div>';
        options += '<div class="x-emotion-element-visibility" data-emotionid="' + me.record.internalId + '"></div>';
        options += '<div class="x-emotion-element-delete" data-emotionid="' + me.record.internalId + '"></div>';

        return options;
    },

    /**
     * Proxy function for creating the preview of the element.
     * Override to implement the preview template of an individual element.
     *
     * @returns { string }
     */
    createPreview: function() {
        var me = this,
            label = Ext.String.format('<div class="x-emotion-preview-title">[0]</div>', me.getLabel());

        return Ext.String.format('<div class="x-emotion-element-preview-content">[0]</div>', label);
    },

    /**
     * Returns the necessary styles for the grid element.
     *
     * @returns { object }
     */
    getStyles: function() {
        var me = this,
            cellSize = me.gridView.getCurrentCellSize(),
            gridSettings = me.getGridSettings();

        // When the element is not visible don't set any styling
        if (!gridSettings.visible) {
            return {};
        }

        var cols = gridSettings.endCol - gridSettings.startCol + 1,
            rows = gridSettings.endRow - gridSettings.startRow + 1,
            width = me.gridView.getWidthFromColumns(cols, cellSize),
            height = me.gridView.getHeightFromRows(rows, cellSize),
            position = me.gridView.getCellPosition(gridSettings.startRow, gridSettings.startCol);

        return {
            top: position[1] + 'px',
            left: position[0] + 'px',
            width: width + 'px',
            height: height + 'px'
        };
    },

    /**
     * Returns the grid settings of the element for the given viewport.
     *
     * @param viewportAlias { string }
     * @returns { object }
     */
    getGridSettings: function(viewportAlias) {
        var me = this,
            viewports,
            viewport,
            state = viewportAlias || me.gridView.state || null,
            settings = {
                'startCol': 1,
                'startRow': 1,
                'endCol': 1,
                'endRow': 1,
                'visible': false
            };

        if (state === null) {
            return settings;
        }

        viewports = me.record.getViewports();
        viewport = viewports.findRecord('alias', state);

        if (viewport === null) {
            return settings;
        }

        return {
            'viewport': viewport.get('alias'),
            'startCol': viewport.get('startCol'),
            'startRow': viewport.get('startRow'),
            'endCol': viewport.get('endCol'),
            'endRow': viewport.get('endRow'),
            'visible': viewport.get('visible')
        }
    },

    /**
     * Sets the settings for one or several viewports by the viewport alias.
     *
     * @param settings { object }
     * @param viewportAlias { string | [] }
     */
    setGridSettings: function(settings, viewportAlias) {
        var me = this,
            state = viewportAlias || me.gridView.state || null,
            gridSettings = me.getGridSettings(Ext.isArray(state) ? state[0] : state),
            newSettings = Ext.merge(gridSettings, settings);

        if (state === null) {
            me.record.set({
                startRow: newSettings.startRow,
                startCol: newSettings.startCol,
                endRow: newSettings.endRow,
                endCol: newSettings.endCol
            });

        } else if (Ext.isArray(state)) {
            Ext.each(state, function(alias) {
                me.setViewportSettings(newSettings, alias);
            });

        } else {
            me.setViewportSettings(newSettings, state);
        }
    },

    /**
     * Set the grid settings for a single viewport by alias.
     *
     * @param settings { object }
     * @param viewportAlias { string }
     * @returns { boolean }
     */
    setViewportSettings: function(settings, viewportAlias) {
        var me = this,
            state = viewportAlias || me.gridView.state || null,
            viewports, viewport;

        if (state === null) {
            return false;
        }

        viewports = me.record.getViewports();

        if (viewports.getCount() <= 0) {
            viewports = me.initViewports();
        }

        viewport = viewports.findRecord('alias', state);

        if (viewport === null) {
            viewport = Ext.create('Shopware.apps.Emotion.model.Viewport', {
                id: Ext.id(),
                alias: state
            });

            viewports.add(viewport);
        }

        viewport.set({
            startRow: settings.startRow,
            startCol: settings.startCol,
            endRow: settings.endRow,
            endCol: settings.endCol,
            visible: settings.visible
        });
    },

    /**
     * Copy grid settings from one viewport to several others.
     *
     * @param toAlias { string | [] }
     * @param fromAlias { string }
     * @returns { boolean }
     */
    copyViewportSettings: function(toAlias, fromAlias) {
        var me = this,
            fromState = fromAlias || me.gridView.state || null,
            toState = toAlias || null,
            settings;

        if (fromState === null || toState === null) {
            return false;
        }

        settings = me.getGridSettings(fromState);

        me.setGridSettings(settings, toState);
    },

    /**
     * Compare the settings of two viewports.
     *
     * @param viewportAliasCheck { string }
     * @param viewportAlias { string }
     * @returns { boolean }
     */
    hasSameViewportSettings: function(viewportAliasCheck, viewportAlias) {
        var me = this,
            state = viewportAlias || me.gridView.state || null,
            checkState = viewportAliasCheck || null,
            viewport = me.getGridSettings(state),
            checkViewport = me.getGridSettings(checkState);

        return viewport.startRow === checkViewport.startRow &&
               viewport.startCol === checkViewport.startCol &&
               viewport.endRow === checkViewport.endRow &&
               viewport.endCol === checkViewport.endCol &&
               viewport.visible === checkViewport.visible;
    },

    initViewports: function() {
        var me = this,
            viewports = me.record.getViewports();

        me.gridView.viewportStore.each(function(item) {
            var viewport = Ext.create('Shopware.apps.Emotion.model.Viewport', {
                id: Ext.id(),
                alias: item.get('alias')
            });

            viewports.add(viewport);
        });

        return viewports;
    },

    afterRender: function() {
        var me = this;

        me.createDragZone();
        me.createElementResizer();
        me.registerEvents();
    },

    registerEvents: function() {
        var me = this,
            el = me.getEl();

        el.on({
            'dblclick': {
                scope: me,
                fn: function () {
                    me.gridView.openSettingsWindow(me.record);
                }
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.x-emotion-element-pencil',
                fn: function () {
                    me.gridView.openSettingsWindow(me.record);
                }
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.x-emotion-element-delete',
                fn: function () {
                    me.gridView.deleteElement(me.record);
                }
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.x-emotion-element-visibility',
                fn: function () {
                    me.setGridSettings({
                        startRow: 1,
                        startCol: 1,
                        endRow: 1,
                        endCol: 1,
                        visible: false
                    }, me.gridView.stateConnections);

                    me.gridView.designer.counterChange = me.gridView.stateConnections;
                    me.gridView.refresh();
                }
            }
        });
    },

    createDragZone: function() {
        var me = this,
            element = me.getEl();

        if (!me.gridView.settings.drag || me.hasCls('x-draggable')) {
            return false;
        }

        me.dragZone = Ext.create('Ext.dd.DragZone', element, {

            ddGroup: 'emotion-dd',

            containerScroll: true,

            proxyCls: Ext.baseCSSPrefix + 'emotion-dd-proxy',

            onBeforeDrag: function(data) {
                return !me.hasCls('x-resizable-over');
            },

            onStartDrag: function() {
                me.addCls('is--dragging');
            },

            getDragData: function() {
                var sourceEl = element.dom,
                    dragRecord = me.record,
                    sourceStore = me.gridView.store,
                    dragEl, proxyEl = me.dragZone.proxy;

                proxyEl.getEl().addCls(Ext.baseCSSPrefix + 'shopware-dd-proxy');

                // When the CTRL key is pressed during dragging the element is cloned instead.
                if (me.gridView.isPressedCtrl) {
                    var id = Ext.id();

                    // Clone the record with new internal id.
                    dragRecord = me.record.copy(id);
                    dragRecord.set('id', id);

                    // Reset associations
                    dragRecord.getViewports().removeAll();
                    dragRecord.getComponent().removeAll();
                    dragRecord.getComponent().add(me.record.getComponent().first());

                    // Create a pseudo source store which holds the record.
                    sourceStore = Ext.create('Ext.data.Store', {
                        model: 'Shopware.apps.Emotion.model.EmotionElement',
                        data: [ dragRecord ]
                    });
                }

                dragEl = sourceEl.cloneNode(true);
                dragEl.id = Ext.id();

                return {
                    ddel: dragEl,
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    sourceStore: sourceStore,
                    draggedRecord: dragRecord
                }
            },

            getRepairXY: function() {

                if (me.gridView.previewElement) {
                    me.gridView.previewElement.remove();
                }

                me.removeCls('is--dragging');

                return this.dragData.repairXY;
            },

            afterDragDrop: function() {

                if (me.gridView.previewElement) {
                    me.gridView.previewElement.remove();
                }

                me.removeCls('is--dragging');
            }
        });

        me.addCls('x-draggable');
    },

    createElementResizer: function () {
        var me = this,
            cellSize = me.gridView.getCurrentCellSize(),
            cellSpacing = me.gridView.emotion.get('cellSpacing'),
            rows = me.gridView.emotion.get('rows'),
            cols = me.gridView.emotion.get('cols'),
            handles = me.getResizeHandles();

        if (!handles || !handles.length || !me.getVisible()) {
            return;
        }

        me.gridResizer = Ext.create('Ext.resizer.Resizer', {
            el: me,
            handles: handles,
            minHeight: cellSize.height,
            minWidth: cellSize.width,
            maxHeight: (cellSize.height + cellSpacing) * rows - cellSpacing,
            maxWidth: (cellSize.width + cellSpacing) * cols - cellSpacing,
            target: me,
            constrainTo: me.gridView.getEl().down('.x-designer-grid'),
            listeners: {
                scope: me,
                resizedrag: me.onResizeDrag,
                resize: me.onElementResize,
                beforeresize: me.onBeforeResize
            }
        });
    },

    getResizeHandles: function() {
        var me = this,
            resizeRow = me.gridView.settings.resizeRow,
            resizeCol = me.gridView.settings.resizeCol,
            handles = '';

        if (!resizeRow && !resizeCol) {
            return false;
        }

        if (resizeCol) {
            handles += 'e ';
        }

        if (resizeRow) {
            handles += 's '
        }

        if (resizeCol && resizeRow) {
            handles += 'se'
        }

        return Ext.String.trim(handles);
    },

    onBeforeResize: function(resizer) {
        resizer.target.addCls('is--resizing');
    },

    onResizeDrag: function(resizer, width, height) {
        var me = this,
            gridSettings = me.getGridSettings(),
            element = resizer.target,
            cellSize = me.gridView.getCurrentCellSize(),
            cellSpacing = me.gridView.emotion.get('cellSpacing'),
            cols = Math.round((width + cellSpacing) / (cellSize.width + cellSpacing)),
            rows = Math.round((height + cellSpacing) / (cellSize.height + cellSpacing)),
            startRow, startCol, endRow, endCol;

        cols = me.clampCols(cols);
        rows = me.clampRows(rows);

        // Get the start row and column
        startRow = gridSettings.startRow;
        startCol = gridSettings.startCol;

        // Calculate the new endRow and endCol
        endRow = startRow + rows - 1;
        endCol = startCol + cols - 1;

        if (!me.gridView.validateDrop(me.record, startRow, startCol, endRow, endCol)) {
            element.addCls('is--invalid');
        } else {
            element.removeCls('is--invalid');
        }

        element.setWidth(me.gridView.getWidthFromColumns(cols, cellSize));
        element.setHeight(me.gridView.getHeightFromRows(rows, cellSize));
    },

    onElementResize: function(resizer, width, height) {
        var me = this,
            gridSettings = me.getGridSettings(),
            element = resizer.target,
            cellSize = me.gridView.getCurrentCellSize(),
            cellSpacing = me.gridView.emotion.get('cellSpacing'),
            cols = Math.round((width + cellSpacing) / (cellSize.width + cellSpacing)),
            rows = Math.round((height + cellSpacing) / (cellSize.height + cellSpacing)),
            startRow, startCol, endRow, endCol;

        // Remove resizing class
        element.removeCls('is--resizing');
        element.removeCls('is--invalid');

        cols = me.clampCols(cols);
        rows = me.clampRows(rows);

        // Get the start row and column
        startRow = gridSettings.startRow;
        startCol = gridSettings.startCol;

        // Calculate the new endRow and endCol
        endRow = startRow + rows - 1;
        endCol = startCol + cols - 1;

        // Validate the resize drop
        if (!me.gridView.validateDrop(me.record, startRow, startCol, endRow, endCol)) {

            // Get the original endRow and endCol
            endRow = gridSettings.endRow;
            endCol = gridSettings.endCol;

            // Reset the element size to the original size
            element.setWidth(me.gridView.getWidthFromColumns(endCol - startCol + 1, cellSize));
            element.setHeight(me.gridView.getHeightFromRows(endRow - startRow + 1, cellSize));

            return false;
        }

        // Set the new with and height of the element by snapping to the cells
        element.setWidth(me.gridView.getWidthFromColumns(cols, cellSize));
        element.setHeight(me.gridView.getHeightFromRows(rows, cellSize));

        // Update the record
        me.setGridSettings({
            endRow: endRow,
            endCol: endCol
        }, me.gridView.stateConnections);

        // Refresh the data view
        me.gridView.refresh();
    },

    getConfigValue: function(fieldName) {
        var me = this, configField,
            data = me.record.get('data');

        Ext.each(data, function(field) {
            if (field.key === fieldName) {
                configField = field;
                return false;
            }
        });

        return (Ext.isDefined(configField)) ? configField.value : undefined;
    },

    getLabel: function() {
        return this.component.get('fieldLabel');
    },

    getIcon: function() {
        return this.icon;
    },

    getVisible: function(viewportAlias) {
        var me = this,
            state = viewportAlias || me.gridView.state,
            settings = me.getGridSettings(state);

        return settings.visible;
    },

    getRows: function() {
        var me = this,
            gridSettings = me.getGridSettings(),
            rows = gridSettings.endRow - gridSettings.startRow + 1;

        return me.clampRows(rows);
    },

    getCols: function() {
        var me = this,
            gridSettings = me.getGridSettings(),
            cols = gridSettings.endCol - gridSettings.startCol + 1;

        return me.clampCols(cols);
    },

    clampRows: function(rows) {
        var me = this;

        rows = Math.max(rows, me.minRows);

        if (me.maxRows !== null && me.maxRows > me.minRows) {
            rows = Math.min(rows, me.maxRows);
        }

        if (me.gridView.settings.maxElementRows !== null) {
            rows = Math.min(rows, me.gridView.settings.maxElementRows);
        }

        return rows;
    },

    clampCols: function(cols) {
        var me = this;

        cols = Math.max(cols, me.minCols);

        if (me.maxCols !== null && me.maxCols > me.minCols) {
            cols = Math.min(cols, me.maxCols);
        }

        if (me.gridView.settings.maxElementCols !== null) {
            cols = Math.min(cols, me.gridView.settings.maxElementCols);
        }

        return cols;
    }
});
//{/block}
