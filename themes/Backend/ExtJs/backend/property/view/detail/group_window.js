
//{namespace name=backend/property/view/main}
//{block name="backend/property/view/detail/group_window"}

Ext.define('Shopware.apps.Property.view.detail.GroupWindow', {
    extend: 'Shopware.apps.Property.view.detail.DetailWindow',
    alias: 'widget.property-group-detail-window',
    translationType: 'propertyoption',
    attributeTable: 's_filter_options_attributes',
    successNotification: '{s name="message/group_successful_saved"}{/s}',
    title: '{s name="group/window_title"}{/s}',

    fields: [{
        xtype: 'textfield',
        fieldLabel: '{s name=group/column_name}{/s}',
        translatable: true,
        name: 'name',
        translationName: 'optionName',
        allowBlank: false
    }, {
        xtype: 'checkbox',
        fieldLabel: '{s name=group/column_filterable}{/s}',
        inputValue: true,
        uncheckedValue: false,
        name: 'filterable'
    }]
});

//{/block}
