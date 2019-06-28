
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.model.AttributeConfig', {
    extend: 'Shopware.data.Model',

    fields: [
        { name: 'id', type: 'integer', useNull: true },
        { name: 'tableName', type: 'string' },
        { name: 'columnName', type: 'string' },
        { name: 'columnType', type: 'string' },
        { name: 'defaultValue', type: 'string', useNull: true, defaultValue: null },
        { name: 'entity', type: 'string', useNull: true },
        { name: 'dbalType', type: 'string' },
        { name: 'sqlType', type: 'string' },
        { name: 'label', type: 'string' },
        { name: 'helpText', type: 'string' },
        { name: 'supportText', type: 'string' },
        { name: 'translatable', type: 'boolean' },
        { name: 'displayInBackend', type: 'boolean', defaultValue: true },
        { name: 'pluginId', type: 'integer' },
        { name: 'configured', type: 'boolean' },
        { name: 'position', type: 'integer' },
        { name: 'custom', type: 'boolean', defaultValue: false },
        { name: 'identifier', type: 'boolean' },
        { name: 'core', type: 'boolean' },
        { name: 'arrayStore', type: 'string' },

        //pseudo columns for view generation / data operations
        { name: 'deleteButton', type: 'boolean' },
        { name: 'originalName', type: 'string', mapping: 'columnName' }
    ],

    configure: function() {
        return {
            controller: 'Attributes'
        };
    },

    allowDelete: function() {
        if (this.get('core')) {
            return false;
        }
        if (this.get('identifier')) {
            return false;
        }
        return this.get('custom');
    },

    allowNameChange: function() {
        if (this.get('identifier')) {
            return false;
        }
        return this.get('custom');
    },

    allowTypeChange: function() {
        if (this.get('identifier')) {
            return false;
        }
        return this.get('custom') || this.get('core');
    },

    allowConfigure: function() {
        return this.get('custom') || this.get('core');
    },

    merge: function(column) {
        var me = this;
        var fields = [
            'columnName',
            'columnType',
            'entity',
            'label',
            'helpText',
            'supportText',
            'arrayStore',
            'translatable',
            'displayInBackend',
            'pluginId',
            'position',
            'custom',
            'dbalType',
            'sqlType'
        ];

        Ext.each(fields, function(field) {
            me.set(field, column.get(field));
        });
    }
});
