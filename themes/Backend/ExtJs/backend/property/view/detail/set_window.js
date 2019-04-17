
//{namespace name=backend/property/view/main}
//{block name="backend/property/view/detail/set_window"}

Ext.define('Shopware.apps.Property.view.detail.SetWindow', {
    extend: 'Shopware.apps.Property.view.detail.DetailWindow',
    alias: 'widget.property-set-detail-window',
    translationType: 'propertygroup',
    attributeTable: 's_filter_attributes',
    successNotification: '{s name="message/set_successful_saved"}{/s}',
    title: '{s name="set/window_title"}{/s}',

    fields: [{
        xtype: 'textfield',
        fieldLabel: '{s name=set/column_set}{/s}',
        translatable: true,
        name: 'name',
        translationName: 'groupName',
        allowBlank: false
    }, {
        xtype: 'numberfield',
        name: 'position',
        fieldLabel: '{s name=set/column_position}Position{/s}'
    }, {
        xtype: 'checkbox',
        fieldLabel: '{s name=set/column_comparable}Comparable{/s}',
        inputValue: true,
        uncheckedValue: false,
        name: 'comparable'
    }, {
        xtype: 'combobox',
        name: 'sortMode',
        fieldLabel: '{s name=set/column_sort}Sort{/s}',
        allowBlank: false,
        editable: false,
        mode: 'local',
        displayField: 'label',
        valueField: 'id',
        store: new Ext.data.SimpleStore({
            fields:['id', 'label'],
            data: [
                [0, '{s name=set/cobo_sort_mode_alphabetical}{/s}'],
                [1, '{s name=set/cobo_sort_mode_numeric}{/s}'],
                [3, '{s name=set/cobo_sort_mode_postition}{/s}']
            ]
        })
    }]
});

//{/block}
