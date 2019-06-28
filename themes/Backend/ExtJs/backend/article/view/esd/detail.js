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
 * @package    Article
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article esd page
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/esd/panel"}
Ext.define('Shopware.apps.Article.view.esd.Detail', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.panel.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-esd-detail',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-esd-detail',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    layout: {
        align: 'stretch',
        type: 'hbox'
    },

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        esdInfo:{
            title: '{s name=esd/detail/esdinfo/title}ESD-Version{/s}',
            article: '{s name=esd/detail/esdinfo/artcile}Article:{/s}',
            serialAdministration: '{s name=esd/detail/esdinfo/serial_administration}Serial administration:{/s}',
            enableSerialAdministration: '{s name=esd/detail/esdinfo/enable_serial_administration}Enables the administration of serials{/s}'
        },

        fileInfo:{
            title: '{s name=esd/detail/fileinfo/title}File-Info{/s}',
            downloadFile: '{s name=esd/detail/fileinfo/download_file}Download file{/s}',
            noFile: '{s name=esd/detail/fileinfo/no_file}No file choosen{/s}'
        },

        fileUpload:{
            title: '{s name=esd/detail/fileupload/title}File-Upload{/s}',
            selection: '{s name=esd/detail/fileupload/selection}Selection{/s}',
            selectFile: '{s name=esd/detail/fileupload/download_file}Select file{/s}',
            dropZoneText: '{s name=esd/detail/fileupload/drop_zone_text}Upload file via Drag and Drop{/s}',
            buttonOverwrite: '{s name=esd/detail/fileupload/buttonOverwrite}Overwrite{/s}',
            buttonRename: '{s name=esd/detail/fileupload/buttonRename}Rename{/s}',
            buttonCancel: '{s name=esd/detail/fileupload/buttonCancel}Cancel{/s}',
            renameMessage: '{s name=esd/detail/fileupload/reanameMessage}You file has been renamed to [0]{/s}'
    },

        fileChoose:{
            title: '{s name=esd/detail/filechoose/title}Choose File{/s}',
            pleaseChoose: '{s name=esd/detail/fileupload/please_choose}Please choose...{/s}'
        }
    },

    /**
     * Initialize the Shopware.apps.Article.view.esd.List and defines the necessary default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * @event downloadFile
             */
            'downloadFile',

            /**
             * @event backToList
             */
            'backToList',

            /**
             * @event hasSerialsChanged
             * @param [boolean]
             */
            'hasSerialsChanged',

            /**
             * @event fileChange
             * @param [string]
             */
            'fileChanged'
        );
    },

    /**
     * Creates the items
     *
     * @return [array] items
     */
    getItems: function() {
        var me = this;
        return [
            {
                xtype: 'container',
                padding: 10,
                flex: 4,
                layout: {
                    align: 'stretch',
                    type: 'vbox'
                },
                items: [
                    me.getEsdInfoPanel(),
                    {
                        xtype: 'article-esd-serials',
                        store: me.serialStore,
                        disabled: !me.esdRecord.get('hasSerials'),
                        flex: 1
                    }
                ]
            },
            {
                xtype: 'container',
                padding: 10,
                flex: 2,
                items: [
                    me.getFileInfoPanel(),
                    me.getFileUploadPanel(),
                    me.getFileChoosePanel()
                ]
            }
        ];
    },

    /**
     * @return [object]
     */
    getEsdInfoPanel: function() {
        var me = this;

        var name = me.esdRecord.get('name');

        if (me.esdRecord.get('additionalText')) {
            name = name + ' - ' + me.esdRecord.get('additionalText');
        }

        return  {
            xtype: 'form',
            margin: '0 0 10',
            flex: 0,
            defaults: {
                labelWidth: 155
            },
            bodyPadding: 10,
            title: me.snippets.esdInfo.title,
            items: [
                {
                    xtype: 'displayfield',
                    readonly: true,
                    value: name,
                    fieldLabel: me.snippets.esdInfo.article,
                    anchor: '100%'
                },
                {
                    xtype: 'checkboxfield',
                    fieldLabel: me.snippets.esdInfo.serialAdministration,
                    boxLabel: me.snippets.esdInfo.enableSerialAdministration,
                    checked: me.esdRecord.get('hasSerials'),
                    anchor: '100%',
                    handler: function(checkbox, checked) {
                        me.fireEvent('hasSerialsChanged', checked);
                    }
                }
            ]
        };
    },


    /**
     * Creates the XTemplate for the file information panel
     *
     * @return [object] generated Ext.XTemplate
     */
    createInfoPanelTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '<tpl for=".">',
            '<tpl if="file">',
            '<p>{literal}<span style="font-weight: bold;">Dateiname:</span> {file}{/literal}</p>',
            '<tpl else>',
            '<p>' + me.snippets.fileInfo.noFile + '</p>',
            '</tpl>',
            '</tpl>'
        );
    },

    /**
     * Returns the fileinfo panel
     *
     * @return [object]
     */
    getFileInfoPanel: function() {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            tpl: me.createInfoPanelTemplate(),
            margin: '0 0 12',
            data: me.esdRecord.data
        });

        me.downloadButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-inbox-download',
            text: me.snippets.fileInfo.downloadFile,
            action: 'downloadFile',
            handler: function() {
                me.fireEvent('downloadFile');
            }
        });

        return {
            xtype: 'panel',
            margin: '0 0 10',
            bodyPadding: 8,
            title: me.snippets.fileInfo.title,
            items: [
                me.infoView,
                me.downloadButton
            ]
        };
    },


    /**
     * Creates the drop zone for article images
     * @return Shopware.app.FileUpload
     */
    getMediaDropZone: function() {
        var me = this;
        var confirmationCallback = function (apply) {
            if (apply === 'cancel') {
                return;
            }

            me.fileUpload.on('fileUploaded', function () {
                me.fileUpload.requestURL = '{url controller="article" action="uploadEsdFile"}';
            }, { single: true });

            if (apply === 'yes') {
                me.fileUpload.requestURL = '{url controller="article" action="uploadEsdFile" uploadMode=overwrite}';
                me.fileUpload.reuploadFiles();
            }
            if (apply === 'no') {
                me.fileUpload.requestURL = '{url controller="article" action="uploadEsdFile"  uploadMode=rename}';
                me.fileUpload.reuploadFiles();
            }
        };

        return me.fileUpload = Ext.create('Shopware.app.FileUpload', {
            name: 'drop-zone',
            requestURL: '{url controller="article" action="uploadEsdFile"}',
            showInput: false,
            padding:0,
            checkSize: false,
            checkType: false,
            checkAmount: false,
            enablePreviewImage: false,
            dropZoneText: me.snippets.fileUpload.dropZoneText,
            listeners: {
                uploadFailed: function (response) {
                    if (!response.hasOwnProperty('fileExists')) {
                        return;
                    }

                    Ext.MessageBox.confirm(me.getMessageBoxConfig('{s name=esd/detail/fileexists/title}File already exists{/s}', '{s name=esd/detail/fileexists/message}Do you want to overwrite the existing file?{/s}', confirmationCallback));
                },
                fileUploaded: function (target, response) {
                    if (!response.hasOwnProperty('newName')) {
                        return;
                    }


                    Ext.Msg.alert('{s name=esd/detail/rename/title}Upload{/s}', Ext.String.format(me.snippets.fileUpload.renameMessage, response.newName));
                }
            }
        });
    },

    getMessageBoxConfig: function(title, message, callback) {
        var me = this;
        return {
            title: title,
            icon: Ext.Msg.QUESTION,
            msg: message,
            buttons: Ext.Msg.YESNOCANCEL,
            buttonText: {
                yes: me.snippets.fileUpload.buttonOverwrite,
                no: me.snippets.fileUpload.buttonRename,
                cancel: me.snippets.fileUpload.buttonCancel
            },
            callback: callback
        };
    },

    getFileUploadPanel: function() {
        var me = this;

        me.uploadField  = Ext.create('Ext.form.field.File', {
            buttonOnly: false,
            xtype: 'filefield',
            margin: '0 0 10',
            fieldLabel: me.snippets.fileUpload.selection,
            labelWidth: 60,
            anchor: '100%',
            buttonText : me.snippets.fileUpload.selectFile,
            listeners: {
                scope: this,
                afterrender: function(btn) {
                    btn.fileInputEl.dom.multiple = true;
                },
                change: function(field) {
                    me.fireEvent('mediaUpload', field);
                }
            },
            buttonConfig : {
                iconCls: 'sprite-inbox-upload',
                cls: 'small secondary'
            }
        });

        return {
            xtype: 'form',
            margin: '0 0 10',
            bodyPadding: 10,
            title: me.snippets.fileUpload.title,
            items: [
                me.uploadField,
                me.getMediaDropZone()
            ]
        };
    },

    getFileChoosePanel: function() {
        var me = this;

        return {
            xtype: 'panel',
            layout: {
                type: 'anchor'
            },
            bodyPadding: 10,
            title: me.snippets.fileChoose.title,
            items: [
                {
                    xtype: 'combobox',
                    store: me.fileStore,
                    valueField: 'filename',
                    forceSelection: true,
                    queryMode: 'local',
                    displayField: 'filename',
                    fieldLabel: 'Datei',
                    emptyText: me.snippets.fileChoose.pleaseChoose,
                    anchor: '100%',
                    listeners: {
                        select: function(field, records) {
                            var filename = records[0].get('filename');
                            me.fireEvent('fileChanged', filename);
                        }
                    }
                }
            ]
        };
    }
});
//{/block}
