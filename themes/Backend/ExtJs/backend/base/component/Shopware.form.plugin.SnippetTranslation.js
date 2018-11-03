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

//{block name="backend/base/component/form_plugin_sinppet_translation"}

Ext.define('Shopware.form.plugin.SnippetTranslation',
/** @lends Ext.AbstractPlugin# */
{
    /**
     * Extends the abstact plugin component
     * @string
     */
    extend: 'Ext.AbstractPlugin',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @string
     */
    alias: 'plugin.snippet-translation',

    /**
     * Indicatates the type of the translation.
     *
     * @default article
     * @string
     */
    namespace: null,

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

        form._snippetTranslationConfig = {
            namespace: me.namespace,
            getSnippetName: me.getSnippetName
        };

        form.on('afterrender', function() {
            me.initTranslationFields(form);
        });

        form.getForm().on('recordchange', function() {
            me.initTranslationFields(form);
        });

        form.snippetTranslationPlugin = this;
        me.callParent(arguments);
    },

    /**
     * Validates if the form can be translated and initials the field globe icon
     * @param form - Ext.form.Panel
     */
    initTranslationFields: function(form) {
        var me = this;
        var config = form._snippetTranslationConfig;
        var record = form.getForm().getRecord();

        var fields = me.getTranslatableFields(form);
        Ext.each(fields, function(field) {
            if (field.snippetGlobeIcon) {
                field.snippetGlobeIcon.removeListener('click');
                field.snippetGlobeIcon.remove();
            }
        });
        if (!config.namespace && typeof record === 'undefined') {
            return;
        }
        if (!config.namespace && record.phantom) {
            return;
        }
        if (!record || !record.get('id')) {
            return
        }

        if (!Ext.isFunction(config.getSnippetName)) {
            return;
        }

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
        var me = this, style, snippetGlobeIcon;

        style = me.getGlobeElementStyle(field);
        snippetGlobeIcon = new Ext.Element(document.createElement('span'));
        snippetGlobeIcon.set({
            cls: 'settings--snippets',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;'+style
        });

        snippetGlobeIcon.addListener('click', function() {
            me.openTranslationWindow(form, field);
        });

        if (field.getEl()) {
            field.getEl().setStyle('position', 'relative');
        }

        try {
            if (field.snippetGlobeIcon) {
                field.snippetGlobeIcon.removeListener('click');
                field.snippetGlobeIcon.remove();
            }
        } catch (e) { }

        field.snippetGlobeIcon = snippetGlobeIcon;
        if (Ext.isFunction(field.insertGlobeIcon)) {
            field.insertGlobeIcon(snippetGlobeIcon);
        } else if (field.inputEl) {
            snippetGlobeIcon.insertAfter(field.inputEl);
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

    openTranslationWindow: function(form, field) {
        var config = form._snippetTranslationConfig;

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Snippet',
            action: 'detail',
            shopId: 1,
            snippet: {
                name: config.getSnippetName(field),
                namespace: config.namespace
            }
        });
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
