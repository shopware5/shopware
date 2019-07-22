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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/document}

//{block name="backend/config/view/form/document"}
Ext.define('Shopware.apps.Config.view.form.Document', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-document',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.Document',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            items: me.getFormItems(),
            store: 'detail.Document',
            width: '60%',
            plugins: [{
                pluginId: 'translation',
                ptype: 'translation',
                translationType: 'documents',
                translationMerge: true
            }]
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            xtype: 'gridcolumn',
            // Show the translated name saved in description instead of the (probably German) name
            dataIndex: 'description',
            text: '{s name=document/table/name_text}Name{/s}',
            flex: 1,
            getSortParam: function () {
                return 'name';
            }
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        var html =
                '<div style="font-size: 10px" class="containerDocument">' +
                    '<div style="border:1px dotted;position:absolute;width:25px;height:15px;margin-left:5px;margin-top:15px;text-align:center">Body</div>' +
                    '<div style="width:30px;margin:0 auto;height:5px;background-color:#CCC"></div>' +
                    '<div style="position:absolute;width:5px;margin-top:202px;height:30px;background-color:#CCC"></div>' +
                    '<div style="position:absolute;width:5px;margin-top:202px;margin-left:310px;height:30px;background-color:#CCC"></div>' +
                    '<div style="position:absolute;width:30px;margin-top:425px;;margin-left:140px;height:5px;background-color:#CCC"></div>' +
                    '<div style="border:1px dotted;position:absolute;width:90px;height:25px;margin-left:45px;margin-top:15px;text-align:center">Logo</div>' +
                    '<div style="border:1px dotted;position:absolute;width:100px;height:15px;margin-left:45px;margin-top:50px;text-align:center;font-weight:bold">[Header_Box_Left]</div>' +
                    '<div style="border:1px dotted;position:absolute;width:100px;height:45px;margin-left:45px;margin-top:65px;text-align:center">Header_Sender<br />Header_Recipient</div>' +
                    '<div style="border:1px dotted;position:absolute;width:60px;height:110px;margin-left:245px;margin-top:55px;text-align:center;font-weight:bold">Header_<br />Box_<br />Right</div>' +
                    '<div style="border:1px dotted;position:absolute;width:260px;height:120px;margin-left:45px;margin-top:45px;text-align:center">[Header]</div>' +
                    '<div style="border:1px dotted;position:absolute;width:160px;height:20px;margin-left:45px;margin-top:180px;text-align:center">Header_Box_Bottom</div>' +
                    '<div style="border:1px dotted;position:absolute;width:250px;height:105px;margin-left:45px;margin-top:225px;text-align:center">Content<br />Td/Td_Head/Td_Line/Td_Name</div>' +
                    '<div style="border:1px dotted;position:absolute;width:95px;height:45px;margin-left:200px;margin-top:335px;text-align:center">Content_Amount</div>' +
                    '<div style="border:1px dotted;position:absolute;width:95px;height:35px;margin-left:45px;margin-top:335px;text-align:center">Content_Info</div>' +
                    '<div style="border:1px dotted;position:absolute;width:250px;height:15px;margin-left:45px;margin-top:400px;text-align:center">Footer</div>' +
                '</div>'
        return [{
            xtype: 'fieldset',
            defaults: {
                anchor: '100%',
                labelWidth: 250,
                xtype: 'textfield'
            },
            items:[{
                name: 'id',
                fieldLabel: '{s name=document/detail/id_label}ID{/s}',
                hidden: true
            },{
                name: 'name',
                fieldLabel: '{s name=document/detail/name_label}Name{/s}',
                allowBlank: false,
                translatable: true
            },{
                name: 'key',
                fieldLabel: '{s name=document/detail/key_label}Technical name{/s}',
                allowBlank: false,
                validator: function(key){
                    var keysOfOtherElements = [],
                        elementId = me.query('fieldset [name=id]')[0].getValue();

                    Ext.getStore('form.Document').each(function(record) {
                        var id = record.data.id;
                        if (record.data.id != elementId) {
                            keysOfOtherElements.push(record.data.key);
                        }
                    });

                    if (key === '') {
                        return '{s name=document/detail/no_key}Please specify a unique technical name.{/s}';
                    }

                    if (keysOfOtherElements.indexOf(key) === -1) {
                        return true;
                    } else {
                        return '{s name=document/detail/key_exists}The current key already exists. Please specify a unique technical name.{/s}';
                    }
                }
            },{
                name: 'numbers',
                xtype: 'config-element-select',
                valueField: 'name',
                displayField: 'description',
                store: 'Shopware.apps.Config.store.form.Number',
                fieldLabel: '{s name=document/detail/numbers_label}Numbers{/s}'
            },{
                name: 'template',
                fieldLabel: '{s name=document/detail/template_label}Template{/s}'
            },{
                name: 'left',
                xtype: 'config-element-number',
                fieldLabel: '{s name=document/detail/left_label}Spacing left (mm){/s}'
            },{
                name: 'right',
                xtype: 'config-element-number',
                fieldLabel: '{s name=document/detail/right_label}Spacing right (mm){/s}'
            },{
                name: 'top',
                xtype: 'config-element-number',
                fieldLabel: '{s name=document/detail/top_label}Spacing top (mm){/s}'
            },{
                name: 'bottom',
                xtype: 'config-element-number',
                fieldLabel: '{s name=document/detail/bottom_label}Spacing bottom (mm){/s}'
            },{
                name: 'pageBreak',
                xtype: 'config-element-number',
                fieldLabel: '{s name=document/detail/pagebreak_label}Articles per page{/s}'
            },{
                xtype: 'config-element-boolean',
                name: 'booleanPageBreak',
                fieldLabel: '{s name=document/detail/booleanpagebreak_label}Pagination{/s}',
                style: {
                    marginTop: '20px'
                }
            },{
                xtype: 'container',
                layout: 'hbox',
                items: [{
                    xtype: 'config-element-button',
                    text: '{s name=document/detail/preview_button}Preview{/s}',
                    width: 150,
                    iconCls: 'sprite-document-pdf',
                    handler: function(){
                        var detailPanel = me.down('config-base-detail'),
                            values = detailPanel.getValues();
                        var previewPageBreak = values['booleanPageBreak'] ? '&pagebreak=on' : '';
                        window.open('{url controller=document}?typ=' + values.id + '&preview=1&sampleData=1' + previewPageBreak);
                    }
                },{
                    xtype: 'config-element-button',
                    width: 150,
                    style: {
                        marginLeft: '10px'
                    },
                    iconCls: 'sprite-document-template',
                    text: '{s name=document/detail/preview_structure}View structure{/s}',
                    handler: function(){
                        Ext.create('Ext.window.Window',{
                            title: 'Structure',
                            width: '319px',
                            height: '471px',
                            border: 0,
                            items: {
                                xtype: 'container',
                                html: html
                            }
                        }).show();
                    }
                }]

            }]
        },{
            xtype: 'fieldset',
            title: '{s name=document/detail/elements_label}Elements{/s}',
            name: 'elementFieldSet',
            defaults: {
                anchor: '100%',
                labelWidth: 250,
                xtype: 'textfield'
            },
            items:[{
                xtype: 'config-element-button',
                text: '{s name=document/detail/applyconfig_label}Use the element-config for all forms{/s}',
                width: '100%',
                style :{
                    'margin-bottom': '10px'
                },
                handler: function(){
                    var detailPanel = me.down('config-base-detail'),
                        values = detailPanel.getValues(),
                        id = values['id'];

                    Ext.MessageBox.confirm(
                        '{s name=document/detail/applyconfig_label}Use the element-config for all forms{/s}',
                        '{s name=document/detail/applyconfig_config_confirm_message}Are you sure you want to take over the properties for all types of documents?{/s}',
                        function (response) {
                            if (response !== 'yes') {
                                return false;
                            }
                            Ext.Ajax.request({
                                url: '{url controller="Document" action="duplicateProperties"}',
                                params: {
                                    id: id
                                },
                                scope: this
                            });
                        }
                    );
                }
            },{
                xtype: 'combo',
                queryMode:'local',
                forceSelection: true,
                valueField: 'id',
                displayField: 'name',
                name: 'elements'
            },{
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_body}Body-Content{/s}',
                labelWidth: 100,
                name: 'Body_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_logo}Logo-Content{/s}',
                labelWidth: 100,
                name: 'Logo_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header_recipient}Header-Recipient-Content{/s}',
                labelWidth: 100,
                name: 'Header_Recipient_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header}Header-Content{/s}',
                labelWidth: 100,
                name: 'Header_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header_sender}Header-Sender-Content{/s}',
                labelWidth: 100,
                name: 'Header_Sender_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header_box_left}Header-Box-Left-Content{/s}',
                labelWidth: 100,
                name: 'Header_Box_Left_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header_box_right}Header-Box-Right-Content{/s}',
                labelWidth: 100,
                name: 'Header_Box_Right_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_label_header_box_bottom}Header-Box-Bottom-Content{/s}',
                labelWidth: 100,
                name: 'Header_Box_Bottom_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_content_label}Content-Content{/s}',
                labelWidth: 100,
                name: 'Content_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_td_label}Td-Content{/s}',
                labelWidth: 100,
                name: 'Td_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_td_name_label}Td-Name-Content{/s}',
                labelWidth: 100,
                name: 'Td_Name_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_td_line_label}Td-Line-Content{/s}',
                labelWidth: 100,
                name: 'Td_Line_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_td_head_label}Td-Head-Content{/s}',
                labelWidth: 100,
                name: 'Td_Head_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_footer_label}Footer-Content{/s}',
                labelWidth: 100,
                name: 'Footer_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_content_amount_label}Content-Amount-Content{/s}',
                labelWidth: 100,
                name: 'Content_Amount_Value',
                hidden: true,
                translatable: true
            }, {
                xtype: 'tinymce',
                fieldLabel: '{s name=document/detail/content_content_info_label}Content-Info-Content{/s}',
                labelWidth: 100,
                name: 'Content_Info_Value',
                hidden: true,
                translatable: true
            },{
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_body_label}Body-Style{/s}',
                labelWidth: 100,
                name: 'Body_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_logo_label}Logo-Style{/s}',
                labelWidth: 100,
                name: 'Logo_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_recipient_label}Header-Recipient-Style{/s}',
                labelWidth: 100,
                name: 'Header_Recipient_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_label}Header-Style{/s}',
                labelWidth: 100,
                name: 'Header_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_sender_label}Header-Sender-Style{/s}',
                labelWidth: 100,
                name: 'Header_Sender_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_box_left_label}Header-Box-Left-Style{/s}',
                labelWidth: 100,
                name: 'Header_Box_Left_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_box_right_label}Header-Box-Right-Style{/s}',
                labelWidth: 100,
                name: 'Header_Box_Right_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_header_box_bottom_label}Header-Box-Bottom-Style{/s}',
                labelWidth: 100,
                name: 'Header_Box_Bottom_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_content_label}Content-Style{/s}',
                labelWidth: 100,
                name: 'Content_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_td_label}Td-Style{/s}',
                labelWidth: 100,
                name: 'Td_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_td_name_label}Td-Name-Style{/s}',
                labelWidth: 100,
                name: 'Td_Name_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_td_line_label}Td-Line-Style{/s}',
                labelWidth: 100,
                name: 'Td_Line_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_td_head_label}Td-Head-Style{/s}',
                labelWidth: 100,
                name: 'Td_Head_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_footer_label}Footer-Style{/s}',
                labelWidth: 100,
                name: 'Footer_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_content_amount_label}Content-Amount-Style{/s}',
                labelWidth: 100,
                name: 'Content_Amount_Style',
                hidden: true,
                translatable: true
            }, {
                xtype: 'textarea',
                fieldLabel: '{s name=document/detail/style_content_info_label}Content-Info-Style{/s}',
                labelWidth: 100,
                name: 'Content_Info_Style',
                hidden: true,
                translatable: true
            }]
        }];
    }
});
//{/block}
