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
        { ref: 'designer', selector: 'emotion-detail-window emotion-detail-designer' },
        { ref: 'designerGrid', selector: 'emotion-detail-window emotion-detail-grid' },
        { ref: 'designerPreview', selector: 'emotion-detail-window emotion-detail-preview' },
        { ref: 'listing', selector: 'emotion-main-window emotion-list-grid' },
        { ref: 'deleteButton', selector: 'emotion-main-window button[action=emotion-list-toolbar-delete]' },
        { ref: 'attributeForm', selector: 'emotion-detail-window shopware-attribute-form' },
        { ref: 'listingView', selector: 'presets-list' },
        { ref: 'presetWindow', selector: 'emotion-presets-window' }
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
        emotionNotFoundMsg: '{s name="save/error/emotion_not_found"}{/s}',
        previewErrorMessage: '{s name="preview/error"}{/s}'
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
                'saveEmotion': me.onSaveEmotion,
                'saveAsPreset': me.onSaveAsPreset
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
            'emotion-detail-window emotion-detail-settings': {
                'deviceChange': me.onDeviceChange
            },
            'emotion-detail-window emotion-detail-layout': {
                'changeMode': me.onModeChange,
                'changeColumns': me.onColumnsChange,
                'updateGridByField': me.onUpdateGridByField
            },
            'emotion-main-window button[action=emotion-list-toolbar-add]': {
                'click': me.onOpenDetail
            },
            'emotion-main-window button[action=emotion-list-toolbar-add-preset]': {
                'click': me.onOpenPreset
            },
            'emotion-presets-window, presets-list': {
                'emotionpresetselect': me.onEmotionPresetSelection
            },
            'emotion-presets-window presets-list': {
                'deletepreset': me.onDeletePreset,
                'showpresetdetails': me.onShowPresetDetails
            },
            'emotion-presets-form-window': {
                'savepreset': me.savePreset
            },
            'emotion-main-window emotion-list-grid': {
                'editemotion': me.onEditEmotion,
                'updateemotion': me.onUpdateEmotion,
                'deleteemotion': me.removeEmotions,
                'selectionChange': me.onSelectionChange,
                'duplicateemotion': me.onDuplicateEmotion,
                'preview': me.onPreviewEmotion,
                'export': me.onExportEmotion
            },
            'emotion-main-window emotion-list-toolbar': {
                'searchEmotions': me.onSearch,
                'removeEmotions': me.onRemoveEmotions,
                'uploadEmotion': me.onUploadEmotion
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

    onUploadEmotion: function(grid, uploadfield, newValue) {
        var me = this,
            form = uploadfield.up('form');

        form.submit({
            url: '{url controller=Emotion action=upload}',
            success: function(form, action) {
                if (action.result.filePath) {
                    me.importEmotion(action.result.filePath);
                }
            },
            failure: function(form, action) {
                var msg =  '{s name=emotion/emotion_import_failure_message}{/s}';
                if (action.result.message) {
                    msg = action.result.message;
                }

                return Shopware.Notification.createGrowlMessage(
                    '{s name=emotion/emotion_import_failure}{/s}',
                    msg
                );
            }
        });
    },

    importEmotion: function(path) {
        var me = this;
        me.getMainWindow().setLoading(true);

        Ext.Ajax.request({
            url: '{url controller=Emotion action=import}',
            jsonData: {
                filePath: path
            },
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText);

                if (result.success && result.presetId && result.presetData) {
                    me.onImportSuccess(result, path);
                } else {
                    me.getMainWindow().setLoading(false);

                    Shopware.Notification.createGrowlMessage(
                        '{s name=emotion/emotion_import_failure}{/s}',
                        result.message
                    );

                    me.cleanupImport(null, path);
                }
            }
        });
    },

    /**
     *
     * @param { Object } result
     * @param { string } path
     * @return { Ext.panel.Panel }
     */
    onImportSuccess: function(result, path) {
        var me = this,
            preset = Ext.create('Shopware.apps.Emotion.model.Preset', {
                id: result.presetId,
                presetData: result.presetData,
                emotionTranslations: result.emotionTranslations
            });

        me.importAssets(preset, function(success) {
            me.progressbarWindow.down('progressbar').updateText('{s name=preset/assets_import_success}{/s}');

            if (!success) {
                if (me.progressbarWindow) {
                    me.progressbarWindow.destroy();
                }
                me.cleanupImport(preset.get('id'), path);

                return Shopware.Notification.createGrowlMessage(
                    '{s name=preset/assets_import_failure}{/s}',
                    '{s name=preset/assets_import_failure_message}{/s}'
                );
            }

            me.loadPreset(preset, function(result) {
                if (me.progressbarWindow) {
                    me.progressbarWindow.destroy();
                }
                var emotion;

                if (result.success && result.data) {
                    emotion = me.decodeEmotionPresetData(result.data);

                    emotion.save({
                        callback: function(record, operation) {
                            var store = me.getListing().getStore();

                            me.cleanupImport(preset.get('id'), path);

                            if (operation.success) {
                                if (!Ext.isEmpty(preset.get('emotionTranslations'))) {
                                    me.importEmotionTranslations(record, preset);

                                    return;
                                }
                                store.load();

                                me.loadEmotionRecord(
                                    record.get('id'),
                                    Ext.bind(me.openDetailWindow, me)
                                );
                            } else {
                                Shopware.Notification.createGrowlMessage(
                                    me.snippets.errorTitle,
                                    me.snippets.saveErrorMessage + '<br>' + rawData.message,
                                    me.snippets.growlMessage
                                );
                            }
                        }
                    });
                }
            }, me);
        }, me);
    },

    /**
     * @param { Shopware.apps.Emotion.model.Emotion } record
     * @param { Shopware.apps.Emotion.model.Preset } preset
     */
    importEmotionTranslations: function(record, preset) {
        var me = this,
            store = me.getListing().getStore();

        Ext.Ajax.request({
            url: '{url controller=Emotion action=importTranslations}',
            jsonData: {
                emotionId: record.get('id'),
                emotionTranslations: preset.get('emotionTranslations'),
                autoMapping: true
            },
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result.success && result.mappingRequired) {
                    me.getMainWindow().setLoading(false);
                    Ext.create('Shopware.apps.Emotion.view.translation.Window', {
                        emotionId: record.get('id'),
                        emotionTranslations: preset.get('emotionTranslations'),
                        shops: result.shops,
                        listeners: {
                            close: function() {
                                store.load();

                                me.loadEmotionRecord(
                                    record.get('id'),
                                    Ext.bind(me.openDetailWindow, me)
                                );
                            }
                        }
                    });

                    return;
                }
                store.load();

                me.loadEmotionRecord(
                    record.get('id'),
                    Ext.bind(me.openDetailWindow, me)
                );
            }
        });
    },

    /**
     *
     * @param { int } presetId
     * @param { string } filePath
     */
    cleanupImport: function(presetId, filePath) {
        Ext.Ajax.request({
            url: '{url controller=Emotion action=afterImport}',
            jsonData: {
                presetId: presetId,
                filePath: filePath
            }
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
            formFields = form.getFields(),
            data= [],
            cssField,
            xtype, compField;

        if(!formPanel.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage(
                win.title,
                me.snippets.saveComponentAlert
            );

            return false;
        }

        formFields.each(function(formField) {
            xtype = formField.getXType();
            compField = compFields.getById(formField.fieldId);

            if (compField) {
                if (xtype !== 'radiofield' || (xtype === 'radiofield' && formField.checked)) {
                    data.push(me.getFieldData(formField, compField, record));
                }
            }
        });

        cssField = form.findField('cssClass');

        record.set('cssClass', cssField.getValue() || '');
        record.set('data', data);

        detailWindow.designer.grid.refresh();

        win.destroy();
    },

    getFieldData: function(formField, compField, record) {
        var xtype = formField.getXType(),
            itemData = record.get('data'),
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
            fieldName === 'selected_manufacturers') {

            data['value'] = record.get('mapping');

            if (fieldName === 'bannerMapping' && !data['value']) {
                Ext.each(itemData, function(el) {
                    if (el.key === 'bannerMapping') {
                        data['value'] = el.value;
                        return false;
                    }
                });
            }

        } else if (xtype === 'radiofield') {
            data['value'] = formField.inputValue;
        } else if (xtype === 'timefield') {
            data['value'] = formField.getSubmitValue();
        } else if (xtype === 'datefield') {
            data['value'] = Ext.Date.format(formField.getValue(), 'Y-m-d');
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

    /**
     *
     * @param { Shopware.apps.Emotion.model.Emotion } record
     */
    onSaveAsPreset: function(record) {
        var me = this,
            settings = me.getSettingsForm(),
            sidebar = me.getSidebar(),
            layout = me.getLayoutForm();

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

        Ext.create('Shopware.apps.Emotion.view.presets.Form', {
            emotion: record
        }).show();
    },

    /**
     *
     * @param { Shopware.apps.Emotion.view.presets.Form } win
     */
    savePreset: function(win) {
        var me = this,
            form = win.down('form'),
            record = win.emotion,
            deletionRequired = record.get('id') === 0,
            values;

        if (!form.getForm().isValid()) {
            return Shopware.Notification.createGrowlMessage(
                win.title,
                '{s name=error/not_all_required_fields_filled_preset}{/s}'
            );
        }
        win.setLoading('{s name=preset/saving_as_preset}{/s}');

        values = form.getForm().getValues();
        values.preview = values.thumbnail;
        values.translations = [{
            label: values.name,
            description: values.description
        }];
        delete values.save;

        // save emotion for transformation, will be deleted automatically
        record.save({
            callback: function(records, operation) {
                var result = Ext.JSON.decode(operation.response.responseText);

                if (!result.success) {
                    return Shopware.Notification.createGrowlMessage(
                        me.snippets.errorTitle,
                        me.snippets.saveErrorMessage + '<br>' + result.message,
                        me.snippets.growlMessage
                    );
                } else {
                    values.emotionId = result.data.id;

                    Ext.Ajax.request({
                        url: '{url controller="EmotionPreset" action="save"}',
                        jsonData: values,
                        method: 'POST',
                        callback: function(operation, success, response) {
                            var result = Ext.JSON.decode(response.responseText);
                            win.setLoading(false);
                            if (deletionRequired) {
                                me.deleteDummyEmotion(values.emotionId);
                            }

                            if (!result.success) {
                                return Shopware.Notification.createGrowlMessage(
                                    me.snippets.errorTitle,
                                    me.snippets.saveErrorMessage + '<br>' + result.message,
                                    me.snippets.growlMessage
                                );
                            }
                            win.close();
                            Shopware.Notification.createGrowlMessage(
                                '{s name=preset/save_success}{/s}',
                                '{s name=preset/save_success_msg}{/s}'
                            );
                        }
                    });
                }
            }
        });
    },

    /**
     * Removes dummy emotion after creating preset.
     *
     * @param { int } emotionId
     */
    deleteDummyEmotion: function(emotionId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url action="delete" targetField=emotions}',
            jsonData: {
                id: emotionId
            }
        });
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
            jsonData: {
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
                    message = result.message;
                    if (!message && Ext.isDefined(result.emotion) && result.emotion == false) {
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

    onEmotionPresetSelection: function() {
        var me = this,
            record = Ext.create('Shopware.apps.Emotion.model.Emotion'),
            listingView = me.getListingView(),
            window = me.getPresetWindow(),
            selectedPreset = listingView.selectedPreset;

        if (!selectedPreset) {
            window.close();
            me.getMainWindow().setLoading(true);
            me.openDetailWindow(record);
            return;
        }

        if (!selectedPreset.allowUsage()) {
            // check for required plugins first
            if (!selectedPreset.get('pluginsInstalled')) {
                return me.getRequiredPluginsMessage(selectedPreset.get('requiredPlugins'));
            }
            // check for assset import then
            if (!selectedPreset.get('assetsImported')) {
                me.askAssetImport(selectedPreset);
            }
        } else {
            me.loadPreset(selectedPreset, function(result) {
                if (result.success && result.data) {
                    me.getPresetWindow().close();
                    me.getMainWindow().setLoading(true);
                    me.openDetailWindow(
                        me.decodeEmotionPresetData(result.data)
                    );
                }
            }, me);
        }
    },

    getRequiredPluginsMessage: function(requiredPlugins) {
        var i = 0,
            count = requiredPlugins.length,
            pluginInfo = [];

        for (i; i < count; i++) {
            var plugin = requiredPlugins[i];

            if (!plugin.valid) {
                var info = Ext.String.format('[0] ([1])', plugin.plugin_label || plugin.name, plugin.version);

                pluginInfo.push(info);
            }
        }

        return Ext.Msg.confirm(
           '{s name=preset/required_plugins_title}{/s}',
            Ext.String.format('{s name="preset/required_plugins_confirmation"}{/s}', pluginInfo.join('<br>')),
            function(btn) {
               if (btn === 'yes') {
                   Shopware.app.Application.addSubApplication({
                       name: 'Shopware.apps.PluginManager'
                   });
               }
            }
        );
    },

    loadPreset: function(selectedPreset, callback, scope) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="EmotionPreset" action="loadPreset"}',
            jsonData: {
                id: selectedPreset.get('id')
            },
            callback: function(operation, success, response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result.success) {
                    return Shopware.Notification.createGrowlMessage(
                        me.snippets.errorTitle,
                        me.snippets.saveErrorMessage + '<br>' + result.message,
                        me.snippets.growlMessage
                    );
                }

                Ext.callback(callback, scope, [result]);
            }
        });
    },

    askAssetImport: function(preset) {
        var me = this;

        Ext.Msg.confirm(
            '{s name="preset/assets_import_title"}{/s}',
            Ext.String.format('{s name="preset/assets_import_info"}{/s}'),
            function(btn) {
                if (btn !== 'yes') {
                    return;
                }

                me.getMainWindow().setLoading(true);
                me.importAssets(preset, function(success) {
                    me.getMainWindow().setLoading(false);
                    me.progressbarWindow.down('progressbar').updateText('{s name=preset/assets_import_success}{/s}');
                    if (!success) {
                        if (me.progressbarWindow) {
                            me.progressbarWindow.destroy();
                        }
                        return Shopware.Notification.createGrowlMessage(
                            '{s name=preset/assets_import_failure}{/s}',
                            '{s name=preset/assets_import_failure_message}{/s}'
                        );
                    }

                    me.loadPreset(preset, function(result) {
                        if (me.progressbarWindow) {
                            me.progressbarWindow.destroy();
                        }
                        if (result.success && result.data) {
                            me.getPresetWindow().close();
                            me.getMainWindow().setLoading(true);
                            me.openDetailWindow(
                                me.decodeEmotionPresetData(result.data)
                            );
                        }
                    }, me);
                }, me);
            },
            me
        );
    },

    importAssets: function(preset, callback, scope) {
        var me = this,
            presetId = preset.get('id'),
            presetData = Ext.JSON.decode(preset.get('presetData')),
            elements;

        if (!Ext.isObject(presetData)) {
            return Ext.callback(callback, scope, [preset]);
        }

        elements = presetData['elements'];
        me.createProgressBar();

        me.processAssetImport(presetId, 0, elements, callback, me.assetImportCallback, me);
    },

    processAssetImport: function(presetId, index, elements, outerCallback, importCallback, scope) {
        // ignore elements without assets
        if (Ext.isEmpty(elements)) {
            return Ext.callback(importCallback, scope, [true, outerCallback, presetId, index, elements]);
        }
        var elementSyncKey = elements[index]['syncKey'];

        Ext.Ajax.request({
            url: '{url controller=emotionPreset action=importAsset}',
            methid: 'POST',
            timeout: 4000000,
            params: {
                id: presetId,
                syncKey: elementSyncKey
            },
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=preset/assets_import_element_failure}{/s}',
                        Ext.String.format('{s name=preset/assets_import_element_failure_message}{/s}', elements[index]['componentId'])
                    );
                }

                return Ext.callback(importCallback, scope, [true, outerCallback, presetId, index, elements]);
            },
            failure: function() {
                return Ext.callback(importCallback, scope, [false]);
            }
        });
    },

    assetImportCallback: function(success, callback, presetId, index, elements) {
        var me = this;

        if (!success) {
            return Ext.callback(callback, me, [false]);
        }
        index++;

        if (index < elements.length) {
            me.updateProgressBar(index, elements.length);

            return me.processAssetImport(presetId, index, elements, callback, me.assetImportCallback, me);
        }

        me.updateProgressBar(index, elements.length);

        return Ext.callback(callback, me, [true]);
    },

    updateProgressBar: function(progress, totalCount) {
        var me = this;

        me.progressbarWindow.down('progressbar').updateProgress(progress / totalCount, Ext.String.format('{s name=preset/assets_import_progress}{/s}', progress, totalCount));
    },

    createProgressBar: function() {
        var me = this;

        me.progressbarWindow = Ext.create('Ext.Window', {
            title: '{s name=preset/assets_import_title}{/s}',
            autoShow: true,
            height: 150,
            width: 350,
            bodyPadding: 20,
            items: [{
                xtype: 'progressbar',
                text: '{s name=preset/assets_import_text}{/s}'
            }]
        });
    },

    onOpenDetail: function() {
        var me = this,
            record;

        me.getMainWindow().setLoading(true);

        record = Ext.create('Shopware.apps.Emotion.model.Emotion');

        me.openDetailWindow(record);
    },

    /**
     * @param { Ext.data.Store } store
     * @param { Ext.data.Model } preset
     */
    onDeletePreset: function(store, preset) {
        var me = this;

        Ext.MessageBox.confirm(
            '{s name=preset/delete_preset}{/s}',
            '{s name=preset/delete_preset_confirmation}{/s}',
            function (response) {
                if (response !== 'yes') {
                    return;
                }

                if (!(store instanceof Ext.data.Store)) {
                    return;
                }

                preset.destroy({
                    callback: function(record, operation) {
                        store.load();
                        var result = record.proxy.getReader().rawData,
                            failureMsg = '{s name=preset/delete_failure_msg}{/s}';

                        if (result.message) {
                            failureMsg = Ext.String.format('[0]<br>[1]', failureMsg, result.message);
                        }

                        if (!result.success) {
                            return Shopware.Notification.createGrowlMessage(
                                '{s name=preset/delete_failure}{/s}',
                                failureMsg
                            );
                        }
                        Shopware.Notification.createGrowlMessage(
                            '{s name=preset/delete_success}{/s}',
                            '{s name=preset/delete_success_msg}{/s}'
                        );
                    }
                });
            });
    },

    onShowPresetDetails: function(selectedPreset) {
        var me = this,
            win = me.getPresetWindow();

        if (selectedPreset && Ext.isEmpty(selectedPreset.get('presetData'))) {
            win.setLoading(true);
            Ext.Ajax.request({
                url: '{url controller=emotionPreset action=preview}',
                jsonData: {
                    id: selectedPreset.get('id')
                },
                callback: function(options, success, response) {
                    var result = Ext.JSON.decode(response.responseText);
                    win.setLoading(false);

                    if (result.success) {
                        selectedPreset.beginEdit();
                        selectedPreset.set(result.data);
                        selectedPreset.endEdit(true);
                    }
                    win.infoView.updateInfoView(selectedPreset);
                }
            });
        } else {
            win.infoView.updateInfoView(selectedPreset);
        }
    },

    decodeEmotionPresetData: function(presetData){
        var me = this,
            data,
            store = me.getStore('Detail'),
            resultSet,
            record;

        if (!presetData){
            return Ext.create('Shopware.apps.Emotion.model.Emotion');
        }

        data = Ext.JSON.decode(presetData);

        /** { Ext.data.ResultSet } resultSet */
        resultSet = store.getProxy().getReader().readRecords([data]);
        record = resultSet.records[0];
        // very important for automatic id assignment after saving
        record.phantom = true;

        return record;
    },

    onOpenPreset: function() {
        var me = this;

        me.openPresetsWindow();
    },

    openPresetsWindow: function(options) {
        var me = this;

        me.getView('presets.Window').create(options);
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
            settings = me.getSettingsForm(),
            layout = me.getLayoutForm(),
            gridPanel = me.getDesignerGrid(),
            previewPanel = me.getDesignerPreview(),
            emotionData;

        settings.getForm().updateRecord(emotion);
        layout.getForm().updateRecord(emotion);

        if (!emotion.get('id')) {
            gridPanel.designer.activePreview = false;
            gridPanel.refresh();
            return;
        }

        emotionData = me.convertPreviewData(emotion);

        var previewSaveFailed = function() {
            gridPanel.designer.activePreview = false;
            gridPanel.refresh();
            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.previewErrorMessage);
        };

        Ext.Ajax.request({
            url: '{url module=backend controller=emotion action=savePreview}',
            jsonData: emotionData,
            success: function(response) {
                var json = Ext.decode(response.responseText);

                if (Ext.isEmpty(json) || json.success === false) {
                    previewSaveFailed();
                    return;
                }

                var previewSrc = '{url module=widgets controller=emotion action=preview}/?emotionId=' + json.data.id + '&secret=' + json.data.previewSecret;

                layout.setDisabled(true);
                gridPanel.hide();

                previewPanel.showPreview(viewport, previewSrc)
            },
            failure: previewSaveFailed
        });
    },

    /**
     * Converts the emotion ExtJS record into an useful array structure
     * including all associations and additional data which is used by the preview.
     *
     * @param emotionRecord
     * @returns emotionData []
     */
    convertPreviewData: function(emotionRecord) {
        var me = this,
            layout = me.getLayoutForm(),
            emotionData = emotionRecord.getData(),
            elements = emotionRecord.getElements(),
            template = layout.tplStore.findRecord('id', emotionData['templateId']);

        if (template !== null) {
            emotionData['template'] = template.getData();
        } else {
            emotionData['template'] = {
                'file': 'index.tpl'
            }
        }

        emotionData['elements'] = [];
        elements.each(function(element) {
            var elementData = element.getData(),
                viewports = element.getViewports(),
                component = element.getComponent().getAt(0);

            elementData['viewports'] = [];
            viewports.each(function(viewport) {
                elementData['viewports'].push(viewport.getData());
            });

            if (Ext.isDefined(component)) {
                elementData['component'] = component.getData();
            }

            emotionData['elements'].push(elementData);
        });

        return emotionData;
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

    onExportEmotion: function(emotionId) {
        var me = this;

        Ext.Msg.confirm(
            '{s name="emotion/export_confirm_title"}{/s}',
            '{s name="emotion/export_confirm_msg"}{/s}',
            function(button) {
                if (button === 'yes') {
                    window.open('{url controller="emotion" action="export"}?emotionId=' + emotionId, '_blank');
                }
            }
        );
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

    onDeviceChange: function (record, deviceField, value) {
        var me = this,
            designer = me.getDesigner();

        if (!value.device) {
            return false;
        }

        record.set('device', value.device.join(','));

        designer.toolbar.refresh();
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
