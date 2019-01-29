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
 * @package    Translation
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/translation/view/main}

/**
 * Shopware UI - Translation Manager Main Controller
 *
 * This file contains the business logic for the Translation Manager module.
 */

//{block name="backend/translation/controller/main"}
Ext.define('Shopware.apps.Translation.controller.Main',
/** @lends Ext.app.Controller# */
{
    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Array of views to require from AppName.view namespace.
     * @array
     */
    views: [ 'main.Window', 'main.Navigation', 'main.Form', 'main.Toolbar', 'main.Services' ],

    /**
     * Array of models to require from AppName.model namespace.
     * @array
     */
    models: [ 'Language' ],

    /**
     * Array of stores to require from AppName.store namespace.
     * @array
     */
    stores: [ 'Language' ],

    /**
     * Property which holds the main window due to the fact that the SubApplication needs the
     * "main application window" to destroy the sub application propertly.
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Property which holds the active language model for later usage.
     *
     * @default null
     * @object
     */
    activeLanguage: null,

    /**
     * References to ExtJS 4 components
     * @array
     */
    refs: [
        { ref: 'languageTree', selector: 'translation-main-navigation' },
        { ref: 'languageForm', selector: 'translation-main-form' }
    ],

    /**
     * Provides an registry for the available translation
     * services.
     * @object Ext.util.HashMap
     */
    urls: Ext.create('Ext.util.HashMap'),

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        // Bind the neccessary event listeners
        me.control({
            'translation-main-navigation': {
                //load: me.onSetActiveLanguage,
                itemclick: me.onLoadTranslations
            },
            'translation-main-window button[action=translation-main-window-cancel]': {
                click: me.onCloseWindow
            },
            'translation-main-window button[action=translation-main-window-save]': {
                click: me.onSaveTranslations
            },
            'translation-main-window button[action=translation-main-window-save-and-close]': {
                click: me.onSaveAndCloseTranslation
            },
            'translation-main-toolbar button[action=translation-main-toolbar-google]': {
                click: me.onGoogleTranslator
            },
            'translation-main-services-window button[action=translation-main-services-window-translate]': {
                click: me.onStartTranslation
            }
        });

        // Create the main window
        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.subApplication.getStore('Language'),
            translatableFields: me.subApplication.translatableFields
        });

        // Register the available translation services
        me.registerTranslationSerivces();

        me.callParent(arguments);
    },

    /**
     * Saves the translation and closes the window
     * @param btn
     */
    onSaveAndCloseTranslation: function(btn) {
        var me = this;
        me.onSaveTranslations();
        me.onCloseWindow(btn);
    },

    /**
     * Registers all available translation services.
     *
     * Third-Party developers can override this method to add more translation services.
     * If you're adding more translation services, please note that each services needs
     * an template which be used to fill the Request URL.
     *
     * @private
     * @return void
     */
    registerTranslationSerivces: function() {
        var me = this;

        // Register Google Translations
        me.urls.add('google', {
            tpl: '{literal}//translate.google.de/?hl=de&tab=wT#{0}|{1}|{2}{/literal}',
            sep: '|',
            fullName: '{s name=service/google}Google translator{/s}'
        });
    },

    /**
     * Event listener method which will be fired when the navigation
     * panel on the left hand is rendered.
     *
     * Sets the first language in the tree as the active item.
     *
     * @event load
     * @public
     * @return void
     */
    onSetActiveLanguage: function() {
        var me = this,
            view = me.getLanguageTree(),
            index = 1,
            record = view.getRootNode().getChildAt(index),
            node = view.getView().getNode(index);

        if(record && !view.initialized) {
            view.getSelectionModel().select(record);
            view.initialized = true;
            me.activeLanguage = record;

            view.fireEvent('itemclick', view, record, node, index);
        }
    },

    /**
     * Event listener method which will be fired when the user clicks
     * on an leaf in the navigation tree (left hand of the module).
     *
     * Loads the translations for the selected language
     * and fills the translation form.
     *
     * @event itemclick
     * @public
     * @param [object] view - Ext.tree.Panel
     * @param [object] record - Ext.data.Record of the clicked tree node
     */
    onLoadTranslations: function(view, record) {
        var me = this,
            pnl = me.getLanguageForm();

        if(record.get('default')) {
            return false;
        }

        // Always set the active language
        me.activeLanguage = record;

        // Clear the form
        pnl.getForm().reset();
        pnl.enable();
        pnl.setLoading(true);

        Ext.Ajax.request({
            url: '{url action=readTranslation}',
            params: {
                key: me.subApplication.translationKey,
                type: me.subApplication.translationType,
                merge: me.subApplication.translationMerge ? 1 : 0,
                language: me.activeLanguage.get('id')
            },
            success: function(response) {

                // Set the language as a suffix for the title
                pnl.setTitle(pnl.originalTitle + ' - ' + me.activeLanguage.get('text'));

                response = Ext.JSON.decode(response.responseText);
                pnl.getForm().loadRecord(response);

                // SW-3564 - Force codemirror fields to refresh
                var codeMirrorFields = pnl.query('codemirrorfield');
                Ext.each(codeMirrorFields, function(field) {
                    var editor = field.editor;
                    editor.refresh();
                });

                pnl.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which will be fired when the user clicks
     * on the "cancel" button.
     *
     * Closes the translation window.
     *
     * @event click
     * @public
     * @param [Ext.button.Button] btn - clicked button
     * @return void
     */
    onCloseWindow: function(btn) {
        var win = btn.up('window');
        win.destroy();
    },

    /**
     * Event listener method which will be fired when the user clicks
     * on the "save translations" button.
     *
     * Saves the translations through an AJAX request and shows an
     * growl message.
     *
     * @event click
     * @public
     * @param [object] btn - clicked Ext.button.Button
     * @return void
     */
    onSaveTranslations: function() {
        var me = this,
            pnl = me.getLanguageForm(),
            params = {
                key: me.subApplication.translationKey,
                type: me.subApplication.translationType,
                merge: me.subApplication.translationMerge ? 1 : 0,
                language: me.activeLanguage.get('id')
            },
            values = pnl.getForm().getValues();

        // Sanitize the parameters for the AJAX request
        Ext.iterate(values, function(item, index) {
            if(index === true) {
                index = '1';
            } else if(index === false) {
                index = '0';
            }
            if(index !== null) {
                params['data[' + item + ']'] = index;
            }
        });

        Ext.Ajax.request({
            url: '{url action=saveTranslation}',
            params: params,
            success: function() {
                Shopware.Notification.createGrowlMessage('{s name=messages/success_title}Successful{/s}', '{s name=messages/success_message}Translations have been saved successfully.{/s}', '{s name=window_title}Translation{/s}');
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage('{s name=messages/failure_title}Error{/s}', "{s name=messages/failure_message}Translations could not be saved.{/s}", '{s name=window_title}Translation{/s}');
            }
        });
    },

    /**
     * Proxy method which just sets an service name and calls the
     * private method onOpenTranslationServiceWindow, which
     * handles the rest.
     *
     * @event click
     * @public
     * @return void
     */
    onGoogleTranslator: function() {
        this.onOpenTranslationServiceWindow('google');
    },

    /**
     * Opens a translation service window which contains
     * serveral configuration properties.
     *
     * Please note that there's a special behavior if the
     * field label is filled with a non-breaking space.
     *
     * @private
     * @param [string] serviceName - Name of the service
     * @return void
     */
    onOpenTranslationServiceWindow: function(serviceName) {
        var me = this,
            serviceInfo = me.urls.get(serviceName);

        // Get all translatable fields with an empty text
        var data = [];
        Ext.each(me.subApplication.translatableFields, function(field) {
            if(field.emptyText) {
                var label;

                // Special behavior - if the fieldLabel contains a non-breaking space, then set an custom label
                if(field.fieldLabel === '&nbsp') {
                    label = '{s name=emptyFieldLabel}Description{/s}';
                }

                data.push({ valueField: field.emptyText, displayField: label || field.fieldLabel });
            }
        });

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: [ 'valueField', 'displayField' ],
            data: data
        });

        me.langStore = Ext.create('Shopware.apps.Base.store.Locale');

        me.getView('main.Services').create({
            activeLanguage: me.activeLanguage,
            fieldStore: fieldStore,
            serviceName: serviceInfo.fullName,
            langStore: me.langStore,
            serviceInfo: serviceInfo
        }).show();
    },

    /**
     * Event listener method which will be called when the user clicks
     * on the "start translation" button.
     *
     * Determines the values of the tranlation service window and
     * formats the translation url.
     *
     * @private
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onStartTranslation: function(btn) {
        var me = this,
            win = btn.up('window'),
            formPnl = win.down('form'),
            form = formPnl.getForm(),
            values = form.getValues(),
            fromLanguage = win.activeLanguage.get('id'),
            toLanguage = values.language,
            text = values.translationField,
            url;

        fromLanguage = me.langStore.getById(fromLanguage).get('locale');
        fromLanguage = fromLanguage.substr(0, 2);

        toLanguage = me.langStore.getById(toLanguage).get('locale');
        toLanguage = toLanguage.substr(0, 2);

        // Add german umlauts to the default character entities
        Ext.String.addCharacterEntities({
            '&uuml;': 'ü',
            '&Uuml;': 'Ü',
            '&auml;': 'ä',
            '&Auml;': 'Ä',
            '&ouml;': 'ö',
            '&Ouml;': 'Ö',
            '&szlig': 'ß',
            '&nbsp;': ' '
        });

        text = Ext.String.trim(Ext.util.Format.stripTags(text));
        text = Ext.String.htmlDecode(text);
        url =Ext.String.format(win.serviceInfo.tpl, fromLanguage, toLanguage, Ext.String.trim(Ext.util.Format.stripTags(text)));

        window.open(url);
    }
});
//{/block}text
