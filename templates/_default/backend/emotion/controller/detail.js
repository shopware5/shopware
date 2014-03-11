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
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Emotion Main Controller
 *
 * This file contains the business logic for the Emotion module.
 */
//{block name="backend/emotion/controller/detail"}
Ext.define('Shopware.apps.Emotion.controller.Detail', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
	extend: 'Ext.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'emotion-detail-window' },
        { ref: 'settingsForm', selector: 'emotion-detail-window emotion-detail-settings' },
        { ref: 'listing', selector: 'emotion-main-window emotion-list-grid' },
        { ref: 'deleteButton', selector: 'emotion-main-window button[action=emotion-list-toolbar-delete]' }
    ],

    snippets: {
        successTitle: '{s name=save/success/title}Successful{/s}',
        errorTitle: '{s name=save/error/title}Error{/s}',
        saveSuccessMessage: '{s name=save/success/message}The emotion [0] has been saved.{/s}',
        saveErrorMessage: '{s name=save/error/message}An error has occurred while saving the emotion:{/s}',
        onSaveChangesNotValid: '{s name=save/error/not_valid}All required fields have not been filled{/s}',
        removeSuccessMessage: '{s name=remove/success/message}Emotion(s) has been removed{/s}',
        removeErrorMessage: '{s name=remove/error/message}An error has occurred while removing the emotion(s):{/s}',
		growlMessage: '{s name=growlMessage}Emotion{/s}',
		confirmMessage: '{s name=confirmMessage}Are you sure you want to delete the selected emotion?{/s}'
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
                saveEmotion: me.onSaveEmotion
            },
            'emotion-detail-settings-window': {
                saveComponent: me.onSaveComponent
            },
            'emotion-detail-designer': {
                'openSettingsWindow': me.onOpenSettingsWindow
            },
            'emotion-main-window button[action=emotion-list-toolbar-add]': {
                'click': me.onOpenDetail
            },
            'emotion-main-window emotion-list-grid': {
                'editemotion': me.onEditEmotion,
                'deleteemotion': me.removeEmotions,
                'selectionChange': me.onSelectionChange,
                'duplicateemotion': me.onDuplicateEmotion
            },
            'emotion-main-window emotion-list-toolbar': {
                searchEmotions: me.onSearch,
                removeEmotions: me.onRemoveEmotions
            },
            'emotion-components-banner': {
                openMappingWindow: me.onOpenBannerMappingWindow
            },
            'emotion-components-banner-mapping': {
                saveBannerMapping: me.onSaveBannerMapping
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
     * @param string value
     * @return void
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
            form = win.down('form'),
            data= [], fieldValue,
            fieldKeys = [],
            fields = form.getForm().getFields();

        if(!form.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage(win.title, '{s name=error/not_all_required_fields_filled}Please fill out all required fields to save the component settings.{/s}');
            return false;
        }

        compFields.each(function(item){
            fieldKeys.push(item.get('name'));
        });

        fields.each(function(field) {
            if (Ext.Array.indexOf(fieldKeys, field.getName()) > -1) {
                data.push(me.getFieldData(field, record));
            }
        });
        record.set('data', data);
        win.destroy();
    },

    getFieldData: function(field, record) {
        if (field.getName() === 'bannerMapping') {
            var recordData = record.get('data'),
                mapping = record.get('mapping');

            if(!mapping) {
                Ext.each(recordData, function(el) {
                    if(el.key === 'bannerMapping') {
                        mapping = el.value;
                        return false;
                    }
                });
            }

            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: mapping
            };
        } else if(field.getName() === 'banner_slider') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if(field.getName() === 'selected_manufacturers') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if(field.getName() === 'selected_articles') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: field.getValue()
            };
        }
    },

    /**
     * Event will be fired when the user want to save the current emotion of the detail window
     * @param record
     * @param dataViewStore
     */
    onSaveEmotion: function(record, dataViewStore) {
        var me = this, form = me.getSettingsForm(), win = me.getDetailWindow();

        form.getForm().updateRecord(record);

        if (!form.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.onSaveChangesNotValid);
            return;
        }

        var elements = dataViewStore.getAt(0).get('elements');
        record.getElements().removeAll();
        record.getElements().add(elements);
        record.save({
            callback: function(item) {
                var rawData = item.proxy.getReader().rawData;
                if (rawData.success === true) {
                    var message = Ext.String.format(me.snippets.saveSuccessMessage, record.get('name')),
                        gridStore = me.getListing().getStore();

                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, message, me.snippets.growlMessage);
                    win.destroy();
                    gridStore.load();
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.saveErrorMessage + '<br>' + rawData.message, me.snippets.growlMessage);
                }
            }
        });
    },

    onEditEmotion: function(scope, view, rowIndex, colIndex) {
        var me = this,
            detailStore = me.getStore('Detail'),
            listStore = scope.getStore();

        detailStore.getProxy().extraParams.id = listStore.getAt(rowIndex).get('id');
        detailStore.load({
            callback: function(records, operation) {
                if (operation.success) {
                    me.openDetailWindow(records[0]);
                }
            }
        });
    },

    onOpenDetail: function() {
        var me = this,
            record;

        record = Ext.create('Shopware.apps.Emotion.model.Emotion', {
            cols: 4,
            rows: 20,
            categoryId: null,
            cellHeight: 185,
            articleHeight: 2,
            containerWidth: 808,
            template: 'Standard'
        });
        me.openDetailWindow(record);
    },

    openDetailWindow: function(record) {
        var me = this,
            libraryStore = me.getStore('Library');

        libraryStore.load({
            callback: function() {
                me.getView('detail.Window').create({
                    emotion: record,
                    libraryStore: libraryStore,
                    categoryPathStore: me.subApplication.categoryPathStore
                });
            }
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
     */
    onOpenSettingsWindow: function(view, record, component, fields, emotion) {
        this.getView('components.SettingsWindow').create({
            settings: {
                record: record,
                component: component,
                fields: fields,
                grid: emotion
            }
        });
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

    onDuplicateEmotion: function(scope, record) {
        var me = this;

        Ext.Ajax.request({
            'url': '{url controller=emotion action=duplicate}',
            'params': {
                emotionId: ~~(1 * record.get('id'))
            },
            callback: function(operation, success, response) {
                var response = Ext.decode(response.responseText);

                if(!response.success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.growlMessage, '{s name=duplicate/error_msg}An error occurs while duplicating the selected emotion.{/s}');
                    return false;
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.growlMessage, '{s name=duplicate/success_msg}The selected emotion was successful duplicated.{/s}');
                }

                me.getStore('Detail').load();
            }
        })
    }
});
//{/block}