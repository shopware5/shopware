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
 * @package    NewsletterManager
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_newsletter/main"}

/**
 * Shopware Controller - Overview controller
 * For events and actions fired in the overview tab
 */
//{block name="backend/newsletter_manager/controller/designer"}
Ext.define('Shopware.apps.NewsletterManager.controller.Designer', {

    extend: 'Ext.app.Controller',

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'newsletter-designer': {
                'openSettingsWindow': me.onOpenSettingsWindow
            },
            'newsletter-settings-window': {
                saveComponent: me.onSaveComponent
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the the save button in the settings window was pressed. Validates the form,
     * sets the component's isNew to false
     * @param win
     * @param record
     * @param compFields
     * @return Boolean
     */
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

        // mark the element as not being new any more.
        record.set('isNew', false);
        // Check if the newsletter is valid and enable the save button if it is
        me.getController('Editor').onFormChanged();
    },

    /**
     * Helper methode to get data from field depending on its type
     * @param field
     * @param record
     * @return Object
     */
    getFieldData: function(field, record) {
        if (field.getName() === 'bannerMapping') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if (field.getName() === 'link_data') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if (field.getName() === 'article_data') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if (field.getName() === 'selected_manufacturers') {
            return {
                id: field.fieldId,
                type: field.valueType,
                key: field.getName(),
                value: record.get('mapping')
            };
        } else if (field.getName() === 'selected_articles') {
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
     * Event listener method which opens the settings window
     * for the clicked item.
     *
     * @param view
     * @param record
     * @param component
     * @param fields
     */
    onOpenSettingsWindow: function(view, record, component, fields, newsletter) {
        this.getView('components.SettingsWindow').create({
            settings: {
                record: record,
                component: component,
                fields: fields,
                grid: newsletter
            }
        });
    }


});
//{/block}