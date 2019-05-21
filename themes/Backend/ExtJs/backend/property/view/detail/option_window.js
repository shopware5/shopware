
//{namespace name=backend/property/view/main}
//{block name="backend/property/view/detail/option_window"}

Ext.define('Shopware.apps.Property.view.detail.OptionWindow', {
    extend: 'Shopware.apps.Property.view.detail.DetailWindow',
    alias: 'widget.property-option-detail-window',
    translationType: 'propertyvalue',
    attributeTable: 's_filter_values_attributes',
    successNotification: '{s name="message/option_successful_saved"}{/s}',
    title: '{s name="option/window_title"}{/s}',

    fields: [{
        xtype: 'textfield',
        fieldLabel: '{s name=option/column_option}Option{/s}',
        translatable: true,
        name: 'value',
        translationName: 'optionValue',
        allowBlank: false
    }, {
        xtype: 'shopware-media-field',
        labelWidth: 155,
        fieldLabel: '{s name=option/media_field}{/s}',
        name: 'mediaId'
    }]
});

//{/block}
