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
     * Property which holds the translatable form elements to pass them to the
     * "Shopware.apps.Translation" sub application.
     * @array
     */
    translatableFields: [],

    /**
     * Property which holds the translatable form field configuration to pass them
     * to the "Shopware.apps.Translation" sub application.
     * @array
     */
    translatableFieldsConfig: [],

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
     * Property which holds the client (e.g. the Ext.form.Panel)
     * @default null
     * @object
     */
    client: null,

    /**
     * The init method is invoked after initComponent method has been run for the client Component.
     *
     * @public
     * @param [object] client - Ext.Component which calls the plugin
     * @return void
     */
    init: function(client) {
        var me = this;

        me.callParent(arguments);

        // Assign the client to the plugin scope
        me.client = client;
        me.client.on('afterrender', me.onGetTranslatableFields, me);
        me.client.getForm().on('recordchange', me.onGetTranslatableFields, me);
    },

    /**
     * Removes clear previously generated icons
     *
     * @public
     * @return void
     */
    clear: function() {
        var me = this;

        // clear array
        me.translatableFields.length = 0;

        // unset listeners and remove icon from dom
        Ext.each(this.icons, function(icon) {
            icon.removeListener('click');
            icon.remove();
        });
    },

    /**
     * Event listener method which will be fired when the client fires
     * the "afterrender" event.
     *
     * Collects the translatable items and creates some sanitize.
     *
     * @event afterrender
     * @public
     * @return void
     */
    onGetTranslatableFields: function() {
        var me = this;

        // clear previously generated icons
        me.clear();

        if (!me.translationKey && typeof me.client.getForm().getRecord() === 'undefined') {
            return;
        }

        if (!me.translationKey && me.client.getForm().getRecord().phantom) {
            return;
        }

        Ext.each(me.client.getForm().getFields().items, function(field) {
            var config = field.initialConfig;

            if(config.translatable) {

                // Always allow empty fields due to the fact that we're always having a fallback value
                if(!config.allowBlank) {
                    config.allowBlank = true;
                }

                // Allow overwrite of field label with a alternative label.
                if(config.translationLabel) {
                    config.fieldLabel = config.translationLabel;
                }
                // Allow overwrite of field name with a alternative name.
                if(config.translationName) {
                    config.name = config.translationName;
                }
                // If there's no label, set a non-breaking space
                if(!config.fieldLabel) {
                    config.fieldLabel = '&nbsp';
                    config.labelSeparator = '';
                }

                // SW-3564 - Don't take disabled fields into account
                if(!field.isDisabled()) {
                    me.translatableFields.push(field);
                }

                // Inject the globe element into the component
                if(field.getEl()) {
                    me.createGlobeElement(field);
                }
            }
        });
    },

    /**
     * Creates the translation indicator in the form elements.
     *
     * @private
     * @param [object] field - translatable Ext.form.Field
     * @return void
     */
    createGlobeElement: function(field) {
        var me = this, type, style, globeIcon;

        type = me.getFieldType(field);
        switch(type) {
            case 'tinymce':
                style = 'top: 3px; right: 3px';
                break;
            case 'codemirror':
                style = 'top: 6px; right: 26px;z-index:999999';
                break;
            case 'textarea':
                style = 'top: 6px; right: 6px';
                break;
            case 'trigger':
                style = 'top: 6px; right: 26px';
                break;
            case 'textfield':
            default:
                style = 'top: 6px; right: 6px; z-index:1;';
                break;
        }

        globeIcon = new Ext.Element(document.createElement('span'));

        globeIcon.set({
            cls: Ext.baseCSSPrefix + 'translation-globe sprite-globe',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;'+style
        });
        globeIcon.addListener('click', me.onOpenTranslationWindow, me);


        field.getEl().setStyle('position', 'relative');
        globeIcon.insertAfter(field.inputEl);
        me.icons.push(globeIcon);
    },

    /**
     * Opens the translation sub application and pass
     * the translatable field to the component
     *
     * @private
     * @return void
     */
    onOpenTranslationWindow: function() {
        var me = this;

        // Check if subapplications are supported
        if(typeof(Shopware.app.Application.addSubApplication) !== 'function') {
            Ext.Error.raise('Your ExtJS application does not support sub applications');
        }

        var key = me.translationKey || me.client.getForm().getRecord().getId();
        me.translatableFieldsConfig = me.getFieldValues(me.translatableFields);

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Translation',
            eventScope: me,
            translationCallback: me.translationCallback,
            translatableFields: me.translatableFieldsConfig,
            translationType: me.translationType,
            translationMerge: me.translationMerge,
            translationKey: key
        });
    },

    /**
     * Determines the values of the passed field array to
     * set the value as the emptyText.
     *
     * @private
     * @param [array] fields - the translatable fields
     * @return [array] result - resulting field configuration
     */
    getFieldValues: function(fields) {
        var result = [];

        Ext.each(fields, function(field) {
            var value = field.getValue(),
                config = field.initialConfig;

            if(!config.xtype) {
                config.xtype = field.xtype;
            }
            if(value) {
                config.emptyText = value;
            }
            result.push(config)
        });

        return result;
    },

    /**
     * Evalutes the field type.
     *
     * @private
     * @param [object] field - Ext.form.Field
     * @return [string|boolean] - field type
     */
    getFieldType: function(field) {
        var type;

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
                || className === 'Ext.form.TimeField') {
                type = 'trigger';
            }
        });

        return type;
    },

    /**
     * The plugin cleanup method which the owning Component calls at Component destruction time.
     * Removes the class member before the Component will be destroyed.
     *
     * @public
     * @return void
     */
    destroy: function() {
        //this.destroyMembers('client', 'translatableFields', 'translationType', 'translationKey');
        this.callParent(arguments);
    }
});
