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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Translation Plugin for Ext.form.Panel's
 *
 * This plugin provides an easy-to-use way to fill in translation
 * for multiple form elements such as a textfield, a combo box or
 * a fancy TinyMCE form field.
 */

//{block name="backend/base/component/form_plugin_translation"}

Ext.define('Shopware.form.plugin.Translation',
/** @lends Ext.AbstractPlugin# */
{
    /**
     * Extends the abstact plugin component
     * @string
     */
    extend: 'Ext.AbstractPlugin',

    /**
     * Defines alternate names for this class
     * @array
     */
    alternateClassName: [ 'Shopware.form.Translation', 'Shopware.plugin.Translation' ],

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @string
     */
    alias: 'plugin.translation',

    /**
     * Indicatates the type of the translation.
     *
     * @default article
     * @string
     */
    translationType: 'article',

    /**
     * Callback method which will be called when the translation window is closed.
     * @function
     */
    translationCallback: Ext.emptyFn,

    /**
     * Indicates the record id
     * @default null
     * @integer
     */
    translationKey: null,

    /**
     * @boolean
     */
    translationMerge: false,

    /**
     * Property which holds the generated icons for later cleanup
     * @array
     */
    icons: [],

    /**
     * List of classes to load together with this class. These aren't neccessarily loaded before this class is instantiated.
     * @array
     */
    uses: [ 'Ext.DomHelper', 'Ext.Element' ],

    /**
     * Initials the plugin for the provided form
     * @param form
     */
    init: function(form) {
        var me = this;

        me.initConfig(form);

        form.on('afterrender', function() {
            me.initTranslationFields(form);
        });

        form.getForm().on('recordchange', function() {
            me.initTranslationFields(form);
        });

        form.translationPlugin = this;
        me.callParent(arguments);
    },

    /**
     * @param form Ext.form.Panel
     */
    initConfig: function (form) {
        form._translationConfig = {
            translationType: this.translationType,
            translationKey: this.translationKey,
            translationCallback: this.translationCallback,
            translationMerge: this.translationMerge
        };
    },

    /**
     * Validates if the form can be translated and initials the field globe icon
     * @param form - Ext.form.Panel
     */
    initTranslationFields: function(form) {
        var me = this;
        var config = form._translationConfig;
        var record = form.getForm().getRecord();

        if (!config.translationKey && typeof record === 'undefined') {
            return;
        }
        if (!config.translationKey && record.phantom) {
            return;
        }

        var fields = me.getTranslatableFields(form);
        Ext.each(fields, function(field) {
            me.createGlobeElement(form, field);
        });
    },

    /**
     * Returns all fields of the provided form which are translatable
     * @param form - Ext.form.Panel
     * @returns { Array }
     */
    getTranslatableFields: function(form) {
        var fields = [];

        Ext.each(form.getForm().getFields().items, function(field) {
            var config = field.initialConfig;
            if (config.translatable && !field.isDisabled()) {
                fields.push(field);
            }
        });
        return fields;
    },

    /**
     * Creates the translation indicator in the form element.
     *
     * @param field - Ext.form.Field
     * @param form - Ext.form.Panel
     * @return void
     */
    createGlobeElement: function(form, field) {
        var me = this, type, style, globeIcon;

        style = me.getGlobeElementStyle(field);
        globeIcon = new Ext.Element(document.createElement('span'));
        globeIcon.set({
            cls: Ext.baseCSSPrefix + 'translation-globe sprite-globe',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;'+style
        });

        globeIcon.addListener('click', function() {
            me.openTranslationWindow(form);
        });

        if (field.getEl()) {
            field.getEl().setStyle('position', 'relative');
        }

        try {
            if (field.globeIcon) {
                field.globeIcon.removeListener('click');
                field.globeIcon.remove();
            }
        } catch (e) { }

        field.globeIcon = globeIcon;
        if (Ext.isFunction(field.insertGlobeIcon)) {
            field.insertGlobeIcon(globeIcon);
        } else if (field.inputEl) {
            globeIcon.insertAfter(field.inputEl);
        }
    },

    /**
     * Returns custom styling for different field types.
     * @param field - Ext.form.Field
     * @returns string
     */
    getGlobeElementStyle: function(field) {
        switch(this.getFieldType(field)) {
            case 'tinymce':
                return 'top: 3px; right: 3px';
            case 'codemirror':
                return 'top: 6px; right: 26px;z-index:999999';
            case 'textarea':
                return 'top: 6px; right: 6px';
            case 'trigger':
                return 'top: 6px; right: 26px';
            case 'textfield':
            default:
                return 'top: 6px; right: 6px; z-index:1;';
        }
    },

    /**
     * Opens the translation module for the provided form
     * @param form - Ext.form.Panel
     */
    openTranslationWindow: function(form) {
        var me = this;
        var config = form._translationConfig;
        var key = config.translationKey || form.getForm().getRecord().getId();
        var fields = me.createTranslationFields(form);

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Translation',
            eventScope: me,
            translationCallback: config.translationCallback,
            translatableFields: fields,
            translationType: config.translationType,
            translationMerge: config.translationMerge,
            translationKey: key
        });
    },

    /**
     * Creates the translation fields for the translation module.
     * @param form - Ext.form.Panel
     * @returns { Array }
     */
    createTranslationFields: function(form) {
        var me = this;
        var fields = me.getTranslatableFields(form);
        var result = [];

        Ext.each(fields, function(field) {
            var config = Ext.clone(field.initialConfig);

            // Always allow empty fields due to the fact that we're always having a fallback value
            if (!config.allowBlank) {
                config.allowBlank = true;
            }

            //reset all field listeners to prevent cross module side effects
            if (config.listeners) {
                config.listeners = { };
            }

            // Allow overwrite of field label with an alternative label.
            if (config.translationLabel) {
                config.fieldLabel = config.translationLabel;
            }
            // Allow overwrite of field name with an alternative name.
            if (config.translationName) {
                config.name = config.translationName;
            }
            // If there's no label, set a non-breaking space
            if (!config.fieldLabel) {
                config.fieldLabel = '&nbsp';
                config.labelSeparator = '';
            }
            config.labelWidth = 130;

            if (!config.xtype) {
                config.xtype = field.xtype;
            }
            if (field.getValue()) {
                if (config.xtype != 'tinymce') {
                    config.emptyText = field.getValue();

                    if (config.xtype === 'productstreamselection') {
                        config.emptyText = field.store.findRecord('id', config.emptyText).get('name');
                    }
                }
                if (config.xtype == 'checkbox') {
                    config.checked = field.checked;
                }
            }
            result.push(config)
        });

        return result;
    },

    /**
     * Evaluates the field type.
     *
     * @param field - Ext.form.Field
     * @return { string|boolean } - field type
     */
    getFieldType: function(field) {
        var type = null;

        Ext.each(field.alternateClassName, function(className) {
            if(className === 'Ext.form.TextField') {
                type = 'textfield';
            }

            if(className === 'Shopware.form.TinyMCE') {
                type = 'tinymce';
            }

            if(className === 'Shopware.form.CodeMirror') {
                type = 'codemirror';
            }

            if(className === 'Ext.form.TextArea') {
                type = 'textarea';
            }

            if(className === 'Ext.form.TriggerField'
                || className === 'Ext.form.ComboBox'
                || className === 'Ext.form.DateField'
                || className === 'Ext.form.Picker'
                || className === 'Ext.form.Spinner'
                || className === 'Ext.form.NumberField'
                || className === 'Ext.form.Number'
                || className === 'Ext.form.TimeField') {
                type = 'trigger';
            }
        });
        return type;
    }
});
//{/block}
