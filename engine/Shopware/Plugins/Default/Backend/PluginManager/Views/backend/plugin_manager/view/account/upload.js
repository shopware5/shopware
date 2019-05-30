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
 * @package    PluginManager
 * @subpackage Account
 * @version    $Id$
 * @author shopware AG
 */
// {namespace name=backend/plugin_manager/translation}

// {block name="backend/plugin_manager/view/account/upload"}
Ext.define('Shopware.apps.PluginManager.view.account.Upload', {
    cls: 'plugin-manager-upload-window',

    alias: 'widget.plugin-manager-upload-window',

    extend: 'Ext.window.Window',
    modal: true,

    header: false,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    width: 500,
    height: 250,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        me.fileUpload = Ext.create('Ext.form.field.File', {
            fieldLabel: '{s name="upload_plugin"}Upload plugin{/s}',
            name: 'plugin',
            labelWidth: 125,
            flex: 1,
            allowBlank: false,
            margin: '10 0 0',
            buttonConfig: {
                cls: 'primary small',
                text: '{s name="upload_select"}Select{/s}'
            },
            listeners: {
                'change': function() {
                    if (me.fileUpload.getValue()) {
                        me.uploadButton.enable();
                    } else {
                        me.uploadButton.disable();
                    }
                }
            }
        });

        me.info = Ext.create('Ext.form.FieldSet', {
            cls: 'info',
            title: '{s name="upload_info_title"}Tip{/s}',
            html: '{s name="upload_info_text"}Here you can upload and install your plugins manually. Please keep in mind that plugins have to be in a ZIP archive and the file size can\'t exceed the configured upload size limit.{/s}'
        });

        me.form = Ext.create('Ext.form.Panel', {
            items: [ me.info, me.fileUpload ],
            bodyPadding: 20,
            border: false,
            url: '{url controller="PluginInstaller" action="upload"}',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            }
        });

        return me.form;
    },

    createToolbar: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name="cancel"}Cancel{/s}',
            handler: function() {
                me.destroy();
            }
        });

        me.uploadButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="upload_plugin"}Upload plugin{/s}',
            disabled: true,
            handler: function() {
                if (!me.form.getForm().isValid()) {
                    return;
                }

                Shopware.app.Application.fireEvent('upload-plugin', me.form, function(success) {
                    me.destroy();
                    Shopware.app.Application.fireEvent('reload-local-listing');
                });
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [me.cancelButton, '->', me.uploadButton]
        });
    }
});
// {/block}
