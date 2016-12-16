
//{block name="backend/emotion/model/preset"}
Ext.define('Shopware.apps.Emotion.model.Preset', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/emotion/model/preset/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'premium', type: 'bool' },
        { name: 'thumbnail', type: 'string' },
        { name: 'presetData', type: 'string' },
        { name: 'label', type: 'string' },
        { name: 'description', type: 'string' }
    ],

});
//{/block}