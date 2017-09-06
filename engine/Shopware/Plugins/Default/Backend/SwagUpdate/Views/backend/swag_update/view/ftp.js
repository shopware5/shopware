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
 */

//{namespace name=backend/swag_update/main}
//{block name="backend/swag_update/view/ftp"}
Ext.define('Shopware.apps.SwagUpdate.view.Ftp', {

    extend: 'Enlight.app.Window',

    alias: 'widget.update-ftp',

    title: '{s name="ftp/window_title"}FTP Credentials{/s}',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    width: 360,
    height: 380,

    wrongPermissionCount: 0,

    initComponent: function () {
        var me = this;

        me.items = [ me.createFormPanel() ];

        me.dockedItems = [ me.createToolbar() ];

        return me.callParent(arguments);
    },

    createFormPanel: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 15,
            flex: 1,
            background: '#fff',
            defaults: {
                xtype: 'textfield',
                labelWidth: 120,
                allowBlank: false
            },
            items: [ me.createInfoContainer(), {
                fieldLabel: '{s name="ftp/label_username"}Username{/s}',
                name: 'user'
            },{
                fieldLabel: '{s name="ftp/label_password"}Password{/s}',
                name: 'password',
                inputType: 'password'
            },{
                fieldLabel: '{s name="ftp/label_server"}Server{/s}',
                name: 'server'
            },{
                fieldLabel: '{s name="ftp/label_path"}Path{/s}',
                name: 'path'
            }]
        });

        return me.formPanel;
    },

    createToolbar: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls:'secondary',
            name: 'save-article-button',
            text: '{s name="cancel"}Cancel{/s}',
            handler: function() {
                me.destroy();
            }
        });

        me.saveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-article-button',
            text: '{s name="performUpdate"}Update{/s}',
            handler: function() {
                me.fireEvent('saveFtp', me, me.formPanel);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [ '->', me.cancelButton, me.saveButton ]
        });
    },

    createInfoContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            margin: '0 0 30 0',
            style: 'font-size: 13px;',
            html: Ext.String.format(
                '{s name="ftp/info_text"}The file permissions of [0] file(s) could not be fixed. A list of affected files can be found in the logs.<br><br>Please fix all file permission problems (recommended) or fill in your ftp credentials.{/s}',
                me.wrongPermissionCount
            )
        });
    }

});

//{/block}
