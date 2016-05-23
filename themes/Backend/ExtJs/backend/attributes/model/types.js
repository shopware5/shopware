
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.model.Types', {
    extend: 'Shopware.data.Model',

    snippets: {
        string: '{s name="type_string"}{/s}',
        text: '{s name="type_text"}{/s}',
        html: '{s name="type_html"}{/s}',
        integer: '{s name="type_integer"}{/s}',
        float: '{s name="type_float"}{/s}',
        date: '{s name="type_date"}{/s}',
        datetime: '{s name="type_datetime"}{/s}',
        boolean: '{s name="type_boolean"}{/s}',
        single_selection: '{s name="type_single_selection"}{/s}',
        multi_selection: '{s name="type_multi_selection"}{/s}',
    },

    fields: [
        { name: 'label', type: 'string', convert: function(value, record) {
            return record.getLabel();
        } },
        { name: 'unified', type: 'string' },
        { name: 'dbal', type: 'string' },
        { name: 'sql', type: 'string' },
        { name: 'extJs', type: 'string' }
    ],

    getLabel: function() {
        var name = this.get('unified');

        if (this.snippets.hasOwnProperty(name)) {
            return this.snippets[name];
        } else {
            return '';
        }
    }
});