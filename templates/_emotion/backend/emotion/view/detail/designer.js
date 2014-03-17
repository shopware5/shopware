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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/detail/designer"}
Ext.define('Shopware.apps.Emotion.view.detail.Designer', {
	extend: 'Ext.panel.Panel',
    title: '{s name=title/designer_tab}Designer{/s}',
    alias: 'widget.emotion-detail-designer',
    layout: 'column',
    autoScroll: true,
    bodyPadding: 20,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.dataView = me.createGridView();

        me.addEvents('openSettingsWindow');

        me.items = [ me.dataView ];
        me.callParent(arguments);
    },

    createGridView: function() {
        var me = this, dataview;

        me.dataViewTemplate = me.createGridTemplate(me.getId());

        dataview = Ext.create('Ext.view.View', {
            store: me.dataviewStore,
            columnWidth: 1,
            cls: 'x-emotion-grid-outer-container',
            tpl: me.dataViewTemplate,
            style: 'position: absolute; top: 15px; left: 15px; overflow-y: hidden; margin: 0 0 30px;',
            listeners: {
                scope: me,
                afterrender: me.addGridEvents,
                refresh: me.createDragZoneForEachElement
            }
        });

        return dataview;
    },

    createGridTemplate: function(id) {

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="x-emotion-grid-inner-container listing-{settings.cols}col">',

                    // Underlying gridsystem - e.g. first layer
                    '<div class="x-emotion-grid-first-layer"{[this.getGridWidth(values.settings)]}>',
                        '{[this.createRows(values.settings)]}',
                    '</div>',

                    // Actual layer which contains the elements
                    '<div class="x-emotion-grid-second-layer" style="height:{[this.getGridHeight(values.settings)]}px">',
                        '{[this.createGridElements(values)]}',
                    '</div>',
                '</div>',
            '</tpl>{/literal}',
            {
                /**
                 * Property which holds the id of the parent element to
                 * fly through the DOM to get the base width of a single column.
                 * @integer
                 */
                parentId: id,

                /**
                 * Helper method which returns the height of the complete grid.
                 *
                 * @private
                 * @param [object] settings - Grid Settings
                 * @return [integer] total height of the grid (in pixels)
                 */
                getGridHeight: function(settings) {
                    var horizontal = (settings.cols > settings.rows),
                        height = settings.rows * 45;

                    // ...we need 30px more due to the scrollbar at the bottom of the grid
                    if(horizontal) {
                        height += 30;
                    }

                    return height;
                },

                getGridWidth: function(settings) {
                    var cols = settings.cols,
                        rows = settings.rows,
                        horizontal = (cols > rows),
                        width;

                    if(!horizontal) {
                        return '';
                    }


                    width = 100 * cols;
                    width += 1; // ...just for the right border
                    return ' style="width: ' + width + 'px"';
                },

                /**
                 * Helper method which creates the rows in the grid.
                 *
                 * @private
                 * @param [object] settings - Grid Settings
                 * @return [string] HTML string of the generated rows
                 */
                createRows: function(settings) {
                    var me = this, rows = '';

                    for(var i = 1; i <= settings.rows; i++) {
                        if(i === settings.rows) {
                            rows += '<div class="row row-last">' + me.createColumns(settings.cols, settings.rows) + '</div>';
                        } else {
                            rows += '<div class="row">' + me.createColumns(settings.cols, settings.rows) + '</div>';
                        }
                    }
                    return rows;
                },

                /**
                 * Helper method which creates the columns in the grid.
                 *
                 * @private
                 * @param settings
                 * @return [string] HTML string of the generated columns
                 */
                createColumns: function(cols, rows) {
                    var columns = '',
                        horizontal = (cols > rows),
                        width;

                    for(var i = 1; i <= cols; i++) {
                        width = (100 / cols);
                        if(horizontal) {
                            width = '100px';
                        } else {
                            width = width + '%';
                        }

                        if(i === cols) {
                            columns += '<div class="col col-1x1 col-last" style="width:' + width+ '"></div>';
                        } else {
                            columns += '<div class="col col-1x1" style="width:' + width + '"></div>';
                        }
                    }
                    columns += '<div class="x-clear"></div>';

                    return columns;
                },

                createGridElements: function(values) {
                    var me = this, elements = '',
                        els = values.elements,
                        baseElement = Ext.get(this.parentId),
                        baseWidth = (baseElement.getWidth() - 40) / values.settings.cols,
                        horizontal = values.settings.cols > values.settings.rows,
                        dh = new Ext.dom.Helper, specs;

                    if(horizontal) {
                        baseWidth = 100;
                    }

                    Ext.each(els, function(element) {
                        var width = (element.get('endCol') - element.get('startCol')) + 1,
                            rowHeight = (element.get('endRow') - element.get('startRow')) + 1,
                            children = [],  baseCls = 'col-' + width + 'x' + rowHeight, height,
                            component = element.getComponent().first(), componentId = element.data.componentId,
                            elementWidth;

                        height = rowHeight * 45 + 'px';

                        switch(componentId) {

                            // Banner element
                            case 3:
                                children = me.getBannerMarkup(element, component);
                                break;

                            // Article element
                            case 4:
                                children = me.getArticleMarkup(element, component, rowHeight);
                                break;

                            default:
                                children = me.getDefaultMarkup(element, component);
                                break;
                        }

                        elementWidth = (100 / values.settings.cols) * width + '%';
                        if(horizontal) {
                            elementWidth = 100 * width + 'px';
                        }
                        specs = {
                            cls: baseCls + ' x-emotion-element ' + (component.get('cls').length ? ' ' + component.get('cls') : ''),
                            tag: 'div',
                            'data-emotionid': element.internalId,
                            style: {
                                top: (element.get('startRow') -1) * 45 + 'px',
                                left: (element.get('startCol') -1) * baseWidth + 'px',
                                height: height,
                                'line-height': height,
                                width: elementWidth
                            },
                            children: children
                        };
                        elements += dh.createHtml(specs);
                    });

                    return elements;
                },

                /**
                 * Creates the children array for the DOM-Helper, which creates the
                 * banner emotion element.
                 *
                 * @param { Shopware.apps.Emotion.model.Element } element
                 * @param { Shopware.apps.Emotion.model.Component } component
                 * @returns { Array }
                 */
                getBannerMarkup: function(element, component) {
                    var me = this, file, i = 0;

                    // Article element was not configured yet
                    if(!element.data.data.length) {
                        return me.getDefaultMarkup(element, component);
                    }

                    for(; i < element.data.data.length; i++) {
                        var banner = element.data.data[i];
                        if(banner.key === 'file') {
                            file = banner;
                            break;
                        }
                    }

                    // If no banner was found
                    if(!file.hasOwnProperty('value')) {
                        file.value = '';
                    }

                    return [
                        { tag: 'div', cls: 'x-emotion-banner-preview', children: [
                            { tag: 'img', cls: 'x-emotion-banner-image', src: '{link file="" fullPath}' + file.value  },
                            { tag: 'div', cls: 'x-emotion-banner-preview-inner' }
                        ] },
                        { tag: 'div', cls: 'x-emotion-element-handle' },
                        { tag: 'div', cls: 'x-emotion-element-inner', html: component.get('fieldLabel') },
                        { tag: 'div', cls: 'x-emotion-element-pencil', 'data-emotionid': element.internalId },
                        { tag: 'div', cls: 'x-emotion-element-delete', 'data-emotionid': element.internalId }
                    ];
                },

                /**
                 * Creates the children array for the DOM-Helper, which creates the
                 * article emotion element.
                 *
                 * @param { Shopware.apps.Emotion.model.Element } element
                 * @param { Shopware.apps.Emotion.model.Component } component
                 * @param { Number } rowHeight
                 * @returns { Array }
                 */
                getArticleMarkup: function(element, component, rowHeight) {
                    var me = this, type, i = 0, snippet, article, object,
                        types = {
                            newcomer: '{s name=element/types/newcomer}Newcomer article{/s}',
                            topseller: '{s name=element/types/topseller}Topseller article{/s}',
                            random_article: '{s name=element/types/random_article}Random article{/s}',
                            selected_article: '{s name=element/types/selected_article}Selected article{/s}'
                        };

                    // Article element was not configured yet
                    if(!element.data.data.length) {
                        return me.getDefaultMarkup(element, component);
                    }

                    for(; i < element.data.data.length; i++) {
                        object = element.data.data[i];
                        if(object.key === 'article_type') {
                            type = object;
                            break;
                        }
                    }

                    // If no article was found
                    if(!type.hasOwnProperty('value')) {
                        type.value = '';
                    }

                    // Get the snippet
                    snippet = (type.value.length) ? types[type.value] : '';

                    // If we're dealing with a selected product, terminate the ordernumber
                    if(type.value === 'selected_article') {
                        object = '';
                        i = 0;

                        for(; i < element.data.data.length; i++) {
                            object = element.data.data[i];
                            if(object.key === 'article') {
                                article = object;
                                break;
                            }
                        }

                        if(!article.hasOwnProperty('value')) {
                            article.value = '';
                        }

                        // Modify the snippet
                        snippet = Ext.String.format('[0]: [1]', snippet, article.value);
                    }

                    return (rowHeight < 2) ? me.getDefaultMarkup(element, component) : [
                        { tag: 'div', cls: 'x-emotion-element-handle' },
                        { tag: 'div', cls: 'x-emotion-element-inner', html: component.get('fieldLabel') },
                        { tag: 'div', cls: 'x-emotion-element-info', html: snippet },
                        { tag: 'div', cls: 'x-emotion-element-pencil', 'data-emotionid': element.internalId },
                        { tag: 'div', cls: 'x-emotion-element-delete', 'data-emotionid': element.internalId }
                    ];
                },

                /**
                 * Creates the children array for the DOM-Helper, which creates the
                 * article emotion element.
                 *
                 * @param { Shopware.apps.Emotion.model.Element } element
                 * @param { Shopware.apps.Emotion.model.Component } component
                 * @returns { Array }
                 */
                getDefaultMarkup: function(element, component) {
                    return [
                        { tag: 'div', cls: 'x-emotion-element-handle' },
                        { tag: 'div', cls: 'x-emotion-element-inner', html: component.get('fieldLabel') },
                        { tag: 'div', cls: 'x-emotion-element-pencil', 'data-emotionid': element.internalId },
                        { tag: 'div', cls: 'x-emotion-element-delete', 'data-emotionid': element.internalId }
                    ];
                }
            }
        );
    },

    /**
     * Adds additional events to the dataview
     *
     * @event afterrender
     * @private
     * @param [object] view - Ext.view.View
     * @return voud
     */
    addGridEvents: function() {
        var me = this

        /**
         * Patching the height of the emotion outer container
         * which fix the emotion designer d'n'd functionality temporarily.
         */
        Ext.defer(function() {
            var height = me.dataViewTemplate.getGridHeight(me.dataviewStore.getAt(0).data.settings);
            me.dataView.getEl().setHeight(height);
        }, 200, me);

        me.dataView.getEl().on({
            'click': {
                delegate: '.x-emotion-element-delete',
                fn: me.onDeleteElement,
                scope: me
            },
            'dblclick': {
                delegate: '.x-emotion-element',
                fn: me.onOpenSettingsWindow,
                scope: me
            }
        });

        me.dataView.getEl().on({
            click: {
                delegate: '.x-emotion-element-pencil',
                fn: function(event, el) {
                    var element = Ext.get(el).up('.x-emotion-element').dom;
                    me.onOpenSettingsWindow(event, element);
                },
                scope: me
            }
        });

        me.createDropZone(me);
    },

    /**
     * Event listener method which deletes an element from the grid.
     *
     * @event click
     * @param [object] event - Ext.EventObjImpl
     * @param [object] el - DOM object of the clicked element
     */
    onDeleteElement: function(event, el) {
        var me = this,
            element = Ext.get(el),
            id =  element.getAttribute('data-emotionid'),
            store = me.dataviewStore.getAt(0).get('elements'),
            i, attr;

        if(!id) {
            for(i in element.dom.attributes) {
                attr = element.dom.attributes[i];
                if(attr.name == 'data-emotionid') {
                    id = attr.value;
                    break;
                }
            }
        }

        Ext.each(store, function(record) {
            if(record.internalId == id) {
                Ext.Array.remove(store, record);
                return false;
            }
        });
        element.parent().destroy();
    },

    createDropZone: function(view) {
        var me = this;

        var proxyElement;
        me.dropZone = new Ext.dd.DropZone(view.dataView.getEl(), {
            ddGroup: 'emotion-dd',

            getTargetFromEvent: function(e) {
                return e.getTarget(view.itemSelector);
            },

            // While over a target node, return the default drop allowed class which
            // places a "tick" icon into the drag proxy.
            onNodeOver:function (target, dd, e, data) {
                var stage = view.dataView.getEl(),
                    x = e.getX(),
                    y = e.getY(),
                    id = me.getId(),
                    colHeight = 44,
                    colWidth = (Ext.get(id).getWidth() - 40) / me.dataviewStore.getAt(0).data.settings.cols,
                    startCol, startRow, record = data.draggedRecord,
                    entry = me.dataviewStore.getAt(0), elements = entry.get('elements'),
                    horizontal = (me.dataviewStore.getAt(0).data.settings.cols > me.dataviewStore.getAt(0).data.settings.rows);

                if(horizontal) {
                    colWidth = 100;
                }

                x = x - stage.getX();
                y = y - stage.getY();

                // The element isn't in the drop area, so return "false"
                if (y < 0 || x < 0) {
                    Ext.get(target).addCls('x-emotion-collision');
                    return Ext.dd.DropZone.prototype.dropNotAllowed;
                }

                // Get the start and end points
                startRow = Math.floor(y / colHeight) + 1;
                startCol = Math.floor(x / colWidth) + 1;

                // Create preview element
                if(record.get('startRow') && record.get('endRow')
                   && record.get('startCol') && record.get('endCol')) {

                    // Create the preview element for existing elements on the stage
                    var colSpan = (record.get('endCol') - record.get('startCol')),
                        rowSpan = (record.get('endRow') - record.get('startRow')),
                        width = colSpan + 1,
                        height = rowSpan + 1;

                    this.createPreviewElement(Math.floor(width * colWidth), Math.floor(height * colHeight), startCol - 1, startRow - 1, colWidth);
                } else {

                    // Create the preview element for newly added elements
                    var endRow = startRow,
                        endCol = startCol,
                        rowSpan = 1,
                        colSpan = 1,
                        width = colSpan * colWidth,
                        height = Math.floor(rowSpan * colHeight);

                    // Special behavior the article element
                    if(record.get('xType') == 'emotion-components-article' ||
                       record.get('xType') == 'emotion-components-article-slider') {
                        rowSpan = entry.data.settings.articleHeight;
                        height = Math.floor(rowSpan * colHeight);
                    }
                    this.createPreviewElement(width, height, startCol - 1, startRow - 1, colWidth, entry);
                }
            },

            /**
             * Helper method which creates an proxy preview element to give the
             * user a visually response for it's drag action.
             *
             * @public
             * @param [integer] width - Width of the element
             * @param [integer] height - Height of the element
             * @param [integer] left - Left offset of the element
             * @param [integer] top - Top offset of the element
             * @param [integer] colWidth - calculated column width
             */
            createPreviewElement: function(width, height, left, top, colWidth, entry) {
                var firstLayer = view.dataView.getEl().down('.x-emotion-grid-first-layer');

                if(proxyElement) {
                    proxyElement.remove();
                }

                proxyElement = document.createElement('div');
                proxyElement = Ext.get(proxyElement);
                proxyElement.addCls(Ext.baseCSSPrefix + 'shopware-proxy-state-element');
                proxyElement.setStyle({
                   width: width + 'px',
                   height: height + 'px',
                   left: Math.floor(left * colWidth) + 'px',
                   top: Math.floor(top * 45) + 'px'
                });

                proxyElement.appendTo(firstLayer);
            },

            // On node drop we can interrogate the target to find the underlying
            // application object that is the real target of the dragged data.
            onNodeDrop : function(target, dd, e, data) {
                var stage = view.dataView.getEl(),
                   x = e.getX(),
                   y = e.getY(),
                   id = me.getId(),
                   colHeight = 44,
                   colWidth = (Ext.get(id).getWidth() - 40) / me.dataviewStore.getAt(0).data.settings.cols,
                   startCol, startRow, record = data.draggedRecord, endRow, endCol,
                   entry = me.dataviewStore.getAt(0), elements = entry.get('elements'),
                   horizontal = (me.dataviewStore.getAt(0).data.settings.cols > me.dataviewStore.getAt(0).data.settings.rows);

                if(horizontal) {
                    colWidth = 100;
                }

                x = x - stage.getX();
                y = y - stage.getY();

                // The element isn't in the drop area, so return "false"
                if(y < 0 || x < 0) {
                    return false;
                }

                // Get the start and end points
                startRow = Math.floor(y / colHeight) + 1;
                startCol = Math.floor(x / colWidth) + 1;
                endRow = startRow;
                endCol = startCol;

                /**
                 * The record comes from the element librarys
                 * Set startRow, endRow, startCol and endCol for collision-detection
                 */
                if(record.$className !== 'Shopware.apps.Emotion.model.EmotionElement') {
                    var elEndRow = startRow;

                    if (record.get('xType') == 'emotion-components-article' ||
                        record.get('xType') == 'emotion-components-article-slider') {
                        elEndRow = startRow + (entry.data.settings.articleHeight - 1);

                    }
                    record.set({
                        startRow: startRow,
                        endRow: elEndRow,
                        startCol: startCol,
                        endCol: startCol
                    });
                }

                if(!this.isDroppableElement(record, startRow, startCol, true)) {
                    return false;
                }

                if(proxyElement) {
                    proxyElement.remove();
                    proxyElement = null;
                }

                /**
                 * The record comes from the element librarys
                 */
                if(record.$className !== 'Shopware.apps.Emotion.model.EmotionElement') {
                    // Create new record in the the dataview store

                    var elEndRow = startRow;

                    if(record.get('xType') == 'emotion-components-article' ||
                       record.get('xType') == 'emotion-components-article-slider') {
                        elEndRow = startRow + (entry.data.settings.articleHeight - 1);
                    }

                    var model = Ext.create('Shopware.apps.Emotion.model.EmotionElement', {
                        componentId: record.get('id'),
                        id: '',
                        data: {},
                        name: record.get('name'),
                        fieldLabel: record.get('fieldLabel'),
                        startRow: startRow,
                        endRow: elEndRow,
                        startCol: startCol,
                        endCol: startCol
                    });
                    model.getComponent().add(record);
                    elements.push(model);

                /**
                 * The record is an element on the stage and just need to get new row and col properties
                 */
                } else {
                    var cols = (record.data.endCol - record.data.startCol),
                        rows = (record.data.endRow - record.data.startRow);

                    record.set({
                        startRow: startRow,
                        endRow: startRow + rows,
                        startCol: startCol,
                        endCol: startCol + cols
                    });
                }

                me.dataView.refresh();

                // Remove class from the sourceEl element
                Ext.get(data.sourceEl).removeCls('dragged');

                return true;
            },

            isDroppableElement: function(record, startRow, startCol, returnBoolean) {
                var rowHeight = (record.get('endRow') - record.get('startRow')),
                    colWidth = (record.get('endCol') - record.get('startCol')),
                    endRow = startRow + rowHeight,
                    endCol =  startCol + colWidth,
                    result = true;

                for(var r = startRow; endRow >= r; r++) {
                    for(var c = startCol; endCol >= c; c++) {
                        if(!this.isCellAvailable(r, c, record.internalId)) {
                            result = false;
                        }
                    }
                }

                if(result) {
                    return (returnBoolean) ? result : Ext.dd.DropZone.prototype.dropAllowed;
                }
                return (returnBoolean) ? result : Ext.dd.DropZone.prototype.dropNotAllowed;
            },

            isCellAvailable: function(row, col, id) {
                var entry = me.dataviewStore.getAt(0), elements = entry.get('elements'),
                    maxCols = me.dataviewStore.getAt(0).data.settings.cols,
                    maxRows = me.dataviewStore.getAt(0).data.settings.rows;

                if(row > maxRows || col > maxCols) {
                    return false;
                }

                var error = false;
                Ext.each(elements, function(item) {
                    if(item.internalId == id) {
                        return true;
                    }

                    if(row >= item.get('startRow') && row <= item.get('endRow')) {
                        if(col >= item.get('startCol') && col <= item.get('endCol')) {
                            error = true;
                        }
                    }

                    if(col >= item.get('startCol') && col <= item.get('endCol')) {
                        if(row >= item.get('startRow') && row <= item.get('endRow')) {
                            error = true;
                        }
                    }

                    if(error) { return !error; }
                });
                return !error;
            }
        });
    },

    createDragZoneForEachElement: function() {
        var me = this,
            view = me.dataView.getEl(),
            elements = view.query('.x-emotion-element'),
            id = me.getId(),
            dataViewData = me.dataviewStore.getAt(0).data.settings,
            cellHeight = 45,
            cellWidth = (Ext.get(id).getWidth() - 40) / dataViewData.cols,
            horizontal = (me.dataviewStore.getAt(0).data.settings.cols > me.dataviewStore.getAt(0).data.settings.rows);

        if(horizontal) {
            cellWidth = 100;
        }

        Ext.each(elements, function(item) {

            var element = Ext.get(item);

            // The element has already a drag zone
            if(element.hasCls('x-draggable')) {
                return false;
            }

            Ext.create('Ext.resizer.Resizer', {
                el: element,
                handles: 's e se',
                minHeight: cellHeight,
                minWidth: cellWidth,
                maxHeight: cellHeight * dataViewData.rows,
                maxWidth: cellWidth * dataViewData.cols,
                heightIncrement: cellHeight,
                widthIncrement: cellWidth,
                target: element,
                listeners: {
                    scope: me,
                    resizedrag: me.onResizeDrag,
                    resize: me.onResize,
                    beforeresize: me.onBeforeResize

                }
            });
            element.dragZone = new Ext.dd.DragZone(element, {
                ddGroup: 'emotion-dd',

                /**
                 * Checks if the element is not in the resize mode. If it is in it, the
                 * dd functionality will be disabled.
                 *
                 * @param [object] data - drag and drop data from getDragData
                 * @return [boolean]
                 */
                onBeforeDrag: function(data) {
                    return !Ext.get(data.sourceEl).hasCls('x-resizable-over');
                },

                getDragData: function() {
                    var sourceEl = item,
                        id = element.getAttribute('data-emotionid'),
                        records = me.dataviewStore.getAt(0).get('elements'),
                        d, record, proxy, i, attr;

                    proxy = element.dragZone.proxy;
                    if(!proxy.getEl().hasCls(Ext.baseCSSPrefix + 'shopware-dd-proxy')) {
                        proxy.getEl().addCls(Ext.baseCSSPrefix + 'shopware-dd-proxy')
                    }

                    if(!id) {
                        for(i in element.dom.attributes) {
                            attr = element.dom.attributes[i];
                            if(attr.name == 'data-emotionid') {
                                id = attr.value;
                                break;
                            }
                        }
                    }

                    Ext.each(records, function(item) {
                        if(item.internalId == id) {
                            record = item;
                            return false;
                        }
                    });

                    if (sourceEl) {
                        d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        return {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: me.dataviewStore,
                            draggedRecord: record
                        }
                    }
                },

                getRepairXY: function() {
                    return this.dragData.repairXY;
                }
            });

            // Add class which indicates if the element has already a drag zone
            element.addCls('x-draggable');
        });
    },

    onOpenSettingsWindow: function(event, el) {
        var me = this,
            element = Ext.get(el),
            id = element.getAttribute('data-emotionid'),
            store = me.dataviewStore.getAt(0).get('elements'),
            record, attr, i, component, fields;


        if(!id) {
            for(i in element.dom.attributes) {
                attr = element.dom.attributes[i];
                if(attr.name == 'data-emotionid') {
                    id = attr.value;
                    break;
                }
            }
        }

        Ext.each(store, function(item) {
            if(item.internalId == id) {
                record = item;
                return false;
            }
        });

        component = record.getComponent().first(),
        fields = component.getFields();

        me.fireEvent('openSettingsWindow', me, record, component, fields, me.emotion);
    },

    onBeforeResize: function(resizer, width, height, event) {
        var element = resizer.el,
            me = this,
            id = element.getAttribute('data-emotionid'),
            store = me.dataviewStore.getAt(0).get('elements'),
            record, attr, i;


        if(!id) {
            for(i in element.dom.attributes) {
                attr = element.dom.attributes[i];
                if(attr.name == 'data-emotionid') {
                    id = attr.value;
                    break;
                }
            }
        }

        Ext.each(store, function(item) {
           if(item.internalId == id) {
               record = item;
           }
        });

        record.data.initialStartCol = record.get('startCol');
        record.data.initialEndCol = record.get('endCol');
        record.data.initialStartRow = record.get('startRow');
        record.data.initialEndRow = record.get('endRow');
        record.data.isDroppable = false;
        record.data.needsReset = true;

        return true;
    },

    onResizeDrag: function(resizer, width, height, event) {
        var element = resizer.el,
            me = this,
            id = element.getAttribute('data-emotionid'),
            store = me.dataviewStore.getAt(0).get('elements'),
            dataViewData = me.dataviewStore.getAt(0).data.settings,
            cellHeight = 45,
            cellWidth = (Ext.get(me.getId()).getWidth() - 40) / dataViewData.cols,
            colSpan = width / cellWidth,
            rowSpan = height / cellHeight,
            baseCls, record, i, attr;


        if(!id) {
            for(i in element.dom.attributes) {
                attr = element.dom.attributes[i];
                if(attr.name == 'data-emotionid') {
                    id = attr.value, 10;
                    break;
                }
            }
        }

        Ext.each(store, function(item) {
            if(item.internalId == id) {
                record = item;
            }
        });

        colSpan = Math.round(colSpan);
        rowSpan = Math.round(rowSpan);

        var component = record.getComponent().first();
        if(component.get('xType') == 'emotion-components-article') {
            if(rowSpan == 1) {
                rowSpan = rowSpan + 1;
            }
        }
        record.set({
            endCol: colSpan + record.get('startCol') - 1,
            endRow: rowSpan + record.get('startRow') - 1
        });

        var component = record.getComponent().first();
        baseCls = 'col-' + colSpan + 'x' + rowSpan;

        element.set({
            'cls': baseCls + ' x-emotion-element ' + (component.get('cls').length ? ' ' + component.get('cls') : ''),
            'style': {
                'line-height': height + 'px'
            }
        });

        record.data.isDroppable = me.dropZone.isDroppableElement(record, record.get('startRow'), record.get('startCol'), true);
    },

    /**
     * Saves the new size of the element on the stage
     *
     * @public
     * @param resizer
     * @param width
     * @param height
     */
    onResize: function(resizer, width, height) {
        var element = resizer.el,
            me = this,
            view = me.dataView.getEl(),
            id = element.getAttribute('data-emotionid'),
            store = me.dataviewStore.getAt(0).get('elements'),
            dataViewData = me.dataviewStore.getAt(0).data.settings,
            cellHeight = 45,
            cellWidth = (Ext.get(me.getId()).getWidth() - 40) / dataViewData.cols,
            colSpan = width / cellWidth,
            rowSpan = height / cellHeight,
            record, baseCls, i, attr;

        if(!id) {
            for(i in element.dom.attributes) {
                attr = element.dom.attributes[i];
                if(attr.name == 'data-emotionid') {
                    id = attr.value;
                    break;
                }
            }
        }

        Ext.each(store, function(item) {
            if(item.internalId == id) {
                record = item;
            }
        });

        var component = record.getComponent().first();
        if(component.get('xType') == 'emotion-components-article') {
            if(rowSpan == 1) {
                rowSpan = rowSpan + 1;
            }

            record.set({
                endRow: rowSpan + record.get('startRow') - 1
            });
            resizer.target.setSize((record.get('endCol') - record.get('startCol') + 1) * cellWidth, rowSpan * cellHeight);
            element.set({
                style: {
                    'line-height': rowSpan * cellHeight + 'px'
                }
            })
        }

        if(!record.data.isDroppable && record.data.needsReset) {
            record.set({
                endRow: record.data.initialEndRow,
                endCol: record.data.initialEndCol
            });
            record.data.needsReset = false;
            resizer.resizeTo((record.get('endCol') - record.get('startCol') + 1) * cellWidth, (record.get('endRow') - record.get('startRow') + 1) * cellHeight);
        }
    }
});
//{/block}
