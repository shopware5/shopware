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

//{namespace name=backend/emotion/view/detail}
//{block name="backend_emotion_view_components_banner_mapping"}
Ext.define('Shopware.apps.Emotion.view.components.BannerMapping', {
    extend: 'Enlight.app.Window',
    footerButton: false,
    title: '{s name=banner_mapping/window_title}Create banner-mapping{/s}',
    autoShow: true,
    layout: 'border',
    alias: 'widget.emotion-components-banner-mapping',
    width: '80%',
    height: '90%',
    basePath: '',
    resizeCollection: Ext.create('Ext.util.MixedCollection'),

    initComponent: function() {
        var me = this;

        // Build the action toolbar
        me.dockedItems = [ me.createMappingToolbar(), {
            dock: 'bottom',
            xtype: 'toolbar',
            ui: 'shopware-ui',
            items: me.createActionButtons()
        }];
        me.items = [ me.createImage(), me.createMappingGrid() ]
        me.addEvents('saveBannerMapping');
        me.callParent(arguments);

        me.on('afterrender', function() {
            if (me.element) {
                var mapping = me.getMapping(me.element);

                if (mapping) {
                    me.createResizerBasedOnSettings(mapping);
                }
            }
        });
    },

    getMapping: function(element) {
        var mapping = null, data = element.get('data');

        Ext.each(data, function(item) {
            if (item.key === 'bannerMapping') {
                mapping = item;
                return false;
            }
        });
        return mapping;
    },

    createResizerBasedOnSettings: function(mapping) {
        var me = this;
        Ext.each(mapping.value, function(item) {
            me.createMappingResizer(item);
        })
    },

    createImage: function() {
        var me = this,
            media = me.media;

        me.image = Ext.create('Ext.Img', {
            src: me.basePath + (Ext.isObject(media) && !Ext.isString(media) ? media.get('path') : media),
            autoEl: 'div'
        });

        me.imageContainer = Ext.create('Ext.container.Container', {
            style: 'position: relative',
            region: 'center',
            autoScroll: true,
            items: [ me.image ]
        });
        return me.imageContainer;
    },

    createMappingGrid: function() {
        var me = this;

        me.mappingStore = me.createMappingStore();

        me.rowEdit = me.createMappingRowEditor();

        me.mappingGrid = Ext.create('Ext.grid.Panel', {
            title: '{s name=banner_mapping/title_grid}Mapping{/s}',
            selType: 'rowmodel',
            region: 'south',
            height: 200,
            autoScroll: true,
            plugins: [ me.rowEdit ],
            store: me.mappingStore,
            columns: me.createMappingGridColumns(),
            listeners: {
                scope: me,
                edit: function(editor, event) {
                    var record = event.record,
                        newValues = event.newValues,
                        oldValues = event.originalValues;

                    var cmp = me.resizeCollection.getAt(record.get('resizerIndex'));

                    if (newValues.width !== oldValues.width || newValues.height !== oldValues.height) {
                        cmp.setSize(newValues.width, newValues.height);
                    }
                    if (newValues.x !== oldValues.x || newValues.y !== oldValues.y) {
                        cmp.setPosition(newValues.x, newValues.y);
                    }

                    record.set({
                        'link': newValues.link
                    });

                },
                beforeedit: function(editor, e) {
                    var columns = editor.editor.items.items;
                    columns[0].setValue(e.record.get('link'));
                }
            }
        });

        return me.mappingGrid;
    },

    /**
     * @return { Ext.data.Store }
     */
    createMappingStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: [
                'x',
                'y',
                'width',
                'height',
                'link',
                'resizerIndex',
                'linkLocation',
                'title',
                { name: 'as_tooltip', type: 'int' }
            ]
        });
    },

    /**
     * @return { Ext.grid.plugin.RowEditing }
     */
    createMappingRowEditor: function() {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            listeners: {
                scope: me,
                beforeedit: function(editor, eOpts) {
                    if (eOpts.field === 'link') {
                        me.articleSearch.getSearchField().focus(true, true);
                    }
                }
            }
        });
    },

    createMappingGridColumns: function() {
        var me = this;

        me.articleSearch = Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'link',
            articleStore: Ext.create('Shopware.apps.Base.store.Variant'),
            returnValue: 'number',
            hiddenReturnValue: 'number',
            allowBlank: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            },

            /**
             * Event listener method which will be fired if
             * the user types into the search field.
             *
             * Shows the trigger button and starts the search.
             *
             * @event keyup
             * @param { Object } el - Ext.form.field.Trigger which has fired the event
             * @param { Object } event - Ext.EventObject
             * @return void
             */
            onSearchKeyUp: function(el, event) {
                var me = this;

                el.setHideTrigger(el.getValue().length === 0);
                clearTimeout(me.searchTimeout);

                var value = el.getValue();

                // Check if we're dealing with a link
                if (value.substr(0, 1) === '/' || Ext.isArray(value.match(/(http|https|shopware\.php)/))) {
                    me.fireEvent('valueselect', me, value, value, value);
                    return;
                }

                // Check if we've a value and the user did press the ESC key
                if (event.keyCode === Ext.EventObject.ESC || !el.value) {
                    event.preventDefault();
                    el.setValue('');
                    me.dropDownStore.filters.clear();
                    me.getDropDownMenu().hide();
                    return false;
                }

                var dropdown = me.getDropDownMenu(),
                    selModel = dropdown.getSelectionModel(),
                    record = selModel.getLastSelected(),
                    curIndex = me.dropDownStore.indexOf(record),
                    lastIndex = me.dropDownStore.getCount() - 1;

                // Keyboard up pressed
                if (event.keyCode === Ext.EventObject.UP) {
                    if (curIndex === undefined) {
                        selModel.select(0);
                    } else {
                        selModel.select(curIndex === 0 ? lastIndex : (curIndex - 1));
                    }
                }

                // Keyboard down pressed
                else if (event.keyCode === Ext.EventObject.DOWN) {
                    if (curIndex == undefined) {
                        selModel.select(0);
                    } else {
                        selModel.select(curIndex === lastIndex ? 0 : (curIndex + 1));
                    }
                }

                // Keyboard enter pressed
                else if (event.keyCode === Ext.EventObject.ENTER) {
                    event.preventDefault();
                    record && me.onSelectArticle(null, record);
                }

                // No special key was pressed, start searching...
                else {
                    me.searchTimeout = setTimeout(function() {
                        me.dropDownStore.filters.clear();
                        me.dropDownStore.filter('free', '%' + el.value + '%');
                    }, me.searchBuffer);
                }
            },

            listeners: {
                scope: me,
                valueselect: function(value, record) {
                    var columns = me.rowEdit.editor.items.items,
                        updateButton = me.rowEdit.editor.floatingButtons.items.items[0];

                    updateButton.setDisabled(false);
                    columns[0].setValue(record);
                }
            }
        });

        // Combobox which will be used for the link type field
        me.linkComboBox = Ext.create('Ext.form.field.ComboBox', {
            queryMode: 'local',
            name: 'linkLocation',
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'display' ],
                data: [
                    { value: 'interal', display: '{s name=banner_mapping/column/location/interal}Internal link{/s}' },
                    { value: 'external', display: '{s name=banner_mapping/column/location/external}External link{/s}' }
                ]
            }),
            displayField: 'display',
            valueField: 'value'
        });

        me.columns = [{
            dataIndex: 'link',
            header: '{s name=banner_mapping/column/link}Link{/s}',
            flex: 2,
            editor: me.articleSearch
        }, {
            dataIndex: 'linkLocation',
            header: '{s name=banner_mapping/column/link_type}Link type{/s}',
            flex: 1,
            editor: me.linkComboBox,
            renderer: function(value) {

                if (value === 'external') {
                    return '{s name=banner_mapping/column/location/external}External link{/s}';
                }
                return '{s name=banner_mapping/column/location/interal}Internal link{/s}';
            }
        }, {
            dataIndex: 'x',
            header: '{s name=banner_mapping/column/x_position}X-Position{/s}',
            width: 80,
            renderer: me.pixelRenderer,
            editor: {
                xtype: 'numberfield',
                minValue: 0
            }
        }, {
            dataIndex: 'y',
            header: '{s name=banner_mapping/column/y_position}Y-Position{/s}',
            width: 80,
            renderer: me.pixelRenderer,
            editor: {
                xtype: 'numberfield',
                minValue: 0
            }
        }, {
            dataIndex: 'width',
            header: '{s name=banner_mapping/column/width}Width{/s}',
            width: 80,
            renderer: me.pixelRenderer,
            editor: {
                xtype: 'numberfield',
                minValue: 1
            }
        }, {
            dataIndex: 'height',
            header: '{s name=banner_mapping/column/height}Height{/s}',
            width: 80,
            renderer: me.pixelRenderer,
            editor: {
                xtype: 'numberfield',
                minValue: 1
            }
        }, {
            dataIndex: 'title',
            header: '{s name=banner_mapping/column/title}Title{/s}',
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: true
            }
        }, {
            dataIndex: 'as_tooltip',
            align: 'center',
            header: '{s name=banner_mapping/column/as_tooltip}Show title as tooltip{/s}',
            flex: 1,
            renderer: me.checkboxRenderer,
            editor: {
                xtype: 'checkboxfield',
                inputValue: 1,
                uncheckedValue: 0

            }
        }, {
            xtype: 'actioncolumn',
            width: 50,
            header: '{s name=banner_mapping/column/actions}Actions{/s}',
            items: [{
                iconCls: 'sprite-minus-circle',
                tooltip: '{s name=banner_mapping/column/actions_info}Delete mapping{/s}',
                handler: function(grid, rowIndex) {
                    var cmp = me.resizeCollection.getAt(rowIndex);

                    me.mappingStore.removeAt(rowIndex);
                    cmp.resizer.destroy();
                    cmp.destroy();
                }
            }]
        }];

        return me.columns;
    },

    /**
     * Column renderer which appends an `px` to the incoming value.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    pixelRenderer: function(value) {
        // Cast value to a string
        value += '';
        if (!value.length) {
            return '-';
        }
        return Ext.String.format('[0]px', value);
    },

    /**
     * Column renderer which renders an icon which represents the `checked` state
     * based on the incoming value.
     *
     * @param { Number } value - The column content
     * @returns { String } formatted output
     */
    checkboxRenderer: function(value) {
        var cls;
        if (value === 1) {
            cls = 'sprite-tick-small';
        } else {
            cls = 'sprite-cross-small';
        }
        return Ext.String.format('<div class="[0]" style="display: inline-block; width: 16px; height: 16px;"></div>', cls);
    },

    createMappingToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            region: 'north',
            items: [{
                xtype: 'button',
                text: '{s name=banner_mapping/mapping_add}Add a new Mapping{/s}',
                iconCls: 'sprite-plus-circle',
                handler: function() {
                    me.createMappingResizer()
                }
            }]
        });
    },

    createActionButtons: function() {
        var me = this;

        return ['->', {
            xtype: 'button',
            cls: 'secondary',
            text: '{s name=banner_mapping/cancel}Cancel{/s}',
            action: 'emotion-detail-settings-window-cancel',
            handler: function(button) {
                var win = button.up('window');
                win.destroy();
            }
        }, {
            xtype: 'button',
            cls: 'primary',
            text: '{s name=banner_mapping/save}Save{/s}',
            action: 'emotion-detail-settings-window-save',
            handler: function() {
                me.fireEvent('saveBannerMapping', me, me.mappingStore, me.element);
            }
        }];
    },

    createMappingResizer: function (item) {
        var me = this,
            imageEl = me.image.imgEl,
            size = imageEl.getSize(),
            config;

        if (item) {
            config = item;
            config.height = ~~(1 * config.height);
            config.width = ~~(1 * config.width);
            config.x = ~~(1 * config.x);
            config.y = ~~(1 * config.y);
        } else {
            config = {
                height: 100,
                width: 100,
                x: 0,
                y: 0,
                link: ''
            };
        }
        var cmp = me.resizeCollection.add(Ext.create('Ext.Component', {
            floating: true,
            renderTo: me.image.getEl(),
            height: config.height,
            width: config.width,
            constrain: true,
            constrainTo: imageEl,
            resizer: null,
            collectionId: null,
            draggable: {
                delegate: '.inner-component'
            },
            html: '<div class="inner-component" style="background:rgba(255, 255, 255, .5);display:block;width:100%;height:100%"></div>'
        }));
        var id = cmp.collectionId = me.resizeCollection.getCount() - 1;

        cmp.resizer = Ext.create('Ext.resizer.Resizer', {
            el: cmp.getEl(),
            handles: 'all',
            minWidth: 10,
            minHeight: 10,
            maxWidth: size.width,
            maxHeight: size.height,
            pinned: true,
            constrain: true,
            constrainTo: imageEl
        });

        cmp.setPosition(config.x, config.y);

        // Create the record for the `me.mappingStore`
        var record = me.mappingStore.add(me.createMappingRecord(id, config));
        record = record[0];
        Ext.defer(function () {
            cmp.doComponentLayout();
            size = imageEl.getSize();
            cmp.dd.on('dragend', function () {
                var y = cmp.getEl().getTop() - imageEl.getTop(),
                    x = cmp.getEl().getLeft() - imageEl.getLeft();
                record.set({
                    x: x,
                    y: y
                });
            });

            cmp.resizer.on('resize', function (resizer, width, height) {
                var y = cmp.getEl().getTop() - imageEl.getTop(),
                    x = cmp.getEl().getLeft() - imageEl.getLeft();
                record.set({
                    width: width,
                    height: height,
                    x: x,
                    y: y
                });
            });
        }, 1000);
    },

    /**
     * @param { Number } id
     * @param { Object } config
     * @return { Object }
     */
    createMappingRecord: function(id, config) {
        return {
            x: config.x,
            y: config.y,
            height: config.height,
            width: config.width,
            resizerIndex: id,
            link: config.link,
            linkLocation: config.linkLocation || 'internal',
            title: config.title || '',
            as_tooltip: config.as_tooltip || 0
        };
    }
});
//{/block}
