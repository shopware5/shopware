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
 * @package    Emotion
 * @subpackage View
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
//{block name="backend/emotion/view/templates/settings"}
Ext.define('Shopware.apps.Emotion.view.templates.Settings', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-view-templates-settings',
    width: 600,
    height: 250,
    autoShow: true,
    layout: 'fit',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        invalid_template: '{s name=templates/error/invalid_template}The provided file seems to be not a valid template file{/s}',
        title_new: '{s name=templates/settings/title_new}Create new template{/s}',
        title_edit: '{s name=templates/settings/title_edit}Edit existing template{/s}',
        fieldset_title: '{s name=templates/settings/fieldset}Define template{/s}',
        fields: {
            name: '{s name=templates/settings/name}Name{/s}',
            file: '{s name=templates/settings/file}Template file{/s}'
        },
        support: {
            name: '{s name=templates/support/name}Initial description of the template.{/s}',
            file: '{s name=templates/support/file}Template file name in the file system. The template must be under widgets/emotion.{/s}'
        },
        buttons: {
            cancel: '{s name=templates/button/cancel}Cancel{/s}',
            save: '{s name=templates/button/save}Save{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets[(me.hasOwnProperty('record') ? 'title_edit' : 'title_new' )];

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            border: 0,
            bodyBorder: 0,
            items: [{
                xtype: 'fieldset',
                defaults: {
                    labelWidth: '155',
                    anchor: '100%'
                },
                title: me.snippets.fieldset_title,
                items: me.createFormItems()
            }]
        });
        me.items = [ me.formPanel ];
        me.bbar = me.createActionButtons();

        if (me.hasOwnProperty('record')) {
            me.formPanel.loadRecord(me.record);
        }

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar which contains the action buttons.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createActionButtons: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            docked: 'bottom',
            items: [ '->', {
                xtype: 'button',
                text: me.snippets.buttons.cancel,
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }, {
                xtype: 'button',
                text: me.snippets.buttons.save,
                cls: 'primary',
                action: 'emotion-save-grid'
            }]
        });
    },

    /**
     * Creates the form items for the settings window.
     *
     * @returns { Array }
     */
    createFormItems: function() {
        var me = this, label = me.snippets.fields,
            support = me.snippets.support;

        var name = Ext.create('Ext.form.field.Text', {
            name: 'name',
            fieldLabel: label.name,
            allowBlank: false,
            supportText: support.name
        });

        var template = Ext.create('Ext.form.field.Text', {
            name: 'file',
            fieldLabel: label.file,
            allowBlank: false,
            validator: function(value) {
                return (/^((.*)\.tpl)$/.test(value)) ? true : me.snippets.invalid_template;
            },
            supportText: support.file
        });

        return [ name, template ];
    }
});
//{/block}
