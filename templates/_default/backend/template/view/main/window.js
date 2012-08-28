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
 * @package    Template
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/template/view/main}
//{block name="backend/template/view/main/window"}
Ext.define('Shopware.apps.Template.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.template-main-window',
    layout: 'fit',
    width: 850,
    height: 600,
    stateful: true,
    stateId: 'shopware-template-main-window',
    cls: Ext.baseCSSPrefix + 'template-main-window',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title:   '{s name=title}Template Selector{/s}',

        panelMoreInformation:  '{s name=panel_more_information}Further information{/s}',

        tabInstalledTemplates: '{s name=tab_Installed_templates}Installed templates{/s}',
        tabInstallTemplate: '{s name=tab_install_template}Install templates{/s}',
        tabAvailableTemplates: '{s name=tab_available_templates}Available templates{/s}',

        buttonChooseFile:       '{s name=button_choose_file}Choose file{/s}',
        emptyTextChooseFile:    '{s name=empty_text_choose_file}Please choose a file..{/s}',
        messageUploadFile:      '{s name=message_upload_file}Uploading your file...{/s}',
        buttonStartUpload:      '{s name=button_start_upload}Start upload{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.items = [{
            xtype: 'tabpanel',
            itemId: 'tabpanel',
            items: [
                {
                    xtype: 'template-main-media',
                    title: me.snippets.tabInstalledTemplates,
                    templateStore: me.templateStore
                },
                {
                    xtype: 'container',
                    layout: 'fit',
                    title: me.snippets.tabInstallTemplate,
                    items: [
                            me.createImportForm()
                    ]
                },
                {
                    title: me.snippets.tabAvailableTemplates,
                    disabled: true
                }
            ]
        }];


        me.callParent(arguments);
    },

    createImportForm: function() {
        var me = this;

        return {
            xtype: 'form',
            layout: 'anchor',
            bodyPadding: 10,

            defaults: {
                layout: 'anchor',
                labelWidth: 155,
                anchor: '100%'
            },

            items: [{
                xtype: 'filefield',
                emptyText: me.snippets.emptyTextChooseFile,
                buttonText: me.snippets.buttonChooseFile,
                name: 'file',
                fieldLabel: 'File',
                allowBlank: false,
                buttonConfig: {
                    cls: 'small secondary'
                }
            }],

            buttons: [{
                text: me.snippets.buttonStartUpload,
                cls: 'primary',
                handler: function () {
                    var form = this.up('form').getForm();
                    if (form.isValid()) {
                        form.submit({
                            url: ' {url module=backend controller=template action=uploadTemplate}',
                            waitMsg: me.snippets.messageUploadFile,
                            success: function (fp, o) {
                                Ext.Msg.alert('Result', "Uploaded template: " + o.result.data.name);
                                me.templateStore.load();
                            },
                            failure: function (fp, o) {
                                Ext.Msg.alert('Fehler', o.result.message);
                            }
                        });
                    }
                }
            }]
        };
    }
});
//{/block}
