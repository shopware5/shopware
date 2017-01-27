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

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/import_export"}
Ext.define('Shopware.apps.Snippet.view.main.ImportExport', {
    extend: 'Enlight.app.Window',
    alias: 'widget.snippet-main-importExport',
    layout: 'fit',

    width: 500,
    height: 220,

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title:                  '{s name=title}Import / Export Snippets{/s}',
        buttonChooseFile:       '{s name=button_choose_file}Choose File{/s}',
        emptyTextChooseFile:    '{s name=empty_text_choose_file}Please choose a file..{/s}',
        messageUploadFile:      '{s name=message_upload_file}Uploading your file...{/s}',
        buttonStartImport:      '{s name=button_start_import}Start Import{/s}',
        buttonExport:           '{s name=button_export}Export{/s}',
        fieldFile:              '{s name=field_file}File{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.title = me.snippets.title;

        me.items = [
            {
                xtype: 'tabpanel',
                items: [me.createExportForm(), me.createImportForm()]
            }
        ];

        me.callParent(arguments);
    },

    createImportForm: function() {
        var me = this;

        return {
            xtype: 'form',
            title: 'Import',
            layout: 'anchor',
            bodyPadding: 10,
            flex: 1,
            defaults: {
                layout: 'anchor',
                labelWidth: 130,
                anchor: '98%'
            },

            items: [{
                xtype: 'filefield',
                emptyText: me.snippets.emptyTextChooseFile,
                buttonText: me.snippets.buttonChooseFile,
                name: 'file',
                fieldLabel: me.snippets.fieldFile,
                allowBlank: false,
                anchor: '100%',
                buttonConfig : {
                    iconCls: 'sprite-inbox-upload',
                    cls: 'small secondary'
                }
            }],

            buttons: [{
                text: me.snippets.buttonStartImport,
                cls: 'button primary',
                handler: function () {
                    var form = this.up('form').getForm();
                    if (form.isValid()) {
                        form.submit({
                            url: ' {url module=backend controller=snippet action=importSnippet}',
                            waitMsg: me.snippets.messageUploadFile,
                            success: function (fp, o) {
                                Ext.Msg.alert('Result', o.result.message);
                                me.close();
                            },
                            failure: function (fp, o) {
                                Ext.Msg.alert('Fehler', o.result.message);
                            }
                        });
                    }
                }
            }]
        };
    },

    createExportForm: function() {
        var me = this;

        return {
            xtype: 'form',
            title: 'Export',
            layout: 'anchor',
            standardSubmit: true,
            bodyPadding: 10,
            flex: 1,
            defaults: {
                layout: 'anchor',
                labelWidth: 130,
                anchor: '98%'
            },

            items: [{
                xtype: 'combo',
                fieldLabel: 'Format',
                listeners: {
                    'afterrender': function () {
                        this.setValue(this.store.getAt('0').get('id'));
                    }
                },
                store: me.getFormatComboStore(),
                name: 'format',
                forceSelection: true,
                allowBlank: false,
                editable: false,
                mode: 'local',
                triggerAction: 'all',
                displayField: 'label',
                valueField: 'id'
            }],

            buttons: [{
                text: me.snippets.buttonExport,
                cls: 'button primary',
                handler: function () {
                    var form = this.up('form').getForm();
                    if (!form.isValid()) {
                        return;
                    }
                    form.submit({
                        method: 'GET',
                        url: ' {url module=backend controller=snippet action=exportSnippet}'
                    });
                }
            }]
        };
    },


    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getFormatComboStore: function() {
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['sql', 'SQL (Backup)'],
                ['csvexcel', 'CSV (Microsoft Excel)'],
                ['csv', 'CSV']
            ]
        });
    }
});
//{/block}
