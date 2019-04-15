// {namespace name="backend/mail_log/view/detail"}
// {block name="backend/mail_log/view/detail/form_mixin"}
Ext.define('Shopware.apps.MailLog.view.detail.FormMixin', {

    config: {
        editableFields: false,
    },

    createFieldSets: function(config) {
        var me = this;

        if (!!config) {
            me.config = config;
        }

        return [
            me.createMetaDataFieldSet(),
            me.createContentFieldSet(),
        ];
    },

    createMetaDataFieldSet: function () {
        return {
            title: '{s name="fieldset_metadata_title"}{/s}',
            flex: 1,
            fields: {
                subject: {
                    xtype: 'displayfield',
                    fieldLabel: '{s name="metadata_subject_label"}{/s}',
                },
                sentAt: this.createSentAtField,
                sender: {
                    xtype: 'displayfield',
                    fieldLabel: '{s name="metadata_sender_label"}{/s}',
                },
                recipients: this.createRecipientsField
            }
        };
    },

    createContentFieldSet: function () {
        return {
            title: '{s name="fieldset_content_title"}{/s}',
            flex: 3,
            layout: 'fit',
            fields: {
                contentText: this.createContentField,
                contentHtml: this.createContentField
            },
            style: {
                background: '#ffffff',
                paddingRight: 0,
            },
        };
    },

    createRecipientsField: function (model, formField) {
        var me = this;

        if (this.config.editableFields) {
            formField.xtype = 'textfield';
            formField.onBlur = function (event, element) {
                model.set('recipients', me.rawValueToArray(element.value).map(function (address) {
                    return {
                        id: null,
                        mailAddress: address,
                    };
                }));
            }
        } else {
            formField.xtype = 'displayfield';
        }

        formField.fieldLabel = '{s name="metadata_recipients_label"}{/s}';
        formField.valueToRaw = me.arrayValueToRaw;
        formField.allowBlank = false;

        return formField;
    },

    rawValueToArray: function (value) {
        if (!value) {
            return [];
        }

        return value.split(',').map(function (el) {
            return el.trim();
        });
    },

    arrayValueToRaw: function (value) {
        if (!value) {
            return '';
        }

        return value.map(function (recipient) {
            return recipient.mailAddress;
        }).join(', ');
    },

    createSentAtField: function (model, formField) {
        formField.xtype = 'displayfield';
        formField.fieldLabel = '{s name="metadata_sent_at_label"}{/s}';

        formField.valueToRaw = function (value) {
            if (!value) {
                return '';
            }

            return value.toLocaleString();
        };

        return formField;
    },

    createContentField: function(model, formField, value) {
        var me = this,
            isTextContent = value.name === 'contentText',
            hasHtmlContent = model.get('contentHtml').length > 0;

        if (isTextContent === hasHtmlContent) {
            // Discard element if we're looking at text content, but HTML content is available,
            // or if we're looking at HTML content, but it's empty.
            return null;
        }

        var content = hasHtmlContent ? model.get('contentHtml') : model.get('contentText');

        formField.xtype = 'container';
        formField.html = me.formFieldHtml(content, !hasHtmlContent);
        formField.layout = 'fit';
        formField.height = '100%';

        return formField;
    },

    formFieldHtml: function (content, textOnly) {
        if (textOnly) {
            content = '<pre>' + content + '</pre>';
        }
        // {literal}
        var template = '<iframe sandbox="" src="data:text/html;charset=UTF-8,{0}"></iframe>';
        // {/literal}

        return Ext.String.format(template, encodeURIComponent(content));
    },

});
// {/block}