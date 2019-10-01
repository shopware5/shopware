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
//{block name="backend/emotion/view/detail/grid"}
Ext.define('Shopware.apps.Emotion.view.detail.Grid', {

    extend: 'Ext.view.View',

    alias: 'widget.emotion-detail-grid',

    style: 'height: auto !important',

    width: '100%',

    cls: Ext.baseCSSPrefix + 'emotion-grid-container',

    defaultTypeSettings: {
        'standard': {
            sections: null,
            rowButtons: true,
            maxElementRows: null,
            maxElementCols: null,
            resizeCol: true,
            resizeRow: true,
            drag: true,
            drop: true
        },
        'rows': {
            maxElementRows: 1,
            resizeRow: false,
            drag: true,
            drop: true
        }
    },

    /**
     * The current viewport the grid renders.
     */
    state: 'xl',

    /**
     * Connected viewports for which all changes are applied.
     */
    stateConnections: [ 'xl' ],

    snippets: {
        addSection: '{s name="designer/action/add_section"}{/s}',
        deleteSection: '{s name="designer/action/delete_section"}{/s}'
    },

    initComponent: function() {
        this.setWidth(this.basicGridWidth);

        this.settings = this.getSettings();

        this.fixEmotionRows();

        this.store = this.createStore();
        this.tpl = this.createGridTemplate();
        this.itemSelector = '.' + this.itemCls;

        this.registerEvents();

        this.callParent(arguments);
    },

    registerEvents: function() {
        var me = this;

        Ext.EventManager.addListener(Ext.getBody(), 'keydown', me.onKeyDown, me);
        Ext.EventManager.addListener(Ext.getBody(), 'keyup', me.onKeyUp, me);

        me.on({
            'refresh': me.onViewRefresh,
            'afterrender': me.onAfterRender,
            'beforerefresh': me.onBeforeRefresh,
            'destroy': function() {
                Ext.EventManager.removeListener(Ext.getBody(), 'keydown', me.onKeyDown, me);
                Ext.EventManager.removeListener(Ext.getBody(), 'keyup', me.onKeyUp, me);
            }
        });
    },

    createStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store',{
            model: 'Shopware.apps.Emotion.model.Emotion',
            data: [ me.emotion ]
        });
    },

    onViewRefresh: function(view, event) {
        var me = this;

        me.usedCells = {};

        me.createElements();
        me.createDropZones();
        me.refreshToolbar();

        // Scroll to the last position after refresh
        if (me.scrollPosition) {
            me.designer.body.scrollTo('top', me.scrollPosition.top, false);
        }
    },

    onBeforeRefresh: function() {
        var me = this;

        me.settings = me.getSettings();

        // Save the current scroll position before refreshing
        me.scrollPosition = me.designer.body.getScroll();
    },

    onAfterRender: function() {
        var me = this;

        me.registerGridEvents();
    },

    onKeyDown: function(event) {

        if (event.keyCode !== 17) {
            return;
        }

        this.isPressedCtrl = true;
    },

    onKeyUp: function(event) {

        if (event.keyCode !== 17) {
            return;
        }

        this.isPressedCtrl = false;
    },

    registerGridEvents: function() {
        var me = this,
            el = me.getEl();

        el.removeAllListeners();

        el.on({
            'click': {
                scope: me,
                delegate: '.' + Ext.baseCSSPrefix + 'designer-add-row-btn',
                fn: Ext.bind(me.onAddRow, me)
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.' + Ext.baseCSSPrefix + 'designer-remove-row-btn',
                fn: Ext.bind(me.onRemoveRow, me)
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.' + Ext.baseCSSPrefix + 'designer-add-section-btn',
                fn: Ext.bind(me.onAddSection, me)
            }
        });

        el.on({
            'click': {
                scope: me,
                delegate: '.' + Ext.baseCSSPrefix + 'designer-remove-section-btn',
                fn: Ext.bind(me.onRemoveSection, me)
            }
        });
    },

    onAddRow: function(event) {
        var me = this,
            btn = Ext.get(event.target),
            row = ~~me.getElAttr(btn, 'data-addRow');

        me.addRows(row);
    },

    onRemoveRow: function(event) {
        var me = this,
            btn = Ext.get(event.target),
            row = ~~me.getElAttr(btn, 'data-removeRow');

        me.removeRows(row);
    },

    onAddSection: function(event) {
        var me = this,
            btn = Ext.get(event.target),
            section = ~~me.getElAttr(btn, 'data-section'),
            row = section * me.settings.sections + 1,
            endRow = row + me.settings.sections - 1;

        me.addRows(row, endRow);
    },

    onRemoveSection: function (event) {
        var me = this,
            btn = Ext.get(event.target),
            section = ~~me.getElAttr(btn, 'data-section'),
            row = section * me.settings.sections + 1 - me.settings.sections,
            endRow = row + me.settings.sections - 1;

        me.removeRows(row, endRow);
    },

    createGridTemplate: function() {
        var view = this,
            gridCls = Ext.baseCSSPrefix + 'designer-grid',
            layerGridCls = Ext.baseCSSPrefix + 'designer-grid-layer',
            layerElCls = Ext.baseCSSPrefix + 'designer-element-layer';

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="' + gridCls + '" style="{[this.getGridStyles()]}">',

                    // layer for grid drop zones
                    '<div class="' + layerGridCls + '">',
                        '{[this.getGridRows()]}',
                    '</div>',

                    // layer for grid elements
                    '<div class="' + layerElCls + '"></div>',
                '</div>',
            '</tpl>{/literal}',
            {
                parentId: view.getId(),

                getGridStyles: function() {
                    var viewport = view.viewportStore.findRecord('alias', view.state),
                        width = viewport.get('minWidth') || view.basicGridWidth;

                    return 'width: ' + width + 'px';
                },

                getGridRows: function() {
                    var me = this, settings, rows = '', i = 1;

                    /**
                     * Get cloned settings to clear object references and prevent data bubbling in ExtJS.
                     */
                    settings = Ext.clone(view.settings);

                    if (settings.sections !== null && (settings.rows % settings.sections) > 0) {
                        settings.rows += settings.sections - (settings.rows % settings.sections);
                        view.emotion.set('rows', settings.rows);
                    }

                    for (i; i <= settings.rows; i++) {
                        var row = me.getRowMarkup(settings, i);

                        if (settings.sections !== null) {
                            row = me.getSectionMarkup(settings, i, row);
                        }

                        rows += row;
                    }

                    return rows;
                },

                getSectionMarkup: function(settings, rowIndex, row) {
                    var me = this,
                        sectionCls = Ext.baseCSSPrefix + 'emotion-grid-section',
                        sectionIndex = Math.round(rowIndex / settings.sections) + 1;

                    if (rowIndex === 1) {
                        row = Ext.String.format('<div class="[0]" data-section="[1]">[2]', sectionCls, sectionIndex, row);
                    }

                    if (rowIndex % settings.sections === 0) {
                        row += Ext.String.format(
                            '</div>[2]<div class="[0]" data-section="[1]">',
                            sectionCls, sectionIndex, me.getSectionButtonContainer(settings, sectionIndex)
                        );
                    }

                    if (rowIndex === settings.rows) {
                        row += '</div>';
                    }

                    return row;
                },

                getRowMarkup: function(settings, rowIndex) {
                    var me = this,
                        cls = Ext.baseCSSPrefix + 'designer-grid-row',
                        columns = me.getColumns(settings, rowIndex),
                        buttons = me.getRowButtons(settings, rowIndex),
                        style = 'margin-left: ' + -settings.cellSpacing  + 'px;',
                        rowContent = Ext.String.format('<div style="[0]">[1]</div>', style, columns),
                        rowButtons = '';

                    if (settings.rowButtons) {
                        rowButtons += buttons.addBtn;

                        if (settings.rows > 1)  {
                            rowButtons += buttons.removeBtn;
                        }
                    }

                    return Ext.String.format('<div class="[0]" data-row="[1]">[2][3]</div>',
                        cls, rowIndex, rowContent, rowButtons, rowContent
                    );
                },

                getColumns: function(settings, rowIndex) {
                    var me = this, columns = '', i = 1;

                    for (i; i <= settings.cols; i++) {
                        columns += me.getColumnMarkup(settings, rowIndex, i);
                    }

                    return columns;
                },

                getColumnMarkup: function(settings, rowIndex, colIndex) {
                    var width = 100 / settings.cols + '%',
                        height = settings.cellHeight + 'px',
                        colCls = Ext.baseCSSPrefix + 'designer-grid-column',
                        cellCls = Ext.baseCSSPrefix + 'designer-grid-cell',
                        padding = '0 0 ' + settings.cellSpacing + 'px ' + settings.cellSpacing + 'px',
                        colStyle = Ext.String.format('width: [0]; padding: [1];', width, padding),
                        cellStyle = Ext.String.format('height: [0];', height),
                        cell = Ext.String.format(
                            '<div class="[0]" data-row="[1]" data-col="[2]" style="[3]"></div>',
                            cellCls, rowIndex, colIndex, cellStyle
                        );

                    return Ext.String.format('<div class="[0]" style="[1]">[2]</div>', colCls, colStyle, cell);
                },

                getRowButtons: function(settings, rowIndex) {
                    var topPosition = (rowIndex === 1) ? -5 : -(settings.cellSpacing / 2),
                        addBtnStyle = 'top: ' + topPosition  + 'px;',
                        removeBtnStyle = 'top: ' + (settings.cellHeight / 2)  + 'px;',
                        addCls = Ext.baseCSSPrefix + 'designer-add-row-btn',
                        removeCls = Ext.baseCSSPrefix + 'designer-remove-row-btn';

                    return {
                        addBtn: Ext.String.format(
                            '<div class="[0]" data-addRow="[1]" style="[2]">+</div>',
                            addCls, rowIndex, addBtnStyle
                        ),
                        removeBtn: Ext.String.format(
                            '<div class="[0]" data-removeRow="[1]" style="[2]">x</div>',
                            removeCls, rowIndex, removeBtnStyle
                        )
                    }
                },

                getSectionButtonContainer: function(settings, sectionIndex) {
                    var me = this,
                        containerCls = Ext.baseCSSPrefix + 'designer-section-actions';

                    return Ext.String.format('<div class="[0]">[1]</div>', containerCls, me.getSectionButtons(settings, sectionIndex - 1));
                },

                getSectionButtons: function(settings, sectionIndex) {
                    var addSectionBtnCls = Ext.baseCSSPrefix + 'designer-add-section-btn',
                        removeSectionBtnCls = Ext.baseCSSPrefix + 'designer-remove-section-btn',
                        sections = Math.round(settings.rows / settings.sections),
                        addBtn = Ext.String.format(
                            '<button class="[0]" data-section="[2]">[1]</button>',
                            addSectionBtnCls, view.snippets.addSection, (sectionIndex)
                        ),
                        removeBtn = Ext.String.format(
                            '<button class="[0]" data-section="[2]">[1]</button>',
                            removeSectionBtnCls, view.snippets.deleteSection, (sectionIndex)
                        );

                    return (sections > 1) ? addBtn + removeBtn : addBtn;
                }
            }
        );
    },

    createElements: function() {
        var me = this,
            elements = me.emotion.getElements(),
            elementLayer = me.getEl().down('.x-designer-element-layer');

        if (me.elements) {
            for (var key in me.elements) {
                if (me.elements[key].gridResizer) {
                    me.elements[key].gridResizer.destroy();
                }
                me.elements[key].getEl().removeAllListeners();
            }
        }

        me.elements = {};

        // Clear all hidden Elements which are outside the data view
        me.hiddenElements.getEl().setHTML('');

        elements.each(function(elementRecord) {
            var element = me.createElementFromRecord(elementRecord);

            if (Ext.isDefined(element)) {
                me.elements[elementRecord.internalId] = element;

                if (element.getVisible(me.state)) {
                    element.render(elementLayer);
                    me.setUsedCells(element);
                } else {
                    element.render(me.hiddenElements.getEl());
                }
            }
        });

        if (me.hiddenElements.getEl().getHTML().length <= 0) {
            me.hiddenElements.hide();
            me.designer.activeHiddenElements = false;
        }
    },

    createElementFromRecord: function(elementRecord) {
        var me = this,
            component = elementRecord.getComponent().first(),
            xType = component.get('xType');

        if (!Ext.isDefined(component)) {
            return false;
        }

        return me.getGridComponentByType(xType, elementRecord);
    },

    createDropZones: function() {
        var me = this,
            dropZonEl = me.getEl().down('.x-designer-grid');

        if (!me.settings.drop) {
            return false;
        }

        if (me.dropZone) {
            me.dropZone.destroy();
        }

        me.dropZone = Ext.create('Ext.dd.DropZone', dropZonEl, {

            ddGroup: 'emotion-dd',

            getTargetFromEvent: function(e) {
                return e.getTarget('.x-designer-grid-cell');
            },

            onNodeOver: function(targetEl, dragEl, event, dragData) {
                var dragRecord = dragData.draggedRecord,
                    gridElement = me.elements[dragRecord.internalId],
                    targetCell = Ext.get(targetEl),
                    cellRow = ~~me.getElAttr(targetCell, 'data-row'),
                    cellCol = ~~me.getElAttr(targetCell, 'data-col');

                if (!Ext.isDefined(gridElement)) {
                    gridElement = me.createElementFromRecord(dragRecord);
                }

                var compEndRow = cellRow + (gridElement.getRows() - 1),
                    compEndCol = cellCol + (gridElement.getCols() - 1),
                    isValidDrop = me.validateDrop(dragRecord, cellRow, cellCol, compEndRow, compEndCol);

                me.createPreviewElement(targetEl, gridElement.getRows(), gridElement.getCols(), isValidDrop);

                if (!isValidDrop) {
                    me.previewElement.addCls('is--invalid');
                } else {
                    me.previewElement.removeCls('is--invalid');
                }

                return (isValidDrop) ? Ext.dd.DropZone.prototype.dropAllowed : false;
            },

            onNodeDrop: function(targetEl, dragEl, event, dragData) {
                var elements = me.emotion.getElements(),
                    dragRecord = dragData.draggedRecord,
                    gridElement = me.elements[dragRecord.internalId],
                    targetCell = Ext.get(targetEl),
                    cellRow = ~~me.getElAttr(targetCell, 'data-row'),
                    cellCol = ~~me.getElAttr(targetCell, 'data-col');

                if (!Ext.isDefined(gridElement)) {
                    gridElement = me.createElementFromRecord(dragRecord);
                    me.designer.counterChange = Ext.Array.difference([ 'xs', 's', 'm', 'l', 'xl' ], me.stateConnections);
                }

                gridElement.removeCls('is--dragging');

                var compEndRow = cellRow + (gridElement.getRows() - 1),
                    compEndCol = cellCol + (gridElement.getCols() - 1),
                    isValidDrop = me.validateDrop(dragRecord, cellRow, cellCol, compEndRow, compEndCol);

                if (me.previewElement) {
                    me.previewElement.remove();
                }

                if (!isValidDrop) {
                    return false;
                }

                gridElement.setGridSettings({
                    startRow: cellRow,
                    startCol: cellCol,
                    endRow: compEndRow,
                    endCol: compEndCol,
                    visible: true
                }, me.stateConnections);

                if (elements.getById(gridElement.record.get('id')) === null) {
                    elements.add(gridElement.record);
                }

                me.refresh();
            }
        });
    },

    validateDrop: function(dragRecord, startRow, startCol, endRow, endCol) {
        var me = this,
            drop = true, r, c;

        // Some of the cells are outside the grid
        if (startRow < 1 ||
            startCol < 1 ||
            endRow > me.emotion.get('rows') ||
            endCol > me.emotion.get('cols')) {
            return false;
        }

        // Prevent elements from overlapping sections
        if (me.settings.sections !== null) {
            var startSection = Math.ceil(startRow / me.settings.sections),
                endSection = Math.ceil(endRow / me.settings.sections);

            if (startSection !== endSection) {
                return false;
            }
        }

        // Check if some cells of the element are already in use by other elements
        for (r = startRow; r <= endRow; r++) {
            for (c = startCol; c <= endCol; c++) {
                if (Ext.isDefined(me.usedCells[r + '-' + c]) &&
                    me.usedCells[r + '-' + c].internalId !== dragRecord.internalId) {

                    drop = false;
                }
            }
        }

        return drop;
    },

    createPreviewElement: function(targetEl, rows, cols, isValidDrop) {
        var me = this,
            dropZonEl = me.getEl().down('.x-designer-grid'),
            cellSize = me.getCurrentCellSize(),
            width = me.getWidthFromColumns(cols, cellSize),
            height = me.getHeightFromRows(rows, cellSize),
            offset = Ext.get(targetEl).getOffsetsTo(dropZonEl);

        if (me.previewElement) {
            me.previewElement.remove();
        }

        me.previewElement = document.createElement('div');
        me.previewElement = Ext.get(me.previewElement);
        me.previewElement.addCls('x-designer-preview-el');

        if (!isValidDrop) {
            me.previewElement.addCls('drop-invalid');
        }

        me.previewElement.setStyle({
            width: width + 'px',
            height: height + 'px',
            left: offset[0] + 'px',
            top: offset[1] + 'px'
        });

        me.previewElement.appendTo(dropZonEl);
    },

    addRows: function(startIndex, endIndex) {
        var me = this,
            start = startIndex || 1,
            end = endIndex || start,
            rows = end - start + 1,
            startRow, endRow, gridSettings;

        me.emotion.set('rows', me.emotion.get('rows') + rows);

        Ext.iterate(me.elements, function(elementId, element) {

            gridSettings = element.getGridSettings(me.state);
            startRow = gridSettings.startRow;
            endRow = gridSettings.endRow;

            if (start > startRow) {
                return;
            }

            element.setGridSettings({
                'startRow': startRow + rows,
                'endRow': endRow + rows
            }, me.stateConnections);
        });

        me.refresh();
    },

    removeRows: function(startIndex, endIndex) {
        var me = this,
            start = startIndex || 1,
            end = endIndex || start,
            rows = end - start + 1,
            affectedElementRecords = [],
            movedElements = [],
            startRow, endRow, gridSettings;

        Ext.iterate(me.elements, function(elementId, element) {

            gridSettings = element.getGridSettings(me.state);
            startRow = gridSettings.startRow;
            endRow = gridSettings.endRow;

            if ((startRow >= start && startRow <= end) ||
                (endRow >= start && endRow <= end)) {
                affectedElementRecords.push(element);
                return;
            }

            if (startRow > end) {
                movedElements.push(element);
            }
        });

        me.emotion.set('rows', me.emotion.get('rows') - rows);

        Ext.each(affectedElementRecords, function(element) {
            element.setGridSettings({
                startRow: 1,
                startCol: 1,
                endRow: 1,
                endCol: 1,
                visible: false
            }, me.stateConnections);
        });

        Ext.each(movedElements, function(element) {
            var gridSettings = element.getGridSettings(me.state);

            element.setGridSettings({
                'startRow': gridSettings.startRow - rows,
                'endRow': gridSettings.endRow - rows
            }, me.stateConnections);
        });

        me.refresh();
    },

    deleteElement: function(elementRecord) {
        var me = this,
            elements = me.emotion.getElements();

        elements.remove(elementRecord);

        me.refresh();
    },

    openSettingsWindow: function(elementRecord) {
        var me = this,
            component = elementRecord.getComponent().first(),
            fields = component.getFields();

        me.fireEvent('openSettingsWindow', me, elementRecord, component, fields, me.emotion);
    },

    setUsedCells: function(element) {
        var me = this, r, c,
            gridSettings = element.getGridSettings(me.state),
            startCol = gridSettings.startCol,
            startRow = gridSettings.startRow,
            endCol =  gridSettings.endCol,
            endRow =  gridSettings.endRow;

        for (r = startRow; r <= endRow; r++) {
            for (c = startCol; c <= endCol; c++) {
                me.usedCells[r + '-' + c] = element.record;
            }
        }
    },

    refreshToolbar: function() {
        var me = this;

        me.viewportStore.suspendEvents();

        // Reset the current counters.
        me.viewportStore.each(function(viewport) {
            viewport.set('hiddenCounter', 0);
        });

        // Iterate through all elements and get the visibility settings.
        Ext.iterate(me.elements, function(internalId, element) {
            me.viewportStore.each(function(viewport) {
                if (!element.getVisible(viewport.get('alias'))) {
                    viewport.set('hiddenCounter', viewport.get('hiddenCounter') + 1)
                }
            });
        });

        me.viewportStore.resumeEvents();

        me.toolbar.refresh();
        me.designer.counterChange = [];
    },

    /**
     * Fixes the row setting of the emotion world if the rows don't match the elements.
     */
    fixEmotionRows: function() {
        var me = this,
            viewports, endRow,
            rows = me.emotion.get('rows'),
            elements = me.emotion.getElements();

        elements.each(function(elementRecord) {
            viewports = elementRecord.getViewports();

            viewports.each(function(viewport) {
                endRow = viewport.get('endRow');

                if (endRow > rows) {
                    rows = endRow;
                }
            });
        });

        if (me.settings.sections !== null && (rows % me.settings.sections) > 0) {
            rows += me.settings.sections - (rows % me.settings.sections);
        }

        me.emotion.set('rows', rows);
        me.settings['rows'] = rows;
    },

    /**
     *  Copy all grid element settings from one viewport to another.
     *
     * @param toAlias { string }
     * @param fromAlias { string }
     * @returns { boolean }
     */
    copyViewportElements: function(toAlias, fromAlias) {
        var me = this,
            fromState = fromAlias || me.state || null,
            toState = toAlias || null;

        if (fromState === null || toState === null) {
            return false;
        }

        Ext.iterate(me.elements, function(internalId, element) {
            element.copyViewportSettings(toState, fromState);
        });
    },

    /**
     * Checks if the given viewport has visible elements.
     *
     * @param viewportAlias { string }
     * @returns { boolean }
     */
    checkEmptyViewport: function(viewportAlias) {
        var me = this,
            isEmpty = true;

        Ext.iterate(me.elements, function(internalId, element) {
            if (element.getVisible(viewportAlias)) {
                isEmpty = false;
                return false;
            }
        });

        return isEmpty;
    },

    /**
     * Compares the element settings of two viewports.
     * Returns true when the settings are identical.
     *
     * @param viewportAliasCheck { string }
     * @param viewportAlias { string }
     * @returns { boolean }
     */
    checkSameViewportSettings: function(viewportAliasCheck, viewportAlias) {
        var me = this,
            state = viewportAlias || me.state || null,
            checkState = viewportAliasCheck || null,
            isSame = true;

        if (state === null || checkState === null) {
            return false;
        }

        Ext.iterate(me.elements, function(internalId, element) {
            if (!element.hasSameViewportSettings(checkState, state)) {
                isSame = false;
                return false;
            }
        });

        return isSame;
    },

    /**
     * This method generates the complete grid settings by merging
     * the settings from the emotion data, the type settings and the sate settings.
     *
     * The type settings are merged at last, because have the highest priority.
     * The order is: emotion settings | state
     *
     * @param state
     * @param type
     * @returns { * }
     */
    getSettings: function(state, type) {
        var me = this,
            settings = me.emotion.getData() || {},
            typeSettings;

        /**
         * Delete unnecessary object trees.
         */
        delete settings['categories'];
        delete settings['shops'];
        delete settings['template'];

        typeSettings = me.getTypeSettings(type);

        settings = Ext.merge(settings, typeSettings);

        return settings;
    },

    /**
     * Get the designer settings for the current emotion type.
     * Used internal by the getSettings() method to merge the type settings with the global settings.
     *
     * can also be called separately to get just the current type settings.
     *
     * @param type
     * @returns { * }
     */
    getTypeSettings: function(type) {
        var me = this,
            typeSettings = Ext.clone(me.defaultTypeSettings['standard']);

        /**
         * The type can be passed directly to the method.
         * If no specific type is passed to the method it will fall back to
         * the emotion type field or the type which got bound the grid.
         *
         * @type string
         * @default 'standard'
         */
        type = type || me.emotion.get('mode') || me.type || 'standard';

        /**
         * Get the default settings for the type.
         */
        if (Ext.isDefined(me.defaultTypeSettings[type])) {
            typeSettings = Ext.merge(typeSettings, me.defaultTypeSettings[type]);
        }

        /**
         * If there are custom type settings bound to the grid
         * they get merged with the default type settings.
         */
        if (me['typeSettings']) {
            return Ext.merge(typeSettings, me['typeSettings'][type] || {});
        }

        return typeSettings;
    },

    getGridComponentByType: function(xType, elementRecord) {
        var me = this, element,
            elementClass = Ext.ClassManager.getNameByAlias('widget.detail-element-' + xType);

        if (elementClass.length > 0) {
            element = Ext.create(elementClass, {
                record: elementRecord,
                gridView: me
            });
        } else {
            element = Ext.create('Shopware.apps.Emotion.view.detail.elements.Base', {
                record: elementRecord,
                gridView: me
            });
        }

        return element;
    },

    getWidthFromColumns: function(columns, cellSize) {
        var me = this,
            cell = cellSize || me.getCurrentCellSize();

        return cell.width * columns + (columns - 1) * me.settings.cellSpacing;
    },

    getHeightFromRows: function(rows, cellSize) {
        var me = this,
            cell = cellSize || me.getCurrentCellSize();

        return cell.height * rows + (rows - 1) * me.settings.cellSpacing;
    },

    getCellPosition: function(row, col) {
        var me = this,
            view = me.getEl(),
            gridEl = view.down('.x-designer-grid'),
            cell = view.down('[data-row="' + row + '"][data-col="' + col + '"]');

        return Ext.get(cell).getOffsetsTo(gridEl);
    },

    getCurrentCellSize: function() {
        var me = this,
            cell = me.getEl().down('.x-designer-grid-cell');

        return {
            width: cell.getWidth(),
            height: cell.getHeight()
        }
    },

    /**
     * Wrapper method for IE getAttribute fix.
     *
     * @param el
     * @param attrName
     * @returns { string|* }
     */
    getElAttr: function(el, attrName) {
        var i, attribute, attr = el.getAttribute(attrName);

        if (Ext.isDefined(attr)) {
            return attr;
        }

        for (i in el.dom.attributes) {
            attribute = el.dom.attributes[i];
            if (attribute.name === attrName) {
                attr = attr.value;
                break;
            }
        }

        return attr;
    }
});
//{/block}
