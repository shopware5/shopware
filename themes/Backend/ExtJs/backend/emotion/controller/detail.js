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
//{block name="backend/emotion/controller/detail"}
Ext.define('Shopware.apps.Emotion.controller.Detail', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'mainWindow', selector: 'emotion-main-window' },
        { ref: 'detailWindow', selector: 'emotion-detail-window' },
        { ref: 'sidebar', selector: 'emotion-detail-window tabpanel[name=sidebar]' },
        { ref: 'settingsForm', selector: 'emotion-detail-window emotion-detail-settings' },
        { ref: 'layoutForm', selector: 'emotion-detail-window emotion-detail-layout' },
        { ref: 'designerGrid', selector: 'emotion-detail-window emotion-detail-grid' },
        { ref: 'designerPreview', selector: 'emotion-detail-window emotion-detail-preview' },
        { ref: 'listing', selector: 'emotion-main-window emotion-list-grid' },
        { ref: 'deleteButton', selector: 'emotion-main-window button[action=emotion-list-toolbar-delete]' },
        { ref: 'attributeForm', selector: 'emotion-detail-window shopware-attribute-form' }
    ],

    snippets: {
        successTitle: '{s name=save/success/title}{/s}',
        errorTitle: '{s name=save/error/title}{/s}',
        warningTitle: '{s name=save/warning/title}{/s}',
        saveWarningMessage: '{s name=save/warning/message}{/s}',
        saveSuccessMessage: '{s name=save/success/message}{/s}',
        saveErrorMessage: '{s name=save/error/message}{/s}',
        onSaveChangesNotValid: '{s name=save/error/not_valid}{/s}',
        removeSuccessMessage: '{s name=remove/success/message}{/s}',
        removeErrorMessage: '{s name=remove/error/message}{/s}',
        growlMessage: '{s name=growlMessage}{/s}',
        confirmMessage: '{s name=confirmMessage}{/s}',
        saveComponentAlert: '{s name=error/not_all_required_fields_filled}{/s}',
        duplicateErrorMsg: '{s name=duplicate/error_msg}{/s}',
        duplicateSuccessMsg: '{s name=duplicate/success_msg}{/s}',
        removeColTitle: '{s name="settings/grid/removeColTitel"}{/s}',
        removeColMsg: '{s name="settings/grid/removeColMsg"}{/s}',
        emotionNotFoundMsg: '{s name="save/error/emotion_not_found"}{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'emotion-detail-window': {
                'saveEmotion': me.onSaveEmotion
            },
            'emotion-detail-settings-window': {
                'saveComponent': me.onSaveComponent
            },
            'emotion-detail-designer': {
                'preview': me.onPreview,
                'closePreview': me.closePreview
            },
            'emotion-detail-grid': {
                'openSettingsWindow': me.onOpenSettingsWindow
            },
            'emotion-detail-window emotion-detail-layout': {
                'changeMode': me.onModeChange,
                'changeColumns': me.onColumnsChange,
                'updateGridByField': me.onUpdateGridByField
            },
            'emotion-main-window button[action=emotion-list-toolbar-add]': {
                'click': me.onOpenDetail
            },
            'emotion-main-window emotion-list-grid': {
                'editemotion': me.onEditEmotion,
                'updateemotion': me.onUpdateEmotion,
                'deleteemotion': me.removeEmotions,
                'selectionChange': me.onSelectionChange,
                'duplicateemotion': me.onDuplicateEmotion,
                'preview': me.onPreviewEmotion
            },
            'emotion-main-window emotion-list-toolbar': {
                'searchEmotions': me.onSearch,
                'removeEmotions': me.onRemoveEmotions
            },
            'emotion-components-banner': {
                'openMappingWindow': me.onOpenBannerMappingWindow
            },
            'emotion-components-banner-mapping': {
                'saveBannerMapping': me.onSaveBannerMapping
            }
        });
    },

    /**
     * Event listener function which fired when the user change the listing selection over the checkbox selection
     * model.
     *
     * @param selection
     */
    onSelectionChange: function(selection) {
        var me = this,
            btn = me.getDeleteButton();

        if (btn) {
            btn.setDisabled(selection.length === 0);
        }
    },

    /**
     * Event listener function which fired when the user clicks the "remove all selected" button.
     */
    onRemoveEmotions: function() {
        var me = this,
            grid = me.getListing(),
            selected = grid.getSelectionModel().selected;

        me.removeEmotions(selected.items);
    },

    removeEmotions: function(emotions) {
        var me = this,
            grid = me.getListing(),
            store = grid.getStore();

        Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.confirmMessage , function (response) {
            if ( response !== 'yes' ) {
                return;
            }

            if (!(store instanceof Ext.data.Store)) {
                return;
            }
            store.remove(emotions);
            store.sync({
                callback: function(batch) {
                    var rawData = batch.proxy.getReader().rawData;
                    if (rawData.success === true) {
                        Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.removeSuccessMessage, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.removeErrorMessage + '<br>' + rawData.message, me.snippets.growlMessage);
                    }
                }
            });
        });
    },

    /**
     * Event listener function which fired when the user insert a value into the search field.
     * @return void
     * @param value
     */
    onSearch: function(value) {
        var me = this,
            searchString = Ext.String.trim(value),
            grid = me.getListing(),
            store = grid.getStore();

        //scroll the store to first page
        store.currentPage = 1;

        //If the search-value is empty, reset the filter
        if ( searchString.length === 0 ) {
            store.clearFilter();
        } else {
            //This won't reload the store
            store.filters.clear();
            //Loads the store with a special filter
            store.filter('filter', searchString);
        }

        return true;
    },

    onSaveComponent: function(win, record, compFields) {
        var me = this,
            detailWindow = me.getDetailWindow(),
            formPanel = win.down('form'),
            form = formPanel.getForm(),
            data= [],
            cssField;

        if(!formPanel.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage(
                win.title,
                me.snippets.saveComponentAlert
            );

            return false;
        }

        compFields.each(function(compField) {
            var formField = form.findField(compField.get('name'));

            if (formField !== null) {
                data.push(me.getFieldData(formField, compField, record));
            }
        });

        cssField = form.findField('cssClass');

        record.set('cssClass', cssField.getValue() || '');
        record.set('data', data);

        detailWindow.designer.grid.refresh();

        win.destroy();
    },

    getFieldData: function(formField, compField, record) {
        var itemData = record.get('data'),
            fieldName = formField.getName(),
            data = {
                id: null,
                fieldId: compField.get('id'),
                type: compField.get('valueType'),
                key: fieldName
            };

        Ext.each(itemData, function(item) {
            if (item.key === fieldName) {
                data['id'] = item['id'];
                return false;
            }
        });

        if (fieldName === 'banner_slider' ||
            fieldName === 'bannerMapping' ||
            fieldName === 'selected_manufacturers' ||
            fieldName === 'selected_articles') {

            data['value'] = record.get('mapping');

            if (fieldName === 'bannerMapping' && !data['value']) {
                Ext.each(itemData, function(el) {
                    if (el.key === 'bannerMapping') {
                        data['value'] = el.value;
                        return false;
                    }
                });
            }

        } else {
            data['value'] = formField.getValue()
        }

        return data;
    },

    /**
     * Event will be fired when the user want to save the current emotion of the detail window
     * @param record
     * @param preview
     */
    onSaveEmotion: function(record, preview) {
        var me = this,
            settings = me.getSettingsForm(),
            attributeForm = me.getAttributeForm(),
            sidebar = me.getSidebar(),
            layout = me.getLayoutForm(),
            win = me.getDetailWindow(),
            activeTab = win.sidebar.items.indexOf(win.sidebar.getActiveTab());

        if (Ext.isObject(preview)) {
            preview = false;
        }

        if (activeTab === 3) {
            sidebar.setActiveTab(0);
            activeTab = 0;
        }

        settings.getForm().updateRecord(record);
        layout.getForm().updateRecord(record);

        if (!settings.getForm().isValid()) {
            sidebar.setActiveTab(0);
            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.onSaveChangesNotValid);
            return false;
        }

        if (!layout.getForm().isValid()) {
            sidebar.setActiveTab(1);
            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.onSaveChangesNotValid);
            return false;
        }

        record.save({
            callback: function(item) {
                var rawData = item.proxy.getReader().rawData;

                if (rawData.success === true) {
                    var message = Ext.String.format(me.snippets.saveSuccessMessage, record.get('name')),
                        listing = me.getListing(),
                        gridStore = listing.getStore();

                    if (rawData.alreadyExists) {
                        Shopware.Notification.createGrowlMessage(
                            me.snippets.warningTitle,
                            me.snippets.saveWarningMessage,
                            me.snippets.growlMessage
                        );
                    }

                    if (preview) {
                        return;
                    }

                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, message, me.snippets.growlMessage);

                    win.showPreview = false;

                    attributeForm.saveAttribute(record.get('id'), function() {
                        me.loadEmotionRecord(record.get('id'), function(newRecord) {
                            win.loadEmotion(newRecord, activeTab);
                        });
                    });

                    gridStore.load();
                } else {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.errorTitle,
                        me.snippets.saveErrorMessage + '<br>' + rawData.message,
                        me.snippets.growlMessage
                    );
                }
            }
        });

        return true;
    },

    onEditEmotion: function(scope, view, rowIndex, colIndex) {
        var me = this, listStore = scope.getStore();

        me.getMainWindow().setLoading(true);

        me.loadEmotionRecord(
            listStore.getAt(rowIndex).get('id'),
            Ext.bind(me.openDetailWindow, me)
        );
    },

    onUpdateEmotion: function(editor, context) {
        var me = this, message,
            record = context.record;

        Ext.Ajax.request({
            url: '{url controller="Emotion" action="updateStatusAndPosition"}',
            params: {
                id: record.get('id'),
                active: record.get('active'),
                position: record.get('position')
            },
            callback: function(operation, success, response) {
                var result = Ext.JSON.decode(response.responseText);
                if (success && result.success) {
                    message = Ext.String.format(me.snippets.saveSuccessMessage, record.get('name'));
                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, message, me.snippets.growlMessage);

                    me.getListing().getStore().load();
                } else {
                    message = '';
                    if (Ext.isDefined(result.emotion) && result.emotion == false) {
                        message = me.snippets.emotionNotFoundMsg
                    }
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.errorTitle,
                        me.snippets.saveErrorMessage + '<br>' + message,
                        me.snippets.growlMessage
                    );
                }
            }
        });
    },

    loadEmotionRecord: function(emotionId, callback) {
        var me = this,
            detailStore = me.getStore('Detail');

        detailStore.getProxy().extraParams.id = emotionId;

        detailStore.load({
            callback: function(records, operation) {
                if (operation.success) {
                    Ext.callback(callback, me, [ records[0] ]);
                }
            }
        });
    },

    onOpenDetail: function() {
        var me = this,
            record;

        me.getMainWindow().setLoading(true);

        record = Ext.create('Shopware.apps.Emotion.model.Emotion');

        me.openDetailWindow(record);
    },

    openDetailWindow: function(record, options) {
        var me = this,
            libraryStore = me.getStore('Library'),
            categoryStore = Ext.create('Shopware.apps.Emotion.store.CategoryPath'),
            shopStore = Ext.create('Shopware.apps.Base.store.Shop', {
                autoLoad: false,
                filters: []
            });

        options = options || {};
        options['emotion'] = record;

        categoryStore.getProxy().extraParams.parents = true;

        var loadCounter = 0,
            stores = [
                { name: 'libraryStore', store: libraryStore },
                { name: 'categoryStore', store: categoryStore },
                { name: 'shopStore', store: shopStore }
            ];

        Ext.each(stores, function(item) {

            item.store.load({
                scope: me,
                callback: function() {
                    loadCounter++;

                    options[item.name] = item.store;

                    if (loadCounter === stores.length) {
                        me.getMainWindow().setLoading(false);
                        me.getView('detail.Window').create(options);
                    }
                }
            });
        });
    },

    /**
     * Event listener method which opens the settings window
     * for the clicked item.
     *
     * @param view
     * @param record
     * @param component
     * @param fields
     * @param emotion
     */
    onOpenSettingsWindow: function(view, record, component, fields, emotion) {
        this.getView('components.SettingsWindow').create({
            settings: {
                record: record,
                component: component,
                fields: fields,
                grid: emotion,
                gridSettings: emotion.getData()
            }
        });
    },

    onPreviewEmotion: function(emotionId) {
        var me = this;

        me.getMainWindow().setLoading(true);

        me.loadEmotionRecord(
            emotionId,
            Ext.bind(me.openDetailWindow, me, {
                'showPreview': true
            }, true)
        );
    },

    onPreview: function(view, viewport, emotion) {
        var me = this,
            layoutForm = me.getLayoutForm(),
            gridPanel = me.getDesignerGrid(),
            previewPanel = me.getDesignerPreview();

        if (!me.onSaveEmotion(emotion, true)) {
            gridPanel.designer.activePreview = false;
            gridPanel.refresh();
            return false;
        }

        layoutForm.setDisabled(true);

        previewPanel.showPreview(viewport);
        gridPanel.hide();
    },

    closePreview: function() {
        var me = this,
            layoutForm = me.getLayoutForm(),
            gridPanel = me.getDesignerGrid(),
            previewPanel = me.getDesignerPreview();

        layoutForm.setDisabled(false);

        previewPanel.hidePreview();
        gridPanel.show();
    },

    onModeChange: function(record, mode) {
        var me = this,
            elements = record.getElements(),
            grid = me.getDesignerGrid(),
            viewports;

        mode = mode || 'fluid';

        if (mode === 'rows') {
            // Check if some elements are higher than one row.
            elements.each(function(element) {
                viewports = element.getViewports();

                viewports.each(function(viewport) {
                    if (viewport.get('endRow') > viewport.get('startRow')) {
                        viewport.set('visible', false);
                    }
                });
            });
        }

        record.set('mode', mode);
        grid.refresh();
    },

    onColumnsChange: function(record, value, columnField) {
        var me = this,
            currentValue = record.get('cols'),
            elements = record.getElements(),
            affectedElements = [],
            viewports;

        if (value < currentValue) {

            elements.each(function(element) {
                viewports = element.getViewports();

                viewports.each(function(viewport) {
                    if (viewport.get('endCol') > value && viewport.get('visible')) {
                        affectedElements.push(element);
                        return false;
                    }
                });
            });

            if (affectedElements.length > 0) {
                Ext.MessageBox.confirm(
                    me.snippets.removeColTitle,
                    me.snippets.removeColMsg,
                    function(response) {
                        if (response !== 'yes') {
                            columnField.setValue(currentValue);
                            return false;
                        }

                        Ext.each(affectedElements, function(element) {
                            viewports = element.getViewports();

                            viewports.each(function(viewport) {
                                if (viewport.get('startCol') > value) {
                                    viewport.set('visible', false);
                                }

                                if (viewport.get('endCol') > value && viewport.get('visible')) {
                                    viewport.set('endCol', value);
                                }
                            });
                        });

                        me.setColumns(record, value);
                    }
                );

                return;

            } else {
                me.setColumns(record, value);
            }
        }

        me.setColumns(record, value);
    },

    setColumns: function(record, cols) {
        var me = this,
            grid = me.getDesignerGrid();

        record.set('cols', cols);
        grid.refresh();
    },

    onUpdateGridByField: function(record, value, field) {
        var me = this,
            grid = me.getDesignerGrid();

        record.set(field.name, value);
        grid.refresh();
    },

    onOpenBannerMappingWindow: function(view, media, preview, element) {
        this.getView('components.BannerMapping').create({
            media: media,
            preview: preview,
            element: element
        });
    },

    onSaveBannerMapping: function(view, store, element) {
        var mapping = [];

        store.each(function(item) {
            mapping.push(item.data);
        });

        element.set('mapping', mapping);
        view.destroy();

    },

    onDuplicateEmotion: function(grid, record, device) {
        var me = this;

        Ext.Ajax.request({
            'url': '{url controller=emotion action=duplicate}',
            'params': {
                emotionId: ~~(1 * record.get('id')),
                forDevice: device
            },
            callback: function(operation, success, response) {
                var response = Ext.decode(response.responseText);

                if(!response.success) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.growlMessage,
                        me.snippets.duplicateErrorMsg
                    );

                    return false;
                } else {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.growlMessage,
                        me.snippets.duplicateSuccessMsg
                    );
                }

                me.getListing().getStore().load();
            }
        })
    }
});
//{/block}
